<a
    href="{{ route('tenant.pages.cart') }}"
    wire:navigate.hover
    class="relative bg-black/10 hover:bg-black/20 backdrop-blur-md p-2 px-3 rounded-xl text-black/50 flex items-center gap-x-2 text-base"
    aria-label="{{ $count > 0 ? "سلة المشتريات، {$count} عناصر" : 'سلة المشتريات' }}"
>
    <span class="sr-only">{{ $count > 0 ? "سلة المشتريات، {$count} عناصر" : 'سلة المشتريات' }}</span>
    <iconify-icon icon="solar:cart-large-2-bold-duotone" class="inline text-2xl" stroke-width="2" aria-hidden="true"></iconify-icon>
    @if ($count > 0)
        <span class="absolute -top-2 -left-1 md:-left-2 text-sm leading-none px-1.5 py-1 rounded-full bg-stone-700 text-white font-geist" aria-hidden="true">{{ $count }}</span>
    @endif
</a>
