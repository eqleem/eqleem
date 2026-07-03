<div>
    <nav class="">
        <div class="absolute md:top-4 top-3 md:start-0 start-1 flex items-center gap-2">
            @if ($showShareButton)
                <button
                    type="button"
                    class=" bg-black/10 hover:bg-black/20 backdrop-blur-md p-2 rounded-xl text-stone-500"
                    x-on:click="$dispatch('open-modal', { name: 'share-page-modal' })"
                >
                    <iconify-icon icon="solar:screen-share-bold-duotone" class="inline text-2xl" stroke-width="1.5"></iconify-icon>
                </button>
            @endif

            @if ($showThemeToggle)
                <button class=" bg-black/10 hover:bg-black/20 backdrop-blur-md p-2 rounded-xl text-stone-500">
                    <iconify-icon icon="solar:moon-bold-duotone" class="inline text-2xl" stroke-width="1.5"></iconify-icon>
                </button>
            @endif

            @if ($showLanguageSwitcher)
                <button class="bg-black/10 hover:bg-black/20 backdrop-blur-md p-2 px-3 rounded-xl text-stone-500 flex items-center gap-x-2 text-base">
                    <span class="hidden md:inline">English</span>
                    <iconify-icon icon="ri:translate" class="inline text-2xl" stroke-width="1.5"></iconify-icon>
                </button>
            @endif
        </div>

        <div class="flex items-center justify-center w-full h-14">
            @if ($showBackButtonLink)
                <a href="{{ $homeUrl }}" wire:navigate id="backBtn" class="h-11 w-11 mt-3 rounded-full bg-black/10 hover:bg-white p-0.5X flex items-center justify-center transition-all duration-200">
                    <img src="{{tenant('logo')}}" alt="Eqleem" class="w-full h-full object-cover rounded-full transition-transform duration-500">
                </a>
            @endif
        </div>

        <div class="absolute md:top-4 top-3 md:end-0 end-1 flex items-center gap-2">
            @if ($showClientLogin)
                <button
                    type="button"
                    class="bg-black/10 hover:bg-black/20 backdrop-blur-md p-2 px-3 rounded-xl text-stone-500 flex items-center gap-x-2 text-base"
                    x-on:click="$dispatch('open-modal', { name: 'customer-login-modal' })"
                >
                    <iconify-icon icon="solar:lock-keyhole-minimalistic-unlocked-bold-duotone" class="inline text-2xl" stroke-width="1.5"></iconify-icon>
                    <span class="hidden md:inline">{{ $clientLoginLabel }}</span>
                </button>
            @endif
        </div>
    </nav>

    @if ($showShareButton)
        <x-tenant-theme::modal name="share-page-modal" maxWidth="md">
            <x-slot:title>مشاركة الصفحة</x-slot:title>

            <div
                class="space-y-4"
                x-data="{
                    shareUrl: window.location.href,
                    shareText: 'شاهد هذه الصفحة',
                    copied: false,
                    shareLink(platform) {
                        const url = encodeURIComponent(this.shareUrl);
                        const text = encodeURIComponent(this.shareText);

                        if (platform === 'whatsapp') {
                            return `https://wa.me/?text=${text}%20${url}`;
                        }

                        if (platform === 'telegram') {
                            return `https://t.me/share/url?url=${url}&text=${text}`;
                        }

                        if (platform === 'x') {
                            return `https://twitter.com/intent/tweet?url=${url}&text=${text}`;
                        }

                        if (platform === 'facebook') {
                            return `https://www.facebook.com/sharer/sharer.php?u=${url}`;
                        }

                        return this.shareUrl;
                    },
                    async copyLink() {
                        try {
                            await navigator.clipboard.writeText(this.shareUrl);
                        } catch (error) {
                            this.$refs.shareInput.select();
                            document.execCommand('copy');
                        }

                        this.copied = true;
                        setTimeout(() => this.copied = false, 2000);
                    }
                }"
            >
                <p class="text-sm text-stone-600">انسخ الرابط وشاركه مع عملائك مباشرة.</p>

                <div class="flex items-center gap-2">
                    <input
                        x-ref="shareInput"
                        x-model="shareUrl"
                        type="text"
                        dir="ltr"
                        readonly
                        class="w-full rounded-xl border border-stone-200 bg-stone-50 px-3 py-2.5 text-sm text-stone-700"
                    >
                    <button
                        type="button"
                        class="inline-flex shrink-0 items-center gap-2 rounded-xl bg-primary-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-primary-700"
                        x-on:click="copyLink()"
                    >
                        <iconify-icon icon="hugeicons:copy-01" class="text-lg"></iconify-icon>
                        نسخ
                    </button>
                </div>

                <div class="grid grid-cols-2 gap-2 sm:grid-cols-4">
                    <a
                        :href="shareLink('whatsapp')"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="inline-flex items-center justify-center gap-2 rounded-xl border border-stone-200 bg-white px-3 py-2.5 text-sm font-medium text-stone-700 transition hover:bg-stone-50"
                    >
                        <iconify-icon icon="mdi:whatsapp" class="text-lg text-green-600"></iconify-icon>
                        واتساب
                    </a>
                    <a
                        :href="shareLink('telegram')"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="inline-flex items-center justify-center gap-2 rounded-xl border border-stone-200 bg-white px-3 py-2.5 text-sm font-medium text-stone-700 transition hover:bg-stone-50"
                    >
                        <iconify-icon icon="mdi:telegram" class="text-lg text-sky-500"></iconify-icon>
                        تيلجرام
                    </a>
                    <a
                        :href="shareLink('x')"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="inline-flex items-center justify-center gap-2 rounded-xl border border-stone-200 bg-white px-3 py-2.5 text-sm font-medium text-stone-700 transition hover:bg-stone-50"
                    >
                        <iconify-icon icon="ri:twitter-x-fill" class="text-lg text-stone-900"></iconify-icon>
                        X
                    </a>
                    <a
                        :href="shareLink('facebook')"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="inline-flex items-center justify-center gap-2 rounded-xl border border-stone-200 bg-white px-3 py-2.5 text-sm font-medium text-stone-700 transition hover:bg-stone-50"
                    >
                        <iconify-icon icon="ic:baseline-facebook" class="text-lg text-blue-600"></iconify-icon>
                        فيسبوك
                    </a>
                </div>

                <p x-show="copied" x-transition class="text-xs font-medium text-green-600">تم نسخ الرابط بنجاح</p>
            </div>
        </x-tenant-theme::modal>
    @endif

    @if ($showClientLogin)
        <x-tenant-theme::modal name="customer-login-modal" maxWidth="md">
            <x-slot:title>تسجيل دخول العملاء</x-slot:title>

            <div class="space-y-5" dir="rtl" x-data="{ otpStep: false }">
                <button
                    type="button"
                    class="inline-flex w-full items-center justify-center gap-2 rounded-xl border border-stone-200 bg-white px-4 py-3 text-sm font-semibold text-stone-700 transition hover:bg-stone-50"
                >
                    <iconify-icon icon="flat-color-icons:google" class="text-xl"></iconify-icon>
                    المتابعة باستخدام Google
                </button>

                <div class="relative py-1">
                    <div class="absolute inset-0 flex items-center">
                        <span class="w-full border-t border-stone-200"></span>
                    </div>
                    <div class="relative flex justify-center">
                        <span class="bg-white px-3 text-xs text-stone-400">أو</span>
                    </div>
                </div>

                <div class="space-y-4">
                    <div class="space-y-1">
                        <label class="text-sm font-medium text-stone-700">رقم الجوال</label>
                        <input
                            type="tel"
                            placeholder="05xxxxxxxx"
                            dir="ltr"
                            class="w-full rounded-xl border border-stone-200 px-4 py-3 text-sm text-stone-700 focus:border-primary-300 focus:outline-none"
                        >
                    </div>

                    <template x-if="otpStep">
                        <div class="space-y-1">
                            <label class="text-sm font-medium text-stone-700">كود التحقق OTP</label>
                            <div class="grid grid-cols-6 gap-2" dir="ltr">
                                <input type="text" maxlength="1" class="h-12 rounded-xl border border-stone-200 text-center text-lg font-semibold focus:border-primary-300 focus:outline-none">
                                <input type="text" maxlength="1" class="h-12 rounded-xl border border-stone-200 text-center text-lg font-semibold focus:border-primary-300 focus:outline-none">
                                <input type="text" maxlength="1" class="h-12 rounded-xl border border-stone-200 text-center text-lg font-semibold focus:border-primary-300 focus:outline-none">
                                <input type="text" maxlength="1" class="h-12 rounded-xl border border-stone-200 text-center text-lg font-semibold focus:border-primary-300 focus:outline-none">
                                <input type="text" maxlength="1" class="h-12 rounded-xl border border-stone-200 text-center text-lg font-semibold focus:border-primary-300 focus:outline-none">
                                <input type="text" maxlength="1" class="h-12 rounded-xl border border-stone-200 text-center text-lg font-semibold focus:border-primary-300 focus:outline-none">
                            </div>
                        </div>
                    </template>

                    <button
                        type="button"
                        class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-primary-600 px-4 py-3 text-sm font-bold text-white transition hover:bg-primary-700"
                        x-show="!otpStep"
                        x-on:click="otpStep = true"
                    >
                        <iconify-icon icon="hugeicons:message-lock-01" class="text-xl"></iconify-icon>
                        إرسال كود التحقق
                    </button>

                    <button
                        type="button"
                        class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-primary-600 px-4 py-3 text-sm font-bold text-white transition hover:bg-primary-700"
                        x-show="otpStep"
                    >
                        <iconify-icon icon="hugeicons:checkmark-circle-02" class="text-xl"></iconify-icon>
                        تأكيد الدخول
                    </button>
                </div>
            </div>
        </x-tenant-theme::modal>
    @endif
</div>
