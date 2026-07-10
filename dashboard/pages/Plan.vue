<script setup>
import { ref, computed } from 'vue';
import Container from '../components/ui/Container.vue';
import MainBox from '../components/ui/MainBox.vue';
import Button from '../components/ui/Button.vue';
import Icon from '../components/ui/Icon.vue';
import { money } from '../data/orders.js';

// Ported from resources/views/admin/plan/home.blade.php (dummy data).
const billingPeriod = ref('monthly');
const currentTier = 'free';

const tierFeatures = {
    free: ['صفحة شخصية واحدة', 'قوالب أساسية جاهزة', 'تحليلات أساسية', 'تخصيص الألوان والخطوط', 'دعم عبر البريد', 'بدون بطاقة بنكية'],
    basic: ['كل ميزات الباقة المجانية', 'نطاق مخصص', 'قوالب احترافية', 'تحليلات متقدمة', 'إزالة شعار المنصة', 'دعم أولوي'],
    pro: ['كل ميزات بيسك', 'صفحات غير محدودة', 'تكاملات API', 'أتمتة متقدمة', 'تقارير مخصصة', 'دعم مباشر'],
};

const tierAudience = {
    free: { title: 'للمبتدئين', description: 'مثالية لمن يريد تجربة المنصة وإنشاء صفحته الأولى بسرعة.' },
    basic: { title: 'للمشاريع الشخصية', description: 'مناسبة للصفحات الشخصية والمشاريع الصغيرة التي تحتاج مزايا أكثر.' },
    pro: { title: 'للأعمال الاحترافية', description: 'الخيار الأمثل للأعمال والصفحات التي تحتاج أدوات متقدمة ودعماً مباشراً.' },
};

const prices = {
    basic: { monthly: 49, yearly: 490 },
    pro: { monthly: 99, yearly: 990 },
};

const displayPlans = computed(() => [
    { tier: 'free', title: 'المجاني', description: 'ابدأ صفحتك الأولى بدون أي تكلفة.', free: true, price: 0, accent: 'text-stone-900' },
    { tier: 'basic', title: 'بيسك', description: 'مزايا أكثر لصفحتك الشخصية ومشروعك الصغير.', free: false, featured: billingPeriod.value === 'yearly', price: prices.basic[billingPeriod.value], accent: 'text-rose-500' },
    { tier: 'pro', title: 'برو', description: 'كل ما تحتاجه الأعمال الاحترافية من أدوات ودعم.', free: false, highlighted: true, price: prices.pro[billingPeriod.value], accent: 'text-orange-500' },
].map((plan) => ({
    ...plan,
    current: plan.tier === currentTier,
    features: tierFeatures[plan.tier],
    audience: tierAudience[plan.tier],
    intervalLabel: billingPeriod.value === 'monthly' ? 'شهرياً' : 'سنوياً',
})));

const faqs = [
    { question: 'هل الباقة المجانية مجانية فعلاً؟', answer: 'نعم، يمكنك إنشاء صفحتك واستخدام الميزات الأساسية بدون بطاقة بنكية أو حد زمني. الباقة المجانية مناسبة للبدء وتجربة المنصة.' },
    { question: 'ما الفرق بين الاشتراك الشهري والسنوي؟', answer: 'كلاهما يمنحك نفس الميزات، لكن الاشتراك السنوي يوفر خصم شهرين مقارنة بالدفع الشهري على مدار العام.' },
    { question: 'هل يمكنني الترقية أو تخفيض باقتي لاحقاً؟', answer: 'نعم، يمكنك تغيير باقتك في أي وقت من صفحة الاشتراك. عند الترقية تُفعَّل الميزات الجديدة فوراً، وعند التخفيض تبقى الميزات الحالية حتى نهاية فترة الاشتراك.' },
    { question: 'هل أحتاج بطاقة بنكية للباقة المجانية؟', answer: 'لا، تفعيل الباقة المجانية لا يتطلب أي بيانات دفع. بطاقة البنك مطلوبة فقط عند الاشتراك في الباقات المدفوعة.' },
    { question: 'هل يمكنني إلغاء الاشتراك؟', answer: 'نعم، يمكنك إلغاء الاشتراك في أي وقت. ستبقى باقتك المدفوعة فعّالة حتى نهاية الفترة المدفوعة، ثم تعود صفحتك للباقة المجانية ما لم تجدّد.' },
];

