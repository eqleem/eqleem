<ui:form wire:submit="submit">
    <div class="mb-4">
        <ui:alert
            type="info"
            title="وحدة المخزون"
            text="كل وحدة في المخزون تمثل وحدة فعلية قابلة للحجز — مثل غرفة ١٠١ أو قاعة اجتماعات ٢. يمكن ربطها بأنواع الوحدات لتحديد أوقات الحجز."
        />
    </div>

    <ui:input name="name" label="اسم الوحدة" placeholder="مثال: غرفة ١٠١" />

    <ui:toggle
        name="useBranchHours"
        label="ساعات عمل الفرع"
        info="عند التفعيل، ستُستخدم ساعات عمل الفرع بدلاً من أوقات مخصصة لهذه الوحدة."
        live
    />

    @unless ($useBranchHours)
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
            <ui:input name="fromDate" label="من تاريخ" type="date" dir="ltr" />
            <ui:input name="toDate" label="إلى تاريخ" type="date" dir="ltr" />
        </div>

        <p class="text-xs text-gray-500 mb-3">اختر الفترة الزمنية المتاحة لحجز هذه الوحدة.</p>

        <div class="rounded-xl border border-gray-200 overflow-hidden">
            <div class="bg-gray-50 px-4 py-2 text-sm font-medium text-gray-700">الأوقات المتاحة</div>

            <div class="divide-y divide-gray-100">
                @foreach ($weekdayLabels as $day => $label)
                    <div wire:key="calendar-day-{{ $day }}" class="flex flex-wrap items-center gap-3 px-4 py-3">
                        <label class="flex items-center gap-2 w-28 shrink-0">
                            <input
                                type="checkbox"
                                wire:model.live="availabilities.{{ $day }}.enabled"
                                class="rounded border-gray-300"
                            >
                            <span class="text-sm text-gray-700">{{ $label }}</span>
                        </label>

                        <div class="flex items-center gap-2 flex-1 min-w-[220px]">
                            <input
                                type="time"
                                wire:model="availabilities.{{ $day }}.start"
                                @disabled(! ($availabilities[$day]['enabled'] ?? false))
                                class="rounded-lg border border-gray-200 px-2 py-1.5 text-sm text-gray-700 disabled:bg-gray-100 disabled:text-gray-400"
                            >
                            <span class="text-gray-400 text-sm">إلى</span>
                            <input
                                type="time"
                                wire:model="availabilities.{{ $day }}.end"
                                @disabled(! ($availabilities[$day]['enabled'] ?? false))
                                class="rounded-lg border border-gray-200 px-2 py-1.5 text-sm text-gray-700 disabled:bg-gray-100 disabled:text-gray-400"
                            >
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endunless

    <x-slot:footer>
        <ui:button target="submit" label="{{ __('Save') }}" />
    </x-slot>
</ui:form>

<?php

use App\Models\Calendar;
use Livewire\Attributes\Locked;

new class extends \Livewire\Component
{
    private const CALENDAR_TYPE = 'rental-unit';

    #[Locked]
    public ?int $calendarId = null;

    public string $name = '';

    public string $fromDate = '';

    public string $toDate = '';

    public bool $useBranchHours = false;

    /** @var array<string, array{enabled: bool, start: string, end: string}> */
    public array $availabilities = [];

    public function mount(?int $calendarId = null): void
    {
        $this->calendarId = $calendarId;
        $this->availabilities = Calendar::defaultAvailabilities();

        if ($calendarId) {
            $this->loadCalendar();
        }
    }

    public function loadCalendar(): void
    {
        $calendar = Calendar::query()->findOrFail($this->calendarId);

        $this->name = $calendar->name;
        $this->fromDate = $calendar->from?->format('Y-m-d') ?? '';
        $this->toDate = $calendar->to?->format('Y-m-d') ?? '';
        $this->useBranchHours = (bool) data_get($calendar->meta, 'use_branch_hours', false);
        $this->availabilities = Calendar::normalizeAvailabilities($calendar->availabilities);
    }

    /**
     * @return array<string, string>
     */
    public function weekdayLabels(): array
    {
        return Calendar::weekdayLabels();
    }

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        return [
            'name' => 'required|string|min:1|max:255',
            'fromDate' => 'nullable|date',
            'toDate' => 'nullable|date|after_or_equal:fromDate',
            'useBranchHours' => 'boolean',
            'availabilities' => 'array',
        ];
    }

    public function submit(): void
    {
        $this->validate();

        $meta = $this->calendarId
            ? (Calendar::query()->find($this->calendarId)?->meta ?? [])
            : [];

        $meta['use_branch_hours'] = $this->useBranchHours;

        $attributes = [
            'name' => $this->name,
            'from' => $this->useBranchHours ? null : (filled($this->fromDate) ? $this->fromDate : null),
            'to' => $this->useBranchHours ? null : (filled($this->toDate) ? $this->toDate : null),
            'availabilities' => $this->useBranchHours
                ? null
                : Calendar::normalizeAvailabilities($this->availabilities),
            'meta' => $meta,
            'active' => true,
        ];

        if ($this->calendarId) {
            Calendar::query()->findOrFail($this->calendarId)->update($attributes);
        } else {
            Calendar::query()->create(array_merge($attributes, [
                'type' => self::CALENDAR_TYPE,
            ]));
            $this->reset(['name', 'fromDate', 'toDate', 'useBranchHours']);
            $this->availabilities = Calendar::defaultAvailabilities();
        }

        $this->dispatch('updateUnitRentalCalendarList');
        $this->dispatch('closemodal', modal: 'unit-rental-calendar-form');
        $this->dispatch('notify', text: __('Saved'));
    }

    public function render()
    {
        return $this->view([
            'weekdayLabels' => $this->weekdayLabels(),
        ]);
    }
}; ?>
