<x-tenant::menu.layout>
    <section
        class="p-1 space-y-5"
        x-data="{
            meals: @js($meals),
            selectedMealId: null,
            selectedSize: 'medium',
            selectedOptions: [],
            quantity: 1,
            get selectedMeal() {
                return this.meals.find((meal) => meal.id === this.selectedMealId) ?? null;
            },
            openMealModal(id) {
                this.selectedMealId = id;
                this.selectedSize = 'medium';
                this.selectedOptions = [];
                this.quantity = 1;
                $dispatch('open-modal', { name: 'meal-customize-modal' });
            },
            toggleOption(optionName) {
                if (this.selectedOptions.includes(optionName)) {
                    this.selectedOptions = this.selectedOptions.filter((item) => item !== optionName);
                    return;
                }

                this.selectedOptions.push(optionName);
            }
        }"
    >
        <div class="flex items-center gap-3 overflow-x-auto no-scrollbar bg-stone-200/40 rounded-2xl p-1 whitespace-nowrap w-full">
            <button class="p-3 text-center py-2.5 rounded-xl bg-white text-stone-900 text-sm font-medium shadow-sm">الكل</button>
            <button class="p-3 text-center py-2.5 rounded-xl hover:bg-stone-50 text-stone-600 text-sm font-medium hover:text-stone-900">وجبات رئيسية</button>
            <button class="p-3 text-center py-2.5 rounded-xl hover:bg-stone-50 text-stone-600 text-sm font-medium hover:text-stone-900">مشاوي</button>
            <button class="p-3 text-center py-2.5 rounded-xl hover:bg-stone-50 text-stone-600 text-sm font-medium hover:text-stone-900">مقبلات</button>
            <button class="p-3 text-center py-2.5 rounded-xl hover:bg-stone-50 text-stone-600 text-sm font-medium hover:text-stone-900">مشروبات</button>
        </div>

        <div class="grid  grid-cols-2 md:grid-cols-3 gap-5">
            @foreach ($meals as $meal)
                <article wire:key="meal-{{ $meal['id'] }}" class="overflow-hidden rounded-2xl border border-stone-200 bg-white transition hover:shadow-sm">
                    <button type="button" class="block w-full text-start" x-on:click="openMealModal('{{ $meal['id'] }}')">
                        <img src="{{ $meal['image'] }}" alt="{{ $meal['name'] }}" class="h-52 w-full object-cover">
                    </button>

                    <div class="space-y-3 p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <button type="button" class="text-sm font-semibold text-stone-900 transition hover:text-primary-700" x-on:click="openMealModal('{{ $meal['id'] }}')">
                                    {{ $meal['name'] }}
                                </button>
                                <p class="text-xs text-stone-500">{{ $meal['category'] }}</p>
                            </div>
                            <span class="text-base font-bold text-stone-900">{{ $meal['price'] }} ر.س</span>
                        </div>

                        <button
                            type="button"
                            class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-primary-50 px-4 py-2.5 text-sm font-semibold text-primary-700 hover:bg-primary-100"
                            x-on:click="openMealModal('{{ $meal['id'] }}')"
                        >
                            <iconify-icon icon="hugeicons:shopping-basket-02" class="text-xl"></iconify-icon>
                            أضف للسلة
                        </button>
                    </div>
                </article>
            @endforeach
        </div>

        <x-tenant::modal name="meal-customize-modal" maxWidth="lg">
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
                            <span class="text-base font-bold text-primary-600" x-text="`${selectedMeal.price} ر.س`"></span>
                        </div>

                        <div>
                            <p class="mb-2 text-sm font-semibold text-stone-700">اختر الحجم</p>
                            <div class="grid grid-cols-3 gap-2">
                                <button type="button" class="rounded-xl border px-3 py-2 text-sm font-medium transition" :class="selectedSize === 'small' ? 'border-primary-500 bg-primary-50 text-primary-700' : 'border-stone-200 text-stone-600 hover:bg-stone-50'" x-on:click="selectedSize = 'small'">صغير</button>
                                <button type="button" class="rounded-xl border px-3 py-2 text-sm font-medium transition" :class="selectedSize === 'medium' ? 'border-primary-500 bg-primary-50 text-primary-700' : 'border-stone-200 text-stone-600 hover:bg-stone-50'" x-on:click="selectedSize = 'medium'">وسط</button>
                                <button type="button" class="rounded-xl border px-3 py-2 text-sm font-medium transition" :class="selectedSize === 'large' ? 'border-primary-500 bg-primary-50 text-primary-700' : 'border-stone-200 text-stone-600 hover:bg-stone-50'" x-on:click="selectedSize = 'large'">كبير</button>
                            </div>
                        </div>

                        <div>
                            <p class="mb-2 text-sm font-semibold text-stone-700">الإضافات</p>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                <template x-for="option in selectedMeal.options" :key="option">
                                    <button
                                        type="button"
                                        class="inline-flex items-center justify-between rounded-xl border px-3 py-2 text-sm font-medium transition"
                                        :class="selectedOptions.includes(option) ? 'border-primary-500 bg-primary-50 text-primary-700' : 'border-stone-200 text-stone-600 hover:bg-stone-50'"
                                        x-on:click="toggleOption(option)"
                                    >
                                        <span x-text="option"></span>
                                        <iconify-icon icon="solar:add-circle-bold-duotone" class="text-base"></iconify-icon>
                                    </button>
                                </template>
                            </div>
                        </div>

                        <div class="flex items-center justify-between rounded-xl bg-stone-50 px-4 py-3">
                            <span class="text-sm font-semibold text-stone-700">الكمية</span>
                            <div class="inline-flex items-center gap-3">
                                <button type="button" class="h-8 w-8 rounded-lg bg-white text-stone-600 border border-stone-200" x-on:click="quantity = Math.max(1, quantity - 1)">-</button>
                                <span class="min-w-5 text-center text-sm font-bold text-stone-900" x-text="quantity"></span>
                                <button type="button" class="h-8 w-8 rounded-lg bg-white text-stone-600 border border-stone-200" x-on:click="quantity++">+</button>
                            </div>
                        </div>

                        <button
                            type="button"
                            class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-primary-600 px-4 py-3 text-sm font-bold text-white transition hover:bg-primary-700"
                            x-on:click="$dispatch('close-modal', { name: 'meal-customize-modal' })"
                        >
                            <iconify-icon icon="hugeicons:shopping-basket-02" class="text-xl"></iconify-icon>
                            إضافة للسلة
                        </button>
                    </div>
                </template>
            </div>
        </x-tenant::modal>
    </section>
