<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sender_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('traveler_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('tracking_code')->unique();
            $table->string('from_country', 2)->default('SA');
            $table->string('from_city');
            $table->string('to_country', 2)->default('JO');
            $table->string('to_city');
            $table->string('border_name');
            $table->string('recipient_name');
            $table->string('recipient_phone');
            $table->string('category')->default('أغراض شخصية');
            $table->text('details');
            $table->decimal('reward_fee', 8, 2)->default(0.00);
            $table->string('status')->default('pending'); // pending, assigned, in_transit, delivered, cancelled
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipments');
    }
};
