<div>
    @if ($submitted)
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-6 text-center">
            <p class="text-base font-semibold text-emerald-800">تم تأكيد حجزك</p>
            <p class="mt-1 text-sm text-emerald-700">سنتواصل معك قريباً لتأكيد التفاصيل.</p>
            @if (filled($confirmationLabel))
                <p class="mt-3 text-sm font-medium text-emerald-900">{{ $confirmationLabel }}</p>
            @endif
        </div>
    @else
        <form wire:submit="submit" class="space-y-4">
            <div>
                <label for="cta-booking-name-{{ $linkId }}" class="mb-1 block text-sm font-medium text-stone-700">الاسم *</label>
                <input
                    id="cta-booking-name-{{ $linkId }}"
                    type="text"
                    wire:model="guestName"
                    class="w-full rounded-xl border border-stone-200 bg-white px-3 py-2.5 text-sm text-stone-800 focus:border-primary-400 focus:outline-none"
                    placeholder="اسمك الكامل"
                    autocomplete="name"
                >
                @error('guestName')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid gap-3 sm:grid-cols-2">
                <div>
                    <label for="cta-booking-phone-{{ $linkId }}" class="mb-1 block text-sm font-medium text-stone-700">رقم الجوال</label>
                    <input
                        id="cta-booking-phone-{{ $linkId }}"
                        type="tel"
                        wire:model="guestPhone"
                        class="w-full rounded-xl border border-stone-200 bg-white px-3 py-2.5 text-sm text-stone-800 focus:border-primary-400 focus:outline-none"
                        placeholder="05xxxxxxxx"
                        autocomplete="tel"
                        dir="ltr"
                    >
                    @error('guestPhone')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="cta-booking-email-{{ $linkId }}" class="mb-1 block text-sm font-medium text-stone-700">البريد الإلكتروني</label>
                    <input
                        id="cta-booking-email-{{ $linkId }}"
                        type="email"
                        wire:model="guestEmail"
                        class="w-full rounded-xl border border-stone-200 bg-white px-3 py-2.5 text-sm text-stone-800 focus:border-primary-400 focus:outline-none"
                        placeholder="name@example.com"
                        autocomplete="email"
                        dir="ltr"
                    >
                    @error('guestEmail')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
            <p class="text-xs text-stone-400">أدخل رقم الجوال أو البريد الإلكتروني (أحدهما على الأقل).</p>

            @if ($allowClientChoice && count($calendars) > 1)
                <div>
                    <label for="cta-booking-calendar-{{ $linkId }}" class="mb-1 block text-sm font-medium text-stone-700">الفرع / التقويم *</label>
                    <select
                        id="cta-booking-calendar-{{ $linkId }}"
                        wire:model.live="calendarId"
                        class="w-full rounded-xl border border-stone-200 bg-white px-3 py-2.5 text-sm text-stone-800 focus:border-primary-400 focus:outline-none"
                    >
                        <option value="">اختر...</option>
                        @foreach ($calendars as $calendar)
                            <option value="{{ $calendar['id'] }}">
                                {{ $calendar['name'] }}@if(filled($calendar['branch_name'])) — {{ $calendar['branch_name'] }}@endif
                            </option>
                        @endforeach
                    </select>
                    @error('calendarId')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            @endif

            <div>
                <p class="mb-2 text-sm font-medium text-stone-700">اختر التاريخ *</p>
                <div class="grid grid-cols-2 gap-2 sm:grid-cols-4">
                    @foreach ($dateOptions as $option)
                        <button
                            type="button"
                            wire:key="date-option-{{ $option['key'] }}"
                            wire:click="selectDatePreset('{{ $option['key'] }}')"
                            @disabled(! $option['available'])
                            @class([
                                'rounded-xl border px-2 py-2.5 text-center text-sm font-medium transition',
                                'border-primary-500 bg-primary-50 text-primary-900' => $datePreset === $option['key'] && $option['available'],
                                'border-stone-200 bg-white text-stone-700 hover:border-stone-300' => $datePreset !== $option['key'] && $option['available'],
                                'cursor-not-allowed border-stone-100 bg-stone-50 text-stone-300' => ! $option['available'],
                            ])
                        >
                            <span class="block">{{ $option['label'] }}</span>
                            @if (filled($option['subtitle']))
                                <span class="mt-0.5 block text-[11px] font-normal opacity-70">{{ $option['subtitle'] }}</span>
                            @endif
                        </button>
                    @endforeach
                </div>

                @if ($datePreset === 'custom')
                    <div class="mt-3">
                        <label for="cta-booking-custom-date-{{ $linkId }}" class="mb-1 block text-sm font-medium text-stone-700">تاريخ محدد</label>
                        <input
                            id="cta-booking-custom-date-{{ $linkId }}"
                            type="date"
                            wire:model.live="customDate"
                            min="{{ $minCustomDate }}"
                            max="{{ $maxCustomDate }}"
                            class="w-full rounded-xl border border-stone-200 bg-white px-3 py-2.5 text-sm text-stone-800 focus:border-primary-400 focus:outline-none"
                            dir="ltr"
                        >
                    </div>
                @endif

                @error('selectedDate')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            @if (filled($selectedDate))
                <div>
                    <p class="mb-2 text-sm font-medium text-stone-700">الأوقات المتاحة</p>

                    @if ($slotsLoading)
                        <p class="text-sm text-stone-400">جاري تحميل الأوقات...</p>
                    @elseif (count($timeSlots) === 0)
                        <p class="rounded-xl border border-dashed border-stone-200 px-3 py-4 text-center text-sm text-stone-400">
                            لا توجد أوقات في هذا اليوم.
                        </p>
                    @else
                        <div class="grid max-h-56 grid-cols-2 gap-2 overflow-y-auto sm:grid-cols-3">
                            @foreach ($timeSlots as $slot)
                                @php
                                    $isAvailable = (bool) ($slot['available'] ?? false);
                                    $isSelected = $isAvailable
                                        && $selectedStartAt === $slot['start_at']
                                        && $calendarId === $slot['calendar_id'];
                                @endphp
                                <button
                                    type="button"
                                    wire:key="slot-{{ $slot['calendar_id'] }}-{{ $slot['start_at'] }}"
                                    @disabled(! $isAvailable)
                                    @if ($isAvailable)
                                        wire:click="selectSlot('{{ $slot['start_at'] }}', '{{ $slot['end_at'] }}', {{ $slot['calendar_id'] }})"
                                    @endif
                                    @class([
                                        'rounded-xl border px-2 py-2.5 text-center text-sm transition',
                                        'border-primary-500 bg-primary-50 text-primary-900' => $isSelected,
                                        'border-stone-200 bg-white text-stone-700 hover:border-stone-300' => $isAvailable && ! $isSelected,
                                        'cursor-not-allowed border-stone-100 bg-stone-50 text-stone-300 line-through' => ! $isAvailable,
                                    ])
                                >
                                    <span class="block font-semibold" dir="ltr">{{ $slot['label'] }}</span>
                                    @if (! $isAvailable)
                                        <span class="mt-0.5 block text-[11px] font-normal no-underline">
                                            {{ ($slot['unavailable_reason'] ?? '') === 'past' ? 'منتهي' : 'محجوز' }}
                                        </span>
                                    @elseif ($allowClientChoice && count($calendars) > 1)
                                        <span class="mt-0.5 block truncate text-[11px] opacity-70">{{ $slot['calendar_name'] }}</span>
                                    @endif
                                </button>
                            @endforeach
                        </div>
                    @endif

                    @error('selectedStartAt')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            @endif

            <button
                type="submit"
                wire:loading.attr="disabled"
                class="flex w-full items-center justify-center rounded-xl bg-primary-600 px-4 py-3 text-sm font-semibold text-white transition hover:bg-primary-700 disabled:opacity-60"
            >
                <span wire:loading.remove wire:target="submit">تأكيد الحجز</span>
                <span wire:loading wire:target="submit">جاري الحفظ...</span>
            </button>
        </form>
    @endif
