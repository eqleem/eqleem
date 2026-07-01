<?php

use Livewire\Component;

new class extends Component
{
    //
};
?>

<div>
    <div class="relative w-full h-[480px]" style="animation: blurIn 1s ease-out 0.5s both;">
        <img src="https://images.unsplash.com/photo-1506905925346-21bda4d32df4?q=80&amp;w=1974&amp;auto=format&amp;fit=crop" alt="Mountain Vista" class="absolute inset-0 w-full h-full rounded-2xl object-cover">
        <div class="absolute inset-0 bg-gradient-to-t from-black/75 via-black/30 to-transparent rounded-2xl"></div>
  

        <!-- Hero Content -->
        <div class="absolute bottom-0 left-0 right-0 p-6 text-white">
          <div style="animation: slideUp 0.6s ease-out 0.8s both;" class="">
            <div class="flex items-center gap-2 mb-2">
              <span class="text-[11px] font-medium text-white bg-white/10 ring-white/20 ring-1 rounded-full pt-1 pr-2.5 pb-1 pl-2.5 backdrop-blur-sm">Premium Collection</span>
              <span class="px-2.5 py-1 rounded-full bg-white/10 backdrop-blur-sm ring-1 ring-white/20 text-[11px] font-medium text-white flex items-center gap-1">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" data-lucide="trending-up" aria-hidden="true" class="lucide lucide-trending-up w-3 h-3"><path d="M16 7h6v6"></path><path d="m22 7-8.5 8.5-5-5L2 17"></path></svg>
                Trending
              </span>
            </div>
            <h1 class="mt-3 text-[38px] leading-[1.05] font-semibold tracking-tight">Discover Your Perfect Escape</h1>
            <p class="mt-3 text-[15px] leading-relaxed text-white/90">Experience handpicked luxury stays in the world's most breathtaking destinations. From mountain lodges to coastal villas.</p>
          </div>
          
          <div class="mt-6 space-y-3" style="animation: slideUp 0.6s ease-out 0.9s both;">
            <div class="flex items-center gap-3">
              <button id="exploreBtn" class="flex-1 px-5 py-3.5 rounded-xl bg-white hover:bg-white/95 text-slate-900 text-[14px] font-medium transition-all flex items-center justify-center gap-2 shadow-lg">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" data-lucide="compass" aria-hidden="true" class="lucide lucide-compass w-4 h-4"><circle cx="12" cy="12" r="10"></circle><path d="m16.24 7.76-1.804 5.411a2 2 0 0 1-1.265 1.265L7.76 16.24l1.804-5.411a2 2 0 0 1 1.265-1.265z"></path></svg>
                Start exploring
              </button>
              <button id="watchBtn" class="px-5 py-3.5 rounded-xl bg-white/10 hover:bg-white/20 ring-1 ring-white/30 text-white text-[14px] font-medium backdrop-blur transition-all flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" data-lucide="play-circle" aria-hidden="true" class="lucide lucide-play-circle w-4 h-4"><path d="M9 9.003a1 1 0 0 1 1.517-.859l4.997 2.997a1 1 0 0 1 0 1.718l-4.997 2.997A1 1 0 0 1 9 14.996z"></path><circle cx="12" cy="12" r="10"></circle></svg>
                Watch
              </button>
            </div>
          </div>
        </div>
      </div>

      

    {{-- <div class="w-full group relative overflow-hidden rounded-2xl bg-gradient-to-br from-emerald-500 to-cyan-600 p-8 text-white hover:shadow-xl transition-all duration-300 xhover:scale-[1.02] Xopacity-0" style="animation: fadeInScale 0.8s ease-out 0.4s forwards">
        <div class="relative z-10">
          <h3 class="text-2xl font-semibold mb-2 font-geist tracking-tight">Trending Now</h3>
          <p class="text-emerald-100 mb-4 font-geist">Discover the most popular collections this week</p>
          <button class="inline-flex items-center gap-2 bg-white/20 backdrop-blur-sm px-4 py-2 rounded-full text-sm font-medium hover:bg-white/30 transition-all duration-200">
            Explore <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="arrow-left" aria-hidden="true" class="lucide lucide-arrow-left rotate-180 w-4 h-4"><path d="M5 12h14"></path><path d="m12 5 7 7-7 7"></path></svg>
          </button>
        </div>
        <div class="absolute inset-0 bg-[url(https://hoirqrkdgbmvpwutwuwj.supabase.co/storage/v1/object/public/assets/assets/445dcfd9-d3b8-4f16-ac08-1279950515b8_1600w.jpg)] bg-cover" style=""></div>
    </div> --}}
</div>