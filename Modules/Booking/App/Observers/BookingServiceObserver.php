<?php

namespace Modules\Booking\App\Observers;

use Modules\Booking\Entities\BookingService;

class BookingServiceObserver
{
    /**
     * Handle the BookingService "saved" event.
     * In solo clinics, solo lawyers, single operators, or when no specialist is assigned,
     * automatically ensures the service has an active default provider (the tenant manager/admin).
     */
    public function saved(BookingService $service): void
    {
        // If request explicitly provided selected provider IDs, controller is syncing them
        if (request()->has('provider_ids') && !empty(array_filter((array) request()->input('provider_ids')))) {
            return;
        }

        $service->ensureDefaultProvider();
    }
}
