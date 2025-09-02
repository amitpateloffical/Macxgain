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

class FetchTrueDataJob implements ShouldQueue
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
            Log::info('FetchTrueDataJob: Starting TrueData fetch...');
            
            // Run Python script to fetch data
            $result = Process::timeout(10)->run('python3 truedata_test.py');
            
            if ($result->successful()) {
                $output = $result->output();
                Log::info('FetchTrueDataJob: Python script executed successfully');
                
                // Parse the output and extract market data
                $marketData = $this->parsePythonOutput($output);
                
                if (!empty($marketData)) {
                    // Store in cache for 1 hour (3600 seconds)
                    Cache::put('truedata_live_data', $marketData, 3600);
                    Cache::put('truedata_last_update', now(), 3600);
                    
                    Log::info('FetchTrueDataJob: Market data cached successfully - ' . count($marketData) . ' symbols');
                } else {
                    Log::warning('FetchTrueDataJob: No market data parsed from Python output');
                }
                
            } else {
                Log::error('FetchTrueDataJob: Python script failed - ' . $result->errorOutput());
            }
            
        } catch (\Exception $e) {
            Log::error('FetchTrueDataJob: Error - ' . $e->getMessage());
        }
    }
    
    /**
     * Parse Python script output to extract market data
     */
    private function parsePythonOutput($output): array
    {
        $marketData = [];
        
        try {
            // Split output into lines
            $lines = explode("\n", $output);
            
            foreach ($lines as $line) {
                // Look for JSON data lines
                if (strpos($line, 'Data #') !== false && strpos($line, '{') !== false) {
                    // Extract JSON part
                    $jsonStart = strpos($line, '{');
                    if ($jsonStart !== false) {
                        $jsonPart = substr($line, $jsonStart);
                        
                        $data = json_decode($jsonPart, true);
                        if ($data) {
                            $this->processMarketData($data, $marketData);
                        }
                    }
                }
            }
            
        } catch (\Exception $e) {
            Log::error('ParsePythonOutput Error: ' . $e->getMessage());
        }
        
        return $marketData;
    }
    
    /**
     * Process individual market data entries
     */
    private function processMarketData($data, &$marketData)
    {
        // Handle symbol list data
        if (isset($data['symbollist']) && is_array($data['symbollist'])) {
            foreach ($data['symbollist'] as $symbolData) {
                if (is_array($symbolData) && count($symbolData) >= 4) {
                    $symbol = $symbolData[0];
                    $price = floatval($symbolData[3]);
                    $high = floatval($symbolData[7]);
                    $low = floatval($symbolData[8]);
                    $open = floatval($symbolData[9]);
                    $prevClose = floatval($symbolData[10]);
                    
                    $marketData[$symbol] = [
                        'symbol' => $symbol,
                        'ltp' => $price,
                        'high' => $high,
                        'low' => $low,
                        'open' => $open,
                        'prev_close' => $prevClose,
                        'change' => $price - $prevClose,
                        'change_percent' => $prevClose > 0 ? (($price - $prevClose) / $prevClose) * 100 : 0,
                        'timestamp' => now()->toISOString(),
                        'source' => 'TrueData Live'
                    ];
                }
            }
        }
        
        // Handle trade data
        if (isset($data['trade']) && is_array($data['trade']) && count($data['trade']) >= 3) {
            $symbolId = $data['trade'][0];
            $price = floatval($data['trade'][2]);
            
            // Map symbol ID to symbol name (you might need to expand this mapping)
            $symbolName = $this->mapSymbolIdToName($symbolId);
            
            if ($symbolName) {
                $marketData[$symbolName] = array_merge($marketData[$symbolName] ?? [], [
                    'ltp' => $price,
                    'last_trade_time' => now()->toISOString(),
                    'source' => 'TrueData Live Trade'
                ]);
            }
        }
        
        // Handle bid/ask data
        if (isset($data['bidask']) && is_array($data['bidask']) && count($data['bidask']) >= 5) {
            $symbolId = $data['bidask'][0];
            $bid = floatval($data['bidask'][2]);
            $ask = floatval($data['bidask'][4]);
            
            $symbolName = $this->mapSymbolIdToName($symbolId);
            
            if ($symbolName) {
                $marketData[$symbolName] = array_merge($marketData[$symbolName] ?? [], [
                    'bid' => $bid,
                    'ask' => $ask,
                    'spread' => $ask - $bid,
                    'last_bidask_time' => now()->toISOString(),
                    'source' => 'TrueData Live BidAsk'
                ]);
            }
        }
    }
    
    /**
     * Map symbol ID to symbol name
     */
    private function mapSymbolIdToName($symbolId): ?string
    {
        $mapping = [
            '200000001' => 'NIFTY 50',
            '200000004' => 'NIFTY BANK',
            '800000372' => 'MCXCOMPDEX',
            '100000011' => 'AARTIIND',
            '100000243' => 'BRITANNIA',
            '100000310' => 'COLPAL',
            '100000382' => 'DMART',
            '100000409' => 'EICHERMOT',
            '100000508' => 'GILLETTE',
            '100000589' => 'HDFCBANK',
            '100000647' => 'ICICIBANK',
            '100000781' => 'JKTYRE',
            '100000807' => 'KAJARIACER',
            '100000893' => 'LICHSGFIN',
            '100000995' => 'MINDTREE',
            '100001105' => 'OFSS',
            '100001182' => 'PNB',
            '100001229' => 'QUICKHEAL',
            '100001262' => 'RELIANCE',
            '100001337' => 'SBIN',
            '100001528' => 'TCS',
            '100001598' => 'UJJIVAN',
            '100001692' => 'WIPRO',
            '100001700' => 'YESBANK',
            '100001701' => 'ZEEL',
            '900000596' => 'NIFTY-I',
            '900000110' => 'BANKNIFTY-I',
            '900000840' => 'UPL-I',
            '900000846' => 'VEDL-I',
            '900000852' => 'VOLTAS-I',
            '900000870' => 'ZEEL-I',
            '950000072' => 'CRUDEOIL-I',
            '950000114' => 'GOLDM-I',
            '950000182' => 'SILVERM-I',
            '950000026' => 'COPPER-I',
            '950000172' => 'SILVER-I',
        ];
        
        return $mapping[$symbolId] ?? null;
    }
}