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
            $table->decimal('exit_unit_price', 10, 2)->nullable()->after('exit_price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ai_trading_orders', function (Blueprint $table) {
            $table->dropColumn('exit_unit_price');
        });
    }
};


