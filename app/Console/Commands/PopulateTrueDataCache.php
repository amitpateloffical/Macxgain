<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\FetchTrueDataJob;
use App\Models\MarketData;
use Illuminate\Support\Facades\File;

class PopulateTrueDataCache extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'truedata:populate-cache';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Populate TrueData cache with live market data';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Populating TrueData cache and database...');
        
        // First, try to populate from existing JSON file if available
        $jsonFile = base_path('market_data.json');
        if (File::exists($jsonFile)) {
            $this->info('Found existing market_data.json file, importing to database...');
            
            try {
                $jsonContent = File::get($jsonFile);
                $marketData = json_decode($jsonContent, true);
                
                if (!empty($marketData) && is_array($marketData)) {
                    $storedCount = MarketData::storeMarketData($marketData, true, 'OPEN');
                    $this->info("Successfully imported {$storedCount} symbols from JSON file to database.");
                } else {
                    $this->warn('JSON file is empty or invalid.');
                }
            } catch (\Exception $e) {
                $this->error('Error importing from JSON file: ' . $e->getMessage());
            }
        }
        
        // Dispatch the job to fetch fresh data
        FetchTrueDataJob::dispatch();
        
        $this->info('TrueData cache population job dispatched successfully!');
        $this->info('Cache and database will be populated with live market data.');
        
        // Show database stats
        $stats = MarketData::getMarketDataStats();
        $this->info("Database stats: {$stats['total_symbols']} total symbols, {$stats['live_symbols']} live, {$stats['historical_symbols']} historical");
        
        return 0;
    }
}