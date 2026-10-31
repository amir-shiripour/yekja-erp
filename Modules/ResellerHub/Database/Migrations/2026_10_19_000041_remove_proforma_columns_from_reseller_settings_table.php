<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reseller_settings', function (Blueprint $table) {
            $columnsToDrop = [];
            foreach ([
                'proforma_invoice_prefix',
                'proforma_invoice_middle_prefix',
                'proforma_invoice_suffix',
                'proforma_invoice_padding',
                'proforma_invoice_auto',
            ] as $column) {
                if (Schema::hasColumn('reseller_settings', $column)) {
                    $columnsToDrop[] = $column;
                }
            }

            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }

    public function down(): void
    {
        Schema::table('reseller_settings', function (Blueprint $table) {
            $table->string('proforma_invoice_prefix')->nullable();
            $table->string('proforma_invoice_middle_prefix')->nullable();
            $table->string('proforma_invoice_suffix')->nullable();
            $table->integer('proforma_invoice_padding')->default(4);
            $table->boolean('proforma_invoice_auto')->default(false);
        });
    }
};
