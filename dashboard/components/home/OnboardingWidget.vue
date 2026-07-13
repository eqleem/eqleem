<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import { storeToRefs } from 'pinia';
import Modal from '../ui/Modal.vue';
import CompletionVerification from './CompletionVerification.vue';
import OnboardingWizardPanel from './OnboardingWizardPanel.vue';
import SettingsPaymentOptions from '../../pages/settings/PaymentOptions.vue';
import SettingsShippingOptions from '../../pages/settings/ShippingOptions.vue';
import { useOnboardingStore } from '../../stores/onboarding.js';
import { openModal, closeModal } from '../../lib/modal.js';
import Button from '../ui/Button.vue';

const props = defineProps({
    /** Light translucent styles for use inside the dark WelcomeWidget. */
    onPrimary: { type: Boolean, default: false },
});

const store = useOnboardingStore();
const {
    percentage,
    completedSteps,
    totalSteps,
    currentStep,
    loading,
    loaded,
    completed,
} = storeToRefs(store);

const editorStep = ref('business');

const showBar = computed(() => loaded.value || (loading.value && !loaded.value));

const ringStyle = computed(() => {
    if (props.onPrimary) {
        const fill = completed.value ? '#34d399' : '#fbbf24';

        return {
            background: `conic-gradient(${fill} ${percentage.value * 3.6}deg, rgba(255,255,255,0.25) 0deg)`,
        };
    }

    const fill = completed.value ? '#10b981' : '#f59e0b';
    const track = completed.value ? '#d1fae5' : '#fef3c7';

    return {
        background: `conic-gradient(${fill} ${percentage.value * 3.6}deg, ${track} 0deg)`,
    };
});

const barClass = computed(() => {
    if (props.onPrimary) {
        return completed.value
            ? 'bg-white/10 text-white hover:bg-white/15'
            : 'bg-white/10 text-white hover:bg-white/15';
    }

    return completed.value
        ? 'bg-emerald-500/10 text-emerald-800 hover:bg-emerald-500/15'
        : 'bg-amber-500/10 text-amber-900 hover:bg-amber-500/15';
});

const progressClass = computed(() => {
    if (props.onPrimary) {
        return completed.value ? 'bg-emerald-300' : 'bg-amber-300';
    }

    return completed.value ? 'bg-emerald-500' : 'bg-amber-500';
});

const progressTrackClass = computed(() => (
    props.onPrimary
        ? 'bg-white/20'
        : (completed.value ? 'bg-emerald-500/20' : 'bg-amber-500/20')
));

const titleText = computed(() => (
    completed.value ? 'صفحتك جاهزة بالكامل' : 'أكمل إعداد صفحتك'
));

const hintText = computed(() => (
    completed.value ? 'اضغط للتعديل' : 'اضغط للمتابعة'
));

onMounted(async () => {
    await store.fetchOnboarding();
    window.addEventListener('closemodal', onOrdersModalClosed);
});

onBeforeUnmount(() => {
    window.removeEventListener('closemodal', onOrdersModalClosed);
});

function onOrdersModalClosed(event) {
    const modal = event.detail?.modal;

    if (!modal || ['onboarding-payment', 'onboarding-shipping', 'onboarding-verification', 'home-step-verification'].includes(modal)) {
        store.refreshQuiet();
    }
}

function openEditor() {
    editorStep.value = completed.value
        ? 'business'
        : (currentStep.value || 'business');
    openModal('onboarding-editor');
}

function closeOrdersSetupModal(name) {
    closeModal(name);
    store.refreshQuiet();
}

function onVerificationSaved() {
    closeModal('onboarding-verification');
    store.refreshQuiet();
}

function onOnboardingFinished() {
    closeModal('onboarding-editor');
    store.refreshQuiet();
}
</script>

