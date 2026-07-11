<script setup>
import { reactive, ref, computed, watch, onBeforeUnmount } from 'vue';
import { useRouter } from 'vue-router';
import Box from '../ui/Box.vue';
import Button from '../ui/Button.vue';
import Input from '../ui/Input.vue';
import Dropdown from '../Dropdown.vue';
import { ApiError } from '../../lib/api.js';
import { closeModal } from '../../lib/modal.js';
import { useClientsStore } from '../../stores/clients.js';
import { useOrdersStore } from '../../stores/orders.js';
import { addItemTypeOptions, itemTypeOptions, itemSearchPlaceholders, walkingClientLabel, money } from '../../data/orders.js';

const router = useRouter();
const clientsStore = useClientsStore();
const ordersStore = useOrdersStore();

const clientId = ref(null);
const isWalking = ref(true);
const selectedClient = ref(null);
const clientSearch = ref('');
const showClientResults = ref(false);
const clientResults = ref([]);
const clientSearching = ref(false);
let clientSearchTimer = null;

const formError = ref(null);
const submitting = ref(false);

watch(clientSearch, (value) => {
    if (clientId.value || isWalking.value) {
        return;
    }

    clearTimeout(clientSearchTimer);
    clientSearchTimer = setTimeout(async () => {
        const query = value.trim();
        if (!query) {
            clientResults.value = [];
            return;
        }

        clientSearching.value = true;
        try {
            clientResults.value = await clientsStore.searchClients(query);
        } catch {
            clientResults.value = [];
        } finally {
            clientSearching.value = false;
        }
    }, 250);
});

function selectClient(client) {
    clientId.value = client.id;
    isWalking.value = false;
    selectedClient.value = client;
    clientSearch.value = client.name;
    showClientResults.value = false;
    clientResults.value = [];
}

function selectWalking() {
    clientId.value = null;
    isWalking.value = true;
    selectedClient.value = null;
    clientSearch.value = '';
    showClientResults.value = false;
    clientResults.value = [];
}

function enterSearch() {
    clientId.value = null;
    isWalking.value = false;
    selectedClient.value = null;
    clientSearch.value = '';
    showClientResults.value = true;
}

const showCreate = ref(false);
const newClient = reactive({ name: '', phone: '', email: '' });
const newClientErrors = reactive({ name: null, phone: null, email: null });
const creatingClient = ref(false);

function openCreate() {
    newClient.name = clientSearch.value;
    newClient.phone = '';
    newClient.email = '';
    newClientErrors.name = null;
    newClientErrors.phone = null;
    newClientErrors.email = null;
    showCreate.value = true;
}

async function saveNewClient() {
    creatingClient.value = true;
    newClientErrors.name = null;
    newClientErrors.phone = null;
    newClientErrors.email = null;

    try {
        const { client } = await clientsStore.createClient({
            name: newClient.name.trim(),
            phone: String(newClient.phone).trim() || null,
            email: newClient.email.trim() || null,
        });

        selectClient(client);
        showCreate.value = false;
    } catch (error) {
        if (error instanceof ApiError) {
            newClientErrors.name = error.errors?.name?.[0] ?? null;
            newClientErrors.phone = error.errors?.phone?.[0] ?? null;
            newClientErrors.email = error.errors?.email?.[0] ?? null;
            formError.value = error.message;
        } else {
            formError.value = 'تعذر حفظ العميل.';
        }
    } finally {
        creatingClient.value = false;
    }
}

let itemKey = 0;
const items = reactive([]);
const contentTimers = {};

function addItem(type) {
    items.push({
        key: (itemKey += 1),
        type,
        search: '',
        name: '',
        product_id: null,
        status: null,
        qty: 1,
        unit_price: 0,
        discount: 0,
        results: [],
        showResults: false,
        searching: false,
        creating: false,
    });
}

function removeItem(index) {
    const item = items[index];
    if (item) {
        clearTimeout(contentTimers[item.key]);
        delete contentTimers[item.key];
    }
    items.splice(index, 1);
}

function onItemSearchInput(item) {
    item.product_id = null;
    item.name = '';
    item.status = null;

    if (item.type === 'other') {
        item.results = [];
        item.showResults = false;
        return;
    }

    clearTimeout(contentTimers[item.key]);
    contentTimers[item.key] = setTimeout(async () => {
        const query = String(item.search ?? '').trim();
        if (!query) {
            item.results = [];
            item.showResults = false;
            return;
        }

        item.searching = true;
        item.showResults = true;
        try {
            item.results = await ordersStore.searchContent(item.type, query);
        } catch {
            item.results = [];
        } finally {
            item.searching = false;
        }
    }, 250);
}

