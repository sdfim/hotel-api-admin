<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up (): void
    {
        Schema::table('weights', function (Blueprint $table) {
            $table->integer('property')->change();
            $table->integer('weight')->change();
            $table->foreignId('supplier_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down (): void
    {
        //
    }
};
