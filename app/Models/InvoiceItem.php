<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class InvoiceItem extends Model
{
    use HasUuid;

    protected $fillable = [
        'invoice_id',
        'invoicable_type',
        'invoicable_id',
        'model_info',
        'name',
        'currency',
        'type',
        'amount_before_vat',
        'amount_after_vat',
        'total_before_vat',
        'total_after_vat',
        'discount_amount',
        'vat_amount',
        'quantity',
        'initial_status',
        'note',
        'meta',
    ];

    public $casts = [
        'model_info' => 'json',
        'meta' => 'json',
    ];

    /**
     * @param  object{id: int, product_id: ?int, sku: ?string, name: string, qty: int, unit_price: int, discount_total: int, tax_total: int, line_total: int, meta: ?string}  $orderItem
     */
    public static function insertFromOrderItem(int $invoiceId, object $orderItem, string $currency): void
    {
        $meta = is_string($orderItem->meta ?? null)
            ? json_decode($orderItem->meta, true)
            : (array) ($orderItem->meta ?? []);

        $type = $meta['type'] ?? 'item';
        $productId = $orderItem->product_id ?? null;

        DB::table('invoice_items')->insert([
            'invoice_id' => $invoiceId,
            'invoicable_type' => $productId ? Content::class : null,
            'invoicable_id' => $productId,
            'model_info' => json_encode([
                'order_item_id' => $orderItem->id,
                'product_id' => $productId,
                'sku' => $orderItem->sku ?? null,
                'type' => $type,
            ]),
            'name' => $orderItem->name,
            'currency' => $currency,
            'type' => $type,
            'amount_before_vat' => $orderItem->unit_price,
            'amount_after_vat' => $orderItem->unit_price,
            'total_before_vat' => $orderItem->line_total,
            'total_after_vat' => $orderItem->line_total,
            'discount_amount' => $orderItem->discount_total,
            'vat_amount' => $orderItem->tax_total,
            'quantity' => $orderItem->qty,
            'initial_status' => 'paid',
            'meta' => is_string($orderItem->meta ?? null) ? $orderItem->meta : json_encode($meta),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
