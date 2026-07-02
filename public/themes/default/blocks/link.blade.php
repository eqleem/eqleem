@php
    $data = $block->data ?? [];
    $url = $data['url'] ?? null;
@endphp

@if (filled($url))
    <x-tenant-theme::block-link
        :title="$data['title'] ?? $block->title ?? ''"
        :link="$url"
        icon="hugeicons:link-04"
        desc=""
    />
@endif
