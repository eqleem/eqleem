<x-auth::layout title="تسجيل الدخول" subtitle="أدخل بريدك الإلكتروني وسنرسل لك رابط تسجيل الدخول">
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
        <ui:button variant="ghost" class="w-full !p-7 !bg-black !hover:bg-black/80 !text-white" icon="brand-github" href="{{ route('auth.social', ['social' => 'github']) }}">
            سجل باستخدام حسابك في GitHub
        </ui:button>
    </div>

    <ui:separator text="أو سجل باستخدام بريدك الإلكتروني" height="4" />

    <form wire:submit="submit" class="flex flex-col gap-y-1">
        <ui:input label="البريد الإلكتروني" name="email" infoDir="rtl" width="w-full" dir="ltr" type="email" placeholder="your@email.com" />

        @if($usePassword)
            <ui:input label="كلمة المرور" name="password" infoDir="rtl" width="w-full" dir="ltr" type="password" placeholder="*****" />
            <div class="flex justify-end mt-1">
                <a href="{{ route('auth.password.forgot-password') }}" wire:navigate class="text-sm text-blue-600 hover:text-blue-800 underline">
                    نسيت كلمة المرور؟
                </a>
            </div>
        @endif

        <div class="flex items-center gap-2 mt-2">
            <input type="checkbox" id="usePassword" wire:model.live="usePassword" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500">
            <label for="usePassword" class="text-sm text-gray-700 cursor-pointer">تسجيل الدخول بكلمة المرور</label>
        </div>



        @if($usePassword)
            <ui:button label="تسجيل الدخول" wire-target="submit" class="mt-4" />
        @else
            <ui:button label="أرسل رابط تسجيل الدخول" wire-target="submit" class="mt-4" />
        @endif
    </form>

    <div class="mt-2 flex justify-between items-center border-b border-gray-300/50 border-dotted p-1 rounded">
        <span class="text-gray-500 text-sm">ليس لديك حسابك بعد ؟ </span>
        <ui:button href="{{ route('auth.register') }}" wire:navigate label="أنشئ حساب جديد" variant="outline" />
    </div>
    
</x-auth::layout>

<?php

use App\Actions\SendRegistrationLink;
use Illuminate\Support\Facades\Auth;

new class extends \Livewire\Component {
    public $email;
    public $password;
    public $usePassword = false;

    public function updatedUsePassword()
    {
        $this->reset('password');
    }

    protected function rules()
    {
        $rules = [
            'email' => 'required|email|max:255',
        ];

        if ($this->usePassword) {
            $rules['password'] = 'required|string|min:6';
        }

        return $rules;
    }

    function submit()
    {
        $this->validate();

        try {
            if ($this->usePassword) {
                // Direct password login
                if (Auth::attempt(['email' => $this->email, 'password' => $this->password])) {
                    $user = Auth::user();
                    
                    // Update last login
                    $user->update([
                        'last_login_at' => now(),
                        'last_login_ip' => request()->ip(),
                    ]);

                    // Redirect to dashboard
                    return $this->redirect(route('admin.home')) ;
                } else {
                    $this->addError('email', 'البريد الإلكتروني أو كلمة المرور غير صحيحة.');
                    return;
                }
            } else {
                // Send registration link
                SendRegistrationLink::run($this->email);
                
                session()->flash('success', 'تم إرسال رابط تسجيل الدخول إلى بريدك الإلكتروني. يرجى التحقق من صندوق الوارد الخاص بك.');
                $this->reset('email');
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Validation errors are automatically handled by Livewire
            throw $e;
        } catch (\Exception $e) {
            $this->addError('email', $e->getMessage());
        }
    }

    protected function validationAttributes()
    {
        return [
            'email' => 'البريد الإلكتروني',
            'password' => 'كلمة المرور',
        ];
    }

}; ?>
