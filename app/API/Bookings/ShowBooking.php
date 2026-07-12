<?php

namespace App\API\Bookings;

use App\API\Concerns\AuthorizesDashboardTenant;
use App\Http\Resources\BookingResource;
use App\Models\Booking;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Shows a single booking for the authenticated user's current tenant.
 */
class ShowBooking
{
    use AsAction;
    use AuthorizesDashboardTenant;

    public function handle(Tenant $tenant, int $bookingId): Booking
    {
        setCurrentTenant($tenant);

        $booking = Booking::query()
            ->with([
                'client:id,uuid,name,email,phone,meta',
                'content:id,title,type',
                'calendar:id,name,type',
                'order:id,uuid,number',
            ])
            ->where('tenant_id', $tenant->id)
            ->whereKey($bookingId)
            ->first();

        if (! $booking instanceof Booking) {
            throw (new ModelNotFoundException)->setModel(Booking::class, [$bookingId]);
        }

        return $booking;
    }

    public function asController(ActionRequest $request, int $booking): Booking
    {
        return $this->handle($this->currentDashboardTenant($request), $booking);
    }

    public function jsonResponse(Booking $booking): BookingResource
    {
        return new BookingResource($booking);
    }
}
