<x-tenant-theme::courses.layout>

<div class="flex items-center justify-between mb-5 px-2">
    <a href="{{route('tenant.courses.index')}}" wire:navigate class="w-10 h-10 rounded-full flex items-center justify-center bg-stone-100 hover:bg-stone-200 transition rotate-180">
      <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="arrow-left" aria-hidden="true" class="lucide lucide-arrow-left w-5 h-5 text-stone-700 "><path d="m12 19-7-7 7-7"></path><path d="M19 12H5"></path></svg>
    </a>
    <div class="flex items-center gap-2">
      <button class="w-10 h-10 rounded-full flex items-center justify-center bg-stone-100 hover:bg-stone-200 transition">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="share" aria-hidden="true" class="lucide lucide-share w-5 h-5 text-stone-700 "><path d="M12 2v13"></path><path d="m16 6-4-4-4 4"></path><path d="M4 12v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8"></path></svg>
      </button>  
      <button class="w-10 h-10 rounded-full flex items-center justify-center bg-stone-100 hover:bg-stone-200 transition">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="heart" aria-hidden="true" class="lucide lucide-heart h-4 w-4"><path d="M2 9.5a5.5 5.5 0 0 1 9.591-3.676.56.56 0 0 0 .818 0A5.49 5.49 0 0 1 22 9.5c0 2.29-1.5 4-3 5.5l-5.492 5.313a2 2 0 0 1-3 .019L5 15c-1.5-1.5-3-3.2-3-5.5"></path></svg> 
    </div>
    </button>
  </div>
  
    <section class="px-3 mb-8 w-full">

 


        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-12">
            <div class="space-y-4">
                <div class="aspect-video bg-stone-100 rounded-2xl overflow-hidden">
                    <img src="{{ $course['image'] }}" alt="{{ $course['title'] }}" class="w-full h-full object-cover">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div class="rounded-2xl border border-stone-200 bg-stone-50 p-4">
                        <p class="text-xs text-stone-500 mb-1">مقدم الدورة</p>
                        <p class="text-sm font-semibold text-stone-900">{{ $course['instructor'] }}</p>
                    </div>
                    <div class="rounded-2xl border border-stone-200 bg-stone-50 p-4">
                        <p class="text-xs text-stone-500 mb-1">عدد الساعات</p>
                        <p class="text-sm font-semibold text-stone-900">{{ $course['hours'] }} ساعة تدريبية</p>
                    </div>
                </div>
            </div>

            <div>
                <div class="flex items-center justify-between gap-3 mb-4">
                    <h1 class="text-2xl md:text-3xl font-bold tracking-tight text-stone-900">{{ $course['title'] }}</h1>
                    <span class="text-2xl font-bold text-primary-600">{{ $course['price'] }} ر.س</span>
                </div>

                <p class="text-stone-600 leading-8">{{ $course['description'] }}</p>

                <div class="mt-6 flex flex-wrap items-center gap-3">
                    <button class="inline-flex items-center gap-2 rounded-xl bg-primary-600 px-6 py-3 text-sm font-bold text-white hover:bg-primary-700 transition">
                        <iconify-icon icon="solar:check-circle-bold-duotone" class="text-xl"></iconify-icon>
                        الالتحاق بالكورس
                    </button>
                    <a href="{{ route('tenant.courses.index') }}" wire:navigate class="inline-flex items-center gap-2 rounded-xl border border-stone-300 bg-white px-5 py-3 text-sm font-semibold text-stone-700 hover:bg-stone-50">
                        <iconify-icon icon="solar:arrow-right-linear" class="text-xl"></iconify-icon>
                        الرجوع للدورات
                    </a>
                </div>

                <div class="mt-6 rounded-2xl border border-green-100 bg-green-50 p-4 text-sm text-green-800">
                    بعض الدروس مجانية ويمكن تشغيلها مباشرة بدون شراء الدورة كاملة.
                </div>
            </div>
        </div>
    </section>

    <section class="px-3 pb-8">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-bold text-stone-900">محتوى الكورس</h2>
            <span class="text-sm text-stone-500">الدروس مقسمة حسب الأقسام</span>
        </div>

        <div class="space-y-4">
            @foreach($course['sections'] as $section)
                <div wire:key="section-{{ $course['slug'] }}-{{ $section['id'] }}" class="rounded-2xl border border-stone-200 bg-white p-4 md:p-5">
                    <h3 class="text-base md:text-lg font-bold text-stone-900 mb-4">{{ $section['title'] }}</h3>

                    <div class="space-y-3">
                        @foreach($section['lessons'] as $lesson)
                            <div wire:key="lesson-{{ $course['slug'] }}-{{ $lesson['id'] }}" class="flex flex-col md:flex-row md:items-center gap-3 rounded-xl border border-stone-100 bg-stone-50 p-3">
                                <img src="{{ $lesson['image'] }}" alt="{{ $lesson['title'] }}" class="w-full md:w-44 h-28 object-cover rounded-lg">

                                <div class="flex-1">
                                    <div class="flex items-center gap-2 mb-1">
                                        <h4 class="font-semibold text-stone-900">{{ $lesson['title'] }}</h4>
                                        @if($lesson['free'])
                                            <span class="inline-flex items-center rounded-full bg-green-100 px-2 py-1 text-[11px] font-semibold text-green-700">مجاني</span>
                                        @else
                                            <span class="inline-flex items-center rounded-full bg-primary-100 px-2 py-1 text-[11px] font-semibold text-primary-700">ضمن الدورة</span>
                                        @endif
                                    </div>

                                    <p class="text-sm text-stone-500 inline-flex items-center gap-1">
                                        <iconify-icon icon="solar:clock-circle-bold-duotone" class="text-base"></iconify-icon>
                                        {{ $lesson['minutes'] }} دقيقة
                                    </p>
                                </div>

                                <button class="inline-flex items-center justify-center gap-2 rounded-xl px-4 py-2.5 text-sm font-semibold transition {{ $lesson['free'] ? 'bg-primary-600 text-white hover:bg-primary-700' : 'bg-stone-200 text-stone-500 cursor-not-allowed' }}">
                                    <iconify-icon icon="solar:play-circle-bold-duotone" class="text-lg"></iconify-icon>
                                    {{ $lesson['free'] ? 'تشغيل' : 'مقفل' }}
                                </button>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </section>
</x-tenant-theme::courses.layout>
