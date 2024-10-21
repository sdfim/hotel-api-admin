<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Modules\Insurance\Seeders\InsuranceRestrictionTypeSeeder;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('insurance_restriction_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        (new InsuranceRestrictionTypeSeeder())->run();
    }

    public function down(): void
    {
        Schema::dropIfExists('insurance_restriction_types');
    }
};
