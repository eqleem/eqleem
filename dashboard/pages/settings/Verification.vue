<script setup>
import { computed, onMounted, reactive, ref } from 'vue';
import SettingsShell from '../../components/settings/SettingsShell.vue';
import MainBox from '../../components/ui/MainBox.vue';
import Form from '../../components/ui/Form.vue';
import Input from '../../components/ui/Input.vue';
import Select from '../../components/ui/Select.vue';
import Radio from '../../components/ui/Radio.vue';
import Alert from '../../components/ui/Alert.vue';
import Button from '../../components/ui/Button.vue';
import { identityTypes as fallbackTypes, verificationCountries as fallbackCountries } from '../../data/settings.js';
import { api, ApiError } from '../../lib/api.js';

const form = reactive({
    identity_type: 'individual',
    identity_number: '',
    country: 'SA',
    current_file: null,
    current_file_url: null,
    is_confirmed: false,
    confirm_status: null,
});

const identityTypes = ref({ ...fallbackTypes });
const verificationCountries = ref({ ...fallbackCountries });
const fileInput = ref(null);
const selectedFile = ref(null);

const loading = ref(true);
const saving = ref(false);
const saved = ref(false);
const message = ref(null);
const errors = reactive({
    identity_type: null,
    identity_number: null,
    country: null,
    file: null,
});

const identityLabel = computed(() => (
    form.identity_type === 'individual' ? 'رقم الهوية' : 'رقم السجل التجاري'
));

function applyPayload(payload) {
    const data = payload?.data ?? payload;

    form.identity_type = data.identity_type ?? 'individual';
    form.identity_number = data.identity_number ?? '';
    form.country = data.country ?? 'SA';
    form.current_file = data.identity_file ?? null;
    form.current_file_url = data.identity_file_url ?? null;
    form.is_confirmed = Boolean(data.is_confirmed);
    form.confirm_status = data.confirm_status ?? null;

    if (data.types) {
        identityTypes.value = data.types;
    }

    if (data.countries) {
        verificationCountries.value = data.countries;
    }
}

async function load() {
    loading.value = true;
    message.value = null;

    try {
        applyPayload(await api('/settings/verification'));
    } catch (error) {
        message.value = error instanceof ApiError ? error.message : 'تعذر تحميل بيانات التوثيق.';
    } finally {
        loading.value = false;
    }
}

function onFileChange(event) {
    selectedFile.value = event.target.files?.[0] ?? null;
}

async function submit() {
    saving.value = true;
    saved.value = false;
    message.value = null;
    errors.identity_type = null;
    errors.identity_number = null;
    errors.country = null;
    errors.file = null;

    const body = new FormData();
    body.append('identity_type', form.identity_type);
    body.append('identity_number', form.identity_number);
    body.append('country', form.country);

    if (selectedFile.value) {
        body.append('file', selectedFile.value);
    }

    try {
        applyPayload(await api('/settings/verification', { method: 'POST', body }));
        selectedFile.value = null;
        if (fileInput.value) {
            fileInput.value.value = '';
        }
        saved.value = true;
        setTimeout(() => {
            saved.value = false;
        }, 2000);
    } catch (error) {
        if (error instanceof ApiError) {
            errors.identity_type = error.errors?.identity_type?.[0] ?? null;
            errors.identity_number = error.errors?.identity_number?.[0] ?? null;
            errors.country = error.errors?.country?.[0] ?? null;
            errors.file = error.errors?.file?.[0] ?? null;
            message.value = error.message;
        } else {
            message.value = 'تعذر حفظ طلب التوثيق.';
        }
    } finally {
        saving.value = false;
    }
}

onMounted(load);
</script>

<template>
    <SettingsShell title="توثيق الحساب">
        <MainBox title="توثيق المتجر" subtitle="بيانات توثيق المتجر بالمستندات الرسمية.">
            <p v-if="loading" class="px-4 py-6 text-sm text-gray-400">جاري التحميل...</p>
            <p v-else-if="message && !saved" class="px-4 pt-3 text-sm text-red-500">{{ message }}</p>

            <template v-if="!loading">
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
                        :error="errors.identity_number"
                    />
                    <Select
                        v-model="form.country"
                        name="country"
                        label="الدولة"
                        :options="verificationCountries"
                        :error="errors.country"
                    />

                    <div class="relative items-start gap-x-2 rounded-md bg-gray-100/75 p-1 lg:flex">
                        <span class="inline-block w-36 shrink-0 p-2 text-sm font-semibold text-gray-500">ملف الهوية</span>
                        <div class="space-y-2 p-2">
                            <p class="text-xs text-gray-400">الرجاء إرفاق صورة الهوية أو صورة السجل التجاري حسب نوع الكيان.</p>
                            <a
                                v-if="form.current_file_url"
                                :href="form.current_file_url"
                                target="_blank"
                                class="block truncate rounded-md bg-white p-2 text-sm text-gray-500 hover:bg-primary-50"
                            >
                                عرض الملف الحالي
                            </a>
                            <input
                                ref="fileInput"
                                type="file"
                                accept="image/*"
                                class="block w-full text-sm text-gray-600"
                                @change="onFileChange"
                            >
                            <p v-if="errors.file" class="text-xs text-red-500">{{ errors.file }}</p>
                        </div>
                    </div>

                    <template #footer>
                        <div class="flex items-center gap-3">
                            <span v-if="saved" class="text-sm text-emerald-600">تم إرسال طلب التوثيق.</span>
                            <Button type="submit" label="حفظ" :disabled="saving" />
                        </div>
                    </template>
                </Form>
            </template>
        </MainBox>
    </SettingsShell>
</template>
