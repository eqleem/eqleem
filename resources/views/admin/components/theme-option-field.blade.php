@php
    $wireName = 'themeOptions.'.$key;
    $uploadName = 'themeOptionUploads.'.$key;
    $fieldType = $field['type'] ?? 'text';
    $fieldLabel = $field['label'] ?? $key;
    $fieldInfo = $field['info'] ?? '';
    $fieldOptions = $field['options'] ?? [];
@endphp

@switch($fieldType)
    @case('picker-color')
        <ui:picker-color
            :name="$wireName"
            :label="__($fieldLabel)"
            :options="$fieldOptions"
            :value="$value"
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
        <ui:upload-single-image
            :name="$wireName"
            :uploadName="$uploadName"
            :label="__($fieldLabel)"
            :info="$fieldInfo"
            :value="$value"
            :pendingUpload="$upload ?? null"
        />
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
