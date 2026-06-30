@props([
    'value' => null,
    'name' => 'text',
    'label' => null,
    'subtitle' => '',
    'info' => '',
    'placeholder' => '',
    'options' => [],
    'live' => false,
    'width' => '',
    'labelWidth' => 'w-36',
    'title' => '',
    'block' => false,
])

<ui:field name="{{ $name }}" info="{{ $info }}" label="{{ __($label) }}" :width="$width" :labelWidth="$labelWidth" :block="$block">
    <label class="flex items-center gap-x-2 p-1" for="{{ $name }}">
        <input type="checkbox" @if($live) wire:model.live="{{ $name }}" @else wire:model="{{ $name }}" @checked($value) @endif id="{{ $name }}"
            value="{{ $value }}" @if ($value == $value) selected @endif {{$attributes->class($width.' w-auto py-2 bg-white border  p-2 px-3 text-sm  flex-shrink-0 rounded-md shadow-smX focus:outline-none border-transparent focus:border-primary-400 placeholder-gray-400 text-gray-700   ') }}>
            {{ __($title ?? $label) }}
    </label>
</ui:field>
