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
        Schema::create('themes', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique()->index();
            $table->unsignedBigInteger('tenant_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('app_id')->nullable();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->string('name')->nullable();
            $table->string('slug')->nullable()->unique();
            $table->string('type', 100)->nullable()->default('all')->index();
            $table->string('app', 100)->nullable()->default('all')->index(); // all / wjeez / 360 / tahweal
            $table->json('meta')->nullable();
            $table->json('config')->nullable();
            $table->boolean('active')->default(true);
            $table->boolean('public')->default(true);
            $table->unsignedSmallInteger('sort')->default(1000)->index();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['slug', 'app', 'type']);
        });
    }
 
};
