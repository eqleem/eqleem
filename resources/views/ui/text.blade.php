@props([
    'size' => null,
    'text' => null,
])
 

@php
$classes = match ($size) {
    'xl' => 'text-lg',
        'lg' => 'text-base',
        default => 'text-sm',
        'sm' => 'text-xs',
};

$classes = 'text-gray-500 [*+&]:mt-2 dark:text-white/70 ' . $classes;
@endphp

<div {{ $attributes->class($classes) }}>
    {{ $slot->isEmpty() ? $text : $slot }}
</div>
