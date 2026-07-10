<script setup>
import Container from '../components/ui/Container.vue';
import MainBox from '../components/ui/MainBox.vue';
</script>

<template>
    <Container>
        <MainBox title="الصفحة غير موجودة 🧭" subtitle="هذا المسار غير موجود في لوحة العرض.">
            <div class="p-6 text-sm text-gray-400">
                <RouterLink to="/" class="text-primary-700 underline">العودة إلى الرئيسية</RouterLink>
            </div>
        </MainBox>
    </Container>
</template>
