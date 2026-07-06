<?php

use App\Models\Content;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Str;

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
            ->chunkById(100, function ($contents): void {
                foreach ($contents as $content) {
                    $content->forceFill(['uuid' => (string) Str::uuid()])->saveQuietly();
                }
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
