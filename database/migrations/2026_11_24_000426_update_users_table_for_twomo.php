<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone')->nullable()->unique()->after('email');
            $table->string('role')->default('traveler')->after('phone');
            $table->string('gender')->default('male')->after('role');
            $table->string('nationality')->default('سعودي')->after('gender');
            $table->string('id_number')->nullable()->after('nationality');
            $table->string('avatar')->nullable()->after('id_number');
            $table->string('car_model')->nullable()->after('avatar');
            $table->string('car_plate')->nullable()->after('car_model');
            $table->decimal('rating', 3, 1)->default(5.0)->after('car_plate');
            $table->decimal('wallet_balance', 10, 2)->default(0.00)->after('rating');
            $table->boolean('is_subscribed')->default(true)->after('wallet_balance');
            $table->integer('border_points')->default(0)->after('is_subscribed');
            $table->string('fcm_token')->nullable()->after('border_points');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'phone', 'role', 'gender', 'nationality', 'id_number',
                'avatar', 'car_model', 'car_plate', 'rating',
                'wallet_balance', 'is_subscribed', 'border_points', 'fcm_token'
            ]);
        });
    }
};
