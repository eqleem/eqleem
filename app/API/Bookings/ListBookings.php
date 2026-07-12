<?php

namespace App\API\Bookings;

use App\API\Concerns\AuthorizesDashboardTenant;
use App\Http\Resources\BookingListResource;
use App\Models\Booking;
use App\Models\Tenant;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
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
            // Calendar view needs a wider page size for a month of bookings.
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:200'],
            'status' => ['sometimes', 'nullable', 'string', Rule::in(array_keys(Booking::statuses()))],
            'from' => ['sometimes', 'nullable', 'date'],
            'to' => ['sometimes', 'nullable', 'date', 'after_or_equal:from'],
        ];
    }

    /**
     * @return LengthAwarePaginator<int, Booking>
     */
    public function handle(
        Tenant $tenant,
        ?string $search = null,
        ?string $status = null,
        int $perPage = 20,
        ?string $from = null,
        ?string $to = null,
    ): LengthAwarePaginator {
        setCurrentTenant($tenant);

        $query = Booking::query()
            ->with([
                'client:id,name,email,phone',
                'content:id,title,type',
                'calendar:id,name,type',
                'order:id,uuid,number',
            ])
            ->orderByDesc('bookings.start_at')
            ->orderByDesc('bookings.id');

        $this->applyStatusFilter($query, $status);
        $this->applySearch($query, $search);
        $this->applyDateRange($query, $from, $to);

        /** @var LengthAwarePaginator<int, Booking> $bookings */
        $bookings = $query->paginate($perPage);

        return $bookings;
    }

    public function asController(ActionRequest $request): LengthAwarePaginator
    {
        /** @var array{search?: string|null, status?: string|null, per_page?: int, from?: string|null, to?: string|null} $validated */
        $validated = $request->validated();

        $status = isset($validated['status']) ? trim((string) $validated['status']) : null;
        $from = isset($validated['from']) ? trim((string) $validated['from']) : null;
        $to = isset($validated['to']) ? trim((string) $validated['to']) : null;

        return $this->handle(
            $this->currentDashboardTenant($request),
            isset($validated['search']) ? trim((string) $validated['search']) : null,
            $status !== '' ? $status : null,
            (int) ($validated['per_page'] ?? 20),
            $from !== '' ? $from : null,
            $to !== '' ? $to : null,
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

    private function applyDateRange(Builder $query, ?string $from, ?string $to): void
    {
        if ($from === null && $to === null) {
            return;
        }

        // Inclusive overlap: booking intersects [from, to].
        if ($from !== null) {
            $query->where('bookings.end_at', '>=', $from);
        }

        if ($to !== null) {
            $query->where('bookings.start_at', '<=', $to);
        }
    }
}
