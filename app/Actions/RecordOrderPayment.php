<?php

namespace App\Actions;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\Concerns\AsAction;

class RecordOrderPayment
{
    use AsAction;

    public function handle(Order $order, int $amountMinor, string $paymentMethod, ?string $notes = null): Payment
    {
        if ($amountMinor <= 0) {
            throw ValidationException::withMessages([
                'paymentAmount' => 'يجب أن يكون المبلغ أكبر من صفر.',
            ]);
        }

        return DB::transaction(function () use ($order, $amountMinor, $paymentMethod, $notes): Payment {
            $order = Order::query()->lockForUpdate()->findOrFail($order->id);

            if ($amountMinor > $order->due_total) {
                throw ValidationException::withMessages([
                    'paymentAmount' => 'المبلغ يتجاوز المتبقي على الطلب.',
                ]);
            }

            $dueTotalAfterPayment = max(0, $order->grand_total - ($order->paid_total + $amountMinor));

            $invoice = Invoice::create([
                'invoicable_type' => Order::class,
                'invoicable_id' => $order->id,
                'user_id' => auth()->id(),
                'created_by' => auth()->id(),
                'amount_paid' => $amountMinor,
                'total_before_vat' => $order->grand_total - $order->tax_total,
                'total_after_vat' => $order->grand_total,
                'subtotal_before_vat' => $order->subtotal,
                'subtotal_after_vat' => max(0, $order->subtotal - $order->discount_total),
                'currency' => $order->currency_code,
                'type' => 'sell',
                'initial_status' => $dueTotalAfterPayment <= 0 ? 'paid' : 'issued',
                'issued_on' => now(),
                'paid_on' => $dueTotalAfterPayment <= 0 ? now() : null,
                'note' => $notes,
            ]);

            $orderItems = DB::table('order_items')
                ->where('order_id', $order->id)
                ->orderBy('id')
                ->get();

            foreach ($orderItems as $orderItem) {
                InvoiceItem::insertFromOrderItem($invoice->id, $orderItem, $order->currency_code);
            }

            $payment = Payment::create([
                'tenant_id' => $order->tenant_id,
                'user_id' => auth()->id(),
                'client_id' => $order->client_id,
                'order_id' => $order->id,
                'purchased_id' => $order->id,
                'purchased_type' => Order::class,
                'paymentable_type' => Invoice::class,
                'paymentable_id' => $invoice->id,
                'amount' => $amountMinor,
                'invoice_id' => $invoice->id,
                'currency' => $order->currency_code,
                'initial_status' => 'paid',
                'type' => 'payment',
                'gateway' => 'manual',
                'reason' => 'client-buy-from-tenant',
                'description' => $notes ?: 'دفعة للطلب #'.($order->number ?? $order->id),
                'source_type' => $paymentMethod,
            ]);

            $paidTotal = $order->paid_total + $amountMinor;
            $dueTotal = max(0, $order->grand_total - $paidTotal);

            $order->update([
                'paid_total' => $paidTotal,
                'due_total' => $dueTotal,
                'payment_status' => $this->resolvePaymentStatus($paidTotal, $dueTotal),
            ]);

            return $payment;
        });
    }

    protected function resolvePaymentStatus(int $paidTotal, int $dueTotal): string
    {
        if ($paidTotal <= 0) {
            return 'unpaid';
        }

        if ($dueTotal <= 0) {
            return 'paid';
        }

        return 'partial';
    }
}
