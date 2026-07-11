<script setup>
import { computed, onMounted, reactive, ref, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import ManageLayout from '../../../components/page/ManageLayout.vue';
import Form from '../../../components/ui/Form.vue';
import Input from '../../../components/ui/Input.vue';
import Textarea from '../../../components/ui/Textarea.vue';
import Button from '../../../components/ui/Button.vue';
import Toggle from '../../../components/ui/Toggle.vue';
import Select from '../../../components/ui/Select.vue';
import NotFound from '../../NotFound.vue';
import { useFormsStore, fieldTypeHasOptions, makeField } from '../../../stores/forms.js';
import { ApiError } from '../../../lib/api.js';
import { notifySuccess, notifyApiError } from '../../../lib/notify.js';

const route = useRoute();
const router = useRouter();
const store = useFormsStore();
const formTab = ref('edit');
const notFound = ref(false);
const addFieldOpen = ref(false);
const fieldModal = ref(false);
const editingFieldId = ref(null);
const dragId = ref(null);

const form = reactive({
    title: '',
    description: '',
    slug: '',
    published: false,
    submitLabel: 'إرسال',
    successMessage: '',
    fields: [],
});

const errors = reactive({
    title: null,
    slug: null,
    fields: null,
    form: null,
});

const uuid = computed(() => String(route.params.id));
const slugPrefix = computed(() => store.detail?.slug_prefix ?? '/forms/');
const fieldTypeOptions = computed(() => store.detail?.field_type_options ?? store.fieldTypeOptions);

const editingFieldIndex = computed(() => {
    if (!editingFieldId.value) {
        return null;
    }

    return form.fields.findIndex((field) => field.id === editingFieldId.value);
});

const editingField = computed(() => {
    const index = editingFieldIndex.value;

    return index === null || index < 0 ? null : form.fields[index];
});

function switchTab(tab) {
    formTab.value = tab;
}

function slugify(value) {
    return String(value)
        .trim()
        .toLowerCase()
        .replace(/[^\w\u0600-\u06FF\s-]/g, '')
        .replace(/[\s-]+/g, '_')
        .replace(/^_+|_+$/g, '');
}

function fieldTypeLabel(type) {
    return fieldTypeOptions.value?.[type] ?? type;
}

function loadForm(item) {
    if (!item) {
        return;
    }

    form.title = item.title ?? '';
    form.description = item.description ?? '';
    form.slug = item.slug ?? '';
    form.published = Boolean(item.published);
    form.submitLabel = item.submit_label ?? 'إرسال';
    form.successMessage = item.success_message ?? '';
    form.fields = Array.isArray(item.fields)
        ? item.fields.map((field) => ({
            id: field.id,
            type: field.type,
            label: field.label ?? '',
            name: field.name ?? field.id,
            placeholder: field.placeholder ?? '',
            required: Boolean(field.required),
            info: field.info ?? '',
            options: Array.isArray(field.options) ? [...field.options] : [],
        }))
        : [];

    errors.title = null;
    errors.slug = null;
    errors.fields = null;
    errors.form = null;
}

onMounted(async () => {
    try {
        const item = await store.fetchForm(uuid.value);
        loadForm(item);
    } catch (error) {
        notFound.value = error instanceof ApiError && error.status === 404;
    }
});

watch(() => route.params.id, async (id) => {
    if (!id) {
        return;
    }

    notFound.value = false;
    formTab.value = 'edit';
    editingFieldId.value = null;
    fieldModal.value = false;

    try {
        const item = await store.fetchForm(String(id));
        loadForm(item);
    } catch (error) {
        notFound.value = error instanceof ApiError && error.status === 404;
    }
});

function addField(type) {
    const field = makeField(type);
    form.fields.push(field);
    editingFieldId.value = field.id;
    fieldModal.value = true;
    addFieldOpen.value = false;
}

function openFieldEditor(fieldId) {
    editingFieldId.value = fieldId;
    fieldModal.value = true;
}

function deleteField(fieldId) {
    if (!confirm('هل أنت متأكد من حذف هذا الحقل؟')) {
        return;
    }

    form.fields = form.fields.filter((field) => field.id !== fieldId);

    if (editingFieldId.value === fieldId) {
        editingFieldId.value = null;
        fieldModal.value = false;
    }
}

function onDragStart(id) {
    dragId.value = id;
}

function onDrop(targetId) {
    const sourceId = dragId.value;
    dragId.value = null;

    if (!sourceId || sourceId === targetId) {
        return;
    }

    const sourceIndex = form.fields.findIndex((field) => field.id === sourceId);
    const targetIndex = form.fields.findIndex((field) => field.id === targetId);

    if (sourceIndex < 0 || targetIndex < 0) {
        return;
    }

    const next = [...form.fields];
    const [moved] = next.splice(sourceIndex, 1);
    next.splice(targetIndex, 0, moved);
    form.fields = next;
}

function onFieldTypeChange(type) {
    const index = editingFieldIndex.value;

    if (index === null || index < 0) {
        return;
    }

    if (fieldTypeHasOptions(type) && form.fields[index].options.length === 0) {
        form.fields[index].options = ['', ''];
    }

    if (!fieldTypeHasOptions(type)) {
        form.fields[index].options = [];
    }
}

function onFieldLabelChange(label) {
    const index = editingFieldIndex.value;

    if (index === null || index < 0) {
        return;
    }

    const field = form.fields[index];

    if (field.name === field.id || !field.name) {
        const nextName = slugify(label);
        form.fields[index].name = nextName !== '' ? nextName : field.id;
    }
}

function onFieldNameChange(name) {
    const index = editingFieldIndex.value;

    if (index === null || index < 0) {
        return;
    }

    form.fields[index].name = String(name).toLowerCase().replace(/-/g, '_');
}

function addOption() {
    const index = editingFieldIndex.value;

    if (index === null || index < 0) {
        return;
    }

    form.fields[index].options.push('');
}

function removeOption(optionIndex) {
    const index = editingFieldIndex.value;

    if (index === null || index < 0) {
        return;
    }

    form.fields[index].options = form.fields[index].options.filter((_, i) => i !== optionIndex);
}

function closeFieldModal() {
    fieldModal.value = false;
}

async function persist({ close = false } = {}) {
    const title = form.title.trim();
    const slug = form.slug.trim();

    errors.title = title ? null : 'اسم النموذج مطلوب.';
    errors.slug = slug ? null : 'نص الرابط مطلوب.';
    errors.fields = null;
    errors.form = null;

    if (errors.title || errors.slug) {
        switchTab(errors.title ? 'edit' : 'advanced');
        return;
    }

    try {
        const item = await store.updateForm(uuid.value, {
            title,
            description: form.description.trim(),
            slug,
            published: Boolean(form.published),
            submit_label: form.submitLabel.trim() || 'إرسال',
            success_message: form.successMessage.trim(),
            fields: form.fields.map((field) => ({
                id: field.id,
                type: field.type,
                label: field.label,
                name: field.name,
                placeholder: field.placeholder,
                required: Boolean(field.required),
                info: field.info,
                options: field.options,
            })),
        });

        if (close) {
            router.push('/manage/forms');
            return;
        }

        loadForm(item);
        notifySuccess('Saved');
    } catch (error) {
        if (error instanceof ApiError) {
            errors.title = error.errors?.title?.[0] ?? null;
            errors.slug = error.errors?.slug?.[0] ?? null;
            errors.fields = error.errors?.fields?.[0] ?? null;
            errors.form = (!errors.title && !errors.slug && !errors.fields)
                ? (error.message || 'تعذر حفظ النموذج.')
                : null;

            if (errors.title || errors.fields) {
                switchTab('edit');
            } else if (errors.slug) {
                switchTab('advanced');
            }
        } else {
            errors.form = 'تعذر حفظ النموذج.';
        }

        notifyApiError(error, 'تعذر حفظ النموذج.');
    }
}

function save() {
    persist({ close: false });
}

function saveAndClose() {
    persist({ close: true });
}
</script>

<template>
    <ManageLayout v-if="store.detail && !notFound">
        <div class="overflow-hidden rounded-2xl bg-white">
            <div class="flex items-center justify-between gap-4 border-b border-stone-200 bg-stone-200/70 px-4 py-3">
                <div class="flex min-w-0 items-center gap-3">
                    <RouterLink
                        to="/manage/forms"
                        title="رجوع"
                        class="flex shrink-0 items-center justify-center rounded-lg bg-white p-2 shadow-sm hover:bg-gray-50"
                    >
                        <svg class="h-5 w-5 text-gray-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                    </RouterLink>
                    <div class="flex min-w-0 items-center gap-2 text-sm text-gray-700">
                        <img v-if="store.type?.icon" :src="`/${store.type.icon}`" class="h-5 w-5 shrink-0" alt="">
                        <span class="truncate font-semibold">{{ store.type?.name }}</span>
                        <span class="text-gray-400">/</span>
                        <span class="truncate text-gray-600">تحرير النموذج</span>
                    </div>
                </div>

                <nav class="flex shrink-0 items-center gap-1 rounded-xl bg-gray-300/40 p-0.5">
                    <button
                        type="button"
                        class="flex items-center gap-1.5 rounded-lg px-3 py-2 text-sm transition"
                        :class="formTab === 'edit' ? 'bg-white font-semibold text-gray-900 shadow-sm' : 'text-gray-600 hover:bg-white/60 hover:text-gray-800'"
                        @click.prevent.stop="switchTab('edit')"
                    >
                        تحرير
                    </button>
                    <button
                        type="button"
                        class="flex items-center gap-1.5 rounded-lg px-3 py-2 text-sm transition"
                        :class="formTab === 'advanced' ? 'bg-white font-semibold text-gray-900 shadow-sm' : 'text-gray-600 hover:bg-white/60 hover:text-gray-800'"
                        @click.prevent.stop="switchTab('advanced')"
                    >
                        متقدم
                    </button>
                </nav>
            </div>

            <Form class="!rounded-none !p-4 md:!p-6" @submit="save">
                <p v-if="errors.form" class="mb-3 text-sm text-red-600">{{ errors.form }}</p>
                <p v-if="errors.fields" class="mb-3 text-sm text-red-600">{{ errors.fields }}</p>

                <div v-show="formTab === 'edit'" class="space-y-4">
                    <Input
                        v-model="form.title"
                        name="title"
                        label="اسم النموذج"
                        placeholder="مثال: نموذج تواصل"
                        :error="errors.title"
                    />

                    <Textarea
                        v-model="form.description"
                        name="description"
                        label="وصف النموذج"
                        placeholder="وصف مختصر يظهر للمستخدم قبل تعبئة النموذج"
                        info="يُحفظ في data.description ويمكن استخدامه عند عرض النموذج في الموقع."
                    />

                    <div class="space-y-3">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div>
                                <p class="text-sm font-semibold text-gray-700">حقول النموذج</p>
                                <p class="mt-0.5 text-xs text-gray-400">رتّب الحقول بالسحب والإفلات.</p>
                            </div>

                            <div class="relative shrink-0">
                                <button
                                    type="button"
                                    class="inline-flex h-9 cursor-pointer items-center justify-center gap-2 whitespace-nowrap rounded-md bg-primary-600 px-4 py-2 text-sm text-white transition-all duration-300 hover:bg-primary-700"
                                    @click="addFieldOpen = !addFieldOpen"
                                >
                                    إضافة حقل
                                    <svg class="h-4 w-4 opacity-75" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" d="M6 9l6 6 6-6" /></svg>
                                </button>

                                <div
                                    v-if="addFieldOpen"
                                    class="absolute z-50 mt-2 min-w-48 rounded-lg border border-gray-100 bg-white p-1 shadow-lg ltr:right-0 rtl:left-0"
                                >
                                    <button
                                        v-for="(label, type) in fieldTypeOptions"
                                        :key="type"
                                        type="button"
                                        class="flex w-full items-center gap-2 rounded-md px-3 py-2 text-start text-sm text-gray-700 transition hover:bg-gray-100"
                                        @click="addField(type)"
                                    >
                                        {{ label }}
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div v-if="form.fields.length === 0" class="rounded-xl border border-dashed border-gray-200 p-8 text-center text-sm text-gray-500">
                            لا توجد حقول بعد. أضف حقولاً لبناء النموذج.
                        </div>

                        <ul v-else class="space-y-1.5">
                            <li
                                v-for="field in form.fields"
                                :key="field.id"
                                draggable="true"
                                class="group flex items-center gap-2 rounded-lg border border-gray-100 bg-white px-2 py-2 transition hover:border-gray-200"
                                @dragstart="onDragStart(field.id)"
                                @dragover.prevent
                                @drop.prevent="onDrop(field.id)"
                            >
                                <button
                                    type="button"
                                    class="cursor-grab rounded-md p-1 text-gray-300 transition hover:bg-gray-100 hover:text-gray-500 active:cursor-grabbing"
                                    aria-label="سحب لإعادة الترتيب"
                                >
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><circle cx="9" cy="7" r="1.2" /><circle cx="15" cy="7" r="1.2" /><circle cx="9" cy="12" r="1.2" /><circle cx="15" cy="12" r="1.2" /><circle cx="9" cy="17" r="1.2" /><circle cx="15" cy="17" r="1.2" /></svg>
                                </button>

                                <button
                                    type="button"
                                    class="flex min-w-0 flex-1 flex-col items-start text-start transition hover:text-primary-600"
                                    @click="openFieldEditor(field.id)"
                                >
                                    <span class="truncate text-sm font-medium text-gray-800">
                                        {{ field.label || 'حقل بدون عنوان' }}
                                    </span>
                                    <span class="truncate font-mono text-xs text-gray-500" dir="ltr">{{ field.name }}</span>
                                    <span class="truncate text-xs text-gray-400">
                                        {{ fieldTypeLabel(field.type) }}
                                        <template v-if="field.required"> · مطلوب</template>
                                    </span>
                                </button>

                                <button
                                    type="button"
                                    class="shrink-0 rounded-lg p-1 text-red-400/80 transition hover:bg-red-50 hover:text-red-500"
                                    aria-label="حذف الحقل"
                                    @click="deleteField(field.id)"
                                >
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" d="M4 7h16M10 11v6M14 11v6M6 7l1 14h10l1-14M9 7V5h6v2" /></svg>
                                </button>
                            </li>
                        </ul>
                    </div>
                </div>

                <div v-show="formTab === 'advanced'" class="space-y-2">
                    <Input
                        v-model="form.slug"
                        name="slug"
                        label="نص الرابط"
                        dir="ltr"
                        :prefix="slugPrefix"
                        :error="errors.slug"
                    />

                    <Toggle v-model="form.published" name="published" label="حالة النشر" />

                    <Input
                        v-model="form.submitLabel"
                        name="submitLabel"
                        label="نص زر الإرسال"
                        placeholder="إرسال"
                        info="يُحفظ في data.submit_label"
                    />

                    <Textarea
                        v-model="form.successMessage"
                        name="successMessage"
                        label="رسالة النجاح"
                        placeholder="شكراً! تم استلام طلبك بنجاح."
                        info="يُحفظ في data.success_message"
                    />
                </div>

                <template #footer>
                    <div class="flex items-center gap-2">
                        <Button type="button" variant="secondary" label="حفظ وإغلاق" :disabled="store.saving" @click="saveAndClose" />
                        <Button type="submit" label="حفظ" :disabled="store.saving" />
                    </div>
                </template>
            </Form>

            <div
                v-if="fieldModal && editingField"
                class="fixed inset-0 z-50 flex items-center justify-center p-4"
            >
                <div class="absolute inset-0 bg-gray-800/75" @click="closeFieldModal" />

                <div class="relative max-h-[90vh] w-full max-w-lg overflow-y-auto rounded-xl bg-white shadow-xl">
                    <div class="sticky top-0 z-10 flex items-center justify-between border-b border-gray-100 bg-white p-3 px-4">
                        <p class="text-sm font-semibold text-gray-600">تعديل الحقل</p>
                        <button type="button" class="rounded-md bg-gray-100 p-1 text-gray-400 hover:bg-gray-200" @click="closeFieldModal">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" d="M6 6l12 12M18 6 6 18" /></svg>
                        </button>
                    </div>

                    <div class="space-y-3 p-4">
                        <Select
                            v-model="editingField.type"
                            name="fieldType"
                            label="نوع الحقل"
                            :options="fieldTypeOptions"
                            @update:model-value="onFieldTypeChange"
                        />

                        <Input
                            v-model="editingField.label"
                            name="fieldLabel"
                            label="عنوان الحقل"
                            placeholder="مثال: الاسم الكامل"
                            @update:model-value="onFieldLabelChange"
                        />

                        <Input
                            v-model="editingField.name"
                            name="fieldName"
                            label="اسم الحقل (name)"
                            placeholder="full_name"
                            dir="ltr"
                            info="يُستخدم كمفتاح الحقل عند بناء النموذج. أحرف إنجليزية صغيرة وأرقام و _ فقط."
                            @update:model-value="onFieldNameChange"
                        />

                        <Input
                            v-if="editingField.type !== 'checkbox'"
                            v-model="editingField.placeholder"
                            name="fieldPlaceholder"
                            label="نص توضيحي"
                            placeholder="اكتب هنا..."
                        />

                        <Toggle v-model="editingField.required" name="fieldRequired" label="حقل مطلوب" />

                        <Textarea
                            v-model="editingField.info"
                            name="fieldInfo"
                            label="نص مساعد"
                            placeholder="تعليمات إضافية تظهر تحت الحقل"
                        />

                        <div v-if="fieldTypeHasOptions(editingField.type)" class="space-y-2">
                            <p class="px-1 text-sm font-semibold text-gray-600">الخيارات</p>

                            <div
                                v-for="(option, optionIndex) in editingField.options"
                                :key="`${editingField.id}-option-${optionIndex}`"
                                class="flex items-center gap-2"
                            >
                                <Input
                                    v-model="editingField.options[optionIndex]"
                                    :name="`option-${optionIndex}`"
                                    :placeholder="`خيار ${optionIndex + 1}`"
                                    class="flex-1"
                                />
                                <button
                                    type="button"
                                    class="shrink-0 rounded-lg p-2 text-red-400 transition hover:bg-red-50 hover:text-red-500"
                                    @click="removeOption(optionIndex)"
                                >
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" d="M6 6l12 12M18 6 6 18" /></svg>
                                </button>
                            </div>

                            <Button type="button" variant="secondary" label="إضافة خيار" class="w-full" @click="addOption" />
                        </div>

                        <div class="flex justify-end pt-2">
                            <Button type="button" label="تم" @click="closeFieldModal" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </ManageLayout>
    <ManageLayout v-else-if="store.detailLoading">
        <div class="rounded-2xl bg-white p-10 text-center text-sm text-gray-500">جاري التحميل…</div>
    </ManageLayout>
    <NotFound v-else-if="notFound" />
</template>
