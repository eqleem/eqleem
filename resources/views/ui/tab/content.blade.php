@props([ 
    'name' => '',
])

<div x-cloak x-show="activeTab === '{{$name}}'" {{ $attributes->class(['bg-white rounded-lg p-3']) }}>
     {{ $slot }}
</div>