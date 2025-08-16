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
        Schema::create('withdrawal_requests', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_id')->nullable();
            $table->decimal('amount', 12, 2)->nullable();
            $table->text('description')->nullable();
            $table->string('image_path')->nullable();
            $table->integer('request_by')->nullable();
            $table->integer('request_create_for');
            $table->text('reject_reason')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->integer('approve_by')->nullable();
            $table->timestamp('approve_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('withdrawal_requests');
    }
};
