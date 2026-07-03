<?php

namespace App\Livewire\Tenant\Portfolio;

use App\Models\Content;
use App\Models\Setting;
use App\Models\Taxonomy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Livewire\Attributes\Url;
use Livewire\Component;

class Index extends Component
{
    #[Url(as: 'category', except: '', history: true)]
    public ?string $categorySlug = null;

    public string $search = '';

    public function render()
    {
        $this->syncCategorySlugFromRequest();

        $categories = $this->filterCategories();
        $categoryIds = $this->categoryFilterIds();

        $projects = Content::query()
            ->type('portfolio')
            ->published()
            ->where('active', true)
            ->with(['taxonomies' => fn ($query) => $query->where('type', 'portfolio_category')])
            ->when(
                $categoryIds !== [],
                fn (Builder $query) => $query->withAnyTaxonomiesOfType('portfolio_category', $categoryIds),
            )
            ->when(
                $this->search !== '',
                fn (Builder $query) => $query->where(function (Builder $builder): void {
                    $term = '%'.$this->search.'%';

                    $builder
                        ->where('title', 'like', $term)
                        ->orWhere('data->subtitle', 'like', $term);
                }),
            )
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->get();

        return tenantView('portfolio.index', [
            'categories' => $categories,
            'projects' => $projects,
            'categorySlug' => $this->categorySlug,
        ])->title(Setting::portfolioSettings()['section_title']);
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
            ->type('portfolio_category')
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
            ->type('portfolio_category')
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
