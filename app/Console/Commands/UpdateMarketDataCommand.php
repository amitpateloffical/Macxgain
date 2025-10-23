<?php

namespace App\Console\Commands;

use App\Jobs\UpdateMarketDataJob;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class UpdateMarketDataCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'market:update {--force : Force update even if data is fresh}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update market data from NSE free APIs and store in database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting market data update...');
        
        try {
            // Dispatch the job
            UpdateMarketDataJob::dispatch();
            
            $this->info('Market data update job dispatched successfully');
            $this->info('Check logs for detailed progress');
            
            return 0;
            
        } catch (\Exception $e) {
            $this->error('Failed to dispatch market data update job: ' . $e->getMessage());
            Log::error('UpdateMarketDataCommand: ' . $e->getMessage());
            
            return 1;
        }
    }
}
