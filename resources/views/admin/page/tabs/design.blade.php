<div class="space-y-3">
    @if ($themes->isEmpty())
        <div class="rounded-xl bg-stone-100/50 p-6 text-center text-sm text-stone-500">
            لا توجد قوالب متاحة حالياً.
        </div>
    @else
        <div class="rounded-xl bg-stone-300/30 p-2">
            <div class="no-scrollbar flex gap-2 overflow-x-auto">
                @foreach ($themes as $theme)
                    <button
                        type="button"
                        wire:click="selectTheme({{ $theme['id'] }})"
                        wire:key="theme-thumb-{{ $theme['id'] }}"
                        @class([
                            'group relative w-24 shrink-0 rounded-lg border-2 bg-transparent text-start transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary-500/40 sm:w-40',
                            'border-primary-500 shadow-md border-primary-500/15' => $selectedThemeId === $theme['id'],
                            'border-transparent hover:border-stone-200 hover:shadow-sm bg-white' => $selectedThemeId !== $theme['id'],
                        ])
                    >
                        @if ($theme['is_active'])
                            <span class="absolute start-1.5 top-1.5 z-10 inline-flex items-center gap-0.5 rounded-full bg-green-500 px-1.5 py-0.5 text-[9px] font-semibold text-white shadow-sm">
                                <ui:icon name="Check" class="!h-2.5 !w-2.5" />
                                نشط
                            </span>
                        @endif

                        <div class="overflow-hidden rounded-t-md bg-stone-100">
                            <img
                                src="{{ $theme['image_path'] }}"
                                alt="{{ $theme['name'] }}"
                                class="aspect-square w-full object-cover object-top transition duration-300 group-hover:scale-[1.02]"
                                loading="lazy"
                            >
                        </div>

                        <div class="rounded-b-lg bg-white px-2 py-1.5">
                            <div class="flex items-center justify-between gap-2">
                                <span class="min-w-0 truncate text-[11px] font-medium text-stone-700">{{ $theme['name'] }}</span>
                                <span class="shrink-0 text-[11px] font-semibold text-green-600">{{ $theme['price_label'] }}</span>
                            </div>
                        </div>
                    </button>
                @endforeach
            </div>
        </div>

        @if ($selectedTheme)
            <div
                class="overflow-hidden rounded-xl bg-white shadow-sm"
                wire:key="theme-details-{{ $selectedTheme['id'] }}-{{ $selectedTheme['is_active'] ? 'active' : 'inactive' }}"
                x-data="{ activeTab: @js($selectedTheme['is_active'] ? 'customize' : 'info') }"
            >
                <div class="border-b border-stone-100">
                    <div class="flex flex-col gap-2 px-3 py-2.5 sm:flex-row sm:items-center sm:justify-between">
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center justify-between gap-2">
                                <div class="flex min-w-0 items-center justify-end gap-1.5">
                                    <h3 class="truncate text-base font-semibold text-stone-900">{{ $selectedTheme['name'] }}</h3>

                                    @if ($selectedTheme['is_active'])
                                        <ui:badge color="green" size="sm">القالب النشط</ui:badge>
                                    @endif
                                </div>
                                <span class="shrink-0 text-sm font-semibold text-green-600">{{ $selectedTheme['price_label'] }}</span>
                            </div>

                            {{-- <p class="mt-0.5 text-end text-xs text-stone-500">{{ $selectedTheme['label_ar'] }}</p> --}}
                        </div>

                        <div class="shrink-0">
                            @if ($selectedTheme['is_active'])
                                <div class="inline-flex items-center gap-1.5 rounded-md border border-green-200 bg-green-50 px-2.5 py-1.5 text-xs font-medium text-green-700">
                                    <ui:icon name="circle-check" class="!h-3.5 !w-3.5" />
                                    مُفعّل على صفحتك
                                </div>
                            @else
                                <ui:button
                                    wire:click="setDefaultTheme"
                                    wire:loading.attr="disabled"
                                    target="setDefaultTheme"
                                    icon="Palette"
                                    label="تعيين كقالب افتراضي"
                                />
                            @endif
                        </div>
                    </div>

                    <nav class="flex items-center gap-0.5 border-b border-stone-200  bg-stone-100">
                        @if ($selectedTheme['is_active'])
                            <button
                                type="button"
                                x-on:click="activeTab = 'customize'"
                                x-bind:class="activeTab === 'customize'
                                    ? 'border-b-2 border-primary-500 text-stone-900 font-semibold'
                                    : 'border-b-2 border-transparent text-stone-500 hover:text-stone-700'"
                                class="-mb-px px-3 py-2 text-xs transition sm:text-sm"
                            >
                                تخصيص القالب
                            </button>
                            <button
                                type="button"
                                x-on:click="activeTab = 'info'"
                                x-bind:class="activeTab === 'info'
                                    ? 'border-b-2 border-primary-500 text-stone-900 font-semibold'
                                    : 'border-b-2 border-transparent text-stone-500 hover:text-stone-700'"
                                class="-mb-px px-3 py-2 text-xs transition sm:text-sm"
                            >
                                معلومات
                            </button>
                        @else
                            <button
                                type="button"
                                x-on:click="activeTab = 'info'"
                                x-bind:class="activeTab === 'info'
                                    ? 'border-b-2 border-primary-500 text-stone-900 font-semibold'
                                    : 'border-b-2 border-transparent text-stone-500 hover:text-stone-700'"
                                class="-mb-px px-3 py-2 text-xs transition sm:text-sm"
                            >
                                معلومات
                            </button>
                            <button
                                type="button"
                                x-on:click="activeTab = 'customize'"
                                x-bind:class="activeTab === 'customize'
                                    ? 'border-b-2 border-primary-500 text-stone-900 font-semibold'
                                    : 'border-b-2 border-transparent text-stone-500 hover:text-stone-700'"
                                class="-mb-px px-3 py-2 text-xs transition sm:text-sm"
                            >
                                تخصيص القالب
                            </button>
                        @endif
                    </nav>
                </div>

                <div class="p-3">
                    <div x-cloak x-show="activeTab === 'info'" class="space-y-3">
                        @if (count($selectedTheme['gallery']) > 0)
                            <div class="no-scrollbar flex gap-2 overflow-x-auto">
                                @foreach ($selectedTheme['gallery'] as $image)
                                    <div class="shrink-0 overflow-hidden rounded-lg border border-stone-200 bg-stone-50">
                                        <img
                                            src="{{ $image }}"
                                            alt="{{ $selectedTheme['name'] }}"
                                            class="h-28 w-20 object-cover object-top sm:h-32 sm:w-24"
                                            loading="lazy"
                                        >
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        <div class="grid gap-3 lg:grid-cols-2">
                            <div class="overflow-hidden rounded-lg border border-stone-200 bg-stone-50/60">
                                <div class="border-b border-stone-200 bg-white px-3 py-1.5">
                                    <p class="text-[11px] font-medium text-stone-400">معاينة القالب</p>
                                </div>
                                <div class="flex justify-center p-3">
                                    <img
                                        src="{{ $selectedTheme['preview_url'] }}"
                                        alt="{{ $selectedTheme['name'] }}"
                                        class="max-h-56 w-auto rounded-md border border-stone-200 bg-white shadow-sm sm:max-h-64"
                                    >
                                </div>
                            </div>

                            <div class="overflow-hidden rounded-lg border border-stone-200 bg-stone-50/60">
                                <div class="border-b border-stone-200 bg-white px-3 py-1.5">
                                    <p class="text-[11px] font-medium text-stone-400">معلومات القالب</p>
                                </div>

                                <dl class="divide-y divide-stone-200/80 px-3 py-1">
                                    <div class="flex items-center justify-between gap-3 py-1.5">
                                        <dt class="text-xs text-stone-500">المعرّف</dt>
                                        <dd class="text-xs font-medium text-stone-800">{{ $selectedTheme['slug'] }}</dd>
                                    </div>

                                    <div class="flex items-center justify-between gap-3 py-1.5">
                                        <dt class="text-xs text-stone-500">النوع</dt>
                                        <dd class="text-xs font-medium text-stone-800">{{ $selectedTheme['type'] }}</dd>
                                    </div>

                                    <div class="flex items-center justify-between gap-3 py-1.5">
                                        <dt class="text-xs text-stone-500">التطبيق</dt>
                                        <dd class="text-xs font-medium text-stone-800">{{ $selectedTheme['app'] }}</dd>
                                    </div>

                                    <div class="flex items-center justify-between gap-3 py-1.5">
                                        <dt class="text-xs text-stone-500">المصمم</dt>
                                        <dd class="text-xs font-medium text-stone-800">{{ $selectedTheme['designer'] }}</dd>
                                    </div>

                                    <div class="flex items-center justify-between gap-3 py-1.5">
                                        <dt class="text-xs text-stone-500">السعر</dt>
                                        <dd class="text-xs font-semibold text-green-600">{{ $selectedTheme['price_label'] }}</dd>
                                    </div>

                                    <div class="flex items-center justify-between gap-3 py-1.5">
                                        <dt class="text-xs text-stone-500">الحالة</dt>
                                        <dd>
                                            @if ($selectedTheme['is_active'])
                                                <ui:badge color="green" size="sm">مُفعّل على الصفحة</ui:badge>
                                            @else
                                                <ui:badge color="gray" size="sm">غير مُفعّل</ui:badge>
                                            @endif
                                        </dd>
                                    </div>
                                </dl>
                            </div>
                        </div>
                    </div>

                    <div x-cloak x-show="activeTab === 'customize'">
                        @if (count($themeOptionsSchema) > 0)
                            <ui:form wire:submit="saveThemeOptions" class="!p-0">
                                <div class="space-y-2">
                                    @foreach ($themeOptionsSchema as $key => $field)
                                        @include('admin.components.theme-option-field', [
                                            'key' => $key,
                                            'field' => $field,
                                            'value' => $themeOptions[$key] ?? ($field['default'] ?? ''),
                                            'upload' => $themeOptionUploads[$key] ?? null,
                                        ])
                                    @endforeach
                                </div>

                                <x-slot:footer>
                                    <ui:button target="saveThemeOptions" label="{{ __('Save') }}" />
                                </x-slot>
                            </ui:form>
                        @else
                            <div class="rounded-lg bg-stone-50 px-4 py-6 text-center text-sm text-stone-500">
                                لا توجد خيارات متاحة لهذا القالب
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endif
    @endif
