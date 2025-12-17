<?php

namespace App\Http\Controllers;

use App\Services\MarketStatusService;
use App\Services\OptionsService;
use App\Services\FreeMarketDataService;
use App\Models\MarketData;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class MarketDataController extends Controller
{
    private $marketStatusService;
    private $optionsService;
    private $freeMarketDataService;

    public function __construct(MarketStatusService $marketStatusService, OptionsService $optionsService, FreeMarketDataService $freeMarketDataService)
    {
        $this->marketStatusService = $marketStatusService;
        $this->optionsService = $optionsService;
        $this->freeMarketDataService = $freeMarketDataService;
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
            
            // First, try to get data from database (faster and more reliable)
            $allMarketData = MarketData::getAllMarketData(true);
            $lastUpdate = MarketData::getLatestDataTimestamp() ?? now();
            $dataType = $isMarketLive ? 'LIVE' : 'HISTORICAL';
            
            // Filter to show only major indices (NIFTY 50, NIFTY BANK, SENSEX)
            $marketData = $this->filterToMajorIndices($allMarketData);
            
            // Check if database data is fresh (within last 5 minutes)
            $isDataFresh = MarketData::isDataFresh();
            
            if (empty($marketData) || !$isDataFresh) {
                Log::info("Database data is stale or empty, fetching fresh data from APIs");
                
                // Try to get fresh data from free APIs
                $freeDataResult = $this->freeMarketDataService->getLiveMarketData();
                
                if ($freeDataResult['success'] && !empty($freeDataResult['data'])) {
                    // Use fresh API data (it's already filtered by the service)
                    $marketData = $freeDataResult['data'];
                    $dataSourceMessage = $freeDataResult['source'] ?? 'Free API Market Data';
                    $lastUpdate = now();
                    
                    Log::info("Using fresh API data for dashboard: {$freeDataResult['source']}");
                } else {
                    // Fallback to existing database data
                    $dataSourceMessage = 'Database Data (Stale)';
                    Log::info("Using existing database data for dashboard");
                }
            } else {
                // Use fresh database data
                $dataSourceMessage = 'Database Data (Fresh)';
                Log::info("Using fresh database data for dashboard");
            }
            
            // Convert data to array format for frontend
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
            Log::error('Market Data Dashboard Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Failed to fetch market data'
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
            $result = $this->freeMarketDataService->getLiveMarketData($symbols);

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
            Log::error('Market Data Quotes Error: ' . $e->getMessage());
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
            $result = $this->freeMarketDataService->getLiveMarketData();

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
            Log::error('Market Data Indices Error: ' . $e->getMessage());
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
            // Get market data and sort by change percentage to find top gainers
            $result = $this->freeMarketDataService->getLiveMarketData();
            
            if ($result['success'] && !empty($result['data'])) {
                $stocks = $result['data'];
                // Sort by change_percent descending to get top gainers
                uasort($stocks, function($a, $b) {
                    return $b['change_percent'] <=> $a['change_percent'];
                });
                
                $topGainers = array_slice($stocks, 0, 10, true); // Get top 10
                
                return response()->json([
                    'success' => true,
                    'data' => $topGainers,
                    'message' => 'Top gainers loaded successfully from Free API'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'No market data available'
                ], 404);
            }
        } catch (\Exception $e) {
            Log::error('Free API Top Gainers Error: ' . $e->getMessage());
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
            // Get market data and sort by change percentage to find top losers
            $result = $this->freeMarketDataService->getLiveMarketData();
            
            if ($result['success'] && !empty($result['data'])) {
                $stocks = $result['data'];
                // Sort by change_percent ascending to get top losers
                uasort($stocks, function($a, $b) {
                    return $a['change_percent'] <=> $b['change_percent'];
                });
                
                $topLosers = array_slice($stocks, 0, 10, true); // Get top 10
                
                return response()->json([
                    'success' => true,
                    'data' => $topLosers,
                    'message' => 'Top losers loaded successfully from Free API'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'No market data available'
                ], 404);
            }

        } catch (\Exception $e) {
            Log::error('Free API Top Losers Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Failed to fetch top losers'
            ], 500);
        }
    }

    /**
     * Search instruments (placeholder - would need search API)
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

            // Placeholder - search API would be implemented here
            return response()->json([
                'success' => false,
                'error' => 'Search API not implemented',
                'message' => 'Search functionality not available yet'
            ], 501);

        } catch (\Exception $e) {
            Log::error('Search Instruments Error: ' . $e->getMessage());
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
            
            // Get live market data from free APIs
            $result = $this->freeMarketDataService->getLiveMarketData($symbols);

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
            Log::error('Live Stock Data Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Failed to fetch live stock data'
            ], 500);
        }
    }

    /**
     * Get historical data (placeholder - would need historical API)
     */
    public function getHistoricalData(Request $request): JsonResponse
    {
        try {
            // Placeholder - historical API would be implemented here
            return response()->json([
                'success' => false,
                'error' => 'Historical data API not implemented',
                'message' => 'Historical data functionality not available yet'
            ], 501);

        } catch (\Exception $e) {
            Log::error('Historical Data Error: ' . $e->getMessage());
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
            
            // Try to get fresh data from free APIs first
            $freeDataResult = $this->freeMarketDataService->getLiveMarketData();
            
            if ($freeDataResult['success'] && !empty($freeDataResult['data'])) {
                // Use free API data
                $liveData = $freeDataResult['data'];
                $lastUpdate = now();
                $dataSourceMessage = $freeDataResult['source'] ?? 'Free API Market Data';
                
                Log::info("Using free API data for live data: {$freeDataResult['source']}");
            } else {
                // Fallback to database data
                $liveData = MarketData::getAllMarketData(true);
                $lastUpdate = MarketData::getLatestDataTimestamp() ?? now();
                $dataSourceMessage = 'Database Data';
                
                // If no data in database or data is stale, trigger fresh fetch
                if (empty($liveData) || !MarketData::isDataFresh()) {
                    Log::info('No fresh data in database, triggering fresh fetch...');
                    
                // Trigger job to fetch fresh data
                \App\Jobs\FetchMarketDataJob::dispatch();
                    
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
                
                Log::info("Using database data for live data");
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
            Log::error('Live Data Error: ' . $e->getMessage());
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
            $cachedData = Cache::get('free_market_data', []);
            $lastUpdate = Cache::get('free_market_last_update', null);
            
            // If data is very recent (less than 30 seconds old), use it
            if (!empty($cachedData) && $lastUpdate && $lastUpdate->diffInSeconds(now()) < 30) {
                Log::info('Using very recent cached data - ' . count($cachedData) . ' symbols');
                return $cachedData;
            }
            
            // Otherwise, trigger fresh data fetch
            \App\Jobs\FetchFreeMarketDataJob::dispatch();
            
            // Wait briefly for job to complete
            usleep(500000); // 0.5 seconds
            
            // Try to get fresh data
            $freshData = Cache::get('free_market_data', []);
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
            \App\Jobs\FetchFreeMarketDataJob::dispatch();
            
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

            // For free APIs, we don't need to subscribe - just return success
            $result = ['success' => true, 'message' => 'Free APIs automatically provide all available symbols'];

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
            Log::error('Subscribe Symbols Error: ' . $e->getMessage());
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
            Log::info("MarketDataController: Getting valid option symbols");
            
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
            Log::error('MarketDataController: Exception in getValidOptionSymbols - ' . $e->getMessage());
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
            Log::info("MarketDataController: Getting expiry dates for symbol: {$symbol}");
            
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
            Log::error('MarketDataController: Exception in getOptionExpiries - ' . $e->getMessage());
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
            
            // Get market status
            $marketStatus = $this->marketStatusService->getMarketStatus();
            $isMarketOpen = ($marketStatus['status'] ?? 'CLOSED') === 'OPEN';
            
            // Use fixed underlying price for now (will be made dynamic later)
            $underlyingPrice = 25000;

            // Try free market data service for option chain first
            $freeResp = $this->freeMarketDataService->getOptionChain($symbol);
            if ($freeResp['success'] && !empty($freeResp['data'])) {
                $isCached = $freeResp['is_cached'] ?? false;
                $marketStatusFromResponse = $freeResp['market_status'] ?? ($isMarketOpen ? 'OPEN' : 'CLOSED');
                
                Log::info("Using free market data service for option chain" . ($isCached ? " (cached)" : "") . " - Market: {$marketStatusFromResponse}");
                
                // Determine message based on market status
                $message = $freeResp['message'] ?? '';
                if (empty($message)) {
                    if ($marketStatusFromResponse === 'CLOSED') {
                        $message = 'Market is closed - showing last available prices';
                    } elseif ($isCached) {
                        $message = 'Showing last available market data';
                    } else {
                        $message = 'Real option prices from free API';
                    }
                }
                
                return response()->json([
                    'success' => true,
                    'data' => $freeResp['data'],
                    'symbol' => $symbol,
                    'expiry' => $expiry,
                    'underlying_price' => $underlyingPrice,
                    'data_source' => $freeResp['source'] ?? 'Free API (1-2 min delayed)',
                    'message' => $message,
                    'total_options' => count($freeResp['data']),
                    'is_cached' => $isCached,
                    'market_status' => $marketStatusFromResponse,
                    'is_market_open' => $isMarketOpen,
                    'timestamp' => $freeResp['timestamp'] ?? now()->toISOString()
                ]);
            }

            // Return error with helpful message
            $errorMessage = $freeResp['message'] ?? 'No option chain data available from free APIs. The market may be closed or NSE API is temporarily unavailable.';
            Log::warning("Free APIs failed for {$symbol}: " . $errorMessage);
            return response()->json([
                'success' => false,
                'message' => $errorMessage,
                'data' => [],
                'market_status' => $isMarketOpen ? 'OPEN' : 'CLOSED',
                'is_market_open' => $isMarketOpen
            ], 200); // Return 200 to avoid frontend errors
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
            
            // Try free market data service first for real option prices
            $freeResp = $this->freeMarketDataService->getOptionChain($symbol);
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
            
            return response()->json([
                'success' => false,
                'message' => 'No option price available from free APIs'
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
     * Update market data in database (manual trigger)
     */
    public function updateMarketData(): JsonResponse
    {
        try {
            Log::info("Manual market data update triggered");
            
            // Fetch fresh data from APIs
            $freeDataResult = $this->freeMarketDataService->getLiveMarketData();
            
            if ($freeDataResult['success'] && !empty($freeDataResult['data'])) {
                $count = count($freeDataResult['data']);
                
                return response()->json([
                    'success' => true,
                    'message' => "Market data updated successfully. {$count} symbols updated.",
                    'data_count' => $count,
                    'source' => $freeDataResult['source'] ?? 'Free API',
                    'timestamp' => now()->toISOString()
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to fetch fresh market data from APIs',
                    'error' => $freeDataResult['message'] ?? 'Unknown error'
                ], 503);
            }
            
        } catch (\Exception $e) {
            Log::error('Update Market Data Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Failed to update market data'
            ], 500);
        }
    }

    /**
     * Get market data statistics
     */
    public function getMarketDataStats(): JsonResponse
    {
        try {
            $stats = MarketData::getMarketDataStats();
            
            return response()->json([
                'success' => true,
                'data' => $stats,
                'message' => 'Market data statistics retrieved successfully'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Market Data Stats Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Failed to get market data statistics'
            ], 500);
        }
    }

    /**
     * Filter market data to show major indices first, then additional symbols
     * @param array $marketData
     * @return array
     */
    private function filterToMajorIndices(array $marketData): array
    {
        $prioritySymbols = [
            // Major indices first
            'NIFTY 50', 'NIFTY BANK', 'SENSEX',
            // Additional NIFTY indices
            'NIFTY IT', 'NIFTY FMCG', 'NIFTY AUTO', 'NIFTY PHARMA', 'NIFTY METAL', 'NIFTY ENERGY', 
            'NIFTY REALTY', 'NIFTY PSU BANK', 'NIFTY PVT BANK', 'NIFTY MEDIA', 'NIFTY INFRA', 
            'FINNIFTY', 'NIFTY MIDCAP', 'NIFTY COMMODITIES', 'NIFTY NEXT 50', 'NIFTY 100', 
            'NIFTY 200', 'NIFTY 500', 'NIFTY MIDCAP 100', 'NIFTY SMALLCAP 100', 'NIFTY MIDCAP 50',
            'NIFTY SMALLCAP 50', 'NIFTY 1000', 'NIFTY ALPHA 50', 'NIFTY QUALITY 50',
            // Popular NSE stocks (50+ symbols)
            'RELIANCE', 'TCS', 'HDFCBANK', 'ICICIBANK', 'HINDUNILVR', 'ITC', 'KOTAKBANK', 'SBIN', 'BHARTIARTL', 'LT',
            'AXISBANK', 'ASIANPAINT', 'MARUTI', 'NESTLEIND', 'ULTRACEMCO', 'SUNPHARMA', 'TITAN', 'POWERGRID', 'NTPC', 'TECHM',
            'WIPRO', 'ONGC', 'TATAMOTORS', 'BAJFINANCE', 'BAJAJFINSV', 'BAJAJ-AUTO', 'DRREDDY', 'CIPLA', 'COALINDIA', 'BPCL',
            'HCLTECH', 'INFY', 'INDUSINDBK', 'GRASIM', 'JSWSTEEL', 'TATASTEEL', 'ADANIENT', 'ADANIPORTS', 'ADANIGREEN', 'ADANIENSOL',
            'BRITANNIA', 'COLPAL', 'DMART', 'EICHERMOT', 'HDFC', 'HDFCLIFE', 'ICICIGI', 'ICICIPRULI', 'LICHSGFIN', 'M&M',
            'TATACONSUM', 'TATAPOWER', 'UPL', 'VEDL', 'ZEEL', 'APOLLOHOSP', 'DIVISLAB', 'HEROMOTOCO', 'SHREECEM', 'TATACHEM',
            // Additional popular symbols
            'MCXCOMPDEX', 'AARTIIND', 'GILLETTE', 'JKTYRE', 'KAJARIACER', 'MINDTREE', 'OFSS', 'PNB', 'QUICKHEAL', 'UJJIVAN',
            'YESBANK', 'NIFTY-I', 'BANKNIFTY-I', 'UPL-I', 'VEDL-I', 'VOLTAS-I', 'ZEEL-I', 'CRUDEOIL-I', 'GOLDM-I', 'SILVERM-I',
            'COPPER-I', 'SILVER-I'
        ];
        
        $filteredData = [];
        
        // Add symbols in priority order
        foreach ($prioritySymbols as $symbol) {
            if (isset($marketData[$symbol])) {
                $filteredData[$symbol] = $marketData[$symbol];
            }
        }
        
        // If any major index is missing, add fallback data
        if (!isset($filteredData['NIFTY 50'])) {
            $filteredData['NIFTY 50'] = $this->getNifty50FallbackData();
        }
        
        if (!isset($filteredData['NIFTY BANK'])) {
            $filteredData['NIFTY BANK'] = $this->getNiftyBankFallbackData();
        }
        
        if (!isset($filteredData['SENSEX'])) {
            $filteredData['SENSEX'] = $this->getSensexFallbackData();
        }
        
        // Add any additional indices that are available in market data but not in priority list
        $additionalIndices = [
            'NIFTY IT', 'NIFTY FMCG', 'NIFTY AUTO', 'NIFTY PHARMA', 'NIFTY METAL', 'NIFTY ENERGY',
            'NIFTY REALTY', 'NIFTY PSU BANK', 'NIFTY PVT BANK', 'NIFTY MEDIA', 'NIFTY INFRA',
            'FINNIFTY', 'NIFTY MIDCAP', 'NIFTY COMMODITIES', 'NIFTY NEXT 50', 'NIFTY 100',
            'NIFTY 200', 'NIFTY 500', 'NIFTY MIDCAP 100', 'NIFTY SMALLCAP 100', 'NIFTY MIDCAP 50',
            'NIFTY SMALLCAP 50', 'NIFTY 1000', 'NIFTY ALPHA 50', 'NIFTY QUALITY 50'
        ];
        
        // Add any additional indices that exist in market data
        foreach ($additionalIndices as $index) {
            if (!isset($filteredData[$index]) && isset($marketData[$index])) {
                $filteredData[$index] = $marketData[$index];
            }
        }
        
        Log::info("MarketDataController: Filtered to " . count($filteredData) . " symbols (major indices + additional)");
        
        return $filteredData;
    }

    /**
     * Get NIFTY 50 fallback data when not available from APIs
     * @return array
     */
    private function getNifty50FallbackData(): array
    {
        $basePrice = 25000;
        $variation = rand(-200, 200); // ±200 points variation
        $ltp = $basePrice + $variation;
        $change = $variation;
        $changePercent = ($change / $basePrice) * 100;
        
        return [
            'symbol' => 'NIFTY 50',
            'ltp' => $ltp,
            'change' => $change,
            'change_percent' => round($changePercent, 2),
            'high' => $ltp + rand(50, 150),
            'low' => $ltp - rand(50, 150),
            'open' => $basePrice,
            'prev_close' => $basePrice,
            'volume' => rand(500000, 2000000),
            'timestamp' => now()->toISOString(),
            'data_source' => 'Fallback Calculation (Estimated)',
            'is_live' => false
        ];
    }

    /**
     * Get NIFTY BANK fallback data when not available from APIs
     * @return array
     */
    private function getNiftyBankFallbackData(): array
    {
        $basePrice = 50000;
        $variation = rand(-300, 300); // ±300 points variation
        $ltp = $basePrice + $variation;
        $change = $variation;
        $changePercent = ($change / $basePrice) * 100;
        
        return [
            'symbol' => 'NIFTY BANK',
            'ltp' => $ltp,
            'change' => $change,
            'change_percent' => round($changePercent, 2),
            'high' => $ltp + rand(100, 250),
            'low' => $ltp - rand(100, 250),
            'open' => $basePrice,
            'prev_close' => $basePrice,
            'volume' => rand(300000, 1500000),
            'timestamp' => now()->toISOString(),
            'data_source' => 'Fallback Calculation (Estimated)',
            'is_live' => false
        ];
    }

    /**
     * Get SENSEX fallback data when not available from APIs
     * @return array
     */
    private function getSensexFallbackData(): array
    {
        $basePrice = 65000;
        $variation = rand(-500, 500); // ±500 points variation
        $ltp = $basePrice + $variation;
        $change = $variation;
        $changePercent = ($change / $basePrice) * 100;
        
        return [
            'symbol' => 'SENSEX',
            'ltp' => $ltp,
            'change' => $change,
            'change_percent' => round($changePercent, 2),
            'high' => $ltp + rand(100, 300),
            'low' => $ltp - rand(100, 300),
            'open' => $basePrice,
            'prev_close' => $basePrice,
            'volume' => rand(1000000, 5000000),
            'timestamp' => now()->toISOString(),
            'data_source' => 'Fallback Calculation (Estimated)',
            'is_live' => false
        ];
    }

    /**
     * Test method for debugging option chain data
     */
    public function testOptionChain($symbol): JsonResponse
    {
        try {
            Log::info("Testing option chain for symbol: {$symbol}");
            
            // Get option chain data
            $result = $this->freeMarketDataService->getOptionChain($symbol);
            
            // Log detailed information
            if ($result['success'] && !empty($result['data'])) {
                $sampleOption = $result['data'][0] ?? null;
                Log::info("Sample option data: " . json_encode($sampleOption));
                
                return response()->json([
                    'success' => true,
                    'message' => 'Option chain data retrieved',
                    'symbol' => $symbol,
                    'total_options' => count($result['data']),
                    'data_source' => $result['source'] ?? 'Unknown',
                    'sample_option' => $sampleOption,
                    'first_5_options' => array_slice($result['data'], 0, 5)
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'] ?? 'No data available',
                    'symbol' => $symbol,
                    'error' => $result['message'] ?? 'Unknown error'
                ]);
            }
        } catch (\Exception $e) {
            Log::error("Test option chain error: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
                'symbol' => $symbol
            ]);
        }
    }
}