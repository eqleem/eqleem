@props([
    'active' =>  null,
])

<div 
    x-data="{ selectedAccordionItem : 0}" 
    @if($active)
        x-init="selectedAccordionItem = '{{$active}}'" 
    @else 
        x-init="selectedAccordionItem = $el.querySelector('.accordion-item').id" 
    @endif
    
    class="w-full divide-y divide-black/5 overflow-hidden ">
    {{ $slot }}
</div>
