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
        Schema::table('ai_trading_orders', function (Blueprint $table) {
            $table->enum('status', ['PENDING', 'COMPLETED', 'CANCELLED', 'CLOSED'])->default('PENDING')->change();
            // Columns exit_price, pnl, and closed_at already exist from the table creation migration
            // Only updating the status enum to include 'CLOSED' status
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ai_trading_orders', function (Blueprint $table) {
            $table->enum('status', ['PENDING', 'COMPLETED', 'CANCELLED'])->default('PENDING')->change();
            // Reverting status enum to original values (exit_price, pnl, closed_at remain as they were in original table)
        });
    }
};