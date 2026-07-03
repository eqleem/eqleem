<?php

namespace App\Livewire\Tenant\Services;

use App\Models\Content;
use Livewire\Component;

class Detail extends Component
{
    public Content $service;

    public function mount(string $slug): void
    {
        $this->service = Content::query()
            ->type(contentTypeModel('services'))
            ->published()
            ->where('active', true)
            ->where('slug', $slug)
            ->firstOrFail();
    }

    public function render()
    {
        $categories = $this->service->taxonomiesOfType('service_category');
        $images = $this->service->serviceImageUrls();

        return tenantView('services.detail', [
            'service' => $this->service,
            'categories' => $categories,
            'subtitle' => (string) data_get($this->service->data, 'subtitle', ''),
            'body' => (string) data_get($this->service->data, 'body', ''),
            'images' => $images,
            'imageUrl' => $images[0] ?? $this->service->avatar,
            'price' => (int) data_get($this->service->data, 'price', 0),
            'durationMinutes' => (int) data_get($this->service->data, 'duration_minutes', 0),
        ])->title($this->service->title);
    }
}
