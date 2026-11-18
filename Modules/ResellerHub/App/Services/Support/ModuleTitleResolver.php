<?php

namespace Modules\ResellerHub\App\Services\Support;

use App\Models\Module;
use App\Services\Modules\ModuleMenuService;
use App\Support\PermissionCatalog;
use App\Support\WidgetRegistry;
use Illuminate\Support\Facades\Cache;
use Modules\ResellerHub\App\Enums\QuotaKey;
use Throwable;

class ModuleTitleResolver
{
    /**
     * Resolve the title of a module efficiently using Cache to prevent Disk I/O bottlenecks.
     */
    public function resolve(string $slug): string
    {
        $slugLower = strtolower(trim($slug));
        if ($slugLower === '') {
            return '';
        }

        $cleanKey = str_replace(['-', '_', ' '], '', $slugLower);
        $cacheKey = "resellerhub_module_title_{$cleanKey}";

        return Cache::rememberForever($cacheKey, function () use ($slugLower, $cleanKey) {
            // 1. Resolve directly from PermissionCatalog (Guaranteed STRICTLY PERSIAN, zero English)
            $catalogTitle = $this->resolveFromPermissionCatalog($slugLower);
            if (!empty($catalogTitle)) {
                return $catalogTitle;
            }

            $title = ucfirst($slugLower);

            if (class_exists(ModuleMenuService::class)) {
                $menuService = app(ModuleMenuService::class);
                $resolved = $menuService->resolveModuleGroupTitle($slugLower, $title);
                if (!empty($resolved) && $resolved !== $title && $resolved !== $slugLower && preg_match('/[\x{0600}-\x{06FF}]/u', $resolved)) {
                    return $this->cleanPersianTitle($resolved, true);
                }
            }

            // 2. Check Module translation keys (Defined by the module itself, e.g. lang/fa/menu.php)
            foreach ([
                "{$slugLower}::menu.group_title", "{$slugLower}::general.title", "menu.{$slugLower}",
                "{$cleanKey}::menu.group_title", "{$cleanKey}::general.title", "menu.{$cleanKey}"
            ] as $transKey) {
                $t = trans($transKey);
                if ($t !== $transKey && !empty($t) && preg_match('/[\x{0600}-\x{06FF}]/u', $t)) {
                    return $this->cleanPersianTitle($t, true);
                }
            }

            // 3. Check Database Module record (if title column is set in Persian)
            $dbMod = Module::whereRaw('LOWER(slug) = ?', [$slugLower])
                ->orWhereRaw('LOWER(name) = ?', [$slugLower])
                ->orWhereRaw('LOWER(REPLACE(REPLACE(slug, "_", ""), "-", "")) = ?', [$cleanKey])
                ->orWhereRaw('LOWER(REPLACE(REPLACE(name, "_", ""), "-", "")) = ?', [$cleanKey])
                ->first();

            if ($dbMod) {
                if (!empty($dbMod->title) && preg_match('/[\x{0600}-\x{06FF}]/u', $dbMod->title)) {
                    return $this->cleanPersianTitle($dbMod->title, true);
                }
                if (class_exists(ModuleMenuService::class)) {
                    $menuService = app(ModuleMenuService::class);
                    $resolved = $menuService->resolveModuleGroupTitle($dbMod->slug, $dbMod->name);
                    if (!empty($resolved) && $resolved !== ucfirst($slugLower) && preg_match('/[\x{0600}-\x{06FF}]/u', $resolved)) {
                        return $this->cleanPersianTitle($resolved, true);
                    }
                }
            }

            // 4. Check Module menu files (Fallback to first menu item title if no group title defined)
            $candidates = [ucfirst($slugLower), strtoupper($slugLower), $slugLower, ucfirst($cleanKey), $cleanKey];
            foreach ($candidates as $cand) {
                $menuPath = base_path("Modules/{$cand}/resources/menu.php");
                if (file_exists($menuPath)) {
                    try {
                        $items = include $menuPath;
                        if (is_array($items) && !empty($items[0]['title']) && preg_match('/[\x{0600}-\x{06FF}]/u', $items[0]['title'])) {
                            return $this->cleanPersianTitle($items[0]['title'], true);
                        }
                    } catch (Throwable $e) {
                        // ignore
                    }
                }
            }

            return $title;
        });
    }

