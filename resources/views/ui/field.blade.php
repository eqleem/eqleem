@props([
    'name' => '',
    'label' => null,
    'labelWidth' => 'w-36',
    'info' => '',
    'hidelabel' => true,
    'block' => false,
    'prefix' => null,
    'suffix' => null,
    'dir' => null,
    'width' => 'w-full',
    'errormsg' => true,
    'key' => null,
    'prime' => false,
    'infoDir' => null,
])

<div wire:key="{{$name}}" class="relative lg:flex items-center  gap-x-2 p-1 bg-gray-100/75 rounded-md @if ($block) w-full flex-col items-start @else  {{ $width }} @endif XX[&:has(+[*])]:mb-2 XX[*+&]:mt-2 @if ($prime) pt-10 border border-red-500 border-dashed @endif">
    
    @if ($prime)
        <div title="هذا الخيار يتطلب اشتراك برايم" class="text-end p-px absolute top-0 end-0">
            <span class="px-2 py-1 rounded-sm bg-orange-50 ms-1 inline-block text-orange-600 font-bold text-sm">برايم 👑</span>
        </div>
    @endif

    @if ($label)
        <label for="{{$name}}" class="inline-block text-sm text-gray-500 p-2 flex-shrink-0 @if ($block) w-full @else {{ $labelWidth }} @endif font-semibold">
            {{ $label }} </label>
    @endif
    <div {{ $attributes->merge(['class' => 'relative '. $width]) }} dir="{{ $dir }}">
        <div class="flex items-center w-full text-gray-500">

            @if ($prefix)
                <div class="px-2 text-xs opacity-70 shrink-0">
                    {{ $prefix }}
                </div>
            @endif
            
            <div class="w-full">
                {{ $slot }}
            </div>

            @if ($suffix)
                <div class="px-2 text-xs opacity-70 shrink-0">
                    {{ $suffix }}
                </div>
            @endif

        </div> 

        <div>
            @if ($info)
                <small class=" text-gray-400 flex items-center gap-x-1 text-xs mt-1 px-1 bg-gray-50 rounded-md" dir="{{ $infoDir }}">
                    <ui:icon name="info-circle" class="!w-4 !h-4" />
                    {{ $info }}
                </small>
            @endif
        </div>
  
        @if ($errormsg)
            @error($key ?? $name)
                <small class=" text-red-600 flex items-center gap-x-1 text-xs mt-1 px-1 bg-red-50 rounded-md" dir="{{ $infoDir }}">
                    <ui:icon name="alert-circle" class="!w-4 !h-4 text-red-600" />
                    {{ $message }}
                </small>
            @enderror
        @endif
    </div>
</div>
