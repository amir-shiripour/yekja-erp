<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private string $archiveTable = 'tenant_owner_id_archive';

    public function up(): void
    {
        if (!Schema::hasColumn('tenants', 'owner_id')) {
            return;
        }

        // Preserve the existing values before removing the column. The
        // application no longer uses owner_id, but dropping data without a
        // backup is irreversible and avoidable.
        if (!Schema::hasTable($this->archiveTable)) {
            Schema::create($this->archiveTable, function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tenant_id')->unique();
                $table->unsignedBigInteger('owner_id')->nullable()->index();
                $table->timestamp('archived_at')->nullable();
            });
        }

        $rows = DB::table('tenants')
            ->whereNotNull('owner_id')
            ->select('id as tenant_id', 'owner_id')
            ->get()
            ->map(fn ($row) => [
                'tenant_id' => $row->tenant_id,
                'owner_id' => $row->owner_id,
                'archived_at' => now(),
            ])
            ->all();

        if (!empty($rows)) {
            DB::table($this->archiveTable)->insertOrIgnore($rows);
        }

        $hasOwnerIndex = collect(Schema::getIndexes('tenants'))->contains(
            fn (array $index) => ($index['name'] ?? null) === 'tenants_owner_id_index'
        );

        if ($hasOwnerIndex) {
            Schema::table('tenants', function (Blueprint $table) {
                $table->dropIndex('tenants_owner_id_index');
            });
        }

        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn('owner_id');
        });
    }

    public function down(): void
    {
        if (!Schema::hasColumn('tenants', 'owner_id')) {
            Schema::table('tenants', function (Blueprint $table) {
                $table->unsignedBigInteger('owner_id')->nullable()->index();
            });
        }

        if (!Schema::hasTable($this->archiveTable)) {
            return;
        }

        foreach (DB::table($this->archiveTable)->get() as $row) {
            DB::table('tenants')
                ->where('id', $row->tenant_id)
                ->update(['owner_id' => $row->owner_id]);
        }
    }
};
