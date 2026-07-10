<script setup>
import { computed, reactive, watch } from 'vue';
import Form from '../../ui/Form.vue';
import Input from '../../ui/Input.vue';
import Button from '../../ui/Button.vue';
import {
    addCategory,
    updateCategory,
    findCategory,
    parentCategoryOptions,
    descendantIds,
} from '../../../data/portfolio.js';
import { closeModal } from '../../../lib/modal.js';

const props = defineProps({
    categoryId: { type: [Number, String], default: null },
    defaultParentId: { type: [Number, String], default: null },
    modalName: { type: String, required: true },
});

const form = reactive({
    name: '',
    slug: '',
    parentId: '',
});
const errors = reactive({ name: null, slug: null });

const isEdit = computed(() => props.categoryId != null);

const parentOptions = computed(() => {
    const excluded = isEdit.value ? descendantIds(props.categoryId) : [];

    return parentCategoryOptions(excluded);
});

function load() {
    errors.name = null;
    errors.slug = null;

    if (isEdit.value) {
        const category = findCategory(props.categoryId);

        if (!category) {
            return;
        }

        form.name = category.name;
        form.slug = category.slug;
        form.parentId = category.parent_id != null ? String(category.parent_id) : '';
        return;
    }

    form.name = '';
    form.slug = '';
    form.parentId = props.defaultParentId != null ? String(props.defaultParentId) : '';
}

watch(() => [props.categoryId, props.defaultParentId], load, { immediate: true });

function submit() {
    const name = form.name.trim();

    if (!name) {
        errors.name = 'اسم التصنيف مطلوب.';
        return;
    }

    errors.name = null;

    if (isEdit.value) {
        const slug = form.slug.trim();

        if (!slug) {
            errors.slug = 'نص الرابط مطلوب.';
            return;
        }

        errors.slug = null;
        updateCategory(props.categoryId, {
            name,
            slug,
            parent_id: form.parentId ? Number(form.parentId) : null,
        });
    } else {
        addCategory({
            name,
            parent_id: form.parentId ? Number(form.parentId) : null,
        });
    }

    closeModal(props.modalName);
}
</script>

<template>
    <Form class="!rounded-none" @submit="submit">
        <Input
            v-model="form.name"
            name="name"
            label="اسم التصنيف"
            placeholder="مثال: تصميم داخلي"
            :error="errors.name"
        />

        <Input
            v-if="isEdit"
            v-model="form.slug"
            name="slug"
            label="نص الرابط"
            dir="ltr"
            placeholder="مثال: interior-design"
            info="يُستخدم في فلترة المشاريع عبر الرابط."
            :error="errors.slug"
        />

        <div class="relative items-center gap-x-2 rounded-md bg-gray-100/75 p-1 lg:flex">
            <label for="parentId" class="inline-block w-36 flex-shrink-0 p-2 text-sm font-semibold text-gray-500">
                التصنيف الأب
            </label>
            <div class="w-full">
                <select
                    id="parentId"
                    v-model="form.parentId"
                    class="block w-full rounded-md border-2 border-transparent bg-white px-3 py-1.5 text-sm text-gray-600 focus:border-primary-500 focus:bg-gray-100/50 focus:outline-none"
                >
                    <option v-for="option in parentOptions" :key="option.id || 'root'" :value="option.id">
                        {{ option.label }}
                    </option>
                </select>
            </div>
        </div>

        <template #footer>
            <Button type="submit" label="حفظ" />
        </template>
    </Form>
</template>
