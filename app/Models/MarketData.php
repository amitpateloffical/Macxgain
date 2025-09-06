<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class MarketData extends Model
{
    protected $fillable = [
        'symbol',
        'ltp',
        'change',
        'change_percent',
        'high',
        'low',
        'open',
        'prev_close',
        'volume',
        'data_timestamp',
        'data_source',
        'raw_data',
        'is_live',
        'market_status'
    ];

    protected $casts = [
        'ltp' => 'decimal:4',
        'change' => 'decimal:4',
        'change_percent' => 'decimal:4',
        'high' => 'decimal:4',
        'low' => 'decimal:4',
        'open' => 'decimal:4',
        'prev_close' => 'decimal:4',
        'volume' => 'decimal:2',
        'data_timestamp' => 'datetime',
        'is_live' => 'boolean',
    ];

    /**
     * Encrypt raw data before storing
     */
    public function setRawDataAttribute($value)
    {
        if ($value !== null) {
            $this->attributes['raw_data'] = Crypt::encryptString(json_encode($value));
        }
    }

    /**
     * Decrypt raw data when retrieving
     */
    public function getRawDataAttribute($value)
    {
        if ($value !== null) {
            try {
                return json_decode(Crypt::decryptString($value), true);
            } catch (\Exception $e) {
                return null;
            }
        }
        return null;
    }

    /**
     * Store market data from array format
     */
    public static function storeMarketData(array $marketData, bool $isLive = true, string $marketStatus = 'OPEN')
    {
        $storedCount = 0;
        
        foreach ($marketData as $symbol => $data) {
            try {
                // Prepare additional data for encryption
                $rawData = [
                    'original_timestamp' => $data['timestamp'] ?? null,
                    'data_source_details' => $data['data_source'] ?? null,
                    'additional_metadata' => [
                        'processed_at' => now()->toISOString(),
                        'data_version' => '1.0'
                    ]
                ];

                // Update or create market data record
                self::updateOrCreate(
                    ['symbol' => $symbol],
                    [
                        'ltp' => $data['ltp'] ?? 0,
                        'change' => $data['change'] ?? 0,
                        'change_percent' => $data['change_percent'] ?? 0,
                        'high' => $data['high'] ?? 0,
                        'low' => $data['low'] ?? 0,
                        'open' => $data['open'] ?? 0,
                        'prev_close' => $data['prev_close'] ?? 0,
                        'volume' => $data['volume'] ?? 0,
                        'data_timestamp' => isset($data['timestamp']) ? Carbon::parse($data['timestamp']) : now(),
                        'data_source' => $data['data_source'] ?? 'Unknown',
                        'raw_data' => $rawData,
                        'is_live' => $isLive,
                        'market_status' => $marketStatus
                    ]
                );
                
                $storedCount++;
            } catch (\Exception $e) {
                \Log::error("Failed to store market data for symbol {$symbol}: " . $e->getMessage());
            }
        }

        // Clear cache after storing new data
        self::clearCache();
        
        return $storedCount;
    }

    /**
     * Get all market data with caching
     */
    public static function getAllMarketData(bool $useCache = true)
    {
        $cacheKey = 'market_data_all';
        
        if ($useCache && Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $data = self::orderBy('symbol')->get()->mapWithKeys(function ($item) {
            return [
                $item->symbol => [
                    'symbol' => $item->symbol,
                    'ltp' => (float) $item->ltp,
                    'change' => (float) $item->change,
                    'change_percent' => (float) $item->change_percent,
                    'high' => (float) $item->high,
                    'low' => (float) $item->low,
                    'open' => (float) $item->open,
                    'prev_close' => (float) $item->prev_close,
                    'volume' => (float) $item->volume,
                    'timestamp' => $item->data_timestamp->toISOString(),
                    'data_source' => $item->data_source,
                    'is_live' => $item->is_live,
                    'market_status' => $item->market_status,
                    'raw_data' => $item->raw_data
                ]
            ];
        })->toArray();

        // Cache for 30 seconds for live data, 24 hours for historical
        $cacheTime = self::where('is_live', true)->exists() ? 30 : 86400;
        Cache::put($cacheKey, $data, $cacheTime);

        return $data;
    }

    /**
     * Get market data for specific symbols
     */
    public static function getMarketDataForSymbols(array $symbols, bool $useCache = true)
    {
        $cacheKey = 'market_data_symbols_' . md5(implode(',', $symbols));
        
        if ($useCache && Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $data = self::whereIn('symbol', $symbols)
            ->orderBy('symbol')
            ->get()
            ->mapWithKeys(function ($item) {
                return [
                    $item->symbol => [
                        'symbol' => $item->symbol,
                        'ltp' => (float) $item->ltp,
                        'change' => (float) $item->change,
                        'change_percent' => (float) $item->change_percent,
                        'high' => (float) $item->high,
                        'low' => (float) $item->low,
                        'open' => (float) $item->open,
                        'prev_close' => (float) $item->prev_close,
                        'volume' => (float) $item->volume,
                        'timestamp' => $item->data_timestamp->toISOString(),
                        'data_source' => $item->data_source,
                        'is_live' => $item->is_live,
                        'market_status' => $item->market_status,
                        'raw_data' => $item->raw_data
                    ]
                ];
            })->toArray();

        // Cache for 30 seconds for live data, 24 hours for historical
        $cacheTime = self::whereIn('symbol', $symbols)->where('is_live', true)->exists() ? 30 : 86400;
        Cache::put($cacheKey, $data, $cacheTime);

        return $data;
    }

    /**
     * Get latest market data timestamp
     */
    public static function getLatestDataTimestamp()
    {
        $timestamp = self::max('data_timestamp');
        return $timestamp ? Carbon::parse($timestamp) : null;
    }

    /**
     * Check if data is fresh (within last 5 minutes for live data)
     */
    public static function isDataFresh()
    {
        $latestTimestamp = self::getLatestDataTimestamp();
        if (!$latestTimestamp) {
            return false;
        }

        return $latestTimestamp->diffInMinutes(now()) <= 5;
    }

    /**
     * Clear all market data cache
     */
    public static function clearCache()
    {
        Cache::forget('market_data_all');
        
        // Clear symbol-specific caches (this is a simplified approach)
        // In production, you might want to track cache keys more precisely
        try {
            // Only try to clear Redis keys if Redis is available
            if (Cache::getStore() instanceof \Illuminate\Cache\RedisStore) {
                $keys = Cache::getRedis()->keys('market_data_*');
                foreach ($keys as $key) {
                    Cache::forget($key);
                }
            }
        } catch (\Exception $e) {
            // If Redis is not available, just log the error and continue
            \Log::warning('Could not clear Redis cache keys: ' . $e->getMessage());
        }
    }

    /**
     * Get market data statistics
     */
    public static function getMarketDataStats()
    {
        return [
            'total_symbols' => self::count(),
            'live_symbols' => self::where('is_live', true)->count(),
            'historical_symbols' => self::where('is_live', false)->count(),
            'latest_update' => self::getLatestDataTimestamp(),
            'is_fresh' => self::isDataFresh(),
            'data_sources' => self::distinct('data_source')->pluck('data_source')->toArray()
        ];
    }
}
