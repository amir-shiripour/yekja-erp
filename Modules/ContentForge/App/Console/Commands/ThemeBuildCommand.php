<?php

declare(strict_types=1);

namespace Modules\ContentForge\App\Console\Commands;

use App\Services\ThemeManager;
use Illuminate\Console\Command;

class ThemeBuildCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'theme:build
                            {--skip-npm : Only clear view and cache without running npm build}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear view cache, clear app cache, and compile theme frontend assets via npm run build';

    /**
     * Execute the console command.
     */
    public function handle(ThemeManager $themeManager): int
    {
        $skipNpm = (bool)$this->option('skip-npm');
        $this->info('Starting theme cache clear and asset build...');

        $result = $themeManager->syncAndBuildAssets(!$skipNpm);

        if ($result['view_cleared']) {
            $this->line('<info>✓</info> Blade view cache cleared successfully.');
        } else {
            $this->line('<comment>!</comment> Could not clear view cache.');
        }

        if ($result['cache_cleared']) {
            $this->line('<info>✓</info> Application cache cleared successfully.');
        }

        if ($result['build_attempted']) {
            if ($result['build_success']) {
                $this->line('<info>✓</info> Frontend assets compiled successfully (npm run build).');
                if (!empty($result['output'])) {
                    $this->line($result['output']);
                }
            } else {
                $this->error('✗ npm run build failed.');
                if (!empty($result['error'])) {
                    $this->error($result['error']);
                }
                return self::FAILURE;
            }
        } else {
            $this->line('<comment>i</comment> Asset build skipped (--skip-npm).');
        }

        $this->info('Theme synchronization completed.');
        return self::SUCCESS;
    }
}
