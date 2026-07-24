<?php

namespace App\Livewire\Tenant\OnDemandServices;

use App\Models\Content;
use App\Support\OnDemandUnit;
use Livewire\Component;

class Detail extends Component
{
    public Content $service;

    public function mount(string $slug): void
    {
        $this->service = Content::query()
            ->type(contentTypeModel('on-demand-services'))
            ->published()
            ->where('active', true)
            ->where('slug', $slug)
            ->with(['media'])
            ->firstOrFail();
    }

    public function render()
    {
        $images = collect($this->service->onDemandServiceImages())->pluck('url')->values()->all();
        $price = (int) data_get($this->service->data, 'price', 0);
        $unitType = (string) data_get($this->service->data, 'unit_type', '');
        $unitLabel = (string) data_get($this->service->data, 'unit_label', '');

        return tenantView('on-demand-services.detail', [
            'service' => $this->service,
            'subtitle' => (string) data_get($this->service->data, 'subtitle', ''),
            'body' => (string) data_get($this->service->data, 'body', ''),
            'images' => $images,
            'imageUrl' => $images[0] ?? $this->service->avatar,
            'price' => $price,
            'comparePrice' => data_get($this->service->data, 'compare_price'),
            'unitDisplay' => OnDemandUnit::label($unitType, $unitLabel),
            'priceHtml' => OnDemandUnit::priceHtml($price, $unitType, $unitLabel),
        ])->title($this->service->title);
    }
}
