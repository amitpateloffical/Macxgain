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
            $table->string('bank_name')->nullable()->change();
            $table->string('account_holder_name')->nullable()->change();
            $table->string('account_number')->nullable()->change();
            $table->string('ifsc_code')->nullable()->change();
            $table->text('qr_code')->nullable()->change();
            $table->text('barcode_image')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('admin_payment_collectors', function (Blueprint $table) {
            $table->string('bank_name')->nullable(false)->change();
            $table->string('account_holder_name')->nullable(false)->change();
            $table->string('account_number')->nullable(false)->change();
            $table->string('ifsc_code')->nullable(false)->change();
            $table->text('qr_code')->nullable(false)->change();
            $table->text('barcode_image')->nullable(false)->change();
        });
    }
};
