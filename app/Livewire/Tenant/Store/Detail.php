<?php

namespace App\Livewire\Tenant\Store;

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
            ->type(contentTypeModel('store'))
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
        $cart->addProduct($this->product, $this->quantity);

        $this->addedToCart = true;
        $this->dispatch('cart-updated');
    }

    public function render()
    {
        $categories = $this->product->taxonomiesOfType('store_category');
        $images = $this->product->storeImageUrls();

        return tenantView('store.detail', [
            'product' => $this->product,
            'categories' => $categories,
            'body' => (string) data_get($this->product->data, 'body', ''),
            'images' => $images,
            'imageUrl' => $images[0] ?? null,
            'price' => (int) data_get($this->product->data, 'price', 0),
            'comparePrice' => data_get($this->product->data, 'compare_price'),
            'weight' => data_get($this->product->data, 'weight'),
        ])->title($this->product->title);
    }
}
