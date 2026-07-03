<?php

namespace App\Livewire\Tenant\DigitalServices;

use App\Models\Content;
use Livewire\Component;

class Detail extends Component
{
    public Content $service;

    public function mount(string $slug): void
    {
        $this->service = Content::query()
            ->type(contentTypeModel('digital-services'))
            ->published()
            ->where('active', true)
            ->where('slug', $slug)
            ->firstOrFail();
    }

    public function render()
    {
        $categories = $this->service->taxonomiesOfType('digital_service_category');
        $images = collect($this->service->digitalServiceImages())->pluck('url')->values()->all();

        return tenantView('digital-services.detail', [
            'service' => $this->service,
            'categories' => $categories,
            'subtitle' => (string) data_get($this->service->data, 'subtitle', ''),
            'body' => (string) data_get($this->service->data, 'body', ''),
            'images' => $images,
            'imageUrl' => $images[0] ?? $this->service->avatar,
            'price' => (int) data_get($this->service->data, 'price', 0),
            'comparePrice' => data_get($this->service->data, 'compare_price'),
            'deliveryDays' => (int) data_get($this->service->data, 'delivery_days', 0),
        ])->title($this->service->title);
    }
}
