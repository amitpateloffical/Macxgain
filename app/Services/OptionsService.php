<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Models\OptionChain;
use Carbon\Carbon;

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
     * Sort expiry dates by relevance - prioritize current week's expiry
     */
    private function sortExpiriesByRelevance($expiries)
    {
        if (empty($expiries)) {
            return $expiries;
        }
        
        $now = new \DateTime();
        $currentWeekEnd = clone $now;
        
        // Find the end of current week (Sunday)
        $daysUntilSunday = (7 - $now->format('w')) % 7;
        if ($daysUntilSunday === 0) $daysUntilSunday = 7; // If today is Sunday, go to next Sunday
        $currentWeekEnd->modify("+{$daysUntilSunday} days");
        $currentWeekEnd->setTime(23, 59, 59);
        
        // For Wednesday (today), also consider Thursday expiries as current week
        $dayOfWeek = $now->format('w'); // 0=Sunday, 3=Wednesday
        if ($dayOfWeek >= 3) { // Wednesday onwards
            $currentWeekEnd->modify('+1 day'); // Include Thursday expiries
        }
        
        Log::info("Smart Expiry Selection: Current week end considered as " . $currentWeekEnd->format('Y-m-d H:i:s'));
        
        // Separate expiries into current week and future
        $currentWeekExpiries = [];
        $futureExpiries = [];
        
        foreach ($expiries as $expiry) {
            $expiryDate = new \DateTime($expiry['date']);
            
            if ($expiryDate <= $currentWeekEnd && $expiryDate >= $now) {
                $currentWeekExpiries[] = $expiry;
                Log::info("Current week expiry found: " . $expiry['label']);
            } else {
                $futureExpiries[] = $expiry;
            }
        }
        
        // Sort current week expiries by date (ascending)
        usort($currentWeekExpiries, function($a, $b) {
            return $a['timestamp'] - $b['timestamp'];
        });
        
        // Sort future expiries by date (ascending)
        usort($futureExpiries, function($a, $b) {
            return $a['timestamp'] - $b['timestamp'];
        });
        
        // Prioritize current week expiries, then future ones
        $sortedExpiries = array_merge($currentWeekExpiries, $futureExpiries);
        
        if (!empty($currentWeekExpiries)) {
            Log::info("Smart Expiry Selection: Prioritizing current week expiry: " . $currentWeekExpiries[0]['label']);
        } else if (!empty($futureExpiries)) {
            Log::info("Smart Expiry Selection: No current week expiry found, using nearest future: " . $futureExpiries[0]['label']);
        }
        
        return $sortedExpiries;
    }

    /**
     * Get all symbols that have options trading available
     */
    public function getValidOptionSymbols()
    {
        try {
            Log::info("TrueData API: Fetching all valid option symbols");
            
            // Check cache first
            $cacheKey = "truedata_valid_option_symbols";
            if (Cache::has($cacheKey)) {
                Log::info("TrueData API: Returning cached valid option symbols");
                            return [
                                'success' => true,
                    'data' => Cache::get($cacheKey),
                    'message' => 'Valid option symbols from cache'
                ];
            }
            
            $response = Http::timeout(60)->get($this->baseUrl . '/getAllSymbols', [
                'segment' => 'fo',
                'user' => $this->username,
                'password' => $this->password
            ]);
            
            if (!$response->successful()) {
                Log::error("TrueData API: Failed to fetch symbols - HTTP {$response->status()}");
                return [
                    'success' => false,
                    'error' => "HTTP {$response->status()}",
                    'message' => 'Failed to fetch symbols'
                ];
            }
            
            $data = $response->json();
            
            if (!isset($data['status']) || $data['status'] !== 'Success' || !isset($data['Records'])) {
                Log::error("TrueData API: Invalid response for symbols");
                return [
                    'success' => false,
                    'error' => 'Invalid response',
                    'message' => 'No symbols data available'
                ];
            }
            
            $validSymbols = [];
            
            foreach ($data['Records'] as $record) {
                // Record format: [id, symbol, type, ?, exchange, ?, strike, expiry, ?, ?, ?, baseSymbol, ...]
                if (is_array($record) && count($record) >= 12) {
                    $optionType = $record[2];
                    $baseSymbol = $record[11];
                    
                    // Only collect symbols that have option contracts (CE/PE)
                    if (($optionType === 'CE' || $optionType === 'PE') && $baseSymbol) {
                        $validSymbols[] = $baseSymbol;
                    }
                }
            }
            
            // Remove duplicates and sort
            $validSymbols = array_unique($validSymbols);
            sort($validSymbols);
            
            // Cache for 1 hour
            Cache::put($cacheKey, $validSymbols, 3600);
            
            Log::info("TrueData API: Found " . count($validSymbols) . " symbols with options trading");
            
                            return [
                                'success' => true,
                'data' => $validSymbols,
                'message' => 'Valid option symbols fetched successfully'
            ];
            
                            } catch (\Exception $e) { 
            Log::error('TrueData API: Exception while fetching valid symbols - ' . $e->getMessage());
                            return [
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Exception occurred while fetching valid symbols'
            ];
        }
    }

    /**
     * Get available expiry dates for a symbol from TrueData API
     */
    public function getAvailableExpiries($symbol)
    {
        try {
            Log::info("TrueData API: Fetching available expiries for {$symbol}");
            
            $normalizedSymbol = $this->normalizeSymbol($symbol);
            
            $response = Http::timeout(30)->get($this->baseUrl . '/getAllSymbols', [
                'segment' => 'fo',
                        'user' => $this->username,
                        'password' => $this->password,
                'search' => $normalizedSymbol
            ]);

                    if (!$response->successful()) {
                Log::error("TrueData API: Failed to fetch symbols - HTTP {$response->status()}");
                            return [
                    'success' => false,
                    'error' => "HTTP {$response->status()}",
                    'message' => 'Failed to fetch available expiries'
                ];
            }
            
                        $data = $response->json();
            
            if (!isset($data['status']) || $data['status'] !== 'Success' || !isset($data['Records'])) {
                Log::error("TrueData API: Invalid response for expiries");
                                return [
                    'success' => false,
                    'error' => 'Invalid response',
                    'message' => 'No expiry data available'
                ];
            }
            
            $expiries = [];
            
            foreach ($data['Records'] as $record) {
                // Record format: [id, symbol, type, ?, exchange, ?, strike, expiry, ...]
                if (is_array($record) && count($record) >= 8) {
                    $symbolName = $record[1];
                    $optionType = $record[2];
                    $expiryDate = $record[7];
                    
                    // Only process option contracts (CE/PE)
                    if (($optionType === 'CE' || $optionType === 'PE') && $expiryDate) {
                        $expiries[] = $expiryDate;
                    }
                }
            }
            
            // Remove duplicates and sort
            $expiries = array_unique($expiries);
            sort($expiries);
            
            // Format expiries for better display and smart sorting
            $formattedExpiries = array_map(function($expiry) {
                $date = new \DateTime($expiry);
                return [
                    'value' => $date->format('Ymd'),
                    'label' => $date->format('d M Y'),
                    'date' => $expiry,
                    'timestamp' => $date->getTimestamp()
                ];
            }, $expiries);
            
            // Smart expiry selection: prefer current week's expiry
            $formattedExpiries = $this->sortExpiriesByRelevance($formattedExpiries);
            
            Log::info("TrueData API: Found " . count($formattedExpiries) . " expiry dates for {$symbol}");
            
            return [
                'success' => true,
                'data' => $formattedExpiries,
                'symbol' => $normalizedSymbol,
                'message' => 'Available expiry dates fetched successfully'
            ];
            
        } catch (\Exception $e) {
            Log::error('TrueData API: Exception while fetching expiries - ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Exception occurred while fetching expiry dates'
            ];
        }
    }

    /**
     * Get Option Chain for a symbol using only TrueData API
     */
    public function getOptionChain($symbol, $expiry = null, array $options = [])
    {
        try {
            Log::info("TrueData Options API: Fetching option chain for {$symbol}");
            
            // Normalize symbol
            $normalizedSymbol = $this->normalizeSymbol($symbol);
            
            // Get expiry date - if not provided, fetch nearest available expiry
            if (!$expiry) {
                $expiryResponse = $this->getAvailableExpiries($normalizedSymbol);
                if ($expiryResponse['success'] && !empty($expiryResponse['data'])) {
                    $formattedExpiry = $expiryResponse['data'][0]['value']; // Use first (nearest) expiry
                    Log::info("TrueData Options API: Auto-selected nearest expiry: {$formattedExpiry}");
                } else {
                    $expiry = $this->getDefaultExpiry($normalizedSymbol);
                    $formattedExpiry = $this->formatExpiry($expiry);
                    Log::warning("TrueData Options API: Could not fetch expiries, using default: {$formattedExpiry}");
                }
            } else {
                // Format provided expiry to YYYYMMDD format
                $formattedExpiry = $this->formatExpiry($expiry);
            }
            
            Log::info("TrueData Options API: Using symbol={$normalizedSymbol}, expiry={$formattedExpiry}");
            
            // Check cache first
            $cacheKey = "truedata_options_{$normalizedSymbol}_{$formattedExpiry}";
            $cachedData = Cache::get($cacheKey);
            
            if ($cachedData) {
                $age = isset($cachedData[0]['timestamp']) ? 
                    Carbon::parse($cachedData[0]['timestamp'])->diffInSeconds(now()) : 999;
                
                if ($age <= 30) { // Use cache for 30 seconds
                    Log::info("TrueData Options API: Returning cached data (age: {$age}s)");
                    return [
                        'success' => true,
                        'data' => $cachedData,
                        'symbol' => $normalizedSymbol,
                        'expiry' => $formattedExpiry,
                        'cached' => true,
                        'data_source' => 'TrueData'
                    ];
                }
            }
            
            // Call TrueData Option Chain API
            $url = "{$this->baseUrl}/getOptionChain";
            $params = [
                'user' => $this->username,
                'password' => $this->password,
                'symbol' => $normalizedSymbol,
                'expiry' => $formattedExpiry
            ];
            
            Log::info("TrueData Options API: Calling {$url} with params", $params);
            
            $response = Http::timeout(10)->get($url, $params);
            
            if (!$response->successful()) {
                Log::error("TrueData Options API: HTTP error - Status: {$response->status()}");
                return [
                    'success' => false,
                    'error' => "HTTP {$response->status()}",
                    'message' => 'Failed to fetch from TrueData API'
                ];
            }
            
            $rawData = $response->json();
            Log::info("TrueData Options API: Raw response received", ['status' => $rawData['status'] ?? 'unknown', 'records_count' => isset($rawData['Records']) && is_array($rawData['Records']) ? count($rawData['Records']) : 0]);
            
            if (empty($rawData) || !is_array($rawData) || !isset($rawData['status']) || $rawData['status'] !== 'Success') {
                Log::warning("TrueData Options API: Invalid response or failed status");
                return [
                    'success' => false,
                    'error' => 'Invalid response',
                    'message' => 'TrueData API returned invalid response or failed status'
                ];
            }
            
            if (!isset($rawData['Records']) || !is_array($rawData['Records']) || empty($rawData['Records'])) {
                Log::warning("TrueData Options API: No records in response");
                return [
                    'success' => false,
                    'error' => 'No records',
                    'message' => 'No option records available for this symbol/expiry'
                ];
            }
            
            // Process the TrueData response
            $processedData = $this->processTrueDataOptions($rawData['Records'], $normalizedSymbol, $formattedExpiry);
            
            if (empty($processedData)) {
                Log::warning("TrueData Options API: No valid options after processing");
                return [
                    'success' => false,
                    'error' => 'No valid options',
                    'message' => 'Unable to process option data'
                ];
            }
            
            // Store in database and cache
            try {
                OptionChain::storeChain($normalizedSymbol, $formattedExpiry, $processedData, 'TrueData');
                Cache::put($cacheKey, $processedData, 60); // Cache for 1 minute
            } catch (\Exception $e) {
                Log::error('TrueData Options API: Failed to store data - ' . $e->getMessage());
            }
            
            Log::info("TrueData Options API: Success - Processed " . count($processedData) . " options");
            
            return [
                'success' => true,
                'data' => $processedData,
                'symbol' => $normalizedSymbol,
                'expiry' => $formattedExpiry,
                'cached' => false,
                'data_source' => 'TrueData'
            ];
            
        } catch (\Exception $e) {
            Log::error('TrueData Options API: Exception - ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Exception occurred while fetching TrueData options'
            ];
        }
    }
    
    /**
     * Process TrueData option chain response
     */
    private function processTrueDataOptions($rawData, $symbol, $expiry)
    {
        $processedOptions = [];
        
        Log::info("TrueData Options API: Processing " . count($rawData) . " raw options");
        
        foreach ($rawData as $option) {
            try {
                // TrueData returns option data in a specific format
                // Based on documentation, each option should have symbol info
                
                // Extract option details from TrueData format
                $optionType = $this->extractOptionType($option);
                $strikePrice = $this->extractStrikePrice($option);
                
                if (!$optionType || !$strikePrice) {
                    Log::warning("TrueData Options API: Skipping invalid option", ['option' => $option]);
                    continue;
                }
                
                $processedOption = [
                    'symbol' => $symbol,
                    'expiry' => $expiry,
                    'option_type' => $optionType,
                    'strike_price' => $strikePrice,
                    'ltp' => $this->extractValue($option, 'ltp', 0),
                    'bid' => $this->extractValue($option, 'bid', 0),
                    'ask' => $this->extractValue($option, 'ask', 0),
                    'bid_qty' => $this->extractValue($option, 'bid_qty', 0),
                    'ask_qty' => $this->extractValue($option, 'ask_qty', 0),
                    'volume' => $this->extractValue($option, 'volume', 0),
                    'oi' => $this->extractValue($option, 'oi', 0),
                    'prev_close' => $this->extractValue($option, 'prev_close', 0),
                    'timestamp' => now()->toISOString(),
                    'data_source' => 'TrueData'
                ];
                
                $processedOptions[] = $processedOption;
                
            } catch (\Exception $e) {
                Log::error("TrueData Options API: Error processing option - " . $e->getMessage());
                continue;
            }
        }
        
        Log::info("TrueData Options API: Successfully processed " . count($processedOptions) . " options");
        return $processedOptions;
    }

    /**
     * Extract option type (CALL/PUT) from TrueData response
     */
    private function extractOptionType($option)
    {
        // TrueData format: option type is at index 2 (CE/PE)
        if (is_array($option) && isset($option[2])) {
            $type = $option[2];
            if ($type === 'CE') return 'CALL';
            if ($type === 'PE') return 'PUT';
        }
        
        // Fallback: extract from symbol name at index 1
        if (is_array($option) && isset($option[1])) {
            $symbolName = $option[1];
            if (strpos($symbolName, 'CE') !== false) {
            return 'CALL';
            } elseif (strpos($symbolName, 'PE') !== false) {
            return 'PUT';
            }
        }
        
        return null;
    }

    /**
     * Extract strike price from TrueData response
     */
    private function extractStrikePrice($option)
    {
        // TrueData format: strike price is at index 6
        if (is_array($option) && isset($option[6])) {
            return (float)$option[6];
        }
        
        // Fallback: extract from symbol name at index 1
        if (is_array($option) && isset($option[1])) {
            $symbolName = $option[1];
            // Extract numeric strike from symbol name (e.g., NIFTY25091619200CE -> 19200)
            if (preg_match('/(\d+)(CE|PE)$/', $symbolName, $matches)) {
                return (float)$matches[1];
            }
        }
        
        return null;
    }

    /**
     * Extract specific value from option data
     */
    private function extractValue($option, $key, $default = 0)
    {
        if (is_array($option)) {
            return $option[$key] ?? $default;
        }
        
        return $default;
    }

    /**
     * Get default expiry for a symbol
     */
    private function getDefaultExpiry($symbol)
    {
        $isIndex = in_array($symbol, ['NIFTY', 'BANKNIFTY', 'FINNIFTY', 'MIDCPNIFTY']);
        
        if ($isIndex) {
            // For indices, get nearest Thursday
            return $this->getNearestThursdayExpiry();
        } else {
            // For stocks, get current month expiry
            return $this->getCurrentMonthExpiry();
        }
    }

    /**
     * Format expiry to YYYYMMDD format required by TrueData
     */
    private function formatExpiry($expiry)
    {
        if (strlen($expiry) == 8 && is_numeric($expiry)) {
            return $expiry; // Already in YYYYMMDD format
        }
        
        try {
            $date = Carbon::parse($expiry);
            return $date->format('Ymd');
        } catch (\Exception $e) {
            Log::warning("TrueData Options API: Invalid expiry format {$expiry}, using default");
            return $this->getDefaultExpiry('NIFTY');
        }
    }

    /**
     * Get nearest Thursday expiry (for indices)
     */
    private function getNearestThursdayExpiry($weeksAhead = 0)
    {
        $now = Carbon::now();
        $currentDay = $now->dayOfWeek; // 0 = Sunday, 4 = Thursday
        
        if ($currentDay <= 4) {
            // If today is Sunday to Thursday, get this Thursday
            $daysToAdd = 4 - $currentDay;
        } else {
            // If today is Friday/Saturday, get next Thursday
            $daysToAdd = 11 - $currentDay;
        }
        
        $thursday = $now->addDays($daysToAdd)->addWeeks($weeksAhead);
        return $thursday->format('Ymd');
    }

    /**
     * Get current month expiry (last Thursday)
     */
    private function getCurrentMonthExpiry()
    {
        $now = Carbon::now();
        $lastDay = $now->copy()->endOfMonth();
        
        // Find last Thursday of current month
        while ($lastDay->dayOfWeek !== 4) {
            $lastDay->subDay();
        }
        
        return $lastDay->format('Ymd');
    }

    /**
     * Get next month expiry
     */
    private function getNextMonthExpiry()
    {
        $nextMonth = Carbon::now()->addMonth();
        $lastDay = $nextMonth->endOfMonth();
        
        // Find last Thursday of next month
        while ($lastDay->dayOfWeek !== 4) {
            $lastDay->subDay();
        }
        
        return $lastDay->format('Ymd');
    }

    /**
     * Normalize symbol for TrueData format
     */
    private function normalizeSymbol($symbol)
    {
        $symbolMap = [
            'NIFTY 50' => 'NIFTY',
            'NIFTY50' => 'NIFTY',
            'NIFTY BANK' => 'BANKNIFTY',
            'BANK NIFTY' => 'BANKNIFTY',
            'NIFTY FIN SERVICE' => 'FINNIFTY',
            'NIFTY MIDCAP SELECT' => 'MIDCPNIFTY'
        ];
        
        $normalizedSymbol = strtoupper(trim($symbol));
        return $symbolMap[$normalizedSymbol] ?? $normalizedSymbol;
    }

    /**
     * Get options dashboard data
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

    /**
     * Get popular options symbols
     */
    public function getPopularOptions()
    {
        return [
            'NIFTY' => [
                'name' => 'NIFTY 50',
                'type' => 'index'
            ],
            'BANKNIFTY' => [
                'name' => 'BANK NIFTY',
                'type' => 'index'
            ],
            'FINNIFTY' => [
                'name' => 'FIN NIFTY',
                'type' => 'index'
            ],
            'RELIANCE' => [
                'name' => 'Reliance Industries',
                'type' => 'stock'
            ],
            'TCS' => [
                'name' => 'Tata Consultancy Services',
                'type' => 'stock'
            ]
        ];
    }
}

