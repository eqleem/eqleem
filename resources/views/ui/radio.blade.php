@props([
    'value' => null,
    'live' => false,
    'name' => 'text',
    'label' => null,
    'subtitle' => '',
    'info' => '',
    'placeholder' => '',
    'options' => [],
])

<ui:field name="{{ $name }}" info="{{ $info }}" label="{{ __($label) }}" wire:key="{{ $name }}">
    {{-- <select wire:model="{{ $name }}" id="{{ $name }}"
        class="py-1.5  bg-white borderxxx  p-2 px-3 rounded-md w-full shadow-sm focus:outline-none @error($name) border-red-300 text-red-900 placeholder-red-300 focus:border-red-300 focus:shadow-outline-red  @else border-transparent focus:border-primary-400 placeholder-gray-400 text-gray-700 @enderror ">
        @if ($options == array_values($options))
            @foreach ($options as $item)
                <option value="{{ $item }}" @if ($value == $item) selected @endif>
                    {{ __($item) }}</option>
            @endforeach
        @else
            @foreach ($options as $key => $item)
                <option value="{{ $key }}" @if ($value == $key) selected @endif>
                    {{ __($item) }}</option>
            @endforeach
        @endif
    </select> --}}

    <div class="flex items-center gap-1.5 text-sm flex-wrap">
 

           @if ($options == array_values($options))
            @foreach ($options as $item)
                <label for="radio-{{ $name }}-{{ $item }}"
                class="p-2 px-3 bg-white hover:bg-primary-100 rounded cursor-pointer flex items-center gap-x-2 capitalize">
          

                <input type="radio"
                    @if ($live) wire:model.live="{{ $name }}" @else wire:model="{{ $name }}" @endif
                    value="{{ $item }}" 
                    id="radio-{{ $name }}-{{ $item }}" />

                   {{ __($item) }}
            </label>
            @endforeach
        @else
            @foreach ($options as $key => $item)
                     <label for="radio{{ $key }}"
                class="p-2 px-3 bg-white hover:bg-primary-100 rounded cursor-pointer flex items-center gap-x-2 capitalize">
          
                <input type="radio"
                    @if ($live) wire:model.live="{{ $name }}" @else wire:model="{{ $name }}" @endif
                    value="{{ $key }}" id="radio{{ $key }}" />

                    {{ __($item) }}
            </label>

            @endforeach
        @endif

         

    </div>

</ui:field>
