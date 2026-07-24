<script setup>
import { defineAsyncComponent } from 'vue';
import Modal from '../ui/Modal.vue';

const AddBlogPost = defineAsyncComponent(() => import('../page/blog/AddPost.vue'));
const AddPage = defineAsyncComponent(() => import('../page/pages/AddPage.vue'));
const AddForm = defineAsyncComponent(() => import('../page/forms/AddForm.vue'));
const AddStoreProduct = defineAsyncComponent(() => import('../page/store/AddProduct.vue'));
const AddDigitalProduct = defineAsyncComponent(() => import('../page/digital-products/AddProduct.vue'));
const AddDigitalService = defineAsyncComponent(() => import('../page/digital-services/AddDigitalService.vue'));
const AddOnDemandService = defineAsyncComponent(() => import('../page/on-demand-services/AddOnDemandService.vue'));
const AddService = defineAsyncComponent(() => import('../page/services/AddService.vue'));
const AddNewsletter = defineAsyncComponent(() => import('../page/newsletter/AddNewsletter.vue'));
const AddMenuItem = defineAsyncComponent(() => import('../page/menu/AddItem.vue'));
const AddUnit = defineAsyncComponent(() => import('../page/unit-rental/AddUnit.vue'));
const AddCourse = defineAsyncComponent(() => import('../page/courses/AddCourse.vue'));
const AddPortfolioProject = defineAsyncComponent(() => import('../page/portfolio/AddProject.vue'));

const addForms = [
    { slug: 'blog', modal: 'add-blog-post', title: 'إضافة تدوينة جديدة', component: AddBlogPost },
    { slug: 'store', modal: 'add-store-product', title: 'إضافة منتج جديد', component: AddStoreProduct },
    { slug: 'courses', modal: 'add-course', title: 'إضافة دورة تدريبية جديد', component: AddCourse },
    { slug: 'portfolio', modal: 'add-portfolio-project', title: 'إضافة مشروع جديد', component: AddPortfolioProject },
    { slug: 'pages', modal: 'add-page', title: 'إضافة صفحة جديدة', component: AddPage },
    { slug: 'forms', modal: 'add-form', title: 'إضافة نموذج جديد', component: AddForm },
    { slug: 'services', modal: 'add-service', title: 'إضافة خدمة جديد', component: AddService },
    { slug: 'digital-products', modal: 'add-digital-product', title: 'إضافة منتج رقمي جديد', component: AddDigitalProduct },
    { slug: 'digital-services', modal: 'add-digital-service', title: 'إضافة خدمة جديد', component: AddDigitalService },
    { slug: 'on-demand-services', modal: 'add-on-demand-service', title: 'إضافة خدمة حسب الطلب', component: AddOnDemandService },
    { slug: 'newsletter', modal: 'add-newsletter', title: 'إضافة نشرة بريدية جديدة', component: AddNewsletter },
    { slug: 'menu', modal: 'add-menu-item', title: 'إضافة طبق جديد', component: AddMenuItem },
    { slug: 'unit-rental', modal: 'add-unit', title: 'إضافة نوع وحدة', component: AddUnit },
];
</script>

<template>
    <Modal
        v-for="form in addForms"
        :key="form.slug"
        :title="form.title"
        size="2xl"
        :name="form.modal"
    >
        <component :is="form.component" />
    </Modal>
</template>
