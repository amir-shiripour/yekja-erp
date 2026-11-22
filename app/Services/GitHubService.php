<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Log;

class GitHubService
{
    protected string $repo;
    protected string $branch;
    protected ?string $token;

    public function __construct()
    {
        $this->repo = config('services.github.repo', env('GITHUB_REPO', 'amir-shiripour/yekja-erp'));
        $this->branch = config('services.github.branch', env('GITHUB_BRANCH', 'main'));
        $this->token = config('services.github.token', env('GITHUB_TOKEN'));
    }

    public function getRepo(): string
    {
        return $this->repo;
    }

    public function getBranch(): string
    {
        return $this->branch;
    }


    protected function gitCmd(string $command): string
    {
        return "git -c safe.directory=* {$command}";
    }


    public function isGitRepository(): bool
    {
        return is_dir(base_path('.git'));
    }


    public function getLocalGitStatus(): array
    {
        $base = base_path();
        if (!$this->isGitRepository()) {
            return [
                'initialized' => false,
                'branch' => null,
                'commit' => null,
                'message' => 'مخزن گیت روی سرور ایجاد نشده است.',
            ];
        }

        try {
            $branchRes = Process::path($base)->run($this->gitCmd('rev-parse --abbrev-ref HEAD'));
            $commitRes = Process::path($base)->run($this->gitCmd('log -1 --format="%h - %s (%ci)"'));

            return [
                'initialized' => true,
                'branch' => $branchRes->successful() ? trim($branchRes->output()) : 'نامشخص',
                'commit' => $commitRes->successful() ? trim($commitRes->output()) : 'نامشخص',
                'message' => 'مخزن گیت فعال است.',
            ];
        } catch (Exception $e) {
            return [
                'initialized' => true,
                'branch' => 'نامشخص',
                'commit' => 'نامشخص',
                'message' => $e->getMessage(),
            ];
        }
    }


    public function getLatestRemoteInfo(): ?array
    {
        try {
            $headers = [
                'Accept' => 'application/vnd.github.v3+json',
                'User-Agent' => 'Yekja-ERP-Agent',
            ];

            if (!empty($this->token)) {
                $headers['Authorization'] = 'Bearer ' . $this->token;
            }

            $response = Http::withHeaders($headers)
                ->timeout(12)
                ->get("https://api.github.com/repos/{$this->repo}/branches/{$this->branch}");

            if ($response->successful()) {
                $data = $response->json();
                $data['active_repo'] = $this->repo;
                $data['active_branch'] = $this->branch;
                return $data;
            }

            Log::warning("GitHub API returned status {$response->status()}: " . $response->body());
        } catch (Exception $e) {
            Log::error("GitHub API Error: " . $e->getMessage());
            return null;
        }

        return null;
    }

    public function initializeGitRepository(): array
    {
        $base = base_path();
        try {
            Log::info("Auto-initializing Git repository at {$base} for {$this->repo} on branch {$this->branch}...");

            if (!$this->isGitRepository()) {
                $init = Process::path($base)->run($this->gitCmd('init'));
                if (!$init->successful()) {
                    return [
                        'success' => false,
                        'message' => 'خطا در اجرای git init: ' . $init->errorOutput(),
                    ];
                }
            }

            Process::path($base)->run($this->gitCmd("branch -M {$this->branch}"));

            $remoteUrl = "https://github.com/{$this->repo}.git";
            Process::path($base)->run($this->gitCmd('remote remove origin'));
            $remote = Process::path($base)->run($this->gitCmd("remote add origin {$remoteUrl}"));
            if (!$remote->successful()) {
                return [
                    'success' => false,
                    'message' => 'خطا در افزودن remote origin: ' . $remote->errorOutput(),
                ];
            }

            $fetch = Process::path($base)->timeout(120)->run($this->gitCmd("fetch origin {$this->branch}"));
            if (!$fetch->successful()) {
                return [
                    'success' => false,
                    'message' => 'خطا در دریافت شاخه از گیت‌هاب (git fetch): ' . $fetch->errorOutput(),
                ];
            }

            Process::path($base)->run($this->gitCmd("branch --set-upstream-to=origin/{$this->branch} {$this->branch}"));
            Process::path($base)->run($this->gitCmd("reset --mixed origin/{$this->branch}"));

            Log::info("Git repository successfully initialized and connected to origin/{$this->branch}");

            return [
                'success' => true,
                'message' => 'مخزن گیت با موفقیت روی سرور مقداردهی شد و به شاخه ' . $this->branch . ' متصل گردید.',
            ];
        } catch (Exception $e) {
            Log::error("Git initialization exception: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'خطای سیستمی در راه‌اندازی گیت: ' . $e->getMessage(),
            ];
        }
    }


