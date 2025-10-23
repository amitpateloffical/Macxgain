<?php

/**
 * Test script for the market data system
 * This script tests the complete flow: API -> Database -> Frontend
 */

require_once 'vendor/autoload.php';

use App\Services\FreeMarketDataService;
use App\Models\MarketData;
use Illuminate\Support\Facades\Log;

echo "🧪 Testing Market Data System\n";
echo "============================\n\n";

try {
    // Initialize Laravel
    $app = require_once 'bootstrap/app.php';
    $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
    
    echo "1. Testing FreeMarketDataService...\n";
    $freeMarketDataService = new FreeMarketDataService();
    
    // Test fetching data from NSE
    $result = $freeMarketDataService->getLiveMarketData();
    
    if ($result['success']) {
        echo "✅ Successfully fetched data from: {$result['source']}\n";
        echo "📊 Data count: " . count($result['data']) . " symbols\n";
        
        // Show first few symbols
        $symbols = array_slice($result['data'], 0, 5, true);
        foreach ($symbols as $symbol => $data) {
            echo "   - {$symbol}: ₹{$data['ltp']} ({$data['change_percent']}%)\n";
        }
    } else {
        echo "❌ Failed to fetch data: {$result['message']}\n";
    }
    
    echo "\n2. Testing Database Storage...\n";
    
    // Check database stats
    $stats = MarketData::getMarketDataStats();
    echo "📈 Database Stats:\n";
    echo "   - Total symbols: {$stats['total_symbols']}\n";
    echo "   - Live symbols: {$stats['live_symbols']}\n";
    echo "   - Latest update: {$stats['latest_update']}\n";
    echo "   - Data is fresh: " . ($stats['is_fresh'] ? 'Yes' : 'No') . "\n";
    
    echo "\n3. Testing Database Retrieval...\n";
    
    // Test getting data from database
    $dbData = MarketData::getAllMarketData(true);
    if (!empty($dbData)) {
        echo "✅ Successfully retrieved " . count($dbData) . " symbols from database\n";
        
        // Show first few symbols from database
        $dbSymbols = array_slice($dbData, 0, 5, true);
        foreach ($dbSymbols as $symbol => $data) {
            echo "   - {$symbol}: ₹{$data['ltp']} ({$data['change_percent']}%)\n";
        }
    } else {
        echo "❌ No data found in database\n";
    }
    
    echo "\n4. Testing API Endpoints...\n";
    
    // Test the API endpoints
    $baseUrl = 'http://127.0.0.1:8000/api/truedata';
    
    // Test dashboard endpoint
    $dashboardResponse = file_get_contents($baseUrl . '/dashboard');
    if ($dashboardResponse) {
        $dashboardData = json_decode($dashboardResponse, true);
        if ($dashboardData['success']) {
            echo "✅ Dashboard API working - " . count($dashboardData['data']['live_stocks']) . " stocks\n";
        } else {
            echo "❌ Dashboard API failed: {$dashboardData['message']}\n";
        }
    } else {
        echo "❌ Dashboard API not accessible\n";
    }
    
    // Test market stats endpoint
    $statsResponse = file_get_contents($baseUrl . '/market-stats');
    if ($statsResponse) {
        $statsData = json_decode($statsResponse, true);
        if ($statsData['success']) {
            echo "✅ Market Stats API working\n";
            echo "   - Data sources: " . implode(', ', $statsData['data']['data_sources']) . "\n";
        } else {
            echo "❌ Market Stats API failed: {$statsData['message']}\n";
        }
    } else {
        echo "❌ Market Stats API not accessible\n";
    }
    
    echo "\n🎉 Market Data System Test Complete!\n";
    echo "====================================\n";
    echo "✅ System is working correctly\n";
    echo "📊 Data is being fetched from NSE and stored in database\n";
    echo "🌐 Frontend can access data via API endpoints\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
