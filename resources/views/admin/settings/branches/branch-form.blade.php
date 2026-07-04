<ui:form wire:submit="submit" class="max-h-[75vh] overflow-y-auto">
    <ui:toggle name="active" label="الحالة" live />

    <ui:input name="name" label="الاسم" placeholder="الفرع الرئيسي" />

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
        <ui:select name="country" label="الدولة" :options="$countryOptions" live />
        <ui:select name="city" label="المدينة" :options="$cityOptions" placeholder="" />
    </div>

    <ui:input name="address" label="العنوان" placeholder="الحي واسم الشارع" />
    <ui:input name="postalCode" label="الرمز البريدي" placeholder="12345" dir="ltr" />

    <ui:input name="email" label="البريد الإلكتروني" placeholder="a@aa.aaa" type="email" dir="ltr" />

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
        <ui:select name="phonecode" label="رمز الدولة" :options="$phonecodeOptions" />
        <div class="sm:col-span-2">
            <ui:input name="phone" label="رقم الجوال" placeholder="512345678" dir="ltr" />
        </div>
    </div>

    <div class="space-y-1">
        <ui:toggle name="isWarehouse" label="مستودع تخزين؟" live />
        <p class="text-xs text-gray-400 ms-36">يستقبل شركات الشحن لاستلام الشحنات وتسليمها للعميل.</p>
    </div>

    <div class="space-y-1">
        <ui:toggle name="isPickup" label="يمكن الاستلام منه؟" live />
        <p class="text-xs text-gray-400 ms-36">يستقبل العميل لاستلام شحنته بنفسه.</p>
    </div>

    <div class="rounded-xl border border-gray-200 overflow-hidden mt-2">
        <div class="bg-gray-50 px-4 py-2 text-sm font-medium text-gray-700">ساعات العمل</div>

        <div class="divide-y divide-gray-100">
            @foreach ($weekdayLabels as $day => $label)
                <div wire:key="branch-day-{{ $day }}" class="flex flex-wrap items-center gap-3 px-4 py-3">
                    <label class="flex items-center gap-2 w-28 shrink-0">
                        <input
                            type="checkbox"
                            wire:model.live="workingHours.{{ $day }}.enabled"
                            class="rounded border-gray-300"
                        >
                        <span class="text-sm text-gray-700">{{ $label }}</span>
                    </label>

                    <div class="flex items-center gap-2 flex-1 min-w-[220px]">
                        <input
                            type="time"
                            wire:model="workingHours.{{ $day }}.start"
                            @disabled(! ($workingHours[$day]['enabled'] ?? false))
                            class="rounded-lg border border-gray-200 px-2 py-1.5 text-sm text-gray-700 disabled:bg-gray-100 disabled:text-gray-400"
                        >
                        <span class="text-gray-400 text-sm">إلى</span>
                        <input
                            type="time"
                            wire:model="workingHours.{{ $day }}.end"
                            @disabled(! ($workingHours[$day]['enabled'] ?? false))
                            class="rounded-lg border border-gray-200 px-2 py-1.5 text-sm text-gray-700 disabled:bg-gray-100 disabled:text-gray-400"
                        >
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <x-slot:footer>
        <div class="flex w-full items-center justify-between gap-3">
            <div>
                @if ($branchId)
                    <button
                        type="button"
                        wire:click="deleteBranch"
                        wire:confirm="هل أنت متأكد من حذف هذا الفرع؟"
                        wire:loading.attr="disabled"
                        wire:target="deleteBranch"
                        class="text-sm text-red-500 hover:text-red-600"
                    >
                        حذف؟
                    </button>
                @endif
            </div>

            <div class="flex items-center gap-2">
                <ui:button
                    type="button"
                    variant="ghost"
                    label="إلغاء"
                    wire:click="cancel"
                />
                <ui:button target="submit" :label="$branchId ? 'تعديل' : 'إضافة'" />
            </div>
        </div>
    </x-slot:footer>
</ui:form>

<?php

use App\Models\Branch;
use App\Models\Calendar;
use Livewire\Attributes\Locked;

