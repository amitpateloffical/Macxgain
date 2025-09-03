<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;

class AITradingController extends Controller
{
    /**
     * Check if market is currently open
     */
    private function isMarketOpen(): bool
    {
        try {
            // Use the same logic as MarketStatusService for consistency
            $currentTime = now('Asia/Kolkata');
            $currentHour = $currentTime->hour;
            $currentMinute = $currentTime->minute;
            $currentTimeMinutes = $currentHour * 60 + $currentMinute;
            
            // Market hours: 9:15 AM (555 minutes) to 3:30 PM (930 minutes)
            $marketOpenTime = 9 * 60 + 15; // 9:15 AM
            $marketCloseTime = 15 * 60 + 30; // 3:30 PM
            
            // Check if it's a weekday (Monday to Friday)
            $isWeekday = $currentTime->dayOfWeek >= 1 && $currentTime->dayOfWeek <= 5;
            
            // Check if current time is within market hours
            $isWithinHours = $currentTimeMinutes >= $marketOpenTime && $currentTimeMinutes <= $marketCloseTime;
            
            // Market is open if: it's weekday AND within hours
            return $isWeekday && $isWithinHours;
            
        } catch (\Exception $e) {
            Log::error('Error checking market status: ' . $e->getMessage());
            return false; // Default to closed if error
        }
    }

