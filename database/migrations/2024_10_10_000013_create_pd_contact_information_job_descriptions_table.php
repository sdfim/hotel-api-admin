<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('pd_contact_information_job_descriptions', function (Blueprint $table) {
            $table->foreignId('job_descriptions_id')
                ->constrained('config_job_descriptions')
                ->onDelete('cascade')
                ->name('fk_job_desc_id');
            $table->foreignId('contact_information_id')
                ->constrained('pd_contact_information')
                ->onDelete('cascade')
                ->name('fk_contact_info_id');

            $table->unique(['job_descriptions_id', 'contact_information_id'], 'job_desc_contact_info_unique');
        });
    }

    public function down()
    {
        Schema::dropIfExists('pd_contact_information_job_descriptions');
    }
};
