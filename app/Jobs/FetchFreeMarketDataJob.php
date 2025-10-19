<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Models\MarketData;
use App\Services\MarketStatusService;
use App\Services\FreeMarketDataService;

class FetchFreeMarketDataJob implements ShouldQueue
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
            Log::info('FetchFreeMarketDataJob: Starting free market data fetch...');
            
            // Get market status
            $marketStatusService = new MarketStatusService();
            $isMarketLive = $marketStatusService->isMarketLive();
            $marketStatus = $isMarketLive ? 'OPEN' : 'CLOSED';
            
            // Try to get data from free APIs first
            $freeMarketDataService = new FreeMarketDataService();
            $freeDataResult = $freeMarketDataService->getLiveMarketData();
            
            if ($freeDataResult['success'] && !empty($freeDataResult['data'])) {
                // Store in database
                $storedCount = MarketData::storeMarketData($freeDataResult['data'], $isMarketLive, $marketStatus);
                
                // Also store in cache with short expiry (10 seconds) for real-time data
                $cacheKey = 'free_market_data_' . md5(implode(',', []));
                Cache::put('free_market_data', $freeDataResult['data'], 10);
                Cache::put('free_market_last_update', now(), 10);
                Cache::put('free_market_data_type', $isMarketLive ? 'LIVE' : 'HISTORICAL', 10);
                Cache::put($cacheKey, $freeDataResult, 10);
                
                Log::info("FetchFreeMarketDataJob: Free API data stored in database ({$storedCount} symbols) and cached for 30 seconds");
                Log::info("FetchFreeMarketDataJob: Data source: {$freeDataResult['source']}");
            } else {
                // Fallback: Run Python script to fetch data
                Log::info('FetchFreeMarketDataJob: Free APIs failed, trying Python script...');
                
                $result = Process::timeout(30)->run('python3 ' . base_path('free_market_data_fetch.py'));
                
                if ($result->successful()) {
                    $output = $result->output();
                    $marketData = json_decode($output, true);
                    
                    if ($marketData && is_array($marketData)) {
                        // Store in database
                        $storedCount = MarketData::storeMarketData($marketData, $isMarketLive, $marketStatus);
                        
                        // Also store in cache
                        $cacheKey = 'free_market_data_' . md5(implode(',', []));
                        Cache::put('free_market_data', $marketData, 10);
                        Cache::put('free_market_last_update', now(), 10);
                        Cache::put('free_market_data_type', $isMarketLive ? 'LIVE' : 'HISTORICAL', 10);
                        Cache::put($cacheKey, ['success' => true, 'data' => $marketData, 'source' => 'Python Script'], 10);
                        
                        Log::info("FetchFreeMarketDataJob: Python script data stored in database ({$storedCount} symbols) and cached for 30 seconds");
                    } else {
                        Log::error('FetchFreeMarketDataJob: Python script returned invalid JSON');
                    }
                } else {
                    Log::error('FetchFreeMarketDataJob: Python script failed: ' . $result->errorOutput());
                }
            }
            
        } catch (\Exception $e) {
            Log::error('FetchFreeMarketDataJob: Exception - ' . $e->getMessage());
        }
    }
}
