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
        Schema::create('money_requests', function (Blueprint $table) {
        $table->id();
        $table->string('transaction_id')->unique();
        $table->decimal('amount', 12, 2);
        $table->text('description')->nullable();
        $table->string('image_path');
        $table->Integer('request_by'); 
        $table->Integer('request_create_for');
        $table->text('raject_reason')->nullable();
        $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');

        $table->timestamps();

        // $table->foreign('request_by')->references('id')->on('users')->onDelete('cascade');
        // $table->foreign('request_create_for')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('money_requests');
    }
};
