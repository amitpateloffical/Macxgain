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
        Schema::create('user_watchlists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('symbol', 50);
            $table->string('name', 255)->nullable();
            $table->decimal('price', 10, 2)->default(0);
            $table->decimal('change', 10, 2)->default(0);
            $table->decimal('change_percent', 8, 4)->default(0);
            $table->decimal('high', 10, 2)->default(0);
            $table->decimal('low', 10, 2)->default(0);
            $table->decimal('open', 10, 2)->default(0);
            $table->decimal('prev_close', 10, 2)->default(0);
            $table->bigInteger('volume')->default(0);
            $table->timestamp('last_updated')->nullable();
            $table->timestamps();
            
            // Ensure unique combination of user and symbol
            $table->unique(['user_id', 'symbol']);
            $table->index(['user_id', 'symbol']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_watchlists');
    }
};
