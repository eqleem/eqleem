<div class="p-4 space-y-6">
    <div class="border-b border-dotted border-gray-200 pb-4">
        <p class="text-sm font-semibold text-gray-700">الحسابات البنكية</p>
        <p class="mt-1 text-xs text-gray-500">/ أضف حساباتك البنكية المتاحة لاستقبال الحوالات للطلبات.</p>
    </div>

    @if ($showAccountForm)
        <div class="rounded-xl border border-primary-200 bg-primary-50/30 p-4 space-y-4">
            <div class="flex items-center justify-between gap-3 border-b border-dotted border-primary-200 pb-3">
                <p class="text-sm font-semibold text-gray-800">
                    {{ $editingAccountId ? 'تعديل حساب بنكي' : 'أضف حساب بنكي' }}
                </p>
                <button
                    type="button"
                    wire:click="cancelAccountForm"
                    class="rounded-lg p-1 text-gray-400 hover:bg-white hover:text-gray-600 transition"
                    aria-label="إلغاء"
                >
                    <ui:icon name="x" class="!h-4 !w-4" />
                </button>
            </div>

            <ui:form wire:submit="saveAccount" id="bank-account-form">
                <ui:input name="bankName" label="اسم البنك" placeholder="مثال: الراجحي" />
                <ui:input name="accountName" label="اسم صاحب الحساب" placeholder="مثال: شركة ..." />
                <ui:input name="iban" label="رقم الآيبان" placeholder="SA..." dir="ltr" />
                <ui:input name="accountNumber" label="رقم الحساب" placeholder="1234567890" dir="ltr" />

                <x-slot:footer>
                    <div class="flex items-center gap-2">
                        <ui:button target="saveAccount" icon="check" label="{{ __('Save') }}" />
                        <ui:button type="button" wire:click="cancelAccountForm" variant="secondary" label="إلغاء" />
                    </div>
                </x-slot:footer>
            </ui:form>
        </div>
    @endif

    @if ($accounts === [] && ! $showAccountForm)
        <div class="rounded-xl border border-gray-100 bg-gray-50/70 px-6 py-10 text-center">
            <ui:icon name="building-bank" class="mx-auto !h-12 !w-12 text-gray-300" />
            <p class="mt-4 text-sm font-semibold text-gray-600">لا توجد حسابات بنكية بعد.</p>
            <p class="mt-1 text-xs text-gray-400">قم بإضافة الحسابات البنكية التي يمكن لعملائك تحويل الأموال إليها.</p>
            <ui:button
                type="button"
                wire:click="openAccountForm"
                icon="square-rounded-plus"
                label="أضف حساب بنكي"
                class="mt-5"
            />
        </div>
    @elseif ($accounts !== [])
        <ul class="divide-y divide-gray-100 rounded-xl border border-gray-100">
            @foreach ($accounts as $index => $account)
                <li wire:key="bank-account-{{ $account['id'] ?? $index }}" class="flex items-center gap-3 px-4 py-3">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-blue-50">
                        <ui:icon name="building-bank" class="!h-5 !w-5 text-blue-500" />
                    </div>

                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-semibold text-gray-800 truncate">{{ $account['bank_name'] ?: 'حساب بنكي' }}</p>
                        <p class="mt-0.5 text-xs text-gray-500 truncate">{{ $account['account_name'] }}</p>
                        @if (filled($account['iban']))
                            <p class="mt-0.5 text-xs text-gray-400 truncate" dir="ltr">{{ $account['iban'] }}</p>
                        @endif
                    </div>

                    <div class="flex shrink-0 items-center gap-1">
                        <button
                            type="button"
                            wire:click="openAccountForm('{{ $account['id'] }}')"
                            class="rounded-lg p-1.5 text-gray-400 hover:bg-gray-100 hover:text-primary-600 transition"
                            aria-label="تعديل الحساب"
                        >
                            <ui:icon name="pencil" class="!h-4 !w-4" />
                        </button>
                        <button
                            type="button"
                            wire:click="removeAccount('{{ $account['id'] }}')"
                            wire:confirm="هل أنت متأكد من حذف هذا الحساب؟"
                            class="rounded-lg p-1.5 text-gray-400 hover:bg-red-50 hover:text-red-500 transition"
                            aria-label="حذف الحساب"
                        >
                            <ui:icon name="trash" class="!h-4 !w-4" />
                        </button>
                    </div>
                </li>
            @endforeach
        </ul>

        @if (! $showAccountForm)
            <ui:button
                type="button"
                wire:click="openAccountForm"
                icon="square-rounded-plus"
                label="أضف حساب بنكي"
                variant="secondary"
            />
        @endif
    @endif

    <div class="flex justify-end border-t border-dotted border-gray-200 pt-4">
        <ui:button wire:click="submit" target="submit" icon="check" label="{{ __('Save') }}" />
    </div>
