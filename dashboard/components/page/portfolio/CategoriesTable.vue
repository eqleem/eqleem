<script setup>
import { ref, computed } from 'vue';
import Button from '../../ui/Button.vue';
import Modal from '../../ui/Modal.vue';
import Dropdown from '../../Dropdown.vue';
import CategoryForm from './CategoryForm.vue';
import { categories, deleteCategory } from '../../../data/portfolio.js';
import { openModal } from '../../../lib/modal.js';

const search = ref('');
const editingCategoryId = ref(null);
const addingParentId = ref(null);

const results = computed(() => {
    const query = search.value.trim().toLowerCase();

    if (!query) {
        return categories;
    }

    return categories.filter((item) => {
        return item.name.toLowerCase().includes(query)
            || String(item.description ?? '').toLowerCase().includes(query);
    });
});

function openAdd(parentId = null) {
    addingParentId.value = parentId;
    openModal('add-portfolio-category');
}

function openEdit(categoryId) {
    editingCategoryId.value = categoryId;
    openModal('edit-portfolio-category');
}

function remove(categoryId) {
    if (!confirm('هل أنت متأكد من حذف هذا التصنيف؟')) {
        return;
    }

    deleteCategory(categoryId);
}
</script>

<template>
    <div class="divide-y divide-dotted divide-gray-200">
        <div class="flex w-full items-center gap-x-4 bg-gray-100 p-3">
            <div class="flex-grow">
                <div class="relative text-sm text-gray-800">
                    <div class="pointer-events-none absolute bottom-0 right-0 top-0 flex items-center ps-2 text-gray-500">
                        <svg class="h-5 w-5 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="11" cy="11" r="7" /><path stroke-linecap="round" d="m20 20-3-3" /></svg>
                    </div>
                    <input
                        v-model="search"
                        type="text"
                        placeholder="ابحث .."
                        class="block w-full rounded-lg border border-transparent py-1.5 ps-10 text-gray-800 ring-inset ring-gray-200 placeholder:text-gray-400 focus:border-primary-500 focus:outline-none sm:text-sm sm:leading-6"
                    >
                </div>
            </div>

            <Button label="تصنيف جديد" @click="openAdd()">
                <template #icon>
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" d="M12 5v14M5 12h14" /></svg>
                </template>
            </Button>

            <Modal
                :title="addingParentId ? 'إضافة قسم فرعي' : 'إضافة تصنيف'"
                size="lg"
                name="add-portfolio-category"
            >
                <CategoryForm
                    :key="`add-${addingParentId ?? 'root'}`"
                    :default-parent-id="addingParentId"
                    modal-name="add-portfolio-category"
                />
            </Modal>
        </div>

        <div class="relative p-1">
            <div v-if="results.length === 0" class="flex flex-col items-center justify-center gap-2 p-10 text-center">
                <svg class="h-12 w-12 text-gray-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" d="M4 6h16M4 12h16M4 18h10" /></svg>
                <p class="text-gray-700">لا توجد تصنيفات.</p>
                <small class="text-gray-500">سيتم عرض تصنيفات الأعمال هنا بعد إضافتها.</small>
            </div>

            <ul v-else class="pb-4">
                <li
                    v-for="category in results"
                    :key="category.id"
                    class="group flex w-full items-center justify-between gap-x-4 hover:bg-gray-50 last:rounded-b-2xl"
                >
                    <div
                        class="min-w-0 w-full py-3"
                        :style="{ paddingInlineStart: `calc(1.5rem + ${(category.depth ?? 0) * 1.25}rem)` }"
                    >
                        <div class="flex min-w-0 items-center gap-x-3 pe-4">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-gray-100">
                                <svg class="h-5 w-5 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" d="M4 6h16M4 12h16M4 18h10" /></svg>
                            </div>

                            <button
                                type="button"
                                class="min-w-0 text-start transition hover:text-primary-600"
                                @click="openEdit(category.id)"
                            >
                                <h2 class="truncate text-base text-gray-700">{{ category.name }}</h2>
                                <p v-if="category.description" class="mt-0.5 truncate text-xs text-gray-500">
                                    {{ category.description }}
                                </p>
                            </button>
                        </div>
                    </div>

                    <div class="flex shrink-0 items-center gap-x-1 pe-6">
                        <button
                            type="button"
                            class="rounded-lg p-1.5 text-gray-400 opacity-0 transition pointer-events-none hover:bg-primary-50 hover:text-primary-600 group-hover:pointer-events-auto group-hover:opacity-100"
                            title="إضافة قسم فرعي"
                            aria-label="إضافة قسم فرعي"
                            @click="openAdd(category.id)"
                        >
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" d="M12 5v14M5 12h14" /></svg>
                        </button>

                        <Dropdown width="w-36">
                            <template #trigger>
                                <button type="button" class="rounded p-1.5 text-gray-500 hover:bg-gray-100" aria-label="menu">
                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="5" r="1.6" /><circle cx="12" cy="12" r="1.6" /><circle cx="12" cy="19" r="1.6" /></svg>
                                </button>
                            </template>
                            <button
                                type="button"
                                class="flex w-full items-center gap-x-2 rounded p-1.5 text-start hover:bg-stone-100"
                                @click="openEdit(category.id)"
                            >
                                تعديل
                            </button>
                            <button
                                type="button"
                                class="flex w-full items-center gap-x-2 rounded p-1.5 text-start text-red-600 hover:bg-stone-100"
                                @click="remove(category.id)"
                            >
                                حذف
                            </button>
                        </Dropdown>
                    </div>
                </li>
            </ul>
        </div>

        <Modal title="تعديل التصنيف" size="lg" name="edit-portfolio-category">
            <CategoryForm
                v-if="editingCategoryId"
                :key="`edit-${editingCategoryId}`"
                :category-id="editingCategoryId"
                modal-name="edit-portfolio-category"
            />
        </Modal>
    </div>
</template>
