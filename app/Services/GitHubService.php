<?php

namespace App\Services;

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

    /**
     * بررسی اینکه آیا مسیر پروژه یک مخزن گیت معتبر است یا خیر
     */
    public function isGitRepository(): bool
    {
        return is_dir(base_path('.git'));
    }

    /**
     * دریافت وضعیت گیت محلی سرور
     */
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
            $branchRes = Process::path($base)->run('git rev-parse --abbrev-ref HEAD');
            $commitRes = Process::path($base)->run('git log -1 --format="%h - %s (%ci)"');

            return [
                'initialized' => true,
                'branch' => $branchRes->successful() ? trim($branchRes->output()) : 'نامشخص',
                'commit' => $commitRes->successful() ? trim($commitRes->output()) : 'نامشخص',
                'message' => 'مخزن گیت فعال است.',
            ];
        } catch (\Exception $e) {
            return [
                'initialized' => true,
                'branch' => 'نامشخص',
                'commit' => 'نامشخص',
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * دریافت اطلاعات آخرین کامیت از برنچ مشخص شده مخزن گیت‌هاب از طریق API
     */
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
        } catch (\Exception $e) {
            Log::error("GitHub API Error: " . $e->getMessage());
            return null;
        }

        return null;
    }

    /**
     * راه‌اندازی و اتصال اولیه پوشه پروژه به مخزن گیت‌هاب (برای سرورهایی که با Zip یا FTP آپلود شده‌اند)
     */
    public function initializeGitRepository(): array
    {
        $base = base_path();
        try {
            Log::info("Auto-initializing Git repository at {$base} for {$this->repo} on branch {$this->branch}...");

            // ۱. تنظیم safe.directory برای جلوگیری از خطای مالکیت کاربر وب‌سرور
            Process::path($base)->run("git config --global --add safe.directory \"{$base}\"");

            // ۲. مقداردهی اولیه Git
            if (!$this->isGitRepository()) {
                $init = Process::path($base)->run('git init');
                if (!$init->successful()) {
                    return [
                        'success' => false,
                        'message' => 'خطا در اجرای git init: ' . $init->errorOutput(),
                    ];
                }
            }

            // ۳. تنظیم نام شاخه اصلی
            Process::path($base)->run("git branch -M {$this->branch}");

            // ۴. افزودن آدرس ریموت
            $remoteUrl = "https://github.com/{$this->repo}.git";
            Process::path($base)->run('git remote remove origin');
            $remote = Process::path($base)->run("git remote add origin {$remoteUrl}");
            if (!$remote->successful()) {
                return [
                    'success' => false,
                    'message' => 'خطا در افزودن remote origin: ' . $remote->errorOutput(),
                ];
            }

            // ۵. دریافت اطلاعات شاخه از گیت‌هاب
            $fetch = Process::path($base)->timeout(120)->run("git fetch origin {$this->branch}");
            if (!$fetch->successful()) {
                return [
                    'success' => false,
                    'message' => 'خطا در دریافت شاخه از گیت‌هاب (git fetch): ' . $fetch->errorOutput(),
                ];
            }

            // ۶. اتصال شاخه محلی به شاخه ریموت و ریست ملایم جهت حفظ فایل‌ها
            Process::path($base)->run("git branch --set-upstream-to=origin/{$this->branch} {$this->branch}");
            Process::path($base)->run("git reset --mixed origin/{$this->branch}");

            Log::info("Git repository successfully initialized and connected to origin/{$this->branch}");

            return [
                'success' => true,
                'message' => 'مخزن گیت با موفقیت روی سرور مقداردهی شد و به شاخه ' . $this->branch . ' متصل گردید.',
            ];
        } catch (\Exception $e) {
            Log::error("Git initialization exception: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'خطای سیستمی در راه‌اندازی گیت: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * اجرای عملیات به‌روزرسانی خودکار با ثبت لاگ دقیق
     */
    public function performUpdate(): array
    {
        $base = base_path();
        try {
            Log::info("Starting Smart Deployment from GitHub ({$this->repo} / {$this->branch})...");

            // اگر پوشه .git روی سرور وجود نداشت، ابتدا به صورت خودکار آن را مقداردهی و متصل کن
            if (!$this->isGitRepository()) {
                Log::info("No .git folder detected on server. Attempting auto-initialization...");
                $initResult = $this->initializeGitRepository();
                if (!$initResult['success']) {
                    return $initResult;
                }
            }

            // اطمینان از تنظیم safe.directory
            Process::path($base)->run("git config --global --add safe.directory \"{$base}\"");

            // ۱. بردن سایت به حالت تعمیرات
            Process::path($base)->run('php artisan down');

            // ۲. واکشی آخرین کدها از گیت‌هاب
            $fetch = Process::path($base)->timeout(90)->run("git fetch origin {$this->branch}");
            if (!$fetch->successful()) {
                Log::error("Git Fetch Failed: " . $fetch->errorOutput());
                Process::path($base)->run('php artisan up');
                return [
                    'success' => false,
                    'message' => 'خطا در دریافت اطلاعات از گیت‌هاب: ' . $fetch->errorOutput(),
                ];
            }

            // تلاش برای ادغام آخرین تغییرات شاخه
            $pull = Process::path($base)->timeout(90)->run("git pull origin {$this->branch}");
            if (!$pull->successful()) {
                Log::warning("Git Pull standard failed, attempting safe merge/checkout: " . $pull->errorOutput());
                // اگر پول عادی به دلیل تفاوت‌های محلی با ارور مواجه شد، ریست ملایم انجام می‌دهیم
                $reset = Process::path($base)->run("git reset --hard origin/{$this->branch}");
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

            // ۳. آپدیت دیتابیس، کش‌ها و وابستگی‌ها
            Process::path($base)->timeout(180)->run('php artisan migrate --force');
            Process::path($base)->run('php artisan optimize:clear');
            Process::path($base)->run('php artisan up');

            Log::info("Deployment completed successfully.");

            return [
                'success' => true,
                'message' => 'سیستم با موفقیت به آخرین نسخه گیت‌هاب به‌روزرسانی و همگام شد.',
            ];

        } catch (\Exception $e) {
            Log::error("Critical Deployment Error: " . $e->getMessage());
            Process::path($base)->run('php artisan up');
            return [
                'success' => false,
                'message' => 'خطای سیستمی در فرآیند استقرار: ' . $e->getMessage(),
            ];
        }
    }
}
