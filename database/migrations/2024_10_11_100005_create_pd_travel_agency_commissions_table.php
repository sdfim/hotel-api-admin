<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pd_travel_agency_commissions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->decimal('commission_value', 10, 2);
            $table->date('date_range_start');
            $table->date('date_range_end');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pd_travel_agency_commissions');
    }
};
