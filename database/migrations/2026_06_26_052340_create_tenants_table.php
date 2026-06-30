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
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('theme_id')->nullable(); // one theme for all apps
            $table->string('handle')->unique()->index();
            $table->string('name');
            $table->boolean('active')->default(true);
            $table->jsonb('data')->nullable();
            $table->jsonb('meta')->nullable();
            $table->jsonb('config')->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('email', 255)->nullable();
            $table->string('status', 255)->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }
};
