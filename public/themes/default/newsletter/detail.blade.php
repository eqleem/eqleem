<x-tenant-theme::newsletter.layout>
    <div class="mb-5 flex items-center justify-between px-2">
        <a href="{{ route('tenant.newsletter.index') }}" wire:navigate class="flex h-10 w-10 rotate-180 items-center justify-center rounded-full bg-stone-100 transition hover:bg-stone-200">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5 text-stone-700"><path d="m12 19-7-7 7-7"></path><path d="M19 12H5"></path></svg>
        </a>
    </div>

    <div class="mx-auto max-w-md text-center md:max-w-2xl">
        <h1 class="mb-4 text-2xl font-extrabold text-stone-800 md:text-3xl">
            {{ $article['title'] }}
        </h1>
        <h3 class="my-3 text-base leading-tight text-stone-500 md:text-xl">
            {{ $article['excerpt'] }}
        </h3>
        <p class="mt-4 text-sm text-stone-400 md:text-base">
            <span class="rounded-md bg-primary-50 px-2 py-1 text-sm text-primary-600">{{ $article['date'] }}</span>
        </p>
    </div>

    <section class="px-2 md:px-4">
        <img
            src="{{ $article['image'] }}"
            alt="{{ $article['title'] }}"
            class="mx-auto my-10 h-full w-full rounded-2xl object-cover"
        >

        <div class="space-y-4 p-3 leading-8 text-stone-700">
            <p>{{ $article['content'][0] }}</p>
            <p>{{ $article['content'][1] }}</p>
            <p>{{ $article['content'][2] }}</p>
        </div>
    </section>
</x-tenant-theme::newsletter.layout>