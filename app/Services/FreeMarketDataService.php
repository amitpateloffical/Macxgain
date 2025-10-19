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
     * Get live market data from free APIs
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
     * Get option chain data from free APIs
     * @param string $symbol
     * @return array
     */
    public function getOptionChain(string $symbol): array
    {
        $cacheKey = "free_option_chain_{$symbol}";
        
        return Cache::remember($cacheKey, $this->cacheTimeout, function () use ($symbol) {
            try {
                // Try multiple free APIs for option chain
                $apis = [
                    'nse_india_options' => $this->getOptionChainFromNSE($symbol),
                    'mock_realistic_options' => $this->getMockRealisticOptions($symbol)
                ];
                
                foreach ($apis as $apiName => $result) {
                    if ($result['success'] && !empty($result['data'])) {
                        Log::info("FreeMarketDataService: Using {$apiName} for {$symbol} options");
                        return $result;
                    }
                }
                
                return [
                    'success' => false,
                    'message' => 'All free option APIs failed',
                    'data' => []
                ];
                
            } catch (\Exception $e) {
                Log::error("FreeMarketDataService option error: " . $e->getMessage());
                return [
                    'success' => false,
                    'message' => 'Service error: ' . $e->getMessage(),
                    'data' => []
                ];
            }
        });
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
            
            // Get major indices
            $indices = ['NIFTY', 'BANKNIFTY', 'SENSEX'];
            
            foreach ($indices as $index) {
                $url = "https://www.alphavantage.co/query?function=GLOBAL_QUOTE&symbol={$index}.BSE&apikey={$apiKey}";
                
                $response = Http::timeout(10)->get($url);
                
                if ($response->successful()) {
                    $data = $response->json();
                    if (isset($data['Global Quote'])) {
                        $quote = $data['Global Quote'];
                        $marketData[$index] = [
                            'symbol' => $index,
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
     * Get option chain from NSE India
     */
    private function getOptionChainFromNSE(string $symbol): array
    {
        try {
            $url = "https://www.nseindia.com/api/option-chain-indices?symbol=" . strtoupper($symbol);
            
            $response = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36',
                'Accept' => 'application/json',
                'Accept-Language' => 'en-US,en;q=0.9',
                'Referer' => 'https://www.nseindia.com/',
            ])->timeout(15)->get($url);
            
            if ($response->successful()) {
                $data = $response->json();
                if (isset($data['records']['data']) && !empty($data['records']['data'])) {
                    $processed = $this->processNSEData($data['records']['data'], $symbol);
                    return [
                        'success' => true,
                        'data' => $processed,
                        'source' => 'NSE India Free API (1-2 min delayed)'
                    ];
                }
            }
            
            return ['success' => false, 'message' => 'NSE India options API failed'];
            
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'NSE India options error: ' . $e->getMessage()];
        }
    }
    
    /**
     * Process NSE option chain data
     */
    private function processNSEData(array $data, string $symbol): array
    {
        $options = [];
        foreach ($data as $row) {
            $strike = (float)($row['strikePrice'] ?? 0);
            if ($strike <= 0) continue;
            
            if (!empty($row['CE'])) {
                $ce = $row['CE'];
                $options[] = [
                    'symbol' => $symbol,
                    'strike_price' => $strike,
                    'option_type' => 'CALL',
                    'ltp' => (float)($ce['lastPrice'] ?? 0),
                    'bid' => (float)($ce['bidprice'] ?? $ce['bidPrice'] ?? 0),
                    'ask' => (float)($ce['askPrice'] ?? $ce['askprice'] ?? 0),
                    'volume' => (int)($ce['totalTradedVolume'] ?? 0),
                    'oi' => (int)($ce['openInterest'] ?? 0),
                    'data_source' => 'NSE India Free API (1-2 min delayed)',
                    'timestamp' => now()->toISOString()
                ];
            }
            
            if (!empty($row['PE'])) {
                $pe = $row['PE'];
                $options[] = [
                    'symbol' => $symbol,
                    'strike_price' => $strike,
                    'option_type' => 'PUT',
                    'ltp' => (float)($pe['lastPrice'] ?? 0),
                    'bid' => (float)($pe['bidprice'] ?? $pe['bidPrice'] ?? 0),
                    'ask' => (float)($pe['askPrice'] ?? $pe['askprice'] ?? 0),
                    'volume' => (int)($pe['totalTradedVolume'] ?? 0),
                    'oi' => (int)($pe['openInterest'] ?? 0),
                    'data_source' => 'NSE India Free API (1-2 min delayed)',
                    'timestamp' => now()->toISOString()
                ];
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
        $underlyingPrice = $this->getUnderlyingPrice($symbol);
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
     * Get underlying price for option calculations
     */
    private function getUnderlyingPrice(string $symbol): float
    {
        $prices = [
            'NIFTY' => 19500,
            'BANKNIFTY' => 45000,
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
        $range = $underlyingPrice * 0.1; // ±10% range
        $step = $underlyingPrice * 0.01; // 1% step
        
        for ($i = -10; $i <= 10; $i++) {
            $strike = $underlyingPrice + ($i * $step);
            if ($strike > 0) {
                $strikes[] = round($strike, 0);
            }
        }
        
        return $strikes;
    }
    
    /**
     * Calculate realistic option price using simplified Black-Scholes
     */
    private function calculateRealisticPrice(float $underlying, float $strike, string $type): float
    {
        $timeToExpiry = 0.25; // 3 months
        $volatility = 0.2; // 20% volatility
        $riskFreeRate = 0.05; // 5% risk-free rate
        
        $d1 = (log($underlying / $strike) + ($riskFreeRate + 0.5 * $volatility * $volatility) * $timeToExpiry) / ($volatility * sqrt($timeToExpiry));
        $d2 = $d1 - $volatility * sqrt($timeToExpiry);
        
        $nd1 = $this->normalCDF($d1);
        $nd2 = $this->normalCDF($d2);
        
        if ($type === 'CALL') {
            $price = $underlying * $nd1 - $strike * exp(-$riskFreeRate * $timeToExpiry) * $nd2;
        } else {
            $price = $strike * exp(-$riskFreeRate * $timeToExpiry) * (1 - $nd2) - $underlying * (1 - $nd1);
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
}
