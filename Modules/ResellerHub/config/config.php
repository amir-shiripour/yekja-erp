<?php

return [
    'name' => 'ResellerHub',

    /*
    |--------------------------------------------------------------------------
    | Server & Network Infrastructure
    |--------------------------------------------------------------------------
    | Server IP and central CNAME target used for tenant custom domain verification.
    */
    'server_ip' => env('SERVER_IP', null),
    'cname_target' => env('CENTRAL_CNAME_TARGET', null),

    /*
    |--------------------------------------------------------------------------
    | Schema Cache & Versioning
    |--------------------------------------------------------------------------
    | Version prefix and TTL for schema introspection checks to prevent forever-stale caches.
    */
    'schema_cache' => [
        'version' => env('RESELLERHUB_SCHEMA_CACHE_VERSION', 'v1'),
        'ttl_seconds' => env('RESELLERHUB_SCHEMA_CACHE_TTL', 86400),
    ],

    /*
    |--------------------------------------------------------------------------
    | Sequence Prefixes & Numbering
    |--------------------------------------------------------------------------
    | Fallback prefixes for order and invoice numbers when the reseller has not
    | configured their own in reseller settings. `invoice_prefix` deliberately
    | keeps the historical 'INV-' default so existing sequences stay contiguous.
    */
    'order_prefix' => env('RESELLERHUB_ORDER_PREFIX', 'RORD'),
    'invoice_prefix' => env('RESELLERHUB_INVOICE_PREFIX', 'INV-'),

    /*
    |--------------------------------------------------------------------------
    | Security & Authentication
    |--------------------------------------------------------------------------
    | SSO token time-to-live in seconds (strictly short-lived, session-bound).
    */
    'sso' => [
        'token_ttl_seconds' => env('RESELLERHUB_SSO_TTL', 120),
        'rate_limit' => env('RESELLERHUB_SSO_RATE_LIMIT', 20), // per minute
    ],

    /*
    |--------------------------------------------------------------------------
    | Entitlements & Quotas
    |--------------------------------------------------------------------------
    | Threshold percentages for tenant resource usage alarms and UI badges, plus
    | the TTL (seconds) for cached per-tenant usage counters. A shorter TTL means
    | fresher counts at the cost of more queries on quota-heavy pages.
    */
    'quotas' => [
        'warning_threshold' => 75,
        'critical_threshold' => 90,
        'cache_ttl_seconds' => env('RESELLERHUB_QUOTA_CACHE_TTL', 30),
    ],

    /*
    |--------------------------------------------------------------------------
    | Workflows & Event Processing
    |--------------------------------------------------------------------------
    | Toggle workflow automation triggers on domain events.
    */
    'workflows' => [
        'enabled' => env('RESELLERHUB_ENABLE_WORKFLOWS', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Audit & Observability
    |--------------------------------------------------------------------------
    | Log channel and audit logging behavior.
    */
    'logging' => [
        'channel' => env('RESELLERHUB_LOG_CHANNEL', 'daily'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Package Comparison & Module Presentation Priorities
    |--------------------------------------------------------------------------
    | Strategic buyer-oriented priority weighting for public comparison matrices.
    | Core customer & financial modules appear first, followed by scheduling,
    | inventory, communication, and infrastructure tools.
    */
    'comparison_module_priorities' => [
        'crm' => 10,
        'clients' => 20,
        'contacts' => 30,
        'clientcalls' => 40,
        'followups' => 50,
        'sales' => 60,
        'invoices' => 70,
        'contracts' => 80,
        'accounting' => 90,
        'wallet' => 100,
        'appointments' => 110,
        'booking' => 120,
        'reminders' => 130,
        'services' => 140,
        'products' => 150,
        'inventory' => 160,
        'sms' => 170,
        'smsreseller' => 180,
        'notifications' => 190,
        'smartbot' => 200,
        'tasks' => 210,
        'projects' => 220,
        'workflows' => 230,
        'market' => 240,
        'contentforge' => 250,
        'domainmanager' => 260,
        'directadmin' => 270,
    ],
];
