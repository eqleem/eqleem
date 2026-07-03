<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('calendars', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('tenant_id')->nullable();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('name');
            $table->string('type', 100)->default('provider');
            $table->string('timezone', 30)->default('UTC'); // provider, tool , unit etc ..
            $table->date('from')->nullable();
            $table->date('to')->nullable();
            $table->boolean('active')->default(true);
            $table->boolean('forever')->default(false); // 24/7
            $table->json('availabilities')->nullable(); // [sun : [8:00-4:30, 6:00-9:00], mon[], tue: false, wed, thu, fri, sat]
            $table->json('special_dates')->nullable(); // [12/5/2024 : [8:00-4:30, 6:00-9:00], 12/6/2024 : [8:00-4:30, 6:00-9:00]]
            $table->json('off_dates')->nullable(); // [1/5/2024, 3/3/205]
            $table->json('meta')->nullable();
            $table->smallInteger('clients_number')->default(1);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('bookables', function (Blueprint $table) {
            $table->foreignId('calendar_id')->constrained()->cascadeOnDelete();
            $table->morphs('bookable');
            $table->string('type', 100)->nullable();
            $table->unique(['calendar_id', 'bookable_id', 'bookable_type']);
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

    }
};
