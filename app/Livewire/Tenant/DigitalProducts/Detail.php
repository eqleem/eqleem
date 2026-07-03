<?php

namespace App\Livewire\Tenant\DigitalProducts;

use App\Models\Content;
use Livewire\Component;

class Detail extends Component
{
    public Content $product;

    public function mount(string $slug): void
    {
        $this->product = Content::query()
            ->type(contentTypeModel('digital-products'))
            ->published()
            ->where('active', true)
            ->where('slug', $slug)
            ->firstOrFail();
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
            'downloadsCount' => $this->product->getMedia('digital-product-downloads')->count(),
        ])->title($this->product->title);
    }
}
