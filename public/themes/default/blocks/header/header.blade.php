<header class="relative">

    <div class="flex absolute w-full flex-col gap-1.5 p-0 lg:px-2.5 lg:py-1 z-20  text-white   ">
        <div class="w-full">
            @livewire('tenant.blocks.top-nav')
        </div> 
    </div>
  
    <div class="w-full sm:relative Xfixed Xobject-cover  min-h-[56px]  bg-[radial-gradient(ellipse_at_bottom_right,_var(--tw-gradient-stops))] from-primary-600 via-primary-700 to-primary-900 ">
        @php
            $headerImagePath = $themeOptions['headerImage'] ?? null;
            $isCssCover = filled($headerImagePath) && (
                str_starts_with((string) $headerImagePath, 'color:')
                || str_starts_with((string) $headerImagePath, 'gradient:')
            );
            $cssCoverBackground = $isCssCover
                ? (string) preg_replace('/^(color|gradient):/', '', (string) $headerImagePath)
                : null;
            $headerImagePosition = max(0, min(100, (int) ($themeOptions['headerImagePosition'] ?? 50)));
            $headerImage = (! $isCssCover && filled($headerImagePath))
                ? (str_starts_with((string) $headerImagePath, 'http')
                    ? $headerImagePath
                    : \Storage::url((string) $headerImagePath))
                : null;
        @endphp
        @if ($isCssCover)
            <div
                class="h-52 w-full  "
                style="background: {{ $cssCoverBackground }}"
                role="img"
                aria-label="{{ tenant('name') }}"
            ></div>
        @elseif ($headerImage)
            <img
                src="{{ $headerImage }}"
                alt="{{ tenant('name') }}"
                class="h-52 w-full object-cover   opacity-90"
                style="object-position: 50% {{ $headerImagePosition }}%"
            >
        @else
        <div class="h-32 w-full  bg-primary-600"
                role="img"
                aria-label="{{ tenant('name') }}"
            ></div>
            {{-- <img src="{{ asset('assets/images/cover.png') }}" alt="{{ tenant('name') }}" class="h-52 w-full object-cover md:rounded-t-2xlx opacity-90"> --}}
        @endif
    </div>
    <!-- Name, Title and Logo Row -->
    @php
        $logoRadius = theme_option('logoRadius', 'full');
        $logoRadiusClass = str_starts_with((string) $logoRadius, 'rounded-') ? $logoRadius : 'rounded-'.$logoRadius;
        $brandMarkType = is_array($brandMark ?? null) ? (string) ($brandMark['type'] ?? 'image') : 'image';
        $isGlyphMark = in_array($brandMarkType, ['icon', 'emoji'], true);
        $logoSizeClass = $isGlyphMark ? 'size-[4rem] md:size-[5.5rem]' : 'size-12 ms-2 md:ms-3 me-2 md:me-3 md:size-[3.8rem]';
    @endphp
    <div class="flex items-center gap-2 py-3 lg:py-2 px-2 lg:px-1 relative">

        @if ($socialLinks->isNotEmpty())
            <div class="flex items-end justify-end gap-x-3 absolute end-2 top-2">
                @foreach ($socialLinks as $link)
                    <a
                        href="{{ $link['url'] }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        wire:key="header-social-link-{{ $link['id'] }}"
                        class="flex items-center  text-black/80 p-2 rounded-lg bg-black/10  hover:bg-black/20"
                        aria-label="{{ $link['label'] }}"
                    >
                        <iconify-icon icon="{{ $link['icon'] }}" class="inline lg:text-base text-xl stroke-width="1.5" aria-hidden="true"></iconify-icon>
                    </a>
                @endforeach
            </div>
        @endif

        
        <!-- Logo -->
        <x-brand-mark
            :mark="$brandMark ?? null"
            :url="$avatarUrl ?? null"
            :alt="$tenantName ?? tenant('name')"
            icon-size="3rem"
            class="{{ $logoSizeClass }} {{ $logoRadiusClass }} lg:mt-2 flex items-center justify-center object-cover [--brand-mark-icon-size:3rem] md:[--brand-mark-icon-size:3.5rem]"
        />
        
        <!-- Name and Title -->
        <div class="flex-1">
            <h1 class="md:text-xl text-base font-bold text-gray-900 Xmb-1 flex items-center gap-x-1 tracking-wide" >
                <span class="truncate">{{ tenant('name') }} </span>
                <iconify-icon icon="solar:verified-check-bold" class="text-2xl text-blue-800 ms-1" />
            </h1>
            <p class="text-black/60 text-sm mt-1 md:mt-2"> {{ $bio }}  </p>
            @if (tenant('country') or tenant('city'))
                <p class="text-black/50 text-xs  flex items-center gap-x-1">
                    {{ icon('location', 4, 'text-gray-500') }}
                    @if (tenant('city')){{ tenant('city') }} @endif
                </p>
            @endif
        </div>


        
    </div>
 
</header>