<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pd_travel_agency_commissions_conditions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('travel_agency_commissions_id');
            $table->string('field');
            $table->string('value');
            $table->timestamps();

            $table->foreign('travel_agency_commissions_id', 'fk_commissions_conditions')
                ->references('id')
                ->on('pd_travel_agency_commissions')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pd_travel_agency_commissions_conditions');
    }
};
