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
        Schema::create('ai_trading_orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('user_id');
            $table->string('stock_symbol', 50);
            $table->enum('option_type', ['CALL', 'PUT']);
            $table->enum('action', ['BUY', 'SELL']);
            $table->decimal('strike_price', 10, 2);
            $table->integer('quantity');
            $table->decimal('total_amount', 12, 2);
            $table->enum('status', ['PENDING', 'COMPLETED', 'CANCELLED', 'CLOSED'])->default('PENDING');
            $table->decimal('exit_price', 10, 2)->nullable();
            $table->decimal('pnl', 12, 2)->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->index(['user_id', 'created_at']);
            $table->index(['stock_symbol', 'created_at']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_trading_orders');
    }
};
