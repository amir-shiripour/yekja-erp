<?php

namespace Modules\ResellerHub\App\Providers;

use App\Tenancy\TenantManager;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\ValidationException;
use Modules\Accounting\Entities\Invoice;
use Modules\Booking\Entities\Appointment;
use Modules\Booking\Entities\BookingServiceProvider;
use Modules\Clients\Entities\Client;
use Modules\Market\App\Models\Order as MarketOrder;
use Modules\Market\Entities\Vendor;
use Modules\Market\Entities\Warehouse;
use Modules\Market\Entities\MasterProduct;
use Modules\ResellerHub\App\Enums\QuotaKey;
use Modules\ResellerHub\App\Services\TenantQuotaService;

class ResellerQuotaServiceProvider extends ServiceProvider
{
    /**
     * Boot quota interceptors for the 4 core modules without modifying any of their code.
     */
    public function boot(): void
    {
        $this->registerClientInterceptors();
        $this->registerBookingInterceptors();
        $this->registerInvoiceInterceptors();
        $this->registerMarketInterceptors();
    }

    /**
     * 1. Intercept Client creations in Modules\Clients
     */
    protected function registerClientInterceptors(): void
    {
        if (class_exists(Client::class)) {
            Client::creating(function ($client) {
                $tenantManager = app(TenantManager::class);
                if (!$tenantManager->hasTenant()) {
                    return;
                }

                $tenant = $tenantManager->getTenant();
                $quotaService = app(TenantQuotaService::class);

                if (!$quotaService->hasRemainingQuota($tenant, QuotaKey::Clients)) {
                    $payload = $quotaService->getExceededQuotaPayload($tenant, QuotaKey::Clients);
                    throw ValidationException::withMessages([
                        QuotaKey::Clients->value => $payload['message'],
                    ]);
                }
            });
        }
    }

    /**
     * 2. Intercept Appointments & Providers in Modules\Booking
     */
    protected function registerBookingInterceptors(): void
    {
        // Intercept Appointment creation
        if (class_exists(Appointment::class)) {
            Appointment::creating(function ($appointment) {
                $tenantManager = app(TenantManager::class);
                if (!$tenantManager->hasTenant()) {
                    return;
                }

                $tenant = $tenantManager->getTenant();
                $quotaService = app(TenantQuotaService::class);

                if (!$quotaService->hasRemainingQuota($tenant, QuotaKey::Appointments)) {
                    $payload = $quotaService->getExceededQuotaPayload($tenant, QuotaKey::Appointments);
                    throw ValidationException::withMessages([
                        QuotaKey::Appointments->value => $payload['message'],
                    ]);
                }
            });
        }

        // Intercept Booking Service Provider (Staff / Doctors)
        if (class_exists(BookingServiceProvider::class)) {
            BookingServiceProvider::creating(function ($provider) {
                $tenantManager = app(TenantManager::class);
                if (!$tenantManager->hasTenant()) {
                    return;
                }

                $tenant = $tenantManager->getTenant();
                $quotaService = app(TenantQuotaService::class);

                if (!$quotaService->hasRemainingQuota($tenant, QuotaKey::BookingProviders)) {
                    $payload = $quotaService->getExceededQuotaPayload($tenant, QuotaKey::BookingProviders);
                    throw ValidationException::withMessages([
                        'provider' => $payload['message'],
                    ]);
                }
            });
        }
    }

    /**
     * 3. Intercept Invoices in Modules\Accounting and Modules\Services
     */
    protected function registerInvoiceInterceptors(): void
    {
        // Accounting Invoices
        if (class_exists(Invoice::class)) {
            Invoice::creating(function ($invoice) {
                $this->enforceInvoiceQuota();
            });
        }

        // Service Invoices
        if (class_exists(\Modules\Services\App\Http\Models\Invoice::class)) {
            \Modules\Services\App\Http\Models\Invoice::creating(function ($invoice) {
                $this->enforceInvoiceQuota();
            });
        }
    }

