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
        Schema::create('shipping_methods', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->enum('type', ['flat', 'weight_based'])->default('flat');
            // The flat fee amount - only used when type = 'flat'. Weight-based methods
            // instead look up their cost from the shipping_weight_rates table.
            $table->decimal('flat_rate', 10, 2)->nullable();
            $table->enum('status', ['Active', 'Deactive'])->default('Active');
            $table->boolean('is_delete')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipping_methods');
    }
};
