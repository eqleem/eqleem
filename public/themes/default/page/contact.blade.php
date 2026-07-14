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

                        <livewire:tenant.pages.contact-form
                            :page-id="$page->id"
                            :form-fields="$formFields"
                            :success-message="$successMessage"
                            :key="'contact-page-form-'.$page->id"
                        />
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
