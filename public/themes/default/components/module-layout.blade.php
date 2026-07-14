 
@props(['width' => 'max-w-5xl', 'icon' => 'hugeicons:store-02', 'title' =>  tenant('name'), 'desc' => tenant('bio'), 'backLink' => route('tenant.home'), 'backLinkText' => 'العودة للصفحة الرئيسية'])
<x-tenant-theme::layout width="{{$width}}">

    <div class="w-full mb-3 border-b-2 border-stone-100 pb-3 pt-1">
        <div class="flex h-16 items-center justify-between px-4">
            <div class="flex items-center gap-3">
                {{-- <a href="{{route('tenant.home')}}" wire:navigate  id="backBtn" class="size-9 rounded-full bg-stone-100 hover:bg-white p-0.5 border border-transparent hover:border-primary-500 flex items-center justify-center transition-all duration-200">
                    <img src="https://images.unsplash.com/photo-1585747860715-2ba37e788b70?q=80&amp;w=2074&amp;auto=format&amp;fit=crop" alt="Marcus Rivera" class="w-full h-full object-cover rounded-full transition-transform duration-500">
                </a> --}}
                <a href="{{$backLink}}" wire:navigate alt="{{$backLinkText}}" alt="{{$backLinkText}}" id="backBtn" class="p-2 rounded-xl bg-primary-500 hover:bg-primary-600 flex items-center justify-center transition-all duration-200"> 
                    <iconify-icon icon="{{$icon}}" class="text-white text-3xl" stroke-width="1.5" aria-label="{{$backLinkText}}"></iconify-icon>
                </a> 
                <a href="{{$backLink}}" wire:navigate alt="{{$backLinkText}}" alt="{{$backLinkText}}"  class="">
                    <h1 class="text-xl font-semibold tracking-tight font-geist">{{$title}}</h1>
                    <p class="text-sm text-stone-600 font-geist">{{$desc}}</p>
                </a>
            </div>
           
            @if (isset($actions) && !empty($actions))
                <div>
                    {{$actions}}
                </div>
            @endif
        </div>
    </div> 
 

    <div class="w-full mb-3 px-2 lg:px-3">
        {{ $slot }}
    </div>
</x-tenant-theme::layout>