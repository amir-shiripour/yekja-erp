<?php

namespace Modules\DomainManager\Console\Commands;

use Illuminate\Console\Command;
use Modules\DomainManager\Installer;

class DomainManagerUpdatePermissions extends Command
{
    protected $signature = 'domainmanager:update-permissions';
    protected $description = 'Update DomainManager module permissions without reinstalling everything.';

    public function handle(): int
    {
        $this->info('Updating DomainManager module permissions...');

        Installer::syncModulePermissions();

        $this->info('DomainManager module permissions updated successfully.');
        return self::SUCCESS;
    }
}
