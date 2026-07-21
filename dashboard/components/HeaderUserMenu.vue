<script setup>
import { computed, onMounted, ref } from 'vue';
import BrandMark from './ui/BrandMark.vue';
import Button from './ui/Button.vue';
import Icon from './ui/Icon.vue';
import Input from './ui/Input.vue';
import Modal from './ui/Modal.vue';
import { api, ApiError } from '../lib/api.js';
import { openModal, closeModal } from '../lib/modal.js';
import { notifyApiError, notifyApiSuccess } from '../lib/notify.js';
import { useSession } from '../stores/session.js';

const props = defineProps({
    variant: { type: String, default: 'compact' },
    userName: { type: String, default: '' },
    userEmail: { type: String, default: '' },
    userPhone: { type: String, default: '' },
    userImage: { type: String, default: '' },
    tenantName: { type: String, default: '' },
    tenantLogo: { type: String, default: null },
    tenantBrandMark: { type: Object, default: null },
    tenantPlan: { type: String, default: '' },
    tenantUrl: { type: String, default: '#' },
    appName: { type: String, default: '' },
    homeUrl: { type: String, default: '/' },
    logoutUrl: { type: String, default: '/logout' },
});

const emit = defineEmits(['navigate']);

const { tenant } = useSession();

const tenants = ref([]);
const loadingTenants = ref(false);
const pagesOpen = ref(false);
const creating = ref(false);
const newPageName = ref('');
const nameError = ref(null);

const currentTenantId = computed(() => tenant.value?.id ?? null);

const createModalName = 'create-user-tenant';

const linkClass = {
    compact: 'flex cursor-pointer items-center gap-x-2 rounded bg-stone-100 p-1.5 hover:bg-stone-200',
    panel: 'flex cursor-pointer items-center gap-x-3 rounded-xl px-3 py-2.5 text-sm text-stone-700 transition hover:bg-white',
};

function onNavigate() {
    emit('navigate');
}

async function loadTenants() {
    if (loadingTenants.value) {
        return;
    }

    loadingTenants.value = true;

    try {
        const payload = await api('/tenants');
        tenants.value = payload?.data ?? [];
    } catch (error) {
        notifyApiError(error, 'تعذر تحميل صفحاتك.');
    } finally {
        loadingTenants.value = false;
    }
}

function togglePages(event) {
    event?.stopPropagation?.();
    pagesOpen.value = !pagesOpen.value;

    if (pagesOpen.value && tenants.value.length === 0) {
        loadTenants();
    }
}

function openCreateModal(event) {
    event?.stopPropagation?.();
    newPageName.value = '';
    nameError.value = null;
    openModal(createModalName);
}

async function switchTenant(page) {
    if (!page?.id || page.id === currentTenantId.value) {
        onNavigate();
        return;
    }

    try {
        await api(`/tenants/${page.id}/switch`, { method: 'POST' });
        onNavigate();
        window.location.assign('/dashboard');
    } catch (error) {
        notifyApiError(error, 'تعذر التبديل إلى الصفحة.');
    }
}

async function createTenant() {
    const name = newPageName.value.trim();
    nameError.value = null;

    if (name.length < 2) {
        nameError.value = 'اسم الصفحة مطلوب (حرفان على الأقل).';
        return;
    }

    creating.value = true;

    try {
        const payload = await api('/tenants', {
            method: 'POST',
            body: { name },
        });

        notifyApiSuccess(payload, 'تم إنشاء الصفحة بنجاح.');
        closeModal(createModalName);
        onNavigate();
        window.location.assign('/dashboard');
    } catch (error) {
        if (error instanceof ApiError && error.errors?.name?.[0]) {
            nameError.value = error.errors.name[0];
        }

        notifyApiError(error, 'تعذر إنشاء الصفحة.');
    } finally {
        creating.value = false;
    }
}

onMounted(() => {
    if (props.variant === 'panel') {
        pagesOpen.value = true;
        loadTenants();
    }
});
</script>

