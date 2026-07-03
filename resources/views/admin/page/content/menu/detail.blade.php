<div class="bg-white rounded-2xl overflow-hidden" x-data="{ formTab: 'edit' }">
    <div class="bg-stone-200/70 flex items-center justify-between gap-4 px-4 py-3 border-b border-stone-200">
        <div class="flex items-center gap-3 min-w-0">
            <a
                href="{{ route('admin.page.home', ['tab' => $contentType['tab_id']]) }}"
                wire:navigate
                title="{{ __('Back') }}"
                class="bg-white p-2 rounded-lg shadow-sm hover:bg-gray-50 flex items-center justify-center shrink-0"
            >
                <ui:icon name="arrow-right" class="!w-5 !h-5 text-gray-600" />
            </a>
            <div class="flex items-center gap-2 min-w-0 text-sm text-gray-700">
                <img src="{{ asset($contentType['icon']) }}" class="w-5 h-5 shrink-0" alt="">
                <span class="font-semibold truncate">{{ $contentType['name'] }}</span>
                <span class="text-gray-400">/</span>
                <span class="text-gray-600 truncate">تحرير الطبق</span>
            </div>
        </div>

        <nav class="flex items-center gap-1 shrink-0 bg-gray-300/40 rounded-xl p-0.5">
            <button
                type="button"
                x-on:click="formTab = 'edit'"
                x-bind:class="formTab === 'edit'
                    ? 'bg-white text-gray-900 shadow-sm font-semibold'
                    : 'text-gray-600 hover:bg-white/60 hover:text-gray-800'"
                class="flex items-center gap-1.5 px-3 py-2 rounded-lg text-sm transition"
            >
                <ui:icon name="Pencil" class="!w-4 !h-4 shrink-0" />
                تحرير
            </button>
            <button
                type="button"
                x-on:click="formTab = 'advanced'"
                x-bind:class="formTab === 'advanced'
                    ? 'bg-white text-gray-900 shadow-sm font-semibold'
                    : 'text-gray-600 hover:bg-white/60 hover:text-gray-800'"
                class="flex items-center gap-1.5 px-3 py-2 rounded-lg text-sm transition"
            >
                <ui:icon name="adjustments-horizontal" class="!w-4 !h-4 shrink-0" />
                متقدم
            </button>
        </nav>
    </div>

    <ui:form wire:submit="save" class="!p-4 md:!p-6 !rounded-none">
        <div x-cloak x-show="formTab === 'edit'" class="space-y-2">
            <ui:input name="title" placeholder="اسم الطبق" />

            <ui:upload
                name="images"
                :value="$images"
                label="صور الطبق"
                :block="true"
                :multiple="true"
                :max-files="20"
                :sortable="true"
                collection="menu-media"
                :model-id="$content->id"
                model-type="content"
                add-method="addImage"
                remove-method="removeImage"
                reorder-method="reorderImages"
            />

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                <ui:input
                    name="price"
                    label="السعر الأساسي"
                    type="number"
                    dir="ltr"
                    step="0.01"
                    min="0"
                    placeholder="0.00"
                />

                <ui:input
                    name="comparePrice"
                    label="سعر المقارنة"
                    type="number"
                    dir="ltr"
                    step="0.01"
                    min="0"
                    placeholder="0.00"
                />
            </div>

            <div class="space-y-3 rounded-xl border border-stone-200 bg-stone-50/50 p-4">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-sm font-semibold text-gray-800">خيارات الوجبة</p>
                        <p class="text-xs text-gray-500 mt-0.5">مثل الحجم والإضافات — كل خيار له اختيارات وأسعار إضافية</p>
                    </div>
                    <ui:button
                        type="button"
                        wire:click="addMealOptionGroup"
                        variant="outline"
                        icon="plus"
                        label="إضافة خيار"
                    />
                </div>

                @if ($mealOptions === [])
                    <div class="rounded-lg border border-dashed border-stone-300 bg-white px-4 py-6 text-center">
                        <p class="text-sm text-gray-500">لا توجد خيارات بعد. أضف خياراً مثل «حجم الوجبة» أو «الإضافات».</p>
                    </div>
                @endif

                @foreach ($mealOptions as $groupIndex => $group)
                    <div wire:key="meal-option-group-{{ $group['id'] }}" class="rounded-xl border border-stone-200 bg-white p-4 space-y-3">
                        <div class="flex items-start justify-between gap-3">
                            <p class="text-xs font-semibold text-primary-600 bg-primary-50 px-2 py-1 rounded-md">
                                خيار {{ $groupIndex + 1 }}
                            </p>
                            <button
                                type="button"
                                wire:click="removeMealOptionGroup({{ $groupIndex }})"
                                class="rounded-lg p-1.5 text-red-400 hover:bg-red-50 hover:text-red-600 transition"
                                title="حذف الخيار"
                            >
                                <ui:icon name="trash" class="!w-4 !h-4" />
                            </button>
                        </div>

                        <ui:input
                            name="mealOptions.{{ $groupIndex }}.name"
                            label="اسم الخيار"
                            placeholder="مثال: حجم الوجبة"
                        />

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                            <div>
                                <label class="text-sm font-medium text-gray-700 mb-1 block">نوع الاختيار</label>
                                <select
                                    wire:model="mealOptions.{{ $groupIndex }}.type"
                                    class="block w-full rounded-lg py-2 px-3 text-sm text-gray-800 border border-gray-200 focus:border-primary-500 focus:outline-none"
                                >
                                    <option value="single">اختيار واحد (مثل الحجم)</option>
                                    <option value="multiple">اختيار متعدد (مثل الإضافات)</option>
                                </select>
                            </div>

                            <div class="flex items-end pb-1">
                                <ui:toggle
                                    name="mealOptions.{{ $groupIndex }}.required"
                                    label="إلزامي"
                                />
                            </div>
                        </div>

                        <div class="space-y-2">
                            <p class="text-sm font-semibold text-gray-600">الاختيارات</p>

                            @foreach ($group['choices'] as $choiceIndex => $choice)
                                <div wire:key="meal-option-choice-{{ $choice['id'] }}" class="flex items-start gap-2">
                                    <div class="flex-1 grid grid-cols-1 sm:grid-cols-2 gap-2">
                                        <ui:input
                                            name="mealOptions.{{ $groupIndex }}.choices.{{ $choiceIndex }}.name"
                                            placeholder="اسم الاختيار"
                                        />
                                        <ui:input
                                            name="mealOptions.{{ $groupIndex }}.choices.{{ $choiceIndex }}.price"
                                            type="number"
                                            dir="ltr"
                                            step="0.01"
                                            min="0"
                                            placeholder="سعر إضافي (0.00)"
                                        />
                                    </div>
                                    <button
                                        type="button"
                                        wire:click="removeMealOptionChoice({{ $groupIndex }}, {{ $choiceIndex }})"
                                        class="mt-1 rounded-lg p-2 text-red-400 hover:bg-red-50 hover:text-red-600 transition shrink-0"
                                        title="حذف الاختيار"
                                    >
                                        <ui:icon name="x" class="!w-4 !h-4" />
                                    </button>
                                </div>
                            @endforeach

                            <ui:button
                                type="button"
                                wire:click="addMealOptionChoice({{ $groupIndex }})"
                                variant="secondary"
                                icon="plus"
                                label="إضافة اختيار"
                                class="w-full"
                            />
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div x-cloak x-show="formTab === 'advanced'" class="space-y-2">
            <ui:checkbox-select
                name="categoryIds"
                label="القسم"
                :options="$categories"
                :selected="$categoryIds"
                placeholder="اختر الأقسام"
            />

            <ui:input
                name="slug"
                label="نص الرابط"
                dir="ltr"
                :prefix="$slugPrefix"
            />

            <ui:toggle name="published" label="حالة النشر" live />
        </div>

        <x-slot:footer>
            <div class="flex items-center gap-2">
                <ui:button wire:click="saveAndClose" type="button" target="saveAndClose" label="حفظ وإغلاق" />
                <ui:button type="submit" target="save" label="{{ __('Save') }}" />
            </div>
        </x-slot:footer>
    </ui:form>
