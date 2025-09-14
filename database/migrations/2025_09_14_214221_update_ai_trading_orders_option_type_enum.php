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
            // Modify the option_type column to include 'STOCK'
            $table->enum('option_type', ['CALL', 'PUT', 'STOCK'])->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ai_trading_orders', function (Blueprint $table) {
            // Revert back to original enum values
            $table->enum('option_type', ['CALL', 'PUT'])->change();
        });
    }
};
