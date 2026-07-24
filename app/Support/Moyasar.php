<?php

namespace App\Support;

use Illuminate\Support\Facades\Http;

class Moyasar
{
    /**
     * @return array<string, mixed>|null
     */
    public static function fetchPayment(string $paymentId): ?array
    {
        return Http::withHeaders([
            'Authorization' => 'Basic '.base64_encode((string) config('services.moyasar.secret_key')),
            'Content-Type' => 'application/x-www-form-urlencoded',
        ])->get(config('services.moyasar.base_url').'payments/'.$paymentId)->json();
    }

    public static function isPaid(?array $payment): bool
    {
        return data_get($payment, 'status') === 'paid';
    }
}
