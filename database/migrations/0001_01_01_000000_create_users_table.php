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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('current_tenant_id')->nullable()->index();
            $table->string('name');
            $table->string('username', 150)->nullable()->unique();
            $table->string('email')->unique();
            $table->string('image')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password')->nullable();
            $table->string('phone', 14)->nullable()->unique();
            $table->boolean('active')->default(true);
            $table->jsonb('data')->nullable();
            $table->jsonb('meta')->nullable();
            $table->jsonb('config')->nullable();
            $table->rememberToken();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });

        Schema::create('users_social', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedBigInteger('user_id');
            $table->string('provider');
            $table->string('provider_id');
            $table->text('provider_token')->nullable();
            $table->string('provider_refresh_token')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'provider']);
        });

        Schema::create('registration_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->string('code')->nullable();
            $table->timestamp('created_at')->nullable();
        });

    }
};
