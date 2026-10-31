<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Dynamically Scoped Tenant Models (Zero-Touch Tenancy)
    |--------------------------------------------------------------------------
    |
    | Models listed here will have TenantScope, automatic tenant_id injection,
    | and anti-tampering guards applied dynamically at runtime.
    | They do NOT need `use BelongsToTenant;` in their source files.
    |
    */
    'models' => [
        'Modules\\Booking\\Entities\\BookingService',
        'Modules\\Booking\\Entities\\Appointment',
        'Modules\\Booking\\Entities\\BookingCategory',
        'Modules\\Booking\\Entities\\BookingForm',
        'Modules\\Booking\\Entities\\BookingPayment',
        'Modules\\Booking\\Entities\\BookingServiceProvider',
        'Modules\\Booking\\Entities\\BookingSetting',
        'Modules\\Clients\\Entities\\Client',
        'Modules\\Clients\\Entities\\ClientForm',
        'Modules\\Clients\\Entities\\ClientSetting',
        'Modules\\Services\\App\\Http\\Models\\Invoice',
        'Modules\\Services\\App\\Http\\Models\\InvoiceItem',
        'Modules\\Services\\App\\Http\\Models\\Order',
        'Modules\\Services\\App\\Http\\Models\\Payment',
        'Modules\\Services\\App\\Http\\Models\\Service',
        'Modules\\Services\\App\\Http\\Models\\ServiceCategory',
        'Modules\\Services\\App\\Http\\Models\\ServicePackage',
        'Modules\\Market\\App\\Models\\Order',
        'Modules\\Market\\Entities\\MarketSetting',
        'Modules\\Market\\Entities\\MasterProduct',
        'Modules\\Market\\Entities\\Vendor',
        'Modules\\Market\\Entities\\Warehouse',
        'Modules\\Accounting\\Entities\\Invoice',
    ],
];
