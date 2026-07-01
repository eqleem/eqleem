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
                تفاصيل المنتج — سيتم استبدال هذه البيانات لاحقاً من قاعدة البيانات.
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
        return collect($this->items())->firstWhere('id', (int) $this->itemId) ?? [
            'id' => (int) $this->itemId,
            'title' => 'منتج #'.$this->itemId,
            'subtitle' => '—',
        ];
    }

    /** @return array<int, array{id: int, title: string, subtitle: string}> */
    private function items(): array
    {
        return [
            ['id' => 1, 'title' => 'منتج تجريبي ١', 'subtitle' => '120 ر.س'],
            ['id' => 2, 'title' => 'منتج تجريبي ٢', 'subtitle' => '85 ر.س'],
            ['id' => 3, 'title' => 'منتج تجريبي ٣', 'subtitle' => '200 ر.س'],
        ];
    }

    public function render()
    {
        return $this->view(['item' => $this->item()]);
    }
}; ?>
