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
        Schema::create('branches', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->nullable();
            $table->unsignedBigInteger('city_id')->nullable();
            $table->json('name');
            $table->string('city', 120)->nullable();
            $table->string('country', 3)->default('SA')->nullable();
            $table->string('lat')->nullable();
            $table->string('long')->nullable();
            $table->string('type')->nullable();
            $table->string('address')->nullable();
            $table->string('street')->nullable();
            $table->string('district')->nullable();
            $table->string('building_number')->nullable();
            $table->string('extra_number')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('phone', 14)->nullable();
            $table->string('phonecode', 6)->nullable();
            $table->string('email', 150)->nullable();
            $table->boolean('active')->default(true);
            $table->boolean('is_warehouse')->default(true);
            $table->boolean('is_pickup')->default(true);
            $table->json('config')->nullable();
            $table->unsignedSmallInteger('order')->default(100);
            $table->softDeletes();
            $table->timestamps();

            $table->index('tenant_id');

            $table->index('city_id');
            $table->index('type');
            $table->index('active');
            $table->index('is_warehouse');
            $table->index('is_pickup');
        });
    }
};
