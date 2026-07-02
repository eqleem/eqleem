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
        Schema::create('form_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignId('content_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignId('client_id')
                ->nullable()
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignId('block_id')
                ->nullable()
                ->constrained()
                ->cascadeOnDelete();
            $table->string('status')->default('new');
            $table->jsonb('data');
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();

            $table->softDeletes();

            $table->index('tenant_id');
            $table->index('content_id');
            $table->index('client_id');
            $table->index('block_id');
            $table->index('status');
            $table->index('submitted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('form_submissions');
    }
};
