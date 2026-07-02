<div>
    @php
        $visibleCtaLinks = $ctaLinks->filter(fn ($link) => $link['isForm'] || filled($link['url']));
        $ctaLinksCount = $visibleCtaLinks->count();
        $ctaLinksRemainder = $ctaLinksCount % 3;
    @endphp
    @if($visibleCtaLinks->isNotEmpty())
    <div @class([
        'grid w-full gap-4 mb-6 animate-fade-in-up delay-300',
        'grid-cols-1' => $ctaLinksCount === 1,
        'grid-cols-2' => $ctaLinksCount >= 2,
        'lg:grid-cols-3' => $ctaLinksCount >= 3,
    ])>
        @foreach($visibleCtaLinks as $link)
            @php
                $isLonelyMobileLast = $ctaLinksCount % 2 === 1 && $loop->last && $ctaLinksCount > 1;
                $isLonelyLgLast = $ctaLinksRemainder === 1 && $loop->last && $ctaLinksCount >= 3;
            @endphp

            @if($ctaLinksCount >= 3 && $ctaLinksRemainder === 2 && $loop->iteration === $ctaLinksCount - 1)
                <div class="contents lg:col-span-3 lg:grid lg:grid-cols-2 lg:gap-4">
            @endif

            @if($link['isForm'])
            <button
                type="button"
                wire:key="cta-link-{{ $link['id'] }}"
                @class([
                    'flex w-full items-center justify-center gap-2 bg-primary-600 hover:bg-primary-700 text-white text-base rounded-2xl px-4 py-3 font-medium transition-all duration-300 hover-lift',
                    'col-span-2 lg:col-span-1' => $isLonelyMobileLast && ! $isLonelyLgLast,
                    'col-span-2 lg:col-span-3' => $isLonelyMobileLast && $isLonelyLgLast,
                    'lg:col-span-3' => $isLonelyLgLast && ! $isLonelyMobileLast,
                ])
                x-on:click="$dispatch('open-modal', { name: 'cta-form-{{ $link['id'] }}' })"
            >
                <iconify-icon icon="{{ $link['icon'] }}" class="inline text-3xl" stroke-width="1.5"></iconify-icon>
                {{ $link['label'] }}
            </button>
            @elseif(filled($link['url']))
            <a
                href="{{ $link['url'] }}"
                wire:key="cta-link-{{ $link['id'] }}"
                @if($link['opensInNewTab']) target="_blank" rel="noopener noreferrer" @else wire:navigate @endif
                @class([
                    'flex w-full items-center text-white justify-center bg-primary-600 hover:bg-primary-700 transition-all duration-200 text-base font-medium font-geist rounded-2xl px-4 py-3 group relative overflow-hidden',
                    'col-span-2 lg:col-span-1' => $isLonelyMobileLast && ! $isLonelyLgLast,
                    'col-span-2 lg:col-span-3' => $isLonelyMobileLast && $isLonelyLgLast,
                    'lg:col-span-3' => $isLonelyLgLast && ! $isLonelyMobileLast,
                ])
            >
                <span class="relative z-10 flex items-center gap-2">
                    <iconify-icon icon="{{ $link['icon'] }}" class="inline text-3xl" stroke-width="1.5"></iconify-icon>
                    {{ $link['label'] }}
                </span>
            </a>
            @endif

            @if($ctaLinksCount >= 3 && $ctaLinksRemainder === 2 && $loop->last)
                </div>
            @endif
        @endforeach
    </div>
    @endif

    @foreach($ctaLinks as $link)
        @if($link['isForm'] && filled($link['formContentId']))
            <x-tenant-theme::modal wire:key="cta-form-modal-{{ $link['id'] }}" name="cta-form-{{ $link['id'] }}" maxWidth="md">
                <x-slot:title>{{ $link['label'] }}</x-slot:title>

                <livewire:tenant.forms.submit
                    :form-content-id="$link['formContentId']"
                    :block-id="$block?->id"
                    :key="'cta-form-submit-'.$link['id']"
                />

                <x-slot:footer>
                    <button type="button" x-on:click="$dispatch('close-modal', { name: 'cta-form-{{ $link['id'] }}' })" class="rounded-xl border border-stone-200 px-4 py-2 text-sm font-medium text-stone-600 transition hover:bg-stone-50">
                        إغلاق
                    </button>
                </x-slot:footer>
            </x-tenant-theme::modal>
        @endif
    @endforeach
</div>
