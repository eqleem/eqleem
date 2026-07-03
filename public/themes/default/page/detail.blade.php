<x-tenant-theme::layout>
    <article class="px-2 md:px-4">
        <header class="space-y-6">
            <h1 class="max-w-3xl text-3xl font-black leading-tight text-stone-900 md:text-5xl">
                {{ $page->title }}
            </h1>

            @if ($subtitle !== '')
                <p class="max-w-3xl text-base leading-8 text-stone-600 md:text-xl md:leading-9">
                    {{ $subtitle }}
                </p>
            @endif

            @if ($page->published_at)
                <div class="text-sm text-stone-500">
                    <span class="font-semibold text-stone-400">تاريخ النشر</span>
                    <span class="font-semibold text-stone-800">{{ $page->published_at->translatedFormat('j F Y') }}</span>
                </div>
            @endif
        </header>

        @if ($body !== '')
            <div class="my-8 h-px w-full bg-stone-200"></div>

            <section class="prose prose-stone max-w-none text-lg leading-9 text-stone-700">
                {!! $body !!}
            </section>
        @endif

        @if ($pageBlocks->isNotEmpty())
            <section @class(['space-y-6', 'mt-10' => $body !== ''])>
                @foreach ($pageBlocks as $block)
                    @if (in_array($block->type, ['block-link', 'link'], true))
                        <livewire:tenant.blocks.block-link :block-id="$block->id" :key="'page-block-'.$block->id" />
                    @else
                        @includeFirst([
                            $block->variant,
                            "tenant-theme::blocks.{$block->type}",
                            "default-tenant-theme::blocks.{$block->type}",
                        ], ['block' => $block])
                    @endif
                @endforeach
            </section>
        @endif
    </article>
</x-tenant-theme::layout>
