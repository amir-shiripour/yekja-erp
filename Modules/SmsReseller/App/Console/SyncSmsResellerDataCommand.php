<?php

namespace Modules\SmsReseller\App\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Modules\SmsReseller\App\Services\SmsResellerSyncService;
use Throwable;

class SyncSmsResellerDataCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'smsreseller:sync
                            {--skip-credits : صرف‌نظر از همگام‌سازی اعتبارات جهت اجرای فوق‌سریع}
                            {--force : اجرای اجباری فرآیند همگام‌سازی}';

    /**
     * The console command aliases.
     *
     * @var array
     */
    protected $aliases = [
        'sms-reseller:sync',
    ];

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'همگام‌سازی خودکار و روزانه اطلاعات تراکنش‌ها، پرداخت‌ها، اعتبارات و خلاصه‌های مالی ریسیلر پیامک (Cron Daily Sync)';

    /**
     * Execute the console command.
     */
    public function handle(SmsResellerSyncService $syncService): int
    {
        @ini_set('memory_limit', '512M');
        @set_time_limit(0);

        $startTime = microtime(true);
        $startDate = now()->toDateTimeString();

        $this->info("[{$startDate}] آغاز فرآیند همگام‌سازی خودکار داده‌های ریسیلر پیامک (Cron Daily Sync)...");
        Log::info('[SmsReseller] Automated daily sync started (Cron Job).');

        try {
            $this->line('  ۱. دریافت و ذخیره واریزی‌های کاربران (User Payments)...');
            $userPaymentsCount = $syncService->syncUserPayments();
            $this->info("     ✓ {$userPaymentsCount} پرداخت کاربر با موفقیت بررسی و همگام‌سازی شد.");

            $this->line('  ۲. دریافت و ذخیره شارژهای پنل نمایندگی (Panel Payments)...');
            $panelPaymentsCount = $syncService->syncPanelPayments();
            $this->info("     ✓ {$panelPaymentsCount} شارژ پنل نمایندگی با موفقیت بررسی و همگام‌سازی شد.");

            $userCreditsCount = 0;
            if ($this->option('skip-credits')) {
                $this->warn('     - بخش اعتبارات کاربران به دلیل استفاده از فلگ --skip-credits نادیده گرفته شد.');
            } else {
                $this->line('  ۳. دریافت و ذخیره تراکنش‌های اعتبار کاربران (User Credits)...');
                $userCreditsCount = $syncService->syncUserCredits();
                $this->info("     ✓ {$userCreditsCount} تراکنش اعتبار کاربر با موفقیت ذخیره گردید.");
            }

            $this->line('  ۴. محاسبه و بازسازی خلاصه‌های مالی روزانه (Daily Summaries)...');
            $syncService->rebuildDailySummaries();
            $this->info('     ✓ خلاصه‌های آماری و سود روزانه با موفقیت بروزرسانی شد.');

            $duration = round(microtime(true) - $startTime, 2);
            $endDate = now()->toDateTimeString();

            $this->newLine();
            $this->info("[{$endDate}] همگام‌سازی خودکار کرون پیامک با موفقیت پایان یافت (مدت زمان: {$duration} ثانیه).");
            $this->line("  مجموع: {$userPaymentsCount} واریز کاربر | {$panelPaymentsCount} شارژ پنل | {$userCreditsCount} تراکنش اعتبار.");

            Log::info("[SmsReseller] Automated daily sync completed in {$duration}s. User payments: {$userPaymentsCount}, Panel payments: {$panelPaymentsCount}, User credits: {$userCreditsCount}.");

            return self::SUCCESS;

        } catch (Throwable $e) {
            $duration = round(microtime(true) - $startTime, 2);
            Log::error("[SmsReseller] Daily automated cron sync error after {$duration}s: " . $e->getMessage(), [
                'exception' => $e
            ]);

            $this->newLine();
            $this->error("  خطای سیستمی در اجرای کرون جاب روزانه ریسیلر پیامک: " . $e->getMessage());
            return self::FAILURE;
        }
    }
}
