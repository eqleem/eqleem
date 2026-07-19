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
    </div>

    <ui:separator text="أو سجل باستخدام بريدك الإلكتروني" height="4" />

    <form wire:submit="submit" class="flex flex-col gap-y-1">
        <ui:input label="البريد الإلكتروني" name="user_email" infoDir="rtl" width="w-full" dir="ltr" type="email" placeholder="your@email.com" />
        <ui:button label="أرسل رابط التسجيل" wire:target="submit" class="mt-4" />
    </form>
</x-auth::layout>

<?php

use App\Actions\SendRegistrationLink;
 
new class extends \Livewire\Component {
    public string $user_email = '';
 
    protected function rules(): array
    {
        return [
            'user_email' => 'required|email|unique:users,email|max:255',
        ];
    }

    protected function messages(): array
    {
        return [
            'user_email.unique' => 'هذا البريد الإلكتروني مسجل مسبقاً. يمكنك تسجيل الدخول بدلاً من إنشاء حساب جديد.',
        ];
    }
 
    public function submit(): void
    {
        $this->validate();

        try {
            SendRegistrationLink::run($this->user_email);
            
            session()->flash('success', 'تم إرسال رابط التسجيل إلى بريدك الإلكتروني. يرجى التحقق من صندوق الوارد الخاص بك.');
            $this->reset('user_email');
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

    protected function validationAttributes(): array
    {
        return [
            'user_email' => 'البريد الإلكتروني',
        ];
    }
}; ?>
