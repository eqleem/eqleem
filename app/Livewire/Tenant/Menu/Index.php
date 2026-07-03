<?php

namespace App\Livewire\Tenant\Menu;

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

        $meals = Content::query()
            ->type(contentTypeModel('menu'))
            ->published()
            ->where('active', true)
            ->with(['taxonomies' => fn ($query) => $query->where('type', 'menu_category')])
            ->when(
                $categoryIds !== [],
                fn (Builder $query) => $query->withAnyTaxonomiesOfType('menu_category', $categoryIds),
            )
            ->when(
                $this->search !== '',
                fn (Builder $query) => $query->where(function (Builder $builder): void {
                    $term = '%'.$this->search.'%';

                    $builder->where('title', 'like', $term);
                }),
            )
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->get();

        $mealsForJs = $meals
            ->map(fn (Content $meal): array => [
                'id' => (string) $meal->id,
                'name' => $meal->title,
                'category' => $meal->taxonomies->first()?->name ?? '',
                'price' => (int) data_get($meal->data, 'price', 0),
                'image' => $meal->getFirstMediaUrl('menu-media') ?: $meal->avatar,
                'meal_options' => data_get($meal->data, 'meal_options', []),
            ])
            ->values()
            ->all();

        return tenantView('menu.index', [
            'categories' => $categories,
            'meals' => $meals,
            'mealsForJs' => $mealsForJs,
            'categorySlug' => $this->categorySlug,
        ])->title(Setting::menuSettings()['section_title']);
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
            ->type('menu_category')
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
            ->type('menu_category')
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
