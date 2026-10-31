<?php

namespace Modules\DomainManager\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Modules\DomainManager\Entities\DomainReseller;
use Modules\DomainManager\Entities\DomainRecord;
use Modules\DomainManager\Entities\DomainLog;
use Modules\DomainManager\Services\ResellerWorldClient;
use Throwable;

class SyncDomainsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'domainmanager:sync {--reseller= : شناسه کارگزار خاص جهت همگام‌سازی}';

    /**
     * The console command aliases.
     *
     * @var array
     */
    protected $aliases = [
        'domain:sync',
    ];

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'همگام‌سازی خودکار و روزانه اطلاعات دامنه‌ها و موجودی کیف پول از کارگزاری‌های فعال';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('[' . now()->toDateTimeString() . '] آغاز فرآیند همگام‌سازی خودکار دامنه‌ها (Cron Daily Sync)...');

        $resellerId = $this->option('reseller');
        $query = DomainReseller::where('is_active', true);

        if ($resellerId) {
            $query->where('id', $resellerId);
        }

        $resellers = $query->get();

        if ($resellers->isEmpty()) {
            $this->warn('هیچ کارگزار فعالی برای همگام‌سازی یافت نشد.');
            return self::SUCCESS;
        }

        $totalSyncedAll = 0;
        $totalNewAll = 0;

        foreach ($resellers as $reseller) {
            $this->line("در حال همگام‌سازی کارگزار: {$reseller->name} (شناسه: {$reseller->id})...");

            try {
                $client = new ResellerWorldClient($reseller->auth_userid, $reseller->decrypted_api_key);

                // 1. Sync Wallet Balance
                try {
                    $balanceInfo = $client->getBalance();
                    if (isset($balanceInfo['sellingbalance'])) {
                        $reseller->update(['balance' => (float)$balanceInfo['sellingbalance']]);
                        $this->line("  موجودی کیف پول بروزرسانی شد: {$balanceInfo['sellingbalance']} USD");
                    }
                } catch (Throwable $e) {
                    $this->warn("  خطا در دریافت موجودی: " . $e->getMessage());
                }

                // 2. Paginate & Sync All Domains
                $syncedCount = 0;
                $newCount = 0;
                $page = 1;

                do {
                    $searchResult = $client->searchDomains($page, 20);

                    if (isset($searchResult['status']) && $searchResult['status'] === 'error') {
                        $errMsg = $searchResult['message'] ?? 'خطای نامشخص از وب‌سرویس کارگزار';
                        $this->error("  خطا در صفحه {$page}: {$errMsg}");
                        DomainLog::log(
                            'sync_all',
                            "خطای خودکار کرون در همگام‌سازی کارگزار {$reseller->name}: {$errMsg}",
                            null,
                            $reseller->id,
                            'failed'
                        );
                        break;
                    }

                    if (!is_array($searchResult) || !isset($searchResult['data']) || empty($searchResult['data'])) {
                        break;
                    }

                    foreach ($searchResult['data'] as $orderData) {
                        $domainName = $orderData['domain'] ?? null;
                        if (empty($domainName)) {
                            continue;
                        }

                        $orderId = $orderData['id'] ?? null;
                        $expiryDate = $orderData['expire_at'] ?? null;
                        $creationDate = $orderData['created_at'] ?? null;

                        // Calculate status
                        $status = 'active';
                        if ($expiryDate && strtotime($expiryDate) < time()) {
                            $status = 'expired';
                        }

                        $domainRecord = DomainRecord::where('domain_reseller_id', $reseller->id)
                            ->where('domain_name', $domainName)
                            ->first();

                        if ($domainRecord) {
                            $domainRecord->update([
                                'order_id' => $orderId,
                                'status' => $status,
                                'expiry_date' => $expiryDate,
                                'creation_date' => $creationDate,
                                'synced_at' => now()
                            ]);
                            $syncedCount++;
                        } else {
                            DomainRecord::create([
                                'domain_reseller_id' => $reseller->id,
                                'order_id' => $orderId,
                                'domain_name' => $domainName,
                                'status' => $status,
                                'expiry_date' => $expiryDate,
                                'creation_date' => $creationDate,
                                'synced_at' => now()
                            ]);
                            $newCount++;
                        }
                    }

                    if (count($searchResult['data']) < 20) {
                        break;
                    }

                    $page++;
                } while ($page <= 30);

                $totalSyncedAll += $syncedCount;
                $totalNewAll += $newCount;

                DomainLog::log(
                    'sync_all',
                    "همگام‌سازی خودکار روزانه کرون (Cron Job) با موفقیت انجام شد. بروزرسانی: {$syncedCount} دامنه، دامنه‌های جدید: {$newCount} دامنه.",
                    null,
                    $reseller->id,
                    'success'
                );

                $this->info("  همگام‌سازی کارگزار {$reseller->name} پایان یافت (بروزرسانی: {$syncedCount}، جدید: {$newCount}).");

            } catch (Throwable $e) {
                Log::error("[DomainManager] Cron daily sync error for reseller {$reseller->id}: " . $e->getMessage());
                $this->error("  خطای سیستمی برای کارگزار {$reseller->name}: " . $e->getMessage());
                DomainLog::log(
                    'sync_all',
                    "استثنای سیستمی در اجرای کرون جاب روزانه کارگزار {$reseller->name}: " . $e->getMessage(),
                    null,
                    $reseller->id,
                    'failed'
                );
            }
        }

        $this->info('[' . now()->toDateTimeString() . "] همگام‌سازی خودکار کرون با موفقیت پایان یافت. مجموع بروزرسانی: {$totalSyncedAll} دامنه، مجموع دامنه‌های جدید: {$totalNewAll} دامنه.");
        return self::SUCCESS;
    }
}
