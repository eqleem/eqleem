<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'world';

    public function up(): void
    {
        if (DB::connection()->getName() != 'world') {
            return;
        }

        if (Schema::hasTable('countries')) {
            return;
        }

        Schema::connection('world')->create('countries', function (Blueprint $table) {
            $table->id();
            $table->string('name_en', 150);
            $table->string('name_ar', 150);
            $table->string('iso2', 2)->unique()->index();
            $table->boolean('active')->index()->default(true);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
            $table->json('meta')->nullable();

            $table->softDeletes();
        });

        Schema::connection('world')->create('states', function (Blueprint $table) {
            $table->id();
            $table->foreignId('country_id')->index()->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('name_en', 150);
            $table->string('name_ar', 150);
            $table->string('code', 10)->nullable()->index();
            $table->boolean('active')->index()->default(true);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
            $table->json('meta')->nullable();

            $table->softDeletes();
        });

        Schema::connection('world')->create('cities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('country_id')->index()->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('state_id')->index()->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('name_en', 150);
            $table->string('name_ar', 150);
            $table->boolean('active')->index()->default(true);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
            $table->json('meta')->nullable();

            $table->softDeletes();
        });

        Schema::connection('world')->create('villages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('country_id')->index()->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('state_id')->index()->nullable()->constrained()->cascadeOnUpdate()->nullOnDelete();
            $table->string('name_en', 150);
            $table->string('name_ar', 150);
            $table->boolean('active')->index()->default(true);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
            $table->json('meta')->nullable();

            $table->softDeletes();
        });

        Schema::connection('world')->create('neighborhoods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('country_id')->index()->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('state_id')->index()->nullable()->constrained()->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('city_id')->index()->nullable()->constrained()->cascadeOnUpdate()->nullOnDelete();
            $table->string('name_en', 150);
            $table->string('name_ar', 150);
            $table->boolean('active')->index()->default(true);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
            $table->json('meta')->nullable();

            $table->softDeletes();
        });

        Schema::connection('world')->create('languages', function (Blueprint $table) {
            $table->id();
            $table->string('code', 2)->unique()->index();
            $table->string('name');
            $table->string('name_native')->nullable();
            $table->string('dir')->default('ltr')->nullable();
            $table->boolean('active')->index()->default(true);
            $table->json('meta')->nullable();

            $table->softDeletes();
        });

        Schema::connection('world')->create('nationalities', function (Blueprint $table) {
            $table->id();
            $table->string('code', 2)->unique()->index();
            $table->string('name_en', 150);
            $table->string('name_ar', 150);
            $table->boolean('active')->index()->default(true);
            $table->json('meta')->nullable();

            $table->softDeletes();
        });
    }

    public function down(): void
    {
        if (DB::connection()->getName() != 'world') {
            return;
        }

        Schema::connection('world')->dropIfExists('nationalities');
        Schema::connection('world')->dropIfExists('languages');
        Schema::connection('world')->dropIfExists('neighborhoods');
        Schema::connection('world')->dropIfExists('villages');
        Schema::connection('world')->dropIfExists('cities');
        Schema::connection('world')->dropIfExists('states');
        Schema::connection('world')->dropIfExists('countries');
    }
};
