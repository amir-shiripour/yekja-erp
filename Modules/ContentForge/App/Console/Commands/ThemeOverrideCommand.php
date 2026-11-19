<?php

declare(strict_types=1);

namespace Modules\ContentForge\App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use App\Services\ThemeManager;
use Throwable;

class ThemeOverrideCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'theme:override
                            {view : The view name to override (e.g. user.partials.sidebar, layouts.user, resellerhub::user.dashboard)}
                            {--theme= : Target theme name in resources/views/themes (defaults to active CRM theme)}
                            {--force : Overwrite existing override file if it already exists}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scaffold or copy a CRM or module view into a theme override directory for customization';

    /**
     * Execute the console command.
     */
    public function handle(ThemeManager $themeManager): int
    {
        $viewName = trim((string)$this->argument('view'));
        $theme = $this->option('theme') ?: $themeManager->getActiveCrmTheme();
        $force = (bool)$this->option('force');

        if (empty($theme)) {
            $theme = 'default';
        }

        $theme = strtolower($theme);

        // 1. Locate original source view file
        $sourcePath = null;
        try {
            $finder = app('view')->getFinder();
            if (method_exists($finder, 'find')) {
                $sourcePath = $finder->find($viewName);
            }
        } catch (Throwable $e) {
            $this->error("Error resolving view [{$viewName}]: " . $e->getMessage());
            return self::FAILURE;
        }

        if (!$sourcePath || !File::exists($sourcePath)) {
            $this->error("Source view file for [{$viewName}] could not be found.");
            return self::FAILURE;
        }

        // 2. Compute destination override path
        if (str_contains($viewName, '::')) {
            [$namespace, $rel] = explode('::', $viewName, 2);
            $relPath = str_replace('.', DIRECTORY_SEPARATOR, $rel);
            $destPath = resource_path("views/themes/{$theme}/overrides/{$namespace}/{$relPath}.blade.php");
        } else {
            $relPath = str_replace('.', DIRECTORY_SEPARATOR, $viewName);
            $destPath = resource_path("views/themes/{$theme}/overrides/{$relPath}.blade.php");
        }

        // 3. Check existing file
        if (File::exists($destPath) && !$force) {
            $this->warn("Override file already exists at: [{$destPath}]");
            if (!$this->confirm('Do you want to overwrite it?', false)) {
                $this->info('Operation cancelled.');
                return self::SUCCESS;
            }
        }

        // 4. Ensure directory exists
        $destDir = dirname($destPath);
        if (!File::isDirectory($destDir)) {
            File::makeDirectory($destDir, 0755, true);
        }

        // 5. Copy file
        File::copy($sourcePath, $destPath);

        $this->newLine();
        $this->info(" Override file successfully created for theme [{$theme}]!");
        $this->line("   Source:      <comment>{$sourcePath}</comment>");
        $this->line("   Destination: <info>{$destPath}</info>");
        $this->newLine();
        $this->line("You can now edit this file to customize the CRM frontend without modifying core code.");

        return self::SUCCESS;
    }
}
