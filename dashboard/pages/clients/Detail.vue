<script setup>
import { computed, onMounted, onUnmounted, watch } from 'vue';
import { storeToRefs } from 'pinia';
import { useRoute } from 'vue-router';
import Container from '../../components/ui/Container.vue';
import Icon from '../../components/ui/Icon.vue';
import OrdersTable from '../../components/clients/OrdersTable.vue';
import InvoicesTable from '../../components/clients/InvoicesTable.vue';
import { useClientsStore } from '../../stores/clients.js';

const route = useRoute();
const clientsStore = useClientsStore();
const { detail: client, detailLoading: loading, detailError: error } = storeToRefs(clientsStore);

const tabs = [
    { name: 'info', label: 'المعلومات الشخصية', icon: 'user' },
    { name: 'orders', label: 'الطلبات', icon: 'message' },
    { name: 'invoices', label: 'الفواتير', icon: 'invoice' },
];
const active = computed(() => (tabs.some((tab) => tab.name === route.query.tab) ? route.query.tab : 'info'));

async function loadClient(uuid) {
    if (!uuid) {
        return;
    }

    try {
        await clientsStore.fetchClient(uuid);
    } catch {
        // Error state is handled via store.
    }
}

watch(
    () => route.params.uuid,
    (uuid) => loadClient(uuid),
);

onMounted(() => loadClient(route.params.uuid));
onUnmounted(() => clientsStore.clearDetail());
</script>

<template>
    <Container :title="`العملاء / ${client?.name ?? '...'}`" back-route="/clients">
        <div
            v-if="loading && !client"
            class="flex items-center justify-center rounded-xl bg-white p-16"
        >
            <svg class="h-10 w-10 animate-spin text-gray-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" d="M12 3a9 9 0 1 0 9 9" />
            </svg>
        </div>

        <div
            v-else-if="error && !client"
            class="flex flex-col items-center justify-center gap-3 rounded-xl bg-white p-16 text-center"
        >
            <p class="text-sm text-red-600">{{ error }}</p>
            <button
                type="button"
                class="rounded-lg border bg-white px-3 py-1.5 text-sm text-gray-700 hover:bg-gray-100"
                @click="loadClient(route.params.uuid)"
            >
                إعادة المحاولة
            </button>
        </div>

        <article v-else-if="client" class="rounded-xl bg-white">
            <div class="h-20 w-full rounded-t-xl bg-primary-200 lg:h-40"></div>
            <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
                <div class="-mt-6 sm:-mt-10 sm:flex sm:items-end sm:gap-x-5">
                    <img class="z-50 h-16 w-16 rounded-full ring-4 ring-white/50 sm:h-20 sm:w-20" :src="client.avatar" alt="">
                    <div class="mt-6 min-w-0 flex-1">
                        <h1 class="ms-3 truncate text-2xl font-bold text-gray-900">{{ client.name }}</h1>
                    </div>
                </div>
            </div>

            <div class="mt-6">
                <div class="flex border-b">
                    <RouterLink
                        v-for="tab in tabs"
                        :key="tab.name"
                        :to="{ query: { tab: tab.name } }"
                        class="flex items-center gap-x-2 px-4 py-3 text-sm"
                        :class="active === tab.name ? 'border-b-2 border-blue-800 text-gray-900' : 'text-gray-500 hover:text-gray-800'"
                    >
                        <Icon :name="tab.icon" class="h-4 w-4" />
                        {{ tab.label }}
                    </RouterLink>
                </div>

                <div v-if="active === 'info'" class="grid grid-cols-1 gap-x-4 gap-y-8 p-5 sm:grid-cols-2 xl:p-10">
                    <div>
                        <dt class="text-sm text-gray-400">رقم الجوال</dt>
                        <dd class="mt-2 inline-block text-base font-bold text-gray-700" dir="ltr">{{ client.phone || '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-gray-400">البريد الإلكتروني</dt>
                        <dd class="mt-2 text-base font-bold text-gray-700">{{ client.email || '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-gray-400">العنوان</dt>
                        <dd class="mt-2 text-base font-bold text-gray-700">{{ client.address || '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-gray-400">المدينة</dt>
                        <dd class="mt-2 text-base font-bold text-gray-700">{{ client.city || '-' }}</dd>
                    </div>
                </div>
                <OrdersTable v-else-if="active === 'orders'" />
                <InvoicesTable v-else-if="active === 'invoices'" />
            </div>
        </article>
    </Container>
</template>
