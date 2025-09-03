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
            $result = Process::timeout(15)->run('python3 truedata_fetch.py');
            
            if ($result->successful()) {
                $output = $result->output();
                Log::info('FetchTrueDataJob: Python script executed successfully');
                
                // Parse the output and extract market data
                $marketData = $this->parsePythonOutput($output);
                
                if (!empty($marketData)) {
                    // Store in cache with short expiry (30 seconds) for real-time data
                    Cache::put('truedata_live_data', $marketData, 30);
                    Cache::put('truedata_last_update', now(), 30);
                    
                    Log::info('FetchTrueDataJob: Market data cached for 30 seconds - ' . count($marketData) . ' symbols');
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
            // Try to parse the entire output as JSON first (for truedata_fetch.py)
            $data = json_decode($output, true);
            if ($data && is_array($data)) {
                // Direct JSON output from truedata_fetch.py
                $marketData = $data;
                Log::info('FetchTrueDataJob: Parsed direct JSON output - ' . count($marketData) . ' symbols');
                return $marketData;
            }
            
            // Fallback: Split output into lines and look for JSON data lines (for truedata_test.py)
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
            // Major Indices
            '200000001' => 'NIFTY 50',
            '200000004' => 'NIFTY BANK',
            '200000002' => 'NIFTY IT',
            '400000001' => 'SENSEX',
            '400000012' => 'BANKEX',
            '200000013' => 'NIFTY FMCG',
            '200000019' => 'NIFTY AUTO',
            '200000015' => 'NIFTY PHARMA',
            '200000021' => 'NIFTY METAL',
            '200000012' => 'NIFTY ENERGY',
            '200000009' => 'NIFTY REALTY',
            '200000017' => 'NIFTY PSU BANK',
            '200000039' => 'NIFTY PVT BANK',
            '200000020' => 'NIFTY MEDIA',
            '200000010' => 'NIFTY INFRA',
            '200000025' => 'NIFTY COMMODITIES',
            
            // Large Cap Stocks
            '100001262' => 'RELIANCE',
            '100001528' => 'TCS',
            '100000589' => 'HDFCBANK',
            '100000647' => 'ICICIBANK',
            '100001337' => 'SBIN',
            '100000213' => 'BHARTIARTL',
            '100000737' => 'ITC',
            '100000854' => 'KOTAKBANK',
            '100000908' => 'LT',
            '100000619' => 'HINDUNILVR',
            '100000129' => 'ASIANPAINT',
            '100000961' => 'MARUTI',
            '100000154' => 'AXISBANK',
            '100001061' => 'NESTLEIND',
            '100001600' => 'ULTRACEMCO',
            '100001474' => 'SUNPHARMA',
            '100001562' => 'TITAN',
            '100001194' => 'POWERGRID',
            '100001099' => 'NTPC',
            '100001116' => 'ONGC',
            
            // Mid Cap Stocks
            '100000011' => 'AARTIIND',
            '100000243' => 'BRITANNIA',
            '100000310' => 'COLPAL',
            '100000382' => 'DMART',
            '100000409' => 'EICHERMOT',
            '100000508' => 'GILLETTE',
            '100000781' => 'JKTYRE',
            '100000807' => 'KAJARIACER',
            '100000893' => 'LICHSGFIN',
            '100000995' => 'MINDTREE',
            '100001105' => 'OFSS',
            '100001182' => 'PNB',
            '100001229' => 'QUICKHEAL',
            '100001598' => 'UJJIVAN',
        ];
        
        return $mapping[$symbolId] ?? null;
    }
}