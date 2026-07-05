<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique()->index();
            $table->foreignId('tenant_id')->nullable()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->nullOnDelete();
            $table->string('name');
            $table->string('email')->unique()->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('phone')->nullable();
            $table->string('national_id')->nullable();
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('neighborhood')->nullable();
            $table->text('notes')->nullable();
            $table->json('meta')->nullable();
            $table->boolean('active')->default(true);
            $table->rememberToken();
            $table->timestamps();

            $table->index('tenant_id');
            $table->index('user_id');
        });

        Schema::create('client_login_codes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id');
            $table->string('email');
            $table->string('code');
            $table->timestamp('expires_at');
            $table->timestamp('created_at')->nullable();

            $table->index(['email', 'tenant_id']);
            $table->index('tenant_id');
        });

        Schema::create('clients_social', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->string('provider');
            $table->string('provider_id');
            $table->text('provider_token')->nullable();
            $table->string('provider_refresh_token')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->unique(['provider', 'provider_id']);
            $table->index(['client_id', 'provider']);
        });
    }
};
