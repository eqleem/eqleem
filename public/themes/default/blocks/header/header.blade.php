<header class="relative">

    <div class="flex absolute   flex-col gap-1.5 p-1   z-20    rounded-2xl">
        @if ($socialLinks->isNotEmpty())
            <div class="flex items-center justify-center gap-x-3 p-2">
                @foreach ($socialLinks as $link)
                    <a
                        href="{{ $link['url'] }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        wire:key="header-social-link-{{ $link['id'] }}"
                        class="flex items-center  text-white/80 p-2  rounded-xl bg-black/40  hover:bg-black/50"
                    >
                        <iconify-icon icon="{{ $link['icon'] }}" class="inline text-2xl" stroke-width="1.5"></iconify-icon>
                    </a>
                @endforeach
            </div>
        @endif
    </div>
  
    <div class="w-full sm:relative Xfixed Xobject-cover min-h-[56px] md:rounded-t-3xl bg-[radial-gradient(ellipse_at_bottom_right,_var(--tw-gradient-stops))] from-primary-600 via-primary-700 to-primary-900 ">
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
                class="h-52 w-full md:rounded-t-3xl"
                style="background: {{ $cssCoverBackground }}"
                role="img"
                aria-label="{{ tenant('name') }}"
            ></div>
        @elseif ($headerImage)
            <img
                src="{{ $headerImage }}"
                alt="{{ tenant('name') }}"
                class="h-52 w-full object-cover md:rounded-t-3xl opacity-90"
                style="object-position: 50% {{ $headerImagePosition }}%"
            >
        @else
            <img src="{{ asset('assets/images/cover.png') }}" alt="{{ tenant('name') }}" class="h-52 w-full object-cover md:rounded-t-3xl opacity-90">
        @endif
    </div>
    <!-- Name, Title and Logo Row -->
    @php
        $logoRadius = theme_option('logoRadius', 'full');
        $logoRadiusClass = str_starts_with((string) $logoRadius, 'rounded-') ? $logoRadius : 'rounded-'.$logoRadius;
    @endphp
    <div class="flex items-center gap-x-3 p-2">
        <!-- Logo -->
        <x-brand-mark
            :mark="$brandMark ?? null"
            :url="tenant('logo')"
            :alt="tenant('name')"
            icon-size="4.5rem"
            class="size-24 {{ $logoRadiusClass }} flex items-center justify-center object-cover"
        />
        
        <!-- Name and Title -->
        <div class="flex-1">
            <h1 class="md:text-xl text-base font-bold text-gray-900 mb-1 flex items-center gap-x-1 tracking-wide" >
                <span class="truncate">{{ tenant('name') }} </span>

                <svg viewBox="0 0 24 24" class="size-7 text-primary-900" fill="none" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <rect width="24" height="24" fill="white"></rect> <path fill-rule="evenodd" clip-rule="evenodd" d="M9.55879 3.6972C10.7552 2.02216 13.2447 2.02216 14.4412 3.6972L14.6317 3.96387C14.8422 4.25867 15.1958 4.41652 15.5558 4.37652L16.4048 4.28218C18.3156 4.06988 19.9301 5.68439 19.7178 7.59513L19.6235 8.44415C19.5835 8.8042 19.7413 9.15774 20.0361 9.36831L20.3028 9.55879C21.9778 10.7552 21.9778 13.2447 20.3028 14.4412L20.0361 14.6317C19.7413 14.8422 19.5835 15.1958 19.6235 15.5558L19.7178 16.4048C19.9301 18.3156 18.3156 19.9301 16.4048 19.7178L15.5558 19.6235C15.1958 19.5835 14.8422 19.7413 14.6317 20.0361L14.4412 20.3028C13.2447 21.9778 10.7553 21.9778 9.55879 20.3028L9.36831 20.0361C9.15774 19.7413 8.8042 19.5835 8.44414 19.6235L7.59513 19.7178C5.68439 19.9301 4.06988 18.3156 4.28218 16.4048L4.37652 15.5558C4.41652 15.1958 4.25867 14.8422 3.96387 14.6317L3.6972 14.4412C2.02216 13.2447 2.02216 10.7553 3.6972 9.55879L3.96387 9.36831C4.25867 9.15774 4.41652 8.8042 4.37652 8.44414L4.28218 7.59513C4.06988 5.68439 5.68439 4.06988 7.59513 4.28218L8.44415 4.37652C8.8042 4.41652 9.15774 4.25867 9.36831 3.96387L9.55879 3.6972ZM15.7071 9.29289C16.0976 9.68342 16.0976 10.3166 15.7071 10.7071L11.8882 14.526C11.3977 15.0166 10.6023 15.0166 10.1118 14.526L8.29289 12.7071C7.90237 12.3166 7.90237 11.6834 8.29289 11.2929C8.68342 10.9024 9.31658 10.9024 9.70711 11.2929L11 12.5858L14.2929 9.29289C14.6834 8.90237 15.3166 8.90237 15.7071 9.29289Z" fill="currentColor"></path> </g></svg>

            </h1>
            <p class="text-black/70 text-sm mb-1"> {{ $bio }}  </p>
            @if (tenant('country') or tenant('city'))
                <p class="text-black/50 text-xs mb-1 flex items-center gap-x-1">
                    {{ icon('location', 4, 'text-gray-500') }}
                    @if (tenant('city')){{ tenant('city') }}@endif
                </p>
            @endif
        </div>
    </div>
 
</header>