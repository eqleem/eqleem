<script setup>
import { computed, reactive, ref } from 'vue';
import SettingsShell from '../../components/settings/SettingsShell.vue';
import MainBox from '../../components/ui/MainBox.vue';
import Form from '../../components/ui/Form.vue';
import Input from '../../components/ui/Input.vue';
import Select from '../../components/ui/Select.vue';
import Radio from '../../components/ui/Radio.vue';
import Alert from '../../components/ui/Alert.vue';
import Button from '../../components/ui/Button.vue';
import { identityTypes, verificationCountries } from '../../data/settings.js';

// Port of resources/views/admin/settings/info/verification.blade.php (dummy data).
const form = reactive({
    identity_type: 'individual',
    identity_number: '',
    country: 'SA',
    current_file: null,
    is_confirmed: false,
    confirm_status: null,
});

const saved = ref(false);

const identityLabel = computed(() => (
    form.identity_type === 'individual' ? 'رقم الهوية' : 'رقم السجل التجاري'
));

function submit() {
    form.confirm_status = 'pending';
    form.is_confirmed = false;
    saved.value = true;
    setTimeout(() => {
        saved.value = false;
    }, 2000);
}
</script>

<template>
    <SettingsShell title="توثيق الحساب">
        <MainBox title="توثيق المتجر" subtitle="بيانات توثيق المتجر بالمستندات الرسمية.">
            <div v-if="!form.is_confirmed && form.confirm_status !== 'pending'" class="mt-3 px-4">
                <Alert color="blue" heading="توثيق المتجر" text="يجب عليك توثيق المتجر لتتمكن من استقبال المدفوعات من عملائك." />
            </div>
            <div v-if="form.is_confirmed" class="mt-3 px-4">
                <Alert color="green" heading="توثيق المتجر" text="تم توثيق المتجر بنجاح." />
            </div>
            <div v-if="form.confirm_status === 'pending'" class="mt-3 px-4">
                <Alert color="yellow" heading="توثيق المتجر" text="تم إرسال طلب توثيق المتجر، سيتم توثيقه خلال يوم عمل على الأكثر." />
            </div>

            <Form @submit="submit">
                <Radio v-model="form.identity_type" name="identity_type" label="نوع الهوية" :options="identityTypes" />
                <Input
                    v-model="form.identity_number"
                    name="identity_number"
                    :label="identityLabel"
                    placeholder="1234567890"
                />
                <Select v-model="form.country" name="country" label="الدولة" :options="verificationCountries" />

                <div class="relative items-start gap-x-2 rounded-md bg-gray-100/75 p-1 lg:flex">
                    <span class="inline-block w-36 shrink-0 p-2 text-sm font-semibold text-gray-500">ملف الهوية</span>
                    <div class="space-y-2 p-2">
                        <p class="text-xs text-gray-400">الرجاء إرفاق صورة الهوية أو صورة السجل التجاري حسب نوع الكيان.</p>
                        <p class="text-xs text-gray-400">رفع الملف (قريباً)</p>
                    </div>
                </div>

                <template #footer>
                    <div class="flex items-center gap-3">
                        <span v-if="saved" class="text-sm text-emerald-600">تم إرسال طلب التوثيق.</span>
                        <Button type="submit" label="حفظ" />
                    </div>
                </template>
            </Form>
        </MainBox>
    </SettingsShell>
</template>
