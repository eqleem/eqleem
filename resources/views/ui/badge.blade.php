@props([
    'size' => null,
    'text' => null,
    'color' => null,
])
 

@php
$classes = match ($size) {
    'xl' => 'text-lg px-2 py-1.5',
    'lg' => 'text-base px-2 py-1',
    default => 'text-sm px-1.5 py-1',
    'sm' => 'text-xs px-1.5 py-1',
};

$color = match ($color) {
    'red' => 'bg-red-50 text-red-500',
    'green' => 'bg-green-50 text-green-500',
    'blue' => 'bg-blue-50 text-blue-500',
    'yellow' => 'bg-yellow-50 text-yellow-500',
    'purple' => 'bg-purple-50 text-purple-500',
    'pink' => 'bg-pink-50 text-pink-500',
    'gray' => 'bg-gray-50 text-gray-500',
    'white' => 'bg-white text-white',
    default => 'bg-gray-50 text-gray-500',
};

$classes = 'text-gray-500 [*+&]:mt-2 dark:text-white/70 bg-gray-100 rounded-lg  inline-block ' . $classes . ' ' . $color;
@endphp

<span {{ $attributes->class($classes) }}>
    {{ $slot->isEmpty() ? $text : $slot }}
</span>
