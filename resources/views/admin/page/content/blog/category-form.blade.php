<ui:form wire:submit="submit">
    <ui:input name="name" label="اسم التصنيف" placeholder="مثال: أخبار الشركة" />

    <ui:textarea name="description" label="الوصف" placeholder="وصف مختصر للتصنيف" rows="3" />

    <ui:select
        name="parentId"
        label="التصنيف الأب"
        :options="$parentOptions"
        placeholder=""
    />

    <x-slot:footer>
        <ui:button target="submit" label="{{ __('Save') }}" />
    </x-slot>
</ui:form>

<?php

use App\Models\Taxonomy;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Locked;

new class extends \Livewire\Component
{
    /** @var array<string, mixed> */
    public array $contentType = [];

    #[Locked]
    public ?int $categoryId = null;

    public string $name = '';

    public string $description = '';

    public string $parentId = '';

    public function mount(?int $categoryId = null): void
    {
        $this->categoryId = $categoryId;

        if ($categoryId) {
            $this->loadCategory();
        }
    }

    public function loadCategory(): void
    {
        $category = Taxonomy::query()
            ->type('blog_category')
            ->findOrFail($this->categoryId);

        $this->name = $category->name;
        $this->description = (string) $category->description;
        $this->parentId = (string) ($category->parent_id ?? '');
    }

    /**
     * @return array<string, string>
     */
    public function parentOptions(): array
    {
        $excludedIds = [];

        if ($this->categoryId) {
            $category = Taxonomy::query()->type('blog_category')->find($this->categoryId);

            if ($category) {
                $excludedIds = $category->getDescendants()
                    ->pluck('id')
                    ->push($category->id)
                    ->all();
            }
        }

        $options = ['' => 'بدون تصنيف أب'];

        foreach (Taxonomy::flatTree('blog_category') as $item) {
            if (in_array($item->id, $excludedIds, true)) {
                continue;
            }

            $indent = str_repeat('— ', (int) ($item->depth ?? 0));
            $options[(string) $item->id] = $indent.$item->name;
        }

        return $options;
    }

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        return [
            'name' => 'required|string|min:1|max:255',
            'description' => 'nullable|string|max:1000',
            'parentId' => [
                'nullable',
                Rule::exists('taxonomies', 'id')->where(function ($query): void {
                    $query->where('type', 'blog_category');

                    if ($tenantId = currentTenantId()) {
                        $query->where('tenant_id', $tenantId);
                    }
                }),
            ],
        ];
    }

    public function submit(): void
    {
        $this->validate();

        $attributes = [
            'name' => $this->name,
            'description' => filled($this->description) ? $this->description : null,
            'type' => 'blog_category',
            'parent_id' => filled($this->parentId) ? (int) $this->parentId : null,
        ];

        if ($this->categoryId) {
            $category = Taxonomy::query()
                ->type('blog_category')
                ->findOrFail($this->categoryId);

            $category->update($attributes);

            $modal = 'edit-blog-category';
        } else {
            Taxonomy::query()->create($attributes);

            $this->reset(['name', 'description', 'parentId']);

            $modal = 'add-blog-category';
        }

        $this->dispatch('updateBlogCategoryList');
        $this->dispatch('closemodal', modal: $modal);
        $this->dispatch('notify', text: __('Saved'));
    }

    public function render()
    {
        return $this->view([
            'parentOptions' => $this->parentOptions(),
        ]);
    }
}; ?>
