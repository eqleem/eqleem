<div class="space-y-4">
    @if ($themes->isEmpty())
        <div class="rounded-2xl bg-stone-100/50 p-8 text-center text-sm text-stone-500">
            لا توجد قوالب متاحة حالياً.
        </div>
    @else
        <div class="rounded-2xl bg-stone-300/30 p-3">
            <div class="no-scrollbar flex gap-3 overflow-x-auto">
                @foreach ($themes as $theme)
                    <button
                        type="button"
                        wire:click="selectTheme({{ $theme['id'] }})"
                        wire:key="theme-thumb-{{ $theme['id'] }}"
                        @class([
                            'group relative w-28 shrink-0 rounded-xl border-2 bg-transparent text-start transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary-500/40 sm:w-48',
                            'border-primary-500 shadow-md border-primary-500/15' => $selectedThemeId === $theme['id'],
                            'border-transparent hover:border-stone-200 hover:shadow-sm bg-white' => $selectedThemeId !== $theme['id'],
                        ])
                    >
                        @if ($theme['is_active'])
                            <span class="absolute start-2 top-2 z-10 inline-flex items-center gap-1 rounded-full bg-green-500 px-2 py-0.5 text-[10px] font-semibold text-white shadow-sm">
                                <ui:icon name="Check" class="!h-3 !w-3" />
                                نشط
                            </span>
                        @endif

                        <div class="overflow-hidden rounded-t-[10px] bg-stone-100">
                            <img
                                src="{{ $theme['preview_url'] }}"
                                alt="{{ $theme['name'] }}"
                                class="aspect-[2/2] w-full object-coverx object-top transition duration-300 group-hover:scale-[1.02]"
                                loading="lazy"
                            >
                        </div>

                        <div class="p-3 bg-white rounded-b-xl">
                            <p class="truncate text-xs font-semibold text-stone-700">{{ $theme['name'] }}</p>
                        </div>
                    </button>
                @endforeach
            </div>
        </div>

        @if ($selectedTheme)
            <div
                class="overflow-hidden rounded-2xl bg-white shadow-sm"
                wire:key="theme-details-{{ $selectedTheme['id'] }}-{{ $selectedTheme['is_active'] ? 'active' : 'inactive' }}"
            >
                <div class="flex flex-col gap-3 border-b border-stone-100 p-4 sm:flex-row sm:items-center sm:justify-between">
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <h3 class="text-lg font-semibold text-stone-900">{{ $selectedTheme['name'] }}</h3>

                            @if ($selectedTheme['is_active'])
                                <ui:badge color="green" size="sm">القالب النشط</ui:badge>
                            @endif
                        </div>

                        <p class="mt-0.5 text-sm text-stone-500">{{ $selectedTheme['label_ar'] }}</p>
                    </div>

                    <div class="shrink-0">
                        @if ($selectedTheme['is_active'])
                            <div class="inline-flex items-center gap-2 rounded-lg border border-green-200 bg-green-50 px-3 py-2 text-sm font-medium text-green-700">
                                <ui:icon name="circle-check" class="!h-4 !w-4" />
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

                <div
                    x-data="{ activeTab: @js($selectedTheme['is_active'] ? 'customize' : 'info') }"
                    class="p-4 sm:p-5"
                >
                    <nav class="mb-5 flex w-fit items-center gap-1 rounded-xl bg-stone-100/80 p-0.5">
                        @if ($selectedTheme['is_active'])
                            <button
                                type="button"
                                x-on:click="activeTab = 'customize'"
                                x-bind:class="activeTab === 'customize'
                                    ? 'bg-white text-stone-900 shadow-sm font-semibold'
                                    : 'text-stone-600 hover:bg-white/60 hover:text-stone-800'"
                                class="rounded-lg px-4 py-2 text-sm transition"
                            >
                                تخصيص القالب
                            </button>
                            <button
                                type="button"
                                x-on:click="activeTab = 'info'"
                                x-bind:class="activeTab === 'info'
                                    ? 'bg-white text-stone-900 shadow-sm font-semibold'
                                    : 'text-stone-600 hover:bg-white/60 hover:text-stone-800'"
                                class="rounded-lg px-4 py-2 text-sm transition"
                            >
                                معلومات
                            </button>
                        @else
                            <button
                                type="button"
                                x-on:click="activeTab = 'info'"
                                x-bind:class="activeTab === 'info'
                                    ? 'bg-white text-stone-900 shadow-sm font-semibold'
                                    : 'text-stone-600 hover:bg-white/60 hover:text-stone-800'"
                                class="rounded-lg px-4 py-2 text-sm transition"
                            >
                                معلومات
                            </button>
                            <button
                                type="button"
                                x-on:click="activeTab = 'customize'"
                                x-bind:class="activeTab === 'customize'
                                    ? 'bg-white text-stone-900 shadow-sm font-semibold'
                                    : 'text-stone-600 hover:bg-white/60 hover:text-stone-800'"
                                class="rounded-lg px-4 py-2 text-sm transition"
                            >
                                تخصيص القالب
                            </button>
                        @endif
                    </nav>

                    <div x-cloak x-show="activeTab === 'info'" class="space-y-5">
                        @if (count($selectedTheme['gallery']) > 0)
                            <div class="no-scrollbar flex gap-3 overflow-x-auto pb-1">
                                @foreach ($selectedTheme['gallery'] as $image)
                                    <div class="shrink-0 overflow-hidden rounded-xl border border-stone-200 bg-stone-50">
                                        <img
                                            src="{{ $image }}"
                                            alt="{{ $selectedTheme['name'] }}"
                                            class="h-36 w-24 object-cover object-top sm:h-44 sm:w-28"
                                            loading="lazy"
                                        >
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        <div class="grid gap-5 lg:grid-cols-2">
                            <div class="overflow-hidden rounded-xl border border-stone-200 bg-stone-50/60">
                                <div class="border-b border-stone-200 bg-white px-4 py-2.5">
                                    <p class="text-xs font-medium text-stone-400">معاينة القالب</p>
                                </div>
                                <div class="flex justify-center p-4">
                                    <img
                                        src="{{ $selectedTheme['preview_url'] }}"
                                        alt="{{ $selectedTheme['name'] }}"
                                        class="max-h-80 w-auto rounded-lg border border-stone-200 bg-white shadow-sm"
                                    >
                                </div>
                            </div>

                            <div class="overflow-hidden rounded-xl border border-stone-200 bg-stone-50/60">
                                <div class="border-b border-stone-200 bg-white px-4 py-2.5">
                                    <p class="text-xs font-medium text-stone-400">معلومات القالب</p>
                                </div>

                                <dl class="divide-y divide-stone-200/80 p-4">
                                    <div class="flex items-center justify-between gap-4 py-2.5">
                                        <dt class="text-sm text-stone-500">المعرّف</dt>
                                        <dd class="text-sm font-medium text-stone-800">{{ $selectedTheme['slug'] }}</dd>
                                    </div>

                                    <div class="flex items-center justify-between gap-4 py-2.5">
                                        <dt class="text-sm text-stone-500">النوع</dt>
                                        <dd class="text-sm font-medium text-stone-800">{{ $selectedTheme['type'] }}</dd>
                                    </div>

                                    <div class="flex items-center justify-between gap-4 py-2.5">
                                        <dt class="text-sm text-stone-500">التطبيق</dt>
                                        <dd class="text-sm font-medium text-stone-800">{{ $selectedTheme['app'] }}</dd>
                                    </div>

                                    <div class="flex items-center justify-between gap-4 py-2.5">
                                        <dt class="text-sm text-stone-500">المصمم</dt>
                                        <dd class="text-sm font-medium text-stone-800">{{ $selectedTheme['designer'] }}</dd>
                                    </div>

                                    <div class="flex items-center justify-between gap-4 py-2.5">
                                        <dt class="text-sm text-stone-500">السعر</dt>
                                        <dd class="text-sm font-semibold text-stone-800">{{ $selectedTheme['price_label'] }}</dd>
                                    </div>

                                    <div class="flex items-center justify-between gap-4 py-2.5">
                                        <dt class="text-sm text-stone-500">الحالة</dt>
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
                        <div class="rounded-xl border border-dashed border-stone-200 bg-stone-50/50 px-4 py-10 text-center text-sm text-stone-500">
                            سيتم تعديلها لاحقاً ..
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @endif
</div>

<?php

use App\Models\Theme;
use Illuminate\Support\Collection;

new class extends \Livewire\Component
{
    /** @var array<string, mixed> */
    public array $tab = [];

    public ?int $selectedThemeId = null;

    public ?int $tenantThemeId = null;

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
            'preview_url' => asset($preview),
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
        ]);
    }
}; ?>
