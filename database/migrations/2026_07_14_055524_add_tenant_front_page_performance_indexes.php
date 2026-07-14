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
        Schema::table('blocks', function (Blueprint $table) {
            $table->index(
                ['tenant_id', 'parent_id', 'content_id', 'type'],
                'blocks_tenant_roots_type_index'
            );

            $table->index(
                ['tenant_id', 'parent_id', 'content_id', 'is_default', 'active', 'position', 'sort_order'],
                'blocks_tenant_home_page_index'
            );
        });

        Schema::table('contents', function (Blueprint $table) {
            $table->index('block_id', 'contents_block_id_index');

            $table->index(
                ['block_id', 'type', 'active', 'sort_order'],
                'contents_block_type_active_sort_index'
            );

            $table->index(
                ['tenant_id', 'type', 'status', 'active', 'sort_order'],
                'contents_tenant_type_status_active_sort_index'
            );
        });

        Schema::table('tenants', function (Blueprint $table) {
            $table->index(['handle', 'active'], 'tenants_handle_active_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('blocks', function (Blueprint $table) {
            $table->dropIndex('blocks_tenant_roots_type_index');
            $table->dropIndex('blocks_tenant_home_page_index');
        });

        Schema::table('contents', function (Blueprint $table) {
            $table->dropIndex('contents_block_id_index');
            $table->dropIndex('contents_block_type_active_sort_index');
            $table->dropIndex('contents_tenant_type_status_active_sort_index');
        });

        Schema::table('tenants', function (Blueprint $table) {
            $table->dropIndex('tenants_handle_active_index');
        });
    }
};
