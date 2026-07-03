<ui:form wire:submit="submit">
    <div class="mb-4">
        <ui:alert
            type="info"
            title="الأصل هو محرك الحجز"
            text="الأصل هو ما يُربط بالخدمات لتحديد أوقات الحجز المتاحة — مثل مقدم الخدمة أو المكان أو الأداة."
        />
    </div>

    <ui:input name="name" label="الاسم" placeholder="مثال: سمية" />

    <ui:radio name="type" label="النوع" :options="$typeOptions" />

    <p class="text-xs text-gray-500 -mt-1 mb-2">
        مقدم الخدمة مثل خبيرة تجميل، المكان مثل استوديو، والأداة مثل كاميرا أو سيارة.
    </p>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
        <ui:input name="fromDate" label="من تاريخ" type="date" dir="ltr" />
        <ui:input name="toDate" label="إلى تاريخ" type="date" dir="ltr" />
    </div>

    <p class="text-xs text-gray-500 mb-3">اختر الفترة الزمنية المتاحة لحجز هذا الأصل.</p>

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

    <x-slot:footer>
        <ui:button target="submit" label="{{ __('Save') }}" />
    </x-slot>
</ui:form>

<?php

use App\Models\Calendar;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Locked;

new class extends \Livewire\Component
{
    #[Locked]
    public ?int $calendarId = null;

    public string $name = '';

    public string $type = 'provider';

    public string $fromDate = '';

    public string $toDate = '';

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
        $this->type = $calendar->type;
        $this->fromDate = $calendar->from?->format('Y-m-d') ?? '';
        $this->toDate = $calendar->to?->format('Y-m-d') ?? '';
        $this->availabilities = Calendar::normalizeAvailabilities($calendar->availabilities);
    }

    /**
     * @return array<string, string>
     */
    public function typeOptions(): array
    {
        return Calendar::typeOptions();
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
            'type' => ['required', Rule::in(array_keys(Calendar::typeOptions()))],
            'fromDate' => 'nullable|date',
            'toDate' => 'nullable|date|after_or_equal:fromDate',
            'availabilities' => 'array',
        ];
    }

    public function submit(): void
    {
        $this->validate();

        $attributes = [
            'name' => $this->name,
            'type' => $this->type,
            'from' => filled($this->fromDate) ? $this->fromDate : null,
            'to' => filled($this->toDate) ? $this->toDate : null,
            'availabilities' => Calendar::normalizeAvailabilities($this->availabilities),
            'active' => true,
        ];

        if ($this->calendarId) {
            Calendar::query()->findOrFail($this->calendarId)->update($attributes);
        } else {
            Calendar::query()->create($attributes);
            $this->reset(['name', 'type', 'fromDate', 'toDate']);
            $this->type = 'provider';
            $this->availabilities = Calendar::defaultAvailabilities();
        }

        $this->dispatch('updateServiceCalendarList');
        $this->dispatch('closemodal', modal: 'service-calendar-form');
        $this->dispatch('notify', text: __('Saved'));
    }

    public function render()
    {
        return $this->view([
            'typeOptions' => $this->typeOptions(),
            'weekdayLabels' => $this->weekdayLabels(),
        ]);
    }
}; ?>
