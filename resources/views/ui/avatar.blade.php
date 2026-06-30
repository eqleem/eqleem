@props([
    'size' => null,
    'src' => null,
    'alt' => null,
    'rounded' => 'full',
])
 
@php
$classes = match ($size) {
    'xl' => 'size-16',
    'lg' => 'size-12',
    'md' => 'size-10',
    'sm' => 'size-8',
    'xs' => 'size-6',
    default => 'size-10',
};
 
$rounded = match ($rounded) {
    'full' => 'rounded-full',
    'lg' => 'rounded-xl',
    'md' => 'rounded-lg',
    'sm' => 'rounded-md',
    'xs' => 'rounded-sm',
    'none' => 'rounded-none',
    default => 'rounded-full',
};
@endphp

<img src="{{ $src }}" alt="{{ $alt }}" {{ $attributes->class("object-cover $rounded $classes") }}>
 
