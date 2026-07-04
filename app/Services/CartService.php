<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Client;
use App\Models\Content;
use App\Models\Order;
use App\Models\Tenant;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class CartService
{
    protected ?Cart $resolvedCart = null;

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
                $existingItem = CartItem::query()
                    ->where('cart_id', $clientCart->id)
                    ->where('productable_type', $guestItem->productable_type)
                    ->where('productable_id', $guestItem->productable_id)
                    ->first();

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
        return CartItem::query()
            ->where('cart_id', $this->cart()->id)
            ->with('productable')
            ->orderBy('id')
            ->get();
    }

    public function subtotal(): int
    {
        return $this->items()->sum(fn (CartItem $item): int => $item->lineTotal());
    }

    public function shippingFee(string $shippingMethod): int
    {
        return match ($shippingMethod) {
            'pickup' => 0,
            default => 3500,
        };
    }

    public function grandTotal(string $shippingMethod): int
    {
        return $this->subtotal() + $this->shippingFee($shippingMethod);
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
            ->first();

        if ($item) {
            $item->increment('quantity', $quantity);

            return $item->refresh();
        }

        return CartItem::query()->create([
            'cart_id' => $cart->id,
            'productable_type' => $content->getMorphClass(),
            'productable_id' => $content->id,
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'meta' => [
                'item_type' => $content->orderItemType(),
                'title' => $content->title,
                'slug' => $content->slug,
                'image_url' => $content->cartImageUrl(),
            ],
        ]);
    }

    public function updateQuantity(int $itemId, int $quantity): void
    {
        $item = $this->findOwnedItem($itemId);

        if ($quantity < 1) {
            $item->delete();

            return;
        }

        $item->update(['quantity' => $quantity]);
    }

    public function removeItem(int $itemId): void
    {
        $this->findOwnedItem($itemId)->delete();
    }

    public function clear(): void
    {
        CartItem::query()
            ->where('cart_id', $this->cart()->id)
            ->delete();
    }

    /**
     * @param  array{name: string, phone: string, email?: string|null}  $customer
     */
    public function checkout(array $customer, string $shippingMethod, string $paymentMethod): Order
    {
        $items = $this->items();

        abort_if($items->isEmpty(), 422, 'السلة فارغة.');

        $tenantId = currentTenantId();
        abort_unless($tenantId, 404);

        $subtotal = $this->subtotal();
        $shippingFee = $this->shippingFee($shippingMethod);
        $grandTotal = $subtotal + $shippingFee;

        return DB::transaction(function () use ($items, $customer, $shippingMethod, $paymentMethod, $tenantId, $subtotal, $shippingFee, $grandTotal): Order {
            $client = $this->resolveCheckoutClient($customer, $tenantId);

            $order = Order::create([
                'tenant_id' => $tenantId,
                'type' => 'order',
                'status' => 'open',
                'channel' => 'ecommerce',
                'number' => $this->generateOrderNumber($tenantId),
                'client_id' => $client->id,
                'currency_code' => 'SAR',
                'subtotal' => $subtotal,
                'discount_total' => 0,
                'tax_total' => 0,
                'grand_total' => $grandTotal,
                'paid_total' => 0,
                'due_total' => $grandTotal,
                'payment_status' => 'unpaid',
                'issued_at' => now(),
                'notes' => null,
                'financial_status' => 'open',
                'fulfillment_status' => 'unfulfilled',
                'meta' => [
                    'payment_method' => $paymentMethod,
                    'shipping_method' => $shippingMethod,
                    'shipping_fee' => $shippingFee,
                    'source' => 'store_cart',
                ],
            ]);

            foreach ($items as $item) {
                $productId = $item->productable_type === Content::class
                    ? $item->productable_id
                    : null;

                DB::table('order_items')->insert([
                    'order_id' => $order->id,
                    'product_id' => $productId,
                    'name' => $item->title(),
                    'qty' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'discount_total' => 0,
                    'tax_total' => 0,
                    'line_total' => $item->lineTotal(),
                    'meta' => json_encode([
                        'type' => $item->itemType(),
                        'slug' => $item->slug(),
                        'image_url' => $item->imageUrl(),
                    ]),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $this->clear();

            return $order;
        });
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