</div>

<?php

use App\Models\Setting;
use Illuminate\Support\Str;

new class extends \Livewire\Component
{
    public string $slug = 'bank-transfer';

    public bool $showAccountForm = false;

    /** @var list<array<string, mixed>> */
    public array $accounts = [];

    public ?string $editingAccountId = null;

    public string $bankName = '';

    public string $accountName = '';

    public string $iban = '';

    public string $accountNumber = '';

    public function mount(string $slug): void
    {
        $this->slug = $slug;
        $this->loadSettings();
    }

    public function rules(): array
    {
        return [
            'accounts' => ['array'],
            'accounts.*.bank_name' => ['required', 'string', 'max:120'],
            'accounts.*.account_name' => ['required', 'string', 'max:120'],
            'accounts.*.iban' => ['nullable', 'string', 'max:34'],
            'accounts.*.account_number' => ['nullable', 'string', 'max:40'],
        ];
    }

    /**
     * @return array<string, array<int, string>>
     */
    protected function accountFormRules(): array
    {
        return [
            'bankName' => ['required', 'string', 'max:120'],
            'accountName' => ['required', 'string', 'max:120'],
            'iban' => ['nullable', 'string', 'max:34'],
            'accountNumber' => ['nullable', 'string', 'max:40'],
        ];
    }

    public function openAccountForm(?string $accountId = null): void
    {
        $this->editingAccountId = $accountId;
        $this->resetAccountForm();
        $this->showAccountForm = true;

        if ($accountId !== null) {
            $account = collect($this->accounts)
                ->first(fn (array $row): bool => (string) data_get($row, 'id') === $accountId);

            if (is_array($account)) {
                $this->bankName = (string) data_get($account, 'bank_name', '');
                $this->accountName = (string) data_get($account, 'account_name', '');
                $this->iban = (string) data_get($account, 'iban', '');
                $this->accountNumber = (string) data_get($account, 'account_number', '');
            }
        }
    }

    public function cancelAccountForm(): void
    {
        $this->showAccountForm = false;
        $this->editingAccountId = null;
        $this->resetAccountForm();
    }

    public function saveAccount(): void
    {
        $this->validate($this->accountFormRules());

        $account = [
            'id' => $this->editingAccountId ?: (string) Str::uuid(),
            'bank_name' => trim($this->bankName),
            'account_name' => trim($this->accountName),
            'iban' => trim($this->iban),
            'account_number' => trim($this->accountNumber),
        ];

        $existingIndex = collect($this->accounts)->search(
            fn (array $row): bool => (string) data_get($row, 'id') === (string) data_get($account, 'id')
        );

        if ($existingIndex !== false) {
            $this->accounts[$existingIndex] = $account;
        } else {
            $this->accounts[] = $account;
        }

        $this->cancelAccountForm();
    }

    public function removeAccount(string $accountId): void
    {
        $this->accounts = collect($this->accounts)
            ->reject(fn (array $account): bool => (string) data_get($account, 'id') === $accountId)
            ->values()
            ->all();
    }

    public function submit(): void
    {
        $this->validate();

        Setting::savePaymentMethod($this->slug, [
            'accounts' => $this->accounts,
        ], (bool) data_get(Setting::paymentMethod($this->slug), 'active', false));

        $this->dispatch('paymentMethodSaved');
        $this->dispatch('notify', text: __('Settings updated successfully.'));
        $this->dispatch('closemodal', modal: 'payment-method-'.$this->slug);
    }

    protected function loadSettings(): void
    {
        $saved = Setting::paymentMethod($this->slug);

        $this->accounts = collect(data_get($saved, 'accounts', []))
            ->map(function (array $account): array {
                return array_merge($account, [
                    'id' => (string) data_get($account, 'id', (string) Str::uuid()),
                ]);
            })
            ->values()
            ->all();
    }

    protected function resetAccountForm(): void
    {
        $this->reset(['bankName', 'accountName', 'iban', 'accountNumber']);
        $this->resetValidation(array_keys($this->accountFormRules()));
    }
}; ?>
