@php
    $wireName = 'themeOptions.'.$key;
    $uploadName = 'themeOptionUploads.'.$key;
    $fieldType = $field['type'] ?? 'text';
    $fieldLabel = $field['label'] ?? $key;
    $fieldInfo = $field['info'] ?? '';
    $fieldOptions = $field['options'] ?? [];
    $cropEnabled = (bool) ($field['crop'] ?? false);
    $cropShape = $field['cropShape'] ?? 'both';
    $previewUrl = null;

    if (($upload ?? null) instanceof \Livewire\Features\SupportFileUploads\TemporaryUploadedFile) {
        try {
            $previewUrl = $upload->temporaryUrl();
        } catch (\Throwable) {
            $previewUrl = null;
        }
    } elseif (filled($value ?? null)) {
        $previewUrl = str_starts_with((string) $value, 'http')
            ? $value
            : \Illuminate\Support\Facades\Storage::url((string) $value);
    }
@endphp

@switch($fieldType)
    @case('picker-color')
        <ui:picker-color
            :name="$wireName"
            :label="__($fieldLabel)"
            :options="$fieldOptions"
            :value="$value"
            :allowCustom="(bool) ($field['allowCustom'] ?? false)"
        />
        @break

    @case('tailwindcss-colorpicker-light')
        <ui:tailwindcss-colorpicker-light
            :name="$wireName"
            :label="__($fieldLabel)"
            :value="$value"
        />
        @break

    @case('upload-single-image')
        @if ($cropEnabled)
            @php
                $fileCropShape = match ($cropShape) {
                    'free' => 'free',
                    'square' => 'square',
                    default => 'square',
                };
                $allowCropShapeSwitch = $cropShape === 'both';
            @endphp
            <ui:file-crop
                :name="$uploadName"
                :label="__($fieldLabel)"
                :info="$fieldInfo"
                uploadLabel="{{ __('Upload file') }}"
                :shape="$fileCropShape"
                :allowShapeSwitch="$allowCropShapeSwitch"
                cropTitle="قص الصورة"
                :outputSize="1920"
                previewClass="mb-2 h-32 w-full max-w-xs rounded-lg object-cover"
                :preview="$previewUrl"
            />
        @else
            <ui:upload-single-image
                :name="$wireName"
                :uploadName="$uploadName"
                :label="__($fieldLabel)"
                :info="$fieldInfo"
                :value="$value"
                :pendingUpload="$upload ?? null"
            />
        @endif
        @break

    @case('radio')
        <ui:radio
            :name="$wireName"
            :label="__($fieldLabel)"
            :info="$fieldInfo"
            :options="$fieldOptions"
            :value="$value"
        />
        @break

    @case('select-entry-app')
        <ui:select-entry-app
            :name="$wireName"
            :label="__($fieldLabel)"
            :info="$fieldInfo"
            :value="$value"
        />
        @break

    @case('select')
        <ui:select
            :name="$wireName"
            :label="__($fieldLabel)"
            :info="$fieldInfo"
            :options="$fieldOptions"
            :value="$value"
        />
        @break

    @case('textarea')
        <ui:textarea
            :name="$wireName"
            :label="__($fieldLabel)"
            :info="$fieldInfo"
            :value="$value"
        />
        @break

    @default
        <ui:input
            :name="$wireName"
            :label="__($fieldLabel)"
            :info="$fieldInfo"
            :value="$value"
        />
@endswitch
