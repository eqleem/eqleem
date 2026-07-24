<?php

namespace App\Actions;

use App\Mail\OrderConfirmation;
use App\Models\Order;
use App\Support\OrderEmailItems;
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

        Mail::to($email)->queue(new OrderConfirmation(
            order: $order,
            tenant: $order->tenant,
            customerName: (string) ($order->client?->name ?? ''),
            items: OrderEmailItems::forOrder($order->id),
        ));
    }
}
