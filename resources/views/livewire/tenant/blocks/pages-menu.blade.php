<div>
    @if ($publishedPages->isNotEmpty())
        <div class="relative" x-data="{ open: false }" @click.away="open = false">
            <button
                type="button"
                class="bg-black/10 hover:bg-black/20 backdrop-blur-md p-2 px-3 rounded-xl text-black/50 flex items-center gap-x-2 text-base"
                x-on:click="open = !open"
                aria-haspopup="true"
                x-bind:aria-expanded="open"
            >
                <iconify-icon icon="solar:documents-bold-duotone" class="inline text-2xl" stroke-width="1.5"></iconify-icon>
                <span class="hidden md:inline">الصفحات</span>
                <iconify-icon
                    icon="solar:alt-arrow-down-bold"
                    class="hidden md:inline text-base transition-transform duration-200"
                    x-bind:class="open ? 'rotate-180' : ''"
                ></iconify-icon>
            </button>

            <div
                x-show="open"
                x-transition
                x-cloak
                class="absolute end-0 top-full z-50 mt-2 w-56 max-w-[calc(100vw-1.5rem)] overflow-hidden rounded-xl border border-stone-200/80 bg-white/95 py-1 shadow-lg backdrop-blur-md"
            >
                @foreach ($publishedPages as $publishedPage)
                    <a
                        href="{{ route('tenant.page.detail', $publishedPage->slug) }}"
                        wire:navigate
                        wire:key="top-nav-page-{{ $publishedPage->id }}"
                        class="flex items-center gap-x-2.5 px-3 py-2.5 text-sm text-stone-700 transition hover:bg-stone-100"
                        x-on:click="open = false"
                    >
                        <iconify-icon
                            icon="{{ $pageMenuIcon($publishedPage->template) }}"
                            class="shrink-0 text-lg text-black/50"
                        ></iconify-icon>
                        <span class="min-w-0 truncate">{{ $publishedPage->title }}</span>
                    </a>
                @endforeach
            </div>
        </div>
    @endif
</div>
