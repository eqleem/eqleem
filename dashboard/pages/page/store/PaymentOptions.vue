<script setup>
import ManageLayout from '../../../components/page/ManageLayout.vue';
import Shell from '../../../components/page/store/Shell.vue';
import SettingsPaymentOptions from '../../settings/PaymentOptions.vue';
</script>

<template>
    <ManageLayout>
        <Shell>
            <SettingsPaymentOptions embedded />
        </Shell>
    </ManageLayout>
</template>
