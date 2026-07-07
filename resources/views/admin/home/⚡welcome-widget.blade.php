<div
    class="mb-6 overflow-hidden rounded-2xl bg-gradient-to-br from-primary-600 via-primary-700 to-primary-800 text-white"
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
            <p class="text-sm font-medium text-primary-100">{{ $greeting }}</p>
            <h2 class="mt-1 text-xl font-bold sm:text-2xl">مرحباً، {{ $userName }} 👋</h2>

            @if ($percentage < 100)
                <div class="mt-4">
                    <div class="flex items-center justify-between text-xs sm:text-sm">
                        <span class="text-primary-100">{{ $completedSteps }}/{{ $totalSteps }} خطوات</span>
                        <span class="font-bold">{{ $percentage }}%</span>
                    </div>
                    <div class="mt-2 h-1.5 overflow-hidden rounded-full bg-white/20 sm:h-2">
                        <div
                            class="h-full rounded-full bg-amber-300 transition-all duration-700 ease-out"
                            style="width: {{ $percentage }}%"
                        ></div>
                    </div>
                </div>

                @if ($nextStep)
                    <button
                        type="button"
                        x-on:click="$dispatch('openmodal', { modal: @js($nextStep['modal']) })"
                        class="mt-4 inline-flex w-full items-center justify-center gap-2 rounded-xl bg-white px-4 py-2.5 text-sm font-semibold text-primary-700 transition hover:bg-primary-50 sm:w-auto"
                    >
                        <iconify-icon icon="solar:arrow-left-bold" class="text-base"></iconify-icon>
                        {{ $nextStep['label'] }}
                    </button>
                @endif
            @else
                <p class="mt-2 text-sm text-primary-100/90">صفحتك جاهزة — شاركها مع عملائك.</p>
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
                    class="flex flex-col items-center justify-center gap-1 rounded-xl bg-white/10 p-2.5 text-center text-[11px] font-medium transition hover:bg-white/20"
                    title="معاينة الصفحة"
                >
                    <iconify-icon icon="solar:eye-bold" class="text-lg"></iconify-icon>
                    معاينة
                </a>

                <button
                    type="button"
                    x-on:click="copyLink()"
                    class="flex flex-col items-center justify-center gap-1 rounded-xl bg-white/10 p-2.5 text-center text-[11px] font-medium transition hover:bg-white/20"
                    title="نسخ الرابط"
                >
                    <iconify-icon icon="solar:copy-bold" class="text-lg"></iconify-icon>
                    نسخ
                </button>

                <button
                    type="button"
                    x-on:click="$dispatch('openmodal', { modal: 'home-share-page' })"
                    class="flex flex-col items-center justify-center gap-1 rounded-xl bg-white/10 p-2.5 text-center text-[11px] font-medium transition hover:bg-white/20"
                    title="مشاركة الصفحة"
                >
                    <iconify-icon icon="solar:share-bold" class="text-lg"></iconify-icon>
                    مشاركة
                </button>

                <button
                    type="button"
                    x-on:click="$dispatch('openmodal', { modal: 'home-page-qr' })"
                    class="flex items-center justify-center rounded-xl bg-white p-1 ring-1 ring-white/20 transition hover:bg-white/90"
                    title="رمز QR — اضغط للتكبير"
                >
                    <img
                        src="{{ $this->qrImageUrl(120) }}"
                        alt="رمز QR لصفحة {{ tenant('name') }}"
                        class="size-16 rounded-md"
                        loading="lazy"
                    >
                </button>
            </div>
 
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

    @if ($headerBlockId)
        <ui:modal title="البيانات الأساسية" size="lg" name="home-step-basic-info">
            <livewire:admin::home.completion-basic-info :headerBlockId="$headerBlockId" :key="'completion-basic-'.$headerBlockId" />
        </ui:modal>

        <ui:modal title="بيانات الاتصال" size="lg" name="home-step-contact">
            <livewire:admin::home.completion-contact :headerBlockId="$headerBlockId" :key="'completion-contact-'.$headerBlockId" />
        </ui:modal>

        <ui:modal title="السوشال ميديا" size="lg" name="home-step-social">
            <livewire:admin::home.completion-social :headerBlockId="$headerBlockId" :key="'completion-social-'.$headerBlockId" />
        </ui:modal>
    @endif

    <ui:modal title="إضافة محتوى" size="2xl" name="home-step-content">
        <livewire:admin::home.completion-content />
    </ui:modal>

    <ui:modal title="توثيق الحساب" size="2xl" name="home-step-verification">
        <livewire:admin::settings.info.verification />
    </ui:modal>

    <ui:modal title="رمز QR للصفحة" size="md" name="home-page-qr">
        <div class="space-y-4 p-4 text-center" dir="rtl">
            <p class="text-sm text-gray-600">امسح الرمز لمشاركة صفحتك بسرعة.</p>

            <div class="mx-auto inline-block rounded-2xl border border-gray-100 bg-white p-4 shadow-sm">
                <img
                    src="{{ $this->qrImageUrl(220) }}"
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

use App\Models\Block;
use App\Models\Tenant;
use App\Support\PageCompletion;
use Livewire\Attributes\On;
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

    public ?int $headerBlockId = null;

    /** @var array<int, array{key: string, label: string, hint: string, done: bool, modal: string}> */
    public array $steps = [];

    /** @var array{key: string, label: string, hint: string, done: bool, modal: string}|null */
    public ?array $nextStep = null;

    public function mount(PageCompletion $pageCompletion): void
    {
        $tenant = currentTenant();
        $user = auth()->user();

        $this->userName = (string) ($user?->name ?? 'ضيف');
        $this->pageUrl = (string) ($tenant?->url ?? url('/'));
        $this->shareText = 'شاهد صفحة '.(string) ($tenant?->name ?? config('app.name'));
        $this->greeting = $this->resolveGreeting();
        $this->headerBlockId = Block::findSingleton('header')?->id;

        $this->refreshCompletion($pageCompletion, $tenant);
    }

    #[On('page-completion-updated')]
    public function onPageCompletionUpdated(PageCompletion $pageCompletion): void
    {
        $this->refreshCompletion($pageCompletion, currentTenant());
    }

    #[On('openContentItem')]
    public function openContentItem(string $tab, string $item): void
    {
        $this->redirect(route('admin.page.home', [
            'tab' => $tab,
            'item' => $item,
        ]), navigate: true);
    }

    protected function refreshCompletion(PageCompletion $pageCompletion, ?Tenant $tenant): void
    {
        $completion = $pageCompletion->forTenant($tenant);

        $this->percentage = $completion['percentage'];
        $this->completedSteps = $completion['completed'];
        $this->totalSteps = $completion['total'];
        $this->steps = $completion['steps']->values()->all();
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

    public function qrImageUrl(int $size = 220): string
    {
        return 'https://api.qrserver.com/v1/create-qr-code/?size='.$size.'x'.$size.'&data='.urlencode($this->pageUrl);
    }

    public function placeholder(): string
    {
        return '<div class="mb-6 h-36 animate-pulse rounded-2xl bg-gray-300/40"></div>';
    }
}; ?>
