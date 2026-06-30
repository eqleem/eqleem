@props([
    'title' => null,
    'titleIcon' => null,
    'footer' => null,
    'size' => '3xl',
    'height' => 'auto',
    'name' => 'default',
    'escape' => true,
    'close' => true,
])

<template x-teleport="body">
<div {{ $attributes }} x-cloak x-data="{ showModal: false, activeModal: 'default' }"
    @if ($escape) @keydown.window.escape="showModal = false; activeModal: '-'" @endif
    @openmodal.window="activeModal = $event.detail.modal ? $event.detail.modal : 'default'; showModal = true;  document.body.className += ' overflow-hiddenXXX'; "
    @closemodal.window="activeModal = null; showModal = false;   "
    x-show="showModal && activeModal == @js($name); " class="relative z-40 " aria-labelledby="modal-title"
    role="dialog" aria-modal="true">
    <div class="fixed inset-0 bg-gray-800 bg-opacity-75 transition-opacity"
        x-show="showModal && activeModal == @js($name)" x-transition.opacity></div>

    <div class="fixed inset-0 overflow-y-auto">
        <div class="flex min-h-full justify-center items-center w-full">
            <div @if ($escape) @click.away="showModal = false" @endif
                class="relative transform overflow-hiddenX rounded-xl pb-2.5 bg-white min-h-[14vh]x shadow-xl transition-all w-full sm:max-w-{{ $size }} max-w-md h-{{ $height }}">
                <div class="flex items-center justify-between w-full p-2 border-b border-gray-100">
                    @if ($title)
                    <p class="text-gray-600 font-semibold flex items-center gap-x-2 text-sm px-2">
                        @if ($titleIcon)
                            <ui:icon name="{{ $titleIcon }}" class="w-5 h-5 text-gray-500" />
                        @endif
                        {{ $icon ?? '' }}
                        {{ $title }}
                    </p>
                    @else 
                    <span></span>
                    @endif
                    @if ($close)
                        <button @click.prevent="showModal=false" type="button"
                            class="rounded-md bg-gray-100 p-1 justify-end text-gray-400 hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                            <span class="sr-only">Close</span>
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    @endif
                </div>
                <div class="w-full">
                    {{ $slot }}
                </div>

                @if ($footer)
                    <div class="p-3 px-5 border-t-2 border-gray-100 shadow">
                        {{ $footer }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
</template>
