<?php

use Livewire\Component;

new class extends Component
{
    //
};
?>

<x-tenant::store.layout>

<section class="px-1 mb-5 w-full flex items-center justify-between gap-3">
  <div class="flex items-center gap-3 overflow-x-auto no-scrollbar bg-stone-200/40 rounded-2xl p-1 whitespace-nowrap w-full">
      <button class="p-3 text-center py-2.5 rounded-xl bg-white text-stone-900 text-sm font-medium shadow-sm">الكل</button>
      <button class="p-3 text-center py-2.5 rounded-xl hover:bg-stone-50 text-stone-600 text-sm font-medium hover:text-stone-900">أعمال الباركيه</button>
      <button class="p-3 text-center py-2.5 rounded-xl hover:bg-stone-50 text-stone-600 text-sm font-medium hover:text-stone-900">بديل الرخام</button>
      <button class="p-3 text-center py-2.5 rounded-xl hover:bg-stone-50 text-stone-600 text-sm font-medium hover:text-stone-900">والشيبورد بديل الخشب</button>
      <button class="p-3 text-center py-2.5 rounded-xl hover:bg-stone-50 text-stone-600 text-sm font-medium hover:text-stone-900">النعلات</button>
  </div>

  <div class="flex items-center gap-3">
    <button class="p-3 rounded-xl bg-stone-200/40 hover:bg-stone-200 flex items-center justify-center transition-all duration-200 hover:scale-105">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="search" aria-hidden="true" class="lucide lucide-search size-6 text-stone-700"><path d="m21 21-4.34-4.34"></path><circle cx="11" cy="11" r="8"></circle></svg>
    </button>
    {{-- <button class="w-9 h-9 rounded-full bg-stone-100 hover:bg-stone-200 flex items-center justify-center transition-all duration-200 hover:scale-105">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="filter" aria-hidden="true" class="lucide lucide-filter w-5 h-5 text-stone-700"><path d="M10 20a1 1 0 0 0 .553.895l2 1A1 1 0 0 0 14 21v-7a2 2 0 0 1 .517-1.341L21.74 4.67A1 1 0 0 0 21 3H3a1 1 0 0 0-.742 1.67l7.225 7.989A2 2 0 0 1 10 14z"></path></svg>
    </button> --}}
  </div> 
</section>


