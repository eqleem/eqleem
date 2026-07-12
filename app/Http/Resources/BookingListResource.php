<?php

namespace App\Http\Resources;

use App\Models\Booking;
use App\Models\Calendar;
use App\Models\Order;
use App\Support\Money;
use Carbon\CarbonInterface;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Lean payload for the dashboard bookings table.
 *
 * @mixin Booking
 */
class BookingListResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var Booking $booking */
        $booking = $this->resource;
        $status = Booking::normalizeStatus((string) $booking->status);
        $contentType = $booking->content?->orderItemType();
        $priceMinor = Money::toMinor($booking->price_snapshot);
        $currency = $booking->currency ?: Money::defaultCurrencyCode();

        return [
            'id' => $booking->id,
            'status' => $status,
            'status_label' => Booking::statusLabelFor($status),
            'status_color' => Booking::statusBadgeColorFor($status),
            'client' => $booking->client ? [
                'name' => $booking->client->name,
                'email' => $booking->client->email,
                'phone' => $booking->client->phone,
            ] : null,
            'content' => $booking->content ? [
                'id' => $booking->content->id,
                'title' => $booking->content->title,
                'type' => $contentType,
                'type_label' => $contentType ? (Order::itemTypeOptions()[$contentType] ?? $contentType) : null,
            ] : null,
            'calendar' => $booking->calendar ? [
                'id' => $booking->calendar->id,
                'name' => $booking->calendar->name,
                'type' => $booking->calendar->type,
                'type_label' => Calendar::typeOptions()[$booking->calendar->type] ?? $booking->calendar->type,
            ] : null,
            'start_at' => $booking->start_at?->toIso8601String(),
            'end_at' => $booking->end_at?->toIso8601String(),
            'dates_label' => $this->datesLabel($booking->start_at, $booking->end_at, $contentType),
            'price' => $priceMinor,
            'price_formatted' => Money::formatWithCurrency($priceMinor, $currency),
            'currency' => $currency,
            'order' => $booking->order ? [
                'uuid' => $booking->order->uuid,
                'number' => $booking->order->number,
            ] : null,
            'created' => $booking->created_at?->locale(app()->getLocale())->diffForHumans(),
            'created_at' => $booking->created_at?->toIso8601String(),
        ];
    }

    private function datesLabel(?CarbonInterface $startAt, ?CarbonInterface $endAt, ?string $contentType): ?string
    {
        if ($startAt === null || $endAt === null) {
            return null;
        }

        $locale = app()->getLocale();
        $start = $startAt->copy()->locale($locale);
        $end = $endAt->copy()->locale($locale);

        if ($contentType === 'unit_rental' || ! $start->isSameDay($end)) {
            return sprintf(
                'من %s إلى %s',
                $start->translatedFormat('j F Y'),
                $end->translatedFormat('j F Y'),
            );
        }

        return sprintf(
            '%s · %s – %s',
            $start->translatedFormat('l j F Y'),
            $start->format('H:i'),
            $end->format('H:i'),
        );
    }
}
