@props([
    'center' => null,
    'end' => null,
    'container' => 'container ',
    'classes' => '',
])
<div {{ $attributes->class('bg-white py-2 px-2 xl:px-0') }}>
    <div class="{{$container}} mx-auto flex items-center justify-between">
        <div class="flex items-center {{$classes}}">
            {{$slot}}
        </div>  

        @if ($center)
            <div {{ $center->attributes->class('flex items-center gap-4') }}>
                {{$center}}
            </div>
        @endif

        @if ($end)
            <div {{ $end->attributes->class('flex items-center gap-4') }}>
                {{$end}}
            </div>
        @endif
    </div>
</div>