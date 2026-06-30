@props([
    'title' => '',
    'icon' => null,
    'backRoute' => null,
])

<div>
    @if ($title)
        <div class=" border-b p-px  bg-gray-300 text-gray-500 text-base" hx-boost="true">
            <div class="flex items-center gap-x-3 max-w-5xl mx-auto truncate text-sm">
                @if ($backRoute)
                    <a href="{{ $backRoute }}" wire:navigate
                        class="bg-gray-200 p-2 flex items-center gap-x-2 hover:bg-gray-100 text-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ltr:rotate-180" viewBox="0 0 24 24"
                            fill="none">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                stroke-miterlimit="10" stroke-width="1.5" d="M14.43 5.93L20.5 12l-6.07 6.07"></path>
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                stroke-miterlimit="10" stroke-width="1.5" d="M3.5 12h16.83" opacity=".4"></path>
                        </svg>
                    </a>
                @endif
                <p> {{ $title }} </p>
            </div>
        </div>
    @endif

    <div class="mx-auto py-6 md:py-10 max-w-5xl px-1 md:px-3X" {{ $attributes }}>
        {{ $slot }}
    </div>
</div>
