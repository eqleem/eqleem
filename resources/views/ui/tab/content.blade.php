@props([ 
    'name' => '',
])

<div x-cloak x-show="activeTab === '{{$name}}'" {{ $attributes->class(['bg-white rounded-lg p-3 !rounded-b-2xl']) }}>
     {{ $slot }}
</div>