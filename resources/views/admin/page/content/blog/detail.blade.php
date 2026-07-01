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
                <span class="text-gray-600 truncate">تحرير التدوينة</span>
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
            <ui:input name="title" placeholder="عنوان التدوينة" />

            <ui:textarea
                name="subtitle"
                placeholder="عنوان فرعي"
                info="عنوان فرعي يظهر تحت العنوان الرئيسي في الصفحة الرئيسية وعند عرض التدوينات."
            />

            <ui:ck
                name="body"
                :value="$body"
                :model-id="$content->id"
                model-type="content"
            />
        </div>

        <div x-cloak x-show="formTab === 'advanced'" class="space-y-2">
            <ui:select
                name="categoryId"
                label="القسم"
                :options="$categories"
                placeholder=""
            />

            <ui:input
                name="slug"
                label="نص الرابط"
                dir="ltr"
                :prefix="$slugPrefix"
            />

            <ui:file name="image" label="الصورة الرئيسية" upload-label="رفع الصورة">
                @if ($image)
                    <img src="{{ $image->temporaryUrl() }}" class="w-32 rounded-lg mb-2" alt="">
                @elseif ($currentImage)
                    <img src="{{ $currentImage }}" class="w-32 rounded-lg mb-2" alt="">
                @endif
            </ui:file>

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
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

new class extends \Livewire\Component
{
    use WithFileUploads;

    /** @var array<string, mixed> */
    public array $contentType = [];

    public string $itemId = '';

    public string $title = '';

    public string $subtitle = '';

    public string $body = '';

    public string $editorMode = 'html';

    public string $slug = '';

    public string $categoryId = '';

    public bool $published = false;

    public $image = null;

    public ?string $currentImage = null;

    public function mount(): void
    {
        $content = $this->content();

        $this->title = $content->title;
        $this->subtitle = (string) data_get($content->data, 'subtitle', '');
        $this->body = (string) data_get($content->data, 'body', '');
        $this->editorMode = (string) data_get($content->data, 'editor_mode', 'html');
        $this->slug = $content->slug;
        $this->categoryId = (string) data_get($content->data, 'category_id', '');
        $this->published = $content->status === 'published';
        $this->currentImage = $this->resolveImageUrl(data_get($content->data, 'image'));
    }

    public function content(): Content
    {
        return Content::query()
            ->type('blog')
            ->where('uuid', $this->itemId)
            ->firstOrFail();
    }

    /**
     * @return array<string, string>
     */
    public function categories(): array
    {
        $items = Content::query()
            ->type('blog_category')
            ->orderBy('title')
            ->pluck('title', 'id');

        return ['' => 'اختر القسم ('.$items->count().')'] + $items->all();
    }

    public function slugPrefix(): string
    {
        $base = rtrim((string) (tenant('url') ?? url('/')), '/');

        return $base.'/blog/';
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
            'categoryId' => 'nullable',
            'published' => 'boolean',
            'image' => 'nullable|image|max:15360',
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
        $data['category_id'] = filled($this->categoryId) ? $this->categoryId : null;

        if ($this->image instanceof TemporaryUploadedFile) {
            $tenantUuid = tenant('uuid') ?? 'shared';
            $path = $this->image->storePublicly('tenant-media/'.$tenantUuid.'/blog', 'spaces');
            $data['image'] = $path;
            $this->currentImage = $this->resolveImageUrl($path);
            $this->image = null;
        }

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

        $this->slug = $slug;
        $this->dispatch('updateBlogPostList');
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
        $slug = $baseSlug !== '' ? $baseSlug : 'post';
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

    private function resolveImageUrl(?string $path): ?string
    {
        if (! filled($path)) {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        return Storage::disk('spaces')->url($path);
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
