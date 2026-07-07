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
            ->each(function (Content $content): void {
                $content->forceFill([
                    'uuid' => (string) Str::uuid(),
                ])->saveQuietly();
            });
    }
};
