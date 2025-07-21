<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pd_product_deposit_information', function (Blueprint $table) {
            $table->string('initial_payment_due_type')->nullable();
            $table->integer('days_initial_payment_due')->nullable();
            $table->date('date_initial_payment_due')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('pd_product_deposit_information', function (Blueprint $table) {
            $table->dropColumn(['initial_payment_due_type', 'days_initial_payment_due', 'date_initial_payment_due']);
        });
    }
};
