<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserWatchlist extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'symbol',
        'name',
        'price',
        'change',
        'change_percent',
        'high',
        'low',
        'open',
        'prev_close',
        'volume',
        'last_updated'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'change' => 'decimal:2',
        'change_percent' => 'decimal:4',
        'high' => 'decimal:2',
        'low' => 'decimal:2',
        'open' => 'decimal:2',
        'prev_close' => 'decimal:2',
        'volume' => 'integer',
        'last_updated' => 'datetime'
    ];

    /**
     * Get the user that owns the watchlist item
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope to get watchlist for a specific user
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope to get watchlist by symbol
     */
    public function scopeBySymbol($query, $symbol)
    {
        return $query->where('symbol', $symbol);
    }

    /**
     * Check if stock is in user's watchlist
     */
    public static function isInWatchlist($userId, $symbol): bool
    {
        return self::where('user_id', $userId)
                   ->where('symbol', $symbol)
                   ->exists();
    }

    /**
     * Add stock to watchlist
     */
    public static function addToWatchlist($userId, $stockData): self
    {
        return self::updateOrCreate(
            [
                'user_id' => $userId,
                'symbol' => $stockData['symbol']
            ],
            [
                'name' => $stockData['name'] ?? $stockData['symbol'],
                'price' => $stockData['price'] ?? $stockData['last'] ?? 0,
                'change' => $stockData['change'] ?? 0,
                'change_percent' => $stockData['change_percent'] ?? 0,
                'high' => $stockData['high'] ?? 0,
                'low' => $stockData['low'] ?? 0,
                'open' => $stockData['open'] ?? 0,
                'prev_close' => $stockData['prev_close'] ?? 0,
                'volume' => $stockData['volume'] ?? 0,
                'last_updated' => now()
            ]
        );
    }

    /**
     * Remove stock from watchlist
     */
    public static function removeFromWatchlist($userId, $symbol): bool
    {
        return self::where('user_id', $userId)
                   ->where('symbol', $symbol)
                   ->delete() > 0;
    }

    /**
     * Get watchlist count for user
     */
    public static function getWatchlistCount($userId): int
    {
        return self::where('user_id', $userId)->count();
    }

    /**
     * Get all watchlists for admin view
     */
    public static function getAllWatchlists()
    {
        return self::with('user')
                   ->orderBy('created_at', 'desc')
                   ->get()
                   ->groupBy('user_id');
    }
}