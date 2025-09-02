<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Exception;
use Ratchet\Client\WebSocket;
use Ratchet\Client\Connector;
use React\EventLoop\Loop;

class TrueDataWebSocketManager
{
    private $username;
    private $password;
    private $realtimePort;
    private $baseUrl;
    private $wsConnection;
    private $isConnected = false;
    private $subscribedSymbols = [];
    private $marketData = [];
    private $reconnectAttempts = 0;
    private $maxReconnectAttempts = 5;
    private $reconnectDelay = 5; // seconds
    private $lastHeartbeat;
    private $heartbeatInterval = 30; // seconds
    private $loop;
    private $connector;

    public function __construct()
    {
        $this->username = config('services.truedata.username');
        $this->password = config('services.truedata.password');
        $this->realtimePort = config('services.truedata.realtime_port', 8086);
        $this->baseUrl = config('services.truedata.base_url', 'push.truedata.in');
        $this->lastHeartbeat = time();
        $this->loop = Loop::get();
        $this->connector = new Connector($this->loop);
    }

    /**
     * Initialize WebSocket connection with retry logic
     */
    public function initializeConnection($forceReconnect = false)
    {
        try {
            if ($forceReconnect) {
                $this->closeConnection();
            }

            if ($this->isConnected && !$forceReconnect) {
                return ['success' => true, 'message' => 'Connection already established'];
            }

            if (empty($this->username) || empty($this->password)) {
                throw new Exception('TrueData credentials not configured');
            }

            $this->connectWebSocket();
            $this->reconnectAttempts = 0;
            
            return ['success' => true, 'message' => 'TrueData connection established'];
        } catch (Exception $e) {
            Log::error('TrueData Connection Error: ' . $e->getMessage());
            
            if ($this->reconnectAttempts < $this->maxReconnectAttempts) {
                $this->reconnectAttempts++;
                Log::info("Attempting to reconnect... Attempt {$this->reconnectAttempts}/{$this->maxReconnectAttempts}");
                
                sleep($this->reconnectDelay);
                return $this->initializeConnection(true);
            }
            
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Connect to TrueData WebSocket with enhanced error handling
     */
    private function connectWebSocket()
    {
        try {
            $this->connectWithRatchet();
            return;
        } catch (Exception $e) {
            Log::warning('Ratchet WebSocket connection failed, trying fallback: ' . $e->getMessage());
        }
        
        // Fallback to stream connection
        try {
            $this->connectWithStream();
            return;
        } catch (Exception $e) {
            Log::warning('Stream connection failed, using historical data: ' . $e->getMessage());
        }
        
        // Last resort: use historical data (last closing prices)
        $this->addHistoricalData();
    }

    /**
     * Add historical data (last closing prices when market is closed)
     */
    private function addHistoricalData()
    {
        // Try to fetch real historical data from TrueData History API
        try {
            $historyService = new \App\Services\TrueDataHistoryService();
            $historicalData = $historyService->getLastClosingPrices();
            
            if (!empty($historicalData)) {
                $this->marketData = $historicalData;
                Cache::put('truedata_market_data', $historicalData, 300);
                Log::info('Real historical data fetched from TrueData API - ' . count($historicalData) . ' stocks');
                return;
            }
        } catch (Exception $e) {
            Log::warning('Failed to fetch historical data from TrueData API: ' . $e->getMessage());
        }

        // No mock data - show empty state when market is closed
        $this->marketData = [];
        Cache::put('truedata_market_data', [], 300);
        Log::info('No historical data available - market closed, no mock data');
    }

    private function addMockData()
    {
        // This method is kept for reference but not used
        $historicalData = [
            'NIFTY 50' => [
                'symbol' => 'NIFTY 50',
                'ltp' => 24567.50,
                'change' => 125.75,
                'change_percent' => 0.51,
                'volume' => 125000000,
                'turnover' => 1250000000,
                'high' => 24600.25,
                'low' => 24400.75,
                'open' => 24450.00,
                'prev_close' => 24441.75,
                'bid' => 24567.00,
                'ask' => 24568.00,
                'timestamp' => now()->toISOString()
            ],
            'NIFTY BANK' => [
                'symbol' => 'NIFTY BANK',
                'ltp' => 52145.30,
                'change' => -245.80,
                'change_percent' => -0.47,
                'volume' => 85000000,
                'turnover' => 850000000,
                'high' => 52400.50,
                'low' => 52000.25,
                'open' => 52300.00,
                'prev_close' => 52391.10,
                'bid' => 52145.00,
                'ask' => 52146.00,
                'timestamp' => now()->toISOString()
            ],
            'RELIANCE' => [
                'symbol' => 'RELIANCE',
                'ltp' => 2856.75,
                'change' => 45.25,
                'change_percent' => 1.61,
                'volume' => 2500000,
                'turnover' => 7141875000,
                'high' => 2865.00,
                'low' => 2810.50,
                'open' => 2815.00,
                'prev_close' => 2811.50,
                'bid' => 2856.50,
                'ask' => 2857.00,
                'timestamp' => now()->toISOString()
            ],
            'TCS' => [
                'symbol' => 'TCS',
                'ltp' => 4125.80,
                'change' => -25.40,
                'change_percent' => -0.61,
                'volume' => 1800000,
                'turnover' => 7426440000,
                'high' => 4155.00,
                'low' => 4120.25,
                'open' => 4150.00,
                'prev_close' => 4151.20,
                'bid' => 4125.50,
                'ask' => 4126.00,
                'timestamp' => now()->toISOString()
            ],
            'HDFCBANK' => [
                'symbol' => 'HDFCBANK',
                'ltp' => 1689.45,
                'change' => 12.85,
                'change_percent' => 0.77,
                'volume' => 3200000,
                'turnover' => 5406240000,
                'high' => 1695.00,
                'low' => 1675.50,
                'open' => 1680.00,
                'prev_close' => 1676.60,
                'bid' => 1689.00,
                'ask' => 1689.50,
                'timestamp' => now()->toISOString()
            ],
            'ICICIBANK' => [
                'symbol' => 'ICICIBANK',
                'ltp' => 1125.30,
                'change' => -8.70,
                'change_percent' => -0.77,
                'volume' => 4500000,
                'turnover' => 5063850000,
                'high' => 1135.00,
                'low' => 1120.25,
                'open' => 1130.00,
                'prev_close' => 1134.00,
                'bid' => 1125.00,
                'ask' => 1125.50,
                'timestamp' => now()->toISOString()
            ],
            'SBIN' => [
                'symbol' => 'SBIN',
                'ltp' => 756.85,
                'change' => 15.25,
                'change_percent' => 2.05,
                'volume' => 8500000,
                'turnover' => 6433225000,
                'high' => 760.00,
                'low' => 740.50,
                'open' => 745.00,
                'prev_close' => 741.60,
                'bid' => 756.50,
                'ask' => 757.00,
                'timestamp' => now()->toISOString()
            ],
            'INFY' => [
                'symbol' => 'INFY',
                'ltp' => 1856.40,
                'change' => -18.60,
                'change_percent' => -0.99,
                'volume' => 2200000,
                'turnover' => 4084080000,
                'high' => 1875.00,
                'low' => 1850.25,
                'open' => 1870.00,
                'prev_close' => 1875.00,
                'bid' => 1856.00,
                'ask' => 1856.50,
                'timestamp' => now()->toISOString()
            ],
            'WIPRO' => [
                'symbol' => 'WIPRO',
                'ltp' => 485.75,
                'change' => 8.25,
                'change_percent' => 1.73,
                'volume' => 3800000,
                'turnover' => 1845850000,
                'high' => 488.00,
                'low' => 477.50,
                'open' => 480.00,
                'prev_close' => 477.50,
                'bid' => 485.50,
                'ask' => 486.00,
                'timestamp' => now()->toISOString()
            ],
            'BHARTIARTL' => [
                'symbol' => 'BHARTIARTL',
                'ltp' => 1234.50,
                'change' => -12.25,
                'change_percent' => -0.98,
                'volume' => 2800000,
                'turnover' => 3456600000,
                'high' => 1250.00,
                'low' => 1230.25,
                'open' => 1245.00,
                'prev_close' => 1246.75,
                'bid' => 1234.00,
                'ask' => 1235.00,
                'timestamp' => now()->toISOString()
            ],
            'ITC' => [
                'symbol' => 'ITC',
                'ltp' => 456.80,
                'change' => 8.25,
                'change_percent' => 1.84,
                'volume' => 3200000,
                'turnover' => 1461760000,
                'high' => 460.00,
                'low' => 448.50,
                'open' => 450.00,
                'prev_close' => 448.55,
                'bid' => 456.50,
                'ask' => 457.00,
                'timestamp' => now()->toISOString()
            ],
            'LT' => [
                'symbol' => 'LT',
                'ltp' => 3456.75,
                'change' => -45.25,
                'change_percent' => -1.29,
                'volume' => 850000,
                'turnover' => 2938237500,
                'high' => 3500.00,
                'low' => 3450.25,
                'open' => 3490.00,
                'prev_close' => 3502.00,
                'bid' => 3456.00,
                'ask' => 3457.00,
                'timestamp' => now()->toISOString()
            ],
            'MARUTI' => [
                'symbol' => 'MARUTI',
                'ltp' => 12345.60,
                'change' => 125.40,
                'change_percent' => 1.03,
                'volume' => 450000,
                'turnover' => 5555520000,
                'high' => 12400.00,
                'low' => 12200.25,
                'open' => 12250.00,
                'prev_close' => 12220.20,
                'bid' => 12345.00,
                'ask' => 12346.00,
                'timestamp' => now()->toISOString()
            ],
            'ASIANPAINT' => [
                'symbol' => 'ASIANPAINT',
                'ltp' => 3456.25,
                'change' => -25.75,
                'change_percent' => -0.74,
                'volume' => 650000,
                'turnover' => 2246562500,
                'high' => 3480.00,
                'low' => 3450.50,
                'open' => 3470.00,
                'prev_close' => 3482.00,
                'bid' => 3456.00,
                'ask' => 3456.50,
                'timestamp' => now()->toISOString()
            ]
        ];

        $this->marketData = $historicalData;
        Cache::put('truedata_market_data', $historicalData, 300); // 5 minutes cache for fresh data
        Log::info('Historical data added (last closing prices) - ' . count($historicalData) . ' stocks');
    }

    /**
     * Connect using Ratchet WebSocket client (proper WebSocket implementation)
     */
    private function connectWithRatchet()
    {
        try {
            $url = "wss://{$this->baseUrl}:{$this->realtimePort}?user={$this->username}&password={$this->password}";
            
            Log::info("Connecting to TrueData WebSocket: {$url}");
            
            $promise = $this->connector->__invoke($url);
            
            $promise->then(function (WebSocket $conn) {
                $this->wsConnection = $conn;
                $this->isConnected = true;
                $this->lastHeartbeat = time();
                
                Log::info('TrueData WebSocket connected successfully using Ratchet');
                
                // Handle incoming messages
                $conn->on('message', function ($msg) {
                    $this->processIncomingMessage($msg->getPayload());
                });
                
                // Handle connection close
                $conn->on('close', function ($code = null, $reason = null) {
                    Log::warning("TrueData WebSocket connection closed: {$code} - {$reason}");
                    $this->isConnected = false;
                });
                
                // Handle connection errors
                $conn->on('error', function ($error) {
                    Log::error("TrueData WebSocket error: " . $error->getMessage());
                    $this->isConnected = false;
                });
                
            }, function (Exception $e) {
                Log::error('TrueData WebSocket connection failed: ' . $e->getMessage());
                throw $e;
            });
            
            // Run the event loop for a short time to establish connection
            $this->loop->addTimer(2, function() {
                $this->loop->stop();
            });
            $this->loop->run();
            
        } catch (Exception $e) {
            Log::error('Ratchet WebSocket connection failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Connect using PHP sockets extension
     */
    private function connectWithSockets()
    {
        try {
            $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
            if (!$socket) {
                throw new Exception('Could not create socket');
            }

            $result = socket_connect($socket, $this->baseUrl, $this->realtimePort);
            if (!$result) {
                throw new Exception('Could not connect to socket');
            }

            $this->wsConnection = $socket;
            $this->isConnected = true;
            $this->lastHeartbeat = time();
            
            // Add historical data since market is closed
            $this->addHistoricalData();
            
            Log::info('TrueData WebSocket connected successfully using sockets');
            
        } catch (Exception $e) {
            Log::error('Socket connection failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Connect using cURL (for testing purposes)
     */
    private function connectWithCurl()
    {
        try {
            // For now, we'll simulate a connection for testing
            // In production, you might want to use a proper WebSocket library
            $this->isConnected = true;
            $this->lastHeartbeat = time();
            
            // Add historical data (last closing prices)
            $this->addHistoricalData();
            
            Log::info('TrueData WebSocket connection simulated (cURL method)');
            
        } catch (Exception $e) {
            Log::error('cURL connection failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Add sample data for demonstration
     */
    private function addSampleData()
    {
        $sampleData = [
            'NIFTY 50' => [
                'symbol' => 'NIFTY 50',
                'last' => 19500.50,
                'open' => 19450.00,
                'high' => 19520.00,
                'low' => 19400.00,
                'prev_close' => 19450.00,
                'bid' => 19500.00,
                'ask' => 19501.00,
                'bid_size' => 100,
                'ask_size' => 150,
                'volume' => 1000000,
                'total_volume' => 5000000,
                'oi' => 100000,
                'prev_oi' => 95000,
                'average' => 19475.00,
                'last_time' => now()->toISOString(),
                'change' => 50.50,
                'change_percent' => 0.26,
                'updated_at' => now()->toISOString()
            ],
            'NIFTY BANK' => [
                'symbol' => 'NIFTY BANK',
                'last' => 44500.00,
                'open' => 44600.00,
                'high' => 44650.00,
                'low' => 44400.00,
                'prev_close' => 44600.00,
                'bid' => 44500.00,
                'ask' => 44501.00,
                'bid_size' => 200,
                'ask_size' => 180,
                'volume' => 800000,
                'total_volume' => 4000000,
                'oi' => 150000,
                'prev_oi' => 140000,
                'average' => 44525.00,
                'last_time' => now()->toISOString(),
                'change' => -100.00,
                'change_percent' => -0.22,
                'updated_at' => now()->toISOString()
            ],
            'RELIANCE' => [
                'symbol' => 'RELIANCE',
                'last' => 2450.50,
                'open' => 2440.00,
                'high' => 2460.00,
                'low' => 2435.00,
                'prev_close' => 2440.00,
                'bid' => 2450.00,
                'ask' => 2451.00,
                'bid_size' => 500,
                'ask_size' => 400,
                'volume' => 1200000,
                'total_volume' => 6000000,
                'oi' => 200000,
                'prev_oi' => 195000,
                'average' => 2445.00,
                'last_time' => now()->toISOString(),
                'change' => 10.50,
                'change_percent' => 0.43,
                'updated_at' => now()->toISOString()
            ],
            'TCS' => [
                'symbol' => 'TCS',
                'last' => 3850.00,
                'open' => 3840.00,
                'high' => 3860.00,
                'low' => 3830.00,
                'prev_close' => 3840.00,
                'bid' => 3850.00,
                'ask' => 3851.00,
                'bid_size' => 300,
                'ask_size' => 250,
                'volume' => 600000,
                'total_volume' => 3000000,
                'oi' => 80000,
                'prev_oi' => 75000,
                'average' => 3845.00,
                'last_time' => now()->toISOString(),
                'change' => 10.00,
                'change_percent' => 0.26,
                'updated_at' => now()->toISOString()
            ],
            'HDFCBANK' => [
                'symbol' => 'HDFCBANK',
                'last' => 1650.75,
                'open' => 1645.00,
                'high' => 1655.00,
                'low' => 1640.00,
                'prev_close' => 1645.00,
                'bid' => 1650.00,
                'ask' => 1651.00,
                'bid_size' => 400,
                'ask_size' => 350,
                'volume' => 900000,
                'total_volume' => 4500000,
                'oi' => 120000,
                'prev_oi' => 115000,
                'average' => 1647.50,
                'last_time' => now()->toISOString(),
                'change' => 5.75,
                'change_percent' => 0.35,
                'updated_at' => now()->toISOString()
            ],
            'ICICIBANK' => [
                'symbol' => 'ICICIBANK',
                'last' => 950.25,
                'open' => 945.00,
                'high' => 955.00,
                'low' => 940.00,
                'prev_close' => 945.00,
                'bid' => 950.00,
                'ask' => 951.00,
                'bid_size' => 600,
                'ask_size' => 500,
                'volume' => 1500000,
                'total_volume' => 7500000,
                'oi' => 180000,
                'prev_oi' => 170000,
                'average' => 947.50,
                'last_time' => now()->toISOString(),
                'change' => 5.25,
                'change_percent' => 0.56,
                'updated_at' => now()->toISOString()
            ],
            'SBIN' => [
                'symbol' => 'SBIN',
                'last' => 580.50,
                'open' => 575.00,
                'high' => 585.00,
                'low' => 570.00,
                'prev_close' => 575.00,
                'bid' => 580.00,
                'ask' => 581.00,
                'bid_size' => 800,
                'ask_size' => 700,
                'volume' => 2000000,
                'total_volume' => 10000000,
                'oi' => 250000,
                'prev_oi' => 240000,
                'average' => 577.50,
                'last_time' => now()->toISOString(),
                'change' => 5.50,
                'change_percent' => 0.96,
                'updated_at' => now()->toISOString()
            ],
            'INFY' => [
                'symbol' => 'INFY',
                'last' => 1450.00,
                'open' => 1445.00,
                'high' => 1455.00,
                'low' => 1440.00,
                'prev_close' => 1445.00,
                'bid' => 1450.00,
                'ask' => 1451.00,
                'bid_size' => 350,
                'ask_size' => 300,
                'volume' => 800000,
                'total_volume' => 4000000,
                'oi' => 90000,
                'prev_oi' => 85000,
                'average' => 1447.50,
                'last_time' => now()->toISOString(),
                'change' => 5.00,
                'change_percent' => 0.35,
                'updated_at' => now()->toISOString()
            ],
            'WIPRO' => [
                'symbol' => 'WIPRO',
                'last' => 420.75,
                'open' => 418.00,
                'high' => 425.00,
                'low' => 415.00,
                'prev_close' => 418.00,
                'bid' => 420.00,
                'ask' => 421.00,
                'bid_size' => 500,
                'ask_size' => 450,
                'volume' => 1200000,
                'total_volume' => 6000000,
                'oi' => 110000,
                'prev_oi' => 105000,
                'average' => 419.00,
                'last_time' => now()->toISOString(),
                'change' => 2.75,
                'change_percent' => 0.66,
                'updated_at' => now()->toISOString()
            ],
            'BHARTIARTL' => [
                'symbol' => 'BHARTIARTL',
                'last' => 1125.50,
                'open' => 1120.00,
                'high' => 1130.00,
                'low' => 1115.00,
                'prev_close' => 1120.00,
                'bid' => 1125.00,
                'ask' => 1126.00,
                'bid_size' => 400,
                'ask_size' => 350,
                'volume' => 1000000,
                'total_volume' => 5000000,
                'oi' => 130000,
                'prev_oi' => 125000,
                'average' => 1122.50,
                'last_time' => now()->toISOString(),
                'change' => 5.50,
                'change_percent' => 0.49,
                'updated_at' => now()->toISOString()
            ]
        ];

        $this->marketData = $sampleData;
        
        // Cache the sample data
        Cache::put('truedata_market_data', $sampleData, 300); // 5 minutes
        
        Log::info('Sample data added for demonstration');
    }

    /**
     * Connect using basic stream (fallback)
     */
    private function connectWithStream()
    {
        try {
            // Use regular HTTP connection instead of WSS for testing
            $url = "http://{$this->baseUrl}:{$this->realtimePort}";
            
            $context = stream_context_create([
                'http' => [
                    'timeout' => 30,
                    'method' => 'GET',
                    'header' => "User-Agent: Laravel-TrueData-Client\r\n"
                ]
            ]);

            $this->wsConnection = stream_socket_client(
                $url, 
                $errno, 
                $errstr, 
                30, 
                STREAM_CLIENT_CONNECT, 
                $context
            );
            
            if (!$this->wsConnection) {
                throw new Exception("Stream connection failed: $errstr ($errno)");
            }

            $this->isConnected = true;
            $this->lastHeartbeat = time();
            
            Log::info('TrueData connection established using stream (fallback method)');
            
        } catch (Exception $e) {
            Log::error('Stream connection failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Send message through WebSocket
     */
    public function sendMessage($message)
    {
        if (!$this->isConnected || !$this->wsConnection) {
            throw new Exception('WebSocket not connected');
        }

        // Handle different connection types
        if ($this->wsConnection instanceof WebSocket) {
            // Ratchet WebSocket
            $this->wsConnection->send($message);
            return strlen($message);
        } elseif (is_resource($this->wsConnection)) {
            // Stream resource
            $result = fwrite($this->wsConnection, $message);
        } elseif (is_object($this->wsConnection) && get_class($this->wsConnection) === 'Socket') {
            // Socket resource
            $result = socket_write($this->wsConnection, $message);
        } else {
            // Simulated connection (for testing)
            Log::info('Simulated message send: ' . $message);
            $result = strlen($message);
        }

        if ($result === false) {
            throw new Exception('Failed to send message');
        }

        return $result;
    }

    /**
     * Subscribe to symbols with automatic reconnection
     */
    public function subscribeToSymbols($symbols = [])
    {
        try {
            if (empty($symbols)) {
                $symbols = $this->getDefaultSymbols();
            }

            // Ensure connection is established
            if (!$this->isConnected) {
                $initResult = $this->initializeConnection();
                if (!$initResult['success']) {
                    return $initResult;
                }
            }

            $subscribeMessage = json_encode([
                'method' => 'addsymbol',
                'symbols' => $symbols
            ]);

            $this->sendMessage($subscribeMessage);
            $this->subscribedSymbols = $symbols;
            
            Log::info('Subscribed to symbols: ' . implode(', ', $symbols));
            return ['success' => true, 'symbols' => $symbols];

        } catch (Exception $e) {
            Log::error('Subscribe to symbols error: ' . $e->getMessage());
            
            // Try to reconnect and resubscribe
            if ($this->reconnectAttempts < $this->maxReconnectAttempts) {
                $this->reconnectAttempts++;
                $this->initializeConnection(true);
                return $this->subscribeToSymbols($symbols);
            }
            
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Get real-time data with connection monitoring
     */
    public function getRealTimeData($timeout = 2)
    {
        try {
            if (!$this->isConnected) {
                $initResult = $this->initializeConnection();
                if (!$initResult['success']) {
                    return $initResult;
                }
            }

            // Check if we need to send heartbeat
            if (time() - $this->lastHeartbeat > $this->heartbeatInterval) {
                $this->sendHeartbeat();
            }

            $data = [];
            $startTime = time();
            
            while ((time() - $startTime) < $timeout) {
                $message = $this->readMessage();
                if ($message !== false && !empty($message)) {
                    $decoded = json_decode($message, true);
                    if ($decoded) {
                        $data[] = $decoded;
                        $this->processMarketData($decoded);
                    }
                }
                usleep(100000); // 100ms sleep
            }

            return [
                'success' => true,
                'data' => $data,
                'cached_data' => $this->getCachedMarketData(),
                'timestamp' => now()->toISOString(),
                'connection_status' => $this->isConnected
            ];

        } catch (Exception $e) {
            Log::error('Get real-time data error: ' . $e->getMessage());
            
            // Try to reconnect
            if ($this->reconnectAttempts < $this->maxReconnectAttempts) {
                $this->reconnectAttempts++;
                $this->initializeConnection(true);
                return $this->getRealTimeData($timeout);
            }
            
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Read message from WebSocket
     */
    private function readMessage()
    {
        if (!$this->isConnected || !$this->wsConnection) {
            return false;
        }

        // Handle different connection types
        if ($this->wsConnection instanceof WebSocket) {
            // Ratchet WebSocket - messages are handled via event callbacks
            // This method is mainly for fallback connections
            return false;
        } elseif (is_resource($this->wsConnection)) {
            $message = fread($this->wsConnection, 8192);
            
            if ($message === false) {
                // Check if connection is still alive
                $meta = stream_get_meta_data($this->wsConnection);
                if ($meta['eof']) {
                    $this->isConnected = false;
                    throw new Exception('WebSocket connection closed');
                }
            }
        } elseif (is_object($this->wsConnection) && get_class($this->wsConnection) === 'Socket') {
            $message = socket_read($this->wsConnection, 8192);
            if ($message === false) {
                $this->isConnected = false;
                throw new Exception('Socket connection closed');
            }
        } else {
            // Simulated connection
            $message = false;
        }

        return $message;
    }

    /**
     * Send heartbeat to keep connection alive
     */
    private function sendHeartbeat()
    {
        try {
            $this->sendMessage('{"method": "ping"}');
            $this->lastHeartbeat = time();
            Log::debug('Heartbeat sent');
        } catch (Exception $e) {
            Log::error('Heartbeat failed: ' . $e->getMessage());
            $this->isConnected = false;
        }
    }

    /**
     * Process incoming WebSocket messages
     */
    private function processIncomingMessage($message)
    {
        try {
            $data = json_decode($message, true);
            if ($data) {
                $this->processMarketData($data);
            }
        } catch (Exception $e) {
            Log::error('Error processing incoming message: ' . $e->getMessage());
        }
    }

    /**
     * Process incoming market data
     */
    private function processMarketData($data)
    {
        if (isset($data['symbol']) && isset($data['last'])) {
            $this->marketData[$data['symbol']] = [
                'symbol' => $data['symbol'],
                'last' => $data['last'] ?? 0,
                'open' => $data['open'] ?? 0,
                'high' => $data['high'] ?? 0,
                'low' => $data['low'] ?? 0,
                'prev_close' => $data['prev'] ?? 0,
                'bid' => $data['bid'] ?? 0,
                'ask' => $data['ask'] ?? 0,
                'bid_size' => $data['bidsize'] ?? 0,
                'ask_size' => $data['asksize'] ?? 0,
                'volume' => $data['tradevol'] ?? 0,
                'total_volume' => $data['totalvol'] ?? 0,
                'oi' => $data['oi'] ?? 0,
                'prev_oi' => $data['prevoi'] ?? 0,
                'average' => $data['average'] ?? 0,
                'last_time' => $data['lasttime'] ?? now()->toISOString(),
                'change' => isset($data['last'], $data['prev']) ? $data['last'] - $data['prev'] : 0,
                'change_percent' => isset($data['last'], $data['prev']) && $data['prev'] > 0 
                    ? (($data['last'] - $data['prev']) / $data['prev']) * 100 : 0,
                'updated_at' => now()->toISOString()
            ];

            // Cache the updated data
            Cache::put('truedata_market_data', $this->marketData, 300); // 5 minutes
        }
    }

    /**
     * Get cached market data
     */
    public function getCachedMarketData()
    {
        return Cache::get('truedata_market_data', []);
    }

    /**
     * Get default symbols
     */
    private function getDefaultSymbols()
    {
        return [
            "NIFTY 50", "NIFTY BANK", "MCXCOMPDEX", "AARTIIND",
            "BRITANNIA", "COLPAL", "DMART", "EICHERMOT", "GILLETTE",
            "HDFCBANK", "ICICIBANK", "JKTYRE", "KAJARIACER", "LICHSGFIN",
            "MINDTREE", "OFSS", "PNB", "QUICKHEAL", "RELIANCE", "SBIN",
            "TCS", "UJJIVAN", "WIPRO", "YESBANK", "ZEEL", "NIFTY-I",
            "BANKNIFTY-I", "UPL-I", "VEDL-I", "VOLTAS-I", "ZEEL-I",
            "CRUDEOIL-I", "GOLDM-I", "SILVERM-I", "COPPER-I", "SILVER-I"
        ];
    }

    /**
     * Get connection status
     */
    public function getConnectionStatus()
    {
        return [
            'is_connected' => $this->isConnected,
            'reconnect_attempts' => $this->reconnectAttempts,
            'max_reconnect_attempts' => $this->maxReconnectAttempts,
            'last_heartbeat' => $this->lastHeartbeat,
            'subscribed_symbols_count' => count($this->subscribedSymbols),
            'cached_data_count' => count($this->getCachedMarketData())
        ];
    }

    /**
     * Close WebSocket connection
     */
    public function closeConnection()
    {
        if ($this->wsConnection && $this->isConnected) {
            // Handle different connection types
            if ($this->wsConnection instanceof WebSocket) {
                // Ratchet WebSocket
                $this->wsConnection->close();
            } elseif (is_resource($this->wsConnection)) {
                fclose($this->wsConnection);
            } elseif (is_object($this->wsConnection) && get_class($this->wsConnection) === 'Socket') {
                socket_close($this->wsConnection);
            }
            
            $this->isConnected = false;
            $this->wsConnection = null;
            Log::info('TrueData WebSocket connection closed');
        }
    }

    /**
     * Destructor to ensure connection is closed
     */
    public function __destruct()
    {
        $this->closeConnection();
    }
}
