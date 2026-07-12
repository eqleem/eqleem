<script setup>
import { computed, onMounted, reactive, ref, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import ManageLayout from '../../../components/page/ManageLayout.vue';
import Form from '../../../components/ui/Form.vue';
import Input from '../../../components/ui/Input.vue';
import Button from '../../../components/ui/Button.vue';
import Toggle from '../../../components/ui/Toggle.vue';
import MediaGallery from '../../../components/ui/MediaGallery.vue';
import NotFound from '../../NotFound.vue';
import { useMenuStore } from '../../../stores/menu.js';
import { ApiError } from '../../../lib/api.js';
import { notifySuccess, notifyApiError } from '../../../lib/notify.js';

const route = useRoute();
const router = useRouter();
const menu = useMenuStore();
const formTab = ref('edit');
const uploading = ref(false);
const notFound = ref(false);

const form = reactive({
    title: '',
    slug: '',
    price: '',
    comparePrice: '',
    categoryIds: [],
    published: false,
    images: [],
    mealOptions: [],
});

const errors = reactive({
    title: null,
    slug: null,
    form: null,
});

const uuid = computed(() => String(route.params.id));
const categories = computed(() => menu.detail?.category_options ?? []);
const slugPrefix = computed(() => menu.detail?.slug_prefix ?? '/menu/item/');

function newChoiceId() {
    return crypto.randomUUID();
}

function newGroupId() {
    return crypto.randomUUID();
}

function switchTab(tab) {
    formTab.value = tab;
}

function loadForm(item) {
    if (!item) {
        return;
    }

    form.title = item.title ?? '';
    form.slug = item.slug ?? '';
    form.price = item.price ?? '';
    form.comparePrice = item.compare_price ?? '';
    form.categoryIds = [...(item.category_ids ?? [])].map(String);
    form.published = Boolean(item.published);
    form.images = [...(item.images ?? [])];
    form.mealOptions = (item.meal_options ?? []).map((group) => ({
        id: group.id ?? newGroupId(),
        name: group.name ?? '',
        type: group.type === 'multiple' ? 'multiple' : 'single',
        required: Boolean(group.required),
        choices: (group.choices ?? []).map((choice) => ({
            id: choice.id ?? newChoiceId(),
            name: choice.name ?? '',
            price: choice.price ?? '',
        })),
    }));
    errors.title = null;
    errors.slug = null;
    errors.form = null;
}

onMounted(async () => {
    try {
        const item = await menu.fetchItem(uuid.value);
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

    try {
        const item = await menu.fetchItem(String(id));
        loadForm(item);
    } catch (error) {
        notFound.value = error instanceof ApiError && error.status === 404;
    }
});

function toggleCategory(id, checked) {
    const key = String(id);

    if (checked) {
        if (!form.categoryIds.includes(key)) {
            form.categoryIds.push(key);
        }
        return;
    }

    form.categoryIds = form.categoryIds.filter((item) => item !== key);
}

function addMealOptionGroup() {
    form.mealOptions.push({
        id: newGroupId(),
        name: '',
        type: 'single',
        required: false,
        choices: [{
            id: newChoiceId(),
            name: '',
            price: '',
        }],
    });
}

function removeMealOptionGroup(groupIndex) {
    form.mealOptions.splice(groupIndex, 1);
}

function addMealOptionChoice(groupIndex) {
    if (!form.mealOptions[groupIndex]) {
        return;
    }

    form.mealOptions[groupIndex].choices.push({
        id: newChoiceId(),
        name: '',
        price: '',
    });
}

function removeMealOptionChoice(groupIndex, choiceIndex) {
    if (!form.mealOptions[groupIndex]) {
        return;
    }

    form.mealOptions[groupIndex].choices.splice(choiceIndex, 1);
}

async function uploadFiles(files) {
    uploading.value = true;

    try {
        for (const file of files) {
            form.images = await menu.uploadImage(uuid.value, file);
        }
    } catch (error) {
        errors.form = error instanceof ApiError ? error.message : 'تعذر رفع الصورة.';
    } finally {
        uploading.value = false;
    }
}

async function reorderImages(order) {
    try {
        form.images = await menu.reorderImages(uuid.value, order);
    } catch (error) {
        errors.form = error instanceof ApiError ? error.message : 'تعذر إعادة ترتيب الصور.';
        form.images = [...(menu.detail?.images ?? [])];
    }
}

async function removeImage(mediaId) {
    try {
        form.images = await menu.deleteImage(uuid.value, mediaId);
    } catch (error) {
        errors.form = error instanceof ApiError ? error.message : 'تعذر حذف الصورة.';
    }
}

async function persist({ close = false } = {}) {
    const title = form.title.trim();
    const slug = form.slug.trim();

    errors.title = title ? null : 'اسم الطبق مطلوب.';
    errors.slug = slug ? null : 'نص الرابط مطلوب.';
    errors.form = null;

    if (errors.title || errors.slug) {
        switchTab(errors.title ? 'edit' : 'advanced');
        return;
    }

    const selectable = new Set(categories.value.filter((item) => item.selectable).map((item) => String(item.id)));
    const categoryIds = form.categoryIds
        .filter((id) => selectable.has(String(id)))
        .map((id) => Number(id))
        .filter((id) => Number.isFinite(id) && id > 0);

    const payload = {
        title,
        slug,
        category_ids: categoryIds,
        published: Boolean(form.published),
        meal_options: form.mealOptions.map((group) => ({
            id: group.id,
            name: group.name,
            type: group.type,
            required: Boolean(group.required),
            choices: group.choices.map((choice) => ({
                id: choice.id,
                name: choice.name,
                price: choice.price === '' ? null : Number(choice.price),
            })),
        })),
    };

    if (form.price !== '') {
        payload.price = Number(form.price);
    }

    if (form.comparePrice !== '') {
        payload.compare_price = Number(form.comparePrice);
    }

    try {
        const item = await menu.updateItem(uuid.value, payload);

        if (close) {
            router.push('/manage/menu');
            return;
        }

        loadForm(item);
        notifySuccess('Saved');
    } catch (error) {
        if (error instanceof ApiError) {
            errors.title = error.errors?.title?.[0] ?? null;
            errors.slug = error.errors?.slug?.[0] ?? null;
            errors.form = (!errors.title && !errors.slug)
                ? (error.message || 'تعذر حفظ الطبق.')
                : null;

            if (errors.title) {
                switchTab('edit');
            } else if (errors.slug) {
                switchTab('advanced');
            }
        } else {
            errors.form = 'تعذر حفظ الطبق.';
        }

        notifyApiError(error, 'تعذر حفظ الطبق.');
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
    <ManageLayout v-if="menu.detail && !notFound">
        <div class="overflow-hidden rounded-2xl bg-white">
            <div class="relative z-20 flex items-center justify-between gap-4 border-b border-stone-200 bg-stone-200/70 px-4 py-3">
                <div class="flex min-w-0 items-center gap-3">
                    <RouterLink
                        to="/manage/menu"
                        title="رجوع"
                        class="flex shrink-0 items-center justify-center rounded-lg bg-white p-2 shadow-sm hover:bg-gray-50"
                    >
                        <svg class="h-5 w-5 text-gray-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                    </RouterLink>
                    <div class="flex min-w-0 items-center gap-2 text-sm text-gray-700">
                        <img v-if="menu.type?.icon" :src="`/${menu.type.icon}`" class="h-5 w-5 shrink-0" alt="">
                        <span class="truncate font-semibold">{{ menu.type?.name }}</span>
                        <span class="text-gray-400 hidden md:inline">/</span>
                        <span class="truncate text-gray-600 hidden md:inline">تحرير الطبق</span>
                    </div>
                </div>

                <nav class="relative z-20 flex shrink-0 items-center gap-1 rounded-xl bg-gray-300/40 p-0.5">
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

                <div class="space-y-2" :class="formTab === 'edit' ? 'relative z-0 block' : 'hidden'">
                    <Input
                        v-model="form.title"
                        name="title"
                        placeholder="اسم الطبق"
                        :error="errors.title"
                    />

                    <MediaGallery
                        v-model="form.images"
                        label="صور الطبق"
                        :uploading="uploading"
                        :disabled="menu.saving"
                        @upload="uploadFiles"
                        @remove="removeImage"
                        @reorder="reorderImages"
                    />

                    <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                        <Input
                            v-model="form.price"
                            name="price"
                            label="السعر الأساسي"
                            type="number"
                            dir="ltr"
                            step="0.01"
                            min="0"
                            placeholder="0.00"
                        />
                        <Input
                            v-model="form.comparePrice"
                            name="comparePrice"
                            label="سعر المقارنة"
                            type="number"
                            dir="ltr"
                            step="0.01"
                            min="0"
                            placeholder="0.00"
                        />
                    </div>

                    <div class="space-y-3 rounded-xl border border-stone-200 bg-stone-50/50 p-4">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <p class="text-sm font-semibold text-gray-800">خيارات الوجبة</p>
                                <p class="mt-0.5 text-xs text-gray-500">مثل الحجم والإضافات — كل خيار له اختيارات وأسعار إضافية</p>
                            </div>
                            <Button type="button" variant="outline" label="إضافة خيار" @click="addMealOptionGroup" />
                        </div>

                        <div v-if="form.mealOptions.length === 0" class="rounded-lg border border-dashed border-stone-300 bg-white px-4 py-6 text-center">
                            <p class="text-sm text-gray-500">لا توجد خيارات بعد. أضف خياراً مثل «حجم الوجبة» أو «الإضافات».</p>
                        </div>

                        <div
                            v-for="(group, groupIndex) in form.mealOptions"
                            :key="group.id"
                            class="space-y-3 rounded-xl border border-stone-200 bg-white p-4"
                        >
                            <div class="flex items-start justify-between gap-3">
                                <p class="rounded-md bg-primary-50 px-2 py-1 text-xs font-semibold text-primary-600">
                                    خيار {{ groupIndex + 1 }}
                                </p>
                                <button
                                    type="button"
                                    class="rounded-lg p-1.5 text-red-400 transition hover:bg-red-50 hover:text-red-600"
                                    title="حذف الخيار"
                                    @click="removeMealOptionGroup(groupIndex)"
                                >
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" d="M3 6h18M8 6V4h8v2M19 6l-1 14H6L5 6" /></svg>
                                </button>
                            </div>

                            <Input
                                v-model="group.name"
                                :name="`mealOptions.${groupIndex}.name`"
                                label="اسم الخيار"
                                placeholder="مثال: حجم الوجبة"
                            />

                            <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                                <div>
                                    <label class="mb-1 block text-sm font-medium text-gray-700">نوع الاختيار</label>
                                    <select
                                        v-model="group.type"
                                        class="block w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-800 focus:border-primary-500 focus:outline-none"
                                    >
                                        <option value="single">اختيار واحد (مثل الحجم)</option>
                                        <option value="multiple">اختيار متعدد (مثل الإضافات)</option>
                                    </select>
                                </div>
                                <div class="flex items-end pb-1">
                                    <Toggle v-model="group.required" :name="`mealOptions.${groupIndex}.required`" label="إلزامي" />
                                </div>
                            </div>

                            <div class="space-y-2">
                                <p class="text-sm font-semibold text-gray-600">الاختيارات</p>

                                <div
                                    v-for="(choice, choiceIndex) in group.choices"
                                    :key="choice.id"
                                    class="flex items-start gap-2"
                                >
                                    <div class="grid flex-1 grid-cols-1 gap-2 sm:grid-cols-2">
                                        <Input
                                            v-model="choice.name"
                                            :name="`mealOptions.${groupIndex}.choices.${choiceIndex}.name`"
                                            placeholder="اسم الاختيار"
                                        />
                                        <Input
                                            v-model="choice.price"
                                            :name="`mealOptions.${groupIndex}.choices.${choiceIndex}.price`"
                                            type="number"
                                            dir="ltr"
                                            step="0.01"
                                            min="0"
                                            placeholder="سعر إضافي (0.00)"
                                        />
                                    </div>
                                    <button
                                        type="button"
                                        class="mt-1 shrink-0 rounded-lg p-2 text-red-400 transition hover:bg-red-50 hover:text-red-600"
                                        title="حذف الاختيار"
                                        @click="removeMealOptionChoice(groupIndex, choiceIndex)"
                                    >
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" d="M6 18L18 6M6 6l12 12" /></svg>
                                    </button>
                                </div>

                                <Button
                                    type="button"
                                    variant="secondary"
                                    label="إضافة اختيار"
                                    class="w-full"
                                    @click="addMealOptionChoice(groupIndex)"
                                />
                            </div>
                        </div>
                    </div>
                </div>

                <div class="space-y-2" :class="formTab === 'advanced' ? 'relative z-10 block' : 'hidden'">
                    <div class="relative rounded-md bg-gray-100/75 p-1 lg:flex lg:items-start lg:gap-x-2">
                        <span class="inline-block w-36 flex-shrink-0 p-2 text-sm font-semibold text-gray-500">القسم</span>
                        <div class="w-full space-y-1.5 p-2">
                            <label
                                v-for="option in categories"
                                :key="option.id"
                                class="flex items-center gap-2 text-sm"
                                :class="option.selectable ? 'text-gray-700' : 'text-gray-400'"
                            >
                                <input
                                    type="checkbox"
                                    class="h-4 w-4 rounded border-gray-300"
                                    :disabled="!option.selectable"
                                    :checked="form.categoryIds.includes(String(option.id))"
                                    @change="toggleCategory(option.id, $event.target.checked)"
                                >
                                <span>{{ option.label }}</span>
                            </label>
                            <p v-if="categories.length === 0" class="text-xs text-gray-400">لا توجد تصنيفات بعد.</p>
                        </div>
                    </div>

                    <Input
                        v-model="form.slug"
                        name="slug"
                        label="نص الرابط"
                        dir="ltr"
                        :prefix="slugPrefix"
                        :error="errors.slug"
                    />

                    <Toggle v-model="form.published" name="published" label="حالة النشر" />
                </div>

                <template #footer>
                    <div class="flex items-center gap-2">
                        <Button type="button" variant="secondary" label="حفظ وإغلاق" :disabled="menu.saving" @click="saveAndClose" />
                        <Button type="submit" label="حفظ" :disabled="menu.saving" />
                    </div>
                </template>
            </Form>
        </div>
    </ManageLayout>
    <ManageLayout v-else-if="menu.detailLoading">
        <div class="flex items-center justify-center rounded-2xl bg-white p-10"><LoadingSpinner size="lg" /></div>
    </ManageLayout>
    <NotFound v-else-if="notFound" />
</template>
