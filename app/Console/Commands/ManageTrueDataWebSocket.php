<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Log;

class ManageTrueDataWebSocket extends Command
{
    protected $signature = 'truedata:websocket {action : start|stop|status|restart}';
    protected $description = 'Manage TrueData WebSocket script (start, stop, status, restart)';

    public function handle()
    {
        $action = $this->argument('action');
        
        switch ($action) {
            case 'start':
                $this->startWebSocket();
                break;
            case 'stop':
                $this->stopWebSocket();
                break;
            case 'status':
                $this->checkStatus();
                break;
            case 'restart':
                $this->restartWebSocket();
                break;
            default:
                $this->error('Invalid action. Use: start, stop, status, or restart');
                return 1;
        }
        
        return 0;
    }

    private function startWebSocket()
    {
        try {
            // Check if already running
            $result = Process::run('pgrep -f "truedata_websocket.py"');
            
            if ($result->successful() && !empty(trim($result->output()))) {
                $this->info('✅ TrueData WebSocket script is already running');
                return;
            }

            // Start the script
            $scriptPath = base_path('truedata_websocket.py');
            
            if (!file_exists($scriptPath)) {
                $this->error('❌ TrueData WebSocket script not found at: ' . $scriptPath);
                return;
            }

            Process::start("python3 {$scriptPath} > /dev/null 2>&1 &");
            
            $this->info('🚀 TrueData WebSocket script started successfully');
            Log::info('TrueData WebSocket script started manually via command');
            
        } catch (\Exception $e) {
            $this->error('❌ Failed to start WebSocket script: ' . $e->getMessage());
            Log::error('Failed to start TrueData WebSocket script: ' . $e->getMessage());
        }
    }

    private function stopWebSocket()
    {
        try {
            $result = Process::run('pkill -f "truedata_websocket.py"');
            
            if ($result->successful()) {
                $this->info('🛑 TrueData WebSocket script stopped successfully');
                Log::info('TrueData WebSocket script stopped manually via command');
            } else {
                $this->warn('⚠️  No running WebSocket script found to stop');
            }
            
        } catch (\Exception $e) {
            $this->error('❌ Failed to stop WebSocket script: ' . $e->getMessage());
            Log::error('Failed to stop TrueData WebSocket script: ' . $e->getMessage());
        }
    }

    private function checkStatus()
    {
        try {
            $result = Process::run('pgrep -f "truedata_websocket.py"');
            
            if ($result->successful() && !empty(trim($result->output()))) {
                $pid = trim($result->output());
                $this->info("✅ TrueData WebSocket script is running (PID: {$pid})");
                
                // Get process details
                $psResult = Process::run("ps -p {$pid} -o pid,ppid,etime,command");
                if ($psResult->successful()) {
                    $this->line($psResult->output());
                }
            } else {
                $this->warn('⚠️  TrueData WebSocket script is not running');
            }
            
        } catch (\Exception $e) {
            $this->error('❌ Failed to check WebSocket status: ' . $e->getMessage());
        }
    }

    private function restartWebSocket()
    {
        $this->info('🔄 Restarting TrueData WebSocket script...');
        $this->stopWebSocket();
        sleep(2); // Wait 2 seconds
        $this->startWebSocket();
    }
}
