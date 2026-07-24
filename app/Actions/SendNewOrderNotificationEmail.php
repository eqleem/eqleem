<?php

namespace App\Actions;

use App\Mail\NewOrderNotification;
use App\Models\Order;
use App\Models\User;
use App\Support\OrderEmailItems;
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

        Mail::to($owner->email)->queue(new NewOrderNotification(
            order: $order,
            tenant: $order->tenant,
            owner: $owner,
            customerName: (string) ($order->client?->name ?? ''),
            customerPhone: (string) ($order->client?->phone ?? ''),
            customerEmail: $order->client?->email,
            items: OrderEmailItems::forOrder($order->id),
            orderDetailUrl: route('admin.orders.detail', ['id' => $order->uuid]),
        ));
    }
}