new class extends \Livewire\Component
{
    #[Locked]
    public ?int $branchId = null;

    public bool $active = true;

    public string $name = '';

    public string $country = 'SA';

    public string $city = '';

    public string $address = '';

    public string $postalCode = '';

    public string $email = '';

    public string $phonecode = '+966';

    public string $phone = '';

    public bool $isWarehouse = true;

    public bool $isPickup = false;

    /** @var array<string, array{enabled: bool, start: string, end: string}> */
    public array $workingHours = [];

    public function mount(?int $branchId = null): void
    {
        $this->branchId = $branchId;
        $this->workingHours = Calendar::defaultAvailabilities();

        if ($branchId) {
            $this->loadBranch();
        }
    }

    public function loadBranch(): void
    {
        $branch = Branch::query()->findOrFail($this->branchId);

        $this->active = (bool) $branch->active;
        $this->name = $branch->display_name;
        $this->country = (string) ($branch->country ?: 'SA');
        $this->city = (string) $branch->city;
        $this->address = (string) $branch->address;
        $this->postalCode = (string) $branch->postal_code;
        $this->email = (string) $branch->email;
        $this->phonecode = (string) ($branch->phonecode ?: '+966');
        $this->phone = (string) $branch->phone;
        $this->isWarehouse = (bool) $branch->is_warehouse;
        $this->isPickup = (bool) $branch->is_pickup;
        $this->workingHours = $branch->workingHours();
    }

    /**
     * @return array<string, string>
     */
    public function countryOptions(): array
    {
        return config('verification.countries', []);
    }

    /**
     * @return array<string, string>
     */
    public function cityOptions(): array
    {
        return config('branches.cities', []);
    }

    /**
     * @return array<string, string>
     */
    public function phonecodeOptions(): array
    {
        return [
            '+966' => '+966',
            '+971' => '+971',
            '+973' => '+973',
            '+965' => '+965',
            '+968' => '+968',
            '+974' => '+974',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function weekdayLabels(): array
    {
        return Calendar::weekdayLabels();
    }

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        return [
            'name' => 'required|string|min:1|max:255',
            'country' => 'required|string|max:3',
            'city' => 'required|string|max:120',
            'address' => 'nullable|string|max:255',
            'postalCode' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:150',
            'phonecode' => 'nullable|string|max:6',
            'phone' => 'nullable|string|max:14',
            'active' => 'boolean',
            'isWarehouse' => 'boolean',
            'isPickup' => 'boolean',
            'workingHours' => 'array',
        ];
    }

    public function submit(): void
    {
        $this->validate();

        $attributes = [
            'name' => Branch::localizedName($this->name),
            'country' => $this->country,
            'city' => $this->city,
            'address' => $this->address,
            'postal_code' => $this->postalCode,
            'email' => $this->email,
            'phonecode' => $this->phonecode,
            'phone' => $this->phone,
            'active' => $this->active,
            'is_warehouse' => $this->isWarehouse,
            'is_pickup' => $this->isPickup,
        ];

        if ($this->branchId) {
            $branch = Branch::query()->findOrFail($this->branchId);
            $branch->update($attributes);
            $branch->setWorkingHours($this->workingHours);
            $branch->save();
        } else {
            $attributes['order'] = (int) Branch::query()->max('order') + 1;

            $branch = Branch::query()->create($attributes);
            $branch->setWorkingHours($this->workingHours);
            $branch->save();

            $this->resetForm();
        }

        $this->dispatch('updateBranchList');
        $this->dispatch('closemodal', modal: 'branch-form');
        $this->dispatch('notify', text: __('Saved'));
    }

    public function deleteBranch(): void
    {
        if (! $this->branchId) {
            return;
        }

        Branch::query()->findOrFail($this->branchId)->delete();

        $this->dispatch('updateBranchList');
        $this->dispatch('closemodal', modal: 'branch-form');
        $this->dispatch('notify', text: __('Item(s) deleted successfully.'));
    }

    public function cancel(): void
    {
        $this->dispatch('closemodal', modal: 'branch-form');
    }

    protected function resetForm(): void
    {
        $this->reset([
            'name',
            'country',
            'city',
            'address',
            'postalCode',
            'email',
            'phone',
            'active',
            'isWarehouse',
            'isPickup',
        ]);

        $this->country = 'SA';
        $this->phonecode = '+966';
        $this->active = true;
        $this->isWarehouse = true;
        $this->isPickup = false;
        $this->workingHours = Calendar::defaultAvailabilities();
    }

    public function render()
    {
        return $this->view([
            'countryOptions' => $this->countryOptions(),
            'cityOptions' => $this->cityOptions(),
            'phonecodeOptions' => $this->phonecodeOptions(),
            'weekdayLabels' => $this->weekdayLabels(),
        ]);
    }
}; ?>
