<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\TrueDataWebSocketManager;
use Illuminate\Support\Facades\Log;

class TestTrueDataConnection extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'truedata:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test TrueData WebSocket connection and fetch live data';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Testing TrueData WebSocket connection...');
        
        try {
            $wsManager = new TrueDataWebSocketManager();
            
            // Test connection
            $this->info('Initializing connection...');
            $result = $wsManager->initializeConnection();
            
            if ($result['success']) {
                $this->info('✅ Connection established successfully!');
                
                // Subscribe to symbols
                $this->info('Subscribing to symbols...');
                $symbols = [
                    "NIFTY 50", "NIFTY BANK", "RELIANCE", "TCS", "HDFCBANK"
                ];
                
                $subscribeResult = $wsManager->subscribeToSymbols($symbols);
                
                if ($subscribeResult['success']) {
                    $this->info('✅ Successfully subscribed to symbols');
                    
                    // Get real-time data
                    $this->info('Fetching real-time data...');
                    $dataResult = $wsManager->getRealTimeData(5); // 5 seconds timeout
                    
                    if ($dataResult['success']) {
                        $this->info('✅ Real-time data received!');
                        $this->info('Data count: ' . count($dataResult['data']));
                        $this->info('Cached data count: ' . count($dataResult['cached_data']));
                        
                        // Show some sample data
                        if (!empty($dataResult['cached_data'])) {
                            $this->info('Sample data:');
                            foreach (array_slice($dataResult['cached_data'], 0, 3, true) as $symbol => $data) {
                                $this->line("  {$symbol}: " . json_encode($data, JSON_PRETTY_PRINT));
                            }
                        }
                    } else {
                        $this->error('❌ Failed to get real-time data: ' . $dataResult['error']);
                    }
                } else {
                    $this->error('❌ Failed to subscribe to symbols: ' . $subscribeResult['error']);
                }
                
                // Show connection status
                $status = $wsManager->getConnectionStatus();
                $this->info('Connection Status:');
                $this->line('  Connected: ' . ($status['is_connected'] ? 'Yes' : 'No'));
                $this->line('  Reconnect attempts: ' . $status['reconnect_attempts']);
                $this->line('  Subscribed symbols: ' . $status['subscribed_symbols_count']);
                $this->line('  Cached data: ' . $status['cached_data_count']);
                
            } else {
                $this->error('❌ Connection failed: ' . $result['error']);
            }
            
        } catch (\Exception $e) {
            $this->error('❌ Error: ' . $e->getMessage());
            Log::error('TrueData Test Command Error: ' . $e->getMessage());
        }
        
        $this->info('Test completed.');
    }
}