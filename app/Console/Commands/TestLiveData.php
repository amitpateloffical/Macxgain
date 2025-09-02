<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class TestLiveData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'truedata:test-live';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test live data from Python script';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Testing live data from Python script...');
        
        $liveData = Cache::get('truedata_live_data', []);
        $lastUpdate = Cache::get('truedata_last_update', null);
        
        if (empty($liveData)) {
            $this->error('❌ No live data found in cache');
            $this->info('Run: php artisan truedata:python-test to fetch data first');
            return;
        }
        
        $this->info('✅ Live data found!');
        $this->info('Data count: ' . count($liveData) . ' symbols');
        $this->info('Last update: ' . $lastUpdate);
        
        // Show sample data
        $this->info('Sample data:');
        $count = 0;
        foreach ($liveData as $symbol => $data) {
            if ($count >= 5) break;
            
            $this->line("  {$symbol}:");
            $this->line("    LTP: ₹{$data['ltp']}");
            $this->line("    Change: ₹{$data['change']} ({$data['change_percent']}%)");
            $this->line("    High: ₹{$data['high']}, Low: ₹{$data['low']}");
            $this->line("    Source: {$data['source']}");
            $this->line("");
            $count++;
        }
        
        $this->info('✅ Test completed successfully!');
    }
}
