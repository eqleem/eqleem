<div>
    <div class="grid grid-cols-1 gap-2 p-4 sm:grid-cols-2" dir="rtl">
        @foreach ($contentTypes as $contentType)
            <button
                type="button"
                wire:click="openAddModal('{{ $contentType['slug'] }}')"
                wire:loading.attr="disabled"
                wire:target="openAddModal('{{ $contentType['slug'] }}')"
                class="flex items-center gap-3 rounded-xl border border-gray-100 px-3 py-3 text-start transition hover:border-gray-200 hover:bg-gray-50 disabled:opacity-50"
            >
                <img
                    src="{{ $contentType['icon_url'] }}"
                    alt=""
                    class="size-9 shrink-0 rounded-lg bg-gray-100 p-1.5"
                >
                <span class="min-w-0">
                    <span class="block text-sm font-medium text-gray-800">{{ $contentType['name'] }}</span>
                    <span class="block text-xs text-gray-400">{{ $contentType['description'] }}</span>
                </span>
            </button>
        @endforeach
    </div>

    @foreach ($addForms as $slug => $form)
        <ui:modal title="{{ $form['title'] }}" size="2xl" name="{{ $form['modal'] }}">
            <livewire:dynamic-component
                :is="$form['component']"
                :contentType="$form['contentType']"
                :key="'home-add-'.$slug"
            />
        </ui:modal>
    @endforeach
</div>

<?php

use App\Support\ContentTypeRegistry;
use Livewire\Component;

new class extends Component
{
    /** @var array<int, array<string, mixed>> */
    public array $contentTypes = [];

    /** @var array<string, array{modal: string, title: string, component: string, contentType: array<string, mixed>}> */
    public array $addForms = [];

    public function mount(ContentTypeRegistry $contentTypes): void
    {
        $this->contentTypes = $contentTypes->all()
            ->map(fn ($type): array => $type->toArray())
            ->all();

        $this->addForms = collect($this->contentTypes)
            ->mapWithKeys(fn (array $contentType): array => [
                $contentType['slug'] => $this->addFormFor($contentType),
            ])
            ->filter()
            ->all();
    }

    public function openAddModal(string $slug): void
    {
        $form = $this->addForms[$slug] ?? null;

        if (! $form) {
            return;
        }

        $this->dispatch('closemodal', modal: 'home-step-content');
        $this->dispatch('openmodal', modal: $form['modal']);
    }

    /**
     * @param  array<string, mixed>  $contentType
     * @return array{modal: string, title: string, component: string, contentType: array<string, mixed>}|null
     */
    protected function addFormFor(array $contentType): ?array
    {
        $form = match ($contentType['slug']) {
            'blog' => ['modal' => 'add-blog-post', 'component' => 'admin::page.content.blog.add-post', 'title' => 'إضافة تدوينة'],
            'store' => ['modal' => 'add-store-product', 'component' => 'admin::page.content.store.add-product', 'title' => 'إضافة منتج'],
            'courses' => ['modal' => 'add-course', 'component' => 'admin::page.content.courses.add-course', 'title' => 'إضافة دورة'],
            'portfolio' => ['modal' => 'add-portfolio-project', 'component' => 'admin::page.content.portfolio.add-project', 'title' => 'إضافة مشروع'],
            'pages' => ['modal' => 'add-page', 'component' => 'admin::page.content.pages.add-page', 'title' => 'إضافة صفحة'],
            'forms' => ['modal' => 'add-form', 'component' => 'admin::page.content.forms.add-form', 'title' => 'إضافة نموذج'],
            'services' => ['modal' => 'add-service', 'component' => 'admin::page.content.services.add-service', 'title' => 'إضافة خدمة'],
            'digital-products' => ['modal' => 'add-digital-product', 'component' => 'admin::page.content.digital-products.add-product', 'title' => 'إضافة منتج رقمي'],
            'digital-services' => ['modal' => 'add-digital-service', 'component' => 'admin::page.content.digital-services.add-service', 'title' => 'إضافة خدمة رقمية'],
            'newsletter' => ['modal' => 'add-newsletter', 'component' => 'admin::page.content.newsletter.add-newsletter', 'title' => 'إضافة نشرة'],
            'menu' => ['modal' => 'add-menu-item', 'component' => 'admin::page.content.menu.add-item', 'title' => 'إضافة طبق'],
            'unit-rental' => ['modal' => 'add-unit', 'component' => 'admin::page.content.unit-rental.add-unit', 'title' => 'إضافة وحدة'],
            default => null,
        };

        if (! is_array($form)) {
            return null;
        }

        return [
            ...$form,
            'contentType' => $contentType,
        ];
    }
}; ?>
