<x-tenant-theme::blog.layout>
    <section class="px-2 mb-8 w-full flex items-center justify-between gap-3">
        <div class="flex items-center gap-3 overflow-x-auto no-scrollbar bg-stone-200/40 w-full rounded-2xl p-1 whitespace-nowrap">
            <a
                href="{{ route('tenant.blog.index') }}"
                wire:click.prevent="$set('categorySlug', null)"
                @class([
                    'p-3 text-center py-2.5 rounded-xl text-sm font-medium transition',
                    'bg-white text-stone-900 shadow-sm' => blank($categorySlug),
                    'hover:bg-stone-50 text-stone-600 hover:text-stone-900' => filled($categorySlug),
                ])
            >
                الكل
            </a>

            @foreach ($categories as $category)
                <a
                    href="{{ route('tenant.blog.index', ['category' => $category->slug]) }}"
                    wire:click.prevent="$set('categorySlug', '{{ $category->slug }}')"
                    wire:key="blog-category-filter-{{ $category->id }}"
                    @class([
                        'p-3 text-center py-2.5 rounded-xl text-sm font-medium transition',
                        'bg-white text-stone-900 shadow-sm' => $categorySlug === $category->slug,
                        'hover:bg-stone-50 text-stone-600 hover:text-stone-900' => $categorySlug !== $category->slug,
                    ])
                >
                    {{ $category->name }}
                </a>
            @endforeach
        </div>

        <div class="flex items-center gap-3" x-data="{ open: false }">
            <div x-show="open" x-transition class="hidden sm:block">
                <input
                    wire:model.live.debounce.300ms="search"
                    type="search"
                    placeholder="ابحث في التدوينات..."
                    class="w-44 rounded-xl border border-stone-200 bg-white px-3 py-2 text-sm text-stone-700 outline-none focus:border-stone-400 md:w-56"
                >
            </div>

            <button
                type="button"
                @click="open = !open"
                class="p-3 rounded-xl bg-stone-200/40 hover:bg-stone-200 flex items-center justify-center transition-all duration-200 hover:scale-105"
                aria-label="البحث"
            >
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" class="size-6 text-stone-700"><path d="m21 21-4.34-4.34"></path><circle cx="11" cy="11" r="8"></circle></svg>
            </button>
        </div>
    </section>

    <section>
        @if ($posts->isEmpty())
            <div class="rounded-2xl bg-stone-100/80 p-8 text-center">
                <p class="text-base font-semibold text-stone-700">لا توجد تدوينات حالياً</p>
                <p class="mt-2 text-sm text-stone-500">ستظهر التدوينات المنشورة هنا عند إضافتها من لوحة التحكم.</p>
            </div>
        @else
            <div class="space-y-5 md:space-y-6">
                @foreach ($posts as $post)
                    @php
                        $postCategories = $post->taxonomies;
                        $imageUrl = contentImageUrl(data_get($post->data, 'image')) ?? $post->avatar;
                        $subtitle = (string) data_get($post->data, 'subtitle', '');
                    @endphp

                    <article wire:key="blog-post-{{ $post->id }}" class="group bg-stone-100/80 hover:bg-stone-200/50 rounded-2xl p-2 md:p-4">
                        <a href="{{ route('tenant.blog.detail', $post->slug) }}" wire:navigate class="flex items-start gap-4 md:gap-6">
                            <img
                                src="{{ $imageUrl }}"
                                alt="{{ $post->title }}"
                                class="h-20 w-20 shrink-0 rounded-2xl object-cover md:h-28 md:w-28"
                            >

                            <div class="flex-1">
                                <h3 class="mb-2 text-base font-extrabold leading-tight text-stone-900 md:text-xl">
                                    {{ $post->title }}
                                </h3>

                                @if ($subtitle !== '')
                                    <p class="mb-3 text-sm text-stone-500 md:text-base">{{ $subtitle }}</p>
                                @endif

                                <p class="flex flex-wrap items-center gap-2 text-sm text-stone-400 md:text-base">
                                    <span class="text-base font-extrabold text-orange-600 md:text-lg">{{ tenant('name') }}</span>

                                    @if ($postCategories->isNotEmpty())
                                        <span>في {{ $postCategories->pluck('name')->join('، ') }}</span>
                                    @endif

                                    @if ($post->published_at)
                                        <span>· {{ $post->published_at->translatedFormat('j F Y') }}</span>
                                    @endif
                                </p>
                            </div>
                        </a>
                    </article>
                @endforeach
            </div>
        @endif
    </section>
</x-tenant-theme::blog.layout>
