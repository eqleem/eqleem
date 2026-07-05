<?php

namespace App\Livewire\Tenant\DigitalProducts;

use App\Models\Content;
use App\Models\Setting;
use App\Models\Taxonomy;
use App\Services\CartService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Livewire\Attributes\Url;
use Livewire\Component;

class Index extends Component
{
    #[Url(as: 'category', except: '', history: true)]
    public ?string $categorySlug = null;

    public string $search = '';

    public function addToCart(int $productId, CartService $cart): void
    {
        $product = Content::query()
            ->type(contentTypeModel('digital-products'))
            ->published()
            ->where('active', true)
            ->whereKey($productId)
            ->firstOrFail();

        $cart->addItem($product);

        $this->dispatch('cart-updated');
    }

    public function render()
    {
        $this->syncCategorySlugFromRequest();

        $categories = $this->filterCategories();
        $categoryIds = $this->categoryFilterIds();

        $products = Content::query()
            ->type(contentTypeModel('digital-products'))
            ->published()
            ->where('active', true)
            ->with(['taxonomies' => fn ($query) => $query->where('type', 'digital_store_category')])
            ->when(
                $categoryIds !== [],
                fn (Builder $query) => $query->withAnyTaxonomiesOfType('digital_store_category', $categoryIds),
            )
            ->when(
                $this->search !== '',
                fn (Builder $query) => $query->where(function (Builder $builder): void {
                    $term = '%'.$this->search.'%';

                    $builder
                        ->where('title', 'like', $term)
                        ->orWhere('data->subtitle', 'like', $term)
                        ->orWhere('data->body', 'like', $term);
                }),
            )
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->get();

        return tenantView('digital-products.index', [
            'categories' => $categories,
            'products' => $products,
            'categorySlug' => $this->categorySlug,
        ])->title(Setting::digitalProductSettings()['section_title']);
    }

    private function syncCategorySlugFromRequest(): void
    {
        if (! request()->has('category')) {
            return;
        }

        $slug = request()->query('category');

        $this->categorySlug = filled($slug) ? (string) $slug : null;
    }

    /**
     * @return Collection<int, Taxonomy>
     */
    private function filterCategories(): Collection
    {
        return Taxonomy::query()
            ->type('digital_store_category')
            ->whereNull('parent_id')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    /**
     * @return array<int, int>
     */
    private function categoryFilterIds(): array
    {
        if (! filled($this->categorySlug)) {
            return [];
        }

        $category = Taxonomy::query()
            ->type('digital_store_category')
            ->where('slug', $this->categorySlug)
            ->first();

        if (! $category) {
            return [];
        }

        return $category->descendants()
            ->pluck('id')
            ->prepend($category->id)
            ->map(fn (mixed $id): int => (int) $id)
            ->all();
    }
}
