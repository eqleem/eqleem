@props([ 
    'active' => '' ,
    'nav' => '',
    'content' => '',
])

<div x-data="{ activeTab: '{{$active}}' }" {{ $attributes->class(['mb-5']) }}>
    <div {{ $nav->attributes->class(['whitespace-nowrap text-sm  rounded-t-lg text-gray-600 overflow-x-auto']) }}>
        {{ $nav }}
    </div>
    @if ($content)
        <div {{ $content->attributes->class(['[&>*:first-child]:rounded-ts-none']) }}>
            {{ $content }}
        </div>
    @else 
        <div class="[&>*:first-child]:rounded-ts-none ">
            {{ $slot }}
        </div>
    @endif
</div>