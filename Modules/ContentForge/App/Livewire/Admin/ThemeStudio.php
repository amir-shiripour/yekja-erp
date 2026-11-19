<?php

declare(strict_types=1);

namespace Modules\ContentForge\App\Livewire\Admin;

use App\Services\ThemeManager;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Livewire\Component;
use Modules\ContentForge\App\Services\ThemeResolver;
use Modules\ContentForge\Entities\ContentSetting;
use Modules\Settings\Entities\Setting;

class ThemeStudio extends Component
{
    use AuthorizesRequests;

    public string $activeCrmTheme = 'default';
    public bool $crmOverridesEnabled = true;
    public string $activeWebTheme = 'default';
    public bool $showCreateThemeModal = false;
    public string $newThemeKey = '';
    public string $newThemeName = '';
    public string $newThemeDescription = '';
    public array $initialOverrideViews = ['admin.users.edit'];
    public string $initialCustomViews = '';
    public bool $showOverridesModal = false;
    public string $inspectingThemeKey = '';
    public string $selectedOverrideTarget = 'admin.users.edit';
    public string $customOverrideView = '';
    public bool $showEditThemeModal = false;
    public string $editingThemeKey = '';
    public string $editThemeName = '';
    public string $editThemeDescription = '';
    public string $editThemeVersion = '1.0.0';

    public function openEditThemeModal(string $themeKey): void
    {
        $themes = app(ThemeResolver::class)->getAvailableThemes();
        $theme = $themes[$themeKey] ?? null;
        if (!$theme) {
            return;
        }

        $this->editingThemeKey = $themeKey;
        $this->editThemeName = $theme['name'];
        $this->editThemeDescription = $theme['description'];
        $this->editThemeVersion = $theme['version'] ?? '1.0.0';
        $this->showEditThemeModal = true;
    }

    public function closeEditThemeModal(): void
    {
        $this->showEditThemeModal = false;
        $this->reset(['editingThemeKey', 'editThemeName', 'editThemeDescription', 'editThemeVersion']);
    }

