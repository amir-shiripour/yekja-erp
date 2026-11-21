<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\File;
use Modules\ContentForge\Entities\ContentSetting;
use Throwable;

final class ViewOverrideResolver
{
    public function __construct(private ThemeManager $themeManager)
    {
    }


    public function isCrmView(string $viewName): bool
    {
        if (str_starts_with($viewName, 'user.') || str_starts_with($viewName, 'admin.') || str_starts_with($viewName, 'layouts.user') || str_starts_with($viewName, 'layouts.auth')) {
            return true;
        }

        if (str_contains($viewName, '::user.') || str_contains($viewName, '::livewire.admin.') || str_contains($viewName, '::admin.')) {
            return true;
        }

        try {
            $request = request();
            if ($request && ($request->is('user*') || $request->is('*/user*') || $request->is('reseller*'))) {
                return true;
            }
        } catch (Throwable) {
        }

        return false;
    }

    /**
     * بررسی می‌کند که آیا فایل override اختصاصی برای view درخواستی در تم‌های فعال وجود دارد یا خیر.
     * پشتیبانی کامل از تمامی ۲۴ ماژول سیستم و ویوهای هسته CRM.
     *
     * ورودی: 'smartbot::page.chat' یا 'accounting::invoices.index' یا 'user.dashboard' یا 'layouts.user'
     * خروجی: مسیر فایل جایگزین فیزیکی یا null
     */
    public function findOverridePath(string $viewName): ?string
    {
        if (str_contains($viewName, '::')) {
            [$namespace, $relativeView] = explode('::', $viewName, 2);
            $relativePath = $namespace . '/' . str_replace('.', '/', $relativeView);
        } else {
            $relativePath = str_replace('.', '/', $viewName);
        }

        if ($this->themeManager->isCrmOverrideEnabled()) {
            $crmTheme = $this->themeManager->getActiveCrmTheme();
            if ($crmTheme) {
                $crmOverridePath = resource_path("views/themes/{$crmTheme}/overrides/{$relativePath}.blade.php");
                if (File::exists($crmOverridePath)) {
                    return $crmOverridePath;
                }
            }
        }

        $entity = $this->themeManager->getActiveEntity();
        if ($entity && !empty($entity->theme_key)) {
            $entityTheme = strtolower((string)$entity->theme_key);
            $entityOverridePath = resource_path("views/themes/{$entityTheme}/overrides/{$relativePath}.blade.php");
            if (File::exists($entityOverridePath)) {
                return $entityOverridePath;
            }

            if (!empty($entity->module_source)) {
                $moduleSource = strtolower((string)$entity->module_source);
                $moduleOverridePath = resource_path("views/themes/{$entityTheme}/overrides/{$moduleSource}/{$relativePath}.blade.php");
                if (File::exists($moduleOverridePath)) {
                    return $moduleOverridePath;
                }
            }
        }

        if (class_exists(ContentSetting::class)) {
            try {
                $defaultWebTheme = ContentSetting::getValue('general.default_theme_key');
                if (!empty($defaultWebTheme) && !in_array($defaultWebTheme, ['content', 'default'], true)) {
                    $webTheme = strtolower((string)$defaultWebTheme);
                    $webOverridePath = resource_path("views/themes/{$webTheme}/overrides/{$relativePath}.blade.php");
                    if (File::exists($webOverridePath)) {
                        return $webOverridePath;
                    }
                }
            } catch (Throwable) {
            }
        }

        $defaultOverridePath = resource_path("views/themes/default/overrides/{$relativePath}.blade.php");
        if (File::exists($defaultOverridePath)) {
            return $defaultOverridePath;
        }

        return null;
    }
}
