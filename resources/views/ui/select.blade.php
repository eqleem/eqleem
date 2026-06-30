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
])

<ui:field name="{{ $name }}" info="{{ $info }}" label="{{ __($label) }}" :width="$width" :labelWidth="$labelWidth">
    <select @if($live) wire:model.live="{{ $name }}" @else wire:model="{{ $name }}"@endif id="{{ $name }}"
        {{$attributes->class('py-2 bg-white border  p-2 px-3 text-sm  flex-shrink-0 rounded-md shadow-smX focus:outline-none border-transparent focus:border-primary-400 placeholder-gray-400 text-gray-700   ') }}>
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
    </select>
</ui:field>
