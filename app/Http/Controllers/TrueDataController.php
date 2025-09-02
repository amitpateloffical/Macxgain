<?php

namespace App\Http\Controllers;

use App\Services\TrueDataService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class TrueDataController extends Controller
{
    private $trueDataService;

    public function __construct(TrueDataService $trueDataService)
    {
        $this->trueDataService = $trueDataService;
    }

    /**
     * Get market dashboard data
     */
    public function getDashboardData(): JsonResponse
    {
        try {
            // Get market status
            $marketStatus = $this->trueDataService->getMarketStatus();
            
            // Get cached data
            $cachedData = $this->trueDataService->getCachedMarketData();
            
            // Prepare response data
            $responseData = [
                'market_status' => $marketStatus['success'] ? $marketStatus['data'] : null,
                'quotes' => $cachedData,
                'indices' => array_slice($cachedData, 0, 5, true), // First 5 as indices
                'top_gainers' => array_slice($cachedData, 0, 10, true), // First 10 as gainers
                'top_losers' => array_slice($cachedData, 5, 10, true), // Next 10 as losers
                'timestamp' => now()->toISOString()
            ];

            return response()->json([
                'success' => true,
                'data' => $responseData,
                'message' => 'Market data loaded successfully'
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
     * Get market status
     */
    public function getMarketStatus(): JsonResponse
    {
        try {
            $result = $this->trueDataService->getMarketStatus();

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'data' => $result['data'],
                    'message' => 'Market status fetched successfully'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'error' => $result['error'],
                    'message' => 'Failed to fetch market status'
                ], 503);
            }

        } catch (\Exception $e) {
            Log::error('TrueData Market Status Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Failed to fetch market status'
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
            $result = $this->trueDataService->getTopGainers();

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'data' => $result['data'],
                    'message' => 'Top gainers fetched successfully'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'error' => $result['error'],
                    'message' => 'Failed to fetch top gainers'
                ], 503);
            }

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
            $result = $this->trueDataService->getTopLosers();

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'data' => $result['data'],
                    'message' => 'Top losers fetched successfully'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'error' => $result['error'],
                    'message' => 'Failed to fetch top losers'
                ], 503);
            }

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
}