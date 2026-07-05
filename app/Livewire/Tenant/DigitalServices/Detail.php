<?php

namespace App\Livewire\Tenant\DigitalServices;

use App\Models\Content;
use App\Services\CartService;
use Livewire\Component;

class Detail extends Component
{
    public Content $service;

    public int $quantity = 1;

    public bool $addedToCart = false;

    public function mount(string $slug): void
    {
        $this->service = Content::query()
            ->type(contentTypeModel('digital-services'))
            ->published()
            ->where('active', true)
            ->where('slug', $slug)
            ->firstOrFail();
    }

    public function incrementQuantity(): void
    {
        $this->quantity++;
    }

    public function decrementQuantity(): void
    {
        if ($this->quantity > 1) {
            $this->quantity--;
        }
    }

    public function addToCart(CartService $cart): void
    {
        $cart->addItem($this->service, $this->quantity);

        $this->addedToCart = true;
        $this->dispatch('cart-updated');
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
