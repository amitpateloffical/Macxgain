<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Process;

class TestTrueDataPython extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'truedata:python-test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test TrueData connection using Python script';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Testing TrueData connection using Python script...');
        
        try {
            // Run Python script for 10 seconds
            $this->info('Running Python script for 10 seconds...');
            
            $result = Process::timeout(10)->run('python3 truedata_test.py');
            
            if ($result->successful()) {
                $this->info('✅ Python script executed successfully!');
                $this->line('Output:');
                $this->line($result->output());
            } else {
                $this->error('❌ Python script failed:');
                $this->line($result->errorOutput());
            }
            
        } catch (\Exception $e) {
            $this->error('❌ Error: ' . $e->getMessage());
        }
        
        $this->info('Test completed.');
    }
}
