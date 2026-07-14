<x-tenant-theme::pages.layout>
    <x-tenant-theme::breadcrumb :links="[['url' => route('tenant.store.index'), 'title' => 'المتجر'], ['url' => null, 'title' => 'السلة']]" />
    <section class="space-y-5 p-2" dir="rtl">
        @if ($items->isEmpty())
            <div class="flex flex-col items-center rounded-2xl border border-dashed border-stone-200 bg-gradient-to-b from-stone-50 to-white px-6 py-14 text-center sm:px-10">
                <div class="relative mb-6">
                    <div class="absolute inset-0 scale-125 rounded-full bg-primary-100/60 blur-xl" aria-hidden="true"></div>
                    <div class="relative flex h-24 w-24 items-center justify-center rounded-full bg-primary-50 ring-8 ring-primary-50/50">
                        <iconify-icon icon="hugeicons:shopping-cart-01" class="text-5xl text-primary-600" aria-hidden="true"></iconify-icon>
                    </div>
                </div>

                <h3 class="text-lg font-bold text-stone-900">سلتك فارغة حالياً</h3>
                <p class="mt-2 max-w-sm text-sm leading-relaxed text-stone-500">
                    ابدأ بتصفح المنتجات والخدمات والدورات، ثم أضف ما يعجبك إلى السلة.
                </p>

                <a
                    href="{{ route('tenant.home') }}"
                    wire:navigate
                    class="mt-7 inline-flex items-center justify-center gap-2 rounded-xl bg-primary-600 px-6 py-3 text-sm font-bold text-white transition hover:bg-primary-700"
                >
                    <iconify-icon icon="hugeicons:home-01" class="text-xl" aria-hidden="true"></iconify-icon>
                    تصفح المنتجات والخدمات
                </a>
            </div>
        @else
            <div class="rounded-2xl border border-stone-200 bg-white p-5">
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="text-lg font-bold text-stone-900">ملخص السلة</h3>
                    <span class="rounded-lg bg-primary-50 px-3 py-1 text-xs font-semibold text-primary-700">{{ $itemCount }} {{ $itemCount === 1 ? 'عنصر' : 'عناصر' }}</span>
                </div>

                <div class="space-y-3">
                    @foreach ($items as $item)
                        <article wire:key="cart-item-{{ $item->id }}" class="flex items-center gap-3 rounded-xl bg-stone-50 p-3">
                            @if ($item->imageUrl())
                                <img src="{{ $item->imageUrl() }}" alt="{{ $item->title() }}" class="h-16 w-16 rounded-lg object-cover">
                            @else
                                <div class="flex h-16 w-16 items-center justify-center rounded-lg bg-stone-200 text-stone-500">
                                    <iconify-icon icon="hugeicons:image-01" class="text-2xl"></iconify-icon>
                                </div>
                            @endif

                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-semibold text-stone-900">{{ $item->title() }}</p>

                                @if ($item->isBooking())
                                    <div class="mt-1 space-y-0.5 text-xs text-stone-500">
                                        @if ($item->bookingDateLabel())
                                            <p>{{ $item->bookingDateLabel() }}</p>
                                        @endif
                                        @if ($item->bookingTimeLabel())
                                            <p dir="ltr">{{ $item->bookingTimeLabel() }}</p>
                                        @endif
                                    </div>
                                @elseif ($item->mealOptionsLabel())
                                    <p class="mt-1 text-xs text-stone-500">{{ $item->mealOptionsLabel() }}</p>
                                @endif

                                <div class="mt-2 flex items-center gap-2">
                                    @if ($item->isBooking())
                                        <span class="rounded-md bg-primary-50 px-2 py-1 text-xs font-medium text-primary-700">حجز</span>
                                    @else
                                        <button
                                            type="button"
                                            wire:click="updateQuantity({{ $item->id }}, {{ $item->quantity - 1 }})"
                                            class="flex h-7 w-7 items-center justify-center rounded-md border border-stone-200 text-stone-600 hover:bg-white"
                                            aria-label="تقليل الكمية"
                                        >−</button>
                                        <span class="w-6 text-center text-xs font-medium">{{ $item->quantity }}</span>
                                        <button
                                            type="button"
                                            wire:click="updateQuantity({{ $item->id }}, {{ $item->quantity + 1 }})"
                                            class="flex h-7 w-7 items-center justify-center rounded-md border border-stone-200 text-stone-600 hover:bg-white"
                                            aria-label="زيادة الكمية"
                                        >+</button>
                                    @endif
                                    <button
                                        type="button"
                                        wire:click="removeItem({{ $item->id }})"
                                        wire:confirm="هل تريد حذف هذا العنصر من السلة؟"
                                        class="ms-auto text-xs text-red-600 hover:text-red-700"
                                    >
                                        حذف
                                    </button>
                                </div>
                            </div>

                            <span class="shrink-0 text-sm font-bold text-stone-900" >{{ money_format($item->lineTotal()) }}</span>
                        </article>
                    @endforeach
                </div>
            </div>

            <div class="rounded-2xl border border-stone-200 bg-white p-5">
                <div class="mb-4 flex items-center justify-between text-sm">
                    <span class="text-stone-500">الإجمالي</span>
                    <span class="font-semibold text-stone-900" >{{ money_format($subtotal) }}</span>
                </div>

                <a
                    href="{{ route('tenant.pages.checkout') }}"
                    wire:navigate
                    class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-primary-600 px-5 py-3 text-sm font-bold text-white transition hover:bg-primary-700"
                >
                    <iconify-icon icon="hugeicons:credit-card" class="text-xl"></iconify-icon>
                    إتمام الشراء
                </a>
            </div>
        @endif
    </section>
</x-tenant-theme::pages.layout>
