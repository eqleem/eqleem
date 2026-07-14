@props(['link' => '#', 'title' => 'رابط', 'desc' => '...', 'icon' => 'hugeicons:store-02', 'brandMark' => null])

 <div class="bg-white rounded-2xl group border-2 border-secondary-900/5 hover:border-primary-500 focus-within:border-primary-500 transition">
    <a href="{{$link}}" wire:navigate.hover class="flex items-center justify-between p-3 ">
        <div class="flex items-center gap-3 truncate">
            @if (is_array($brandMark) && filled($brandMark['type'] ?? null))
                @if (($brandMark['type'] ?? '') === 'image')
                    <div class="brand-icon size-14 shrink-0 overflow-hidden rounded-xl">
                        <x-brand-mark :mark="$brandMark" :alt="$title" class="size-full object-cover" />
                    </div>
                @elseif (($brandMark['type'] ?? '') === 'emoji')
                    <div class="brand-icon flex size-14 shrink-0 items-center justify-center overflow-hidden rounded-xl text-4xl">
                        <x-brand-mark :mark="$brandMark" :alt="$title" class="size-10 object-cover" />
                    </div>
                @else
                    <div class="brand-icon flex size-14 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-primary-500 text-white">
                        <x-brand-mark :mark="$brandMark" :alt="$title" class="size-10 object-cover" />
                    </div>
                @endif
            @elseif ($icon && is_string($icon))
                <div class="brand-icon flex size-14 shrink-0 items-center justify-center rounded-xl bg-primary-500 p-2 text-white">
                    <iconify-icon icon="{{$icon}}" class="text-4xl block" stroke-width="1.5"></iconify-icon>
                </div>
            @else
                {{$icon}}
            @endif

            <div>
                <p class="text-lg font-semibold" style=""> {{$title}} </p>
                <p class="text-sm md:text-base text-stone-500 flex items-center gap-1" style="">
                    {{$desc}}
                </p>
            </div>
        </div>
        <div class="pe-2 text-primary-400 group-hover:text-primary-500 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="arrow-right" aria-hidden="true" class="lucide lucide-arrow-right size-6 rotate-180"><path d="M5 12h14"></path><path d="m12 5 7 7-7 7"></path></svg>
        </div>
    </a>

    @if ($slot->isNotEmpty())
        <div class="animate-content delay-400 pt-6 border-t border-stone-100 mt-4">
            {{$slot}}
        </div>
    @endif
</div>
