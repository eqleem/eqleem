<x-auth::layout title="جارٍ إنشاء حسابك..." subtitle="يرجى الانتظار بينما نقوم بإنشاء حسابك وصفحتك">
    <div class="flex flex-col items-center justify-center gap-4">
        <ui:icon name="loader-3" class="animate-spin text-primary-500 size-12" />
        <p class="text-gray-600">جارٍ إنشاء حسابك وصفحتك...</p>
    </div>
</x-auth::layout>

<?php

use App\Actions\VerifyRegistration;
use Illuminate\Support\Facades\Auth;

new class extends \Livewire\Component {
    public function mount($token)
    {
        $email = request()->query('email');

        if (!$email) {
            session()->flash('error', 'رابط التسجيل غير صالح.');
            return redirect()->route('auth.register-login');
        }
 
        try {
            $data = VerifyRegistration::run($email, $token);
             
            if ($data['user']) {
                Auth::login($data['user'], true);
                
                if ($data['tenant']) {
                    // New registration
                    session()->flash('success', 'تم إنشاء حسابك بنجاح! مرحباً بك في إقليم.');
                } else {
                    // Existing user logging in
                    session()->flash('success', 'تم تسجيل الدخول بنجاح! مرحباً بك مرة أخرى.');
                }
                
                return redirect(route('admin.home') );
            }
        } catch (\Exception $e) {
            logger()->error($e->getMessage());
            session()->flash('error', $e->getMessage());
            return redirect()->route('auth.register-login');
        }
    }
}; ?>

