<?php

namespace App\Http\Resources;

use App\Models\Booking;
use App\Models\Calendar;
use App\Models\Client;
use App\Models\Order;
use App\Support\Money;
use Carbon\CarbonInterface;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

/**
 * Full booking detail for the dashboard.
 *
 * @mixin Booking
 */
class BookingResource extends JsonResource
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
        $startAt = $booking->start_at;
        $endAt = $booking->end_at;
        $client = $booking->client;

        return [
            'id' => $booking->id,
            'status' => $status,
            'status_label' => Booking::statusLabelFor($status),
            'status_color' => Booking::statusBadgeColorFor($status),
            'client' => $client instanceof Client ? [
                'id' => $client->id,
                'uuid' => $client->uuid,
                'name' => $client->name,
                'email' => $client->email,
                'phone' => $client->phone,
                'avatar' => $this->clientAvatar($client->id, data_get($client->meta, 'avatar')),
            ] : (filled(data_get($booking->data, 'guest_name')) ? [
                'id' => null,
                'uuid' => null,
                'name' => (string) data_get($booking->data, 'guest_name'),
                'email' => filled(data_get($booking->data, 'guest_email'))
                    ? (string) data_get($booking->data, 'guest_email')
                    : null,
                'phone' => filled(data_get($booking->data, 'guest_phone'))
                    ? (string) data_get($booking->data, 'guest_phone')
                    : null,
                'avatar' => null,
            ] : null),
            'guest_name' => filled(data_get($booking->data, 'guest_name'))
                ? (string) data_get($booking->data, 'guest_name')
                : null,
            'guest_email' => filled(data_get($booking->data, 'guest_email'))
                ? (string) data_get($booking->data, 'guest_email')
                : null,
            'guest_phone' => filled(data_get($booking->data, 'guest_phone'))
                ? (string) data_get($booking->data, 'guest_phone')
                : null,
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
            'start_at' => $startAt?->toIso8601String(),
            'end_at' => $endAt?->toIso8601String(),
            'dates_label' => $this->datesLabel($startAt, $endAt, $contentType),
            'date_label' => $startAt?->copy()->locale(app()->getLocale())->translatedFormat('l j F Y'),
            'time_label' => $contentType === 'service' ? $this->timeLabel($startAt, $endAt) : null,
            'duration_label' => $contentType === 'unit_rental' ? $this->durationLabel($startAt, $endAt) : null,
            'price' => $priceMinor,
            'price_formatted' => Money::formatWithCurrency($priceMinor, $currency),
            'currency' => $currency,
            'order' => $booking->order ? [
                'uuid' => $booking->order->uuid,
                'number' => $booking->order->number,
            ] : null,
            'created' => $booking->created_at?->locale(app()->getLocale())->diffForHumans(),
            'created_at' => $booking->created_at?->toIso8601String(),
            'created_label' => $booking->created_at?->copy()->locale(app()->getLocale())->translatedFormat('d M Y h:i A'),
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

    private function timeLabel(?CarbonInterface $startAt, ?CarbonInterface $endAt): ?string
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

    private function durationLabel(?CarbonInterface $startAt, ?CarbonInterface $endAt): ?string
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
