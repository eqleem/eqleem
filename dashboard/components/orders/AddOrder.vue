<script setup>
import { reactive, ref, computed } from 'vue';
import Box from '../ui/Box.vue';
import Button from '../ui/Button.vue';
import Input from '../ui/Input.vue';
import Dropdown from '../Dropdown.vue';
import { clients, addClient } from '../../data/clients.js';
import { addOrder, addItemTypeOptions, itemTypeOptions, itemSearchPlaceholders, walkingClientLabel, money } from '../../data/orders.js';
import { closeModal } from '../../lib/modal.js';

// Port of resources/views/admin/orders/add-order.blade.php — the booking/calendar
// logic (which needs a backend) is omitted; client selection, items and totals are kept.
const clientId = ref(null);
const isWalking = ref(true);
const selectedClient = ref(null);
const clientSearch = ref('');
const showResults = ref(false);

const clientResults = computed(() => {
    const query = clientSearch.value.trim().toLowerCase();
    if (!query) {
        return [];
    }
    return clients
        .filter((c) => c.name.toLowerCase().includes(query) || (c.email || '').toLowerCase().includes(query) || (c.phone || '').includes(query))
        .slice(0, 8);
});

function selectClient(client) {
    clientId.value = client.id;
    isWalking.value = false;
    selectedClient.value = client;
    clientSearch.value = client.name;
    showResults.value = false;
}

function selectWalking() {
    clientId.value = null;
    isWalking.value = true;
    selectedClient.value = null;
    clientSearch.value = '';
    showResults.value = false;
}

function enterSearch() {
    clientId.value = null;
    isWalking.value = false;
    selectedClient.value = null;
    clientSearch.value = '';
    showResults.value = true;
}

// Nested create-client modal.
const showCreate = ref(false);
const newClient = reactive({ name: '', phone: '', email: '' });

function openCreate() {
    newClient.name = clientSearch.value;
    newClient.phone = '';
    newClient.email = '';
    showCreate.value = true;
}

function saveNewClient() {
    if (!newClient.name || !newClient.phone) {
        return;
    }
    addClient({ name: newClient.name, phone: newClient.phone, email: newClient.email });
    selectClient(clients[0]);
    showCreate.value = false;
}

// Items.
let itemKey = 0;
const items = reactive([]);

function addItem(type) {
    items.push({ key: (itemKey += 1), type, name: '', qty: 1, unit_price: 0, discount: 0 });
}

function removeItem(index) {
    items.splice(index, 1);
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

function submit() {
    if (items.length === 0) {
        return;
    }
    addOrder({ client: isWalking.value ? null : selectedClient.value?.name ?? null, grand_total: totals.value.grand });
    items.splice(0);
    selectWalking();
    closeModal('add-order');
}
</script>

<template>
    <div>
        <div class="max-h-[75vh] overflow-y-auto">
            <div class="space-y-4 p-5">
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
                                @focus="showResults = true"
                            >
                        </div>

                        <div v-if="showResults" class="absolute z-50 mt-1 max-h-52 w-full overflow-y-auto rounded-lg border border-gray-200 bg-white shadow-lg">
                            <button type="button" class="w-full border-b border-gray-100 bg-gray-50/50 px-3 py-2.5 text-start hover:bg-gray-50" @click="selectWalking">
                                <p class="text-sm font-semibold text-gray-800">{{ walkingClientLabel }}</p>
                                <p class="mt-0.5 text-xs text-gray-500">بدون حساب عميل</p>
                            </button>
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
                                v-if="clientSearch !== '' && clientResults.length === 0"
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

                <!-- Items -->
                <Box title="العناصر" class="border border-gray-100 shadow-sm">
                    <template #action>
                        <Dropdown width="min-w-52 w-52">
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

                    <div class="space-y-4 p-4">
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

                            <textarea
                                v-if="item.type === 'other'"
                                v-model="item.name"
                                rows="2"
                                :placeholder="itemSearchPlaceholders.other"
                                class="block w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-800 focus:border-primary-500 focus:outline-none"
                            ></textarea>
                            <input
                                v-else
                                v-model="item.name"
                                type="text"
                                :placeholder="itemSearchPlaceholders[item.type] ?? 'ابحث أو أدخل الاسم ..'"
                                class="block w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-800 focus:border-primary-500 focus:outline-none"
                            >

                            <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                                <div>
                                    <label class="mb-1 block text-xs text-gray-500">الكمية</label>
                                    <input v-model.number="item.qty" type="number" min="1" dir="ltr" class="block w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none">
                                </div>
                                <div>
                                    <label class="mb-1 block text-xs text-gray-500">سعر الوحدة</label>
                                    <input v-model.number="item.unit_price" type="number" min="0" step="0.01" dir="ltr" class="block w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none">
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

                <!-- Summary -->
                <Box title="ملخص الطلب" class="border border-gray-100 shadow-sm">
                    <div class="ms-auto max-w-sm space-y-2 p-4">
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
                </Box>
            </div>
        </div>

        <div class="flex justify-end border-t-2 border-gray-100 p-5 shadow">
            <Button type="button" label="أنشئ الطلب" @click="submit" />
        </div>

        <!-- Nested create-client modal -->
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
                        <Input v-model="newClient.name" name="newClientName" label="الاسم" placeholder="الاسم" />
                        <Input v-model="newClient.phone" name="newClientPhone" type="number" label="رقم الجوال" placeholder="123456789" dir="ltr" />
                        <Input v-model="newClient.email" name="newClientEmail" type="email" label="البريد الإلكتروني" placeholder="client@email.com" dir="ltr" />
                        <div class="flex justify-end gap-2 border-t border-gray-100 pt-4">
                            <Button type="button" variant="outline" label="إلغاء" @click="showCreate = false" />
                            <Button type="button" label="حفظ" @click="saveNewClient" />
                        </div>
                    </div>
                </div>
            </div>
        </Teleport>
    </div>
</template>
