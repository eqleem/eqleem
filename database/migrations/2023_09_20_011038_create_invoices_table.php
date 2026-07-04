<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->nullableMorphs('invoicable');
            $table->string('number', 24)->nullable();
            $table->uuid('uuid')->nullable();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->string('prefixed_number')->unique()->nullable();
            $table->unsignedBigInteger('tenant_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('member_id')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('created_by_type')->nullable();

            $table->integer('amount_paid')->default(0);
            $table->integer('total_before_vat')->default(0);
            $table->integer('total_after_vat')->default(0);
            $table->integer('subtotal_before_vat')->default(0);
            $table->integer('subtotal_after_vat')->default(0);
            $table->string('currency', 3)->default('SAR');

            $table->integer('vat_amount')->default(0);
            $table->string('type', 80)->default('sell'); // sell or purchase
            $table->string('initial_status', 40)->default('draft'); // or issued

            $table->datetime('issued_on')->nullable();
            $table->datetime('due_on')->nullable();
            $table->datetime('paid_on')->nullable();
            $table->datetime('cancelled_on')->nullable();

            $table->text('note')->nullable();
            $table->text('conditions')->nullable();
            $table->json('meta')->nullable();

            $table->text('receiver_info')->nullable();
            $table->text('sender_info')->nullable();
            $table->text('payment_info')->nullable();

            $table->softDeletes();
            $table->unique(['number', 'tenant_id']);
            $table->timestamps();
        });

        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();
            $table->nullableMorphs('invoicable');
            $table->unsignedBigInteger('invoice_id');
            $table->json('model_info')->nullable();
            $table->string('name')->nullable();
            $table->string('currency', 3)->default('SAR');
            $table->string('type', 40)->default('item');

            $table->integer('amount_before_vat')->default(0)->description('in cents');
            $table->integer('amount_after_vat')->default(0)->description('in cents');
            $table->integer('total_before_vat')->default(0)->description('in cents');
            $table->integer('total_after_vat')->default(0)->description('in cents');
            $table->integer('vat_percentage')->default(0)->nullable();
            $table->integer('vat_amount')->default(0)->nullable();
            $table->integer('discount_percentage')->default(0)->nullable();
            $table->integer('discount_amount')->default(0)->nullable();
            $table->integer('quantity')->default(1);
            $table->text('note')->nullable();
            $table->string('initial_status', 100)->default('pending');
            $table->json('meta')->nullable();
            $table->timestamps();
        });
    }
};
