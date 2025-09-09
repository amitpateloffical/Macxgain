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
     * Get market status (Open/Closed with trading hours)
     */
    public function getMarketStatus(): JsonResponse
    {
        try {
            $marketStatus = $this->marketStatusService->getMarketStatus();
            
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
     * Get Option Chain for a specific symbol
     */
    public function getOptionChain($symbol): JsonResponse
    {
        try {
            $expiry = request()->query('expiry');
            $forceSource = request()->query('source'); // e.g., 'nse' or 'truedata'

            // Forward selected headers (to help NSE fallback)
            $forwardHeaders = [
                'User-Agent' => request()->header('User-Agent'),
                'Cookie' => request()->header('Cookie'),
                'Accept-Language' => request()->header('Accept-Language'),
            ];

            $result = $this->optionsService->getOptionChain($symbol, $expiry, [
                'force_source' => $forceSource,
                'forward_headers' => $forwardHeaders
            ]);
            
            return response()->json($result);
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
}