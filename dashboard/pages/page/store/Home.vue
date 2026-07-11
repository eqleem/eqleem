<script setup>
import ManageLayout from '../../../components/page/ManageLayout.vue';
import Shell from '../../../components/page/store/Shell.vue';
import ProductsTable from '../../../components/page/store/ProductsTable.vue';
</script>

<template>
    <ManageLayout>
        <Shell>
            <ProductsTable />
        </Shell>
    </ManageLayout>
</template>
