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

        Schema::create('blocks', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            $table->unsignedBigInteger('tenant_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();

            $table->string('component');
            $table->string('type')
                ->default('widget');

            $table->foreignId('parent_id')
                ->nullable()
                ->constrained('blocks')
                ->cascadeOnDelete();

            $table->foreignId('content_id')
                ->nullable()
                ->constrained('contents')
                ->cascadeOnDelete();

            $table->foreignId('theme_id')
                ->nullable();

            $table->string('title')->nullable();

            $table->string('slug')->nullable();

            $table->string('position')->nullable(); // header, footer, content, sidebar, floating, cta, body, top-nav, bottom-nav
            $table->string('variant')->nullable();

            $table->integer('sort_order')->default(0);

            $table->string('status')
                ->default('draft');

            $table->boolean('active')->default(true);
            $table->boolean('is_default')->default(false);

            $table->jsonb('data')
                ->nullable();

            $table->timestamp('published_at')
                ->nullable();

            $table->timestamps();

            $table->index('tenant_id');
            $table->index('theme_id');
            $table->index('position');
            $table->index('type');
            $table->index('status');

            $table->unique([
                'tenant_id',
                'slug',
            ]);
        });

        Schema::create('block_translations', function (Blueprint $table) {
            $table->id();

            $table->foreignId('block_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('locale', 10);

            $table->string('title')->nullable();

            $table->string('slug')->nullable();

            $table->jsonb('data')->nullable();

            $table->timestamps();

            $table->unique(['block_id', 'locale']);

            $table->index('locale');

            $table->index(['locale', 'slug']);
        });
    }
};
