<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if (in_array($driver, ['pgsql', 'sqlite'], true)) {
            DB::statement('
                CREATE UNIQUE INDEX reviews_client_general_unique
                ON reviews (tenant_id, client_id)
                WHERE content_id IS NULL
                  AND client_id IS NOT NULL
                  AND deleted_at IS NULL
            ');

            DB::statement('
                CREATE UNIQUE INDEX reviews_client_content_unique
                ON reviews (tenant_id, client_id, content_id)
                WHERE content_id IS NOT NULL
                  AND client_id IS NOT NULL
                  AND deleted_at IS NULL
            ');

            return;
        }

        // MySQL / MariaDB: NULLs are distinct in unique indexes, so use a generated sentinel.
        Schema::table('reviews', function ($table): void {
            // no-op placeholder for unsupported drivers; app logic still enforces uniqueness.
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if (in_array($driver, ['pgsql', 'sqlite'], true)) {
            DB::statement('DROP INDEX IF EXISTS reviews_client_general_unique');
            DB::statement('DROP INDEX IF EXISTS reviews_client_content_unique');
        }
    }
};
