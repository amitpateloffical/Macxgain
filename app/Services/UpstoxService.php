<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class UpstoxService
{
    private $apiKey;
    private $baseUrl;

    public function __construct()
    {
        $this->apiKey = config('services.upstox.api_key');
        $this->baseUrl = config('services.upstox.base_url');
    }

    /**
     * Get market quotes for multiple instruments
     */
    public function getMarketQuotes($instruments = [])
    {
        try {
            // Default popular instruments if none provided
            if (empty($instruments)) {
                $instruments = [
                    'NSE_EQ|INE002A01018', // Reliance
                    'NSE_EQ|INE009A01021', // Infosys
                    'NSE_EQ|INE467B01029', // TCS
                    'NSE_EQ|INE040A01034', // HDFC Bank
                    'NSE_EQ|INE030A01027', // ICICI Bank
                    'NSE_EQ|INE256A01028', // Wipro
                    'NSE_EQ|INE018A01030', // SBI
                    'NSE_EQ|INE062A01020', // Bharti Airtel
                    'NSE_EQ|INE758T01015', // Tech Mahindra
                    'NSE_EQ|INE397D01024', // Asian Paints
                ];
            }

            $instrumentString = implode(',', $instruments);
            
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Accept' => 'application/json',
            ])->get($this->baseUrl . '/market-quote/quotes', [
                'instrument_key' => $instrumentString
            ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json()
                ];
            }

            return [
                'success' => false,
                'error' => 'Failed to fetch market quotes',
                'details' => $response->json()
            ];

        } catch (\Exception $e) {
            Log::error('Upstox API Error: ' . $e->getMessage());
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
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Accept' => 'application/json',
            ])->get($this->baseUrl . '/market-quote/market-status/NSE');

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json()
                ];
            }

            return [
                'success' => false,
                'error' => 'Failed to fetch market status'
            ];

        } catch (\Exception $e) {
            Log::error('Upstox Market Status Error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get historical data for an instrument
     */
    public function getHistoricalData($instrumentKey, $interval = '1day', $toDate = null, $fromDate = null)
    {
        try {
            $toDate = $toDate ?? now()->format('Y-m-d');
            $fromDate = $fromDate ?? now()->subDays(30)->format('Y-m-d');

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Accept' => 'application/json',
            ])->get($this->baseUrl . '/historical-candle/' . $instrumentKey . '/' . $interval . '/' . $toDate . '/' . $fromDate);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json()
                ];
            }

            return [
                'success' => false,
                'error' => 'Failed to fetch historical data'
            ];

        } catch (\Exception $e) {
            Log::error('Upstox Historical Data Error: ' . $e->getMessage());
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
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Accept' => 'application/json',
            ])->get($this->baseUrl . '/search/instruments', [
                'query' => $query
            ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json()
                ];
            }

            return [
                'success' => false,
                'error' => 'Failed to search instruments'
            ];

        } catch (\Exception $e) {
            Log::error('Upstox Search Error: ' . $e->getMessage());
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
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Accept' => 'application/json',
            ])->get($this->baseUrl . '/market-quote/market-data/NSE');

            if ($response->successful()) {
                $data = $response->json();
                // Filter for gainers
                if (isset($data['data'])) {
                    $gainers = collect($data['data'])->filter(function ($item) {
                        return isset($item['net_change']) && $item['net_change'] > 0;
                    })->sortByDesc('net_change')->take(10);

                    return [
                        'success' => true,
                        'data' => $gainers->values()
                    ];
                }
            }

            return [
                'success' => false,
                'error' => 'Failed to fetch top gainers'
            ];

        } catch (\Exception $e) {
            Log::error('Upstox Top Gainers Error: ' . $e->getMessage());
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
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Accept' => 'application/json',
            ])->get($this->baseUrl . '/market-quote/market-data/NSE');

            if ($response->successful()) {
                $data = $response->json();
                // Filter for losers
                if (isset($data['data'])) {
                    $losers = collect($data['data'])->filter(function ($item) {
                        return isset($item['net_change']) && $item['net_change'] < 0;
                    })->sortBy('net_change')->take(10);

                    return [
                        'success' => true,
                        'data' => $losers->values()
                    ];
                }
            }

            return [
                'success' => false,
                'error' => 'Failed to fetch top losers'
            ];

        } catch (\Exception $e) {
            Log::error('Upstox Top Losers Error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get market indices (Nifty, Sensex, etc.)
     */
    public function getMarketIndices()
    {
        try {
            $indices = [
                'NSE_INDEX|Nifty 50',
                'NSE_INDEX|Nifty Bank',
                'NSE_INDEX|Nifty IT',
                'BSE_INDEX|SENSEX',
                'NSE_INDEX|Nifty Auto',
                'NSE_INDEX|Nifty Pharma'
            ];

            $instrumentString = implode(',', $indices);
            
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Accept' => 'application/json',
            ])->get($this->baseUrl . '/market-quote/quotes', [
                'instrument_key' => $instrumentString
            ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json()
                ];
            }

            return [
                'success' => false,
                'error' => 'Failed to fetch market indices'
            ];

        } catch (\Exception $e) {
            Log::error('Upstox Market Indices Error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get cached market data (to avoid rate limits)
     */
    public function getCachedMarketData()
    {
        return Cache::remember('upstox_market_data', 60, function () {
            $marketQuotes = $this->getMarketQuotes();
            $marketStatus = $this->getMarketStatus();
            $marketIndices = $this->getMarketIndices();

            return [
                'quotes' => $marketQuotes,
                'status' => $marketStatus,
                'indices' => $marketIndices,
                'timestamp' => now()->toISOString()
            ];
        });
    }
}
