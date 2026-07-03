<x-tenant-theme::portfolio.layout>
    <section class="px-2 mb-8 w-full flex items-center justify-between gap-3">
        <div class="flex items-center gap-3 overflow-x-auto no-scrollbar bg-stone-200/40 w-full rounded-2xl p-1 whitespace-nowrap">
            <a
                href="{{ route('tenant.portfolio.index') }}"
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
                    href="{{ route('tenant.portfolio.index', ['category' => $category->slug]) }}"
                    wire:click.prevent="$set('categorySlug', '{{ $category->slug }}')"
                    wire:key="portfolio-category-filter-{{ $category->id }}"
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
                    placeholder="ابحث في الأعمال..."
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
        @if ($projects->isEmpty())
            <div class="rounded-2xl bg-stone-100/80 p-8 text-center">
                <p class="text-base font-semibold text-stone-700">لا توجد أعمال حالياً</p>
                <p class="mt-2 text-sm text-stone-500">ستظهر المشاريع المنشورة هنا عند إضافتها من لوحة التحكم.</p>
            </div>
        @else
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($projects as $project)
                    @php
                        $projectCategories = $project->taxonomies;
                        $imageUrl = $project->getFirstMediaUrl('portfolio-media')
                            ?: contentImageUrl(data_get($project->data, 'images.0'))
                            ?: contentImageUrl(data_get($project->data, 'image'))
                            ?: $project->avatar;
                        $subtitle = (string) data_get($project->data, 'subtitle', '');
                    @endphp

                    <a
                        href="{{ route('tenant.portfolio.detail', $project->slug) }}"
                        wire:navigate
                        wire:key="portfolio-project-{{ $project->id }}"
                        class="group flex cursor-pointer flex-col gap-4"
                    >
                        <div class="relative aspect-[4/3] w-full overflow-hidden rounded-[16px] bg-stone-100 shadow-sm transition-all duration-300 group-hover:shadow-md">
                            <img
                                src="{{ $imageUrl }}"
                                alt="{{ $project->title }}"
                                class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"
                            >

                            @if ($projectCategories->isNotEmpty())
                                <div class="absolute bottom-3 left-3 flex gap-2">
                                    @foreach ($projectCategories->take(2) as $category)
                                        <span class="rounded-md bg-black/20 px-2 py-1 text-[10px] font-medium text-white backdrop-blur-md">
                                            {{ $category->name }}
                                        </span>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        <div>
                            <div class="mb-1 flex items-start justify-between">
                                <h3 class="text-base font-semibold tracking-tight text-stone-800 transition-colors group-hover:text-primary-600">
                                    {{ $project->title }}
                                </h3>
                            </div>

                            @if ($subtitle !== '')
                                <p class="mb-2 line-clamp-2 text-xs text-stone-400">{{ $subtitle }}</p>
                            @endif

                            @if ($projectCategories->isNotEmpty())
                                <div class="flex items-center gap-1 overflow-x-auto truncate no-scrollbar">
                                    @foreach ($projectCategories as $category)
                                        <span class="rounded-[6px] border border-transparent bg-stone-100 px-2 py-1 text-[10px] font-medium text-stone-500 transition-colors hover:border-stone-200">
                                            {{ $category->name }}
                                        </span>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </section>
</x-tenant-theme::portfolio.layout>
