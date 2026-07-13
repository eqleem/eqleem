<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Support\Collection;

class PaymentMethodRegistry
{
    /**
     * @return Collection<int, PaymentMethod>
     */
    public function all(): Collection
    {
        return collect(config('payment-methods', []))
            ->map(fn (array $config, string $slug): PaymentMethod => PaymentMethod::fromConfig($slug, $config))
            ->sortBy('order')
            ->values();
    }

    public function find(string $slug): ?PaymentMethod
    {
        $config = config("payment-methods.{$slug}");

        if (! is_array($config)) {
            return null;
        }

        return PaymentMethod::fromConfig($slug, $config);
    }

    /**
     * @return array<string, mixed>
     */
    public function defaults(string $slug): array
    {
        return config("payment-methods.{$slug}.defaults", []);
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function activeForCheckout(): Collection
    {
        return $this->all()
            ->map(function (PaymentMethod $method): array {
                $settings = Setting::paymentMethod($method->slug);

                return array_merge($method->toArray(), $settings, [
                    'checkout_component' => $method->component('checkout'),
                ]);
            })
            ->filter(fn (array $method): bool => (bool) ($method['available'] ?? false) && (bool) ($method['active'] ?? false))
            ->values();
    }
}
