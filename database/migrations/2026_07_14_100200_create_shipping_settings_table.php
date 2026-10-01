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
        // Singleton settings table - the app only ever uses row id = 1. Stores the
        // business's shipping-origin address for reference/packing slips now, and
        // for a future live-carrier-API phase later.
        Schema::create('shipping_settings', function (Blueprint $table) {
            $table->id();
            $table->string('origin_company_name')->nullable();
            $table->string('origin_street_address')->nullable();
            $table->string('origin_city')->nullable();
            $table->string('origin_region')->nullable();
            $table->string('origin_postcode')->nullable();
            $table->string('origin_country')->nullable()->default('New Zealand');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipping_settings');
    }
};
