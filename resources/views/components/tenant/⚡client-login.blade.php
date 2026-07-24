<div class="space-y-5" dir="rtl">
    @if (session('client_auth_error'))
        <p class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            {{ session('client_auth_error') }}
        </p>
    @endif

    @if ($authenticatedClient)
        <div class="space-y-4 text-center">
            <div class="mx-auto flex h-16 w-16 items-center justify-center overflow-hidden rounded-full bg-stone-100">
                <img src="{{ $authenticatedClient->avatar }}" alt="{{ $clientName }}" class="h-full w-full object-cover">
            </div>

            <div>
                <p class="text-base font-semibold text-stone-800">{{ $clientName }}</p>
                <p class="text-sm text-stone-500">{{ $clientEmail }}</p>
            </div>

            <form method="POST" action="{{ route('tenant.client.logout', ['tenant' => tenant('handle')]) }}">
                @csrf
                <button
                    type="submit"
                    class="inline-flex w-full items-center justify-center gap-2 rounded-xl border border-stone-200 bg-white px-4 py-3 text-sm font-semibold text-stone-700 transition hover:bg-stone-50"
                >
                    <iconify-icon icon="solar:logout-2-bold-duotone" class="text-xl"></iconify-icon>
                    تسجيل الخروج
                </button>
            </form>
        </div>
    @else
        <a
            href="{{ route('tenant.client.auth.social', ['tenant' => tenant('handle'), 'provider' => 'google']) }}"
            x-on:click.prevent="window.location.assign($el.href)"
            class="inline-flex w-full items-center justify-center gap-2 rounded-xl border border-stone-200 bg-white px-4 py-3 text-sm font-semibold text-stone-700 transition hover:bg-stone-50"
        >
            <iconify-icon icon="flat-color-icons:google" class="text-xl" aria-hidden="true"></iconify-icon>
            الدخول بواسطة Gmail
        </a>

        <div class="relative py-1">
            <div class="absolute inset-0 flex items-center">
                <span class="w-full border-t border-stone-200"></span>
            </div>
            <div class="relative flex justify-center">
                <span class="bg-white px-3 text-xs text-stone-400">أو عبر البريد الإلكتروني</span>
            </div>
        </div>

        <form wire:submit="{{ $otpStep ? 'verifyCode' : 'sendCode' }}" class="space-y-4">
            <div class="space-y-1">
                <label for="client-login-email" class="text-sm font-medium text-stone-700">البريد الإلكتروني</label>
                <input
                    id="client-login-email"
                    type="email"
                    wire:model="email"
                    placeholder="you@gmail.com"
                    dir="ltr"
                    @disabled($otpStep)
                    class="w-full rounded-xl border border-stone-200 px-4 py-3 text-sm text-stone-700 focus:border-primary-300 focus:outline-none disabled:bg-stone-50 @error('email') border-red-400 @enderror"
                >
                @error('email')
                    <p class="text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            @if ($otpStep)
                <div class="space-y-1">
                    <label for="client-login-code" class="text-sm font-medium text-stone-700">كود التحقق</label>
                    <input
                        id="client-login-code"
                        type="text"
                        wire:model="code"
                        maxlength="6"
                        inputmode="numeric"
                        placeholder="123456"
                        dir="ltr"
                        class="w-full rounded-xl border border-stone-200 px-4 py-3 text-center text-lg font-semibold tracking-[0.4em] text-stone-700 focus:border-primary-300 focus:outline-none @error('code') border-red-400 @enderror"
                    >
                    @error('code')
                        <p class="text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            @endif

            @if (! $otpStep)
                <button
                    type="submit"
                    wire:loading.attr="disabled"
                    class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-primary-600 px-4 py-3 text-sm font-bold text-white transition hover:bg-primary-700 disabled:opacity-60"
                >
                    <iconify-icon icon="hugeicons:mail-send-01" class="text-xl"></iconify-icon>
                    <span wire:loading.remove wire:target="sendCode">إرسال رابط الدخول</span>
                    <span wire:loading wire:target="sendCode">جاري الإرسال...</span>
                </button>
            @else
                <button
                    type="submit"
                    wire:loading.attr="disabled"
                    class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-primary-600 px-4 py-3 text-sm font-bold text-white transition hover:bg-primary-700 disabled:opacity-60"
                >
                    <iconify-icon icon="hugeicons:checkmark-circle-02" class="text-xl"></iconify-icon>
                    <span wire:loading.remove wire:target="verifyCode">تأكيد الدخول</span>
                    <span wire:loading wire:target="verifyCode">جاري التحقق...</span>
                </button>

                <button
                    type="button"
                    wire:click="resetEmailStep"
                    class="inline-flex w-full items-center justify-center gap-2 rounded-xl border border-stone-200 bg-white px-4 py-3 text-sm font-medium text-stone-600 transition hover:bg-stone-50"
                >
                    تغيير البريد الإلكتروني
                </button>
            @endif
        </form>

        @if ($codeSent)
            <p class="text-center text-xs font-medium text-green-600">
                تم إرسال رابط الدخول وكود التحقق إلى بريدك. يمكنك فتح الرابط مباشرة أو إدخال الكود هنا.
            </p>
        @endif
    @endif
</div>

<?php

use App\Actions\SendClientLoginCode;
use App\Actions\VerifyClientLoginCode;
use App\Models\Client;
use Livewire\Component;

new class extends Component
{
    public string $email = '';

    public string $code = '';

    public bool $otpStep = false;

    public bool $codeSent = false;

    public function sendCode(): void
    {
        $this->validate([
            'email' => ['required', 'email', 'max:255'],
        ], [], [
            'email' => 'البريد الإلكتروني',
        ]);

        $tenantId = currentTenantId();

        abort_unless($tenantId, 404);

        SendClientLoginCode::run($this->email, $tenantId);

        $this->otpStep = true;
        $this->codeSent = true;
        $this->resetErrorBag();
    }

    public function verifyCode(): void
    {
        $this->validate([
            'email' => ['required', 'email', 'max:255'],
            'code' => ['required', 'digits:6'],
        ], [], [
            'email' => 'البريد الإلكتروني',
            'code' => 'كود التحقق',
        ]);

        $tenantId = currentTenantId();

        abort_unless($tenantId, 404);

        VerifyClientLoginCode::run($this->email, $this->code, $tenantId);

        if (session()->has('client_auth_intended')) {
            $this->redirect(clientAuthIntendedUrl(currentTenant()), navigate: true);

            return;
        }

        $this->dispatch('close-modal', name: 'customer-login-modal');
        $this->dispatch('close-modal', name: 'reviews-login-modal');
        $this->dispatch('client-authenticated');
        $this->dispatch('cart-updated');
    }

    public function resetEmailStep(): void
    {
        $this->otpStep = false;
        $this->codeSent = false;
        $this->code = '';
        $this->resetErrorBag();
    }

    public function with(): array
    {
        $client = authClient();

        return [
            'authenticatedClient' => $client instanceof Client ? $client : null,
            'clientName' => $client instanceof Client ? $client->displayName() : null,
            'clientEmail' => $client instanceof Client ? ($client->profileForTenant()['email'] ?? $client->email) : null,
        ];
    }
};
?>
