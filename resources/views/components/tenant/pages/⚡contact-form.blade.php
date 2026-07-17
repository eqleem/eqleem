<div>
    @if ($submitted)
        <div class="mt-5 mb-5 flex items-start gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 p-4 sm:p-5">
            <div class="flex size-11 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-emerald-600 ring-4 ring-emerald-100/60">
                <iconify-icon icon="solar:check-circle-bold" class="text-2xl"></iconify-icon>
            </div>
            <div class="min-w-0 pt-1">
                <p class="text-sm font-bold text-emerald-900">تم الإرسال بنجاح</p>
                <p class="mt-1 text-sm leading-relaxed text-emerald-800">{{ $successMessage }}</p>
            </div>
        </div>
    @endif

    <form wire:submit="submit" @class(['space-y-4', 'mt-5' => ! $submitted])>
        @if (($formFields['name'] ?? false) || ($formFields['phone'] ?? false))
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                @if ($formFields['name'] ?? false)
                    <div>
                        <label for="contact-name" class="mb-1.5 block text-xs font-semibold text-stone-500">الاسم الكامل</label>
                        <input
                            id="contact-name"
                            type="text"
                            wire:model="name"
                            class="w-full rounded-xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none focus:border-primary-300 focus:bg-white"
                            placeholder="اكتب اسمك"
                        />
                        @error('name') <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                @endif
                @if ($formFields['phone'] ?? false)
                    <div>
                        <label for="contact-phone" class="mb-1.5 block text-xs font-semibold text-stone-500">رقم الجوال</label>
                        <input
                            id="contact-phone"
                            type="tel"
                            dir="ltr"
                            wire:model="phone"
                            class="w-full rounded-xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none focus:border-primary-300 focus:bg-white"
                            placeholder="0541234567"
                        />
                        @error('phone') <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                @endif
            </div>
        @endif

        @if ($formFields['email'] ?? false)
            <div>
                <label for="contact-email" class="mb-1.5 block text-xs font-semibold text-stone-500">البريد الإلكتروني</label>
                <input
                    id="contact-email"
                    type="email"
                    dir="ltr"
                    wire:model="email"
                    class="w-full rounded-xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none focus:border-primary-300 focus:bg-white"
                    placeholder="name@email.com"
                />
                @error('email') <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
        @endif

        @if ($formFields['address'] ?? false)
            <div>
                <label for="contact-address" class="mb-1.5 block text-xs font-semibold text-stone-500">العنوان</label>
                <input
                    id="contact-address"
                    type="text"
                    wire:model="address"
                    class="w-full rounded-xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none focus:border-primary-300 focus:bg-white"
                    placeholder="اكتب عنوانك"
                />
                @error('address') <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
        @endif

        @if ($formFields['message'] ?? false)
            <div>
                <label for="contact-message" class="mb-1.5 block text-xs font-semibold text-stone-500">رسالتك</label>
                <textarea
                    id="contact-message"
                    rows="5"
                    wire:model="message"
                    class="w-full rounded-xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none focus:border-primary-300 focus:bg-white"
                    placeholder="اكتب تفاصيل طلبك هنا..."
                ></textarea>
                @error('message') <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
        @endif

        <button
            type="submit"
            wire:loading.attr="disabled"
            wire:target="submit"
            class="inline-flex items-center justify-center gap-2 rounded-xl bg-primary-600 px-5 py-3 text-sm font-bold text-white transition hover:bg-primary-700 disabled:cursor-not-allowed disabled:opacity-70"
        >
            <iconify-icon icon="hugeicons:sent-02" class="text-lg" wire:loading.remove wire:target="submit"></iconify-icon>
            <span wire:loading.remove wire:target="submit">إرسال الرسالة</span>
            <span wire:loading wire:target="submit">جارٍ الإرسال...</span>
        </button>
    </form>
</div>

<?php

use App\Actions\SendContactMessageEmail;
use App\Models\Content;
use App\Models\FormSubmission;
use App\Models\Tenant;
use Livewire\Component;

