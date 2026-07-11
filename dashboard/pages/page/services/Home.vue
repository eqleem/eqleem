<script setup>
import ManageLayout from '../../../components/page/ManageLayout.vue';
import Shell from '../../../components/page/services/Shell.vue';
import ServicesTable from '../../../components/page/services/ServicesTable.vue';
</script>

<template>
    <ManageLayout>
        <Shell>
            <ServicesTable />
        </Shell>
    </ManageLayout>
</template>
