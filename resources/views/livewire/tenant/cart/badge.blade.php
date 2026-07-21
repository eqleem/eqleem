<a
    href="{{ route('tenant.pages.cart') }}"
    wire:navigate.hover
    x-data="{ loading: false }"
    x-on:click="loading = true"
    x-on:livewire:navigated.window="loading = false"
    x-bind:aria-busy="loading"
    class="relative bg-black/10 hover:bg-black/20 backdrop-blur-md p-2 px-3 rounded-xl  flex items-center gap-x-2 text-base"
    aria-label="{{ $count > 0 ? "سلة المشتريات، {$count} عناصر" : 'سلة المشتريات' }}"
>
    <span class="sr-only">{{ $count > 0 ? "سلة المشتريات، {$count} عناصر" : 'سلة المشتريات' }}</span>
    <iconify-icon
        x-show="! loading"
        icon="solar:cart-large-2-bold-duotone"
        class="inline text-2xl"
        stroke-width="2"
        aria-hidden="true"
    ></iconify-icon>
    <iconify-icon
        x-cloak
        x-show="loading"
        icon="solar:refresh-bold-duotone"
        class="inline text-2xl animate-spin"
        stroke-width="2"
        aria-hidden="true"
    ></iconify-icon>
    @if ($count > 0)
        <span
            x-show="! loading"
            class="absolute -top-2 -left-1 md:-left-2 text-sm leading-none px-1.5 py-1 rounded-full bg-stone-700 text-white font-geist"
            aria-hidden="true"
        >{{ $count }}</span>
    @endif
</a>
