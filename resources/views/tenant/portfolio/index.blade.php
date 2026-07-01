<?php

use Livewire\Component;

new class extends Component
{
    //
};
?>

<x-tenant::portfolio.layout>


<div id="discovery-view" class="p-2 px-4">
                
    <div class="flex items-center gap-3 overflow-x-auto no-scrollbar mb-6 bg-stone-200/40 w-full rounded-2xl p-1 whitespace-nowrap">
        <button class="p-3 text-center py-2.5 rounded-xl bg-white text-stone-900 text-sm font-medium shadow-sm">الكل</button>
        <button class="p-3 text-center py-2.5 rounded-xl hover:bg-stone-50 text-stone-600 text-sm font-medium hover:text-stone-900">أعمال الباركيه</button>
        <button class="p-3 text-center py-2.5 rounded-xl hover:bg-stone-50 text-stone-600 text-sm font-medium hover:text-stone-900">بديل الرخام</button>
        <button class="p-3 text-center py-2.5 rounded-xl hover:bg-stone-50 text-stone-600 text-sm font-medium hover:text-stone-900">والشيبورد بديل الخشب</button>
        <button class="p-3 text-center py-2.5 rounded-xl hover:bg-stone-50 text-stone-600 text-sm font-medium hover:text-stone-900">النعلات</button>
    </div>

  <!-- Vendor Grid -->
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
      
      <!-- Vendor Card 1 -->
      <a href="{{route('tenant.portfolio.detail', 'burger-lab')}}" wire:navigate class="group flex flex-col gap-4 cursor-pointer">
          <div class="relative w-full aspect-[4/3] rounded-[16px] overflow-hidden bg-stone-100 shadow-sm group-hover:shadow-md transition-all duration-300">
              <img src="https://images.unsplash.com/photo-1550547660-d9450f859349?q=80&amp;w=800&amp;auto=format&amp;fit=crop" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" alt="Burger">
             
              {{-- <div class="absolute bottom-3 left-3 flex gap-2">
                  <span class="px-2 py-1 rounded-md bg-black/20 backdrop-blur-md text-[10px] font-medium text-white">موقع ويب</span>
              </div> --}}
          </div>
          <div>
              <div class="flex justify-between items-start mb-1">
                  <h3 class="text-base font-semibold tracking-tight text-stone-800 group-hover:text-primary-600 transition-colors"> مشروع تصميم الموقع الجديد لشركة التكنولوجيا المتقدمة </h3>
           
              </div>
              {{-- <p class="text-xs text-stone-400 line-clamp-1 mb-2 truncate"> مشروع تصميم الموقع الجديد  والتطبيقات المتعددة لشركة التكنولوجيا المتقدمة </p> --}}
              
              <!-- Tags (Many-to-Many) -->
              <div class="flex items-center gap-1 truncate overflow-x-auto no-scrollbar">
                  <span class="px-2 py-1 rounded-[6px] bg-stone-100 text-[10px] font-medium text-stone-500 border border-transparent hover:border-stone-200 transition-colors">موقع ويب</span>
                  <span class="px-2 py-1 rounded-[6px] bg-stone-100 text-[10px] font-medium text-stone-500 border border-transparent hover:border-stone-200 transition-colors">تطبيق اندرويد</span>
                  <span class="px-2 py-1 rounded-[6px] bg-stone-100 text-[10px] font-medium text-stone-500 border border-transparent hover:border-stone-200 transition-colors">تطبيق ios</span>
              </div>
          </div>
      </a>

      <!-- Vendor Card 2 -->
      <a href="{{route('tenant.portfolio.detail', 'fresh-market')}}" wire:navigate class="group flex flex-col gap-4 cursor-pointer">
          <div class="relative w-full aspect-[4/3] rounded-[16px] overflow-hidden bg-stone-100 shadow-sm group-hover:shadow-md transition-all duration-300">
              <img src="https://images.unsplash.com/photo-1542838132-92c53300491e?q=80&amp;w=800&amp;auto=format&amp;fit=crop" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" alt="Grocery">
              
              {{-- <div class="absolute bottom-3 left-3 flex gap-2">
                  <span class="px-2 py-1 rounded-md bg-black/20 backdrop-blur-md text-[10px] font-medium text-white">تطبيق اندرويد</span>
              </div> --}}
          </div>
          <div>
              <div class="flex justify-between items-start mb-1">
                  <h3 class="text-base font-semibold tracking-tight text-stone-800 group-hover:text-primary-600 transition-colors">Whole Fresh Market</h3>
              </div>
              {{-- <p class="text-xs text-stone-400 line-clamp-1 mb-2 truncate"> مشروع تصميم الموقع الجديد  والتطبيقات المتعددة لشركة التكنولوجيا المتقدمة </p> --}}
               <div class="flex items-center gap-1 truncate overflow-x-auto no-scrollbar">
                  <span class="px-2 py-1 rounded-[6px] bg-stone-100 text-[10px] font-medium text-stone-500 border border-transparent hover:border-stone-200 transition-colors">تطبيق اندرويد</span>
                  <span class="px-2 py-1 rounded-[6px] bg-stone-100 text-[10px] font-medium text-stone-500 border border-transparent hover:border-stone-200 transition-colors">تطبيق ios</span>
              </div>
          </div>
      </a>

      <!-- Vendor Card 3 -->
      <a href="{{route('tenant.portfolio.detail', 'tech-hub')}}" wire:navigate class="group flex flex-col gap-4 cursor-pointer">
          <div class="relative w-full aspect-[4/3] rounded-[16px] overflow-hidden bg-stone-100 shadow-sm group-hover:shadow-md transition-all duration-300">
              <img src="https://images.unsplash.com/photo-1550009158-9ebf69173e03?q=80&amp;w=800&amp;auto=format&amp;fit=crop" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" alt="Tech">
              
              <div class="absolute bottom-3 left-3 flex gap-2">
                  <span class="px-2 py-1 rounded-md bg-black/20 backdrop-blur-md text-[10px] font-medium text-white">تطبيق ios</span>
              </div>
          </div>
          <div>
              <div class="flex justify-between items-start mb-1">
                  <h3 class="text-base font-semibold tracking-tight text-stone-800 group-hover:text-primary-600 transition-colors">Tech Hub Central</h3>
            
              </div>
              {{-- <p class="text-xs text-stone-400 line-clamp-1 mb-2 truncate"> مشروع تصميم الموقع الجديد  والتطبيقات المتعددة لشركة التكنولوجيا المتقدمة </p> --}}
               <div class="flex flex-wrap gap-2">
                  <span class="px-2 py-1 rounded-[6px] bg-stone-100 text-[10px] font-medium text-stone-500 border border-transparent hover:border-stone-200 transition-colors">بديل الرخام</span>
                  <span class="px-2 py-1 rounded-[6px] bg-stone-100 text-[10px] font-medium text-stone-500 border border-transparent hover:border-stone-200 transition-colors">بديل الخشب</span>
              </div>
          </div>
      </a>
       <!-- Vendor Card 4 -->
       <a href="{{route('tenant.portfolio.detail', 'sushi-zen')}}" wire:navigate class="group flex flex-col gap-4 cursor-pointer">
          <div class="relative w-full aspect-[4/3] rounded-[16px] overflow-hidden bg-stone-100 shadow-sm group-hover:shadow-md transition-all duration-300">
              <img src="https://images.unsplash.com/photo-1579871494447-9811cf80d66c?q=80&amp;w=800&amp;auto=format&amp;fit=crop" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" alt="Sushi">
             
              <div class="absolute bottom-3 left-3 flex gap-2">
                  <span class="px-2 py-1 rounded-md bg-black/20 backdrop-blur-md text-[10px] font-medium text-white">تطبيق سطح مكتب</span>
              </div>
          </div>
          <div>
              <div class="flex justify-between items-start mb-1">
                  <h3 class="text-base font-semibold tracking-tight text-stone-8 group-hover:text-primary-600 transition-colors">Sushi Zen</h3>
                 
              </div>
              {{-- <p class="text-xs text-stone-400 line-clamp-1 mb-2 truncate"> مشروع تصميم الموقع الجديد  والتطبيقات المتعددة لشركة التكنولوجيا المتقدمة </p> --}}
               <div class="flex items-center gap-1 truncate overflow-x-auto no-scrollbar">
                  <span class="px-2 py-1 rounded-[6px] bg-stone-100 text-[10px] font-medium text-stone-500 border border-transparent hover:border-stone-200 transition-colors">بديل الشيبورد</span>
                  <span class="px-2 py-1 rounded-[6px] bg-stone-100 text-[10px] font-medium text-stone-500 border border-transparent hover:border-stone-200 transition-colors">بديل الخشب</span>
                  <span class="px-2 py-1 rounded-[6px] bg-stone-100 text-[10px] font-medium text-stone-500 border border-transparent hover:border-stone-200 transition-colors">باركيه</span>
              </div>
          </div>
      </a>
  </div>
</div>

 
</x-tenant::portfolio.layout>