<?php

namespace App\Livewire\Tenant\PropertiesRental;

use App\Models\Content;
use Livewire\Component;

class Detail extends Component
{
    public Content $unit;

    public function mount(string $slug): void
    {
        $this->unit = Content::query()
            ->type(contentTypeModel('unit-rental'))
            ->published()
            ->where('active', true)
            ->where('slug', $slug)
            ->firstOrFail();
    }

    public function render()
    {
        $categories = $this->unit->taxonomiesOfType('unit_category');
        $images = collect($this->unit->unitImages())->pluck('url')->values()->all();

        return tenantView('properties-rental.detail', [
            'unit' => $this->unit,
            'categories' => $categories,
            'subtitle' => (string) data_get($this->unit->data, 'subtitle', ''),
            'body' => (string) data_get($this->unit->data, 'body', ''),
            'images' => $images,
            'imageUrl' => $images[0] ?? $this->unit->avatar,
            'pricePerNight' => (int) data_get($this->unit->data, 'price', 0),
        ])->title($this->unit->title);
    }
}
