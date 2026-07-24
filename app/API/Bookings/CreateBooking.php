<?php

namespace App\API\Bookings;

use App\API\Concerns\AuthorizesDashboardTenant;
use App\Http\Resources\BookingListResource;
use App\Models\Booking;
use App\Models\Calendar;
use App\Models\Client;
use App\Models\Content;
use App\Models\Order;
use App\Models\Tenant;
use App\Services\CalendarSlotService;
use App\Support\Money;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Creates a manual booking (and linked draft order) for the current dashboard tenant.
 */
class CreateBooking
{
    use AsAction;
    use AuthorizesDashboardTenant;

    /**
     * @return list<string>
     */
    public function getControllerMiddleware(): array
    {
        return [
            'auth:sanctum',
            'throttle:30,1',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'client_id' => ['nullable', 'integer'],
            'type' => ['required', 'string', Rule::in(Order::bookingItemTypes())],
            'content_id' => ['required', 'integer'],
            'calendar_id' => ['required', 'integer'],
            'start_at' => ['required', 'date'],
            'end_at' => ['required', 'date', 'after:start_at'],
            'status' => ['sometimes', 'string', Rule::in(array_keys(Booking::statuses()))],
        ];
    }

    /**
     * @param  array{
     *     client_id?: int|null,
     *     type: string,
     *     content_id: int,
     *     calendar_id: int,
     *     start_at: string,
     *     end_at: string,
     *     status?: string
     * }  $data
     */
    public function handle(Tenant $tenant, array $data, ?int $createdBy = null): Booking
    {
        setCurrentTenant($tenant);

        $type = (string) $data['type'];
        $content = $this->resolveContent($tenant, (int) $data['content_id'], $type);
        $calendar = $this->resolveCalendar($tenant, $content, (int) $data['calendar_id']);
        $clientId = $this->resolveClientId($tenant, $data['client_id'] ?? null);

        $startAt = Carbon::parse($data['start_at']);
        $endAt = Carbon::parse($data['end_at']);
        $status = (string) ($data['status'] ?? 'new');

        $this->assertSlotAvailable($calendar, $content, $type, $startAt, $endAt);

        $priceMinor = (int) (data_get($content->data, 'price') ?: $content->price ?: 0);
        $currency = Money::defaultCurrencyCode();

        return DB::transaction(function () use ($tenant, $clientId, $content, $calendar, $type, $startAt, $endAt, $status, $priceMinor, $currency, $createdBy): Booking {
            $order = Order::query()->create([
                'tenant_id' => $tenant->id,
                'type' => 'order',
                'status' => 'draft',
                'channel' => 'manual',
                'number' => $this->generateOrderNumber($tenant->id),
                'client_id' => $clientId,
                'currency_code' => $currency,
                'subtotal' => $priceMinor,
                'discount_total' => 0,
                'tax_total' => 0,
                'grand_total' => $priceMinor,
                'paid_total' => 0,
                'due_total' => $priceMinor,
                'payment_status' => 'unpaid',
                'issued_at' => now(),
                'created_by' => $createdBy,
                'notes' => null,
                'financial_status' => 'draft',
                'fulfillment_status' => 'unfulfilled',
                'meta' => [
                    'payment_method' => 'cash',
                    'source' => 'dashboard_booking',
                ],
            ]);

            $booking = Booking::query()->create([
                'tenant_id' => $tenant->id,
                'client_id' => $clientId,
                'order_id' => $order->id,
                'content_id' => $content->id,
                'calendar_id' => $calendar->id,
                'start_at' => $startAt,
                'end_at' => $endAt,
                'status' => $status,
                'price_snapshot' => Order::fromMinor($priceMinor),
                'currency' => $currency,
                'meta' => [
                    'order_channel' => 'manual',
                    'created_from' => 'dashboard_bookings',
                ],
            ]);

            DB::table('order_items')->insert([
                'order_id' => $order->id,
                'product_id' => $content->id,
                'booking_id' => $booking->id,
                'name' => $content->title,
                'qty' => 1,
                'unit_price' => $priceMinor,
                'discount_total' => 0,
                'tax_total' => 0,
                'line_total' => $priceMinor,
                'meta' => json_encode([
                    'type' => $type,
                    'booking_id' => $booking->id,
                    'calendar_id' => $calendar->id,
                    'booking_start_at' => $startAt->toDateTimeString(),
                    'booking_end_at' => $endAt->toDateTimeString(),
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $booking->setRelation('order', $order);
            $booking->load(['client:id,name', 'content:id,title,type', 'calendar:id,name,type']);

            return $booking;
        });
    }

    public function asController(ActionRequest $request): Booking
    {
        /** @var array{
         *     client_id?: int|null,
         *     type: string,
         *     content_id: int,
         *     calendar_id: int,
         *     start_at: string,
         *     end_at: string,
         *     status?: string
         * } $validated */
        $validated = $request->validated();

        return $this->handle(
            $this->currentDashboardTenant($request),
            $validated,
            $request->user()?->id,
        );
    }

    public function jsonResponse(Booking $booking): BookingListResource
    {
        return (new BookingListResource($booking))->additional([
            'message' => 'تم إنشاء الحجز بنجاح.',
        ]);
    }

    private function resolveContent(Tenant $tenant, int $contentId, string $type): Content
    {
        $contentType = match ($type) {
            'service' => contentTypeModel('services'),
            'unit_rental' => contentTypeModel('unit-rental'),
            default => null,
        };

        $content = Content::query()
            ->whereKey($contentId)
            ->where('tenant_id', $tenant->id)
            ->when($contentType !== null, fn ($query) => $query->where('type', $contentType))
            ->first();

        if (! $content instanceof Content) {
            throw ValidationException::withMessages([
                'content_id' => [__('The selected content is invalid.')],
            ]);
        }

        if ($content->orderItemType() !== $type) {
            throw ValidationException::withMessages([
                'type' => [__('The selected content type does not match.')],
            ]);
        }

        return $content;
    }

    private function resolveCalendar(Tenant $tenant, Content $content, int $calendarId): Calendar
    {
        $calendar = $content->calendars()
            ->where('calendars.id', $calendarId)
            ->where('calendars.tenant_id', $tenant->id)
            ->where('calendars.active', true)
            ->first();

        if (! $calendar instanceof Calendar) {
            throw ValidationException::withMessages([
                'calendar_id' => [__('The selected calendar is invalid.')],
            ]);
        }

        return $calendar;
    }

    private function resolveClientId(Tenant $tenant, mixed $clientId): ?int
    {
        if ($clientId === null || $clientId === '') {
            return null;
        }

        $id = (int) $clientId;

        $exists = Client::withoutGlobalScope('tenantable')
            ->whereKey($id)
            ->whereHas('tenants', fn ($query) => $query->where('tenants.id', $tenant->id))
            ->exists();

        if (! $exists) {
            throw ValidationException::withMessages([
                'client_id' => [__('The selected client is invalid.')],
            ]);
        }

        return $id;
    }

    private function assertSlotAvailable(
        Calendar $calendar,
        Content $content,
        string $type,
        Carbon $startAt,
        Carbon $endAt,
    ): void {
        if ($type === 'unit_rental') {
            $conflict = Booking::query()
                ->where('calendar_id', $calendar->id)
                ->where('status', '!=', 'cancelled')
                ->where('start_at', '<', $endAt)
                ->where('end_at', '>', $startAt)
                ->exists();

            if ($conflict) {
                throw ValidationException::withMessages([
                    'start_at' => ['فترة التأجير غير متاحة.'],
                ]);
            }

            return;
        }

        $durationMinutes = max(1, (int) data_get($content->data, 'duration_minutes', 60));
        $bookingDate = $startAt->toDateString();

        $slot = collect(app(CalendarSlotService::class)->availableTimeSlots(
            $calendar,
            $bookingDate,
            $durationMinutes,
            'slot',
        ))->first(fn (array $candidate): bool => ($candidate['start_at'] ?? '') === $startAt->toDateTimeString()
            && ($candidate['end_at'] ?? '') === $endAt->toDateTimeString()
            && ($candidate['available'] ?? false));

        if (! is_array($slot)) {
            throw ValidationException::withMessages([
                'start_at' => ['الوقت المحدد غير متاح.'],
            ]);
        }
    }

    private function generateOrderNumber(int $tenantId): string
    {
        $lastId = Order::query()
            ->where('tenant_id', $tenantId)
            ->where('type', 'order')
            ->orderByDesc('id')
            ->lockForUpdate()
            ->value('id');

        return str_pad((string) (($lastId ?? 0) + 1), 6, '0', STR_PAD_LEFT);
    }
}
