<?php

namespace App\Http\Resources;

use App\Models\Content;
use App\Support\Money;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

/**
 * Full menu item payload for the detail editor.
 *
 * @mixin Content
 */
class MenuItemResource extends JsonResource
{
    /**
     * @param  array<string, mixed>  $additional
     */
    public function __construct($resource, protected array $extra = [])
    {
        parent::__construct($resource);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var Content $content */
        $content = $this->resource;

        return [
            'id' => $content->id,
            'uuid' => $content->uuid,
            'title' => $content->title,
            'slug' => $content->slug,
            'price' => $this->decimalFromMinor(data_get($content->data, 'price')),
            'compare_price' => $this->decimalFromMinor(data_get($content->data, 'compare_price')),
            'currency_code' => Money::defaultCurrencyCode(),
            'currency_symbol' => Money::symbolFor(),
            'meal_options' => $this->mealOptionsFromData(data_get($content->data, 'meal_options', [])),
            'status' => $content->status,
            'active' => (bool) $content->active,
            'published' => (bool) $content->active,
            'published_at' => $content->published_at?->toIso8601String(),
            'category_ids' => $content->taxonomiesOfType('menu_category')
                ->pluck('id')
                ->map(fn (mixed $id): string => (string) $id)
                ->values()
                ->all(),
            'images' => $content->menuImages(),
            'slug_prefix' => $this->extra['slug_prefix'] ?? null,
            'category_options' => $this->extra['category_options'] ?? [],
        ];
    }

    /**
     * @param  array<int, mixed>  $stored
     * @return array<int, array{
     *     id: string,
     *     name: string,
     *     type: string,
     *     required: bool,
     *     choices: array<int, array{id: string, name: string, price: string}>
     * }>
     */
    private function mealOptionsFromData(array $stored): array
    {
        return collect($stored)
            ->filter(fn (mixed $group): bool => is_array($group))
            ->map(function (array $group): array {
                $choices = collect($group['choices'] ?? [])
                    ->filter(fn (mixed $choice): bool => is_array($choice))
                    ->map(fn (array $choice): array => [
                        'id' => (string) ($choice['id'] ?? Str::uuid()),
                        'name' => (string) ($choice['name'] ?? ''),
                        'price' => $this->decimalFromMinor($choice['price'] ?? null),
                    ])
                    ->values()
                    ->all();

                return [
                    'id' => (string) ($group['id'] ?? Str::uuid()),
                    'name' => (string) ($group['name'] ?? ''),
                    'type' => in_array($group['type'] ?? '', ['single', 'multiple'], true)
                        ? (string) $group['type']
                        : 'single',
                    'required' => (bool) ($group['required'] ?? false),
                    'choices' => $choices,
                ];
            })
            ->values()
            ->all();
    }

    private function decimalFromMinor(mixed $minor): string
    {
        if ($minor === null || $minor === '' || (int) $minor === 0) {
            return '';
        }

        return (string) Money::fromMinor((int) $minor);
    }
}
