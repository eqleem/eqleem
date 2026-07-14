<div x-data="{ socialModal: false }" x-cloak x-on:social-link-saved.window="socialModal = false">

    <ui:mainbox title="معلومات الصفحة" subtitle="تعديل اسم وشعار الصفحة .">
        <x-slot:icon>
            <ui:icon name="id" class="!w-7 !h-7 text-gray-500 p-0.5" />
        </x-slot:icon>

        <ui:form wire:submit="submit" id="catalog-info-form">
            <ui:input name="name" label="اسم الصفحة" placeholder="اسم الصفحة" />

            <ui:file-crop
                name="logo"
                label="الشعار"
                uploadLabel="رفع شعار"
                shape="square"
                cropTitle="قص الشعار"
                previewClass="mb-1 size-20 rounded-lg object-cover"
                :preview="$logo ?: ($currentLogo ?: null)"
            />

            <x-slot:footer>
                <ui:button target="submit" label="{{ __('Save') }}" />
            </x-slot>
        </ui:form>
    </ui:mainbox>

    <ui:mainbox title="بيانات الاتصال" subtitle="معلومات التواصل التي تظهر في صفحتك." class="mt-10">
        <x-slot:icon>
            <ui:icon name="phone" class="!w-7 !h-7 text-gray-500 p-0.5" />
        </x-slot:icon>

        <ui:form wire:submit="saveContact" id="contact-info-form">
            <ui:input name="phone" label="رقم الجوال للاتصال" placeholder="05xxxxxxxx" dir="ltr" />
            <ui:input name="email" label="البريد الإلكتروني" placeholder="hello@example.com" dir="ltr" />
            <ui:input name="whatsapp" label="جوال الواتساب" placeholder="966500000000" dir="ltr" />
            <ui:input name="country" label="الدولة" placeholder="السعودية" />
            <ui:input name="city" label="المدينة" placeholder="الرياض" />

            <x-slot:footer>
                <ui:button target="saveContact" label="{{ __('Save') }}" />
            </x-slot>
        </ui:form>
    </ui:mainbox>

    <ui:mainbox title="حسابات السوشال ميديا" subtitle="روابط حساباتك على شبكات التواصل." class="mt-10">
        <x-slot:icon>
            <ui:icon name="share-2" class="!w-7 !h-7 text-gray-500 p-0.5" />
        </x-slot:icon>

        <div class="space-y-2 px-4 pb-4">
            <div class="flex items-center justify-between my-4 border-b border-gray-100 pb-2 border-dotted">
                <p class="text-xs font-semibold text-gray-500">روابط التواصل</p>
                <ui:button type="button" variant="secondary" icon="square-rounded-plus" label="إضافة رابط" x-on:click="socialModal = true" />
            </div>

            @if ($socialLinks->isEmpty())
                <p class="text-xs text-gray-400 py-2">لا توجد روابط بعد. أضف أول رابط تواصل.</p>
            @else
                <ul
                    wire:sortable="updateSocialOrder"
                    wire:sortable.options="{ animation: 150 }"
                    class="space-y-1.5"
                >
                    @foreach ($socialLinks as $link)
                        @php $network = $networks[$link['network'] ?? ''] ?? null; @endphp
                        <li
                            wire:sortable.item="{{ $link['id'] }}"
                            wire:key="social-link-{{ $link['id'] }}"
                            class="group flex items-center gap-2 rounded-lg border border-gray-100 bg-white px-2 py-2 hover:border-gray-200 transition"
                        >
                            <button
                                type="button"
                                wire:sortable.handle
                                class="cursor-grab active:cursor-grabbing rounded-md p-1 text-gray-300 hover:bg-gray-100 hover:text-gray-500 transition"
                                aria-label="سحب لإعادة الترتيب"
                            >
                                <ui:icon name="grip-vertical" class="!w-4 !h-4" />
                            </button>

                            <iconify-icon icon="{{ $network['icon'] ?? 'ri:link' }}" class="text-xl text-gray-500 shrink-0"></iconify-icon>

                            <div class="flex flex-1 flex-col items-center justify-start">
                                <span class="text-sm font-medium text-gray-800 truncate">{{ $network['label'] ?? ($link['network'] ?? '') }}</span>
                                <span class="text-xs text-gray-400 truncate" dir="ltr">{{ $link['url'] ?? '' }}</span>
                            </div>

                            <button
                                type="button"
                                wire:click="deleteSocialLink('{{ $link['id'] }}')"
                                wire:confirm="هل أنت متأكد من حذف هذا الرابط؟"
                                wire:loading.attr="disabled"
                                wire:target="deleteSocialLink('{{ $link['id'] }}')"
                                class="shrink-0 rounded-lg p-1 text-red-400/80 hover:bg-red-50 hover:text-red-500 transition"
                                aria-label="حذف الرابط"
                            >
                                <ui:icon name="trash" class="!w-4 !h-4" />
                            </button>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </ui:mainbox>

    <div
        x-show="socialModal"
        x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center p-4"
        x-transition.opacity
    >
        <div class="absolute inset-0 bg-gray-800/75" x-on:click="socialModal = false"></div>

        <div class="relative w-full max-w-md rounded-xl bg-white shadow-xl">
            <div class="flex items-center justify-between border-b border-gray-100 p-3 px-4">
                <p class="text-sm font-semibold text-gray-600">إضافة رابط تواصل</p>
                <button type="button" x-on:click="socialModal = false" class="rounded-md bg-gray-100 p-1 text-gray-400 hover:bg-gray-200">
                    <ui:icon name="x" class="!w-4 !h-4" />
                </button>
            </div>

            <div class="space-y-3 p-4">
                <ui:select name="newNetwork" label="الشبكة" :options="$networkOptions" />
                <ui:input name="newUrl" label="الرابط" placeholder="https://..." dir="ltr" />
            </div>

            <div class="flex justify-end gap-2 border-t border-gray-100 p-3 px-4">
                <ui:button type="button" variant="ghost" label="إلغاء" x-on:click="socialModal = false" />
                <ui:button type="button" wire:click="addSocialLink" target="addSocialLink" label="إضافة" />
            </div>
        </div>
    </div>
