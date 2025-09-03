<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class OptionsService
{
    private $username;
    private $password;
    private $baseUrl;

    public function __construct()
    {
        $this->username = 'Trial189';
        $this->password = 'patel189';
        $this->baseUrl = 'https://api.truedata.in';
    }

    /**
     * Get Option Chain for a symbol
     */
    public function getOptionChain($symbol, $expiry = null)
    {
        try {
            // If no expiry provided, use current month's last Thursday
            if (!$expiry) {
                $expiry = $this->getCurrentMonthExpiry();
            }

            // Check cache first
            $cacheKey = "options_chain_{$symbol}_{$expiry}";
            $cachedData = Cache::get($cacheKey);
            if ($cachedData) {
                Log::info("Returning cached options data for {$symbol}");
                return [
                    'success' => true,
                    'data' => $cachedData,
                    'symbol' => $symbol,
                    'expiry' => $expiry,
                    'cached' => true
                ];
            }

            $url = "{$this->baseUrl}/getOptionChain";
            $params = [
                'user' => $this->username,
                'password' => $this->password,
                'symbol' => $symbol,
                'expiry' => $expiry,
                'response' => 'json'
            ];

            Log::info("Fetching options chain for {$symbol} with expiry {$expiry}");
            $response = Http::timeout(30)->get($url, $params);

            if ($response->successful()) {
                $data = $response->json();
                
                // Process the data according to TrueData format
                $processedData = $this->processOptionsData($data, $symbol);
                
                // Cache for 5 minutes
                Cache::put($cacheKey, $processedData, 300);
                
                Log::info("Successfully fetched and processed options data for {$symbol}");
                
                return [
                    'success' => true,
                    'data' => $processedData,
                    'symbol' => $symbol,
                    'expiry' => $expiry,
                    'cached' => false
                ];
            } else {
                Log::error("Options API failed for {$symbol}: " . $response->body());
                return [
                    'success' => false,
                    'error' => 'Failed to fetch options data',
                    'status_code' => $response->status()
                ];
            }
        } catch (\Exception $e) {
            Log::error("Options Service Error for {$symbol}: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Process options data according to TrueData format
     */
    private function processOptionsData($data, $symbol)
    {
        try {
            // TrueData API returns data in different formats
            $optionsData = [];
            
            // Check for different possible data structures
            if (isset($data['Records']) && is_array($data['Records'])) {
                $optionsData = $data['Records'];
            } elseif (is_array($data)) {
                $optionsData = $data;
            } elseif (isset($data['data']) && is_array($data['data'])) {
                $optionsData = $data['data'];
            }
            
            if (empty($optionsData)) {
                Log::warning("No options data found for {$symbol}");
                return $this->generateMockOptionsData($symbol);
            }
            
            // Process each option according to TrueData format
            $processedOptions = [];
            
            foreach ($optionsData as $option) {
                if (is_array($option) && count($option) >= 18) {
                    // TrueData format: [symbol, symbolid, timestamp, ltp, volume, atp, totalvolume, open, high, low, prevclose, oi, prevoi, turnover, bid, bidqty, ask, askqty]
                    $processedOptions[] = [
                        'symbol' => $option[0] ?? '',
                        'symbol_id' => $option[1] ?? '',
                        'timestamp' => $option[2] ?? '',
                        'ltp' => floatval($option[3] ?? 0),
                        'volume' => intval($option[4] ?? 0),
                        'atp' => floatval($option[5] ?? 0),
                        'total_volume' => intval($option[6] ?? 0),
                        'open' => floatval($option[7] ?? 0),
                        'high' => floatval($option[8] ?? 0),
                        'low' => floatval($option[9] ?? 0),
                        'prev_close' => floatval($option[10] ?? 0),
                        'oi' => intval($option[11] ?? 0),
                        'prev_oi' => intval($option[12] ?? 0),
                        'turnover' => floatval($option[13] ?? 0),
                        'bid' => floatval($option[14] ?? 0),
                        'bid_qty' => intval($option[15] ?? 0),
                        'ask' => floatval($option[16] ?? 0),
                        'ask_qty' => intval($option[17] ?? 0),
                        'strike_price' => $this->extractStrikePrice($option[0] ?? ''),
                        'option_type' => $this->getOptionType($option[0] ?? ''),
                        'implied_volatility' => 0.0 // Not provided in basic format
                    ];
                } elseif (is_array($option)) {
                    // If it's already an object/associative array
                    $processedOptions[] = [
                        'symbol' => $option['symbol'] ?? $option[0] ?? '',
                        'symbol_id' => $option['symbol_id'] ?? $option[1] ?? '',
                        'timestamp' => $option['timestamp'] ?? $option[2] ?? '',
                        'ltp' => floatval($option['ltp'] ?? $option[3] ?? 0),
                        'volume' => intval($option['volume'] ?? $option[4] ?? 0),
                        'atp' => floatval($option['atp'] ?? $option[5] ?? 0),
                        'total_volume' => intval($option['total_volume'] ?? $option[6] ?? 0),
                        'open' => floatval($option['open'] ?? $option[7] ?? 0),
                        'high' => floatval($option['high'] ?? $option[8] ?? 0),
                        'low' => floatval($option['low'] ?? $option[9] ?? 0),
                        'prev_close' => floatval($option['prev_close'] ?? $option[10] ?? 0),
                        'oi' => intval($option['oi'] ?? $option[11] ?? 0),
                        'prev_oi' => intval($option['prev_oi'] ?? $option[12] ?? 0),
                        'turnover' => floatval($option['turnover'] ?? $option[13] ?? 0),
                        'bid' => floatval($option['bid'] ?? $option[14] ?? 0),
                        'bid_qty' => intval($option['bid_qty'] ?? $option[15] ?? 0),
                        'ask' => floatval($option['ask'] ?? $option[16] ?? 0),
                        'ask_qty' => intval($option['ask_qty'] ?? $option[17] ?? 0),
                        'strike_price' => $this->extractStrikePrice($option['symbol'] ?? $option[0] ?? ''),
                        'option_type' => $this->getOptionType($option['symbol'] ?? $option[0] ?? ''),
                        'implied_volatility' => floatval($option['implied_volatility'] ?? 0)
                    ];
                }
            }
            
            Log::info("Processed " . count($processedOptions) . " options for {$symbol}");
            return $processedOptions;
            
        } catch (\Exception $e) {
            Log::error("Error processing options data for {$symbol}: " . $e->getMessage());
            return $this->generateMockOptionsData($symbol);
        }
    }
    
    /**
     * Extract strike price from option symbol
     */
    private function extractStrikePrice($optionSymbol)
    {
        if (empty($optionSymbol)) return 0;
        
        // Try to extract numeric part at the end (e.g., NIFTY25JAN2024CE18000)
        if (preg_match('/(\d+)$/', $optionSymbol, $matches)) {
            return intval($matches[1]);
        }
        
        // Try to extract from middle (e.g., NIFTY18000CE)
        if (preg_match('/(\d+)(?:CE|PE|CALL|PUT)/i', $optionSymbol, $matches)) {
            return intval($matches[1]);
        }
        
        return 0;
    }
    
    /**
     * Get option type from symbol
     */
    private function getOptionType($optionSymbol)
    {
        if (empty($optionSymbol)) return 'UNKNOWN';
        
        if (stripos($optionSymbol, 'CE') !== false || stripos($optionSymbol, 'CALL') !== false) {
            return 'CALL';
        } elseif (stripos($optionSymbol, 'PE') !== false || stripos($optionSymbol, 'PUT') !== false) {
            return 'PUT';
        }
        
        return 'UNKNOWN';
    }
    
    /**
     * Generate mock options data as fallback
     */
    private function generateMockOptionsData($symbol)
    {
        $currentPrice = 1000; // Default price
        $strikes = [];
        
        // Generate strike prices around current price
        for ($i = -5; $i <= 5; $i++) {
            $strikes[] = round($currentPrice + ($i * $currentPrice * 0.02));
        }
        
        $mockOptions = [];
        
        // Generate CALL options
        foreach ($strikes as $strike) {
            $mockOptions[] = [
                'symbol' => "{$symbol}{$strike}CE",
                'symbol_id' => rand(100000000, 999999999),
                'timestamp' => now()->toISOString(),
                'ltp' => round($strike * 0.01, 2),
                'volume' => rand(100, 1000),
                'atp' => round($strike * 0.012, 2),
                'total_volume' => rand(1000, 10000),
                'open' => round($strike * 0.009, 2),
                'high' => round($strike * 0.015, 2),
                'low' => round($strike * 0.008, 2),
                'prev_close' => round($strike * 0.01, 2),
                'oi' => rand(1000, 5000),
                'prev_oi' => rand(1000, 5000),
                'turnover' => round($strike * rand(100, 1000), 2),
                'bid' => round($strike * 0.009, 2),
                'bid_qty' => rand(50, 500),
                'ask' => round($strike * 0.011, 2),
                'ask_qty' => rand(50, 500),
                'strike_price' => $strike,
                'option_type' => 'CALL',
                'implied_volatility' => round(rand(20, 50) / 100, 3)
            ];
        }
        
        // Generate PUT options
        foreach ($strikes as $strike) {
            $mockOptions[] = [
                'symbol' => "{$symbol}{$strike}PE",
                'symbol_id' => rand(100000000, 999999999),
                'timestamp' => now()->toISOString(),
                'ltp' => round($strike * 0.01, 2),
                'volume' => rand(100, 1000),
                'atp' => round($strike * 0.012, 2),
                'total_volume' => rand(1000, 10000),
                'open' => round($strike * 0.009, 2),
                'high' => round($strike * 0.015, 2),
                'low' => round($strike * 0.008, 2),
                'prev_close' => round($strike * 0.01, 2),
                'oi' => rand(1000, 5000),
                'prev_oi' => rand(1000, 5000),
                'turnover' => round($strike * rand(100, 1000), 2),
                'bid' => round($strike * 0.009, 2),
                'bid_qty' => rand(50, 500),
                'ask' => round($strike * 0.011, 2),
                'ask_qty' => rand(50, 500),
                'strike_price' => $strike,
                'option_type' => 'PUT',
                'implied_volatility' => round(rand(20, 50) / 100, 3)
            ];
        }
        
        return $mockOptions;
    }

    /**
     * Get popular options symbols
     */
    public function getPopularOptions()
    {
        return [
            'NIFTY' => [
                'name' => 'NIFTY 50',
                'type' => 'Index',
                'strikes' => range(24000, 25000, 100) // Popular strikes
            ],
            'BANKNIFTY' => [
                'name' => 'Bank NIFTY',
                'type' => 'Index', 
                'strikes' => range(45000, 55000, 500)
            ],
            'RELIANCE' => [
                'name' => 'Reliance Industries',
                'type' => 'Stock',
                'strikes' => range(2500, 2700, 50)
            ],
            'TCS' => [
                'name' => 'Tata Consultancy Services',
                'type' => 'Stock',
                'strikes' => range(3800, 4000, 50)
            ]
        ];
    }

    /**
     * Get current month's expiry date (last Thursday)
     */
    private function getCurrentMonthExpiry()
    {
        $year = date('Y');
        $month = date('m');
        
        // Get last day of current month
        $lastDay = date('t', mktime(0, 0, 0, $month, 1, $year));
        
        // Find last Thursday of the month
        for ($day = $lastDay; $day >= 1; $day--) {
            $timestamp = mktime(0, 0, 0, $month, $day, $year);
            if (date('w', $timestamp) == 4) { // Thursday
                return date('Ymd', $timestamp);
            }
        }
        
        // Fallback to current date
        return date('Ymd');
    }

    /**
     * Get options data for dashboard
     */
    public function getOptionsDashboard()
    {
        $popularOptions = $this->getPopularOptions();
        $optionsData = [];

        foreach ($popularOptions as $symbol => $info) {
            $chainData = $this->getOptionChain($symbol);
            
            if ($chainData['success']) {
                $optionsData[$symbol] = [
                    'name' => $info['name'],
                    'type' => $info['type'],
                    'data' => $chainData['data'],
                    'expiry' => $chainData['expiry']
                ];
            }
        }

        return [
            'success' => true,
            'data' => $optionsData,
            'timestamp' => now()->toISOString()
        ];
    }
}
