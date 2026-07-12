<script setup>
import { reactive, ref } from 'vue';
import Form from '../../ui/Form.vue';
import Toggle from '../../ui/Toggle.vue';
import Input from '../../ui/Input.vue';
import Select from '../../ui/Select.vue';
import Separator from '../../ui/Separator.vue';
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
    position: props.editor.position ?? 'bottom-end',
    show_whatsapp: Boolean(props.editor.show_whatsapp),
    whatsapp_number: props.editor.whatsapp_number ?? '',
    show_phone: Boolean(props.editor.show_phone),
    phone_number: props.editor.phone_number ?? '',
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
        <p class="mb-4 text-xs text-stone-400">تخصيص الأزرار الطافية الثابتة على الصفحة.</p>

        <div class="space-y-2">
            <Select
                v-model="form.position"
                name="position"
                label="موضع الأزرار"
                :options="editor.position_options ?? {}"
                :error="errors.position"
            />
            <Separator />
            <Toggle v-model="form.show_whatsapp" name="show_whatsapp" label="زر واتساب" />
            <Toggle v-model="form.show_phone" name="show_phone" label="زر الاتصال" />
            <Input
                v-if="form.show_whatsapp"
                v-model="form.whatsapp_number"
                name="whatsapp_number"
                label="رقم واتساب"
                placeholder="966500000000"
                dir="ltr"
                :error="errors.whatsapp_number"
            />
            <Input
                v-if="form.show_phone"
                v-model="form.phone_number"
                name="phone_number"
                label="رقم الاتصال"
                placeholder="+966500000000"
                dir="ltr"
                :error="errors.phone_number"
            />
        </div>


        <template #footer>
            <Button type="submit" label="حفظ" :loading="saving" />
        </template>
    </Form>
</template>
