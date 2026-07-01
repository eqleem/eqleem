<div
    class="mb-6 overflow-hidden rounded-2xl bg-gradient-to-br from-primary-600 via-primary-700 to-primary-800 text-white shadow-lg"
    x-data="{
        shareUrl: @js($pageUrl),
        shareText: @js($shareText),
        copied: false,
        async copyLink() {
            try {
                await navigator.clipboard.writeText(this.shareUrl);
            } catch (error) {
                this.$refs.shareInput?.select();
                document.execCommand('copy');
            }

            this.copied = true;
            $dispatch('notify', { text: 'تم نسخ الرابط بنجاح', type: 'success' });
            setTimeout(() => this.copied = false, 2500);
        },
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
    }"
>
    <div class="grid gap-0 lg:grid-cols-[1fr_auto]">
        <div class="p-5 sm:p-6">
            <div class="flex flex-col gap-5 sm:flex-row sm:items-start sm:justify-between">
                <div class="min-w-0 flex-1">
                    <p class="text-sm font-medium text-primary-100">{{ $greeting }}</p>
                    <h2 class="mt-1 text-xl font-bold sm:text-2xl">
                        مرحباً، {{ $userName }} 👋
                    </h2>
                    <p class="mt-2 max-w-lg text-sm leading-relaxed text-primary-100/90">
                        @if ($percentage >= 100)
                            صفحتك جاهزة بالكامل — شاركها الآن وابدأ بجذب عملائك.
                        @elseif ($percentage >= 70)
                            أنت قريب جداً! أكمل الخطوات المتبقية لتظهر صفحتك بأفضل شكل.
                        @else
                            أكمل إعداد صفحتك خطوة بخطوة لتبدو احترافية وتجذب المزيد من الزوار.
                        @endif
                    </p>
                </div>

                <div class="flex shrink-0 items-center gap-4 rounded-2xl bg-white/10 p-4 backdrop-blur-sm ring-1 ring-white/15">
                    <div class="relative size-20 shrink-0">
                        <svg class="size-20 -rotate-90" viewBox="0 0 36 36" aria-hidden="true">
                            <circle cx="18" cy="18" r="15.5" fill="none" class="stroke-white/20" stroke-width="3" />
                            <circle
                                cx="18"
                                cy="18"
                                r="15.5"
                                fill="none"
                                class="stroke-amber-300 transition-all duration-700 ease-out"
                                stroke-width="3"
                                stroke-linecap="round"
                                stroke-dasharray="{{ $percentage }}, 100"
                            />
                        </svg>
                        <div class="absolute inset-0 flex flex-col items-center justify-center">
                            <span class="text-2xl font-bold leading-none">{{ $percentage }}%</span>
                            <span class="mt-0.5 text-[10px] text-primary-100">اكتمال</span>
                        </div>
                    </div>

                    <div class="min-w-0">
                        <p class="text-sm font-semibold">
                            {{ $completedSteps }}/{{ $totalSteps }} خطوات
                        </p>
                        @if ($nextStep)
                            <a
                                href="{{ $nextStep['url'] }}"
                                wire:navigate
                                class="mt-1 block text-xs leading-relaxed text-amber-200 hover:text-white transition"
                            >
                                التالي: {{ $nextStep['label'] }} ←
                            </a>
                        @else
                            <p class="mt-1 text-xs text-emerald-200">كل الخطوات مكتملة</p>
                        @endif
                    </div>
                </div>
            </div>

            @if ($percentage < 100)
                <div class="mt-5 flex flex-wrap gap-2">
                    @foreach ($pendingSteps as $step)
                        <a
                            href="{{ $step['url'] }}"
                            wire:navigate
                            class="inline-flex items-center gap-1.5 rounded-full bg-white/10 px-3 py-1.5 text-xs font-medium text-white ring-1 ring-white/15 transition hover:bg-white/20"
                            title="{{ $step['hint'] }}"
                        >
                            <iconify-icon icon="solar:add-circle-linear" class="text-sm text-amber-300"></iconify-icon>
                            {{ $step['label'] }}
                        </a>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="border-t border-white/10 bg-black/10 p-5 sm:p-6 lg:w-96 lg:border-s lg:border-t-0">
            <p class="mb-2 text-xs font-medium text-primary-100">رابط صفحتك</p>

            <div class="flex items-center gap-2 rounded-xl bg-white/10 p-2 ring-1 ring-white/10">
                <input
                    x-ref="shareInput"
                    type="text"
                    dir="ltr"
                    readonly
                    value="{{ $pageUrl }}"
                    class="min-w-0 flex-1 truncate bg-transparent px-2 text-sm text-white outline-none"
                >
            </div>

            <div class="mt-3 grid grid-cols-4 gap-2">
                <a
                    href="{{ $pageUrl }}"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="flex flex-col items-center gap-1 rounded-xl bg-white/10 p-2.5 text-center text-[11px] font-medium transition hover:bg-white/20"
                    title="معاينة الصفحة"
                >
                    <iconify-icon icon="solar:eye-bold" class="text-lg"></iconify-icon>
                    معاينة
                </a>

                <button
                    type="button"
                    x-on:click="copyLink()"
                    class="flex flex-col items-center gap-1 rounded-xl bg-white/10 p-2.5 text-center text-[11px] font-medium transition hover:bg-white/20"
                    title="نسخ الرابط"
                >
                    <iconify-icon icon="solar:copy-bold" class="text-lg"></iconify-icon>
                    نسخ
                </button>

                <button
                    type="button"
                    x-on:click="$dispatch('openmodal', { modal: 'home-share-page' })"
                    class="flex flex-col items-center gap-1 rounded-xl bg-white/10 p-2.5 text-center text-[11px] font-medium transition hover:bg-white/20"
                    title="مشاركة الصفحة"
                >
                    <iconify-icon icon="solar:share-bold" class="text-lg"></iconify-icon>
                    مشاركة
                </button>

                <button
                    type="button"
                    x-on:click="$dispatch('openmodal', { modal: 'home-page-qr' })"
                    class="flex flex-col items-center gap-1 rounded-xl bg-white/10 p-2.5 text-center text-[11px] font-medium transition hover:bg-white/20"
                    title="رمز QR"
                >
                    <iconify-icon icon="solar:qr-code-bold" class="text-lg"></iconify-icon>
                    QR
                </button>
            </div>

            <p x-show="copied" x-transition class="mt-2 text-center text-xs text-emerald-300">
                تم نسخ الرابط
            </p>
        </div>
    </div>

    <ui:modal title="مشاركة الصفحة" size="lg" name="home-share-page">
        <div class="space-y-4 p-4" dir="rtl">
            <p class="text-sm text-gray-600">انسخ الرابط أو شاركه مباشرة عبر المنصات.</p>

            <div class="flex items-center gap-2">
                <input
                    type="text"
                    dir="ltr"
                    readonly
                    x-bind:value="shareUrl"
                    class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2.5 text-sm text-gray-700"
                >
                <button
                    type="button"
                    class="inline-flex shrink-0 items-center gap-2 rounded-xl bg-primary-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-primary-700"
                    x-on:click="copyLink()"
                >
                    <iconify-icon icon="solar:copy-bold" class="text-lg"></iconify-icon>
                    نسخ
                </button>
            </div>

            <div class="grid grid-cols-2 gap-2 sm:grid-cols-4">
                <a
                    x-bind:href="shareLink('whatsapp')"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="inline-flex items-center justify-center gap-2 rounded-xl border border-gray-200 bg-white px-3 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50"
                >
                    <iconify-icon icon="mdi:whatsapp" class="text-lg text-green-600"></iconify-icon>
                    واتساب
                </a>
                <a
                    x-bind:href="shareLink('telegram')"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="inline-flex items-center justify-center gap-2 rounded-xl border border-gray-200 bg-white px-3 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50"
                >
                    <iconify-icon icon="mdi:telegram" class="text-lg text-sky-500"></iconify-icon>
                    تيلجرام
                </a>
                <a
                    x-bind:href="shareLink('x')"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="inline-flex items-center justify-center gap-2 rounded-xl border border-gray-200 bg-white px-3 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50"
                >
                    <iconify-icon icon="ri:twitter-x-fill" class="text-lg text-gray-900"></iconify-icon>
                    X
                </a>
                <a
                    x-bind:href="shareLink('facebook')"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="inline-flex items-center justify-center gap-2 rounded-xl border border-gray-200 bg-white px-3 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50"
                >
                    <iconify-icon icon="ic:baseline-facebook" class="text-lg text-blue-600"></iconify-icon>
                    فيسبوك
                </a>
            </div>
        </div>
    </ui:modal>

    <ui:modal title="رمز QR للصفحة" size="md" name="home-page-qr">
        <div class="space-y-4 p-4 text-center" dir="rtl">
            <p class="text-sm text-gray-600">امسح الرمز لمشاركة صفحتك بسرعة.</p>

            <div class="mx-auto inline-block rounded-2xl border border-gray-100 bg-white p-4 shadow-sm">
                <img
                    src="https://api.qrserver.com/v1/create-qr-code/?size=220x220&data={{ urlencode($pageUrl) }}"
                    alt="رمز QR لصفحة {{ tenant('name') }}"
                    class="mx-auto size-[220px]"
                    loading="lazy"
                >
            </div>

            <p class="truncate text-xs text-gray-500" dir="ltr">{{ $pageUrl }}</p>

            <button
                type="button"
                class="inline-flex items-center gap-2 rounded-xl bg-primary-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-primary-700"
                x-on:click="copyLink()"
            >
                <iconify-icon icon="solar:copy-bold" class="text-lg"></iconify-icon>
                نسخ الرابط
            </button>
        </div>
    </ui:modal>
