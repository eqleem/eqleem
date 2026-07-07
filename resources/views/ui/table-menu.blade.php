@props([
    'width' => 'w-48',
])

<div
    x-data="{
        dropdownMenu: false,
        position: { top: 0, left: 0 },
        toggleMenu() {
            this.dropdownMenu = ! this.dropdownMenu;

            if (! this.dropdownMenu) {
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
    {{ $attributes->class('relative shrink-0') }}
    @keydown.escape.window="dropdownMenu = false"
>
    <button
        type="button"
        x-ref="trigger"
        @click="toggleMenu()"
        class="hover:bg-gray-200 p-1 rounded-lg inline-block"
        aria-haspopup="menu"
        x-bind:aria-expanded="dropdownMenu ? 'true' : 'false'"
    >
        <ui:icon name="dots" class="text-gray-400" />
    </button>

    <template x-teleport="body">
        <div
            x-show="dropdownMenu"
            x-ref="menu"
            x-cloak
            @click.outside="dropdownMenu = false"
            @click="dropdownMenu = false"
            :style="`position: fixed; top: ${position.top}px; left: ${position.left}px; z-index: 9999;`"
            @class([
                $width,
                'bg-white border shadow-sm rounded-lg text-gray-800 text-sm flex p-1 flex-col gap-y-px',
            ])
            x-transition.scale.origin.top
            role="menu"
        >
            {{ $slot }}
        </div>
    </template>
</div>
