<?php

use App\Tenancy\TenantManager;

return [
    [
        'title' => 'داشبورد نمایندگی فروش',
        'route' => 'reseller.dashboard',
        'permission' => 'resellerhub.view',
        'show' => function () {
            $user = auth()->user();
            if (!$user) return false;
            if (app(TenantManager::class)->hasTenant()) return false;
            return method_exists($user, 'hasRole') && ($user->hasRole('reseller') || $user->hasRole('super-admin') || $user->can('resellerhub.view'));
        },
        'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>',
        'group' => 'resellerhub',
        'position' => 30,
    ],
    [
        'title' => 'مدیریت زیرمجموعه‌ها',
        'route' => 'reseller.sub-accounts.index',
        'permission' => 'resellerhub.sub-accounts.view',
        'show' => function () {
            $user = auth()->user();
            if (!$user) return false;
            if (app(TenantManager::class)->hasTenant()) return false;
            return method_exists($user, 'hasRole') && ($user->hasRole('reseller') || $user->hasRole('super-admin') || $user->can('resellerhub.sub-accounts.view'));
        },
        'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>',
        'group' => 'resellerhub',
        'position' => 31,
    ],
    [
        'title' => 'استخراج دیتابیس (SQL Dump)',
        'route' => 'reseller.tenants.export',
        'permission' => 'resellerhub.tenants.export',
        'show' => function () {
            $user = auth()->user();
            if (!$user) return false;
            if (app(TenantManager::class)->hasTenant()) return false;
            return method_exists($user, 'hasRole') && ($user->hasRole('reseller') || $user->hasRole('super-admin') || $user->can('resellerhub.tenants.export'));
        },
        'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 7v10c0 2 1.5 3 3.5 3h9c2 0 3.5-1 3.5-3V7M4 7c0-2 1.5-3 3.5-3h9c2 0 3.5 1 3.5 3M4 7c0 2 1.5 3 3.5 3h9c2 0 3.5-1 3.5-3m0 5c0 2 1.5 3 3.5 3h9c2 0 3.5-1 3.5-3"/></svg>',
        'group' => 'resellerhub',
        'position' => 32,
    ],
    [
        'title' => 'مدیریت پکیج‌ها',
        'route' => 'reseller.packages.index',
        'permission' => 'resellerhub.packages.view',
        'show' => function () {
            $user = auth()->user();
            if (!$user) return false;
            if (app(TenantManager::class)->hasTenant()) return false;
            return method_exists($user, 'hasRole') && ($user->hasRole('reseller') || $user->hasRole('super-admin') || $user->can('resellerhub.packages.view'));
        },
        'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline><line x1="12" y1="22.08" x2="12" y2="12"></line></svg>',
        'group' => 'resellerhub',
        'position' => 33,
    ],
    [
        'title' => 'مدیریت فاکتورها',
        'route' => 'reseller.invoices.index',
        'permission' => 'resellerhub.invoices.view',
        'show' => function () {
            $user = auth()->user();
            if (!$user) return false;
            if (app(TenantManager::class)->hasTenant()) return false;
            return method_exists($user, 'hasRole') && ($user->hasRole('reseller') || $user->hasRole('super-admin') || $user->can('resellerhub.invoices.view'));
        },
        'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>',
        'group' => 'resellerhub',
        'position' => 34,
    ],
    [
        'title' => 'مدیریت سفارشات',
        'route' => 'reseller.orders.index',
        'permission' => 'resellerhub.orders.view',
        'show' => function () {
            $user = auth()->user();
            if (!$user) return false;
            if (app(TenantManager::class)->hasTenant()) return false;
            return method_exists($user, 'hasRole') && ($user->hasRole('reseller') || $user->hasRole('super-admin') || $user->can('resellerhub.orders.view'));
        },
        'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg>',
        'group' => 'resellerhub',
        'position' => 35,
    ],
    [
        'title' => 'تعرفه ماژول‌ها و امکانات',
        'route' => 'reseller.pricings.index',
        'permission' => 'resellerhub.pricings.view',
        'show' => function () {
            $user = auth()->user();
            if (!$user) return false;
            if (app(TenantManager::class)->hasTenant()) return false;
            return method_exists($user, 'hasRole') && ($user->hasRole('reseller') || $user->hasRole('super-admin') || $user->can('resellerhub.pricings.view'));
        },
        'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>',
        'group' => 'resellerhub',
        'position' => 36,
    ],
    [
        'title' => 'تنظیمات نمایندگی',
        'route' => 'reseller.settings.index',
        'permission' => 'resellerhub.settings.manage',
        'show' => function () {
            $user = auth()->user();
            if (!$user) return false;
            if (app(TenantManager::class)->hasTenant()) return false;
            return method_exists($user, 'hasRole') && ($user->hasRole('reseller') || $user->hasRole('super-admin') || $user->can('resellerhub.settings.manage') || $user->can('resellerhub.branding'));
        },
        'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>',
        'group' => 'resellerhub',
        'position' => 37,
    ],
    [
        'title' => 'اشتراک و نماینده من',
        'route' => 'tenant.subscription',
        'permission' => null,
        'show' => function () {
            $user = auth()->user();
            if (!$user) return false;
            return app(TenantManager::class)->hasTenant() && ((method_exists($user, 'hasRole') && $user->hasRole('admin')) || !empty($user->tenant_id));
        },
        'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/></svg>',
        'group' => 'subscription',
        'position' => 95,
    ],
];

