<script setup>
import { reactive, ref } from 'vue';
import SettingsShell from '../../components/settings/SettingsShell.vue';
import MainBox from '../../components/ui/MainBox.vue';
import Form from '../../components/ui/Form.vue';
import Input from '../../components/ui/Input.vue';
import Toggle from '../../components/ui/Toggle.vue';
import Button from '../../components/ui/Button.vue';
import { analyticsProviders } from '../../data/settings.js';

// Port of resources/views/admin/settings/info/analytics.blade.php (dummy data).
const integrations = reactive(
    Object.fromEntries(
        analyticsProviders.map((provider) => [
            provider.key,
            { identifier: '', active: false },
        ]),
    ),
);

const saved = ref(false);

function submit() {
    saved.value = true;
    setTimeout(() => {
        saved.value = false;
    }, 2000);
}
</script>

<template>
    <SettingsShell title="ربط الإحصائيات">
        <MainBox title="ربط الإحصائيات" subtitle="ألحق أكواد التتبع لبدء قياس زيارات وإحصائيات صفحتك.">
            <template #icon>
                <img :src="`/assets/icons/business/030-growth-chart.svg`" class="h-7 w-7" alt="">
            </template>

            <Form @submit="submit">
                <div class="flex flex-col gap-3">
                    <div
                        v-for="provider in analyticsProviders"
                        :key="provider.key"
                        class="rounded-xl border border-gray-100 bg-gray-50/50 p-4"
                    >
                        <div class="mb-3 flex items-center justify-between gap-4">
                            <h3 class="text-sm font-semibold text-gray-700">{{ provider.name }}</h3>
                            <Toggle
                                v-model="integrations[provider.key].active"
                                :name="`${provider.key}-active`"
                                label="تفعيل"
                                label-width="w-auto"
                            />
                        </div>
                        <Input
                            v-model="integrations[provider.key].identifier"
                            :name="`${provider.key}-identifier`"
                            :label="provider.label"
                            :placeholder="provider.placeholder"
                            dir="ltr"
                            block
                        />
                    </div>

                    <div class="flex min-h-[80px] flex-col items-center justify-center rounded-xl border border-dashed border-gray-200 bg-gray-50/30 p-4 text-center">
                        <p class="text-sm font-semibold text-gray-500">قريباً</p>
                        <p class="mt-1 text-xs text-gray-400">سيتم إضافة المزيد من التكاملات لاحقاً.</p>
                    </div>
                </div>

                <template #footer>
                    <div class="flex items-center gap-3">
                        <span v-if="saved" class="text-sm text-emerald-600">تم الحفظ.</span>
                        <Button type="submit" label="حفظ" />
                    </div>
                </template>
            </Form>
        </MainBox>
    </SettingsShell>
</template>
