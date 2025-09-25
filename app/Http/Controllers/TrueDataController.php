<?php

namespace App\Http\Controllers;

use App\Services\TrueDataService;
use App\Services\MarketStatusService;
use App\Services\OptionsService;
use App\Models\MarketData;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class TrueDataController extends Controller
{
    private $trueDataService;
    private $marketStatusService;
    private $optionsService;

    public function __construct(TrueDataService $trueDataService, MarketStatusService $marketStatusService, OptionsService $optionsService)
    {
        $this->trueDataService = $trueDataService;
        $this->marketStatusService = $marketStatusService;
        $this->optionsService = $optionsService;
    }

    /**
     * Get underlying price from local market_data.json (acts as ~1m delayed source)
     */
    private function getUnderlyingFromMarketData(string $symbol): ?float
    {
        try {
            $path = base_path('market_data.json');
            if (!file_exists($path)) { return null; }
            $json = file_get_contents($path);
            $data = json_decode($json, true);
            if (!is_array($data)) { return null; }

            // Map symbol names used by options to keys in market_data.json
            $map = [
                'NIFTY' => 'NIFTY 50',
                'NIFTY 50' => 'NIFTY 50',
                'BANKNIFTY' => 'NIFTY BANK',
                'NIFTY BANK' => 'NIFTY BANK',
            ];
            $key = $map[$symbol] ?? $symbol;
            if (isset($data[$key]['ltp'])) {
                return (float)$data[$key]['ltp'];
            }
            return null;
        } catch (\Exception $e) {
            \Log::warning('Failed reading market_data.json: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get market status (Open/Closed with trading hours)
     */
    public function getMarketStatus(): JsonResponse
    {
        try {
            // Simple market hours check for Indian stock market (NSE/BSE)
            $currentTime = now('Asia/Kolkata');
            $currentHour = (int) $currentTime->format('H');
            $currentMinute = (int) $currentTime->format('i');
            $currentDay = $currentTime->format('N'); // 1 (Monday) to 7 (Sunday)
            
            // Market hours: Monday to Friday, 9:15 AM to 3:30 PM IST
            $isWeekday = $currentDay >= 1 && $currentDay <= 5; // Monday to Friday
            $isMarketHours = false;
            
            if ($isWeekday) {
                $currentTimeMinutes = ($currentHour * 60) + $currentMinute;
                $marketOpenMinutes = (9 * 60) + 15; // 9:15 AM
                $marketCloseMinutes = (15 * 60) + 30; // 3:30 PM
                
                $isMarketHours = $currentTimeMinutes >= $marketOpenMinutes && $currentTimeMinutes <= $marketCloseMinutes;
            }
            
            $marketStatus = [
                'is_open' => $isMarketHours,
                'current_time' => $currentTime->format('H:i:s'),
                'current_date' => $currentTime->format('Y-m-d'),
                'market_status' => $isMarketHours ? 'OPEN' : 'CLOSED',
                'next_open_time' => $isMarketHours ? null : $this->getNextMarketOpenTime($currentTime),
                'timezone' => 'Asia/Kolkata'
            ];
            
            return response()->json([
                'success' => true,
                'data' => $marketStatus,
                'message' => 'Market status retrieved successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting market status: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to get market status',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    private function getNextMarketOpenTime($currentTime)
    {
        $nextOpen = $currentTime->copy();
        
        // If it's weekend, move to Monday
        if ($nextOpen->format('N') > 5) {
            $nextOpen->next('Monday');
        }
        // If it's after market hours on weekday, move to next day
        elseif ($nextOpen->format('H') >= 16 || ($nextOpen->format('H') == 15 && $nextOpen->format('i') > 30)) {
            $nextOpen->addDay();
            // Skip weekend
            if ($nextOpen->format('N') > 5) {
                $nextOpen->next('Monday');
            }
        }
        
        $nextOpen->setTime(9, 15, 0); // Set to 9:15 AM
        return $nextOpen->format('Y-m-d H:i:s');
    }

    /**
     * Get market dashboard data
     */
    public function getDashboardData(): JsonResponse
    {
        try {
            // Get market status
            $marketStatus = $this->marketStatusService->getMarketStatus();
            $isMarketLive = $this->marketStatusService->isMarketLive();
            
            // Get data from database (with caching)
            $marketData = MarketData::getAllMarketData(true);
            $dataType = $isMarketLive ? 'LIVE' : 'HISTORICAL';
            $lastUpdate = MarketData::getLatestDataTimestamp() ?? now();
            
            // Convert database data to array format for frontend
            $liveStocks = [];
            foreach ($marketData as $symbol => $stockData) {
                $liveStocks[] = [
                    'symbol' => $symbol,
                    'ltp' => $stockData['ltp'] ?? 0,
                    'change' => $stockData['change'] ?? 0,
                    'change_percent' => $stockData['change_percent'] ?? 0,
                    'high' => $stockData['high'] ?? 0,
                    'low' => $stockData['low'] ?? 0,
                    'open' => $stockData['open'] ?? 0,
                    'prev_close' => $stockData['prev_close'] ?? 0,
                    'volume' => $stockData['volume'] ?? 0,
                    'timestamp' => $stockData['timestamp'] ?? $lastUpdate->toISOString(),
                    'is_live' => $stockData['is_live'] ?? false,
                    'market_status' => $stockData['market_status'] ?? 'UNKNOWN'
                ];
            }
            
            // Determine data source message
            $dataSourceMessage = $this->marketStatusService->getDataSourceMessage();
            if ($dataType === 'HISTORICAL') {
                $dataSourceMessage = 'TrueData Historical Data (Market Closed)';
            } elseif ($dataType === 'LIVE') {
                $dataSourceMessage = 'TrueData Live Market Data';
            }
            
            // Prepare response data
            $responseData = [
                'market_status' => $marketStatus,
                'is_market_live' => $isMarketLive,
                'data_type' => $dataType,
                'live_stocks' => $liveStocks,
                'quotes' => $liveStocks,
                'indices' => array_slice($liveStocks, 0, 5), // First 5 as indices
                'top_gainers' => array_slice($liveStocks, 0, 10), // First 10 as gainers
                'top_losers' => array_slice($liveStocks, 5, 10), // Next 10 as losers
                'timestamp' => $lastUpdate->toISOString(),
                'data_source' => $dataSourceMessage,
                'last_updated' => $lastUpdate->format('H:i:s'),
                'last_update' => $lastUpdate->toISOString()
            ];

            return response()->json([
                'success' => true,
                'data' => $responseData,
                'message' => 'Market data loaded successfully from database'
            ]);
        } catch (\Exception $e) {
            Log::error('TrueData Dashboard Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Failed to fetch market data'
            ], 500);
        }
    }

    /**
     * Test API connection
     */
    public function testConnection(): JsonResponse
    {
        try {
            $username = config('services.truedata.username');
            $password = config('services.truedata.password');

            if (empty($username) || empty($password)) {
                return response()->json([
                    'success' => false,
                    'error' => 'TrueData credentials not configured',
                    'message' => 'Please configure TrueData username and password in .env file'
                ], 400);
            }

            $result = $this->trueDataService->initializeConnection();
            
            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => 'TrueData connection successful',
                    'credentials_configured' => true,
                    'timestamp' => now()->toISOString()
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'error' => $result['error'],
                    'message' => 'TrueData connection failed',
                    'credentials_configured' => true
                ], 503);
            }

        } catch (\Exception $e) {
            Log::error('TrueData Connection Test Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Connection test failed'
            ], 500);
        }
    }

    /**
     * Get market quotes
     */
    public function getMarketQuotes(Request $request): JsonResponse
    {
        try {
            $symbols = $request->get('symbols', []);
            $result = $this->trueDataService->getMarketQuotes($symbols);

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'data' => $result['data'],
                    'message' => 'Market quotes fetched successfully'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'error' => $result['error'],
                    'message' => 'Failed to fetch market quotes'
                ], 503);
            }

        } catch (\Exception $e) {
            Log::error('TrueData Market Quotes Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Failed to fetch market quotes'
            ], 500);
        }
    }



    /**
     * Get market indices
     */
    public function getMarketIndices(): JsonResponse
    {
        try {
            $result = $this->trueDataService->getMarketIndices();

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'data' => $result['data'],
                    'message' => 'Market indices fetched successfully'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'error' => $result['error'],
                    'message' => 'Failed to fetch market indices'
                ], 503);
            }

        } catch (\Exception $e) {
            Log::error('TrueData Market Indices Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Failed to fetch market indices'
            ], 500);
        }
    }

    /**
     * Get top gainers
     */
    public function getTopGainers(): JsonResponse
    {
        try {
            $historyService = new \App\Services\TrueDataHistoryService();
            $gainers = $historyService->getTopGainers();
            
            return response()->json([
                'success' => true,
                'data' => $gainers,
                'message' => 'Top gainers loaded successfully from TrueData API'
            ]);
        } catch (\Exception $e) {
            Log::error('TrueData Top Gainers Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Failed to fetch top gainers'
            ], 500);
        }
    }

    /**
     * Get top losers
     */
    public function getTopLosers(): JsonResponse
    {
        try {
            $historyService = new \App\Services\TrueDataHistoryService();
            $losers = $historyService->getTopLosers();
            
            return response()->json([
                'success' => true,
                'data' => $losers,
                'message' => 'Top losers loaded successfully from TrueData API'
            ]);

        } catch (\Exception $e) {
            Log::error('TrueData Top Losers Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Failed to fetch top losers'
            ], 500);
        }
    }

    /**
     * Search instruments (placeholder - would need TrueData search API)
     */
    public function searchInstruments(Request $request): JsonResponse
    {
        try {
            $query = $request->get('q', '');
            
            if (empty($query)) {
                return response()->json([
                    'success' => false,
                    'error' => 'Search query is required',
                    'message' => 'Please provide a search query'
                ], 400);
            }

            // Placeholder - TrueData search API would be implemented here
            return response()->json([
                'success' => false,
                'error' => 'Search API not implemented',
                'message' => 'Search functionality not available yet'
            ], 501);

        } catch (\Exception $e) {
            Log::error('TrueData Search Instruments Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Failed to search instruments'
            ], 500);
        }
    }

    /**
     * Get live stock data (using WebSocket service)
     */
    public function getLiveStockData(Request $request): JsonResponse
    {
        try {
            $symbols = $request->get('symbols', []);
            
            // Initialize connection if not already done
            $this->trueDataService->initializeConnection();
            
            // Subscribe to requested symbols
            if (!empty($symbols)) {
                $this->trueDataService->subscribeToSymbols($symbols);
            }

            // Get real-time data
            $result = $this->trueDataService->getRealTimeData();

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'data' => $result,
                    'message' => 'Live stock data fetched successfully'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'error' => 'Failed to fetch live data',
                    'message' => 'Real-time data unavailable'
                ], 503);
            }

        } catch (\Exception $e) {
            Log::error('TrueData Live Stock Data Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Failed to fetch live stock data'
            ], 500);
        }
    }

    /**
     * Get historical data (placeholder - would need TrueData historical API)
     */
    public function getHistoricalData(Request $request): JsonResponse
    {
        try {
            // Placeholder - TrueData historical API would be implemented here
            return response()->json([
                'success' => false,
                'error' => 'Historical data API not implemented',
                'message' => 'Historical data functionality not available yet'
            ], 501);

        } catch (\Exception $e) {
            Log::error('TrueData Historical Data Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Failed to fetch historical data'
            ], 500);
        }
    }

    /**
     * Get live data from database (with fallback to Python script)
     */
    public function getLiveDataFromPython(): JsonResponse
    {
        try {
            // Get market status
            $marketStatus = $this->marketStatusService->getMarketStatus();
            $isMarketLive = $this->marketStatusService->isMarketLive();
            
            // First try to get data from database
            $liveData = MarketData::getAllMarketData(true);
            $lastUpdate = MarketData::getLatestDataTimestamp() ?? now();
            
            // If no data in database or data is stale, trigger fresh fetch
            if (empty($liveData) || !MarketData::isDataFresh()) {
                Log::info('No fresh data in database, triggering fresh fetch...');
                
                // Trigger job to fetch fresh data
                \App\Jobs\FetchTrueDataJob::dispatch();
                
                // Wait a moment for the job to complete
                sleep(2);
                
                // Try to get fresh data from database
                $liveData = MarketData::getAllMarketData(false); // Skip cache for fresh data
                $lastUpdate = MarketData::getLatestDataTimestamp() ?? now();
                
                if (empty($liveData)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'No live data available. Please try again in a moment.',
                        'data' => [],
                        'last_update' => null,
                        'market_status' => $marketStatus
                    ], 202);
                }
            }
            
            // If WebSocket JSON has more symbols than DB, prefer it and backfill DB
            try {
                $jsonPath = base_path('market_data.json');
                if (file_exists($jsonPath)) {
                    $jsonContent = file_get_contents($jsonPath);
                    $fileData = json_decode($jsonContent, true) ?: [];
                    if (is_array($fileData) && count($fileData) > count($liveData)) {
                        $formatted = [];
                        foreach ($fileData as $symbol => $data) {
                            if (!is_array($data)) { continue; }
                            $formatted[$symbol] = [
                                'symbol' => $symbol,
                                'ltp' => (float)($data['ltp'] ?? 0),
                                'change' => (float)($data['change'] ?? 0),
                                'change_percent' => (float)($data['change_percent'] ?? 0),
                                'high' => (float)($data['high'] ?? 0),
                                'low' => (float)($data['low'] ?? 0),
                                'open' => (float)($data['open'] ?? 0),
                                'prev_close' => (float)($data['prev_close'] ?? 0),
                                'volume' => (float)($data['volume'] ?? 0),
                                'timestamp' => $data['timestamp'] ?? now()->toISOString(),
                                'data_source' => $data['data_source'] ?? 'TrueData WebSocket Live',
                                'is_live' => $isMarketLive,
                                'market_status' => $marketStatus['status']
                            ];
                        }
                        if (!empty($formatted)) {
                            $liveData = $formatted;
                            // Fire-and-forget DB backfill
                            try {
                                \App\Jobs\FetchTrueDataJob::dispatch();
                            } catch (\Exception $e) { /* ignore */ }
                        }
                    }
                }
            } catch (\Exception $e) {
                \Log::warning('Using JSON fallback failed: ' . $e->getMessage());
            }

            // Merge SENSEX from local market_data.json if missing or zeroed in DB
            try {
                $jsonPath = base_path('market_data.json');
                if (file_exists($jsonPath)) {
                    $jsonContent = file_get_contents($jsonPath);
                    $fileData = json_decode($jsonContent, true);
                    if (is_array($fileData) && isset($fileData['SENSEX'])) {
                        $sx = $fileData['SENSEX'];
                        $sensexValid = is_array($sx) && isset($sx['ltp']) && (float)$sx['ltp'] > 0;
                        $dbSensex = $liveData['SENSEX'] ?? null;
                        $dbSensexZero = !$dbSensex || ((float)($dbSensex['ltp'] ?? 0) <= 0);
                        if ($sensexValid && $dbSensexZero) {
                            $liveData['SENSEX'] = [
                                'symbol' => 'SENSEX',
                                'ltp' => (float)($sx['ltp'] ?? 0),
                                'change' => (float)($sx['change'] ?? 0),
                                'change_percent' => (float)($sx['change_percent'] ?? 0),
                                'high' => (float)($sx['high'] ?? 0),
                                'low' => (float)($sx['low'] ?? 0),
                                'open' => (float)($sx['open'] ?? 0),
                                'prev_close' => (float)($sx['prev_close'] ?? 0),
                                'volume' => (float)($sx['volume'] ?? 0),
                                'timestamp' => $sx['timestamp'] ?? now()->toISOString(),
                                'data_source' => $sx['data_source'] ?? 'TrueData WebSocket Live',
                                'is_live' => $isMarketLive,
                                'market_status' => $marketStatus['status']
                            ];
                        }
                    }
                }
            } catch (\Exception $e) {
                Log::warning('Merge SENSEX from JSON failed: ' . $e->getMessage());
            }

            // Add market status to each data item
            foreach ($liveData as $symbol => &$data) {
                $data['market_status'] = $marketStatus['status'];
                $data['is_live'] = $isMarketLive;
                $data['data_source'] = $this->marketStatusService->getDataSourceMessage();
            }
            
            return response()->json([
                'success' => true,
                'data' => $liveData,
                'last_update' => $lastUpdate,
                'data_count' => count($liveData),
                'market_status' => $marketStatus,
                'message' => 'Live data fetched successfully from database'
            ]);
            
        } catch (\Exception $e) {
            Log::error('TrueData Live Data Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Failed to fetch live data'
            ], 500);
        }
    }

    /**
     * Get live data directly from Python script (minimal caching for performance)
     */
    private function getLiveDataFromPythonScript(): array
    {
        try {
            // Check if we have very recent data (last 2 seconds)
            $cachedData = Cache::get('truedata_live_data', []);
            $lastUpdate = Cache::get('truedata_last_update', null);
            
            // If data is very recent (less than 30 seconds old), use it
            if (!empty($cachedData) && $lastUpdate && $lastUpdate->diffInSeconds(now()) < 30) {
                Log::info('Using very recent cached data - ' . count($cachedData) . ' symbols');
                return $cachedData;
            }
            
            // Otherwise, trigger fresh data fetch
            \App\Jobs\FetchTrueDataJob::dispatch();
            
            // Wait briefly for job to complete
            usleep(500000); // 0.5 seconds
            
            // Try to get fresh data
            $freshData = Cache::get('truedata_live_data', []);
            if (!empty($freshData)) {
                Log::info('Fresh data fetched - ' . count($freshData) . ' symbols');
                return $freshData;
            }
            
            Log::warning('No live data available');
            return [];
            
        } catch (\Exception $e) {
            Log::error('Error getting live data: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Trigger manual data fetch
     */
    public function triggerDataFetch(): JsonResponse
    {
        try {
            \App\Jobs\FetchTrueDataJob::dispatch();
            
            return response()->json([
                'success' => true,
                'message' => 'Data fetch job dispatched successfully'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Trigger Data Fetch Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Failed to trigger data fetch'
            ], 500);
        }
    }

    /**
     * Subscribe to specific symbols
     */
    public function subscribeToSymbols(Request $request): JsonResponse
    {
        try {
            $symbols = $request->get('symbols', []);
            
            if (empty($symbols)) {
                return response()->json([
                    'success' => false,
                    'error' => 'Symbols array is required',
                    'message' => 'Please provide symbols to subscribe'
                ], 400);
            }

            $result = $this->trueDataService->subscribeToSymbols($symbols);

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'data' => $result,
                    'message' => 'Successfully subscribed to symbols'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'error' => 'Failed to subscribe to symbols',
                    'message' => 'Symbol subscription failed'
                ], 503);
            }

        } catch (\Exception $e) {
            Log::error('TrueData Subscribe Symbols Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Failed to subscribe to symbols'
            ], 500);
        }
    }

    /**
     * Get all symbols that have options trading available
     */
    public function getValidOptionSymbols(): JsonResponse
    {
        try {
            Log::info("TrueDataController: Getting valid option symbols");
            
            $result = $this->optionsService->getValidOptionSymbols();
            
            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'data' => $result['data'],
                    'count' => count($result['data']),
                    'message' => 'Valid option symbols fetched successfully'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'error' => $result['error'],
                    'message' => $result['message']
                ], 400);
            }
        } catch (\Exception $e) {
            Log::error('TrueDataController: Exception in getValidOptionSymbols - ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Server error',
                'message' => 'Failed to fetch valid option symbols'
            ], 500);
        }
    }

    /**
     * Get available expiry dates for a symbol
     */
    public function getOptionExpiries($symbol): JsonResponse
    {
        try {
            Log::info("TrueDataController: Getting expiry dates for symbol: {$symbol}");
            
            $result = $this->optionsService->getAvailableExpiries($symbol);
            
            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'data' => $result['data'],
                    'symbol' => $result['symbol'],
                    'message' => $result['message']
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'error' => $result['error'],
                    'message' => $result['message']
                ], 400);
            }
        } catch (\Exception $e) {
            Log::error('TrueDataController: Exception in getOptionExpiries - ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Server error',
                'message' => 'Failed to fetch expiry dates'
            ], 500);
        }
    }

    /**
     * Get Option Chain for a specific symbol
     */
    public function getOptionChain($symbol): JsonResponse
    {
        try {
            // Get expiry from query parameter or use default
            $expiry = request()->query('expiry', '20250916');
            
            // Use fixed underlying price for now (will be made dynamic later)
            $underlyingPrice = 25000;

            // Try free option chain service first for real option prices
            $freeService = app(\App\Services\FreeOptionChainService::class);
            $freeResp = $freeService->getOptionChain($symbol);
            if ($freeResp['success'] && !empty($freeResp['data'])) {
                Log::info("Using free option chain service for real option prices");
                return response()->json([
                    'success' => true,
                    'data' => $freeResp['data'],
                    'symbol' => $symbol,
                    'expiry' => $expiry,
                    'underlying_price' => $underlyingPrice,
                    'data_source' => $freeResp['source'] ?? 'Free API (1-2 min delayed)',
                    'message' => 'Real option prices from free API',
                    'total_options' => count($freeResp['data'])
                ]);
            }

            // Fallback to TrueData API if NSE fails
            Log::info("NSE failed, trying TrueData API");
            $trueDataUrl = "https://api.truedata.in/getOptionChain";
            $httpResponse = \Illuminate\Support\Facades\Http::timeout(10)->get($trueDataUrl, [
                'user' => 'tdwsp759',
                'password' => 'mosh@759',
                'symbol' => $symbol,
                'expiry' => $expiry,
            ]);
            if (!$httpResponse->successful()) {
                throw new \Exception('Both NSE and TrueData APIs failed');
            }
            $trueDataResponse = $httpResponse->json();
            
            if (!$trueDataResponse || $trueDataResponse['status'] !== 'Success') {
                throw new \Exception('TrueData API returned no data');
            }
            
            // Process options with live/calculated/delayed prices
            $processedOptions = [];
            $records = $trueDataResponse['Records'] ?? [];
            $currentTime = time();
            
            // Process TrueData records - only use real prices, skip if no real data
            foreach ($records as $option) {
                if (!is_array($option) || count($option) < 7) continue;
                
                $strikePrice = (float) ($option[6] ?? 0);
                $optionType = $option[2] ?? 'CE';
                
                if ($strikePrice <= 0) continue;

                // Skip far strikes to reduce processing load (keep +/- 1000 around assumed underlying)
                if (abs($strikePrice - $underlyingPrice) > 1000) {
                    continue;
                }

                // Extract real market prices from TrueData API response
                $realLtp = (float) ($option[3] ?? 0);      // Real LTP from market (index 3)
                $realBid = (float) ($option[4] ?? 0);      // Real bid from market (index 4) 
                $realAsk = (float) ($option[5] ?? 0);      // Real ask from market (index 5)
                $realVolume = (int) ($option[10] ?? 0);    // Real volume from market (index 10)
                $realOi = (int) ($option[14] ?? 0);        // Real open interest from market (index 14)
                
                // Only use real market prices - skip if no real data
                if ($realLtp > 0) {
                    $ltp = $realLtp;
                    $bid = $realBid > 0 ? $realBid : max(0.25, $ltp * 0.95);
                    $ask = $realAsk > 0 ? $realAsk : $ltp * 1.05;
                    $volume = $realVolume;
                    $oi = $realOi;
                    
                    Log::info("Using real market prices for {$optionType} strike {$strikePrice}: LTP={$ltp}, Bid={$bid}, Ask={$ask}");
                    
                    $processedOptions[] = [
                        'symbol' => $symbol,
                        'expiry' => $expiry,
                        'strike_price' => $strikePrice,
                        'option_type' => $optionType === 'CE' ? 'CALL' : 'PUT',
                        'ltp' => round($ltp, 2),
                        'bid' => round($bid, 2),
                        'ask' => round($ask, 2),
                        'volume' => $volume,
                        'oi' => $oi,
                        'prev_close' => round($ltp * 0.98, 2),
                        'change' => round($ltp * 0.02, 2),
                        'change_percent' => round(2.0, 2),
                        'data_source' => 'TrueData Real Market'
                    ];
                } else {
                    Log::info("Skipping {$optionType} strike {$strikePrice} - no real price data available");
                }
            }
            
            // Sort by strike price
            usort($processedOptions, function($a, $b) {
                return $a['strike_price'] <=> $b['strike_price'];
            });
            
            // Count how many options use real vs calculated prices
            $realPriceCount = 0;
            $calculatedPriceCount = 0;
            foreach ($processedOptions as $option) {
                if (strpos($option['data_source'], 'Real Market') !== false) {
                    $realPriceCount++;
                } else {
                    $calculatedPriceCount++;
                }
            }
            
            // Determine data source status
            $dataSourceStatus = 'TrueData API (Calculated Prices)';
            $message = "Option chain data with calculated prices";
            
            if ($realPriceCount > 0) {
                $dataSourceStatus = 'TrueData API (Real Market + Calculated)';
                $message = "Live option chain data: {$realPriceCount} real prices, {$calculatedPriceCount} calculated prices";
            } else {
                $message = "Option chain data with calculated prices (Real prices not available - may be due to market hours or trial account limitations)";
            }
            
            return response()->json([
                'success' => true,
                'data' => $processedOptions,
                'symbol' => $symbol,
                'expiry' => $expiry,
                'underlying_price' => $underlyingPrice,
                'data_source' => $dataSourceStatus,
                'message' => $message,
                'total_options' => count($processedOptions),
                'real_prices_count' => $realPriceCount,
                'calculated_prices_count' => $calculatedPriceCount,
                'trial_limitation_note' => $realPriceCount === 0 ? 'Trial account may have limited access to real-time prices. Consider upgrading for live market data.' : null
            ]);
        } catch (\Exception $e) {
            Log::error('Options Chain Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Failed to fetch option chain'
            ], 500);
        }
    }

    /**
     * Get Options Dashboard Data
     */
    public function getOptionsDashboard(): JsonResponse
    {
        try {
            $result = $this->optionsService->getOptionsDashboard();
            
            return response()->json($result);
        } catch (\Exception $e) {
            Log::error('Options Dashboard Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Failed to fetch options dashboard'
            ], 500);
        }
    }

    /**
     * Get current option price for a specific strike and option type
     */
    public function getCurrentOptionPrice(): JsonResponse
    {
        try {
            $symbol = request()->query('symbol');
            $strikePrice = request()->query('strike_price');
            $optionType = request()->query('option_type');
            
            if (!$symbol || !$strikePrice || !$optionType) {
                return response()->json([
                    'success' => false,
                    'message' => 'Missing required parameters: symbol, strike_price, option_type'
                ], 400);
            }
            
            // Use live underlying price from frontend, fallback to defaults
            $underlyingPrice = request()->query('underlying_price');
            if (!$underlyingPrice || $underlyingPrice <= 0) {
                if ($symbol === 'NIFTY') {
                    $underlyingPrice = 25000;
                } elseif ($symbol === 'BANKNIFTY') {
                    $underlyingPrice = 52000;
                } else {
                    $underlyingPrice = 25000;
                }
            }
            
            $targetStrike = (float) $strikePrice;
            $targetType = strtoupper($optionType) === 'CALL' ? 'CALL' : 'PUT';
            
            // Try free option chain service first for real option prices
            $freeService = app(\App\Services\FreeOptionChainService::class);
            $freeResp = $freeService->getOptionChain($symbol);
            if ($freeResp['success'] && !empty($freeResp['data'])) {
                foreach ($freeResp['data'] as $option) {
                    if ((float)$option['strike_price'] == $targetStrike && 
                        strtoupper($option['option_type']) === $targetType) {
                        $price = (float)($option['ltp'] ?? 0);
                        if ($price > 0) {
                            return response()->json([
                                'success' => true,
                                'data' => [
                                    'symbol' => $symbol,
                                    'strike_price' => $targetStrike,
                                    'option_type' => strtoupper($optionType),
                                    'current_price' => $price,
                                    'underlying_price' => $underlyingPrice,
                                    'timestamp' => now()->toISOString(),
                                    'data_source' => $freeResp['source'] ?? 'Free API (1-2 min delayed)'
                                ]
                            ]);
                        }
                    }
                }
            }
            
            // Fallback to TrueData API
            $expiry = request()->query('expiry', '20250916');
            $trueDataUrl = "https://api.truedata.in/getOptionChain?user=tdwsp759&password=mosh@759&symbol={$symbol}&expiry={$expiry}";
            $rawResponse = file_get_contents($trueDataUrl);
            $trueDataResponse = json_decode($rawResponse, true);
            
            if ($trueDataResponse && $trueDataResponse['status'] === 'Success') {
                $records = $trueDataResponse['Records'] ?? [];
                $targetTypeCode = $targetType === 'CALL' ? 'CE' : 'PE';
                
                foreach ($records as $option) {
                    if (!is_array($option) || count($option) < 7) continue;
                    
                    $optionStrike = (float) ($option[6] ?? 0);
                    $optionTypeCode = $option[2] ?? 'CE';
                    
                    if ($optionStrike == $targetStrike && $optionTypeCode === $targetTypeCode) {
                        $realLtp = (float) ($option[3] ?? 0);
                        if ($realLtp > 0) {
                            return response()->json([
                                'success' => true,
                                'data' => [
                                    'symbol' => $symbol,
                                    'strike_price' => $targetStrike,
                                    'option_type' => strtoupper($optionType),
                                    'current_price' => $realLtp,
                                    'underlying_price' => $underlyingPrice,
                                    'timestamp' => now()->toISOString(),
                                    'data_source' => 'TrueData Real Market'
                                ]
                            ]);
                        }
                    }
                }
            }
            
            return response()->json([
                'success' => false,
                'message' => 'No real option price available for the specified strike and type'
            ], 404);
            
        } catch (\Exception $e) {
            Log::error('Get Current Option Price Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Failed to fetch current option price'
            ], 500);
        }
    }

    /**
     * Get Popular Options
     */
    public function getPopularOptions(): JsonResponse
    {
        try {
            $popularOptions = $this->optionsService->getPopularOptions();
            
            return response()->json([
                'success' => true,
                'data' => $popularOptions,
                'message' => 'Popular options retrieved successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Popular Options Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Failed to fetch popular options'
            ], 500);
        }
    }

    /**
     * Search for a specific stock and return its data
     */
    public function searchStock(Request $request): JsonResponse
    {
        try {
            $symbol = $request->input('symbol');
            
            if (!$symbol) {
                return response()->json([
                    'success' => false,
                    'message' => 'Stock symbol is required'
                ], 400);
            }

            // Try to get data from database first
            $marketData = MarketData::getMarketDataForSymbols([$symbol], true);
            
            // Check if symbol exists in database
            if (isset($marketData[$symbol])) {
                return response()->json([
                    'success' => true,
                    'data' => $marketData[$symbol],
                    'message' => 'Stock data retrieved from database'
                ]);
            }

            // If not in database, return error for now (can be enhanced later)
            return response()->json([
                'success' => false,
                'message' => 'Stock not found in current data. Please try again when market is open.'
            ], 404);

        } catch (\Exception $e) {
            Log::error('Error in searchStock: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to search for stock',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Test method for debugging
     */
    public function testOptionChain($symbol): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Test method working',
            'symbol' => $symbol
        ]);
    }
}