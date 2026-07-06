@props([
    'name' => null,
    'label' => null,
    'info' => '',
    'uploadLabel' => null,
    'shape' => 'circle',
    'outputSize' => 512,
    'cropTitle' => 'قص الصورة',
    'allowShapeSwitch' => false,
    'preview' => null,
    'previewClass' => 'mb-1 size-20 rounded-full object-cover',
])

@php
    use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

    $previewUrl = null;

    if ($preview instanceof TemporaryUploadedFile) {
        try {
            $previewUrl = $preview->temporaryUrl();
        } catch (\Throwable) {
            $previewUrl = null;
        }
    } elseif (is_string($preview) && filled($preview)) {
        $previewUrl = $preview;
    }
@endphp

<div
    class="relative"
    x-data="fileCrop(@js([
        'wireName' => $name,
        'outputSize' => $outputSize,
        'shape' => $shape,
        'allowShapeSwitch' => $allowShapeSwitch,
        'previewUrl' => $previewUrl,
    ]))"
>
    <ui:field name="{{ $name }}" info="{{ $info }}" label="{{ __($label) }}">
        <div class="text-sm pt-1" wire:ignore>
            <img
                x-show="previewUrl"
                x-cloak
                :src="previewUrl"
                alt="{{ __($label) }}"
                class="{{ $previewClass }}"
            >
        </div>

        <button
            type="button"
            x-on:click="pickFile()"
            class="flex cursor-pointer items-center gap-x-2"
        >
            <b class="flex items-center gap-x-2 rounded-lg bg-primary-100 p-2 px-3 text-xs text-primary-600 hover:bg-primary-200">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-upload size-5">
                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                    <path d="M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2 -2v-2" />
                    <path d="M7 9l5 -5l5 5" />
                    <path d="M12 4l0 12" />
                </svg>

                <div wire:loading wire:target="{{ $name }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-loader size-5 animate-spin" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
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
        </button>

        <input
            x-ref="fileInput"
            type="file"
            accept="image/jpeg,image/png,image/webp,image/gif"
            class="sr-only"
            x-on:change="openCropper($event)"
        >
    </ui:field>

    <div
        x-show="open"
        x-cloak
        {{-- dir="ltr" --}}
        data-file-crop-overlay
        class="fixed inset-0 z-[100] flex items-center justify-center p-4"
        x-transition.opacity
        x-on:click.stop
    >
        <div class="absolute inset-0 bg-gray-800/75" x-on:click="closeCropper()"></div>

        <div class="relative w-full {{ $shape === 'free' ? 'max-w-lg' : 'max-w-md' }} rounded-xl bg-white shadow-xl" x-on:click.stop>
            <div class="flex items-center justify-between border-b border-gray-100 p-3 px-4">
                <p class="text-sm font-semibold text-gray-600" dir="rtl">{{ $cropTitle }}</p>
                <button type="button" x-on:click="closeCropper()" class="rounded-md bg-gray-100 p-1 text-gray-400 hover:bg-gray-200">
                    <ui:icon name="x" class="!size-4" />
                </button>
            </div>

            <div class="flex justify-center p-4">
                <div
                    wire:ignore
                    :id="cropHostId"
                    class="{{ $shape === 'free' ? 'min-h-[400px] min-w-[360px]' : 'min-h-[360px] min-w-[320px]' }}"
                ></div>
            </div>

            <div class="flex justify-end gap-2 border-t border-gray-100 p-3 px-4" dir="rtl">
                <ui:button
                    type="button"
                    variant="ghost"
                    label="إلغاء"
                    x-bind:disabled="cropping"
                    x-on:click="closeCropper()"
                />
                <ui:button
                    type="button"
                    x-bind:disabled="cropping"
                    x-on:click="confirmCrop()"
                >
                    <span x-show="! cropping">تأكيد</span>
                    <span x-show="cropping" x-cloak class="inline-flex items-center gap-1.5">
                        <svg xmlns="http://www.w3.org/2000/svg" class="size-4 animate-spin" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
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
                        جاري المعالجة...
                    </span>
                </ui:button>
            </div>
        </div>
    </div>
</div>