function selectContent(item, product) {
    item.product_id = product.product_id;
    item.name = product.name;
    item.search = product.name;
    item.unit_price = Number(product.unit_price) || 0;
    item.status = product.status ?? null;
    item.results = [];
    item.showResults = false;
}

async function useAsNewContent(item) {
    const title = String(item.search ?? '').trim();

    if (!title || item.type === 'other' || item.creating) {
        return;
    }

    item.creating = true;
    formError.value = null;

    try {
        const content = await ordersStore.createDraftContent({
            type: item.type,
            title,
            unit_price: Number(item.unit_price) || 0,
        });

        selectContent(item, content);
        item.status = content.status ?? 'draft';
    } catch (error) {
        if (error instanceof ApiError) {
            formError.value = Object.values(error.errors || {}).flat()[0] || error.message || 'تعذر إنشاء العنصر.';
        } else {
            formError.value = 'تعذر إنشاء العنصر.';
        }
    } finally {
        item.creating = false;
    }
}

async function syncDraftContentPrice(item) {
    if (item.type === 'other' || item.status !== 'draft' || !item.product_id || !item.name) {
        return;
    }

    const unitPrice = Number(item.unit_price) || 0;

    if (unitPrice <= 0) {
        return;
    }

    try {
        await ordersStore.createDraftContent({
            type: item.type,
            title: String(item.name).trim(),
            unit_price: unitPrice,
        });
    } catch {
        // Price sync is best-effort; order submit still uses the line price.
    }
}

function onDraftUnitPriceChange(item) {
    clearTimeout(contentTimers[`price-${item.key}`]);
    contentTimers[`price-${item.key}`] = setTimeout(() => {
        syncDraftContentPrice(item);
    }, 400);
}

function clearSelectedContent(item) {
    item.product_id = null;
    item.name = '';
    item.status = null;
    item.search = '';
    item.results = [];
    item.showResults = false;
}

function lineTotal(item) {
    return Math.max(0, (Number(item.qty) || 0) * (Number(item.unit_price) || 0) - (Number(item.discount) || 0));
}

const totals = computed(() => {
    const subtotal = items.reduce((sum, item) => sum + (Number(item.qty) || 0) * (Number(item.unit_price) || 0), 0);
    const discount = items.reduce((sum, item) => sum + (Number(item.discount) || 0), 0);
    const tax = 0;
    return { subtotal, discount, tax, grand: Math.max(0, subtotal - discount + tax) };
});

function resetForm() {
    items.splice(0);
    selectWalking();
    formError.value = null;
}

async function submit() {
    formError.value = null;

    if (items.length === 0) {
        formError.value = 'أضف عنصراً واحداً على الأقل.';
        return;
    }

    for (const item of items) {
        if (item.type === 'other') {
            if (!String(item.name ?? item.search ?? '').trim()) {
                formError.value = 'أكمل وصف العنصر المخصص قبل التأكيد.';
                return;
            }
        } else if (!item.product_id || !String(item.name ?? '').trim()) {
            formError.value = 'اختر عنصراً من النتائج أو أضفه كعنصر جديد قبل التأكيد.';
            return;
        }
    }

    submitting.value = true;

    try {
        await Promise.all(
            items
                .filter((item) => item.status === 'draft' && item.product_id)
                .map((item) => syncDraftContentPrice(item)),
        );

        const { order } = await ordersStore.createOrder({
            client_id: isWalking.value ? null : clientId.value,
            items: items.map((item) => {
                const name = item.type === 'other'
                    ? String(item.name || item.search).trim()
                    : String(item.name).trim();

                return {
                    type: item.type,
                    name,
                    product_id: item.product_id,
                    qty: Number(item.qty) || 1,
                    unit_price: Number(item.unit_price) || 0,
                    discount: Number(item.discount) || 0,
                    description: item.type === 'other' ? name : null,
                };
            }),
        });

        resetForm();
        closeModal('add-order');
        await router.push(`/orders/${order.uuid}`);
    } catch (error) {
        if (error instanceof ApiError) {
            const firstFieldError = Object.values(error.errors || {}).flat()[0];
            formError.value = firstFieldError || error.message || 'تعذر إنشاء الطلب.';
        } else {
            formError.value = 'تعذر إنشاء الطلب.';
        }
    } finally {
        submitting.value = false;
    }
}

