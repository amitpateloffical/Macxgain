<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('option_chains', function (Blueprint $table) {
            $table->id();
            $table->string('symbol', 32)->index();
            $table->date('expiry_date')->index();
            $table->string('option_type', 8)->index(); // CALL or PUT
            $table->decimal('strike_price', 12, 4)->index();
            $table->decimal('ltp', 16, 4)->default(0);
            $table->decimal('bid', 16, 4)->default(0);
            $table->decimal('ask', 16, 4)->default(0);
            $table->integer('volume')->default(0);
            $table->integer('oi')->default(0);
            $table->timestamp('data_timestamp')->nullable()->index();
            $table->string('data_source', 64)->default('TrueData');
            $table->longText('raw_data')->nullable();
            $table->timestamps();

            $table->unique(['symbol', 'expiry_date', 'option_type', 'strike_price'], 'uniq_symbol_expiry_type_strike');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('option_chains');
    }
};


