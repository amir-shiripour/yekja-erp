<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Business rule change: a single reseller sub-account (person) can now
     * own multiple tenants/CRMs. Each tenant keeps its own separate `users`
     * row for its admin (one login per tenant), but that means the SAME
     * mobile number / email address legitimately needs to appear on more
     * than one `users` row (one per tenant) belonging to the same person.
     *
     * The previous global-unique indexes on `mobile` and `email` blocked
     * this and caused:
     *   SQLSTATE[23000]: Duplicate entry '...' for key 'users.users_mobile_unique'
     *
     * We scope both indexes to (tenant_id, column) instead, so uniqueness
     * is enforced within a tenant, not across the whole table.
     */
    public function up(): void
    {
        // Drop the old global-unique indexes if they exist (name may vary
        // slightly depending on how they were originally created).
        $this->dropIndexIfExists('users', 'users_mobile_unique');
        $this->dropIndexIfExists('users', 'users_email_unique');

        Schema::table('users', function (Blueprint $table) {
            $table->unique(['tenant_id', 'mobile'], 'users_tenant_id_mobile_unique');
            $table->unique(['tenant_id', 'email'], 'users_tenant_id_email_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $this->dropIndexIfExists('users', 'users_tenant_id_mobile_unique');
        $this->dropIndexIfExists('users', 'users_tenant_id_email_unique');

        Schema::table('users', function (Blueprint $table) {
            $table->unique('mobile', 'users_mobile_unique');
            $table->unique('email', 'users_email_unique');
        });
    }

    /**
     * Drop an index by name only if it currently exists on the table
     * (avoids errors when the original index name differs across envs).
     */
    private function dropIndexIfExists(string $table, string $indexName): void
    {
        $connection = Schema::getConnection();
        $dbName = $connection->getDatabaseName();

        $exists = DB::select(
            "SELECT COUNT(1) AS cnt FROM information_schema.statistics
             WHERE table_schema = ? AND table_name = ? AND index_name = ?",
            [$dbName, $table, $indexName]
        );

        if (!empty($exists) && (int)$exists[0]->cnt > 0) {
            Schema::table($table, function (Blueprint $t) use ($indexName) {
                $t->dropUnique($indexName);
            });
        }
    }
};
