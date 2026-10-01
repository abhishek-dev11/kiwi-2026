<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            if (!Schema::hasColumn('categories', 'bottom_level_category_id')) {
                $table->unsignedBigInteger('bottom_level_category_id')->nullable()->after('mid_level_category_id');
            }
        });

        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'bottom_level_category_id')) {
                $table->unsignedBigInteger('bottom_level_category_id')->nullable()->after('mid_level_category_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'bottom_level_category_id')) {
                $table->dropColumn('bottom_level_category_id');
            }
        });

        Schema::table('categories', function (Blueprint $table) {
            if (Schema::hasColumn('categories', 'bottom_level_category_id')) {
                $table->dropColumn('bottom_level_category_id');
            }
        });
    }
};
