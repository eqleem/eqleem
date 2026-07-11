<script setup>
import { reactive, ref, watch } from 'vue';
import Form from '../ui/Form.vue';
import Select from '../ui/Select.vue';
import Textarea from '../ui/Textarea.vue';
import Button from '../ui/Button.vue';
import Badge from '../ui/Badge.vue';
import { ApiError } from '../../lib/api.js';
import { closeModal } from '../../lib/modal.js';
import { notifySuccess, notifyApiError } from '../../lib/notify.js';
import { statusOptions } from '../../data/orders.js';
import { useOrdersStore } from '../../stores/orders.js';

const props = defineProps({
    order: { type: Object, required: true },
});

const ordersStore = useOrdersStore();

const form = reactive({
    status: '',
    reason: '',
});

const errors = reactive({
    status: null,
    reason: null,
});

const saving = ref(false);

function resetForm() {
    form.status = props.order?.status ?? '';
    form.reason = '';
    errors.status = null;
    errors.reason = null;
}

watch(
    () => props.order?.uuid,
    () => resetForm(),
    { immediate: true },
);

watch(
    () => props.order?.status,
    (status) => {
        if (!saving.value && status) {
            form.status = status;
        }
    },
);

async function submit() {
    saving.value = true;
    errors.status = null;
    errors.reason = null;

    try {
        await ordersStore.updateStatus(props.order.uuid, {
            status: form.status,
            reason: form.reason.trim() || null,
        });

        notifySuccess('تم تحديث حالة الطلب بنجاح.');
        closeModal('change-order-status');
        resetForm();
    } catch (error) {
        if (error instanceof ApiError) {
            errors.status = error.errors?.status?.[0] ?? error.errors?.newStatus?.[0] ?? null;
            errors.reason = error.errors?.reason?.[0] ?? error.errors?.statusReason?.[0] ?? null;
        }

        notifyApiError(error, 'تعذر تحديث حالة الطلب.');
    } finally {
        saving.value = false;
    }
}
</script>

<template>
    <Form class="!gap-4 !p-5 !py-6" @submit="submit">
        <div class="rounded-xl bg-gray-50 px-4 py-3">
            <p class="text-xs text-gray-400">الحالة الحالية</p>
            <div class="mt-1">
                <Badge :color="order.status_color">{{ order.status_label }}</Badge>
            </div>
        </div>

        <Select
            v-model="form.status"
            name="newStatus"
            label="الحالة الجديدة"
            :options="statusOptions"
            :error="errors.status"
        />

        <Textarea
            v-model="form.reason"
            name="statusReason"
            label="سبب التغيير"
            placeholder="اكتب سبب تغيير الحالة (اختياري)..."
            :rows="4"
            :error="errors.reason"
        />

        <div class="flex items-center justify-end gap-2 border-t border-gray-100 pt-4">
            <Button type="button" variant="ghost" label="إلغاء" @click="closeModal('change-order-status')" />
            <Button type="submit" label="حفظ التغيير" :loading="saving" />
        </div>
    </Form>
</template>
