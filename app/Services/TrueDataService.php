<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Exception;

class TrueDataService
{
    private $webSocketManager;

    public function __construct()
    {
        $this->webSocketManager = new TrueDataWebSocketManager();
    }

    /**
     * Initialize WebSocket connection
     */
    public function initializeConnection()
    {
        return $this->webSocketManager->initializeConnection();
    }

    /**
     * Subscribe to symbols
     */
    public function subscribeToSymbols($symbols = [])
    {
        return $this->webSocketManager->subscribeToSymbols($symbols);
    }

    /**
     * Get real-time market data
     */
    public function getRealTimeData()
    {
        return $this->webSocketManager->getRealTimeData();
    }

    /**
     * Get cached market data
     */
    public function getCachedMarketData()
    {
        return $this->webSocketManager->getCachedMarketData();
    }

    /**
     * Get market quotes (formatted for compatibility with existing frontend)
     */
    public function getMarketQuotes($symbols = [])
    {
        try {
            $cachedData = $this->getCachedMarketData();
            $quotes = [];

            if (empty($symbols)) {
                $symbols = array_keys($cachedData);
            }

            foreach ($symbols as $symbol) {
                if (isset($cachedData[$symbol])) {
                    $quotes[$symbol] = $cachedData[$symbol];
                } else {
                    // Return default structure if no data available
                    $quotes[$symbol] = [
                        'symbol' => $symbol,
                        'last' => 0,
                        'open' => 0,
                        'high' => 0,
                        'low' => 0,
                        'prev_close' => 0,
                        'change' => 0,
                        'change_percent' => 0,
                        'volume' => 0,
                        'last_time' => now()->toISOString()
                    ];
                }
            }

            return [
                'success' => true,
                'data' => [
                    'quotes' => $quotes,
                    'timestamp' => now()->toISOString()
                ]
            ];

        } catch (Exception $e) {
            Log::error('TrueData Market Quotes Error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get market indices
     */
    public function getMarketIndices()
    {
        try {
            $indices = [
                'NIFTY 50', 'NIFTY BANK', 'NIFTY IT', 'SENSEX',
                'NIFTY AUTO', 'NIFTY PHARMA', 'NIFTY FMCG', 'NIFTY METAL'
            ];

            $indexData = [];
            $cachedData = $this->getCachedMarketData();

            foreach ($indices as $index) {
                if (isset($cachedData[$index])) {
                    $indexData[$index] = $cachedData[$index];
                }
            }

            return [
                'success' => true,
                'data' => $indexData
            ];

        } catch (Exception $e) {
            Log::error('TrueData Market Indices Error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get top gainers
     */
    public function getTopGainers()
    {
        try {
            $cachedData = $this->getCachedMarketData();
            $gainers = [];

            foreach ($cachedData as $symbol => $data) {
                if (isset($data['change_percent']) && $data['change_percent'] > 0) {
                    $gainers[] = $data;
                }
            }

            // Sort by change percentage descending
            usort($gainers, function($a, $b) {
                return $b['change_percent'] <=> $a['change_percent'];
            });

            return [
                'success' => true,
                'data' => array_slice($gainers, 0, 10) // Top 10
            ];

        } catch (Exception $e) {
            Log::error('TrueData Top Gainers Error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get top losers
     */
    public function getTopLosers()
    {
        try {
            $cachedData = $this->getCachedMarketData();
            $losers = [];

            foreach ($cachedData as $symbol => $data) {
                if (isset($data['change_percent']) && $data['change_percent'] < 0) {
                    $losers[] = $data;
                }
            }

            // Sort by change percentage ascending (most negative first)
            usort($losers, function($a, $b) {
                return $a['change_percent'] <=> $b['change_percent'];
            });

            return [
                'success' => true,
                'data' => array_slice($losers, 0, 10) // Top 10
            ];

        } catch (Exception $e) {
            Log::error('TrueData Top Losers Error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
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
            $currentDay = $currentTime->dayOfWeek; // 1 = Monday, 7 = Sunday

            // Market hours: 9:00 AM to 3:30 PM IST, Monday to Friday
            $isWeekday = $currentDay >= 1 && $currentDay <= 5;
            $isWithinTradingHours = $isWeekday && 
                (($currentHour == 9 && $currentMinute >= 0) || 
                 ($currentHour > 9 && $currentHour < 15) || 
                 ($currentHour == 15 && $currentMinute <= 30));

            $marketStatus = $isWithinTradingHours ? 'OPEN' : 'CLOSED';

            return [
                'success' => true,
                'data' => [
                    'market_status' => $marketStatus,
                    'current_time' => $currentTime->format('H:i:s'),
                    'current_day' => $currentTime->format('l'),
                    'trading_hours' => '9:00 AM - 3:30 PM IST',
                    'trading_days' => 'Monday to Friday',
                    'is_weekday' => $isWeekday,
                    'is_trading_hours' => $isWithinTradingHours
                ]
            ];

        } catch (Exception $e) {
            Log::error('TrueData Market Status Error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Search instruments
     */
    public function searchInstruments($query)
    {
        try {
            // TrueData doesn't have a direct search API, so we'll return common symbols
            $commonSymbols = [
                'RELIANCE', 'TCS', 'HDFCBANK', 'ICICIBANK', 'SBIN', 'INFY',
                'WIPRO', 'BHARTIARTL', 'ITC', 'LT', 'ASIANPAINT', 'MARUTI',
                'NESTLEIND', 'TITAN', 'SUNPHARMA', 'BAJFINANCE', 'HINDUNILVR',
                'KOTAKBANK', 'AXISBANK', 'ULTRACEMCO', 'POWERGRID'
            ];

            $filteredSymbols = array_filter($commonSymbols, function($symbol) use ($query) {
                return stripos($symbol, strtoupper($query)) !== false;
            });

            return [
                'success' => true,
                'data' => array_values($filteredSymbols)
            ];

        } catch (Exception $e) {
            Log::error('TrueData Search Instruments Error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Close WebSocket connection
     */
    public function closeConnection()
    {
        $this->webSocketManager->closeConnection();
    }

    /**
     * Get connection status
     */
    public function getConnectionStatus()
    {
        return $this->webSocketManager->getConnectionStatus();
    }

    /**
     * Destructor to ensure connection is closed
     */
    public function __destruct()
    {
        $this->closeConnection();
    }
}
