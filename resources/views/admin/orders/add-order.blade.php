<ui:form class="!p-0 !gap-0 max-h-[75vh] overflow-y-auto" novalidate>
    <div class="space-y-4 p-5">
        <ui:box title="معلومات العميل" class="border border-gray-100 shadow-sm">
            <div class="p-4 space-y-3">
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
                        <button type="button" wire:click="clearClient"
                            class="text-xs text-red-500 hover:text-red-700 px-2 py-1 rounded hover:bg-red-50">
                            تغيير
                        </button>
                    </div>
                @else
                    <div class="relative" x-data="{ open: @entangle('showClientResults') }">
                        <div class="relative">
                            <div
                                class="absolute ps-2 right-0 top-0 bottom-0 flex items-center pointer-events-none text-gray-500">
                                <ui:icon name="search" class="text-gray-400" />
                            </div>
                            <input wire:model.live.debounce.300ms="clientSearch" type="text"
                                placeholder="ابحث بالاسم أو البريد أو الهاتف .."
                                class="block w-full rounded-lg py-2 ps-10 text-gray-800 border border-gray-200 placeholder:text-gray-400 focus:border-primary-500 focus:outline-none sm:text-sm">
                        </div>
                        @error('client_id')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                        @if ($showClientResults && count($clientResults) > 0)
                            <div
                                class="absolute z-50 mt-1 w-full bg-white border border-gray-200 rounded-lg shadow-lg max-h-48 overflow-y-auto">
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
                            </div>
                        @elseif ($showClientResults && $clientSearch !== '')
                            <div
                                class="absolute z-50 mt-1 w-full bg-white border border-gray-200 rounded-lg shadow-lg p-3 text-sm text-gray-500">
                                لا توجد نتائج.
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        </ui:box>

        <ui:box title="معلومات الطلب" class="border border-gray-100 shadow-sm">
            <div class="p-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
                <ui:select name="status" label="حالة الطلب" :options="$statusOptions" live />
                <ui:select name="payment_status" label="{{ __('Payment status') }}" :options="$paymentStatusOptions"
                    live />
                <ui:select name="payment_method" label="طريقة الدفع" :options="$paymentMethodOptions" live />
                <div>
                    <label class="text-sm text-gray-600 mb-1 block">العملة</label>
                    <input type="text" value="SAR" disabled
                        class="block w-full rounded-md text-sm border-2 bg-gray-50 py-2 px-3 text-gray-500 border-transparent">
                </div>
                <div class="sm:col-span-2">
                    <ui:textarea name="notes" label="ملاحظات" placeholder="ملاحظات إضافية (اختياري)" rows="2" />
                </div>
            </div>
        </ui:box>

        <ui:box title="العناصر" class="border border-gray-100 shadow-sm">
            <x-slot:action>
                <ui:button type="button" wire:click="addItem" icon="plus" variant="outline" label="إضافة عنصر" />
            </x-slot:action>
            <div class="p-4 space-y-4">
                @error('items')
                    <p class="text-red-500 text-xs">{{ $message }}</p>
                @enderror

                @foreach ($items as $index => $item)
                    <div wire:key="item-{{ $item['key'] }}"
                        class="bg-gray-50 rounded-lg p-3 space-y-3 relative border border-gray-100">
                        @if (count($items) > 1)
                            <button type="button" wire:click="removeItem({{ $index }})"
                                class="absolute top-2 left-2 text-red-400 hover:text-red-600 p-1 rounded hover:bg-red-50">
                                <ui:icon name="trash" class="w-4 h-4" />
                            </button>
                        @endif

                        <div>
                            <label class="text-xs text-gray-500 mb-1 block">النوع</label>
                            <select wire:model.live="items.{{ $index }}.type"
                                class="block w-full rounded-lg py-2 px-3 text-sm text-gray-800 border border-gray-200 focus:border-primary-500 focus:outline-none">
                                <option value="">اختر النوع ..</option>
                                @foreach ($itemTypeOptions as $typeKey => $typeLabel)
                                    <option value="{{ $typeKey }}">{{ $typeLabel }}</option>
                                @endforeach
                            </select>
                            @error('items.'.$index.'.type')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        @if ($item['type'] === 'other')
                            <div>
                                <label class="text-xs text-gray-500 mb-1 block">الوصف</label>
                                <textarea wire:model="items.{{ $index }}.description" rows="2"
                                    placeholder="وصف العنصر .."
                                    class="block w-full rounded-lg py-2 px-3 text-sm text-gray-800 border border-gray-200 focus:border-primary-500 focus:outline-none"></textarea>
                                @error('items.'.$index.'.description')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        @elseif ($item['type'] !== '')
                            <div class="relative">
                                <label class="text-xs text-gray-500 mb-1 block">
                                    {{ $itemTypeOptions[$item['type']] ?? 'العنصر' }}
                                </label>
                                <input wire:model.live.debounce.300ms="items.{{ $index }}.search" type="text"
                                    placeholder="ابحث أو أدخل الاسم .."
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
                            </div>
                        @endif

                        @if ($item['type'] !== '')
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

    <x-slot:footer>
        <ui:button type="button" wire:click="submit" wire:target="submit" label="حفظ الطلب" />
    </x-slot:footer>
