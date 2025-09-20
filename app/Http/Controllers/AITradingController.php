<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

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
                'unit_price' => 'required|numeric|min:0.01',
                'lot_size' => 'required|integer|min:1|max:1000',
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
            $unitPrice = $request->input('unit_price');
            $lotSize = $request->input('lot_size');
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
                    'unit_price' => $unitPrice,
                    'lot_size' => $lotSize,
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
                        'unit_price' => $unitPrice,
                        'lot_size' => $lotSize,
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
     * Place a stock order (direct stock purchase/sale)
     */
    public function placeStockOrder(Request $request): JsonResponse
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
                'stock_symbol' => 'required|string|max:50',
                'action' => 'required|string|in:BUY,SELL',
                'quantity' => 'required|integer|min:1|max:10000',
                'unit_price' => 'required|numeric|min:0.01',
                'user_id' => 'required|integer|exists:users,id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 400);
            }

            $stockSymbol = $request->input('stock_symbol');
            $action = $request->input('action');
            $quantity = $request->input('quantity');
            $unitPrice = $request->input('unit_price');
            
            // Calculate total amount from quantity * unit price
            $totalAmount = $quantity * $unitPrice;
            
            // Validate calculated total amount
            if ($totalAmount <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid total amount calculated. Please check quantity and unit price.'
                ], 400);
            }
            
            // Log the calculation for debugging
            Log::info('Stock order calculation', [
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'calculated_total' => $totalAmount,
                'stock_symbol' => $stockSymbol
            ]);

            // Get user ID from request (selected user)
            $userId = $request->input('user_id');
            
            // Verify the authenticated user has permission to place orders for this user
            $authenticatedUserId = auth()->id();
            if (!$authenticatedUserId) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }
            
            // Debug: Log the user IDs
            Log::info('User IDs for stock order', [
                'selected_user_id' => $userId,
                'authenticated_user_id' => $authenticatedUserId
            ]);

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

            // If no wallet transactions exist, balance is 0
            $totalBalance = $totalBalance ?? 0;
            
            // Debug: Check wallet transactions for this user
            $walletTransactions = DB::table('wallet_transactions')
                ->where('user_id', $userId)
                ->orderBy('created_at', 'desc')
                ->get();
            
            Log::info('Wallet transactions debug', [
                'user_id' => $userId,
                'total_balance_from_query' => $totalBalance,
                'wallet_transactions_count' => $walletTransactions->count(),
                'latest_transaction' => $walletTransactions->first(),
                'all_transactions' => $walletTransactions->toArray()
            ]);

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
                // Create stock order
                $orderId = DB::table('ai_trading_orders')->insertGetId([
                    'user_id' => $userId,
                    'stock_symbol' => $stockSymbol,
                    'option_type' => 'STOCK', // Mark as stock order
                    'action' => $action,
                    'strike_price' => 0, // No strike price for stocks
                    'unit_price' => $unitPrice,
                    'lot_size' => 1, // 1 share per lot for stocks
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
                    'transaction_code' => 'STOCK_ORDER_' . $orderId,
                    'type' => 'block',
                    'amount' => $totalAmount,
                    'running_balance' => $totalBalance, // Balance stays same
                    'remark' => "Stock Order: {$action} {$quantity} shares of {$stockSymbol} @ ₹{$unitPrice} (Amount: ₹{$totalAmount})",
                    'approved_by' => $userId, // Self-approved for stock orders
                    'approved_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                DB::commit();

                Log::info("Stock order placed", [
                    'order_id' => $orderId,
                    'user_id' => $userId,
                    'stock_symbol' => $stockSymbol,
                    'action' => $action,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'total_amount' => $totalAmount
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Stock order placed successfully',
                    'order_id' => $orderId,
                    'data' => [
                        'order_id' => $orderId,
                        'stock_symbol' => $stockSymbol,
                        'action' => $action,
                        'quantity' => $quantity,
                        'unit_price' => $unitPrice,
                        'total_amount' => $totalAmount,
                        'calculation' => "{$quantity} shares × ₹{$unitPrice} = ₹{$totalAmount}",
                        'status' => 'COMPLETED',
                        'created_at' => now()->toISOString()
                    ]
                ]);

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (\Exception $e) {
            Log::error('Error placing stock order: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to place stock order',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Add initial funds for testing purposes
     */
    public function addInitialFunds(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'amount' => 'required|numeric|min:100|max:100000'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 400);
            }

            $userId = auth()->id();
            if (!$userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            $amount = $request->input('amount');

            // Check if user already has wallet transactions
            $existingTransaction = DB::table('wallet_transactions')
                ->where('user_id', $userId)
                ->exists();

            if ($existingTransaction) {
                return response()->json([
                    'success' => false,
                    'message' => 'User already has wallet transactions. Use fund adjustment instead.'
                ], 400);
            }

            // Start database transaction
            DB::beginTransaction();

            try {
                // Create initial wallet transaction
                DB::table('wallet_transactions')->insert([
                    'user_id' => $userId,
                    'transaction_code' => 'INITIAL_FUNDS_' . time() . '_' . $userId,
                    'type' => 'credit',
                    'amount' => $amount,
                    'running_balance' => $amount,
                    'remark' => "Initial funds added for testing - ₹{$amount}",
                    'approved_by' => $userId,
                    'approved_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                // Note: User's total_balance is calculated dynamically from wallet_transactions
                // No need to update users table as it's handled by the User model's getTotalBalanceAttribute()

                DB::commit();

                Log::info("Initial funds added", [
                    'user_id' => $userId,
                    'amount' => $amount
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Initial funds added successfully',
                    'data' => [
                        'amount' => $amount,
                        'new_balance' => $amount
                    ]
                ]);

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (\Exception $e) {
            Log::error('Error adding initial funds: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to add initial funds',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Debug balance information
     */
    public function debugBalance(): JsonResponse
    {
        try {
            $userId = auth()->id();
            if (!$userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            // Get all wallet transactions for this user
            $walletTransactions = DB::table('wallet_transactions')
                ->where('user_id', $userId)
                ->orderBy('created_at', 'desc')
                ->get();

            // Get user info
            $user = DB::table('users')->where('id', $userId)->first();

            // Get active trades
            $activeTrades = DB::table('ai_trading_orders')
                ->where('user_id', $userId)
                ->where('status', 'COMPLETED')
                ->get();

            // Calculate balance using the same logic as placeStockOrder
            $totalBalance = DB::table('wallet_transactions')
                ->where('user_id', $userId)
                ->orderBy('created_at', 'desc')
                ->value('running_balance');
            $totalBalance = $totalBalance ?? 0;

            $blockedAmount = DB::table('ai_trading_orders')
                ->where('user_id', $userId)
                ->where('status', 'COMPLETED')
                ->sum('total_amount');

            $availableBalance = $totalBalance - $blockedAmount;

            return response()->json([
                'success' => true,
                'debug_info' => [
                    'user_id' => $userId,
                    'user_info' => $user,
                    'wallet_transactions_count' => $walletTransactions->count(),
                    'wallet_transactions' => $walletTransactions->toArray(),
                    'total_balance_from_wallet' => $totalBalance,
                    'active_trades_count' => $activeTrades->count(),
                    'active_trades' => $activeTrades->toArray(),
                    'blocked_amount' => $blockedAmount,
                    'available_balance' => $availableBalance,
                    'calculation' => "Total: ₹{$totalBalance} - Blocked: ₹{$blockedAmount} = Available: ₹{$availableBalance}"
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error in debug balance: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to debug balance',
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
            ->select(
                'ai_trading_orders.*',
            )
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
            // Debug: Log the user ID being used for balance check
            Log::info('Getting balance for user', [
                'requested_user_id' => $userId,
                'authenticated_user_id' => auth()->id()
            ]);
            
            // Get total balance from all transactions
            $totalBalance = DB::table('wallet_transactions')
                ->where('user_id', $userId)
                ->orderBy('created_at', 'desc')
                ->value('running_balance');

            // If no wallet transactions exist, balance is 0
            $totalBalance = $totalBalance ?? 0;

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

            // Get exit unit price from request, or use current market price as fallback
            $exitUnitPrice = $request->input('exit_unit_price');
            $exitPrice = $request->input('exit_price');
            
            if ($exitUnitPrice && $exitPrice) {
                // Use provided exit prices
                $currentPrice = $exitPrice;
                Log::info("Using provided exit prices", [
                    'order_id' => $orderId,
                    'exit_unit_price' => $exitUnitPrice,
                    'exit_price' => $exitPrice
                ]);
            } else {
                // Fallback to current market price
                $currentPrice = $this->getCurrentMarketPrice($order->stock_symbol);
                
                if ($currentPrice === null) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Unable to fetch current market price. Please provide exit unit price.'
                    ], 400);
                }
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
                $finalBalance = $currentTotalBalance + $request->input('exit_price') * $order->quantity - $order->total_amount;

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
                    'new_balance' => $finalBalance
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Trade exited successfully',
                    'data' => [
                        'order_id' => $orderId,
                        'exit_price' => $currentPrice,
                        'pnl' => $pnl,
                        'new_balance' => $finalBalance,
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

            // If not in cache, use fallback prices for exit trades
            Log::info("Cache miss for symbol: {$symbol}, using fallback price for exit trade");

            // Fallback: Return a realistic price for known symbols
            $fallbackPrices = [
                'NIFTY 50' => 25000,
                'BANK NIFTY' => 52000,
                'NIFTY BANK' => 52000,
            ];
            
            if (isset($fallbackPrices[$symbol])) {
                Log::info("Using fallback price for {$symbol}: {$fallbackPrices[$symbol]}");
                return $fallbackPrices[$symbol];
            }

            Log::warning("No price found for symbol: {$symbol}");
            return null;

        } catch (\Exception $e) {
            Log::error('Error fetching market price for ' . $symbol . ': ' . $e->getMessage());
            
            // Emergency fallback for known symbols
            $emergencyPrices = [
                'NIFTY 50' => 25000,
                'BANK NIFTY' => 52000,
                'NIFTY BANK' => 52000,
            ];
            
            return $emergencyPrices[$symbol] ?? null;
        }
    }

    /**
     * Calculate P&L for an option trade using real option chain prices
     */
    private function calculatePnL($order, $currentPrice)
    {
        try {
            $strikePrice = $order->strike_price;
            $quantity = $order->quantity;
            $optionType = $order->option_type;
            $action = $order->action;
            $entryPremium = $order->total_amount / $quantity; // Premium per share at entry
            $stockSymbol = $order->stock_symbol;

            // Try to get current option price from option chain data
            $currentOptionPrice = $this->getCurrentOptionPrice($stockSymbol, $strikePrice, $optionType, $currentPrice);
            
            if ($currentOptionPrice !== null && $currentOptionPrice > 0) {
                // Use real option price for calculation
                Log::info("Using real option price for P&L calculation", [
                    'symbol' => $stockSymbol,
                    'strike' => $strikePrice,
                    'type' => $optionType,
                    'action' => $action,
                    'entry_premium' => $entryPremium,
                    'current_option_price' => $currentOptionPrice,
                    'quantity' => $quantity
                ]);
                
                $netPnL = $this->calculatePnLWithOptionPrice($order, $currentOptionPrice);
                
                Log::info("Real-time P&L calculation result", [
                    'symbol' => $stockSymbol,
                    'strike' => $strikePrice,
                    'type' => $optionType,
                    'action' => $action,
                    'entry_premium' => $entryPremium,
                    'current_option_price' => $currentOptionPrice,
                    'quantity' => $quantity,
                    'pnl' => $netPnL
                ]);

                return round($netPnL, 2);
            } else {
                // Fallback to intrinsic value calculation if option price not available
                Log::info("Option price not available, using intrinsic value calculation");
                return $this->calculateIntrinsicPnL($order, $currentPrice);
            }

        } catch (\Exception $e) {
            Log::error("Error in P&L calculation: " . $e->getMessage());
            // Fallback to intrinsic value calculation
            return $this->calculateIntrinsicPnL($order, $currentPrice);
        }
    }

    /**
     * Get current option price from option chain data
     */
    private function getCurrentOptionPrice($stockSymbol, $strikePrice, $optionType, $currentPrice)
    {
        try {
            // Map symbol names for API compatibility
            $apiSymbol = $stockSymbol;
            if ($stockSymbol === 'NIFTY 50') {
                $apiSymbol = 'NIFTY';
            } elseif ($stockSymbol === 'BANK NIFTY') {
                $apiSymbol = 'BANKNIFTY';
            }
            
            // Get the latest option chain data
            $expiry = '20250916'; // Default expiry - you might want to make this dynamic
            $optionChainData = \App\Models\OptionChain::getChain($apiSymbol, $expiry);
            
            if (empty($optionChainData)) {
                Log::warning("No option chain data found for {$apiSymbol}");
                return null;
            }
            
            // Find the specific option
            $targetStrike = (float) $strikePrice;
            $targetType = strtoupper($optionType) === 'CALL' ? 'CE' : 'PE';
            
            foreach ($optionChainData as $option) {
                if (isset($option['strike_price']) && isset($option['option_type'])) {
                    $optionStrike = (float) $option['strike_price'];
                    $optionTypeCode = $option['option_type'];
                    
                    if (abs($optionStrike - $targetStrike) < 0.01 && $optionTypeCode === $targetType) {
                        $ltp = (float) ($option['ltp'] ?? 0);
                        Log::info("Found option price for {$optionType} strike {$strikePrice}: {$ltp}");
                        return $ltp;
                    }
                }
            }
            
            Log::warning("Option not found in chain data for {$optionType} strike {$strikePrice}");
            return null;
            
        } catch (\Exception $e) {
            Log::error("Error getting current option price: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Calculate P&L using real option prices
     */
    private function calculatePnLWithOptionPrice($order, $currentOptionPrice)
    {
        $strikePrice = $order->strike_price;
        $quantity = $order->quantity;
        $optionType = $order->option_type;
        $action = $order->action;
        $entryPremium = $order->total_amount / $quantity; // Premium per share at entry
        
        if ($action === 'BUY') {
            // Bought option: P&L = (Current Option Price - Entry Premium) * Quantity
            $pnlPerShare = $currentOptionPrice - $entryPremium;
            $netPnL = $pnlPerShare * $quantity;
        } else {
            // Sold option: P&L = (Entry Premium - Current Option Price) * Quantity
            $pnlPerShare = $entryPremium - $currentOptionPrice;
            $netPnL = $pnlPerShare * $quantity;
        }
        
        return $netPnL;
    }

    /**
     * Fallback P&L calculation using intrinsic value (old method)
     */
    private function calculateIntrinsicPnL($order, $currentPrice)
    {
        $strikePrice = $order->strike_price;
        $quantity = $order->quantity;
        $optionType = $order->option_type;
        $action = $order->action;
        $premiumPaid = $order->total_amount; // Total amount paid/received for the option

        // For CALL options
        if ($optionType === 'CALL') {
            if ($action === 'BUY') {
                // Bought CALL: Profit if current price > strike price
                $intrinsicValue = max(0, $currentPrice - $strikePrice);
                $grossPnL = $intrinsicValue * $quantity;
                $netPnL = $grossPnL - $premiumPaid; // Subtract premium paid
            } else {
                // Sold CALL: Profit if current price < strike price
                $intrinsicValue = max(0, $currentPrice - $strikePrice);
                $grossPnL = $intrinsicValue * $quantity;
                $netPnL = $premiumPaid - $grossPnL; // Premium received minus loss
            }
        }
        // For PUT options
        else {
            if ($action === 'BUY') {
                // Bought PUT: Profit if current price < strike price
                $intrinsicValue = max(0, $strikePrice - $currentPrice);
                $grossPnL = $intrinsicValue * $quantity;
                $netPnL = $grossPnL - $premiumPaid; // Subtract premium paid
            } else {
                // Sold PUT: Profit if current price > strike price
                $intrinsicValue = max(0, $strikePrice - $currentPrice);
                $grossPnL = $intrinsicValue * $quantity;
                $netPnL = $premiumPaid - $grossPnL; // Premium received minus loss
            }
        }

        return round($netPnL, 2);
    }

    /**
     * Get detailed trade information with live P&L
     */
    public function getTradeDetails($orderId): JsonResponse
    {
        try {
            $order = DB::table('ai_trading_orders')
                ->where('id', $orderId)
                ->first();

            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order not found'
                ], 404);
            }

            // Get current market price
            $currentPrice = $this->getCurrentMarketPrice($order->stock_symbol);
            
            // Get real-time option price
            $optionsService = new \App\Services\OptionsService();
            $currentOptionPrice = $optionsService->getRealTimeOptionPrice(
                $order->stock_symbol,
                $order->strike_price,
                $order->option_type
            );

            // Calculate live P&L
            $livePnL = 0;
            $entryPremium = $order->total_amount / $order->quantity;
            
            if ($order->status === 'COMPLETED' && $currentOptionPrice !== null) {
                if ($order->action === 'BUY') {
                    $pnlPerShare = $currentOptionPrice - $entryPremium;
                    $livePnL = $pnlPerShare * $order->quantity;
                } else if ($order->action === 'SELL') {
                    $pnlPerShare = $entryPremium - $currentOptionPrice;
                    $livePnL = $pnlPerShare * $order->quantity;
                }
            }

            // Calculate percentage change
            $pnlPercentage = 0;
            if ($order->total_amount > 0) {
                $pnlPercentage = ($livePnL / $order->total_amount) * 100;
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'order_id' => $order->id,
                    'stock_symbol' => $order->stock_symbol,
                    'option_type' => $order->option_type,
                    'strike_price' => $order->strike_price,
                    'action' => $order->action,
                    'quantity' => $order->quantity,
                    'entry_premium' => round($entryPremium, 2),
                    'total_amount' => $order->total_amount,
                    'current_stock_price' => $currentPrice,
                    'current_option_price' => $currentOptionPrice,
                    'live_pnl' => round($livePnL, 2),
                    'pnl_percentage' => round($pnlPercentage, 2),
                    'status' => $order->status,
                    'created_at' => $order->created_at,
                    'closed_at' => $order->closed_at ?? null,
                    'exit_price' => $order->exit_price ?? null,
                    'final_pnl' => $order->pnl ?? null
                ]
            ]);

        } catch (\Exception $e) {
            Log::error("Error getting trade details: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to get trade details'
            ], 500);
        }
    }

    /**
     * Get live P&L for all active trades of a user
     */
    public function getLivePnL($userId): JsonResponse
    {
        try {
            $activeTrades = DB::table('ai_trading_orders')
                ->where('user_id', $userId)
                ->where('status', 'COMPLETED')
                ->get();

            $totalLivePnL = 0;
            $tradeDetails = [];

            foreach ($activeTrades as $order) {
                // Get current market price
                $currentPrice = $this->getCurrentMarketPrice($order->stock_symbol);
                
                // Get real-time option price
                $optionsService = new \App\Services\OptionsService();
                $currentOptionPrice = $optionsService->getRealTimeOptionPrice(
                    $order->stock_symbol,
                    $order->strike_price,
                    $order->option_type
                );

                // Calculate live P&L
                $livePnL = 0;
                $entryPremium = $order->total_amount / $order->quantity;
                
                if ($currentOptionPrice !== null) {
                    if ($order->action === 'BUY') {
                        $pnlPerShare = $currentOptionPrice - $entryPremium;
                        $livePnL = $pnlPerShare * $order->quantity;
                    } else if ($order->action === 'SELL') {
                        $pnlPerShare = $entryPremium - $currentOptionPrice;
                        $livePnL = $pnlPerShare * $order->quantity;
                    }
                }

                $totalLivePnL += $livePnL;

                $tradeDetails[] = [
                    'order_id' => $order->id,
                    'stock_symbol' => $order->stock_symbol,
                    'option_type' => $order->option_type,
                    'strike_price' => $order->strike_price,
                    'action' => $order->action,
                    'quantity' => $order->quantity,
                    'entry_premium' => round($entryPremium, 2),
                    'current_option_price' => $currentOptionPrice,
                    'live_pnl' => round($livePnL, 2),
                    'pnl_percentage' => $order->total_amount > 0 ? round(($livePnL / $order->total_amount) * 100, 2) : 0
                ];
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'total_live_pnl' => round($totalLivePnL, 2),
                    'active_trades_count' => count($activeTrades),
                    'trades' => $tradeDetails
                ]
            ]);

        } catch (\Exception $e) {
            Log::error("Error getting live P&L: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to get live P&L'
            ], 500);
        }
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
