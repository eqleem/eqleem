<div>

    <ui:mainbox title="الدومين المجاني" subtitle="تعديل الدومين الفرعي للمجاني من إقليم في حال كنت لا تملك دومين مخصص بعد.">
        <x-slot:icon>
            <img src="{{ asset('assets/icons/business/015-cloud-network.svg') }}" class="w-7 h-7" alt="">
        </x-slot:icon>

        <ui:form wire:submit="submit" id="domain-form">

            <ui:input
                name="handle"
                label="رابط الصفحة"
                placeholder="admin"
                prefix="https://"
                dir="ltr"
                :suffix="'.'.config('app.domain')"
            />

            <x-slot:footer>
                <ui:button target="submit" label="{{ __('Save') }}" />
            </x-slot>
        </ui:form>

    </ui:mainbox>

    <ui:mainbox
        title="الدومين المخصص"
        subtitle="اربط دومينك الخاص بصفحتك على إقليم."
        class="mt-10"
        prime
    >
        <x-slot:icon>
            <ui:icon name="world" class="!w-7 !h-7 text-gray-500 p-0.5" />
        </x-slot:icon>

        <x-slot:actions>
            <ui:badge color="{{ $customDomainStatusColor }}" size="sm">
                {{ $customDomainStatusLabel }}
            </ui:badge>
        </x-slot:actions>

        <ui:form wire:submit="submitCustomDomain" id="custom-domain-form">
            <ui:input
                name="customDomain"
                label="الدومين المخصص"
                placeholder="shop.example.com"
                dir="ltr"
                info="أدخل الدومين بدون https:// — مثل shop.example.com أو www.example.com"
                infoDir="rtl"
            />

            @if (filled($customDomain))
                <div class="mx-4 mb-2 rounded-xl border border-dashed border-blue-200 bg-blue-50/60 p-4">
                    <div class="flex items-start gap-3">
                        <ui:icon name="info-circle" class="!w-5 !h-5 text-blue-500 shrink-0 mt-0.5" />
                        <div class="space-y-3 w-full">
                            <div>
                                <p class="text-sm font-semibold text-blue-800">إعدادات DNS — سجل CNAME</p>
                                <p class="text-xs text-blue-700/80 mt-1">
                                    أضف السجل التالي من لوحة تحكم مزوّد الدومين (GoDaddy، Cloudflare، Namecheap، وغيرها).
                                    قد يستغرق انتشار التغييرات من 5 دقائق إلى 48 ساعة.
                                </p>
                            </div>

                            <dl class="divide-y divide-blue-100 rounded-lg border border-blue-100 bg-white text-sm">
                                <div class="flex items-center justify-between gap-4 px-3 py-2.5">
                                    <dt class="text-xs font-medium text-gray-500">النوع</dt>
                                    <dd class="font-mono text-xs text-gray-800" dir="ltr">CNAME</dd>
                                </div>
                                <div class="flex items-center justify-between gap-4 px-3 py-2.5">
                                    <dt class="text-xs font-medium text-gray-500">الاسم (Host)</dt>
                                    <dd class="font-mono text-xs text-gray-800" dir="ltr">{{ $customDomainHost }}</dd>
                                </div>
                                <div class="flex items-center justify-between gap-4 px-3 py-2.5">
                                    <dt class="text-xs font-medium text-gray-500">القيمة (يشير إلى)</dt>
                                    <dd class="font-mono text-xs text-gray-800 break-all text-left" dir="ltr">host.{{ config('app.domain') }}</dd>
                                </div>
                            </dl>

                            <p class="text-xs text-blue-700/80">
                                بعد حفظ السجل، سيبقى الدومين في حالة «قيد التحقق» حتى نتأكد من إعداد CNAME بشكل صحيح.
                            </p>
                        </div>
                    </div>
                </div>
            @else
                <div class="mx-4 mb-2 rounded-xl border border-dashed border-gray-200 bg-gray-50/60 p-4">
                    <p class="text-xs text-gray-500">
                        بعد إدخال الدومين المخصص، ستظهر هنا تعليمات إعداد سجل CNAME في DNS.
                    </p>
                </div>
            @endif

            <x-slot:footer>
                <ui:button target="submitCustomDomain" label="{{ __('Save') }}" />
            </x-slot:footer>
        </ui:form>

    </ui:mainbox>
