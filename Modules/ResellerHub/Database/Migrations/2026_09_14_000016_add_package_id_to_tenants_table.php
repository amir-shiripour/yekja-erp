<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('tenants') && !Schema::hasColumn('tenants', 'package_id')) {
            Schema::table('tenants', function (Blueprint $table) {
                $table->foreignId('package_id')
                    ->nullable()
                    ->after('reseller_id')
                    ->constrained('reseller_packages')
                    ->nullOnDelete();
            });

            // Backfill existing tenants that match reseller packages modules
            try {
                if (Schema::hasTable('reseller_packages')) {
                    $packages = DB::table('reseller_packages')->get();
                    foreach ($packages as $pkg) {
                        $pkgModules = json_decode($pkg->modules, true) ?: [];
                        sort($pkgModules);
                        if (empty($pkgModules)) {
                            continue;
                        }

                        $tenants = DB::table('tenants')
                            ->where('reseller_id', $pkg->reseller_id)
                            ->whereNull('package_id')
                            ->get();

                        foreach ($tenants as $t) {
                            $tMods = DB::table('tenant_modules')->where('tenant_id', $t->id)->pluck('module_slug')->toArray();
                            sort($tMods);
                            if ($pkgModules == $tMods) {
                                DB::table('tenants')->where('id', $t->id)->update(['package_id' => $pkg->id]);
                            }
                        }
                    }
                }
            } catch (Throwable $e) {
                // Ignore backfill errors on migration
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('tenants') && Schema::hasColumn('tenants', 'package_id')) {
            Schema::table('tenants', function (Blueprint $table) {
                $table->dropForeign(['package_id']);
                $table->dropColumn('package_id');
            });
        }
    }
};
