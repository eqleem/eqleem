@props([
    'name' => null,
    'logo' => null,
    'alt' => null,
    'href' => null,
    'size' => '5',
    'iconClass' => null,
    'subtitle' => null,
])

<a href="{{ $href }}" {{ $attributes->class('inline-flex items-center gap-2') }}>
    @if ($logo)
        <img src="{{ $logo }}" alt="{{ $alt }}" class="size-{{ $size }} {{ $iconClass }}">
    @endif
        
    <div>
        <span class="font-bold">{{ $name }}</span>
        @if ($subtitle)
            <p class="text-xs opacity-60">{{ $subtitle }}</p>
        @endif
    </div>
</a>
