<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * @noinspection SqlResolve
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('custom_user_fields')) {
            Schema::table('custom_user_fields', function (Blueprint $table) {
                if (!Schema::hasColumn('custom_user_fields', 'category_group')) {
                    $table->string('category_group', 100)->nullable()->default('general')->after('field_type');
                }
                if (!Schema::hasColumn('custom_user_fields', 'target_scope')) {
                    $table->string('target_scope', 50)->nullable()->default('user')->after('category_group');
                }
                if (!Schema::hasColumn('custom_user_fields', 'sort_order')) {
                    $table->integer('sort_order')->default(0)->after('target_scope');
                }
                if (!Schema::hasColumn('custom_user_fields', 'meta')) {
                    $table->json('meta')->nullable()->after('rules');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('custom_user_fields')) {
            Schema::table('custom_user_fields', function (Blueprint $table) {
                $columnsToDrop = [];
                foreach (['category_group', 'target_scope', 'sort_order', 'meta'] as $col) {
                    if (Schema::hasColumn('custom_user_fields', $col)) {
                        $columnsToDrop[] = $col;
                    }
                }
                if (!empty($columnsToDrop)) {
                    $table->dropColumn($columnsToDrop);
                }
            });
        }
    }
};
