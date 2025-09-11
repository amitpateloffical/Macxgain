<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NSEOptionChainService
{
    /**
     * Fetch real option chain from NSE free API and cache for 120 seconds
     * @param string $symbol 'NIFTY' | 'BANKNIFTY'
     * @return array [success=>bool, data=>array, message?]
     */
    public function getOptionChain(string $symbol): array
    {
        try {
            $mapped = $this->mapSymbol($symbol);
            if (!$mapped) {
                return ['success' => false, 'message' => 'Unsupported symbol for NSE'];
            }

            $cacheKey = "nse_option_chain_{$mapped}";
            return Cache::remember($cacheKey, 120, function () use ($mapped) {
                $url = $this->buildUrl($mapped);
                $headers = $this->defaultHeaders();

                // Warm-up call to set cookies (homepage)
                try {
                    Http::withHeaders($headers)->timeout(8)->get('https://www.nseindia.com');
                } catch (\Throwable $e) {
                    // ignore warm-up errors
                }

                $resp = Http::withHeaders($headers)->timeout(10)->get($url);
                if (!$resp->successful()) {
                    return ['success' => false, 'message' => 'NSE API request failed: ' . $resp->status()];
                }

                $json = $resp->json();
                $records = $json['records']['data'] ?? [];
                if (empty($records)) {
                    return ['success' => false, 'message' => 'NSE API returned no data'];
                }

                $processed = $this->processRecords($records, $mapped, $json['records']['timestamp'] ?? null);

                return [
                    'success' => true,
                    'data' => $processed,
                    'symbol' => $mapped,
                    'data_source' => 'NSE Free API (1-2 min delayed)'
                ];
            });
        } catch (\Throwable $e) {
            Log::warning('NSEOptionChainService error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'NSE service error'];
        }
    }

    private function mapSymbol(string $symbol): ?string
    {
        $s = strtoupper($symbol);
        if ($s === 'NIFTY' || $s === 'NIFTY 50') return 'NIFTY';
        if ($s === 'BANKNIFTY' || $s === 'NIFTY BANK' || $s === 'BANK NIFTY') return 'BANKNIFTY';
        return null;
    }

    private function buildUrl(string $mapped): string
    {
        // NSE free API endpoints
        if ($mapped === 'NIFTY') {
            return 'https://www.nseindia.com/api/option-chain-indices?symbol=NIFTY';
        }
        if ($mapped === 'BANKNIFTY') {
            return 'https://www.nseindia.com/api/option-chain-indices?symbol=BANKNIFTY';
        }
        return '';
    }

    private function defaultHeaders(): array
    {
        return [
            'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.0.0 Safari/537.36',
            'Accept' => 'application/json, text/plain, */*',
            'Accept-Language' => 'en-US,en;q=0.9',
            'Referer' => 'https://www.nseindia.com/option-chain',
            'Origin' => 'https://www.nseindia.com',
            'Connection' => 'keep-alive',
        ];
    }

    private function processRecords(array $records, string $symbol, ?string $timestamp): array
    {
        $result = [];
        foreach ($records as $row) {
            $strike = (float)($row['strikePrice'] ?? 0);
            if ($strike <= 0) continue;

            if (!empty($row['CE'])) {
                $ce = $row['CE'];
                $result[] = $this->mapOption($symbol, $strike, 'CALL', $ce, $timestamp);
            }
            if (!empty($row['PE'])) {
                $pe = $row['PE'];
                $result[] = $this->mapOption($symbol, $strike, 'PUT', $pe, $timestamp);
            }
        }
        return array_values(array_filter($result, function ($o) {
            return ($o['ltp'] ?? 0) > 0 || ($o['bid'] ?? 0) > 0 || ($o['ask'] ?? 0) > 0;
        }));
    }

    private function mapOption(string $symbol, float $strike, string $type, array $src, ?string $ts): array
    {
        return [
            'symbol' => $symbol,
            'option_type' => $type,
            'strike_price' => $strike,
            'ltp' => (float)($src['lastPrice'] ?? 0),
            'bid' => (float)($src['bidprice'] ?? $src['bidPrice'] ?? 0),
            'ask' => (float)($src['askPrice'] ?? $src['askprice'] ?? 0),
            'volume' => (int)($src['totalTradedVolume'] ?? 0),
            'oi' => (int)($src['openInterest'] ?? 0),
            'timestamp' => $ts,
            'data_source' => 'NSE Free API (1-2 min delayed)'
        ];
    }
}