<template>
    <div v-if="showBar" :class="onPrimary ? '' : 'mb-2'" dir="rtl">
        <button
            type="button"
            class="group flex w-full cursor-pointer flex-col gap-1.5 rounded-lg px-3 py-2 text-start text-sm transition"
            :class="[barClass, { 'animate-pulse opacity-80': loading && !loaded }]"
            :disabled="loading && !loaded"
            @click="openEditor"
        >
            <span class="flex w-full items-center gap-2">
                <span
                    class="relative inline-flex size-5 shrink-0 items-center justify-center rounded-full p-px"
                    :style="ringStyle"
                >
                    <span
                        class="flex size-full items-center justify-center rounded-full"
                        :class="onPrimary
                            ? 'bg-primary-700 text-white'
                            : (completed ? 'bg-white text-emerald-600' : 'bg-white text-amber-600')"
                    >
                        <iconify-icon
                            :icon="completed ? 'hugeicons:tick-02' : 'hugeicons:task-01'"
                            class="text-sm"
                        ></iconify-icon>
                    </span>
                </span>

                <span class="min-w-0 flex-1 truncate">
                    {{ titleText }}
                    <b class="mx-1.5 inline-block font-bold">{{ percentage }}%</b>
                    <span class="opacity-60">{{ completedSteps }}/{{ totalSteps }}</span>
                    <span class="ms-1.5 opacity-50">— {{ hintText }}</span>
                </span>

                <iconify-icon
                    icon="hugeicons:arrow-left-01"
                    class="shrink-0 text-lg opacity-40 transition group-hover:opacity-80"
                ></iconify-icon>
            </span>

            <span class="h-1 w-full overflow-hidden rounded-full" :class="progressTrackClass">
                <span
                    class="block h-full rounded-full transition-all duration-500"
                    :class="progressClass"
                    :style="{ width: `${percentage}%` }"
                ></span>
            </span>
        </button>

        <Modal title="إعداد الحساب لأول مرة" size="2xl" name="onboarding-editor">
            <div class="max-h-[80vh] overflow-y-auto">
                <OnboardingWizardPanel
                    embedded
                    :allow-all-steps="completed"
                    :initial-step="editorStep"
                    @finished="onOnboardingFinished"
                />
            </div>
        </Modal>

        <Modal title="طرق الدفع" size="2xl" name="onboarding-payment">
            <div class="flex max-h-[75vh] flex-col">
                <div class="min-h-0 flex-1 overflow-y-auto p-2 sm:p-3">
                    <SettingsPaymentOptions embedded />
                </div>
                <div class="shrink-0 border-t border-stone-100 bg-white p-3 px-4">
                    <Button
                        type="button"
                        class="h-11 w-full rounded-xl font-semibold"
                        label="تم"
                        @click="closeOrdersSetupModal('onboarding-payment')"
                    >
                        <template #icon>
                            <iconify-icon icon="hugeicons:tick-02" class="text-lg"></iconify-icon>
                        </template>
                    </Button>
                </div>
            </div>
        </Modal>

        <Modal title="طرق الشحن" size="2xl" name="onboarding-shipping">
            <div class="flex max-h-[75vh] flex-col">
                <div class="min-h-0 flex-1 overflow-y-auto p-2 sm:p-3">
                    <SettingsShippingOptions embedded />
                </div>
                <div class="shrink-0 border-t border-stone-100 bg-white p-3 px-4">
                    <Button
                        type="button"
                        class="h-11 w-full rounded-xl font-semibold"
                        label="تم"
                        @click="closeOrdersSetupModal('onboarding-shipping')"
                    >
                        <template #icon>
                            <iconify-icon icon="hugeicons:tick-02" class="text-lg"></iconify-icon>
                        </template>
                    </Button>
                </div>
            </div>
        </Modal>

        <Modal title="توثيق النشاط" size="lg" name="onboarding-verification">
            <CompletionVerification skip-welcome-flow @saved="onVerificationSaved" />
        </Modal>
    </div>
</template>
