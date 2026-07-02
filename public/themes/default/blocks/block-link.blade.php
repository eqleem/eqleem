@php
    use App\Support\CtaLink;

    $data = $block->data ?? [];
    $url = CtaLink::urlFromData($data);
@endphp

@if(filled($url))
<x-tenant-theme::block-link
    :title="CtaLink::titleFromData($data)"
    :link="$url"
    :icon="CtaLink::iconFromData($data)"
    :desc="CtaLink::descriptionFromData($data)"
/>
@endif
