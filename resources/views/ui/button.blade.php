@props([
    'label' => null,
    'href' => null,
    'icon' => null,
    'wireTarget' => '',
    'variant' => 'primary',
    'rounded' => null,
    'current' => null,
    'image' => null,
    'iconClass' => '',
])

@php $iconTrailing = $attributes->get('icon:trailing'); @endphp
 
@php
    $tag = $href ? 'a' : 'button';
    $variantClasses = match($variant) {
        'primary' => 'bg-primary-600 text-white hover:bg-primary-700',
        'secondary' => 'bg-black/10 text-black/70 hover:bg-black/20 shadow-none',
        'outline' => 'bg-white text-black/70 hover:bg-black/5 border border-black/10 shadow-none',
        'ghost' => 'bg-transparent text-black/70 hover:bg-black/10 shadow-none',
        'green' => 'bg-green-600 text-white hover:bg-green-700',
        'danger' => 'bg-red-600 text-white hover:bg-red-700',
        'warning' => 'bg-yellow-500 text-white hover:bg-yellow-600',
        'link' => 'bg-transparent text-black/70 underline hover:bg-transparent shadow-none hover:text-black/30',
        default => 'bg-gray-300 text-black/50'
    };
 
    $class = "inline-flex items-center cursor-pointer transition-all duration-300 justify-center gap-2 whitespace-nowrap rounded-md text-sm transition-colors  focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50  h-9 px-4 py-2 
     
    {$variantClasses} 
    "
@endphp

{{-- {$bgColor} {$colorText} {$hover} {$shadow} {$border} --}}

<{{$tag}} 
@if ($href) href="{{ $href }}" @else role="button" @endif
{{ $attributes->class($class) }} 
{{-- class="{{ $class }}" --}}
{{ $attributes->except('icon:trailing') }}
@if ($current) wire:current="{{ $current }}" @endif

> 

@if ($icon)
<ui:icon name="{{ $icon }}" class="!w-5 !h-5 @if($slot->isEmpty() && !$label) rtl:-mr-1 ltr:-ml-1 @endif {{ $iconClass }}" />
@endif

@if ($image)
{{$image}}
@endif

@if ($label)
    <span>
        {{ $label }}
    </span>
@endif
 
{{ $slot->isEmpty() ? '' : $slot }}

@if ($iconTrailing)
    <ui:icon name="{{ $iconTrailing }}" class="!w-5 !h-5 rtl:-ml-1 ltr:-mr-1  {{ $iconClass }}" />
@endif

@if ($wireTarget)
<div wire:loading wire:target="{{ $wireTarget }}">
    <svg xmlns="http://www.w3.org/2000/svg"
        class="icon icon-tabler icon-tabler-loader animate-spin h-5 w-5" viewBox="0 0 24 24"
        stroke-width="1.5" stroke="currentColor" fill="none" stroke-linecap="round"
        stroke-linejoin="round">
        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
        <line x1="12" y1="6" x2="12" y2="3" />
        <line x1="16.25" y1="7.75" x2="18.4" y2="5.6" />
        <line x1="18" y1="12" x2="21" y2="12" />
        <line x1="16.25" y1="16.25" x2="18.4" y2="18.4" />
        <line x1="12" y1="18" x2="12" y2="21" />
        <line x1="7.75" y1="16.25" x2="5.6" y2="18.4" />
        <line x1="6" y1="12" x2="3" y2="12" />
        <line x1="7.75" y1="7.75" x2="5.6" y2="5.6" />
    </svg>
</div>
@endif


</{{$tag}}>