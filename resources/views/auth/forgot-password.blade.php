<x-auth::layout title="إستعادة كلمة المرور" subtitle="أدخل بريدك الإلكتروني لإستعادة كلمة المرور.">
 
    <form wire:submit="submit" class="grid mb-12 flex flex-col gap-y-2">
        <ui:input label="بريدك الإلكتروني" width="w-full" name="email" wire:model="email" dir="ltr"  type="email" placeholder="your@email.com" />
      
        <ui:button variant="primary" type="submit" icon="lock-open" wire-target="submit"> استعادة كلمة المرور </ui:button>
    </form>
  
    <div class="mt-2  flex justify-between items-center border-b border-gray-300/50 border-dotted p-1 rounded">
        <span class="text-gray-500 text-sm">ليس لديك حساب ؟ </span>
        <ui:button href="{{ route('auth.register') }}" wire:navigate variant="outline" icon="user-plus">أنشئ حساب جديد</ui:button>
        <span class="text-gray-500 text-sm mx-2">أو</span>
        <ui:button href="{{ route('auth.login') }}" wire:navigate variant="outline" icon="user">سجل دخولك</ui:button>
    </div>
</x-auth::layout>

<?php

use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Password;

new class extends \Livewire\Component {
    public $email;
    public $password;
    public $remember;

    protected function rules()
    {
        return [
            'email' => 'required|email|max:255',
        ];
    }

    public function submit()
    {
        $this->validate();

        $user = User::where('email', $this->email)->first();

        if(!$user){
            $this->addError('email', 'البريد الإلكتروني غير موجود.');
            return;
        }
   
        $status = Password::sendResetLink(
            $this->only('email')
        );
    
        if($status === Password::ResetLinkSent){
            session()->flash('success', 'تم إرسال رابط إستعادة كلمة المرور إلى بريدك الإلكتروني.');
            return redirect()->route('auth.login');
        }
        $this->addError('email', 'حدث خطأ ما.');
        return redirect()->route('auth.password.forgot-password') ; 
    }

    public function rendering($view): void
    {
        $view->title('تسجيل الدخول');
    }
}; ?>
