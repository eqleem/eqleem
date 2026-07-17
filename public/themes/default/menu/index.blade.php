<x-tenant-theme::menu.layout>
    <section
        class="p-1 space-y-5"
        x-data="{
            meals: @js($mealsForJs),
            currencySymbol: @js(money_symbol()),
            selectedMealId: null,
            selectedChoices: {},
            quantity: 1,
            get selectedMeal() {
                return this.meals.find((meal) => meal.id === this.selectedMealId) ?? null;
            },
            openMealModal(id) {
                this.selectedMealId = id;
                this.selectedChoices = {};
                this.quantity = 1;
                $dispatch('open-modal', { name: 'meal-customize-modal' });
            },
            toggleChoice(groupIndex, choiceId, type) {
                const key = String(groupIndex);
                if (type === 'multiple') {
                    const current = this.selectedChoices[key] ?? [];
                    if (current.includes(choiceId)) {
                        this.selectedChoices[key] = current.filter((id) => id !== choiceId);
                    } else {
                        this.selectedChoices[key] = [...current, choiceId];
                    }
                    return;
                }
                this.selectedChoices[key] = choiceId;
            },
            isChoiceSelected(groupIndex, choiceId, type) {
                const key = String(groupIndex);
                if (type === 'multiple') {
                    return (this.selectedChoices[key] ?? []).includes(choiceId);
                }
                return this.selectedChoices[key] === choiceId;
            }
        }"
    >
        @if ($addedToCart)
            <div class="mb-4 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
                تمت إضافة الوجبة إلى السلة.
                <a href="{{ route('tenant.pages.cart') }}" wire:navigate class="ms-1 font-semibold underline">عرض السلة</a>
            </div>
        @endif

        @if ($categories->isNotEmpty())
            <section class="mb-2 flex w-full items-center justify-between gap-3">
                <div class="flex items-center gap-3 overflow-x-auto no-scrollbar bg-stone-200/40 rounded-2xl p-1 whitespace-nowrap w-full">
                    <a
                        href="{{ route('tenant.menu.index') }}"
                        wire:click.prevent="$set('categorySlug', null)"
                        @class([
                            'p-3 text-center py-2.5 rounded-xl text-sm font-medium transition',
                            'bg-white text-stone-900 shadow-sm' => blank($categorySlug),
                            'hover:bg-stone-50 text-stone-600 hover:text-stone-900' => filled($categorySlug),
                        ])
                    >
                        الكل
                    </a>

                    @foreach ($categories as $category)
                        <a
                            href="{{ route('tenant.menu.index', ['category' => $category->slug]) }}"
                            wire:click.prevent="$set('categorySlug', '{{ $category->slug }}')"
                            wire:key="menu-category-filter-{{ $category->id }}"
                            @class([
                                'p-3 text-center py-2.5 rounded-xl text-sm font-medium transition',
                                'bg-white text-stone-900 shadow-sm' => $categorySlug === $category->slug,
                                'hover:bg-stone-50 text-stone-600 hover:text-stone-900' => $categorySlug !== $category->slug,
                            ])
                        >
                            {{ $category->name }}
                        </a>
                    @endforeach
                </div>

                <div class="flex items-center gap-3" x-data="{ open: false }">
                    <div x-show="open" x-transition class="hidden sm:block">
                        <input
                            wire:model.live.debounce.300ms="search"
                            type="search"
                            placeholder="ابحث في القائمة..."
                            class="w-44 rounded-xl border border-stone-200 bg-white px-3 py-2 text-sm text-stone-700 outline-none focus:border-stone-400"
                        >
                    </div>

                    <button type="button" @click="open = !open" class="p-3 rounded-xl bg-stone-200/40 hover:bg-stone-200 flex items-center justify-center transition" aria-label="البحث">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" class="size-6 text-stone-700"><path d="m21 21-4.34-4.34"></path><circle cx="11" cy="11" r="8"></circle></svg>
                    </button>
                </div>
            </section>
        @endif

        @if ($meals->isEmpty())
            <div class="rounded-2xl bg-stone-100/80 p-8 text-center">
                <p class="text-base font-semibold text-stone-700">لا توجد أصناف حالياً</p>
                <p class="mt-2 text-sm text-stone-500">ستظهر عناصر القائمة هنا عند إضافتها من لوحة التحكم.</p>
            </div>
        @else
            <div class="grid grid-cols-2 md:grid-cols-3 gap-5 mt-5">
                @foreach ($meals as $meal)
                    @php
                        $imageUrl = $meal->getFirstMediaUrl('menu-media') ?: $meal->avatar;
                        $price = (int) data_get($meal->data, 'price', 0);
                        $categoryName = $meal->taxonomies->first()?->name ?? '';
                    @endphp

                    <article wire:key="meal-{{ $meal->id }}" class="overflow-hidden rounded-2xl border border-stone-200 bg-white transition hover:shadow-sm">
                        <button type="button" class="block w-full text-start" x-on:click="openMealModal('{{ $meal->id }}')">
                            <img src="{{ $imageUrl }}" alt="{{ $meal->title }}" class="h-52 w-full object-cover">
                        </button>

                        <div class="space-y-3 p-4">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <button type="button" class="text-sm font-semibold text-stone-900 transition hover:text-primary-700" x-on:click="openMealModal('{{ $meal->id }}')">
                                        {{ $meal->title }}
                                    </button>

                                    @if ($categoryName !== '')
                                        <p class="text-xs text-stone-500">{{ $categoryName }}</p>
                                    @endif
                                </div>

                                @if ($price > 0)
                                    <span class="text-base font-bold text-stone-900" >{{ money_format($price) }}</span>
                                @endif
                            </div>

                            <button
                                type="button"
                                class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-primary-50 px-4 py-2.5 text-sm font-semibold text-primary-700 hover:bg-primary-100"
                                x-on:click="openMealModal('{{ $meal->id }}')"
                            >
                                <iconify-icon icon="hugeicons:shopping-basket-02" class="text-xl"></iconify-icon>
                                أضف للسلة
                            </button>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif

        <x-tenant-theme::modal name="meal-customize-modal" maxWidth="lg">
            <x-slot:title>إضافة الوجبة للسلة</x-slot:title>

            <div dir="rtl" class="space-y-5" x-show="selectedMeal">
                <template x-if="selectedMeal">
                    <div class="space-y-5">
                        <div class="flex items-center gap-3 rounded-xl bg-stone-50 p-3">
                            <img :src="selectedMeal.image" :alt="selectedMeal.name" class="h-16 w-16 rounded-lg object-cover">
                            <div class="flex-1">
                                <p class="text-sm text-stone-500" x-text="selectedMeal.category"></p>
                                <h4 class="text-base font-bold text-stone-900" x-text="selectedMeal.name"></h4>
                            </div>
                            <span dir="ltr" class="text-base font-bold text-primary-600" x-text="selectedMeal.price > 0 ? `${(selectedMeal.price / 100).toFixed(2)} ${currencySymbol}` : '—'"></span>
                        </div>

                        <template x-for="(group, groupIndex) in selectedMeal.meal_options" :key="group.id ?? groupIndex">
                            <div>
                                <p class="mb-2 text-sm font-semibold text-stone-700" x-text="group.name"></p>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                    <template x-for="(choice, choiceIndex) in (group.choices ?? [])" :key="choice.id ?? choiceIndex">
                                        <button
                                            type="button"
                                            class="inline-flex items-center justify-between rounded-xl border px-3 py-2 text-sm font-medium transition"
                                            :class="isChoiceSelected(groupIndex, choice.id, group.type) ? 'border-primary-500 bg-primary-50 text-primary-700' : 'border-stone-200 text-stone-600 hover:bg-stone-50'"
                                            x-on:click="toggleChoice(groupIndex, choice.id, group.type)"
                                        >
                                            <span x-text="choice.name"></span>
                                            <span x-show="choice.price > 0" x-text="`+${(choice.price / 100).toFixed(2)}`" class="text-xs"></span>
                                        </button>
                                    </template>
                                </div>
                            </div>
                        </template>

                        <div class="flex items-center justify-between rounded-xl bg-stone-50 px-4 py-3">
                            <span class="text-sm font-semibold text-stone-700">الكمية</span>
                            <div class="inline-flex items-center gap-3">
                                <button type="button" class="h-8 w-8 rounded-lg bg-white text-stone-600 border border-stone-200" x-on:click="quantity = Math.max(1, quantity - 1)">-</button>
                                <span class="min-w-5 text-center text-sm font-bold text-stone-900" x-text="quantity"></span>
                                <button type="button" class="h-8 w-8 rounded-lg bg-white text-stone-600 border border-stone-200" x-on:click="quantity++">+</button>
                            </div>
                        </div>

                        @error('meal_options')
                            <p class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ $message }}</p>
                        @enderror

                        <button
                            type="button"
                            wire:loading.attr="disabled"
                            class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-primary-600 px-4 py-3 text-sm font-bold text-white transition hover:bg-primary-700 disabled:cursor-not-allowed disabled:opacity-50"
                            x-on:click="$wire.addMealToCart(parseInt(selectedMealId, 10), quantity, selectedChoices)"
                        >
                            <iconify-icon icon="hugeicons:shopping-basket-02" class="text-xl"></iconify-icon>
                            <span wire:loading.remove wire:target="addMealToCart">إضافة للسلة</span>
                            <span wire:loading wire:target="addMealToCart">جاري الإضافة...</span>
                        </button>
                    </div>
                </template>
            </div>
        </x-tenant-theme::modal>
    </section>
</x-tenant-theme::menu.layout>
