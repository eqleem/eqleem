@props([
    'title' => '',
    'subtitle' => '',
    'icon' => null,
    'actions' => null,
    'prime' => false,
])

<div {{ $attributes->merge(['class' => 'bg-white rounded-2xl shadow-sm w-full relative']) }}>

    @if ($prime)
        @if (currentTenant()?->fresh(['subscription.plan.features'])->missingFeature('domain'))
            {{-- <div
                class="text-2xl z-50 absolute -top-3 -left-2 bg-yellow-100 border border-dotted border-yellow-500 rounded px-2 p-1 rotate-12">
                👑
            </div> --}}
            <div
                class=" text-center items-center justify-center flex absolute w-full h-full bg-black/40 rounded-2xl cursor-not-allowed z-40">
                <a href="{{ route('admin.plan.home') }}" wire:navigate
                    class="bg-white hover:text-blue-500 p-4 rounded-xl cursor-pointer"> يتطلب
                    ترقية الباقة 👑</a href="{{ route('admin.plan.home') }}" wire:navigate>
            </div>
        @endif
    @endif

    <div class="flex justify-between items-start p-4  border-gray-200">
        <div class="flex gap-x-4">
            @if ($icon)
                <div class="h-8 w-8 bg-gray-200 p-1 rounded-lg flex-shrink-0 flex items-center justify-center">
                    {{ $icon }}
                </div>
            @endif

            @if ($title)
                <div class="">
                    <h2 class="text-lg">{{ $title }} </h2>
                    @if ($subtitle)
                        <p class="opacity-50 text-sm"><span class="opacity-50">/</span> {{ $subtitle }} </p>
                    @endif
                </div>
            @endif
        </div>
        <div class="flex-shrink-0 ">
            {{ $actions }}
        </div>
    </div>

    <div class="flex flex-col [&>*:last-child]:rounded-b-xl [&>*:last-child]:border-b-0">
        {{ $slot }}
    </div>
</div>
