<div class="bg-stone-300/50  min-h-screen pt-10 lg:pt-12">
    <section class="relative xbg-white/50 pb-20 mx-auto max-w-7xl rounded-3xl   bg-gradient-to-b from-[#f4f4f4]  via-teal-300/10 to-transparent">
        <div class="absolute inset-0 -z-10 opacity-40 pointer-events-none">
            <svg class="size-full text-stone-200" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" preserveAspectRatio="none">
                <defs>
                    <pattern id="grid" width="10" height="10" patternUnits="userSpaceOnUse">
                        <path d="M10 0H0V10" fill="none" stroke="currentColor" stroke-width="0.4" />
                    </pattern>
                </defs>
                <rect width="100%" height="100%" fill="url(#grid)" />
            </svg>
        </div>

        <div class="px-4 mx-auto max-w-7xl ">
            <div class="flex justify-center max-w-xl mx-auto">
                <div class="rounded-full flex items-center w-full mx-auto justify-between -mt-7 bg-white text-white px-2 py-2 text-xs sm:text-sm font-medium">
                      <a href="{{ route('home') }}" wire:navigate title="" class="flex items-center gap-x-2 ms-3">
                        <img class="w-auto h-5 md:h-7" src="{{ asset('assets/images/logo-text-black.webp') }}" alt="" />
                        {{-- <img class="w-auto h-5 md:h-7" src="{{ asset('assets/images/logo-y-r-p.webp') }}" alt="" /> --}}
                        {{-- <img class="w-auto h-6 md:h-9" src="{{ asset('assets/images/logo-10-white.webp') }}" alt="" /> --}}
                        {{-- <img class="w-auto h-6 md:h-9" src="{{ asset('assets/images/logo-10-orange.webp') }}" alt="" /> --}}
                        {{-- <img class="w-auto h-6 md:h-9" src="{{ asset('assets/images/t1-w.png') }}" alt="" /> --}}
                        {{-- <span class="text-xl lg:text-3xl font-camel font-extrabold">
                            {{ config('app.name') }} 
                        </span> --}}
                    </a>
                   
              
                    <div class="flex items-center justify-end lg:gap-x-2">
                        @auth
                            <ui:button variant="primary" href="{{ route('admin.home') }}" label="لوحة التحكم" class="!rounded-full text-base font-tsh !p-6" rounded="full" icon="settings" />
                        @else
                            <ui:button variant="primary" href="{{ route('auth.register') }}" label="أنشئ صفحة أعمال" wire:navigate rounded="full" icon="plus" class="!rounded-full !bg-primary-600 !hover:bg-primary-700 " />
                            <ui:button variant="ghost" href="{{ route('auth.login') }}" label="دخول" wire:navigate class="text-white hover:text-white/70 !hover:bg-black/5 !font-normal" icon:trailing="arrow-left" />
                        @endauth   
                    </div>
                </div>
            </div>


            <div class="mt-24 lg:mt-32 text-center max-w-4xl mx-auto">
 


      <div class="inline-flex items-center justify-center max-w-5xl  bg-white/30 rounded-xl p-2 mx-auto gap-x-2  mb-10 border-black/5 border-dotted">
            <img class="size-7" src="{{ asset('assets/images/logos/mnjm-logo.png') }}" alt="" />
            <div>
                <div class="flex items-center justify-center gap-x-0.5 -mr-0.5">
                    <svg class="size-3 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path
                            d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"
                        />
                    </svg>
                    <svg class="size-3 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path
                            d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"
                        />
                    </svg>
                    <svg class="size-3 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path
                            d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"
                        />
                    </svg>
                    <svg class="size-3 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path
                            d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"
                        />
                    </svg>
                    <svg class="size-3 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path
                            d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"
                        />
                    </svg>
                </div>
                <p class=" text-[11px] opacity-50"><b>4.9</b> على منجم</p>
            </div>
        </div>

                <h1 class="text-4xl sm:text-5xl md:text-6xl text-base-900 font-semibold leading-12 !lg:leading-[4.5rem] max-w-sm sm:max-w-xl xl:max-w-2xl mx-auto">
                     أنشئ صفحة أعمال مُركّزة،   
                    {{-- <div class="relative inline-flex">
                        <span class="absolute inset-x-0 bottom-0 border-b-[12px] border-amber-400"></span>
                        <h1 class="relative text-4xl font-semibold text-black sm:text-6xl  "> تحقق أهدافك </h1>
                    </div>، --}}
                     
                    {{-- <span class="text-primary-500">تبيع عنّك، </span> --}}
                     بدقائق!
                </h1>
                <p class="mt-8 text-gray-500 max-w-sm lg:max-w-xl mx-auto text-base lg:text-xl">
                    {{-- <b>صفحة أعمال</b> --}}
                    <img src="{{ asset('assets/images/t1.png') }}" class="inline-block h-6 md:h-9" alt="" />
                    يساعدك تنشئ

                    صفحات لتسويق أعمالك ومشاريعك
                    خلال دقائق،
                    تستقبل طلبات العملاء عنّك، تجاوب على أسئلتهم، وتوصلهم بك 24/7 على مدار الساعة.
                </p>
            </div>

        <div class="mt-12 text-center max-w-sm lg:max-w-xl mx-auto">
                <div class="mt-6 flex items-center justify-center gap-3">
                    <a href="{{ route('auth.register-login')}}" wire:navigate class="inline-flex items-center gap-3 rounded-full bg-primary-600 text-white px-3 py-3 text-2xl ps-6 font-semiboldx shadow-sm hover:bg-primary-700">
                        أنشئ صفحتك مجاناً 
                        <ui:icon name="arrow-left" size="10" class="bg-teal-400 rounded-full p-1" />
                    </a>
                </div>
                <p class="mt-2 text-xs text-stone-500/50">خطة مجانية •  أنشئ صفحتك بثواني</p>
        </div>


        <div class="bg-stone-100x z-50 relative">
            <div class="max-w-7xl mx-auto px-4">
            
                <div class="mt-12 grid grid-cols-1 lg:grid-cols-3 gap-10 items-start">
                    
                    <div class="lg:pt-24 order-2 lg:order-none">
                        <div class="flex items-start gap-3 lg:text-end lg:flex-row-reverse">
                            <div class="shrink-0">
                                <svg viewBox="0 0 24 24" fill="none" class="size-12"  xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path d="M2 8L8 10" stroke="#1C274C" stroke-width="1.5" stroke-linecap="round"></path> <path d="M6 4L8 7" stroke="#1C274C" stroke-width="1.5" stroke-linecap="round"></path> <path opacity="0.5" d="M11 6.56252L14.7001 2.93755C16.1597 1.50753 18.7629 1.73938 20.5145 3.4554C22.266 5.17142 22.5027 7.72176 21.043 9.15178L18.1358 12" stroke="#1C274C" stroke-width="1.5" stroke-linecap="round"></path> <path opacity="0.5" d="M15 15.5866L10.9653 20.001C9.57254 21.5247 7.0887 21.2777 5.41744 19.4492C3.74618 17.6207 3.52038 14.9032 4.91309 13.3795L6.17395 12" stroke="#1C274C" stroke-width="1.5" stroke-linecap="round"></path> </g></svg>
                                {{-- <svg xmlns="http://www.w3.org/2000/svg" class="size-10" viewBox="0 0 24 24" fill="currentColor"><path d="M3 3h18v2H3V3zm0 6h12v2H3V9zm0 6h18v2H3v-2z"/></svg> --}}
                            </div>
                            <div>
                                <h3 class="font-semibold text-stone-900"> رابط دائم </h3>
                                <p class="mt-1 text-sm text-stone-600"> احصل على رابط دائم لصفحتك يمكن تخصيصه وربطه بدومين مخصص باسم هويتك وشاركه مع عملائك. </p>
                                {{-- <a href="#" class="mt-2 inline-block text-sm font-medium text-stone-700 hover:text-stone-900 underline underline-offset-4">Learn More</a> --}}
                            </div>
                        </div>

                        <div class="mt-16 flex items-start lg:text-end gap-3 lg:flex-row-reverse">
                            <div class="shrink-0">
                                <svg viewBox="0 0 24 24" fill="none" class="size-12" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path d="M2 6C2 3.79086 3.79086 2 6 2C8.20914 2 10 3.79086 10 6V18C10 20.2091 8.20914 22 6 22C3.79086 22 2 20.2091 2 18V6Z" stroke="#1C274C" stroke-width="1.5"></path> <path d="M10 8.24268L13.3137 4.92902C14.8758 3.36692 17.4084 3.36692 18.9705 4.92902C20.5326 6.49112 20.5326 9.02378 18.9705 10.5859L9.3064 20.25" stroke="#1C274C" stroke-width="1.5"></path> <path opacity="0.5" d="M6 22L18 22C20.2091 22 22 20.2091 22 18C22 15.7909 20.2091 14 18 14L15.5 14" stroke="#1C274C" stroke-width="1.5"></path> <path opacity="0.5" d="M7 18C7 18.5523 6.55228 19 6 19C5.44772 19 5 18.5523 5 18C5 17.4477 5.44772 17 6 17C6.55228 17 7 17.4477 7 18Z" stroke="#1C274C" stroke-width="1.5"></path> </g></svg>
                            </div>
                            <div>
                                <h3 class="font-semibold text-stone-900"> تصميم مخصص  </h3>
                                <p class="mt-1 text-sm text-stone-600">تصميم مخصص لصفحتك، بناءً على ألوان هويتك وبراندك.</p>
                            </div>
                        </div>

                        <div class="mt-16 flex items-start lg:text-end gap-3 lg:flex-row-reverse">
                            <div class="shrink-0">
                                <svg viewBox="0 0 24 24" fill="none" class="size-12" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path d="M3.79424 12.0291C4.33141 9.34329 4.59999 8.00036 5.48746 7.13543C5.65149 6.97557 5.82894 6.8301 6.01786 6.70061C7.04004 6 8.40956 6 11.1486 6H12.8515C15.5906 6 16.9601 6 17.9823 6.70061C18.1712 6.8301 18.3486 6.97557 18.5127 7.13543C19.4001 8.00036 19.6687 9.34329 20.2059 12.0291C20.9771 15.8851 21.3627 17.8131 20.475 19.1793C20.3143 19.4267 20.1267 19.6555 19.9157 19.8616C18.7501 21 16.7839 21 12.8515 21H11.1486C7.21622 21 5.25004 21 4.08447 19.8616C3.87342 19.6555 3.68582 19.4267 3.5251 19.1793C2.63744 17.8131 3.02304 15.8851 3.79424 12.0291Z" stroke="#1C274C" stroke-width="1.5"></path> <path opacity="0.5" d="M9 6V5C9 3.34315 10.3431 2 12 2C13.6569 2 15 3.34315 15 5V6" stroke="#1C274C" stroke-width="1.5" stroke-linecap="round"></path> <path opacity="0.5" d="M9.1709 15C9.58273 16.1652 10.694 17 12.0002 17C13.3064 17 14.4177 16.1652 14.8295 15" stroke="#1C274C" stroke-width="1.5" stroke-linecap="round"></path> </g></svg>

                            </div>
                            <div>
                                <h3 class="font-semibold text-stone-900"> أعرض منتجاتك وخدماتك </h3>
                                <p class="mt-1 text-sm text-stone-600"> أعرض خدماتك ومنتجاتك بأسعارها وتفاصيلها واسمح لعملائك بالطلب المباشر أو التواصل معك لحجز والإستفسار بأي وقت.</p>
                            </div>
                        </div>
                    </div>

                    <div class="order-1 lg:order-none">
                        <ui:mobile-dark>
                            {{-- <img src="https://template.canva.com/EAEsa3Aii8U/4/0/900w-3QolSmpWFXk.jpg" class="w-full h-full object-cover" alt="" /> --}}
                            {{-- <img src="https://template.canva.com/EAFqeAlAaHc/1/0/900w-2-Zw-73tvq4.jpg" class="w-full h-full object-cover" alt="" /> --}}
                            {{-- <img src="{{ asset('assets/images/demo.webp') }}" class="w-full h-full object-contain object-top" alt="" /> --}}
                            <img src="{{ asset('assets/images/aswar.webp') }}" class="w-full h-full object-contain object-top" alt="" />
                        </ui:mobile-dark>
                    </div>

                    <div class="lg:pt-24 order-3 lg:order-none">
                        <div class="flex items-start gap-3 lg:justify-end">
                            <div class="shrink-0 ">
                                <svg viewBox="0 0 24 24" fill="none" class="size-12" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path d="M2 10C2 7.17157 2 5.75736 2.87868 4.87868C3.75736 4 5.17157 4 8 4H13C15.8284 4 17.2426 4 18.1213 4.87868C19 5.75736 19 7.17157 19 10C19 12.8284 19 14.2426 18.1213 15.1213C17.2426 16 15.8284 16 13 16H8C5.17157 16 3.75736 16 2.87868 15.1213C2 14.2426 2 12.8284 2 10Z" stroke="#1C274C" stroke-width="1.5"></path> <path opacity="0.5" d="M19.0003 7.07617C19.9754 7.17208 20.6317 7.38885 21.1216 7.87873C22.0003 8.75741 22.0003 10.1716 22.0003 13.0001C22.0003 15.8285 22.0003 17.2427 21.1216 18.1214C20.2429 19.0001 18.8287 19.0001 16.0003 19.0001H11.0003C8.17187 19.0001 6.75766 19.0001 5.87898 18.1214C5.38909 17.6315 5.17233 16.9751 5.07642 16" stroke="#1C274C" stroke-width="1.5"></path> <path d="M13 10C13 11.3807 11.8807 12.5 10.5 12.5C9.11929 12.5 8 11.3807 8 10C8 8.61929 9.11929 7.5 10.5 7.5C11.8807 7.5 13 8.61929 13 10Z" stroke="#1C274C" stroke-width="1.5"></path> <path opacity="0.5" d="M16 12L16 8" stroke="#1C274C" stroke-width="1.5" stroke-linecap="round"></path> <path opacity="0.5" d="M5 12L5 8" stroke="#1C274C" stroke-width="1.5" stroke-linecap="round"></path> </g></svg>

                            </div>
                            <div>
                                <h3 class="font-semibold text-stone-900"> استقبل طلبات عملائك </h3>
                                <p class="mt-1 text-sm text-stone-600"> لا تفوّت عميل، استقبل طلبات وحجوزات وتواصل عملائك من خلال نماذج ذكية.</p>
                                {{-- <a href="#" class="mt-2 inline-block text-sm font-medium text-stone-700 hover:text-stone-900 underline underline-offset-4">Learn More</a> --}}
                            </div>
                            
                        </div>

                        <div class="mt-16 flex items-start gap-3 lg:justify-end">
                            <div class="shrink-0">
                                <svg viewBox="0 0 24 24" fill="none" class="size-12"  xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path opacity="0.5" d="M2 12C2 7.28595 2 4.92893 3.46447 3.46447C4.92893 2 7.28595 2 12 2C16.714 2 19.0711 2 20.5355 3.46447C22 4.92893 22 7.28595 22 12C22 16.714 22 19.0711 20.5355 20.5355C19.0711 22 16.714 22 12 22C7.28595 22 4.92893 22 3.46447 20.5355C2 19.0711 2 16.714 2 12Z" stroke="#1C274C" stroke-width="1.5"></path> <path opacity="0.5" d="M11.25 18C11.25 18.4142 11.5858 18.75 12 18.75C12.4142 18.75 12.75 18.4142 12.75 18H11.25ZM18 8L18.5303 8.53033C18.8232 8.23744 18.8232 7.76256 18.5303 7.46967L18 8ZM17.0303 5.96967C16.7374 5.67678 16.2626 5.67678 15.9697 5.96967C15.6768 6.26256 15.6768 6.73744 15.9697 7.03033L17.0303 5.96967ZM15.9697 8.96967C15.6768 9.26256 15.6768 9.73744 15.9697 10.0303C16.2626 10.3232 16.7374 10.3232 17.0303 10.0303L15.9697 8.96967ZM12.75 18V12H11.25V18H12.75ZM16 8.75H18V7.25H16V8.75ZM18.5303 7.46967L17.0303 5.96967L15.9697 7.03033L17.4697 8.53033L18.5303 7.46967ZM17.4697 7.46967L15.9697 8.96967L17.0303 10.0303L18.5303 8.53033L17.4697 7.46967ZM12.75 12C12.75 10.2051 14.2051 8.75 16 8.75V7.25C13.3766 7.25 11.25 9.37665 11.25 12H12.75Z" fill="#1C274C"></path> <path d="M11.25 18C11.25 18.4142 11.5858 18.75 12 18.75C12.4142 18.75 12.75 18.4142 12.75 18H11.25ZM6 8L5.46967 7.46967C5.17678 7.76256 5.17678 8.23744 5.46967 8.53033L6 8ZM8.03033 7.03033C8.32322 6.73744 8.32322 6.26256 8.03033 5.96967C7.73744 5.67678 7.26256 5.67678 6.96967 5.96967L8.03033 7.03033ZM6.96967 10.0303C7.26256 10.3232 7.73744 10.3232 8.03033 10.0303C8.32322 9.73744 8.32322 9.26256 8.03033 8.96967L6.96967 10.0303ZM12.75 18V12H11.25V18H12.75ZM8 7.25H6V8.75H8V7.25ZM6.53033 8.53033L8.03033 7.03033L6.96967 5.96967L5.46967 7.46967L6.53033 8.53033ZM5.46967 8.53033L6.96967 10.0303L8.03033 8.96967L6.53033 7.46967L5.46967 8.53033ZM12.75 12C12.75 9.37665 10.6234 7.25 8 7.25V8.75C9.79493 8.75 11.25 10.2051 11.25 12H12.75Z" fill="#1C274C"></path> </g></svg>

                            </div>
                            <div>
                                <h3 class="font-semibold text-stone-900"> متعدد اللغات  </h3>
                                <p class="mt-1 text-sm text-stone-600"> خاطب عميلك بلغته، أضف الإنجليزية بجانب العربية ولغات أخرى عديدة.</p>
                                {{-- <a href="#" class="mt-2 inline-block text-sm font-medium text-stone-700 hover:text-stone-900 underline underline-offset-4">Learn More</a> --}}
                            </div>  
                        </div>

                        <div class="mt-16 flex items-start gap-3 justify-end">
                            <div class="shrink-0">
                                <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="size-12"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path d="M10.0802 7.89712C11.1568 5.96571 11.6952 5 12.5 5C13.3048 5 13.8432 5.96571 14.9198 7.89712L15.1984 8.3968C15.5043 8.94564 15.6573 9.22007 15.8958 9.40114C16.1343 9.5822 16.4314 9.64942 17.0255 9.78384L17.5664 9.90622C19.6571 10.3793 20.7025 10.6158 20.9512 11.4156C21.1999 12.2153 20.4872 13.0487 19.0619 14.7154L18.6932 15.1466C18.2881 15.6203 18.0856 15.8571 17.9945 16.1501C17.9034 16.443 17.934 16.759 17.9953 17.3909L18.051 17.9662C18.2665 20.19 18.3742 21.3019 17.7231 21.7962C17.072 22.2905 16.0932 21.8398 14.1357 20.9385L13.6292 20.7053C13.073 20.4492 12.7948 20.3211 12.5 20.3211C12.2052 20.3211 11.927 20.4492 11.3708 20.7053L10.8643 20.9385C8.90677 21.8398 7.928 22.2905 7.27688 21.7962C6.62575 21.3019 6.7335 20.19 6.94899 17.9662L7.00474 17.3909C7.06597 16.759 7.09659 16.443 7.00548 16.1501C6.91438 15.8571 6.71186 15.6203 6.30683 15.1466L5.93808 14.7154C4.51276 13.0487 3.8001 12.2153 4.04881 11.4156C4.29751 10.6158 5.34288 10.3793 7.43361 9.90622L7.9745 9.78384C8.56862 9.64942 8.86568 9.5822 9.1042 9.40114C9.34272 9.22007 9.4957 8.94565 9.80165 8.3968L10.0802 7.89712Z" stroke="#1C274C" stroke-width="1.5"></path> <path opacity="0.55" d="M4.98987 2C4.98987 2 5.2778 3.45771 5.90909 4.08475C6.54037 4.71179 8 4.98987 8 4.98987C8 4.98987 6.54229 5.2778 5.91525 5.90909C5.28821 6.54037 5.01013 8 5.01013 8C5.01013 8 4.7222 6.54229 4.09091 5.91525C3.45963 5.28821 2 5.01013 2 5.01013C2 5.01013 3.45771 4.7222 4.08475 4.09091C4.71179 3.45963 4.98987 2 4.98987 2Z" stroke="#1C274C" stroke-linejoin="round"></path> <path opacity="0.55" d="M18 5H20M19 6L19 4" stroke="#1C274C" stroke-width="1.5" stroke-linecap="round"></path> </g></svg>
                            </div>
                            <div>
                                <h3 class="font-semibold text-stone-900"> أعرض أعمالك وعملائك  </h3>
                                <p class="mt-1 text-sm text-stone-600"> أعرض قائمة أعمالك ومشاريعك السابقة وشعارات العملاء الذين تعاملت معهم لتعزيز وتقوية رسالتك التسويقية.</p>
                                {{-- <a href="#" class="mt-2 inline-block text-sm font-medium text-stone-700 hover:text-stone-900 underline underline-offset-4">Learn More</a> --}}
                            </div>  
                        </div>
                    </div>
                </div>
            </div>
        </div>
  
        {{-- <div class="flex flex-wrap items-center justify-center max-w-5xl gap-5 mx-auto mt-20 sm:gap-y-8 md:gap-12">
            <img class="object-contain bg-white/20 rounded-2xl p-2 size-16 lg:size-20 max-w-full filter grayscaleX hover:grayscale-0 transition-all duration-300" src="{{ asset('assets/images/clients/aswar.avif') }}" alt="" />
            <img class="object-contain bg-white/20 rounded-2xl p-2 size-16 lg:size-20 max-w-full filter grayscaleX hover:grayscale-0 transition-all duration-300" src="{{ asset('assets/images/clients/frst.webp') }}" alt="" />
            <img class="object-contain bg-white/20 rounded-2xl p-2 size-16 lg:size-20 max-w-full filter grayscaleX hover:grayscale-0 transition-all duration-300" src="{{ asset('assets/images/clients/qoot.png') }}" alt="" />
            <img class="object-contain bg-white/20 rounded-2xl p-2 size-16 lg:size-20 max-w-full filter grayscaleX hover:grayscale-0 transition-all duration-300" src="{{ asset('assets/images/clients/shthel-color.webp') }}" alt="" />
            <img class="object-contain bg-white/20 rounded-2xl p-2 size-16 lg:size-20 max-w-full filter grayscaleX hover:grayscale-0 transition-all duration-300" src="{{ asset('assets/images/clients/crisp-burger.webp') }}" alt="" />
            <img class="object-contain bg-white/20 rounded-2xl p-2 size-16 lg:size-20 max-w-full filter grayscaleX hover:grayscale-0 transition-all duration-300" src="{{ asset('assets/images/clients/baderatg.jpg') }}" alt="" />
            <img class="object-contain bg-white/20 rounded-2xl p-2 size-16 lg:size-20 max-w-full filter grayscaleX hover:grayscale-0 transition-all duration-300" src="{{ asset('assets/images/clients/crisp-ice.png') }}" alt="" />
        </div> --}}
 
        </div>
    </section>
      
     <x-clients />
  

     <div class="mx-auto max-w-3xl text-center relative z-10 mt-24 lg:mt-32">
        <h1 class="text-3xl sm:text-4xl  text-gray-800">
            <b class="font-semibold inline-block mb-7 text-5xl lg:text-6xl">لا تفوّت عميل!</b>
            <br class="" />
            استقبل طلبات عملائك وجاوب على كل أسئلتهم.
        </h1>
        <p class="mt-4 text-gray-600 leading-7 text-xl font-thin">
            استقبل اتصالات العملاء، طلبات الشراء والحجوزات، الاشتراكات والدفع، لا تفّوت عميل بعد اليوم.
        </p>
        {{-- <div class="mt-6">
            <a href="#" class="inline-flex items-center justify-center rounded-full bg-violet-600 px-6 py-3 text-white font-semibold shadow-sm hover:bg-violet-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-violet-600">
                Start for Free
            </a>
        </div> --}}
    </div>


    <!-- Features section -->
    <section class="px-4 mx-auto max-w-7xl mt-12 lg:mt-20">
        <div class="rounded-[28px] bg-base-500/5 shadow-sm  p-4 sm:p-6 lg:p-8">
            <div class="grid grid-cols-12 gap-4 sm:gap-5 lg:gap-6 xauto-rows-[130px] sm:auto-rows-[160px] lg:auto-rows-[180px]">
                <!-- Maximize Card Rewards -->
                <div class="col-span-12 md:col-span-6 lg:col-span-5 row-span-3 rounded-3xl bg-violet-100/70 p-6 sm:p-7 lg:p-8 flex flex-col">
                    <div class="flex-1">
                        <h3 class="text-2xl lg:text-3xl font-black tracking-tight text-black"> نموذج للحجز</h3>
                        <p class="mt-4 text-stone-600 text-base font-thin leading-6 max-w-sm">
                         أنشئ نماذج حجز ذكية 
                         لاستقبال طلبات وحجوزات عملائك 
                         مهما كان مجال عملك.
                         مع إمكانية التحكم في النماذج والتعديل عليها بسهولة.
                        </p>
 
                    </div>
                    <div class="mt-6 self-end">
                        <img src="{{asset('assets/images/home/booking.svg')}}" class="lg:w-screen w-full" />
                        {{-- <img src="{{asset('assets/images/home/1.svg')}}" class="w-screen " /> --}}
                        {{-- <!-- credit card illustration -->
                        <svg class="w-40 h-28 sm:w-48 sm:h-32 text-violet-500" viewBox="0 0 200 130" xmlns="http://www.w3.org/2000/svg" fill="none">
                            <rect x="10" y="20" rx="14" ry="14" width="180" height="100" fill="currentColor" opacity="0.25"/>
                            <rect x="18" y="36" width="164" height="16" rx="4" fill="currentColor"/>
                            <rect x="18" y="62" width="90" height="10" rx="3" fill="currentColor" opacity="0.6"/>
                            <rect x="18" y="78" width="60" height="10" rx="3" fill="currentColor" opacity="0.6"/>
                            <rect x="152" y="70" width="30" height="22" rx="4" fill="#7c3aed"/>
                        </svg> --}}
                    </div>
                </div>
 

                <!-- Manage Cards (wide) -->
                <div class="col-span-12 md:col-span-6 lg:col-span-7 row-span-1 rounded-3xl bg-green-100 p-6 sm:p-7 lg:p-8 ">

                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-2xl font-black text-black"> رسالة واتساب </h3>
                            <p class="mt-2 text-stone-600 text-base font-thin"> أضف زر تواصل واتساب لصفحتك لسهولة تواصل العملاء معك من خلال المحادثة المباشرة على واتساب. 
                            وتتبع عدد الضغات والزيارات لكل طريقة تواصل.
                            </p>
                        </div>
                        
                        {{-- <img src="https://template.canva.com/EAE1Pp8_lIQ/2/0/900w-cn4UI_o1JqU.jpg" class="w-28" /> --}}

                        <div class="flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-28 h-28 text-green-600" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                <path d="M3 21l1.65 -3.8a9 9 0 1 1 3.4 2.9l-5.05 .9" />
                                <path d="M9 10a.5 .5 0 0 0 1 0v-1a.5 .5 0 0 0 -1 0v1a5 5 0 0 0 5 5h1a.5 .5 0 0 0 0 -1h-1a.5 .5 0 0 0 0 1" />
                            </svg>
                        </div>
                    </div>
                    
                    {{-- <img src="https://www.hello-charles.com/hs-fs/hubfs/ServiceAIv2.png?width=1150&height=1040&name=ServiceAIv2.png" class="xw-full" /> --}}

                </div>

                <!-- Set Goals -->
                <div class="col-span-12 sm:col-span-6 lg:col-span-3 row-span-2 overflow-hidden rounded-3xl bg-yellow-100 pt-6 px-6 sm:px-7 lg:px-8 flex flex-col items-center justify-between">
                    <div>
                        <h3 class="text-xl font-extrabold text-black"> تحميل ملف </h3>
                        <p class="mt-2 text-stone-700 text-sm font-thin text-justify"> 
                            اسمح لعملائك بتحميل ملف البروفايل أو كتاب  أو أي ملف بشكل مباشر من صفحتك. 
                            يمكن طلب بيانات التواصل قبل التحميل لإعادة استهدافهم لاحقاً، 
                            كما يمكنك تتبع عدد التحميلات والزيارات لكل ملف
                        .</p>
                    </div>
                    <img src="{{asset('assets/images/home/download.svg')}}" class="w-[99%]" />

                    {{-- <svg class="w-20 h-20 text-yellow-500" viewBox="0 0 96 96" xmlns="http://www.w3.org/2000/svg" fill="none">
                        <circle cx="48" cy="48" r="28" stroke="currentColor" stroke-width="6"/>
                        <circle cx="48" cy="48" r="16" stroke="currentColor" stroke-width="6"/>
                        <path d="M48 20v-8M76 48h8M48 84v8M12 48H4" stroke="#eab308" stroke-width="6" stroke-linecap="round"/>
                        <path d="M69 27 86 14" stroke="#eab308" stroke-width="6" stroke-linecap="round"/>
                    </svg> --}}
                </div>

                <!-- Lounges -->
                <div class="col-span-12 sm:col-span-6 lg:col-span-4 overflow-hidden row-span-2 rounded-3xl bg-pink-100  pt-6 px-6 sm:px-7 lg:px-8 flex flex-col items-center justify-between">
                    <div>
                        <h3 class="text-xl font-extrabold text-black"> شراء مباشر </h3>
                        <p class="mt-2 text-stone-700 text-base font-thin">
                        أضف زر للشراء والدفع مباشرة من خلال صفحتك، سواء من خلال منصة صفحة أعمال أو 
                        باستخدام منصات البيع والدفع الخارجية.
                        .</p>
                    </div>
                    <img src="{{asset('assets/images/home/buy.svg')}}" class="w-[85%]" />

                    {{-- <svg class="w-24 h-24 text-lime-600" viewBox="0 0 120 120" xmlns="http://www.w3.org/2000/svg" fill="none">
                        <rect x="10" y="50" width="100" height="22" rx="6" fill="currentColor" opacity="0.25"/>
                        <rect x="20" y="40" width="40" height="10" rx="3" fill="currentColor"/>
                        <rect x="20" y="76" width="40" height="10" rx="3" fill="currentColor"/>
                        <path d="M88 40v46" stroke="#65a30d" stroke-width="8" stroke-linecap="round"/>
                        <path d="M76 56h24" stroke="#65a30d" stroke-width="8" stroke-linecap="round"/>
                    </svg> --}}
                </div>

                <!-- Credit Card Strategy -->
                <div class="col-span-12 lg:col-span-7 row-span-2 overflow-hidden rounded-3xl order-last lg:order-5 bg-orange-100 pt-8 px-6 sm:px-7 lg:px-8 flex flex-col justify-between">
                    <div>
                        <h3 class="text-2xl lg:text-3xl font-black text-black"> نماذج متعددة للاستحواذ على عميلك </h3>
                        <p class="mt-4 text-stone-700 text-base font-thin max-w-mdx">
                            لأن كل منتج وخدمة وحملة إعلانية تختلف أهدافها وطرق الإستحواذ على العملاء.
                             
                          
                            يمكنك إنشاء نماذج مختلفة للاستحواذ على عملائك بناء على أهدافك ومتطلباتك.
                            مثل زر شراء مباشر، حجز خدمة مع الدفع، حجز استشارة أو موعد.. 
                             
                            ويمكنك تتبع عدد الزيارات والتفعاعل لكل نموذج.


                        .</p>
                    </div>
                    
                    <img src="{{asset('assets/images/home/form.svg')}}" class="lg:w-1/2 w-full" />

                    {{-- <svg class="w-40 h-28 text-orange-500" viewBox="0 0 200 120" xmlns="http://www.w3.org/2000/svg" fill="none">
                        <rect x="16" y="20" width="24" height="80" rx="4" fill="currentColor" opacity="0.3"/>
                        <rect x="60" y="44" width="24" height="56" rx="4" fill="currentColor"/>
                        <rect x="104" y="32" width="24" height="68" rx="4" fill="currentColor" opacity="0.6"/>
                        <path d="M24 22c36 0 60 20 92 20 20 0 40-8 68-20" stroke="#f97316" stroke-width="6" stroke-linecap="round"/>
                    </svg> --}}
                </div>

                <!-- Find, Compare & Apply -->
                <div class="col-span-12 lg:col-span-5 lg:row-span-2 rounded-3xl overflow-hidden bg-sky-100 order-5 lg:order-last pt-8 px-6 sm:px-7 lg:px-8 flex flex-col justify-between">
                    <div>
                        <h3 class="text-2xl lg:text-3xl font-black text-black"> تسجيل اهتمام </h3>
                        <p class="mt-4 text-stone-700 text-base font-thin max-w-md">
                        أضف زر تسجيل اهتمام لصفحتك لسهولة تسجيل العملاء المهتمين بمنتجك أو خدمتك.
                        يمكنك تتبع عدد الزيارات والتفعاعل لكل زر
                        .</p>
                    </div>
                        
                    <div class="w-full ">
                        <img src="{{asset('assets/images/home/interest.svg')}}" class="lg:w-[70%]" />
                    </div>
 
                </div>
            </div>
        </div>
    </section>



 <!-- broshur review -->

     <div class="mx-auto max-w-3xl text-center relative z-10 mt-32">
        <h1 class="text-3xl sm:text-4xl  text-gray-800">
            <b class="font-semibold inline-block mb-7 text-5xl lg:text-6xl">صممّها بنفسك!</b>
         </h1>
        <p class="  text-gray-600 leading-7 text-xl font-thin max-w-lg mx-auto">
            ما تحتاج خبير تقني،
            أنشئ صفحتك وأضف المحتويات حسب مجال عملك بسرعة وبسهولة، بدقائق صفحتك جاهزة للنشر والبيع 🚀
        </p>
    </div>

    <div class="mt-24">
        <div class="relative mx-auto max-w-5xl">
            <div class="absolute inset-x-10 -top-6 -z-10 h-6 rounded-3xl bg-white shadow-lg ring-1 ring-black/10"></div>
            <div class="absolute inset-x-6 -top-3 -z-10 h-8 rounded-3xl bg-white shadow-lg ring-1 ring-black/10"></div>
            <div class="rounded-3xl bg-white shadow-xl ring-1 ring-black/10 overflow-hidden">
                <div class="border-b border-stone-200 px-4 sm:px-6 py-3 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="size-2.5 rounded-full bg-red-400"></span>
                        <span class="size-2.5 rounded-full bg-yellow-400"></span>
                        <span class="size-2.5 rounded-full bg-green-400"></span>
                    </div>
                    <div class="text-xs text-stone-400">{{ now()->format('F Y') }}</div>
                    {{-- <div class="flex items-center gap-2">
                        <span class="rounded-md bg-stone-100 text-stone-700 text-xs px-2 py-1">أنشر</span>
                        <span class="rounded-md bg-stone-100 text-stone-700 text-xs px-2 py-1">شارك</span>
                    </div> --}}
                </div>
                <div class="aspect-[16/10.3] bg-white">
                    <div class="h-full w-full bg-gradient-to-br from-stone-50 to-stone-100">
                    <img src="{{ asset('assets/images/dashboard.webp') }}" class="w-full h-full object-contain object-top" alt="" />
                    </div>
                </div>
            </div>
        </div>
    </div>


    <section class="py-16 lg:py-20 xl:py-32 lg:mt-5 mt-12">
        <div class="px-4 mx-auto sm:px-6 lg:px-8 max-w-6xl">
            <div class="max-w-2xl mx-auto text-center">
                <h2 class="text-3xl font-semibold text-gray-900 sm:text-4xl lg:text-5xl"> الأسئلة المتكررة  </h2>
                <p class="mt-4 text-base font-thin leading-7 text-gray-600/60 lg:text-xl lg:mt-6 lg:leading-8"> هنا قائمة بإجاباتنا على أكثر الأسئلة تكراراً، إذا لم تجد إجابة لسؤالك 
                <a href="#" wire:navigate class="text-primary-700 hover:text-primary-600"> تواصل معنا</a>
                 في أي وقت.</p>
            </div>
    
            <div x-cloak class="max-w-5xl bg-white mx-auto mt-12 overflow-hidden divide-y sm:mt-16 rounded-xl" x-data="{ active: 1 }">
                <div
                    x-data="{
                            id: 1,
                            get expanded() {
                                return this.active === this.id
                            },
                            set expanded(value) {
                                this.active = value ? this.id : null
                            },
                        }"
                    role="region"
                >
                    <h3>
                        <button @click="expanded = !expanded" :aria-expanded="expanded" class="flex items-center justify-between w-full px-6 py-5 text-sm lg:text-lg  text-gray-900 sm:p-6">
                            <span>  ما هو صفحة أعمال؟  </span>
                            <span x-show="expanded" aria-hidden="true" class="ms-4">
                                <svg class="w-6 h-6 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                                </svg>
                            </span>
                            <span x-show="!expanded" aria-hidden="true" class="ms-4">
                                <svg class="w-6 h-6 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                            </span>
                        </button>
                    </h3>
    
                    <div x-show="expanded" x-collapse>
                        <div class="px-6 pb-6">
                            <p class="text-sm font-thin lg:text-base text-gray-600">
