<script setup>
import { computed, reactive, ref, watch } from 'vue';
import Input from '../../ui/Input.vue';
import Select from '../../ui/Select.vue';
import Button from '../../ui/Button.vue';
import Icon from '../../ui/Icon.vue';
import Switch from '../../settings/Switch.vue';
import { usePageStructureStore } from '../../../stores/pageStructure.js';
import { ApiError } from '../../../lib/api.js';
import { notifyApiError, notifySuccess } from '../../../lib/notify.js';

const props = defineProps({
    blockId: { type: Number, required: true },
    editor: { type: Object, required: true },
});

const emit = defineEmits(['updated']);

const store = usePageStructureStore();

const form = reactive({
    position: props.editor.position ?? 'bottom-end',
    show_whatsapp: Boolean(props.editor.show_whatsapp),
    whatsapp_number: props.editor.whatsapp_number ?? '',
    show_phone: Boolean(props.editor.show_phone),
    phone_number: props.editor.phone_number ?? '',
});

const busyKey = ref(null);
const editModal = ref(false);
const editingKey = ref(null);
const editValue = ref('');
const editError = ref(null);
const editSaving = ref(false);

watch(() => props.editor, (value) => {
    form.position = value.position ?? 'bottom-end';
    form.show_whatsapp = Boolean(value.show_whatsapp);
    form.whatsapp_number = value.whatsapp_number ?? '';
    form.show_phone = Boolean(value.show_phone);
    form.phone_number = value.phone_number ?? '';
}, { deep: true });

const positionLabel = computed(() => {
    const options = props.editor.position_options ?? {};

    return options[form.position] ?? form.position;
});

const items = computed(() => [
    {
        key: 'whatsapp',
        label: 'واتساب',
        summary: form.whatsapp_number || 'أضف رقم واتساب',
        icon: 'brand-whatsapp',
        active: form.show_whatsapp,
        editable: true,
    },
    {
        key: 'phone',
        label: 'اتصال',
        summary: form.phone_number || 'أضف رقم الاتصال',
        icon: 'phone',
        active: form.show_phone,
        editable: true,
    },
]);

async function persist(next = {}) {
    const body = {
        position: form.position,
        show_whatsapp: form.show_whatsapp,
        whatsapp_number: form.whatsapp_number,
        show_phone: form.show_phone,
        phone_number: form.phone_number,
        ...next,
    };

    Object.assign(form, next);

    try {
        const payload = await store.updateBlock(props.blockId, body);
        store.clearEditing();
        const editor = payload?.editor ?? { ...props.editor, ...body };
        emit('updated', { editor });

        return editor;
    } catch (error) {
        Object.assign(form, {
            position: props.editor.position ?? 'bottom-end',
            show_whatsapp: Boolean(props.editor.show_whatsapp),
            whatsapp_number: props.editor.whatsapp_number ?? '',
            show_phone: Boolean(props.editor.show_phone),
            phone_number: props.editor.phone_number ?? '',
        });
        notifyApiError(error, 'تعذر الحفظ.');
        throw error;
    }
}

async function toggleItem(item, active) {
    busyKey.value = item.key;

    const patch = item.key === 'whatsapp'
        ? { show_whatsapp: active }
        : { show_phone: active };

    try {
        await persist(patch);
    } catch {
        // rolled back in persist
    } finally {
        busyKey.value = null;
    }
}

function openEdit(item) {
    if (!item.editable) {
        return;
    }

    editingKey.value = item.key;
    editValue.value = item.key === 'whatsapp' ? form.whatsapp_number : form.phone_number;
    editError.value = null;
    editModal.value = true;
}

function openPosition() {
    editingKey.value = 'position';
    editValue.value = form.position;
    editError.value = null;
    editModal.value = true;
}

defineExpose({ openPosition });

