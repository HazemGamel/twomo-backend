<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trips', function (Blueprint $table) {
            $table->id();
            $table->foreignId('driver_id')->constrained('users')->onDelete('cascade');
            $table->string('from_country', 2)->default('SA');
            $table->string('from_city');
            $table->string('to_country', 2)->default('JO');
            $table->string('to_city');
            $table->string('border_name');
            $table->dateTime('departure_date');
            $table->integer('available_seats')->default(3);
            $table->integer('total_seats')->default(3);
            $table->decimal('price_per_seat', 8, 2)->default(50.00);
            $table->text('notes')->nullable();
            $table->string('status')->default('scheduled'); // scheduled, in_progress, completed, cancelled
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trips');
    }
};