new class extends Component
{
    public int $pageId;

    /** @var array<string, bool> */
    public array $formFields = [];

    public string $successMessage = '';

    public string $name = '';

    public string $email = '';

    public string $phone = '';

    public string $address = '';

    public string $message = '';

    public bool $submitted = false;

    /**
     * @param  array<string, bool>  $formFields
     */
    public function mount(int $pageId, array $formFields = [], string $successMessage = ''): void
    {
        $this->pageId = $pageId;
        $this->formFields = $formFields;
        $this->successMessage = filled($successMessage)
            ? $successMessage
            : (string) Content::defaultContactPageData()['success_message'];
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    protected function rules(): array
    {
        $rules = [];

        if ($this->formFields['name'] ?? false) {
            $rules['name'] = ['required', 'string', 'max:120'];
        }

        if ($this->formFields['email'] ?? false) {
            $rules['email'] = ['required', 'email', 'max:255'];
        }

        if ($this->formFields['phone'] ?? false) {
            $rules['phone'] = ['required', 'string', 'max:30'];
        }

        if ($this->formFields['address'] ?? false) {
            $rules['address'] = ['required', 'string', 'max:255'];
        }

        if ($this->formFields['message'] ?? false) {
            $rules['message'] = ['required', 'string', 'max:5000'];
        }

        return $rules;
    }

    /**
     * @return array<string, string>
     */
    protected function messages(): array
    {
        return [
            'name.required' => 'يرجى إدخال الاسم.',
            'email.required' => 'يرجى إدخال البريد الإلكتروني.',
            'email.email' => 'يرجى إدخال بريد إلكتروني صالح.',
            'phone.required' => 'يرجى إدخال رقم الجوال.',
            'address.required' => 'يرجى إدخال العنوان.',
            'message.required' => 'يرجى كتابة الرسالة.',
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function validationAttributes(): array
    {
        return [
            'name' => 'الاسم الكامل',
            'email' => 'البريد الإلكتروني',
            'phone' => 'رقم الجوال',
            'address' => 'العنوان',
            'message' => 'الرسالة',
        ];
    }

    public function submit(): void
    {
        if (! $this->hasEnabledFields()) {
            return;
        }

        $this->validate();

        $tenantId = currentTenantId();

        if (! $tenantId) {
            $this->addError('message', 'تعذّر إرسال الرسالة حالياً.');

            return;
        }

        $page = Content::query()
            ->type(contentTypeModel('pages'))
            ->template('contact')
            ->whereKey($this->pageId)
            ->where('active', true)
            ->first();

        if (! $page) {
            $this->addError('message', 'تعذّر إرسال الرسالة حالياً.');

            return;
        }

        FormSubmission::query()->create([
            'tenant_id' => $tenantId,
            'content_id' => $page->id,
            'client_id' => authClient()?->id,
            'status' => 'new',
            'data' => ['fields' => $this->storedFields()],
            'submitted_at' => now(),
        ]);

        $tenant = Tenant::query()->find($tenantId);

        if ($tenant) {
            SendContactMessageEmail::run($tenant, [
                'name' => $this->name,
                'email' => $this->email,
                'phone' => $this->phone,
                'address' => $this->address,
                'message' => $this->message,
            ]);
        }

        $this->reset('message');
        $this->resetValidation();
        $this->submitted = true;
    }

    protected function hasEnabledFields(): bool
    {
        foreach (Content::contactFormFieldKeys() as $field) {
            if ($this->formFields[$field] ?? false) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return list<array{id: string, name: string, label: string, type: string, value: mixed}>
     */
    protected function storedFields(): array
    {
        $definitions = [
            'name' => ['label' => 'الاسم الكامل', 'type' => 'text'],
            'email' => ['label' => 'البريد الإلكتروني', 'type' => 'email'],
            'phone' => ['label' => 'رقم الجوال', 'type' => 'tel'],
            'address' => ['label' => 'العنوان', 'type' => 'text'],
            'message' => ['label' => 'الرسالة', 'type' => 'textarea'],
        ];

        $fields = [];

        foreach ($definitions as $name => $meta) {
            if (! ($this->formFields[$name] ?? false)) {
                continue;
            }

            $fields[] = [
                'id' => $name,
                'name' => $name,
                'label' => $meta['label'],
                'type' => $meta['type'],
                'value' => $this->{$name},
            ];
        }

        return $fields;
    }
};
?>
