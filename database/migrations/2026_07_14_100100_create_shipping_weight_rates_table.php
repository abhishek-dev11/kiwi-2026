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
        Schema::create('shipping_weight_rates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('shipping_method_id');
            // Inclusive lower bound (kg) for this bracket.
            $table->decimal('min_weight', 10, 2);
            // Inclusive upper bound (kg) for this bracket - null means "and above",
            // i.e. no upper limit for this bracket.
            $table->decimal('max_weight', 10, 2)->nullable();
            // The shipping cost charged for orders whose total weight falls in this bracket.
            $table->decimal('rate', 10, 2);
            $table->timestamps();

            $table->foreign('shipping_method_id')
                ->references('id')->on('shipping_methods')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipping_weight_rates');
    }
};
