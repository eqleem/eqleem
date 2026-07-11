<script setup>
import ManageLayout from '../../../components/page/ManageLayout.vue';
import Shell from '../../../components/page/store/Shell.vue';
import SettingsShippingOptions from '../../settings/ShippingOptions.vue';
</script>

<template>
    <ManageLayout>
        <Shell>
            <SettingsShippingOptions embedded />
        </Shell>
    </ManageLayout>
</template>
