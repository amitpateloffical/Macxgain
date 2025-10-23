<?php

namespace App\Jobs;

use App\Services\FreeMarketDataService;
use App\Models\MarketData;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class UpdateMarketDataJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            Log::info("UpdateMarketDataJob: Starting scheduled market data update");
            
            $freeMarketDataService = new FreeMarketDataService();
            
            // Fetch fresh data from APIs (this will automatically store in database)
            $result = $freeMarketDataService->getLiveMarketData();
            
            if ($result['success'] && !empty($result['data'])) {
                $count = count($result['data']);
                Log::info("UpdateMarketDataJob: Successfully updated {$count} market data records from {$result['source']}");
                
                // Clear cache to ensure fresh data is served
                MarketData::clearCache();
                
            } else {
                Log::warning("UpdateMarketDataJob: Failed to fetch fresh market data - {$result['message']}");
            }
            
        } catch (\Exception $e) {
            Log::error("UpdateMarketDataJob: Error updating market data - " . $e->getMessage());
        }
    }
}
