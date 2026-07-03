<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('client_id')->nullable();
            $table->unsignedBigInteger('order_id')->nullable();
            $table->unsignedBigInteger('purchased_id')->nullable();
            $table->string('purchased_type')->nullable();
            $table->nullableMorphs('paymentable');

            $table->unsignedBigInteger('from_id')->nullable();
            $table->string('from_type')->nullable();

            $table->unsignedBigInteger('to_id')->nullable();
            $table->string('to_type')->nullable();

            $table->string('reason')->nullable(); // tenant-subscribe-to-plan, client-buy-from-tenant, tenant-client-subscribe-to-tenant-plan, etc

            $table->string('payment_id')->nullable();
            $table->unsignedInteger('amount')->nullable();
            $table->unsignedInteger('amount_long')->nullable();
            $table->unsignedInteger('invoice_id')->nullable();
            $table->string('ip')->nullable();
            $table->string('type', 50)->nullable();
            $table->boolean('captured')->default(false);
            $table->string('gateway_id')->nullable();
            $table->string('gateway')->nullable();
            $table->string('initial_status', 50)->nullable();
            $table->char('currency', 3)->default('SAR');
            $table->string('description')->nullable();
            $table->string('source_number', 50)->nullable();
            $table->string('source_name')->nullable();
            $table->unsignedSmallInteger('source_expiry_year')->nullable();
            $table->unsignedSmallInteger('source_expiry_month')->nullable();
            $table->unsignedInteger('source_last_four')->nullable();
            $table->string('source_type', 30)->nullable();
            $table->string('source_company', 30)->nullable();
            $table->json('meta')->nullable();
            $table->datetime('refunded_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

};