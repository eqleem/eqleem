<?php

use Livewire\Component;

new class extends Component
{
    public $range = [7];
 
    public function mount()
    {
        $this->range = [(int) now()->diffInDays(now()->copy()->startOfMonth()->subDay(3), true)];
    }
 
    public function render()
    {
        return $this->view()->layout('admin::layout');
    }
};
?>

<ui:container>
    <div class="text-sm mb-2 bg-gray-300/30 p-2 px-3 rounded-lg text-gray-700">
        ملخّص الشهر
        <b class="inline-block ms-2">{{ now()->translatedFormat('M Y') }}</b>
    </div>
    <div role="list"
        class="bg-gray-300/30 p-1 rounded-xl grid grid-cols-2 gap-3 lg:grid-cols-4 lg:gap-5 xl:grid-cols-4 mb-8">
        <livewire:admin::home.orders-count :range="$range" lazy />
        <livewire:admin::home.sales-total-count :range="$range" lazy />
        <livewire:admin::home.visits-count :range="$range" lazy />
        <livewire:admin::home.clients-count :range="$range" lazy />
    </div>
    <div role="list" class="bg-gray-300/30 p-1 rounded-2xl grid grid-cols-1 gap-3 lg:grid-cols-2 lg:gap-5 mb-8">
        <livewire:admin::home.orders-chart :range="$range" lazy />
        <livewire:admin::home.visits-chart :range="$range" lazy />
        <livewire:admin::home.clients-chart :range="$range" lazy />
        <livewire:admin::home.sales-chart :range="$range" lazy />
    </div>
</ui:container>