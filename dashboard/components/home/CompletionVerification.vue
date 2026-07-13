<script setup>
import { computed, onMounted, reactive, ref } from 'vue';
import Form from '../ui/Form.vue';
import Input from '../ui/Input.vue';
import Select from '../ui/Select.vue';
import Radio from '../ui/Radio.vue';
import Alert from '../ui/Alert.vue';
import Button from '../ui/Button.vue';
import { identityTypes as fallbackTypes, verificationCountries as fallbackCountries } from '../../data/settings.js';
import { api, ApiError } from '../../lib/api.js';
import { useWelcomeStore } from '../../stores/welcome.js';
import { notifyApiSuccess, notifyApiError } from '../../lib/notify.js';

const props = defineProps({
    skipWelcomeFlow: { type: Boolean, default: false },
});

const emit = defineEmits(['saved']);

const store = useWelcomeStore();

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
    errors.file = null;
}

async function submit() {
    saving.value = true;
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

        notifyApiSuccess({ message: 'تم إرسال طلب التوثيق.' }, 'تم إرسال طلب التوثيق.');
        emit('saved');

        if (!props.skipWelcomeFlow) {
            await store.afterStepSaved('home-step-verification');
        }
    } catch (error) {
        if (error instanceof ApiError) {
            errors.identity_type = error.errors?.identity_type?.[0] ?? null;
            errors.identity_number = error.errors?.identity_number?.[0] ?? null;
            errors.country = error.errors?.country?.[0] ?? null;
            errors.file = error.errors?.file?.[0] ?? null;
        } else {
        }
        notifyApiError(error, 'تعذر حفظ طلب التوثيق.');
    } finally {
        saving.value = false;
    }
}

onMounted(load);
</script>

<template>
    <div class="space-y-3 p-4 text-stone-800" dir="rtl">
        <div v-if="loading" class="py-4 flex items-center justify-center"><LoadingSpinner size="sm" /></div>

        <template v-else>
            <Alert
                v-if="!form.is_confirmed && form.confirm_status !== 'pending'"
                color="blue"
                heading="توثيق المتجر"
                text="يجب عليك توثيق المتجر لتتمكن من استقبال المدفوعات من عملائك."
            />
            <Alert
                v-if="form.is_confirmed"
                color="green"
                heading="توثيق المتجر"
                text="تم توثيق المتجر بنجاح."
            />
            <Alert
                v-if="form.confirm_status === 'pending'"
                color="yellow"
                heading="توثيق المتجر"
                text="تم إرسال طلب توثيق المتجر، سيتم توثيقه خلال يوم عمل على الأكثر."
            />

            <Form class="!p-0" form-class="!space-y-2" @submit="submit">
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

                <div class="relative items-start gap-x-2 rounded-md bg-stone-100/75 p-1 lg:flex">
                    <span class="inline-block w-36 shrink-0 p-2 text-sm font-semibold text-stone-500">ملف الهوية</span>
                    <div class="space-y-2 p-2">
                        <p class="text-xs text-stone-400">الرجاء إرفاق صورة الهوية أو صورة السجل التجاري حسب نوع الكيان.</p>
                        <a
                            v-if="form.current_file_url"
                            :href="form.current_file_url"
                            target="_blank"
                            class="block truncate rounded-md bg-white p-2 text-sm text-stone-500 hover:bg-primary-50"
                        >
                            عرض الملف الحالي
                        </a>
                        <input
                            ref="fileInput"
                            type="file"
                            accept="image/*"
                            class="block w-full text-sm text-stone-600"
                            @change="onFileChange"
                        >
                        <p v-if="errors.file" class="text-xs text-red-500">{{ errors.file }}</p>
                    </div>
                </div>


                <template #footer>
                    <Button type="submit" label="حفظ" :loading="saving" />
                </template>
            </Form>
        </template>
    </div>
</template>
