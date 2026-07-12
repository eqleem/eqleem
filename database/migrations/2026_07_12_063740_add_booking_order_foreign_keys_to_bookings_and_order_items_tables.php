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
        Schema::table('bookings', function (Blueprint $table) {
            $table->unsignedBigInteger('order_id')->nullable()->index()->after('client_id');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->unsignedBigInteger('booking_id')->nullable()->index()->after('product_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropIndex(['order_id']);
            $table->dropColumn('order_id');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->dropIndex(['booking_id']);
            $table->dropColumn('booking_id');
        });
    }
};
