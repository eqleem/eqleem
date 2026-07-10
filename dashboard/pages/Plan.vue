<script setup>
import Container from '../components/ui/Container.vue';
import MainBox from '../components/ui/MainBox.vue';
</script>

<template>
    <Container>
        <MainBox title="إدارة الاشتراك" subtitle="اختر الباقة المناسبة لصفحتك.">
            <template #icon>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-600" viewBox="0 0 24 24" fill="none">
                    <path
                        d="M12 12a5 5 0 1 0 0-10 5 5 0 0 0 0 10Z"
                        stroke="currentColor"
                        stroke-width="1.5"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    />
                    <path
                        opacity=".4"
                        d="M20.59 22c0-3.87-3.85-7-8.59-7s-8.59 3.13-8.59 7"
                        stroke="currentColor"
                        stroke-width="1.5"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    />
                </svg>
            </template>
            <div class="p-6 text-sm text-gray-400">صفحة الاشتراك — لا توجد بيانات حقيقية هنا بعد.</div>
        </MainBox>
    </Container>
</template>
