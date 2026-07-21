<x-auth::layout title="مرحباً بك في إقليم" subtitle="أدخل بريدك الإلكتروني وسنرسل لك رابطاً وكوداً لإنشاء حسابك أو دخولك بشكل سريع">
    @if(session('success'))
        <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg">
            {{ session('error') }}
        </div>
    @endif

    <div class="flex flex-col gap-2 mb-2">
        <ui:button variant="blue" class="w-full !p-7 !bg-red-700 !hover:bg-red-800 !text-white" href="{{ route('auth.social', ['social' => 'google']) }}">
            <ui:icon name="brand-google" class="!w-5 !h-5" />
            سجل باستخدام حسابك في Google
        </ui:button>
    </div>

    <ui:separator text="أو استخدم بريدك الإلكتروني" height="4" />

    <form wire:submit="{{ $otpStep ? 'verifyCode' : 'submit' }}" class="flex flex-col gap-y-1">
        <ui:input
            label="البريد الإلكتروني"
            name="email"
            infoDir="rtl"
            width="w-full"
            dir="ltr"
            type="email"
            placeholder="your@email.com"
            :disabled="$otpStep"
        />

        @if ($otpStep)
            <ui:input
                label="كود التحقق"
                name="code"
                infoDir="rtl"
                width="w-full"
                dir="ltr"
                type="text"
                placeholder="123456"
                maxlength="6"
                inputmode="numeric"
                class="!text-center !text-lg !tracking-[0.4em] !font-semibold"
            />
        @endif

        @if (! $otpStep)
            <ui:button label="أرسل الرابط والكود إلى بريدي" wire:target="submit" class="mt-4" />
        @else
            <ui:button label="تأكيد الدخول" wire:target="verifyCode" class="mt-4" />
            <ui:button type="button" label="تغيير البريد الإلكتروني" variant="outline" wire:click="resetEmailStep" class="mt-2" />
        @endif
    </form>

    @if ($codeSent)
        <p class="mt-3 text-center text-sm font-medium text-green-600">
            تم إرسال رابط الدخول وكود التحقق إلى بريدك. يمكنك فتح الرابط مباشرة أو إدخال الكود هنا.
        </p>
    @endif
</x-auth::layout>

<?php

use App\Actions\SendRegistrationLink;
use App\Actions\VerifyRegistrationCode;
use Illuminate\Support\Facades\Auth;

new class extends \Livewire\Component {
    public string $email = '';

    public string $code = '';

    public bool $otpStep = false;

    public bool $codeSent = false;

    protected function rules(): array
    {
        return [
            'email' => 'required|email|max:255',
        ];
    }

    public function submit(): void
    {
        $this->validate();

        try {
            SendRegistrationLink::run($this->email);

            $this->otpStep = true;
            $this->codeSent = true;
            $this->resetErrorBag();
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            $this->addError('email', $e->getMessage());
        }
    }

    public function verifyCode()
    {
        $this->validate([
            'email' => ['required', 'email', 'max:255'],
            'code' => ['required', 'digits:6'],
        ], [], [
            'email' => 'البريد الإلكتروني',
            'code' => 'كود التحقق',
        ]);

        try {
            $data = VerifyRegistrationCode::run($this->email, $this->code);

            if ($data['user']) {
                Auth::login($data['user'], true);

                if ($data['tenant']) {
                    session()->flash('success', 'تم إنشاء حسابك بنجاح! مرحباً بك في إقليم.');
                } else {
                    session()->flash('success', 'تم تسجيل الدخول بنجاح! مرحباً بك مرة أخرى.');
                }

                return $this->redirect(route('dashboard'));
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            $this->addError('code', $e->getMessage());
        }
    }

    public function resetEmailStep(): void
    {
        $this->otpStep = false;
        $this->codeSent = false;
        $this->code = '';
        $this->resetErrorBag();
    }

    protected function validationAttributes(): array
    {
        return [
            'email' => 'البريد الإلكتروني',
            'code' => 'كود التحقق',
        ];
    }
}; ?>
