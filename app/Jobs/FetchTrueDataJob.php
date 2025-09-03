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
use App\Services\MarketStatusService;

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
            $marketStatusService = new MarketStatusService();
            $marketStatus = $marketStatusService->getMarketStatus();
            $isMarketLive = $marketStatusService->isMarketLive();
            
            Log::info('FetchTrueDataJob: Starting TrueData fetch...', [
                'market_status' => $marketStatus['status'],
                'is_live' => $isMarketLive,
                'reason' => $marketStatus['reason'] ?? 'Unknown'
            ]);
            
            // Check if we should fetch live data or use historical data
            if ($isMarketLive) {
                // Market is open - fetch live data
                $this->fetchLiveData();
            } else {
                // Market is closed - use historical data (cached, no refresh)
                $this->useHistoricalData();
            }
            
        } catch (\Exception $e) {
            Log::error('FetchTrueDataJob: Error in handle method: ' . $e->getMessage());
        }
    }
    
    /**
     * Fetch live data when market is open
     */
    private function fetchLiveData(): void
    {
        try {
            // Run Python script to fetch live data
            $result = Process::timeout(15)->run('python3 truedata_fetch.py');
            
            if ($result->successful()) {
                $output = $result->output();
                Log::info('FetchTrueDataJob: Live data fetched successfully');
                
                // Parse the output and extract market data
                $marketData = $this->parsePythonOutput($output);
                
                if (!empty($marketData)) {
                    // Store in cache with short expiry (30 seconds) for live data
                    Cache::put('truedata_live_data', $marketData, 30);
                    Cache::put('truedata_last_update', now(), 30);
                    Cache::put('truedata_data_type', 'LIVE', 30);
                    
                    Log::info('FetchTrueDataJob: Live market data cached for 30 seconds - ' . count($marketData) . ' symbols');
                } else {
                    Log::warning('FetchTrueDataJob: No live market data parsed from Python output');
                }
                
            } else {
                Log::error('FetchTrueDataJob: Python script failed - ' . $result->errorOutput());
            }
            
        } catch (\Exception $e) {
            Log::error('FetchTrueDataJob: Error in fetchLiveData - ' . $e->getMessage());
        }
    }
    
    /**
     * Use historical data when market is closed (no refresh)
     */
    private function useHistoricalData(): void
    {
        try {
            // Check if we already have historical data cached
            $existingData = Cache::get('truedata_live_data');
            $dataType = Cache::get('truedata_data_type', 'UNKNOWN');
            
            if ($existingData && $dataType === 'HISTORICAL') {
                // Historical data already exists, no need to refresh
                Log::info('FetchTrueDataJob: Using existing historical data - no refresh needed');
                return;
            }
            
            // If no historical data exists, fetch it once and cache it
            if (!$existingData) {
                Log::info('FetchTrueDataJob: No historical data found, fetching once...');
                
                // Run Python script to fetch historical data (last trading day)
                $result = Process::timeout(15)->run('python3 truedata_fetch.py');
                
                if ($result->successful()) {
                    $output = $result->output();
                    Log::info('FetchTrueDataJob: Historical data fetched successfully');
                    
                    // Parse the output and extract market data
                    $marketData = $this->parsePythonOutput($output);
                    
                    if (!empty($marketData)) {
                        // Store in cache with long expiry (24 hours) for historical data
                        Cache::put('truedata_live_data', $marketData, 86400); // 24 hours
                        Cache::put('truedata_last_update', now(), 86400);
                        Cache::put('truedata_data_type', 'HISTORICAL', 86400);
                        
                        Log::info('FetchTrueDataJob: Historical market data cached for 24 hours - ' . count($marketData) . ' symbols');
                    } else {
                        Log::warning('FetchTrueDataJob: No historical market data parsed from Python output');
                    }
                } else {
                    Log::error('FetchTrueDataJob: Python script failed for historical data - ' . $result->errorOutput());
                }
            } else {
                // Convert existing live data to historical data
                Cache::put('truedata_data_type', 'HISTORICAL', 86400);
                Log::info('FetchTrueDataJob: Converted existing data to historical data');
            }
            
        } catch (\Exception $e) {
            Log::error('FetchTrueDataJob: Error in useHistoricalData - ' . $e->getMessage());
        }
    }
    
    /**
     * Parse Python script output to extract market data
     */
    private function parsePythonOutput($output): array
    {
        $marketData = [];
        
        try {
            // Try to parse the entire output as JSON first (new format)
            $jsonData = json_decode($output, true);
            if ($jsonData && is_array($jsonData)) {
                // New format: direct JSON output
                foreach ($jsonData as $symbol => $stockData) {
                    if (is_array($stockData) && isset($stockData['symbol'])) {
                        $marketData[$symbol] = [
                            'symbol' => $stockData['symbol'],
                            'ltp' => $stockData['ltp'] ?? 0,
                            'high' => $stockData['high'] ?? 0,
                            'low' => $stockData['low'] ?? 0,
                            'open' => $stockData['open'] ?? 0,
                            'prev_close' => $stockData['prev_close'] ?? 0,
                            'change' => $stockData['change'] ?? 0,
                            'change_percent' => $stockData['change_percent'] ?? 0,
                            'volume' => $stockData['volume'] ?? 0,
                            'timestamp' => $stockData['timestamp'] ?? now()->toISOString(),
                            'source' => $stockData['data_source'] ?? 'TrueData Live'
                        ];
                    }
                }
                return $marketData;
            }
            
            // Fallback: old format parsing
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
            '200000003' => 'SENSEX',
            '200000005' => 'FINNIFTY',
            '200000006' => 'NIFTY MIDCAP',
            '200000007' => 'BANKEX',
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
            '100001692' => 'WIPRO',
            '100001700' => 'YESBANK',
            '100001701' => 'ZEEL',
            
            // Futures
            '900000596' => 'NIFTY-I',
            '900000110' => 'BANKNIFTY-I',
            '900000840' => 'UPL-I',
            '900000846' => 'VEDL-I',
            '900000852' => 'VOLTAS-I',
            '900000870' => 'ZEEL-I',
            
            // Commodities
            '950000072' => 'CRUDEOIL-I',
            '950000114' => 'GOLDM-I',
            '950000182' => 'SILVERM-I',
            '950000026' => 'COPPER-I',
            '950000172' => 'SILVER-I',
            
            // MCX Indices
            '800000372' => 'MCXCOMPDEX',
        ];
        
        return $mapping[$symbolId] ?? null;
    }
}