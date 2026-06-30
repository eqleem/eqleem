{{-- ref code : {{ auth()->user()->getReferralCode()}} --}}
<header class="bg-primary-700 p-2 text-white">
    <div class="max-w-5xl flex justify-between mx-auto">
        <div class="flex items-center gap-x-3">
            <a href="{{ route('admin.home') }}" wire:navigate class="flex items-center gap-x-2 justify-center text-center"
                wire:ignore>
                @if (tenant('logo'))
                    <img src="{{ tenant('logo') }}?p=logo" alt="" class="h-8 rounded-sm ms-1">
                @else
                    <img src="{{ asset('assets/images/t-logo.png') }}" alt="" class="h-8 rounded-sm ms-1">
                @endif
                {{ tenant('name') }}
            </a>

            <a href="{{ route('admin.plan.home') }}" wire:navigate
                class="flex items-center gap-x-1 justify-center text-center">
                <span class="bg-purple-500 hover:bg-purple-600 p-0.5 px-1.5 rounded text-xs text-purple-100">
                    {{ __(tenant('subscription.plan.name', 'Free')) }} </span>
        </div>

        <div class="flex items-center gap-x-3">
            <a href="{{ tenant('url') }}" target="_blank"
                class="bg-green-600 hover:bg-green-500 rounded-full text-white p-1 px-3 text-sm flex items-center gap-x-2">
                {{ __('Preview') }} <span class="hidden lg:block">{{ __('page') }}</span>
                <svg xmlns="http://www.w3.org/2000/svg"
                    class="icon icon-tabler icon-tabler-arrow-up-left h-4 w-4 ltr:rotate-90" viewBox="0 0 24 24"
                    stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round"
                    stroke-linejoin="round">
                    <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                    <path d="M7 7l10 10"></path>
                    <path d="M16 7l-9 0l0 9"></path>
                </svg>
            </a>

            {{-- <a href="{{ route('admin.settings.home') }}" wire:navigate title="{{ __('Settings') }}"
                class="@if(request()->routeIs('admin.settings.home')) bg-black/30 hover:bg-black/30 @endif hover:bg-black/30 rounded-full text-white p-1 px-2 text-sm flex items-center gap-x-2">
                <ui:icon name="settings" class="w-5 h-5" />
                <span class="hidden lg:block">{{ __('Settings') }}</span>
            </a> --}}

            <div class="" x-data="{ dropdownMenu: false }">
                <div class="relative" @click.outside="dropdownMenu=false">
                    <button @click="dropdownMenu = ! dropdownMenu" type="button" class="flex items-center gap-2"
                        id="user-menu-button" aria-expanded="false" aria-haspopup="true">
                        <div class="flex items-center gap-x-2 justify-center text-center">
                            <img src="{{ user('image') }}" alt="" class="w-8 rounded-full">
                            <span class="-mt-2 opacity-50">⌄</span>
                        </div>
                    </button>

                    <div x-show="dropdownMenu" x-cloak
                        class="absolute z-50 mt-2 bg-white border shadow-sm rounded-b-lg text-gray-800 text-sm flex p-1 ltr:right-0 rtl:left-0 w-48 flex-col gap-y-px"
                        role="menu" aria-orientation="vertical" aria-labelledby="user-menu-button" tabindex="-1"
                        x-transition.scale.origin.top>
                        <div class="p-3 truncate">
                            <p>{{ user('name') }}</p>
                            <p class="opacity-50">{{ user('email') }}</p>
                        </div>

                        <a href="{{ route('admin.account.home') }}" wire:navigate.hover
                            class="bg-stone-100 hover:bg-stone-200 p-1.5 rounded flex items-center gap-x-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none">
                                <path d="M12 12a5 5 0 1 0 0-10 5 5 0 0 0 0 10Z" stroke="currentColor" stroke-width="1.5"
                                    stroke-linecap="round" stroke-linejoin="round"></path>
                                <path opacity=".4" d="M20.59 22c0-3.87-3.85-7-8.59-7s-8.59 3.13-8.59 7"
                                    stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                                    stroke-linejoin="round"></path>
                            </svg>
                            {{ __('Manage account') }}
                        </a>
 

                        <a href="{{ route('admin.plan.home') }}" wire:navigate.hover
                            class="bg-stone-100 hover:bg-stone-200 p-1.5 rounded flex items-center gap-x-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none">
                                <path
                                    d="M12 15c3.728 0 6.75-2.91 6.75-6.5S15.728 2 12 2 5.25 4.91 5.25 8.5 8.272 15 12 15Z"
                                    stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                                    stroke-linejoin="round"></path>
                                <path opacity=".4"
                                    d="m7.52 13.52-.01 7.38c0 .9.63 1.34 1.41.97l2.68-1.27c.22-.11.59-.11.81 0l2.69 1.27c.77.36 1.41-.07 1.41-.97v-7.56"
                                    stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                                    stroke-linejoin="round"></path>
                            </svg>
                            إدارة الاشتراك
                        </a>

                        <a href="{{ route('home') }}"
                            class="bg-gray-100 hover:bg-gray-200 p-1.5 rounded flex items-center gap-x-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24"
                                fill="none">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                    stroke-width="1.5"
                                    d="M12 22c5.523 0 10-4.477 10-10S17.523 2 12 2 2 6.477 2 12s4.477 10 10 10z"></path>
                                <g opacity=".4">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                        stroke-width="1.5"
                                        d="M8 3h1a28.424 28.424 0 000 18H8M15 3a28.424 28.424 0 010 18"></path>
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                        stroke-width="1.5"
                                        d="M3 16v-1a28.424 28.424 0 0018 0v1M3 9a28.424 28.424 0 0118 0"></path>
                                </g>
                            </svg>
                            {{ config('app.name') }}
                        </a>
                        <a href="{{ route('auth.logout') }}"
                            class="bg-gray-100 hover:bg-gray-200 p-1.5 rounded flex items-center gap-x-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24"
                                fill="none">
                                <g opacity=".4">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                        stroke-miterlimit="10" stroke-width="1.5"
                                        d="M17.44 14.62L20 12.06 17.44 9.5M9.76 12.06h10.17"></path>
                                </g>
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                    stroke-miterlimit="10" stroke-width="1.5"
                                    d="M11.76 20c-4.42 0-8-3-8-8s3.58-8 8-8">
                                </path>
                            </svg>
                            <span>{{ __('Logout') }}</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>
