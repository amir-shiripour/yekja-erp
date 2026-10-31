<?php

namespace Modules\ResellerHub\App\Services\Support;

use App\Models\Module;
use App\Services\Modules\ModuleMenuService;
use App\Support\PermissionCatalog;
use App\Support\WidgetRegistry;
use Illuminate\Support\Facades\Cache;
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

        $cacheKey = "resellerhub_module_title_{$slugLower}";

        return Cache::rememberForever($cacheKey, function () use ($slugLower) {
            // 1. Resolve directly from PermissionCatalog (Guaranteed STRICTLY PERSIAN, zero English)
            $catalogTitle = $this->resolveFromPermissionCatalog($slugLower);
            if (!empty($catalogTitle)) {
                return $catalogTitle;
            }

            $title = ucfirst($slugLower);

            if (class_exists(ModuleMenuService::class)) {
                $menuService = app(ModuleMenuService::class);
                $resolved = $menuService->resolveModuleGroupTitle($slugLower, $title);
                if (!empty($resolved) && $resolved !== $title && $resolved !== $slugLower) {
                    return $this->cleanPersianTitle($resolved, true);
                }
            }

            $candidates = [ucfirst($slugLower), strtoupper($slugLower), $slugLower];
            foreach ($candidates as $cand) {
                $menuPath = base_path("Modules/{$cand}/resources/menu.php");
                if (file_exists($menuPath)) {
                    try {
                        $items = include $menuPath;
                        if (is_array($items) && !empty($items[0]['title'])) {
                            return $this->cleanPersianTitle($items[0]['title'], true);
                        }
                    } catch (Throwable $e) {
                        // ignore
                    }
                }
            }

            $dbMod = Module::whereRaw('LOWER(slug) = ?', [$slugLower])
                ->orWhereRaw('LOWER(name) = ?', [$slugLower])
                ->first();

            if ($dbMod) {
                if (class_exists(ModuleMenuService::class)) {
                    $menuService = app(ModuleMenuService::class);
                    $resolved = $menuService->resolveModuleGroupTitle($dbMod->slug, $dbMod->name);
                    if (!empty($resolved) && $resolved !== ucfirst($slugLower)) {
                        return $this->cleanPersianTitle($resolved, true);
                    }
                }
                if (!empty($dbMod->title)) {
                    return $this->cleanPersianTitle($dbMod->title, true);
                }
            }

            foreach (["{$slugLower}::menu.group_title", "{$slugLower}::general.title", "menu.{$slugLower}"] as $transKey) {
                $t = trans($transKey);
                if ($t !== $transKey && !empty($t)) {
                    return $this->cleanPersianTitle($t, true);
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
        if (!class_exists(PermissionCatalog::class)) {
            return null;
        }

        $slugLower = strtolower(trim($slug));
        if ($slugLower === '') {
            return null;
        }

        $normalized = str_replace(['-', ' '], '_', $slugLower);
        $cleanKey = str_replace(['-', '_', ' '], '', $slugLower);

        // 1. Check PermissionCatalog::groups()
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

        // Prefix match on groups (e.g. resellerhub_admin matches resellerhub)
        foreach ($groups as $key => $data) {
            if (str_starts_with($key, $normalized . '_') || str_starts_with($normalized, $key . '_')) {
                $clean = $this->cleanPersianTitle($data['title'], true);
                if (!empty($clean)) {
                    return $clean;
                }
            }
        }

        // 2. Check PermissionCatalog::translate()
        $translated = PermissionCatalog::translate($slug);
        if ($translated !== $slug && !empty($translated) && preg_match('/[\x{0600}-\x{06FF}]/u', $translated)) {
            $clean = $this->cleanPersianTitle($translated, false);
            if (!empty($clean)) {
                return $clean;
            }
        }

        // 3. Check WidgetRegistry (e.g., calendar_widget -> تقویم و رویدادها)
        if (class_exists(WidgetRegistry::class)) {
            $widget = WidgetRegistry::get($slugLower) ?: WidgetRegistry::get($normalized);
            if ($widget && !empty($widget['label'])) {
                return $this->cleanPersianTitle($widget['label'], false);
            }
        }

        // 4. Core system aliases (Pure Persian ONLY, zero English)
        $coreAliases = [
            'crm' => 'سامانه ارتباط با مشتری',
            'core' => 'سامانه ارتباط با مشتری',
            'calendar' => 'تقویم و رویدادها',
            'events' => 'رویدادها',
            'chat' => 'چت و گفتگوی آنلاین',
            'smartbot' => 'دستیار هوشمند',
            'contentforge' => 'تولید محتوا',
            'admin' => 'مدیریت سیستم',
        ];

        return $coreAliases[$normalized] ?? null;
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
        Cache::forget("resellerhub_module_title_{$slugLower}");
    }
}
