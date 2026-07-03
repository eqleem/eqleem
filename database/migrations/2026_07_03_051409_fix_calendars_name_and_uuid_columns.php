<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('calendars', function (Blueprint $table): void {
            if (! Schema::hasColumn('calendars', 'uuid')) {
                $table->uuid('uuid')->nullable()->unique()->after('id');
            }
        });

        if (Schema::hasColumn('calendars', 'name')) {
            Schema::table('calendars', function (Blueprint $table): void {
                $table->dropColumn('name');
            });
        }

        Schema::table('calendars', function (Blueprint $table): void {
            if (! Schema::hasColumn('calendars', 'name')) {
                $table->string('name')->after('user_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('calendars', function (Blueprint $table): void {
            if (Schema::hasColumn('calendars', 'uuid')) {
                $table->dropColumn('uuid');
            }

            if (Schema::hasColumn('calendars', 'name')) {
                $table->dropColumn('name');
            }
        });

        Schema::table('calendars', function (Blueprint $table): void {
            $table->json('name')->nullable();
        });
    }
};
