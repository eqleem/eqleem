<?php

namespace App\Http\Resources;

use App\Models\Booking;
use App\Models\Calendar;
use App\Models\Order;
use App\Support\Money;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Full order detail for the dashboard.
 *
 * @mixin Order
 */
class OrderResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var Order $order */
        $order = $this->resource;
        $issuedAt = $order->issued_at ?? $order->created_at;
        $status = $order->statusValue();
        $paymentStatus = (string) $order->payment_status;
        $client = $order->client;

        return [
            'id' => $order->id,
            'uuid' => $order->uuid,
            'number' => $order->number ?? (string) $order->id,
            'status' => $status,
            'status_label' => Order::statusLabelFor($status),
            'status_color' => Order::statusBadgeColorFor($status),
            'payment_status' => $paymentStatus,
            'payment_status_label' => $order->paymentStatusLabel(),
            'payment_status_color' => $order->paymentStatusBadgeColor(),
            'channel' => $order->channel,
            'channel_label' => $order->channelLabel(),
            'payment_method_label' => $order->paymentMethodLabel(),
            'shipping_method_label' => $order->shippingMethodLabel(),
            'shipping_address' => $order->shippingAddressLabel(),
            'tracking_number' => data_get($order->meta, 'tracking_number'),
            'notes' => $order->notes,
            'currency_code' => $order->currency_code,
            'subtotal' => (int) $order->subtotal,
            'subtotal_formatted' => Money::formatWithCurrency($order->subtotal, $order->currency_code),
            'discount_total' => (int) $order->discount_total,
            'discount_total_formatted' => Money::formatWithCurrency($order->discount_total, $order->currency_code),
            'tax_total' => (int) $order->tax_total,
            'tax_total_formatted' => Money::formatWithCurrency($order->tax_total, $order->currency_code),
            'shipping_fee' => $order->shippingFee(),
            'shipping_fee_formatted' => Money::formatWithCurrency($order->shippingFee(), $order->currency_code),
            'grand_total' => (int) $order->grand_total,
            'grand_total_formatted' => Money::formatWithCurrency($order->grand_total, $order->currency_code),
            'paid_total' => (int) $order->paid_total,
            'paid_total_formatted' => Money::formatWithCurrency($order->paid_total, $order->currency_code),
            'due_total' => (int) $order->due_total,
            'due_total_formatted' => Money::formatWithCurrency($order->due_total, $order->currency_code),
            'created' => $issuedAt?->locale(app()->getLocale())->diffForHumans(),
            'issued_at' => $issuedAt?->toIso8601String(),
            'client' => $client ? [
                'id' => $client->id,
                'uuid' => $client->uuid,
                'name' => $client->name,
                'email' => $client->email,
                'phone' => $client->phone,
                'avatar' => $this->clientAvatar($client->id, data_get($client->meta, 'avatar')),
            ] : null,
            'items' => $this->itemsPayload($order),
            'payments' => $order->relationLoaded('payments')
                ? $order->payments->map(fn ($payment) => [
                    'id' => $payment->id,
                    'uuid' => $payment->uuid,
                    'method' => $payment->sourceTypeLabel(),
                    'status' => $payment->resolvedStatus(),
                    'status_label' => $payment->statusLabel(),
                    'status_color' => $payment->statusBadgeColor(),
                    'amount' => (int) $payment->amount,
                    'amount_formatted' => Money::formatWithCurrency($payment->amount, $payment->currency),
                    'currency' => $payment->currency,
                    'created' => $payment->created_at?->translatedFormat('d M Y h:i A'),
                ])->values()->all()
                : [],
            'activity' => $this->activityPayload($order),
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function itemsPayload(Order $order): array
    {
        $rows = DB::table('order_items')
            ->where('order_id', $order->id)
            ->orderBy('id')
            ->get(['id', 'name', 'qty', 'unit_price', 'discount_total', 'line_total', 'sku', 'booking_id', 'meta']);

        $metas = $rows->mapWithKeys(function (object $item): array {
            $meta = is_string($item->meta ?? null)
                ? (json_decode($item->meta, true) ?: [])
                : (array) ($item->meta ?? []);

            return [$item->id => $meta];
        });

        $bookingIds = $rows->pluck('booking_id')->filter()->unique()->values();

        [$bookings, $calendars] = $this->loadBookingLookups($metas, $bookingIds);

        return $rows->map(function (object $item) use ($metas, $bookings, $calendars): array {
            $meta = $metas->get($item->id, []);
            $type = (string) ($meta['type'] ?? 'other');
            $isBooking = Order::isBookingItemType($type);
            $bookingId = filled($item->booking_id ?? null) ? (int) $item->booking_id : null;
            $booking = $bookingId ? $bookings->get($bookingId) : null;

            $startAt = $booking?->start_at
                ?? (filled($meta['booking_start_at'] ?? null) ? Carbon::parse($meta['booking_start_at']) : null);
            $endAt = $booking?->end_at
                ?? (filled($meta['booking_end_at'] ?? null) ? Carbon::parse($meta['booking_end_at']) : null);

            $calendarId = $booking?->calendar_id ?? ($meta['calendar_id'] ?? null);
            $calendar = $booking?->calendar
                ?? ($calendarId ? $calendars->get((int) $calendarId) : null);
            $bookingStatus = $booking?->status
                ? Booking::normalizeStatus((string) $booking->status)
                : null;

            return [
                'id' => $item->id,
                'name' => $item->name,
                'sku' => $item->sku,
                'type' => $type,
                'type_label' => Order::itemTypeOptions()[$type] ?? $type,
                'type_color' => match ($type) {
                    'product', 'digital_service', 'unit_rental' => 'blue',
                    'digital_product', 'course' => 'purple',
                    'menu' => 'yellow',
                    'service' => 'green',
                    default => 'gray',
                },
                'qty' => (int) $item->qty,
                'unit_price' => (int) $item->unit_price,
                'unit_price_formatted' => Money::formatWithCurrency($item->unit_price),
                'discount' => (int) $item->discount_total,
                'discount_formatted' => Money::formatWithCurrency($item->discount_total),
                'line_total' => (int) $item->line_total,
                'line_total_formatted' => Money::formatWithCurrency($item->line_total),
                'description' => filled($meta['description'] ?? null) ? (string) $meta['description'] : null,
                'image_url' => $meta['image_url'] ?? null,
                'is_booking' => $isBooking,
                'booking' => $isBooking ? [
                    'calendar_id' => $calendar?->id ?? ($calendarId ? (int) $calendarId : null),
                    'calendar_name' => $calendar?->name,
                    'calendar_label' => $type === 'unit_rental' ? 'مخزون الوحدات' : 'مقدم الخدمة',
                    'date_label' => $startAt?->copy()->locale(app()->getLocale())->translatedFormat('l j F Y'),
                    'time_label' => $type === 'service' ? $this->bookingTimeLabel($startAt, $endAt) : null,
                    'dates_label' => $type === 'unit_rental' ? $this->bookingDatesLabel($startAt, $endAt) : null,
                    'duration_label' => $type === 'unit_rental' ? $this->bookingDurationLabel($startAt, $endAt) : null,
                    'status' => $bookingStatus,
                    'status_label' => $bookingStatus
                        ? Booking::statusLabelFor($bookingStatus)
                        : null,
                    'status_color' => $bookingStatus
                        ? Booking::statusBadgeColorFor($bookingStatus)
                        : null,
                ] : null,
            ];
        })->all();
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $metas
     * @param  Collection<int, mixed>  $bookingIds
     * @return array{0: Collection<int, Booking>, 1: Collection<int, Calendar>}
     */
    private function loadBookingLookups(Collection $metas, Collection $bookingIds): array
    {
        $bookings = $bookingIds->isEmpty()
            ? collect()
            : Booking::query()
                ->with('calendar:id,name,type')
                ->whereIn('id', $bookingIds)
                ->get()
                ->keyBy('id');

        $calendarIds = $metas->pluck('calendar_id')
            ->filter()
            ->unique()
            ->diff($bookings->pluck('calendar_id')->filter())
            ->values();

        $calendars = $calendarIds->isEmpty()
            ? collect()
            : Calendar::query()
                ->whereIn('id', $calendarIds)
                ->get(['id', 'name', 'type'])
                ->keyBy('id');

        return [$bookings, $calendars];
    }

    private function bookingTimeLabel(?CarbonInterface $startAt, ?CarbonInterface $endAt): ?string
    {
        if ($startAt === null) {
            return null;
        }

        $start = $startAt->copy()->locale(app()->getLocale());

        if ($endAt !== null) {
            $end = $endAt->copy()->locale(app()->getLocale());

            return $start->format('H:i').' – '.$end->format('H:i');
        }

        return $start->format('H:i');
    }

    private function bookingDatesLabel(?CarbonInterface $startAt, ?CarbonInterface $endAt): ?string
    {
        if ($startAt === null || $endAt === null) {
            return null;
        }

        $locale = app()->getLocale();
        $start = $startAt->copy()->locale($locale);
        $end = $endAt->copy()->locale($locale);

        return sprintf(
            'من %s إلى %s',
            $start->translatedFormat('j F Y'),
            $end->translatedFormat('j F Y'),
        );
    }

    private function bookingDurationLabel(?CarbonInterface $startAt, ?CarbonInterface $endAt): ?string
    {
        if ($startAt === null || $endAt === null || $endAt->lte($startAt)) {
            return null;
        }

        $nights = (int) $startAt->diffInDays($endAt);

        if ($nights <= 0) {
            return null;
        }

        return match ($nights) {
            1 => 'ليلة واحدة',
            2 => 'ليلتان',
            default => $nights.' ليالٍ',
        };
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function activityPayload(Order $order): array
    {
        $statusEntries = $order->statuses()
            ->latest('id')
            ->limit(50)
            ->get()
            ->map(fn ($status): array => [
                'key' => 'status-'.$status->id,
                'type' => 'status',
                'title' => 'تغيير حالة الطلب',
                'status' => $status->name,
                'status_label' => Order::statusLabelFor((string) $status->name),
                'status_color' => Order::statusBadgeColorFor((string) $status->name),
                'date' => $status->created_at?->translatedFormat('d M Y h:i A'),
                'sort' => $status->created_at?->getTimestamp() ?? 0,
            ]);

        $activityEntries = $order->activitiesAsSubject()
            ->latest('id')
            ->limit(50)
            ->get()
            ->map(fn ($activity): array => [
                'key' => 'activity-'.$activity->id,
                'type' => 'activity',
                'title' => filled($activity->description) ? (string) $activity->description : 'نشاط على الطلب',
                'status' => null,
                'status_label' => null,
                'status_color' => null,
                'date' => $activity->created_at?->translatedFormat('d M Y h:i A'),
                'sort' => $activity->created_at?->getTimestamp() ?? 0,
            ]);

        return $statusEntries
            ->concat($activityEntries)
            ->sortByDesc('sort')
            ->values()
            ->map(fn (array $entry): array => collect($entry)->except('sort')->all())
            ->all();
    }

    private function clientAvatar(int $id, mixed $metaAvatar): string
    {
        if (filled($metaAvatar)) {
            if (str_starts_with((string) $metaAvatar, 'http')) {
                return (string) $metaAvatar;
            }

            return Storage::disk('public')->url((string) $metaAvatar);
        }

        return 'https://api.dicebear.com/9.x/fun-emoji/svg?seed='.$id;
    }
}
