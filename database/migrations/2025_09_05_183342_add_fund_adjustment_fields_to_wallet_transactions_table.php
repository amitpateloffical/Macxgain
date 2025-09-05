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
        Schema::table('wallet_transactions', function (Blueprint $table) {
            $table->text('description')->nullable()->after('remark');
            $table->string('status')->default('pending')->after('description');
            $table->unsignedInteger('admin_id')->nullable()->after('status');
            $table->string('adjustment_type')->nullable()->after('admin_id');
            $table->text('adjustment_reason')->nullable()->after('adjustment_type');
            
            $table->foreign('admin_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wallet_transactions', function (Blueprint $table) {
            $table->dropForeign(['admin_id']);
            $table->dropColumn([
                'description',
                'status',
                'admin_id',
                'adjustment_type',
                'adjustment_reason'
            ]);
        });
    }
};
