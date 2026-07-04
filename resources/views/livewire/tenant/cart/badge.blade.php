<a href="{{ route('tenant.pages.cart') }}" wire:navigate class="relative bg-black/10 hover:bg-black/20 backdrop-blur-md p-2 px-3 rounded-xl text-stone-500 flex items-center gap-x-2 text-base">
    <iconify-icon icon="hugeicons:shopping-bag-01" class="inline text-2xl" stroke-width="2"></iconify-icon>
    @if ($count > 0)
        <span class="absolute -top-2 -left-1 md:-left-2 text-sm leading-none px-1.5 py-1 rounded-full bg-stone-700 text-white font-geist">{{ $count }}</span>
    @endif
</a>
