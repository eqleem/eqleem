@props([
    'heading' => null,
    'id' => random_int(1000000000, 9999999999),
])
 
<div class="accordion-item p-3 py-3" id="{{$id}}">
    <button id="{{$id}}" type="button" class=" flex w-full items-center justify-between gap-4" 
        aria-controls="{{$id}}" 
        x-on:click="selectedAccordionItem = '{{$id}}'" 
        x-bind:class="selectedAccordionItem === '{{$id}}' ? 'text-onSurfaceStrong dark:text-onSurfaceDarkStrong font-bold'  : 'text-onSurface dark:text-onSurfaceDark font-medium'" 
        x-bind:aria-expanded="selectedAccordionItem === '{{$id}}' ? 'true' : 'false'"
    >
        <ui:heading level="3">{{ $heading }}</ui:heading>
        <ui:icon name="chevron-down" class="size-5 shrink-0 transition text-black/30" x-bind:class="selectedAccordionItem === '{{$id}}'  ?  'rotate-180'  :  ''" />
    </button>
    <div x-cloak x-show="selectedAccordionItem === '{{$id}}'" id="{{$id}}" role="region" aria-labelledby="{{$id}}" x-collapse>
        <ui:text>{{ $slot }}</ui:text>
    </div>
</div>
 