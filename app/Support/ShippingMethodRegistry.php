<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Support\Collection;

class ShippingMethodRegistry
{
    /**
     * @return Collection<int, ShippingMethod>
     */
    public function all(): Collection
    {
        return collect(config('shipping-methods', []))
            ->map(fn (array $config, string $slug): ShippingMethod => ShippingMethod::fromConfig($slug, $config))
            ->sortBy('order')
            ->values();
    }

    public function find(string $slug): ?ShippingMethod
    {
        $config = config("shipping-methods.{$slug}");

        if (! is_array($config)) {
            return null;
        }

        return ShippingMethod::fromConfig($slug, $config);
    }

    /**
     * @return array<string, mixed>
     */
    public function defaults(string $slug): array
    {
        return config("shipping-methods.{$slug}.defaults", []);
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function activeForCheckout(): Collection
    {
        return $this->all()
            ->map(function (ShippingMethod $method): array {
                $settings = Setting::shippingMethod($method->slug);

                return array_merge($method->toArray(), $settings);
            })
            ->filter(fn (array $method): bool => (bool) ($method['active'] ?? false))
            ->values();
    }
}
