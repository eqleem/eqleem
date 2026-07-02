<div>
    <ui:mainbox :title="$item['title']" :subtitle="$contentType['name']">
        <x-slot:icon>
            <img src="{{ asset($contentType['icon']) }}" class="w-7 h-7" alt="">
        </x-slot:icon>

        <x-slot:actions>
            <a
                href="{{ route('admin.page.home', ['tab' => $contentType['tab_id']]) }}"
                wire:navigate
                class="text-sm text-gray-500 hover:text-gray-800 flex items-center gap-1"
            >
                <ui:icon name="arrow-right" class="w-4 h-4 ltr:rotate-180" />
                رجوع
            </a>
        </x-slot:actions>

        <div class="px-4 lg:px-8 space-y-4">
            <p class="text-sm text-gray-500">{{ $item['subtitle'] }}</p>
            <p class="text-sm text-gray-600 leading-relaxed">
                تفاصيل النموذج — سيتم استبدال هذه البيانات لاحقاً من قاعدة البيانات.
            </p>
        </div>
    </ui:mainbox>
</div>

<?php

new class extends \Livewire\Component
{
    /** @var array<string, mixed> */
    public array $contentType = [];

    public string $itemId = '';

    /** @return array{id: int, title: string, subtitle: string} */
    public function item(): array
    {
        return collect([
            ['id' => 1, 'title' => 'نموذج تواصل', 'subtitle' => '٣ حقول — نشط'],
            ['id' => 2, 'title' => 'نموذج طلب عرض سعر', 'subtitle' => '٥ حقول — نشط'],
            ['id' => 3, 'title' => 'نموذج الاشتراك', 'subtitle' => '٢ حقول — مسودة'],
        ])->firstWhere('id', (int) $this->itemId) ?? [
            'id' => (int) $this->itemId,
            'title' => 'نموذج #'.$this->itemId,
            'subtitle' => '—',
        ];
    }

    public function render()
    {
        return $this->view(['item' => $this->item()]);
    }
}; ?>
