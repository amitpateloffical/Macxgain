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
            $table->decimal('unit_price', 10, 2)->nullable()->after('strike_price');
            $table->integer('lot_size')->nullable()->after('unit_price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ai_trading_orders', function (Blueprint $table) {
            $table->dropColumn(['unit_price', 'lot_size']);
        });
    }
};
