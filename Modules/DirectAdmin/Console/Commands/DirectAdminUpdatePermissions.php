<?php

namespace Modules\DirectAdmin\Console\Commands;

use Illuminate\Console\Command;
use Modules\DirectAdmin\Installer;

class DirectAdminUpdatePermissions extends Command
{
    protected $signature = 'directadmin:update-permissions';
    protected $description = 'Update DirectAdmin module permissions without reinstalling everything.';

    public function handle(): int
    {
        $this->info('Updating DirectAdmin module permissions...');

        Installer::syncModulePermissions();

        $this->info('DirectAdmin module permissions updated successfully.');
        return self::SUCCESS;
    }
}