    /**
     * Execute a trade for a user
     */
    public function executeTrade(Request $request): JsonResponse
    {
        try {
            // 24/7 Trading enabled for testing purposes
            // Commented out market hours check
            /*
            if (!$this->isMarketOpen()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Trading is only allowed during market hours (9:15 AM - 3:30 PM IST, Monday-Friday)',
                    'market_status' => 'CLOSED'
                ], 400);
            }
            */

            $validator = Validator::make($request->all(), [
                'user_id' => 'required|integer|exists:users,id',
                'stock_symbol' => 'required|string|max:50',
                'option_type' => 'required|string|in:CALL,PUT',
                'action' => 'required|string|in:BUY,SELL',
                'strike_price' => 'required|numeric|min:0.01',
                'quantity' => 'required|integer|min:1|max:1000',
                'total_amount' => 'required|numeric|min:0.01'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 400);
            }

            $userId = $request->input('user_id');
            $stockSymbol = $request->input('stock_symbol');
            $optionType = $request->input('option_type');
            $action = $request->input('action');
            $strikePrice = $request->input('strike_price');
            $quantity = $request->input('quantity');
            $totalAmount = $request->input('total_amount');

            // Check user exists
            $user = DB::table('users')->where('id', $userId)->first();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found'
                ], 404);
            }

            // Get user's current balance from wallet transactions
            $totalBalance = DB::table('wallet_transactions')
                ->where('user_id', $userId)
                ->orderBy('created_at', 'desc')
                ->value('running_balance');

            // Calculate blocked amount from active trades
            $blockedAmount = DB::table('ai_trading_orders')
                ->where('user_id', $userId)
                ->where('status', 'COMPLETED') // Active trades
                ->sum('total_amount');

            // Available balance = Total balance - Blocked amount
            $availableBalance = ($totalBalance ?? 0) - ($blockedAmount ?? 0);

            if ($availableBalance < $totalAmount) {
                return response()->json([
                    'success' => false,
                    'message' => 'Insufficient available balance. Available: ₹' . number_format($availableBalance, 2) . ' (Total: ₹' . number_format($totalBalance ?? 0, 2) . ', Blocked: ₹' . number_format($blockedAmount ?? 0, 2) . ')'
                ], 400);
            }

            // Start database transaction
            DB::beginTransaction();

            try {
                // Create trade order
                $orderId = DB::table('ai_trading_orders')->insertGetId([
                    'user_id' => $userId,
                    'stock_symbol' => $stockSymbol,
                    'option_type' => $optionType,
                    'action' => $action,
                    'strike_price' => $strikePrice,
                    'quantity' => $quantity,
                    'total_amount' => $totalAmount,
                    'status' => 'COMPLETED',
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                // Create wallet transaction record (block amount - don't deduct from balance)
                // Balance remains same, but amount is blocked for trading
                DB::table('wallet_transactions')->insert([
                    'user_id' => $userId,
                    'transaction_code' => 'AI_TRADE_BLOCK_' . $orderId,
                    'type' => 'block',
                    'amount' => $totalAmount,
                    'running_balance' => $totalBalance, // Balance stays same
                    'remark' => "AI Trading Block: {$action} {$quantity} {$optionType} {$stockSymbol} @ ₹{$strikePrice} (Amount: ₹{$totalAmount})",
                    'approved_by' => auth()->id() ?? 1, // Admin who executed the trade
                    'approved_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                DB::commit();

                Log::info("AI Trading order executed", [
                    'order_id' => $orderId,
                    'user_id' => $userId,
                    'stock_symbol' => $stockSymbol,
                    'option_type' => $optionType,
                    'action' => $action,
                    'total_amount' => $totalAmount
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Trade executed successfully',
                    'order_id' => $orderId,
                    'data' => [
                        'order_id' => $orderId,
                        'stock_symbol' => $stockSymbol,
                        'option_type' => $optionType,
                        'action' => $action,
                        'strike_price' => $strikePrice,
                        'quantity' => $quantity,
                        'total_amount' => $totalAmount,
                        'status' => 'COMPLETED',
                        'created_at' => now()->toISOString()
                    ]
                ]);

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (\Exception $e) {
            Log::error('Error executing AI trade: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to execute trade',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user's trading orders
     */
    public function getUserOrders($userId): JsonResponse
    {
        try {
            $orders = DB::table('ai_trading_orders')
                ->where('user_id', $userId)
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'orders' => $orders,
                'total' => $orders->count()
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching user orders: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch orders',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all trading orders (admin view)
     */
    public function getAllOrders(Request $request): JsonResponse
    {
        try {
            $page = $request->input('page', 1);
            $limit = $request->input('limit', 20);
            $userId = $request->input('user_id');
            $status = $request->input('status');

            $query = DB::table('ai_trading_orders')
                ->join('users', 'ai_trading_orders.user_id', '=', 'users.id')
                ->select(
                    'ai_trading_orders.*',
                    'users.name as user_name',
                    'users.email as user_email'
                );

            if ($userId) {
                $query->where('ai_trading_orders.user_id', $userId);
            }

            if ($status) {
                $query->where('ai_trading_orders.status', $status);
            }

            $total = $query->count();
            $orders = $query->orderBy('ai_trading_orders.created_at', 'desc')
                ->offset(($page - 1) * $limit)
                ->limit($limit)
                ->get();

            return response()->json([
                'success' => true,
                'orders' => $orders,
                'pagination' => [
                    'current_page' => $page,
                    'per_page' => $limit,
                    'total' => $total,
                    'last_page' => ceil($total / $limit)
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching all orders: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch orders',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cancel a trading order
     */
    public function cancelOrder($orderId): JsonResponse
    {
        try {
            $order = DB::table('ai_trading_orders')->where('id', $orderId)->first();
            
            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order not found'
                ], 404);
            }

            if ($order->status !== 'PENDING') {
                return response()->json([
                    'success' => false,
                    'message' => 'Order cannot be cancelled'
                ], 400);
            }

            DB::beginTransaction();

            try {
                // Update order status
                DB::table('ai_trading_orders')
                    ->where('id', $orderId)
                    ->update([
                        'status' => 'CANCELLED',
                        'updated_at' => now()
                    ]);

                // Refund user balance
                DB::table('users')
                    ->where('id', $order->user_id)
                    ->increment('balance', $order->total_amount);

                // Create refund transaction
                DB::table('transactions')->insert([
                    'user_id' => $order->user_id,
                    'type' => 'AI_TRADING_REFUND',
                    'amount' => $order->total_amount,
                    'description' => "AI Trading Order Cancelled: #{$orderId}",
                    'status' => 'COMPLETED',
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Order cancelled successfully'
                ]);

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (\Exception $e) {
            Log::error('Error cancelling order: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel order',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user's current wallet balance (excluding blocked amounts)
     */
    public function getUserBalance($userId): JsonResponse
    {
        try {
            // Get total balance from all transactions
            $totalBalance = DB::table('wallet_transactions')
                ->where('user_id', $userId)
                ->orderBy('created_at', 'desc')
                ->value('running_balance');

            // Calculate blocked amount from active trades
            $blockedAmount = DB::table('ai_trading_orders')
                ->where('user_id', $userId)
                ->where('status', 'COMPLETED') // Active trades
                ->sum('total_amount');

            // Available balance = Total balance - Blocked amount
            $availableBalance = ($totalBalance ?? 0) - ($blockedAmount ?? 0);

            return response()->json([
                'success' => true,
                'balance' => floatval($availableBalance),
                'total_balance' => floatval($totalBalance ?? 0),
                'blocked_amount' => floatval($blockedAmount ?? 0),
                'formatted_balance' => '₹' . number_format($availableBalance, 2),
                'formatted_total_balance' => '₹' . number_format($totalBalance ?? 0, 2),
                'formatted_blocked_amount' => '₹' . number_format($blockedAmount ?? 0, 2)
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching user balance: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch user balance',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Exit/Close a trade
     */
    public function exitTrade(Request $request, $orderId): JsonResponse
    {
        try {
            // 24/7 Trading enabled for testing purposes
            // Commented out market hours check for trade exit
            /*
            if (!$this->isMarketOpen()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Trade exit is only allowed during market hours (9:15 AM - 3:30 PM IST, Monday-Friday)',
                    'market_status' => 'CLOSED'
                ], 400);
            }
            */

            $order = DB::table('ai_trading_orders')
                ->where('id', $orderId)
                ->where('status', 'COMPLETED')
                ->first();

            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order not found or already closed'
                ], 404);
            }

            // Get current market price for the stock
            $currentPrice = $this->getCurrentMarketPrice($order->stock_symbol);
            
            if ($currentPrice === null) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unable to fetch current market price'
                ], 400);
            }

            // Calculate P&L based on option type
            $pnl = $this->calculatePnL($order, $currentPrice);
            
            // Start database transaction
            DB::beginTransaction();

            try {
                // Update order status to closed
                DB::table('ai_trading_orders')
                    ->where('id', $orderId)
                    ->update([
                        'status' => 'CLOSED',
                        'exit_price' => $currentPrice,
                        'pnl' => $pnl,
                        'closed_at' => now(),
                        'updated_at' => now()
                    ]);

                // Get user's current total balance
                $currentTotalBalance = DB::table('wallet_transactions')
                    ->where('user_id', $order->user_id)
                    ->orderBy('created_at', 'desc')
                    ->value('running_balance');

                // Calculate final balance: current total balance + P&L
                $blockedAmount = $order->total_amount; // Amount that was blocked
                $finalBalance = $currentTotalBalance + $pnl;

                // Create wallet transaction for trade exit (unblock + P&L)
                DB::table('wallet_transactions')->insert([
                    'user_id' => $order->user_id,
                    'transaction_code' => 'AI_TRADE_EXIT_' . $orderId,
                    'type' => 'unblock',
                    'amount' => $blockedAmount + $pnl, // Return blocked amount + P&L
                    'running_balance' => $finalBalance,
                    'remark' => "AI Trading Exit: Order #{$orderId} - Returned ₹{$blockedAmount} + " . ($pnl >= 0 ? 'Profit' : 'Loss') . " ₹" . abs($pnl) . " = Final: ₹" . ($blockedAmount + $pnl),
                    'approved_by' => auth()->id() ?? 1,
                    'approved_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                DB::commit();

                Log::info("AI Trading order exited", [
                    'order_id' => $orderId,
                    'user_id' => $order->user_id,
                    'exit_price' => $currentPrice,
                    'pnl' => $pnl,
                    'new_balance' => $newBalance
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Trade exited successfully',
                    'data' => [
                        'order_id' => $orderId,
                        'exit_price' => $currentPrice,
                        'pnl' => $pnl,
                        'new_balance' => $newBalance,
                        'pnl_type' => $pnl >= 0 ? 'profit' : 'loss'
                    ]
                ]);

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (\Exception $e) {
            Log::error('Error exiting trade: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to exit trade',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get current market price for a stock
     */
    private function getCurrentMarketPrice($symbol)
    {
        try {
            // Get from cache first
            $cachedData = Cache::get('truedata_live_data', []);
            
            if (isset($cachedData[$symbol])) {
                return $cachedData[$symbol]['ltp'] ?? null;
            }

            // If not in cache, try to fetch from TrueData API
            // This would be a fallback mechanism
            return null;

        } catch (\Exception $e) {
            Log::error('Error fetching market price for ' . $symbol . ': ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Calculate P&L for an option trade
     */
    private function calculatePnL($order, $currentPrice)
    {
        $strikePrice = $order->strike_price;
        $quantity = $order->quantity;
        $optionType = $order->option_type;
        $action = $order->action;

        // For CALL options
        if ($optionType === 'CALL') {
            if ($action === 'BUY') {
                // Bought CALL: Profit if current price > strike price
                $intrinsicValue = max(0, $currentPrice - $strikePrice);
                $pnl = ($intrinsicValue - $strikePrice) * $quantity;
            } else {
                // Sold CALL: Profit if current price < strike price
                $intrinsicValue = max(0, $currentPrice - $strikePrice);
                $pnl = ($strikePrice - $intrinsicValue) * $quantity;
            }
        }
        // For PUT options
        else {
            if ($action === 'BUY') {
                // Bought PUT: Profit if current price < strike price
                $intrinsicValue = max(0, $strikePrice - $currentPrice);
                $pnl = ($intrinsicValue - $strikePrice) * $quantity;
            } else {
                // Sold PUT: Profit if current price > strike price
                $intrinsicValue = max(0, $strikePrice - $currentPrice);
                $pnl = ($strikePrice - $intrinsicValue) * $quantity;
            }
        }

        return round($pnl, 2);
    }

    /**
     * Get market status
     */
    public function getMarketStatus(): JsonResponse
    {
        try {
            $isOpen = $this->isMarketOpen();
            $marketStatus = Cache::get('truedata_market_status', 'CLOSED');
            
            // Get current time info
            $currentTime = now('Asia/Kolkata');
            $currentTimeFormatted = $currentTime->format('H:i:s');
            $currentDate = $currentTime->format('Y-m-d');
            $dayOfWeek = $currentTime->format('l');
            
            // Calculate next market open time
            $nextOpenTime = null;
            if (!$isOpen) {
                $marketOpenTime = $currentTime->copy()->setTime(9, 15, 0);
                
                // If it's weekend or past market hours today, get next weekday
                if ($currentTime->dayOfWeek >= 6 || $currentTime->hour >= 15 || ($currentTime->hour == 15 && $currentTime->minute >= 30)) {
                    $marketOpenTime = $currentTime->copy()->nextWeekday()->setTime(9, 15, 0);
                }
                
                $nextOpenTime = $marketOpenTime->format('Y-m-d H:i:s');
            }
            
            return response()->json([
                'success' => true,
                'data' => [
                    'is_open' => $isOpen,
                    'status' => $isOpen ? 'OPEN' : 'CLOSED',
                    'current_time' => $currentTimeFormatted,
                    'current_date' => $currentDate,
                    'day_of_week' => $dayOfWeek,
                    'market_status_from_cache' => $marketStatus,
                    'next_open_time' => $nextOpenTime,
                    'market_hours' => [
                        'open' => '09:15',
                        'close' => '15:30',
                        'timezone' => 'Asia/Kolkata',
                        'days' => 'Monday to Friday'
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting market status: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to get market status',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get trading statistics
     */
    public function getTradingStats(): JsonResponse
    {
        try {
            $stats = [
                'total_orders' => DB::table('ai_trading_orders')->count(),
                'total_volume' => DB::table('ai_trading_orders')->sum('total_amount'),
                'completed_orders' => DB::table('ai_trading_orders')->where('status', 'COMPLETED')->count(),
                'pending_orders' => DB::table('ai_trading_orders')->where('status', 'PENDING')->count(),
                'cancelled_orders' => DB::table('ai_trading_orders')->where('status', 'CANCELLED')->count(),
                'active_traders' => DB::table('ai_trading_orders')->distinct('user_id')->count('user_id'),
                'top_stocks' => DB::table('ai_trading_orders')
                    ->select('stock_symbol', DB::raw('COUNT(*) as trade_count'), DB::raw('SUM(total_amount) as total_volume'))
                    ->groupBy('stock_symbol')
                    ->orderBy('trade_count', 'desc')
                    ->limit(10)
                    ->get()
            ];

            return response()->json([
                'success' => true,
                'stats' => $stats
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching trading stats: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch trading statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