</div>

<?php

use App\Models\Theme;
use Illuminate\Support\Collection;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

new class extends \Livewire\Component
{
    use WithFileUploads;

    /** @var array<string, mixed> */
    public array $tab = [];

    public ?int $selectedThemeId = null;

    public ?int $tenantThemeId = null;

    /** @var array<string, array<string, mixed>> */
    public array $themeOptionsSchema = [];

    /** @var array<string, mixed> */
    public array $themeOptions = [];

    /** @var array<string, mixed> */
    public array $themeOptionUploads = [];

    public function mount(): void
    {
        $tenant = currentTenant();

        $this->tenantThemeId = $tenant?->theme_id;

        $firstThemeId = Theme::query()
            ->where('active', true)
            ->where('public', true)
            ->orderBy('sort')
            ->value('id');

        $this->selectedThemeId = $this->tenantThemeId ?? $firstThemeId;

        $this->loadThemeOptions();
    }

    public function selectTheme(int $themeId): void
    {
        $exists = Theme::query()
            ->where('id', $themeId)
            ->where('active', true)
            ->where('public', true)
            ->exists();

        if (! $exists) {
            return;
        }

        $this->selectedThemeId = $themeId;
        $this->loadThemeOptions();
    }

    public function saveThemeOptions(): void
    {
        $tenant = currentTenant();

        if (! $tenant || $this->themeOptionsSchema === []) {
            return;
        }

        $rules = [];

        foreach ($this->themeOptionsSchema as $key => $field) {
            if (($field['type'] ?? null) !== 'upload-single-image') {
                continue;
            }

            if (! ($this->themeOptionUploads[$key] ?? null) instanceof TemporaryUploadedFile) {
                continue;
            }

            $rules['themeOptionUploads.'.$key] = ['nullable', 'image', 'max:15024'];
        }

        if ($rules !== []) {
            $this->validate($rules);
        }

        foreach ($this->themeOptionsSchema as $key => $field) {
            if (($field['type'] ?? null) !== 'upload-single-image') {
                continue;
            }

            if (! ($this->themeOptionUploads[$key] ?? null) instanceof TemporaryUploadedFile) {
                continue;
            }

            $this->themeOptions[$key] = $tenant->uploadThemeOptionMedia(
                $this->selectedThemeId,
                $key,
                $this->themeOptionUploads[$key],
            );

            unset($this->themeOptionUploads[$key]);
        }

        $tenant->saveThemeSettingsFor($this->selectedThemeId, $this->themeOptions);

        $this->dispatch('notify', text: __('Settings updated successfully.'));
    }

    protected function loadThemeOptions(): void
    {
        $this->themeOptionsSchema = [];
        $this->themeOptions = [];
        $this->themeOptionUploads = [];

        if (! $this->selectedThemeId) {
            return;
        }

        $theme = Theme::query()
            ->where('id', $this->selectedThemeId)
            ->where('active', true)
            ->where('public', true)
            ->first(['slug']);

        if (! $theme) {
            return;
        }

        $optionsPath = public_path('themes/'.$theme->slug.'/options.json');

        if (! is_file($optionsPath)) {
            return;
        }

        $schema = json_decode((string) file_get_contents($optionsPath), true);

        if (! is_array($schema)) {
            return;
        }

        $this->themeOptionsSchema = $schema;
        $saved = currentTenant()?->themeSettingsFor($this->selectedThemeId) ?? [];

        foreach ($schema as $key => $field) {
            $this->themeOptions[$key] = data_get($saved, $key, $field['default'] ?? '');
        }
    }

    public function setDefaultTheme(): void
    {
        $tenant = currentTenant();

        if (! $tenant || ! $this->selectedThemeId) {
            return;
        }

        $themeExists = Theme::query()
            ->where('id', $this->selectedThemeId)
            ->where('active', true)
            ->where('public', true)
            ->exists();

        if (! $themeExists) {
            return;
        }

        $tenant->update(['theme_id' => $this->selectedThemeId]);
        $this->tenantThemeId = $this->selectedThemeId;
        $this->loadThemeOptions();

        $this->dispatch('notify', text: 'تم تعيين القالب الافتراضي بنجاح.');
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    protected function themes(): Collection
    {
        return Theme::query()
            ->where('active', true)
            ->where('public', true)
            ->orderBy('sort')
            ->get(['id', 'name', 'slug', 'meta', 'config', 'type', 'app'])
            ->map(fn (Theme $theme): array => $this->mapTheme($theme));
    }

    /**
     * @return array<string, mixed>
     */
    protected function mapTheme(Theme $theme): array
    {
        $preview = data_get($theme->meta, 'preview', 'assets/wjeez/themes/default.svg');
        $gallery = data_get($theme->meta, 'gallery', [$preview]);
        $price = data_get($theme->meta, 'price');

        return [
            'id' => $theme->id,
            'name' => $theme->name,
            'slug' => $theme->slug,
            'label_ar' => data_get($theme->meta, 'label_ar', $theme->name),
            'image_path' => $theme->image_path,
            'preview_url' => $theme->image_path,
            'gallery' => collect($gallery)->map(fn (string $path): string => asset($path))->all(),
            'type' => $theme->type,
            'app' => $theme->app,
            'designer' => data_get($theme->meta, 'designer', '—'),
            'price_label' => $this->formatPrice($price),
            'config' => $theme->config ?? [],
            'is_active' => $theme->id === $this->tenantThemeId,
        ];
    }

    protected function formatPrice(mixed $price): string
    {
        if ($price === null || $price === '' || (is_numeric($price) && (float) $price <= 0)) {
            return 'مجاني';
        }

        return number_format((float) $price, 0).' ر.س';
    }

    public function render()
    {
        $themes = $this->themes();

        return $this->view([
            'themes' => $themes,
            'selectedTheme' => $themes->firstWhere('id', $this->selectedThemeId),
            'themeOptionsSchema' => $this->themeOptionsSchema,
            'themeOptions' => $this->themeOptions,
            'themeOptionUploads' => $this->themeOptionUploads,
        ]);
    }
}; ?>
