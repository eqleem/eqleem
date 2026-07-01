<?php

use Livewire\Component;

new class extends Component
{
    //
};
?>

<header class="flex flex-col items-center justify-center mt-2 md:mt-5">
    <a href="#" class="relative mb-5 animate-fade-in-up delay-100 group">
        <div class="w-28 h-28 rounded-full p-1 bg-white overflow-hidden ">
            <img src="https://images.unsplash.com/photo-1585747860715-2ba37e788b70?q=80&amp;w=2074&amp;auto=format&amp;fit=crop" alt="Marcus Rivera" class="w-full h-full object-cover rounded-full transition-transform duration-500">
        </div>
        <div class="absolute bottom-1 bg-white rounded-full p-1  flex items-center justify-center">
            {{-- <div class="bg-green-500 w-4 h-4 rounded-full border-2 border-white"></div> --}}
            {{-- <iconify-icon icon="solar:verified-check-bold-duotone" class="text-blue-700 text-3xl" stroke-width="1.5"></iconify-icon> --}}
            <iconify-icon icon="solar:verified-check-bold" class="text-blue-700 text-3xl" stroke-width="1.5"></iconify-icon>
        </div>
    </a>

    <!-- Info -->
    <h1 class="text-3xl font-semibold text-stone-900 tracking-tight font-space-grotesk  animate-fade-in-up delay-100 mb-2 flex items-center gap-2">
        
        {{tenant()->name}}
        {{-- <iconify-icon icon="lucide:badge-check" class="text-blue-500 text-2xl" stroke-width="1.5"></iconify-icon> --}}
    </h1>
    <p class="text-stone-500/75 font-medium font-geist text-sm mb-4  animate-fade-in-up delay-200 flex items-center gap-1.5">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="map-pin" aria-hidden="true" class="lucide lucide-map-pin w-3.5 h-3.5"><path d="M20 10c0 4.993-5.539 10.193-7.399 11.799a1 1 0 0 1-1.202 0C9.539 20.193 4 14.993 4 10a8 8 0 0 1 16 0"></path><circle cx="12" cy="10" r="3"></circle></svg>
        {{ tenant('meta.city') }}
    </p>

    <p class="text-stone-500  max-w-md text-sm leading-relaxed  animate-fade-in-up delay-200 font-geist mb-5">
        {{ tenant('meta.slogan.'.app()->getLocale()) }}
    </p>

    <div class="flex items-center justify-center gap-x-3 mb-12 text-stone-500">
        <a href="#" class="bg-black/5 hover:bg-black/10 p-2.5 rounded-xl">
            <iconify-icon icon="ri:twitter-x-fill" class="inline text-xl" stroke-width="1.5"></iconify-icon>
        </a>  
        <a href="#" class="bg-black/5 hover:bg-black/10 p-2.5 rounded-xl">
            <iconify-icon icon="ri:instagram-fill" class="inline text-xl" stroke-width="1.5"></iconify-icon>
        </a>   
        <a href="#" class="bg-black/5 hover:bg-black/10 p-2.5 rounded-xl">
            <iconify-icon icon="ri:snapchat-fill" class="inline text-xl" stroke-width="1.5"></iconify-icon>
        </a>  
        <a href="#" class="bg-black/5 hover:bg-black/10 p-2.5 rounded-xl">
            <iconify-icon icon="ri:youtube-fill" class="inline text-xl" stroke-width="1.5"></iconify-icon>
        </a>
    </div>
</header>