صفحة أعمال هو منصة تساعدك على إنشاء صفحة تجارية مخصصة لمتجرك أو مشروعك، يمكنك تصميمها بنفسك، تعديل الألوان والعناصر، وعرض منتجاتك وخدماتك بطريقة جذابة وسهلة المشاركة.

                            </p>
                        </div>
                    </div>
                </div>
    
                <div
                    x-data="{
                            id: 2,
                            get expanded() {
                                return this.active === this.id
                            },
                            set expanded(value) {
                                this.active = value ? this.id : null
                            },
                        }"
                    role="region"
                >
                    <h3>
                        <button @click="expanded = !expanded" :aria-expanded="expanded" class="flex items-center justify-between w-full px-6 py-5 text-sm lg:text-lg  text-gray-900 sm:p-6">
                           
                            <span>   هل أحتاج لأي خبرة تقنية لإنشاء صفحتي؟ </span>
                            <span x-show="expanded" aria-hidden="true" class="ms-4">
                                <svg class="w-6 h-6 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                                </svg>
                            </span>
                            <span x-show="!expanded" aria-hidden="true" class="ms-4">
                                <svg class="w-6 h-6 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                            </span>
                        </button>
                    </h3>
    
                    <div x-show="expanded" x-collapse>
                        <div class="px-6 pb-6">
                            <p class="text-sm font-thin lg:text-base text-gray-600">
                            لا، المنصة مصممة لتكون سهلة الاستخدام. يمكنك إنشاء صفحتك وتخصيصها خلال دقائق بدون أي معرفة تقنية.
                             </p>
                        </div>
                    </div>
                </div>
    
                <div
                    x-data="{
                            id: 3,
                            get expanded() {
                                return this.active === this.id
                            },
                            set expanded(value) {
                                this.active = value ? this.id : null
                            },
                        }"
                    role="region"
                >
                    <h3>
                        <button @click="expanded = !expanded" :aria-expanded="expanded" class="flex items-center justify-between w-full px-6 py-5 text-sm lg:text-lg  text-gray-900 sm:p-6">
                           
                            <span>  هل أحتاج لدفع رسوم لإنشاء الصفحة؟ </span>
                            <span x-show="expanded" aria-hidden="true" class="ms-4">
                                <svg class="w-6 h-6 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                                </svg>
                            </span>
                            <span x-show="!expanded" aria-hidden="true" class="ms-4">
                                <svg class="w-6 h-6 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                            </span>
                        </button>
                    </h3>
    
                    <div x-show="expanded" x-collapse>
                        <div class="px-6 pb-6">
                            <p class="text-sm font-thin lg:text-base text-gray-600">
                                لا،
                                نقدم خطة أساسية مجانية لإنشاء صفحتك بسرعة. كما توجد خيارات للترقية للاستفادة من مزايا إضافية مثل دومين مخصص وقوالب مخصصة وغيره
                            </p>
                        </div>
                    </div>
                </div>
    
                <div
                    x-data="{
                        id: 4,
                        get expanded() {
                            return this.active === this.id
                        },
                        set expanded(value) {
                            this.active = value ? this.id : null
                        },
                    }"
                    role="region"
                >
                    <h3>
                        <button @click="expanded = !expanded" :aria-expanded="expanded" class="flex items-center justify-between w-full px-6 py-5 text-sm lg:text-lg  text-gray-900 sm:p-6">
                            <span> هل أستطيع استخدام رابط ونطاق مخصص لهويتي مثل company.com ؟  </span>
                            <span x-show="expanded" aria-hidden="true" class="ms-4">
                                <svg class="w-6 h-6 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                                </svg>
                            </span>
                            <span x-show="!expanded" aria-hidden="true" class="ms-4">
                                <svg class="w-6 h-6 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                            </span>
                        </button>
                    </h3>
    
                    <div x-show="expanded" x-collapse>
                        <div class="px-6 pb-6">
                            <p class="text-sm font-thin text-gray-600 lg:text-base"> 
                                نعم، في باقة بريميوم
                                يمكنك ربط نطاقك (company.com) للاستخدام في الصفحة بدل 
                                من رابط صفحة أعمال الافتراضي.
                            </p>
                        </div>
                    </div>
                </div>
    
                <div
                    x-data="{
                            id: 5,
                            get expanded() {
                                return this.active === this.id
                            },
                            set expanded(value) {
                                this.active = value ? this.id : null
                            },
                        }"
                    role="region"
                >
                    <h3>
                        <button @click="expanded = !expanded" :aria-expanded="expanded" class="flex items-center justify-between w-full px-6 py-5 text-sm lg:text-lg  text-gray-900 sm:p-6">
                            <span> ماهي الدول المسموح بها لاستخدام منصة صفحة أعمال ؟ </span>
                            <span x-show="expanded" aria-hidden="true" class="ms-4">
                                <svg class="w-6 h-6 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                                </svg>
                            </span>
                            <span x-show="!expanded" aria-hidden="true" class="ms-4">
                                <svg class="w-6 h-6 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                            </span>
                        </button>
                    </h3>
    
                    <div x-show="expanded" x-collapse>
                        <div class="px-6 pb-6">
                            <p class="text-sm font-thin text-gray-600 lg:text-base"> 
                             لا توجد قيود على الدول. يمكنك استخدام منصة صفحة أعمال من أي مكان به انترنت.
                             <br>