    public function updateTheme(): void
    {
        $this->validate([
            'editThemeName' => ['required', 'string', 'min:2', 'max:100'],
            'editThemeDescription' => ['nullable', 'string', 'max:255'],
            'editThemeVersion' => ['nullable', 'string', 'max:20'],
        ]);

        $themeDir = resource_path("views/themes/{$this->editingThemeKey}");
        $jsonPath = $themeDir . DIRECTORY_SEPARATOR . 'theme.json';

        $meta = [];
        if (File::exists($jsonPath)) {
            $meta = json_decode(File::get($jsonPath), true) ?: [];
        }

        $meta['name'] = trim($this->editThemeName);
        $meta['description'] = trim($this->editThemeDescription);
        $meta['version'] = trim($this->editThemeVersion ?: '1.0.0');
        $meta['updated_at'] = now()->toIso8601String();

        File::put($jsonPath, json_encode($meta, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

        $this->showEditThemeModal = false;
        session()->flash('success', "مشخصات پوسته «{$meta['name']}» با موفقیت ویرایش شد.");
    }

    public function mount(): void
    {
        $this->authorize('content.themes.manage');
        $this->syncActiveStates();
    }

    protected function syncActiveStates(): void
    {
        $this->activeCrmTheme = (string)ContentSetting::getValue('crm.theme_key', 'default');
        $this->crmOverridesEnabled = ContentSetting::getValue('crm.enable_overrides', 'true') === 'true';
        $this->activeWebTheme = (string)(Setting::where('key', 'app_theme')->value('value') ?? 'default');
    }

    public function activateCrmTheme(string $themeKey): void
    {
        $themeKey = strtolower(trim($themeKey));
        $themes = app(ThemeResolver::class)->getAvailableThemes();

        if (!isset($themes[$themeKey])) {
            session()->flash('error', 'پوسته انتخاب‌شده یافت نشد.');
            return;
        }

        ContentSetting::setValue('crm.theme_key', $themeKey);
        ContentSetting::setValue('crm.enable_overrides', 'true');
        app(ThemeManager::class)->clearCache();

        $this->syncActiveStates();
        session()->flash('success', "پوسته «{$themes[$themeKey]['name']}» با موفقیت برای کل پنل مدیریت CRM فعال شد.");
    }

    public function toggleCrmOverrides(): void
    {
        $newState = $this->crmOverridesEnabled ? 'false' : 'true';
        ContentSetting::setValue('crm.enable_overrides', $newState);
        app(ThemeManager::class)->clearCache();

        $this->syncActiveStates();
        session()->flash(
            'success',
            $newState === 'true'
                ? 'موتور بازنویسی (Override) ویوهای پنل CRM فعال شد.'
                : 'موتور بازنویسی غیرفعال شد؛ پنل موقتاً به ظاهر استاندارد هسته بازگشت.'
        );
    }

    public function activateWebTheme(string $themeKey): void
    {
        $themeKey = strtolower(trim($themeKey));
        $themes = app(ThemeResolver::class)->getAvailableThemes();

        if (!isset($themes[$themeKey])) {
            session()->flash('error', 'پوسته انتخاب‌شده یافت نشد.');
            return;
        }

        if (empty($themes[$themeKey]['has_web_index'])) {
            session()->flash('error', "پوسته «{$themes[$themeKey]['name']}» مخصوص پنل CRM است و صفحه اصلی وب‌سایت (index.blade.php) ندارد.");
            return;
        }

        Setting::updateOrCreate(['key' => 'app_theme'], ['value' => $themeKey]);
        ContentSetting::setValue('general.default_theme_key', $themeKey);
        Cache::forget('app_theme');

        $this->syncActiveStates();
        session()->flash('success', "پوسته «{$themes[$themeKey]['name']}» برای وب‌سایت عمومی (صفحه اول) فعال شد.");
    }

    public function openCreateThemeModal(): void
    {
        $this->reset(['newThemeKey', 'newThemeName', 'newThemeDescription', 'initialCustomViews']);
        $this->initialOverrideViews = ['admin.users.edit'];
        $this->showCreateThemeModal = true;
    }

    public function closeCreateThemeModal(): void
    {
        $this->showCreateThemeModal = false;
        $this->reset(['newThemeKey', 'newThemeName', 'newThemeDescription', 'initialCustomViews']);
        $this->initialOverrideViews = ['admin.users.edit'];
    }

    public function selectAllInitialViews(): void
    {
        $all = [];
        foreach (app(ThemeResolver::class)->getCommonOverrideTargets() as $items) {
            foreach (array_keys($items) as $viewKey) {
                $all[] = $viewKey;
            }
        }
        $this->initialOverrideViews = $all;
    }

    public function clearInitialViews(): void
    {
        $this->initialOverrideViews = [];
    }

    public function createTheme(): void
    {
        $this->validate([
            'newThemeKey' => ['required', 'string', 'min:2', 'max:40', 'regex:/^[a-z0-9\-_]+$/i'],
            'newThemeName' => ['required', 'string', 'min:2', 'max:100'],
            'newThemeDescription' => ['nullable', 'string', 'max:255'],
        ], [
            'newThemeKey.regex' => 'شناسه انگلیسی پوسته فقط می‌تواند شامل حروف انگلیسی، اعداد، خط فاصله (-) و زیرخط (_) باشد.',
            'newThemeKey.required' => 'شناسه انگلیسی پوسته الزامی است.',
            'newThemeName.required' => 'نام نمایشی پوسته الزامی است.',
        ]);

        $themeKey = strtolower(trim($this->newThemeKey));
        $themeDir = resource_path("views/themes/{$themeKey}");

        if (File::isDirectory($themeDir)) {
            $this->addError('newThemeKey', 'پوسته‌ای با این شناسه انگلیسی از قبل وجود دارد.');
            return;
        }

        File::makeDirectory($themeDir, 0755, true);

        $meta = [
            'name' => trim($this->newThemeName),
            'description' => trim($this->newThemeDescription ?: 'پوسته سفارشی CRM'),
            'version' => '1.0.0',
            'target' => 'crm',
            'author' => auth()->user()?->name ?? 'مدیر سیستم',
            'created_at' => now()->toIso8601String(),
        ];
        File::put(
            $themeDir . DIRECTORY_SEPARATOR . 'theme.json',
            json_encode($meta, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        );

        $viewsToCopy = array_filter(array_map('trim', $this->initialOverrideViews));
        if (!empty(trim($this->initialCustomViews))) {
            $customs = array_filter(array_map('trim', explode(',', $this->initialCustomViews)));
            $viewsToCopy = array_values(array_unique(array_merge($viewsToCopy, $customs)));
        }

        $copiedCount = 0;
        foreach ($viewsToCopy as $viewName) {
            if ($viewName !== 'none') {
                $res = $this->copyViewToThemeOverrides($themeKey, $viewName);
                if (!empty($res['ok'])) {
                    $copiedCount++;
                }
            }
        }

        $this->syncActiveStates();
        $this->showCreateThemeModal = false;
        $msg = "پوسته «{$this->newThemeName}» با موفقیت ساخته شد";
        if ($copiedCount > 0) {
            $msg .= " و {$copiedCount} فایل در پوشه overrides کپی گردید.";
        } else {
            $msg .= '.';
        }
        session()->flash('success', $msg);
    }

    public function openOverridesModal(string $themeKey): void
    {
        $this->inspectingThemeKey = strtolower(trim($themeKey));
        $this->selectedOverrideTarget = 'admin.users.edit';
        $this->customOverrideView = '';
        $this->showOverridesModal = true;
    }

    public function closeOverridesModal(): void
    {
        $this->showOverridesModal = false;
        $this->inspectingThemeKey = '';
    }

    public function addOverrideFile(): void
    {
        if (empty($this->inspectingThemeKey)) {
            return;
        }

        $viewName = !empty(trim($this->customOverrideView))
            ? trim($this->customOverrideView)
            : trim($this->selectedOverrideTarget);

        if (empty($viewName)) {
            session()->flash('error', 'لطفاً یک صفحه را انتخاب کنید یا نام ویو را وارد نمایید.');
            return;
        }

        $result = $this->copyViewToThemeOverrides($this->inspectingThemeKey, $viewName);

        if ($result['ok']) {
            $this->customOverrideView = '';
            session()->flash('override_success', "فایل ویو [{$viewName}] با موفقیت در مسیر {$result['relative']} کپی شد و آماده ویرایش است.");
        } else {
            session()->flash('override_error', $result['message']);
        }
    }

    public function deleteOverrideFile(string $relativePath): void
    {
        if (empty($this->inspectingThemeKey)) {
            return;
        }

        // Prevent path traversal
        $cleanRel = str_replace(['..', '\\'], ['', '/'], ltrim($relativePath, '/\\'));
        $overridesRoot = realpath(resource_path("views/themes/{$this->inspectingThemeKey}/overrides"));
        $targetFile = $overridesRoot ? realpath($overridesRoot . DIRECTORY_SEPARATOR . $cleanRel) : false;

        if ($overridesRoot && $targetFile && str_starts_with($targetFile, $overridesRoot) && File::exists($targetFile)) {
            File::delete($targetFile);
            session()->flash('override_success', "فایل اورراید [{$cleanRel}] حذف شد.");
        } else {
            session()->flash('override_error', 'فایل مورد نظر یافت نشد.');
        }
    }

    protected function copyViewToThemeOverrides(string $themeKey, string $viewName): array
    {
        try {
            $finder = app('view')->getFinder();
            $sourcePath = $finder->find($viewName);
        } catch (\Throwable $e) {
            return ['ok' => false, 'message' => "ویوی [{$viewName}] در سیستم یافت نشد."];
        }

        if (!$sourcePath || !File::exists($sourcePath)) {
            return ['ok' => false, 'message' => "فایل فیزیکی ویوی [{$viewName}] پیدا نشد."];
        }

        if (str_contains($viewName, '::')) {
            [$namespace, $rel] = explode('::', $viewName, 2);
            $relPath = $namespace . '/' . str_replace('.', '/', $rel) . '.blade.php';
        } else {
            $relPath = str_replace('.', '/', $viewName) . '.blade.php';
        }

        $destPath = resource_path("views/themes/{$themeKey}/overrides/{$relPath}");

        if (File::exists($destPath)) {
            return ['ok' => false, 'message' => "فایل [{$relPath}] از قبل در این پوسته وجود دارد."];
        }

        if (!File::isDirectory(dirname($destPath))) {
            File::makeDirectory(dirname($destPath), 0755, true, true);
        }
        File::copy($sourcePath, $destPath);

        return ['ok' => true, 'relative' => "themes/{$themeKey}/overrides/{$relPath}"];
    }

    public function render()
    {
        $resolver = app(ThemeResolver::class);
        $availableThemes = $resolver->getAvailableThemes();
        $commonTargets = $resolver->getCommonOverrideTargets();
        $inspectingTheme = $this->inspectingThemeKey ? ($availableThemes[$this->inspectingThemeKey] ?? null) : null;

        return view('contentforge::livewire.admin.theme-studio', compact(
            'availableThemes',
            'commonTargets',
            'inspectingTheme'
        ))->layout('layouts.user');
    }
}
