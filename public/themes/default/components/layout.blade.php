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
        'p-1 md:p-px min-h-screen [background-size:40px_40px]',
        $bgIsHex || $bgIsTransparent ? null : 'bg-'.$bgColor,
        $bgIsTransparent ? 'bg-transparent' : null,
    ])
    @style([$bgIsHex ? 'background-color: '.$bgColor : null])
>
    <div class="{{ $width }} mx-auto relative">
        @livewire('tenant.blocks.top-nav')

        {{-- <x-tenant-theme::top-nav /> --}}
    </div>

    <main class="{{ $width }} pb-12 mt-1 md:mt-4   mx-auto flex-grow Xpx-3 Xpy-1 flex flex-col relative w-full bg-white/90 Xbackdrop-blur-2xl rounded-3xl md:rounded-[22px] overflow-hidden animate-card transform-style-3d">
        {{ $slot }}
    </main>

    @livewire('tenant.blocks.footer')

    @livewire('tenant.blocks.float-links')
</div>
