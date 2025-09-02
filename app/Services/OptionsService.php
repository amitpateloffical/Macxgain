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

            $url = "{$this->baseUrl}/getOptionChain";
            $params = [
                'user' => $this->username,
                'password' => $this->password,
                'symbol' => $symbol,
                'expiry' => $expiry,
                'response' => 'json'
            ];

            $response = Http::timeout(30)->get($url, $params);

            if ($response->successful()) {
                $data = $response->json();
                
                // Cache for 5 minutes
                $cacheKey = "options_chain_{$symbol}_{$expiry}";
                Cache::put($cacheKey, $data, 300);
                
                return [
                    'success' => true,
                    'data' => $data,
                    'symbol' => $symbol,
                    'expiry' => $expiry
                ];
            } else {
                Log::error("Options API failed: " . $response->body());
                return [
                    'success' => false,
                    'error' => 'Failed to fetch options data',
                    'status_code' => $response->status()
                ];
            }
        } catch (\Exception $e) {
            Log::error("Options Service Error: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
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
