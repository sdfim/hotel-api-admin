<?php

namespace Modules\Insurance\Seeders;

use Illuminate\Database\Seeder;
use Modules\Insurance\Models\InsuranceType;

class InsuranceTypeSeeder extends Seeder
{

    public function run(): void
    {
        InsuranceType::updateOrCreate(
            ['name' => 'Silver Plan - F545U'],
            ['benefits' => [
                ['type' => 'Cancel For Any Reason*', 'amount' => 'n/a'],
                ['type' => 'Trip Cancellation', 'amount' => 'Trip Cost (max $30K)'],
                ['type' => 'Trip Interruption', 'amount' => 'Trip Cost'],
                ['type' => 'Missed Connection', 'amount' => '$750'],
                ['type' => 'Travel Delay', 'amount' => '$1,500'],
                ['type' => 'Medical Expense (Excess)', 'amount' => '$25,000'],
                ['type' => 'Evacuation/Repatriation (Excess)', 'amount' => '$100,000'],
                ['type' => 'Accidental Death and Dismemberment', 'amount' => '$15,000'],
                ['type' => 'Baggage & Personal Effects', 'amount' => '$2,500'],
                ['type' => 'Baggage Delay', 'amount' => '$500'],
            ]]
        );

        InsuranceType::updateOrCreate(
            ['name' => 'Platinum Plan - F545F'],
            ['benefits' => [
                ['type' => 'Cancel For Any Reason*', 'amount' => '75% Trip Cost (max $20K)'],
                ['type' => 'Trip Cancellation', 'amount' => 'Trip Cost (max $20K)'],
                ['type' => 'Trip Interruption', 'amount' => 'Trip Cost'],
                ['type' => 'Missed Connection', 'amount' => '$1,500'],
                ['type' => 'Travel Delay', 'amount' => '$3,000'],
                ['type' => 'Medical Expense (Excess)', 'amount' => '$50,000'],
                ['type' => 'Evacuation/Repatriation (Excess)', 'amount' => '$100,000'],
                ['type' => 'Accidental Death and Dismemberment', 'amount' => '$25,000'],
                ['type' => 'Baggage & Personal Effects', 'amount' => '$2,500'],
                ['type' => 'Baggage Delay', 'amount' => '$500'],
            ]]
        );
    }

}
