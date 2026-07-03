<x-tenant-theme::portfolio.layout>
    <article class="px-2 md:px-4">
        <div class="mb-8 flex items-center justify-between gap-3">
            <a
                href="{{ route('tenant.portfolio.index') }}"
                wire:navigate
                class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-stone-100 text-stone-700 transition hover:bg-stone-200"
                aria-label="العودة إلى معرض الأعمال"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 rotate-180" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="m12 19-7-7 7-7"></path>
                    <path d="M19 12H5"></path>
                </svg>
            </a>

            <div class="flex items-center gap-2">
                <button class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-stone-100 text-stone-700 transition hover:bg-stone-200" aria-label="مشاركة المشروع">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 2v13"></path>
                        <path d="m16 6-4-4-4 4"></path>
                        <path d="M4 12v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8"></path>
                    </svg>
                </button>
                <button class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-stone-100 text-stone-700 transition hover:bg-stone-200" aria-label="إضافة إلى المفضلة">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M2 9.5a5.5 5.5 0 0 1 9.591-3.676.56.56 0 0 0 .818 0A5.49 5.49 0 0 1 22 9.5c0 2.29-1.5 4-3 5.5l-5.492 5.313a2 2 0 0 1-3 .019L5 15c-1.5-1.5-3-3.2-3-5.5"></path>
                    </svg>
                </button>
            </div>
        </div>

        <section class="mb-8 w-full">
            <div class="grid grid-cols-1 gap-12 md:grid-cols-2">
                @if ($images !== [])
                    <div class="hidden space-y-3 lg:block">
                        @foreach ($images as $image)
                            <div class="aspect-w-16 aspect-h-9 overflow-hidden rounded-lg">
                                <img class="h-full w-full object-cover" src="{{ $image }}" alt="{{ $project->title }}">
                            </div>
                        @endforeach
                    </div>

                    <div
                        class="block lg:hidden"
                        x-data="{ activeImage: @js($images[0]) }"
                    >
                        <div class="mb-4 aspect-square overflow-hidden rounded-2xl bg-stone-100">
                            <img
                                :src="activeImage"
                                alt="{{ $project->title }}"
                                class="h-full w-full object-cover"
                            >
                        </div>

                        @if (count($images) > 1)
                            <div class="flex gap-3 overflow-x-auto pb-2">
                                @foreach ($images as $image)
                                    <button
                                        type="button"
                                        @click="activeImage = @js($image)"
                                        class="gallery-nav h-20 w-20 shrink-0 overflow-hidden rounded-lg border-2 bg-stone-100"
                                        :class="activeImage === @js($image) ? 'border-stone-900' : 'border-transparent hover:border-stone-300'"
                                    >
                                        <img src="{{ $image }}" alt="{{ $project->title }}" class="h-full w-full object-cover">
                                    </button>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @elseif ($imageUrl)
                    <div class="overflow-hidden rounded-2xl bg-stone-100">
                        <img class="aspect-[4/3] w-full object-cover" src="{{ $imageUrl }}" alt="{{ $project->title }}">
                    </div>
                @endif

                <div>
                    @if ($categories->isNotEmpty())
                        <div class="mb-4 flex flex-wrap items-center gap-2">
                            @foreach ($categories as $category)
                                <a
                                    href="{{ route('tenant.portfolio.index', ['category' => $category->slug]) }}"
                                    wire:navigate
                                    class="inline-flex items-center rounded-md bg-stone-100 px-2 py-1 text-xs font-medium text-stone-700 transition hover:bg-stone-200"
                                >
                                    {{ $category->name }}
                                </a>
                            @endforeach
                        </div>
                    @endif

                    <h1 class="mb-2 text-xl font-bold tracking-tight text-stone-900 md:text-2xl">
                        {{ $project->title }}
                    </h1>

                    @if ($subtitle !== '')
                        <p class="mb-5 text-base leading-8 text-stone-600">
                            {{ $subtitle }}
                        </p>
                    @endif

                    <div class="mb-8 flex flex-col gap-4">
                        @if ($project->published_at)
                            <p class="text-sm text-stone-500">
                                {{ $project->published_at->translatedFormat('j F Y') }}
                            </p>
                        @endif
                    </div>

                    @if ($body !== '')
                        <div class="border-t border-stone-200 pt-8">
                            <h3 class="mb-4 text-lg font-semibold">تفاصيل المشروع</h3>
                            <div class="prose prose-stone max-w-none space-y-3 text-base text-stone-600">
                                {!! $body !!}
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </section>
    </article>
</x-tenant-theme::portfolio.layout>