</ui:form>

<?php

use App\Models\Client;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

new class extends Livewire\Component {
    public ?int $client_id = null;

    public string $clientSearch = '';

    public bool $showClientResults = false;

    public ?string $selectedClientName = null;

    public ?string $selectedClientEmail = null;

    public ?string $selectedClientPhone = null;

    public string $status = 'confirmed';

    public string $payment_status = 'unpaid';

    public string $payment_method = 'cash';

    public string $currency_code = 'SAR';

    public ?string $notes = null;

    /** @var array<int, array{key: string, type: string, product_id: ?int, name: string, search: string, description: string, qty: int, unit_price: string, discount: string, line_total: int}> */
    public array $items = [];

    public function mount(): void
    {
        $this->addItem();
    }

    protected function rules(): array
    {
        return [
            'client_id' => 'required|exists:clients,id',
            'status' => ['required', Rule::in(array_keys(Order::statusOptions()))],
            'payment_status' => ['required', Rule::in(array_keys(Order::paymentStatusOptions()))],
            'payment_method' => ['required', Rule::in(array_keys(Order::paymentMethodOptions()))],
            'notes' => 'nullable|string|max:2000',
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
            'client_id.required' => 'يجب اختيار عميل.',
            'items.required' => 'يجب إضافة عنصر واحد على الأقل.',
            'items.min' => 'يجب إضافة عنصر واحد على الأقل.',
            'items.*.type.required' => 'يجب اختيار نوع العنصر.',
            'items.*.name.required' => 'يجب اختيار أو إدخال اسم العنصر.',
            'items.*.description.required' => 'الوصف مطلوب.',
            'items.*.qty.min' => 'الكمية يجب أن تكون أكبر من صفر.',
            'status.required' => 'حالة الطلب مطلوبة.',
            'payment_status.required' => 'حالة الدفع مطلوبة.',
        ];
    }

    public function updatedClientSearch(): void
    {
        $this->showClientResults = $this->clientSearch !== '';

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
        $this->selectedClientName = $client->name;
        $this->selectedClientEmail = $client->email;
        $this->selectedClientPhone = $client->phone;
        $this->clientSearch = $client->name;
        $this->showClientResults = false;
    }

    public function clearClient(): void
    {
        $this->client_id = null;
        $this->selectedClientName = null;
        $this->selectedClientEmail = null;
        $this->selectedClientPhone = null;
        $this->clientSearch = '';
        $this->showClientResults = false;
    }

    public function addItem(): void
    {
        $this->items[] = $this->blankItem();
    }

    public function removeItem(int $index): void
    {
        if (count($this->items) <= 1) {
            return;
        }

        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }

    public function selectProduct(int $index, array $product): void
    {
        if (! isset($this->items[$index])) {
            return;
        }

        $this->items[$index]['product_id'] = $product['product_id'];
        $this->items[$index]['name'] = $product['name'];
        $this->items[$index]['search'] = $product['name'];
        $this->items[$index]['unit_price'] = Order::formatMinor($product['unit_price']);
        $this->recalculateLineTotal($index);
    }

    public function useCustomProduct(int $index): void
    {
        if (! isset($this->items[$index])) {
            return;
        }

        $this->items[$index]['product_id'] = null;
        $this->items[$index]['name'] = $this->items[$index]['search'];
        $this->recalculateLineTotal($index);
    }

    public function updated($property): void
    {
        if (preg_match('/^items\.(\d+)\.type$/', $property, $matches)) {
            $this->resetItemFields((int) $matches[1]);

            return;
        }

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

        if (preg_match('/^items\.(\d+)\.(qty|unit_price|discount)$/', $property, $matches)) {
            $this->recalculateLineTotal((int) $matches[1]);
        }
    }

    protected function resetItemFields(int $index): void
    {
        if (! isset($this->items[$index])) {
            return;
        }

        $type = $this->items[$index]['type'] ?? '';

        $this->items[$index]['product_id'] = null;
        $this->items[$index]['name'] = '';
        $this->items[$index]['search'] = '';
        $this->items[$index]['description'] = '';
        $this->items[$index]['unit_price'] = '0';
        $this->items[$index]['discount'] = '0';
        $this->items[$index]['line_total'] = 0;
        $this->items[$index]['type'] = $type;
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
        $paidTotal = $this->payment_status === 'paid' ? $totals['grand_total'] : 0;

        $order = DB::transaction(function () use ($tenantId, $totals, $paidTotal) {
            $number = $this->generateOrderNumber($tenantId);

            $order = Order::create([
                'tenant_id' => $tenantId,
                'type' => 'order',
                'status' => $this->status,
                'channel' => 'manual',
                'number' => $number,
                'client_id' => $this->client_id,
                'currency_code' => $this->currency_code,
                'subtotal' => $totals['subtotal'],
                'discount_total' => $totals['discount_total'],
                'tax_total' => $totals['tax_total'],
                'grand_total' => $totals['grand_total'],
                'paid_total' => $paidTotal,
                'due_total' => max(0, $totals['grand_total'] - $paidTotal),
                'payment_status' => $this->payment_status,
                'issued_at' => now(),
                'created_by' => auth()->id(),
                'notes' => $this->notes,
                'financial_status' => 'draft',
                'fulfillment_status' => 'unfulfilled',
                'meta' => [
                    'payment_method' => $this->payment_method,
                ],
            ]);

            foreach ($this->items as $item) {
                $qty = (int) $item['qty'];
                $unitPrice = Order::minorFromDecimal($item['unit_price']);
                $discount = Order::minorFromDecimal($item['discount'] ?? 0);
                $lineTotal = max(0, ($qty * $unitPrice) - $discount);

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
        ];
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

    protected function searchProducts(string $term, string $type): array
    {
        if ($term === '' || $type === '' || $type === 'other') {
            return [];
        }

        return DB::table('order_items')
            ->select('name', DB::raw('MAX(product_id) as product_id'), DB::raw('MAX(unit_price) as unit_price'))
            ->where('name', 'ilike', '%'.$term.'%')
            ->where(function ($query) use ($type) {
                $query->whereRaw("meta->>'type' = ?", [$type]);

                if ($type === 'product') {
                    $query->orWhereNull('meta')
                        ->orWhereRaw("meta->>'type' IS NULL");
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
            'statusOptions' => Order::statusOptions(),
            'paymentStatusOptions' => Order::paymentStatusOptions(),
            'paymentMethodOptions' => Order::paymentMethodOptions(),
            'totals' => $this->buildTotals(),
        ];
    }
}; ?>
