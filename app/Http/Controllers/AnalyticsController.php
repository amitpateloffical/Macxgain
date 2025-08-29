<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\User;
use App\Models\MoneyRequest;
use App\Models\WithdrawalRequest;
use App\Models\WalletTransaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    /**
     * Get analytics dashboard data
     */
    public function getAnalyticsData(Request $request): JsonResponse
    {
        try {
            $period = $request->get('period', 'current_month');
            $startDate = $request->get('start_date');
            $endDate = $request->get('end_date');

            // Calculate date range based on period
            $dateRange = $this->calculateDateRange($period, $startDate, $endDate);
            
            // Get live metrics
            $metrics = $this->getLiveMetrics($dateRange);
            
            // Get growth rates
            $growthRates = $this->getGrowthRates($dateRange);
            
            // Get detailed data
            $users = $this->getUsersData($dateRange);
            $deposits = $this->getDepositsData($dateRange);
            $withdrawals = $this->getWithdrawalsData($dateRange);
            
            // Get chart data
            $charts = $this->getChartsData($dateRange);

            return response()->json([
                'success' => true,
                'metrics' => $metrics,
                'growth_rates' => $growthRates,
                'users' => $users,
                'deposits' => $deposits,
                'withdrawals' => $withdrawals,
                'charts' => $charts,
                'period' => $period,
                'date_range' => $dateRange,
                'timestamp' => now()->toISOString(),
                'message' => 'Live analytics data fetched successfully'
            ]);

        } catch (\Exception $e) {
            \Log::error('Analytics Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Failed to fetch analytics data'
            ], 500);
        }
    }

    /**
     * Calculate date range based on period
     */
    private function calculateDateRange($period, $startDate = null, $endDate = null)
    {
        $now = Carbon::now();
        
        switch ($period) {
            case 'current_month':
                return [
                    'start' => $now->startOfMonth()->toDateString(),
                    'end' => $now->endOfMonth()->toDateString()
                ];
            
            case 'last_month':
                return [
                    'start' => $now->subMonth()->startOfMonth()->toDateString(),
                    'end' => $now->subMonth()->endOfMonth()->toDateString()
                ];
                
            case 'last_3_months':
                return [
                    'start' => $now->subMonths(3)->startOfMonth()->toDateString(),
                    'end' => $now->endOfMonth()->toDateString()
                ];
                
            case 'last_6_months':
                return [
                    'start' => $now->subMonths(6)->startOfMonth()->toDateString(),
                    'end' => $now->endOfMonth()->toDateString()
                ];
                
            case 'current_year':
                return [
                    'start' => $now->startOfYear()->toDateString(),
                    'end' => $now->endOfYear()->toDateString()
                ];
                
            case 'last_year':
                return [
                    'start' => $now->subYear()->startOfYear()->toDateString(),
                    'end' => $now->subYear()->endOfYear()->toDateString()
                ];
                
            case 'custom':
                return [
                    'start' => $startDate ?: $now->subMonth()->toDateString(),
                    'end' => $endDate ?: $now->toDateString()
                ];
                
            default:
                return [
                    'start' => $now->startOfMonth()->toDateString(),
                    'end' => $now->endOfMonth()->toDateString()
                ];
        }
    }

    /**
     * Get live metrics
     */
    private function getLiveMetrics($dateRange)
    {
        // Total users
        $totalUsers = User::count();
        
        // Active users (logged in within last 30 days)
        $activeUsers = User::where('last_login_at', '>=', Carbon::now()->subDays(30))->count();
        
        // Total deposits
        $totalDeposits = MoneyRequest::where('status', 'approved')
            ->whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
            ->sum('amount');
            
        // Total withdrawals
        $totalWithdrawals = WithdrawalRequest::where('status', 'approved')
            ->whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
            ->sum('amount');
            
        // Net revenue (deposits - withdrawals)
        $netRevenue = $totalDeposits - $totalWithdrawals;
        
        // Pending requests
        $pendingRequests = MoneyRequest::where('status', 'pending')->count() + 
                          WithdrawalRequest::where('status', 'pending')->count();

        return [
            'total_users' => $totalUsers,
            'active_users' => $activeUsers,
            'total_deposits' => $totalDeposits,
            'total_withdrawals' => $totalWithdrawals,
            'net_revenue' => $netRevenue,
            'pending_requests' => $pendingRequests
        ];
    }

    /**
     * Calculate growth rates
     */
    private function getGrowthRates($dateRange)
    {
        // Calculate previous period for comparison
        $currentStart = Carbon::parse($dateRange['start']);
        $currentEnd = Carbon::parse($dateRange['end']);
        $periodLength = $currentStart->diffInDays($currentEnd);
        
        $previousStart = $currentStart->copy()->subDays($periodLength);
        $previousEnd = $currentStart->copy()->subDay();

        // Current period data
        $currentUsers = User::whereBetween('created_at', [$currentStart, $currentEnd])->count();
        $currentDeposits = MoneyRequest::where('status', 'approved')
            ->whereBetween('created_at', [$currentStart, $currentEnd])
            ->sum('amount');
        $currentWithdrawals = WithdrawalRequest::where('status', 'approved')
            ->whereBetween('created_at', [$currentStart, $currentEnd])
            ->sum('amount');

        // Previous period data
        $previousUsers = User::whereBetween('created_at', [$previousStart, $previousEnd])->count();
        $previousDeposits = MoneyRequest::where('status', 'approved')
            ->whereBetween('created_at', [$previousStart, $previousEnd])
            ->sum('amount');
        $previousWithdrawals = WithdrawalRequest::where('status', 'approved')
            ->whereBetween('created_at', [$previousStart, $previousEnd])
            ->sum('amount');

        // Calculate growth rates
        $userGrowth = $previousUsers > 0 ? (($currentUsers - $previousUsers) / $previousUsers) * 100 : 0;
        $depositGrowth = $previousDeposits > 0 ? (($currentDeposits - $previousDeposits) / $previousDeposits) * 100 : 0;
        $withdrawalGrowth = $previousWithdrawals > 0 ? (($currentWithdrawals - $previousWithdrawals) / $previousWithdrawals) * 100 : 0;
        $revenueGrowth = ($currentDeposits - $currentWithdrawals) - ($previousDeposits - $previousWithdrawals);
        $revenueGrowthPercent = ($previousDeposits - $previousWithdrawals) > 0 ? 
            ($revenueGrowth / ($previousDeposits - $previousWithdrawals)) * 100 : 0;

        // Active user growth
        $currentActiveUsers = User::where('last_login_at', '>=', $currentStart)->count();
        $previousActiveUsers = User::whereBetween('last_login_at', [$previousStart, $previousEnd])->count();
        $activeUserGrowth = $previousActiveUsers > 0 ? (($currentActiveUsers - $previousActiveUsers) / $previousActiveUsers) * 100 : 0;

        return [
            'user_growth' => round($userGrowth, 1),
            'active_user_growth' => round($activeUserGrowth, 1),
            'deposit_growth' => round($depositGrowth, 1),
            'withdrawal_growth' => round($withdrawalGrowth, 1),
            'revenue_growth' => round($revenueGrowthPercent, 1),
            'pending_change' => 0 // Calculate based on previous pending count if needed
        ];
    }

    /**
     * Get users data
     */
    private function getUsersData($dateRange)
    {
        return User::select([
                'id', 'name', 'email', 'status', 'created_at', 'last_login_at',
                DB::raw('COALESCE((SELECT SUM(amount) FROM money_requests WHERE request_create_for = users.id AND status = "approved"), 0) as total_balance')
            ])
            ->whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
            ->orderBy('created_at', 'desc')
            ->limit(100)
            ->get();
    }

    /**
     * Get deposits data
     */
    private function getDepositsData($dateRange)
    {
        return MoneyRequest::select([
                'id', 'request_create_for as user_id', 'amount', 'status', 'description', 'created_at',
                DB::raw('CONCAT("DEP-", LPAD(id, 6, "0")) as transaction_id'),
                DB::raw('(SELECT name FROM users WHERE id = money_requests.request_create_for) as user_name')
            ])
            ->whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
            ->orderBy('created_at', 'desc')
            ->limit(100)
            ->get();
    }

    /**
     * Get withdrawals data
     */
    private function getWithdrawalsData($dateRange)
    {
        return WithdrawalRequest::select([
                'id', 'request_create_for as user_id', 'amount', 'status', 'created_at', 'updated_at',
                DB::raw('CONCAT("WTH-", LPAD(id, 6, "0")) as transaction_id'),
                DB::raw('(SELECT name FROM users WHERE id = withdrawal_requests.request_create_for) as user_name')
            ])
            ->whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
            ->orderBy('created_at', 'desc')
            ->limit(100)
            ->get();
    }

    /**
     * Get chart data
     */
    private function getChartsData($dateRange)
    {
        // User growth over time
        $userGrowth = User::select([
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as count')
            ])
            ->whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Financial data over time
        $financialData = DB::table('money_requests')
            ->select([
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(CASE WHEN status = "approved" THEN amount ELSE 0 END) as deposits')
            ])
            ->whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date')
            ->get();

        // Monthly comparison
        $monthlyData = DB::table('money_requests')
            ->select([
                DB::raw('YEAR(created_at) as year'),
                DB::raw('MONTH(created_at) as month'),
                DB::raw('SUM(CASE WHEN status = "approved" THEN amount ELSE 0 END) as deposits'),
                DB::raw('COUNT(*) as transactions')
            ])
            ->whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
            ->groupBy(['year', 'month'])
            ->orderBy('year')
            ->orderBy('month')
            ->get();

        return [
            'user_growth' => $userGrowth,
            'financial' => $financialData,
            'monthly' => $monthlyData
        ];
    }
}