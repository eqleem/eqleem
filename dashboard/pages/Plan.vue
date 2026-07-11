<script setup>
import { nextTick, onBeforeUnmount, onMounted, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import Container from '../components/ui/Container.vue';
import MainBox from '../components/ui/MainBox.vue';
import Button from '../components/ui/Button.vue';
import Icon from '../components/ui/Icon.vue';
import Alert from '../components/ui/Alert.vue';
import { notifySuccess, notifyError } from '../lib/notify.js';
import Modal from '../components/ui/Modal.vue';
import { openModal } from '../lib/modal.js';
import { loadDashboardContext } from '../stores/session.js';
import {
    clearCheckout,
    fetchPlans,
    loadCheckout,
    subscribeFreePlan,
    usePlanStore,
} from '../stores/plan.js';

const route = useRoute();
const router = useRouter();
const { state: planState } = usePlanStore();

const activeFaq = ref(1);
const moyasarMount = ref(null);

function onCheckoutModalClosed(event) {
    if (event.detail?.modal && event.detail.modal !== 'plan-checkout') {
        return;
    }

    if (moyasarMount.value) {
        moyasarMount.value.innerHTML = '';
    }

    clearCheckout();
}

async function handlePaymentReturn() {
    const status = route.query.status;

    if (status === 'success') {
        notifySuccess('تم تفعيل الباقة بنجاح.');
        await loadDashboardContext();
        await fetchPlans(planState.billingPeriod);
    } else if (status === 'error') {
        notifyError('عملية الدفع فشلت، الرجاء المحاولة مرة أخرى.');
    }

    if (status) {
        await router.replace({ name: 'plan' });
    }
}

async function changeBillingPeriod(period) {
    if (planState.billingPeriod === period || planState.loading) {
        return;
    }

    await fetchPlans(period);
}

async function handleSubscribeFree() {
    try {
        const message = await subscribeFreePlan();
        notifySuccess(message);
        await loadDashboardContext();
    } catch {
        // surfaced via planState.error
    }
}

async function openPaidCheckout(plan) {
    clearCheckout();
    openModal('plan-checkout');

    try {
        await loadCheckout(plan.id);
        await nextTick();
        mountMoyasar();
    } catch {
        // surfaced via planState.checkoutError
    }
}

function mountMoyasar() {
    const mount = moyasarMount.value;
    const config = planState.checkoutConfig;

    if (!mount || !config || !window.Moyasar) {
        return;
    }

    mount.innerHTML = '';

    window.Moyasar.init({
        element: mount,
        amount: config.amount,
        currency: config.currency,
        description: config.description,
        publishable_api_key: config.publishable_api_key,
        callback_url: config.callback_url,
        methods: config.methods ?? ['creditcard'],
        supported_networks: config.supported_networks ?? ['mada', 'visa', 'mastercard'],
        metadata: config.metadata ?? {},
    });
}

onMounted(async () => {
    window.addEventListener('closemodal', onCheckoutModalClosed);

    const initialPeriod = route.query.yearly === '1' || route.query.yearly === 'true' ? 'yearly' : 'monthly';

    await handlePaymentReturn();
    await fetchPlans(initialPeriod);
});

onBeforeUnmount(() => {
    window.removeEventListener('closemodal', onCheckoutModalClosed);
});
</script>

<template>
    <Container class="!pb-24">
        <MainBox title="إدارة الاشتراك" subtitle="اختر الباقة المناسبة لصفحتك.">
            <template #icon>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-600" viewBox="0 0 24 24" fill="none">
                    <path d="M12 12a5 5 0 1 0 0-10 5 5 0 0 0 0 10Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                    <path opacity=".4" d="M20.59 22c0-3.87-3.85-7-8.59-7s-8.59 3.13-8.59 7" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
            </template>
        </MainBox>

        <Alert v-if="planState.error" class="mt-6" color="red" :text="planState.error" />

        <div class="mt-10 flex flex-col items-center gap-3">
            <div class="flex items-center">
                <div class="inline-flex rounded-xl bg-stone-300/60 p-1">
                    <button
                        type="button"
                        class="rounded-lg px-6 py-2.5 text-sm font-semibold transition"
                        :class="planState.billingPeriod === 'monthly' ? 'bg-white text-stone-900 shadow-sm' : 'text-stone-500 hover:text-stone-800'"
                        :disabled="planState.loading"
                        @click="changeBillingPeriod('monthly')"
                    >
                        شهري
                    </button>
                    <button
                        type="button"
                        class="rounded-lg px-6 py-2.5 text-sm font-semibold transition"
                        :class="planState.billingPeriod === 'yearly' ? 'bg-white text-stone-900 shadow-sm' : 'text-stone-500 hover:text-stone-800'"
                        :disabled="planState.loading"
                        @click="changeBillingPeriod('yearly')"
                    >
                        سنوي
                    </button>
                </div>
                <div class="pointer-events-none ms-1.5 flex flex-col items-start pt-1">
                    <p class="ms-3 -rotate-3 whitespace-nowrap text-[10px] font-bold leading-none text-emerald-700">خصم شهرين</p>
                </div>
            </div>
        </div>

        <div v-if="planState.loading && !planState.plans.length" class="mt-10 flex items-center justify-center"><LoadingSpinner /></div>

        <div v-else class="mt-10 grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-3">
            <div
                v-for="plan in planState.plans"
                :key="plan.id"
                class="relative rounded-2xl"
                :class="plan.highlighted ? 'bg-gradient-to-b from-orange-400 via-rose-500 to-violet-600 p-0.5' : plan.free ? 'bg-stone-100' : 'bg-white ring-1 ring-stone-200'"
            >
                <div class="flex h-full flex-col p-6" :class="plan.highlighted ? 'rounded-[calc(1rem-1px)] bg-white' : ''">
                    <span v-if="plan.current" class="absolute -top-3 right-4 z-10 rounded-full bg-stone-900 px-3 py-1 text-xs font-medium text-white">باقتك الحالية</span>
                    <span v-if="plan.featured" class="absolute -top-3 left-4 z-10 rounded-full bg-amber-500 px-3 py-1 text-xs font-medium text-white">الأوفر</span>

                    <div class="mb-5 flex items-start justify-between gap-4">
                        <div>
                            <span v-if="plan.free" class="inline-flex rounded-md border border-stone-900 bg-white px-2.5 py-1 text-sm font-bold text-stone-900">{{ plan.title }}</span>
                            <h3 v-else class="text-lg font-bold" :class="plan.accent_class">{{ plan.title }}</h3>
                        </div>
                        <div class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-stone-100 text-stone-600"><Icon name="user" class="h-5 w-5" /></div>
                    </div>

                    <p class="mb-6 text-sm leading-relaxed text-stone-500">{{ plan.description }}</p>

                    <div class="mb-6">
                        <template v-if="plan.free">
                            <p class="text-4xl font-bold tracking-tight text-stone-900">مجاناً</p>
                            <p class="mt-1 text-sm text-stone-400">بدون حد زمني</p>
                        </template>
                        <template v-else>
                            <p class="text-4xl font-bold tracking-tight text-stone-900"><Money :formatted="plan.price_formatted" /></p>
                            <p class="mt-1 text-sm text-stone-400">{{ plan.interval_label }}</p>
                        </template>
                    </div>

                    <div class="mb-6">
                        <Button v-if="plan.current" variant="outline" disabled label="مفعّلة" class="h-11 w-full !rounded-lg" />
                        <Button
                            v-else-if="plan.free"
                            variant="outline"
                            label="ابدأ مجاناً"
                            class="h-11 w-full !rounded-lg !border-stone-900 !bg-white !text-stone-900 hover:!bg-stone-50"
                            :disabled="planState.subscribingFree"
                            @click="handleSubscribeFree"
                        />
                        <Button
                            v-else
                            label="اشترك الآن"
                            class="h-11 w-full !rounded-lg !bg-stone-900 !text-white hover:!bg-stone-800"
                            @click="openPaidCheckout(plan)"
                        />
                    </div>

                    <div class="mb-6 h-px bg-[repeating-linear-gradient(to_right,#d6d3d1_0,#d6d3d1_6px,transparent_6px,transparent_11px)]"></div>

                    <ul class="grow space-y-3">
                        <li v-for="feature in plan.features" :key="feature" class="flex items-start gap-2.5 text-sm text-stone-700">
                            <Icon name="check" class="mt-0.5 h-4 w-4 shrink-0 text-stone-900" />
                            <span>{{ feature }}</span>
                        </li>
                    </ul>

                    <div class="mt-8 border-t border-stone-200 pt-6">
                        <div class="flex items-start gap-3">
                            <div class="flex size-9 shrink-0 items-center justify-center rounded-full bg-stone-200 text-stone-600"><Icon name="user" class="h-4 w-4" /></div>
                            <div>
                                <p class="text-sm font-semibold text-stone-900">{{ plan.audience.title }}</p>
                                <p class="mt-1 text-xs leading-relaxed text-stone-500">{{ plan.audience.description }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <section class="mt-20">
            <div class="mx-auto max-w-3xl text-center">
                <div class="inline-flex size-11 items-center justify-center rounded-xl bg-stone-100 text-stone-600"><Icon name="info" class="h-5 w-5" /></div>
                <h2 class="mt-4 text-2xl font-bold text-stone-900">الأسئلة المتكررة</h2>
                <p class="mt-2 text-sm leading-relaxed text-stone-500">إجابات سريعة عن الاشتراكات والباقات.</p>
            </div>

            <div class="mx-auto mt-10 max-w-3xl overflow-hidden rounded-2xl bg-white ring-1 ring-stone-200">
                <div v-for="(faq, index) in planState.faqs" :key="faq.question" :class="{ 'border-b border-stone-200': index < planState.faqs.length - 1 }">
                    <button
                        type="button"
                        class="flex w-full items-center justify-between gap-4 px-6 py-5 text-start text-sm font-semibold text-stone-900 transition hover:bg-stone-50 sm:px-7"
                        @click="activeFaq = activeFaq === index + 1 ? null : index + 1"
                    >
                        <span>{{ faq.question }}</span>
                        <span class="flex size-8 shrink-0 items-center justify-center rounded-lg bg-stone-100 text-stone-500 transition" :class="{ 'rotate-180': activeFaq === index + 1 }">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6" /></svg>
                        </span>
                    </button>
                    <div v-show="activeFaq === index + 1" class="border-t border-stone-100 px-6 pb-6 pt-4 sm:px-7">
                        <p class="text-sm leading-relaxed text-stone-600">{{ faq.answer }}</p>
                    </div>
                </div>
            </div>
        </section>

        <Modal
            :title="planState.checkoutPlan ? `إتمام الدفع — ${planState.checkoutPlan.title}` : 'إتمام الدفع'"
            size="lg"
            name="plan-checkout"
        >
            <div class="p-4">
                <div v-if="planState.checkoutLoading" class="flex items-center justify-center py-8"><LoadingSpinner /></div>
                <Alert v-else-if="planState.checkoutError" color="red" :text="planState.checkoutError" />
                <template v-else-if="planState.checkoutPlan">
                    <p class="text-sm text-stone-500">
                        <Money :formatted="planState.checkoutPlan.price_formatted" class="inline-flex" /> — {{ planState.checkoutPlan.interval_label }}
                    </p>
                    <div ref="moyasarMount" class="mt-4 min-h-[280px]"></div>
                </template>
            </div>
        </Modal>
    </Container>
</template>
