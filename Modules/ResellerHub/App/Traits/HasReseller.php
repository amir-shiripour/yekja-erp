<?php

namespace Modules\ResellerHub\App\Traits;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Modules\ResellerHub\App\Models\Reseller;
use Throwable;

trait HasReseller
{
    /**
     * Get the reseller associated with the authenticated user.
     *
     * @throws AuthenticationException
     */
    protected function getReseller(): Reseller
    {
        $reseller = $this->resolveReseller();

        if ($reseller) {
            $this->assertResellerOperable($reseller);

            return $reseller;
        }

        $user = Auth::user();

        // 1. If SuperAdmin, never block: pick first active reseller or auto-create a master reseller
        if ($this->isSuperAdmin($user)) {
            $fallback = Reseller::where('status', Reseller::STATUS_ACTIVE)->orderBy('id')->first()
                ?: Reseller::orderBy('id')->first();

            if ($fallback) {
                return $fallback;
            }

            try {
                return Reseller::firstOrCreate(
                    ['user_id' => $user->id],
                    [
                        'company_name' => $user->name ?: 'مدیریت کل سیستم',
                        'brand_title' => config('app.name', 'CRM'),
                        'status' => Reseller::STATUS_ACTIVE,
                    ]
                );
            } catch (Throwable $e) {
                $lastResort = Reseller::first();
                if ($lastResort) {
                    return $lastResort;
                }
            }
        }

        // 2. If user has reseller role, auto-provision initial Reseller row if missing
        if ($user && method_exists($user, 'hasRole') && ($user->hasRole('reseller') || $user->hasRole('reseller-owner') || $user->hasRole('reseller-super-admin'))) {
            try {
                $name = !empty($user->name) ? $user->name : ('نمایندگی ' . $user->id);
                return Reseller::firstOrCreate(
                    ['user_id' => $user->id],
                    [
                        'company_name' => $name,
                        'brand_title' => $name,
                        'status' => Reseller::STATUS_ACTIVE,
                    ]
                );
            } catch (Throwable $e) {
                $fallback = Reseller::where('user_id', $user->id)->first();
                if ($fallback) {
                    return $fallback;
                }
            }
        }

        abort(403, 'حساب کاربری شما دسترسی به پنل نمایندگی فروش ندارد.');
    }

    protected function getResellerOrNull(): ?Reseller
    {
        return $this->resolveReseller();
    }

    private function resolveReseller(): ?Reseller
    {
        $user = Auth::user();

        if (!$user) {
            abort(401, 'Unauthenticated.');
        }

        if ($this->isSuperAdmin($user)) {
            $activeSessionId = session('active_reseller_id');
            $requestedId = request('reseller_id') ?: request('reseller') ?: $activeSessionId;

            if ($requestedId && $requestedId !== 'all') {
                $chosen = Reseller::find($requestedId);
                if ($chosen) {
                    return $chosen;
                }
            }

            $ownReseller = $this->ownedReseller($user);
            if ($ownReseller) {
                return $ownReseller;
            }

            return null;
        }

        $reseller = $this->ownedReseller($user);

        if ($reseller && !$reseller->isActive()) {
            abort(403, 'حساب نمایندگی شما در وضعیت معلق قرار دارد و دسترسی به پنل امکان‌پذیر نیست.');
        }

        return $reseller;
    }

    private function ownedReseller(mixed $user): ?Reseller
    {
        if (!$user) {
            return null;
        }

        $reseller = Reseller::where('user_id', $user->id)->orderBy('id')->first();

        // If no reseller record exists yet, but the user has a reseller or super-admin role, auto-provision
        if (!$reseller && method_exists($user, 'hasRole') && ($user->hasRole('reseller') || $user->hasRole('reseller-owner') || $user->hasRole('reseller-super-admin') || $user->hasRole('super-admin'))) {
            try {
                $name = !empty($user->name) ? $user->name : ('نمایندگی ' . $user->id);
                $reseller = Reseller::firstOrCreate(
                    ['user_id' => $user->id],
                    [
                        'company_name' => $name,
                        'brand_title' => $name,
                        'status' => Reseller::STATUS_ACTIVE,
                    ]
                );
            } catch (Throwable $e) {
                $reseller = Reseller::where('user_id', $user->id)->first();
            }
        }

        return $reseller;
    }

    private function isSuperAdmin(mixed $user): bool
    {
        return $user !== null
            && method_exists($user, 'hasRole')
            && $user->hasRole('super-admin');
    }

    /**
     * Operational guard: acting as a suspended reseller is never allowed (except for super-admin).
     */
    private function assertResellerOperable(Reseller $reseller): void
    {
        if ($reseller->isActive()) {
            return;
        }

        if ($this->isSuperAdmin(Auth::user())) {
            return;
        }

        abort(403, 'حساب نمایندگی شما در وضعیت معلق قرار دارد و دسترسی به پنل امکان‌پذیر نیست.');
    }
}
