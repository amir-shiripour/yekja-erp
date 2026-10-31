<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('reseller_settings')) {
            Schema::table('reseller_settings', function (Blueprint $table) {
                if (!Schema::hasColumn('reseller_settings', 'reseller_rounding_mode')) {
                    $table->string('reseller_rounding_mode', 20)->default('none')->after('tax_mode');
                }
                if (!Schema::hasColumn('reseller_settings', 'reseller_rounding_factor')) {
                    $table->unsignedInteger('reseller_rounding_factor')->default(1000)->after('reseller_rounding_mode');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('reseller_settings')) {
            Schema::table('reseller_settings', function (Blueprint $table) {
                $columns = [
                    'reseller_rounding_mode',
                    'reseller_rounding_factor',
                ];

                $existingColumns = array_filter($columns, static fn(string $column): bool => Schema::hasColumn('reseller_settings', $column));

                if (!empty($existingColumns)) {
                    $table->dropColumn($existingColumns);
                }
            });
        }
    }
};