</div>

<?php

use App\Models\Booking;
use App\Models\Calendar;
use App\Support\CtaBooking;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Livewire\Component;

new class extends Component
{
    public int $linkId;

    public ?int $blockId = null;

    /** @var list<int> */
    public array $branchIds = [];

    /** @var list<int> */
    public array $calendarIds = [];

    public bool $allowClientChoice = true;

    public int $durationMinutes = 30;

    public string $guestName = '';

    public string $guestPhone = '';

    public string $guestEmail = '';

    public ?int $calendarId = null;

    public string $datePreset = '';

    public string $selectedDate = '';

    public string $customDate = '';

    public ?string $selectedStartAt = null;

    public ?string $selectedEndAt = null;

    /** @var list<array{id: int, name: string, branch_id: int|null, branch_name: string|null}> */
    public array $calendars = [];

    /** @var list<string> */
    public array $availableDates = [];

    /** @var list<array{key: string, label: string, subtitle: string, date: string, available: bool}> */
    public array $dateOptions = [];

    /** @var list<array<string, mixed>> */
    public array $timeSlots = [];

    public bool $slotsLoading = false;

    public bool $submitted = false;

    public string $confirmationLabel = '';

    public string $minCustomDate = '';

    public string $maxCustomDate = '';

    /**
     * @param  list<int>  $branchIds
     * @param  list<int>  $calendarIds
     */
    public function mount(
        int $linkId,
        ?int $blockId = null,
        array $branchIds = [],
        array $calendarIds = [],
        bool $allowClientChoice = true,
        int $durationMinutes = 30,
    ): void {
        $this->linkId = $linkId;
        $this->blockId = $blockId;
        $this->branchIds = CtaBooking::normalizeIds($branchIds);
        $this->calendarIds = CtaBooking::normalizeIds($calendarIds);
        $this->allowClientChoice = $allowClientChoice;
        $this->durationMinutes = max(1, $durationMinutes);
        $this->minCustomDate = now()->toDateString();
        $this->maxCustomDate = now()->addDays(60)->toDateString();

        $this->loadCalendars();
        $this->refreshAvailableDates();
        $this->buildDateOptions();
    }

    public function updatedCalendarId(): void
    {
        $this->resetSlotSelection();
        $this->selectedDate = '';
        $this->datePreset = '';
        $this->customDate = '';
        $this->timeSlots = [];
        $this->refreshAvailableDates();
        $this->buildDateOptions();
    }

    public function updatedCustomDate(): void
    {
        if ($this->datePreset !== 'custom') {
            return;
        }

        $this->applySelectedDate($this->customDate);
    }

    public function selectDatePreset(string $preset): void
    {
        $option = collect($this->dateOptions)->firstWhere('key', $preset);

        if (! is_array($option) || ! ($option['available'] ?? false)) {
            return;
        }

        $this->datePreset = $preset;
        $this->resetSlotSelection();

        if ($preset === 'custom') {
            $this->selectedDate = '';
            $this->timeSlots = [];
            $this->customDate = $this->customDate !== '' ? $this->customDate : '';

            return;
        }

        $this->customDate = '';
        $this->applySelectedDate((string) ($option['date'] ?? ''));
    }

    public function selectSlot(string $startAt, string $endAt, int $calendarId): void
    {
        $slot = collect($this->timeSlots)->first(
            fn (array $candidate): bool => ($candidate['start_at'] ?? '') === $startAt
                && ($candidate['end_at'] ?? '') === $endAt
                && (int) ($candidate['calendar_id'] ?? 0) === $calendarId
                && ($candidate['available'] ?? false)
        );

        if (! is_array($slot)) {
            return;
        }

        $this->calendarId = $calendarId;
        $this->selectedStartAt = $startAt;
        $this->selectedEndAt = $endAt;
    }

    public function submit(): void
    {
        $rules = [
            'guestName' => ['required', 'string', 'max:120'],
            'guestPhone' => ['required_without:guestEmail', 'nullable', 'string', 'max:30'],
            'guestEmail' => ['required_without:guestPhone', 'nullable', 'email', 'max:255'],
            'selectedDate' => ['required', 'date_format:Y-m-d'],
            'selectedStartAt' => ['required', 'string'],
            'selectedEndAt' => ['required', 'string'],
            'calendarId' => ['required', 'integer'],
        ];

        if ($this->allowClientChoice && count($this->calendars) > 1) {
            $rules['calendarId'][] = 'in:'.collect($this->calendars)->pluck('id')->implode(',');
        }

        $this->validate($rules, [
            'guestName.required' => 'يرجى إدخال الاسم.',
            'guestPhone.required_without' => 'أدخل رقم الجوال أو البريد الإلكتروني.',
            'guestEmail.required_without' => 'أدخل رقم الجوال أو البريد الإلكتروني.',
            'guestEmail.email' => 'البريد الإلكتروني غير صالح.',
            'selectedDate.required' => 'يرجى اختيار التاريخ.',
            'selectedStartAt.required' => 'يرجى اختيار الوقت.',
            'calendarId.required' => 'يرجى اختيار التقويم.',
        ]);

        $allowed = $this->scopedCalendars();
        $calendar = $allowed->firstWhere('id', $this->calendarId);

        if (! $calendar instanceof Calendar) {
            $this->addError('calendarId', 'التقويم المحدد غير متاح.');
            $this->refreshTimeSlots();

            return;
        }

        $slot = collect(CtaBooking::slotsForDate(
            collect([$calendar]),
            $this->selectedDate,
            $this->durationMinutes,
        ))->first(
            fn (array $candidate): bool => ($candidate['start_at'] ?? '') === $this->selectedStartAt
                && ($candidate['end_at'] ?? '') === $this->selectedEndAt
                && ($candidate['available'] ?? false)
        );

        if (! is_array($slot)) {
            $this->addError('selectedStartAt', 'الوقت المحدد لم يعد متاحاً.');
            $this->refreshTimeSlots();

            return;
        }

        $guestPhone = trim($this->guestPhone);
        $guestEmail = trim($this->guestEmail);

        $booking = Booking::query()->create([
            'tenant_id' => currentTenantId(),
            'client_id' => null,
            'content_id' => null,
            'calendar_id' => $calendar->id,
            'start_at' => $this->selectedStartAt,
            'end_at' => $this->selectedEndAt,
            'status' => 'new',
            'data' => [
                'guest_name' => trim($this->guestName),
                'guest_phone' => $guestPhone !== '' ? $guestPhone : null,
                'guest_email' => $guestEmail !== '' ? $guestEmail : null,
                'source' => 'cta_booking',
                'cta_link_id' => $this->linkId,
                'duration_minutes' => $this->durationMinutes,
            ],
            'meta' => [
                'created_from' => 'tenant_cta_booking',
                'block_id' => $this->blockId,
            ],
        ]);

        $this->submitted = true;
        $this->confirmationLabel = trim($this->guestName).' · '.$slot['date_label'].' · '.$slot['label'];
        $this->dispatch('cta-booking-submitted', bookingId: $booking->id);
    }

    protected function loadCalendars(): void
    {
        $this->calendars = $this->allowedCalendars()
            ->map(fn (Calendar $calendar): array => [
                'id' => (int) $calendar->id,
                'name' => $calendar->name,
                'branch_id' => $calendar->branch_id ? (int) $calendar->branch_id : null,
                'branch_name' => $calendar->branch?->display_name,
            ])
            ->values()
            ->all();

        if (count($this->calendars) === 1) {
            $this->calendarId = $this->calendars[0]['id'];
        }
    }

    protected function refreshAvailableDates(): void
    {
        $this->availableDates = CtaBooking::availableDates($this->scopedCalendars());
    }

    protected function buildDateOptions(): void
    {
        $today = now()->startOfDay();
        $available = collect($this->availableDates);

        $presets = [
            [
                'key' => 'today',
                'label' => 'اليوم',
                'date' => $today->toDateString(),
            ],
            [
                'key' => 'tomorrow',
                'label' => 'غداً',
                'date' => $today->copy()->addDay()->toDateString(),
            ],
            [
                'key' => 'day_after',
                'label' => 'بعد غد',
                'date' => $today->copy()->addDays(2)->toDateString(),
            ],
        ];

        $this->dateOptions = collect($presets)
            ->map(function (array $preset) use ($available): array {
                $date = Carbon::parse($preset['date']);

                return [
                    'key' => $preset['key'],
                    'label' => $preset['label'],
                    'subtitle' => $date->translatedFormat('j M'),
                    'date' => $preset['date'],
                    'available' => $available->contains($preset['date']),
                ];
            })
            ->push([
                'key' => 'custom',
                'label' => 'تاريخ محدد',
                'subtitle' => 'اختر يوماً',
                'date' => '',
                'available' => $available->isNotEmpty(),
            ])
            ->all();
    }

    protected function applySelectedDate(string $date): void
    {
        $date = trim($date);

        if ($date === '' || ! in_array($date, $this->availableDates, true)) {
            $this->selectedDate = '';
            $this->timeSlots = [];
            $this->resetSlotSelection();

            if ($date !== '') {
                $this->addError('selectedDate', 'هذا التاريخ غير متاح للحجز.');
            }

            return;
        }

        $this->resetErrorBag('selectedDate');
        $this->selectedDate = $date;
        $this->refreshTimeSlots();
    }

    protected function refreshTimeSlots(): void
    {
        if ($this->selectedDate === '') {
            $this->timeSlots = [];

            return;
        }

        $this->slotsLoading = true;
        $this->timeSlots = CtaBooking::slotsForDate(
            $this->scopedCalendars(),
            $this->selectedDate,
            $this->durationMinutes,
        );
        $this->slotsLoading = false;

        if (
            ! $this->allowClientChoice
            && $this->calendarId === null
        ) {
            $firstAvailable = collect($this->timeSlots)->first(
                fn (array $slot): bool => (bool) ($slot['available'] ?? false)
            );

            if (is_array($firstAvailable)) {
                $this->calendarId = (int) $firstAvailable['calendar_id'];
            }
        }
    }

    protected function resetSlotSelection(): void
    {
        $this->selectedStartAt = null;
        $this->selectedEndAt = null;
    }

    /**
     * @return Collection<int, Calendar>
     */
    protected function scopedCalendars(): Collection
    {
        $calendars = $this->allowedCalendars();

        if ($this->allowClientChoice && $this->calendarId) {
            return $calendars->where('id', $this->calendarId)->values();
        }

        return $calendars;
    }

    /**
     * @return Collection<int, Calendar>
     */
    protected function allowedCalendars(): Collection
    {
        return CtaBooking::resolveCalendars($this->branchIds, $this->calendarIds);
    }
}; ?>
