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
            // Add timestamp column if it doesn't exist
            if (!Schema::hasColumn('market_data', 'timestamp')) {
                $table->timestamp('timestamp')->nullable()->after('volume');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('market_data', function (Blueprint $table) {
            if (Schema::hasColumn('market_data', 'timestamp')) {
                $table->dropColumn('timestamp');
            }
        });
    }
};