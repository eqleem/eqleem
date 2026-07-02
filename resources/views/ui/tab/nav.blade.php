@props([
    'icon' => '',
    'label' => '',
    'name' => '',
    'activeClass' => 'bg-white rounded-t-md',
    'badge' => null,
])
<a @click.prevent="setTab('{{ $name }}')"
    :class="{ '{{ $activeClass }}': activeTab === '{{ $name }}' }" class="inline-flex items-center gap-1.5 px-3 py-2.5 text-sm [&::-webkit-scrollbar]:hidden [-ms-overflow-style:none] [scrollbar-width:none]"
    href="#{{ $name }}" {{ $attributes }}>
    @if ($icon)
        <ui:icon name="{{ $icon }}" class="me-1 opacity-75" />
    @endif
    {{ $label }}
    @if (filled($badge))
        <span class="inline-flex min-w-5 items-center justify-center rounded-full bg-primary-500 px-1.5 py-0.5 text-[10px] font-bold leading-none text-white">
            {{ $badge }}
        </span>
    @endif
</a>
