@props([
    'value' => null,
    'name' => 'homeApp',
    'label' => null,
    'info' => '',
    'live' => false,
])

@php
    $options = ['0' => __('Default home page')];

    foreach (config('content-types', []) as $slug => $type) {
        $options[$slug] = $type['name'];
    }
@endphp

<ui:select
    :name="$name"
    :label="$label"
    :info="$info"
    :options="$options"
    :value="$value"
    :live="$live"
/>