<section class="p-1">
    <div class="">
      <div class="grid grid-cols-2 xl:grid-cols-3 gap-4 md:gap-6">
        <!-- Card 1 -->
        <a href="{{route('tenant.store.detail', 'premium-dry-food')}}" wire:navigate class="group rounded-xl md:rounded-2xl overflow-hidden  bg-white transition [animation:fadeSlideIn_1s_ease-out_0.1s_both] animate-on-scroll animate">
          <div class="relative">
            <img src="https://cdn.midjourney.com/a4759e97-43ff-4a43-8985-ee83b226e1a4/0_0.png?w=800&amp;q=80" alt="Premium Dog Food" class="w-full h-56 md:h-72 object-cover group-hover:scale-105 transition-all duration-500">
            <button aria-label="Favorite" class="absolute top-2 end-2 p-2 rounded-full bg-black/30 backdrop-blur  text-white hover:text-rose-500 transition">
              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" data-lucide="heart" aria-hidden="true" class="lucide lucide-heart w-4 h-4"><path d="M2 9.5a5.5 5.5 0 0 1 9.591-3.676.56.56 0 0 0 .818 0A5.49 5.49 0 0 1 22 9.5c0 2.29-1.5 4-3 5.5l-5.492 5.313a2 2 0 0 1-3 .019L5 15c-1.5-1.5-3-3.2-3-5.5"></path></svg>
            </button>
          </div>
          <div class="p-3 border border-neutral-200 border-t-0 rounded-b-xl md:rounded-b-2xl">
            <div class="flex items-center justify-between">
              <h3 class="text-lg tracking-tight font-semibold font-geist">Premium Dry Food</h3>
              {{-- <p class="text-sm font-medium font-geist">$42</p> --}}
            </div>
            <p class="mt-0.5 text-xs text-neutral-600 font-geist truncate">Organic chicken &amp; brown rice formula.</p>
            <div class="mt-4 flex items-center justify-between">
              <div class="flex items-center gap-1 text-neutral-600 text-xs">
                <p class="text-xl font-bold font-geist">$42</p>
              </div>
              <button class="inline-flex items-center gap-2 px-3 py-1.5 rounded-[9px] bg-primary-50 text-primary-600 hover:bg-primary-100 text-sm font-semibold">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" data-lucide="plus" aria-hidden="true" class="lucide lucide-plus w-3.5 h-3.5"><path d="M5 12h14"></path><path d="M12 5v14"></path></svg>
                أضف للسلة
              </button>
            </div>
          </div>
        </a>

        <!-- Card 2 -->
        <a href="{{route('tenant.store.detail', 'smart-puzzle-toy')}}" wire:navigate class="group rounded-xl md:rounded-2xl overflow-hidden bg-white transition [animation:fadeSlideIn_1s_ease-out_0.2s_both] animate-on-scroll animate">
          <div class="relative">
            <img src="https://cdn.midjourney.com/3b13548f-45ac-469a-9a45-435a59e1066b/0_0.png?w=800&amp;q=80" alt="Interactive Toy" class="w-full h-56 md:h-72 object-cover group-hover:scale-105 transition-all duration-500">
            <button aria-label="Favorite" class="absolute top-2 end-2 p-2 rounded-full bg-black/30 backdrop-blur text-white hover:text-rose-500 transition">
              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" data-lucide="heart" aria-hidden="true" class="lucide lucide-heart w-4 h-4"><path d="M2 9.5a5.5 5.5 0 0 1 9.591-3.676.56.56 0 0 0 .818 0A5.49 5.49 0 0 1 22 9.5c0 2.29-1.5 4-3 5.5l-5.492 5.313a2 2 0 0 1-3 .019L5 15c-1.5-1.5-3-3.2-3-5.5"></path></svg>
            </button>
          </div>
          <div class="p-3 border border-neutral-200 border-t-0 rounded-b-xl md:rounded-b-2xl">
            <div class="flex items-center justify-between">
              <h3 class="text-lg font-semibold tracking-tight font-geist">Smart Puzzle Toy</h3>
              {{-- <p class="text-sm font-medium font-geist">$28</p> --}}
            </div>
            <p class="mt-0.5 text-xs text-neutral-600 font-geist truncate">Mental stimulation &amp; treat dispenser.</p>
            <div class="mt-4 flex items-center justify-between">
              <div class="flex items-center gap-1 text-neutral-600">
                <p class="text-xl font-bold font-geist">$28</p>
              </div>
              <button class="inline-flex items-center gap-2 px-3 py-1.5 rounded-[9px] bg-primary-50 text-primary-600 hover:bg-primary-100 text-sm font-semibold">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" data-lucide="plus" aria-hidden="true" class="lucide lucide-plus w-3.5 h-3.5"><path d="M5 12h14"></path><path d="M12 5v14"></path></svg>
                أضف للسلة
              </button>
            </div>
          </div>
        </a>
        <a href="{{route('tenant.store.detail', 'cloud-comfort-bed')}}" wire:navigate class="group rounded-xl md:rounded-2xl overflow-hidden  bg-white transition [animation:fadeSlideIn_1s_ease-out_0.1s_both] animate-on-scroll animate">
          <div class="relative">
            <img src="https://cdn.midjourney.com/976ff5ab-f0a8-46d0-89c5-020c1e3f46b1/0_0.png?w=800&amp;q=80" alt="Premium Dog Food" class="w-full h-56 md:h-72 object-cover group-hover:scale-105 transition-all duration-500">
            <button aria-label="Favorite" class="absolute top-2 end-2 p-2 rounded-full bg-black/30 backdrop-blur  text-white hover:text-rose-500 transition">
              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" data-lucide="heart" aria-hidden="true" class="lucide lucide-heart w-4 h-4"><path d="M2 9.5a5.5 5.5 0 0 1 9.591-3.676.56.56 0 0 0 .818 0A5.49 5.49 0 0 1 22 9.5c0 2.29-1.5 4-3 5.5l-5.492 5.313a2 2 0 0 1-3 .019L5 15c-1.5-1.5-3-3.2-3-5.5"></path></svg>
            </button>
          </div>
          <div class="p-3 border border-neutral-200 border-t-0 rounded-b-xl md:rounded-b-2xl">
            <div class="flex items-center justify-between">
              <h3 class="text-lg tracking-tight font-semibold font-geist">Cloud Comfort Bed</h3>
              {{-- <p class="text-sm font-medium font-geist">$42</p> --}}
            </div>
            <p class="mt-0.5 text-xs text-neutral-600 font-geist truncate">Organic chicken &amp; brown rice formula.</p>
            <div class="mt-4 flex items-center justify-between">
              <div class="flex items-center gap-1 text-neutral-600 text-xs">
                <p class="text-xl font-bold font-geist">$89</p>
              </div>
              <button class="inline-flex items-center gap-2 px-3 py-1.5 rounded-[9px] bg-primary-50 text-primary-600 hover:bg-primary-100 text-sm font-semibold">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" data-lucide="plus" aria-hidden="true" class="lucide lucide-plus w-3.5 h-3.5"><path d="M5 12h14"></path><path d="M12 5v14"></path></svg>
                أضف للسلة
              </button>
            </div>
          </div>
        </a>
        <!-- Card 2 -->
        <a href="{{route('tenant.store.detail', 'gourmet-treats-box')}}" wire:navigate class="group rounded-xl md:rounded-2xl overflow-hidden bg-white transition [animation:fadeSlideIn_1s_ease-out_0.2s_both] animate-on-scroll animate">
          <div class="relative">
            <img src="https://cdn.midjourney.com/bc5fafbd-0c4c-4085-87fe-87acd5eae983/0_0.png?w=800&amp;q=80" alt="Interactive Toy" class="w-full h-56 md:h-72 object-cover group-hover:scale-105 transition-all duration-500">
            <button aria-label="Favorite" class="absolute top-2 end-2 p-2 rounded-full bg-black/30 backdrop-blur text-white hover:text-rose-500 transition">
              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" data-lucide="heart" aria-hidden="true" class="lucide lucide-heart w-4 h-4"><path d="M2 9.5a5.5 5.5 0 0 1 9.591-3.676.56.56 0 0 0 .818 0A5.49 5.49 0 0 1 22 9.5c0 2.29-1.5 4-3 5.5l-5.492 5.313a2 2 0 0 1-3 .019L5 15c-1.5-1.5-3-3.2-3-5.5"></path></svg>
            </button>
          </div>
          <div class="p-3 border border-neutral-200 border-t-0 rounded-b-xl md:rounded-b-2xl">
            <div class="flex items-center justify-between">
              <h3 class="text-lg font-semibold tracking-tight font-geist">Gourmet Treats Box</h3>
              {{-- <p class="text-sm font-medium font-geist">$28</p> --}}
            </div>
            <p class="mt-0.5 text-xs text-neutral-600 font-geist truncate">Freeze-dried salmon &amp; sweet potato.</p>
            <div class="mt-4 flex items-center justify-between">
              <div class="flex items-center gap-1 text-neutral-600">
                <p class="text-xl font-bold font-geist">$18</p>
              </div>
              <button class="inline-flex items-center gap-2 px-3 py-1.5 rounded-[9px] bg-primary-50 text-primary-600 hover:bg-primary-100 text-sm font-semibold">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" data-lucide="plus" aria-hidden="true" class="lucide lucide-plus w-3.5 h-3.5"><path d="M5 12h14"></path><path d="M12 5v14"></path></svg>
                أضف للسلة
              </button>
            </div>
          </div>
        </a>        
        
        <a href="{{route('tenant.store.detail', 'modern-scratch-tower')}}" wire:navigate class="group rounded-xl md:rounded-2xl overflow-hidden  bg-white transition [animation:fadeSlideIn_1s_ease-out_0.1s_both] animate-on-scroll animate">
          <div class="relative">
            <img src="https://cdn.midjourney.com/1d3e3086-0cd4-48b1-af6b-e01017a4ce26/0_0.png?w=800&amp;q=80" alt="Premium Dog Food" class="w-full h-56 md:h-72 object-cover group-hover:scale-105 transition-all duration-500">
            <button aria-label="Favorite" class="absolute top-2 end-2 p-2 rounded-full bg-black/30 backdrop-blur  text-white hover:text-rose-500 transition">
              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" data-lucide="heart" aria-hidden="true" class="lucide lucide-heart w-4 h-4"><path d="M2 9.5a5.5 5.5 0 0 1 9.591-3.676.56.56 0 0 0 .818 0A5.49 5.49 0 0 1 22 9.5c0 2.29-1.5 4-3 5.5l-5.492 5.313a2 2 0 0 1-3 .019L5 15c-1.5-1.5-3-3.2-3-5.5"></path></svg>
            </button>
          </div>
          <div class="p-3 border border-neutral-200 border-t-0 rounded-b-xl md:rounded-b-2xl">
            <div class="flex items-center justify-between">
              <h3 class="text-lg tracking-tight font-semibold font-geist">Modern Scratch Tower</h3>
              {{-- <p class="text-sm font-medium font-geist">$42</p> --}}
            </div>
            <p class="mt-0.5 text-xs text-neutral-600 font-geist truncate">Natural sisal with plush perch.</p>
            <div class="mt-4 flex items-center justify-between">
              <div class="flex items-center gap-1 text-neutral-600 text-xs">
                <p class="text-xl font-bold font-geist">$64</p>
              </div>
              <button class="inline-flex items-center gap-2 px-3 py-1.5 rounded-[9px] bg-primary-50 text-primary-600 hover:bg-primary-100 text-sm font-semibold">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" data-lucide="plus" aria-hidden="true" class="lucide lucide-plus w-3.5 h-3.5"><path d="M5 12h14"></path><path d="M12 5v14"></path></svg>
                أضف للسلة
              </button>
            </div>
          </div>
        </a>
        <!-- Card 2 -->
        <a href="{{route('tenant.store.detail', 'pro-grooming-kit')}}" wire:navigate class="group rounded-xl md:rounded-2xl overflow-hidden bg-white transition [animation:fadeSlideIn_1s_ease-out_0.2s_both] animate-on-scroll animate">
          <div class="relative">
            <img src="https://cdn.midjourney.com/25e056c6-57c6-4c5f-96fa-d2840628e06a/0_0.png?w=800&amp;q=80" alt="Interactive Toy" class="w-full h-56 md:h-72 object-cover group-hover:scale-105 transition-all duration-500">
            <button aria-label="Favorite" class="absolute top-2 end-2 p-2 rounded-full bg-black/30 backdrop-blur text-white hover:text-rose-500 transition">
              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" data-lucide="heart" aria-hidden="true" class="lucide lucide-heart w-4 h-4"><path d="M2 9.5a5.5 5.5 0 0 1 9.591-3.676.56.56 0 0 0 .818 0A5.49 5.49 0 0 1 22 9.5c0 2.29-1.5 4-3 5.5l-5.492 5.313a2 2 0 0 1-3 .019L5 15c-1.5-1.5-3-3.2-3-5.5"></path></svg>
            </button>
          </div>
          <div class="p-3 border border-neutral-200 border-t-0 rounded-b-xl md:rounded-b-2xl">
            <div class="flex items-center justify-between">
              <h3 class="text-lg font-semibold tracking-tight font-geist">Pro Grooming Kit</h3>
              {{-- <p class="text-sm font-medium font-geist">$28</p> --}}
            </div>
            <p class="mt-0.5 text-xs text-neutral-600 font-geist truncate">Professional grooming tools &amp; accessories.</p>
            <div class="mt-4 flex items-center justify-between">
              <div class="flex items-center gap-1 text-neutral-600">
                <p class="text-xl font-bold font-geist">$52</p>
              </div>
              <button class="inline-flex items-center gap-2 px-3 py-1.5 rounded-[9px] bg-primary-50 text-primary-600 hover:bg-primary-100 text-sm font-semibold">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" data-lucide="plus" aria-hidden="true" class="lucide lucide-plus w-3.5 h-3.5"><path d="M5 12h14"></path><path d="M12 5v14"></path></svg>
                أضف للسلة
              </button>
            </div>
          </div>
        </a>
 
      </div>
    </div>
</section>


</x-tenant::store.layout>