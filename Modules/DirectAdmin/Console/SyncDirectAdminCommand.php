<?php

namespace Modules\DirectAdmin\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Modules\DirectAdmin\Entities\DaServer;
use Modules\DirectAdmin\Entities\DaAccount;
use Modules\DirectAdmin\Services\DirectAdminClient;
use Carbon\Carbon;
use Throwable;

class SyncDirectAdminCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'directadmin:sync {--server= : شناسه سرور خاص جهت همگام‌سازی}';

    /**
     * The console command aliases.
     *
     * @var array
     */
    protected $aliases = [
        'da:sync',
    ];

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'همگام‌سازی خودکار و روزانه اکانت‌ها، مصرف فضا، پهنای باند و وضعیت اتصال سرورهای دایرکت‌ادمین';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('[' . now()->toDateTimeString() . '] آغاز فرآیند همگام‌سازی خودکار دایرکت‌ادمین (Cron Daily Sync)...');

        $serverId = $this->option('server');
        $query = DaServer::where('is_active', true);

        if ($serverId) {
            $query->where('id', $serverId);
        }

        $servers = $query->get();

        if ($servers->isEmpty()) {
            $this->warn('هیچ سرور فعالی برای همگام‌سازی یافت نشد.');
            return self::SUCCESS;
        }

        $totalSynced = 0;
        $totalCreated = 0;
        $totalUpdated = 0;

        foreach ($servers as $server) {
            $this->line("در حال بررسی و همگام‌سازی سرور: {$server->name} ({$server->host}:{$server->port})...");

            try {
                $client = new DirectAdminClient(
                    $server->host,
                    $server->username,
                    $server->decrypted_password,
                    $server->port,
                    $server->ssl,
                    15
                );

                $usernames = $client->getUsersList();

                if (empty($usernames)) {
                    $server->update([
                        'last_connected_at' => now(),
                        'last_error' => 'ارتباط با سرور برقرار نشد یا لیست کاربران خالی است.',
                    ]);
                    $this->warn("  ارتباط با سرور {$server->name} برقرار نشد یا لیست کاربران خالی است.");
                    continue;
                }

                $server->update([
                    'last_connected_at' => now(),
                    'last_error' => null,
                ]);

                $syncedUsernames = [];

                foreach ($usernames as $username) {
                    try {
                        $config = $client->getUserConfig($username);
                        if (!$config) {
                            continue;
                        }

                        $usage = $client->getUserUsage($username);

                        $diskUsage = 0;
                        $diskLimit = 0;
                        $bandwidthUsage = 0;
                        $bandwidthLimit = 0;

                        if ($usage) {
                            $diskUsage = data_get($usage, 'quotaBytes.usage', 0);
                            $diskLimit = data_get($usage, 'quotaBytes.limit', 0);
                            $bandwidthUsage = data_get($usage, 'bandwidthBytes.usage', 0);
                            $bandwidthLimit = data_get($usage, 'bandwidthBytes.limit', 0);
                        }

                        $dateCreated = null;
                        if (!empty($config['dateCreated'])) {
                            try {
                                $dateCreated = Carbon::parse($config['dateCreated']);
                            } catch (Throwable $e) {
                            }
                        }

                        $suspended = (bool)($config['suspended'] ?? false);

                        $accountData = [
                            'da_server_id' => $server->id,
                            'username' => $username,
                            'domain' => $config['domain'] ?? '',
                            'email' => $config['email'] ?? null,
                            'package' => $config['package'] ?? null,
                            'ip' => $config['ip'] ?? null,
                            'suspended' => $suspended,
                            'disk_usage_bytes' => $diskUsage,
                            'disk_limit_bytes' => $diskLimit,
                            'bandwidth_usage_bytes' => $bandwidthUsage,
                            'bandwidth_limit_bytes' => $bandwidthLimit,
                            'domains_count' => count($config['domains'] ?? []),
                            'email_accounts_count' => (int)($config['emailAccountsLim'] ?? 0),
                            'mysql_databases_count' => (int)($config['mySqlDatabasesLim'] ?? 0),
                            'status' => $suspended ? 'suspended' : 'active',
                            'date_created' => $dateCreated,
                            'raw_config' => $config,
                            'synced_at' => now(),
                        ];

                        $account = DaAccount::where('da_server_id', $server->id)
                            ->where('username', $username)
                            ->first();

                        if ($account) {
                            $account->update($accountData);
                            $totalUpdated++;
                        } else {
                            DaAccount::create($accountData);
                            $totalCreated++;
                        }

                        $syncedUsernames[] = $username;
                        $totalSynced++;
                    } catch (Throwable $e) {
                        Log::error("[SyncDirectAdminCommand] Sync failed for user {$username} on server {$server->id}: " . $e->getMessage());
                    }
                }

                $this->info("  همگام‌سازی سرور {$server->name} با موفقیت انجام شد (تعداد اکانت: " . count($syncedUsernames) . ").");

            } catch (Throwable $e) {
                $server->update([
                    'last_error' => $e->getMessage(),
                ]);
                Log::error("[SyncDirectAdminCommand] Error syncing server {$server->id}: " . $e->getMessage());
                $this->error("  خطای سیستمی در اتصال به سرور {$server->name}: " . $e->getMessage());
            }
        }

        $this->info('[' . now()->toDateTimeString() . "] همگام‌سازی دایرکت‌ادمین پایان یافت. مجموع اکانت‌های بررسی‌شده: {$totalSynced} (جدید: {$totalCreated}، بروزرسانی: {$totalUpdated}).");
        return self::SUCCESS;
    }
}
