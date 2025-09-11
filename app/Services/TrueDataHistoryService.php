<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Exception;

class TrueDataHistoryService
{
    private $baseUrl;
    private $username;
    private $password;
    private $accessToken;

    public function __construct()
    {
        $this->baseUrl = config('services.truedata.history_url', 'https://history.truedata.in');
        $this->username = config('services.truedata.username');
        $this->password = config('services.truedata.password');
    }

    /**
     * Get authentication token
     */
    private function getAccessToken()
    {
        if ($this->accessToken) {
            return $this->accessToken;
        }

        try {
            $response = Http::asForm()->post('https://auth.truedata.in/token', [
                'username' => $this->username,
                'password' => $this->password,
                'grant_type' => 'password'
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $this->accessToken = $data['access_token'] ?? null;
                return $this->accessToken;
            }
        } catch (Exception $e) {
            Log::error('TrueData Auth Error: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Get last trading day's closing prices for major stocks
     */
    public function getLastClosingPrices()
    {
        $token = $this->getAccessToken();
        if (!$token) {
            Log::warning('No access token available for historical data');
            return [];
        }

        $symbols = [
            'NIFTY 50', 'NIFTY BANK', 'RELIANCE', 'TCS', 'HDFCBANK', 
            'ICICIBANK', 'SBIN', 'INFY', 'WIPRO', 'BHARTIARTL', 
            'ITC', 'LT', 'MARUTI', 'ASIANPAINT'
        ];

        $historicalData = [];

        foreach ($symbols as $symbol) {
            try {
                $data = $this->getLastNTicks($symbol, $token);
                if ($data) {
                    $historicalData[$symbol] = $data;
                }
            } catch (Exception $e) {
                Log::warning("Failed to fetch data for {$symbol}: " . $e->getMessage());
            }
        }

        return $historicalData;
    }

    /**
     * Get last N ticks for a symbol
     */
    private function getLastNTicks($symbol, $token, $nticks = 1)
    {
        try {
            $response = Http::withToken($token)->get($this->baseUrl . '/getlastnticks', [
                'symbol' => $symbol,
                'bidask' => 1,
                'response' => 'json',
                'nticks' => $nticks,
                'interval' => 'tick'
            ]);

            if ($response->successful()) {
                $data = $response->json();
                if (!empty($data) && is_array($data) && isset($data[0])) {
                    $tick = $data[0]; // Get the latest tick
                    return $this->formatTickData($symbol, $tick);
                } else {
                    Log::warning("No tick data available for {$symbol}");
                }
            } else {
                Log::warning("API request failed for {$symbol}: " . $response->status());
            }
        } catch (Exception $e) {
            Log::error("Error fetching last ticks for {$symbol}: " . $e->getMessage());
        }

        return null;
    }

    /**
     * Format tick data to our standard format
     */
    private function formatTickData($symbol, $tick)
    {
        return [
            'symbol' => $symbol,
            'ltp' => $tick['ltp'] ?? 0,
            'change' => $tick['change'] ?? 0,
            'change_percent' => $tick['change_percent'] ?? 0,
            'volume' => $tick['volume'] ?? 0,
            'turnover' => $tick['turnover'] ?? 0,
            'high' => $tick['high'] ?? 0,
            'low' => $tick['low'] ?? 0,
            'open' => $tick['open'] ?? 0,
            'prev_close' => $tick['prev_close'] ?? 0,
            'bid' => $tick['bid'] ?? 0,
            'ask' => $tick['ask'] ?? 0,
            'timestamp' => $tick['timestamp'] ?? now()->toISOString()
        ];
    }

    /**
     * Get recent (last 1-5 minutes) option quote for a specific option symbol
     */
    public function getRecentOptionQuote(string $optionSymbol, int $maxMinutes = 5): ?array
    {
        $token = $this->getAccessToken();
        if (!$token) {
            Log::warning('TrueDataHistoryService: No auth token to fetch recent option quotes');
            return null;
        }

        try {
            // Fetch up to 200 recent ticks and pick the freshest within N minutes
            $response = Http::withToken($token)->get($this->baseUrl . '/getlastnticks', [
                'symbol' => $optionSymbol,
                'bidask' => 1,
                'response' => 'json',
                'nticks' => 200,
                'interval' => 'tick'
            ]);

            if (!$response->successful()) {
                Log::warning("TrueDataHistoryService: getlastnticks failed for {$optionSymbol} with status " . $response->status());
                return null;
            }

            $data = $response->json();
            if (empty($data) || !is_array($data)) {
                return null;
            }

            $now = now();
            $selected = null;
            foreach ($data as $row) {
                // Row may be associative (with keys) or indexed array
                $timestamp = $row['timestamp'] ?? $row['time'] ?? ($row[0] ?? null);
                if (!$timestamp) { continue; }
                try {
                    $ts = \Carbon\Carbon::parse($timestamp);
                } catch (\Exception $e) {
                    continue;
                }

                if ($ts->diffInMinutes($now) <= $maxMinutes) {
                    $ltp = isset($row['ltp']) ? (float)$row['ltp'] : (isset($row[1]) ? (float)$row[1] : 0);
                    $bid = isset($row['bid']) ? (float)$row['bid'] : (isset($row[4]) ? (float)$row[4] : 0);
                    $ask = isset($row['ask']) ? (float)$row['ask'] : (isset($row[6]) ? (float)$row[6] : 0);
                    $selected = [
                        'ltp' => $ltp,
                        'bid' => $bid,
                        'ask' => $ask,
                        'timestamp' => $ts->toISOString()
                    ];
                    // break on first freshest tick
                    break;
                }
            }

            return $selected;
        } catch (\Exception $e) {
            Log::error('TrueDataHistoryService: Error in getRecentOptionQuote - ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get top gainers from last trading day
     */
    public function getTopGainers()
    {
        $token = $this->getAccessToken();
        if (!$token) {
            return [];
        }

        try {
            $response = Http::withToken($token)->get($this->baseUrl . '/gettopngainers', [
                'segment' => 'CASH',
                'response' => 'json',
                'topn' => 10
            ]);

            if ($response->successful()) {
                return $response->json();
            }
        } catch (Exception $e) {
            Log::error('Error fetching top gainers: ' . $e->getMessage());
        }

        return [];
    }

    /**
     * Get top losers from last trading day
     */
    public function getTopLosers()
    {
        $token = $this->getAccessToken();
        if (!$token) {
            return [];
        }

        try {
            $response = Http::withToken($token)->get($this->baseUrl . '/gettopnlosers', [
                'segment' => 'CASH',
                'response' => 'json',
                'topn' => 10
            ]);

            if ($response->successful()) {
                return $response->json();
            }
        } catch (Exception $e) {
            Log::error('Error fetching top losers: ' . $e->getMessage());
        }

        return [];
    }
}
