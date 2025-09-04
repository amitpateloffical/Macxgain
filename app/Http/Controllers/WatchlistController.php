<?php

namespace App\Http\Controllers;

use App\Models\UserWatchlist;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class WatchlistController extends Controller
{
    /**
     * Get user's watchlist
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            $watchlist = UserWatchlist::forUser($user->id)
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $watchlist,
                'count' => $watchlist->count(),
                'message' => 'Watchlist retrieved successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Watchlist Index Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve watchlist',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Add stock to watchlist
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            $request->validate([
                'symbol' => 'required|string|max:50',
                'name' => 'nullable|string|max:255',
                'price' => 'nullable|numeric|min:0',
                'change' => 'nullable|numeric',
                'change_percent' => 'nullable|numeric',
                'high' => 'nullable|numeric|min:0',
                'low' => 'nullable|numeric|min:0',
                'open' => 'nullable|numeric|min:0',
                'prev_close' => 'nullable|numeric|min:0',
                'volume' => 'nullable|integer|min:0'
            ]);

            // Check if already in watchlist
            if (UserWatchlist::isInWatchlist($user->id, $request->symbol)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Stock is already in your watchlist'
                ], 409);
            }

            $stockData = $request->only([
                'symbol', 'name', 'price', 'change', 'change_percent',
                'high', 'low', 'open', 'prev_close', 'volume'
            ]);

            $watchlistItem = UserWatchlist::addToWatchlist($user->id, $stockData);

            return response()->json([
                'success' => true,
                'data' => $watchlistItem,
                'message' => 'Stock added to watchlist successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Watchlist Store Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to add stock to watchlist',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove stock from watchlist
     */
    public function destroy(Request $request, $symbol): JsonResponse
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            $removed = UserWatchlist::removeFromWatchlist($user->id, $symbol);

            if (!$removed) {
                return response()->json([
                    'success' => false,
                    'message' => 'Stock not found in watchlist'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Stock removed from watchlist successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Watchlist Destroy Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to remove stock from watchlist',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check if stock is in watchlist
     */
    public function check(Request $request, $symbol): JsonResponse
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            $isInWatchlist = UserWatchlist::isInWatchlist($user->id, $symbol);

            return response()->json([
                'success' => true,
                'in_watchlist' => $isInWatchlist,
                'message' => $isInWatchlist ? 'Stock is in watchlist' : 'Stock not in watchlist'
            ]);

        } catch (\Exception $e) {
            Log::error('Watchlist Check Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to check watchlist status',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get watchlist count
     */
    public function count(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            $count = UserWatchlist::getWatchlistCount($user->id);

            return response()->json([
                'success' => true,
                'count' => $count,
                'message' => 'Watchlist count retrieved successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Watchlist Count Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to get watchlist count',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update watchlist item with latest data
     */
    public function update(Request $request, $symbol): JsonResponse
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            $request->validate([
                'price' => 'nullable|numeric|min:0',
                'change' => 'nullable|numeric',
                'change_percent' => 'nullable|numeric',
                'high' => 'nullable|numeric|min:0',
                'low' => 'nullable|numeric|min:0',
                'open' => 'nullable|numeric|min:0',
                'prev_close' => 'nullable|numeric|min:0',
                'volume' => 'nullable|integer|min:0'
            ]);

            $watchlistItem = UserWatchlist::where('user_id', $user->id)
                ->where('symbol', $symbol)
                ->first();

            if (!$watchlistItem) {
                return response()->json([
                    'success' => false,
                    'message' => 'Stock not found in watchlist'
                ], 404);
            }

            $updateData = $request->only([
                'price', 'change', 'change_percent', 'high', 'low',
                'open', 'prev_close', 'volume'
            ]);
            $updateData['last_updated'] = now();

            $watchlistItem->update($updateData);

            return response()->json([
                'success' => true,
                'data' => $watchlistItem,
                'message' => 'Watchlist item updated successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Watchlist Update Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update watchlist item',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Admin: Get all user watchlists
     */
    public function adminIndex(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            if (!$user || !$user->is_admin) {
                return response()->json([
                    'success' => false,
                    'message' => 'Admin access required'
                ], 403);
            }

            $watchlists = UserWatchlist::getAllWatchlists();

            return response()->json([
                'success' => true,
                'data' => $watchlists,
                'message' => 'All watchlists retrieved successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Admin Watchlist Index Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve all watchlists',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Admin: Get specific user's watchlist
     */
    public function adminUserWatchlist(Request $request, $userId): JsonResponse
    {
        try {
            $user = Auth::user();
            if (!$user || !$user->is_admin) {
                return response()->json([
                    'success' => false,
                    'message' => 'Admin access required'
                ], 403);
            }

            $targetUser = User::find($userId);
            if (!$targetUser) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found'
                ], 404);
            }

            $watchlist = UserWatchlist::forUser($userId)
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $watchlist,
                'user' => $targetUser,
                'count' => $watchlist->count(),
                'message' => 'User watchlist retrieved successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Admin User Watchlist Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve user watchlist',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}