const activeFaq = ref(1);
function toggleFaq(index) {
    activeFaq.value = activeFaq.value === index ? null : index;
}
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

        <!-- Billing toggle -->
        <div class="mt-10 flex flex-col items-center gap-3">
            <div class="flex items-center">
                <div class="inline-flex rounded-xl bg-stone-300/60 p-1">
                    <button
                        type="button"
                        class="rounded-lg px-6 py-2.5 text-sm font-semibold transition"
                        :class="billingPeriod === 'monthly' ? 'bg-white text-stone-900 shadow-sm' : 'text-stone-500 hover:text-stone-800'"
                        @click="billingPeriod = 'monthly'"
                    >شهري</button>
                    <button
                        type="button"
                        class="rounded-lg px-6 py-2.5 text-sm font-semibold transition"
                        :class="billingPeriod === 'yearly' ? 'bg-white text-stone-900 shadow-sm' : 'text-stone-500 hover:text-stone-800'"
                        @click="billingPeriod = 'yearly'"
                    >سنوي</button>
                </div>
                <div class="pointer-events-none ms-1.5 flex flex-col items-start pt-1">
                    <p class="ms-3 -rotate-3 whitespace-nowrap text-[10px] font-bold leading-none text-emerald-700">خصم شهرين</p>
                </div>
            </div>
        </div>

        <!-- Plan cards -->
        <div class="mt-10 grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-3">
            <div
                v-for="plan in displayPlans"
                :key="plan.tier"
                class="relative rounded-2xl"
                :class="plan.highlighted ? 'bg-gradient-to-b from-orange-400 via-rose-500 to-violet-600 p-0.5' : plan.free ? 'bg-stone-100' : 'bg-white ring-1 ring-stone-200'"
            >
                <div class="flex h-full flex-col p-6" :class="plan.highlighted ? 'rounded-[calc(1rem-1px)] bg-white' : ''">
                    <span v-if="plan.current" class="absolute -top-3 right-4 z-10 rounded-full bg-stone-900 px-3 py-1 text-xs font-medium text-white">باقتك الحالية</span>
                    <span v-if="plan.featured" class="absolute -top-3 left-4 z-10 rounded-full bg-amber-500 px-3 py-1 text-xs font-medium text-white">الأوفر</span>

                    <div class="mb-5 flex items-start justify-between gap-4">
                        <div>
                            <span v-if="plan.free" class="inline-flex rounded-md border border-stone-900 bg-white px-2.5 py-1 text-sm font-bold text-stone-900">{{ plan.title }}</span>
                            <h3 v-else class="text-lg font-bold text-stone-900"><span>Eqleem</span> <span :class="plan.accent">{{ plan.title }}</span></h3>
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
                            <p class="text-4xl font-bold tracking-tight text-stone-900">{{ money(plan.price) }}</p>
                            <p class="mt-1 text-sm text-stone-400">{{ plan.intervalLabel }}</p>
                        </template>
                    </div>

                    <div class="mb-6">
                        <Button v-if="plan.current" variant="outline" disabled label="مفعّلة" class="h-11 w-full !rounded-lg" />
                        <Button v-else-if="plan.free" variant="outline" label="ابدأ مجاناً" class="h-11 w-full !rounded-lg !border-stone-900 !bg-white !text-stone-900 hover:!bg-stone-50" />
                        <Button v-else label="اشترك الآن" class="h-11 w-full !rounded-lg !bg-stone-900 !text-white hover:!bg-stone-800" />
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

        <!-- FAQ -->
        <section class="mt-20">
            <div class="mx-auto max-w-3xl text-center">
                <div class="inline-flex size-11 items-center justify-center rounded-xl bg-stone-100 text-stone-600"><Icon name="info" class="h-5 w-5" /></div>
                <h2 class="mt-4 text-2xl font-bold text-stone-900">الأسئلة المتكررة</h2>
                <p class="mt-2 text-sm leading-relaxed text-stone-500">إجابات سريعة عن الاشتراكات والباقات.</p>
            </div>

            <div class="mx-auto mt-10 max-w-3xl overflow-hidden rounded-2xl bg-white ring-1 ring-stone-200">
                <div v-for="(faq, index) in faqs" :key="index" :class="{ 'border-b border-stone-200': index < faqs.length - 1 }">
                    <button
                        type="button"
                        class="flex w-full items-center justify-between gap-4 px-6 py-5 text-start text-sm font-semibold text-stone-900 transition hover:bg-stone-50 sm:px-7"
                        @click="toggleFaq(index + 1)"
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
    </Container>
</template>
