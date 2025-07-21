<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pd_product_deposit_information', function (Blueprint $table) {
            $table->integer('days_after_booking_initial_payment_due')->nullable();
            $table->integer('days_before_arrival_initial_payment_due')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('pd_product_deposit_information', function (Blueprint $table) {
            $table->dropColumn('days_after_booking_initial_payment_due');
            $table->dropColumn('days_before_arrival_initial_payment_due');
        });
    }
};
