<?php

namespace Modules\ResellerHub\App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Tenancy\TenantManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Modules\ResellerHub\App\Http\Requests\UpdateTenantBrandingRequest;
use Modules\ResellerHub\App\Http\Requests\UpdateTenantDomainRequest;
use Modules\ResellerHub\App\Services\DomainVerificationService;

class TenantDomainBrandingController extends Controller
{
    /**
     * Resolve the active tenant from TenantManager or authenticated user.
     */
    protected function getActiveTenant(): ?Tenant
    {
        /** @var TenantManager $tenantManager */
        $tenantManager = app(TenantManager::class);
        $tenant = $tenantManager->getTenant();

        if (!$tenant && auth()->check() && !empty(auth()->user()->tenant_id)) {
            $tenant = Tenant::find(auth()->user()->tenant_id);
            if ($tenant) {
                $tenantManager->setTenant($tenant);
            }
        }

        return $tenant;
    }

    /**
     * Connect or disconnect custom domain for tenant.
     */
    public function updateDomain(UpdateTenantDomainRequest $request, DomainVerificationService $domainService): JsonResponse|RedirectResponse
    {
        $tenant = $this->getActiveTenant();

        if (!$tenant) {
            $msg = __('resellerhub::messages.subscription.not_found');
            return ($request->wantsJson() || $request->ajax())
                ? response()->json(['success' => false, 'message' => $msg], 404)
                : back()->with('error', $msg);
        }

        // Entitlement Check: Does tenant plan allow custom domain?
        if (!$tenant->allowsCustomDomain()) {
            $msg = __('resellerhub::messages.branding.custom_domain_disabled');
            return ($request->wantsJson() || $request->ajax())
                ? response()->json(['success' => false, 'message' => $msg], 403)
                : back()->with('error', $msg);
        }

        $rawDomain = (string)$request->input('custom_domain', '');
        $sanitized = $domainService->sanitizeDomain($rawDomain);

        // 1. If disconnect/remove requested
        if (empty($sanitized)) {
            $tenant->update(['custom_domain' => null]);
            $msg = __('resellerhub::messages.tenants.domain_removed');

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => $msg,
                    'custom_domain' => null,
                    'dns' => null,
                ]);
            }

            return back()->with('success', $msg);
        }

        // 2. Validate domain syntax
        if (!$domainService->isValidDomain($sanitized)) {
            $err = __('resellerhub::messages.tenants.domain_invalid');
            return ($request->wantsJson() || $request->ajax())
                ? response()->json(['success' => false, 'message' => $err], 422)
                : back()->with('error', $err);
        }

        // 3. Reserved domain check
        if ($domainService->isReservedDomain($sanitized)) {
            $err = __('resellerhub::messages.tenants.domain_reserved');
            return ($request->wantsJson() || $request->ajax())
                ? response()->json(['success' => false, 'message' => $err], 422)
                : back()->with('error', $err);
        }

        // 4. Availability check (not taken by another tenant)
        if (!$domainService->isDomainAvailable($sanitized, $tenant->id)) {
            $err = __('resellerhub::messages.tenants.domain_taken');
            return ($request->wantsJson() || $request->ajax())
                ? response()->json(['success' => false, 'message' => $err], 422)
                : back()->with('error', $err);
        }

        // 5. Update tenant custom domain
        $tenant->update(['custom_domain' => $sanitized]);

        // 6. Check DNS status
        $dns = $domainService->fullCheck($sanitized, $tenant->id);

        Log::info("[ResellerHub] Tenant #{$tenant->id} connected custom domain: {$sanitized}");

        $successMsg = __('resellerhub::messages.tenants.domain_connected', ['domain' => $sanitized]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $successMsg,
                'custom_domain' => $sanitized,
                'dns' => $dns,
            ]);
        }

        return back()->with('success', $successMsg);
    }

    /**
     * Update tenant white-label branding identity.
     */
    public function updateBranding(UpdateTenantBrandingRequest $request): JsonResponse|RedirectResponse
    {
        $tenant = $this->getActiveTenant();

        if (!$tenant) {
            $msg = __('resellerhub::messages.subscription.not_found');
            return ($request->wantsJson() || $request->ajax())
                ? response()->json(['success' => false, 'message' => $msg], 404)
                : back()->with('error', $msg);
        }

        // Entitlement Check: Does tenant plan allow white-label?
        if (!$tenant->allowsWhiteLabel()) {
            $msg = __('resellerhub::messages.branding.white_label_disabled');
            return ($request->wantsJson() || $request->ajax())
                ? response()->json(['success' => false, 'message' => $msg], 403)
                : back()->with('error', $msg);
        }

        $branding = is_array($tenant->branding) ? $tenant->branding : [];
        $logoPath = $branding['brand_logo'] ?? null;
        $faviconPath = $branding['brand_favicon'] ?? null;

        // Handle brand logo upload or removal
        if ($request->boolean('remove_brand_logo')) {
            if ($logoPath && Storage::disk('public')->exists($logoPath)) {
                Storage::disk('public')->delete($logoPath);
            }
            $logoPath = null;
        } elseif ($request->hasFile('brand_logo')) {
            if ($logoPath && Storage::disk('public')->exists($logoPath)) {
                Storage::disk('public')->delete($logoPath);
            }
            $logoPath = $request->file('brand_logo')->store('branding/tenants/logos', 'public');
        }

        // Handle brand favicon upload or removal
        if ($request->boolean('remove_brand_favicon')) {
            if ($faviconPath && Storage::disk('public')->exists($faviconPath)) {
                Storage::disk('public')->delete($faviconPath);
            }
            $faviconPath = null;
        } elseif ($request->hasFile('brand_favicon')) {
            if ($faviconPath && Storage::disk('public')->exists($faviconPath)) {
                Storage::disk('public')->delete($faviconPath);
            }
            $faviconPath = $request->file('brand_favicon')->store('branding/tenants/favicons', 'public');
        }

        $branding['is_custom'] = true;
        $branding['brand_title'] = trim((string)$request->input('brand_title'));
        $branding['brand_primary_color'] = trim((string)$request->input('brand_primary_color', '#4f46e5'));
        $branding['brand_footer_text'] = trim((string)$request->input('brand_footer_text', ''));
        $branding['support_phone'] = trim((string)$request->input('support_phone', ''));
        $branding['support_email'] = trim((string)$request->input('support_email', ''));
        $branding['brand_logo'] = $logoPath;
        $branding['brand_favicon'] = $faviconPath;

        $settings = is_array($tenant->settings) ? $tenant->settings : [];
        $settings['branding_configured'] = true;

        $tenant->branding = $branding;
        $tenant->settings = $settings;
        $tenant->save();

        Log::info("[ResellerHub] Tenant #{$tenant->id} updated white-label branding: {$branding['brand_title']}");

        $successMsg = __('resellerhub::messages.onboarding.branding_saved');

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $successMsg,
                'brand_title' => $branding['brand_title'],
                'brand_primary_color' => $branding['brand_primary_color'],
                'brand_logo_url' => $logoPath ? asset('storage/' . $logoPath) : null,
                'brand_favicon_url' => $faviconPath ? asset('storage/' . $faviconPath) : null,
            ]);
        }

        return back()->with('success', $successMsg);
    }
}
