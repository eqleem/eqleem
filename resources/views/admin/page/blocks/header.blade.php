<div x-data="{ socialModal: false }" x-cloak x-on:social-link-saved.window="socialModal = false">
    <ui:form wire:submit="save" class="!p-4  ">

        <div class="space-y-2">
            <ui:toggle name="showAvatar" label="عرض الصورة الشخصية" live />

            @if ($showAvatar)
                <ui:file name="avatar" label="الصورة الشخصية" uploadLabel="رفع صورة">
                    @if ($avatar)
                        <img src="{{ $avatar->temporaryUrl() }}" class="w-20 h-20 rounded-full object-cover mb-1">
                    @elseif ($currentAvatarPath)
                        <img src="{{ Storage::url($currentAvatarPath) }}" class="w-20 h-20 rounded-full object-cover mb-1">
                    @endif
                </ui:file>

                <ui:toggle name="showVerifiedBadge" label="شارة التوثيق" />
            @endif

            <ui:textarea name="bio" label="النبذة" placeholder="نبذة قصيرة تظهر أسفل الاسم (اتركها فارغة لإخفائها)" maxlength="250" rows="3" />

            <ui:input name="country" label="الدولة" placeholder="السعودية" />
            <ui:input name="city" label="المدينة" placeholder="الرياض" />

             

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
                            @php $network = $networks[$link->data['network'] ?? ''] ?? null; @endphp
                            <li
                                wire:sortable.item="{{ $link->id }}"
                                wire:key="social-link-{{ $link->id }}"
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
                                    <span class="text-sm font-medium text-gray-800 truncate">{{ $network['label'] ?? ($link->data['network'] ?? '') }}</span>
                                    <span class="text-xs text-gray-400 truncate" dir="ltr">{{ $link->data['url'] ?? '' }}</span>
                                </div>

                                <button
                                    type="button"
                                    wire:click="deleteSocialLink({{ $link->id }})"
                                    wire:confirm="هل أنت متأكد من حذف هذا الرابط؟"
                                    wire:loading.attr="disabled"
                                    wire:target="deleteSocialLink({{ $link->id }})"
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
use App\Models\Content;
use Illuminate\Support\Str;
use Livewire\WithFileUploads;

new class extends \Livewire\Component
{
    use EditsBlock, WithFileUploads;

    public bool $showAvatar = true;

    public $avatar = null;

    public string $currentAvatarPath = '';

    public bool $showVerifiedBadge = true;

    public string $bio = '';

    public string $country = '';

    public string $city = '';

    public string $newNetwork = 'twitter';

    public string $newUrl = '';

    protected function blockType(): string
    {
        return 'header';
    }

    public function mount(int $blockId): void
    {
        $this->blockId = $blockId;

        $data = $this->block()->data ?? [];

        $this->showAvatar = (bool) ($data['show_avatar'] ?? true);
        $this->currentAvatarPath = (string) ($data['avatar_path'] ?? '');
        $this->showVerifiedBadge = (bool) ($data['show_verified_badge'] ?? true);
        $this->bio = (string) ($data['bio'] ?? '');
        $this->country = (string) ($data['country'] ?? '');
        $this->city = (string) ($data['city'] ?? '');
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
        return [
            'bio' => 'nullable|string|max:250',
            'country' => 'nullable|string|max:100',
            'city' => 'nullable|string|max:100',
            'avatar' => 'nullable|image|max:15024',
        ];
    }

    public function addSocialLink(): void
    {
        $this->validate([
            'newNetwork' => 'required|string|in:'.implode(',', array_keys($this->networks())),
            'newUrl' => 'required|url|max:500',
        ]);

        $maxOrder = Content::query()
            ->where('block_id', $this->blockId)
            ->type('social-link')
            ->max('sort_order') ?? 0;

        Content::create([
            'block_id' => $this->blockId,
            'type' => 'social-link',
            'title' => $this->networks()[$this->newNetwork]['label'] ?? $this->newNetwork,
            'slug' => $this->newNetwork.'-'.Str::lower(Str::random(8)),
            'data' => [
                'network' => $this->newNetwork,
                'url' => $this->newUrl,
            ],
            'sort_order' => $maxOrder + 1,
            'active' => true,
            'status' => 'published',
            'published_at' => now(),
        ]);

        $this->reset('newNetwork', 'newUrl');
        $this->newNetwork = 'twitter';

        $this->dispatch('social-link-saved');
    }

    public function deleteSocialLink(int $id): void
    {
        Content::query()
            ->where('block_id', $this->blockId)
            ->type('social-link')
            ->whereKey($id)
            ->first()?->delete();
    }

    /**
     * @param  array<int, array{order: int, value: string}>  $items
     */
    public function updateSocialOrder(array $items): void
    {
        foreach ($items as $item) {
            Content::query()
                ->where('block_id', $this->blockId)
                ->type('social-link')
                ->whereKey($item['value'])
                ->update(['sort_order' => $item['order']]);
        }
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'show_avatar' => $this->showAvatar,
            'avatar_path' => $this->currentAvatarPath,
            'show_verified_badge' => $this->showVerifiedBadge,
            'bio' => $this->bio,
            'country' => $this->country,
            'city' => $this->city,
        ];

        if ($this->avatar) {
            $data['avatar_path'] = $this->avatar->storePublicly('tenant-media/'.currentTenant()->uuid.'/header', 'spaces');
        }

        $this->saveData($data);
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
            'socialLinks' => Content::query()
                ->where('block_id', $this->blockId)
                ->type('social-link')
                ->orderBy('sort_order')
                ->get(),
        ];
    }
}; ?>
