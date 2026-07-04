<?php

namespace App\Support;

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
}
