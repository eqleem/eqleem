@props(['links' => [['url' => route('tenant.home'), 'title' => 'الرئيسية']] ])
<nav class="flex bg-stone-100 rounded-md p-2 mb-2">
    <ol role="list" class="flex items-center space-x-0.5">
        <li>
            <div class="-m-1">
                <a href="{{ route('tenant.home') }}" wire:navigate title="" class="p-1 flex items-center gap-2 text-sm font-medium text-stone-500 rounded-md focus:outline-none focus:ring-2 focus:text-stone-900 focus:ring-stone-900 hover:text-stone-700"> 
                    <iconify-icon icon="hugeicons:home-08" class="text-base"></iconify-icon>
                    الرئيسية
                </a>
            </div>
        </li>
        @foreach ($links as $link)
            <li wire:key="breadcrumb-{{ $loop->index }}">   
                <div class="flex items-center">
                    <svg class="flex-shrink-0 w-5 h-5 text-stone-300" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                        <path d="M5.555 17.776l8-16 .894.448-8 16-.894-.448z" />
                    </svg>
                    <div class="-m-1">
                        @if ($loop->last)
                        <span class="p-1 ms-0.5 text-sm font-medium text-stone-400 "> 
                            {{ data_get($link, 'title') }}
                        </span>
                        @else
                        <a href="{{ data_get($link, 'url') }}" wire:navigate title="" class="p-1 flex items-center gap-2 text-sm font-medium text-stone-500 rounded-md focus:outline-none focus:ring-2 focus:text-stone-900 focus:ring-stone-900 hover:text-stone-700"> 
                            {{ data_get($link, 'title') }}
                        </a>
                        @endif
                    </div>
                </div> 
            </li>
        @endforeach       
    </ol>
</nav>