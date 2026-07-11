<script setup>
import { computed, inject, reactive, ref, watch } from 'vue';
import Form from '../../ui/Form.vue';
import Input from '../../ui/Input.vue';
import Textarea from '../../ui/Textarea.vue';
import Select from '../../ui/Select.vue';
import Button from '../../ui/Button.vue';
import { usePageStructureStore } from '../../../stores/pageStructure.js';
import { api, ApiError } from '../../../lib/api.js';
import { notifyApiError } from '../../../lib/notify.js';

const props = defineProps({
    blockId: { type: Number, required: true },
    editor: { type: Object, required: true },
});

const emit = defineEmits(['saved']);
const store = usePageStructureStore();
const blockActions = inject('blockActions', null);

const form = reactive({
    link_type: props.editor.link_type ?? '',
    title: props.editor.title ?? '',
    description: props.editor.description ?? '',
    content_id: props.editor.content_id ?? null,
    content_search: props.editor.selected_content_title ?? '',
    selected_content_title: props.editor.selected_content_title ?? '',
});

const linkTypeOptions = computed(() => props.editor.link_type_options ?? {});
const needsContentPicker = computed(() => String(form.link_type).startsWith('item:'));
const contentResults = ref([]);
const showContentResults = ref(false);
const errors = reactive({});
const saving = ref(false);

watch(() => form.link_type, async () => {
    form.content_id = null;
    form.content_search = '';
    form.selected_content_title = '';
    showContentResults.value = false;
    contentResults.value = [];

    if (needsContentPicker.value) {
        await searchContent();
    }
});

async function searchContent() {
    if (!needsContentPicker.value) {
        return;
    }

    const params = new URLSearchParams();
    params.set('link_type', form.link_type);
    if (form.content_search.trim().length >= 2) {
        params.set('search', form.content_search.trim());
    }

    try {
        const payload = await api(`/page/link-content?${params.toString()}`);
        contentResults.value = Array.isArray(payload?.data) ? payload.data : [];
        showContentResults.value = true;
    } catch {
        contentResults.value = [];
    }
}

function selectContent(item) {
    form.content_id = item.id;
    form.selected_content_title = item.title;
    form.content_search = item.title;
    showContentResults.value = false;
}

async function submit() {
    saving.value = true;
    Object.keys(errors).forEach((key) => delete errors[key]);

    try {
        const updater = blockActions?.updateBlock
            ?? ((id, body) => store.updateBlock(id, body));
        const payload = await updater(props.blockId, {
            link_type: form.link_type,
            title: form.title,
            description: form.description,
            content_id: form.content_id,
        });
        emit('saved', payload);
    } catch (error) {
        if (error instanceof ApiError) {
            Object.assign(errors, Object.fromEntries(
                Object.entries(error.errors || {}).map(([key, value]) => [key, value?.[0] ?? null]),
            ));
        }
        notifyApiError(error, 'تعذر الحفظ.');
    } finally {
        saving.value = false;
    }
}
</script>

<template>
    <Form class="!rounded-none !p-4" @submit="submit">
        <p class="mb-4 text-xs text-gray-400">
            اختر نوع الرابط أولاً — يُعبّأ العنوان والوصف تلقائياً ويمكنك تعديلهما لاحقاً.
        </p>

        <div class="space-y-2">
            <Select v-model="form.link_type" name="link_type" label="نوع الرابط" :options="linkTypeOptions" :error="errors.link_type" />
            <Input v-model="form.title" name="title" label="العنوان" :error="errors.title" />
            <Textarea v-model="form.description" name="description" label="الوصف" :rows="3" :error="errors.description" />

            <div v-if="needsContentPicker" class="space-y-2">
                <Input
                    v-model="form.content_search"
                    name="content_search"
                    label="بحث المحتوى"
                    :error="errors.content_id"
                    @focus="searchContent"
                    @input="searchContent"
                />
                <ul v-if="showContentResults && contentResults.length" class="max-h-40 overflow-y-auto rounded-lg border border-gray-100">
                    <li v-for="item in contentResults" :key="item.id">
                        <button type="button" class="w-full px-3 py-2 text-start text-sm hover:bg-gray-50" @click="selectContent(item)">
                            {{ item.title }}
                        </button>
                    </li>
                </ul>
                <p v-if="form.selected_content_title" class="text-xs text-gray-400">المحدد: {{ form.selected_content_title }}</p>
            </div>
        </div>


        <template #footer>
            <Button type="submit" label="حفظ" :loading="saving" />
        </template>
    </Form>
</template>
