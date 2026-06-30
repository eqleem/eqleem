@props([
    'color' => 'gray',
    'icon' => null,
    'heading' => null,
    'text' => null,
    'close' => false,
    'action' => null,
])
 
<div {{$attributes->class('rounded-lg bg-'.$color.'-100 p-2 px-1 [*+&]:mt-4')}}>
    <div class="flex items-center">
        @if ($icon)
        <div class="flex-shrink-0 ms-2">
            <ui:icon name="{{ $icon }}" size="6" class="text-{{ $color }}-500/50" />
        </div>
        @endif
        <div class="px-2 @if($icon) pt-1 @endif text-sm flex items-center justify-between w-full">
            <div>
                @if ($heading)
                    <ui:heading level="3" size="base" class="text-{{ $color }}-700">{{ $heading }}</ui:heading>
                @endif
                @if ($text)
                    <p size="1" class="text-xs !text-{{ $color }}-600">{{ $text }}</p>
                @endif
                @if($slot)
                    {{$slot}}
                @endif
            </div>
            <div>
                @if ($action)
                    {{$action}}
                @endif
                @if ($close)
                    <ui:button icon="x" variant="ghost" />
                @endif
            </div>

        </div>
    </div>
</div>