</div>

<?php

use App\Support\PageCompletion;
use Livewire\Component;

new class extends Component
{
    public string $greeting = '';

    public string $userName = '';

    public string $pageUrl = '';

    public string $shareText = '';

    public int $percentage = 0;

    public int $completedSteps = 0;

    public int $totalSteps = 0;

    /** @var array<int, array{key: string, label: string, hint: string, done: bool, url: string}> */
    public array $pendingSteps = [];

    /** @var array{key: string, label: string, hint: string, done: bool, url: string}|null */
    public ?array $nextStep = null;

    public function mount(PageCompletion $pageCompletion): void
    {
        $tenant = currentTenant();
        $user = auth()->user();

        $this->userName = (string) ($user?->name ?? 'ضيف');
        $this->pageUrl = (string) ($tenant?->url ?? url('/'));
        $this->shareText = 'شاهد صفحة '.(string) ($tenant?->name ?? config('app.name'));
        $this->greeting = $this->resolveGreeting();

        $completion = $pageCompletion->forTenant($tenant);

        $this->percentage = $completion['percentage'];
        $this->completedSteps = $completion['completed'];
        $this->totalSteps = $completion['total'];
        $this->pendingSteps = $completion['steps']->where('done', false)->take(3)->values()->all();
        $this->nextStep = $completion['steps']->firstWhere('done', false);
    }

    protected function resolveGreeting(): string
    {
        $hour = (int) now()->format('G');

        if ($hour < 12) {
            return 'صباح الخير';
        }

        if ($hour < 17) {
            return 'مساء الخير';
        }

        return 'مساء الخير';
    }

    public function placeholder(): string
    {
        return '<div class="mb-6 h-44 animate-pulse rounded-2xl bg-gray-300/40"></div>';
    }
}; ?>
