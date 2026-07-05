<?php

namespace App\Actions;

use App\Mail\OrderConfirmation;
use App\Models\Order;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Lorisleiva\Actions\Concerns\AsAction;

class SendOrderConfirmationEmail
{
    use AsAction;

    public function handle(Order $order): void
    {
        $order->loadMissing(['client', 'tenant']);

        $email = $order->client?->email;

        if (blank($email)) {
            return;
        }

        $items = $this->loadOrderItems($order->id);

        Mail::to($email)->queue(new OrderConfirmation(
            order: $order,
            tenant: $order->tenant,
            customerName: (string) ($order->client?->name ?? ''),
            items: $items,
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
