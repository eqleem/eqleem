@props([
    'name' => 'items',
    'label' => null,
    'info' => '',
    'placeholder' => 'ابحث واختر...',
    'options' => [],
    'selected' => [],
    'live' => false,
    'width' => '',
    'labelWidth' => 'w-36',
    'searchable' => true,
    'searchName' => null,
    'openName' => null,
])

@php
    $selectedIds = collect($selected)->map(fn (mixed $id): string => (string) $id)->all();
    $selectableOptions = collect($options)->filter(fn (array $option): bool => $option['selectable'] ?? true);
    $selectedOptions = $selectableOptions
        ->filter(fn (array $option): bool => in_array((string) $option['id'], $selectedIds, true))
        ->values();
    $usesServerSearch = filled($searchName);
@endphp

<ui:field name="{{ $name }}" info="{{ $info }}" label="{{ __($label) }}" :width="$width" :labelWidth="$labelWidth">
    <div
        @if ($openName)
            x-data="{
                open: @entangle($openName).live,
                search: '',
                panelStyle: '',
                matches(label) {
                    if (@js($usesServerSearch) || ! this.search.trim()) {
                        return true;
                    }

                    return label.toLowerCase().includes(this.search.trim().toLowerCase());
                },
                updatePosition() {
                    if (! this.open || ! this.$refs.trigger) {
                        return;
                    }

                    const rect = this.$refs.trigger.getBoundingClientRect();

                    this.panelStyle = `position:fixed;top:${rect.bottom + 4}px;left:${rect.left}px;width:${Math.max(rect.width, 288)}px;z-index:9999;`;
                },
                toggle() {
                    this.open = ! this.open;

                    if (this.open) {
                        this.$nextTick(() => this.updatePosition());
                    }
                }
            }"
        @else
            x-data="{
                open: false,
                search: '',
                panelStyle: '',
                matches(label) {
                    if (@js($usesServerSearch) || ! this.search.trim()) {
                        return true;
                    }

                    return label.toLowerCase().includes(this.search.trim().toLowerCase());
                },
                updatePosition() {
                    if (! this.open || ! this.$refs.trigger) {
                        return;
                    }

                    const rect = this.$refs.trigger.getBoundingClientRect();

                    this.panelStyle = `position:fixed;top:${rect.bottom + 4}px;left:${rect.left}px;width:${Math.max(rect.width, 288)}px;z-index:9999;`;
                },
                toggle() {
                    this.open = ! this.open;

                    if (this.open) {
                        this.$nextTick(() => this.updatePosition());
                    }
                }
            }"
        @endif
        @click.outside="open = false"
        @resize.window="updatePosition()"
        @scroll.window="updatePosition()"
    >
        <div class="rounded-md border border-transparent bg-white px-3 py-2 text-sm shadow-sm focus-within:border-primary-400">
            @if ($selectedOptions->isNotEmpty())
                <div class="mb-2 flex flex-wrap gap-1.5">
                    @foreach ($selectedOptions as $option)
                        <span
                            wire:key="{{ $name }}-tag-{{ $option['id'] }}"
                            class="inline-flex items-center gap-1 rounded-full bg-primary-50 px-2.5 py-1 text-xs font-medium text-primary-700"
                        >
                            <span class="max-w-[12rem] truncate">{{ $option['label'] }}</span>
                            <button
                                type="button"
                                @click.stop="$wire.set('{{ $name }}', ($wire.get('{{ $name }}') || []).filter(id => String(id) !== @js((string) $option['id'])))"
                                class="rounded-full text-primary-500 hover:text-primary-700"
                                aria-label="إزالة {{ $option['label'] }}"
                            >
                                <ui:icon name="x" class="!h-3 !w-3" />
                            </button>
                        </span>
                    @endforeach
                </div>
            @endif

            <button
                type="button"
                x-ref="trigger"
                @click="toggle()"
                class="flex w-full items-center justify-between gap-2 text-start"
            >
                <span @class(['truncate text-gray-400' => $selectedOptions->isEmpty(), 'text-gray-600' => $selectedOptions->isNotEmpty()])>
                    {{ $selectedOptions->isEmpty() ? $placeholder : 'أضف المزيد...' }}
                </span>
                <ui:icon
                    name="chevron-down"
                    class="!h-4 !w-4 shrink-0 text-gray-400 transition"
                    x-bind:class="open && 'rotate-180'"
                />
            </button>
        </div>

        <template x-teleport="body">
            <div
                x-show="open"
                x-cloak
                x-transition
                :style="panelStyle"
                class="max-h-56 overflow-hidden rounded-lg border border-gray-200 bg-white shadow-2xl"
            >
                @if ($searchable)
                    <div class="border-b border-gray-100 p-2">
                        @if ($searchName)
                            <input
                                type="search"
                                wire:model.live.debounce.300ms="{{ $searchName }}"
                                placeholder="ابحث..."
                                class="w-full rounded-md border border-gray-200 px-2.5 py-1.5 text-sm text-gray-700 focus:border-primary-400 focus:outline-none"
                                @click.stop
                                @keydown.stop
                            >
                        @else
                            <input
                                type="search"
                                x-model="search"
                                placeholder="ابحث..."
                                class="w-full rounded-md border border-gray-200 px-2.5 py-1.5 text-sm text-gray-700 focus:border-primary-400 focus:outline-none"
                                @click.stop
                                @keydown.stop
                            >
                        @endif
                    </div>
                @endif

                <div class="max-h-44 overflow-y-auto p-1">
                    @forelse ($options as $option)
                        @if ($option['selectable'] ?? true)
                            <label
                                wire:key="{{ $name }}-option-{{ $option['id'] }}"
                                x-show="matches(@js($option['label']))"
                                class="flex cursor-pointer items-center gap-2 rounded px-2 py-1.5 text-sm text-gray-700 hover:bg-gray-50"
                            >
                                <input
                                    type="checkbox"
                                    @if ($live) wire:model.live="{{ $name }}" @else wire:model="{{ $name }}" @endif
                                    value="{{ $option['id'] }}"
                                    class="h-3.5 w-3.5 shrink-0 rounded border-gray-300"
                                >
                                <span class="min-w-0 truncate">{{ $option['label'] }}</span>
                            </label>
                        @else
                            <div
                                wire:key="{{ $name }}-group-{{ $option['id'] }}"
                                x-show="matches(@js($option['label']))"
                                class="truncate px-2 py-1 text-xs font-medium text-gray-400"
                            >
                                {{ $option['label'] }}
                            </div>
                        @endif
                    @empty
                        <p class="px-2 py-2 text-xs text-gray-400">لا توجد خيارات</p>
                    @endforelse
                </div>
            </div>
        </template>
    </div>
</ui:field>
