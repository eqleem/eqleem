<ui:container>
    <div
        x-data="{
            activeTab: @entangle('activeTab'),
            tabs: @js($tabs),
            init() {
                this.syncFromUrl();
                window.addEventListener('popstate', () => this.syncFromUrl());
                document.addEventListener('livewire:navigated', () => this.syncFromUrl());
            },
            syncFromUrl() {
                const params = new URLSearchParams(window.location.search);
                const tab = params.get('tab');
                const item = params.get('item');

                if (tab && this.tabs.some((entry) => entry.id === tab)) {
                    this.activeTab = tab;
                }

                $wire.set('activeItem', item || null);
            },
            setTab(id) {
                this.activeTab = id;
                $wire.set('activeItem', null);

                const url = new URL(window.location.href);
                url.searchParams.set('tab', id);
                url.searchParams.delete('item');
                history.pushState({ tab: id }, '', url);
            },
            isActive(id) {
                return this.activeTab === id;
            },
        }"
        class="flex lg:gap-5 gap-2 items-start"
    >
        @php
            $contentTabBgClasses = collect(config('content-types', []))
                ->flatMap(fn (array $type): array => [
                    \App\Support\ContentType::backgroundClassFor($type['color'] ?? 'gray'),
                    \App\Support\ContentType::hoverBackgroundClassFor($type['color'] ?? 'gray'),
                ])
                ->filter()
                ->unique()
                ->implode(' ');
        @endphp
        <div class="hidden {{ $contentTabBgClasses }} bg-[var(--tab-bg)] hover:bg-[var(--tab-bg)]" aria-hidden="true"></div>

        {{-- Tab nav (right in RTL) --}}
        <nav class="lg:w-48 w-auto shrink-0 bg-gray-300/30 rounded-xl p-0.5 space-y-0.5">
            <template x-for="tab in tabs.filter((item) => item.type === 'fixed')" :key="tab.id">
                <button
                    type="button"
                    x-on:click="setTab(tab.id)"
                    x-bind:class="isActive(tab.id)
                        ? 'bg-white text-gray-700  '
                        : 'text-gray-600 hover:bg-white/60 hover:text-gray-800'"
                    class="w-full text-start px-3 py-2.5 rounded-lg bg-stone-100/50 text-sm transition flex items-center gap-2"
                >
                    <img x-show="tab.icon_url" x-bind:src="tab.icon_url" x-bind:alt="tab.label" class="w-5 h-5 shrink-0">
                    <span x-text="tab.label" class="truncate hidden md:block"></span>
                </button>
            </template>

            <div class="">
                <p class="text-xs text-gray-400 px-3 py-1 mt-3 hidden md:block">المحتوى</p>
                <div class="border-t border-dotted border-gray-300 lg:mb-2 mb-6 mx-1"></div>
            </div>
 
            <template x-for="tab in tabs.filter((item) => item.type === 'content')" :key="tab.id">
                <button
                    type="button"
                    x-on:click="setTab(tab.id)"
                    x-bind:style="tab.color_bg_hex ? { '--tab-bg': tab.color_bg_hex } : null"
                    x-bind:class="[
                        isActive(tab.id) && tab.color_bg_class ? tab.color_bg_class : '',
                        isActive(tab.id) && tab.color_bg_hex ? 'bg-[var(--tab-bg)]' : '',
                        ! isActive(tab.id) && tab.color_hover_class ? tab.color_hover_class : '',
                        ! isActive(tab.id) && tab.color_bg_hex ? 'hover:bg-[var(--tab-bg)]' : '',
                        isActive(tab.id) ? 'text-gray-900' : 'text-gray-600 hover:text-gray-800',
                    ].filter(Boolean).join(' ')"
                    class="w-full text-start rounded-lg bg-stone-100/50 text-sm transition flex items-center gap-2  "
                >
                    <span
                        x-bind:style="tab.color_bg_hex ? { backgroundColor: tab.color_bg_hex } : null"
                        x-bind:class="[tab.color_bg_class, 'max-md:!bg-transparent shrink-0 flex items-center justify-center rounded-s-lg p-2'].filter(Boolean).join(' ')"
                    >
                        <img x-bind:src="tab.icon_url" x-bind:alt="tab.label" class="w-5 h-5">
                    </span>
                    <span x-text="tab.label" class="truncate hidden md:block"></span>
                </button>
            </template>
        </nav>

        {{-- Tab content (left in RTL) --}}
        <div class="flex-1 min-w-0">
            @foreach ($pageTabs as $pageTab)
                @if ($activeTab === $pageTab->tabId())
                    @livewire($pageTab->component, ['tab' => $pageTab->toArray()], key('page-tab-'.$pageTab->slug))
                @endif
            @endforeach

            @foreach ($contentTypes as $contentType)
                @if ($activeTab === $contentType->tabId())
                    <div wire:key="page-tab-{{ $contentType->slug }}-{{ $activeItem ?? 'index' }}">
                        @if ($activeItem && ($detail = $contentType->component('detail')))
                            @livewire($detail, [
                                'contentType' => $contentType->toArray(),
                                'itemId' => $activeItem,
                            ], key('page-content-detail-'.$contentType->slug.'-'.$activeItem))
                        @elseif ($index = $contentType->component('index'))
                            @livewire($index, [
                                'contentType' => $contentType->toArray(),
                            ], key('page-content-index-'.$contentType->slug))
                        @endif
                    </div>
                @endif
            @endforeach
        </div>
    </div>
</ui:container>

<?php

use App\Support\ContentTypeRegistry;
use App\Support\PageTabRegistry;
use Livewire\Attributes\On;

new class extends \Livewire\Component
{
    public string $activeTab = 'structure';

    public ?string $activeItem = null;

    #[On('openContentItem')]
    public function openContentItem(string $tab, string $item): void
    {
        $this->activeTab = $tab;
        $this->activeItem = $item;

        $url = route('admin.page.home', [
            'tab' => $tab,
            'item' => $item,
        ]);

        $this->js('history.pushState({}, "", '.json_encode($url).')');
    }

    public function mount(PageTabRegistry $pageTabs, ContentTypeRegistry $contentTypes): void
    {
        $tab = request()->query('tab', 'structure');
        $validIds = array_column($this->tabs($pageTabs, $contentTypes), 'id');

        if (in_array($tab, $validIds, true)) {
            $this->activeTab = $tab;
        }

        $item = request()->query('item');

        $this->activeItem = filled($item) ? (string) $item : null;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function tabs(PageTabRegistry $pageTabs, ContentTypeRegistry $contentTypes): array
    {
        return array_merge($pageTabs->tabs(), $contentTypes->tabs());
    }

    public function render(PageTabRegistry $pageTabs, ContentTypeRegistry $contentTypes)
    {
        return $this->view([
            'tabs' => $this->tabs($pageTabs, $contentTypes),
            'pageTabs' => $pageTabs->all(),
            'contentTypes' => $contentTypes->all(),
        ])
            ->layout('admin::layout')
            ->title(__('Manage page'));
    }
}; ?>
