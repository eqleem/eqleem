<?php

namespace App\Support;

class BlockType
{
    public function __construct(
        public string $slug,
        public string $name,
        public string $icon,
        public string $description,
        public string $component,
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
            component: $config['component'],
            order: $config['order'] ?? 0,
        );
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
            'component' => $this->component,
            'order' => $this->order,
        ];
    }
}
