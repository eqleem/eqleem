<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {

        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('tenant_id')->nullable()->index()->constrained()->nullOnDelete();
            $table->string('type', 32)->index()->default('order')->comment('quote, order, invoice, return, receipt');
            $table->string('status', 40)->index()->comment('draft, open, confirmed, partially_paid, paid, void, cancelled, completed, etc.');
            $table->string('channel', 32)->index()->comment('ecommerce, pos, manual, api');
            $table->string('number', 64)->nullable()->index();
            $table->string('number_sequence', 32)->nullable()->comment('Per-series sequence key for atomic numbering');
            $table->unsignedBigInteger('client_id')->nullable()->index();
            $table->char('currency_code', 3)->default('SAR');
            $table->unsignedBigInteger('subtotal')->default(0);
            $table->unsignedBigInteger('discount_total')->default(0);
            $table->unsignedBigInteger('tax_total')->default(0);
            $table->unsignedBigInteger('grand_total')->default(0);
            $table->unsignedBigInteger('paid_total')->default(0);
            $table->unsignedBigInteger('due_total')->default(0);
            $table->string('payment_status', 32)->default('unpaid')->index()->comment('unpaid, partial, paid, refunded, overpaid');
            $table->timestamp('issued_at')->nullable()->index();
            $table->unsignedBigInteger('created_by')->nullable()->index();
            $table->unsignedBigInteger('register_shift_id')->nullable()->index();
            $table->unsignedBigInteger('branch_id')->nullable()->index();
            $table->text('notes')->nullable();

            $table->string('financial_status', 64)->default('draft')->index();
            $table->string('fulfillment_status', 64)->default('unfulfilled')->index();

            $table->json('meta')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'type', 'status']);
            $table->index(['client_id', 'issued_at']);
            $table->unique(['tenant_id', 'number', 'type']);
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id')->nullable()->index();
            $table->unsignedBigInteger('product_id')->nullable()->index();
            $table->unsignedBigInteger('variant_id')->nullable()->index();
            $table->string('sku', 120)->nullable()->index();
            $table->string('name');
            $table->unsignedInteger('qty')->default(1)->comment('Base sellable units; use metadata for decimals if needed');
            $table->unsignedBigInteger('unit_price')->default(0);
            $table->unsignedBigInteger('discount_total')->default(0);
            $table->unsignedBigInteger('tax_total')->default(0);
            $table->unsignedBigInteger('line_total')->default(0);
            $table->unsignedBigInteger('tax_id')->nullable()->index();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['order_id', 'product_id']);
        });
    }
};
