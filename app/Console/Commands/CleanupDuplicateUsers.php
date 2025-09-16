<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CleanupDuplicateUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:cleanup-duplicates {--dry-run : Show what would be deleted without actually deleting} {--type=both : Type of duplicates to clean (email, phone, both)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up duplicate users by email and/or phone, keeping the oldest record';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $isDryRun = $this->option('dry-run');
        $type = $this->option('type');
        
        if ($isDryRun) {
            $this->info('🔍 DRY RUN MODE - No data will be deleted');
        }
        
        $this->info('🧹 Starting duplicate user cleanup...');
        
        $totalDeleted = 0;
        
        // Handle email duplicates
        if ($type === 'email' || $type === 'both') {
            $totalDeleted += $this->cleanupEmailDuplicates($isDryRun);
        }
        
        // Handle phone duplicates
        if ($type === 'phone' || $type === 'both') {
            $totalDeleted += $this->cleanupPhoneDuplicates($isDryRun);
        }
        
        if ($isDryRun) {
            $this->info("🔍 Dry run completed. Run without --dry-run to actually delete duplicates.");
        } else {
            $this->info("✅ Cleanup completed! Deleted {$totalDeleted} duplicate users.");
        }
    }
    
    private function cleanupEmailDuplicates($isDryRun)
    {
        $this->info('📧 Checking for duplicate emails...');
        
        // Get duplicate emails
        $duplicates = DB::table('users')
            ->select('email', DB::raw('COUNT(*) as count'))
            ->groupBy('email')
            ->havingRaw('COUNT(*) > 1')
            ->get();
            
        if ($duplicates->isEmpty()) {
            $this->info('✅ No duplicate emails found!');
            return 0;
        }
        
        $this->warn("Found " . $duplicates->count() . " duplicate email(s):");
        
        $totalDeleted = 0;
        
        foreach ($duplicates as $duplicate) {
            $this->line("📧 Email: " . $duplicate->email . " (Count: " . $duplicate->count . ")");
            
            // Get all users with this email, ordered by ID (oldest first)
            $users = User::where('email', $duplicate->email)
                ->orderBy('id')
                ->get();
                
            $keepUser = $users->first(); // Keep the oldest (first) user
            $deleteUsers = $users->skip(1); // Delete the rest
            
            $this->info("  ✅ Keeping: ID {$keepUser->id} - {$keepUser->name} (Created: {$keepUser->created_at})");
            
            foreach ($deleteUsers as $user) {
                if ($isDryRun) {
                    $this->warn("  🗑️  Would delete: ID {$user->id} - {$user->name} (Created: {$user->created_at})");
                } else {
                    // Delete related data first
                    DB::table('money_requests')->where('request_create_for', $user->id)->delete();
                    DB::table('withdrawal_requests')->where('request_create_for', $user->id)->delete();
                    DB::table('wallet_transactions')->where('user_id', $user->id)->delete();
                    
                    // Delete the user
                    $user->delete();
                    
                    $this->warn("  🗑️  Deleted: ID {$user->id} - {$user->name}");
                    $totalDeleted++;
                }
            }
        }
        
        return $totalDeleted;
    }
    
    private function cleanupPhoneDuplicates($isDryRun)
    {
        $this->info('📱 Checking for duplicate phones...');
        
        // Get duplicate phones
        $duplicates = DB::table('users')
            ->select('phone', DB::raw('COUNT(*) as count'))
            ->groupBy('phone')
            ->havingRaw('COUNT(*) > 1')
            ->get();
            
        if ($duplicates->isEmpty()) {
            $this->info('✅ No duplicate phones found!');
            return 0;
        }
        
        $this->warn("Found " . $duplicates->count() . " duplicate phone(s):");
        
        $totalDeleted = 0;
        
        foreach ($duplicates as $duplicate) {
            $this->line("📱 Phone: " . $duplicate->phone . " (Count: " . $duplicate->count . ")");
            
            // Get all users with this phone, ordered by ID (oldest first)
            $users = User::where('phone', $duplicate->phone)
                ->orderBy('id')
                ->get();
                
            $keepUser = $users->first(); // Keep the oldest (first) user
            $deleteUsers = $users->skip(1); // Delete the rest
            
            $this->info("  ✅ Keeping: ID {$keepUser->id} - {$keepUser->name} (Created: {$keepUser->created_at})");
            
            foreach ($deleteUsers as $user) {
                if ($isDryRun) {
                    $this->warn("  🗑️  Would delete: ID {$user->id} - {$user->name} (Created: {$user->created_at})");
                } else {
                    // Delete related data first
                    DB::table('money_requests')->where('request_create_for', $user->id)->delete();
                    DB::table('withdrawal_requests')->where('request_create_for', $user->id)->delete();
                    DB::table('wallet_transactions')->where('user_id', $user->id)->delete();
                    
                    // Delete the user
                    $user->delete();
                    
                    $this->warn("  🗑️  Deleted: ID {$user->id} - {$user->name}");
                    $totalDeleted++;
                }
            }
        }
        
        return $totalDeleted;
    }
}
