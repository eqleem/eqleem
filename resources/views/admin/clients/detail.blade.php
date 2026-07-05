<ui:container title="{{ __('Clients') }} / {{ data_get($client, 'name') }}"
    backRoute="{{ route('admin.clients.home') }}">

    <article class="bg-white rounded-xl">
        <div>
            <div
                class="h-20 w-full object-cover lg:h-40 bg-primary-200 rounded-t-xl bg-gradient-to-r from-xgray-200/50x tox-gray-300">
                <svg id='patternId' class="rounded-t-2xl opacity-50 z-10" width='100%' height='100%'
                    xmlns='http://www.w3.org/2000/svg'>
                    <defs>
                        <pattern id='a' patternUnits='userSpaceOnUse' width='70' height='70'
                            patternTransform='scale(1) rotate(0)'>
                            <rect x='0' y='0' width='100%' height='100%' fill='hsla(0,0%,100%,1)' />
                            <path
                                d='M-4.8 4.44L4 16.59 16.14 7.8M32 30.54l-13.23 7.07 7.06 13.23M-9 38.04l-3.81 14.5 14.5 3.81M65.22 4.44L74 16.59 86.15 7.8M61 38.04l-3.81 14.5 14.5 3.81'
                                stroke-linecap='square' stroke-width='1' stroke='hsla(258.5,59.4%,59.4%,1)'
                                fill='none' />
                            <path
                                d='M59.71 62.88v3h3M4.84 25.54L2.87 27.8l2.26 1.97m7.65 16.4l-2.21-2.03-2.03 2.21m29.26 7.13l.56 2.95 2.95-.55'
                                stroke-linecap='square' stroke-width='1' stroke='hsla(339.6,82.2%,51.6%,1)'
                                fill='none' />
                            <path
                                d='M58.98 27.57l-2.35-10.74-10.75 2.36M31.98-4.87l2.74 10.65 10.65-2.73M31.98 65.13l2.74 10.66 10.65-2.74'
                                stroke-linecap='square' stroke-width='1' stroke='hsla(198.7,97.6%,48.4%,1)'
                                fill='none' />
                            <path
                                d='M8.42 62.57l6.4 2.82 2.82-6.41m33.13-15.24l-4.86-5.03-5.03 4.86m-14-19.64l4.84-5.06-5.06-4.84'
                                stroke-linecap='square' stroke-width='1' stroke='hsla(47,80.9%,61%,1)' fill='none' />
                        </pattern>
                    </defs>
                    <rect width='800%' height='800%' transform='translate(0,0)' fill='url(#a)' />
                </svg>
            </div>
            <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
                <div class="-mt-6 sm:-mt-10 sm:flex sm:items-end sm:gap-x-5">
                    <div class="flex">
                        <img class="h-16 w-16 rounded-full ring-4 ring-white ring-opacity-50 sm:h-20 sm:w-20 z-50"
                            src="{{ $client->avatar }}" alt="">
                    </div>
                    <div class="mt-6 sm:flex sm:min-w-0 sm:flex-1 sm:items-center sm:justify-end sm:gap-x-6 sm:pb-1">
                        <div class="mt-10 min-w-0 flex-1 sm:hidden 2xl:block">
                            <h1 class="truncate text-2xl font-bold text-gray-900 ms-3">{{ $client->name }}</h1>
                        </div>
                    </div>
                </div>
                <div class="mt-6 hidden min-w-0 flex-1 sm:block 2xl:hidden">
                    <h1 class="truncate text-2xl font-bold text-gray-900">{{ $client->name }}</h1>
                </div>
            </div>
        </div>

        <div>

            <ui:tab.group
                :active="$activeTab"
                url-key="tab"
                :valid-tabs="['info', 'orders', 'invoices']"
                class="mt-6"
            >
                <x-slot name="nav" class="border-b">
                    <ui:tab.nav name="info" label="{{ __('Personal info') }}" icon="user"
                        activeClass="border-b-2 !border-blue-800" />
                    <ui:tab.nav name="orders" label="{{ __('Orders') }}" icon="message-2"
                        activeClass="border-b-2 !border-blue-800" />
                    <ui:tab.nav name="invoices" label="الفواتير" icon="file-invoice"
                        activeClass="border-b-2 !border-blue-800" />
                </x-slot>
                <x-slot name="content">
                    <ui:tab.content name="info"
                        class="grid grid-cols-1 gap-x-4 gap-y-8 sm:grid-cols-2 p-5 xl:p-10">
                        <div class="sm:col-span-1">
                            <dt class="text-sm text-gray-400">{{ __('Phone') }}</dt>
                            <dd class="mt-2 text-base font-bold text-gray-700 inline-block" dir="ltr">
                                {{ data_get($client, 'phone', '-') }}
                            </dd>
                        </div>
                        <div class="sm:col-span-1">
                            <dt class="text-sm text-gray-400">{{ __('Email') }}</dt>
                            <dd class="mt-2 text-base font-bold text-gray-700">
                                {{ data_get($client, 'email', '-') }}</dd>
                        </div>
                        <div class="sm:col-span-1">
                            <dt class="text-sm text-gray-400">{{ __('Address') }}</dt>
                            <dd class="mt-2 text-base font-bold text-gray-700">
                                {{ data_get($client, 'address', data_get($client, 'meta.address', '-')) }}</dd>
                        </div>
                        <div class="sm:col-span-1">
                            <dt class="text-sm text-gray-400">{{ __('City') }}</dt>
                            <dd class="mt-2 text-base font-bold text-gray-700">
                                {{ data_get($client, 'city', '-') }}</dd>
                        </div>
                    </ui:tab.content>
                    <ui:tab.content name="orders" class="!p-0 !rounded-none">
                        <livewire:admin::clients.orders-table :client="$client" lazy />
                    </ui:tab.content>
                    <ui:tab.content name="invoices" class="!p-0 !rounded-none">
                        <livewire:admin::clients.invoices-table :client="$client" lazy />
                    </ui:tab.content>
                </x-slot>
            </ui:tab.group>


        </div>
    </article>

</ui:container>

<?php
use App\Models\Client;

new class extends \Livewire\Component {
    public Client $client;

    public string $activeTab = 'info';

    /** @var list<string> */
    private const TABS = ['info', 'orders', 'invoices'];

    public function mount(): void
    {
        $this->client = Client::whereUuid(request()->id)->firstOrFail();

        $tab = request()->query('tab', 'info');

        if (in_array($tab, self::TABS, true)) {
            $this->activeTab = $tab;
        }
    }

    public function rendering($view): void
    {
        $view->title($this->client->name)->layout('admin::layout');
    }
}; ?>
