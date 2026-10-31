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
        Schema::table('reseller_packages', function (Blueprint $table) {
            if (!Schema::hasColumn('reseller_packages', 'features')) {
                $table->json('features')->nullable()->after('modules');
            }
            if (!Schema::hasColumn('reseller_packages', 'monthly_price')) {
                $table->unsignedBigInteger('monthly_price')->default(0)->after('features');
            }
            if (!Schema::hasColumn('reseller_packages', 'quarterly_price')) {
                $table->unsignedBigInteger('quarterly_price')->default(0)->after('monthly_price');
            }
            if (!Schema::hasColumn('reseller_packages', 'semi_annual_price')) {
                $table->unsignedBigInteger('semi_annual_price')->default(0)->after('quarterly_price');
            }
            if (!Schema::hasColumn('reseller_packages', 'yearly_price')) {
                $table->unsignedBigInteger('yearly_price')->default(0)->after('semi_annual_price');
            }
            if (!Schema::hasColumn('reseller_packages', 'setup_fee')) {
                $table->unsignedBigInteger('setup_fee')->default(0)->after('yearly_price');
            }
            if (!Schema::hasColumn('reseller_packages', 'price_mode')) {
                $table->string('price_mode', 20)->default('custom')->after('setup_fee');
            }
            if (!Schema::hasColumn('reseller_packages', 'discount_percent')) {
                $table->decimal('discount_percent', 5, 2)->default(0)->after('price_mode');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reseller_packages', function (Blueprint $table) {
            $columns = [
                'features',
                'monthly_price',
                'quarterly_price',
                'semi_annual_price',
                'yearly_price',
                'setup_fee',
                'price_mode',
                'discount_percent',
            ];
            foreach ($columns as $column) {
                if (Schema::hasColumn('reseller_packages', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
