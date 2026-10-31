<?php

namespace Modules\SmsReseller\App\Console\Commands;

use Illuminate\Console\Command;
use Modules\SmsReseller\Installer;

class SmsResellerUpdatePermissions extends Command
{
    protected $signature = 'smsreseller:update-permissions';
    protected $description = 'Update SmsReseller module permissions without reinstalling everything.';

    public function handle(): int
    {
        $this->info('Updating SmsReseller module permissions...');

        Installer::syncModulePermissions();

        $this->info('SmsReseller module permissions updated successfully.');
        return self::SUCCESS;
    }
}
