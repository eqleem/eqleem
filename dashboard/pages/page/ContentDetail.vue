<script setup>
import { computed, reactive } from 'vue';
import { useRoute } from 'vue-router';
import ManageLayout from '../../components/page/ManageLayout.vue';
import ContentShell from '../../components/page/ContentShell.vue';
import Form from '../../components/ui/Form.vue';
import Input from '../../components/ui/Input.vue';
import Button from '../../components/ui/Button.vue';
import NotFound from '../NotFound.vue';
import { contentTypeBySlug } from '../../data/page.js';

// Port of resources/views/admin/page/content/<type>/detail.blade.php (dummy data).
// ContentShell keeps the sidebar type + "العناصر" sub-tab active via the route name.
const route = useRoute();
const contentType = computed(() => contentTypeBySlug(route.params.type));
const itemId = computed(() => route.params.id);

const form = reactive({ title: 'عنصر تجريبي', slug: '', status: 'published' });
function submit() {}
</script>

<template>
    <ManageLayout v-if="contentType">
        <ContentShell :content-type="contentType">
            <div class="flex items-center justify-between gap-2 border-b border-dotted border-gray-200 bg-gray-50/60 px-4 py-2.5">
                <p class="truncate text-sm text-gray-500">
                    تعديل العنصر <span class="font-medium text-gray-700">#{{ itemId }}</span>
                </p>
                <RouterLink :to="`/manage/${contentType.slug}`" class="shrink-0 text-xs text-primary-600 hover:text-primary-700">
                    رجوع للقائمة
                </RouterLink>
            </div>

            <Form @submit="submit">
                <Input v-model="form.title" name="title" label="العنوان" placeholder="عنوان العنصر" />
                <Input v-model="form.slug" name="slug" label="الرابط" placeholder="item-slug" dir="ltr" />
                <template #footer>
                    <Button type="submit" label="حفظ" />
                </template>
            </Form>
        </ContentShell>
    </ManageLayout>
    <NotFound v-else />
</template>
