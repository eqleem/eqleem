<x-auth::layout title="أنشئ صفحة أعمال جديدة" subtitle="أدخل بريدك الإلكتروني وسنرسل لك رابط التسجيل">
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

        <ui:input label="البريد الإلكتروني" name="user_email" infoDir="rtl" width="w-full" dir="ltr" type="email" placeholder="your@email.com" />
        @if($usePassword)
            <ui:input label="الاسم" name="user_name" infoDir="rtl" width="w-full" dir="rtl" type="text" placeholder="اسمك الكامل" />
            <ui:input label="كلمة المرور" name="user_password" infoDir="rtl" width="w-full" dir="ltr" type="password" placeholder="*****" />
        @endif
        <div class="flex items-center gap-2 mt-2">
            <input type="checkbox" id="usePassword" wire:model.live="usePassword" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500">
            <label for="usePassword" class="text-sm text-gray-700 cursor-pointer">التسجيل بكلمة المرور</label>
        </div>



        @if($usePassword)
            <ui:button label="إنشاء الحساب" wire-target="submit" class="mt-4" />
        @else
            <ui:button label="أرسل رابط التسجيل" wire-target="submit" class="mt-4" />
        @endif
    </form>
</x-auth::layout>


<?php

use App\Actions\SendRegistrationLink;
use App\Actions\RegisterTenant;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
 
new class extends \Livewire\Component {
    public $user_email;
    public $user_name;
    public $user_password;
    public $usePassword = false;

    public function updatedUsePassword()
    {
        $this->reset(['user_name', 'user_password']);
    }
 
    protected function rules()
    {
        $rules = [
            'user_email' => 'required|email|unique:users,email|max:255',
        ];

        if ($this->usePassword) {
            $rules['user_name'] = 'required|min:2|max:200';
            $rules['user_password'] = 'required|min:6|max:200';
        }

        return $rules;
    }

    protected function messages(): array
    {
        return [
            'user_email.unique' => 'هذا البريد الإلكتروني مسجل مسبقاً. يمكنك تسجيل الدخول بدلاً من إنشاء حساب جديد.',
        ];
    }
 
    function submit()
    {
        $this->validate();

        try {
            if ($this->usePassword) {
                // Direct registration with password
                // Generate username from email prefix (ensure uniqueness)
                $emailPrefix = explode('@', $this->user_email)[0];
                do {
                    $username = $emailPrefix . '-' . generateKey(7);
                } while (User::where('username', $username)->exists());

                // Generate tenant handle
                $tenantHandle = $username;
                
                // Register tenant (creates user and tenant)
                $result = RegisterTenant::run([
                    'tenant_name' => $this->user_name,
                    'tenant_handle' => $tenantHandle,
                    'user_name' => $this->user_name,
                    'user_email' => $this->user_email,
                    'user_password' => $this->user_password,
                ]);

                if ($result['user'] && $result['tenant']) {
                    // Update username after creation
                    $result['user']->update(['username' => $username]);
                    
                    // Login the user
                    Auth::login($result['user'], true);
                    
                    session()->flash('success', 'تم إنشاء حسابك بنجاح! مرحباً بك في إقليم.');
                    
                    // Reset form
                    $this->reset(['user_email', 'user_name', 'user_password']);
                    
                    // Redirect to dashboard
                    return redirect(route('admin.home'));
                } else {
                    $this->addError('user_email', 'حدث خطأ أثناء إنشاء الحساب. يرجى المحاولة مرة أخرى.');
                }
            } else {
                // Send registration link
                SendRegistrationLink::run($this->user_email);
                
                session()->flash('success', 'تم إرسال رابط التسجيل إلى بريدك الإلكتروني. يرجى التحقق من صندوق الوارد الخاص بك.');
                $this->reset('user_email');
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            foreach ($e->errors() as $field => $messages) {
                $targetField = in_array($field, ['email', 'user_email'], true) ? 'user_email' : $field;

                foreach ($messages as $message) {
                    $this->addError($targetField, $message);
                }
            }
        } catch (\Illuminate\Database\QueryException $e) {
            logger()->error($e);

            $this->addError(
                'user_email',
                str_contains($e->getMessage(), 'users')
                    ? 'هذا البريد الإلكتروني مسجل مسبقاً. يمكنك تسجيل الدخول بدلاً من إنشاء حساب جديد.'
                    : 'حدث خطأ أثناء إنشاء الحساب. يرجى المحاولة مرة أخرى.'
            );
        } catch (\Exception $e) {
            logger()->error($e);
            $this->addError('user_email', 'حدث خطأ أثناء إنشاء الحساب. يرجى المحاولة مرة أخرى.');
        }
    }

    protected function validationAttributes()
    {
        return [
            'user_email' => 'البريد الإلكتروني',
            'user_name' => 'الاسم',
            'user_password' => 'كلمة المرور',
        ];
    }
   

}; ?>
