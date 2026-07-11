<script setup>
import { reactive, ref, watch } from 'vue';
import Form from '../ui/Form.vue';
import Input from '../ui/Input.vue';
import Select from '../ui/Select.vue';
import Textarea from '../ui/Textarea.vue';
import Button from '../ui/Button.vue';
import { ApiError } from '../../lib/api.js';
import { closeModal } from '../../lib/modal.js';
import { paymentMethodOptions } from '../../data/orders.js';
import { useOrdersStore } from '../../stores/orders.js';

const props = defineProps({
    order: { type: Object, required: true },
});

const ordersStore = useOrdersStore();

const form = reactive({
    amount: '',
    method: 'cash',
    notes: '',
});

const errors = reactive({
    amount: null,
    method: null,
    notes: null,
});

const message = ref(null);
const saving = ref(false);

const dueMajor = () => Math.max(0, (Number(props.order?.due_total) || 0) / 100);

function resetForm() {
    form.amount = dueMajor() > 0 ? String(dueMajor()) : '';
    form.method = 'cash';
    form.notes = '';
    errors.amount = null;
    errors.method = null;
    errors.notes = null;
    message.value = null;
}

watch(
    () => props.order?.uuid,
    () => resetForm(),
    { immediate: true },
);

watch(
    () => props.order?.due_total,
    () => {
        if (!saving.value) {
            form.amount = dueMajor() > 0 ? String(dueMajor()) : '';
        }
    },
);

async function submit() {
    saving.value = true;
    message.value = null;
    errors.amount = null;
    errors.method = null;
    errors.notes = null;

    try {
        await ordersStore.recordPayment(props.order.uuid, {
            amount: Number(form.amount),
            method: form.method,
            notes: form.notes.trim() || null,
        });

        closeModal('add-order-payment');
        resetForm();
    } catch (error) {
        if (error instanceof ApiError) {
            errors.amount = error.errors?.amount?.[0] ?? error.errors?.paymentAmount?.[0] ?? null;
            errors.method = error.errors?.method?.[0] ?? error.errors?.paymentMethod?.[0] ?? null;
            errors.notes = error.errors?.notes?.[0] ?? error.errors?.paymentNotes?.[0] ?? null;
            message.value = error.message;
        } else {
            message.value = 'تعذر تسجيل الدفعة.';
        }
    } finally {
        saving.value = false;
    }
}
</script>

<template>
    <Form class="!gap-4 !p-5 !py-6" @submit="submit">
        <div class="rounded-xl bg-gray-50 px-4 py-3">
            <p class="text-xs text-gray-400">المبلغ المتبقي</p>
            <p class="mt-1 text-lg font-bold text-amber-700">{{ order.due_total_formatted }}</p>
        </div>

        <p v-if="message" class="rounded-lg bg-red-50 px-3 py-2 text-sm text-red-600">{{ message }}</p>

        <Input
            v-model="form.amount"
            name="paymentAmount"
            label="المبلغ"
            type="number"
            step="0.01"
            min="0.01"
            :max="dueMajor()"
            placeholder="0.00"
            dir="ltr"
            :error="errors.amount"
        />

        <Select
            v-model="form.method"
            name="paymentMethod"
            label="طريقة الدفع"
            :options="paymentMethodOptions"
            :error="errors.method"
        />

        <Textarea
            v-model="form.notes"
            name="paymentNotes"
            label="ملاحظات"
            placeholder="ملاحظات اختيارية..."
            :rows="3"
            :error="errors.notes"
        />

        <div class="flex items-center justify-end gap-2 border-t border-gray-100 pt-4">
            <Button type="button" variant="ghost" label="إلغاء" @click="closeModal('add-order-payment')" />
            <Button type="submit" label="تسجيل الدفعة" :loading="saving" />
        </div>
    </Form>
</template>
