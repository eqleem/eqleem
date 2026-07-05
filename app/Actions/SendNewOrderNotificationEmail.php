<?php

namespace App\Actions;

use App\Mail\NewOrderNotification;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Lorisleiva\Actions\Concerns\AsAction;

class SendNewOrderNotificationEmail
{
    use AsAction;

    public function handle(Order $order): void
    {
        $order->loadMissing(['client', 'tenant.user']);

        $owner = $order->tenant?->user;

        if (! $owner instanceof User || blank($owner->email)) {
            return;
        }

        $items = $this->loadOrderItems($order->id);

        Mail::to($owner->email)->queue(new NewOrderNotification(
            order: $order,
            tenant: $order->tenant,
            owner: $owner,
            customerName: (string) ($order->client?->name ?? ''),
            customerPhone: (string) ($order->client?->phone ?? ''),
            customerEmail: $order->client?->email,
            items: $items,
            orderDetailUrl: route('admin.orders.detail', ['id' => $order->uuid]),
        ));
    }

    /**
     * @return Collection<int, object>
     */
    protected function loadOrderItems(int $orderId): Collection
    {
        return DB::table('order_items')
            ->where('order_id', $orderId)
            ->orderBy('id')
            ->get()
            ->map(function (object $item): object {
                $meta = is_string($item->meta ?? null)
                    ? (json_decode($item->meta, true) ?: [])
                    : (array) ($item->meta ?? []);

                $item->type_label = Order::itemTypeOptions()[(string) ($meta['type'] ?? 'other')] ?? 'أخرى';
                $item->image_url = $meta['image_url'] ?? null;

                return $item;
            });
    }
}
