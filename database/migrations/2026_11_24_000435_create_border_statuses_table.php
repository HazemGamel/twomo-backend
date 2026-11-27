<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('border_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('name_ar');
            $table->string('name_en');
            $table->string('location_ar');
            $table->string('location_en');
            $table->string('status_ar');
            $table->string('status_en');
            $table->string('color_hex')->default('#28A745');
            $table->integer('reports_count')->default(0);
            $table->string('tip_ar')->nullable();
            $table->string('tip_en')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('border_statuses');
    }
};
