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
        Schema::create('contents', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->nullable()->unique();

            $table->foreignId('tenant_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->unsignedBigInteger('user_id')
                ->nullable();

            $table->unsignedBigInteger('block_id')
                ->nullable();

            $table->foreignId('parent_id')
                ->nullable()
                ->constrained('contents')
                ->nullOnDelete();

            $table->string('type');

            $table->string('template')
                ->nullable();

            $table->string('title');

            $table->string('slug');

            $table->integer('price')->nullable();

            $table->jsonb('data')
                ->nullable();
            $table->jsonb('meta')
                ->nullable();

            $table->string('status')
                ->default('draft');

            $table->integer('sort_order')
                ->default(0);

            $table->timestamp('published_at')
                ->nullable();

            $table->boolean('active')
                ->default(true);

            $table->softDeletes();

            $table->timestamps();

            $table->index('tenant_id');
            $table->index('parent_id');
            $table->index('type');
            $table->index('status');
            $table->index('published_at');
            $table->index('sort_order');
            $table->index('block_id', 'contents_block_id_index');
            $table->index(
                ['block_id', 'type', 'active', 'sort_order'],
                'contents_block_type_active_sort_index'
            );
            $table->index(
                ['tenant_id', 'type', 'status', 'active', 'sort_order'],
                'contents_tenant_type_status_active_sort_index'
            );

            $table->unique([
                'tenant_id',
                'slug',
            ]);
        });

    }
};
