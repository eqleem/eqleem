<script setup>
import { onBeforeUnmount, onMounted, ref, watch } from 'vue';
import {
    ClassicEditor,
    Essentials,
    Bold,
    Italic,
    Font,
    Paragraph,
    BlockQuote,
    Heading,
    List,
    Link,
    Image,
    ImageCaption,
    ImageResize,
    ImageStyle,
    ImageToolbar,
    ImageUpload,
    ImageInsert,
    MediaEmbed,
    SimpleUploadAdapter,
    Underline,
    Autoformat,
    HorizontalLine,
    AutoImage,
    CodeBlock,
    Alignment,
} from 'ckeditor5';
import coreTranslations from 'ckeditor5/translations/ar.js';
import 'ckeditor5/ckeditor5.css';
import Field from './Field.vue';

const props = defineProps({
    /** Initial HTML only — do not live-bind keystrokes via v-model. */
    modelValue: { type: String, default: '' },
    name: { type: String, default: 'body' },
    label: { type: String, default: null },
    info: { type: String, default: '' },
    uploadUrl: { type: String, required: true },
    minHeight: { type: String, default: '220px' },
    disabled: { type: Boolean, default: false },
});

const host = ref(null);
const ready = ref(false);

// Keep the CKEditor instance outside Vue reactivity — proxying it breaks EventEmitter (_events).
let editorInstance = null;
let creating = null;
let destroyed = false;

function csrfToken() {
    const match = document.cookie.match(/(?:^|; )XSRF-TOKEN=([^;]*)/);

    return match ? decodeURIComponent(match[1]) : null;
}

function getData() {
    if (!editorInstance || destroyed) {
        return props.modelValue ?? '';
    }

    try {
        return editorInstance.getData();
    } catch {
        return props.modelValue ?? '';
    }
}

function setData(html) {
    if (!editorInstance || destroyed) {
        return;
    }

    const next = html ?? '';

    try {
        if (editorInstance.getData() === next) {
            return;
        }

        editorInstance.setData(next);
    } catch {
        // Editor may already be tearing down (route change / unmount).
    }
}

async function createEditor(initialHtml = '') {
    if (!host.value || editorInstance || creating || destroyed) {
        return;
    }

    creating = ClassicEditor.create(host.value, {
        initialData: initialHtml || '',
        plugins: [
            Essentials,
            Bold,
            Italic,
            Font,
            Paragraph,
            BlockQuote,
            Heading,
            List,
            Link,
            MediaEmbed,
            Underline,
            Image,
            ImageUpload,
            ImageResize,
            ImageStyle,
            ImageToolbar,
            ImageCaption,
            SimpleUploadAdapter,
            HorizontalLine,
            AutoImage,
            ImageInsert,
            CodeBlock,
            Autoformat,
            Alignment,
        ],
        language: {
            ui: 'ar',
            content: 'ar',
        },
        translations: [coreTranslations],
        toolbar: {
            items: [
                'heading', '|', 'bold', 'italic', 'underline', '|',
                'fontColor', 'fontBackgroundColor', 'alignment',
                'link', '|',
                'bulletedList', 'numberedList', '|',
                'blockQuote', '|',
                'mediaEmbed', 'insertImage', '|',
                'codeBlock', '|',
                'horizontalLine',
            ],
        },
        heading: {
            options: [
                { model: 'paragraph', title: 'Paragraph', class: '' },
                { model: 'heading1', view: 'h1', title: 'Heading 1', class: 'h1' },
                { model: 'heading2', view: 'h2', title: 'Heading 2', class: 'h2' },
                { model: 'heading3', view: 'h3', title: 'Heading 3', class: 'h3' },
            ],
        },
        image: {
            toolbar: [
                'imageTextAlternative',
                'toggleImageCaption',
                '|',
                'imageStyle:inline',
                'imageStyle:wrapText',
                'imageStyle:breakText',
                '|',
                'resizeImage',
            ],
        },
        mediaEmbed: {
            previewsInData: true,
        },
        simpleUpload: {
            uploadUrl: props.uploadUrl,
            withCredentials: true,
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                ...(csrfToken() ? { 'X-XSRF-TOKEN': csrfToken() } : {}),
            },
        },
    });

    try {
        const instance = await creating;

        if (destroyed) {
            await instance.destroy().catch(() => {});
            return;
        }

        editorInstance = instance;
        ready.value = true;
    } catch {
        editorInstance = null;
        ready.value = false;
    } finally {
        creating = null;
    }
}

async function destroyEditor() {
    destroyed = true;
    ready.value = false;

    const instance = editorInstance;
    editorInstance = null;

    if (creating) {
        try {
            const pending = await creating;
            await pending.destroy().catch(() => {});
        } catch {
            // create aborted / failed
        }

        creating = null;
    }

    if (!instance) {
        return;
    }

    try {
        await instance.destroy();
    } catch {
        // Ignore teardown races during route navigation.
    }
}

onMounted(() => {
    destroyed = false;
    createEditor(props.modelValue || '');
});

onBeforeUnmount(() => {
    destroyEditor();
});

watch(() => props.modelValue, (value) => {
    if (!ready.value || destroyed) {
        return;
    }

    setData(value ?? '');
});

defineExpose({
    getData,
    setData,
});
</script>

<template>
    <Field :name="name" :label="label" :info="info" block>
        <div
            ref="host"
            class="ck-vue-host relative z-0 w-full overflow-hidden rounded-md bg-white"
            :style="{ minHeight }"
            :aria-disabled="disabled"
        ></div>
    </Field>
</template>

<style>
.ck-vue-host .ck-editor__editable {
    min-height: 220px;
    color: #313842;
}
</style>