    public function performUpdate(): array
    {
        @set_time_limit(300);
        @ini_set('memory_limit', '512M');

        $base = base_path();
        try {
            Log::info("Starting Smart Deployment from GitHub ({$this->repo} / {$this->branch})...");

            if (!$this->isGitRepository()) {
                Log::info("No .git folder detected on server. Attempting auto-initialization...");
                $initResult = $this->initializeGitRepository();
                if (!$initResult['success']) {
                    return $initResult;
                }
            }

            Process::path($base)->run('php artisan down');

            $fetch = Process::path($base)->timeout(90)->run($this->gitCmd("fetch origin {$this->branch}"));
            if (!$fetch->successful()) {
                Log::error("Git Fetch Failed: " . $fetch->errorOutput());
                Process::path($base)->run('php artisan up');
                return [
                    'success' => false,
                    'message' => 'خطا در دریافت اطلاعات از گیت‌هاب: ' . $fetch->errorOutput(),
                ];
            }

            $pull = Process::path($base)->timeout(90)->run($this->gitCmd("pull origin {$this->branch}"));
            if (!$pull->successful()) {
                Log::warning("Git Pull standard failed, attempting safe merge/checkout: " . $pull->errorOutput());
                $reset = Process::path($base)->run($this->gitCmd("reset --hard origin/{$this->branch}"));
                if (!$reset->successful()) {
                    Log::error("Git Reset Failed: " . $reset->errorOutput());
                    Process::path($base)->run('php artisan up');
                    return [
                        'success' => false,
                        'message' => 'خطا در اعمال کدهای جدید از گیت‌هاب: ' . ($pull->errorOutput() ?: $reset->errorOutput()),
                    ];
                }
            }

            Log::info("Git update applied successfully.");

            Process::path($base)->timeout(180)->run('php artisan migrate --force');
            Process::path($base)->timeout(120)->run('php artisan reseller:sync-database');

            $buildResult = null;
            if (app()->bound(ThemeManager::class)) {
                Log::info("Smart Deployment: Compiling frontend theme assets via npm run build...");
                $buildResult = app(ThemeManager::class)->syncAndBuildAssets(true);
            }

            Process::path($base)->run('php artisan optimize:clear');
            Process::path($base)->run('php artisan up');

            Log::info("Deployment completed successfully.");

            $successMsg = 'سیستم با موفقیت به آخرین نسخه گیت‌هاب به‌روزرسانی و همگام شد.';
            if (!empty($buildResult['build_success'])) {
                $successMsg .= ' استایل‌ها و کامپوننت‌های فرانت‌اند (npm run build) نیز با موفقیت کامپایل شدند.';
            }

            return [
                'success' => true,
                'message' => $successMsg,
            ];

        } catch (Exception $e) {
            Log::error("Critical Deployment Error: " . $e->getMessage());
            Process::path($base)->run('php artisan up');
            return [
                'success' => false,
                'message' => 'خطای سیستمی در فرآیند استقرار: ' . $e->getMessage(),
            ];
        }
    }
}
