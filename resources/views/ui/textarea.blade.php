@props([
    'class' => null,
    'value' => null,
    'name' => null,
    'label' => null,
    'disabled' => false,
    'dir' => '',
    'info' => '',
    'prefix' => null,
    'suffix' => null,
    'placeholder' => '',
    'width' => 'w-full',
    'key' => null,
])

<ui:field name="{{ $name }}" info="{{ $info }}" label="{{ __($label) }}" :width="$width" :key="$key">
    <textarea {{ $attributes }} 
        @if ($name) wire:model="{{ $name }}" @endif
        id="{{ $name }}" dir="{{ $dir }}" placeholder="{{ $placeholder }}" value="{{ $value }}"
        class="block w-full rounded-md text-sm border-2 bg-white focus:text-gray-700 focus:border-primary-500 focus:outline-none py-2 px-3 text-gray-600 placeholder:text-sm  @error($key ?? $name) border-red-300  @else border-transparent @enderror">{{ $value }}</textarea>

            {{ $slot }}
</ui:field>
