<?php

namespace App\Services;

use App\Actions\SendNewOrderNotificationEmail;
use App\Actions\SendOrderConfirmationEmail;
use App\Models\Booking;
use App\Models\Calendar;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Client;
use App\Models\Content;
use App\Models\Order;
use App\Models\Tenant;
use App\Support\Money;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CartService
{
    protected ?Cart $resolvedCart = null;

    /** @var Collection<int, CartItem>|null */
    protected ?Collection $resolvedItems = null;

    public function __construct(
        protected CheckoutShippingService $shipping,
        protected CalendarSlotService $calendarSlots,
    ) {}

    public function cart(): Cart
    {
        if ($this->resolvedCart instanceof Cart) {
            return $this->resolvedCart;
        }

        $tenantId = currentTenantId();

        abort_unless($tenantId, 404);

        $client = auth('client')->user();

        if ($client instanceof Client) {
            return $this->resolvedCart = Cart::query()->firstOrCreate(
                [
                    'tenant_id' => $tenantId,
                    'client_id' => $client->id,
                ],
                [
                    'session_id' => session()->getId(),
                ],
            );
        }

        return $this->resolvedCart = tap(
            Cart::query()->firstOrCreate(
                [
                    'tenant_id' => $tenantId,
                    'session_id' => session()->getId(),
                    'client_id' => null,
                ],
            ),
            fn (Cart $cart): mixed => session(['guest_cart_id' => $cart->id]),
        );
    }

    public function stashGuestCartReference(int $tenantId): void
    {
        $cart = Cart::query()
            ->where('tenant_id', $tenantId)
            ->where('session_id', session()->getId())
            ->whereNull('client_id')
            ->first();

        if ($cart instanceof Cart) {
            session(['guest_cart_id' => $cart->id]);
        }
    }

    public function forgetCart(): void
    {
        $this->resolvedCart = null;
        $this->resolvedItems = null;
    }

    protected function forgetItems(): void
    {
        $this->resolvedItems = null;
    }

    public function mergeGuestCartInto(Client $client, int $tenantId, ?string $guestSessionId = null): void
    {
        $guestCart = $this->resolveGuestCartForMerge($tenantId, $guestSessionId);

        if (! $guestCart instanceof Cart || $guestCart->items->isEmpty()) {
            $this->forgetCart();

            return;
        }

        $sessionId = session()->getId();

        DB::transaction(function () use ($guestCart, $client, $tenantId, $sessionId): void {
            $clientCart = Cart::query()->firstOrCreate(
                [
                    'tenant_id' => $tenantId,
                    'client_id' => $client->id,
                ],
                [
                    'session_id' => $sessionId,
                ],
            );

            foreach ($guestCart->items as $guestItem) {
                if ($guestItem->isBooking() || $guestItem->hasMealOptions()) {
                    $guestItem->update(['cart_id' => $clientCart->id]);

                    continue;
                }

                $existingItem = CartItem::query()
                    ->where('cart_id', $clientCart->id)
                    ->where('productable_type', $guestItem->productable_type)
                    ->where('productable_id', $guestItem->productable_id)
                    ->where(function ($query): void {
                        $query->where('line_signature', '')->orWhereNull('line_signature');
                    })
                    ->get()
                    ->first(fn (CartItem $item): bool => ! $item->isBooking() && ! $item->hasMealOptions());

                if ($existingItem instanceof CartItem) {
                    $existingItem->increment('quantity', $guestItem->quantity);
                    $guestItem->delete();

                    continue;
                }

                $guestItem->update(['cart_id' => $clientCart->id]);
            }

            $guestCart->delete();
        });

        session()->forget('guest_cart_id');

        $this->forgetCart();
    }

    protected function resolveGuestCartForMerge(int $tenantId, ?string $guestSessionId = null): ?Cart
    {
        $guestCartId = session('guest_cart_id');

        if ($guestCartId) {
            $cart = Cart::query()
                ->whereKey($guestCartId)
                ->where('tenant_id', $tenantId)
                ->whereNull('client_id')
                ->with('items')
                ->first();

            if ($cart instanceof Cart) {
                return $cart;
            }
        }

        foreach (array_unique(array_filter([$guestSessionId, session()->getId()])) as $sessionId) {
            $cart = Cart::query()
                ->where('tenant_id', $tenantId)
                ->where('session_id', $sessionId)
                ->whereNull('client_id')
                ->with('items')
                ->first();

            if ($cart instanceof Cart) {
                return $cart;
            }
        }

        return null;
    }

    public function itemCount(): int
    {
        return (int) CartItem::query()
            ->where('cart_id', $this->cart()->id)
            ->sum('quantity');
    }

    /**
     * @return Collection<int, CartItem>
     */
    public function items(): Collection
    {
        return $this->resolvedItems ??= CartItem::query()
            ->where('cart_id', $this->cart()->id)
            ->with('productable')
            ->orderBy('id')
            ->get();
    }

    public function subtotal(): int
    {
        return $this->items()->sum(fn (CartItem $item): int => $item->lineTotal());
    }

    public function requiresShipping(): bool
    {
        return $this->items()->contains(fn (CartItem $item): bool => $item->isShippable());
    }

    /**
     * @param  array{country?: string|null, city_id?: string|null}  $address
     */
    public function shippingFee(string $shippingMethod, array $address = []): int
    {
        if (! $this->requiresShipping() || $shippingMethod === 'none') {
            return 0;
        }

        return $this->shipping->fee(
            $shippingMethod,
            $address['country'] ?? null,
            $address['city_id'] ?? null,
        );
    }

    /**
     * @param  array{country?: string|null, city_id?: string|null}  $address
     */
    public function grandTotal(string $shippingMethod, array $address = []): int
    {
        return $this->subtotal() + $this->shippingFee($shippingMethod, $address);
    }

    public function addProduct(Content $product, int $quantity = 1): CartItem
    {
        return $this->addItem($product, $quantity);
    }

    public function addItem(Content $content, int $quantity = 1): CartItem
    {
        $quantity = max(1, $quantity);
        $cart = $this->cart();
        $unitPrice = (int) data_get($content->data, 'price', 0);

        $item = CartItem::query()
            ->where('cart_id', $cart->id)
            ->where('productable_type', $content->getMorphClass())
            ->where('productable_id', $content->id)
            ->where(function ($query): void {
                $query->where('line_signature', '')->orWhereNull('line_signature');
            })
            ->get()
            ->first(fn (CartItem $cartItem): bool => ! $cartItem->isBooking() && ! $cartItem->hasMealOptions());

        if ($item) {
            $item->increment('quantity', $quantity);
            $this->forgetItems();

            return $item->refresh();
        }

        $created = CartItem::query()->create([
            'cart_id' => $cart->id,
            'productable_type' => $content->getMorphClass(),
            'productable_id' => $content->id,
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'line_signature' => '',
            'meta' => $this->baseCartMeta($content),
        ]);

        $this->forgetItems();

        return $created;
    }

    /**
     * @return array{item_type: string, title: string, slug: string, image_url: ?string, shippable: bool}
     */
    protected function baseCartMeta(Content $content): array
    {
        return [
            'item_type' => $content->orderItemType(),
            'title' => $content->title,
            'slug' => $content->slug,
            'image_url' => $content->cartImageUrl(),
            'shippable' => $content->isShippable(),
        ];
    }

    /**
     * @param  array{
     *     calendar_id: int,
     *     calendar_name: string,
     *     booking_date: string,
     *     booking_start_at: string,
     *     booking_end_at: string,
     *     duration_minutes: int,
     *     unit_price?: int,
     *     nights?: int,
     *     check_in?: string,
     *     check_out?: string,
     * }  $booking
     */
    public function addBooking(Content $content, array $booking): CartItem
    {
        abort_unless(Order::isBookingItemType($content->orderItemType()), 422, 'هذا المحتوى لا يدعم الحجز.');

        $cart = $this->cart();
        $unitPrice = (int) ($booking['unit_price'] ?? data_get($content->data, 'price', 0));

        $lineSignature = CartItem::bookingLineSignature(
            $booking['booking_start_at'],
            $booking['booking_end_at'],
        );

        $duplicate = CartItem::query()
            ->where('cart_id', $cart->id)
            ->where('productable_type', $content->getMorphClass())
            ->where('productable_id', $content->id)
            ->where('line_signature', $lineSignature)
            ->first();

        if ($duplicate instanceof CartItem) {
            return $duplicate;
        }

        $meta = array_merge($this->baseCartMeta($content), [
            'calendar_id' => $booking['calendar_id'],
            'calendar_name' => $booking['calendar_name'],
            'booking_date' => $booking['booking_date'],
            'booking_start_at' => $booking['booking_start_at'],
            'booking_end_at' => $booking['booking_end_at'],
            'duration_minutes' => $booking['duration_minutes'],
        ]);

        if (isset($booking['nights'])) {
            $meta['nights'] = (int) $booking['nights'];
        }

        if (isset($booking['check_in'])) {
            $meta['check_in'] = $booking['check_in'];
        }

        if (isset($booking['check_out'])) {
            $meta['check_out'] = $booking['check_out'];
        }

        $created = CartItem::query()->create([
            'cart_id' => $cart->id,
            'productable_type' => $content->getMorphClass(),
            'productable_id' => $content->id,
            'quantity' => 1,
            'unit_price' => $unitPrice,
            'line_signature' => $lineSignature,
            'meta' => $meta,
        ]);

        $this->forgetItems();

        return $created;
    }

    /**
     * @param  array<string, mixed>  $booking
     */
    public function addServiceBooking(Content $service, array $booking): CartItem
    {
        return $this->addBooking($service, $booking);
    }

    /**
     * @param  array<string, mixed>  $selectedChoices
     */
    public function addMenuItem(Content $meal, int $quantity = 1, array $selectedChoices = []): CartItem
    {
        abort_unless($meal->orderItemType() === 'menu', 422, 'هذا المحتوى ليس صنف طعام.');

        $quantity = max(1, $quantity);
        $cart = $this->cart();
        $resolved = $this->resolveMenuOptions($meal, $selectedChoices);

        $duplicate = CartItem::query()
            ->where('cart_id', $cart->id)
            ->where('productable_type', $meal->getMorphClass())
            ->where('productable_id', $meal->id)
            ->where('line_signature', $resolved['signature'])
            ->first();

        if ($duplicate instanceof CartItem) {
            $duplicate->increment('quantity', $quantity);
            $this->forgetItems();

            return $duplicate->refresh();
        }

        $created = CartItem::query()->create([
            'cart_id' => $cart->id,
            'productable_type' => $meal->getMorphClass(),
            'productable_id' => $meal->id,
            'quantity' => $quantity,
            'unit_price' => $resolved['unit_price'],
            'line_signature' => $resolved['signature'],
            'meta' => array_merge($this->baseCartMeta($meal), [
                'options_signature' => $resolved['signature'],
                'meal_options' => $resolved['selected'],
                'meal_options_label' => $resolved['label'],
            ]),
        ]);

        $this->forgetItems();

        return $created;
    }

    public function bookingRangeIsAvailable(int $calendarId, string $startAt, string $endAt, ?int $excludeItemId = null): bool
    {
        $start = Carbon::parse($startAt);
        $end = Carbon::parse($endAt);

        if ($end->lte($start)) {
            return false;
        }

        $hasDatabaseConflict = Booking::query()
            ->where('calendar_id', $calendarId)
            ->where('status', '!=', 'cancelled')
            ->where('start_at', '<', $end)
            ->where('end_at', '>', $start)
            ->exists();

        if ($hasDatabaseConflict) {
            return false;
        }

        foreach ($this->items() as $item) {
            if ($excludeItemId !== null && $item->id === $excludeItemId) {
                continue;
            }

            if (! $item->isBooking() || $item->calendarId() !== $calendarId) {
                continue;
            }

            $itemStart = Carbon::parse((string) $item->bookingStartAt());
            $itemEnd = Carbon::parse((string) $item->bookingEndAt());

            if ($start->lt($itemEnd) && $end->gt($itemStart)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param  array<string, mixed>  $selectedChoices
     * @return array{signature: string, unit_price: int, selected: array<int, array{group: string, choices: array<int, array{id: string, name: string, price: int}>}>, label: string}
     */
    protected function resolveMenuOptions(Content $meal, array $selectedChoices): array
    {
        $basePrice = (int) data_get($meal->data, 'price', 0);
        $groups = collect(data_get($meal->data, 'meal_options', []))
            ->filter(fn (mixed $group): bool => is_array($group))
            ->values();

        $selected = [];
        $extraPrice = 0;
        $labelParts = [];

        foreach ($groups as $groupIndex => $group) {
            $groupName = (string) ($group['name'] ?? '');
            $groupType = ($group['type'] ?? '') === 'multiple' ? 'multiple' : 'single';
            $choices = collect($group['choices'] ?? [])
                ->filter(fn (mixed $choice): bool => is_array($choice))
                ->keyBy(fn (array $choice): string => (string) ($choice['id'] ?? ''));

            $rawSelection = $selectedChoices[(string) $groupIndex]
                ?? $selectedChoices[$groupIndex]
                ?? null;

            $selectedChoiceIds = match (true) {
                $groupType === 'multiple' && is_array($rawSelection) => collect($rawSelection)->map(fn (mixed $id): string => (string) $id)->all(),
                $groupType === 'multiple' => [],
                filled($rawSelection) => [(string) $rawSelection],
                default => [],
            };

            if (($group['required'] ?? false) && $selectedChoiceIds === []) {
                throw ValidationException::withMessages([
                    'meal_options' => 'يرجى اختيار جميع الخيارات المطلوبة.',
                ]);
            }

            $selectedChoicesForGroup = [];

            foreach ($selectedChoiceIds as $choiceId) {
                $choice = $choices->get($choiceId);

                if (! is_array($choice)) {
                    continue;
                }

                $choicePrice = (int) ($choice['price'] ?? 0);
                $choiceName = (string) ($choice['name'] ?? '');
                $extraPrice += $choicePrice;
                $selectedChoicesForGroup[] = [
                    'id' => $choiceId,
                    'name' => $choiceName,
                    'price' => $choicePrice,
                ];
                $labelParts[] = $groupName.': '.$choiceName;
            }

            if ($selectedChoicesForGroup !== []) {
                $selected[] = [
                    'group' => $groupName,
                    'choices' => $selectedChoicesForGroup,
                ];
            }
        }

        $signature = sha1(json_encode([
            'meal_id' => $meal->id,
            'choices' => $selected,
        ]));

        return [
            'signature' => $signature,
            'unit_price' => $basePrice + $extraPrice,
            'selected' => $selected,
            'label' => implode(' · ', $labelParts),
        ];
    }

    /**
     * @return list<array{start_at: string, end_at: string}>
     */
    public function reservedBookingSlots(?int $calendarId, string $bookingDate, ?int $excludeItemId = null): array
    {
        if ($calendarId === null || $calendarId <= 0 || $bookingDate === '') {
            return [];
        }

        return $this->items()
            ->filter(function (CartItem $item) use ($calendarId, $bookingDate, $excludeItemId): bool {
                if ($excludeItemId !== null && $item->id === $excludeItemId) {
                    return false;
                }

                if (! $item->isBooking() || $item->calendarId() !== $calendarId) {
                    return false;
                }

                $startAt = $item->bookingStartAt();

                return filled($startAt)
                    && Carbon::parse($startAt)->toDateString() === $bookingDate;
            })
            ->map(fn (CartItem $item): array => [
                'start_at' => (string) $item->bookingStartAt(),
                'end_at' => (string) $item->bookingEndAt(),
            ])
            ->values()
            ->all();
    }

    public function updateQuantity(int $itemId, int $quantity): void
    {
        $item = $this->findOwnedItem($itemId);

        if ($quantity < 1) {
            $item->delete();
            $this->forgetItems();

            return;
        }

        if ($item->isBooking()) {
            return;
        }

        $item->update(['quantity' => $quantity]);
        $this->forgetItems();
    }

    public function removeItem(int $itemId): void
    {
        $this->findOwnedItem($itemId)->delete();
        $this->forgetItems();
    }

    public function clear(): void
    {
        CartItem::query()
            ->where('cart_id', $this->cart()->id)
            ->delete();
        $this->forgetItems();
    }

    /**
     * @param  array{name: string, phone: string, email?: string|null, address?: string|null, country?: string|null, city_id?: string|null, neighborhood?: string|null}  $customer
     * @param  array<string, mixed>  $paymentMeta
     */
    public function checkout(array $customer, string $shippingMethod, ?string $paymentMethod, array $paymentMeta = []): Order
    {
        $items = $this->items();

        abort_if($items->isEmpty(), 422, 'السلة فارغة.');

        $tenantId = currentTenantId();
        abort_unless($tenantId, 404);

        $subtotal = $this->subtotal();
        $requiresShipping = $this->requiresShipping();
        $effectiveShippingMethod = $requiresShipping ? $shippingMethod : 'none';
        $shippingAddress = $requiresShipping
            ? $this->shipping->normalizeAddress($customer)
            : [];
        $shippingFee = $this->shippingFee($effectiveShippingMethod, $shippingAddress);
        $grandTotal = $subtotal + $shippingFee;
        $shippingMethodLabel = $requiresShipping
            ? $this->shipping->label($effectiveShippingMethod)
            : null;

        $this->validateBookingItems($items);

        return DB::transaction(function () use ($items, $customer, $effectiveShippingMethod, $paymentMethod, $paymentMeta, $tenantId, $subtotal, $shippingFee, $grandTotal, $shippingAddress, $shippingMethodLabel): Order {
            $client = $this->resolveCheckoutClient($customer, $tenantId);
            $isFreeOrder = $grandTotal <= 0;

            $order = Order::create([
                'tenant_id' => $tenantId,
                'type' => 'order',
                'status' => 'open',
                'channel' => 'ecommerce',
                'number' => $this->generateOrderNumber($tenantId),
                'client_id' => $client->id,
                'currency_code' => Money::defaultCurrencyCode(),
                'subtotal' => $subtotal,
                'discount_total' => 0,
                'tax_total' => 0,
                'grand_total' => $grandTotal,
                'paid_total' => 0,
                'due_total' => $isFreeOrder ? 0 : $grandTotal,
                'payment_status' => $isFreeOrder ? 'paid' : 'unpaid',
                'issued_at' => now(),
                'notes' => null,
                'financial_status' => 'open',
                'fulfillment_status' => 'unfulfilled',
                'meta' => array_merge([
                    'payment_method' => $paymentMethod,
                    'shipping_method' => $effectiveShippingMethod,
                    'shipping_method_label' => $shippingMethodLabel,
                    'shipping_fee' => $shippingFee,
                    'shipping_address' => $shippingAddress !== [] ? $shippingAddress : null,
                    'source' => 'store_cart',
                ], $paymentMeta),
            ]);

            $this->createOrderItemsFromCart($order, $items, $client, $tenantId);
            $this->clear();

            SendOrderConfirmationEmail::run($order);

            if ($paymentMethod !== 'credit-card') {
                SendNewOrderNotificationEmail::run($order);
            }

            return $order;
        });
    }

    /**
     * @param  Collection<int, CartItem>  $items
     */
    protected function createOrderItemsFromCart(Order $order, Collection $items, Client $client, int $tenantId): void
    {
        foreach ($items as $item) {
            $productId = $item->productable_type === Content::class
                ? $item->productable_id
                : null;

            $bookingId = null;
            $orderItemMeta = [
                'type' => $item->itemType(),
                'slug' => $item->slug(),
                'image_url' => $item->imageUrl(),
            ];

            if ($item->isBooking()) {
                $booking = Booking::query()->create([
                    'tenant_id' => $tenantId,
                    'client_id' => $client->id,
                    'order_id' => $order->id,
                    'content_id' => $productId,
                    'calendar_id' => $item->calendarId(),
                    'start_at' => $item->bookingStartAt(),
                    'end_at' => $item->bookingEndAt(),
                    'status' => 'new',
                    'price_snapshot' => Order::fromMinor($item->unit_price),
                    'currency' => Money::defaultCurrencyCode(),
                    'meta' => [
                        'order_channel' => 'ecommerce',
                    ],
                ]);

                $bookingId = $booking->id;
                $orderItemMeta['booking_id'] = $bookingId;
                $orderItemMeta['calendar_id'] = $item->calendarId();
                $orderItemMeta['booking_start_at'] = $item->bookingStartAt();
                $orderItemMeta['booking_end_at'] = $item->bookingEndAt();
            }

            DB::table('order_items')->insert([
                'order_id' => $order->id,
                'product_id' => $productId,
                'booking_id' => $bookingId,
                'name' => $item->title(),
                'qty' => $item->quantity,
                'unit_price' => $item->unit_price,
                'discount_total' => 0,
                'tax_total' => 0,
                'line_total' => $item->lineTotal(),
                'meta' => json_encode($orderItemMeta),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * @param  array{name: string, phone: string, email?: string|null}  $customer
     */
    protected function resolveCheckoutClient(array $customer, int $tenantId): Client
    {
        $tenant = Tenant::query()->findOrFail($tenantId);
        $authenticatedClient = auth('client')->user();

        if ($authenticatedClient instanceof Client) {
            app(ClientAuthService::class)->linkClientToTenant($authenticatedClient, $tenant, [
                'name' => $customer['name'],
                'email' => $customer['email'] ?? $authenticatedClient->email,
                'phone' => $customer['phone'],
            ]);

            if (filled($customer['phone']) && $authenticatedClient->phone !== $customer['phone']) {
                $authenticatedClient->update(['phone' => $customer['phone']]);
            }

            return $authenticatedClient->fresh();
        }

        $client = Client::withoutGlobalScope('tenantable')->firstOrCreate(
            ['phone' => $customer['phone']],
            [
                'name' => $customer['name'],
                'phone' => $customer['phone'],
                'email' => $customer['email'] ?? null,
                'tenant_id' => $tenantId,
            ],
        );

        $client->tenants()->sync([
            $tenantId => [
                'active' => true,
                'meta' => [
                    'name' => $customer['name'],
                    'email' => $customer['email'] ?? null,
                    'phone' => $customer['phone'],
                ],
            ],
        ], false);

        return $client;
    }

    protected function findOwnedItem(int $itemId): CartItem
    {
        return CartItem::query()
            ->where('cart_id', $this->cart()->id)
            ->whereKey($itemId)
            ->firstOrFail();
    }

    /**
     * @param  Collection<int, CartItem>  $items
     */
    protected function validateBookingItems(Collection $items): void
    {
        /** @var list<array{calendar_id: int, start_at: string, end_at: string}> $selections */
        $selections = [];

        foreach ($items as $item) {
            if (! $item->isBooking()) {
                continue;
            }

            $calendarId = (int) ($item->calendarId() ?? 0);
            $startAt = $item->bookingStartAt();
            $endAt = $item->bookingEndAt();
            $bookingDate = (string) data_get($item->meta, 'booking_date', '');

            if ($calendarId <= 0 || blank($startAt) || blank($endAt) || $bookingDate === '') {
                throw ValidationException::withMessages([
                    'cart' => 'يوجد حجز غير مكتمل في السلة.',
                ]);
            }

            if ($item->itemType() === 'unit_rental') {
                if (! $this->bookingRangeIsAvailable($calendarId, (string) $startAt, (string) $endAt, $item->id)) {
                    throw ValidationException::withMessages([
                        'cart' => 'فترة تأجير الوحدة لم تعد متاحة.',
                    ]);
                }

                $this->assertNoSelectionOverlap($selections, $calendarId, (string) $startAt, (string) $endAt);

                $selections[] = [
                    'calendar_id' => $calendarId,
                    'start_at' => $startAt,
                    'end_at' => $endAt,
                ];

                continue;
            }

            $calendar = Calendar::query()->find($calendarId);

            if (! $calendar) {
                throw ValidationException::withMessages([
                    'cart' => 'التقويم المرتبط بأحد الحجوزات غير متاح.',
                ]);
            }

            $durationMinutes = max(1, (int) data_get($item->meta, 'duration_minutes', 60));

            $reservedSlots = collect($selections)
                ->filter(fn (array $slot): bool => $slot['calendar_id'] === $calendarId
                    && Carbon::parse($slot['start_at'])->toDateString() === $bookingDate)
                ->map(fn (array $slot): array => [
                    'start_at' => $slot['start_at'],
                    'end_at' => $slot['end_at'],
                ])
                ->values()
                ->all();

            $slot = collect($this->calendarSlots->availableTimeSlots(
                $calendar,
                $bookingDate,
                $durationMinutes,
                'slot',
                $reservedSlots,
            ))->first(fn (array $candidate): bool => ($candidate['start_at'] ?? '') === $startAt
                && ($candidate['end_at'] ?? '') === $endAt);

            if (! is_array($slot) || ! ($slot['available'] ?? false)) {
                throw ValidationException::withMessages([
                    'cart' => 'أحد مواعيد الحجز في السلة لم يعد متاحاً.',
                ]);
            }

            $this->assertNoSelectionOverlap($selections, $calendarId, (string) $startAt, (string) $endAt);

            $selections[] = [
                'calendar_id' => $calendarId,
                'start_at' => $startAt,
                'end_at' => $endAt,
            ];
        }
    }

    /**
     * @param  list<array{calendar_id: int, start_at: string, end_at: string}>  $selections
     */
    protected function assertNoSelectionOverlap(array $selections, int $calendarId, string $startAt, string $endAt): void
    {
        $start = Carbon::parse($startAt);
        $end = Carbon::parse($endAt);

        foreach ($selections as $existing) {
            if ($existing['calendar_id'] !== $calendarId) {
                continue;
            }

            $existingStart = Carbon::parse($existing['start_at']);
            $existingEnd = Carbon::parse($existing['end_at']);

            if ($start->lt($existingEnd) && $end->gt($existingStart)) {
                throw ValidationException::withMessages([
                    'cart' => 'يوجد تعارض بين مواعيد الحجز في السلة.',
                ]);
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
}
