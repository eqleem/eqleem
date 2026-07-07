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

@php
    $isGroupedOptions = collect($options)->contains(
        fn (mixed $option): bool => is_array($option) && array_key_exists('id', $option),
    );
@endphp

<ui:field name="{{ $name }}" info="{{ $info }}" label="{{ __($label) }}" :width="$width" :labelWidth="$labelWidth">
    <select @if($live) wire:model.live="{{ $name }}" @else wire:model="{{ $name }}"@endif id="{{ $name }}"
        {{$attributes->class('py-2 bg-white border  p-2 px-3 text-sm  flex-shrink-0 rounded-md shadow-smX focus:outline-none border-transparent focus:border-primary-400 placeholder-gray-400 text-gray-700   ') }}>
        @if ($isGroupedOptions)
            @php $openGroup = false; @endphp
            @foreach ($options as $option)
                @if ($option['selectable'] ?? true)
                    <option value="{{ $option['id'] }}" @if ((string) $value === (string) $option['id']) selected @endif>
                        {{ $option['label'] }}
                    </option>
                @else
                    @if ($openGroup)
                        </optgroup>
                    @endif
                    <optgroup label="{{ $option['label'] }}">
                    @php $openGroup = true; @endphp
                @endif
            @endforeach
            @if ($openGroup)
                </optgroup>
            @endif
        @elseif (is_array($options) && $options == array_values($options))
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