    /**
     * Dynamically resolve Persian title by querying PermissionCatalog without modifying PermissionCatalog.
     * Guaranteed to extract STRICTLY Persian text without English parentheses or Latin characters.
     */
    public function resolveFromPermissionCatalog(string $slug): ?string
    {
        $slugLower = strtolower(trim($slug));
        if ($slugLower === '') {
            return null;
        }

        $normalized = str_replace(['-', ' '], '_', $slugLower);
        $cleanKey = str_replace(['-', '_', ' '], '', $slugLower);

        // 1. Check PermissionCatalog::groups()
        if (class_exists(PermissionCatalog::class)) {
            $groups = PermissionCatalog::groups();

            // Direct key match
            if (isset($groups[$normalized])) {
                $clean = $this->cleanPersianTitle($groups[$normalized]['title'], true);
                if (!empty($clean)) {
                    return $clean;
                }
            }

            // Clean key match (e.g. clientcalls vs client_calls, smsreseller vs sms_reseller)
            foreach ($groups as $key => $data) {
                $keyClean = str_replace(['-', '_', ' '], '', $key);
                if ($keyClean === $cleanKey) {
                    $clean = $this->cleanPersianTitle($data['title'], true);
                    if (!empty($clean)) {
                        return $clean;
                    }
                }
            }

            // Matchers in groups (e.g. client-calls. matches clientcalls)
            foreach ($groups as $data) {
                foreach ($data['matchers'] as $m) {
                    $mClean = str_replace(['.', '-', '_'], '', $m);
                    if ($mClean === $cleanKey || $mClean === $slugLower) {
                        $clean = $this->cleanPersianTitle($data['title'], true);
                        if (!empty($clean)) {
                            return $clean;
                        }
                    }
                }
            }

            // Prefix match on groups (e.g. reseller_admin matches reseller)
            foreach ($groups as $key => $data) {
                if (str_starts_with($key, $normalized . '_') || str_starts_with($normalized, $key . '_')) {
                    $clean = $this->cleanPersianTitle($data['title'], true);
                    if (!empty($clean)) {
                        return $clean;
                    }
                }
            }
        }

        // 2. Check QuotaKey if applicable (e.g. appointments, invoices, warehouses, products)
        if (class_exists(QuotaKey::class)) {
            $qEnum = QuotaKey::tryFrom($slugLower)
                ?? QuotaKey::tryFrom($normalized)
                ?? QuotaKey::tryFrom($cleanKey);
            if ($qEnum) {
                return $qEnum->shortTitle();
            }
        }

        // 2. Check PermissionCatalog::translate()
        foreach ([$slug, $slugLower, $normalized, $cleanKey] as $attempt) {
            $translated = PermissionCatalog::translate($attempt);
            if ($translated !== $attempt && !empty($translated) && preg_match('/[\x{0600}-\x{06FF}]/u', $translated)) {
                $clean = $this->cleanPersianTitle($translated, false);
                if (!empty($clean)) {
                    return $clean;
                }
            }
        }

        // 3. Check WidgetRegistry
        if (class_exists(WidgetRegistry::class)) {
            $widget = WidgetRegistry::get($slugLower) ?: (WidgetRegistry::get($normalized) ?: WidgetRegistry::get($cleanKey));
            if ($widget && !empty($widget['label'])) {
                return $this->cleanPersianTitle($widget['label'], false);
            }
        }

        // 5. Non-module core system aliases (Virtual entities that are not standalone modules in Modules/)
        $coreAliases = [
            'crm' => 'سامانه ارتباط با مشتری',
            'core' => 'سامانه ارتباط با مشتری',
            'admin' => 'مدیریت سیستم',
            'calendar' => 'تقویم و رویدادها',
            'events' => 'رویدادها',
            'chat' => 'چت و گفتگوی آنلاین',
        ];

        return $coreAliases[$cleanKey] ?? $coreAliases[$normalized] ?? null;
    }

    /**
     * Clean and return STRICTLY Persian text from a raw title.
     * Strips English text in parentheses, colons (subgroup details), Latin characters, etc.
     */
    public function cleanPersianTitle(string $text, bool $moduleOnly = true): string
    {
        // 1. Remove anything in parentheses (e.g. (FollowUps), (SMS), (CRM), (Projects), (عملیات اصلی))
        $text = preg_replace('/\s*\([^)]*\)\s*/u', ' ', $text);

        // 2. If moduleOnly is true and there is a colon (e.g. "نمایندگی فروش: پیشخوان و تنظیمات"), take the prefix
        if ($moduleOnly && str_contains($text, ':')) {
            $parts = explode(':', $text);
            $text = $parts[0];
        }

        // 3. Remove all English/Latin characters and punctuation
        $text = preg_replace('/[a-zA-Z]/u', '', $text);
        $text = preg_replace('/[_\-]+/u', ' ', $text);

        // 4. Normalize whitespace
        return trim(preg_replace('/\s+/u', ' ', $text));
    }

    /**
     * Invalidate the cached title for a module slug.
     */
    public function invalidate(string $slug): void
    {
        $slugLower = strtolower(trim($slug));
        $cleanKey = str_replace(['-', '_', ' '], '', $slugLower);
        Cache::forget("resellerhub_module_title_{$slugLower}");
        Cache::forget("resellerhub_module_title_{$cleanKey}");
    }
}
