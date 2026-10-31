<?php

namespace Modules\ResellerHub\App\Livewire\Portal;

use App\Models\Tenant;
use App\Tenancy\TenantManager;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;
use Modules\ResellerHub\App\Jobs\ProvisionTenantJob;

#[Layout('layouts.guest')]
class TenantProvisioningLoader extends Component
{
    public int $tenantId = 0;
    public int $progress = 15;
    public string $step = 'init';
    public string $message = 'در حال راه‌اندازی و تخصیص زیردامنه سامانه...';
    public bool $completed = false;
    public ?string $redirectUrl = null;

    public function mount(?int $tenant = null)
    {
        $this->tenantId = $tenant ?: (int)request()->query('tenant', 0);

        if (!$this->tenantId) {
            $user = Auth::user();
            if ($user && $user->tenant_id) {
                $this->tenantId = (int)$user->tenant_id;
            }
        }

        if (!$this->tenantId) {
            return redirect()->route('portal.get-started');
        }

        $cached = Cache::get("tenant_provisioning_progress_{$this->tenantId}");
        if ($cached) {
            $this->progress = (int)($cached['progress'] ?? 15);
            $this->step = (string)($cached['step'] ?? 'init');
            $this->message = (string)($cached['message'] ?? 'در حال آماده‌سازی سامانه...');
            $this->redirectUrl = $cached['redirect_url'] ?? null;
            $this->completed = !empty($cached['completed']);
        } else {
            // Dispatch provisioning job if not already initiated
            ProvisionTenantJob::dispatch($this->tenantId);
        }

        // If already completed in DB
        $tenantModel = Tenant::find($this->tenantId);
        if ($tenantModel && $tenantModel->isActive()) {
            $this->progress = 100;
            $this->completed = true;
            $this->message = 'سامانه شما با موفقیت آماده شد!';
            $dashboardUrl = app(TenantManager::class)->getTenantUrl($tenantModel, 'user/dashboard');
            $this->redirectUrl = route('splash.registered', ['redirect_url' => $dashboardUrl]);
        }
    }

    #[On('echo:tenants.{tenantId},TenantProvisioningProgress')]
    public function onProgressUpdated($payload)
    {
        if (!empty($payload['progress'])) {
            $this->progress = (int)$payload['progress'];
        }
        if (!empty($payload['step'])) {
            $this->step = (string)$payload['step'];
        }
        if (!empty($payload['message'])) {
            $this->message = (string)$payload['message'];
        }
        if (!empty($payload['redirect_url'])) {
            $this->redirectUrl = (string)$payload['redirect_url'];
        }
        if ($this->progress >= 100 || !empty($payload['completed'])) {
            $this->completed = true;
            $tenantModel = Tenant::find($this->tenantId);
            $dashboardUrl = $tenantModel ? app(TenantManager::class)->getTenantUrl($tenantModel, 'user/dashboard') : route('user.dashboard');
            $this->redirectUrl = route('splash.registered', ['redirect_url' => $dashboardUrl]);
        }
    }

    public function checkProgress()
    {
        $cached = Cache::get("tenant_provisioning_progress_{$this->tenantId}");
        if ($cached) {
            $this->progress = (int)($cached['progress'] ?? $this->progress);
            $this->step = (string)($cached['step'] ?? $this->step);
            $this->message = (string)($cached['message'] ?? $this->message);
            if (!empty($cached['redirect_url'])) {
                $this->redirectUrl = (string)$cached['redirect_url'];
            }
            if (!empty($cached['completed'])) {
                $this->completed = true;
            }
        }

        $tenantModel = Tenant::find($this->tenantId);
        if (($tenantModel && $tenantModel->isActive()) || $this->progress >= 100 || $this->completed) {
            $this->progress = 100;
            $this->completed = true;
            $this->message = 'سامانه شما با موفقیت آماده شد!';
            $dashboardUrl = $tenantModel ? app(TenantManager::class)->getTenantUrl($tenantModel, 'user/dashboard') : route('user.dashboard');
            $this->redirectUrl = route('splash.registered', ['redirect_url' => $dashboardUrl]);
        }
    }

    public function render()
    {
        return view('resellerhub::livewire.portal.tenant-provisioning-loader');
    }
}
