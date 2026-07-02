@props([
    'value' => null,
    'name' => 'image',
    'uploadName' => null,
    'label' => null,
    'info' => '',
])

@php
    $uploadField = $uploadName ?? $name;
    $imageUrl = filled($value)
        ? (str_starts_with((string) $value, 'http') ? $value : \Illuminate\Support\Facades\Storage::url((string) $value))
        : null;
@endphp

<ui:field name="{{ $uploadField }}" :info="$info" :label="__($label)">
    @if ($imageUrl)
        <img src="{{ $imageUrl }}" alt="{{ __($label) }}" class="mb-2 h-32 w-full max-w-xs rounded-lg object-cover">
    @endif

    <label for="file-{{ $uploadField }}" class="flex cursor-pointer items-center gap-x-2">
        <b class="flex items-center gap-x-2 rounded-lg bg-primary-100 p-2 px-3 text-xs text-primary-600 hover:bg-primary-200">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-upload h-5 w-5">
                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                <path d="M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2 -2v-2" />
                <path d="M7 9l5 -5l5 5" />
                <path d="M12 4l0 12" />
            </svg>

            <div wire:loading wire:target="{{ $uploadField }}">
                <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-loader h-5 w-5 animate-spin" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                    <line x1="12" y1="6" x2="12" y2="3" />
                    <line x1="16.25" y1="7.75" x2="18.4" y2="5.6" />
                    <line x1="18" y1="12" x2="21" y2="12" />
                    <line x1="16.25" y1="16.25" x2="18.4" y2="18.4" />
                    <line x1="12" y1="18" x2="12" y2="21" />
                    <line x1="7.75" y1="16.25" x2="5.6" y2="18.4" />
                    <line x1="6" y1="12" x2="3" y2="12" />
                    <line x1="7.75" y1="7.75" x2="5.6" y2="5.6" />
                </svg>
            </div>

            {{ __('Upload file') }}
        </b>
    </label>

    <input id="file-{{ $uploadField }}" type="file" wire:model="{{ $uploadField }}" accept="image/*" class="sr-only">
</ui:field>
