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
            $table->decimal('exit_price', 10, 2)->nullable()->after('total_amount');
            $table->decimal('pnl', 12, 2)->nullable()->after('exit_price');
            $table->timestamp('closed_at')->nullable()->after('pnl');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ai_trading_orders', function (Blueprint $table) {
            $table->enum('status', ['PENDING', 'COMPLETED', 'CANCELLED'])->default('PENDING')->change();
            $table->dropColumn(['exit_price', 'pnl', 'closed_at']);
        });
    }
};