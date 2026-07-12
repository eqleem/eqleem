<?php

namespace App\API\Bookings;

use App\API\Concerns\AuthorizesDashboardTenant;
use App\Http\Resources\BookingListResource;
use App\Models\Booking;
use App\Models\Tenant;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Lists bookings for the authenticated user's current tenant (dashboard table).
 */
class ListBookings
{
    use AsAction;
    use AuthorizesDashboardTenant;

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            ...$this->listQueryRules(),
            'status' => ['sometimes', 'nullable', 'string', Rule::in(array_keys(Booking::statuses()))],
        ];
    }

    /**
     * @return LengthAwarePaginator<int, Booking>
     */
    public function handle(Tenant $tenant, ?string $search = null, ?string $status = null, int $perPage = 20): LengthAwarePaginator
    {
        setCurrentTenant($tenant);

        $query = Booking::query()
            ->with([
                'client:id,name',
                'content:id,title,type',
                'calendar:id,name,type',
            ])
            ->orderByDesc('bookings.start_at')
            ->orderByDesc('bookings.id');

        $this->applyStatusFilter($query, $status);
        $this->applySearch($query, $search);

        /** @var LengthAwarePaginator<int, Booking> $bookings */
        $bookings = $query->paginate($perPage);

        $this->attachOrderReferences($bookings->getCollection(), $tenant->id);

        return $bookings;
    }

    public function asController(ActionRequest $request): LengthAwarePaginator
    {
        /** @var array{search?: string|null, status?: string|null, per_page?: int} $validated */
        $validated = $request->validated();

        $status = isset($validated['status']) ? trim((string) $validated['status']) : null;

        return $this->handle(
            $this->currentDashboardTenant($request),
            isset($validated['search']) ? trim((string) $validated['search']) : null,
            $status !== '' ? $status : null,
            (int) ($validated['per_page'] ?? 20),
        );
    }

    public function jsonResponse(LengthAwarePaginator $bookings): AnonymousResourceCollection
    {
        return BookingListResource::collection($bookings);
    }

    private function applyStatusFilter(Builder $query, ?string $status): void
    {
        if ($status === null || $status === '') {
            return;
        }

        if ($status === 'new') {
            $query->whereIn('bookings.status', ['new', 'pending']);

            return;
        }

        $query->where('bookings.status', $status);
    }

    private function applySearch(Builder $query, ?string $search): void
    {
        if ($search === null || $search === '') {
            return;
        }

        $term = '%'.$search.'%';

        $query->where(function (Builder $query) use ($term): void {
            $query->whereHas('client', function (Builder $clients) use ($term): void {
                $clients->where('name', 'like', $term)
                    ->orWhere('email', 'like', $term)
                    ->orWhere('phone', 'like', $term);
            })
                ->orWhereHas('content', function (Builder $contents) use ($term): void {
                    $contents->where('title', 'like', $term);
                })
                ->orWhereHas('calendar', function (Builder $calendars) use ($term): void {
                    $calendars->where('name', 'like', $term);
                })
                ->orWhere('status', 'like', $term);
        });
    }

    /**
     * @param  Collection<int, Booking>  $bookings
     */
    private function attachOrderReferences(Collection $bookings, int $tenantId): void
    {
        if ($bookings->isEmpty()) {
            return;
        }

        $bookingIds = $bookings->pluck('id')->all();

        $rows = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.tenant_id', $tenantId)
            ->whereNotNull('order_items.meta')
            ->orderByDesc('order_items.id')
            ->get([
                'orders.uuid as order_uuid',
                'orders.number as order_number',
                'order_items.meta',
            ]);

        $byBookingId = [];

        foreach ($rows as $row) {
            $meta = json_decode((string) $row->meta, true);

            if (! is_array($meta)) {
                continue;
            }

            $bookingId = isset($meta['booking_id']) ? (int) $meta['booking_id'] : 0;

            if ($bookingId <= 0 || ! in_array($bookingId, $bookingIds, true) || isset($byBookingId[$bookingId])) {
                continue;
            }

            $byBookingId[$bookingId] = [
                'order_uuid' => $row->order_uuid,
                'order_number' => $row->order_number,
            ];
        }

        foreach ($bookings as $booking) {
            $ref = $byBookingId[$booking->id] ?? null;
            $booking->setAttribute('order_uuid', $ref['order_uuid'] ?? null);
            $booking->setAttribute('order_number', $ref['order_number'] ?? null);
        }
    }
}
