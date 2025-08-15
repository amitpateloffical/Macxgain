<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\MoneyRequest;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ChartController extends Controller
{
    public function index()
    {
        //
    }

    public function getData()
    {
        $labels = ['0-1 Hours', '1-8 Hours', '8-24 Hours', '>24 Hours', 'No Replies'];
        $data = [81, 9, 4, 4, 2];

        return response()->json([
            'labels' => $labels,
            'data' => $data
        ]);
    }

    /**
     * Get comprehensive analytics data
     */
    public function getAnalytics(Request $request)
    {
        try {
            $period = $request->get('period', 'current_month');
            $startDate = $request->get('start_date');
            $endDate = $request->get('end_date');

            // Calculate date range based on period
            $dateRange = $this->calculateDateRange($period, $startDate, $endDate);
            $start = $dateRange['start'];
            $end = $dateRange['end'];

            // Get metrics for current period
            $currentMetrics = $this->getMetricsForPeriod($start, $end);
            
            // Get metrics for previous period for growth calculation
            $previousStart = Carbon::parse($start)->subDays($start->diffInDays($end))->startOfDay();
            $previousEnd = Carbon::parse($start)->subDay()->endOfDay();
            $previousMetrics = $this->getMetricsForPeriod($previousStart, $previousEnd);

            // Calculate growth rates
            $growthRates = $this->calculateGrowthRates($currentMetrics, $previousMetrics);

            // Get detailed data
            $users = $this->getUsersData($start, $end);
            $deposits = $this->getDepositsData($start, $end);
            $withdrawals = $this->getWithdrawalsData($start, $end);

            // Get chart data
            $charts = $this->getChartData($start, $end);

            return response()->json([
                'success' => true,
                'data' => [
                    'period' => $period,
                    'date_range' => [
                        'start' => $start->toISOString(),
                        'end' => $end->toISOString()
                    ],
                    'metrics' => $currentMetrics,
                    'growth_rates' => $growthRates,
                    'users' => $users,
                    'deposits' => $deposits,
                    'withdrawals' => $withdrawals,
                    'charts' => $charts
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch analytics data: ' . $e->getMessage()
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
                $start = $now->copy()->startOfMonth();
                $end = $now->copy()->endOfMonth();
                break;
            case 'last_month':
                $start = $now->copy()->subMonth()->startOfMonth();
                $end = $now->copy()->subMonth()->endOfMonth();
                break;
            case 'last_3_months':
                $start = $now->copy()->subMonths(3)->startOfMonth();
                $end = $now->copy()->endOfMonth();
                break;
            case 'last_6_months':
                $start = $now->copy()->subMonths(6)->startOfMonth();
                $end = $now->copy()->endOfMonth();
                break;
            case 'current_year':
                $start = $now->copy()->startOfYear();
                $end = $now->copy()->endOfYear();
                break;
            case 'last_year':
                $start = $now->copy()->subYear()->startOfYear();
                $end = $now->copy()->subYear()->endOfYear();
                break;
            case 'custom':
                $start = Carbon::parse($startDate)->startOfDay();
                $end = Carbon::parse($endDate)->endOfDay();
                break;
            default:
                $start = $now->copy()->startOfMonth();
                $end = $now->copy()->endOfMonth();
        }

        return ['start' => $start, 'end' => $end];
    }

    /**
     * Get metrics for a specific period
     */
    private function getMetricsForPeriod($start, $end)
    {
        // Total users (all time)
        $totalUsers = User::count();
        
        // Active users (users who logged in within last 30 days)
        $activeUsers = User::where('last_login_at', '>=', Carbon::now()->subDays(30))->count();
        
        // Total deposits in period
        $totalDeposits = WalletTransaction::where('type', 'deposit')
            ->whereBetween('created_at', [$start, $end])
            ->sum('amount');
        
        // Total withdrawals in period
        $totalWithdrawals = WalletTransaction::where('type', 'withdrawal')
            ->whereBetween('created_at', [$start, $end])
            ->sum('amount');
        
        // Net revenue (deposits - withdrawals)
        $netRevenue = $totalDeposits - $totalWithdrawals;
        
        // Pending requests
        $pendingRequests = MoneyRequest::where('status', 'pending')->count();

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
    private function calculateGrowthRates($current, $previous)
    {
        $calculateGrowth = function($current, $previous) {
            if ($previous == 0) return $current > 0 ? 100 : 0;
            return round((($current - $previous) / $previous) * 100, 1);
        };

        return [
            'user_growth' => $calculateGrowth($current['total_users'], $previous['total_users']),
            'active_user_growth' => $calculateGrowth($current['active_users'], $previous['active_users']),
            'deposit_growth' => $calculateGrowth($current['total_deposits'], $previous['total_deposits']),
            'withdrawal_growth' => $calculateGrowth($current['total_withdrawals'], $previous['total_withdrawals']),
            'revenue_growth' => $calculateGrowth($current['net_revenue'], $previous['net_revenue']),
            'pending_change' => $calculateGrowth($current['pending_requests'], $previous['pending_requests'])
        ];
    }

    /**
     * Get users data
     */
    private function getUsersData($start, $end)
    {
        return User::select([
            'id', 'name', 'email', 'status', 'created_at', 'last_login_at'
        ])
        ->whereBetween('created_at', [$start, $end])
        ->orderBy('created_at', 'desc')
        ->get()
        ->map(function ($user) {
            $user->total_balance = WalletTransaction::where('user_id', $user->id)
                ->orderBy('id', 'desc')
                ->value('running_balance') ?? 0;
            return $user;
        });
    }

    /**
     * Get deposits data
     */
    private function getDepositsData($start, $end)
    {
        return WalletTransaction::where('type', 'deposit')
            ->whereBetween('created_at', [$start, $end])
            ->with('user:id,name')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($transaction) {
                return [
                    'id' => $transaction->id,
                    'transaction_id' => $transaction->transaction_code,
                    'user_name' => $transaction->user->name ?? 'Unknown',
                    'amount' => $transaction->amount,
                    'status' => 'completed', // Assuming all wallet transactions are completed
                    'created_at' => $transaction->created_at,
                    'description' => $transaction->remark
                ];
            });
    }

    /**
     * Get withdrawals data
     */
    private function getWithdrawalsData($start, $end)
    {
        return WalletTransaction::where('type', 'withdrawal')
            ->whereBetween('created_at', [$start, $end])
            ->with('user:id,name')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($transaction) {
                return [
                    'id' => $transaction->id,
                    'transaction_id' => $transaction->transaction_code,
                    'user_name' => $transaction->user->name ?? 'Unknown',
                    'amount' => $transaction->amount,
                    'status' => 'completed', // Assuming all wallet transactions are completed
                    'created_at' => $transaction->created_at,
                    'updated_at' => $transaction->updated_at
                ];
            });
    }

    /**
     * Get chart data
     */
    private function getChartData($start, $end)
    {
        // User growth trend (last 7 days)
        $userGrowth = $this->getUserGrowthData($start, $end);
        
        // Financial overview
        $financial = [
            'deposits' => WalletTransaction::where('type', 'deposit')
                ->whereBetween('created_at', [$start, $end])
                ->sum('amount'),
            'withdrawals' => WalletTransaction::where('type', 'withdrawal')
                ->whereBetween('created_at', [$start, $end])
                ->sum('amount'),
            'net_revenue' => WalletTransaction::where('type', 'deposit')
                ->whereBetween('created_at', [$start, $end])
                ->sum('amount') - WalletTransaction::where('type', 'withdrawal')
                ->whereBetween('created_at', [$start, $end])
                ->sum('amount')
        ];
        
        // Monthly comparison (last 6 months)
        $monthly = $this->getMonthlyComparisonData($start, $end);

        return [
            'user_growth' => $userGrowth,
            'financial' => $financial,
            'monthly' => $monthly
        ];
    }

    /**
     * Get user growth data
     */
    private function getUserGrowthData($start, $end)
    {
        $days = $start->diffInDays($end) + 1;
        $labels = [];
        $values = [];

        for ($i = 0; $i < $days; $i++) {
            $date = $start->copy()->addDays($i);
            $labels[] = $date->format('M d');
            
            $count = User::whereDate('created_at', $date)->count();
            $values[] = $count;
        }

        return [
            'labels' => $labels,
            'values' => $values
        ];
    }

    /**
     * Get monthly comparison data
     */
    private function getMonthlyComparisonData($start, $end)
    {
        $months = [];
        $deposits = [];
        $withdrawals = [];

        $current = $start->copy()->startOfMonth();
        
        while ($current <= $end) {
            $months[] = $current->format('M Y');
            
            $monthStart = $current->copy()->startOfMonth();
            $monthEnd = $current->copy()->endOfMonth();
            
            $deposits[] = WalletTransaction::where('type', 'deposit')
                ->whereBetween('created_at', [$monthStart, $monthEnd])
                ->sum('amount');
                
            $withdrawals[] = WalletTransaction::where('type', 'withdrawal')
                ->whereBetween('created_at', [$monthStart, $monthEnd])
                ->sum('amount');
            
            $current->addMonth();
        }

        return [
            'labels' => $months,
            'deposits' => $deposits,
            'withdrawals' => $withdrawals
        ];
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        //
    }

    public function show(string $id)
    {
        //
    }

    public function edit(string $id)
    {
        //
    }

    public function update(Request $request, string $id)
    {
        //
    }

    public function destroy(string $id)
    {
        //
    }
}