async function saveEdit() {
    editError.value = null;
    editSaving.value = true;

    try {
        if (editingKey.value === 'position') {
            await persist({ position: editValue.value });
        } else if (editingKey.value === 'whatsapp') {
            await persist({
                whatsapp_number: editValue.value,
                show_whatsapp: true,
            });
        } else if (editingKey.value === 'phone') {
            await persist({
                phone_number: editValue.value,
                show_phone: true,
            });
        }

        notifySuccess('Saved');
        editModal.value = false;
    } catch (error) {
        editError.value = error instanceof ApiError ? error.message : 'تعذر الحفظ.';
    } finally {
        editSaving.value = false;
    }
}
</script>

<template>
    <div class="relative min-h-20">
        <ul class="space-y-1.5 p-2">
            <li
                v-for="item in items"
                :key="item.key"
                class="group flex items-center gap-2 rounded-lg border border-transparent bg-white px-2 py-2 transition hover:border-stone-200"
                :class="{ 'opacity-50': !item.active }"
            >
                <div class="rounded-md bg-stone-100 p-1.5 text-stone-500">
                    <Icon :name="item.icon" class="h-4 w-4" />
                </div>

                <button
                    type="button"
                    class="flex min-w-0 flex-1 cursor-pointer flex-col items-start text-start transition hover:text-primary-600"
                    :disabled="!item.editable"
                    :class="{ 'cursor-default hover:text-inherit': !item.editable }"
                    @click="openEdit(item)"
                >
                    <span class="truncate text-sm font-medium text-stone-800">{{ item.label }}</span>
                    <span class="truncate text-xs text-stone-400" dir="ltr">{{ item.summary }}</span>
                </button>

                <Switch
                    :model-value="item.active"
                    :label="item.active ? `تعطيل ${item.label}` : `تفعيل ${item.label}`"
                    :disabled="busyKey === item.key"
                    @update:model-value="toggleItem(item, $event)"
                />
            </li>
        </ul>
    </div>

    <div v-if="editModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-stone-800/75" @click="editModal = false"></div>
        <div class="relative max-h-[90vh] w-full max-w-lg overflow-y-auto rounded-xl bg-white shadow-xl">
            <div class="sticky top-0 z-10 flex items-center justify-between border-b border-stone-100 bg-white p-3 px-4">
                <p class="text-sm font-semibold text-stone-600">
                    <template v-if="editingKey === 'position'">موضع الأزرار</template>
                    <template v-else-if="editingKey === 'whatsapp'">رقم واتساب</template>
                    <template v-else>رقم الاتصال</template>
                </p>
                <button type="button" class="cursor-pointer rounded-md bg-stone-100 p-1 text-stone-400" @click="editModal = false">
                    <Icon name="x" class="h-4 w-4" />
                </button>
            </div>
            <div class="space-y-3 p-4">
                <Select
                    v-if="editingKey === 'position'"
                    v-model="editValue"
                    name="position"
                    label="موضع الأزرار"
                    :options="editor.position_options ?? {}"
                />
                <Input
                    v-else-if="editingKey === 'whatsapp'"
                    v-model="editValue"
                    name="whatsapp_number"
                    label="رقم واتساب"
                    placeholder="966500000000"
                    dir="ltr"
                />
                <Input
                    v-else
                    v-model="editValue"
                    name="phone_number"
                    label="رقم الاتصال"
                    placeholder="+966500000000"
                    dir="ltr"
                />
                <p v-if="editingKey === 'position'" class="text-xs text-stone-400">الموضع الحالي: {{ positionLabel }}</p>
                <p v-if="editError" class="text-sm text-red-500">{{ editError }}</p>
            </div>
            <div class="sticky bottom-0 flex justify-end gap-2 border-t border-stone-100 bg-white p-3 px-4">
                <Button type="button" variant="ghost" label="إلغاء" @click="editModal = false" />
                <Button type="button" label="حفظ" :loading="editSaving" @click="saveEdit" />
            </div>
        </div>
    </div>
</template>
