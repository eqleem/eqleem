<div>
    <ui:form class="!p-0 !gap-0 max-h-[75vh] overflow-y-auto" novalidate>
        <div class="space-y-4 p-5">
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-semibold text-gray-500">العميل</p>
                    
                    <ui:button type="button" wire:click="openCreateClientModal" icon="plus" variant="outline"
                        label="عميل جديد" />
                    
                </div>
                <div class="space-y-3">
                    @if ($client_id)
                        <div class="flex items-center justify-between bg-gray-50 rounded-lg p-3">
                            <div>
                                <p class="text-sm font-bold text-gray-800">{{ $selectedClientName }}</p>
                                @if ($selectedClientEmail)
                                    <p class="text-xs text-gray-500 mt-1">{{ $selectedClientEmail }}</p>
                                @endif
                                @if ($selectedClientPhone)
                                    <p class="text-xs text-gray-500 mt-0.5" dir="ltr">{{ $selectedClientPhone }}</p>
                                @endif
                            </div>
                            <button type="button" wire:click="enterClientSearch"
                                class="text-xs text-red-500 hover:text-red-700 px-2 py-1 rounded hover:bg-red-50">
                                تغيير
                            </button>
                        </div>
                    @elseif ($isWalkingClient)
                        <div class="flex items-center justify-between bg-gray-50 rounded-lg p-3">
                            <div class="flex items-center gap-3">
                                <div
                                    class="flex h-9 w-9 items-center justify-center rounded-lg bg-white text-gray-400 ring-1 ring-gray-100">
                                    <ui:icon name="user" class="h-4 w-4" />
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-gray-800">{{ \App\Models\Order::walkingClientLabel() }}</p>
                                    <p class="text-xs text-gray-500 mt-0.5">الافتراضي — بدون حساب عميل</p>
                                </div>
                            </div>
                            <button type="button" wire:click="enterClientSearch"
                                class="text-xs text-red-500 hover:text-red-700 px-2 py-1 rounded hover:bg-red-50">
                                تغيير
                            </button>
                        </div>
                    @else
                        <div class="relative" x-data="{ open: @entangle('showClientResults') }">
                            <div class="flex gap-2">
                                <div class="relative flex-1">
                                    <div
                                        class="absolute ps-2 right-0 top-0 bottom-0 flex items-center pointer-events-none text-gray-500">
                                        <ui:icon name="search" class="text-gray-400" />
                                    </div>
                                    <input wire:model.live.debounce.300ms="clientSearch" wire:focus="showClientSearchResults"
                                        type="text"
                                        placeholder="ابحث بالاسم أو البريد أو الهاتف .."
                                        class="block w-full rounded-lg py-2 ps-10 text-gray-800 border border-gray-200 placeholder:text-gray-400 focus:border-primary-500 focus:outline-none sm:text-sm">
                                </div>
                                {{-- <ui:button type="button" wire:click="openCreateClientModal" icon="plus"
                                    variant="outline" label="جديد" class="shrink-0" /> --}}
                            </div>
                            @error('client_id')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                            @if ($showClientResults)
                                <div
                                    class="absolute z-50 mt-1 w-full bg-white border border-gray-200 rounded-lg shadow-lg max-h-52 overflow-y-auto">
                                    <button type="button" wire:click="selectWalkingClient"
                                        class="w-full text-start px-3 py-2.5 hover:bg-gray-50 border-b border-gray-100 bg-gray-50/50">
                                        <p class="text-sm font-semibold text-gray-800">{{ \App\Models\Order::walkingClientLabel() }}</p>
                                        <p class="text-xs text-gray-500 mt-0.5">بدون حساب عميل</p>
                                    </button>
                                    @foreach ($clientResults as $client)
                                        <button type="button" wire:click="selectClient({{ $client['id'] }})"
                                            wire:key="client-{{ $client['id'] }}"
                                            class="w-full text-start px-3 py-2 hover:bg-gray-50 border-b border-gray-50 last:border-0">
                                            <p class="text-sm font-semibold text-gray-800">{{ $client['name'] }}</p>
                                            <p class="text-xs text-gray-500 mt-0.5">
                                                @if ($client['email'])
                                                    <span>{{ $client['email'] }}</span>
                                                @endif
                                                @if ($client['phone'])
                                                    <span class="ms-2" dir="ltr">{{ $client['phone'] }}</span>
                                                @endif
                                            </p>
                                        </button>
                                    @endforeach
                                    @if ($clientSearch !== '' && count($clientResults) === 0)
                                        <button type="button" wire:click="openCreateClientModalFromSearch"
                                            class="w-full text-start px-3 py-2.5 hover:bg-primary-50 text-sm text-primary-600 border-t border-gray-100">
                                            <span class="font-semibold">إضافة "{{ $clientSearch }}"</span>
                                            <span class="text-xs text-primary-500/80 ms-1">كعميل جديد</span>
                                        </button>
                                    @endif
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

        <ui:box title="العناصر" class="border border-gray-100 shadow-sm">
            <x-slot:action>
                <div x-data="{ open: false }" class="relative">
                    <button type="button" @click="open = !open"
                        class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-sm font-medium text-gray-700 hover:bg-gray-50">
                        <ui:icon name="plus" class="h-4 w-4" />
                        <span>إضافة عنصر</span>
                        <ui:icon name="chevron-down" class="h-4 w-4 text-gray-400" />
                    </button>
                    <div x-show="open" x-cloak @click.outside="open = false"
                        class="absolute z-50 mt-1 min-w-52 rounded-lg border border-gray-200 bg-white p-1 shadow-lg ltr:right-0 rtl:left-0"
                        x-transition.scale.origin.top>
                        @foreach ($addItemTypeOptions as $typeKey => $typeLabel)
                            <button type="button" wire:click="addItem('{{ $typeKey }}')" @click="open = false"
                                class="flex w-full items-center gap-2.5 rounded-md px-3 py-2 text-start text-sm text-gray-700 hover:bg-gray-50">
                                <ui:icon name="{{ $addItemTypeIcons[$typeKey] ?? 'square-rounded-plus' }}"
                                    class="h-4 w-4 shrink-0 text-gray-400" />
                                <span>{{ $typeLabel }}</span>
                            </button>
                        @endforeach
                    </div>
                </div>
            </x-slot:action>
            <div class="p-4 space-y-4">
                @error('items')
                    <p class="text-red-500 text-xs">{{ $message }}</p>
                @enderror

                @if (count($items) === 0)
                    <p class="text-center text-sm text-gray-400 py-6">اختر نوع العنصر من القائمة لبدء إضافة الطلب.</p>
                @endif

                @foreach ($items as $index => $item)
                    <div wire:key="item-{{ $item['key'] }}"
                        class="bg-gray-50 rounded-lg p-3 space-y-3 relative border border-gray-100">
                        <button type="button" wire:click="removeItem({{ $index }})"
                            class="absolute top-2 left-2 text-red-400 hover:text-red-600 p-1 rounded hover:bg-red-50">
                            <ui:icon name="trash" class="w-4 h-4" />
                        </button>

                        <p class="text-xs font-semibold text-gray-500 pe-8">
                            {{ $itemTypeOptions[$item['type']] ?? $item['type'] }}
                        </p>

                        @if ($item['type'] === 'other')
                            <div>
                                <textarea wire:model="items.{{ $index }}.description" rows="2"
                                    placeholder="{{ $itemSearchPlaceholders['other'] ?? 'أضف وصف العنصر المخصص ..' }}"
                                    class="block w-full rounded-lg py-2 px-3 text-sm text-gray-800 border border-gray-200 focus:border-primary-500 focus:outline-none"></textarea>
                                @error('items.'.$index.'.description')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        @else
                            <div class="relative">
                                <input wire:model.live.debounce.300ms="items.{{ $index }}.search" type="text"
                                    placeholder="{{ $itemSearchPlaceholders[$item['type']] ?? 'ابحث أو أدخل الاسم ..' }}"
                                    class="block w-full rounded-lg py-2 px-3 text-sm text-gray-800 border border-gray-200 focus:border-primary-500 focus:outline-none">
                                @error('items.'.$index.'.name')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                                @if (strlen($item['search'] ?? '') > 0 && empty($item['name']))
                                    <div
                                        class="absolute z-40 mt-1 w-full bg-white border border-gray-200 rounded-lg shadow-lg max-h-40 overflow-y-auto">
                                        @foreach ($productSearchResults[$index] ?? [] as $product)
                                            <button type="button"
                                                wire:click="selectProduct({{ $index }}, {{ json_encode($product) }})"
                                                class="w-full text-start px-3 py-2 hover:bg-gray-50 border-b border-gray-50 last:border-0 text-sm">
                                                {{ $product['name'] }}
                                                <span class="text-xs text-gray-400 ms-2" dir="ltr">
                                                    {{ \App\Models\Order::formatMinor($product['unit_price']) }} SAR
                                                </span>
                                            </button>
                                        @endforeach
                                        @if (! empty($item['search']))
                                            <button type="button" wire:click="useCustomProduct({{ $index }})"
                                                class="w-full text-start px-3 py-2 hover:bg-gray-50 text-sm text-primary-600 border-t border-gray-100">
                                                استخدام "{{ $item['search'] }}" كعنصر جديد
                                            </button>
                                        @endif
                                    </div>
                                @endif
                                @if ($item['name'])
                                    <p class="text-xs text-green-600 mt-1">المحدد: {{ $item['name'] }}</p>
                                @endif
                                @if (\App\Models\Order::isBookingItemType($item['type']) && $item['name'] && empty($item['calendars']))
                                    <p class="text-xs text-amber-600 mt-1">لا يوجد تقويم مرتبط بهذه الخدمة. اربط تقويماً من إعدادات المحتوى أولاً.</p>
                                @endif
                            </div>
                        @endif

                        @if (\App\Models\Order::isBookingItemType($item['type']))
                            @if (! empty($item['calendars']))
                                <div>
                                    <label class="text-xs text-gray-500 mb-1 block">التقويم</label>
                                    <select wire:model.live="items.{{ $index }}.calendar_id"
                                        class="block w-full rounded-lg py-2 px-3 text-sm border border-gray-200 focus:border-primary-500 focus:outline-none">
                                        <option value="">اختر التقويم ..</option>
                                        @foreach ($item['calendars'] as $calendar)
                                            <option value="{{ $calendar['id'] }}">{{ $calendar['name'] }}</option>
                                        @endforeach
                                    </select>
                                    @error('items.'.$index.'.calendar_id')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            @endif

                            @if (! empty($item['calendar_id']) && ! empty($item['available_dates']))
                                <div>
                                    <label class="text-xs text-gray-500 mb-1 block">تاريخ الحجز</label>
                                    <select wire:model.live="items.{{ $index }}.booking_date"
                                        class="block w-full rounded-lg py-2 px-3 text-sm border border-gray-200 focus:border-primary-500 focus:outline-none"
                                        dir="ltr">
                                        <option value="">اختر التاريخ ..</option>
                                        @foreach ($item['available_dates'] as $availableDate)
                                            <option value="{{ $availableDate }}">
                                                {{ \Carbon\Carbon::parse($availableDate)->translatedFormat('l j F Y') }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('items.'.$index.'.booking_date')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            @elseif (! empty($item['calendar_id']))
                                <p class="text-xs text-amber-600">لا توجد تواريخ متاحة في التقويم المحدد.</p>
                            @endif

                            @if (! empty($item['booking_date']) && ! empty($item['time_slots']))
                                <div>
                                    <label class="text-xs text-gray-500 mb-1 block">وقت الحجز</label>
                                    <div class="flex flex-wrap gap-2">
                                        @foreach ($item['time_slots'] as $slot)
                                            @if ($slot['available'] ?? false)
                                                <button type="button"
                                                    wire:click="selectTimeSlot({{ $index }}, {{ json_encode($slot['start_at']) }}, {{ json_encode($slot['end_at']) }})"
                                                    class="rounded-lg border px-3 py-1.5 text-sm transition {{ ($item['booking_start_at'] ?? '') === $slot['start_at'] ? 'border-primary-500 bg-primary-50 text-primary-700 font-semibold' : 'border-gray-200 bg-white text-gray-700 hover:bg-gray-50' }}"
                                                    dir="ltr">
                                                    {{ $slot['label'] }}
                                                </button>
                                            @else
                                                <span
                                                    title="{{ ($slot['unavailable_reason'] ?? '') === 'booked' ? 'محجوز' : 'غير متاح' }}"
                                                    class="rounded-lg border border-gray-100 bg-gray-50 px-3 py-1.5 text-sm text-gray-300 line-through cursor-not-allowed select-none"
                                                    dir="ltr">
                                                    {{ $slot['label'] }}
                                                </span>
                                            @endif
                                        @endforeach
                                    </div>
                                    @error('items.'.$index.'.booking_time')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            @elseif (! empty($item['booking_date']))
                                <p class="text-xs text-amber-600">لا توجد فترات في هذا التاريخ.</p>
                            @endif

                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                                <div>
                                    <label class="text-xs text-gray-500 mb-1 block">السعر</label>
                                    <div class="py-2 px-3 text-sm font-semibold text-gray-800 bg-white rounded-lg border border-gray-100"
                                        dir="ltr">
                                        {{ \App\Models\Order::formatMinor(\App\Models\Order::minorFromDecimal($item['unit_price'])) }}
                                        SAR
                                    </div>
                                </div>
                                <div>
                                    <label class="text-xs text-gray-500 mb-1 block">الخصم</label>
                                    <input wire:model.live="items.{{ $index }}.discount" type="number"
                                        min="0" step="0.01"
                                        class="block w-full rounded-lg py-2 px-3 text-sm border border-gray-200 focus:border-primary-500 focus:outline-none"
                                        dir="ltr">
                                </div>
                                <div>
                                    <label class="text-xs text-gray-500 mb-1 block">الإجمالي</label>
                                    <div class="py-2 px-3 text-sm font-bold text-gray-800 bg-white rounded-lg border border-gray-100"
                                        dir="ltr">
                                        {{ \App\Models\Order::formatMinor($item['line_total']) }} SAR
                                    </div>
                                </div>
                            </div>
                        @else
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                                <div>
                                    <label class="text-xs text-gray-500 mb-1 block">الكمية</label>
                                    <input wire:model.live="items.{{ $index }}.qty" type="number" min="1"
                                        class="block w-full rounded-lg py-2 px-3 text-sm border border-gray-200 focus:border-primary-500 focus:outline-none"
                                        dir="ltr">
                                    @error('items.'.$index.'.qty')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label class="text-xs text-gray-500 mb-1 block">سعر الوحدة</label>
                                    <input wire:model.live="items.{{ $index }}.unit_price" type="number"
                                        min="0" step="0.01"
                                        class="block w-full rounded-lg py-2 px-3 text-sm border border-gray-200 focus:border-primary-500 focus:outline-none"
                                        dir="ltr">
                                    @error('items.'.$index.'.unit_price')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label class="text-xs text-gray-500 mb-1 block">الخصم</label>
                                    <input wire:model.live="items.{{ $index }}.discount" type="number"
                                        min="0" step="0.01"
                                        class="block w-full rounded-lg py-2 px-3 text-sm border border-gray-200 focus:border-primary-500 focus:outline-none"
                                        dir="ltr">
                                </div>
                                <div>
                                    <label class="text-xs text-gray-500 mb-1 block">الإجمالي</label>
                                    <div class="py-2 px-3 text-sm font-bold text-gray-800 bg-white rounded-lg border border-gray-100"
                                        dir="ltr">
                                        {{ \App\Models\Order::formatMinor($item['line_total']) }} SAR
                                    </div>
                                </div>
                        </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </ui:box>

        <ui:box title="ملخص الطلب" class="border border-gray-100 shadow-sm">
            <div class="p-4 space-y-2 max-w-sm ms-auto">
                <div class="flex items-center justify-between text-sm text-gray-600">
                    <span>المجموع الفرعي</span>
                    <span class="font-semibold text-gray-800" dir="ltr">{{ $totals['subtotal_formatted'] }}
                        SAR</span>
                </div>
                <div class="flex items-center justify-between text-sm text-gray-600">
                    <span>الخصومات</span>
                    <span class="font-semibold text-gray-800" dir="ltr">{{ $totals['discount_formatted'] }}
                        SAR</span>
                </div>
                <div class="flex items-center justify-between text-sm text-gray-600">
                    <span>الضريبة</span>
                    <span class="font-semibold text-gray-800" dir="ltr">{{ $totals['tax_formatted'] }} SAR</span>
                </div>
                <div
                    class="border-t border-gray-100 pt-2 flex items-center justify-between text-base font-bold text-gray-800">
                    <span>الإجمالي النهائي</span>
                    <span dir="ltr">{{ $totals['grand_formatted'] }} SAR</span>
                </div>
            </div>
        </ui:box>
    </div>

    <x-slot:footer class="p-5">
        <ui:button type="button" wire:click="submit" wire:target="submit" label="أنشئ الطلب" />
    </x-slot:footer>
    </ui:form>

    @if ($showCreateClientModal)
        <template x-teleport="body">
            <div class="fixed inset-0 z-[60] flex items-center justify-center p-4" wire:key="add-order-create-client">
                <div class="fixed inset-0 bg-gray-900/60" aria-hidden="true"></div>
                <div class="relative w-full max-w-lg rounded-xl bg-white shadow-2xl ring-1 ring-black/5">
                <div class="flex items-center justify-between border-b border-gray-100 p-3">
                    <p class="px-1 text-sm font-semibold text-gray-600">إضافة عميل جديد</p>
                    <button type="button" wire:click="closeCreateClientModal"
                        class="rounded-md bg-gray-100 p-1 text-gray-400 hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                        <span class="sr-only">Close</span>
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                            aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="space-y-4 p-5">
                    <ui:input name="newClientName" label="{{ __('Name') }}" placeholder="{{ __('Name') }}" />
                    <ui:input name="newClientPhone" label="{{ __('Phone') }}" type="number" dir="ltr"
                        placeholder="123456789" />
                    <ui:input name="newClientEmail" label="{{ __('Email') }}" type="email" dir="ltr"
                        placeholder="client@email.com" />
                    <div class="flex justify-end gap-2 border-t border-gray-100 pt-4">
                        <ui:button type="button" variant="outline" label="{{ __('Cancel') }}"
                            wire:click="closeCreateClientModal" />
                        <ui:button type="button" wire:click="createClient" wire:target="createClient"
                            label="{{ __('Save') }}" />
                    </div>
                </div>
                </div>
            </div>
        </template>
    @endif
</div>

<?php

use App\Models\Booking;
use App\Models\Calendar;
use App\Models\Client;
use App\Models\Content;
use App\Models\Order;
use App\Services\CalendarSlotService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

new class extends Livewire\Component {
    public ?int $client_id = null;

    public bool $isWalkingClient = true;

    public string $clientSearch = '';

    public bool $showClientResults = false;

    public ?string $selectedClientName = null;

    public ?string $selectedClientEmail = null;

    public ?string $selectedClientPhone = null;

    public string $newClientName = '';

    public string $newClientPhone = '';

    public ?string $newClientEmail = null;

    public bool $showCreateClientModal = false;

    public string $currency_code = 'SAR';

    /** @var array<int, array{key: string, type: string, product_id: ?int, name: string, search: string, description: string, qty: int, unit_price: string, discount: string, line_total: int, calendar_id: ?int, calendars: array<int, array{id: int, name: string}>, available_dates: array<int, string>, booking_date: string, booking_time: string, booking_start_at: ?string, booking_end_at: ?string, duration_minutes: int, time_slots: array<int, array{start: string, end: string, label: string, start_at: string, end_at: string}>}> */
    public array $items = [];

    protected function rules(): array
    {
        return [
            'client_id' => 'nullable|exists:clients,id',
            'items' => 'required|array|min:1',
            'items.*.type' => ['required', Rule::in(array_keys(Order::itemTypeOptions()))],
            'items.*.qty' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.discount' => 'nullable|numeric|min:0',
        ];
    }

    protected function messages(): array
    {
        return [
            'items.required' => 'يجب إضافة عنصر واحد على الأقل.',
            'items.min' => 'يجب إضافة عنصر واحد على الأقل.',
            'items.*.type.required' => 'يجب اختيار نوع العنصر.',
            'items.*.name.required' => 'يجب اختيار أو إدخال اسم العنصر.',
            'items.*.description.required' => 'الوصف مطلوب.',
            'items.*.qty.min' => 'الكمية يجب أن تكون أكبر من صفر.',
        ];
    }

    public function showClientSearchResults(): void
    {
        $this->showClientResults = true;
        $this->isWalkingClient = false;
    }

    public function updatedClientSearch(): void
    {
        $this->showClientResults = true;
        $this->isWalkingClient = false;

        if ($this->clientSearch === '') {
            return;
        }

        $this->client_id = null;
    }

    public function selectClient(int $id): void
    {
        $client = Client::query()->find($id);

        if (! $client) {
            return;
        }

        $this->client_id = $client->id;
        $this->isWalkingClient = false;
        $this->selectedClientName = $client->name;
        $this->selectedClientEmail = $client->email;
        $this->selectedClientPhone = $client->phone;
        $this->clientSearch = $client->name;
        $this->showClientResults = false;
    }

    public function selectWalkingClient(): void
    {
        $this->client_id = null;
        $this->isWalkingClient = true;
        $this->selectedClientName = null;
        $this->selectedClientEmail = null;
        $this->selectedClientPhone = null;
        $this->clientSearch = '';
        $this->showClientResults = false;
    }

    public function enterClientSearch(): void
    {
        $this->client_id = null;
        $this->isWalkingClient = false;
        $this->selectedClientName = null;
        $this->selectedClientEmail = null;
        $this->selectedClientPhone = null;
        $this->clientSearch = '';
        $this->showClientResults = false;
    }

    public function openCreateClientModal(?string $name = null): void
    {
        $this->newClientName = trim($name ?? $this->clientSearch);
        $this->newClientPhone = '';
        $this->newClientEmail = null;
        $this->resetValidation(['newClientName', 'newClientPhone', 'newClientEmail']);
        $this->showCreateClientModal = true;
    }

    public function openCreateClientModalFromSearch(): void
    {
        $this->openCreateClientModal($this->clientSearch);
    }

    public function closeCreateClientModal(): void
    {
        $this->showCreateClientModal = false;
    }

    public function createClient(): void
    {
        $this->validate([
            'newClientName' => 'required|min:1|max:255',
            'newClientPhone' => 'required|max:14',
            'newClientEmail' => 'nullable|email|max:255',
        ], [
            'newClientName.required' => 'اسم العميل مطلوب.',
            'newClientPhone.required' => 'رقم الهاتف مطلوب.',
        ]);

        $tenantId = currentTenantId();

        if (! $tenantId) {
            $this->addError('newClientName', __('No tenant selected.'));

            return;
        }

        $client = Client::withoutGlobalScope('tenantable')->firstOrCreate(
            [
                'phone' => $this->newClientPhone,
            ],
            [
                'name' => $this->newClientName,
                'phone' => $this->newClientPhone,
                'email' => $this->newClientEmail,
                'tenant_id' => $tenantId,
            ],
        );

        $client->tenants()->sync(
            [
                $tenantId => [
                    'active' => true,
                    'meta' => [
                        'name' => $this->newClientName,
                        'email' => $this->newClientEmail,
                        'phone' => $this->newClientPhone,
                    ],
                ],
            ],
            false,
        );

        $this->selectClient($client->id);
        $this->showCreateClientModal = false;
        $this->dispatch('notify', text: 'تم إضافة العميل واختياره.', type: 'success');
    }

    public function addItem(string $type): void
    {
        if (! array_key_exists($type, Order::itemTypeOptions())) {
            return;
        }

        $item = $this->blankItem();
        $item['type'] = $type;
        $this->items[] = $item;
    }

    public function removeItem(int $index): void
    {
        if (! isset($this->items[$index])) {
            return;
        }

        unset($this->items[$index]);
        $this->items = array_values($this->items);
        $this->refreshAllTimeSlots();
    }

    public function selectProduct(int $index, array $product): void
    {
        if (! isset($this->items[$index])) {
            return;
        }

        $previousCalendarId = (int) ($this->items[$index]['calendar_id'] ?? 0);

        $this->items[$index]['product_id'] = $product['product_id'];
        $this->items[$index]['name'] = $product['name'];
        $this->items[$index]['search'] = $product['name'];
        $this->items[$index]['unit_price'] = Order::formatMinor($product['unit_price']);
        $this->items[$index]['duration_minutes'] = (int) ($product['duration_minutes'] ?? 60);

        if (Order::isBookingItemType($this->items[$index]['type'])) {
            $this->items[$index]['qty'] = 1;
            $this->loadBookingCalendars($index, $previousCalendarId > 0 ? $previousCalendarId : null);
        }

        $this->recalculateLineTotal($index);
    }

    public function loadBookingAvailability(int $index, bool $preserveBookingDate = false): void
    {
        if (! isset($this->items[$index])) {
            return;
        }

        $calendarId = (int) ($this->items[$index]['calendar_id'] ?? 0);

        if ($calendarId <= 0) {
            $this->items[$index]['available_dates'] = [];
            $this->items[$index]['booking_date'] = '';
            $this->items[$index]['booking_time'] = '';
            $this->items[$index]['booking_start_at'] = null;
            $this->items[$index]['booking_end_at'] = null;
            $this->items[$index]['time_slots'] = [];
            $this->refreshTimeSlotsForOtherItems($index);

            return;
        }

        $calendar = Calendar::query()->find($calendarId);

        if (! $calendar) {
            return;
        }

        $this->items[$index]['available_dates'] = app(CalendarSlotService::class)->availableDates($calendar);
        $this->items[$index]['booking_time'] = '';
        $this->items[$index]['booking_start_at'] = null;
        $this->items[$index]['booking_end_at'] = null;
        $this->items[$index]['time_slots'] = [];

        if ($preserveBookingDate && filled($this->items[$index]['booking_date'] ?? '')) {
            $this->reloadTimeSlotsOnly($index);
        } else {
            $this->items[$index]['booking_date'] = '';
        }

        $this->refreshTimeSlotsForOtherItems($index);
    }

    public function loadBookingTimeSlots(int $index): void
    {
        if (! isset($this->items[$index])) {
            return;
        }

        $calendarId = (int) ($this->items[$index]['calendar_id'] ?? 0);
        $bookingDate = (string) ($this->items[$index]['booking_date'] ?? '');

        $this->items[$index]['booking_time'] = '';
        $this->items[$index]['booking_start_at'] = null;
        $this->items[$index]['booking_end_at'] = null;
        $this->items[$index]['time_slots'] = [];

        if ($calendarId <= 0 || $bookingDate === '') {
            $this->refreshTimeSlotsForOtherItems($index);

            return;
        }

        $this->reloadTimeSlotsOnly($index);
        $this->refreshTimeSlotsForOtherItems($index);
    }

    public function selectTimeSlot(int $index, string $startAt, string $endAt): void
    {
        if (! isset($this->items[$index])) {
            return;
        }

        $slot = collect($this->items[$index]['time_slots'] ?? [])
            ->first(fn (array $candidate): bool => ($candidate['start_at'] ?? '') === $startAt
                && ($candidate['end_at'] ?? '') === $endAt);

        if (! is_array($slot) || ! ($slot['available'] ?? false)) {
            return;
        }

        $this->items[$index]['booking_start_at'] = $startAt;
        $this->items[$index]['booking_end_at'] = $endAt;
        $this->items[$index]['booking_time'] = Carbon::parse($startAt)->format('H:i');
        $this->items[$index]['qty'] = 1;
        $this->recalculateLineTotal($index);
        $this->refreshTimeSlotsForOtherItems($index);
    }

    /**
     * @return list<array{start_at: string, end_at: string, calendar_id: int}>
     */
    protected function pendingReservedSlots(int $excludeIndex): array
    {
        $reserved = [];

        foreach ($this->items as $index => $item) {
            if ($index === $excludeIndex) {
                continue;
            }

            if (! Order::isBookingItemType($item['type'] ?? '')) {
                continue;
            }

            if (blank($item['booking_start_at'] ?? null) || blank($item['booking_end_at'] ?? null)) {
                continue;
            }

            $reserved[] = [
                'start_at' => (string) $item['booking_start_at'],
                'end_at' => (string) $item['booking_end_at'],
                'calendar_id' => (int) ($item['calendar_id'] ?? 0),
            ];
        }

        return $reserved;
    }

    /**
     * @return list<array{start_at: string, end_at: string}>
     */
    protected function reservedSlotsForItem(int $index): array
    {
        $calendarId = (int) ($this->items[$index]['calendar_id'] ?? 0);
        $bookingDate = (string) ($this->items[$index]['booking_date'] ?? '');

        if ($calendarId <= 0 || $bookingDate === '') {
            return [];
        }

        return collect($this->pendingReservedSlots($index))
            ->filter(fn (array $slot): bool => $slot['calendar_id'] === $calendarId
                && Carbon::parse($slot['start_at'])->toDateString() === $bookingDate)
            ->map(fn (array $slot): array => [
                'start_at' => $slot['start_at'],
                'end_at' => $slot['end_at'],
            ])
            ->values()
            ->all();
    }

    protected function reloadTimeSlotsOnly(int $index): void
    {
        if (! isset($this->items[$index])) {
            return;
        }

        $calendarId = (int) ($this->items[$index]['calendar_id'] ?? 0);
        $bookingDate = (string) ($this->items[$index]['booking_date'] ?? '');

        if ($calendarId <= 0 || $bookingDate === '') {
            $this->items[$index]['time_slots'] = [];

            return;
        }

        $calendar = Calendar::query()->find($calendarId);

        if (! $calendar) {
            return;
        }

        $durationMinutes = max(1, (int) ($this->items[$index]['duration_minutes'] ?? 60));
        $mode = ($this->items[$index]['type'] ?? '') === 'unit_rental' ? 'day' : 'slot';

        $this->items[$index]['time_slots'] = app(CalendarSlotService::class)->availableTimeSlots(
            $calendar,
            $bookingDate,
            $durationMinutes,
            $mode,
            $this->reservedSlotsForItem($index),
        );

        $this->clearInvalidBookingSelection($index);
    }

    protected function clearInvalidBookingSelection(int $index): void
    {
        $startAt = $this->items[$index]['booking_start_at'] ?? null;
        $endAt = $this->items[$index]['booking_end_at'] ?? null;

        if (blank($startAt) || blank($endAt)) {
            return;
        }

        $slot = collect($this->items[$index]['time_slots'] ?? [])
            ->first(fn (array $candidate): bool => ($candidate['start_at'] ?? '') === $startAt
                && ($candidate['end_at'] ?? '') === $endAt);

        if (is_array($slot) && ($slot['available'] ?? false)) {
            return;
        }

        $this->items[$index]['booking_start_at'] = null;
        $this->items[$index]['booking_end_at'] = null;
        $this->items[$index]['booking_time'] = '';
    }

    protected function refreshTimeSlotsForOtherItems(int $excludeIndex): void
    {
        foreach ($this->items as $index => $item) {
            if ($index === $excludeIndex) {
                continue;
            }

            if (! Order::isBookingItemType($item['type'] ?? '')) {
                continue;
            }

            if (blank($item['booking_date'] ?? '')) {
                continue;
            }

            $this->reloadTimeSlotsOnly($index);
        }
    }

    protected function refreshAllTimeSlots(): void
    {
        foreach ($this->items as $index => $item) {
            if (! Order::isBookingItemType($item['type'] ?? '')) {
                continue;
            }

            if (blank($item['booking_date'] ?? '')) {
                continue;
            }

            $this->reloadTimeSlotsOnly($index);
        }
    }

    protected function loadBookingCalendars(int $index, ?int $previousCalendarId = null): void
    {
        $productId = (int) ($this->items[$index]['product_id'] ?? 0);

        if ($productId <= 0) {
            $this->items[$index]['calendars'] = [];

            return;
        }

        $content = Content::query()
            ->with(['calendars' => fn ($query) => $query->where('calendars.active', true)])
            ->find($productId);

        $this->items[$index]['calendars'] = $content?->calendars
            ->map(fn (Calendar $calendar): array => [
                'id' => (int) $calendar->id,
                'name' => $calendar->name,
            ])
            ->values()
            ->all() ?? [];

        if (count($this->items[$index]['calendars']) === 1) {
            $calendarId = (int) $this->items[$index]['calendars'][0]['id'];
            $preserveBookingDate = $previousCalendarId !== null
                && $calendarId === $previousCalendarId
                && filled($this->items[$index]['booking_date'] ?? '');

            $this->items[$index]['calendar_id'] = $calendarId;
            $this->loadBookingAvailability($index, $preserveBookingDate);

            return;
        }

        $this->items[$index]['calendar_id'] = null;
        $this->items[$index]['available_dates'] = [];
        $this->items[$index]['booking_date'] = '';
        $this->items[$index]['booking_time'] = '';
        $this->items[$index]['booking_start_at'] = null;
        $this->items[$index]['booking_end_at'] = null;
        $this->items[$index]['time_slots'] = [];
        $this->refreshTimeSlotsForOtherItems($index);
    }

    public function useCustomProduct(int $index): void
    {
        if (! isset($this->items[$index])) {
            return;
        }

        $name = trim($this->items[$index]['search'] ?? '');

        if ($name === '') {
            return;
        }

        $content = $this->findOrCreateContentForItem(
            $this->items[$index]['type'],
            $name,
            $this->items[$index]['unit_price'] ?? '0',
        );

        $this->items[$index]['product_id'] = $content?->id;
        $this->items[$index]['name'] = $name;
        $this->items[$index]['search'] = $name;

        if (Order::isBookingItemType($this->items[$index]['type'])) {
            $previousCalendarId = (int) ($this->items[$index]['calendar_id'] ?? 0);
            $this->items[$index]['qty'] = 1;
            $this->items[$index]['duration_minutes'] = (int) data_get($content?->data, 'duration_minutes', 60);
            $this->loadBookingCalendars($index, $previousCalendarId > 0 ? $previousCalendarId : null);
        }

        $this->recalculateLineTotal($index);
    }

    public function updated($property): void
    {
        if (preg_match('/^items\.(\d+)\.search$/', $property, $matches)) {
            $index = (int) $matches[1];

            if (
                isset($this->items[$index])
                && ($this->items[$index]['type'] ?? '') !== 'other'
                && ($this->items[$index]['search'] ?? '') !== ($this->items[$index]['name'] ?? '')
                && ! empty($this->items[$index]['search'])
                && empty($this->items[$index]['product_id'])
            ) {
                $this->items[$index]['name'] = '';
            }

            return;
        }

        if (preg_match('/^items\.(\d+)\.calendar_id$/', $property, $matches)) {
            $this->loadBookingAvailability((int) $matches[1]);

            return;
        }

        if (preg_match('/^items\.(\d+)\.booking_date$/', $property, $matches)) {
            $this->loadBookingTimeSlots((int) $matches[1]);

            return;
        }

        if (preg_match('/^items\.(\d+)\.(qty|unit_price|discount)$/', $property, $matches)) {
            $this->recalculateLineTotal((int) $matches[1]);
        }
    }

    protected function blankItem(): array
    {
        return [
            'key' => Str::uuid()->toString(),
            'type' => '',
            'product_id' => null,
            'name' => '',
            'search' => '',
            'description' => '',
            'qty' => 1,
            'unit_price' => '0',
            'discount' => '0',
            'line_total' => 0,
            'calendar_id' => null,
            'calendars' => [],
            'available_dates' => [],
            'booking_date' => '',
            'booking_time' => '',
            'booking_start_at' => null,
            'booking_end_at' => null,
            'duration_minutes' => 60,
            'time_slots' => [],
        ];
    }

    public static function addItemTypeOptions(): array
    {
        return [
            'product' => 'أضف منتج',
            'digital_product' => 'أضف منتج رقمي',
            'course' => 'أضف دورة',
            'digital_service' => 'أضف خدمة رقمية',
            'menu' => 'أضف صنف طعام',
            'service' => 'أضف خدمة',
            'unit_rental' => 'أضف وحدة تأجير',
            'other' => 'أضف عنصر مخصص',
        ];
    }

    public static function itemSearchPlaceholders(): array
    {
        return [
            'product' => 'ابحث باسم المنتج أو أضف منتج جديد',
            'digital_product' => 'ابحث باسم المنتج الرقمي أو أضف منتجاً جديداً',
            'course' => 'ابحث باسم الدورة التدريبية أو أضف دورة جديدة',
            'digital_service' => 'ابحث باسم الخدمة الرقمية أو أضف خدمة جديدة',
            'menu' => 'ابحث باسم الصنف أو أضف صنفاً جديداً',
            'service' => 'ابحث باسم الخدمة أو أضف خدمة جديدة',
            'unit_rental' => 'ابحث باسم الوحدة أو أضف وحدة جديدة',
            'other' => 'أضف وصف العنصر المخصص ..',
        ];
    }

    public function submit()
    {
        $this->normalizeItems();
        $this->validateItemContent();

        if ($this->getErrorBag()->isNotEmpty()) {
            return;
        }

        $this->validate();

        $tenantId = currentTenantId();

        if (! $tenantId) {
            $this->addError('client_id', __('No tenant selected.'));

            return;
        }

        $totals = Order::calculateTotalsMinor($this->items);

        $order = DB::transaction(function () use ($tenantId, $totals) {
            $number = $this->generateOrderNumber($tenantId);

            $order = Order::create([
                'tenant_id' => $tenantId,
                'type' => 'order',
                'status' => 'draft',
                'channel' => 'manual',
                'number' => $number,
                'client_id' => $this->client_id,
                'currency_code' => $this->currency_code,
                'subtotal' => $totals['subtotal'],
                'discount_total' => $totals['discount_total'],
                'tax_total' => $totals['tax_total'],
                'grand_total' => $totals['grand_total'],
                'paid_total' => 0,
                'due_total' => $totals['grand_total'],
                'payment_status' => 'unpaid',
                'issued_at' => now(),
                'created_by' => auth()->id(),
                'notes' => null,
                'financial_status' => 'draft',
                'fulfillment_status' => 'unfulfilled',
                'meta' => [
                    'payment_method' => 'cash',
                ],
            ]);

            foreach ($this->items as $item) {
                $qty = (int) $item['qty'];
                $unitPrice = Order::minorFromDecimal($item['unit_price']);
                $discount = Order::minorFromDecimal($item['discount'] ?? 0);
                $lineTotal = max(0, ($qty * $unitPrice) - $discount);
                $bookingId = null;

                if (Order::isBookingItemType($item['type']) && filled($item['booking_start_at'] ?? null)) {
                    $booking = Booking::query()->create([
                        'tenant_id' => $tenantId,
                        'client_id' => $this->client_id,
                        'content_id' => $item['product_id'],
                        'calendar_id' => $item['calendar_id'],
                        'start_at' => $item['booking_start_at'],
                        'end_at' => $item['booking_end_at'],
                        'status' => 'pending',
                        'price_snapshot' => Order::fromMinor($unitPrice),
                        'currency' => $this->currency_code,
                        'meta' => [
                            'order_channel' => 'manual',
                        ],
                    ]);

                    $bookingId = $booking->id;
                }

                DB::table('order_items')->insert([
                    'order_id' => $order->id,
                    'product_id' => $item['type'] === 'other' ? null : $item['product_id'],
                    'name' => $item['name'],
                    'qty' => $qty,
                    'unit_price' => $unitPrice,
                    'discount_total' => $discount,
                    'tax_total' => 0,
                    'line_total' => $lineTotal,
                    'meta' => json_encode([
                        'type' => $item['type'],
                        'description' => $item['type'] === 'other' ? ($item['description'] ?? null) : null,
                        'booking_id' => $bookingId,
                        'calendar_id' => $item['calendar_id'] ?? null,
                        'booking_start_at' => $item['booking_start_at'] ?? null,
                        'booking_end_at' => $item['booking_end_at'] ?? null,
                    ]),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            return $order;
        });

        $this->dispatch('updateOrderList');
        $this->dispatch('closemodal', modal: 'add-order');
        $this->dispatch('notify', text: 'تم إنشاء الطلب بنجاح.', type: 'success');

        $this->redirect(route('admin.orders.detail', ['id' => $order->uuid]), navigate: true);
    }

    protected function normalizeItems(): void
    {
        foreach ($this->items as $index => $item) {
            if (($item['type'] ?? '') === 'other') {
                $this->items[$index]['name'] = trim($item['description'] ?? '');
                $this->items[$index]['product_id'] = null;
            } elseif (blank($item['name'] ?? '') && filled($item['search'] ?? '')) {
                $this->items[$index]['name'] = trim($item['search']);
            }

            if (blank($this->items[$index]['discount'] ?? '')) {
                $this->items[$index]['discount'] = '0';
            }

            if (blank($this->items[$index]['unit_price'] ?? '')) {
                $this->items[$index]['unit_price'] = '0';
            }

            if (Order::isBookingItemType($item['type'] ?? '')) {
                $this->items[$index]['qty'] = 1;
            }

            if (
                ($item['type'] ?? '') !== 'other'
                && blank($this->items[$index]['product_id'] ?? null)
                && filled($this->items[$index]['name'] ?? '')
            ) {
                $content = $this->findOrCreateContentForItem(
                    $item['type'],
                    $this->items[$index]['name'],
                    $this->items[$index]['unit_price'] ?? '0',
                );

                if ($content) {
                    $this->items[$index]['product_id'] = $content->id;
                }
            }
        }
    }

    protected function validateItemContent(): void
    {
        foreach ($this->items as $index => $item) {
            if (($item['type'] ?? '') === 'other') {
                if (blank(trim($item['description'] ?? ''))) {
                    $this->addError("items.{$index}.description", 'الوصف مطلوب.');
                }

                continue;
            }

            if (blank($item['name'] ?? '')) {
                $this->addError("items.{$index}.name", 'يجب اختيار أو إدخال اسم العنصر.');

                continue;
            }

            if (! Order::isBookingItemType($item['type'] ?? '')) {
                continue;
            }

            if (blank($item['calendar_id'] ?? null)) {
                $this->addError("items.{$index}.calendar_id", 'يجب اختيار التقويم.');
            }

            if (blank($item['booking_date'] ?? '')) {
                $this->addError("items.{$index}.booking_date", 'يجب اختيار تاريخ الحجز.');
            }

            if (blank($item['booking_start_at'] ?? null) || blank($item['booking_end_at'] ?? null)) {
                $this->addError("items.{$index}.booking_time", 'يجب اختيار وقت الحجز.');
            }
        }

        $this->validateNoDuplicateBookingSlots();
        $this->validateBookingSlotAvailability();
    }

    protected function validateNoDuplicateBookingSlots(): void
    {
        /** @var list<array{calendar_id: int, start_at: string, end_at: string}> $selections */
        $selections = [];

        foreach ($this->items as $index => $item) {
            if (! Order::isBookingItemType($item['type'] ?? '')) {
                continue;
            }

            $startAt = $item['booking_start_at'] ?? null;
            $endAt = $item['booking_end_at'] ?? null;
            $calendarId = (int) ($item['calendar_id'] ?? 0);

            if (blank($startAt) || blank($endAt) || $calendarId <= 0) {
                continue;
            }

            $start = Carbon::parse((string) $startAt);
            $end = Carbon::parse((string) $endAt);

            foreach ($selections as $existing) {
                if ($existing['calendar_id'] !== $calendarId) {
                    continue;
                }

                $existingStart = Carbon::parse($existing['start_at']);
                $existingEnd = Carbon::parse($existing['end_at']);

                if ($start->lt($existingEnd) && $end->gt($existingStart)) {
                    $this->addError("items.{$index}.booking_time", 'هذا الوقت محجوز بالفعل لعنصر آخر في الطلب.');
                }
            }

            $selections[] = [
                'calendar_id' => $calendarId,
                'start_at' => (string) $startAt,
                'end_at' => (string) $endAt,
            ];
        }
    }

    protected function validateBookingSlotAvailability(): void
    {
        foreach ($this->items as $index => $item) {
            if (! Order::isBookingItemType($item['type'] ?? '')) {
                continue;
            }

            $startAt = $item['booking_start_at'] ?? null;
            $endAt = $item['booking_end_at'] ?? null;
            $calendarId = (int) ($item['calendar_id'] ?? 0);
            $bookingDate = (string) ($item['booking_date'] ?? '');

            if (blank($startAt) || blank($endAt) || $calendarId <= 0 || $bookingDate === '') {
                continue;
            }

            $calendar = Calendar::query()->find($calendarId);

            if (! $calendar) {
                continue;
            }

            $durationMinutes = max(1, (int) ($item['duration_minutes'] ?? 60));
            $mode = ($item['type'] ?? '') === 'unit_rental' ? 'day' : 'slot';

            $slot = collect(app(CalendarSlotService::class)->availableTimeSlots(
                $calendar,
                $bookingDate,
                $durationMinutes,
                $mode,
                $this->reservedSlotsForItem($index),
            ))->first(fn (array $candidate): bool => ($candidate['start_at'] ?? '') === $startAt
                && ($candidate['end_at'] ?? '') === $endAt);

            if (! is_array($slot) || ! ($slot['available'] ?? false)) {
                $this->addError("items.{$index}.booking_time", 'الوقت المحدد غير متاح.');
            }
        }
    }

    protected function generateOrderNumber(int $tenantId): string
    {
        $lastId = Order::query()
            ->where('tenant_id', $tenantId)
            ->where('type', 'order')
            ->orderByDesc('id')
            ->lockForUpdate()
            ->value('id');

        return str_pad((string) (($lastId ?? 0) + 1), 6, '0', STR_PAD_LEFT);
    }

    protected function recalculateLineTotal(int $index): void
    {
        if (! isset($this->items[$index])) {
            return;
        }

        $item = $this->items[$index];
        $qty = max(0, (int) ($item['qty'] ?? 0));
        $unitPrice = Order::minorFromDecimal($item['unit_price'] ?? 0);
        $discount = Order::minorFromDecimal($item['discount'] ?? 0);
        $this->items[$index]['line_total'] = max(0, ($qty * $unitPrice) - $discount);
    }

    protected function searchClients(): array
    {
        if ($this->clientSearch === '' || $this->client_id) {
            return [];
        }

        $search = '%'.$this->clientSearch.'%';

        return Client::query()
            ->where(function ($query) use ($search) {
                $query->where('name', 'like', $search)
                    ->orWhere('email', 'like', $search)
                    ->orWhere('phone', 'like', $search);
            })
            ->orderBy('name')
            ->limit(8)
            ->get(['id', 'name', 'email', 'phone'])
            ->toArray();
    }

    protected function orderItemContentType(string $type): ?string
    {
        return match ($type) {
            'product' => contentTypeModel('store'),
            'digital_product' => contentTypeModel('digital-products'),
            'service' => contentTypeModel('services'),
            'course' => contentTypeModel('courses'),
            'digital_service' => contentTypeModel('digital-services'),
            'menu' => contentTypeModel('menu'),
            'unit_rental' => contentTypeModel('unit-rental'),
            default => null,
        };
    }

    protected function searchProducts(string $term, string $type): array
    {
        if ($term === '' || $type === '' || $type === 'other') {
            return [];
        }

        $contentType = $this->orderItemContentType($type);

        if ($contentType === null) {
            return [];
        }

        $search = '%'.$term.'%';
        $results = Content::query()
            ->where('type', $contentType)
            ->where('title', 'like', $search)
            ->orderBy('title')
            ->limit(8)
            ->get(['id', 'title', 'data'])
            ->map(fn (Content $content): array => [
                'name' => $content->title,
                'product_id' => $content->id,
                'unit_price' => (int) data_get($content->data, 'price', 0),
                'duration_minutes' => (int) (data_get($content->data, 'duration_minutes') ?: 60),
            ])
            ->all();

        if ($results !== []) {
            return $results;
        }

        return DB::table('order_items')
            ->select('name', DB::raw('MAX(product_id) as product_id'), DB::raw('MAX(unit_price) as unit_price'))
            ->where('name', 'like', $search)
            ->where(function ($query) use ($type) {
                $query->where('meta->type', $type);

                if ($type === 'product') {
                    $query->orWhereNull('meta');
                }
            })
            ->groupBy('name')
            ->orderBy('name')
            ->limit(8)
            ->get()
            ->map(fn ($row) => [
                'name' => $row->name,
                'product_id' => $row->product_id,
                'unit_price' => (int) $row->unit_price,
            ])
            ->all();
    }

    protected function findOrCreateContentForItem(string $orderItemType, string $title, string $unitPriceDecimal = '0'): ?Content
    {
        $contentType = $this->orderItemContentType($orderItemType);

        if ($contentType === null) {
            return null;
        }

        $tenantId = currentTenantId();

        if (! $tenantId) {
            return null;
        }

        $title = trim($title);

        if ($title === '') {
            return null;
        }

        $existing = Content::query()
            ->where('type', $contentType)
            ->where('title', $title)
            ->first();

        if ($existing) {
            return $existing;
        }

        $data = [
            'price' => Order::minorFromDecimal($unitPriceDecimal),
        ];

        if ($orderItemType === 'course') {
            $data = array_merge($data, [
                'level' => 'beginner',
                'course_type' => 'recorded',
                'hours' => 0,
                'chapters' => [],
            ]);
        }

        if ($orderItemType === 'digital_service') {
            $data['delivery_days'] = null;
        }

        return Content::query()->create([
            'tenant_id' => $tenantId,
            'type' => $contentType,
            'title' => $title,
            'slug' => $this->uniqueContentSlug($title, $orderItemType),
            'status' => 'draft',
            'active' => true,
            'data' => $data,
        ]);
    }

    protected function uniqueContentSlug(string $title, string $orderItemType): string
    {
        $baseSlug = Str::slug($title);
        $fallback = match ($orderItemType) {
            'product' => 'product',
            'digital_product' => 'digital-product',
            'service' => 'service',
            'course' => 'course',
            'digital_service' => 'digital-service',
            'menu' => 'menu-item',
            'unit_rental' => 'unit',
            default => 'item',
        };
        $slug = $baseSlug !== '' ? $baseSlug : $fallback;
        $counter = 1;

        while (Content::query()->where('slug', $slug)->exists()) {
            $slug = ($baseSlug !== '' ? $baseSlug : $fallback).'-'.$counter;
            $counter++;
        }

        return $slug;
    }

    protected function buildTotals(): array
    {
        $totals = Order::calculateTotalsMinor($this->items);

        return [
            'subtotal' => $totals['subtotal'],
            'discount' => $totals['discount_total'],
            'tax' => $totals['tax_total'],
            'grand' => $totals['grand_total'],
            'subtotal_formatted' => Order::formatMinor($totals['subtotal']),
            'discount_formatted' => Order::formatMinor($totals['discount_total']),
            'tax_formatted' => Order::formatMinor($totals['tax_total']),
            'grand_formatted' => Order::formatMinor($totals['grand_total']),
        ];
    }

    public function with(): array
    {
        $productSearchResults = [];

        foreach ($this->items as $index => $item) {
            if (
                ! empty($item['type'])
                && $item['type'] !== 'other'
                && ! empty($item['search'])
                && empty($item['name'])
            ) {
                $productSearchResults[$index] = $this->searchProducts($item['search'], $item['type']);
            }
        }

        return [
            'clientResults' => $this->searchClients(),
            'productSearchResults' => $productSearchResults,
            'itemTypeOptions' => Order::itemTypeOptions(),
            'addItemTypeOptions' => self::addItemTypeOptions(),
            'addItemTypeIcons' => Order::itemTypeIcons(),
            'itemSearchPlaceholders' => self::itemSearchPlaceholders(),
            'totals' => $this->buildTotals(),
        ];
    }
}; ?>
