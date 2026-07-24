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
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->nullable();
            $table->unsignedBigInteger('client_id')->nullable();
            $table->unsignedBigInteger('order_id')->nullable()->index();
            $table->unsignedBigInteger('content_id')->nullable();
            $table->unsignedBigInteger('calendar_id')->nullable();
            $table->dateTime('start_at');
            $table->dateTime('end_at');
            $table->string('status')->default('new');
            $table->json('data')->nullable();
            $table->decimal('price_snapshot', 10, 2)->nullable();
            $table->string('currency', 3)->default('SAR');
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index('tenant_id');
            $table->index('calendar_id');
            $table->index('start_at');
            $table->index(['tenant_id', 'start_at']);
        });
    }
};