</x-tenant::menu.layout>

<?php

use Livewire\Component;

new class extends Component
{
    /** @var array<int, array<string, mixed>> */
    public array $meals = [];

    public function mount(): void
    {
        $this->meals = [
            [
                'id' => 'm1',
                'name' => 'وجبة دجاج مشوي',
                'category' => 'وجبات رئيسية',
                'price' => 32,
                'image' => 'https://images.unsplash.com/photo-1532550907401-a500c9a57435?q=80&w=1200&auto=format&fit=crop',
                'options' => ['بطاطس مقلية', 'سلطة خضراء', 'صوص الثوم', 'خبز إضافي'],
            ],
            [
                'id' => 'm2',
                'name' => 'برجر لحم أنجوس',
                'category' => 'وجبات رئيسية',
                'price' => 29,
                'image' => 'https://images.unsplash.com/photo-1550547660-d9450f859349?q=80&w=1200&auto=format&fit=crop',
                'options' => ['جبنة إضافية', 'بصل مكرمل', 'صوص باربكيو', 'مخلل'],
            ],
            [
                'id' => 'm3',
                'name' => 'طبق مشاوي مشكل',
                'category' => 'مشاوي',
                'price' => 48,
                'image' => 'https://images.unsplash.com/photo-1529193591184-b1d58069ecdd?q=80&w=1200&auto=format&fit=crop',
                'options' => ['رز بسمتي', 'خبز تنور', 'طحينة', 'حمص'],
            ],
            [
                'id' => 'm4',
                'name' => 'باستا ألفريدو',
                'category' => 'وجبات رئيسية',
                'price' => 34,
                'image' => 'https://images.unsplash.com/photo-1645112411341-6c4fd023714a?q=80&w=1200&auto=format&fit=crop',
                'options' => ['فطر', 'دجاج إضافي', 'جبنة بارميزان', 'صوص حار'],
            ],
            [
                'id' => 'm5',
                'name' => 'سلطة سيزر',
                'category' => 'مقبلات',
                'price' => 21,
                'image' => 'https://images.unsplash.com/photo-1512621776951-a57141f2eefd?q=80&w=1200&auto=format&fit=crop',
                'options' => ['دجاج مشوي', 'خبز محمص', 'صوص إضافي', 'جبنة بارميزان'],
            ],
            [
                'id' => 'm6',
                'name' => 'عصير برتقال طازج',
                'category' => 'مشروبات',
                'price' => 14,
                'image' => 'https://images.unsplash.com/photo-1600271886742-f049cd451bba?q=80&w=1200&auto=format&fit=crop',
                'options' => ['بدون سكر', 'ثلج إضافي', 'نعناع', 'كوب كبير'],
            ],
        ];
    }
};
?>
