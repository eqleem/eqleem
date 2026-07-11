<script setup>
import ManageLayout from '../../../components/page/ManageLayout.vue';
import Shell from '../../../components/page/services/Shell.vue';
import CalendarsTable from '../../../components/page/services/CalendarsTable.vue';
</script>

<template>
    <ManageLayout>
        <Shell>
            <CalendarsTable />
        </Shell>
    </ManageLayout>
</template>