</div>

<?php

use App\Models\Content;
use App\Models\Media;
use App\Models\Taxonomy;
use App\Support\Money;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

new class extends \Livewire\Component
{
    /** @var array<string, mixed> */
    public array $contentType = [];

    public string $itemId = '';

    public string $title = '';

    public string $slug = '';

    public string $price = '';

    public string $comparePrice = '';

    /** @var array<int, string> */
    public array $categoryIds = [];

    /** @var array<int, array{id: int, url: string}> */
    public array $images = [];

    /**
     * @var array<int, array{
     *     id: string,
     *     name: string,
     *     type: string,
     *     required: bool,
     *     choices: array<int, array{id: string, name: string, price: string}>
     * }>
     */
    public array $mealOptions = [];

    public bool $published = false;

    public function mount(): void
    {
        $content = $this->content();

        $this->title = $content->title;
        $this->slug = $content->slug;
        $this->price = $this->decimalFromMinor(data_get($content->data, 'price'));
        $this->comparePrice = $this->decimalFromMinor(data_get($content->data, 'compare_price'));
        $this->categoryIds = $content->taxonomiesOfType('menu_category')
            ->pluck('id')
            ->map(fn (mixed $id): string => (string) $id)
            ->values()
            ->all();
        $this->published = $content->status === 'published';
        $this->images = $content->fresh()->menuImages();
        $this->mealOptions = $this->mealOptionsFromData(data_get($content->data, 'meal_options', []));
    }

    public function content(): Content
    {
        return Content::query()
            ->type(contentTypeModel($this->contentType['slug']))
            ->where('uuid', $this->itemId)
            ->firstOrFail();
    }

    /**
     * @return array<int, array{id: string, label: string, selectable: bool}>
     */
    public function categories(): array
    {
        $parentIds = Taxonomy::query()
            ->type('menu_category')
            ->whereNotNull('parent_id')
            ->pluck('parent_id')
            ->map(fn (mixed $id): int => (int) $id)
            ->flip();

        return Taxonomy::flatTree('menu_category')
            ->map(fn (Taxonomy $item): array => [
                'id' => (string) $item->id,
                'label' => str_repeat('— ', (int) ($item->depth ?? 0)).$item->name,
                'selectable' => ! $parentIds->has((int) $item->id),
            ])
            ->all();
    }

    public function slugPrefix(): string
    {
        $base = rtrim((string) (tenant('url') ?? url('/')), '/');

        return $base.'/menu/item/';
    }

    public function addImage(string $path): void
    {
        if (! filled($path)) {
            return;
        }

        $content = $this->content();
        $content->attachMediaFromDiskIfNeeded('menu-media', $path);
        $this->images = $content->fresh()->menuImages();
    }

    /**
     * @param  array<int, int|string>  $orderedIds
     */
    public function reorderImages(array $orderedIds): void
    {
        $content = $this->content();
        $validIds = $content->getMedia('menu-media')->pluck('id')->all();

        $orderedIds = collect($orderedIds)
            ->map(fn (mixed $id): int => (int) $id)
            ->filter(fn (int $id): bool => in_array($id, $validIds, true))
            ->values()
            ->all();

        if ($orderedIds === []) {
            return;
        }

        Media::setNewOrder($orderedIds);

        $this->images = $content->fresh()->menuImages();
    }

    public function removeImage(int $mediaId): void
    {
        $content = $this->content();
        $media = $content->getMedia('menu-media')->firstWhere('id', $mediaId);

        if ($media instanceof Media) {
            $media->delete();
        }

        $this->images = $content->fresh()->menuImages();
    }

    public function addMealOptionGroup(): void
    {
        $this->mealOptions[] = [
            'id' => (string) Str::uuid(),
            'name' => '',
            'type' => 'single',
            'required' => false,
            'choices' => [
                [
                    'id' => (string) Str::uuid(),
                    'name' => '',
                    'price' => '',
                ],
            ],
        ];
    }

    public function removeMealOptionGroup(int $groupIndex): void
    {
        if (! isset($this->mealOptions[$groupIndex])) {
            return;
        }

        unset($this->mealOptions[$groupIndex]);
        $this->mealOptions = array_values($this->mealOptions);
    }

    public function addMealOptionChoice(int $groupIndex): void
    {
        if (! isset($this->mealOptions[$groupIndex])) {
            return;
        }

        $this->mealOptions[$groupIndex]['choices'][] = [
            'id' => (string) Str::uuid(),
            'name' => '',
            'price' => '',
        ];
    }

    public function removeMealOptionChoice(int $groupIndex, int $choiceIndex): void
    {
        if (! isset($this->mealOptions[$groupIndex]['choices'][$choiceIndex])) {
            return;
        }

        unset($this->mealOptions[$groupIndex]['choices'][$choiceIndex]);
        $this->mealOptions[$groupIndex]['choices'] = array_values($this->mealOptions[$groupIndex]['choices']);
    }

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        return [
            'title' => 'required|min:1|max:255',
            'slug' => 'required|string|max:255',
            'price' => 'nullable|numeric|min:0',
            'comparePrice' => 'nullable|numeric|min:0',
            'categoryIds' => 'nullable|array',
            'categoryIds.*' => [
                Rule::exists('taxonomies', 'id')->where(function ($query): void {
                    $query->where('type', 'menu_category');

                    if ($tenantId = currentTenantId()) {
                        $query->where('tenant_id', $tenantId);
                    }
                }),
            ],
            'mealOptions' => 'nullable|array',
            'mealOptions.*.name' => 'nullable|string|max:255',
            'mealOptions.*.type' => 'required|in:single,multiple',
            'mealOptions.*.required' => 'boolean',
            'mealOptions.*.choices' => 'nullable|array',
            'mealOptions.*.choices.*.name' => 'nullable|string|max:255',
            'mealOptions.*.choices.*.price' => 'nullable|numeric|min:0',
            'published' => 'boolean',
        ];
    }

    public function save(): void
    {
        $this->persist(close: false);
    }

    public function saveAndClose(): void
    {
        $this->persist(close: true);
    }

    public function persist(bool $close = false): mixed
    {
        $this->validate();

        $content = $this->content();
        $data = $content->data ?? [];

        $data['price'] = filled($this->price) ? money_minor($this->price) : 0;
        $data['compare_price'] = filled($this->comparePrice) ? money_minor($this->comparePrice) : null;
        $data['meal_options'] = $this->mealOptionsForStorage();

        $selectableIds = collect($this->categories())
            ->where('selectable', true)
            ->pluck('id')
            ->map(fn (mixed $id): string => (string) $id)
            ->all();

        $categoryIds = collect($this->categoryIds)
            ->map(fn (mixed $id): string => (string) $id)
            ->intersect($selectableIds)
            ->map(fn (string $id): int => (int) $id)
            ->values()
            ->all();

        $slug = $this->uniqueSlug(
            filled($this->slug) ? $this->slug : Str::slug($this->title),
            (int) $content->id,
        );

        $status = $this->published ? 'published' : 'draft';

        $content->update([
            'title' => $this->title,
            'slug' => $slug,
            'status' => $status,
            'data' => $data,
            'published_at' => $this->published
                ? ($content->published_at ?? now())
                : null,
        ]);

        $content->syncTaxonomiesOfType('menu_category', $categoryIds);

        $this->slug = $slug;
        $this->images = $content->fresh()->menuImages();
        $this->dispatch('updateMenuItemList');
        $this->dispatch('notify', text: __('Saved'));

        if ($close) {
            return $this->redirect(route('admin.page.home', [
                'tab' => $this->contentType['tab_id'],
            ]), navigate: true);
        }

        return null;
    }

    /**
     * @param  array<int, mixed>  $stored
     * @return array<int, array{
     *     id: string,
     *     name: string,
     *     type: string,
     *     required: bool,
     *     choices: array<int, array{id: string, name: string, price: string}>
     * }>
     */
    private function mealOptionsFromData(array $stored): array
    {
        return collect($stored)
            ->filter(fn (mixed $group): bool => is_array($group))
            ->map(function (array $group): array {
                $choices = collect($group['choices'] ?? [])
                    ->filter(fn (mixed $choice): bool => is_array($choice))
                    ->map(fn (array $choice): array => [
                        'id' => (string) ($choice['id'] ?? Str::uuid()),
                        'name' => (string) ($choice['name'] ?? ''),
                        'price' => $this->decimalFromMinor($choice['price'] ?? null),
                    ])
                    ->values()
                    ->all();

                return [
                    'id' => (string) ($group['id'] ?? Str::uuid()),
                    'name' => (string) ($group['name'] ?? ''),
                    'type' => in_array($group['type'] ?? '', ['single', 'multiple'], true)
                        ? (string) $group['type']
                        : 'single',
                    'required' => (bool) ($group['required'] ?? false),
                    'choices' => $choices,
                ];
            })
            ->values()
            ->all();
    }

    /**
     * @return array<int, array{
     *     id: string,
     *     name: string,
     *     type: string,
     *     required: bool,
     *     choices: array<int, array{id: string, name: string, price: int}>
     * }>
     */
    private function mealOptionsForStorage(): array
    {
        return collect($this->mealOptions)
            ->map(function (array $group): array {
                $choices = collect($group['choices'] ?? [])
                    ->filter(fn (array $choice): bool => filled($choice['name'] ?? null))
                    ->map(fn (array $choice): array => [
                        'id' => (string) ($choice['id'] ?? Str::uuid()),
                        'name' => trim((string) ($choice['name'] ?? '')),
                        'price' => filled($choice['price'] ?? null) ? money_minor($choice['price']) : 0,
                    ])
                    ->values()
                    ->all();

                return [
                    'id' => (string) ($group['id'] ?? Str::uuid()),
                    'name' => trim((string) ($group['name'] ?? '')),
                    'type' => in_array($group['type'] ?? '', ['single', 'multiple'], true)
                        ? (string) $group['type']
                        : 'single',
                    'required' => (bool) ($group['required'] ?? false),
                    'choices' => $choices,
                ];
            })
            ->filter(fn (array $group): bool => filled($group['name']) && $group['choices'] !== [])
            ->values()
            ->all();
    }

    private function uniqueSlug(string $baseSlug, int $exceptId): string
    {
        $slug = $baseSlug !== '' ? $baseSlug : 'menu-item';
        $counter = 1;

        while (
            Content::query()
                ->where('slug', $slug)
                ->whereKeyNot($exceptId)
                ->exists()
        ) {
            $slug = $baseSlug.'-'.$counter;
            $counter++;
        }

        return $slug;
    }

    private function decimalFromMinor(mixed $minor): string
    {
        if ($minor === null || $minor === '' || (int) $minor === 0) {
            return '';
        }

        return (string) Money::fromMinor((int) $minor);
    }

    public function render()
    {
        return $this->view([
            'content' => $this->content(),
            'categories' => $this->categories(),
            'slugPrefix' => $this->slugPrefix(),
        ]);
    }
}; ?>
