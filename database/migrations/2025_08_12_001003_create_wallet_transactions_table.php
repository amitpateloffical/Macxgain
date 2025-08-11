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
        Schema::create('wallet_transactions', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('user_id');
        $table->string('transaction_code')->unique(); // unique reference code
        $table->enum('type', ['credit', 'debit']); // credit = add, debit = minus
        $table->decimal('amount', 12, 2);
        $table->decimal('running_balance', 12, 2); // balance after this txn
        $table->text('remark')->nullable();
        $table->unsignedBigInteger('approved_by')->nullable(); // admin id
        $table->timestamp('approved_at')->nullable();
        $table->timestamps();

    // $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
    // $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wallet_transactions');
    }
};
