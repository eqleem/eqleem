<div class="p-4">
    @php
        $visibleCtaLinks = $ctaLinks->filter(fn ($link) => $link['isForm'] || filled($link['url']));
        $ctaLinksCount = $visibleCtaLinks->count();
        $ctaLinksRemainder = $ctaLinksCount % 3;
    @endphp
    @if($visibleCtaLinks->isNotEmpty())
    <div @class([
        'grid w-full gap-3 mb-6X animate-fade-in-up delay-300',
        'grid-cols-1' => $ctaLinksCount === 1,
        'grid-cols-2' => $ctaLinksCount >= 2,
        'lg:grid-cols-3' => $ctaLinksCount >= 3,
    ])>
        @foreach($visibleCtaLinks as $link)
            @php
                $isLonelyMobileLast = $ctaLinksCount % 2 === 1 && $loop->last && $ctaLinksCount > 1;
                $isLonelyLgLast = $ctaLinksRemainder === 1 && $loop->last && $ctaLinksCount >= 3;
                $brandMark = $link['brand_mark'] ?? null;
                $isPrimaryCta = $loop->first;
                $ctaBgClasses = $isPrimaryCta
                    ? 'bg-primary-600 hover:bg-primary-700 text-white'
                    : 'bg-secondary-900/10 hover:bg-secondary-900/20 text-secondary-900';
            @endphp

            @if($ctaLinksCount >= 3 && $ctaLinksRemainder === 2 && $loop->iteration === $ctaLinksCount - 1)
                <div class="contents lg:col-span-3 lg:grid lg:grid-cols-2 lg:gap-4">
            @endif

            @if($link['isForm'])
            <button
                type="button"
                wire:key="cta-link-{{ $link['id'] }}"
                @class([
                    'flex w-full items-center justify-center gap-2 text-base rounded-xl px-4 py-3 font-medium transition-all duration-300 hover-lift',
                    $ctaBgClasses,
                    'col-span-2 lg:col-span-1' => $isLonelyMobileLast && ! $isLonelyLgLast,
                    'col-span-2 lg:col-span-3' => $isLonelyMobileLast && $isLonelyLgLast,
                    'lg:col-span-3' => $isLonelyLgLast && ! $isLonelyMobileLast,
                ])
                x-on:click="$dispatch('open-modal', { name: 'cta-form-{{ $link['id'] }}' })"
            >
                @if (is_array($brandMark) && filled($brandMark['type'] ?? null))
                    @if (($brandMark['type'] ?? '') === 'image')
                        <span class="size-9 shrink-0 overflow-hidden rounded-lg bg-white/15">
                            <x-brand-mark :mark="$brandMark" :alt="$link['label']" class="size-full object-cover" icon-size="1.35rem" />
                        </span>
                    @elseif (($brandMark['type'] ?? '') === 'emoji')
                        <span class="flex size-9 shrink-0 items-center justify-center overflow-hidden rounded-lg text-xl leading-none">
                            <x-brand-mark :mark="$brandMark" :alt="$link['label']" class="size-7 object-cover" icon-size="1.35rem" />
                        </span>
                    @else
                        <span class="flex size-9 shrink-0 items-center justify-center overflow-hidden rounded-lg bg-white/15 text-white">
                            <x-brand-mark :mark="$brandMark" :alt="$link['label']" class="size-7 object-cover" icon-size="1.35rem" />
                        </span>
                    @endif
                @elseif (filled($link['icon'] ?? null))
                    <iconify-icon icon="{{ $link['icon'] }}" class="inline text-2xl" stroke-width="1.5"></iconify-icon>
                @endif
                {{ $link['label'] }}
            </button>
            @elseif(filled($link['url']))
            <a
                href="{{ $link['url'] }}"
                wire:key="cta-link-{{ $link['id'] }}"
                @if($link['opensInNewTab']) target="_blank" rel="noopener noreferrer" @else wire:navigate.hover @endif
                @class([
                    'flex w-full items-center justify-center transition-all duration-200 text-base font-medium font-geist rounded-xl px-4 py-3 group relative overflow-hidden',
                    $ctaBgClasses,
                    'col-span-2 lg:col-span-1' => $isLonelyMobileLast && ! $isLonelyLgLast,
                    'col-span-2 lg:col-span-3' => $isLonelyMobileLast && $isLonelyLgLast,
                    'lg:col-span-3' => $isLonelyLgLast && ! $isLonelyMobileLast,
                ])
            >
                <span class="relative z-10 flex items-center gap-2 truncate">
                    @if (is_array($brandMark) && filled($brandMark['type'] ?? null))
                        @if (($brandMark['type'] ?? '') === 'image')
                            <span class="size-9x shrink-0 overflow-hidden rounded-lg bg-white/15">
                                <x-brand-mark :mark="$brandMark" :alt="$link['label']" class="size-full object-cover" icon-size="1.35rem" />
                            </span>
                        @elseif (($brandMark['type'] ?? '') === 'emoji')
                            <span class="flex size-9x shrink-0 items-center justify-center overflow-hidden rounded-lg text-xl leading-none">
                                <x-brand-mark :mark="$brandMark" :alt="$link['label']" class=" object-cover" icon-size="1.5rem" />
                            </span>
                        @else
                            <span class="flex size-9x shrink-0 items-center justify-center overflow-hidden">
                                <x-brand-mark :mark="$brandMark" :alt="$link['label']" class="size-7 object-cover" icon-size="1.35rem" />
                            </span>
                        @endif
                    @elseif (filled($link['icon'] ?? null))
                        <iconify-icon icon="{{ $link['icon'] }}" class="inline text-2xl" stroke-width="1.5"></iconify-icon>
                    @endif
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
                    :description="$link['formDescription']"
                    :fields="$link['formFields']"
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
