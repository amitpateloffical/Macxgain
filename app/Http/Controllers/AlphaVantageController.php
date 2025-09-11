<?php

namespace App\Http\Controllers;

use App\Services\AlphaVantageService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AlphaVantageController extends Controller
{
    protected $alphaVantageService;

    public function __construct(AlphaVantageService $alphaVantageService)
    {
        $this->alphaVantageService = $alphaVantageService;
    }

    /**
     * Get popular stocks list
     */
    public function getPopularStocks(): JsonResponse
    {
        try {
            $stocks = $this->alphaVantageService->getPopularStocks();
            return response()->json([
                'success' => true,
                'data' => $stocks,
                'total_stocks' => count($stocks),
                'message' => 'Popular stocks retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get stocks by category
     */
    public function getStocksByCategory(Request $request): JsonResponse
    {
        try {
            $category = $request->input('category');
            $stocks = $this->alphaVantageService->getStocksByCategory($category);
            
            return response()->json([
                'success' => true,
                'data' => array_values($stocks),
                'category' => $category,
                'total_stocks' => count($stocks),
                'message' => $category ? "Stocks for category '{$category}' retrieved successfully" : 'All stocks retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get available stock categories
     */
    public function getStockCategories(): JsonResponse
    {
        try {
            $categories = $this->alphaVantageService->getStockCategories();
            return response()->json([
                'success' => true,
                'data' => $categories,
                'total_categories' => count($categories),
                'message' => 'Stock categories retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Search for stocks/symbols
     */
    public function searchSymbols(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'keywords' => 'required|string|min:1|max:50'
            ]);

            $keywords = $request->input('keywords');
            $data = $this->alphaVantageService->searchSymbols($keywords);

            return response()->json([
                'success' => true,
                'data' => $data,
                'message' => 'Symbol search completed successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get stock quote
     */
    public function getQuote($symbol): JsonResponse
    {
        try {
            $data = $this->alphaVantageService->getQuote($symbol);

            if (isset($data['error'])) {
                return response()->json([
                    'success' => false,
                    'error' => $data['error']
                ], 400);
            }

            return response()->json([
                'success' => true,
                'data' => $data,
                'message' => 'Quote data retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get intraday stock data
     */
    public function getIntradayData(Request $request, $symbol): JsonResponse
    {
        try {
            $request->validate([
                'interval' => 'sometimes|in:1min,5min,15min,30min,60min',
                'outputsize' => 'sometimes|in:compact,full'
            ]);

            $interval = $request->input('interval', '5min');
            $outputsize = $request->input('outputsize', 'compact');

            $data = $this->alphaVantageService->getIntradayData($symbol, $interval, $outputsize);

            if (isset($data['error'])) {
                return response()->json([
                    'success' => false,
                    'error' => $data['error']
                ], 400);
            }

            return response()->json([
                'success' => true,
                'data' => $data,
                'message' => 'Intraday data retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get daily stock data
     */
    public function getDailyData(Request $request, $symbol): JsonResponse
    {
        try {
            $request->validate([
                'outputsize' => 'sometimes|in:compact,full'
            ]);

            $outputsize = $request->input('outputsize', 'compact');
            $data = $this->alphaVantageService->getDailyData($symbol, $outputsize);

            if (isset($data['error'])) {
                return response()->json([
                    'success' => false,
                    'error' => $data['error']
                ], 400);
            }

            return response()->json([
                'success' => true,
                'data' => $data,
                'message' => 'Daily data retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get options data
     */
    public function getOptionsData(Request $request, $symbol): JsonResponse
    {
        try {
            $request->validate([
                'contract' => 'sometimes|string'
            ]);

            $contract = $request->input('contract');
            $data = $this->alphaVantageService->getOptionsData($symbol, $contract);

            if (isset($data['error'])) {
                return response()->json([
                    'success' => false,
                    'error' => $data['error']
                ], 400);
            }

            return response()->json([
                'success' => true,
                'data' => $data,
                'message' => 'Options data retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get company overview
     */
    public function getCompanyOverview($symbol): JsonResponse
    {
        try {
            $data = $this->alphaVantageService->getCompanyOverview($symbol);

            if (isset($data['error'])) {
                return response()->json([
                    'success' => false,
                    'error' => $data['error']
                ], 400);
            }

            return response()->json([
                'success' => true,
                'data' => $data,
                'message' => 'Company overview retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get top gainers and losers
     */
    public function getTopGainersLosers(): JsonResponse
    {
        try {
            $data = $this->alphaVantageService->getTopGainersLosers();

            if (isset($data['error'])) {
                return response()->json([
                    'success' => false,
                    'error' => $data['error']
                ], 400);
            }

            return response()->json([
                'success' => true,
                'data' => $data,
                'message' => 'Top gainers and losers retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get technical indicators - SMA
     */
    public function getSMA(Request $request, $symbol): JsonResponse
    {
        try {
            $request->validate([
                'interval' => 'sometimes|in:1min,5min,15min,30min,60min,daily,weekly,monthly',
                'time_period' => 'sometimes|integer|min:1|max:200',
                'series_type' => 'sometimes|in:close,open,high,low'
            ]);

            $interval = $request->input('interval', 'daily');
            $timePeriod = $request->input('time_period', 20);
            $seriesType = $request->input('series_type', 'close');

            $data = $this->alphaVantageService->getSMA($symbol, $interval, $timePeriod, $seriesType);

            if (isset($data['error'])) {
                return response()->json([
                    'success' => false,
                    'error' => $data['error']
                ], 400);
            }

            return response()->json([
                'success' => true,
                'data' => $data,
                'message' => 'SMA data retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get technical indicators - RSI
     */
    public function getRSI(Request $request, $symbol): JsonResponse
    {
        try {
            $request->validate([
                'interval' => 'sometimes|in:1min,5min,15min,30min,60min,daily,weekly,monthly',
                'time_period' => 'sometimes|integer|min:1|max:200',
                'series_type' => 'sometimes|in:close,open,high,low'
            ]);

            $interval = $request->input('interval', 'daily');
            $timePeriod = $request->input('time_period', 14);
            $seriesType = $request->input('series_type', 'close');

            $data = $this->alphaVantageService->getRSI($symbol, $interval, $timePeriod, $seriesType);

            if (isset($data['error'])) {
                return response()->json([
                    'success' => false,
                    'error' => $data['error']
                ], 400);
            }

            return response()->json([
                'success' => true,
                'data' => $data,
                'message' => 'RSI data retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get market status
     */
    public function getMarketStatus(): JsonResponse
    {
        try {
            $data = $this->alphaVantageService->getMarketStatus();

            if (isset($data['error'])) {
                return response()->json([
                    'success' => false,
                    'error' => $data['error']
                ], 400);
            }

            return response()->json([
                'success' => true,
                'data' => $data,
                'message' => 'Market status retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get stock data with quote and basic info
     */
    public function getStockData($symbol): JsonResponse
    {
        try {
            // Get both quote and company overview in parallel
            $quote = $this->alphaVantageService->getQuote($symbol);
            $overview = $this->alphaVantageService->getCompanyOverview($symbol);

            $result = [
                'symbol' => $symbol,
                'quote' => $quote,
                'overview' => $overview
            ];

            return response()->json([
                'success' => true,
                'data' => $result,
                'message' => 'Stock data retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get comprehensive stock dashboard data
     */
    public function getStockDashboardData($symbol): JsonResponse
    {
        try {
            // Get comprehensive data for dashboard
            $quote = $this->alphaVantageService->getQuote($symbol);
            $dailyData = $this->alphaVantageService->getDailyData($symbol, 'compact');
            $overview = $this->alphaVantageService->getCompanyOverview($symbol);

            $result = [
                'symbol' => $symbol,
                'quote' => $quote,
                'daily_data' => $dailyData,
                'overview' => $overview,
                'timestamp' => now()->toISOString()
            ];

            return response()->json([
                'success' => true,
                'data' => $result,
                'message' => 'Stock dashboard data retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
