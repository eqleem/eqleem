@props([
    'mark' => null,
    'url' => null,
    'alt' => '',
    'class' => 'size-20 object-cover',
    'iconSize' => '2.25rem',
])

@php
    $resolved = is_array($mark) ? $mark : null;

    if ($resolved === null && filled($url)) {
        $resolved = [
            'type' => 'image',
            'value' => '',
            'color' => '',
            'url' => $url,
        ];
    }

    $type = (string) ($resolved['type'] ?? 'image');
    $value = (string) ($resolved['value'] ?? '');
    $color = (string) ($resolved['color'] ?? '');
    $imageUrl = (string) ($resolved['url'] ?? $url ?? '');
    $iconSize = filled($iconSize) ? (string) $iconSize : '2.25rem';
    $iconFontSize = 'var(--brand-mark-icon-size, '.$iconSize.')';
    $iconStyle = filled($color)
        ? 'color: '.$color.'; font-size: '.$iconFontSize.';'
        : 'font-size: '.$iconFontSize.';';
@endphp

@if ($type === 'emoji' && filled($value))
    <span {{ $attributes->class([$class, 'flex items-center justify-center leading-none select-none']) }} style="font-size: {{ $iconFontSize }};" role="img" aria-label="{{ $alt }}">
        {{ $value }}
    </span>
@elseif ($type === 'icon' && filled($value))
    <span {{ $attributes->class([$class, 'flex items-center justify-center']) }} role="img" aria-label="{{ $alt }}">
        <iconify-icon icon="{{ $value }}" style="{{ $iconStyle }}" stroke-width="1.5"></iconify-icon>
    </span>
@elseif (filled($imageUrl))
    <img src="{{ $imageUrl }}" alt="{{ $alt }}" {{ $attributes->class([$class]) }}>
@endif
