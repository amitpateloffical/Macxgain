<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Force HTTPS in production
        if (env('APP_ENV') === 'production') {
            URL::forceScheme('https');
        }
        
        // Schedule TrueData WebSocket script
        $this->scheduleTrueDataWebSocket();
    }

    /**
     * Schedule TrueData WebSocket script based on market hours
     */
    private function scheduleTrueDataWebSocket(): void
    {
        Schedule::call(function () {
            $this->runTrueDataWebSocket();
        })->everyMinute()->when(function () {
            return $this->shouldRunWebSocket();
        });
    }

    /**
     * Check if WebSocket should run based on market hours
     */
    private function shouldRunWebSocket(): bool
    {
        $now = now('Asia/Kolkata');
        $hour = $now->hour;
        $minute = $now->minute;
        $dayOfWeek = $now->dayOfWeek; // 1 = Monday, 7 = Sunday
        
        // Market hours: Monday to Friday, 9:15 AM to 3:30 PM IST
        $isWeekday = $dayOfWeek >= 1 && $dayOfWeek <= 5;
        $isMarketHours = ($hour > 9 || ($hour == 9 && $minute >= 15)) && 
                        ($hour < 15 || ($hour == 15 && $minute <= 30));
        
        return $isWeekday && $isMarketHours;
    }

    /**
     * Run TrueData WebSocket script
     */
    private function runTrueDataWebSocket(): void
    {
        try {
            // Check if WebSocket script is already running
            $result = Process::run('pgrep -f "truedata_websocket.py"');
            
            if ($result->successful() && !empty(trim($result->output()))) {
                // Script is already running
                Log::info('TrueData WebSocket script is already running');
                return;
            }

            // Start the WebSocket script
            $scriptPath = base_path('truedata_websocket.py');
            
            if (!file_exists($scriptPath)) {
                Log::error('TrueData WebSocket script not found at: ' . $scriptPath);
                return;
            }

            // Run the script in background
            Process::start("python3 {$scriptPath} > /dev/null 2>&1 &");
            
            Log::info('TrueData WebSocket script started successfully');
            
        } catch (\Exception $e) {
            Log::error('Failed to start TrueData WebSocket script: ' . $e->getMessage());
        }
    }
}
