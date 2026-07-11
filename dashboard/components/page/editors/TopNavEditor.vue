<script setup>
import { reactive, ref } from 'vue';
import Form from '../../ui/Form.vue';
import Toggle from '../../ui/Toggle.vue';
import Input from '../../ui/Input.vue';
import Button from '../../ui/Button.vue';
import { usePageStructureStore } from '../../../stores/pageStructure.js';
import { ApiError } from '../../../lib/api.js';
import { notifyApiError } from '../../../lib/notify.js';

const props = defineProps({
    blockId: { type: Number, required: true },
    editor: { type: Object, required: true },
});

const emit = defineEmits(['saved']);
const store = usePageStructureStore();

const form = reactive({
    show_share_button: Boolean(props.editor.show_share_button),
    show_theme_toggle: Boolean(props.editor.show_theme_toggle),
    show_language_switcher: Boolean(props.editor.show_language_switcher),
    show_back_button: Boolean(props.editor.show_back_button),
    show_pages_menu: Boolean(props.editor.show_pages_menu),
    show_client_login: Boolean(props.editor.show_client_login),
    client_login_label: props.editor.client_login_label ?? 'دخول العملاء',
});

const errors = reactive({});
const saving = ref(false);

async function submit() {
    saving.value = true;
    Object.keys(errors).forEach((key) => delete errors[key]);

    try {
        const payload = await store.updateBlock(props.blockId, { ...form });
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
        <p class="mb-4 text-xs text-gray-400">تحكم في العناصر الظاهرة في الشريط العلوي للصفحة.</p>

        <div class="space-y-2">
            <Toggle v-model="form.show_share_button" name="show_share_button" label="زر المشاركة" />
            <Toggle v-model="form.show_theme_toggle" name="show_theme_toggle" label="زر الوضع الليلي" />
            <Toggle v-model="form.show_language_switcher" name="show_language_switcher" label="مبدّل اللغة" />
            <Toggle v-model="form.show_back_button" name="show_back_button" label="زر الرجوع للرئيسية" />
            <Toggle v-model="form.show_pages_menu" name="show_pages_menu" label="قائمة الصفحات" />
            <div class="flex items-center gap-2">
                <Toggle v-model="form.show_client_login" name="show_client_login" label="زر دخول العملاء" />
                <Input
                    v-if="form.show_client_login"
                    v-model="form.client_login_label"
                    name="client_login_label"
                    placeholder="دخول العملاء"
                    :error="errors.client_login_label"
                />
            </div>
        </div>


        <template #footer>
            <Button type="submit" label="حفظ" :loading="saving" />
        </template>
    </Form>
</template>
