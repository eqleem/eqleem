<?php

namespace App\Support;

use App\Models\Order;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class OrderEmailItems
{
    /**
     * @return Collection<int, object>
     */
    public static function forOrder(int $orderId): Collection
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
