<x-tenant-theme::module-layout>
    <section class="mb-6">
        <x-tenant-theme::breadcrumb :links="[['url' => null, 'title' => $page->title]]" />
    </section>
    <article class="px-2 md:px-4">
       
            @if ($subtitle !== '')
                <p class="max-w-3xl text-sm leading-8 text-stone-600 md:text-base md:leading-9">
                    {{ $subtitle }}
                </p>
                <div class="mt-3 mb-6 h-px w-full bg-stone-100"></div>
            @endif
  

        @if ($body !== '')
            

            <section class="prose prose-stone max-w-none text-base leading-9 text-stone-700">
                {!! $body !!}
            </section>
        @endif

        @if ($pageBlocks->isNotEmpty())
            <section @class(['space-y-6', 'mt-10' => $body !== ''])>
                @foreach ($pageBlocks as $block)
                    <x-tenant-page-block :block="$block" />
                @endforeach
            </section>
        @endif
    </article>
</x-tenant-theme::module-layout>
