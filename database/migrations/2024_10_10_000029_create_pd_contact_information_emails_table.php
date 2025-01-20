<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('pd_contact_information_emails', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('contact_information_id');
            $table->string('email');
            $table->string('departments');
            $table->timestamps();

            $table->foreign('contact_information_id', 'fk_contact_info_emails_id')
                ->references('id')
                ->on('pd_contact_information')
                ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('pd_contact_information_emails');
    }
};
