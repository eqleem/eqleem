<div class="relative col-span-1 flex rounded-xl shadow-sm group">
    <a href="{{ route('admin.clients.home') }}" wire:navigate.hover
        class="flex w-16 flex-shrink-0 items-center justify-center bg-pgray-100 group-hover:bg-opacity-75 rounded-s-xl text-sm font-medium text-white">
        <img class="h-10" src="{{ asset('assets/icons/business/025-team work.svg') }}" alt="">
    </a>

    <div class="flex flex-1 items-center justify-between truncate rounded-e-xl border-stone-200 bg-white">
        <a href="{{ route('admin.clients.home') }}" wire:navigate.hover class="flex-1 truncate px-3 py-3 text-sm">
            <span class="font-semibold text-stone-700 hover:text-stone-600"> العملاء </span>
            <p class="text-stone-400 mt-1">
                <b class="text-2xl font-bold text-pgray-800 me-1">{{ $value }}</b>
                <span class="text-xs ms-1 font-normal" title="{{ $growth }}% مقارنة بنفس الفترة السابقة">
                    @if ($growth < 0)
                        <span dir="ltr" class="text-red-500"> ⬇ {{ (int) $growth }}%</span>
                    @else
                        <span dir="ltr" class="text-green-500"> ⬆ {{ (int) $growth }}%</span>
                    @endif
                </span>
            </p>
        </a>
    </div>
</div>

<?php

use App\Models\User;
use SaKanjo\EasyMetrics\Metrics\Value;

new class extends \Livewire\Component {
    public $value = 0;
    public $growth = 0;
    public $range;

    function mount()
    {
        [$this->value, $this->growth] = Value::make(User::class)
            ->withGrowthRate()
            ->ranges($this->range)
            ->count();
    }

    public function placeholder()
    {
        return loadingIcon();
    }
}; ?>
