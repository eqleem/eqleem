<x-tenant-theme::store.layout>
  <x-tenant-theme::breadcrumb :links="[['url' => route('tenant.store.index'), 'title' => 'المتجر'], ['url' => null, 'title' => 'تفاصيل المنتج']]" />

<div class="flex items-center justify-between mb-5 px-2">
  <a href="{{route('tenant.store.index')}}" wire:navigate class="w-10 h-10 rounded-full flex items-center justify-center bg-stone-100 hover:bg-stone-200 transition rotate-180">
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




 
 
{{-- 
<div class="flex items-center justify-between p-2 mb-6">
  <button class="h-9 w-9 rounded-xl border border-neutral-200 flex items-center rotate-180 justify-center bg-white hover:bg-neutral-50 transition">
    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="chevron-left" aria-hidden="true" class="lucide lucide-chevron-left h-4 w-4"><path d="m15 18-6-6 6-6"></path></svg>
  </button>
  <div class="flex items-center gap-2">
    <button class="h-9 w-9 rounded-xl border border-neutral-200 flex items-center justify-center bg-white hover:bg-neutral-50 transition">
      <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="share-2" aria-hidden="true" class="lucide lucide-share-2 h-4 w-4"><circle cx="18" cy="5" r="3"></circle><circle cx="6" cy="12" r="3"></circle><circle cx="18" cy="19" r="3"></circle><line x1="8.59" x2="15.42" y1="13.51" y2="17.49"></line><line x1="15.41" x2="8.59" y1="6.51" y2="10.49"></line></svg>
    </button>
    <button class="h-9 w-9 rounded-xl border border-neutral-200 flex items-center justify-center bg-white hover:bg-neutral-50 transition">
      <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="heart" aria-hidden="true" class="lucide lucide-heart h-4 w-4"><path d="M2 9.5a5.5 5.5 0 0 1 9.591-3.676.56.56 0 0 0 .818 0A5.49 5.49 0 0 1 22 9.5c0 2.29-1.5 4-3 5.5l-5.492 5.313a2 2 0 0 1-3 .019L5 15c-1.5-1.5-3-3.2-3-5.5"></path></svg>
    </button>
  </div>
</div>
  --}}



  <section class="px-3 mb-8 w-full">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-12">
      <!-- Product Gallery -->
      <div class="fade-in">
        <div class="aspect-square bg-stone-100 rounded-2xl mb-4 overflow-hidden">
          <img id="mainProductImage" src="https://cdn.midjourney.com/944ee6b3-3133-4f7f-934b-9dce46faef61/0_0.png?w=800&amp;q=80" alt="Nike Air Max 270" class="w-full h-full object-cover product-zoom">
        </div>
        <div class="flex gap-3 overflow-x-auto pb-2">
          <button class="gallery-nav flex-shrink-0 w-20 h-20 bg-stone-100 rounded-lg overflow-hidden border-2 border-stone-900">
            <img src="https://cdn.midjourney.com/944ee6b3-3133-4f7f-934b-9dce46faef61/0_0.png?w=800&amp;q=80" alt="View 1" class="w-full h-full object-cover">
          </button>
          <button class="gallery-nav flex-shrink-0 w-20 h-20 bg-stone-100 rounded-lg overflow-hidden border-2 hover:border-stone-300 border-transparent">
            <img src="https://cdn.midjourney.com/b364832d-777c-48e7-911a-1be3f0ea7f47/0_0.png?w=800&amp;q=80" alt="View 2" class="w-full h-full object-cover">
          </button>
          <button class="gallery-nav flex-shrink-0 w-20 h-20 bg-stone-100 rounded-lg overflow-hidden border-2 hover:border-stone-300 border-transparent">
            <img src="https://cdn.midjourney.com/2a604624-00fc-4126-9c6c-ab9173bdee29/0_0.png?w=800&amp;q=80" alt="View 3" class="w-full h-full object-cover">
          </button>
          <button class="gallery-nav flex-shrink-0 w-20 h-20 bg-stone-100 rounded-lg overflow-hidden border-2 hover:border-stone-300 border-transparent">
            <img src="https://cdn.midjourney.com/598f8ab8-2981-4083-813d-d9c6df6c0f8d/0_0.png?w=800&amp;q=80" alt="View 4" class="w-full h-full object-cover">
          </button>
        </div>
      </div>

      <!-- Product Details -->
      <div >
 
        
        <div class="flex justify-between">
          <div class="">
            <h1 class="text-xl font-bold tracking-tight text-stone-900 mb-1 font-geist">Nike Air Max 270</h1>
            <div class="flex items-center gap-2">
              <span class="inline-flex items-center rounded-md bg-green-100 text-green-800 text-xs px-2 py-1 font-medium">In Stock</span>
              <span class="inline-flex items-center rounded-md bg-orange-100 text-orange-800 text-xs px-2 py-1 font-medium">Best Seller</span>
            </div>
          </div>
          <div class="flex items-baseline gap-4 mb-6">
           <div class="text-end">
              <span class="sf-display text-2xl text-black font-sans font-semibold">$299</span>
              <p class="line-through text-sm text-stone-500 font-sans">$345</p>
            </div>
          </div>
        </div>
 
        <div class="flex items-center justify-between gap-4 mb-8 mt-5">
          <div class="flex items-center border border-stone-200 rounded-[12px] h-12 px-2">
            <button class="w-8 h-full flex items-center justify-center text-stone-500 hover:text-[#111827]"><svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" role="img" width="1em" height="1em" viewBox="0 0 24 24" data-icon="lucide:minus" class="iconify iconify--lucide"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14"></path></svg></button>
            <span class="w-8 text-center text-sm font-medium">1</span>
            <button class="w-8 h-full flex items-center justify-center text-stone-500 hover:text-[#111827]"><svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" role="img" width="1em" height="1em" viewBox="0 0 24 24" data-icon="lucide:plus" class="iconify iconify--lucide"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14m-7-7v14"></path></svg></button>
        </div>

          <button id="addToCartMain" class="flex-1 inline-flex items-center justify-center gap-2 bg-primary-600 text-white px-8 py-2.5 rounded-xl font-semibold text-lg hover:bg-primary-700 transition-all duration-300 font-geist" data-product="Nike Air Max 270" data-price="160.00">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5"><circle cx="8" cy="21" r="1"></circle><circle cx="19" cy="21" r="1"></circle><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"></path></svg>
            أضف للسلة
          </button>
          
        </div>

        <div class="border-t border-stone-200 pt-8">

          <div class="flex gap-2 mb-6 bg-stone-100 rounded-2xl p-1 w-full">
            <button class="w-full text-center py-2.5 rounded-xl bg-white text-stone-900 text-sm font-medium shadow-sm">تفاصيل المنتج</button>
            <button class="w-full text-center py-2.5 rounded-xl text-stone-600 text-sm font-medium hover:text-stone-900"> التقييمات </button>
          </div>

          
          <h3 class="text-lg font-semibold mb-4 font-geist">Product Details</h3>
          <div class="space-y-3 text-sm text-stone-600">
            <p class="font-geist">The Nike Air Max 270 delivers unrivaled all-day comfort with the largest Max Air unit yet, offering a super soft ride that feels as impossible as it looks.</p>
            <div class="grid grid-cols-2 gap-4">
              <div>
                <span class="font-medium text-stone-900 font-geist">Material:</span>
                <p class="font-geist">Mesh and synthetic upper</p>
              </div>
              <div>
                <span class="font-medium text-stone-900 font-geist">Sole:</span>
                <p class="font-geist">Air Max cushioning</p>
              </div>
              <div>
                <span class="font-medium text-stone-900 font-geist">Closure:</span>
                <p class="font-geist">Lace-up</p>
              </div>
              <div>
                <span class="font-medium text-stone-900 font-geist">Origin:</span>
                <p class="font-geist">Made in Vietnam</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</x-tenant-theme::store.layout>