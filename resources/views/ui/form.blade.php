@props([
    'title' => null,
    'subtitle' => null,
    'inputs' => null,
    'target' => '',
    'formClass' => '',
    'footer' => null
])

<form {{ $attributes->class('bg-white rounded-xl w-full p-5 py-8 flex flex-col gap-y-4') }}>
    @if ($title)
        <div class="mb-6 text-gray-600 ">
            <h2 class="text-sm font-semibold">{{ $title }}</h2>
            @if ($subtitle)
                <small class="opacity-50">{{ $subtitle }}</small>
            @endif
        </div>
    @endif

    <div class="{{ $formClass }}">
        {{ $slot }}

        @if($inputs)
            <div {{ $inputs->attributes->class('flex flex-col gap-1 mt-4') }}>
                {{ $inputs }}
            </div>
        @endif
    </div>

    @if($footer)
        <div {{$footer->attributes->class('flex justify-end mt-5')}}>
            {{ $footer }}
        </div>
    @endif
</form>
