<?php

declare(strict_types=1);

namespace App\Services;

use App\Tenancy\TenantManager;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Schema;
use Modules\ContentForge\App\Models\ContentEntity;
use Modules\ContentForge\Entities\ContentSetting;
use Modules\Settings\Entities\Setting;
use Nwidart\Modules\Facades\Module;
use Symfony\Component\Process\ExecutableFinder;
use Throwable;

final class ThemeManager
{
    private ?object $activeEntity = null;
    private bool $entityChecked = false;
    private ?string $resolvedNpmBinary = null;


    private function isInstalled(): bool
    {
        return File::exists(storage_path('app/installed.flag'));
    }


    private function isContentForgeActive(): bool
    {
        if (!class_exists(Module::class)) {
            return false;
        }

        try {
            return Module::has('ContentForge')
                && Module::isEnabled('ContentForge');
        } catch (Throwable $e) {
            return false;
        }
    }


    public function getActiveEntity(): ?object
    {
        if ($this->entityChecked) {
            return $this->activeEntity;
        }

        $this->entityChecked = true;

        if (!$this->isInstalled() || !$this->isContentForgeActive()) {
            return null;
        }

        try {
            if (class_exists(ContentEntity::class) && Schema::hasTable('content_entities')) {
                $entityId = Setting::where('key', 'content_entity_id')->value('value');
                if ($entityId) {
                    $this->activeEntity = Cache::remember("active_theme_entity_{$entityId}", 60, function () use ($entityId) {
                        return ContentEntity::where('id', $entityId)
                            ->where('is_active', true)
                            ->first();
                    });
                }

                if (!$this->activeEntity) {
                    $this->activeEntity = Cache::remember("active_theme_entity_default", 60, function () {
                        return ContentEntity::where('is_default', true)
                            ->where('is_active', true)
                            ->first();
                    });
                }
            }
        } catch (Throwable $e) {
            $this->activeEntity = null;
        }

        return $this->activeEntity;
    }


    public function getActiveCrmTheme(): string
    {
        if (class_exists(TenantManager::class)) {
            try {
                $tenant = app(TenantManager::class)->getTenant();
                if (!$tenant && auth()->check() && !empty(auth()->user()->tenant_id)) {
                    $tenant = auth()->user()->tenant;
                }

                if ($tenant) {
                    $tenantTheme = $tenant->branding['crm_theme_key']
                        ?? $tenant->branding['theme_key']
                        ?? $tenant->settings['crm_theme_key']
                        ?? null;

                    if (!empty($tenantTheme)) {
                        return strtolower((string)$tenantTheme);
                    }
                }
            } catch (Throwable) {
            }
        }

        if ($this->isContentForgeActive() && class_exists(ContentSetting::class)) {
            try {
                $crmTheme = ContentSetting::getValue('crm.theme_key');
                if (!empty($crmTheme) && $crmTheme !== 'default') {
                    return strtolower((string)$crmTheme);
                }
            } catch (Throwable) {
            }
        }

        $entity = $this->getActiveEntity();
        if ($entity && !empty($entity->theme_key) && !in_array(strtolower($entity->theme_key), ['content', 'default'], true)) {
            return strtolower((string)$entity->theme_key);
        }

        if ($this->isInstalled()) {
            try {
                $appTheme = Setting::where('key', 'crm_theme')->value('value')
                    ?? Setting::where('key', 'app_theme')->value('value');
                if (!empty($appTheme) && $appTheme !== 'default') {
                    return strtolower((string)$appTheme);
                }
            } catch (Throwable) {
            }
        }

        return 'default';
    }


    public function isCrmOverrideEnabled(): bool
    {
        if (!$this->isContentForgeActive()) {
            return false;
        }

        if (class_exists(ContentSetting::class)) {
            try {
                $enabled = ContentSetting::getValue('crm.enable_overrides', 'true');
                return $enabled === 'true' || $enabled === true || $enabled === '1' || $enabled === 1;
            } catch (Throwable) {
                return true;
            }
        }

        return true;
    }


