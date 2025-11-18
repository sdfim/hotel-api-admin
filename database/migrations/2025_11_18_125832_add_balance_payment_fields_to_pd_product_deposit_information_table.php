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
        Schema::table('pd_product_deposit_information', function (Blueprint $table) {
            $table->integer('days_after_booking_balance_payment_due')->nullable();
            $table->integer('days_before_arrival_balance_payment_due')->nullable();
            $table->date('date_balance_payment_due')->nullable();
            $table->string('balance_payment_due_type')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pd_product_deposit_information', function (Blueprint $table) {
            $table->dropColumn('days_after_booking_balance_payment_due');
            $table->dropColumn('days_before_arrival_balance_payment_due');
            $table->dropColumn('date_balance_payment_due');
            $table->dropColumn('balance_payment_due_type');
        });
    }
};
