<x-auth::layout title="إعادة تعيين كلمة المرور" subtitle="أدخل بريدك الإلكتروني لإستعادة كلمة المرور.">
 
    <form wire:submit="submit" class="grid mb-12 flex flex-col gap-y-2">
        <input type="hidden" wire:model="token" value="{{ $token }}">
        <input type="hidden" wire:model="email" value="{{ $email }}">

        <ui:input label="كلمة المرور" width="w-full" name="password" wire:model="password" dir="ltr"  type="password" placeholder="*****" />
        <ui:input label="تأكيد كلمة المرور" width="w-full" name="password_confirmation" wire:model="password_confirmation" dir="ltr"  type="password" placeholder="*****" />
        <ui:button variant="primary" type="submit" icon="lock-open" wire-target="submit"> استعادة كلمة المرور </ui:button>

        @if($errors->any())
            <div class="alert alert-danger">
                @foreach($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif
    </form>
  
    <div class="mt-2  flex justify-between items-center border-b border-gray-300/50 border-dotted p-1 rounded">
        <span class="text-gray-500 text-sm"> لديك حسابك ؟ </span>
        <ui:button href="{{ route('auth.login') }}" wire:navigate variant="outline" icon="user">سجل دخولك</ui:button>
    </div>
</x-auth::layout>

<?php

use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Auth\Events\PasswordReset;

new class extends \Livewire\Component {
    public $password;
    public $password_confirmation;
    public $token;
    public $email;

    public function mount()
    {
        $this->token = request()->token;
        $this->email = request()->email;
    }

    protected function rules()
    {
        return [
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:6|confirmed',
        ];
    }

    public function submit()
    {
        $this->validate();

        $status = Password::reset(
            $this->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(60));
    
                $user->save();
    
                event(new PasswordReset($user));
            }
        );
  
        if($status){
            session()->flash('success', 'تم إعادة تعيين كلمة المرور بنجاح.');
            return redirect()->route('auth.login');
        }

        return redirect()->route('auth.password.forgot-password') ;


    }

    public function rendering($view): void
    {
        $view->title('تسجيل الدخول');
    }
}; ?>
