<?php

use App\Actions\CreateDefaultBlocks;
use App\Models\Tenant;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('blocks', function (Blueprint $table) {
            $table->boolean('is_default')->default(false)->after('active');
        });

        Tenant::query()->each(
            fn (Tenant $tenant) => CreateDefaultBlocks::run($tenant)
        );
    }

    public function down(): void
    {
        Schema::table('blocks', function (Blueprint $table) {
            $table->dropColumn('is_default');
        });
    }
};