    public function clearCache(): void
    {
        $this->entityChecked = false;
        $this->activeEntity = null;
        $this->resolvedNpmBinary = null;
        Cache::forget('active_theme_entity_default');
        Cache::forget('active_crm_theme_key');
        Cache::forget('app_theme');
    }

    /**
     * (npm run build)
     *
     * @param bool $runBuild
     * @return array{view_cleared: bool, cache_cleared: bool, build_attempted: bool, build_success: bool, output: string, error: ?string}
     */
    public function syncAndBuildAssets(bool $runBuild = true): array
    {
        $this->clearCache();

        $viewCleared = false;
        $cacheCleared = false;

        try {
            Artisan::call('view:clear');
            $viewCleared = true;
        } catch (Throwable $e) {
            Log::warning('ThemeManager: view:clear failed: ' . $e->getMessage());
        }

        try {
            Artisan::call('cache:clear');
            $cacheCleared = true;
        } catch (Throwable $e) {
            Log::warning('ThemeManager: cache:clear failed: ' . $e->getMessage());
        }

        $buildAttempted = false;
        $buildSuccess = false;
        $output = '';
        $error = null;

        if ($runBuild && class_exists(Process::class)) {
            $buildAttempted = true;
            $npmBinary = $this->resolveNpmBinary();
            $env = $this->getProcessEnvironment();

            try {
                $cmd = [$npmBinary, 'run', 'build'];

                $process = Process::path(base_path())
                    ->env($env)
                    ->timeout(180)
                    ->run($cmd);

                $output = (string) $process->output();
                if ($process->successful()) {
                    $buildSuccess = true;
                    Log::info('ThemeManager: Assets built successfully via npm run build.');
                } else {
                    $error = (string) ($process->errorOutput() ?: $output);
                    Log::warning('ThemeManager: npm run build failed with exit code: ' . $process->exitCode(), [
                        'error' => $error,
                    ]);
                }
            } catch (Throwable $e) {
                $error = $e->getMessage();
                Log::error('ThemeManager: npm run build exception: ' . $e->getMessage());
            }
        }

        return [
            'view_cleared' => $viewCleared,
            'cache_cleared' => $cacheCleared,
            'build_attempted' => $buildAttempted,
            'build_success' => $buildSuccess,
            'output' => $output,
            'error' => $error,
        ];
    }

    public function resolveNpmBinary(): string
    {
        if ($this->resolvedNpmBinary !== null) {
            return $this->resolvedNpmBinary;
        }

        $configured = config('services.npm.binary');
        if (!empty($configured)) {
            return $this->resolvedNpmBinary = (string) $configured;
        }

        $finder = new ExecutableFinder();
        $fallbackDirs = PHP_OS_FAMILY === 'Windows' ? [] : ['/usr/local/bin', '/usr/bin'];
        $binary = $finder->find('npm', null, $fallbackDirs);

        return $this->resolvedNpmBinary = $binary ?: (PHP_OS_FAMILY === 'Windows' ? 'npm.cmd' : 'npm');
    }

    /**
     * آماده‌سازی متغیرهای محیطی برای اجرای مطمئن npm و node در وب سرور
     */
    public function getProcessEnvironment(): array
    {
        $currentPath = (string) (getenv('PATH') ?: getenv('Path') ?: '');
        $separator = PATH_SEPARATOR;
        $npmBinary = $this->resolveNpmBinary();

        // از آنجا که باینری node همواره در همان پوشه باینری npm قرار دارد،
        // قرار دادن dirname($npmBinary) در ابتدای PATH تضمین می‌کند npm به node متصل شود.
        $npmDir = is_file($npmBinary) ? dirname($npmBinary) : null;
        $configuredExtra = config('services.npm.extra_paths', []);

        $paths = array_filter(array_unique(array_merge(
            $npmDir ? [$npmDir] : [],
            is_array($configuredExtra) ? $configuredExtra : [],
            explode($separator, $currentPath),
            PHP_OS_FAMILY === 'Windows' ? [] : ['/usr/local/bin', '/usr/bin']
        )));

        $storageCache = storage_path('framework/cache/npm');
        if (!File::isDirectory($storageCache)) {
            @File::makeDirectory($storageCache, 0755, true);
        }

        return [
            'PATH' => implode($separator, $paths),
            'HOME' => getenv('HOME') ?: storage_path('framework/cache'),
            'npm_config_cache' => $storageCache,
            'NODE_OPTIONS' => '--max-old-space-size=1024',
        ];
    }

