<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\Module;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Module::updateOrCreate(
            ['slug' => 'resellerhub'],
            [
                'name' => 'ResellerHub',
                'description' => 'ماژول نمایندگی فروش و مدیریت چندمستأجری (Multi-Tenancy & Reseller)',
                'version' => '1.0.0',
                'is_core' => false,
                'active' => false,
                'installed' => false,
                'installed_at' => null,
            ]
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Module::where('slug', 'resellerhub')->delete();
    }
};
