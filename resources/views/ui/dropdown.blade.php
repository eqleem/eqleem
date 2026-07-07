@props([
    'trigger' => null,
    'width' => 'w-48',
])

<div
    x-data="{
        openDropdown: false,
        position: { top: 0, left: 0 },
        toggleDropdown() {
            this.openDropdown = ! this.openDropdown;

            if (! this.openDropdown) {
                return;
            }

            this.$nextTick(() => this.updatePosition());
        },
        updatePosition() {
            const rect = this.$refs.trigger.getBoundingClientRect();
            const menuWidth = this.$refs.menu?.offsetWidth ?? 192;
            const isRtl = document.documentElement.dir === 'rtl';

            this.position.top = rect.bottom + 8;
            this.position.left = isRtl ? rect.left : rect.right - menuWidth;
        },
    }"
    class="relative"
    @keydown.escape.window="openDropdown = false"
>
    <div x-ref="trigger" @click="toggleDropdown()" {{ $trigger->attributes->class('cursor-pointer') }}>
        {{ $trigger }}
        @if ($trigger->attributes->get('icon:trailing'))
            <ui:icon name="{{ $trigger->attributes->get('icon:trailing') }}" class="w-4 h-4" />
        @endif
    </div>

    <template x-teleport="body">
        <div
            x-show="openDropdown"
            x-ref="menu"
            x-cloak
            @click.outside="openDropdown = false"
            @click="openDropdown = false"
            :style="`position: fixed; top: ${position.top}px; left: ${position.left}px; z-index: 9999;`"
            {{ $attributes->class([$width, 'bg-white border shadow-sm rounded-lg text-sm flex p-1 flex-col gap-y-px']) }}
            x-transition
        >
            {{ $slot }}
        </div>
    </template>
</div>