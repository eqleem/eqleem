@php
    use Illuminate\Support\Facades\Storage;

    $data = $block?->data ?? [];
    $showAvatar = (bool) ($data['show_avatar'] ?? true);
    $avatarUrl = filled($data['avatar_path'] ?? null)
        ? Storage::url($data['avatar_path'])
        : tenant()->logo;
    $showVerifiedBadge = (bool) ($data['show_verified_badge'] ?? true);
    $bio = $data['bio'] ?? '';
    $locationParts = array_filter([$data['city'] ?? null, $data['country'] ?? null], fn ($part) => filled($part));

    $networks = config('social-networks', []);
    $socialLinks = $block
        ? \App\Models\Content::query()
            ->where('block_id', $block->id)
            ->type('social-link')
            ->where('active', true)
            ->orderBy('sort_order')
            ->get()
        : collect();
@endphp

<header class="flex flex-col items-center justify-center mt-2 md:mt-5">
    @if($showAvatar)
    <a href="#" class="relative mb-5 animate-fade-in-up delay-100 group">
        <div class="w-28 h-28 rounded-full p-1 bg-white overflow-hidden ">
            <img src="{{ $avatarUrl }}" alt="{{ tenant()->name }}" class="w-full h-full object-cover rounded-full transition-transform duration-500">
        </div>
        @if($showVerifiedBadge)
        <div class="absolute bottom-1 bg-white rounded-full p-1  flex items-center justify-center">
            <iconify-icon icon="solar:verified-check-bold" class="text-blue-700 text-3xl" stroke-width="1.5"></iconify-icon>
        </div>
        @endif
    </a>
    @endif

    <h1 class="text-3xl font-semibold text-stone-900 tracking-tight font-space-grotesk  animate-fade-in-up delay-100 mb-2 flex items-center gap-2">
        {{ tenant()->name }}
    </h1>

    @if(count($locationParts))
    <p class="text-stone-500/75 font-medium font-geist text-sm mb-4  animate-fade-in-up delay-200 flex items-center gap-1.5">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="map-pin" aria-hidden="true" class="lucide lucide-map-pin w-3.5 h-3.5"><path d="M20 10c0 4.993-5.539 10.193-7.399 11.799a1 1 0 0 1-1.202 0C9.539 20.193 4 14.993 4 10a8 8 0 0 1 16 0"></path><circle cx="12" cy="10" r="3"></circle></svg>
        {{ implode('، ', $locationParts) }}
    </p>
    @endif

    @if(filled($bio))
    <p class="text-stone-500  max-w-md text-sm leading-relaxed  animate-fade-in-up delay-200 font-geist mb-5">
        {{ $bio }}
    </p>
    @endif

    @if($socialLinks->isNotEmpty())
    <div class="flex items-center justify-center gap-x-3 mb-12 text-stone-500">
        @foreach($socialLinks as $link)
            @php
                $network = $networks[$link->data['network'] ?? ''] ?? null;
                $url = $link->data['url'] ?? '';
            @endphp
            @if($network && filled($url))
            <a href="{{ $url }}" target="_blank" rel="noopener noreferrer" class="bg-black/5 hover:bg-black/10 p-2.5 rounded-xl">
                <iconify-icon icon="{{ $network['icon'] }}" class="inline text-xl" stroke-width="1.5"></iconify-icon>
            </a>
            @endif
        @endforeach
    </div>
    @endif
</header>
