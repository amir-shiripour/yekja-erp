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
        Schema::table('reseller_settings', function (Blueprint $table) {
            $table->unsignedBigInteger('custom_domain_yearly_price')->default(0)->after('allow_custom_domains');
            $table->unsignedBigInteger('custom_domain_monthly_price')->default(0)->after('custom_domain_yearly_price');
            $table->unsignedBigInteger('custom_domain_setup_fee')->default(0)->after('custom_domain_monthly_price');

            $table->unsignedBigInteger('white_label_yearly_price')->default(0)->after('custom_domain_setup_fee');
            $table->unsignedBigInteger('white_label_monthly_price')->default(0)->after('white_label_yearly_price');
            $table->unsignedBigInteger('white_label_setup_fee')->default(0)->after('white_label_monthly_price');

            $table->string('brand_primary_color', 20)->default('#4f46e5')->after('white_label_setup_fee');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reseller_settings', function (Blueprint $table) {
            $table->dropColumn([
                'custom_domain_yearly_price',
                'custom_domain_monthly_price',
                'custom_domain_setup_fee',
                'white_label_yearly_price',
                'white_label_monthly_price',
                'white_label_setup_fee',
                'brand_primary_color',
            ]);
        });
    }
};
