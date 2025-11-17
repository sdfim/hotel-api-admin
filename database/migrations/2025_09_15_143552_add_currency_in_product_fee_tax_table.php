<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('pd_product_fees_and_taxes', function (Blueprint $table): void {
            $table->string('currency')->nullable()->after('rack_value');
        });

        $this->populateCurrencyColumn();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pd_product_fees_and_taxes', function (Blueprint $table): void {
            $table->dropColumn('currency');
        });
    }

    private function populateCurrencyColumn(): void
    {
        $batchSize = 200; // avoid loading too many records at once

        do {
            $ids = DB::table('pd_product_fees_and_taxes')
                ->whereNull('currency')
                ->limit($batchSize)
                ->pluck('id');

            if ($ids->isEmpty()) {
                break;
            }

            DB::table('pd_product_fees_and_taxes')
                ->whereIn('id', $ids)
                ->update(['currency' => 'USD']);

        } while (true);
    }
};
