<div class="p-4 text-center text-stone-500 text-xs mt-3">

    @if ($showDocumentsWarranties && $businessDocuments->isNotEmpty())
        <div class="mb-4 flex flex-wrap items-stretch justify-center gap-3">
            @foreach ($businessDocuments as $document)
                <div
                    wire:key="footer-document-{{ $document['key'] }}"
                    class="flex items-center gap-2 rounded-md bg-black/5 p-1.5"
                >
                    <img
                        src="{{ asset($document['logo']) }}"
                        alt="{{ $document['label'] }}"
                        class="h-9 w-auto shrink-0 object-contain"
                        loading="lazy"
                    >

                    <div class="min-w-0 text-end">
                        <p class="text-xs font-medium text-stone-500">{{ $document['label'] }}</p>
                        <p class="text-xs font-medium tracking-wide text-stone-900" dir="ltr">{{ $document['number'] }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    @if ($footerLinks->isNotEmpty() || $showDocumentsWarranties)
        <div class="flex flex-wrap justify-center items-center gap-x-5 gap-y-2 text-sm">
            @foreach ($footerLinks as $link)
                @if (filled($link['url']))
                    <a
                        href="{{ $link['url'] }}"
                        wire:key="footer-link-{{ $link['id'] }}"
                        @if ($link['opensInNewTab']) target="_blank" rel="noopener noreferrer" @else wire:navigate @endif
                        class="hover:text-stone-900 transition"
                    >
                        {{ $link['label'] }}
                    </a>
                @endif
            @endforeach
        </div>
    @endif

    <div class="mt-3 flex justify-center">
        <a href="https://eqleem.com" target="_blank" rel="noopener noreferrer" title="إقليم" aria-label="إقليم" class="inline-block text-stone-500 hover:text-stone-600 transition">
            <img class="h-6 w-auto" src="{{ asset('assets/images/logo-text-black.webp') }}" alt="إقليم">
        </a>
    </div>
</div>
