<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected array $tables = [
        'client_forms',
        'client_settings',
        'client_statuses',
        'booking_forms',
        'checkout_forms',
        'client_addresses',
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        foreach ($this->tables as $tableName) {
            if (Schema::hasTable($tableName) && !Schema::hasColumn($tableName, 'tenant_id')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->unsignedBigInteger('tenant_id')->nullable()->after('id')->index();
                    $table->foreign('tenant_id')->references('id')->on('tenants')->nullOnDelete();
                });
            }
        }

        // Scope client_forms key uniqueness to tenant
        if (Schema::hasTable('client_forms')) {
            $this->dropIndexIfExists('client_forms', 'client_forms_key_unique');
            if (!$this->hasIndex('client_forms', 'client_forms_tenant_id_key_unique')) {
                Schema::table('client_forms', function (Blueprint $table) {
                    $table->unique(['tenant_id', 'key'], 'client_forms_tenant_id_key_unique');
                });
            }
        }

        // Scope client_settings key uniqueness to tenant
        if (Schema::hasTable('client_settings')) {
            $this->dropIndexIfExists('client_settings', 'client_settings_key_unique');
            if (!$this->hasIndex('client_settings', 'client_settings_tenant_id_key_unique')) {
                Schema::table('client_settings', function (Blueprint $table) {
                    $table->unique(['tenant_id', 'key'], 'client_settings_tenant_id_key_unique');
                });
            }
        }

        // Scope checkout_forms key uniqueness to tenant
        if (Schema::hasTable('checkout_forms')) {
            $this->dropIndexIfExists('checkout_forms', 'checkout_forms_key_unique');
            if (!$this->hasIndex('checkout_forms', 'checkout_forms_tenant_id_key_unique')) {
                Schema::table('checkout_forms', function (Blueprint $table) {
                    $table->unique(['tenant_id', 'key'], 'checkout_forms_tenant_id_key_unique');
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach ($this->tables as $tableName) {
            if (Schema::hasTable($tableName) && Schema::hasColumn($tableName, 'tenant_id')) {
                Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                    $table->dropForeign(['tenant_id']);
                    $table->dropColumn('tenant_id');
                });
            }
        }
    }

    private function dropIndexIfExists(string $table, string $indexName): void
    {
        if ($this->hasIndex($table, $indexName)) {
            Schema::table($table, function (Blueprint $t) use ($indexName) {
                $t->dropUnique($indexName);
            });
        }
    }

    private function hasIndex(string $table, string $indexName): bool
    {
        $connection = Schema::getConnection();
        $dbName = $connection->getDatabaseName();

        $exists = DB::select(
            "SELECT COUNT(1) AS cnt FROM information_schema.statistics
             WHERE table_schema = ? AND table_name = ? AND index_name = ?",
            [$dbName, $table, $indexName]
        );

        return !empty($exists) && (int)$exists[0]->cnt > 0;
    }
};
