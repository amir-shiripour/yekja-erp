<?php

namespace Modules\DirectAdmin\Console\Commands;

use Illuminate\Console\Command;
use Modules\DirectAdmin\Installer;

class DirectAdminInstall extends Command
{
    protected $signature = 'directadmin:install';
    protected $description = 'Install DirectAdmin module permissions/roles (does not run migrations automatically).';

    public function handle(): int
    {
        $installer = app(Installer::class);
        $installer->install();

        $this->info('DirectAdmin module installed (permissions created).');
        $this->line('Now run: php artisan migrate');
        return self::SUCCESS;
    }
}
