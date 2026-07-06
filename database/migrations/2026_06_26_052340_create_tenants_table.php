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
            $table->string('custom_domain', 255)->nullable()->unique();
            $table->string('custom_domain_status', 50)->default('pending')->nullable();
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

        Schema::create('tenantables', function (Blueprint $table) {
            $table->bigInteger('tenant_id')->unsigned();
            $table->morphs('tenantable');
            $table->boolean('active')->default(true);
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->unique(['tenant_id', 'tenantable_id', 'tenantable_type'], 'tenantables_ids_type_unique');
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade')->onUpdate('cascade');
        });
    }
};
