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

            {{-- @if ($showThemeToggle)
                <button class=" bg-black/10 hover:bg-black/20 backdrop-blur-md p-2 rounded-xl text-stone-500">
                    <iconify-icon icon="solar:moon-bold-duotone" class="inline text-2xl" stroke-width="1.5"></iconify-icon>
                </button>
            @endif --}}

            {{-- @if ($showLanguageSwitcher)
                <button class="bg-black/10 hover:bg-black/20 backdrop-blur-md p-2 px-3 rounded-xl text-stone-500 flex items-center gap-x-2 text-base">
                    <span class="hidden md:inline">English</span>
                    <iconify-icon icon="ri:translate" class="inline text-2xl" stroke-width="1.5"></iconify-icon>
                </button>
            @endif --}}
        </div>

        <div class="flex items-center justify-center w-full h-14">
            @if ($showBackButtonLink)
                <a href="{{ $homeUrl }}" wire:navigate id="backBtn" class="h-11 w-11 mt-3 rounded-full bg-black/10 hover:bg-white p-0.5X flex items-center justify-center transition-all duration-200">
                    <img src="{{tenant('logo')}}" alt="Eqleem" class="w-full h-full object-cover rounded-full transition-transform duration-500">
                </a>
            @endif
        </div>

        <div class="absolute md:top-4 top-3 md:end-0 end-1 flex items-center gap-2">
            @if ($showPagesMenu && $publishedPages->isNotEmpty())
                <div class="relative" x-data="{ open: false }" @click.away="open = false">
                    <button
                        type="button"
                        class="bg-black/10 hover:bg-black/20 backdrop-blur-md p-2 px-3 rounded-xl text-stone-500 flex items-center gap-x-2 text-base"
                        x-on:click="open = !open"
                        aria-haspopup="true"
                        x-bind:aria-expanded="open"
                    >
                        <iconify-icon icon="solar:documents-bold-duotone" class="inline text-2xl" stroke-width="1.5"></iconify-icon>
                        <span class="hidden md:inline">الصفحات</span>
                        <iconify-icon
                            icon="solar:alt-arrow-down-bold"
                            class="hidden md:inline text-base transition-transform duration-200"
                            x-bind:class="open ? 'rotate-180' : ''"
                        ></iconify-icon>
                    </button>

                    <div
                        x-show="open"
                        x-transition
                        x-cloak
                        class="absolute end-0 top-full z-50 mt-2 min-w-44 overflow-hidden rounded-xl border border-stone-200/80 bg-white/95 py-1 shadow-lg backdrop-blur-md"
                    >
                        @foreach ($publishedPages as $publishedPage)
                            <a
                                href="{{ route('tenant.page.detail', $publishedPage->slug) }}"
                                wire:navigate
                                wire:key="top-nav-page-{{ $publishedPage->id }}"
                                class="block px-4 py-2.5 text-sm text-stone-700 transition hover:bg-stone-100"
                                x-on:click="open = false"
                            >
                                {{ $publishedPage->title }}
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

            @if ($showClientLogin)
                @if (authClient())
                    <div class="relative" x-data="{ open: false }" @click.away="open = false">
                        <button
                            type="button"
                            class="bg-black/10 hover:bg-black/20 backdrop-blur-md p-2 px-3 rounded-xl text-stone-500 flex items-center gap-x-2 text-base"
                            x-on:click="open = !open"
                        >
                            <img src="{{ authClient()->avatar }}" alt="{{ authClient()->displayName() }}" class="h-7 w-7 rounded-full object-cover">
                            <span class="hidden md:inline">{{ authClient()->displayName() }}</span>
                        </button>

                        <div
                            x-show="open"
                            x-transition
                            x-cloak
                            class="absolute end-0 top-full z-50 mt-2 min-w-40 overflow-hidden rounded-xl border border-stone-200/80 bg-white/95 py-1 shadow-lg backdrop-blur-md"
                        >
                            <form method="POST" action="{{ route('tenant.client.logout', ['tenant' => tenant('handle')]) }}">
                                @csrf
                                <button type="submit" class="block w-full px-4 py-2.5 text-start text-sm text-stone-700 transition hover:bg-stone-100">
                                    تسجيل الخروج
                                </button>
                            </form>
                        </div>
                    </div>
                @else
                    <button
                        type="button"
                        class="bg-black/10 hover:bg-black/20 backdrop-blur-md p-2 px-3 rounded-xl text-stone-500 flex items-center gap-x-2 text-base"
                        x-on:click="$dispatch('open-modal', { name: 'customer-login-modal' })"
                    >
                        <iconify-icon icon="solar:lock-keyhole-minimalistic-unlocked-bold-duotone" class="inline text-2xl" stroke-width="1.5"></iconify-icon>
                        <span class="hidden md:inline">{{ $clientLoginLabel }}</span>
                    </button>
                @endif
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

            <livewire:tenant.client-login />
        </x-tenant-theme::modal>
    @endif
</div>
