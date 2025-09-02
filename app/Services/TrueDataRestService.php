<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Exception;

class TrueDataRestService
{
    private $authService;
    private $baseUrls;

    public function __construct(TrueDataAuthService $authService)
    {
        $this->authService = $authService;
        $this->baseUrls = [
            'history' => 'https://history.truedata.in',
            'analytics' => 'https://analytics2.truedata.in',
            'greeks' => 'https://greeks.truedata.in'
        ];
    }

    /**
     * Get market status
     */
    public function getMarketStatus()
    {
        try {
            $currentTime = now();
            $currentHour = $currentTime->hour;
            $currentMinute = $currentTime->minute;
            $currentDay = $currentTime->dayOfWeek;

            // Market hours: 9:00 AM - 3:30 PM IST, Monday to Friday
            $isWeekday = $currentDay >= 1 && $currentDay <= 5;
            $isTradingHours = $isWeekday && 
                (($currentHour >= 9 && $currentHour < 15) || 
                 ($currentHour == 15 && $currentMinute <= 30));

            return [
                'success' => true,
                'data' => [
                    'market_status' => $isTradingHours ? 'OPEN' : 'CLOSED',
                    'current_time' => $currentTime->format('H:i:s'),
                    'current_day' => $currentTime->format('l'),
                    'trading_hours' => '9:00 AM - 3:30 PM IST',
                    'trading_days' => 'Monday to Friday',
                    'is_weekday' => $isWeekday,
                    'is_trading_hours' => $isTradingHours
                ]
            ];
        } catch (Exception $e) {
            Log::error('Error getting market status: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Get LTP (Last Traded Price) for symbols
     */
    public function getLTP($symbols = [])
    {
        try {
            if (empty($symbols)) {
                $symbols = ['RELIANCE', 'TCS', 'HDFCBANK', 'ICICIBANK', 'SBIN', 'INFY', 'WIPRO', 'BHARTIARTL', 'NIFTY 50', 'NIFTY BANK'];
            }

            $quotes = [];
            $authHeaders = $this->authService->getAuthHeader();

            foreach ($symbols as $symbol) {
                try {
                    $response = Http::withHeaders($authHeaders)
                        ->get($this->baseUrls['history'] . '/getlastnticks', [
                            'symbol' => $symbol,
                            'bidask' => 1,
                            'response' => 'csv',
                            'nticks' => 1,
                            'interval' => 'tick'
                        ]);

                    if ($response->successful()) {
                        $data = $this->parseCSVResponse($response->body());
                        if (!empty($data)) {
                            $quotes[$symbol] = $this->formatQuoteData($data[0], $symbol);
                        }
                    }
                } catch (Exception $e) {
                    Log::warning("Failed to get LTP for {$symbol}: " . $e->getMessage());
                }
            }

            return [
                'success' => true,
                'data' => [
                    'quotes' => $quotes,
                    'count' => count($quotes),
                    'timestamp' => now()->toISOString()
                ]
            ];

        } catch (Exception $e) {
            Log::error('Error getting LTP: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Get top gainers
     */
    public function getTopGainers($limit = 20)
    {
        try {
            $authHeaders = $this->authService->getAuthHeader();

            $response = Http::withHeaders($authHeaders)
                ->get($this->baseUrls['history'] . '/gettopngainers', [
                    'segment' => 'CASH',
                    'response' => 'csv',
                    'topn' => $limit
                ]);

            if (!$response->successful()) {
                throw new Exception('Failed to get top gainers: ' . $response->body());
            }

            $data = $this->parseCSVResponse($response->body());
            $gainers = [];

            foreach ($data as $row) {
                if (count($row) >= 8) {
                    $gainers[] = [
                        'symbol' => $row[0] ?? '',
                        'ltp' => floatval($row[1] ?? 0),
                        'change' => floatval($row[2] ?? 0),
                        'change_percent' => floatval($row[3] ?? 0),
                        'volume' => intval($row[4] ?? 0),
                        'turnover' => floatval($row[5] ?? 0),
                        'high' => floatval($row[6] ?? 0),
                        'low' => floatval($row[7] ?? 0)
                    ];
                }
            }

            return [
                'success' => true,
                'data' => [
                    'gainers' => $gainers,
                    'count' => count($gainers),
                    'timestamp' => now()->toISOString()
                ]
            ];

        } catch (Exception $e) {
            Log::error('Error getting top gainers: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Get top losers
     */
    public function getTopLosers($limit = 20)
    {
        try {
            $authHeaders = $this->authService->getAuthHeader();

            $response = Http::withHeaders($authHeaders)
                ->get($this->baseUrls['history'] . '/gettopnlosers', [
                    'segment' => 'CASH',
                    'response' => 'csv',
                    'topn' => $limit
                ]);

            if (!$response->successful()) {
                throw new Exception('Failed to get top losers: ' . $response->body());
            }

            $data = $this->parseCSVResponse($response->body());
            $losers = [];

            foreach ($data as $row) {
                if (count($row) >= 8) {
                    $losers[] = [
                        'symbol' => $row[0] ?? '',
                        'ltp' => floatval($row[1] ?? 0),
                        'change' => floatval($row[2] ?? 0),
                        'change_percent' => floatval($row[3] ?? 0),
                        'volume' => intval($row[4] ?? 0),
                        'turnover' => floatval($row[5] ?? 0),
                        'high' => floatval($row[6] ?? 0),
                        'low' => floatval($row[7] ?? 0)
                    ];
                }
            }

            return [
                'success' => true,
                'data' => [
                    'losers' => $losers,
                    'count' => count($losers),
                    'timestamp' => now()->toISOString()
                ]
            ];

        } catch (Exception $e) {
            Log::error('Error getting top losers: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Get market indices
     */
    public function getMarketIndices()
    {
        try {
            $indices = ['NIFTY 50', 'NIFTY BANK', 'NIFTY IT', 'NIFTY FMCG', 'NIFTY PHARMA'];
            $authHeaders = $this->authService->getAuthHeader();
            $indexData = [];

            foreach ($indices as $index) {
                try {
                    $response = Http::withHeaders($authHeaders)
                        ->get($this->baseUrls['history'] . '/getlastnticks', [
                            'symbol' => $index,
                            'bidask' => 1,
                            'response' => 'csv',
                            'nticks' => 1,
                            'interval' => 'tick'
                        ]);

                    if ($response->successful()) {
                        $data = $this->parseCSVResponse($response->body());
                        if (!empty($data)) {
                            $indexData[] = $this->formatQuoteData($data[0], $index);
                        }
                    }
                } catch (Exception $e) {
                    Log::warning("Failed to get index data for {$index}: " . $e->getMessage());
                }
            }

            return [
                'success' => true,
                'data' => [
                    'indices' => $indexData,
                    'count' => count($indexData),
                    'timestamp' => now()->toISOString()
                ]
            ];

        } catch (Exception $e) {
            Log::error('Error getting market indices: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Get comprehensive market data
     */
    public function getMarketData()
    {
        try {
            $marketStatus = $this->getMarketStatus();
            $ltpData = $this->getLTP();
            $topGainers = $this->getTopGainers(10);
            $topLosers = $this->getTopLosers(10);
            $indices = $this->getMarketIndices();

            return [
                'success' => true,
                'data' => [
                    'market_status' => $marketStatus['data'] ?? null,
                    'quotes' => $ltpData['data']['quotes'] ?? [],
                    'top_gainers' => $topGainers['data']['gainers'] ?? [],
                    'top_losers' => $topLosers['data']['losers'] ?? [],
                    'indices' => $indices['data']['indices'] ?? [],
                    'timestamp' => now()->toISOString()
                ]
            ];

        } catch (Exception $e) {
            Log::error('Error getting market data: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Parse CSV response
     */
    private function parseCSVResponse($csvData)
    {
        $lines = explode("\n", trim($csvData));
        $data = [];

        foreach ($lines as $line) {
            if (!empty(trim($line))) {
                $data[] = str_getcsv($line);
            }
        }

        return $data;
    }

    /**
     * Format quote data
     */
    private function formatQuoteData($row, $symbol)
    {
        if (count($row) < 8) {
            return null;
        }

        $ltp = floatval($row[3] ?? 0);
        $prevClose = floatval($row[10] ?? 0);
        $change = $ltp - $prevClose;
        $changePercent = $prevClose > 0 ? ($change / $prevClose) * 100 : 0;

        return [
            'symbol' => $symbol,
            'ltp' => $ltp,
            'change' => $change,
            'change_percent' => $changePercent,
            'volume' => intval($row[6] ?? 0),
            'turnover' => floatval($row[13] ?? 0),
            'high' => floatval($row[8] ?? 0),
            'low' => floatval($row[9] ?? 0),
            'open' => floatval($row[7] ?? 0),
            'prev_close' => $prevClose,
            'bid' => floatval($row[14] ?? 0),
            'ask' => floatval($row[16] ?? 0),
            'timestamp' => now()->toISOString()
        ];
    }

    /**
     * Test connection
     */
    public function testConnection()
    {
        try {
            $authTest = $this->authService->testAuth();
            if (!$authTest['success']) {
                return $authTest;
            }

            $marketStatus = $this->getMarketStatus();
            $ltpTest = $this->getLTP(['RELIANCE']);

            return [
                'success' => true,
                'message' => 'TrueData REST API connection successful',
                'auth_status' => $authTest,
                'market_status' => $marketStatus['data'] ?? null,
                'ltp_test' => $ltpTest['success'] ? 'SUCCESS' : 'FAILED',
                'timestamp' => now()->toISOString()
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Connection test failed: ' . $e->getMessage()
            ];
        }
    }
}
