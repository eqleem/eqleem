<?php

namespace App\Support;

class ContentType
{
    /**
     * @param  array<string, string>  $components
     */
    public function __construct(
        public string $slug,
        public string $name,
        public string $icon,
        public string $description,
        public string $modelType,
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
            modelType: contentTypeModel($slug),
            components: $config['components'] ?? [],
            order: $config['order'] ?? 0,
        );
    }

    public function component(string $key = 'index'): ?string
    {
        return $this->components[$key] ?? null;
    }

    public function tabId(): string
    {
        return 'content-'.$this->slug;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'slug' => $this->slug,
            'model_type' => $this->modelType,
            'name' => $this->name,
            'icon' => $this->icon,
            'icon_url' => asset($this->icon),
            'description' => $this->description,
            'components' => $this->components,
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
            'type' => 'content',
        ];
    }
}
