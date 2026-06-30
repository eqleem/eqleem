@props([
    'text' => null,
    'height' => '2',
    'color' => 'black/5',
    'spacing' => '8',
])

{{-- <div {{ $attributes->class('w-full h-1 bg-black/20 my-8 text-center') }}>
    @if ($text)
        <div class="text-center text-gray-500 text-sm bg-white px-4 x-mt-4 absolute -top-2 left-1/2 -translate-x-1/2 inline-block mx-auto">{{ $text }}</div>
    @endif
</div> --}}


<div class="relative my-{{ $spacing }}" {{ $attributes }}>
    <div class="absolute inset-0 flex items-center" aria-hidden="true">
      <div {{ $attributes->class('w-full border-t-'.$height.' border-'.$color) }}></div>
    </div>
    @if ($text)
        <div class="relative flex justify-center">
            <span class="bg-white px-2 text-sm text-black/40">{{ $text }}</span>
        </div>
    @endif
  </div>

  