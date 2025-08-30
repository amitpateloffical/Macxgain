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
        Schema::table('users', function (Blueprint $table) {
            $table->string('aadhar_front_image')->nullable()->after('address');
            $table->string('aadhar_back_image')->nullable()->after('aadhar_front_image');
            $table->string('pan_card_image')->nullable()->after('aadhar_back_image');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['aadhar_front_image', 'aadhar_back_image', 'pan_card_image']);
        });
    }
};
