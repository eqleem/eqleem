<?php

namespace App\Livewire\Tenant\Store;

use App\Models\Content;
use Livewire\Component;

class Detail extends Component
{
    public Content $product;

    public function mount(string $slug): void
    {
        $this->product = Content::query()
            ->type(contentTypeModel('store'))
            ->published()
            ->where('active', true)
            ->where('slug', $slug)
            ->firstOrFail();
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
