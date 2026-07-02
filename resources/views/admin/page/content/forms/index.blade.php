<div>
    <ui:mainbox :title="$contentType['name']" :subtitle="$contentType['description']">
        <x-slot:icon>
            <img src="{{ asset($contentType['icon']) }}" class="w-7 h-7" alt="">
        </x-slot:icon>

        <div class="px-4 lg:px-8 divide-y divide-gray-100">
            @foreach ($items as $item)
                <a
                    href="{{ route('admin.page.home', ['tab' => $contentType['tab_id'], 'item' => $item['id']]) }}"
                    wire:navigate
                    wire:key="forms-item-{{ $item['id'] }}"
                    class="flex items-center justify-between gap-4 py-4 hover:bg-gray-50 -mx-4 px-4 lg:-mx-8 lg:px-8 transition"
                >
                    <div>
                        <p class="text-sm font-semibold text-gray-800">{{ $item['title'] }}</p>
                        <p class="text-xs text-gray-400 mt-0.5">{{ $item['subtitle'] }}</p>
                    </div>
                    <ui:icon name="arrow-left" class="w-4 h-4 text-gray-400 ltr:rotate-180 shrink-0" />
                </a>
            @endforeach
        </div>
    </ui:mainbox>
</div>

<?php

new class extends \Livewire\Component
{
    /** @var array<string, mixed> */
    public array $contentType = [];

    /** @return array<int, array{id: int, title: string, subtitle: string}> */
    public function items(): array
    {
        return [
            ['id' => 1, 'title' => 'نموذج تواصل', 'subtitle' => '٣ حقول — نشط'],
            ['id' => 2, 'title' => 'نموذج طلب عرض سعر', 'subtitle' => '٥ حقول — نشط'],
            ['id' => 3, 'title' => 'نموذج الاشتراك', 'subtitle' => '٢ حقول — مسودة'],
        ];
    }

    public function render()
    {
        return $this->view(['items' => $this->items()]);
    }
}; ?>