<template>
    <div v-if="variant === 'panel'" class="flex min-h-full flex-1 flex-col">
        <div class="px-4 pb-5 pt-12">
            <RouterLink
                to="/plan"
                class="flex cursor-pointer items-center justify-between gap-3 rounded-xl transition hover:bg-stone-50"
                aria-label="إدارة الباقة"
                @click="onNavigate"
            >
                <div class="flex min-w-0 items-center gap-3">
                    <BrandMark
                        :mark="tenantBrandMark"
                        :url="tenantLogo"
                        :alt="tenantName"
                        size-class="size-11 rounded-xl"
                        icon-class="text-2xl leading-none"
                        img-class="rounded-xl object-cover"
                    />
                    <div class="min-w-0">
                        <p class="truncate font-semibold text-stone-900">{{ tenantName }}</p>
                        <p class="mt-0.5 truncate text-xs text-stone-500">باقة {{ tenantPlan }}</p>
                    </div>
                </div>
                <span class="shrink-0 rounded-lg p-2 text-stone-400">
                    <Icon name="settings" class="size-5" />
                </span>
            </RouterLink>

            <a
                :href="tenantUrl"
                target="_blank"
                rel="noopener noreferrer"
                class="mt-5 flex cursor-pointer items-center justify-between gap-3 rounded-xl bg-green-600 px-4 py-3.5 text-white shadow-sm transition hover:bg-green-500"
                @click="onNavigate"
            >
                <div class="min-w-0">
                    <p class="text-sm font-medium">معاينة الصفحة</p>
                    <p class="truncate text-xs text-green-100">معاينة صفحتك في تبويب جديد</p>
                </div>
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    class="h-5 w-5 shrink-0 ltr:rotate-90"
                    viewBox="0 0 24 24"
                    stroke-width="2"
                    stroke="currentColor"
                    fill="none"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                >
                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                    <path d="M7 7l10 10" />
                    <path d="M16 7l-9 0l0 9" />
                </svg>
            </a>
        </div>

        <div class="mx-4 border-t border-stone-200"></div>

        <RouterLink
            to="/account"
            class="mx-2 flex cursor-pointer items-center justify-between gap-3 rounded-xl px-2 py-5 transition hover:bg-stone-50"
            aria-label="إدارة الحساب"
            @click="onNavigate"
        >
            <div class="flex min-w-0 items-center gap-3">
                <img :src="userImage" alt="" class="size-11 rounded-full object-cover ring-2 ring-primary-100">
                <div class="min-w-0">
                    <p class="truncate text-sm font-semibold text-stone-900">{{ userName || '…' }}</p>
                    <p class="truncate text-xs text-stone-500">{{ userEmail || userPhone }}</p>
                </div>
            </div>
            <span class="shrink-0 rounded-lg p-2 text-stone-400">
                <Icon name="settings" class="size-5" />
            </span>
        </RouterLink>

        <div class="mx-4 border-t border-stone-200"></div>

        <div class="px-4 py-4">
            <div class="mb-2 flex items-center justify-between gap-2">
                <p class="text-xs font-semibold text-stone-500">صفحاتي</p>
                <button
                    type="button"
                    class="inline-flex cursor-pointer items-center gap-1 rounded-lg px-2 py-1 text-xs font-medium text-primary-700 transition hover:bg-primary-50"
                    @click="openCreateModal"
                >
                    <Icon name="plus" class="size-3.5" />
                    جديدة
                </button>
            </div>

            <div v-if="loadingTenants" class="py-3 text-center text-xs text-stone-400">
                <iconify-icon icon="hugeicons:spinner-1-linear" class="size-4 animate-spin"></iconify-icon>
            </div>

            <ul v-else class="max-h-48 space-y-1 overflow-y-auto">
                <li v-for="page in tenants" :key="page.id">
                    <button
                        type="button"
                        class="flex w-full cursor-pointer items-center gap-2 rounded-xl px-3 py-2.5 text-start text-sm transition hover:bg-stone-50"
                        :class="page.id === currentTenantId ? 'bg-primary-50 text-primary-800' : 'text-stone-700'"
                        @click="switchTenant(page)"
                    >
                        <BrandMark
                            :mark="page.brand_mark"
                            :url="page.logo"
                            :alt="page.name"
                            size-class="size-8 rounded-lg"
                            icon-class="text-base leading-none"
                            img-class="rounded-lg object-cover"
                        />
                        <span class="min-w-0 flex-1 truncate font-medium">{{ page.name }}</span>
                        <Icon
                            v-if="page.id === currentTenantId"
                            name="check"
                            class="size-4 shrink-0 text-primary-600"
                        />
                    </button>
                </li>
            </ul>
        </div>

        <div class="mt-auto space-y-2 px-4 pb-4">
            <a
                :href="homeUrl"
                class="flex cursor-pointer items-center gap-2 rounded-xl px-3 py-2 text-sm text-stone-500 transition hover:bg-stone-50 hover:text-stone-800"
                @click="onNavigate"
            >
                <img :src="'/assets/images/logo-shape-black.webp'" alt="" class="size-5 object-contain">
                {{ appName }}
            </a>
            <a
                :href="logoutUrl"
                class="flex cursor-pointer items-center gap-2 rounded-xl bg-red-50 px-3 py-2.5 text-sm text-red-600 transition hover:bg-red-100"
                @click="onNavigate"
            >
                <Icon name="logout" class="size-5" />
                <span>تسجيل الخروج</span>
            </a>
        </div>
    </div>

    <div v-else class="space-y-1">
        <div class="truncate p-3">
            <p>{{ userName || '…' }}</p>
            <p class="opacity-50">{{ userEmail || userPhone }}</p>
        </div>

        <RouterLink to="/account" :class="linkClass.compact" @click="onNavigate">
            <Icon name="user" class="size-5 shrink-0" />
            إدارة الحساب
        </RouterLink>

        <RouterLink to="/plan" :class="linkClass.compact" @click="onNavigate">
            <Icon name="coin" class="size-5 shrink-0" />
            إدارة الاشتراك
        </RouterLink>

        <div class="space-y-1">
            <button
                type="button"
                :class="linkClass.compact"
                class="w-full cursor-pointer"
                :aria-expanded="pagesOpen"
                @click.stop="togglePages"
            >
                <Icon name="store" class="size-5 shrink-0" />
                <span class="flex-1 text-start">صفحاتي</span>
                <Icon
                    name="chevron-down"
                    class="size-4 shrink-0 opacity-50 transition"
                    :class="pagesOpen ? 'rotate-180' : ''"
                />
            </button>

            <div v-if="pagesOpen" class="space-y-1 rounded-md bg-stone-50 p-1">
                <div v-if="loadingTenants" class="px-2 py-2 text-center text-xs text-stone-400" @click.stop>
                    <!-- <iconify-icon icon="hugeicons:spinner-1-linear" class="size-4 animate-spin"></iconify-icon> -->
                    <svg xmlns="http://www.w3.org/2000/svg" class="size-4 animate-spin text-stone-600" viewBox="0 0 50 50">
                        <path d="M0 0h50v50H0z" fill="none" />
                        <circle cx="25" cy="10" r="2" fill="currentColor" />
                        <circle cx="25" cy="40" r="2" fill="currentColor" opacity=".3" />
                        <circle cx="32.5" cy="12" r="2" fill="currentColor" opacity=".3" />
                        <circle cx="17.5" cy="38" r="2" fill="currentColor" opacity=".3" />
                        <circle cx="17.5" cy="12" r="2" fill="currentColor" opacity=".93" />
                        <circle cx="32.5" cy="38" r="2" fill="currentColor" opacity=".3" />
                        <circle cx="10" cy="25" r="2" fill="currentColor" opacity=".65" />
                        <circle cx="40" cy="25" r="2" fill="currentColor" opacity=".3" />
                        <circle cx="12" cy="17.5" r="2" fill="currentColor" opacity=".86" />
                        <circle cx="38" cy="32.5" r="2" fill="currentColor" opacity=".3" />
                        <circle cx="12" cy="32.5" r="2" fill="currentColor" opacity=".44" />
                        <circle cx="38" cy="17.5" r="2" fill="currentColor" opacity=".3" />
                    </svg>

                </div>

                <template v-else>
                    <button
                        v-for="page in tenants"
                        :key="page.id"
                        type="button"
                        class="flex w-full cursor-pointer items-center gap-2 rounded px-2 py-1.5 text-start text-sm transition  "
                        :class="page.id === currentTenantId ? 'bg-primary-100 hover:bg-primary-100 font-medium text-primary-800' : 'text-stone-700  hover:bg-primary-100'"
                        @click="switchTenant(page)"
                    >
                        <BrandMark
                            :mark="page.brand_mark"
                            :url="page.logo"
                            :alt="page.name"
                            size-class="size-6 rounded"
                            icon-class="text-sm leading-none"
                            img-class="rounded object-cover"
                        />
                        <span class="min-w-0 flex-1 truncate">{{ page.name }}</span>
                        <Icon
                            v-if="page.id === currentTenantId"
                            name="check"
                            class="size-3.5 shrink-0 text-primary-600"
                        />
                    </button>
                </template>

                <button
                    type="button"
                    class="flex w-full cursor-pointer items-center gap-x-2 rounded px-2 py-1.5 text-sm text-primary-700 transition hover:bg-primary-100"
                    @click="openCreateModal"
                >
                    <Icon name="plus" class="size-4 shrink-0" />
                    إضافة صفحة جديدة
                </button>
            </div>
        </div>

        <a :href="homeUrl" :class="linkClass.compact" @click="onNavigate">
            <img :src="'/assets/images/logo-shape-black.webp'" alt="" class="size-5 shrink-0 object-contain">
            {{ appName }}
        </a>

        <a :href="logoutUrl" :class="linkClass.compact" @click="onNavigate">
            <Icon name="logout" class="size-5 shrink-0" />
            <span>تسجيل الخروج</span>
        </a>
    </div>

    <Modal :name="createModalName" title="إضافة صفحة جديدة" size="sm">
        <form class="space-y-4 p-4" @submit.prevent="createTenant">
            <Input
                v-model="newPageName"
                name="tenant_name"
                label="اسم الصفحة"
                block
                placeholder="مثال: متجري"
                :error="nameError"
            />

            <div class="flex items-center justify-end gap-2">
                <Button
                    type="button"
                    variant="ghost"
                    label="إلغاء"
                    :disabled="creating"
                    @click="closeModal(createModalName)"
                />
                <Button
                    type="submit"
                    variant="primary"
                    label="إنشاء الصفحة"
                    :loading="creating"
                    :disabled="creating"
                />
            </div>
        </form>
    </Modal>
</template>