القيود ستكون عند تعاملك مع شركات الأطراف الخارجية مثل بوابات الدفع والمنصات الأخرى التي تود ربطها في مع صفحة أعمال. 

                            </p>
                        </div>
                    </div>
                </div>
            </div>
     
        </div>
    </section>


   


      <div class=" text-center max-w-sm lg:max-w-xl mx-auto">
                <div class=" flex items-center justify-center gap-3">
                    <a href="{{ route('auth.register-login') }}" wire:navigate class="inline-flex items-center gap-3 rounded-full bg-primary-600 text-white px-3 py-3 text-2xl ps-6 font-semiboldx shadow-sm hover:bg-primary-700">
                        أنشئ صفحتك مجاناً 
                        <ui:icon name="arrow-left" size="10" class="bg-amber-400 rounded-full p-1" />
                    </a>
                </div>
                <p class="mt-2 text-xs text-stone-500/50">خطة مجانية •  أنشئ صفحتك بثواني</p>
        </div>

<footer class="lg:py-2 py-6 bg-white mt-32">
    <div class="px-4 mx-auto max-w-7xl sm:px-6 lg:px-8">
     
        <div class=" md:flex md:items-center md:justify-between">
            <ul class="flex items-center justify-center gap-x-6  order-4 md:justify-end">
                <li>
                    <a href="#" wire:navigate title="" class="text-xs font-normal text-gray-600 transition-all duration-200 font-pj hover:text-gray-900"> الشروط والأحكام </a>
                </li>

                {{-- <li>
                    <a href="{{ route('site.page.privacy') }}" wire:navigate title="" class="text-xs font-normal text-gray-600 transition-all duration-200 font-pj hover:text-gray-900"> الخصوصية </a>
                </li> --}}
            </ul>

            <ul class="flex items-center justify-center  gap-x-3 order-3 lg:justify-end mt-5 lg:mt-0">
                <li>
                    <a href="https://x.com/broshurcom" target="_blank" title="" class="inline-flex items-center justify-center w-10 h-10 text-gray-900 transition-all duration-200 rounded-full hover:bg-gray-100 focus:outline-none focus:bg-gray-200 focus:ring-2 focus:ring-offset-2 focus:ring-gray-200" rel="noopener">
                        <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-brand-x"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 4l11.733 16h4.267l-11.733 -16z" /><path d="M4 20l6.768 -6.768m2.46 -2.46l6.772 -6.772" /></svg>
                        </svg>
                    </a>
                </li>
 
                <li>
                    <a href="https://www.instagram.com/broshurcom" target="_blank" title="" class="inline-flex items-center justify-center w-10 h-10 text-gray-900 transition-all duration-200 rounded-full hover:bg-gray-100 focus:outline-none focus:bg-gray-200 focus:ring-2 focus:ring-offset-2 focus:ring-gray-200" rel="noopener">
                        <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M11.999 7.377a4.623 4.623 0 1 0 0 9.248 4.623 4.623 0 0 0 0-9.248zm0 7.627a3.004 3.004 0 1 1 0-6.008 3.004 3.004 0 0 1 0 6.008z"></path>
                            <circle cx="16.806" cy="7.207" r="1.078"></circle>
                            <path
                                d="M20.533 6.111A4.605 4.605 0 0 0 17.9 3.479a6.606 6.606 0 0 0-2.186-.42c-.963-.042-1.268-.054-3.71-.054s-2.755 0-3.71.054a6.554 6.554 0 0 0-2.184.42 4.6 4.6 0 0 0-2.633 2.632 6.585 6.585 0 0 0-.419 2.186c-.043.962-.056 1.267-.056 3.71 0 2.442 0 2.753.056 3.71.015.748.156 1.486.419 2.187a4.61 4.61 0 0 0 2.634 2.632 6.584 6.584 0 0 0 2.185.45c.963.042 1.268.055 3.71.055s2.755 0 3.71-.055a6.615 6.615 0 0 0 2.186-.419 4.613 4.613 0 0 0 2.633-2.633c.263-.7.404-1.438.419-2.186.043-.962.056-1.267.056-3.71s0-2.753-.056-3.71a6.581 6.581 0 0 0-.421-2.217zm-1.218 9.532a5.043 5.043 0 0 1-.311 1.688 2.987 2.987 0 0 1-1.712 1.711 4.985 4.985 0 0 1-1.67.311c-.95.044-1.218.055-3.654.055-2.438 0-2.687 0-3.655-.055a4.96 4.96 0 0 1-1.669-.311 2.985 2.985 0 0 1-1.719-1.711 5.08 5.08 0 0 1-.311-1.669c-.043-.95-.053-1.218-.053-3.654 0-2.437 0-2.686.053-3.655a5.038 5.038 0 0 1 .311-1.687c.305-.789.93-1.41 1.719-1.712a5.01 5.01 0 0 1 1.669-.311c.951-.043 1.218-.055 3.655-.055s2.687 0 3.654.055a4.96 4.96 0 0 1 1.67.311 2.991 2.991 0 0 1 1.712 1.712 5.08 5.08 0 0 1 .311 1.669c.043.951.054 1.218.054 3.655 0 2.436 0 2.698-.043 3.654h-.011z"
                            ></path>
                        </svg>
                    </a>
                </li>
 
            </ul>

            <p class="text-xs font-normal text-center text-gray-400 md:text-start mt-3 lg:mt-0 md:order-3 font-pj"> © الحقوق محفوظة {{ date('Y') }} | <a href="https://broshur.com" class="text-primary-600 hover:text-primary-700"> صفحة أعمال </a> </p>
        </div>
    </div>
</footer>

</div>

<?php 
 
new 
#[\Livewire\Attributes\Title('أنشئ صفحة لأعمالك، تبيع عنّك بدقائق!')]
class extends \Livewire\Component {
     
}; ?>
