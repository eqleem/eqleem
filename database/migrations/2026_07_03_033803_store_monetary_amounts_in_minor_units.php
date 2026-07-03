<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('plans')) {
            DB::table('plans')
                ->where('price', '>', 0)
                ->where('price', '<', 10000)
                ->update(['price' => DB::raw('price * 100')]);
        }

        if (Schema::hasTable('payments')) {
            DB::table('payments')
                ->whereNotNull('amount')
                ->where('amount', '>', 0)
                ->where('amount', '<', 10000)
                ->update(['amount' => DB::raw('amount * 100')]);

            if (Schema::hasColumn('payments', 'amount_long')) {
                DB::table('payments')
                    ->whereNull('amount')
                    ->whereNotNull('amount_long')
                    ->update(['amount' => DB::raw('amount_long')]);

                Schema::table('payments', function (Blueprint $table) {
                    $table->dropColumn('amount_long');
                });
            }

            Schema::table('payments', function (Blueprint $table) {
                $table->unsignedBigInteger('amount')->nullable()->change();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('plans')) {
            DB::table('plans')
                ->where('price', '>', 0)
                ->where('price', '>=', 100)
                ->update(['price' => DB::raw('price / 100')]);
        }

        if (Schema::hasTable('payments')) {
            Schema::table('payments', function (Blueprint $table) {
                $table->unsignedInteger('amount_long')->nullable()->after('amount');
            });

            DB::table('payments')
                ->whereNotNull('amount')
                ->where('amount', '>', 0)
                ->update([
                    'amount_long' => DB::raw('amount'),
                    'amount' => DB::raw('amount / 100'),
                ]);

            Schema::table('payments', function (Blueprint $table) {
                $table->unsignedInteger('amount')->nullable()->change();
            });
        }
    }
};
