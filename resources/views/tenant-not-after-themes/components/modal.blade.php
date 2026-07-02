@props([
    'name',
    'maxWidth' => '4xl',
])

@php
    $maxWidthClasses = match ($maxWidth) {
        'sm' => 'max-w-sm',
        'md' => 'max-w-md',
        'lg' => 'max-w-lg',
        'xl' => 'max-w-xl',
        '2xl' => 'max-w-2xl',
        default => 'max-w-2xl',
    };
@endphp

<div
    x-data="{ open: false }"
    x-on:open-modal.window="if ($event.detail?.name === @js($name)) { open = true }"
    x-on:close-modal.window="if ($event.detail?.name === @js($name)) { open = false }"
    x-on:keydown.escape.window="open = false"
    x-effect="document.body.classList.toggle('overflow-hidden', open)"
>
    <template x-teleport="body">
        <div
            x-cloak
            x-show="open"
            x-transition.opacity.duration.200ms
            class="fixed inset-0 z-[999] flex items-center justify-center p-4"
            role="dialog"
            aria-modal="true"
        >
            <div
                class="absolute inset-0 bg-black/60 backdrop-blur-[1px]"
                x-on:click="open = false"
            ></div>

            <div
                x-show="open"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 translate-y-4 scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 scale-95"
                class="relative z-10 w-full {{ $maxWidthClasses }} rounded-3xl bg-white shadow-2xl ring-1 ring-black/5"
                x-on:click.outside="open = false"
            >
                <div class="flex items-center justify-between border-b border-stone-100 px-6 py-4">
                    <div class="text-lg font-semibold text-stone-900">
                        {{ $title ?? 'نافذة منبثقة' }}
                    </div>
                    <button
                        type="button"
                        class="inline-flex h-10 w-10 items-center justify-center rounded-full text-stone-500 transition hover:bg-stone-100 hover:text-stone-700"
                        x-on:click="open = false"
                    >
                        <span class="sr-only">Close modal</span>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M6 6l12 12M18 6L6 18" />
                        </svg>
                    </button>
                </div>

                <div class="px-6 py-5 text-sm text-stone-600">
                    {{ $slot }}
                </div>

                @isset($footer)
                    <div class="flex items-center justify-end gap-2 border-t border-stone-100 px-6 py-4">
                        {{ $footer }}
                    </div>
                @endisset
            </div>
        </div>
    </template>
</div>
