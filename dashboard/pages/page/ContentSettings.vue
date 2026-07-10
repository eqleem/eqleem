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

// Port of resources/views/admin/page/content/<type>/customize.blade.php (dummy data).
const route = useRoute();
const contentType = computed(() => contentTypeBySlug(route.params.type));

const form = reactive({ title: '', perPage: '12' });
function submit() {}
</script>

<template>
    <ManageLayout v-if="contentType">
        <ContentShell :content-type="contentType">
            <Form @submit="submit">
                <Input v-model="form.title" name="title" label="عنوان القسم" :placeholder="contentType.name" />
                <Input v-model="form.perPage" name="perPage" type="number" label="عدد العناصر بالصفحة" placeholder="12" dir="ltr" />
                <template #footer>
                    <Button type="submit" label="حفظ" />
                </template>
            </Form>
        </ContentShell>
    </ManageLayout>
    <NotFound v-else />
</template>
