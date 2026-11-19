<?php

declare(strict_types=1);

namespace Modules\ContentForge\App\Services;

use App\Services\ThemeManager;
use Illuminate\Support\Facades\File;
use Modules\ContentForge\App\Models\ContentPost;
use Modules\ContentForge\App\Models\ContentEntity;
use Modules\ContentForge\Entities\ContentSetting;

final class ThemeResolver
{
    public function resolveForPost(ContentPost $post, string $view): string
    {
        $chain = array_filter([
            $post->theme_key,
            $post->category?->theme_key,
            $post->entity?->theme_key,
            $post->entity?->module_source,
            ContentSetting::getValue('general.default_theme_key', 'content'),
            'content',
        ]);

        foreach ($chain as $themeKey) {
            $viewPath = "themes.{$themeKey}.{$view}";
            if (view()->exists($viewPath)) {
                return $viewPath;
            }
        }

        return "contentforge::web.{$view}";
    }

    public function resolveForArchive(ContentEntity $entity, string $view = 'archive'): string
    {
        $chain = array_filter([
            $entity->theme_key,
            $entity->module_source,
            ContentSetting::getValue('general.default_theme_key', 'content'),
            'content',
        ]);

        foreach ($chain as $themeKey) {
            $viewPath = "themes.{$themeKey}.{$view}";
            if (view()->exists($viewPath)) {
                return $viewPath;
            }
        }

        return "contentforge::web.{$view}";
    }

    /**
     * دریافت کلید قالب فعال برای پنل CRM از طریق ThemeManager
     */
    public function resolveCrmThemeKey(): string
    {
        if (app()->bound(ThemeManager::class)) {
            return app(ThemeManager::class)->getActiveCrmTheme();
        }

        return (string)ContentSetting::getValue('crm.theme_key', 'default');
    }

    /**
     * پیدا کردن و رزرو کردن ویوی CRM در زنجیره قالب‌های پنل
     */
    public function resolveForCrm(string $view, ?string $fallback = null): string
    {
        $crmTheme = $this->resolveCrmThemeKey();
        $chain = array_filter([$crmTheme, 'default']);

        foreach ($chain as $themeKey) {
            $viewPath = "themes.{$themeKey}.{$view}";
            if (view()->exists($viewPath)) {
                return $viewPath;
            }
        }

        if ($fallback && view()->exists($fallback)) {
            return $fallback;
        }

        return $view;
    }

    /**
     * دریافت لیست تمام قالب‌های موجود در پوشه resources/views/themes به همراه متادیتا و وضعیت اوررایدها
     */
    public function getAvailableThemes(): array
    {
        $themesPath = resource_path('views/themes');
        if (!File::isDirectory($themesPath)) {
            return [];
        }

        $directories = File::directories($themesPath);
        $themes = [];

        foreach ($directories as $dir) {
            $key = basename($dir);
            $jsonPath = $dir . DIRECTORY_SEPARATOR . 'theme.json';
            $meta = [];

            if (File::exists($jsonPath)) {
                try {
                    $meta = json_decode(File::get($jsonPath), true) ?: [];
                } catch (\Throwable) {
                    $meta = [];
                }
            }

            $overridesDir = $dir . DIRECTORY_SEPARATOR . 'overrides';
            $hasOverrides = File::isDirectory($overridesDir);
            $crmOverridesCount = 0;
            $overriddenFiles = [];

            if ($hasOverrides) {
                $allFiles = File::allFiles($overridesDir);
                foreach ($allFiles as $file) {
                    $rel = str_replace([$overridesDir . DIRECTORY_SEPARATOR, $overridesDir . '/'], '', $file->getPathname());
                    $overriddenFiles[] = str_replace(DIRECTORY_SEPARATOR, '/', $rel);
                    $crmOverridesCount++;
                }
            }

            $hasWebIndex = File::exists($dir . DIRECTORY_SEPARATOR . 'index.blade.php')
                && (($meta['target'] ?? null) !== 'crm');

            $themes[$key] = [
                'key' => $key,
                'name' => $meta['name'] ?? ucfirst($key),
                'description' => $meta['description'] ?? 'پوسته ثبت‌شده در سامانه',
                'version' => $meta['version'] ?? '1.0.0',
                'author' => $meta['author'] ?? 'سیستم',
                'has_overrides' => $hasOverrides,
                'has_web_index' => $hasWebIndex,
                'crm_overrides_count' => $crmOverridesCount,
                'total_overrides' => count($overriddenFiles),
                'overridden_files' => $overriddenFiles,
            ];
        }

        return $themes;
    }

    /**
     * فهرست صفحات کلیدی و پرکاربرد CRM و ماژول‌ها برای کپی و بازنویسی سریع در استودیو تم
     */
    public function getCommonOverrideTargets(): array
    {
        return [
            'هسته CRM' => [
                'layouts.user' => 'اسکلت و لایوت اصلی پنل کاربری (layouts.user)',
                'user.dashboard' => 'صفحه اصلی داشبورد کاربری (user.dashboard)',
                'user.partials.sidebar' => 'سایدبار منوهای اصلی پنل (user.partials.sidebar)',
                'user.partials.topbar' => 'تاپ‌بار و هدر بالای پنل (user.partials.topbar)',
            ],
            'مدیریت کاربران و نقش‌ها' => [
                'admin.users.index' => 'لیست کاربران سیستم (admin.users.index)',
                'admin.users.edit' => 'فرم ایجاد و ویرایش کاربر (admin.users.edit)',
                'admin.roles.index' => 'لیست نقش‌ها و دسترسی‌ها (admin.roles.index)',
            ],
            'ماژول‌ها' => [
                'resellerhub::user.dashboard' => 'نمایندگی: داشبورد نمایندگی (resellerhub::user.dashboard)',
                'accounting::index' => 'حسابداری: صفحه اصلی حسابداری (accounting::index)',
                'clients::index' => 'مشتریان: لیست مشتریان (clients::index)',
                'clients::create' => 'مشتریان: فرم ثبت مشتری جدید (clients::create)',
                'sales::cockpit' => 'فروش: کاک‌پیت فروش (sales::cockpit)',
                'smartbot::page.chat' => 'هوش مصنوعی: صفحه چت هوشمند (smartbot::page.chat)',
                'contentforge::user.dashboard' => 'محتوا: داشبورد محتوا (contentforge::user.dashboard)',
            ],
        ];
    }
}
