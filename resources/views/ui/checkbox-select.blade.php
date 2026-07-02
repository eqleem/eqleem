@props([
    'name' => 'items',
    'label' => null,
    'info' => '',
    'placeholder' => 'اختر...',
    'options' => [],
    'selected' => [],
    'live' => false,
    'width' => '',
    'labelWidth' => 'w-36',
])

@php
    $selectedIds = collect($selected)->map(fn (mixed $id): string => (string) $id)->all();
    $selectedLabels = collect($options)
        ->filter(fn (array $option): bool => in_array((string) $option['id'], $selectedIds, true))
        ->pluck('label')
        ->all();
    $summary = $selectedLabels === []
        ? $placeholder
        : (count($selectedLabels) <= 2
            ? implode('، ', $selectedLabels)
            : count($selectedLabels).' محددة');
@endphp

<ui:field name="{{ $name }}" info="{{ $info }}" label="{{ __($label) }}" :width="$width" :labelWidth="$labelWidth">
    <div class="relative !min-w-[12rem]" x-data="{ open: false }" @click.outside="open = false">
        <button
            type="button"
            @click="open = !open"
            class="flex w-full items-center justify-between gap-2 rounded-md bg-white px-3 py-2 text-sm shadow-sm focus:outline-none focus:border-primary-400 border border-transparent"
        >
            <span @class(['truncate', 'text-gray-400' => $selectedLabels === [], 'text-gray-700' => $selectedLabels !== []])>
                {{ $summary }}
            </span>
            <ui:icon
                name="chevron-down"
                class="!w-4 !h-4 shrink-0 text-gray-400 transition"
                x-bind:class="open && 'rotate-180'"
            />
        </button>

        <div
            x-show="open"
            x-cloak
            x-transition
            class="absolute z-50 mt-1 max-h-44 !min-w-[12rem] w-full overflow-y-auto rounded-lg border border-gray-200 bg-white p-1 shadow-lg"
        >
            @forelse ($options as $option)
                @if ($option['selectable'] ?? true)
                    <label wire:key="{{ $name }}-{{ $option['id'] }}" class="flex cursor-pointer items-center gap-2 rounded px-2 py-1.5 text-sm text-gray-700 hover:bg-gray-50">
                        <input
                            type="checkbox"
                            @if ($live) wire:model.live="{{ $name }}" @else wire:model="{{ $name }}" @endif
                            value="{{ $option['id'] }}"
                            class="h-3.5 w-3.5 shrink-0 rounded border-gray-300"
                        >
                        <span class="min-w-0 truncate">{{ $option['label'] }}</span>
                    </label>
                @else
                    <div wire:key="{{ $name }}-group-{{ $option['id'] }}" class="truncate px-2 py-1 text-xs font-medium text-gray-400">
                        {{ $option['label'] }}
                    </div>
                @endif
            @empty
                <p class="px-2 py-2 text-xs text-gray-400">لا توجد خيارات</p>
            @endforelse
        </div>
    </div>
</ui:field>
