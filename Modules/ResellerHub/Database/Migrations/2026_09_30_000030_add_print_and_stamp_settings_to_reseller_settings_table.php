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
        if (Schema::hasTable('reseller_settings')) {
            Schema::table('reseller_settings', function (Blueprint $table) {
                if (!Schema::hasColumn('reseller_settings', 'reseller_print_mode')) {
                    $table->string('reseller_print_mode', 20)->default('standard')->nullable()->after('invoice_footer_note');
                }
                if (!Schema::hasColumn('reseller_settings', 'reseller_official_invoice_orientation')) {
                    $table->string('reseller_official_invoice_orientation', 20)->default('portrait')->nullable()->after('reseller_print_mode');
                }
                if (!Schema::hasColumn('reseller_settings', 'reseller_stamp_standard_width')) {
                    $table->integer('reseller_stamp_standard_width')->nullable()->after('reseller_official_invoice_orientation');
                }
                if (!Schema::hasColumn('reseller_settings', 'reseller_stamp_standard_height')) {
                    $table->integer('reseller_stamp_standard_height')->nullable()->after('reseller_stamp_standard_width');
                }
                if (!Schema::hasColumn('reseller_settings', 'reseller_stamp_official_width')) {
                    $table->integer('reseller_stamp_official_width')->nullable()->after('reseller_stamp_standard_height');
                }
                if (!Schema::hasColumn('reseller_settings', 'reseller_stamp_official_height')) {
                    $table->integer('reseller_stamp_official_height')->nullable()->after('reseller_stamp_official_width');
                }
                if (!Schema::hasColumn('reseller_settings', 'brand_seal_signature')) {
                    $table->string('brand_seal_signature')->nullable()->after('reseller_stamp_official_height');
                }
                if (!Schema::hasColumn('reseller_settings', 'reseller_economic_number')) {
                    $table->string('reseller_economic_number', 50)->nullable()->after('brand_seal_signature');
                }
                if (!Schema::hasColumn('reseller_settings', 'reseller_national_id')) {
                    $table->string('reseller_national_id', 50)->nullable()->after('reseller_economic_number');
                }
                if (!Schema::hasColumn('reseller_settings', 'reseller_registration_number')) {
                    $table->string('reseller_registration_number', 50)->nullable()->after('reseller_national_id');
                }
                if (!Schema::hasColumn('reseller_settings', 'reseller_postal_code')) {
                    $table->string('reseller_postal_code', 20)->nullable()->after('reseller_registration_number');
                }
                if (!Schema::hasColumn('reseller_settings', 'reseller_address')) {
                    $table->text('reseller_address')->nullable()->after('reseller_postal_code');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('reseller_settings')) {
            Schema::table('reseller_settings', function (Blueprint $table) {
                $columns = [
                    'reseller_print_mode',
                    'reseller_official_invoice_orientation',
                    'reseller_stamp_standard_width',
                    'reseller_stamp_standard_height',
                    'reseller_stamp_official_width',
                    'reseller_stamp_official_height',
                    'brand_seal_signature',
                    'reseller_economic_number',
                    'reseller_national_id',
                    'reseller_registration_number',
                    'reseller_postal_code',
                    'reseller_address',
                ];
                foreach ($columns as $column) {
                    if (Schema::hasColumn('reseller_settings', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};
