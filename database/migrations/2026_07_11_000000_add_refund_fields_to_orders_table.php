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
        Schema::table('orders', function (Blueprint $table) {
            // Populated when a Stripe refund is issued on order cancellation.
            $table->string('refund_id')->nullable()->after('stripe_payment_response');
            $table->timestamp('refunded_at')->nullable()->after('refund_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['refund_id', 'refunded_at']);
        });
    }
};
