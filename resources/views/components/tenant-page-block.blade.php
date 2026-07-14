@props([
    'block',
])

@php
    $isLink = in_array($block->type, ['block-link', 'link'], true);
    $card = $isLink ? \App\Support\BlockLinkCard::fromBlock($block) : null;
@endphp

@if ($isLink)
    @if ($card)
        @includeFirst([
            $block->variant,
            "tenant-theme::blocks.{$block->type}",
            "default-tenant-theme::blocks.{$block->type}",
            'tenant-theme::blocks.block-link',
            'default-tenant-theme::blocks.block-link',
        ], ['card' => $card, 'block' => $block])
    @endif
@else
    @includeFirst([
        $block->variant,
        "tenant-theme::blocks.{$block->type}",
        "default-tenant-theme::blocks.{$block->type}",
    ], ['block' => $block])
@endif
