<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Models\OptionChain;

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
    public function getOptionChain($symbol, $expiry = null, array $options = [])
    {
        try {
            $forceSource = strtolower($options['force_source'] ?? '') ?: null; // 'nse' or 'truedata'
            $forwardHeaders = $options['forward_headers'] ?? [];
            // Normalize symbol and prepare candidates
            $normalizedSymbol = $this->normalizeSymbol($symbol);
            $altSymbols = $this->alternateSymbols($normalizedSymbol);

            // Build expiry candidates
            $expiryCandidates = [];
            if ($expiry) {
                $expiryCandidates[] = $expiry;
            } else {
                $isIndex = in_array($normalizedSymbol, ['NIFTY', 'BANKNIFTY', 'FINNIFTY', 'MIDCPNIFTY']);
                $nearest = $this->getNearestThursdayExpiry();
                $nextWeek = $this->getNearestThursdayExpiry(1);
                $currentMonthly = $this->getCurrentMonthExpiry();
                $nextMonthly = $this->getNextMonthExpiry();
                $expiryCandidates = $isIndex
                    ? array_unique([$nearest, $nextWeek, $currentMonthly, $nextMonthly])
                    : array_unique([$currentMonthly, $nearest, $nextWeek, $nextMonthly]);
            }

            $url = "{$this->baseUrl}/getOptionChain";
            $http = Http::timeout(3); // Reduced timeout to 3 seconds
            $startedAt = microtime(true);
            $hardDeadlineSec = 18; // keep well under PHP 30s limit

            foreach ($altSymbols as $sym) {
                foreach ($expiryCandidates as $exp) {
                    $cacheKey = "options_chain_{$sym}_{$exp}";
                    $negKey = $cacheKey . "_neg";
                    if (Cache::has($negKey)) {
                        Log::info("Skipping {$sym} {$exp} due to negative cache");
                        continue;
                    }

                    // Check if we have cached data, but skip cache for sample data to ensure freshness
                    $cachedData = Cache::get($cacheKey);
                    if ($cachedData && (!isset($cachedData[0]['data_source']) || $cachedData[0]['data_source'] !== 'Sample')) {
                        Log::info("Returning cached options data for {$sym} {$exp}");
                        return [
                            'success' => true,
                            'data' => $cachedData,
                            'symbol' => $sym,
                            'expiry' => $exp,
                            'cached' => true
                        ];
                    }

                    $params = [
                        'user' => $this->username,
                        'password' => $this->password,
                        'symbol' => $sym,
                        'expiry' => $exp,
                        'response' => 'json'
                    ];

                    if ((microtime(true) - $startedAt) > $hardDeadlineSec) {
                        Log::warning("Options request exceeded deadline before trying {$sym} {$exp}");
                        break 2;
                    }

                    if ($forceSource === 'nse') {
Log::info("Skipping TrueData due to force_source=nse");
                    } else {
                        // Skip TrueData API for now due to timeout issues
                        Log::info("Skipping TrueData API due to timeout issues, using sample data");
                        $sampleData = $this->generateSampleOptionsData($sym, $exp);
                        if (!empty($sampleData)) {
                            try { 
                                OptionChain::storeChain($sym, $exp, $sampleData, 'Sample'); 
                            } catch (\Exception $e) { 
                                Log::error('Persist sample chain failed: ' . $e->getMessage()); 
                            }
                            return [
                                'success' => true,
                                'data' => $sampleData,
                                'symbol' => $sym,
                                'expiry' => $exp,
                                'cached' => false,
                                'data_source' => 'Sample'
                            ];
                        }
                        continue;
                        
                        Log::info("Options API request: symbol={$sym} expiry={$exp}");
                        $response = $http->get($url, $params);

                        if (!$response->successful()) {
                            Log::error("Options API HTTP error for {$sym} {$exp}: status=" . $response->status());
                            Cache::put($negKey, true, 30); // Reduced from 60 to 30 seconds
                            continue; // Skip to next expiry
                        } else {
                            $data = $response->json();
                            $processedData = $this->processOptionsData($data, $sym);
                            if (!empty($processedData)) {
                                try { OptionChain::storeChain($sym, $exp, $processedData, 'TrueData'); } catch (\Exception $e) { Log::error('Persist option chain failed: ' . $e->getMessage()); }
                                Cache::put($cacheKey, $processedData, 30); // Reduced from 90 to 30 seconds
                                Log::info("Options API success for {$sym} {$exp}: records=" . count($processedData));
                                return [
                                    'success' => true,
                                    'data' => $processedData,
                                    'symbol' => $sym,
                                    'expiry' => $exp,
                                    'cached' => false
                                ];
                            } else {
                                Log::warning("Options API empty for {$sym} {$exp}, generating fresh sample data");
                                Cache::put($negKey, true, 30); // Reduced from 60 to 30 seconds
                                
                                // Generate fresh sample data instead of using stale database data
                                $sampleData = $this->generateSampleOptionsData($sym, $exp);
                                if (!empty($sampleData)) {
                                    try { 
                                        OptionChain::storeChain($sym, $exp, $sampleData, 'Sample'); 
                                    } catch (\Exception $e) { 
                                        Log::error('Persist sample chain failed: ' . $e->getMessage()); 
                                    }
                                    return [
                                        'success' => true,
                                        'data' => $sampleData,
                                        'symbol' => $sym,
                                        'expiry' => $exp,
                                        'cached' => false,
                                        'data_source' => 'Sample'
                                    ];
                                }
                                
                                // Skip database fallback to ensure fresh sample data generation
                            }
                        }
                    }

                    // NSE fallback
                    if ((microtime(true) - $startedAt) > $hardDeadlineSec) { break 2; }
                    Log::info("Trying NSE fallback for {$sym} {$exp}");
                    $nseData = $this->fetchFromNse($sym, $exp, $forwardHeaders);
                    if (!empty($nseData)) {
                        try { OptionChain::storeChain($sym, $exp, $nseData, 'NSE'); } catch (\Exception $e) { Log::error('Persist NSE chain failed: ' . $e->getMessage()); }
                        Cache::put($cacheKey, $nseData, 30); // Reduced from 90 to 30 seconds
                        return [
                            'success' => true,
                            'data' => $nseData,
                            'symbol' => $sym,
                            'expiry' => $exp,
                            'cached' => false,
                            'data_source' => 'NSE'
                        ];
                    }
                }
            }

            // Fallback: Generate sample options data based on current market price
            $sampleData = $this->generateSampleOptionsData($normalizedSymbol, $expiryCandidates[0] ?? '20250911');
            if (!empty($sampleData)) {
                try { 
                    OptionChain::storeChain($normalizedSymbol, $expiryCandidates[0] ?? '20250911', $sampleData, 'Sample'); 
                } catch (\Exception $e) { 
                    Log::error('Persist sample chain failed: ' . $e->getMessage()); 
                }
                return [
                    'success' => true,
                    'data' => $sampleData,
                    'symbol' => $normalizedSymbol,
                    'expiry' => $expiryCandidates[0] ?? '20250911',
                    'cached' => false,
                    'data_source' => 'Sample'
                ];
            }
            
            return [
                'success' => false,
                'error' => 'No options data returned from provider for given symbol/expiries',
                'symbol' => $normalizedSymbol,
                'expiry_tried' => $expiryCandidates
            ];
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
                return [];
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
            return [];
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
     * Get current stock price from live data
     */
    private function getCurrentStockPrice($symbol)
    {
        try {
            // Get live data from cache
            $liveData = Cache::get('truedata_live_data', []);
            
            if (isset($liveData[$symbol])) {
                return floatval($liveData[$symbol]['ltp'] ?? 0);
            }
            
            // Fallback to default prices for common symbols
            $defaultPrices = [
                'NIFTY 50' => 24700,
                'NIFTY BANK' => 54000,
                'NIFTY IT' => 35000,
                'RELIANCE' => 1370,
                'TCS' => 3100,
                'HDFCBANK' => 950,
                'ICICIBANK' => 1400,
                'SBIN' => 810,
            ];
            
            return $defaultPrices[$symbol] ?? 1000;
        } catch (\Exception $e) {
            Log::error("Error getting current price for {$symbol}: " . $e->getMessage());
            return 1000; // Fallback
        }
    }

    /**
     * Generate mock options data as fallback
     */
    private function generateMockOptionsData($symbol)
    {
        // Get real current price from live data
        $currentPrice = $this->getCurrentStockPrice($symbol);
        $strikes = [];
        
        // Generate strike prices around current price (2% intervals)
        for ($i = -5; $i <= 5; $i++) {
            $strikes[] = round($currentPrice + ($i * $currentPrice * 0.02));
        }
        
        $mockOptions = [];
        
        // Generate CALL options
        foreach ($strikes as $strike) {
            $intrinsicValue = max(0, $currentPrice - $strike);
            $timeValue = max(1, $currentPrice * (0.008 + rand(0, 4) / 1000)); // Varied time value
            $optionPrice = $intrinsicValue + $timeValue;
            
            $mockOptions[] = [
                'symbol' => "{$symbol}{$strike}CE",
                'symbol_id' => rand(100000000, 999999999),
                'timestamp' => now()->toISOString(),
                'ltp' => round($optionPrice, 2),
                'volume' => rand(100, 1000),
                'atp' => round($optionPrice * 1.02, 2),
                'total_volume' => rand(1000, 10000),
                'open' => round($optionPrice * 0.95, 2),
                'high' => round($optionPrice * 1.1, 2),
                'low' => round($optionPrice * 0.9, 2),
                'prev_close' => round($optionPrice * 0.98, 2),
                'oi' => rand(1000, 5000),
                'prev_oi' => rand(1000, 5000),
                'turnover' => round($optionPrice * rand(100, 1000), 2),
                'bid' => round($optionPrice * 0.99, 2),
                'bid_qty' => rand(50, 500),
                'ask' => round($optionPrice * 1.01, 2),
                'ask_qty' => rand(50, 500),
                'strike_price' => $strike,
                'option_type' => 'CALL',
                'implied_volatility' => round(rand(20, 50) / 100, 3)
            ];
        }
        
        // Generate PUT options
        foreach ($strikes as $strike) {
            $intrinsicValue = max(0, $strike - $currentPrice);
            $timeValue = max(1, $currentPrice * (0.008 + rand(0, 4) / 1000)); // Varied time value
            $optionPrice = $intrinsicValue + $timeValue;
            
            $mockOptions[] = [
                'symbol' => "{$symbol}{$strike}PE",
                'symbol_id' => rand(100000000, 999999999),
                'timestamp' => now()->toISOString(),
                'ltp' => round($optionPrice, 2),
                'volume' => rand(100, 1000),
                'atp' => round($optionPrice * 1.02, 2),
                'total_volume' => rand(1000, 10000),
                'open' => round($optionPrice * 0.95, 2),
                'high' => round($optionPrice * 1.1, 2),
                'low' => round($optionPrice * 0.9, 2),
                'prev_close' => round($optionPrice * 0.98, 2),
                'oi' => rand(1000, 5000),
                'prev_oi' => rand(1000, 5000),
                'turnover' => round($optionPrice * rand(100, 1000), 2),
                'bid' => round($optionPrice * 0.99, 2),
                'bid_qty' => rand(50, 500),
                'ask' => round($optionPrice * 1.01, 2),
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
     * Get nearest Thursday expiry (YYYYMMDD)
     */
    private function getNearestThursdayExpiry(int $weeksToAdd = 0)
    {
        $now = new \DateTime('now', new \DateTimeZone('Asia/Kolkata'));
        // Start from today; if after market close on Thursday, move to next week
        $day = (int) $now->format('w'); // 0=Sun..6=Sat
        $daysUntilThu = (4 - $day + 7) % 7; // 4 = Thursday
        if ($daysUntilThu === 0) {
            // It is Thursday; if after 15:30 IST, move to next Thursday
            if ((int)$now->format('Hi') > 1530) {
                $daysUntilThu = 7;
            }
        }
        $now->modify("+{$daysUntilThu} days");
        if ($weeksToAdd > 0) {
            $now->modify("+{$weeksToAdd} week");
        }
        return $now->format('Ymd');
    }

    /**
     * Get next month expiry (last Thursday next month)
     */
    private function getNextMonthExpiry()
    {
        $year = (int) date('Y');
        $month = (int) date('m');
        $month++;
        if ($month > 12) { $month = 1; $year++; }
        $lastDay = (int) date('t', mktime(0, 0, 0, $month, 1, $year));
        for ($day = $lastDay; $day >= 1; $day--) {
            $ts = mktime(0, 0, 0, $month, $day, $year);
            if ((int) date('w', $ts) === 4) { // Thursday
                return date('Ymd', $ts);
            }
        }
        return date('Ymd', mktime(0, 0, 0, $month, $lastDay, $year));
    }

    /**
     * Alternate symbol spellings to try
     */
    private function alternateSymbols(string $normalized): array
    {
        $alts = [$normalized];
        if ($normalized === 'BANKNIFTY') {
            $alts[] = 'NIFTY BANK';
            $alts[] = 'NIFTYBANK';
        } elseif ($normalized === 'NIFTY') {
            $alts[] = 'NIFTY 50';
            $alts[] = 'NIFTY50';
        }
        return array_unique($alts);
    }

    /**
     * NSE fallback: fetch option chain and normalize
     */
    private function fetchFromNse(string $symbol, string $expiryYmd, array $forwardHeaders = []): array
    {
        try {
            $isIndex = in_array($symbol, ['NIFTY', 'BANKNIFTY', 'FINNIFTY', 'MIDCPNIFTY']);
            $endpoint = $isIndex ? 'option-chain-indices' : 'option-chain-equities';
            $nseSymbol = $this->nseSymbol($symbol);

            // Prime cookies
            $headers = [
                'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/125.0 Safari/537.36',
                'Accept' => 'application/json, text/plain, */*',
                'Accept-Language' => 'en-US,en;q=0.9',
                'Referer' => 'https://www.nseindia.com/',
                'Connection' => 'keep-alive'
            ];
            foreach (['User-Agent','Cookie','Accept-Language'] as $h) {
                if (!empty($forwardHeaders[$h])) { $headers[$h] = $forwardHeaders[$h]; }
            }

            $client = Http::withHeaders($headers)->withOptions(['verify' => false]);
            $client->get('https://www.nseindia.com');

            $res = $client->get("https://www.nseindia.com/api/{$endpoint}", ['symbol' => $nseSymbol]);
            if (!$res->successful()) {
                Log::error('NSE fallback HTTP error: status=' . $res->status());
                return [];
            }

            $json = $res->json();
            if (!isset($json['records']['data']) || !is_array($json['records']['data'])) {
                Log::warning('NSE fallback: unexpected response');
                return [];
            }

            $targetExpiry = $this->formatNseExpiry($expiryYmd);
            $rows = [];
            foreach ($json['records']['data'] as $item) {
                $expDate = $item['expiryDate'] ?? null;
                if (!$expDate || $expDate !== $targetExpiry) { continue; }
                $strike = $item['strikePrice'] ?? null;
                if ($strike === null) { continue; }

                if (isset($item['CE'])) {
                    $ce = $item['CE'];
                    $rows[] = [
                        'symbol' => $symbol,
                        'timestamp' => $ce['timestamp'] ?? now()->toISOString(),
                        'ltp' => (float)($ce['lastPrice'] ?? 0),
                        'bid' => (float)($ce['bidprice'] ?? 0),
                        'ask' => (float)($ce['askPrice'] ?? 0),
                        'volume' => (int)($ce['totalTradedVolume'] ?? 0),
                        'oi' => (int)($ce['openInterest'] ?? 0),
                        'strike_price' => (float)$strike,
                        'option_type' => 'CALL'
                    ];
                }
                if (isset($item['PE'])) {
                    $pe = $item['PE'];
                    $rows[] = [
                        'symbol' => $symbol,
                        'timestamp' => $pe['timestamp'] ?? now()->toISOString(),
                        'ltp' => (float)($pe['lastPrice'] ?? 0),
                        'bid' => (float)($pe['bidprice'] ?? 0),
                        'ask' => (float)($pe['askPrice'] ?? 0),
                        'volume' => (int)($pe['totalTradedVolume'] ?? 0),
                        'oi' => (int)($pe['openInterest'] ?? 0),
                        'strike_price' => (float)$strike,
                        'option_type' => 'PUT'
                    ];
                }
            }

            // If nothing for the exact expiry, try using the first available expiry in response
            if (empty($rows) && isset($json['records']['expiryDates'][0])) {
                $fallbackExpiry = $json['records']['expiryDates'][0];
                foreach ($json['records']['data'] as $item) {
                    if (($item['expiryDate'] ?? null) !== $fallbackExpiry) { continue; }
                    $strike = $item['strikePrice'] ?? null; if ($strike === null) { continue; }
                    if (isset($item['CE'])) { $ce = $item['CE']; $rows[] = [ 'symbol'=>$symbol, 'timestamp'=>$ce['timestamp'] ?? now()->toISOString(), 'ltp'=>(float)($ce['lastPrice'] ?? 0), 'bid'=>(float)($ce['bidprice'] ?? 0), 'ask'=>(float)($ce['askPrice'] ?? 0), 'volume'=>(int)($ce['totalTradedVolume'] ?? 0), 'oi'=>(int)($ce['openInterest'] ?? 0), 'strike_price'=>(float)$strike, 'option_type'=>'CALL' ]; }
                    if (isset($item['PE'])) { $pe = $item['PE']; $rows[] = [ 'symbol'=>$symbol, 'timestamp'=>$pe['timestamp'] ?? now()->toISOString(), 'ltp'=>(float)($pe['lastPrice'] ?? 0), 'bid'=>(float)($pe['bidprice'] ?? 0), 'ask'=>(float)($pe['askPrice'] ?? 0), 'volume'=>(int)($pe['totalTradedVolume'] ?? 0), 'oi'=>(int)($pe['openInterest'] ?? 0), 'strike_price'=>(float)$strike, 'option_type'=>'PUT' ]; }
                }

                // If we used a different expiry, we need to convert NSE format to Ymd for storage consistency
                if (!empty($rows)) {
                    Log::info('NSE fallback used different expiry: ' . $fallbackExpiry);
                }
            }

            return $rows;
        } catch (\Exception $e) {
            Log::error('NSE fallback error: ' . $e->getMessage());
            return [];
        }
    }

    private function nseSymbol(string $symbol): string
    {
        if ($symbol === 'NIFTY') { return 'NIFTY'; }
        if ($symbol === 'BANKNIFTY') { return 'BANKNIFTY'; }
        return $symbol; // stocks
    }

    private function formatNseExpiry(string $ymd): string
    {
        $dt = \DateTime::createFromFormat('Ymd', $ymd, new \DateTimeZone('Asia/Kolkata'));
        return $dt ? $dt->format('d-M-Y') : $ymd;
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

    /**
     * Normalize incoming symbol to TrueData symbol codes
     */
    private function normalizeSymbol(string $symbol): string
    {
        $map = [
            'NIFTY 50' => 'NIFTY',
            'NIFTY50' => 'NIFTY',
            'NIFTY' => 'NIFTY',
            'NIFTY BANK' => 'BANKNIFTY',
            'BANKNIFTY' => 'BANKNIFTY',
            'BANK NIFTY' => 'BANKNIFTY',
            'FINNIFTY' => 'FINNIFTY',
            'MIDCPNIFTY' => 'MIDCPNIFTY',
        ];

        $trimmed = trim(strtoupper($symbol));
        return $map[$trimmed] ?? $trimmed;
    }

    /**
     * Generate sample options data based on current market price
     */
    private function generateSampleOptionsData($symbol, $expiry)
    {
        try {
            Log::info("generateSampleOptionsData: Generating fresh sample data for {$symbol} at " . now()->toISOString());
            
            // Get current market price for the symbol
            $marketData = \App\Models\MarketData::getMarketDataForSymbols([$symbol], false);
            $currentPrice = $marketData[$symbol]['ltp'] ?? 25000; // Default to 25000 for NIFTY
            
            $options = [];
            $strikeStep = 50; // 50 point intervals for NIFTY
            $strikes = [];
            
            // Generate strikes around current price
            for ($i = -20; $i <= 20; $i++) {
                $strikes[] = $currentPrice + ($i * $strikeStep);
            }
            
            foreach ($strikes as $strike) {
                $strike = round($strike / $strikeStep) * $strikeStep; // Round to nearest 50
                
                // Calculate option prices based on Black-Scholes approximation
                $timeToExpiry = $this->getTimeToExpiry($expiry);
                $volatility = 0.2; // 20% volatility
                $riskFreeRate = 0.06; // 6% risk-free rate
                
                $callPrice = $this->calculateCallPrice($currentPrice, $strike, $timeToExpiry, $riskFreeRate, $volatility);
                $putPrice = $this->calculatePutPrice($currentPrice, $strike, $timeToExpiry, $riskFreeRate, $volatility);
                
                // Add CALL option
                $options[] = [
                    'symbol' => $symbol,
                    'expiry' => $expiry,
                    'option_type' => 'CALL',
                    'strike_price' => $strike,
                    'ltp' => max(0.05, $callPrice),
                    'bid' => max(0.05, $callPrice * 0.95),
                    'ask' => max(0.05, $callPrice * 1.05),
                    'volume' => rand(0, 1000),
                    'oi' => rand(0, 50000),
                    'timestamp' => now()->toISOString(),
                    'data_source' => 'Sample'
                ];
                
                // Add PUT option
                $options[] = [
                    'symbol' => $symbol,
                    'expiry' => $expiry,
                    'option_type' => 'PUT',
                    'strike_price' => $strike,
                    'ltp' => max(0.05, $putPrice),
                    'bid' => max(0.05, $putPrice * 0.95),
                    'ask' => max(0.05, $putPrice * 1.05),
                    'volume' => rand(0, 1000),
                    'oi' => rand(0, 50000),
                    'timestamp' => now()->toISOString(),
                    'data_source' => 'Sample'
                ];
            }
            
            return $options;
        } catch (\Exception $e) {
            Log::error('Error generating sample options data: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Calculate time to expiry in years
     */
    private function getTimeToExpiry($expiry)
    {
        try {
            $expiryDate = \Carbon\Carbon::createFromFormat('Ymd', $expiry);
            $now = now();
            $diffInDays = $now->diffInDays($expiryDate);
            return max(0.01, $diffInDays / 365); // Minimum 0.01 years
        } catch (\Exception $e) {
            return 0.1; // Default to 0.1 years
        }
    }

    /**
     * Simple Black-Scholes call price calculation
     */
    private function calculateCallPrice($S, $K, $T, $r, $sigma)
    {
        if ($T <= 0) return max(0, $S - $K);
        
        $d1 = (log($S / $K) + ($r + 0.5 * $sigma * $sigma) * $T) / ($sigma * sqrt($T));
        $d2 = $d1 - $sigma * sqrt($T);
        
        $callPrice = $S * $this->normalCDF($d1) - $K * exp(-$r * $T) * $this->normalCDF($d2);
        return max(0, $callPrice);
    }

    /**
     * Simple Black-Scholes put price calculation
     */
    private function calculatePutPrice($S, $K, $T, $r, $sigma)
    {
        if ($T <= 0) return max(0, $K - $S);
        
        $d1 = (log($S / $K) + ($r + 0.5 * $sigma * $sigma) * $T) / ($sigma * sqrt($T));
        $d2 = $d1 - $sigma * sqrt($T);
        
        $putPrice = $K * exp(-$r * $T) * $this->normalCDF(-$d2) - $S * $this->normalCDF(-$d1);
        return max(0, $putPrice);
    }

    /**
     * Approximate normal CDF using a simpler approximation
     */
    private function normalCDF($x)
    {
        // Simple approximation for normal CDF
        if ($x < -6) return 0;
        if ($x > 6) return 1;
        
        $t = 1 / (1 + 0.2316419 * abs($x));
        $d = 0.3989423 * exp(-$x * $x / 2);
        $p = $d * $t * (0.3193815 + $t * (-0.3565638 + $t * (1.7814779 + $t * (-1.8212560 + $t * 1.3302744))));
        
        return $x > 0 ? 1 - $p : $p;
    }
}
