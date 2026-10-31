<?php

namespace Modules\DomainManager\Console\Commands;

use Illuminate\Console\Command;
use Modules\DomainManager\Installer;

class DomainManagerInstall extends Command
{
    protected $signature = 'domainmanager:install';
    protected $description = 'Install DomainManager module permissions/roles (does not run migrations automatically).';

    public function handle(): int
    {
        $installer = app(Installer::class);
        $installer->install();

        $this->info('DomainManager module installed (permissions created).');
        $this->line('Now run: php artisan migrate');
        return self::SUCCESS;
    }
}
