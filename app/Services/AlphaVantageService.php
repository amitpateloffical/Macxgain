<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class AlphaVantageService
{
    private $apiKey;
    private $baseUrl;

    public function __construct()
    {
        $this->apiKey = config('services.alphavantage.api_key', 'II73DAUC1KB906VM');
        $this->baseUrl = 'https://www.alphavantage.co/query';
    }

    /**
     * Get stock quote data with proper rate limiting
     */
    public function getQuote($symbol)
    {
        try {
            // Validate symbol format and warn about unsupported markets
            if (strpos($symbol, '.BSE') !== false || strpos($symbol, '.NSE') !== false) {
                return [
                    'error' => 'Indian markets not fully supported',
                    'message' => 'Alpha Vantage has limited support for BSE/NSE symbols. Try US symbols like AAPL, MSFT, GOOGL.',
                    'suggestion' => 'Switch to US Tech tab for supported symbols'
                ];
            }
            
            // Check rate limiting using cache
            $cacheKey = "alphavantage_rate_limit_" . date('Y-m-d');
            $requestCount = Cache::get($cacheKey, 0);
            
            if ($requestCount >= 25) {
                return [
                    'error' => 'Daily API limit reached (25 requests/day)',
                    'message' => 'Please upgrade to Alpha Vantage Premium for unlimited requests',
                    'upgrade_url' => 'https://www.alphavantage.co/premium/'
                ];
            }

            // Cache the quote for 5 minutes to reduce API calls
            $quoteCacheKey = "alphavantage_quote_" . $symbol;
            $cachedQuote = Cache::get($quoteCacheKey);
            
            if ($cachedQuote) {
                return $cachedQuote;
            }

            $response = Http::timeout(30)->get($this->baseUrl, [
                'function' => 'GLOBAL_QUOTE',
                'symbol' => $symbol,
                'apikey' => $this->apiKey
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                // Increment request counter
                Cache::put($cacheKey, $requestCount + 1, now()->endOfDay());
                
                // Check if we got valid data
                if (isset($data['Global Quote']) && !empty($data['Global Quote']['01. symbol'])) {
                    // Cache successful response for 5 minutes
                    Cache::put($quoteCacheKey, $data, 300);
                    return $data;
                }
                
                // Check if we hit rate limit
                if (isset($data['Information']) && strpos($data['Information'], 'rate limit') !== false) {
                    return [
                        'error' => 'API rate limit exceeded',
                        'message' => 'Free plan allows 25 requests/day. Please upgrade to Premium.',
                        'upgrade_url' => 'https://www.alphavantage.co/premium/',
                        'current_limit' => '25 requests/day'
                    ];
                }
                
                return [
                    'error' => 'Invalid symbol or no data available',
                    'message' => 'The symbol "' . $symbol . '" may not be supported or market is closed'
                ];
            }

            return ['error' => 'API request failed', 'message' => 'Unable to fetch quote data'];
        } catch (\Exception $e) {
            Log::error('Alpha Vantage Quote Error: ' . $e->getMessage());
            return ['error' => 'API error', 'message' => $e->getMessage()];
        }
    }


    /**
     * Search for stocks/symbols
     */
    public function searchSymbols($keywords)
    {
        try {
            $cacheKey = 'av_search_' . md5($keywords);
            
            return Cache::remember($cacheKey, 300, function () use ($keywords) {
                $response = Http::timeout(30)->get($this->baseUrl, [
                    'function' => 'SYMBOL_SEARCH',
                    'keywords' => $keywords,
                    'apikey' => $this->apiKey
                ]);

                if ($response->successful()) {
                    return $response->json();
                }

                return ['error' => 'Failed to search symbols'];
            });
        } catch (\Exception $e) {
            Log::error('Alpha Vantage Search Error: ' . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Get intraday data
     */
    public function getIntradayData($symbol, $interval = '5min', $outputsize = 'compact')
    {
        try {
            $cacheKey = 'av_intraday_' . $symbol . '_' . $interval;
            
            return Cache::remember($cacheKey, 60, function () use ($symbol, $interval, $outputsize) {
                $response = Http::timeout(30)->get($this->baseUrl, [
                    'function' => 'TIME_SERIES_INTRADAY',
                    'symbol' => $symbol,
                    'interval' => $interval,
                    'outputsize' => $outputsize,
                    'apikey' => $this->apiKey
                ]);

                if ($response->successful()) {
                    return $response->json();
                }

                return ['error' => 'Failed to fetch intraday data'];
            });
        } catch (\Exception $e) {
            Log::error('Alpha Vantage Intraday Error: ' . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Get daily stock data
     */
    public function getDailyData($symbol, $outputsize = 'compact')
    {
        try {
            $cacheKey = 'av_daily_' . $symbol;
            
            return Cache::remember($cacheKey, 300, function () use ($symbol, $outputsize) {
                $response = Http::timeout(30)->get($this->baseUrl, [
                    'function' => 'TIME_SERIES_DAILY',
                    'symbol' => $symbol,
                    'outputsize' => $outputsize,
                    'apikey' => $this->apiKey
                ]);

                if ($response->successful()) {
                    return $response->json();
                }

                return ['error' => 'Failed to fetch daily data'];
            });
        } catch (\Exception $e) {
            Log::error('Alpha Vantage Daily Error: ' . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Get options data (Premium feature)
     */
    public function getOptionsData($symbol, $contract = null)
    {
        try {
            $params = [
                'function' => 'REALTIME_OPTIONS',
                'symbol' => $symbol,
                'apikey' => $this->apiKey
            ];

            if ($contract) {
                $params['contract'] = $contract;
            }

            $response = Http::timeout(30)->get($this->baseUrl, $params);

            if ($response->successful()) {
                return $response->json();
            }

            return ['error' => 'Failed to fetch options data'];
        } catch (\Exception $e) {
            Log::error('Alpha Vantage Options Error: ' . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Get company overview
     */
    public function getCompanyOverview($symbol)
    {
        try {
            $cacheKey = 'av_overview_' . $symbol;
            
            return Cache::remember($cacheKey, 3600, function () use ($symbol) {
                $response = Http::timeout(30)->get($this->baseUrl, [
                    'function' => 'OVERVIEW',
                    'symbol' => $symbol,
                    'apikey' => $this->apiKey
                ]);

                if ($response->successful()) {
                    return $response->json();
                }

                return ['error' => 'Failed to fetch company overview'];
            });
        } catch (\Exception $e) {
            Log::error('Alpha Vantage Overview Error: ' . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Get top gainers and losers
     */
    public function getTopGainersLosers()
    {
        try {
            $cacheKey = 'av_top_gainers_losers';
            
            return Cache::remember($cacheKey, 300, function () {
                $response = Http::timeout(30)->get($this->baseUrl, [
                    'function' => 'TOP_GAINERS_LOSERS',
                    'apikey' => $this->apiKey
                ]);

                if ($response->successful()) {
                    return $response->json();
                }

                return ['error' => 'Failed to fetch top gainers/losers'];
            });
        } catch (\Exception $e) {
            Log::error('Alpha Vantage Top Gainers/Losers Error: ' . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Get technical indicators - SMA
     */
    public function getSMA($symbol, $interval = 'daily', $timePeriod = 20, $seriesType = 'close')
    {
        try {
            $cacheKey = 'av_sma_' . $symbol . '_' . $interval . '_' . $timePeriod;
            
            return Cache::remember($cacheKey, 600, function () use ($symbol, $interval, $timePeriod, $seriesType) {
                $response = Http::timeout(30)->get($this->baseUrl, [
                    'function' => 'SMA',
                    'symbol' => $symbol,
                    'interval' => $interval,
                    'time_period' => $timePeriod,
                    'series_type' => $seriesType,
                    'apikey' => $this->apiKey
                ]);

                if ($response->successful()) {
                    return $response->json();
                }

                return ['error' => 'Failed to fetch SMA data'];
            });
        } catch (\Exception $e) {
            Log::error('Alpha Vantage SMA Error: ' . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Get technical indicators - RSI
     */
    public function getRSI($symbol, $interval = 'daily', $timePeriod = 14, $seriesType = 'close')
    {
        try {
            $cacheKey = 'av_rsi_' . $symbol . '_' . $interval . '_' . $timePeriod;
            
            return Cache::remember($cacheKey, 600, function () use ($symbol, $interval, $timePeriod, $seriesType) {
                $response = Http::timeout(30)->get($this->baseUrl, [
                    'function' => 'RSI',
                    'symbol' => $symbol,
                    'interval' => $interval,
                    'time_period' => $timePeriod,
                    'series_type' => $seriesType,
                    'apikey' => $this->apiKey
                ]);

                if ($response->successful()) {
                    return $response->json();
                }

                return ['error' => 'Failed to fetch RSI data'];
            });
        } catch (\Exception $e) {
            Log::error('Alpha Vantage RSI Error: ' . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Get market status
     */
    public function getMarketStatus()
    {
        try {
            $cacheKey = 'av_market_status';
            
            return Cache::remember($cacheKey, 60, function () {
                $response = Http::timeout(30)->get($this->baseUrl, [
                    'function' => 'MARKET_STATUS',
                    'apikey' => $this->apiKey
                ]);

                if ($response->successful()) {
                    return $response->json();
                }

                return ['error' => 'Failed to fetch market status'];
            });
        } catch (\Exception $e) {
            Log::error('Alpha Vantage Market Status Error: ' . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Get popular stocks by category
     */
    public function getPopularStocks()
    {
        return $this->getAllStocksByCategory();
    }

    /**
     * Get all stocks organized by category
     */
    public function getAllStocksByCategory()
    {
        $stocks = [];
        
        // Indian Market - Nifty 50 & Top Stocks
        $indianStocks = [
            ['symbol' => 'RELIANCE.BSE', 'name' => 'Reliance Industries Ltd', 'category' => 'Indian Markets'],
            ['symbol' => 'TCS.BSE', 'name' => 'Tata Consultancy Services', 'category' => 'Indian Markets'],
            ['symbol' => 'HDFCBANK.BSE', 'name' => 'HDFC Bank Ltd', 'category' => 'Indian Markets'],
            ['symbol' => 'INFY.BSE', 'name' => 'Infosys Ltd', 'category' => 'Indian Markets'],
            ['symbol' => 'HINDUNILVR.BSE', 'name' => 'Hindustan Unilever Ltd', 'category' => 'Indian Markets'],
            ['symbol' => 'ICICIBANK.BSE', 'name' => 'ICICI Bank Ltd', 'category' => 'Indian Markets'],
            ['symbol' => 'HDFC.BSE', 'name' => 'Housing Development Finance Corporation Ltd', 'category' => 'Indian Markets'],
            ['symbol' => 'SBIN.BSE', 'name' => 'State Bank of India', 'category' => 'Indian Markets'],
            ['symbol' => 'BHARTIARTL.BSE', 'name' => 'Bharti Airtel Ltd', 'category' => 'Indian Markets'],
            ['symbol' => 'KOTAKBANK.BSE', 'name' => 'Kotak Mahindra Bank Ltd', 'category' => 'Indian Markets'],
            ['symbol' => 'LT.BSE', 'name' => 'Larsen & Toubro Ltd', 'category' => 'Indian Markets'],
            ['symbol' => 'ASIANPAINT.BSE', 'name' => 'Asian Paints Ltd', 'category' => 'Indian Markets'],
            ['symbol' => 'ITC.BSE', 'name' => 'ITC Ltd', 'category' => 'Indian Markets'],
            ['symbol' => 'AXISBANK.BSE', 'name' => 'Axis Bank Ltd', 'category' => 'Indian Markets'],
            ['symbol' => 'MARUTI.BSE', 'name' => 'Maruti Suzuki India Ltd', 'category' => 'Indian Markets'],
            ['symbol' => 'SUNPHARMA.BSE', 'name' => 'Sun Pharmaceutical Industries Ltd', 'category' => 'Indian Markets'],
            ['symbol' => 'TITAN.BSE', 'name' => 'Titan Company Ltd', 'category' => 'Indian Markets'],
            ['symbol' => 'TECHM.BSE', 'name' => 'Tech Mahindra Ltd', 'category' => 'Indian Markets'],
            ['symbol' => 'ULTRACEMCO.BSE', 'name' => 'UltraTech Cement Ltd', 'category' => 'Indian Markets'],
            ['symbol' => 'WIPRO.BSE', 'name' => 'Wipro Ltd', 'category' => 'Indian Markets'],
            ['symbol' => 'NESTLEIND.BSE', 'name' => 'Nestle India Ltd', 'category' => 'Indian Markets'],
            ['symbol' => 'BAJFINANCE.BSE', 'name' => 'Bajaj Finance Ltd', 'category' => 'Indian Markets'],
            ['symbol' => 'POWERGRID.BSE', 'name' => 'Power Grid Corporation of India Ltd', 'category' => 'Indian Markets'],
            ['symbol' => 'M&M.BSE', 'name' => 'Mahindra & Mahindra Ltd', 'category' => 'Indian Markets'],
            ['symbol' => 'HCLTECH.BSE', 'name' => 'HCL Technologies Ltd', 'category' => 'Indian Markets']
        ];

        // US Technology Giants
        $usTechStocks = [
            ['symbol' => 'AAPL', 'name' => 'Apple Inc.', 'category' => 'US Tech Giants'],
            ['symbol' => 'MSFT', 'name' => 'Microsoft Corporation', 'category' => 'US Tech Giants'],
            ['symbol' => 'GOOGL', 'name' => 'Alphabet Inc.', 'category' => 'US Tech Giants'],
            ['symbol' => 'GOOG', 'name' => 'Alphabet Inc. (Class C)', 'category' => 'US Tech Giants'],
            ['symbol' => 'AMZN', 'name' => 'Amazon.com Inc.', 'category' => 'US Tech Giants'],
            ['symbol' => 'TSLA', 'name' => 'Tesla Inc.', 'category' => 'US Tech Giants'],
            ['symbol' => 'META', 'name' => 'Meta Platforms Inc.', 'category' => 'US Tech Giants'],
            ['symbol' => 'NVDA', 'name' => 'NVIDIA Corporation', 'category' => 'US Tech Giants'],
            ['symbol' => 'NFLX', 'name' => 'Netflix Inc.', 'category' => 'US Tech Giants'],
            ['symbol' => 'ADBE', 'name' => 'Adobe Inc.', 'category' => 'US Tech Giants'],
            ['symbol' => 'CRM', 'name' => 'Salesforce Inc.', 'category' => 'US Tech Giants'],
            ['symbol' => 'ORCL', 'name' => 'Oracle Corporation', 'category' => 'US Tech Giants'],
            ['symbol' => 'NOW', 'name' => 'ServiceNow Inc.', 'category' => 'US Tech Giants'],
            ['symbol' => 'INTC', 'name' => 'Intel Corporation', 'category' => 'US Tech Giants'],
            ['symbol' => 'AMD', 'name' => 'Advanced Micro Devices Inc.', 'category' => 'US Tech Giants'],
            ['symbol' => 'UBER', 'name' => 'Uber Technologies Inc.', 'category' => 'US Tech Giants'],
            ['symbol' => 'SPOT', 'name' => 'Spotify Technology S.A.', 'category' => 'US Tech Giants'],
            ['symbol' => 'ZOOM', 'name' => 'Zoom Video Communications Inc.', 'category' => 'US Tech Giants'],
            ['symbol' => 'SNAP', 'name' => 'Snap Inc.', 'category' => 'US Tech Giants']
        ];

        return array_merge($indianStocks, $usTechStocks, $this->getAdditionalStocks());
    }

    /**
     * Get additional stock categories
     */
    private function getAdditionalStocks()
    {
        return [
            // US Financial Sector
            ['symbol' => 'JPM', 'name' => 'JPMorgan Chase & Co.', 'category' => 'US Financial'],
            ['symbol' => 'BAC', 'name' => 'Bank of America Corp.', 'category' => 'US Financial'],
            ['symbol' => 'WFC', 'name' => 'Wells Fargo & Co.', 'category' => 'US Financial'],
            ['symbol' => 'GS', 'name' => 'Goldman Sachs Group Inc.', 'category' => 'US Financial'],
            ['symbol' => 'MS', 'name' => 'Morgan Stanley', 'category' => 'US Financial'],
            ['symbol' => 'C', 'name' => 'Citigroup Inc.', 'category' => 'US Financial'],
            ['symbol' => 'V', 'name' => 'Visa Inc.', 'category' => 'US Financial'],
            ['symbol' => 'MA', 'name' => 'Mastercard Inc.', 'category' => 'US Financial'],
            ['symbol' => 'AXP', 'name' => 'American Express Co.', 'category' => 'US Financial'],
            ['symbol' => 'BRK.A', 'name' => 'Berkshire Hathaway Inc.', 'category' => 'US Financial'],
            ['symbol' => 'BRK.B', 'name' => 'Berkshire Hathaway Inc. (Class B)', 'category' => 'US Financial'],
            ['symbol' => 'PYPL', 'name' => 'PayPal Holdings Inc.', 'category' => 'US Financial'],
            ['symbol' => 'SQ', 'name' => 'Block Inc.', 'category' => 'US Financial'],
            ['symbol' => 'COIN', 'name' => 'Coinbase Global Inc.', 'category' => 'US Financial'],

            // US Healthcare & Pharmaceuticals
            ['symbol' => 'JNJ', 'name' => 'Johnson & Johnson', 'category' => 'US Healthcare'],
            ['symbol' => 'UNH', 'name' => 'UnitedHealth Group Inc.', 'category' => 'US Healthcare'],
            ['symbol' => 'PFE', 'name' => 'Pfizer Inc.', 'category' => 'US Healthcare'],
            ['symbol' => 'ABBV', 'name' => 'AbbVie Inc.', 'category' => 'US Healthcare'],
            ['symbol' => 'TMO', 'name' => 'Thermo Fisher Scientific Inc.', 'category' => 'US Healthcare'],
            ['symbol' => 'ABT', 'name' => 'Abbott Laboratories', 'category' => 'US Healthcare'],
            ['symbol' => 'LLY', 'name' => 'Eli Lilly and Co.', 'category' => 'US Healthcare'],
            ['symbol' => 'MDT', 'name' => 'Medtronic Plc', 'category' => 'US Healthcare'],
            ['symbol' => 'BMY', 'name' => 'Bristol-Myers Squibb Co.', 'category' => 'US Healthcare'],
            ['symbol' => 'CVS', 'name' => 'CVS Health Corporation', 'category' => 'US Healthcare'],
            ['symbol' => 'MRK', 'name' => 'Merck & Co. Inc.', 'category' => 'US Healthcare'],

            // US Consumer Goods & Retail
            ['symbol' => 'HD', 'name' => 'Home Depot Inc.', 'category' => 'US Consumer'],
            ['symbol' => 'WMT', 'name' => 'Walmart Inc.', 'category' => 'US Consumer'],
            ['symbol' => 'PG', 'name' => 'Procter & Gamble Co.', 'category' => 'US Consumer'],
            ['symbol' => 'KO', 'name' => 'Coca-Cola Co.', 'category' => 'US Consumer'],
            ['symbol' => 'PEP', 'name' => 'PepsiCo Inc.', 'category' => 'US Consumer'],
            ['symbol' => 'NKE', 'name' => 'Nike Inc.', 'category' => 'US Consumer'],
            ['symbol' => 'MCD', 'name' => 'McDonald\'s Corporation', 'category' => 'US Consumer'],
            ['symbol' => 'SBUX', 'name' => 'Starbucks Corporation', 'category' => 'US Consumer'],
            ['symbol' => 'DIS', 'name' => 'Walt Disney Co.', 'category' => 'US Consumer'],
            ['symbol' => 'LOW', 'name' => 'Lowe\'s Companies Inc.', 'category' => 'US Consumer'],
            ['symbol' => 'TGT', 'name' => 'Target Corporation', 'category' => 'US Consumer'],
            ['symbol' => 'COST', 'name' => 'Costco Wholesale Corporation', 'category' => 'US Consumer'],

            // US Energy & Industrials
            ['symbol' => 'XOM', 'name' => 'Exxon Mobil Corporation', 'category' => 'US Energy'],
            ['symbol' => 'CVX', 'name' => 'Chevron Corporation', 'category' => 'US Energy'],
            ['symbol' => 'COP', 'name' => 'ConocoPhillips', 'category' => 'US Energy'],
            ['symbol' => 'SLB', 'name' => 'Schlumberger NV', 'category' => 'US Energy'],
            ['symbol' => 'EOG', 'name' => 'EOG Resources Inc.', 'category' => 'US Energy'],
            ['symbol' => 'BA', 'name' => 'Boeing Co.', 'category' => 'US Industrial'],
            ['symbol' => 'CAT', 'name' => 'Caterpillar Inc.', 'category' => 'US Industrial'],
            ['symbol' => 'GE', 'name' => 'General Electric Co.', 'category' => 'US Industrial'],
            ['symbol' => 'MMM', 'name' => '3M Co.', 'category' => 'US Industrial'],
            ['symbol' => 'HON', 'name' => 'Honeywell International Inc.', 'category' => 'US Industrial'],

            // ETFs and Market Indices
            ['symbol' => 'SPY', 'name' => 'SPDR S&P 500 ETF Trust', 'category' => 'US ETFs'],
            ['symbol' => 'QQQ', 'name' => 'Invesco QQQ Trust', 'category' => 'US ETFs'],
            ['symbol' => 'IWM', 'name' => 'iShares Russell 2000 ETF', 'category' => 'US ETFs'],
            ['symbol' => 'VTI', 'name' => 'Vanguard Total Stock Market ETF', 'category' => 'US ETFs'],
            ['symbol' => 'VOO', 'name' => 'Vanguard S&P 500 ETF', 'category' => 'US ETFs'],
            ['symbol' => 'DIA', 'name' => 'SPDR Dow Jones Industrial Average ETF', 'category' => 'US ETFs'],
            ['symbol' => 'XLF', 'name' => 'Financial Select Sector SPDR Fund', 'category' => 'US ETFs'],
            ['symbol' => 'XLK', 'name' => 'Technology Select Sector SPDR Fund', 'category' => 'US ETFs'],
            ['symbol' => 'XLE', 'name' => 'Energy Select Sector SPDR Fund', 'category' => 'US ETFs'],
            ['symbol' => 'XLV', 'name' => 'Health Care Select Sector SPDR Fund', 'category' => 'US ETFs']
        ];
    }

    /**
     * Get stocks by specific category
     */
    public function getStocksByCategory($category = null)
    {
        $allStocks = $this->getAllStocksByCategory();
        
        if ($category) {
            return array_filter($allStocks, function($stock) use ($category) {
                return $stock['category'] === $category;
            });
        }
        
        return $allStocks;
    }

    /**
     * Get available categories
     */
    public function getStockCategories()
    {
        $allStocks = $this->getAllStocksByCategory();
        $categories = array_unique(array_column($allStocks, 'category'));
        return array_values($categories);
    }
}
