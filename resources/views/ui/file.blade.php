@props([
    'class' => null,
    'value' => null,
    'name' => null,
    'tempurl' => null,
    'type' => 'text',
    'inputWidth' => 'w-full',
    'label' => null,
    'disabled' => false,
    'focus' => false,
    'dir' => '',
    'info' => '',
    'placeholder' => '',
    'target' => null,
    'uploadLabel' => null,
])

<ui:field name="{{ $name }}" info="{{ $info }}" label="{{ __($label) }}">
    <div class="text-sm pt-1">
        {{ $slot }}
    </div>

    <label for="file-{{ $name }}" class="  cursor-pointer items-center flex gap-x-2">
        
        <b class="text-xs bg-primary-100 text-primary-600 hover:bg-primary-200 p-2 px-3 rounded-lg flex items-center gap-x-2">
            <svg  xmlns="http://www.w3.org/2000/svg"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="1.25"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-upload w-5"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2 -2v-2" /><path d="M7 9l5 -5l5 5" /><path d="M12 4l0 12" /></svg>

               <div wire:loading wire:target="{{ $name }}">
                    <svg xmlns="http://www.w3.org/2000/svg"
                        class="icon icon-tabler icon-tabler-loader animate-spin h-5 w-5" viewBox="0 0 24 24"
                        stroke-width="1.5" stroke="currentColor" fill="none" stroke-linecap="round"
                        stroke-linejoin="round">
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

            {{ $uploadLabel ?? __('Upload file') }}
        </b>
 
    </label>


    {{-- <div>
        <canvas id="canvas">
            Your browser does not support the HTML5 canvas element.
        </canvas>
    </div>

    <div id="result"></div>

    @if ($tempurl)
        <img src="{{ $tempurl }}" class="w-24 rounded mb-1">
    @endif --}}


    <input id="file-{{ $name }}" type="file"
        @if ($name) wire:model="{{ $name }}" @endif
        {{ $disabled ?? 'disabled="disabled"' }} dir="{{ $dir }}" placeholder="{{ $placeholder }}"
        class="sr-only">

</ui:field>

{{-- @scritps

<script></script>
@endscripts --}}
