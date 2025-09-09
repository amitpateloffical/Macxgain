<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class OptionChain extends Model
{
    protected $fillable = [
        'symbol',
        'expiry_date',
        'option_type',
        'strike_price',
        'ltp',
        'bid',
        'ask',
        'volume',
        'oi',
        'data_timestamp',
        'data_source',
        'raw_data',
    ];

    protected $casts = [
        'expiry_date' => 'date',
        'strike_price' => 'decimal:4',
        'ltp' => 'decimal:4',
        'bid' => 'decimal:4',
        'ask' => 'decimal:4',
        'volume' => 'integer',
        'oi' => 'integer',
        'data_timestamp' => 'datetime',
    ];

    public function setRawDataAttribute($value)
    {
        if ($value !== null) {
            $this->attributes['raw_data'] = Crypt::encryptString(json_encode($value));
        }
    }

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

    public static function storeChain(string $symbol, string $expiryYmd, array $options, string $dataSource = 'TrueData')
    {
        $expiry = Carbon::createFromFormat('Ymd', $expiryYmd)->startOfDay();
        $now = now();
        $count = 0;

        foreach ($options as $row) {
            try {
                self::updateOrCreate(
                    [
                        'symbol' => $symbol,
                        'expiry_date' => $expiry,
                        'option_type' => strtoupper($row['option_type'] ?? ''),
                        'strike_price' => $row['strike_price'] ?? 0,
                    ],
                    [
                        'ltp' => $row['ltp'] ?? 0,
                        'bid' => $row['bid'] ?? 0,
                        'ask' => $row['ask'] ?? 0,
                        'volume' => $row['volume'] ?? 0,
                        'oi' => $row['oi'] ?? ($row['open_interest'] ?? 0),
                        'data_timestamp' => isset($row['timestamp']) ? Carbon::parse($row['timestamp']) : $now,
                        'data_source' => $dataSource,
                        'raw_data' => $row,
                    ]
                );
                $count++;
            } catch (\Exception $e) {
                \Log::error('OptionChain store error: ' . $e->getMessage());
            }
        }

        Cache::forget(self::cacheKey($symbol, $expiryYmd));
        return $count;
    }

    public static function getChain(string $symbol, string $expiryYmd, bool $useCache = true)
    {
        $cacheKey = self::cacheKey($symbol, $expiryYmd);
        if ($useCache && Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $expiry = Carbon::createFromFormat('Ymd', $expiryYmd)->startOfDay();
        $rows = self::where('symbol', $symbol)
            ->whereDate('expiry_date', $expiry)
            ->orderBy('strike_price')
            ->get();

        $data = $rows->map(function ($item) {
            return [
                'symbol' => $item->symbol,
                'expiry' => $item->expiry_date->format('Ymd'),
                'option_type' => $item->option_type,
                'strike_price' => (float) $item->strike_price,
                'ltp' => (float) $item->ltp,
                'bid' => (float) $item->bid,
                'ask' => (float) $item->ask,
                'volume' => (int) $item->volume,
                'oi' => (int) $item->oi,
                'timestamp' => $item->data_timestamp?->toISOString(),
                'data_source' => $item->data_source,
            ];
        })->toArray();

        Cache::put($cacheKey, $data, 90);
        return $data;
    }

    public static function latestTimestamp(string $symbol, string $expiryYmd)
    {
        $expiry = Carbon::createFromFormat('Ymd', $expiryYmd)->startOfDay();
        $ts = self::where('symbol', $symbol)
            ->whereDate('expiry_date', $expiry)
            ->max('data_timestamp');
        return $ts ? Carbon::parse($ts) : null;
    }

    public static function isFresh(string $symbol, string $expiryYmd, int $minutes = 5): bool
    {
        $ts = self::latestTimestamp($symbol, $expiryYmd);
        if (!$ts) { return false; }
        return $ts->diffInMinutes(now()) <= $minutes;
    }

    private static function cacheKey(string $symbol, string $expiryYmd): string
    {
        return 'option_chain_' . strtoupper($symbol) . '_' . $expiryYmd;
    }
}


