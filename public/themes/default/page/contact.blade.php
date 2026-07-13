<x-tenant-theme::module-layout>
    <section class="mb-6">
        <x-tenant-theme::breadcrumb :links="[['url' => null, 'title' => 'اتصل بنا']]" />
    </section>


    <section >
  
        <div class="grid grid-cols-1 gap-4 md:grid-cols-12">
            @if ($showForm)
                <div class="space-y-4 md:col-span-7">
                    <article class="rounded-2xl bg-white p-5">
                        <h2 class="text-lg font-black text-stone-900">نموذج التواصل</h2>
                        <p class="mt-1 text-sm text-stone-500">اكتب تفاصيل طلبك وسنعود لك في أقرب وقت ممكن.</p>

                        <form class="mt-5 space-y-4">
                            @if (($formFields['name'] ?? false) || ($formFields['phone'] ?? false))
                                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                                    @if ($formFields['name'] ?? false)
                                        <div>
                                            <label class="mb-1.5 block text-xs font-semibold text-stone-500">الاسم الكامل</label>
                                            <input type="text" class="w-full rounded-xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none focus:border-primary-300 focus:bg-white" placeholder="اكتب اسمك" />
                                        </div>
                                    @endif
                                    @if ($formFields['phone'] ?? false)
                                        <div>
                                            <label class="mb-1.5 block text-xs font-semibold text-stone-500">رقم الجوال</label>
                                            <input type="tel" dir="ltr" class="w-full rounded-xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none focus:border-primary-300 focus:bg-white" placeholder="+9665XXXXXXXX" />
                                        </div>
                                    @endif
                                </div>
                            @endif

                            @if ($formFields['email'] ?? false)
                                <div>
                                    <label class="mb-1.5 block text-xs font-semibold text-stone-500">البريد الإلكتروني</label>
                                    <input type="email" dir="ltr" class="w-full rounded-xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none focus:border-primary-300 focus:bg-white" placeholder="name@email.com" />
                                </div>
                            @endif

                            @if ($formFields['address'] ?? false)
                                <div>
                                    <label class="mb-1.5 block text-xs font-semibold text-stone-500">العنوان</label>
                                    <input type="text" class="w-full rounded-xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none focus:border-primary-300 focus:bg-white" placeholder="اكتب عنوانك" />
                                </div>
                            @endif

                            @if ($formFields['subject'] ?? false)
                                <div>
                                    <label class="mb-1.5 block text-xs font-semibold text-stone-500">نوع الرسالة</label>
                                    <select class="w-full rounded-xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none focus:border-primary-300 focus:bg-white">
                                        <option>استفسار عام</option>
                                        <option>طلب خدمة</option>
                                        <option>استشارة</option>
                                        <option>شكوى أو ملاحظة</option>
                                    </select>
                                </div>
                            @endif

                            @if ($formFields['message'] ?? false)
                                <div>
                                    <label class="mb-1.5 block text-xs font-semibold text-stone-500">رسالتك</label>
                                    <textarea rows="5" class="w-full rounded-xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none focus:border-primary-300 focus:bg-white" placeholder="اكتب تفاصيل طلبك هنا..."></textarea>
                                </div>
                            @endif

                            <button type="button" class="inline-flex items-center justify-center gap-2 rounded-xl bg-primary-600 px-5 py-3 text-sm font-bold text-white transition hover:bg-primary-700">
                                <iconify-icon icon="hugeicons:sent-02" class="text-lg"></iconify-icon>
                                إرسال الرسالة
                            </button>
                        </form>
                    </article>
                </div>
            @endif

            @if ($showContactInfo || $showFaqLink || $showReviewsLink || $showSocialLinks)
                <aside @class(['space-y-4', 'md:col-span-5' => $showForm, 'md:col-span-12' => ! $showForm])>
                    @if ($showContactInfo || $showFaqLink || $showReviewsLink)
                        <article class="rounded-2xl bg-white p-5">
                            @if ($showContactInfo)
                                <h2 class="text-base font-black text-stone-900">بيانات التواصل</h2>
                                <div class="mt-4 space-y-3">
                                    @if ($phone !== '')
                                        <a href="tel:{{ $phoneDial }}" class="flex items-center justify-between rounded-xl bg-stone-50 px-4 py-3 text-sm text-stone-700 hover:bg-stone-100">
                                            <span class="inline-flex items-center gap-2"><iconify-icon icon="hugeicons:call" class="text-lg text-primary-600"></iconify-icon>الجوال</span>
                                            <span dir="ltr" class="font-semibold">{{ $phone }}</span>
                                        </a>
                                    @endif
                                    @if ($email !== '')
                                        <a href="mailto:{{ $email }}" class="flex items-center justify-between rounded-xl bg-stone-50 px-4 py-3 text-sm text-stone-700 hover:bg-stone-100">
                                            <span class="inline-flex items-center gap-2"><iconify-icon icon="hugeicons:mail-02" class="text-lg text-primary-600"></iconify-icon>البريد</span>
                                            <span dir="ltr" class="font-semibold">{{ $email }}</span>
                                        </a>
                                    @endif
                                </div>

                                <div @class(['grid grid-cols-1 gap-2 sm:grid-cols-2', 'mt-4' => $phone !== '' || $email !== ''])>
                                    @if ($phoneDial !== '')
                                        <a href="tel:{{ $phoneDial }}" class="inline-flex items-center justify-center gap-2 rounded-xl border border-stone-200 bg-white px-3 py-2.5 text-sm font-semibold text-stone-700 hover:bg-stone-50">
                                            <iconify-icon icon="hugeicons:call" class="text-lg"></iconify-icon>
                                            اتصال
                                        </a>
                                    @endif
                                    @if ($whatsappUrl)
                                        <a href="{{ $whatsappUrl }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-600 px-3 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700">
                                            <iconify-icon icon="hugeicons:whatsapp" class="text-lg"></iconify-icon>
                                            واتساب
                                        </a>
                                    @endif
                                </div>
                            @endif

                            @if ($showFaqLink || $showReviewsLink)
                                <div @class(['grid grid-cols-1 gap-2 sm:grid-cols-2', 'mt-4' => $showContactInfo])>
                                    @if ($showFaqLink)
                                        <a href="{{ $faqUrl }}" wire:navigate class="inline-flex items-center justify-center gap-2 rounded-xl border border-stone-200 bg-white px-3 py-2.5 text-sm font-semibold text-stone-700 hover:bg-stone-50">
                                            <iconify-icon icon="hugeicons:help-circle" class="text-lg"></iconify-icon>
                                            الأسئلة المتكررة
                                        </a>
                                    @endif
                                    @if ($showReviewsLink)
                                        <a href="{{ route('tenant.pages.reviews') }}" wire:navigate class="inline-flex items-center justify-center gap-2 rounded-xl border border-stone-200 bg-white px-3 py-2.5 text-sm font-semibold text-stone-700 hover:bg-stone-50">
                                            <iconify-icon icon="hugeicons:star" class="text-lg"></iconify-icon>
                                            التقييمات
                                        </a>
                                    @endif
                                </div>
                            @endif
                        </article>
                    @endif

                    @if ($showSocialLinks && count($socialLinks) > 0)
                        <article class="rounded-2xl bg-white p-5">
                            <h2 class="text-base font-black text-stone-900">السوشال ميديا</h2>
                            <div class="mt-4 space-y-2">
                                @foreach ($socialLinks as $social)
                                    <a
                                        href="{{ $social['url'] }}"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        wire:key="contact-social-{{ $loop->index }}"
                                        class="flex items-center justify-between rounded-xl bg-stone-50 px-4 py-3 text-sm text-stone-700 transition hover:bg-stone-100"
                                    >
                                        <span class="inline-flex items-center gap-2">
                                            <iconify-icon icon="{{ $social['icon'] }}" class="text-lg text-primary-600"></iconify-icon>
                                            {{ $social['name'] }}
                                        </span>
                                        <iconify-icon icon="solar:arrow-left-linear" class="text-base"></iconify-icon>
                                    </a>
                                @endforeach
                            </div>
                        </article>
                    @endif
                </aside>
            @endif
        </div>
    </section>
</x-tenant-theme::module-layout>
