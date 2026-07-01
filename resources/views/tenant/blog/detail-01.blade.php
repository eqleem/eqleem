<?php

use Livewire\Component;

new class extends Component
{
    //
};
?>

<x-tenant::blog.layout>

<div class="flex items-center justify-between mb-5 px-2">
  <a href="{{route('tenant.blog.index')}}" wire:navigate class="w-10 h-10 rounded-full flex items-center justify-center bg-stone-100 hover:bg-stone-200 transition rotate-180">
    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="arrow-left" aria-hidden="true" class="lucide lucide-arrow-left w-5 h-5 text-stone-700 "><path d="m12 19-7-7 7-7"></path><path d="M19 12H5"></path></svg>
  </a>
  <div class="flex items-center gap-2">
    <button class="w-10 h-10 rounded-full flex items-center justify-center bg-stone-100 hover:bg-stone-200 transition">
      <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="share" aria-hidden="true" class="lucide lucide-share w-5 h-5 text-stone-700 "><path d="M12 2v13"></path><path d="m16 6-4-4-4 4"></path><path d="M4 12v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8"></path></svg>
    </button>  
    <button class="w-10 h-10 rounded-full flex items-center justify-center bg-stone-100 hover:bg-stone-200 transition">
      <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="heart" aria-hidden="true" class="lucide lucide-heart h-4 w-4"><path d="M2 9.5a5.5 5.5 0 0 1 9.591-3.676.56.56 0 0 0 .818 0A5.49 5.49 0 0 1 22 9.5c0 2.29-1.5 4-3 5.5l-5.492 5.313a2 2 0 0 1-3 .019L5 15c-1.5-1.5-3-3.2-3-5.5"></path></svg> 
  </div>
  </button>
</div>


 
  <div class="mx-auto max-w-md md:max-w-2xl text-center">
    <h1 class="mb-4   text-stone-800 text-2xl md:text-3xl font-extrabold">
      دليلك الأدبي لكوريا الجنوبية 🇰🇷
      دليلك الأدبي 
    </h1>
    <h3 class="my-3 text-base   leading-tight text-stone-500 md:text-xl">
        زائد: الكتاب في الدراما الكورية 📺
    </h3>
    <p class="mt-4 text-sm text-stone-400 md:text-base">
        <span class="bg-primary-50 text-primary-600   text-sm px-2 py-1 rounded-md"> 17 يونيو 2026  </span>
    </p>
  </div>

 
  <section class="px-2 md:px-4">
      <img
        src="https://r2-bucket.thmanyah.com/cdn-cgi/image/width=400/media/2026/158-20260616-V02.jpg"
        alt="دليلك الأدبي لكوريا الجنوبية"
        class="w-full h-full object-cover rounded-2xl mx-auto my-10"
      >

      <p class="p-3 text-stone-600">
        The Nike Air Max 270 delivers unrivaled all-day comfort with the largest Max Air unit yet, offering a super soft ride that feels as impossible as it looks.
      </p>
  </section>
</x-tenant::blog.layout>