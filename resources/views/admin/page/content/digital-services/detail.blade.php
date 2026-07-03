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
                <span class="text-gray-600 truncate">تحرير الخدمة الرقمية</span>
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
            <ui:input name="title" placeholder="اسم الخدمة" />

            <ui:textarea
                name="subtitle"
                placeholder="عنوان فرعي"
                info="عنوان فرعي يظهر تحت اسم الخدمة في صفحة العرض وقائمة الخدمات."
            />

            <ui:upload
                name="images"
                :value="$images"
                label="صور الخدمة"
                :block="true"
                :multiple="true"
                :max-files="20"
                :sortable="true"
                collection="digital-service-media"
                :model-id="$content->id"
                model-type="content"
                add-method="addImage"
                remove-method="removeImage"
                reorder-method="reorderImages"
            />

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                <ui:input
                    name="price"
                    label="السعر"
                    type="number"
                    dir="ltr"
                    step="0.01"
                    min="0"
                    placeholder="0.00"
                />

                <ui:input
                    name="deliveryDays"
                    label="مدة التسليم"
                    type="number"
                    dir="ltr"
                    step="1"
                    min="1"
                    placeholder="1"
                    suffix="أيام"
                />
            </div>

            <ui:input
                name="comparePrice"
                label="سعر المقارنة"
                type="number"
                dir="ltr"
                step="0.01"
                min="0"
                placeholder="0.00"
            />

            <ui:ck
                name="body"
                :value="$body"
                :model-id="$content->id"
                model-type="content"
            />
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

    public string $subtitle = '';

    public string $body = '';

    public string $editorMode = 'html';

    public string $slug = '';

    public string $price = '';

    public string $comparePrice = '';

    public string $deliveryDays = '';

    /** @var array<int, string> */
    public array $categoryIds = [];

    /** @var array<int, array{id: int, url: string}> */
    public array $images = [];

    public bool $published = false;

    public function mount(): void
    {
        $content = $this->content();

        $this->title = $content->title;
        $this->subtitle = (string) data_get($content->data, 'subtitle', '');
        $this->body = (string) data_get($content->data, 'body', '');
        $this->editorMode = (string) data_get($content->data, 'editor_mode', 'html');
        $this->slug = $content->slug;
        $this->price = $this->decimalFromMinor(data_get($content->data, 'price'));
        $this->comparePrice = $this->decimalFromMinor(data_get($content->data, 'compare_price'));
        $this->deliveryDays = (string) (data_get($content->data, 'delivery_days') ?? '');
        $this->categoryIds = $content->taxonomiesOfType('digital_service_category')
            ->pluck('id')
            ->map(fn (mixed $id): string => (string) $id)
            ->values()
            ->all();
        $this->published = $content->status === 'published';
        $this->images = $content->fresh()->digitalServiceImages();
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
            ->type('digital_service_category')
            ->whereNotNull('parent_id')
            ->pluck('parent_id')
            ->map(fn (mixed $id): int => (int) $id)
            ->flip();

        return Taxonomy::flatTree('digital_service_category')
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

        return $base.'/digital-services/';
    }

    public function addImage(string $path): void
    {
        if (! filled($path)) {
            return;
        }

        $content = $this->content();
        $content->attachMediaFromDiskIfNeeded('digital-service-media', $path);
        $this->images = $content->fresh()->digitalServiceImages();
    }

    /**
     * @param  array<int, int|string>  $orderedIds
     */
    public function reorderImages(array $orderedIds): void
    {
        $content = $this->content();
        $validIds = $content->getMedia('digital-service-media')->pluck('id')->all();

        $orderedIds = collect($orderedIds)
            ->map(fn (mixed $id): int => (int) $id)
            ->filter(fn (int $id): bool => in_array($id, $validIds, true))
            ->values()
            ->all();

        if ($orderedIds === []) {
            return;
        }

        Media::setNewOrder($orderedIds);

        $this->images = $content->fresh()->digitalServiceImages();
    }

    public function removeImage(int $mediaId): void
    {
        $content = $this->content();
        $media = $content->getMedia('digital-service-media')->firstWhere('id', $mediaId);

        if ($media instanceof Media) {
            $media->delete();
        }

        $this->images = $content->fresh()->digitalServiceImages();
    }

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        return [
            'title' => 'required|min:1|max:255',
            'subtitle' => 'nullable|string|max:500',
            'body' => 'nullable|string',
            'editorMode' => 'required|in:html,markdown',
            'slug' => 'required|string|max:255',
            'price' => 'nullable|numeric|min:0',
            'comparePrice' => 'nullable|numeric|min:0',
            'deliveryDays' => 'nullable|integer|min:1|max:365',
            'categoryIds' => 'nullable|array',
            'categoryIds.*' => [
                Rule::exists('taxonomies', 'id')->where(function ($query): void {
                    $query->where('type', 'digital_service_category');

                    if ($tenantId = currentTenantId()) {
                        $query->where('tenant_id', $tenantId);
                    }
                }),
            ],
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

        $data['subtitle'] = $this->subtitle;
        $data['body'] = $this->body;
        $data['editor_mode'] = $this->editorMode;
        $data['price'] = filled($this->price) ? money_minor($this->price) : 0;
        $data['compare_price'] = filled($this->comparePrice) ? money_minor($this->comparePrice) : null;
        $data['delivery_days'] = filled($this->deliveryDays) ? (int) $this->deliveryDays : null;

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

        $content->syncTaxonomiesOfType('digital_service_category', $categoryIds);

        $this->slug = $slug;
        $this->images = $content->fresh()->digitalServiceImages();
        $this->dispatch('updateDigitalServiceList');
        $this->dispatch('notify', text: __('Saved'));

        if ($close) {
            return $this->redirect(route('admin.page.home', [
                'tab' => $this->contentType['tab_id'],
            ]), navigate: true);
        }

        return null;
    }

    private function uniqueSlug(string $baseSlug, int $exceptId): string
    {
        $slug = $baseSlug !== '' ? $baseSlug : 'digital-service';
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