</div>

<?php

use App\Services\TenantProfileService;
use Livewire\WithFileUploads;

new class extends \Livewire\Component
{
    use WithFileUploads;

    public $tenant;

    public $name;

    public $logo;

    public $currentLogo;

    public string $phone = '';

    public string $email = '';

    public string $whatsapp = '';

    public string $country = '';

    public string $city = '';

    public string $newNetwork = 'twitter';

    public string $newUrl = '';

    public function rules(): array
    {
        return [
            'name' => 'required|string|min:2|max:255',
            'logo' => 'nullable|image|max:15024',
        ];
    }

    protected function contactRules(): array
    {
        return [
            'phone' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:255',
            'whatsapp' => 'nullable|string|max:30',
            'country' => 'nullable|string|max:100',
            'city' => 'nullable|string|max:100',
        ];
    }

    /**
     * @return array<string, array{label: string, icon: string}>
     */
    protected function networks(): array
    {
        return config('social-networks', []);
    }

    public function mount(): void
    {
        $this->tenant = currentTenant();
        $profile = app(TenantProfileService::class);

        $this->name = $this->tenant->name;
        $this->currentLogo = $this->tenant->logo;

        $contact = $profile->contact($this->tenant);
        $this->phone = $contact['phone'];
        $this->email = $contact['email'];
        $this->whatsapp = $contact['whatsapp'];
        $this->country = $contact['country'];
        $this->city = $contact['city'];
    }

    public function submit(): void
    {
        $this->validate();

        $this->tenant->name = $this->name;

        if ($this->logo) {
            $path = $this->logo->storePublicly('tenant-media/'.$this->tenant->uuid.'/logo', 'spaces');
            app(TenantProfileService::class)->saveLogo($this->tenant, $path);
            $this->currentLogo = $this->tenant->fresh()->logo;
            $this->reset('logo');
        }

        $this->tenant->save();

        $this->dispatch('notify', text: __('Settings updated successfully.'));
    }

    public function saveContact(): void
    {
        $this->validate($this->contactRules());

        app(TenantProfileService::class)->saveContact($this->tenant, [
            'phone' => $this->phone,
            'email' => $this->email,
            'whatsapp' => $this->whatsapp,
            'country' => $this->country,
            'city' => $this->city,
        ]);

        $this->dispatch('notify', text: __('Settings updated successfully.'));
    }

    public function addSocialLink(): void
    {
        $this->validate([
            'newNetwork' => 'required|string|in:'.implode(',', array_keys($this->networks())),
            'newUrl' => 'required|string|max:500',
        ]);

        app(TenantProfileService::class)->addSocialLink($this->tenant, $this->newNetwork, $this->newUrl);

        $this->reset('newNetwork', 'newUrl');
        $this->newNetwork = 'twitter';

        $this->dispatch('social-link-saved');
        $this->dispatch('notify', text: __('Settings updated successfully.'));
    }

    public function deleteSocialLink(string $id): void
    {
        app(TenantProfileService::class)->deleteSocialLink($this->tenant, $id);
    }

    /**
     * @param  array<int, array{order: int, value: string}>  $items
     */
    public function updateSocialOrder(array $items): void
    {
        app(TenantProfileService::class)->updateSocialOrder($this->tenant, $items);
    }

    /**
     * @return array<string, mixed>
     */
    public function with(): array
    {
        return [
            'networks' => $this->networks(),
            'networkOptions' => collect($this->networks())
                ->map(fn (array $network): string => $network['label'])
                ->all(),
            'socialLinks' => app(TenantProfileService::class)->socialLinks($this->tenant),
        ];
    }
}; ?>
