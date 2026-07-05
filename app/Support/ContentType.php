<?php

namespace App\Support;

class ContentType
{
    /**
     * @var array<int, string>
     */
    private const TAILWIND_COLORS = [
        'slate', 'gray', 'zinc', 'neutral', 'stone',
        'red', 'orange', 'amber', 'yellow', 'lime', 'green', 'emerald', 'teal',
        'cyan', 'sky', 'blue', 'indigo', 'violet', 'purple', 'fuchsia', 'pink', 'rose',
    ];

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
        public string $color = 'gray',
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
            color: $config['color'] ?? 'gray',
        );
    }

    public static function backgroundClassFor(string $color): ?string
    {
        if (str_starts_with($color, '#')) {
            return null;
        }

        if (! in_array($color, self::TAILWIND_COLORS, true)) {
            return null;
        }

        return "bg-{$color}-50";
    }

    public static function hoverBackgroundClassFor(string $color): ?string
    {
        $class = self::backgroundClassFor($color);

        return $class !== null ? str_replace('bg-', 'hover:bg-', $class) : null;
    }

    public static function backgroundHexFor(string $color): ?string
    {
        if (! str_starts_with($color, '#')) {
            return null;
        }

        return $color;
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
            'color' => $this->color,
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
            'color_bg_class' => self::backgroundClassFor($this->color),
            'color_hover_class' => self::hoverBackgroundClassFor($this->color),
            'color_bg_hex' => self::backgroundHexFor($this->color),
        ];
    }
}
