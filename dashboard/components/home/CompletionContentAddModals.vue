<script setup>
import Modal from '../ui/Modal.vue';

import AddBlogPost from '../page/blog/AddPost.vue';
import AddPage from '../page/pages/AddPage.vue';
import AddForm from '../page/forms/AddForm.vue';
import AddStoreProduct from '../page/store/AddProduct.vue';
import AddDigitalProduct from '../page/digital-products/AddProduct.vue';
import AddDigitalService from '../page/digital-services/AddDigitalService.vue';
import AddOnDemandService from '../page/on-demand-services/AddOnDemandService.vue';
import AddService from '../page/services/AddService.vue';
import AddNewsletter from '../page/newsletter/AddNewsletter.vue';
import AddMenuItem from '../page/menu/AddItem.vue';
import AddUnit from '../page/unit-rental/AddUnit.vue';
import AddCourse from '../page/courses/AddCourse.vue';
import AddPortfolioProject from '../page/portfolio/AddProject.vue';

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
