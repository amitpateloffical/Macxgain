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
        Schema::create('market_data', function (Blueprint $table) {
            $table->id();
            $table->string('symbol')->unique()->index();
            $table->decimal('ltp', 15, 4);
            $table->decimal('change', 15, 4);
            $table->decimal('change_percent', 8, 4);
            $table->decimal('high', 15, 4);
            $table->decimal('low', 15, 4);
            $table->decimal('open', 15, 4);
            $table->decimal('prev_close', 15, 4);
            $table->decimal('volume', 20, 2);
            $table->timestamp('data_timestamp');
            $table->string('data_source');
            $table->text('raw_data')->nullable(); // Encrypted JSON for additional data
            $table->boolean('is_live')->default(true);
            $table->string('market_status')->default('OPEN');
            $table->timestamps();
            
            // Indexes for better performance
            $table->index(['symbol', 'data_timestamp']);
            $table->index(['is_live', 'market_status']);
            $table->index('data_timestamp');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('market_data');
    }
};
