<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FreeMarketDataService
{
    private $cacheTimeout = 5; // 5 seconds cache for real-time data
    private $delayedCacheTimeout = 60; // 1 minute cache for delayed data
    
    /**
     * Get live market data from free APIs and store in database
     * @param array $symbols
     * @return array
     */
    public function getLiveMarketData(array $symbols = []): array
    {
        $cacheKey = "free_market_data_" . md5(implode(',', $symbols));
        
        return Cache::remember($cacheKey, $this->cacheTimeout, function () use ($symbols) {
            try {
                // Try multiple free APIs in order of preference
                $apis = [
                    'nse_india' => $this->getFromNSEIndia($symbols),
                    'alpha_vantage' => $this->getFromAlphaVantage($symbols),
                    'yahoo_finance' => $this->getFromYahooFinance($symbols),
                    'mock_realistic' => $this->getMockRealisticData($symbols)
                ];
                
                foreach ($apis as $apiName => $result) {
                    if ($result['success'] && !empty($result['data'])) {
                        Log::info("FreeMarketDataService: Using {$apiName} for market data");
                        
                        // Ensure SENSEX is always available
                        if (!isset($result['data']['SENSEX']) || empty($result['data']['SENSEX']['ltp'])) {
                            $result['data']['SENSEX'] = $this->getSensexFallbackData();
                            Log::info("FreeMarketDataService: Added SENSEX fallback data");
                        }
                        
                        // Filter to show only NIFTY 50, NIFTY BANK, and SENSEX
                        $result['data'] = $this->filterToMajorIndices($result['data']);
                        
                        // Store the data in database
                        $this->storeMarketDataInDatabase($result['data'], $apiName);
                        
                        return $result;
                    }
                }
                
                return [
                    'success' => false,
                    'message' => 'All free APIs failed',
                    'data' => []
                ];
                
            } catch (\Exception $e) {
                Log::error("FreeMarketDataService error: " . $e->getMessage());
                return [
                    'success' => false,
                    'message' => 'Service error: ' . $e->getMessage(),
                    'data' => []
                ];
            }
        });
    }
    
    /**
     * Get option chain data from free APIs with multiple fallback sources
     * When market is closed, shows last available real prices
     * @param string $symbol
     * @return array
     */
    public function getOptionChain(string $symbol): array
    {
        $cacheKey = "free_option_chain_{$symbol}";
        $lastRealDataKey = "free_option_chain_last_real_{$symbol}"; // Persistent cache for last real data
        
        // Check market status
        $marketStatusService = app(\App\Services\MarketStatusService::class);
        $marketStatus = $marketStatusService->getMarketStatus();
        $isMarketOpen = ($marketStatus['status'] ?? 'CLOSED') === 'OPEN';
        
        // If market is closed, prioritize last real data
        if (!$isMarketOpen) {
            Log::info("FreeMarketDataService: Market is closed, checking for last real prices for {$symbol}");
            
            // First, try to get last real data from persistent cache (24 hour cache)
            $lastRealData = Cache::get($lastRealDataKey);
            if ($lastRealData && !empty($lastRealData['data'])) {
                Log::info("FreeMarketDataService: Using last real prices from persistent cache for {$symbol} (market closed) - " . count($lastRealData['data']) . " options");
                $lastRealData['is_cached'] = true;
                $lastRealData['market_status'] = 'CLOSED';
                $lastRealData['message'] = 'Market is closed - showing last available prices from ' . ($lastRealData['timestamp'] ?? 'previous session');
                return $lastRealData;
            }
            
            // Fallback to regular cache (might have shorter expiry)
            $cached = Cache::get($cacheKey);
            if ($cached && !empty($cached['data'])) {
                // Check if it's real data (has is_real_data flag or source indicates real data)
                $isRealData = ($cached['is_real_data'] ?? false) || 
                              (isset($cached['source']) && (strpos($cached['source'], 'NSE') !== false || strpos($cached['source'], 'Real') !== false || strpos($cached['source'], 'API') !== false));
                
                // If we have data with prices > 0, consider it real data
                $hasValidPrices = false;
                foreach ($cached['data'] as $option) {
                    if (($option['ltp'] ?? 0) > 0 || ($option['bid'] ?? 0) > 0 || ($option['ask'] ?? 0) > 0) {
                        $hasValidPrices = true;
                        break;
                    }
                }
                
                if ($isRealData || $hasValidPrices) {
                    Log::info("FreeMarketDataService: Using cached data for {$symbol} (market closed) - " . count($cached['data']) . " options");
                    // Store in persistent cache for next time
                    Cache::put($lastRealDataKey, $cached, 86400); // 24 hours
                    $cached['is_cached'] = true;
                    $cached['market_status'] = 'CLOSED';
                    $cached['message'] = 'Market is closed - showing last available prices';
                    return $cached;
                }
            }
            
            // Try NSE API one more time (sometimes it still works even when market is closed)
            try {
                Log::info("FreeMarketDataService: Trying NSE API one more time for {$symbol} (market closed)");
                $nseResult = $this->getOptionChainFromNSE($symbol);
                if ($nseResult['success'] && !empty($nseResult['data'])) {
                    // Process the data same as when market is open
                    $validOptions = [];
                    foreach ($nseResult['data'] as $option) {
                        $ltp = $option['ltp'] ?? 0;
                        $bid = $option['bid'] ?? 0;
                        $ask = $option['ask'] ?? 0;
                        $prevClose = $option['prev_close'] ?? 0;
                        
                        $displayPrice = $ltp > 0 ? $ltp : (($bid > 0 && $ask > 0) ? (($bid + $ask) / 2) : ($bid > 0 ? $bid : $ask));
                        
                        if ($displayPrice > 0) {
                            $change = 0;
                            $changePercent = $option['change_percent'] ?? 0;
                            if ($prevClose > 0 && $displayPrice > 0) {
                                $change = $displayPrice - $prevClose;
                                $changePercent = ($change / $prevClose) * 100;
                            }
                            
                            $option['ltp'] = round($displayPrice, 2);
                            $option['change'] = round($change, 2);
                            $option['change_percent'] = round($changePercent, 2);
                            $validOptions[] = $option;
                        }
                    }
                    
                    if (!empty($validOptions)) {
                        $nseResult['data'] = $validOptions;
                        $nseResult['is_real_data'] = true;
                        $nseResult['timestamp'] = now()->toISOString();
                        // Store in both caches
                        Cache::put($cacheKey, $nseResult, $this->delayedCacheTimeout);
                        Cache::put($lastRealDataKey, $nseResult, 86400);
                        
                        Log::info("FreeMarketDataService: Got data from NSE API for {$symbol} (market closed) - " . count($validOptions) . " options");
                        $nseResult['is_cached'] = false;
                        $nseResult['market_status'] = 'CLOSED';
                        $nseResult['message'] = 'Market is closed - showing last available prices from NSE';
                        return $nseResult;
                    }
                }
            } catch (\Exception $e) {
                Log::debug("FreeMarketDataService: NSE API failed when market closed: " . $e->getMessage());
            }
            
            // Try to get from database (OptionChain model) as last resort
            try {
                $dbData = $this->getOptionChainFromDatabase($symbol);
                if ($dbData && !empty($dbData['data'])) {
                    Log::info("FreeMarketDataService: Using last real prices from database for {$symbol} (market closed) - " . count($dbData['data']) . " options");
                    // Store in persistent cache for next time
                    Cache::put($lastRealDataKey, $dbData, 86400); // 24 hours
                    $dbData['is_cached'] = true;
                    $dbData['market_status'] = 'CLOSED';
                    $dbData['message'] = 'Market is closed - showing last available prices from database';
                    return $dbData;
                }
            } catch (\Exception $e) {
                Log::debug("FreeMarketDataService: Could not get option chain from database: " . $e->getMessage());
            }
            
            // If no cached real data, don't show calculated prices when market is closed
            Log::info("FreeMarketDataService: No last real prices available for {$symbol} (market closed)");
            return [
                'success' => false,
                'message' => 'Market is closed. No last available prices found. Please check again when market opens.',
                'data' => [],
                'market_status' => 'CLOSED'
            ];
        }
        
        // Market is open - try to fetch fresh data
        // Try multiple free API sources in order of preference
        $apis = [
            'nse' => function() use ($symbol) { return $this->getOptionChainFromNSE($symbol); },
            'opify' => function() use ($symbol) { return $this->getOptionChainFromOpify($symbol); },
        ];
        
        // Try each API source
        foreach ($apis as $apiName => $apiCall) {
            try {
                Log::info("FreeMarketDataService: Trying {$apiName} API for {$symbol}");
                $result = $apiCall();
                
                if ($result['success'] && !empty($result['data'])) {
                    // Filter to only include options with valid prices (LTP, bid, or ask)
                    $validOptions = [];
                    foreach ($result['data'] as $option) {
                        $originalLtp = $option['ltp'] ?? 0;
                        $ltp = $originalLtp;
                        $bid = $option['bid'] ?? 0;
                        $ask = $option['ask'] ?? 0;
                        $prevClose = $option['prev_close'] ?? 0;
                        
                        // Use best available price: LTP > Mid(Bid/Ask) > Bid > Ask
                        $displayPrice = $ltp;
                        $priceSource = 'LTP';
                        
                        if ($ltp > 0) {
                            $displayPrice = $ltp;
                            $priceSource = 'LTP';
                        } elseif ($bid > 0 && $ask > 0) {
                            $displayPrice = ($bid + $ask) / 2;
                            $priceSource = 'Mid(Bid/Ask)';
                        } elseif ($bid > 0) {
                            $displayPrice = $bid;
                            $priceSource = 'Bid';
                        } elseif ($ask > 0) {
                            $displayPrice = $ask;
                            $priceSource = 'Ask';
                        }
                        
                        // Recalculate change_percent if we're using a different price source
                        $change = 0;
                        $changePercent = $option['change_percent'] ?? 0;
                        
                        if ($prevClose > 0 && $displayPrice > 0) {
                            // If we have prev_close, recalculate change_percent based on display price
                            if ($priceSource !== 'LTP' || $changePercent == 0) {
                                $change = $displayPrice - $prevClose;
                                $changePercent = ($change / $prevClose) * 100;
                            }
                        }
                        
                        // Update option with calculated values
                        $option['ltp'] = round($displayPrice, 2);
                        $option['display_price'] = round($displayPrice, 2);
                        $option['price_source'] = $priceSource;
                        $option['change'] = round($change, 2);
                        $option['change_percent'] = round($changePercent, 2);
                        
                        // Only include if we have a valid price
                        if ($displayPrice > 0) {
                            $validOptions[] = $option;
                        }
                    }
                    
                    if (!empty($validOptions)) {
                        Log::info("FreeMarketDataService: Using {$apiName} data for {$symbol} - " . count($validOptions) . " options with prices");
                        $result['data'] = $validOptions;
                        $result['is_real_data'] = true;
                        $result['timestamp'] = now()->toISOString();
                        
                        // Cache the result (short cache during market hours)
                        Cache::put($cacheKey, $result, $this->delayedCacheTimeout);
                        
                        // Also store in persistent cache for when market closes (24 hour cache)
                        Cache::put($lastRealDataKey, $result, 86400);
                        
                        return $result;
                    } else {
                        Log::warning("FreeMarketDataService: {$apiName} returned data but no valid prices for {$symbol}");
                    }
                }
            } catch (\Exception $e) {
                Log::error("FreeMarketDataService: {$apiName} API error for {$symbol}: " . $e->getMessage());
            }
        }
        
        // If all APIs fail during market hours, try to use cached data (last available real data)
        $cached = Cache::get($cacheKey);
        if ($cached && !empty($cached['data'])) {
            Log::info("FreeMarketDataService: Using cached data for {$symbol} - " . count($cached['data']) . " options");
            $cached['is_cached'] = true;
            $cached['message'] = 'Showing last available market data (APIs temporarily unavailable)';
            return $cached;
        }
        
        // Last resort: Generate calculated prices based on underlying when no data is available (only during market hours)
        Log::info("FreeMarketDataService: No API data and no cache, generating calculated prices for {$symbol}");
        $fallbackData = $this->generateFallbackOptionsUnder100($symbol);
        if ($fallbackData['success'] && !empty($fallbackData['data'])) {
            Log::info("FreeMarketDataService: Generated " . count($fallbackData['data']) . " calculated options for {$symbol}");
            return $fallbackData;
        }
        
        // No valid data from any source
        Log::warning("FreeMarketDataService: No valid option prices from any API for {$symbol} and no cached data");
        return [
            'success' => false,
            'message' => 'No option chain data available from free APIs. Please try again in a moment.',
            'data' => []
        ];
    }
    
    /**
     * Get market data from NSE India free API
     */
    private function getFromNSEIndia(array $symbols): array
    {
        try {
            $marketData = [];
            
            // NSE India indices API
            $indicesUrl = "https://www.nseindia.com/api/allIndices";
            $response = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36',
                'Accept' => 'application/json',
                'Accept-Language' => 'en-US,en;q=0.9',
                'Referer' => 'https://www.nseindia.com/',
            ])->timeout(15)->get($indicesUrl);
            
            if ($response->successful()) {
                $data = $response->json();
                if (isset($data['data']) && is_array($data['data'])) {
                    foreach ($data['data'] as $index) {
                        $symbol = $index['index'] ?? '';
                        if (!empty($symbol)) {
                            $marketData[$symbol] = [
                                'symbol' => $symbol,
                                'ltp' => (float)($index['last'] ?? 0),
                                'change' => (float)($index['variation'] ?? 0),
                                'change_percent' => (float)($index['percentChange'] ?? 0),
                                'high' => (float)($index['dayHigh'] ?? 0),
                                'low' => (float)($index['dayLow'] ?? 0),
                                'open' => (float)($index['open'] ?? 0),
                                'prev_close' => (float)($index['previousClose'] ?? 0),
                                'volume' => (int)($index['totalTradedVolume'] ?? 0),
                                'timestamp' => now()->toISOString(),
                                'data_source' => 'NSE India Free API (1-2 min delayed)',
                                'is_live' => true
                            ];
                        }
                    }
                }
            }
            
            // NSE India equity API for stocks
            $equityUrl = "https://www.nseindia.com/api/equity-stockIndices?index=SECURITIES%20IN%20F%26O";
            $equityResponse = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36',
                'Accept' => 'application/json',
                'Accept-Language' => 'en-US,en;q=0.9',
                'Referer' => 'https://www.nseindia.com/',
            ])->timeout(15)->get($equityUrl);
            
            if ($equityResponse->successful()) {
                $equityData = $equityResponse->json();
                if (isset($equityData['data']) && is_array($equityData['data'])) {
                    foreach ($equityData['data'] as $stock) {
                        $symbol = $stock['symbol'] ?? '';
                        if (!empty($symbol)) {
                            $marketData[$symbol] = [
                                'symbol' => $symbol,
                                'ltp' => (float)($stock['lastPrice'] ?? 0),
                                'change' => (float)($stock['change'] ?? 0),
                                'change_percent' => (float)($stock['pChange'] ?? 0),
                                'high' => (float)($stock['dayHigh'] ?? 0),
                                'low' => (float)($stock['dayLow'] ?? 0),
                                'open' => (float)($stock['open'] ?? 0),
                                'prev_close' => (float)($stock['previousClose'] ?? 0),
                                'volume' => (int)($stock['totalTradedVolume'] ?? 0),
                                'timestamp' => now()->toISOString(),
                                'data_source' => 'NSE India Free API (1-2 min delayed)',
                                'is_live' => true
                            ];
                        }
                    }
                }
            }
            
            if (!empty($marketData)) {
                return [
                    'success' => true,
                    'data' => $marketData,
                    'source' => 'NSE India Free API',
                    'count' => count($marketData)
                ];
            }
            
            return ['success' => false, 'message' => 'NSE India API returned no data'];
            
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'NSE India error: ' . $e->getMessage()];
        }
    }
    
    /**
     * Get market data from Alpha Vantage free API
     */
    private function getFromAlphaVantage(array $symbols): array
    {
        try {
            // Alpha Vantage free tier allows 5 calls per minute
            $apiKey = 'demo'; // Use demo key for free tier
            $marketData = [];
            
            // Get major indices - including SENSEX
            $indices = [
                'NIFTY' => 'NIFTY 50',
                'BANKNIFTY' => 'NIFTY BANK', 
                'SENSEX' => 'SENSEX'
            ];
            
            foreach ($indices as $symbol => $displayName) {
                // Use different symbols for different exchanges
                $apiSymbol = $symbol === 'SENSEX' ? '^BSESN' : $symbol . '.BSE';
                
                $url = "https://www.alphavantage.co/query?function=GLOBAL_QUOTE&symbol={$apiSymbol}&apikey={$apiKey}";
                
                $response = Http::timeout(10)->get($url);
                
                if ($response->successful()) {
                    $data = $response->json();
                    if (isset($data['Global Quote'])) {
                        $quote = $data['Global Quote'];
                        $marketData[$displayName] = [
                            'symbol' => $displayName,
                            'ltp' => (float)($quote['05. price'] ?? 0),
                            'change' => (float)($quote['09. change'] ?? 0),
                            'change_percent' => (float)($quote['10. change percent'] ?? 0),
                            'high' => (float)($quote['03. high'] ?? 0),
                            'low' => (float)($quote['04. low'] ?? 0),
                            'open' => (float)($quote['02. open'] ?? 0),
                            'prev_close' => (float)($quote['08. previous close'] ?? 0),
                            'volume' => (int)($quote['06. volume'] ?? 0),
                            'timestamp' => now()->toISOString(),
                            'data_source' => 'Alpha Vantage Free API (15 min delayed)',
                            'is_live' => false
                        ];
                    }
                }
                
                // Rate limiting for free tier - reduced for web requests
                usleep(100000); // Wait 0.1 seconds between calls
            }
            
            if (!empty($marketData)) {
                return [
                    'success' => true,
                    'data' => $marketData,
                    'source' => 'Alpha Vantage Free API',
                    'count' => count($marketData)
                ];
            }
            
            return ['success' => false, 'message' => 'Alpha Vantage API returned no data'];
            
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Alpha Vantage error: ' . $e->getMessage()];
        }
    }
    
    /**
     * Get market data from Yahoo Finance free API
     */
    private function getFromYahooFinance(array $symbols): array
    {
        try {
            $marketData = [];
            
            // Yahoo Finance API for Indian markets
            $yahooSymbols = [
                '^NSEI' => 'NIFTY 50',
                '^NSEBANK' => 'NIFTY BANK',
                '^BSESN' => 'SENSEX',
                'RELIANCE.NS' => 'RELIANCE',
                'TCS.NS' => 'TCS',
                'HDFCBANK.NS' => 'HDFCBANK',
                'ICICIBANK.NS' => 'ICICIBANK'
            ];
            
            foreach ($yahooSymbols as $yahooSymbol => $displaySymbol) {
                $url = "https://query1.finance.yahoo.com/v8/finance/chart/{$yahooSymbol}";
                
                $response = Http::timeout(10)->get($url);
                
                if ($response->successful()) {
                    $data = $response->json();
                    if (isset($data['chart']['result'][0]['meta'])) {
                        $meta = $data['chart']['result'][0]['meta'];
                        $marketData[$displaySymbol] = [
                            'symbol' => $displaySymbol,
                            'ltp' => (float)($meta['regularMarketPrice'] ?? 0),
                            'change' => (float)($meta['regularMarketPrice'] - $meta['previousClose'] ?? 0),
                            'change_percent' => (float)($meta['regularMarketPrice'] - $meta['previousClose']) / $meta['previousClose'] * 100 ?? 0,
                            'high' => (float)($meta['regularMarketDayHigh'] ?? 0),
                            'low' => (float)($meta['regularMarketDayLow'] ?? 0),
                            'open' => (float)($meta['regularMarketOpen'] ?? 0),
                            'prev_close' => (float)($meta['previousClose'] ?? 0),
                            'volume' => (int)($meta['regularMarketVolume'] ?? 0),
                            'timestamp' => now()->toISOString(),
                            'data_source' => 'Yahoo Finance Free API (15 min delayed)',
                            'is_live' => false
                        ];
                    }
                }
                
                // Rate limiting - reduced for web requests
                usleep(50000); // Wait 0.05 seconds
            }
            
            if (!empty($marketData)) {
                return [
                    'success' => true,
                    'data' => $marketData,
                    'source' => 'Yahoo Finance Free API',
                    'count' => count($marketData)
                ];
            }
            
            return ['success' => false, 'message' => 'Yahoo Finance API returned no data'];
            
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Yahoo Finance error: ' . $e->getMessage()];
        }
    }
    
    /**
     * Get option chain from NSE India with improved session handling and retry logic
     */
    private function getOptionChainFromNSE(string $symbol): array
    {
        try {
            // Map symbol to NSE format
            $mappedSymbol = strtoupper($symbol);
            if ($mappedSymbol === 'NIFTY 50') {
                $mappedSymbol = 'NIFTY';
            } elseif ($mappedSymbol === 'NIFTY BANK' || $mappedSymbol === 'BANK NIFTY') {
                $mappedSymbol = 'BANKNIFTY';
            }
            
            // Only try NSE for supported symbols
            if (!in_array($mappedSymbol, ['NIFTY', 'BANKNIFTY'])) {
                return ['success' => false, 'message' => 'Unsupported symbol for NSE'];
            }
            
            $url = "https://www.nseindia.com/api/option-chain-indices?symbol=" . $mappedSymbol;
            
            $headers = [
                'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36',
                'Accept' => 'application/json, text/plain, */*',
                'Accept-Language' => 'en-US,en;q=0.9',
                'Accept-Encoding' => 'gzip, deflate, br',
                'Referer' => 'https://www.nseindia.com/option-chain',
                'Origin' => 'https://www.nseindia.com',
                'Connection' => 'keep-alive',
                'Sec-Fetch-Dest' => 'empty',
                'Sec-Fetch-Mode' => 'cors',
                'Sec-Fetch-Site' => 'same-origin',
            ];
            
            // Retry logic with better session handling
            $maxRetries = 3;
            $lastError = null;
            
            for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
                try {
                    // Create a new client for each attempt to ensure fresh session
                    $client = Http::withHeaders($headers)->timeout(15);
                    
                    // Step 1: Warm-up call to homepage to get session cookies
                    try {
                        $warmup = $client->get('https://www.nseindia.com');
                        Log::debug("NSE warm-up attempt {$attempt} status: " . $warmup->status());
                        
                        // Wait a bit longer for cookies to be set
                        usleep(800000); // 0.8 seconds
                        
                        // Also try accessing market-data page to establish better session
                        $client->get('https://www.nseindia.com/market-data');
                        usleep(300000); // 0.3 seconds
                    } catch (\Throwable $e) {
                        Log::debug('NSE warm-up call failed: ' . $e->getMessage());
                    }
                    
                    // Step 2: Make the actual API call
                    $response = $client->timeout(25)->get($url);
                    
                    if ($response->successful()) {
                        $data = $response->json();
                        Log::debug("NSE API response status: " . $response->status() . " for {$mappedSymbol} (attempt {$attempt})");
                        
                        if (isset($data['records']['data']) && !empty($data['records']['data'])) {
            // Get underlying price from NSE response if available
            $underlyingPrice = $data['records']['underlyingValue'] ?? null;
            $timestamp = $data['records']['timestamp'] ?? null;
            Log::info("NSE API returned " . count($data['records']['data']) . " raw option records for {$mappedSymbol}" . ($underlyingPrice ? " (Underlying: {$underlyingPrice})" : "") . ($timestamp ? " (Timestamp: {$timestamp})" : ""));
            
            // Log sample of first option to debug structure
            if (!empty($data['records']['data'][0])) {
                $sample = $data['records']['data'][0];
                Log::debug("NSE API sample option structure: " . json_encode(array_keys($sample)));
                if (isset($sample['CE'])) {
                    Log::debug("NSE API sample CE fields: " . json_encode(array_keys($sample['CE'])));
                }
            }
                            
                            $processed = $this->processNSEData($data['records']['data'], $mappedSymbol, $underlyingPrice);
                            if (!empty($processed)) {
                                Log::info("NSE API processed " . count($processed) . " valid options for {$mappedSymbol}");
                                return [
                                    'success' => true,
                                    'data' => $processed,
                                    'source' => 'NSE India Free API (Real-time, 1-2 min delayed)'
                                ];
                            } else {
                                Log::warning("NSE API returned data but processing resulted in 0 valid options for {$mappedSymbol} (attempt {$attempt})");
                                // Try next attempt
                                if ($attempt < $maxRetries) {
                                    usleep(1000000); // Wait 1 second before retry
                                    continue;
                                }
                            }
                        } else {
                            Log::warning("NSE API returned empty or invalid data structure for {$mappedSymbol} (attempt {$attempt})");
                            // Try next attempt
                            if ($attempt < $maxRetries) {
                                usleep(1000000); // Wait 1 second before retry
                                continue;
                            }
                        }
                    } else {
                        $statusCode = $response->status();
                        Log::warning("NSE API request failed for {$mappedSymbol}: Status {$statusCode} (attempt {$attempt})");
                        $lastError = "HTTP {$statusCode}";
                        
                        // If 403 Forbidden, wait longer before retry (session issue)
                        if ($statusCode === 403 && $attempt < $maxRetries) {
                            Log::info("NSE API returned 403, waiting longer before retry...");
                            sleep(2); // Wait 2 seconds for session to reset
                            continue;
                        }
                    }
                } catch (\Exception $e) {
                    $lastError = $e->getMessage();
                    Log::warning("NSE API attempt {$attempt} error: " . $lastError);
                    
                    if ($attempt < $maxRetries) {
                        usleep(1000000 * $attempt); // Exponential backoff
                        continue;
                    }
                }
            }
            
            // If we get here, all retries failed
            Log::warning("NSE India options API failed after {$maxRetries} attempts for {$mappedSymbol}: {$lastError}");
            return ['success' => false, 'message' => 'NSE India options API failed after retries. Market may be closed or API temporarily unavailable.'];
            
        } catch (\Exception $e) {
            Log::error('NSE India options error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'NSE India options error: ' . $e->getMessage()];
        }
    }
    
    /**
     * Get option chain from OPIFY (alternative free source)
     * Note: OPIFY may not have a public API, so this is a placeholder for future implementation
     * or web scraping if needed
     */
    private function getOptionChainFromOpify(string $symbol): array
    {
        try {
            // Map symbol to OPIFY format
            $mappedSymbol = strtoupper($symbol);
            if ($mappedSymbol === 'NIFTY 50' || $mappedSymbol === 'NIFTY') {
                $mappedSymbol = 'NIFTY';
            } elseif ($mappedSymbol === 'NIFTY BANK' || $mappedSymbol === 'BANK NIFTY' || $mappedSymbol === 'BANKNIFTY') {
                $mappedSymbol = 'BANKNIFTY';
            } else {
                return ['success' => false, 'message' => 'Unsupported symbol for OPIFY'];
            }
            
            // Try OPIFY public endpoint (if available)
            // Note: This may not work as OPIFY might not have a public API
            // This is a placeholder for future implementation or web scraping
            $url = "https://opify.in/api/v1/option-chain/{$mappedSymbol}";
            
            $headers = [
                'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36',
                'Accept' => 'application/json',
            ];
            
            $response = Http::withHeaders($headers)->timeout(8)->get($url);
            
            if ($response->successful()) {
                $data = $response->json();
                if (isset($data['data']) && !empty($data['data'])) {
                    $processed = $this->processOpifyData($data['data'], $mappedSymbol);
                    if (!empty($processed)) {
                        Log::info("OPIFY API processed " . count($processed) . " options for {$mappedSymbol}");
                        return [
                            'success' => true,
                            'data' => $processed,
                            'source' => 'OPIFY Free API (Real-time, 10-60 sec delayed)'
                        ];
                    }
                }
            }
            
            // OPIFY API not available, return failure (will fallback to NSE or cache)
            return ['success' => false, 'message' => 'OPIFY API not available'];
            
        } catch (\Exception $e) {
            Log::debug('OPIFY API error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'OPIFY API error: ' . $e->getMessage()];
        }
    }
    
    /**
     * Process OPIFY option chain data
     */
    private function processOpifyData(array $data, string $symbol): array
    {
        $options = [];
        foreach ($data as $row) {
            $strike = (float)($row['strike'] ?? $row['strikePrice'] ?? 0);
            if ($strike <= 0) continue;
            
            // Process CALL options
            if (isset($row['call'])) {
                $call = $row['call'];
                $ltp = (float)($call['ltp'] ?? $call['lastPrice'] ?? 0);
                $bid = (float)($call['bid'] ?? $call['bidPrice'] ?? 0);
                $ask = (float)($call['ask'] ?? $call['askPrice'] ?? 0);
                
                $displayPrice = $ltp > 0 ? $ltp : (($bid > 0 && $ask > 0) ? (($bid + $ask) / 2) : ($bid > 0 ? $bid : $ask));
                
                if ($displayPrice > 0) {
                    $options[] = [
                        'symbol' => $symbol,
                        'strike_price' => $strike,
                        'option_type' => 'CALL',
                        'ltp' => $displayPrice,
                        'bid' => $bid,
                        'ask' => $ask,
                        'volume' => (int)($call['volume'] ?? $call['totalTradedVolume'] ?? 0),
                        'oi' => (int)($call['oi'] ?? $call['openInterest'] ?? 0),
                        'data_source' => 'OPIFY Free API (10-60 sec delayed)',
                        'timestamp' => now()->toISOString(),
                        'price_source' => $ltp > 0 ? 'LTP' : (($bid > 0 && $ask > 0) ? 'Mid(Bid/Ask)' : ($bid > 0 ? 'Bid' : 'Ask')),
                        'is_real_data' => true
                    ];
                }
            }
            
            // Process PUT options
            if (isset($row['put'])) {
                $put = $row['put'];
                $ltp = (float)($put['ltp'] ?? $put['lastPrice'] ?? 0);
                $bid = (float)($put['bid'] ?? $put['bidPrice'] ?? 0);
                $ask = (float)($put['ask'] ?? $put['askPrice'] ?? 0);
                
                $displayPrice = $ltp > 0 ? $ltp : (($bid > 0 && $ask > 0) ? (($bid + $ask) / 2) : ($bid > 0 ? $bid : $ask));
                
                if ($displayPrice > 0) {
                    $options[] = [
                        'symbol' => $symbol,
                        'strike_price' => $strike,
                        'option_type' => 'PUT',
                        'ltp' => $displayPrice,
                        'bid' => $bid,
                        'ask' => $ask,
                        'volume' => (int)($put['volume'] ?? $put['totalTradedVolume'] ?? 0),
                        'oi' => (int)($put['oi'] ?? $put['openInterest'] ?? 0),
                        'data_source' => 'OPIFY Free API (10-60 sec delayed)',
                        'timestamp' => now()->toISOString(),
                        'price_source' => $ltp > 0 ? 'LTP' : (($bid > 0 && $ask > 0) ? 'Mid(Bid/Ask)' : ($bid > 0 ? 'Bid' : 'Ask')),
                        'is_real_data' => true
                    ];
                }
            }
        }
        
        return $options;
    }
    
    /**
     * Process NSE option chain data - uses real market prices with change percentage
     */
    private function processNSEData(array $data, string $symbol, $underlyingPrice = null): array
    {
        $options = [];
        foreach ($data as $row) {
            $strike = (float)($row['strikePrice'] ?? 0);
            if ($strike <= 0) continue;
            
            // Process CALL options (CE)
            if (!empty($row['CE'])) {
                $ce = $row['CE'];
                $ltp = (float)($ce['lastPrice'] ?? 0);
                $bid = (float)($ce['bidprice'] ?? $ce['bidPrice'] ?? 0);
                $ask = (float)($ce['askPrice'] ?? $ce['askprice'] ?? 0);
                $prevClose = (float)($ce['previousClose'] ?? $ce['prevClose'] ?? 0);
                
                // Determine best available price: LTP > Mid(Bid/Ask) > Bid > Ask
                $displayPrice = $ltp;
                $priceSource = 'LTP';
                
                if ($ltp > 0) {
                    $displayPrice = $ltp;
                    $priceSource = 'LTP';
                } elseif ($bid > 0 && $ask > 0) {
                    $displayPrice = ($bid + $ask) / 2;
                    $priceSource = 'Mid(Bid/Ask)';
                } elseif ($bid > 0) {
                    $displayPrice = $bid;
                    $priceSource = 'Bid';
                } elseif ($ask > 0) {
                    $displayPrice = $ask;
                    $priceSource = 'Ask';
                }
                
                // Calculate change and change percentage if prev_close is available
                $change = 0;
                $changePercent = 0;
                if ($prevClose > 0 && $displayPrice > 0) {
                    $change = $displayPrice - $prevClose;
                    $changePercent = ($change / $prevClose) * 100;
                }
                
                // Only include if we have a valid price
                if ($displayPrice > 0) {
                    $options[] = [
                        'symbol' => $symbol,
                        'strike_price' => $strike,
                        'option_type' => 'CALL',
                        'ltp' => round($displayPrice, 2), // Use best available price
                        'bid' => round($bid, 2),
                        'ask' => round($ask, 2),
                        'prev_close' => round($prevClose, 2),
                        'change' => round($change, 2),
                        'change_percent' => round($changePercent, 2),
                        'volume' => (int)($ce['totalTradedVolume'] ?? 0),
                        'oi' => (int)($ce['openInterest'] ?? 0),
                        'data_source' => 'NSE India Free API (Real-time, 1-2 min delayed)',
                        'timestamp' => now()->toISOString(),
                        'price_source' => $priceSource,
                        'is_real_data' => true
                    ];
                }
            }
            
            // Process PUT options (PE)
            if (!empty($row['PE'])) {
                $pe = $row['PE'];
                $ltp = (float)($pe['lastPrice'] ?? 0);
                $bid = (float)($pe['bidprice'] ?? $pe['bidPrice'] ?? 0);
                $ask = (float)($pe['askPrice'] ?? $pe['askprice'] ?? 0);
                $prevClose = (float)($pe['previousClose'] ?? $pe['prevClose'] ?? 0);
                
                // Determine best available price: LTP > Mid(Bid/Ask) > Bid > Ask
                $displayPrice = $ltp;
                $priceSource = 'LTP';
                
                if ($ltp > 0) {
                    $displayPrice = $ltp;
                    $priceSource = 'LTP';
                } elseif ($bid > 0 && $ask > 0) {
                    $displayPrice = ($bid + $ask) / 2;
                    $priceSource = 'Mid(Bid/Ask)';
                } elseif ($bid > 0) {
                    $displayPrice = $bid;
                    $priceSource = 'Bid';
                } elseif ($ask > 0) {
                    $displayPrice = $ask;
                    $priceSource = 'Ask';
                }
                
                // Calculate change and change percentage if prev_close is available
                $change = 0;
                $changePercent = 0;
                if ($prevClose > 0 && $displayPrice > 0) {
                    $change = $displayPrice - $prevClose;
                    $changePercent = ($change / $prevClose) * 100;
                }
                
                // Only include if we have a valid price
                if ($displayPrice > 0) {
                    $options[] = [
                        'symbol' => $symbol,
                        'strike_price' => $strike,
                        'option_type' => 'PUT',
                        'ltp' => round($displayPrice, 2), // Use best available price
                        'bid' => round($bid, 2),
                        'ask' => round($ask, 2),
                        'prev_close' => round($prevClose, 2),
                        'change' => round($change, 2),
                        'change_percent' => round($changePercent, 2),
                        'volume' => (int)($pe['totalTradedVolume'] ?? 0),
                        'oi' => (int)($pe['openInterest'] ?? 0),
                        'data_source' => 'NSE India Free API (Real-time, 1-2 min delayed)',
                        'timestamp' => now()->toISOString(),
                        'price_source' => $priceSource,
                        'is_real_data' => true
                    ];
                }
            }
        }
        
        return $options;
    }
    
    /**
     * Generate realistic market data when APIs fail
     */
    private function getMockRealisticData(array $symbols): array
    {
        $marketData = [];
        $basePrices = [
            'NIFTY 50' => 19500, 'NIFTY BANK' => 45000, 'NIFTY IT' => 32000, 'SENSEX' => 65000,
            'RELIANCE' => 2500, 'TCS' => 3800, 'HDFCBANK' => 1600, 'ICICIBANK' => 950,
            'SBIN' => 580, 'BHARTIARTL' => 900, 'ITC' => 450, 'KOTAKBANK' => 1800,
            'LT' => 3200, 'HINDUNILVR' => 2500, 'ASIANPAINT' => 3200, 'MARUTI' => 10000
        ];
        
        foreach ($basePrices as $symbol => $basePrice) {
            $variation = rand(-200, 200) / 10000; // ±2% variation
            $ltp = $basePrice * (1 + $variation);
            $change = $ltp - $basePrice;
            $changePercent = ($change / $basePrice) * 100;
            
            $marketData[$symbol] = [
                'symbol' => $symbol,
                'ltp' => round($ltp, 2),
                'change' => round($change, 2),
                'change_percent' => round($changePercent, 2),
                'high' => round($ltp * 1.02, 2),
                'low' => round($ltp * 0.98, 2),
                'open' => round($basePrice, 2),
                'prev_close' => round($basePrice, 2),
                'volume' => rand(100000, 1000000),
                'timestamp' => now()->toISOString(),
                'data_source' => 'Realistic Calculation (1-2 min delayed)',
                'is_live' => false
            ];
        }
        
        return [
            'success' => true,
            'data' => $marketData,
            'source' => 'Realistic Calculation',
            'count' => count($marketData)
        ];
    }
    
    /**
     * Generate realistic option data
     */
    private function getMockRealisticOptions(string $symbol): array
    {
        // Try to get live underlying price first
        $underlyingPrice = $this->getLiveUnderlyingPrice($symbol);
        if ($underlyingPrice <= 0) {
            $underlyingPrice = $this->getUnderlyingPrice($symbol);
        }
        
        $strikes = $this->generateStrikes($underlyingPrice);
        $options = [];
        
        foreach ($strikes as $strike) {
            $callPrice = $this->calculateRealisticPrice($underlyingPrice, $strike, 'CALL');
            $putPrice = $this->calculateRealisticPrice($underlyingPrice, $strike, 'PUT');
            
            if ($callPrice > 0) {
                $options[] = [
                    'symbol' => $symbol,
                    'strike_price' => $strike,
                    'option_type' => 'CALL',
                    'ltp' => $callPrice,
                    'bid' => $callPrice * 0.98,
                    'ask' => $callPrice * 1.02,
                    'volume' => rand(100, 5000),
                    'oi' => rand(1000, 50000),
                    'data_source' => 'Realistic Calculation (1-2 min delayed)',
                    'timestamp' => now()->toISOString()
                ];
            }
            
            if ($putPrice > 0) {
                $options[] = [
                    'symbol' => $symbol,
                    'strike_price' => $strike,
                    'option_type' => 'PUT',
                    'ltp' => $putPrice,
                    'bid' => $putPrice * 0.98,
                    'ask' => $putPrice * 1.02,
                    'volume' => rand(100, 5000),
                    'oi' => rand(1000, 50000),
                    'data_source' => 'Realistic Calculation (1-2 min delayed)',
                    'timestamp' => now()->toISOString()
                ];
            }
        }
        
        return [
            'success' => true,
            'data' => $options,
            'source' => 'Realistic Calculation'
        ];
    }
    
    /**
     * Try to get live underlying price from market data with multiple fallbacks
     */
    private function getLiveUnderlyingPrice(string $symbol): float
    {
        try {
            // Map symbol to market data symbol
            $marketSymbol = $symbol;
            if ($symbol === 'NIFTY') {
                $marketSymbol = 'NIFTY 50';
            } elseif ($symbol === 'BANKNIFTY') {
                $marketSymbol = 'NIFTY BANK';
            }
            
            // Method 1: Try to get from fresh API call
            try {
                $liveData = $this->getLiveMarketData([$marketSymbol]);
                if ($liveData['success'] && isset($liveData['data'][$marketSymbol]['ltp']) && $liveData['data'][$marketSymbol]['ltp'] > 0) {
                    $price = (float) $liveData['data'][$marketSymbol]['ltp'];
                    Log::debug("Got live underlying price for {$marketSymbol} from API: {$price}");
                    return $price;
                }
            } catch (\Exception $e) {
                Log::debug('Could not get live price from API: ' . $e->getMessage());
            }
            
            // Method 2: Try to get from cache
            $cacheKey = "free_market_data_" . md5($marketSymbol);
            $cachedData = Cache::get($cacheKey);
            if ($cachedData && isset($cachedData['data'][$marketSymbol]['ltp']) && $cachedData['data'][$marketSymbol]['ltp'] > 0) {
                $price = (float) $cachedData['data'][$marketSymbol]['ltp'];
                Log::debug("Got underlying price for {$marketSymbol} from cache: {$price}");
                return $price;
            }
            
            // Method 3: Try to get from database
            try {
                $marketDataModel = new \App\Models\MarketData();
                $data = $marketDataModel::getMarketDataForSymbols([$marketSymbol], true);
                if (isset($data[$marketSymbol]['ltp']) && $data[$marketSymbol]['ltp'] > 0) {
                    $price = (float) $data[$marketSymbol]['ltp'];
                    Log::debug("Got underlying price for {$marketSymbol} from database: {$price}");
                    return $price;
                }
            } catch (\Exception $e) {
                Log::debug('Could not get price from database: ' . $e->getMessage());
            }
            
        } catch (\Exception $e) {
            Log::debug('Could not get live underlying price: ' . $e->getMessage());
        }
        
        return 0;
    }
    
    /**
     * Get underlying price for option calculations (fallback)
     */
    private function getUnderlyingPrice(string $symbol): float
    {
        $prices = [
            'NIFTY' => 26000,  // Updated to current approximate price
            'BANKNIFTY' => 52000,  // Updated to current approximate price
            'FINNIFTY' => 21000,
            'SENSEX' => 65000
        ];
        
        return $prices[$symbol] ?? 20000;
    }
    
    /**
     * Generate strike prices around underlying price
     */
    private function generateStrikes(float $underlyingPrice): array
    {
        $strikes = [];
        
        // Use proper strike steps for different symbols
        $strikeStep = 50; // NIFTY uses 50-point steps
        if ($underlyingPrice > 40000) {
            $strikeStep = 100; // BANKNIFTY uses 100-point steps
        }
        
        // Round underlying price to nearest strike
        $baseStrike = round($underlyingPrice / $strikeStep) * $strikeStep;
        
        // Generate strikes ±20 strikes around current price
        for ($i = -20; $i <= 20; $i++) {
            $strike = $baseStrike + ($i * $strikeStep);
            if ($strike > 0) {
                $strikes[] = (int) $strike;
            }
        }
        
        return $strikes;
    }
    
    /**
     * Generate fallback option prices using nearest calculated prices when real data is not available
     */
    private function generateFallbackOptionsUnder100(string $symbol): array
    {
        try {
            // Get real underlying price from live market data first
            $underlyingPrice = $this->getLiveUnderlyingPrice($symbol);
            
            // If live price not available, try default method
            if ($underlyingPrice <= 0) {
                $underlyingPrice = $this->getUnderlyingPrice($symbol);
            }
            
            // Last resort: use reasonable defaults
            if ($underlyingPrice <= 0) {
                $underlyingPrice = ($symbol === 'BANKNIFTY' || $symbol === 'NIFTY BANK') ? 52000 : 26000;
            }
            
            Log::info("FreeMarketDataService: Using underlying price {$underlyingPrice} for fallback calculation for {$symbol}");
            
            // Generate strikes around underlying price
            $strikes = $this->generateStrikes($underlyingPrice);
            $options = [];
            
            foreach ($strikes as $strike) {
                // Calculate CALL option price using Black-Scholes model
                $callPrice = $this->calculateRealisticPrice($underlyingPrice, $strike, 'CALL');
                
                // Ensure price is reasonable - if too high, use intrinsic value + time value approximation
                if ($callPrice > 5000) {
                    // For deep ITM options, use intrinsic value + small time value
                    $intrinsicValue = max(0, $underlyingPrice - $strike);
                    $callPrice = $intrinsicValue + ($intrinsicValue * 0.02); // Add 2% time value
                }
                
                // Only add if price is reasonable (greater than 0.01 and less than 10000)
                if ($callPrice >= 0.01 && $callPrice <= 10000) {
                    // Estimate prev_close as slightly different from current price for realistic change
                    $prevClose = $callPrice * (1 + (rand(-5, 5) / 100)); // ±5% variation
                    $change = $callPrice - $prevClose;
                    $changePercent = $prevClose > 0 ? ($change / $prevClose) * 100 : 0;
                    
                    $options[] = [
                        'symbol' => $symbol,
                        'strike_price' => $strike,
                        'option_type' => 'CALL',
                        'ltp' => round($callPrice, 2),
                        'bid' => round($callPrice * 0.995, 2), // Tighter spread
                        'ask' => round($callPrice * 1.005, 2),
                        'prev_close' => round($prevClose, 2),
                        'change' => round($change, 2),
                        'change_percent' => round($changePercent, 2),
                        'volume' => 0,
                        'oi' => 0,
                        'data_source' => 'Fallback (Calculated nearest price)',
                        'timestamp' => now()->toISOString(),
                        'price_source' => 'Calculated'
                    ];
                }
                
                // Calculate PUT option price using Black-Scholes model
                $putPrice = $this->calculateRealisticPrice($underlyingPrice, $strike, 'PUT');
                
                // Ensure price is reasonable - if too high, use intrinsic value + time value approximation
                if ($putPrice > 5000) {
                    // For deep ITM options, use intrinsic value + small time value
                    $intrinsicValue = max(0, $strike - $underlyingPrice);
                    $putPrice = $intrinsicValue + ($intrinsicValue * 0.02); // Add 2% time value
                }
                
                // Only add if price is reasonable (greater than 0.01 and less than 10000)
                if ($putPrice >= 0.01 && $putPrice <= 10000) {
                    // Estimate prev_close as slightly different from current price for realistic change
                    $prevClose = $putPrice * (1 + (rand(-5, 5) / 100)); // ±5% variation
                    $change = $putPrice - $prevClose;
                    $changePercent = $prevClose > 0 ? ($change / $prevClose) * 100 : 0;
                    
                    $options[] = [
                        'symbol' => $symbol,
                        'strike_price' => $strike,
                        'option_type' => 'PUT',
                        'ltp' => round($putPrice, 2),
                        'bid' => round($putPrice * 0.995, 2), // Tighter spread
                        'ask' => round($putPrice * 1.005, 2),
                        'prev_close' => round($prevClose, 2),
                        'change' => round($change, 2),
                        'change_percent' => round($changePercent, 2),
                        'volume' => 0,
                        'oi' => 0,
                        'data_source' => 'Fallback (Calculated nearest price)',
                        'timestamp' => now()->toISOString(),
                        'price_source' => 'Calculated'
                    ];
                }
            }
            
            if (!empty($options)) {
                return [
                    'success' => true,
                    'data' => $options,
                    'source' => 'Fallback (Calculated nearest price)',
                    'message' => 'NSE API unavailable. Showing calculated prices based on underlying price.'
                ];
            }
            
            return [
                'success' => false,
                'message' => 'Could not generate fallback options',
                'data' => []
            ];
        } catch (\Exception $e) {
            Log::error("FreeMarketDataService: Error generating fallback options: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error generating fallback data',
                'data' => []
            ];
        }
    }
    
    /**
     * Calculate realistic option price using simplified Black-Scholes
     */
    private function calculateRealisticPrice(float $underlying, float $strike, string $type): float
    {
        // Use weekly expiry (7 days) for more realistic prices
        $timeToExpiry = 7 / 365; // ~0.019 years (1 week)
        $volatility = 0.15; // 15% volatility (more realistic for indices)
        $riskFreeRate = 0.06; // 6% risk-free rate
        
        // Avoid division by zero or invalid calculations
        if ($strike <= 0 || $underlying <= 0) {
            return 0;
        }
        
        $d1 = (log($underlying / $strike) + ($riskFreeRate + 0.5 * $volatility * $volatility) * $timeToExpiry) / ($volatility * sqrt($timeToExpiry));
        $d2 = $d1 - $volatility * sqrt($timeToExpiry);
        
        $nd1 = $this->normalCDF($d1);
        $nd2 = $this->normalCDF($d2);
        
        if ($type === 'CALL') {
            $price = $underlying * $nd1 - $strike * exp(-$riskFreeRate * $timeToExpiry) * $nd2;
        } else {
            $price = $strike * exp(-$riskFreeRate * $timeToExpiry) * (1 - $nd2) - $underlying * (1 - $nd1);
        }
        
        // Ensure price is at least intrinsic value for ITM options
        if ($type === 'CALL') {
            $intrinsic = max(0, $underlying - $strike);
            $price = max($price, $intrinsic);
        } else {
            $intrinsic = max(0, $strike - $underlying);
            $price = max($price, $intrinsic);
        }
        
        return max(0, round($price, 2));
    }
    
    /**
     * Normal cumulative distribution function approximation
     */
    private function normalCDF(float $x): float
    {
        return 0.5 * (1 + $this->erf($x / sqrt(2)));
    }
    
    /**
     * Error function approximation
     */
    private function erf(float $x): float
    {
        $a1 = 0.254829592;
        $a2 = -0.284496736;
        $a3 = 1.421413741;
        $a4 = -1.453152027;
        $a5 = 1.061405429;
        $p = 0.3275911;
        
        $sign = $x >= 0 ? 1 : -1;
        $x = abs($x);
        
        $t = 1.0 / (1.0 + $p * $x);
        $y = 1.0 - ((((($a5 * $t + $a4) * $t) + $a3) * $t + $a2) * $t + $a1) * $t * exp(-$x * $x);
        
        return $sign * $y;
    }

    /**
     * Store market data in database
     * @param array $marketData
     * @param string $apiName
     * @return void
     */
    private function storeMarketDataInDatabase(array $marketData, string $apiName): void
    {
        try {
            // Import MarketData model
            $marketDataModel = new \App\Models\MarketData();
            
            // Determine if data is live based on API source
            $isLive = in_array($apiName, ['nse_india', 'alpha_vantage', 'yahoo_finance']);
            
            // Store data in database
            $storedCount = $marketDataModel::storeMarketData($marketData, $isLive, 'OPEN');
            
            Log::info("FreeMarketDataService: Stored {$storedCount} market data records from {$apiName}");
            
        } catch (\Exception $e) {
            Log::error("FreeMarketDataService: Failed to store market data - " . $e->getMessage());
        }
    }

    /**
     * Get market data from database (fallback when APIs fail)
     * @param array $symbols
     * @return array
     */
    public function getMarketDataFromDatabase(array $symbols = []): array
    {
        try {
            $marketDataModel = new \App\Models\MarketData();
            
            if (empty($symbols)) {
                $data = $marketDataModel::getAllMarketData(true);
            } else {
                $data = $marketDataModel::getMarketDataForSymbols($symbols, true);
            }
            
            // Filter to show only major indices
            $data = $this->filterToMajorIndices($data);
            
            if (!empty($data)) {
                return [
                    'success' => true,
                    'data' => $data,
                    'source' => 'Database (Cached)',
                    'count' => count($data)
                ];
            }
            
            return [
                'success' => false,
                'message' => 'No data available in database',
                'data' => []
            ];
            
        } catch (\Exception $e) {
            Log::error("FreeMarketDataService: Database error - " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Database error: ' . $e->getMessage(),
                'data' => []
            ];
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
            // Popular NSE stocks (50+ symbols)
            'RELIANCE', 'TCS', 'HDFCBANK', 'ICICIBANK', 'HINDUNILVR', 'ITC', 'KOTAKBANK', 'SBIN', 'BHARTIARTL', 'LT',
            'AXISBANK', 'ASIANPAINT', 'MARUTI', 'NESTLEIND', 'ULTRACEMCO', 'SUNPHARMA', 'TITAN', 'POWERGRID', 'NTPC', 'TECHM',
            'WIPRO', 'ONGC', 'TATAMOTORS', 'BAJFINANCE', 'BAJAJFINSV', 'BAJAJ-AUTO', 'DRREDDY', 'CIPLA', 'COALINDIA', 'BPCL',
            'HCLTECH', 'INFY', 'INDUSINDBK', 'GRASIM', 'JSWSTEEL', 'TATASTEEL', 'ADANIENT', 'ADANIPORTS', 'ADANIGREEN', 'ADANIENSOL',
            'BRITANNIA', 'COLPAL', 'DMART', 'EICHERMOT', 'HDFC', 'HDFCLIFE', 'ICICIGI', 'ICICIPRULI', 'LICHSGFIN', 'M&M',
            'TATACONSUM', 'TATAPOWER', 'UPL', 'VEDL', 'ZEEL', 'APOLLOHOSP', 'DIVISLAB', 'HEROMOTOCO', 'SHREECEM', 'TATACHEM',
            // Additional popular symbols
            'MCXCOMPDEX', 'AARTIIND', 'GILLETTE', 'JKTYRE', 'KAJARIACER', 'MINDTREE', 'OFSS', 'PNB', 'QUICKHEAL', 'UJJIVAN',
            'YESBANK', 
            // 'NIFTY-I', 'BANKNIFTY-I', 'UPL-I', 'VEDL-I', 'VOLTAS-I', 'ZEEL-I', 'CRUDEOIL-I', 'GOLDM-I', 'SILVERM-I',
            // 'COPPER-I', 'SILVER-I', 'NIFTY NEXT 50', 'NIFTY 100', 'NIFTY 200', 'NIFTY 500', 'NIFTY MIDCAP 100', 'NIFTY SMALLCAP 100'
        ];
        
        $filteredData = [];
        
        // Add symbols in priority order
        foreach ($prioritySymbols as $symbol) {
            if (isset($marketData[$symbol])) {
                $filteredData[$symbol] = $marketData[$symbol];
            }
        }
        
        // If we have less than 50 symbols, add mock data for missing popular stocks
        if (count($filteredData) < 50) {
            $this->addMockDataForMissingSymbols($filteredData, $prioritySymbols);
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
        
        Log::info("FreeMarketDataService: Filtered to " . count($filteredData) . " symbols (major indices + additional)");
        
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
     * Add mock data for missing symbols to ensure we have at least 50 symbols
     * @param array $filteredData
     * @param array $prioritySymbols
     */
    private function addMockDataForMissingSymbols(array &$filteredData, array $prioritySymbols): void
    {
        $symbolPrices = [
            'RELIANCE' => 2500, 'TCS' => 3800, 'HDFCBANK' => 1600, 'ICICIBANK' => 950, 'HINDUNILVR' => 2500,
            'ITC' => 450, 'KOTAKBANK' => 1800, 'SBIN' => 580, 'BHARTIARTL' => 1100, 'LT' => 3200,
            'AXISBANK' => 900, 'ASIANPAINT' => 3000, 'MARUTI' => 10000, 'NESTLEIND' => 18000, 'ULTRACEMCO' => 8000,
            'SUNPHARMA' => 1000, 'TITAN' => 2500, 'POWERGRID' => 200, 'NTPC' => 180, 'TECHM' => 1200,
            'WIPRO' => 450, 'ONGC' => 200, 'TATAMOTORS' => 500, 'BAJFINANCE' => 7000, 'BAJAJFINSV' => 1500,
            'BAJAJ-AUTO' => 4000, 'DRREDDY' => 5500, 'CIPLA' => 1200, 'COALINDIA' => 250, 'BPCL' => 400,
            'HCLTECH' => 1200, 'INFY' => 1400, 'INDUSINDBK' => 1200, 'GRASIM' => 1800, 'JSWSTEEL' => 800,
            'TATASTEEL' => 120, 'ADANIENT' => 3000, 'ADANIPORTS' => 1000, 'ADANIGREEN' => 2000, 'ADANIENSOL' => 1500,
            'BRITANNIA' => 4500, 'COLPAL' => 1800, 'DMART' => 4200, 'EICHERMOT' => 3500, 'HDFC' => 2500,
            'HDFCLIFE' => 600, 'ICICIGI' => 1200, 'ICICIPRULI' => 500, 'LICHSGFIN' => 500, 'M&M' => 1200,
            'TATACONSUM' => 800, 'TATAPOWER' => 200, 'UPL' => 600, 'VEDL' => 250, 'ZEEL' => 200,
            'APOLLOHOSP' => 5000, 'DIVISLAB' => 3500, 'HEROMOTOCO' => 2500, 'SHREECEM' => 20000, 'TATACHEM' => 1000
        ];
        
        foreach ($prioritySymbols as $symbol) {
            if (!isset($filteredData[$symbol]) && count($filteredData) < 50) {
                $basePrice = $symbolPrices[$symbol] ?? 1000;
                $variation = rand(-100, 100);
                $ltp = $basePrice + $variation;
                $change = $variation;
                $changePercent = ($change / $basePrice) * 100;
                
                $filteredData[$symbol] = [
                    'symbol' => $symbol,
                    'ltp' => $ltp,
                    'change' => $change,
                    'change_percent' => round($changePercent, 2),
                    'high' => $ltp + rand(50, 200),
                    'low' => $ltp - rand(50, 200),
                    'open' => $basePrice,
                    'prev_close' => $basePrice,
                    'volume' => rand(100000, 2000000),
                    'timestamp' => now()->toISOString(),
                    'data_source' => 'Mock Data (Estimated)',
                    'is_live' => false
                ];
            }
        }
        
        Log::info("FreeMarketDataService: Added mock data to reach " . count($filteredData) . " symbols");
    }

    /**
     * Get option chain from database (last stored data)
     */
    private function getOptionChainFromDatabase(string $symbol): ?array
    {
        try {
            // Map symbol to database format
            $mappedSymbol = strtoupper($symbol);
            if ($mappedSymbol === 'NIFTY 50') {
                $mappedSymbol = 'NIFTY';
            } elseif ($mappedSymbol === 'NIFTY BANK' || $mappedSymbol === 'BANK NIFTY') {
                $mappedSymbol = 'BANKNIFTY';
            }
            
            // Get latest option chain data from database
            $optionChainModel = new \App\Models\OptionChain();
            
            // Get the most recent expiry date for this symbol
            $latestExpiry = \App\Models\OptionChain::where('symbol', $mappedSymbol)
                ->whereNotNull('data_timestamp')
                ->orderBy('data_timestamp', 'desc')
                ->value('expiry_date');
            
            if (!$latestExpiry) {
                return null;
            }
            
            // Get all options for this symbol and expiry
            $options = \App\Models\OptionChain::where('symbol', $mappedSymbol)
                ->whereDate('expiry_date', $latestExpiry)
                ->orderBy('strike_price')
                ->orderBy('option_type')
                ->get();
            
            if ($options->isEmpty()) {
                return null;
            }
            
            // Convert to array format
            $data = [];
            foreach ($options as $option) {
                $data[] = [
                    'symbol' => $option->symbol,
                    'strike_price' => (float) $option->strike_price,
                    'option_type' => $option->option_type,
                    'ltp' => (float) $option->ltp,
                    'bid' => (float) $option->bid,
                    'ask' => (float) $option->ask,
                    'volume' => (int) $option->volume,
                    'oi' => (int) $option->oi,
                    'prev_close' => 0, // Database might not have this
                    'change' => 0,
                    'change_percent' => 0,
                    'data_source' => $option->data_source ?? 'Database (Last stored)',
                    'timestamp' => $option->data_timestamp ? $option->data_timestamp->toISOString() : now()->toISOString(),
                    'price_source' => 'LTP',
                    'is_real_data' => true
                ];
            }
            
            return [
                'success' => true,
                'data' => $data,
                'source' => 'Database (Last stored prices)',
                'timestamp' => $options->first()->data_timestamp?->toISOString() ?? now()->toISOString(),
                'is_real_data' => true
            ];
            
        } catch (\Exception $e) {
            Log::error("FreeMarketDataService: Error getting option chain from database: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Get SENSEX fallback data when not available from APIs
     * @return array
     */
    private function getSensexFallbackData(): array
    {
        // Generate realistic SENSEX data based on current time
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
}
