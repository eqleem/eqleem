@props([
    'value' => false,
    'live' => false,
    'name' => null,
    'label' => null,
    'info' => null,
    'class' => null,
    'placeholder' => null,
    'position' => 'end',
    'description' => null,
    'width' => '',
    'labelWidth' => 'w-36',
])

<ui:field name="{{ $name }}" info="{{ $info }}" label="{{ __($label) }}" :width="$width" :labelWidth="$labelWidth">

    <label for="toggle-{{ $name }}" class="">

        {{-- <div class="flex flex-col gap-1">
            @if ($label && $position === 'end')
            <div class="flex flex-col gap-1 me-3">
                <ui:text>{{$label}}</ui:text>
                @if ($description)
                    <ui:text>{{$description}}</ui:text>
                @endif
            </div>
            @endif  

        </div> --}}

        <input id="toggle-{{ $name }}" type="checkbox" name="{{ $name }}" {{ $attributes }} @if ($live) wire:model.live="{{ $name }}" @else wire:model="{{ $name }}" @endif class="peer sr-only" role="switch" {{ $value ? 'checked' : '' }}  />
    
        <div class="relative cursor-pointer h-6 w-11 after:h-5 after:w-5 peer-checked:after:translate-x-[1.3rem] rounded-full bg-gray-200 after:absolute after:bottom-0 after:left-[0.1rem] after:top-0 after:my-auto after:rounded-full after:bg-white after:transition-all after:content-[''] peer-checked:bg-blue-500 peer-checked:after:bg-gray-100 " aria-hidden="true"></div>

        {{-- <div class="flex flex-col gap-1">
            @if ($label && $position === 'start')
                <div class="flex flex-col gap-1 ms-3">
                    <ui:text>{{$label}}</ui:text>
                    @if ($description)
                        <ui:text>{{$description}}</ui:text>
                    @endif
                </div>
            @endif
        </div> --}}
    
    </label>
</ui:field>