<?php

namespace App\Support;

class PaymentMethod
{
    /**
     * @param  array<string, mixed>  $defaults
     * @param  array<string, string>  $components
     */
    public function __construct(
        public string $slug,
        public string $name,
        public string $icon,
        public string $description,
        public array $defaults = [],
        public array $components = [],
        public int $order = 0,
    ) {}

    /**
     * @param  array<string, mixed>  $config
     */
    public static function fromConfig(string $slug, array $config): self
    {
        return new self(
            slug: $config['slug'] ?? $slug,
            name: $config['name'],
            icon: $config['icon'],
            description: $config['description'],
            defaults: $config['defaults'] ?? [],
            components: $config['components'] ?? [],
            order: $config['order'] ?? 0,
        );
    }

    public function component(string $key = 'modal'): ?string
    {
        return $this->components[$key] ?? null;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'slug' => $this->slug,
            'name' => $this->name,
            'icon' => $this->icon,
            'icon_url' => asset($this->icon),
            'description' => $this->description,
            'defaults' => $this->defaults,
            'components' => $this->components,
            'order' => $this->order,
        ];
    }
}
