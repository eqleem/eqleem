<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->rememberToken()->after('active');
            $table->timestamp('email_verified_at')->nullable()->after('email');
        });

        Schema::create('client_login_codes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('email');
            $table->string('code');
            $table->timestamp('expires_at');
            $table->timestamp('created_at')->nullable();

            $table->index(['email', 'tenant_id']);
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

    public function down(): void
    {
        Schema::dropIfExists('clients_social');
        Schema::dropIfExists('client_login_codes');

        Schema::table('clients', function (Blueprint $table) {
            $table->dropRememberToken();
            $table->dropColumn('email_verified_at');
        });
    }
};
