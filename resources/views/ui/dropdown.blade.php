@props([
    'trigger' => null,
])
 
<div x-data="{ openDropdown: false }" class="relative">
    <div @click="openDropdown = !openDropdown" {{ $trigger->attributes->class('cursor-pointer') }}>
        {{ $trigger }}
        @if ($trigger->attributes->get('icon:trailing'))
            <ui:icon name="{{ $trigger->attributes->get('icon:trailing') }}" class="w-4 h-4" />
        @endif
    </div>
 
    <div x-show="openDropdown" @click.outside="openDropdown = false" {{ $attributes->class('absolute [*+&]:mt-2 z-40 left-0') }} x-transition x-cloak>
        {{ $slot }}
    </div>
</div>