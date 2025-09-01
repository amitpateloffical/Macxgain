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
        Schema::table('admin_payment_collectors', function (Blueprint $table) {
            // Make bank details fields nullable
            $table->string('bank_name')->nullable()->change();
            $table->string('account_holder_name')->nullable()->change();
            $table->string('account_number')->nullable()->change();
            $table->string('ifsc_code')->nullable()->change();
            
            // Make QR code required (not nullable)
            $table->text('qr_code')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('admin_payment_collectors', function (Blueprint $table) {
            // Revert back to original state
            $table->string('bank_name')->nullable(false)->change();
            $table->string('account_holder_name')->nullable(false)->change();
            $table->string('account_number')->nullable(false)->change();
            $table->string('ifsc_code')->nullable(false)->change();
            
            // Make QR code nullable again
            $table->text('qr_code')->nullable()->change();
        });
    }
};
