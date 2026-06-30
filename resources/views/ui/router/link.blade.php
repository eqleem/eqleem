@props(['href'])
 
<button x-data x-on:click="history.pushState({}, '', '{{ $href }}')" {{$attributes}}>{{ $slot }}</button>
 
 