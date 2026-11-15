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

        if ($this->isSuperAdmin($user)) {
            $message = 'برای دسترسی به این بخش، ابتدا از داشبورد نماینده مورد نظر را انتخاب کنید.';

            if (request()->expectsJson() || request()->ajax()) {
                abort(403, $message);
            }

            if (Route::has('reseller.dashboard')) {
                throw new HttpResponseException(
                    redirect()->route('reseller.dashboard')->with('error', $message)
                );
            }

            abort(403, $message);
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

        // If no reseller record exists yet, but the user has a reseller role, auto-provision
        if (!$reseller && method_exists($user, 'hasRole') && ($user->hasRole('reseller') || $user->hasRole('reseller-owner') || $user->hasRole('reseller-super-admin'))) {
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
     * Operational guard: acting as a suspended reseller is never allowed.
     */
    private function assertResellerOperable(Reseller $reseller): void
    {
        if ($reseller->isActive()) {
            return;
        }

        if ($this->isSuperAdmin(Auth::user())) {
            abort(403, 'این نمایندگی در وضعیت معلق است؛ برای انجام عملیات ابتدا وضعیت آن را فعال کنید.');
        }

        abort(403, 'حساب نمایندگی شما در وضعیت معلق قرار دارد و دسترسی به پنل امکان‌پذیر نیست.');
    }
}