    /**
     * زنجیره اولویت قالب‌ها را برمی‌گرداند.
     */
    public function getThemeChain(): array
    {
        $chain = [];
        $entity = $this->getActiveEntity();

        if ($entity) {
            if (!empty($entity->theme_key)) {
                $chain[] = strtolower($entity->theme_key);
            }
            if (!empty($entity->module_source)) {
                $chain[] = strtolower($entity->module_source);
            }
        }

        // افزودن قالب پیش‌فرض ثبت شده در تنظیمات سیستم
        $appTheme = 'default';

        if ($this->isInstalled()) {
            try {
                $appTheme = Setting::where('key', 'app_theme')->value('value') ?? 'default';
            } catch (Throwable $e) {
                $appTheme = 'default';
            }
        }

        $chain[] = strtolower($appTheme);

        if (strtolower($appTheme) !== 'default') {
            $chain[] = 'default';
        }

        return array_values(array_unique(array_filter($chain)));
    }

    /**
     * یافتن و رزرو کردن یک فایل View در زنجیره اولویت.
     */
    public function resolveView(string $viewName, ?string $fallback = null): string
    {
        $chain = $this->getThemeChain();

        foreach ($chain as $theme) {
            $viewPath = "themes.{$theme}.{$viewName}";
            if (view()->exists($viewPath)) {
                return $viewPath;
            }
        }

        if ($fallback && view()->exists($fallback)) {
            return $fallback;
        }

        return "themes.default.{$viewName}";
    }

    /**
     * بازگرداندن کل زنجیره قالب‌ها به صورت آرایه جهت استفاده در includeFirst
     */
    public function resolveViewChain(string $viewName): array
    {
        $chain = $this->getThemeChain();
        $views = [];

        foreach ($chain as $theme) {
            $views[] = "themes.{$theme}.{$viewName}";
        }

        $views[] = "themes.default.{$viewName}";

        return array_values(array_unique($views));
    }

    /**
     * بررسی می‌کند آیا قالب فعال یک پروژه SPA است یا خیر.
     */
    public function isSpaTheme(): bool
    {
        $entity = $this->getActiveEntity();
        if (!$entity || empty($entity->theme_key)) {
            return false;
        }

        $themePath = resource_path("views/themes/{$entity->theme_key}/theme.json");
        if (File::exists($themePath)) {
            try {
                $config = json_decode(File::get($themePath), true);
                return isset($config['type']) && strtolower($config['type']) === 'spa';
            } catch (Throwable $e) {
                return false;
            }
        }

        return false;
    }

    /**
     * مدیریت خروجی SPA برای رند یا ریدایرکت.
     */
    public function handleSpaResponse()
    {
        $entity = $this->getActiveEntity();
        if (!$entity || empty($entity->theme_key)) {
            abort(404, 'Theme not configured.');
        }

        $themePath = resource_path("views/themes/{$entity->theme_key}/theme.json");
        $config = json_decode(File::get($themePath), true);

        // اگر در محیط توسعه هستیم و پورت توسعه مشخص شده، ریدایرکت ساده انجام بده
        if (config('app.env') === 'local' && isset($config['dev_port'])) {
            return redirect()->away("http://localhost:{$config['dev_port']}");
        }

        // در محیط پروداکشن، فایل بیلد استاتیک را سرو کن
        $entryFile = $config['entry_file'] ?? 'index.html';
        $buildPath = public_path("themes/{$entity->theme_key}/{$entryFile}");

        if (File::exists($buildPath)) {
            return response()->file($buildPath);
        }

        // در صورت عدم بیلد، فایل داخلی را چک کن
        $internalPath = resource_path("views/themes/{$entity->theme_key}/dist/{$entryFile}");
        if (File::exists($internalPath)) {
            return response()->file($internalPath);
        }

        abort(500, "SPA build file not found for theme: {$entity->theme_key}");
    }
}
