@props([
    'width' => 'max-w-5xl',
])

@php
    $bgColor = theme_option('bgColor', 'stone-200');
    $bgIsHex = is_string($bgColor) && preg_match('/^#(?:[0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/', $bgColor) === 1;
    $bgIsTransparent = in_array($bgColor, ['transparent', 'bg-tranparent'], true);
@endphp

<div
    @class([
        'p-0 md:p-px min-h-screen x[background-size:40px_40px]',
        $bgIsHex || $bgIsTransparent ? null : 'bg-'.$bgColor,
        $bgIsTransparent ? 'bg-transparent' : null,
    ])
    @style([$bgIsHex ? 'background-color: '.$bgColor : null])
>
    @unless (request()->routeIs('tenant.home'))
        <div class="{{ $width }} mx-auto pt-0 lg:pt-3 relative">
            @livewire('tenant.blocks.top-nav')
        </div>
    @endunless

    <main class="{{ $width }} pb-12 md:mt-4 relative  mx-auto flex-grow flex flex-col relative w-full bg-white/90 backdrop-blur-2xl md:rounded-[22px] overflow-hidden animate-card transform-style-3d">
        {{ $slot }}
    </main>



    @livewire('tenant.blocks.footer')

    @livewire('tenant.blocks.float-links' , ['width' => $width])
</div>
