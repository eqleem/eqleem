@props([
    'icon' => '',
    'label' => '',
    'name' => '',
    'activeClass' => 'bg-white rounded-t-md',
])
<a @click.prevent="activeTab = '{{ $name }}'"
    :class="{ '{{ $activeClass }}': activeTab === '{{ $name }}' }" class="inline-block px-3 py-2.5 text-sm [&::-webkit-scrollbar]:hidden [-ms-overflow-style:none] [scrollbar-width:none]"
    href="#{{ $name }}" {{ $attributes }}>
    @if ($icon)
        <ui:icon name="{{ $icon }}" class="me-1 opacity-75" />
    @endif
    {{ $label }}
</a>
