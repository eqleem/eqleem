<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use LucasDotVin\Soulbscription\Models\Feature;
use LucasDotVin\Soulbscription\Models\Plan;
use LucasDotVin\Soulbscription\Models\Subscription;

return new class extends Migration
{
    public function up()
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->nullable();
            $table->boolean('is_system')->default(false);
            $table->integer('grace_days')->default(0);
            $table->string('name');
            $table->string('label')->nullable();
            $table->string('slug')->nullable();
            $table->integer('price')->default(0);
            $table->unsignedInteger('periodicity')->nullable();
            $table->string('periodicity_type')->nullable();

            $table->boolean('active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->json('meta')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Plan::class);
            // $table->unsignedBigInteger('plan_id');
            $table->unsignedBigInteger('tenant_id')->nullable();
            $table->boolean('is_system')->default(false);
            $table->timestamp('canceled_at')->nullable();
            $table->timestamp('expired_at')->nullable();
            $table->timestamp('grace_days_ended_at')->nullable();
            $table->date('started_at');
            $table->timestamp('suppressed_at')->nullable();
            $table->boolean('was_switched')->default(false);
            $table->json('meta')->nullable();
            $table->softDeletes();
            $table->timestamps();
            $table->numericMorphs('subscriber');
        });

        Schema::create('subscription_renewals', function (Blueprint $table) {
            $table->id();
            $table->boolean('overdue');
            $table->boolean('renewal');
            $table->foreignIdFor(Subscription::class);
            // $table->unsignedBigInteger('subscription_id');
            $table->timestamps();
        });

        Schema::create('features', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->nullable();
            $table->string('name');
            $table->boolean('consumable')->default(false);
            $table->boolean('quota')->default(false);
            $table->boolean('postpaid')->default(false);
            $table->unsignedInteger('periodicity')->nullable();
            $table->string('periodicity_type')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('feature_consumptions', function (Blueprint $table) {
            $table->id();
            $table->decimal('consumption')->nullable();
            $table->timestamp('expired_at')->nullable();
            // $table->unsignedBigInteger('feature_id');
            $table->foreignIdFor(Feature::class)->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->numericMorphs('subscriber');
        });

        Schema::create('feature_plan', function (Blueprint $table) {
            $table->id();
            $table->decimal('charges')->nullable();
            // $table->unsignedBigInteger('feature_id');
            // $table->unsignedBigInteger('plan_id');
            $table->foreignIdFor(Feature::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Plan::class)->constrained()->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('feature_tickets', function (Blueprint $table) {
            $table->id();
            $table->decimal('charges')->nullable();
            $table->timestamp('expired_at')->nullable();
            // $table->unsignedBigInteger('feature_id');
            $table->foreignIdFor(Feature::class)->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->numericMorphs('subscriber');
        });
    }
};
