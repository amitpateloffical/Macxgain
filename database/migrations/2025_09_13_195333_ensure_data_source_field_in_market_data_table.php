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
        Schema::table('market_data', function (Blueprint $table) {
            // Check if data_source column exists, if not add it
            if (!Schema::hasColumn('market_data', 'data_source')) {
                $table->string('data_source')->default('TrueData Real WebSocket')->after('data_timestamp');
            }
            
            // Check if data_timestamp column exists, if not add it
            if (!Schema::hasColumn('market_data', 'data_timestamp')) {
                $table->timestamp('data_timestamp')->useCurrent()->after('volume');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('market_data', function (Blueprint $table) {
            // Only drop columns if they exist
            if (Schema::hasColumn('market_data', 'data_source')) {
                $table->dropColumn('data_source');
            }
            
            if (Schema::hasColumn('market_data', 'data_timestamp')) {
                $table->dropColumn('data_timestamp');
            }
        });
    }
};