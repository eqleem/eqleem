<script setup>
import { ref } from 'vue';
import SettingsShell from '../../components/settings/SettingsShell.vue';
import MainBox from '../../components/ui/MainBox.vue';
import Modal from '../../components/ui/Modal.vue';
import Form from '../../components/ui/Form.vue';
import Input from '../../components/ui/Input.vue';
import Button from '../../components/ui/Button.vue';
import Switch from '../../components/settings/Switch.vue';
import { paymentMethods as seed } from '../../data/settings.js';
import { openModal, closeModal } from '../../lib/modal.js';

// Port of resources/views/admin/settings/payment-options/payment-options.blade.php (dummy data).
const methods = ref(seed.map((item) => ({ ...item })));
const activeSlug = ref(null);
const modalForm = ref({ label: '', description: '' });

function openMethod(slug) {
    activeSlug.value = slug;
    modalForm.value = { label: '', description: '' };
    openModal(`payment-method-${slug}`);
}

function toggleActive(method) {
    method.active = !method.active;
}

function saveMethod() {
    closeModal(`payment-method-${activeSlug.value}`);
}
</script>

<template>
    <SettingsShell title="وسائل الدفع">
        <MainBox title="وسائل الدفع" subtitle="قم بتفعيل وتخصيص وسائل الدفع المناسبة لجمهورك.">
            <template #icon>
                <img :src="`/assets/icons/business/017-atm-card.svg`" alt="" class="h-6 w-6">
            </template>

            <div class="divide-y divide-dotted divide-gray-200 border-t border-dotted border-gray-200">
                <div
                    v-for="method in methods"
                    :key="method.slug"
                    class="group flex items-center gap-4 px-4 py-4 transition hover:bg-gray-50/80"
                >
                    <button
                        type="button"
                        class="flex min-w-0 flex-1 items-center gap-4 text-start"
                        @click="openMethod(method.slug)"
                    >
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-semibold text-gray-800">{{ method.name }}</p>
                            <p class="mt-0.5 line-clamp-2 text-xs text-gray-500">{{ method.description }}</p>
                        </div>
                        <div class="shrink-0 rounded-lg border border-gray-100 bg-white p-2">
                            <img :src="`/${method.icon}`" :alt="method.name" class="h-8 w-auto max-w-[72px] object-contain">
                        </div>
                    </button>

                    <Switch
                        :model-value="method.active"
                        :label="method.active ? `تعطيل ${method.name}` : `تفعيل ${method.name}`"
                        @update:model-value="toggleActive(method)"
                    />
                </div>
            </div>
        </MainBox>

        <Modal
            v-for="method in methods"
            :key="`modal-${method.slug}`"
            :title="method.name"
            size="3xl"
            :name="`payment-method-${method.slug}`"
        >
            <Form class="!rounded-none" @submit="saveMethod">
                <Input v-model="modalForm.label" name="label" label="العنوان الظاهر" :placeholder="method.name" />
                <Input v-model="modalForm.description" name="description" label="وصف مختصر" placeholder="اختياري" />
                <p class="px-1 text-xs text-gray-400">إعدادات تفصيلية لهذه الوسيلة ستُربط لاحقاً عبر API.</p>
                <template #footer>
                    <div class="flex gap-2">
                        <Button type="button" variant="ghost" label="إلغاء" @click="closeModal(`payment-method-${method.slug}`)" />
                        <Button type="submit" label="حفظ" />
                    </div>
                </template>
            </Form>
        </Modal>
    </SettingsShell>
</template>
