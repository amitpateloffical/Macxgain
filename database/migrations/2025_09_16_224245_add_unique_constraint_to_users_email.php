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
        // First, clean up duplicate emails before adding unique constraint
        $this->cleanupDuplicateEmails();
        
        // Clean up duplicate phones before adding unique constraint
        $this->cleanupDuplicatePhones();
        
        // Add unique constraint to email field
        Schema::table('users', function (Blueprint $table) {
            $table->unique('email', 'users_email_unique');
        });
        
        // Add unique constraint to phone field
        Schema::table('users', function (Blueprint $table) {
            $table->unique('phone', 'users_phone_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique('users_email_unique');
            $table->dropUnique('users_phone_unique');
        });
    }
    
    /**
     * Clean up duplicate emails by keeping the oldest record
     */
    private function cleanupDuplicateEmails()
    {
        // Get duplicate emails
        $duplicates = DB::table('users')
            ->select('email')
            ->groupBy('email')
            ->havingRaw('COUNT(*) > 1')
            ->get();
            
        foreach ($duplicates as $duplicate) {
            // Get all users with this email, ordered by ID (oldest first)
            $allUsers = DB::table('users')
                ->where('email', $duplicate->email)
                ->orderBy('id')
                ->get(['id']);
                
            // Keep the first (oldest) record, delete the rest
            $usersToDelete = $allUsers->slice(1);
                
            foreach ($usersToDelete as $user) {
                // Delete related data first
                DB::table('money_requests')->where('request_create_for', $user->id)->delete();
                DB::table('withdrawal_requests')->where('request_create_for', $user->id)->delete();
                DB::table('wallet_transactions')->where('user_id', $user->id)->delete();
                
                // Delete the user
                DB::table('users')->where('id', $user->id)->delete();
            }
        }
    }
    
    /**
     * Clean up duplicate phones by keeping the oldest record
     */
    private function cleanupDuplicatePhones()
    {
        // Get duplicate phones
        $duplicates = DB::table('users')
            ->select('phone')
            ->groupBy('phone')
            ->havingRaw('COUNT(*) > 1')
            ->get();
            
        foreach ($duplicates as $duplicate) {
            // Get all users with this phone, ordered by ID (oldest first)
            $allUsers = DB::table('users')
                ->where('phone', $duplicate->phone)
                ->orderBy('id')
                ->get(['id']);
                
            // Keep the first (oldest) record, delete the rest
            $usersToDelete = $allUsers->slice(1);
                
            foreach ($usersToDelete as $user) {
                // Delete related data first
                DB::table('money_requests')->where('request_create_for', $user->id)->delete();
                DB::table('withdrawal_requests')->where('request_create_for', $user->id)->delete();
                DB::table('wallet_transactions')->where('user_id', $user->id)->delete();
                
                // Delete the user
                DB::table('users')->where('id', $user->id)->delete();
            }
        }
    }
};
