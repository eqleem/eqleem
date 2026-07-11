<script setup>
import ManageLayout from '../../../components/page/ManageLayout.vue';
import Shell from '../../../components/page/services/Shell.vue';
import CategoriesTable from '../../../components/page/services/CategoriesTable.vue';
</script>

<template>
    <ManageLayout>
        <Shell>
            <CategoriesTable />
        </Shell>
    </ManageLayout>
</template>
