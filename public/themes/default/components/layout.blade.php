 
@props([
    'width' => 'max-w-5xl',
])

<div class="p-2 md:p-px bg-stone-200 min-h-screen [background-image:linear-gradient(rgba(255,255,255,0.2)_2px,transparent_2px),linear-gradient(90deg,rgba(255,255,255,0.3)_2px,transparent_2px)] [background-size:40px_40px]">
    
    <div class="{{ $width }} mx-auto relative">
        @livewire('tenant.blocks.top-nav')

        {{-- <x-tenant-theme::top-nav /> --}}
    </div>
 
    <main class="{{ $width }} ring ring-stone-300/10 pb-12 mt-3 md:mt-4 min-h-[99.2vh]x xmd:min-h-[50vh]  mx-auto flex-grow px-3 py-1 flex flex-col relative w-full bg-white/80 Xbackdrop-blur-2xl border border-white/60 Xshadow-[0_30px_60px_-12px_rgba(0,0,0,0.08),0_10px_20px_-10px_rgba(0,0,0,0.04)] rounded-2xl md:rounded-[32px] overflow-hidden animate-card transform-style-3d">
        {{ $slot }}
    </main>

    @livewire('tenant.blocks.footer')

    @livewire('tenant.blocks.float-links')
</div>