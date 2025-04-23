<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('pd_vendor_gallery', function (Blueprint $table) {
            $table->foreignId('vendor_id')->constrained('pd_vendors')->onDelete('cascade');
            $table->foreignId('gallery_id')->constrained('pd_image_galleries')->onDelete('cascade');
            $table->primary(['vendor_id', 'gallery_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('pd_vendor_gallery');
    }
};
