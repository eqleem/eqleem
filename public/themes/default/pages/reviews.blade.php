<x-tenant-theme::pages.layout>
    <x-tenant-theme::breadcrumb :links="[['url' => null, 'title' => 'تقييمات وآراء عملاء']]" />
 
    <section class="mt-8" >
        <div class="grid grid-cols-1 gap-8 sm:grid-cols-2 sm:gap-16">
            <div class="rounded-xl bg-stone-100 p-5">
                <h3 class="text-xl font-bold text-gray-900">تقييمات وآراء عملاء أبعاد البيت</h3>
                <div class="mt-6 flex items-center">
                    <div class="flex items-center space-x-px">
                        <iconify-icon icon="solar:star-bold" class="text-amber-500 text-xl"></iconify-icon>
                        <iconify-icon icon="solar:star-bold" class="text-amber-500 text-xl"></iconify-icon>
                        <iconify-icon icon="solar:star-bold" class="text-amber-500 text-xl"></iconify-icon>
                        <iconify-icon icon="solar:star-bold" class="text-amber-500 text-xl"></iconify-icon>
                        <iconify-icon icon="solar:star-bold" class="text-amber-500 text-xl"></iconify-icon>
                    </div>
                    <span class="mr-3 text-sm font-medium text-gray-600">(4.9 من 5)</span>
                </div>
                <p class="mt-2.5 text-sm font-medium text-gray-600">بناءً على 12,540 تقييم</p>
                <button
                    type="button"
                    class="mt-4 inline-flex items-center gap-2 rounded-xl bg-primary-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-primary-700"
                    x-on:click="$dispatch('open-modal', { name: 'add-testimonial-modal' })"
                >
                    <iconify-icon icon="hugeicons:comment-add-01" class="text-lg"></iconify-icon>
                    إضافة تقييم
                </button>
            </div>

            <div>
                <ul class="space-y-2.5">
                    <li class="grid grid-cols-5 items-center gap-x-4">
                        <span class="whitespace-nowrap text-sm font-medium text-gray-600">5 نجوم</span>
                        <div class="relative col-span-3 h-1.5 w-full rounded-full bg-gray-200">
                            <div class="absolute inset-y-0 left-0 w-[86%] rounded-full bg-gray-900"></div>
                        </div>
                        <span class="whitespace-nowrap text-sm font-medium text-gray-600">10,784</span>
                    </li>
                    <li class="grid grid-cols-5 items-center gap-x-4">
                        <span class="whitespace-nowrap text-sm font-medium text-gray-600">4 نجوم</span>
                        <div class="relative col-span-3 h-1.5 w-full rounded-full bg-gray-200">
                            <div class="absolute inset-y-0 left-0 w-[38%] rounded-full bg-gray-900"></div>
                        </div>
                        <span class="whitespace-nowrap text-sm font-medium text-gray-600">1,352</span>
                    </li>
                    <li class="grid grid-cols-5 items-center gap-x-4">
                        <span class="whitespace-nowrap text-sm font-medium text-gray-600">3 نجوم</span>
                        <div class="relative col-span-3 h-1.5 w-full rounded-full bg-gray-200">
                            <div class="absolute inset-y-0 left-0 w-[16%] rounded-full bg-gray-900"></div>
                        </div>
                        <span class="whitespace-nowrap text-sm font-medium text-gray-600">286</span>
                    </li>
                    <li class="grid grid-cols-5 items-center gap-x-4">
                        <span class="whitespace-nowrap text-sm font-medium text-gray-600">2 نجوم</span>
                        <div class="relative col-span-3 h-1.5 w-full rounded-full bg-gray-200">
                            <div class="absolute inset-y-0 left-0 w-[8%] rounded-full bg-gray-900"></div>
                        </div>
                        <span class="whitespace-nowrap text-sm font-medium text-gray-600">73</span>
                    </li>
                    <li class="grid grid-cols-5 items-center gap-x-4">
                        <span class="whitespace-nowrap text-sm font-medium text-gray-600">1 نجمة</span>
                        <div class="relative col-span-3 h-1.5 w-full rounded-full bg-gray-200">
                            <div class="absolute inset-y-0 left-0 w-[5%] rounded-full bg-gray-900"></div>
                        </div>
                        <span class="whitespace-nowrap text-sm font-medium text-gray-600">45</span>
                    </li>
                </ul>
            </div>
        </div>

        <hr class="mt-10 border-gray-200" />

        <div class="mt-10 flow-root">
            <ul class="flex flex-col gap-y-5">
                <li class="grid grid-cols-1 gap-x-8 gap-y-8 rounded-xl bg-stone-100 p-5 py-8 md:grid-cols-7">
                    <div class="md:col-span-2">
                        <div class="flex items-center space-x-px">
                            <iconify-icon icon="solar:star-bold" class="text-amber-500 text-xl"></iconify-icon>
                            <iconify-icon icon="solar:star-bold" class="text-amber-500 text-xl"></iconify-icon>
                            <iconify-icon icon="solar:star-bold" class="text-amber-500 text-xl"></iconify-icon>
                            <iconify-icon icon="solar:star-bold" class="text-amber-500 text-xl"></iconify-icon>
                            <iconify-icon icon="solar:star-bold" class="text-amber-500 text-xl"></iconify-icon>
                        </div>
                        <div class="mt-5 flex items-start md:flex-col">
                            <div class="flex-shrink-0">
                                <p class="text-sm font-bold text-gray-900">أحمد القحطاني</p>
                                <p class="mt-1 text-sm font-normal text-gray-500">15 يونيو 2026</p>
                            </div>
                            <div class="mr-4 flex items-center text-sm font-medium text-gray-600 md:mr-0 md:mt-4">
                                <iconify-icon icon="solar:verified-check-bold" class="ml-1.5 text-green-500 text-xl"></iconify-icon>
                                عميل موثق
                            </div>
                        </div>
                    </div>
                    <div class="md:col-span-5">
                        <p class="text-base font-bold text-gray-900">جودة تشطيب ممتازة والتزام كامل بالوقت</p>
                        <blockquote class="mt-4">
                            <p class="text-sm font-normal leading-7 text-gray-900">
                                تعاملت مع أبعاد البيت في تشطيب مجلس وصالة، والنتيجة كانت ممتازة جدًا. جودة المواد واضحة من أول يوم، والفريق كان منظم وسريع في التنفيذ بدون تأخير.
                            </p>
                        </blockquote>
                        <div class="mt-4 flex flex-wrap gap-2">
                            <img src="https://images.unsplash.com/photo-1616486338812-3dadae4b4ace?q=80&w=500&auto=format&fit=crop" alt="تنفيذ مجلس 2" class="h-20 rounded-lg object-cover">
                        </div>
                    </div>
                </li>

                <li class="grid grid-cols-1 gap-x-8 gap-y-8 rounded-xl bg-stone-100 p-5 py-8 md:grid-cols-7">
                    <div class="md:col-span-2">
                        <div class="flex items-center space-x-px">
                            <iconify-icon icon="solar:star-bold" class="text-amber-500 text-xl"></iconify-icon>
                            <iconify-icon icon="solar:star-bold" class="text-amber-500 text-xl"></iconify-icon>
                            <iconify-icon icon="solar:star-bold" class="text-amber-500 text-xl"></iconify-icon>
                            <iconify-icon icon="solar:star-bold" class="text-amber-500 text-xl"></iconify-icon>
                            <iconify-icon icon="solar:star-half-bold" class="text-amber-500 text-xl"></iconify-icon>
                        </div>
                        <div class="mt-5 flex items-start md:flex-col">
                            <div class="flex-shrink-0">
                                <p class="text-sm font-bold text-gray-900">نورة الحربي</p>
                                <p class="mt-1 text-sm font-normal text-gray-500">9 يونيو 2026</p>
                            </div>
                            <div class="mr-4 flex items-center text-sm font-medium text-gray-600 md:mr-0 md:mt-4">
                                <iconify-icon icon="solar:verified-check-bold" class="ml-1.5 text-green-500 text-xl"></iconify-icon>
                                عميل موثق
                            </div>
                        </div>
                    </div>
                    <div class="md:col-span-5">
                        <p class="text-base font-bold text-gray-900">سرعة إنجاز وتواصل ممتاز طوال المشروع</p>
                        <blockquote class="mt-4">
                            <p class="text-sm font-normal leading-7 text-gray-900">
                                أكثر شيء أعجبني في أبعاد البيت هو سرعة الرد والمتابعة اليومية. أي ملاحظة كنت أرسلها يتم التعامل معها فورًا، والشغل طلع مرتب جدًا.
                            </p>
                        </blockquote>
                        <div class="mt-4 flex flex-wrap gap-2">
                            <img src="https://images.unsplash.com/photo-1484154218962-a197022b5858?q=80&w=500&auto=format&fit=crop" alt="تنفيذ مطبخ 1" class="h-20 rounded-lg object-cover">
                            <img src="https://images.unsplash.com/photo-1616046229478-9901c5536a45?q=80&w=500&auto=format&fit=crop" alt="تنفيذ مطبخ 3" class="h-20 rounded-lg object-cover">
                        </div>
                    </div>
                </li>

                <li class="grid grid-cols-1 gap-x-8 gap-y-8 rounded-xl bg-stone-100 p-5 py-8 md:grid-cols-7">
                    <div class="md:col-span-2">
                        <div class="flex items-center space-x-px">
                            <iconify-icon icon="solar:star-bold" class="text-amber-500 text-xl"></iconify-icon>
                            <iconify-icon icon="solar:star-bold" class="text-amber-500 text-xl"></iconify-icon>
                            <iconify-icon icon="solar:star-bold" class="text-amber-500 text-xl"></iconify-icon>
                            <iconify-icon icon="solar:star-bold" class="text-amber-500 text-xl"></iconify-icon>
                            <iconify-icon icon="solar:star-bold" class="text-amber-500 text-xl"></iconify-icon>
                        </div>
                        <div class="mt-5">
                            <p class="text-sm font-bold text-gray-900">سالم الشهري</p>
                            <p class="mt-1 text-sm font-normal text-gray-500">2 يونيو 2026</p>
                        </div>
                    </div>
                    <div class="md:col-span-5">
                        <p class="text-base font-bold text-gray-900">منتجات ممتازة وتشطيبات دقيقة جدًا</p>
                        <blockquote class="mt-4">
                            <p class="text-sm font-normal leading-7 text-gray-900">
                                طلبت تركيب بديل الرخام والنتيجة كانت أجمل من المتوقع. الخامات ممتازة والقص والتركيب احترافي جدًا، واللمسات النهائية فرّقت كثير في شكل المكان.
                            </p>
                        </blockquote>
                    </div>
                </li>
            </ul>
        </div>
    </section>

    <x-tenant-theme::modal name="add-testimonial-modal" maxWidth="xl">
        <x-slot:title>إضافة تقييم جديد</x-slot:title>

        <form class="space-y-4" dir="rtl">
            <div class="space-y-1">
                <label class="text-sm font-medium text-stone-700">الاسم</label>
                <input
                    type="text"
                    class="w-full rounded-xl border border-stone-200 px-4 py-3 text-sm text-stone-700 focus:border-primary-300 focus:outline-none"
                    placeholder="اكتب اسمك"
                >
            </div>

            <div class="space-y-1">
                <label class="text-sm font-medium text-stone-700">التقييم</label>
                <div class="flex items-center gap-2 rounded-xl border border-stone-200 bg-stone-50 px-4 py-3 text-amber-500">
                    <iconify-icon icon="solar:star-bold" class="text-2xl"></iconify-icon>
                    <iconify-icon icon="solar:star-bold" class="text-2xl"></iconify-icon>
                    <iconify-icon icon="solar:star-bold" class="text-2xl"></iconify-icon>
                    <iconify-icon icon="solar:star-bold" class="text-2xl"></iconify-icon>
                    <iconify-icon icon="solar:star-bold" class="text-2xl"></iconify-icon>
                </div>
            </div>

            <div class="space-y-1">
                <label class="text-sm font-medium text-stone-700">عنوان مختصر</label>
                <input
                    type="text"
                    class="w-full rounded-xl border border-stone-200 px-4 py-3 text-sm text-stone-700 focus:border-primary-300 focus:outline-none"
                    placeholder="مثال: سرعة إنجاز وجودة عالية"
                >
            </div>

            <div class="space-y-1">
                <label class="text-sm font-medium text-stone-700">نص التقييم</label>
                <textarea
                    rows="4"
                    class="w-full rounded-xl border border-stone-200 px-4 py-3 text-sm text-stone-700 focus:border-primary-300 focus:outline-none"
                    placeholder="اكتب تجربتك مع أبعاد البيت"
                ></textarea>
            </div>

            <div class="space-y-1">
                <label class="text-sm font-medium text-stone-700">صور تنفيذ الخدمة (اختياري)</label>
                <input
                    type="file"
                    multiple
                    class="w-full rounded-xl border border-dashed border-stone-300 bg-stone-50 px-4 py-3 text-sm text-stone-600 file:ml-3 file:rounded-lg file:border-0 file:bg-primary-50 file:px-3 file:py-1.5 file:text-primary-700"
                >
            </div>

            <button
                type="button"
                class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-primary-600 px-5 py-3 text-sm font-bold text-white transition hover:bg-primary-700"
            >
                <iconify-icon icon="hugeicons:sent" class="text-xl"></iconify-icon>
                إرسال التقييم
            </button>
        </form>
    </x-tenant-theme::modal>
</x-tenant-theme::pages.layout>