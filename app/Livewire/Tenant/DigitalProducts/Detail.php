<?php

namespace App\Livewire\Tenant\DigitalProducts;

use App\Models\Content;
use App\Services\CartService;
use Livewire\Component;

class Detail extends Component
{
    public Content $product;

    public int $quantity = 1;

    public bool $addedToCart = false;

    public function mount(string $slug): void
    {
        $this->product = Content::query()
            ->type(contentTypeModel('digital-products'))
            ->published()
            ->where('active', true)
            ->where('slug', $slug)
            ->with(['media', 'taxonomies'])
            ->withCount([
                'media as downloads_count' => fn ($query) => $query->where('collection_name', 'digital-product-downloads'),
            ])
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
        $cart->addItem($this->product, $this->quantity);

        $this->addedToCart = true;
        $this->dispatch('cart-updated');
    }

    public function render()
    {
        $categories = $this->product->taxonomiesOfType('digital_store_category');
        $images = collect($this->product->digitalProductImages())->pluck('url')->values()->all();

        return tenantView('digital-products.detail', [
            'product' => $this->product,
            'categories' => $categories,
            'subtitle' => (string) data_get($this->product->data, 'subtitle', ''),
            'body' => (string) data_get($this->product->data, 'body', ''),
            'images' => $images,
            'imageUrl' => $images[0] ?? $this->product->avatar,
            'price' => (int) data_get($this->product->data, 'price', 0),
            'comparePrice' => data_get($this->product->data, 'compare_price'),
            'downloadsCount' => (int) ($this->product->downloads_count ?? $this->product->getMedia('digital-product-downloads')->count()),
        ])->title($this->product->title);
    }
}
