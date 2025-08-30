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
        Schema::table('money_requests', function (Blueprint $table) {
            // Drop the transaction_id column
            $table->dropColumn('transaction_id');
            
            // Drop the description column
            $table->dropColumn('description');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('money_requests', function (Blueprint $table) {
            // Re-add the transaction_id column
            $table->string('transaction_id')->unique()->after('id');
            
            // Re-add the description column
            $table->text('description')->nullable()->after('amount');
        });
    }
};