onBeforeUnmount(() => {
    clearTimeout(clientSearchTimer);
    Object.values(contentTimers).forEach(clearTimeout);
});
</script>

<template>
    <div class="flex max-h-[75vh] flex-col">
        <div class="shrink-0 space-y-4 p-5 pb-0">
                <p v-if="formError" class="rounded-lg bg-red-50 px-3 py-2 text-sm text-red-600">{{ formError }}</p>

                <!-- Client -->
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <p class="text-sm font-semibold text-gray-500">العميل</p>
                        <Button type="button" variant="outline" label="عميل جديد" @click="openCreate">
                            <template #icon>
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" d="M12 5v14M5 12h14" />
                                </svg>
                            </template>
                        </Button>
                    </div>

                    <div v-if="clientId" class="flex items-center justify-between rounded-lg bg-gray-50 p-3">
                        <div>
                            <p class="text-sm font-bold text-gray-800">{{ selectedClient?.name }}</p>
                            <p v-if="selectedClient?.email" class="mt-1 text-xs text-gray-500">{{ selectedClient.email }}</p>
                            <p v-if="selectedClient?.phone" class="mt-0.5 text-xs text-gray-500" dir="ltr">{{ selectedClient.phone }}</p>
                        </div>
                        <button type="button" class="rounded px-2 py-1 text-xs text-red-500 hover:bg-red-50 hover:text-red-700" @click="enterSearch">تغيير</button>
                    </div>

                    <div v-else-if="isWalking" class="flex items-center justify-between rounded-lg bg-gray-50 p-3">
                        <div class="flex items-center gap-3">
                            <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-white text-gray-400 ring-1 ring-gray-100">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                    <circle cx="12" cy="8" r="4" />
                                    <path d="M4 20c0-4 4-6 8-6s8 2 8 6" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-bold text-gray-800">{{ walkingClientLabel }}</p>
                                <p class="mt-0.5 text-xs text-gray-500">الافتراضي — بدون حساب عميل</p>
                            </div>
                        </div>
                        <button type="button" class="rounded px-2 py-1 text-xs text-red-500 hover:bg-red-50 hover:text-red-700" @click="enterSearch">تغيير</button>
                    </div>

                    <div v-else class="relative">
                        <div class="relative flex-1">
                            <div class="pointer-events-none absolute bottom-0 right-0 top-0 flex items-center ps-2 text-gray-500">
                                <svg class="h-5 w-5 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                    <circle cx="11" cy="11" r="7" />
                                    <path stroke-linecap="round" d="m20 20-3-3" />
                                </svg>
                            </div>
                            <input
                                v-model="clientSearch"
                                type="text"
                                placeholder="ابحث بالاسم أو البريد أو الهاتف .."
                                class="block w-full rounded-lg border border-gray-200 py-2 ps-10 text-gray-800 placeholder:text-gray-400 focus:border-primary-500 focus:outline-none sm:text-sm"
                                @focus="showClientResults = true"
                            >
                        </div>

                        <div v-if="showClientResults" class="absolute z-50 mt-1 max-h-52 w-full overflow-y-auto rounded-lg border border-gray-200 bg-white shadow-lg">
                            <button type="button" class="w-full border-b border-gray-100 bg-gray-50/50 px-3 py-2.5 text-start hover:bg-gray-50" @click="selectWalking">
                                <p class="text-sm font-semibold text-gray-800">{{ walkingClientLabel }}</p>
                                <p class="mt-0.5 text-xs text-gray-500">بدون حساب عميل</p>
                            </button>
                            <p v-if="clientSearching" class="px-3 py-2 text-xs text-gray-400">جاري البحث…</p>
                            <button
                                v-for="client in clientResults"
                                :key="client.id"
                                type="button"
                                class="w-full border-b border-gray-50 px-3 py-2 text-start last:border-0 hover:bg-gray-50"
                                @click="selectClient(client)"
                            >
                                <p class="text-sm font-semibold text-gray-800">{{ client.name }}</p>
                                <p class="mt-0.5 text-xs text-gray-500">
                                    <span v-if="client.email">{{ client.email }}</span>
                                    <span v-if="client.phone" class="ms-2" dir="ltr">{{ client.phone }}</span>
                                </p>
                            </button>
                            <button
                                v-if="clientSearch !== '' && !clientSearching && clientResults.length === 0"
                                type="button"
                                class="w-full border-t border-gray-100 px-3 py-2.5 text-start text-sm text-primary-600 hover:bg-primary-50"
                                @click="openCreate"
                            >
                                <span class="font-semibold">إضافة "{{ clientSearch }}"</span>
                                <span class="ms-1 text-xs text-primary-500/80">كعميل جديد</span>
                            </button>
                        </div>
                    </div>
                </div>
        </div>

        <div class="relative z-30 min-h-0 flex-1 overflow-visible px-5 py-4">
                <!-- Items -->
                <Box title="العناصر" class="relative z-30 border border-gray-100 shadow-sm">
                    <template #action>
                        <Dropdown width="min-w-52 w-52" placement="top">
                            <template #trigger>
                                <button type="button" class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-sm font-medium text-gray-700 hover:bg-gray-50">
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" d="M12 5v14M5 12h14" /></svg>
                                    <span>إضافة عنصر</span>
                                    <svg class="h-4 w-4 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6" /></svg>
                                </button>
                            </template>
                            <button
                                v-for="(label, type) in addItemTypeOptions"
                                :key="type"
                                type="button"
                                class="flex w-full items-center gap-2.5 rounded-md px-3 py-2 text-start text-sm text-gray-700 hover:bg-gray-50"
                                @click="addItem(type)"
                            >
                                {{ label }}
                            </button>
                        </Dropdown>
                    </template>

                    <div class="max-h-[40vh] space-y-4 overflow-y-auto overflow-x-visible p-4">
                        <p v-if="items.length === 0" class="py-6 text-center text-sm text-gray-400">اختر نوع العنصر من القائمة لبدء إضافة الطلب.</p>

                        <div
                            v-for="(item, index) in items"
                            :key="item.key"
                            class="relative space-y-3 rounded-lg border border-gray-100 bg-gray-50 p-3"
                        >
                            <button type="button" class="absolute left-2 top-2 rounded p-1 text-red-400 hover:bg-red-50 hover:text-red-600" @click="removeItem(index)">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16M10 11v6M14 11v6M6 7l1 13a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2l1-13" /></svg>
                            </button>

                            <p class="pe-8 text-xs font-semibold text-gray-500">{{ itemTypeOptions[item.type] ?? item.type }}</p>

                            <div class="relative">
                                <textarea
                                    v-if="item.type === 'other'"
                                    v-model="item.name"
                                    rows="2"
                                    :placeholder="itemSearchPlaceholders.other"
                                    class="block w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-800 focus:border-primary-500 focus:outline-none"
                                ></textarea>
                                <template v-else>
                                    <input
                                        v-model="item.search"
                                        type="text"
                                        :placeholder="itemSearchPlaceholders[item.type] ?? 'ابحث أو أدخل الاسم ..'"
                                        class="block w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-800 focus:border-primary-500 focus:outline-none"
                                        @input="onItemSearchInput(item)"
                                        @focus="item.showResults = !item.name && String(item.search || '').trim() !== ''"
                                    >

                                    <div
                                        v-if="!item.name && item.showResults && String(item.search || '').trim() !== ''"
                                        class="absolute z-40 mt-1 max-h-48 w-full overflow-y-auto rounded-lg border border-gray-200 bg-white shadow-lg"
                                    >
                                        <p v-if="item.searching" class="px-3 py-2 text-xs text-gray-400">جاري البحث…</p>
                                        <button
                                            v-for="product in item.results"
                                            :key="`${product.product_id}-${product.name}`"
                                            type="button"
                                            class="w-full border-b border-gray-50 px-3 py-2 text-start last:border-0 hover:bg-gray-50"
                                            @click="selectContent(item, product)"
                                        >
                                            <p class="text-sm font-semibold text-gray-800">{{ product.name }}</p>
                                            <p class="mt-0.5 text-xs text-gray-500">{{ money(product.unit_price) }}</p>
                                        </button>
                                        <button
                                            type="button"
                                            class="w-full border-t border-gray-100 px-3 py-2.5 text-start text-sm text-primary-600 hover:bg-primary-50 disabled:opacity-60"
                                            :disabled="item.creating"
                                            @click="useAsNewContent(item)"
                                        >
                                            <span class="font-semibold">
                                                {{ item.creating ? 'جاري الإضافة…' : `استخدام "${item.search.trim()}" كعنصر جديد` }}
                                            </span>
                                            <span class="ms-1 text-xs text-primary-500/80">{{ itemTypeOptions[item.type] }} — مسودة</span>
                                        </button>
                                    </div>

                                    <div v-if="item.name" class="mt-1 flex items-center justify-between gap-2">
                                        <p class="text-xs text-green-600">
                                            المحدد: {{ item.name }}
                                            <span v-if="item.status === 'draft'" class="ms-1 rounded bg-amber-50 px-1.5 py-0.5 text-[10px] font-medium text-amber-700">مسودة</span>
                                        </p>
                                        <button type="button" class="text-xs text-red-500 hover:text-red-700" @click="clearSelectedContent(item)">تغيير</button>
                                    </div>
                                </template>
                            </div>

                            <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                                <div>
                                    <label class="mb-1 block text-xs text-gray-500">الكمية</label>
                                    <input v-model.number="item.qty" type="number" min="1" dir="ltr" class="block w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none">
                                </div>
                                <div>
                                    <label class="mb-1 block text-xs text-gray-500">سعر الوحدة</label>
                                    <input
                                        v-model.number="item.unit_price"
                                        type="number"
                                        min="0"
                                        step="0.01"
                                        dir="ltr"
                                        class="block w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none"
                                        @change="onDraftUnitPriceChange(item)"
                                        @blur="onDraftUnitPriceChange(item)"
                                    >
                                </div>
                                <div>
                                    <label class="mb-1 block text-xs text-gray-500">الخصم</label>
                                    <input v-model.number="item.discount" type="number" min="0" step="0.01" dir="ltr" class="block w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none">
                                </div>
                                <div>
                                    <label class="mb-1 block text-xs text-gray-500">الإجمالي</label>
                                    <div class="rounded-lg border border-gray-100 bg-white px-3 py-2 text-sm font-bold text-gray-800">{{ money(lineTotal(item)) }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </Box>
        </div>

        <div class="relative z-10 shrink-0 border-t-2 border-gray-100 bg-white">
            <div class="space-y-2 px-5 py-4">
                <div class="ms-auto max-w-sm space-y-2">
                    <div class="flex items-center justify-between text-sm text-gray-600">
                        <span>المجموع الفرعي</span>
                        <span class="font-semibold text-gray-800">{{ money(totals.subtotal) }}</span>
                    </div>
                    <div class="flex items-center justify-between text-sm text-gray-600">
                        <span>الخصومات</span>
                        <span class="font-semibold text-gray-800">{{ money(totals.discount) }}</span>
                    </div>
                    <div class="flex items-center justify-between text-sm text-gray-600">
                        <span>الضريبة</span>
                        <span class="font-semibold text-gray-800">{{ money(totals.tax) }}</span>
                    </div>
                    <div class="flex items-center justify-between border-t border-gray-100 pt-2 text-base font-bold text-gray-800">
                        <span>الإجمالي النهائي</span>
                        <span>{{ money(totals.grand) }}</span>
                    </div>
                </div>
            </div>
            <div class="flex justify-end border-t border-gray-100 px-5 py-4 shadow">
                <Button type="button" label="أنشئ الطلب" :loading="submitting" @click="submit" />
            </div>
        </div>

        <Teleport to="body">
            <div v-if="showCreate" class="fixed inset-0 z-[60] flex items-center justify-center p-4">
                <div class="fixed inset-0 bg-gray-900/60" @click="showCreate = false"></div>
                <div class="relative w-full max-w-lg rounded-xl bg-white shadow-2xl ring-1 ring-black/5">
                    <div class="flex items-center justify-between border-b border-gray-100 p-3">
                        <p class="px-1 text-sm font-semibold text-gray-600">إضافة عميل جديد</p>
                        <button type="button" class="rounded-md bg-gray-100 p-1 text-gray-400 hover:bg-gray-200" @click="showCreate = false">
                            <span class="sr-only">Close</span>
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>
                    <div class="space-y-4 p-5">
                        <Input v-model="newClient.name" name="newClientName" label="الاسم" placeholder="الاسم" :error="newClientErrors.name" />
                        <Input v-model="newClient.phone" name="newClientPhone" type="number" label="رقم الجوال" placeholder="123456789" dir="ltr" :error="newClientErrors.phone" />
                        <Input v-model="newClient.email" name="newClientEmail" type="email" label="البريد الإلكتروني" placeholder="client@email.com" dir="ltr" :error="newClientErrors.email" />
                        <div class="flex justify-end gap-2 border-t border-gray-100 pt-4">
                            <Button type="button" variant="outline" label="إلغاء" @click="showCreate = false" />
                            <Button type="button" label="حفظ" :loading="creatingClient" @click="saveNewClient" />
                        </div>
                    </div>
                </div>
            </div>
        </Teleport>
    </div>
</template>
