<?php

declare(strict_types=1);

namespace Modules\ContentForge\App\Livewire\Admin;

use App\Services\ThemeManager;
use Illuminate\Support\Facades\File;
use Livewire\Component;
use Modules\ContentForge\App\Services\ThemeResolver;
use Modules\ContentForge\Entities\ContentSetting;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Throwable;

class ContentSettings extends Component
{
    use AuthorizesRequests;

    public array $settings = [];
    public bool $showCreateThemeModal = false;
    public string $newThemeKey = '';
    public string $newThemeName = '';
    public string $newThemeDescription = '';
    public string $initialOverrideView = 'admin.users.edit';

    public function openCreateThemeModal(): void
    {
        $this->reset(['newThemeKey', 'newThemeName', 'newThemeDescription']);
        $this->initialOverrideView = 'admin.users.edit';
        $this->showCreateThemeModal = true;
    }

    public function closeCreateThemeModal(): void
    {
        $this->showCreateThemeModal = false;
        $this->reset(['newThemeKey', 'newThemeName', 'newThemeDescription']);
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
            'description' => trim($this->newThemeDescription ?: 'پوسته سفارشی ساخته‌شده از پنل CRM'),
            'version' => '1.0.0',
            'target' => 'crm',
            'author' => auth()->user()?->name ?? 'مدیر سیستم',
            'created_at' => now()->toIso8601String(),
        ];
        File::put(
            $themeDir . DIRECTORY_SEPARATOR . 'theme.json',
            json_encode($meta, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        );

        $createdOverridePath = null;
        if (!empty($this->initialOverrideView) && $this->initialOverrideView !== 'none') {
            try {
                $finder = app('view')->getFinder();
                $sourcePath = $finder->find($this->initialOverrideView);

                if ($sourcePath && File::exists($sourcePath)) {
                    if (str_contains($this->initialOverrideView, '::')) {
                        [$namespace, $rel] = explode('::', $this->initialOverrideView, 2);
                        $relPath = $namespace . DIRECTORY_SEPARATOR . str_replace('.', DIRECTORY_SEPARATOR, $rel);
                    } else {
                        $relPath = str_replace('.', DIRECTORY_SEPARATOR, $this->initialOverrideView);
                    }

                    $destPath = resource_path("views/themes/{$themeKey}/overrides/{$relPath}.blade.php");
                    File::makeDirectory(dirname($destPath), 0755, true);
                    File::copy($sourcePath, $destPath);
                    $createdOverridePath = $destPath;
                }
            } catch (Throwable $e) {
                // Ignore initial copy errors, theme is still created
            }
        }

        $this->settings['crm']['theme_key'] = $themeKey;
        $this->settings['crm']['enable_overrides'] = 'true';
        ContentSetting::setValue('crm.theme_key', $themeKey);
        ContentSetting::setValue('crm.enable_overrides', 'true');

        if (app()->bound(ThemeManager::class)) {
            app(ThemeManager::class)->clearCache();
        }

        $this->showCreateThemeModal = false;
        $successMsg = "پوسته «{$this->newThemeName}» با موفقیت ساخته شد و بلافاصله به عنوان پوسته فعال CRM تنظیم گردید.";
        if ($createdOverridePath) {
            $successMsg .= " فایل صفحه ایجاد کاربر نیز به صورت خودکار در پوشه overrides کپی شد.";
        }
        session()->flash('success', $successMsg);
    }

    protected function rules(): array
    {
        return [
            'settings.general.posts_per_page' => 'required|integer|min:1|max:100',
            'settings.general.default_theme_key' => 'required|string|max:50',
            'settings.general.enable_comments' => 'required|in:true,false',
            'settings.general.enable_tags' => 'required|in:true,false',
            'settings.general.reading_time_wpm' => 'required|integer|min:50|max:500',
            'settings.crm.theme_key' => 'required|string|max:50',
            'settings.crm.enable_overrides' => 'required|in:true,false',
            'settings.seo.auto_generate_description' => 'required|in:true,false',
            'settings.seo.description_length' => 'required|integer|min:100|max:250',
            'settings.seo.auto_schema_markup' => 'required|in:true,false',
            'settings.short_link.enabled' => 'required|in:true,false',
            'settings.short_link.prefix' => 'required|string|alpha|min:1|max:10',
            'settings.short_link.code_length' => 'required|integer|min:4|max:10',
        ];
    }

    protected $validationAttributes = [
        'settings.general.posts_per_page' => 'تعداد پست در هر صفحه',
        'settings.general.default_theme_key' => 'قالب پیش‌فرض وبلاگ',
        'settings.crm.theme_key' => 'قالب پنل کاربری CRM',
        'settings.crm.enable_overrides' => 'وضعیت بازنویسی ویوهای CRM',
        'settings.short_link.prefix' => 'پیشوند لینک کوتاه',
    ];

    public function mount(): void
    {
        $this->authorize('content.settings.manage');

        foreach (ContentSetting::DEFAULTS as $section => $keys) {
            foreach ($keys as $key => $default) {
                $this->settings[$section][$key] = ContentSetting::getValue("{$section}.{$key}", $default);
            }
        }
    }

    public function save(): void
    {
        $this->validate();

        $originalPrefix = ContentSetting::getValue('short_link.prefix', 's');
        $newPrefix = $this->settings['short_link']['prefix'];

        foreach ($this->settings as $section => $keys) {
            foreach ($keys as $key => $value) {
                // Ensure value is converted to string for storage compatibility
                $stringValue = is_bool($value) ? ($value ? 'true' : 'false') : (string)$value;
                ContentSetting::setValue("{$section}.{$key}", $stringValue);
            }
        }

        session()->flash('success', 'تنظیمات با موفقیت ذخیره شد.');

        if ($originalPrefix !== $newPrefix) {
            session()->flash('warning', 'توجه: پیشوند لینک کوتاه تغییر کرد. جهت اعمال صحیح، لطفاً کش بهینه‌سازی سیستم را پاک کنید.');
        }
    }

    public function render()
    {
        $themeResolver = app(ThemeResolver::class);
        $availableThemes = $themeResolver->getAvailableThemes();
        $selectedCrmTheme = $this->settings['crm']['theme_key'] ?? 'default';
        $currentThemeDetails = $availableThemes[$selectedCrmTheme] ?? null;

        return view('contentforge::livewire.admin.content-settings', compact('availableThemes', 'currentThemeDetails'))
            ->layout('layouts.user');
    }
}
