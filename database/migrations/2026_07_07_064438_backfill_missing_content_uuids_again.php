<?php

use App\Models\Content;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Content::query()
            ->withoutGlobalScopes()
            ->whereNull('uuid')
            ->orderBy('id')
            ->each(fn (Content $content) => $content->ensureUuid());
    }
};
