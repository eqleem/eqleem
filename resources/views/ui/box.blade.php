@props([
    'title' => null,
    'subtitle' => null,
    'content' => null,
    'footer' => null,
    'action' => null,
    'rightAction' => null,
])

<div {{ $attributes->class('bg-white rounded-xl w-full') }}>
    @if ($title)
        <div class="mb-6 p-4  text-gray-600 border-b-2 border-gray-100 flex items-center justify-between">
            <div>
                <div class=" flex items-center gap-x-2">
                    {{ $rightAction ?? '' }}
                    <div>
                        <h2 class="text-sm font-semibold text-gray-500">{{ $title }}</h2>
                        @if ($subtitle)
                            <small class="opacity-50 text-xs">{{ $subtitle }}</small>
                        @endif
                    </div>

                </div>

            </div>
            @if ($action)
                <div class="">
                    {{ $action ?? '' }}
                </div>
            @endif
        </div>
    @endif

    <div class="w-full [&>*:first-child]:rounded-ts-none">
        @if ($content)
            <div class="p-4">
                {{ $content }}
            </div>
        @endif

        {{ $slot }}
    </div>

    @if ($footer)
        <div class="flex justify-end p-4">
            {{ $footer ?? '' }}
        </div>
    @endif
</div>
