<?php

namespace Modules\SmsReseller\App\Console\Commands;

use Illuminate\Console\Command;
use Modules\SmsReseller\Installer;

class SmsResellerInstall extends Command
{
    protected $signature = 'smsreseller:install';
    protected $description = 'Install SmsReseller module permissions/roles (does not run migrations automatically).';

    public function handle(): int
    {
        $installer = app(Installer::class);
        $installer->install();

        $this->info('SmsReseller module installed (permissions created).');
        $this->line('Now run: php artisan migrate');
        return self::SUCCESS;
    }
}
