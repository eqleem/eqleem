<div x-data="{ socialModal: false }" x-cloak x-on:social-link-saved.window="socialModal = false">
    <ui:form wire:submit="save" class="!p-4  ">

        <div class="space-y-2">
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

            <ui:textarea name="bio" label="النبذة" placeholder="نبذة قصيرة تظهر أسفل الاسم (اتركها فارغة لإخفائها)" maxlength="250" rows="3" />

            <ui:input name="country" label="الدولة" placeholder="السعودية" />
            <ui:input name="city" label="المدينة" placeholder="الرياض" />

            {{-- @if ($variantOptions !== [])
                <ui:select name="variant" label="تنسيق الهيدر" :options="$variantOptions" />
            @endif --}}

            <div class="space-y-2">
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
        </div>

        <x-slot:footer>
            <ui:button type="submit" target="save" label="{{ __('Save') }}" />
        </x-slot:footer>
    </ui:form>

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

use App\Livewire\Concerns\EditsBlock;
use App\Services\TenantProfileService;
use App\Support\BlockVariants;
use Illuminate\Validation\Rule;
use Livewire\WithFileUploads;

new class extends \Livewire\Component
{
    use EditsBlock, WithFileUploads;

    public string $name = '';

    public bool $showAvatar = true;

    public $logo = null;

    public string $currentLogo = '';

    public bool $showVerifiedBadge = true;

    public string $bio = '';

    public string $country = '';

    public string $city = '';

    public string $variant = '';

    public string $newNetwork = 'twitter';

    public string $newUrl = '';

    protected function blockType(): string
    {
        return 'header';
    }

    public function mount(int $blockId): void
    {
        $this->blockId = $blockId;

        $tenant = currentTenant();
        $block = $this->block();
        $data = $block->data ?? [];
        // $variantOptions = app(BlockVariants::class)->optionsFor($this->blockType());

        $this->name = (string) ($tenant?->name ?? '');
        $this->currentLogo = (string) ($tenant?->logo ?? '');
        $this->showAvatar = (bool) ($data['show_avatar'] ?? true);
        $this->showVerifiedBadge = (bool) ($data['show_verified_badge'] ?? true);
        $this->bio = (string) ($data['bio'] ?? '');

        $contact = app(TenantProfileService::class)->contact($tenant);
        $this->country = $contact['country'];
        $this->city = $contact['city'];

        // $this->variant = (string) ($block->variant ?: array_key_first($variantOptions) ?: $this->blockType());
    }

    /**
     * @return array<string, array{label: string, icon: string}>
     */
    protected function networks(): array
    {
        return config('social-networks', []);
    }

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        // $variantOptions = app(BlockVariants::class)->optionsFor($this->blockType());

        return [
            'name' => 'required|string|min:2|max:255',
            'bio' => 'nullable|string|max:250',
            'country' => 'nullable|string|max:100',
            'city' => 'nullable|string|max:100',
            'logo' => 'nullable|image|max:15024',
            // ...($variantOptions !== [] ? [
            //     'variant' => [
            //         'required',
            //         'string',
            //         Rule::in(array_keys($variantOptions)),
            //     ],
            // ] : []),
        ];
    }

    public function addSocialLink(): void
    {
        $this->validate([
            'newNetwork' => 'required|string|in:'.implode(',', array_keys($this->networks())),
            'newUrl' => 'required|url|max:500',
        ]);

        $tenant = currentTenant();

        if ($tenant) {
            app(TenantProfileService::class)->addSocialLink($tenant, $this->newNetwork, $this->newUrl);
        }

        $this->reset('newNetwork', 'newUrl');
        $this->newNetwork = 'twitter';

        $this->dispatch('social-link-saved');
    }

    public function deleteSocialLink(string $id): void
    {
        $tenant = currentTenant();

        if ($tenant) {
            app(TenantProfileService::class)->deleteSocialLink($tenant, $id);
        }
    }

    /**
     * @param  array<int, array{order: int, value: string}>  $items
     */
    public function updateSocialOrder(array $items): void
    {
        $tenant = currentTenant();

        if ($tenant) {
            app(TenantProfileService::class)->updateSocialOrder($tenant, $items);
        }
    }

    public function save(): void
    {
        $this->validate();

        $tenant = currentTenant();

        if ($tenant) {
            $tenant->name = $this->name;

            if ($this->logo) {
                $path = $this->logo->storePublicly('tenant-media/'.$tenant->uuid.'/logo', 'spaces');
                app(TenantProfileService::class)->saveLogo($tenant, $path);
            } else {
                $tenant->save();
            }

            $this->currentLogo = $tenant->logo;
            $this->reset('logo');
        }

        if ($tenant) {
            app(TenantProfileService::class)->saveContact($tenant, [
                'country' => $this->country,
                'city' => $this->city,
            ]);
        }

        $attributes = [
            'data' => [
                'show_avatar' => $this->showAvatar,
                'show_verified_badge' => $this->showVerifiedBadge,
                'bio' => $this->bio,
            ],
        ];

        if (app(BlockVariants::class)->optionsFor($this->blockType()) !== []) {
            $attributes['variant'] = $this->variant;
        }

        $this->block()->update($attributes);

        $this->notifyStructureChanged();
        $this->dispatch('closemodal');
    }

    /**
     * @return array<string, mixed>
     */
    public function with(): array
    {
        return [
            // 'variantOptions' => app(BlockVariants::class)->optionsFor($this->blockType()),
            'networks' => $this->networks(),
            'networkOptions' => collect($this->networks())
                ->map(fn (array $network): string => $network['label'])
                ->all(),
            'socialLinks' => currentTenant()
                ? app(TenantProfileService::class)->socialLinks(currentTenant())
                : collect(),
        ];
    }
}; ?>
