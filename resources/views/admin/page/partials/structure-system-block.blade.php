@props(['block'])

<li
    wire:key="system-block-{{ $block['id'] }}"
    class="flex items-center gap-2 rounded-lg border border-transparent bg-white px-2 py-2"
>
    <div class="rounded-md p-1 text-gray-200">
        <ui:icon name="Lock" class="!w-4 !h-4" />
    </div>

    @if ($block['editable'])
        <button
            type="button"
            wire:click="openEditBlockModal({{ $block['id'] }})"
            class="flex flex-1 min-w-0 items-center gap-2 text-start hover:text-primary-600 transition"
        >
            <img
                src="{{ $block['icon_url'] }}"
                alt=""
                class="w-6 h-6 shrink-0 rounded-md bg-gray-100 p-1"
            >
            <span class="text-sm font-medium text-gray-800 truncate">{{ $block['title'] }}</span>
        </button>
    @else
        <div class="flex flex-1 min-w-0 items-center gap-2">
            <img
                src="{{ $block['icon_url'] }}"
                alt=""
                class="w-6 h-6 shrink-0 rounded-md bg-gray-100 p-1"
            >
            <span class="text-sm font-medium text-gray-800 truncate">{{ $block['title'] }}</span>
        </div>
    @endif

    @if ($block['editable'])
        <button
            type="button"
            wire:click="openEditBlockModal({{ $block['id'] }})"
            class="rounded-lg p-1 text-gray-400 hover:bg-gray-100 hover:text-primary-600 transition"
            aria-label="خيارات البلوك"
        >
            <ui:icon name="settings" class="!w-5 !h-5" />
        </button>
    @endif
</li>
