<?php

namespace App\Livewire\Tenant\Pages;

use App\Models\Booking;
use App\Models\Calendar;
use App\Models\Client;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class OrderConfirmation extends Component
{
    public Order $order;

    public function mount(Order $order): void
    {
        abort_unless($order->channel === 'ecommerce', 404);

        $client = authClient();

        $canView = (int) session('recent_order_id') === $order->id
            || ($client instanceof Client && (int) $order->client_id === $client->id);

        abort_unless($canView, 403);
    }

    /**
     * @return Collection<int, object>
     */
    protected function orderItems(): Collection
    {
        $items = DB::table('order_items')
            ->where('order_id', $this->order->id)
            ->orderBy('id')
            ->get();

        $metas = $items->mapWithKeys(function (object $item): array {
            $meta = is_string($item->meta ?? null)
                ? (json_decode($item->meta, true) ?: [])
                : (array) ($item->meta ?? []);

            return [$item->id => $meta];
        });

        $bookingIds = $items->pluck('booking_id')->filter()->unique()->values();

        $bookings = $bookingIds->isEmpty()
            ? collect()
            : Booking::query()
                ->with('calendar')
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
                ->get()
                ->keyBy('id');

        return $items->map(function (object $item) use ($metas, $bookings, $calendars): object {
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
            $calendarName = $booking?->calendar?->name
                ?? ($calendarId ? $calendars->get($calendarId)?->name : null);

            $item->type_label = Order::itemTypeOptions()[$type] ?? 'أخرى';
            $item->image_url = $meta['image_url'] ?? null;
            $item->is_booking = $isBooking;
            $item->booking_date_label = $startAt?->translatedFormat('l j F Y');
            $item->booking_time_label = $this->bookingTimeLabel($startAt, $endAt);
            $item->calendar_name = $calendarName;

            return $item;
        });
    }

    protected function bookingTimeLabel(?Carbon $startAt, ?Carbon $endAt): ?string
    {
        if (! $startAt) {
            return null;
        }

        if ($endAt) {
            return $startAt->format('H:i').' - '.$endAt->format('H:i');
        }

        return $startAt->format('H:i');
    }

    public function render()
    {
        $items = $this->orderItems();
        $shippingFee = (int) data_get($this->order->meta, 'shipping_fee', 0);

        return tenantView('pages.order-confirmation', [
            'items' => $items,
            'shippingFee' => $shippingFee,
            'itemCount' => $items->sum('qty'),
        ])->title('تم الطلب بنجاح');
    }
}
