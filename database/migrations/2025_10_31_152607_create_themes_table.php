<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('themes')) {
            Schema::create('themes', function (Blueprint $table) {
                $table->id();
                $table->string('name'); // نام قابل نمایش تم (مثال: تم شرکتی مدرن)
                $table->string('slug')->unique(); // نام کلیدی (مثال: modern-corporate)
                $table->text('description')->nullable(); // توضیحات تم
                $table->string('version')->default('1.0.0'); // نسخه تم
                $table->string('view_path')->unique(); // مسیر پوشه ویوها (مثال: themes.modern-corporate)
                $table->boolean('active')->default(false); // آیا این تم فعال است؟
                $table->timestamps();
            });
        }

        if (Schema::hasTable('users') && !Schema::hasColumn('users', 'active_theme_slug')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('active_theme_slug')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('themes');
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('active_theme_slug');
        });
    }
};

