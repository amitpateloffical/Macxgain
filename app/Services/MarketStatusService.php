<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class MarketStatusService
{
    /**
     * Get current market status based on time and TrueData API
     */
    public function getMarketStatus()
    {
        $now = Carbon::now('Asia/Kolkata');
        $currentTime = $now->format('H:i:s');
        $currentDate = $now->format('Y-m-d');
        
        // Check if it's a trading day (Monday to Friday)
        if ($now->isWeekend()) {
            return [
                'status' => 'CLOSED',
                'reason' => 'Weekend',
                'next_open' => $this->getNextTradingDay($now),
                'trading_hours' => '9:15 AM - 3:30 PM IST',
                'current_time' => $currentTime,
                'current_date' => $currentDate
            ];
        }
        
        // Define market sessions
        $sessions = $this->getMarketSessions();
        
        foreach ($sessions as $session) {
            if ($this->isTimeInRange($currentTime, $session['start'], $session['end'])) {
                return [
                    'status' => $session['status'],
                    'reason' => $session['reason'],
                    'session' => $session['name'],
                    'next_change' => $session['next_change'],
                    'trading_hours' => '9:15 AM - 3:30 PM IST',
                    'current_time' => $currentTime,
                    'current_date' => $currentDate,
                    'is_live' => $session['is_live']
                ];
            }
        }
        
        // Market is closed
        return [
            'status' => 'CLOSED',
            'reason' => 'Outside trading hours',
            'next_open' => $this->getNextTradingDay($now),
            'trading_hours' => '9:15 AM - 3:30 PM IST',
            'current_time' => $currentTime,
            'current_date' => $currentDate
        ];
    }
    
    /**
     * Get market sessions based on TrueData documentation
     */
    private function getMarketSessions()
    {
        return [
            [
                'name' => 'Pre-Open',
                'start' => '09:00:00',
                'end' => '09:15:00',
                'status' => 'PRE_OPEN',
                'reason' => 'Pre-market session',
                'is_live' => false,
                'next_change' => 'Market opens at 9:15 AM'
            ],
            [
                'name' => 'Normal Trading',
                'start' => '09:15:00',
                'end' => '15:30:00',
                'status' => 'OPEN',
                'reason' => 'Normal trading hours',
                'is_live' => true,
                'next_change' => 'Market closes at 3:30 PM'
            ],
            [
                'name' => 'Post-Close',
                'start' => '15:30:00',
                'end' => '16:00:00',
                'status' => 'POST_CLOSE',
                'reason' => 'Post-market session',
                'is_live' => false,
                'next_change' => 'Market closed for the day'
            ]
        ];
    }
    
    /**
     * Check if current time is within a range
     */
    private function isTimeInRange($currentTime, $startTime, $endTime)
    {
        return $currentTime >= $startTime && $currentTime <= $endTime;
    }
    
    /**
     * Get next trading day
     */
    private function getNextTradingDay($currentDate)
    {
        $nextDay = $currentDate->copy()->addDay();
        
        // Skip weekends
        while ($nextDay->isWeekend()) {
            $nextDay->addDay();
        }
        
        return $nextDay->format('Y-m-d') . ' at 9:15 AM IST';
    }
    
    /**
     * Check if market is currently live (for real-time data)
     */
    public function isMarketLive()
    {
        $status = $this->getMarketStatus();
        return $status['status'] === 'OPEN' && ($status['is_live'] ?? false);
    }
    
    /**
     * Get market status from TrueData API (if available)
     */
    public function getTrueDataMarketStatus()
    {
        try {
            // This would be called from WebSocket connection
            // For now, return our calculated status
            return $this->getMarketStatus();
        } catch (\Exception $e) {
            Log::error('Error getting TrueData market status: ' . $e->getMessage());
            return $this->getMarketStatus();
        }
    }
    
    /**
     * Get data refresh interval based on market status
     */
    public function getDataRefreshInterval()
    {
        $status = $this->getMarketStatus();
        
        switch ($status['status']) {
            case 'OPEN':
                return 5; // 5 seconds during market hours
            case 'PRE_OPEN':
            case 'POST_CLOSE':
                return 30; // 30 seconds during pre/post market
            default:
                return 300; // 5 minutes when market is closed
        }
    }
    
    /**
     * Get appropriate data source message
     */
    public function getDataSourceMessage()
    {
        $status = $this->getMarketStatus();
        
        if ($status['status'] === 'OPEN') {
            return 'TrueData Live Market Data';
        } elseif (in_array($status['status'], ['PRE_OPEN', 'POST_CLOSE'])) {
            return 'TrueData Pre/Post Market Data';
        } else {
            return 'TrueData Market Closed - Last Updated Data';
        }
    }
}
