<script setup>
import ManageLayout from '../../../components/page/ManageLayout.vue';
import Shell from '../../../components/page/store/Shell.vue';
import CategoriesTable from '../../../components/page/store/CategoriesTable.vue';
</script>

<template>
    <ManageLayout>
        <Shell>
            <CategoriesTable />
        </Shell>
    </ManageLayout>
</template>
