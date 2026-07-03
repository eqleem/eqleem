 
@props([
    'width' => 'max-w-5xl',
])

<div class="p-2 md:p-px bg-{{ theme_option('bgColor', 'stone-200') }} min-h-screen [background-image:linear-gradient(rgba(255,255,255,0.2)_2px,transparent_2px),linear-gradient(90deg,rgba(255,255,255,0.3)_2px,transparent_2px)] [background-size:40px_40px]">
    
    <div class="{{ $width }} mx-auto relative">
        @livewire('tenant.blocks.top-nav')

        {{-- <x-tenant-theme::top-nav /> --}}
    </div>
 
    <main class="{{ $width }}  pb-12 mt-3 md:mt-4 min-h-[99.2vh]x xmd:min-h-[50vh]  mx-auto flex-grow Xpx-3 Xpy-1 flex flex-col relative w-full bg-white/80 Xbackdrop-blur-2xl rounded-3xl md:rounded-[22px] overflow-hidden animate-card transform-style-3d">
        {{ $slot }}
    </main>

    @livewire('tenant.blocks.footer')

    @livewire('tenant.blocks.float-links')
</div>