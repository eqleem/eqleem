<?php

use Livewire\Component;

new class extends Component
{
    //
};
?>

<div class="bg-stone-300/50  min-h-screen pt-10 lg:pt-12">

    <div class="flex justify-center max-w-xl mx-auto">
        <div class="rounded-full flex items-center w-full mx-auto justify-between -mt-7 bg-[#010301] text-white px-2 py-2 text-xs sm:text-sm font-medium">
              <a href="{{ route('home') }}" wire:navigate title="" class="flex items-center gap-x-2 ms-1">
                <img class="w-auto h-7 ms-2" src="{{ asset('assets/images/logo-shape.webp') }}" alt="" />
                {{-- <img class="w-auto h-5 md:h-7" src="{{ asset('assets/images/logo-y-r-p.webp') }}" alt="" /> --}}
                {{-- <img class="w-auto h-6 md:h-9" src="{{ asset('assets/images/logo-10-white.webp') }}" alt="" /> --}}
                {{-- <img class="w-auto h-6 md:h-9" src="{{ asset('assets/images/logo-10-orange.webp') }}" alt="" /> --}}
                <img class="w-auto h-6 md:h-9" src="{{ asset('assets/images/t1-w.png') }}" alt="" />
                {{-- <span class="text-xl lg:text-3xl font-camel font-extrabold">
                    {{ config('app.name') }} 
                </span> --}}
            </a>
           
      
            <div class="flex items-center justify-end lg:gap-x-2">
                @auth
                    <ui:button variant="primary" href="{{ route('admin.home') }}" label="لوحة التحكم" class="!rounded-full text-base font-tsh !p-6" rounded="full" icon="settings" />
                @else
                    <ui:button variant="primary" href="{{ route('auth.register') }}" label="أنشئ إقليم" wire:navigate rounded="full" icon="plus" class="!rounded-full !bg-primary-600 !hover:bg-primary-700 " />
                    <ui:button variant="ghost" href="{{ route('auth.login') }}" label="دخول" wire:navigate class="text-white hover:text-white/70 !hover:bg-black/5 !font-normal" icon:trailing="arrow-left" />
                @endauth   
            </div>
        </div>
    </div>
</div>
