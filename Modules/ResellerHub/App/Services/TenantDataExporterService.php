<?php

declare(strict_types=1);

namespace Modules\ResellerHub\App\Services;

use App\Models\Tenant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TenantDataExporterService
{
    /**
     * Complete list of tables with tenant_id grouped by business module.
     */
    protected array $moduleTableMap = [
        'clients' => [
            'title' => 'مشتریان و پرونده‌ها',
            'tables' => [
                'clients' => 'مشتریان و بیماران',
                'client_addresses' => 'آدرس‌های مشتریان',
                'client_forms' => 'فرم‌ساز و فیلدهای سفارشی پرونده',
                'client_settings' => 'تنظیمات ماژول مشتریان',
                'client_statuses' => 'وضعیت‌های مشتریان',
            ],
        ],
        'services' => [
            'title' => 'سرویس‌ها و فاکتورها',
            'tables' => [
                'service_invoices' => 'فاکتورهای خدمات',
                'service_invoice_items' => 'اقلام فاکتورها',
                'service_invoice_payments' => 'پرداخت‌های فاکتورها',
                'service_orders' => 'سفارش‌های خدمات',
                'services' => 'تعریف خدمات',
                'service_categories' => 'دسته‌بندی خدمات',
                'service_packages' => 'پکیج‌های خدماتی',
            ],
        ],
        'booking' => [
            'title' => 'نوبت‌دهی و رزرو',
            'tables' => [
                'appointments' => 'نوبت‌های ثبت‌شده',
                'booking_services' => 'خدمات نوبت‌دهی',
                'booking_categories' => 'دسته‌بندی نوبت‌ها',
                'booking_payments' => 'پرداخت‌های نوبت‌دهی',
                'booking_settings' => 'تنظیمات نوبت‌دهی',
                'booking_availability_rules' => 'قوانین دسترسی زمانی',
                'booking_availability_exceptions' => 'استثنائات زمانی',
                'booking_waitlists' => 'لیست انتظار',
                'booking_service_providers' => 'متخصصین و ارائه‌دهندگان',
                'booking_forms' => 'فرم‌های نوبت‌دهی',
            ],
        ],
        'market' => [
            'title' => 'فروشگاه و انبارداری',
            'tables' => [
                'market_orders' => 'سفارش‌های فروشگاه',
                'market_order_items' => 'اقلام سفارش‌های فروشگاه',
                'market_master_products' => 'محصولات فروشگاه',
                'market_product_variants' => 'تنوع محصولات',
                'market_vendors' => 'تأمین‌کنندگان',
                'market_warehouses' => 'انبارها',
                'market_brands' => 'برندها',
                'market_categories' => 'دسته‌بندی محصولات',
                'checkout_forms' => 'فرم‌های تسویه‌حساب',
            ],
        ],
        'accounting' => [
            'title' => 'حسابداری و مالی',
            'tables' => [
                'accounting_invoices' => 'فاکتورهای حسابداری',
                'accounting_transactions' => 'تراکنش‌های مالی',
                'accounting_cheques' => 'چک‌ها و اسناد',
                'accounting_documents' => 'اسناد دوبل حسابداری',
            ],
        ],
    ];

    /**
     * Runtime cache for verified tables containing tenant_id column.
     */
    private static ?array $verifiedTables = null;

    /**
     * Get verified tables that exist in the database and have a tenant_id column.
     */
    public function getVerifiedTables(): array
    {
        if (self::$verifiedTables !== null) {
            return self::$verifiedTables;
        }

        $verified = [];
        foreach ($this->moduleTableMap as $moduleKey => $moduleInfo) {
            foreach ($moduleInfo['tables'] as $tableName => $tableLabel) {
                if (Schema::hasTable($tableName) && Schema::hasColumn($tableName, 'tenant_id')) {
                    $verified[$moduleKey][$tableName] = $tableLabel;
                }
            }
        }

        return self::$verifiedTables = $verified;
    }

    /**
     * Batch load statistics of records per module for multiple tenants in minimal queries.
     */
    public function getStatisticsForMultipleTenants(iterable $tenants): array
    {
        $tenantIds = [];
        foreach ($tenants as $t) {
            $tenantIds[] = is_object($t) ? (int)$t->id : (int)$t;
        }

        $result = [];
        foreach ($tenantIds as $tid) {
            $result[$tid] = [
                'modules' => [],
                'total_records' => 0,
                'tables_count' => 0,
            ];
        }

        if (empty($tenantIds)) {
            return $result;
        }

        $verified = $this->getVerifiedTables();
        $countsByTableAndTenant = [];

        foreach ($verified as $moduleKey => $tables) {
            foreach ($tables as $tableName => $tableLabel) {
                try {
                    $counts = DB::table($tableName)
                        ->select('tenant_id', DB::raw('count(*) as aggregate'))
                        ->whereIn('tenant_id', $tenantIds)
                        ->groupBy('tenant_id')
                        ->pluck('aggregate', 'tenant_id')
                        ->all();

                    foreach ($tenantIds as $tid) {
                        $countsByTableAndTenant[$tid][$moduleKey][$tableName] = (int)($counts[$tid] ?? 0);
                    }
                } catch (\Throwable) {
                    foreach ($tenantIds as $tid) {
                        $countsByTableAndTenant[$tid][$moduleKey][$tableName] = 0;
                    }
                }
            }
        }

        foreach ($tenantIds as $tid) {
            $totalRecords = 0;
            $allTablesList = [];
            $moduleStats = [];

            foreach ($verified as $moduleKey => $tables) {
                $mTotal = 0;
                $mTables = [];
                foreach ($tables as $tableName => $tableLabel) {
                    $c = $countsByTableAndTenant[$tid][$moduleKey][$tableName] ?? 0;
                    $mTables[] = [
                        'table' => $tableName,
                        'label' => $tableLabel,
                        'count' => $c,
                    ];
                    $mTotal += $c;
                    $totalRecords += $c;
                    $allTablesList[] = $tableName;
                }
                $moduleStats[$moduleKey] = [
                    'title' => $this->moduleTableMap[$moduleKey]['title'] ?? $moduleKey,
                    'tables' => $mTables,
                    'module_total' => $mTotal,
                ];
            }

            $result[$tid] = [
                'modules' => $moduleStats,
                'total_records' => $totalRecords,
                'tables_count' => count($allTablesList),
            ];
        }

        return $result;
    }

    /**
     * Get statistics of records per module for a specific tenant.
     */
    public function getTenantStatistics(Tenant $tenant): array
    {
        $batch = $this->getStatisticsForMultipleTenants([$tenant]);
        return $batch[$tenant->id] ?? [
            'modules' => [],
            'total_records' => 0,
            'tables_count' => 0,
        ];
    }

    /**
     * Generate a standalone, ready-to-import SQL dump file for a specific tenant.
     */
    public function streamSqlDump(Tenant $tenant, $stream): void
    {
        $subdomain = $tenant->subdomain ?? 'tenant';
        $timestamp = now()->toDateTimeString();
        $appName = config('app.name', 'CRM System');

        fwrite($stream, "-- =================================================================\n");
        fwrite($stream, "-- {$appName} - Multi-Tenant SQL Data Export\n");
        fwrite($stream, "-- Tenant: {$tenant->name} (Subdomain: {$subdomain}, ID: {$tenant->id})\n");
        fwrite($stream, "-- Export Date: {$timestamp}\n");
        fwrite($stream, "-- Format: MySQL 5.7+ / MySQL 8.0+ / MariaDB\n");
        fwrite($stream, "-- =================================================================\n\n");
        fwrite($stream, "SET NAMES utf8mb4;\n");
        fwrite($stream, "SET FOREIGN_KEY_CHECKS = 0;\n");
        fwrite($stream, "SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';\n");
        fwrite($stream, "SET AUTOCOMMIT = 0;\n");
        fwrite($stream, "START TRANSACTION;\n\n");

        foreach ($this->moduleTableMap as $moduleKey => $moduleInfo) {
            $hasModuleData = false;

            $moduleHeaderPrinted = false;

            foreach ($moduleInfo['tables'] as $tableName => $tableLabel) {
                if (!Schema::hasTable($tableName) || !Schema::hasColumn($tableName, 'tenant_id')) {
                    continue;
                }

                $totalCount = DB::table($tableName)->where('tenant_id', $tenant->id)->count();
                if ($totalCount === 0) {
                    continue;
                }

                if (!$moduleHeaderPrinted) {
                    fwrite($stream, "-- -----------------------------------------------------------------\n");
                    fwrite($stream, "-- Module: {$moduleInfo['title']} ({$moduleKey})\n");
                    fwrite($stream, "-- -----------------------------------------------------------------\n");
                    $moduleHeaderPrinted = true;
                }

                fwrite($stream, "\n-- Table: `{$tableName}` ({$tableLabel}) - Records: {$totalCount}\n");

                $createRow = DB::select("SHOW CREATE TABLE `{$tableName}`");
                if (!empty($createRow)) {
                    $createStmt = $createRow[0]->{'Create Table'} ?? '';
                    if ($createStmt) {
                        $createStmt = str_replace("CREATE TABLE", "CREATE TABLE IF NOT EXISTS", $createStmt);
                        fwrite($stream, $createStmt . ";\n\n");
                    }
                }

                // Instead of chunk() which requires id, we can use cursor() which is faster and doesn't care about keys!
                $cursor = DB::table($tableName)->where('tenant_id', $tenant->id)->cursor();
                $columns = [];
                $colsList = '';

                $batch = [];
                foreach ($cursor as $row) {
                    if (empty($columns)) {
                        $columns = array_keys((array)$row);
                        $escapedCols = array_map(fn($c) => "`{$c}`", $columns);
                        $colsList = implode(', ', $escapedCols);
                    }

                    $rowValues = [];
                    $pdo = DB::connection()->getPdo();
                    foreach ($columns as $col) {
                        $val = $row->{$col};
                        if ($val === null) {
                            $rowValues[] = 'NULL';
                        } elseif (is_int($val) || is_float($val)) {
                            $rowValues[] = (string)$val;
                        } else {
                            // Any string (including phone numbers, national IDs, card numbers with leading zeros) gets properly quoted
                            $rowValues[] = $pdo->quote((string)$val);
                        }
                    }
                    $batch[] = "(" . implode(', ', $rowValues) . ")";

                    if (count($batch) >= 100) {
                        fwrite($stream, "INSERT INTO `{$tableName}` ({$colsList}) VALUES\n" . implode(",\n", $batch) . ";\n");
                        $batch = [];
                    }
                }
                if (!empty($batch)) {
                    fwrite($stream, "INSERT INTO `{$tableName}` ({$colsList}) VALUES\n" . implode(",\n", $batch) . ";\n");
                }
            }
        }

        fwrite($stream, "\nCOMMIT;\n");
        fwrite($stream, "SET FOREIGN_KEY_CHECKS = 1;\n");
    }

    public function streamJsonExport(Tenant $tenant, $stream): void
    {
        fwrite($stream, '{"tenant": ' . json_encode([
                'id' => $tenant->id,
                'name' => $tenant->name,
                'subdomain' => $tenant->subdomain,
                'custom_domain' => $tenant->custom_domain,
                'created_at' => (string)$tenant->created_at,
            ]) . ', "exported_at": "' . now()->toIso8601String() . '", "modules": {');

        $firstModule = true;
        foreach ($this->moduleTableMap as $moduleKey => $moduleInfo) {
            $hasData = false;
            foreach ($moduleInfo['tables'] as $tableName => $tableLabel) {
                if (Schema::hasTable($tableName) && Schema::hasColumn($tableName, 'tenant_id')) {
                    if (DB::table($tableName)->where('tenant_id', $tenant->id)->exists()) {
                        $hasData = true;
                        break;
                    }
                }
            }
            if (!$hasData) continue;

            if (!$firstModule) fwrite($stream, ',');
            $firstModule = false;

            fwrite($stream, '"' . $moduleKey . '": {"title": ' . json_encode($moduleInfo['title']) . ', "tables": {');

            $firstTable = true;
            foreach ($moduleInfo['tables'] as $tableName => $tableLabel) {
                if (!Schema::hasTable($tableName) || !Schema::hasColumn($tableName, 'tenant_id')) {
                    continue;
                }

                $totalCount = DB::table($tableName)->where('tenant_id', $tenant->id)->count();
                if ($totalCount === 0) {
                    continue;
                }

                if (!$firstTable) fwrite($stream, ',');
                $firstTable = false;

                fwrite($stream, '"' . $tableName . '": {"label": ' . json_encode($tableLabel) . ', "count": ' . $totalCount . ', "rows": [');

                $cursor = DB::table($tableName)->where('tenant_id', $tenant->id)->cursor();

                $firstRow = true;
                foreach ($cursor as $row) {
                    if (!$firstRow) fwrite($stream, ',');
                    $firstRow = false;
                    fwrite($stream, json_encode($row, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
                }

                fwrite($stream, ']}');
            }

            fwrite($stream, '}}');
        }

        fwrite($stream, '}}');
    }
}
