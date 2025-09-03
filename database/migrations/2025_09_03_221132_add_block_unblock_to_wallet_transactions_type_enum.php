<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Modify the enum to include 'block' and 'unblock' values
        DB::statement("ALTER TABLE wallet_transactions MODIFY COLUMN type ENUM('credit', 'debit', 'block', 'unblock') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to original enum values
        DB::statement("ALTER TABLE wallet_transactions MODIFY COLUMN type ENUM('credit', 'debit') NOT NULL");
    }
};