</div>

<?php

use App\Models\Tenant;
use Illuminate\Validation\Rule;

new class extends \Livewire\Component {
    public $tenant;

    public $handle;

    public string $customDomain = '';

    public ?string $customDomainStatus = null;

    public function rules(): array
    {
        return [
            'handle' => [
                'required',
                'min:2',
                'max:100',
                'alpha_dash:ascii',
                Rule::unique('tenants', 'handle')->ignore($this->tenant->id),
            ],
        ];
    }

    protected function customDomainRules(): array
    {
        return [
            'customDomain' => [
                'nullable',
                'string',
                'max:255',
                'regex:/^(?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\.)+[a-z]{2,}$/i',
            ],
        ];
    }

    public function mount(): void
    {
        $this->tenant = currentTenant();
        $this->handle = $this->tenant->handle;
        $this->customDomain = (string) ($this->tenant->custom_domain ?? '');
        $this->customDomainStatus = $this->tenant->custom_domain_status;
    }

    public function submit(): void
    {
        $this->validate();

        $this->tenant->handle = $this->handle;
        $this->tenant->save();

        setCurrentTenant($this->tenant->fresh());

        $this->dispatch('notify', text: __('Settings updated successfully.'));
    }

    public function submitCustomDomain(): void
    {
        $this->customDomain = $this->normalizeDomain($this->customDomain);

        $this->validate($this->customDomainRules());

        if (filled($this->customDomain) && $this->customDomainIsTaken($this->customDomain)) {
            $this->addError('customDomain', 'هذا الدومين مستخدم بالفعل.');

            return;
        }

        $normalized = $this->customDomain;

        if (blank($normalized)) {
            $this->tenant->custom_domain = null;
            $this->tenant->custom_domain_status = null;
            $this->customDomainStatus = null;
        } else {
            $previousDomain = (string) ($this->tenant->custom_domain ?? '');

            $this->tenant->custom_domain = $normalized;
            $this->customDomainStatus = $previousDomain === $normalized && filled($this->tenant->custom_domain_status)
                ? (string) $this->tenant->custom_domain_status
                : 'pending';
            $this->tenant->custom_domain_status = $this->customDomainStatus;
        }

        $this->customDomain = $normalized;
        $this->tenant->save();

        $this->dispatch('notify', text: __('Settings updated successfully.'));
    }

    protected function customDomainIsTaken(string $domain): bool
    {
        return Tenant::query()
            ->where('id', '!=', $this->tenant->id)
            ->where('custom_domain', $domain)
            ->exists();
    }

    protected function normalizeDomain(string $domain): string
    {
        $domain = strtolower(trim($domain));
        $domain = preg_replace('#^https?://#', '', $domain) ?? $domain;
        $domain = rtrim($domain, '/');

        return $domain;
    }

    protected function customDomainHost(): string
    {
        if (blank($this->customDomain)) {
            return 'www';
        }

        $parts = explode('.', $this->customDomain);

        if (count($parts) <= 2) {
            return '@';
        }

        return implode('.', array_slice($parts, 0, -2));
    }

    protected function statusLabel(?string $status): string
    {
        return match ($status) {
            'pending' => 'قيد التحقق',
            'active' => 'مُفعّل',
            'failed' => 'فشل التحقق',
            default => 'غير مُضاف',
        };
    }

    protected function statusColor(?string $status): string
    {
        return match ($status) {
            'pending' => 'yellow',
            'active' => 'green',
            'failed' => 'red',
            default => 'gray',
        };
    }

    /**
     * @return array<string, mixed>
     */
    public function with(): array
    {
        return [
            'cnameTarget' => $this->handle.'.'.config('app.domain'),
            'customDomainHost' => $this->customDomainHost(),
            'customDomainStatusLabel' => $this->statusLabel($this->customDomainStatus),
            'customDomainStatusColor' => $this->statusColor($this->customDomainStatus),
        ];
    }
};
?>
