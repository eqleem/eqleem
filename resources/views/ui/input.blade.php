@props([
    'class' => null,
    'value' => null,
    'name' => null,
    'type' => 'text',
    'inputWidth' => 'w-full',
    'disabled' => (bool)false,
    'label' => null,
    'focus' => false,
    'errormsg' => true,
    'dir' => 'rtl',
    'key' => null,
    'icon' => null,
    'description' => '',
    'info' => '',
    'width' => 'w-full',
    'placeholder' => '',
    'prefix' => null,
    'suffix' => null,
    'labelWidth' => 'w-36',
    'block' => false,
    'prime' => false,
    'infoDir' => null,
    'copyable' => false,
    'goto' => false,
    'live' => false,
])

@php $iconTrailing = $attributes->get('icon:trailing'); @endphp

<ui:field name="{{ $name }}" :info="$info" :label="__($label)" :prefix="$prefix" :suffix="$suffix" :dir="$dir" :infoDir="$infoDir" :width="$width" :labelWidth="$labelWidth" :errormsg="$errormsg" :key="$key" :block="$block" :prime="$prime">

    <div class="relative w-full">

        @if ($icon)
            <ui:icon name="{{ $icon }}" class="!w-5 !h-5 @if($slot->isEmpty() && !$label) rtl:-mr-1 ltr:-ml-1 @endif absolute ltr:left-3 rtl:right-3 top-2 opacity-50" />
        @endif

        <input id="{{ $name }}" type="{{ $type }}"
            @if ($name)
                @if ($live)
                    wire:model.live="{{ $name }}"
                @else
                    wire:model="{{ $name }}"
                @endif
            @endif
            {{ $disabled ? 'disabled="disabled"' : '' }} 
    
            placeholder="{{ $placeholder }}"
            class="block {{ $inputWidth }}   rounded-md text-sm border-2 bg-white focus:bg-gray-100/50  disabled:text-gray-400/50 disabled:cursor-not-allowed  focus:text-{{ config('ui.primary', 'gray') }}-700 placeholder:text-sm focus:border-{{ config('ui.primary', 'primary') }}-500 focus:outline-none py-1.5 px-3 text-gray-600  
            @error($key ?? $name) border-red-300  @else border-transparent @enderror
            @if($icon) ps-8 @endif
            @if($iconTrailing) pe-8 @endif
            "

            
            {{ $attributes }}
            >


        @if ($iconTrailing)
            <ui:icon name="{{ $iconTrailing }}" class="!w-5 !h-5 absolute rtl:start-2 ltr:end-2 top-2 opacity-50" />
        @endif
    
        {{ $slot }}

        {{-- <div class="absolute rtl:left-2 ltr:right-2 top-1 flex items-center gap-x-1"> --}}
            @if ($copyable)
                <div x-data="{ input: '{{ $value }}'  }">
                    <ui:icon name="copy" class="!w-5 !h-5 bg-white  absolute rtl:left-2 ltr:right-2 top-2 Xopacity-50 z-10 cursor-pointer hover:text-black" @click="navigator.clipboard.writeText(input), $dispatch('notify', {text: 'تم نسخ الرابط بنجاح', type: 'success'})" />
                </div>
            @endif

            {{-- @if ($goto)
                <div x-data="{ input: '{{ $value }}'  }">
                    <ui:icon name="arrow-up-left" class="!w-5 !h-5 bg-white  Xopacity-50 z-10 cursor-pointer hover:text-black" @click="window.open(input, '_blank')" />
                </div>
            @endif --}}
        {{-- </div> --}}
    </div>
</ui:field>
