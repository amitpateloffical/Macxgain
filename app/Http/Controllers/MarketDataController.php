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
            
            // Try to get fresh data from free APIs first
            $freeDataResult = $this->freeMarketDataService->getLiveMarketData();
            
            if ($freeDataResult['success'] && !empty($freeDataResult['data'])) {
                // Use free API data
                $marketData = $freeDataResult['data'];
                $dataSourceMessage = $freeDataResult['source'] ?? 'Free API Market Data';
                $dataType = 'LIVE';
                $lastUpdate = now();
                
                Log::info("Using free API data for dashboard: {$freeDataResult['source']}");
            } else {
                // Fallback to database data
                $marketData = MarketData::getAllMarketData(true);
                $dataType = $isMarketLive ? 'LIVE' : 'HISTORICAL';
                $lastUpdate = MarketData::getLatestDataTimestamp() ?? now();
                
                // Determine data source message
                $dataSourceMessage = $this->marketStatusService->getDataSourceMessage();
                if ($dataType === 'HISTORICAL') {
                    $dataSourceMessage = 'Historical Data (Market Closed)';
                } elseif ($dataType === 'LIVE') {
                    $dataSourceMessage = 'Live Market Data';
                }
                
                Log::info("Using database data for dashboard");
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
            
            // Use fixed underlying price for now (will be made dynamic later)
            $underlyingPrice = 25000;

            // Try free market data service for option chain first
            $freeResp = $this->freeMarketDataService->getOptionChain($symbol);
            if ($freeResp['success'] && !empty($freeResp['data'])) {
                Log::info("Using free market data service for option chain");
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

            // No fallback needed - free APIs should handle all cases
            Log::info("Free APIs failed, returning empty data");
            return response()->json([
                'success' => false,
                'message' => 'No option chain data available from free APIs',
                'data' => []
            ], 404);
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