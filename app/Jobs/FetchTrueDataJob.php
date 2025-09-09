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
            
            // Get market status
            $marketStatusService = new MarketStatusService();
            $isMarketLive = $marketStatusService->isMarketLive();
            $marketStatus = $isMarketLive ? 'OPEN' : 'CLOSED';
            
            // Try to get data from the running WebSocket daemon first
            $webSocketData = $this->getDataFromWebSocketDaemon();
            
            if (!empty($webSocketData)) {
                // Store in database
                $storedCount = MarketData::storeMarketData($webSocketData, $isMarketLive, $marketStatus);
                
                // Also store in cache with short expiry (30 seconds) for real-time data
                Cache::put('truedata_live_data', $webSocketData, 30);
                Cache::put('truedata_last_update', now(), 30);
                Cache::put('truedata_data_type', $isMarketLive ? 'LIVE' : 'HISTORICAL', 30);
                
                Log::info("FetchTrueDataJob: WebSocket data stored in database ({$storedCount} symbols) and cached for 30 seconds");
            } else {
                // Fallback: Run Python script to fetch data
                $result = Process::timeout(45)->run('python3 truedata_fetch.py');
                
                if ($result->successful()) {
                    $output = $result->output();
                    Log::info('FetchTrueDataJob: Python script executed successfully');
                    
                    // Parse the output and extract market data
                    $marketData = $this->parsePythonOutput($output);
                    
                    if (!empty($marketData)) {
                        // Store in database
                        $storedCount = MarketData::storeMarketData($marketData, $isMarketLive, $marketStatus);
                        
                        // Also store in cache with short expiry (30 seconds) for real-time data
                        Cache::put('truedata_live_data', $marketData, 30);
                        Cache::put('truedata_last_update', now(), 30);
                        Cache::put('truedata_data_type', $isMarketLive ? 'LIVE' : 'HISTORICAL', 30);
                        
                        Log::info("FetchTrueDataJob: Market data stored in database ({$storedCount} symbols) and cached for 30 seconds");
                    } else {
                        Log::warning('FetchTrueDataJob: No market data parsed from Python output');
                    }
                    
                } else {
                    Log::error('FetchTrueDataJob: Python script failed - ' . $result->errorOutput());
                }
            }
            
        } catch (\Exception $e) {
            Log::error('FetchTrueDataJob: Error - ' . $e->getMessage());
        }
    }
    
    /**
     * Get data from the running WebSocket daemon
     */
    private function getDataFromWebSocketDaemon(): array
    {
        try {
            // Check if market_data.json file exists and is recent (less than 30 seconds old)
            $jsonFile = base_path('market_data.json');
            
            if (file_exists($jsonFile)) {
                $fileTime = filemtime($jsonFile);
                $currentTime = time();
                
                // Only use file if it's reasonably fresh (less than 120 seconds old)
                if (($currentTime - $fileTime) < 120) {
                    $jsonContent = file_get_contents($jsonFile);
                    $webSocketData = json_decode($jsonContent, true);
                    
                    if (!empty($webSocketData) && is_array($webSocketData)) {
                        // Convert WebSocket data format to our expected format
                        $marketData = [];
                        foreach ($webSocketData as $symbol => $data) {
                            $marketData[$symbol] = [
                                'symbol' => $data['symbol'] ?? $symbol,
                                'ltp' => $data['ltp'] ?? 0,
                                'high' => $data['high'] ?? 0,
                                'low' => $data['low'] ?? 0,
                                'open' => $data['open'] ?? 0,
                                'prev_close' => $data['prev_close'] ?? 0,
                                'change' => $data['change'] ?? 0,
                                'change_percent' => $data['change_percent'] ?? 0,
                                'volume' => $data['volume'] ?? 0,
                                'timestamp' => $data['timestamp'] ?? now()->toISOString(),
                                'data_source' => $data['data_source'] ?? ($data['source'] ?? 'TrueData WebSocket Live')
                            ];
                        }
                        
                        Log::info('FetchTrueDataJob: Retrieved ' . count($marketData) . ' symbols from WebSocket JSON file');
                        return $marketData;
                    }
                } else {
                    Log::info('FetchTrueDataJob: JSON file is too old (' . ($currentTime - $fileTime) . ' seconds)');
                }
            }
            
            return [];
        } catch (\Exception $e) {
            Log::error('FetchTrueDataJob: Error getting WebSocket data - ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Parse Python script output to extract market data
     */
    private function parsePythonOutput($output): array
    {
        $marketData = [];

        try {
            // First try to decode the entire output as JSON (python prints full JSON)
            $decoded = json_decode(trim($output), true);
            if (json_last_error() === JSON_ERROR_NONE && !empty($decoded)) {
                // If decoded is a map of symbol => data
                if (is_array($decoded)) {
                    foreach ($decoded as $symbol => $data) {
                        if (is_array($data)) {
                            $marketData[$symbol] = [
                                'symbol' => $data['symbol'] ?? $symbol,
                                'ltp' => $data['ltp'] ?? 0,
                                'high' => $data['high'] ?? 0,
                                'low' => $data['low'] ?? 0,
                                'open' => $data['open'] ?? 0,
                                'prev_close' => $data['prev_close'] ?? 0,
                                'change' => $data['change'] ?? 0,
                                'change_percent' => $data['change_percent'] ?? 0,
                                'volume' => $data['volume'] ?? 0,
                                'timestamp' => $data['timestamp'] ?? now()->toISOString(),
                                'data_source' => $data['data_source'] ?? ($data['source'] ?? 'TrueData Real WebSocket')
                            ];
                        }
                    }
                }
            } else {
                // Fallback: split output into lines and process JSON fragments (old behavior)
                $lines = explode("\n", $output);
                foreach ($lines as $line) {
                    $line = trim($line);
                    if ($line === '') { continue; }
                    // Extract any JSON object from the line
                    $jsonStart = strpos($line, '{');
                    if ($jsonStart !== false) {
                        $jsonPart = substr($line, $jsonStart);
                        $data = json_decode($jsonPart, true);
                        if (json_last_error() === JSON_ERROR_NONE && $data) {
                            $this->processMarketData($data, $marketData);
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error('ParsePythonOutput Error: ' . $e->getMessage());
        }

        // As a final fallback, if still empty, try reading market_data.json directly
        if (empty($marketData)) {
            try {
                $jsonFile = base_path('market_data.json');
                if (file_exists($jsonFile)) {
                    $content = file_get_contents($jsonFile);
                    $fileData = json_decode($content, true);
                    if (is_array($fileData)) {
                        foreach ($fileData as $symbol => $data) {
                            if (is_array($data)) {
                                $marketData[$symbol] = [
                                    'symbol' => $data['symbol'] ?? $symbol,
                                    'ltp' => $data['ltp'] ?? 0,
                                    'high' => $data['high'] ?? 0,
                                    'low' => $data['low'] ?? 0,
                                    'open' => $data['open'] ?? 0,
                                    'prev_close' => $data['prev_close'] ?? 0,
                                    'change' => $data['change'] ?? 0,
                                    'change_percent' => $data['change_percent'] ?? 0,
                                    'volume' => $data['volume'] ?? 0,
                                    'timestamp' => $data['timestamp'] ?? now()->toISOString(),
                                    'data_source' => $data['data_source'] ?? ($data['source'] ?? 'TrueData Real WebSocket')
                                ];
                            }
                        }
                    }
                }
            } catch (\Exception $e) {
                Log::error('ParsePythonOutput Fallback Error: ' . $e->getMessage());
            }
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