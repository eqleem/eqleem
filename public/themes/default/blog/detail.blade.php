<x-tenant-theme::blog.layout>
    <article class="px-2 md:px-4">
        <div class="mb-8 flex items-center justify-between gap-3">
            <a
                href="{{ route('tenant.blog.index') }}"
                wire:navigate
                class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-stone-100 text-stone-700 transition hover:bg-stone-200"
                aria-label="العودة إلى صفحة المدونة"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="m12 19 7-7-7-7"></path>
                    <path d="M5 12h14"></path>
                </svg>
            </a>

            <div class="flex items-center gap-2">
                <button class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-stone-100 text-stone-700 transition hover:bg-stone-200" aria-label="مشاركة المقال">
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

        <header class="space-y-6">
            @if ($categories->isNotEmpty())
                <div class="flex flex-wrap items-center gap-x-1 gap-y-1 text-sm font-medium text-stone-500">
                    @foreach ($categories as $category)
                        <a
                            href="{{ route('tenant.blog.index', ['category' => $category->slug]) }}"
                            wire:navigate
                            class="transition hover:text-orange-600"
                        >
                            {{ $category->name }}
                        </a>
                        @if (! $loop->last)
                            <span aria-hidden="true">،</span>
                        @endif
                    @endforeach
                </div>
            @endif

            <h1 class="max-w-3xl text-3xl font-black leading-tight text-stone-900 md:text-5xl">
                {{ $post->title }}
            </h1>

            @if ($subtitle !== '')
                <p class="max-w-3xl text-base leading-8 text-stone-600 md:text-xl md:leading-9">
                    {{ $subtitle }}
                </p>
            @endif

            <div class="flex flex-wrap items-end justify-between gap-4 text-sm text-stone-500 md:text-base">
                <div class="flex flex-wrap items-center gap-6">
                    <div class="flex items-center gap-3">
                        @if ($post->user)
                            <img
                                src="{{ $post->user->image }}"
                                alt="{{ $post->user->name }}"
                                class="size-8 lg:size-11 shrink-0 rounded-lg object-cover"
                            >
                        @endif

                        <div class="space-y-px">
                            <p class="text-xs font-semibold tracking-wide text-stone-700 md:text-sm"> {{ $post->user?->name }} </p>
                            @if ($post->published_at)
                            <p class="text-sm text-stone-500">{{ $post->published_at->translatedFormat('j F Y') }}</p>
                            @endif
                        </div>
                    </div>
 
                </div>

                {{-- <button class="inline-flex items-center gap-2 text-stone-400 transition hover:text-stone-600" aria-label="الإعجابات">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M2 9.5a5.5 5.5 0 0 1 9.591-3.676.56.56 0 0 0 .818 0A5.49 5.49 0 0 1 22 9.5c0 2.29-1.5 4-3 5.5l-5.492 5.313a2 2 0 0 1-3 .019L5 15c-1.5-1.5-3-3.2-3-5.5"></path>
                    </svg>
                    <span>0</span>
                </button> --}}
            </div>
        </header>

        <div class="my-8 h-px w-full bg-stone-100"></div>

        @if ($imageUrl)
            <figure class="overflow-hidden rounded-3xl border border-stone-200/80 bg-stone-50">
                <img
                    src="{{ $imageUrl }}"
                    alt="{{ $post->title }}"
                    class="h-full w-full object-cover"
                >
            </figure>
        @endif

        @if ($body !== '')
            <section class="mt-10 space-y-6 text-lg leading-9 text-stone-700 prose prose-stone max-w-none">
                {!! $body !!}
            </section>
        @endif
    </article>
</x-tenant-theme::blog.layout>
