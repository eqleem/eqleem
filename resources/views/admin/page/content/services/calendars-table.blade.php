<div class="divide-y divide-gray-200 divide-dotted">
    <div class="px-4 py-4 border-b border-stone-100">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h3 class="text-base font-semibold text-gray-800">الأصول القابلة للحجز</h3>
                <p class="mt-1 text-sm text-gray-500">
                    مقدمي الخدمات بالساعة،   يمكنك إدارة ساعات العمل المتاحة للحجز من هنا.
                </p>
            </div>
            <ui:button wire:click="openAddModal" label="أضف جديد" icon="square-rounded-plus" />
        </div>
    </div>

    <div class="bg-gray-100 p-3 flex items-center gap-x-4 w-full">
        <div class="flex-grow">
            <div class="relative text-sm text-gray-800">
                <div class="absolute ps-2 right-0 top-0 bottom-0 flex items-center pointer-events-none text-gray-500">
                    <ui:icon name="search" class="text-gray-400" />
                </div>

                <input wire:model.live="search" type="text" placeholder="ابحث .."
                    class="block w-full rounded-lg py-1.5 ps-10 text-gray-800 ring-0 ring-inset border-transparent border ring-gray-200 placeholder:text-gray-400 focus:border focus:outline-none focus:border-primary-500 sm:text-sm sm:leading-6">
            </div>
        </div>

        <ui:modal :title="$editingCalendarId ? 'تعديل الأصل' : 'أضف أصل جديد'" size="3xl" name="service-calendar-form">
            <livewire:admin::page.content.services.calendar-form
                :calendar-id="$editingCalendarId"
                :key="'service-calendar-form-'.($editingCalendarId ?? 'new')"
            />
        </ui:modal>
    </div>

    <div class="relative overflow-x-auto">
        @if ($calendars->isEmpty())
            <ui:empty subtitle="سيتم عرض مقدمي الخدمات هنا بعد إضافتهم.">
                لا توجد أصول قابلة للحجز.
                <x-slot:icon>
                    <ui:icon name="calendar" class="w-12 h-12 opacity-50" />
                </x-slot:icon>
            </ui:empty>
        @else
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 text-gray-500">
                    <tr>
                        <th class="px-6 py-3 text-start font-medium">الاسم</th>
                        <th class="px-4 py-3 text-start font-medium">النوع</th>
                        <th class="px-4 py-3 text-start font-medium">الفرع</th>
                        <th class="px-4 py-3 text-start font-medium">تاريخ البداية</th>
                        <th class="px-4 py-3 text-start font-medium">تاريخ النهاية</th>
                        <th class="px-4 py-3 text-end font-medium"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach ($calendars as $calendar)
                        <tr wire:key="service-calendar-{{ $calendar->id }}" class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2 min-w-0">
                                    @if ($calendar->active)
                                        <span class="h-2 w-2 shrink-0 rounded-full bg-emerald-500"></span>
                                    @endif
                                    <button
                                        type="button"
                                        wire:click="openEditModal({{ $calendar->id }})"
                                        class="truncate font-medium text-gray-800 hover:text-primary-600 text-start"
                                    >
                                        {{ $calendar->name }}
                                    </button>
                                </div>
                            </td>
                            <td class="px-4 py-4 text-gray-600">{{ $calendar->type_label }}</td>
                            <td class="px-4 py-4 text-gray-400">—</td>
                            <td class="px-4 py-4 text-gray-600">
                                {{ $calendar->from?->translatedFormat('j F Y') ?? '—' }}
                            </td>
                            <td class="px-4 py-4 text-gray-600">
                                {{ $calendar->to?->translatedFormat('j F Y') ?? '—' }}
                            </td>
                            <td class="px-4 py-4 text-end">
                                <div x-data="{ dropdownMenu: false }" class="inline-block">
                                    <div class="relative" @click.outside="dropdownMenu=false" x-cloak>
                                        <button @click="dropdownMenu = ! dropdownMenu" type="button"
                                            class="hover:bg-gray-200 p-1 rounded-lg inline-block">
                                            <ui:icon name="dots" class="text-gray-400" />
                                        </button>

                                        <div x-show="dropdownMenu"
                                            class="absolute z-50 mt-2 bg-white border shadow-sm rounded-lg text-gray-800 text-sm flex p-1 ltr:right-0 rtl:left-0 w-40 flex-col gap-y-px"
                                            x-transition.scale.origin.top>
                                            <button type="button"
                                                wire:click="openEditModal({{ $calendar->id }})"
                                                @click="dropdownMenu = false"
                                                class="hover:bg-stone-100 p-1.5 rounded flex items-center gap-x-2 w-full text-start">
                                                {{ __('Edit') }}
                                            </button>
                                            <button type="button"
                                                wire:click="delete({{ $calendar->id }})"
                                                wire:confirm="هل أنت متأكد من حذف هذا الأصل؟"
                                                @click="dropdownMenu = false"
                                                class="hover:bg-stone-100 p-1.5 rounded flex items-center gap-x-2 w-full text-start text-red-600">
                                                {{ __('Delete') }}
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        <div wire:loading wire:target="search, delete, openEditModal, openAddModal"
            class="absolute inset-0 bg-white opacity-50"></div>

        <div wire:loading.flex wire:target="search, delete, openEditModal, openAddModal"
            class="flex justify-center items-center absolute inset-0">
            <ui:icon name="loader-3" class="animate-spin text-gray-300 w-10 h-10" />
        </div>
    </div>
</div>

<?php

use App\Models\Calendar;
use Livewire\Attributes\On;

new class extends \Livewire\Component
{
    private const CALENDAR_TYPE = 'service-provider';

    public string $search = '';

    public ?int $editingCalendarId = null;

    public function placeholder(): string
    {
        return loadingIcon();
    }

    #[On('updateServiceCalendarList')]
    public function refreshList(): void
    {
        $this->editingCalendarId = null;
    }

    public function openAddModal(): void
    {
        $this->editingCalendarId = null;
        $this->dispatch('openmodal', modal: 'service-calendar-form');
    }

    public function openEditModal(int $calendarId): void
    {
        if (! Calendar::query()->where('type', self::CALENDAR_TYPE)->whereKey($calendarId)->exists()) {
            return;
        }

        $this->editingCalendarId = $calendarId;
        $this->dispatch('openmodal', modal: 'service-calendar-form');
    }

    public function delete(int $id): void
    {
        Calendar::query()->where('type', self::CALENDAR_TYPE)->whereKey($id)->first()?->delete();

        $this->dispatch('notify', text: __('Item(s) deleted successfully.'));
    }

    public function with(): array
    {
        $query = Calendar::query()
            ->where('type', self::CALENDAR_TYPE)
            ->orderByDesc('id');

        if ($this->search !== '') {
            $term = '%'.$this->search.'%';
            $query->where('name', 'like', $term);
        }

        return [
            'calendars' => $query->get(),
        ];
    }
}; ?>
