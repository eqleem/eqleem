<x-tenant-theme::courses.layout>
    <section class="px-1 mb-5 w-full flex items-center justify-between gap-3">
        <div class="flex items-center gap-3 overflow-x-auto no-scrollbar bg-stone-200/40 rounded-2xl p-1 whitespace-nowrap w-full">
            <a
                href="{{ route('tenant.courses.index') }}"
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
                    href="{{ route('tenant.courses.index', ['category' => $category->slug]) }}"
                    wire:click.prevent="$set('categorySlug', '{{ $category->slug }}')"
                    wire:key="course-category-filter-{{ $category->id }}"
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
                    placeholder="ابحث في الدورات..."
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

    <section class="p-1">
        @if ($courses->isEmpty())
            <div class="rounded-2xl bg-stone-100/80 p-8 text-center">
                <p class="text-base font-semibold text-stone-700">لا توجد دورات حالياً</p>
                <p class="mt-2 text-sm text-stone-500">ستظهر الدورات المنشورة هنا عند إضافتها من لوحة التحكم.</p>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4 md:gap-6">
                @foreach ($courses as $course)
                    @php
                        $courseCategories = $course->taxonomies;
                        $imageUrl = $course->getFirstMediaUrl('course-media') ?: $course->avatar;
                        $hours = (int) data_get($course->data, 'hours', 0);
                        $price = (int) data_get($course->data, 'price', 0);
                    @endphp

                    <article wire:key="course-{{ $course->id }}" class="overflow-hidden rounded-xl bg-white transition md:rounded-2xl">
                        <a href="{{ route('tenant.courses.detail', $course->slug) }}" wire:navigate class="group block">
                            <div class="relative">
                                <img
                                    src="{{ $imageUrl }}"
                                    alt="{{ $course->title }}"
                                    class="h-56 w-full object-cover transition-all duration-500 group-hover:scale-105 md:h-64"
                                >

                                <div class="absolute top-2 end-2 inline-flex items-center rounded-full bg-black/50 px-3 py-1 text-xs text-white backdrop-blur">
                                    {{ $course->courseLevelLabel() }}
                                </div>

                                @if ($courseCategories->isNotEmpty())
                                    <div class="absolute bottom-2 start-2 flex flex-wrap gap-1">
                                        @foreach ($courseCategories->take(2) as $category)
                                            <span class="rounded-md bg-black/30 px-2 py-0.5 text-[10px] font-medium text-white backdrop-blur-md">
                                                {{ $category->name }}
                                            </span>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </a>

                        <div class="rounded-b-xl border border-neutral-200 border-t-0 p-4 md:rounded-b-2xl">
                            <a href="{{ route('tenant.courses.detail', $course->slug) }}" wire:navigate>
                                <h3 class="text-lg font-semibold tracking-tight text-stone-900">{{ $course->title }}</h3>
                            </a>

                            @if (filled(data_get($course->data, 'subtitle')))
                                <p class="mt-1 line-clamp-2 text-xs text-stone-500">{{ data_get($course->data, 'subtitle') }}</p>
                            @endif

                            <div class="mt-4 flex items-center justify-between text-sm text-stone-600">
                                <span class="inline-flex items-center gap-1">
                                    <iconify-icon icon="solar:clock-circle-bold-duotone" class="text-lg"></iconify-icon>
                                    @if ($hours > 0)
                                        {{ $hours }} ساعة
                                    @else
                                        {{ $course->courseLessonCount() }} دروس
                                    @endif
                                </span>

                                @if ($price > 0)
                                    <span class="text-xl font-bold text-stone-900" dir="ltr">{{ money_format($price) }}</span>
                                @endif
                            </div>

                            <div class="mt-4 grid grid-cols-2 gap-2">
                                <a href="{{ route('tenant.courses.detail', $course->slug) }}" wire:navigate class="inline-flex items-center justify-center gap-2 rounded-xl bg-stone-100 px-3 py-2 text-sm font-semibold text-stone-700 hover:bg-stone-200">
                                    التفاصيل
                                </a>
                                <button
                                    type="button"
                                    wire:click="addToCart({{ $course->id }})"
                                    wire:loading.attr="disabled"
                                    wire:target="addToCart({{ $course->id }})"
                                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-primary-50 px-3 py-2 text-sm font-semibold text-primary-700 hover:bg-primary-100"
                                >
                                    أضف للسلة
                                </button>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </section>
</x-tenant-theme::courses.layout>
