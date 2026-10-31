<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reseller_packages', function (Blueprint $table) {
            $table->string('price_type')->default('subscription')->after('color');
            $table->json('active_periods')->nullable()->after('price_type');
            $table->unsignedBigInteger('one_time_price')->default(0)->after('yearly_price');
            $table->string('discount_type')->default('percent')->after('setup_fee');
            $table->decimal('discount_value', 12, 2)->default(0)->after('discount_type');
        });

        DB::table('reseller_packages')->orderBy('id')->chunk(100, function ($packages) {
            foreach ($packages as $pkg) {
                DB::table('reseller_packages')->where('id', $pkg->id)->update([
                    'discount_value' => $pkg->discount_percent ?? 0,
                    'discount_type' => 'percent',
                    'active_periods' => json_encode(['monthly_price', 'quarterly_price', 'semi_annual_price', 'yearly_price']),
                ]);
            }
        });

        Schema::table('reseller_packages', function (Blueprint $table) {
            $table->dropColumn(['price_mode', 'discount_percent', 'custom_pricing']);
        });
    }

    public function down(): void
    {
        Schema::table('reseller_packages', function (Blueprint $table) {
            $table->string('price_mode')->default('custom')->after('color');
            $table->decimal('discount_percent', 5, 2)->default(0)->after('setup_fee');
            $table->json('custom_pricing')->nullable()->after('discount_percent');

            $table->dropColumn(['price_type', 'active_periods', 'one_time_price', 'discount_type', 'discount_value']);
        });
    }
};
