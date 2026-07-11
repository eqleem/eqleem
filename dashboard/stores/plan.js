import { reactive } from 'vue';
import { api, ApiError } from '../lib/api.js';

const state = reactive({
    loading: false,
    subscribingFree: false,
    checkoutLoading: false,
    billingPeriod: 'monthly',
    currentPlanId: null,
    plans: [],
    faqs: [],
    appName: 'Eqleem',
    error: null,
    checkoutError: null,
    checkoutPlan: null,
    checkoutConfig: null,
});

export function usePlanStore() {
    return { state };
}

export async function fetchPlans(billingPeriod = state.billingPeriod) {
    state.loading = true;
    state.error = null;
    state.billingPeriod = billingPeriod;

    try {
        const payload = await api(`/plans?billing_period=${billingPeriod}`);
        const data = payload?.data ?? payload;

        state.plans = data.plans ?? [];
        state.faqs = data.faqs ?? [];
        state.currentPlanId = data.current_plan_id ?? null;
        state.appName = data.app_name ?? state.appName;
    } catch (error) {
        state.error = error instanceof ApiError ? error.message : 'تعذّر تحميل الباقات.';
    } finally {
        state.loading = false;
    }
}

export async function subscribeFreePlan() {
    state.subscribingFree = true;
    state.error = null;

    try {
        const payload = await api('/plans/subscribe-free', { method: 'POST' });
        const data = payload?.data ?? payload;

        state.plans = data.plans ?? [];
        state.faqs = data.faqs ?? [];
        state.currentPlanId = data.current_plan_id ?? null;

        return payload?.message ?? 'تم تفعيل الباقة المجانية.';
    } catch (error) {
        const message = error instanceof ApiError ? error.message : 'تعذّر تفعيل الباقة المجانية.';
        state.error = message;
        throw error;
    } finally {
        state.subscribingFree = false;
    }
}

export async function loadCheckout(planId) {
    state.checkoutLoading = true;
    state.checkoutError = null;
    state.checkoutPlan = null;
    state.checkoutConfig = null;

    try {
        const payload = await api(`/plans/${planId}/checkout`);
        const data = payload?.data ?? payload;

        state.checkoutPlan = data.plan ?? null;
        state.checkoutConfig = data.checkout ?? null;
    } catch (error) {
        state.checkoutError = error instanceof ApiError ? error.message : 'تعذّر تحميل نموذج الدفع.';
        throw error;
    } finally {
        state.checkoutLoading = false;
    }
}

export function clearCheckout() {
    state.checkoutError = null;
    state.checkoutPlan = null;
    state.checkoutConfig = null;
}
