<?php

namespace App\Support;

class PageTab
{
    public function __construct(
        public string $slug,
        public string $name,
        public string $description,
        public string $component,
        public string $icon,
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
            description: $config['description'],
            component: $config['component'],
            icon: $config['icon'],
            order: $config['order'] ?? 0,
        );
    }

    public function tabId(): string
    {
        return $this->slug;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'slug' => $this->slug,
            'name' => $this->name,
            'description' => $this->description,
            'component' => $this->component,
            'icon' => $this->icon,
            'icon_url' => asset($this->icon),
            'order' => $this->order,
            'tab_id' => $this->tabId(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function toTabArray(): array
    {
        return [
            'id' => $this->tabId(),
            'slug' => $this->slug,
            'label' => $this->name,
            'icon' => $this->icon,
            'icon_url' => asset($this->icon),
            'description' => $this->description,
            'type' => 'fixed',
        ];
    }
}