    protected function enforceInvoiceQuota(): void
    {
        $tenantManager = app(TenantManager::class);
        if (!$tenantManager->hasTenant()) {
            return;
        }

        $tenant = $tenantManager->getTenant();
        $quotaService = app(TenantQuotaService::class);

        if (!$quotaService->hasRemainingQuota($tenant, QuotaKey::Invoices)) {
            $payload = $quotaService->getExceededQuotaPayload($tenant, QuotaKey::Invoices);
            throw ValidationException::withMessages([
                QuotaKey::Invoices->value => $payload['message'],
            ]);
        }
    }

    /**
     * 4. Intercept Store / Vendor creation in Modules\Market (Single vs Multi-Vendor)
     */
    protected function registerMarketInterceptors(): void
    {
        if (class_exists(Vendor::class)) {
            Vendor::creating(function ($vendor) {
                $tenantManager = app(TenantManager::class);
                if (!$tenantManager->hasTenant()) {
                    return;
                }

                $tenant = $tenantManager->getTenant();
                $quotaService = app(TenantQuotaService::class);

                // Multi-Vendor Boolean Check
                $isMultiVendorAllowed = $quotaService->hasFeature($tenant, QuotaKey::MultiVendor, false);
                $existingStoresCount = $quotaService->getUsage($tenant, QuotaKey::Stores);

                if (!$isMultiVendorAllowed && $existingStoresCount >= 1) {
                    $payload = $quotaService->getExceededQuotaPayload($tenant, QuotaKey::Stores);
                    throw ValidationException::withMessages([
                        'vendor' => $payload['message'],
                    ]);
                }

                // Numeric stores limit check if defined
                if (!$quotaService->hasRemainingQuota($tenant, QuotaKey::Stores)) {
                    $payload = $quotaService->getExceededQuotaPayload($tenant, QuotaKey::Stores);
                    throw ValidationException::withMessages([
                        'vendor' => $payload['message'],
                    ]);
                }
            });
        }

        // Intercept Warehouses (WMS)
        if (class_exists(Warehouse::class)) {
            Warehouse::creating(function ($warehouse) {
                $tenantManager = app(TenantManager::class);
                if (!$tenantManager->hasTenant()) {
                    return;
                }

                $tenant = $tenantManager->getTenant();
                $quotaService = app(TenantQuotaService::class);

                if (!$quotaService->hasRemainingQuota($tenant, QuotaKey::Warehouses)) {
                    $payload = $quotaService->getExceededQuotaPayload($tenant, QuotaKey::Warehouses);
                    throw ValidationException::withMessages([
                        'warehouse' => $payload['message'],
                    ]);
                }
            });
        }

        // Intercept Store Orders in Modules\Market
        if (class_exists(MarketOrder::class)) {
            MarketOrder::creating(function ($order) {
                $tenantManager = app(TenantManager::class);
                if (!$tenantManager->hasTenant()) {
                    return;
                }

                $tenant = $tenantManager->getTenant();
                $quotaService = app(TenantQuotaService::class);

                if (!$quotaService->hasRemainingQuota($tenant, QuotaKey::Orders)) {
                    $payload = $quotaService->getExceededQuotaPayload($tenant, QuotaKey::Orders);
                    throw ValidationException::withMessages([
                        QuotaKey::Orders->value => $payload['message'],
                    ]);
                }
            });
        }

        // Intercept Master Products in Modules\Market
        if (class_exists(MasterProduct::class)) {
            MasterProduct::creating(function ($product) {
                $tenantManager = app(TenantManager::class);
                if (!$tenantManager->hasTenant()) {
                    return;
                }

                $tenant = $tenantManager->getTenant();
                $quotaService = app(TenantQuotaService::class);

                if (!$quotaService->hasRemainingQuota($tenant, QuotaKey::Products)) {
                    $payload = $quotaService->getExceededQuotaPayload($tenant, QuotaKey::Products);
                    throw ValidationException::withMessages([
                        QuotaKey::Products->value => $payload['message'],
                    ]);
                }
            });
        }
    }
}
