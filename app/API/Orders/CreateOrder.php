<?php

namespace App\API\Orders;

use App\API\Concerns\AuthorizesDashboardTenant;
use App\Http\Resources\OrderResource;
use App\Models\Booking;
use App\Models\Calendar;
use App\Models\Client;
use App\Models\Content;
use App\Models\Order;
use App\Models\Tenant;
use App\Services\CalendarSlotService;
use App\Support\DashboardStats;
use App\Support\Money;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Creates a manual draft order for the authenticated user's current tenant.
 */
class CreateOrder
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
        $types = array_keys(Order::itemTypeOptions());

        return [
            'client_id' => ['nullable', 'integer'],
            'currency_code' => ['sometimes', 'nullable', 'string', 'size:3'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.type' => ['required', 'string', Rule::in($types)],
            'items.*.name' => ['required', 'string', 'min:1', 'max:255'],
            'items.*.product_id' => ['nullable', 'integer'],
            'items.*.qty' => ['required', 'integer', 'min:1'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.discount' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'items.*.description' => ['nullable', 'string', 'max:1000'],
            'items.*.calendar_id' => ['nullable', 'integer'],
            'items.*.booking_start_at' => ['nullable', 'date'],
            'items.*.booking_end_at' => ['nullable', 'date', 'after:items.*.booking_start_at'],
        ];
    }

    /**
     * @param  array{
     *     client_id?: int|null,
     *     currency_code?: string|null,
     *     items: list<array<string, mixed>>
     * }  $data
     */
    public function handle(Tenant $tenant, array $data, ?int $createdBy = null): Order
    {
        setCurrentTenant($tenant);

        $clientId = $data['client_id'] ?? null;

        if ($clientId !== null) {
            $clientExists = Client::withoutGlobalScope('tenantable')
                ->whereKey($clientId)
                ->whereHas('tenants', fn ($query) => $query->where('tenants.id', $tenant->id))
                ->exists();

            if (! $clientExists) {
                throw ValidationException::withMessages([
                    'client_id' => [__('The selected client is invalid.')],
                ]);
            }
        }

        $items = $this->normalizeItems($tenant, $data['items']);
        $this->assertNoDuplicateBookingSlots($items);
        $totals = Order::calculateTotalsMinor($items);
        $currency = strtoupper((string) ($data['currency_code'] ?? Money::defaultCurrencyCode()));

        $order = DB::transaction(function () use ($tenant, $clientId, $items, $totals, $currency, $createdBy): Order {
            $number = $this->generateOrderNumber($tenant->id);

            $order = Order::query()->create([
                'tenant_id' => $tenant->id,
                'type' => 'order',
                'status' => 'new',
                'channel' => 'manual',
                'number' => $number,
                'client_id' => $clientId,
                'currency_code' => $currency,
                'subtotal' => $totals['subtotal'],
                'discount_total' => $totals['discount_total'],
                'tax_total' => $totals['tax_total'],
                'grand_total' => $totals['grand_total'],
                'paid_total' => 0,
                'due_total' => $totals['grand_total'],
                'payment_status' => 'unpaid',
                'issued_at' => now(),
                'created_by' => $createdBy,
                'notes' => null,
                'financial_status' => 'draft',
                'fulfillment_status' => 'unfulfilled',
                'meta' => [
                    'payment_method' => 'cash',
                ],
            ]);

            foreach ($items as $item) {
                $qty = (int) $item['qty'];
                $unitPrice = Order::minorFromDecimal($item['unit_price']);
                $discount = Order::minorFromDecimal($item['discount'] ?? 0);
                $lineTotal = max(0, ($qty * $unitPrice) - $discount);
                $bookingId = null;

                if (
                    Order::isBookingItemType($item['type'])
                    && filled($item['booking_start_at'] ?? null)
                    && filled($item['booking_end_at'] ?? null)
                ) {
                    $booking = Booking::query()->create([
                        'tenant_id' => $tenant->id,
                        'client_id' => $clientId,
                        'content_id' => $item['product_id'],
                        'calendar_id' => $item['calendar_id'],
                        'start_at' => $item['booking_start_at'],
                        'end_at' => $item['booking_end_at'],
                        'status' => 'new',
                        'price_snapshot' => Order::fromMinor($unitPrice),
                        'currency' => $currency,
                        'meta' => [
                            'order_channel' => 'manual',
                            'created_from' => 'dashboard_orders',
                        ],
                    ]);

                    $bookingId = $booking->id;
                }

                DB::table('order_items')->insert([
                    'order_id' => $order->id,
                    'product_id' => $item['type'] === 'other' ? null : ($item['product_id'] ?? null),
                    'name' => $item['name'],
                    'qty' => $qty,
                    'unit_price' => $unitPrice,
                    'discount_total' => $discount,
                    'tax_total' => 0,
                    'line_total' => $lineTotal,
                    'meta' => json_encode([
                        'type' => $item['type'],
                        'description' => $item['type'] === 'other' ? ($item['description'] ?? $item['name']) : null,
                        'booking_id' => $bookingId,
                        'calendar_id' => $item['calendar_id'] ?? null,
                        'booking_start_at' => $item['booking_start_at'] ?? null,
                        'booking_end_at' => $item['booking_end_at'] ?? null,
                    ]),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            return $order;
        });

        DashboardStats::forget($tenant);

        return app(ShowOrder::class)->handle($tenant, $order->uuid);
    }

    public function asController(ActionRequest $request): Order
    {
        $tenant = $this->currentDashboardTenant($request);

        /** @var array{
         *     client_id?: int|null,
         *     currency_code?: string|null,
         *     items: list<array<string, mixed>>
         * } $validated
         */
        $validated = $request->validated();

        return $this->handle($tenant, $validated, $request->user()?->id);
    }

    public function jsonResponse(Order $order): OrderResource
    {
        return (new OrderResource($order))
            ->additional([
                'message' => __('Order created successfully.'),
            ]);
    }

    /**
     * @param  list<array<string, mixed>>  $items
     * @return list<array{
     *     type: string,
     *     name: string,
     *     product_id: int|null,
     *     qty: int,
     *     unit_price: float|string,
     *     discount: float|string,
     *     description: string|null,
     *     calendar_id: int|null,
     *     booking_start_at: string|null,
     *     booking_end_at: string|null
     * }>
     */
    private function normalizeItems(Tenant $tenant, array $items): array
    {
        $normalized = [];

        foreach ($items as $index => $item) {
            $type = (string) $item['type'];
            $name = trim((string) $item['name']);
            $productId = $item['product_id'] ?? null;
            $qty = max(1, (int) $item['qty']);
            $unitPrice = $item['unit_price'] ?? 0;
            $discount = $item['discount'] ?? 0;
            $description = isset($item['description']) ? trim((string) $item['description']) : null;
            $calendarId = isset($item['calendar_id']) ? (int) $item['calendar_id'] : null;
            $bookingStartAt = filled($item['booking_start_at'] ?? null)
                ? Carbon::parse((string) $item['booking_start_at'])->toDateTimeString()
                : null;
            $bookingEndAt = filled($item['booking_end_at'] ?? null)
                ? Carbon::parse((string) $item['booking_end_at'])->toDateTimeString()
                : null;

            if ($type === 'other') {
                $name = $description !== null && $description !== '' ? $description : $name;
                $productId = null;
            } elseif ($productId === null && $name !== '') {
                $content = $this->findOrCreateContentForItem($tenant, $type, $name, (string) $unitPrice);
                $productId = $content?->id;
            } elseif ($productId !== null) {
                $content = Content::query()
                    ->whereKey($productId)
                    ->where('tenant_id', $tenant->id)
                    ->first();

                if (! $content instanceof Content) {
                    throw ValidationException::withMessages([
                        "items.{$index}.product_id" => [__('The selected content is invalid.')],
                    ]);
                }

                if ($content->status === 'draft') {
                    $priceMinor = Order::minorFromDecimal((string) $unitPrice);
                    $currentPrice = (int) ($content->price ?? 0);

                    if ($priceMinor > 0 && $currentPrice !== $priceMinor) {
                        $content->forceFill(['price' => $priceMinor])->save();
                    }
                }
            }

            if ($name === '') {
                throw ValidationException::withMessages([
                    "items.{$index}.name" => [__('Item name is required.')],
                ]);
            }

            if (Order::isBookingItemType($type)) {
                $qty = 1;
                $this->assertBookingFields($tenant, $index, $type, $productId, $calendarId, $bookingStartAt, $bookingEndAt);
            } else {
                $calendarId = null;
                $bookingStartAt = null;
                $bookingEndAt = null;
            }

            $normalized[] = [
                'type' => $type,
                'name' => $name,
                'product_id' => $productId,
                'qty' => $qty,
                'unit_price' => $unitPrice,
                'discount' => $discount === null || $discount === '' ? 0 : $discount,
                'description' => $type === 'other' ? $name : null,
                'calendar_id' => $calendarId,
                'booking_start_at' => $bookingStartAt,
                'booking_end_at' => $bookingEndAt,
            ];
        }

        return $normalized;
    }

    private function assertBookingFields(
        Tenant $tenant,
        int $index,
        string $type,
        mixed $productId,
        ?int $calendarId,
        ?string $bookingStartAt,
        ?string $bookingEndAt,
    ): void {
        if ($productId === null) {
            throw ValidationException::withMessages([
                "items.{$index}.product_id" => [__('The selected content is invalid.')],
            ]);
        }

        if ($calendarId === null || $calendarId <= 0) {
            throw ValidationException::withMessages([
                "items.{$index}.calendar_id" => ['اختر التقويم / المخزون قبل التأكيد.'],
            ]);
        }

        if ($bookingStartAt === null || $bookingEndAt === null) {
            throw ValidationException::withMessages([
                "items.{$index}.booking_start_at" => ['حدد فترة الحجز قبل التأكيد.'],
            ]);
        }

        $content = Content::query()
            ->whereKey($productId)
            ->where('tenant_id', $tenant->id)
            ->first();

        if (! $content instanceof Content || $content->orderItemType() !== $type) {
            throw ValidationException::withMessages([
                "items.{$index}.product_id" => [__('The selected content is invalid.')],
            ]);
        }

        $calendar = $content->calendars()
            ->where('calendars.id', $calendarId)
            ->where('calendars.tenant_id', $tenant->id)
            ->where('calendars.active', true)
            ->first();

        if (! $calendar instanceof Calendar) {
            throw ValidationException::withMessages([
                "items.{$index}.calendar_id" => [__('The selected calendar is invalid.')],
            ]);
        }

        $startAt = Carbon::parse($bookingStartAt);
        $endAt = Carbon::parse($bookingEndAt);

        if ($endAt->lte($startAt)) {
            throw ValidationException::withMessages([
                "items.{$index}.booking_end_at" => ['نهاية الحجز يجب أن تكون بعد البداية.'],
            ]);
        }

        $this->assertSlotAvailable($index, $calendar, $content, $type, $startAt, $endAt);
    }

    private function assertSlotAvailable(
        int $index,
        Calendar $calendar,
        Content $content,
        string $type,
        Carbon $startAt,
        Carbon $endAt,
    ): void {
        if ($type === 'unit_rental') {
            $availableDates = app(CalendarSlotService::class)->availableDates($calendar);
            $cursor = $startAt->copy()->startOfDay();
            $checkout = $endAt->copy()->startOfDay();

            while ($cursor->lt($checkout)) {
                if (! in_array($cursor->toDateString(), $availableDates, true)) {
                    throw ValidationException::withMessages([
                        "items.{$index}.booking_start_at" => ['التواريخ المحددة غير متاحة للحجز.'],
                    ]);
                }

                $cursor->addDay();
            }

            $conflict = Booking::query()
                ->where('calendar_id', $calendar->id)
                ->where('status', '!=', 'cancelled')
                ->where('start_at', '<', $endAt)
                ->where('end_at', '>', $startAt)
                ->exists();

            if ($conflict) {
                throw ValidationException::withMessages([
                    "items.{$index}.booking_start_at" => ['فترة التأجير غير متاحة.'],
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
                "items.{$index}.booking_start_at" => ['الوقت المحدد غير متاح.'],
            ]);
        }
    }

    /**
     * @param  list<array{
     *     type: string,
     *     calendar_id: int|null,
     *     booking_start_at: string|null,
     *     booking_end_at: string|null
     * }>  $items
     */
    private function assertNoDuplicateBookingSlots(array $items): void
    {
        foreach ($items as $index => $item) {
            if (! Order::isBookingItemType($item['type'])) {
                continue;
            }

            $calendarId = (int) ($item['calendar_id'] ?? 0);
            $startAt = Carbon::parse((string) $item['booking_start_at']);
            $endAt = Carbon::parse((string) $item['booking_end_at']);

            foreach ($items as $otherIndex => $other) {
                if ($otherIndex <= $index || ! Order::isBookingItemType($other['type'])) {
                    continue;
                }

                if ((int) ($other['calendar_id'] ?? 0) !== $calendarId) {
                    continue;
                }

                $otherStart = Carbon::parse((string) $other['booking_start_at']);
                $otherEnd = Carbon::parse((string) $other['booking_end_at']);

                if ($startAt->lt($otherEnd) && $endAt->gt($otherStart)) {
                    throw ValidationException::withMessages([
                        "items.{$otherIndex}.booking_start_at" => ['لا يمكن حجز نفس الفترة مرتين في نفس الطلب.'],
                    ]);
                }
            }
        }
    }

    private function findOrCreateContentForItem(Tenant $tenant, string $orderItemType, string $title, string $unitPriceDecimal = '0'): ?Content
    {
        $contentType = $this->orderItemContentType($orderItemType);

        if ($contentType === null) {
            return null;
        }

        $title = trim($title);

        if ($title === '') {
            return null;
        }

        $existing = Content::query()
            ->where('type', $contentType)
            ->where('title', $title)
            ->first();

        if ($existing instanceof Content) {
            if ($existing->status === 'draft') {
                $priceMinor = Order::minorFromDecimal($unitPriceDecimal);
                $currentPrice = (int) ($existing->price ?? 0);

                if ($priceMinor > 0 && $currentPrice !== $priceMinor) {
                    $existing->forceFill(['price' => $priceMinor])->save();
                }
            }

            return $existing->refresh();
        }

        $data = [];

        if ($orderItemType === 'course') {
            $data = [
                'level' => 'beginner',
                'course_type' => 'recorded',
                'hours' => 0,
                'chapters' => [],
            ];
        }

        if ($orderItemType === 'digital_service') {
            $data['delivery_days'] = null;
        }

        if (Order::isBookingItemType($orderItemType)) {
            $data['duration_minutes'] = 60;
            $data['price'] = Order::minorFromDecimal($unitPriceDecimal);
        }

        return Content::query()->create([
            'tenant_id' => $tenant->id,
            'type' => $contentType,
            'title' => $title,
            'slug' => $this->uniqueContentSlug($title, $orderItemType),
            'status' => 'draft',
            'active' => true,
            'price' => Order::minorFromDecimal($unitPriceDecimal),
            'data' => $data === [] ? null : $data,
        ]);
    }

    private function uniqueContentSlug(string $title, string $orderItemType): string
    {
        $baseSlug = Str::slug($title);
        $fallback = match ($orderItemType) {
            'product' => 'product',
            'digital_product' => 'digital-product',
            'service' => 'service',
            'course' => 'course',
            'digital_service' => 'digital-service',
            'menu' => 'menu-item',
            'unit_rental' => 'unit',
            default => 'item',
        };
        $slug = $baseSlug !== '' ? $baseSlug : $fallback;
        $counter = 1;

        while (Content::query()->where('slug', $slug)->exists()) {
            $slug = ($baseSlug !== '' ? $baseSlug : $fallback).'-'.$counter;
            $counter++;
        }

        return $slug;
    }

    private function orderItemContentType(string $type): ?string
    {
        return match ($type) {
            'product' => contentTypeModel('store'),
            'digital_product' => contentTypeModel('digital-products'),
            'service' => contentTypeModel('services'),
            'course' => contentTypeModel('courses'),
            'digital_service' => contentTypeModel('digital-services'),
            'menu' => contentTypeModel('menu'),
            'unit_rental' => contentTypeModel('unit-rental'),
            default => null,
        };
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
