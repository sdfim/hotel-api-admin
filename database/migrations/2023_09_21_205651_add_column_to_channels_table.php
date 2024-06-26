<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('channels', function (Blueprint $table) {
            $table->unsignedBigInteger('token_id')->after('description');
            $table->foreign('token_id')
                ->references('id')
                ->on('personal_access_tokens')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->string('access_token')->after('token_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('channels', function (Blueprint $table) {
            $table->dropColumn('token_id');
            $table->dropColumn('access_token');
        });
    }
};
