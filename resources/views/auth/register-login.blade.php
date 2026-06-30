<x-auth::layout title="مرحباً بك في إقليم" subtitle="أدخل بريدك الإلكتروني وسنرسل لك الرابط لإنشاء حسابك أو دخولك بشكل سريع">
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
        <ui:button variant="blue" class="w-full !p-7 !bg-emerald-700 !hover:bg-emerald-800 !text-white" href="{{ route('auth.social', ['social' => 'google']) }}">
            <ui:icon name="brand-google" class="!w-5 !h-5" />
            سجل باستخدام حسابك في Google
        </ui:button>
        <ui:button variant="ghost" class="w-full !p-7 !bg-black !hover:bg-black/80 !text-white" icon="brand-github" href="{{ route('auth.social', ['social' => 'github']) }}">
            سجل باستخدام حسابك في GitHub
        </ui:button>
    </div>

    <ui:separator text="أو استخدم بريدك الإلكتروني" height="4" />

    <form wire:submit="submit" class="flex flex-col gap-y-1">
        <ui:input label="البريد الإلكتروني" name="email" infoDir="rtl" width="w-full" dir="ltr" type="email" placeholder="your@email.com" />

        <ui:button label="أرسل الرابط إلى بريدي الإلكتروني" wire-target="submit" class="mt-4" />
    </form>
</x-auth::layout>

<?php

use App\Actions\SendRegistrationLink;
use Illuminate\Support\Facades\Auth;

new class extends \Livewire\Component {
    public $email;

    protected function rules()
    {
        return [
            'email' => 'required|email|max:255',
        ];
    }

    function submit()
    {
        $this->validate();

        try {
            SendRegistrationLink::run($this->email);
            
            session()->flash('success', 'تم إرسال رابط تسجيل الدخول إلى بريدك الإلكتروني. يرجى التحقق من صندوق الوارد الخاص بك.');
            $this->reset('email');
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
        ];
    }

}; ?>

