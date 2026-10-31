<?php

declare(strict_types=1);

namespace Modules\ResellerHub\Tests\Unit;

use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;
use Modules\ResellerHub\Tests\TestCase;

/**
 * Compiles every Blade view in the module and lints the generated PHP.
 *
 * A view can be "present" and still be broken: an unclosed @php block, a
 * missing @endif or a stray directive only surfaces at compile time, which in
 * production means a 500 on that page. This test catches those at CI time.
 *
 * It renders nothing, so it needs no database and no fixtures.
 */
class BladeCompilationTest extends TestCase
{
    public function test_every_module_view_compiles_to_valid_php(): void
    {
        $viewsPath = module_path('ResellerHub', 'resources/views');

        $this->assertDirectoryExists($viewsPath, 'The module view directory is missing.');

        $files = collect(File::allFiles($viewsPath))
            ->filter(static fn($file): bool => str_ends_with($file->getFilename(), '.blade.php'))
            ->values();

        $this->assertGreaterThan(0, $files->count(), 'No Blade views were found to compile.');

        $compiler = $this->app->make('blade.compiler');
        $failures = [];

        foreach ($files as $file) {
            $compiled = $compiler->compileString(File::get($file->getPathname()));

            $temp = tempnam(sys_get_temp_dir(), 'blade_') . '.php';
            File::put($temp, $compiled);

            $process = new Process([PHP_BINARY, '-l', $temp]);
            $process->run();

            File::delete($temp);

            if ($process->getExitCode() !== 0) {
                $relative = str_replace($viewsPath . DIRECTORY_SEPARATOR, '', $file->getPathname());
                $failures[] = $relative . ' → ' . trim($process->getErrorOutput() ?: $process->getOutput());
            }
        }

        $this->assertSame(
            [],
            $failures,
            "The following Blade views do not compile to valid PHP:\n- " . implode("\n- ", $failures)
        );
    }
}
