<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class PrepareFrontendData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'truedata:prepare-frontend';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Prepare live data for frontend display';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Preparing live data for frontend...');
        
        // First, fetch fresh data
        $this->info('Fetching fresh data...');
        \App\Jobs\FetchTrueDataJob::dispatch();
        
        // Wait for job to complete
        sleep(5);
        
        $liveData = Cache::get('truedata_live_data', []);
        $lastUpdate = Cache::get('truedata_last_update', null);
        
        if (empty($liveData)) {
            $this->error('❌ No live data found');
            return;
        }
        
        $this->info('✅ Live data prepared successfully!');
        $this->info('Data count: ' . count($liveData) . ' symbols');
        $this->info('Last update: ' . $lastUpdate);
        
        // Show sample data
        $this->info('Sample data for frontend:');
        $count = 0;
        foreach ($liveData as $symbol => $data) {
            if ($count >= 3) break;
            
            $this->line("  📈 {$symbol}:");
            $this->line("    💰 LTP: ₹{$data['ltp']}");
            $this->line("    📊 Change: ₹{$data['change']} ({$data['change_percent']}%)");
            $this->line("    🔺 High: ₹{$data['high']}, 🔻 Low: ₹{$data['low']}");
            $this->line("    📅 Source: {$data['source']}");
            $this->line("");
            $count++;
        }
        
        $this->info('🎉 Frontend is ready to display live data!');
        $this->info('Visit: http://localhost:8000/admin/stock-market');
    }
}
