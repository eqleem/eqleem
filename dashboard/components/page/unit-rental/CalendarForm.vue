<script setup>
import { computed, onMounted, reactive, ref } from 'vue';
import Form from '../../ui/Form.vue';
import Input from '../../ui/Input.vue';
import Button from '../../ui/Button.vue';
import Toggle from '../../ui/Toggle.vue';
import Alert from '../../ui/Alert.vue';
import { useUnitRentalStore } from '../../../stores/unit-rental.js';
import { ApiError } from '../../../lib/api.js';
import { closeModal } from '../../../lib/modal.js';
import { notifySuccess, notifyApiError } from '../../../lib/notify.js';

const props = defineProps({
    calendarId: { type: [Number, String], default: null },
    modalName: { type: String, required: true },
});

const store = useUnitRentalStore();
const loading = ref(false);
const submitting = ref(false);
const errors = reactive({ name: null, from: null, to: null });

const weekdayKeys = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];

const fallbackWeekdayLabels = {
    sunday: 'الأحد',
    monday: 'الإثنين',
    tuesday: 'الثلاثاء',
    wednesday: 'الأربعاء',
    thursday: 'الخميس',
    friday: 'الجمعة',
    saturday: 'السبت',
};

const weekdayLabels = ref({ ...fallbackWeekdayLabels });

const form = reactive({
    name: '',
    fromDate: '',
    toDate: '',
    useBranchHours: false,
    availabilities: defaultAvailabilities(),
});

const isEdit = computed(() => props.calendarId != null);

function defaultAvailabilities() {
    return Object.fromEntries(
        weekdayKeys.map((day) => [
            day,
            {
                enabled: ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday'].includes(day),
                start: '08:00',
                end: '17:00',
            },
        ]),
    );
}

function resetForm() {
    errors.name = null;
    errors.from = null;
    errors.to = null;
    form.name = '';
    form.fromDate = '';
    form.toDate = '';
    form.useBranchHours = false;
    form.availabilities = defaultAvailabilities();
    weekdayLabels.value = { ...fallbackWeekdayLabels };
}

function applyCalendar(calendar) {
    form.name = calendar.name ?? '';
    form.fromDate = calendar.from ?? '';
    form.toDate = calendar.to ?? '';
    form.useBranchHours = Boolean(calendar.use_branch_hours);
    form.availabilities = calendar.availabilities ?? defaultAvailabilities();

    if (calendar.weekday_labels) {
        weekdayLabels.value = calendar.weekday_labels;
    }
}

async function load() {
    resetForm();

    if (!isEdit.value) {
        return;
    }

    loading.value = true;

    try {
        const calendar = await store.fetchCalendar(props.calendarId);

        if (calendar) {
            applyCalendar(calendar);
        }
    } catch (error) {
        errors.name = error instanceof ApiError ? error.message : 'تعذر تحميل وحدة المخزون.';
    } finally {
        loading.value = false;
    }
}

onMounted(load);

async function submit() {
    const name = form.name.trim();

    if (!name) {
        errors.name = 'اسم الوحدة مطلوب.';
        return;
    }

    errors.name = null;
    errors.from = null;
    errors.to = null;
    submitting.value = true;

    const payload = {
        name,
        use_branch_hours: Boolean(form.useBranchHours),
        from: form.useBranchHours ? null : (form.fromDate || null),
        to: form.useBranchHours ? null : (form.toDate || null),
        availabilities: form.useBranchHours ? null : form.availabilities,
    };

    try {
        if (isEdit.value) {
            await store.updateCalendar(props.calendarId, payload);
        } else {
            await store.createCalendar(payload);
        }

        notifySuccess('Saved');
        closeModal(props.modalName);
    } catch (error) {
        if (error instanceof ApiError) {
            errors.name = error.errors?.name?.[0] ?? null;
            errors.from = error.errors?.from?.[0] ?? null;
            errors.to = error.errors?.to?.[0] ?? null;

            if (!errors.name && !errors.from && !errors.to) {
                errors.name = error.message;
            }
        } else {
            errors.name = 'تعذر حفظ وحدة المخزون.';
        }
        notifyApiError(error, 'تعذر حفظ الأصل.');
    } finally {
        submitting.value = false;
    }
}
</script>

<template>
    <div v-if="loading" class="flex items-center justify-center p-10"><LoadingSpinner size="lg" /></div>

    <Form v-else class="!rounded-none" @submit="submit">
        <div class="mb-4">
            <Alert
                color="blue"
                heading="وحدة المخزون"
                text="كل وحدة في المخزون تمثل وحدة فعلية قابلة للحجز — مثل غرفة ١٠١ أو قاعة اجتماعات ٢. يمكن ربطها بأنواع الوحدات لتحديد أوقات الحجز."
            />
        </div>

        <Input
            v-model="form.name"
            name="name"
            label="اسم الوحدة"
            placeholder="مثال: غرفة ١٠١"
            :error="errors.name"
        />

        <Toggle
            v-model="form.useBranchHours"
            name="useBranchHours"
            label="ساعات عمل الفرع"
            info="عند التفعيل، ستُستخدم ساعات عمل الفرع بدلاً من أوقات مخصصة لهذه الوحدة."
        />

        <template v-if="!form.useBranchHours">
            <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                <Input
                    v-model="form.fromDate"
                    name="fromDate"
                    label="من تاريخ"
                    type="date"
                    dir="ltr"
                    :error="errors.from"
                />
                <Input
                    v-model="form.toDate"
                    name="toDate"
                    label="إلى تاريخ"
                    type="date"
                    dir="ltr"
                    :error="errors.to"
                />
            </div>

            <p class="mb-3 text-xs text-gray-500">اختر الفترة الزمنية المتاحة لحجز هذه الوحدة.</p>

            <div class="overflow-hidden rounded-xl border border-gray-200">
                <div class="bg-gray-50 px-4 py-2 text-sm font-medium text-gray-700">الأوقات المتاحة</div>

                <div class="divide-y divide-gray-100">
                    <div
                        v-for="day in weekdayKeys"
                        :key="day"
                        class="flex flex-wrap items-center gap-3 px-4 py-3"
                    >
                        <label class="flex w-28 shrink-0 items-center gap-2">
                            <input
                                v-model="form.availabilities[day].enabled"
                                type="checkbox"
                                class="rounded border-gray-300"
                            >
                            <span class="text-sm text-gray-700">{{ weekdayLabels[day] }}</span>
                        </label>

                        <div class="flex min-w-[220px] flex-1 items-center gap-2">
                            <input
                                v-model="form.availabilities[day].start"
                                type="time"
                                :disabled="!form.availabilities[day].enabled"
                                class="rounded-lg border border-gray-200 px-2 py-1.5 text-sm text-gray-700 disabled:bg-gray-100 disabled:text-gray-400"
                            >
                            <span class="text-sm text-gray-400">إلى</span>
                            <input
                                v-model="form.availabilities[day].end"
                                type="time"
                                :disabled="!form.availabilities[day].enabled"
                                class="rounded-lg border border-gray-200 px-2 py-1.5 text-sm text-gray-700 disabled:bg-gray-100 disabled:text-gray-400"
                            >
                        </div>
                    </div>
                </div>
            </div>
        </template>

        <template #footer>
            <Button type="submit" label="حفظ" :disabled="submitting || store.saving" />
        </template>
    </Form>
</template>
