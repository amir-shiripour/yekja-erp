<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('reseller_packages') && !Schema::hasColumn('reseller_packages', 'period_prices')) {
            Schema::table('reseller_packages', function (Blueprint $table) {
                $table->json('period_prices')->nullable()->after('yearly_price');
            });

            // Backfill existing packages
            DB::table('reseller_packages')->orderBy('id')->chunk(100, function ($packages) {
                foreach ($packages as $pkg) {
                    $prices = [];
                    if (!empty($pkg->monthly_price)) {
                        $prices['1'] = (int)$pkg->monthly_price;
                    }
                    if (!empty($pkg->quarterly_price)) {
                        $prices['3'] = (int)$pkg->quarterly_price;
                    }
                    if (!empty($pkg->semi_annual_price)) {
                        $prices['6'] = (int)$pkg->semi_annual_price;
                    }
                    if (!empty($pkg->yearly_price)) {
                        $prices['12'] = (int)$pkg->yearly_price;
                    }

                    DB::table('reseller_packages')->where('id', $pkg->id)->update([
                        'period_prices' => !empty($prices) ? json_encode($prices) : null,
                    ]);
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('reseller_packages') && Schema::hasColumn('reseller_packages', 'period_prices')) {
            Schema::table('reseller_packages', function (Blueprint $table) {
                $table->dropColumn('period_prices');
            });
        }
    }
};
