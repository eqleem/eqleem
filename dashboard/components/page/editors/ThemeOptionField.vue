<script setup>
import { computed, ref, watch } from 'vue';
import PickerColor from '../../ui/PickerColor.vue';
import TailwindColorPickerLight from '../../ui/TailwindColorPickerLight.vue';
import FileCrop from '../../ui/FileCrop.vue';
import Radio from '../../ui/Radio.vue';
import Input from '../../ui/Input.vue';
import Textarea from '../../ui/Textarea.vue';
import Select from '../../ui/Select.vue';

const props = defineProps({
    fieldKey: { type: String, required: true },
    field: { type: Object, required: true },
    modelValue: { default: '' },
    preview: { type: String, default: null },
    error: { type: String, default: null },
});

const emit = defineEmits(['update:modelValue', 'upload']);

const uploadFile = ref(null);
const localPreview = ref(props.preview);

watch(() => props.preview, (value) => {
    if (!uploadFile.value) {
        localPreview.value = value;
    }
});

const type = computed(() => props.field?.type ?? 'text');
const label = computed(() => props.field?.label ?? props.fieldKey);
const info = computed(() => props.field?.info ?? '');
const options = computed(() => props.field?.options ?? []);
const cropEnabled = computed(() => Boolean(props.field?.crop));
const cropShape = computed(() => {
    const shape = props.field?.cropShape ?? 'both';

    if (shape === 'free') {
        return 'free';
    }

    if (shape === 'square') {
        return 'square';
    }

    return 'square';
});
const allowShapeSwitch = computed(() => (props.field?.cropShape ?? 'both') === 'both');

function onUploadChange(file) {
    uploadFile.value = file;
    emit('upload', { key: props.fieldKey, file });
}
</script>

<template>
    <PickerColor
        v-if="type === 'picker-color'"
        :model-value="modelValue"
        :name="fieldKey"
        :label="label"
        :options="options"
        :allow-custom="Boolean(field?.allowCustom)"
        @update:model-value="$emit('update:modelValue', $event)"
    />

    <TailwindColorPickerLight
        v-else-if="type === 'tailwindcss-colorpicker-light'"
        :model-value="modelValue"
        :name="fieldKey"
        :label="label"
        @update:model-value="$emit('update:modelValue', $event)"
    />

    <FileCrop
        v-else-if="type === 'upload-single-image' && cropEnabled"
        v-model="uploadFile"
        v-model:preview="localPreview"
        :name="fieldKey"
        :label="label"
        :info="info"
        upload-label="رفع صورة"
        crop-title="قص الصورة"
        :shape="cropShape"
        :allow-shape-switch="allowShapeSwitch"
        :output-size="1920"
        preview-class="mb-2 h-32 w-full max-w-xs rounded-lg object-cover"
        :error="error"
        @change="onUploadChange"
    />

    <FileCrop
        v-else-if="type === 'upload-single-image'"
        v-model="uploadFile"
        v-model:preview="localPreview"
        :name="fieldKey"
        :label="label"
        :info="info"
        upload-label="رفع صورة"
        :enable-crop="false"
        preview-class="mb-2 h-32 w-full max-w-xs rounded-lg object-cover"
        :error="error"
        @change="onUploadChange"
    />

    <Radio
        v-else-if="type === 'radio'"
        :model-value="modelValue"
        :name="fieldKey"
        :label="label"
        :info="info"
        :options="options"
        :error="error"
        @update:model-value="$emit('update:modelValue', $event)"
    />

    <Select
        v-else-if="type === 'select'"
        :model-value="modelValue"
        :name="fieldKey"
        :label="label"
        :info="info"
        :options="options"
        :error="error"
        @update:model-value="$emit('update:modelValue', $event)"
    />

    <Textarea
        v-else-if="type === 'textarea'"
        :model-value="modelValue"
        :name="fieldKey"
        :label="label"
        :info="info"
        :error="error"
        @update:model-value="$emit('update:modelValue', $event)"
    />

    <Input
        v-else
        :model-value="modelValue"
        :name="fieldKey"
        :label="label"
        :info="info"
        :error="error"
        @update:model-value="$emit('update:modelValue', $event)"
    />
</template>
