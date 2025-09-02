<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\FetchTrueDataJob;
use App\Services\MarketStatusService;
use Illuminate\Support\Facades\Log;

class SmartTrueDataScheduler extends Command
{
    protected $signature = 'truedata:smart-schedule';
    protected $description = 'Smart scheduler that adjusts data fetch frequency based on market hours';

    private $marketStatusService;

    public function __construct(MarketStatusService $marketStatusService)
    {
        parent::__construct();
        $this->marketStatusService = $marketStatusService;
    }

    public function handle()
    {
        try {
            $marketStatus = $this->marketStatusService->getMarketStatus();
            $refreshInterval = $this->marketStatusService->getDataRefreshInterval();
            
            Log::info("Smart TrueData Scheduler - Market Status: {$marketStatus['status']}, Refresh Interval: {$refreshInterval}s");
            
            // Dispatch the job
            FetchTrueDataJob::dispatch();
            
            $this->info("🚀 Smart TrueData fetch dispatched");
            $this->info("📊 Market Status: {$marketStatus['status']}");
            $this->info("⏱️  Refresh Interval: {$refreshInterval} seconds");
            $this->info("🕐 Current Time: " . now('Asia/Kolkata')->format('H:i:s'));
            
            if ($marketStatus['status'] === 'OPEN') {
                $this->info("🟢 Market is LIVE - Real-time data streaming");
            } elseif (in_array($marketStatus['status'], ['PRE_OPEN', 'POST_CLOSE'])) {
                $this->info("🟡 Market in {$marketStatus['status']} session");
            } else {
                $this->info("🔴 Market is CLOSED - Limited data updates");
            }
            
        } catch (\Exception $e) {
            Log::error('Smart TrueData Scheduler Error: ' . $e->getMessage());
            $this->error('❌ Error in smart scheduler: ' . $e->getMessage());
        }
    }
}
