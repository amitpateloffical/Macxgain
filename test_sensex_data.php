<?php

/**
 * Test script specifically for SENSEX data
 */

require_once 'vendor/autoload.php';

use App\Services\FreeMarketDataService;
use App\Models\MarketData;

echo "🧪 Testing SENSEX Data\n";
echo "=====================\n\n";

try {
    // Initialize Laravel
    $app = require_once 'bootstrap/app.php';
    $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
    
    echo "1. Testing FreeMarketDataService for SENSEX...\n";
    $freeMarketDataService = new FreeMarketDataService();
    
    // Test fetching data
    $result = $freeMarketDataService->getLiveMarketData();
    
    if ($result['success']) {
        echo "✅ Successfully fetched data from: {$result['source']}\n";
        echo "📊 Total symbols: " . count($result['data']) . "\n";
        
        // Check specifically for SENSEX
        if (isset($result['data']['SENSEX'])) {
            $sensex = $result['data']['SENSEX'];
            echo "🎯 SENSEX found!\n";
            echo "   - Price: ₹{$sensex['ltp']}\n";
            echo "   - Change: {$sensex['change']} ({$sensex['change_percent']}%)\n";
            echo "   - High: ₹{$sensex['high']}\n";
            echo "   - Low: ₹{$sensex['low']}\n";
            echo "   - Source: {$sensex['data_source']}\n";
        } else {
            echo "❌ SENSEX not found in API data\n";
        }
    } else {
        echo "❌ Failed to fetch data: {$result['message']}\n";
    }
    
    echo "\n2. Testing Database for SENSEX...\n";
    
    // Check database for SENSEX
    $sensexData = MarketData::getMarketDataForSymbols(['SENSEX'], true);
    
    if (isset($sensexData['SENSEX'])) {
        $sensex = $sensexData['SENSEX'];
        echo "✅ SENSEX found in database!\n";
        echo "   - Price: ₹{$sensex['ltp']}\n";
        echo "   - Change: {$sensex['change']} ({$sensex['change_percent']}%)\n";
        echo "   - Last updated: {$sensex['timestamp']}\n";
        echo "   - Source: {$sensex['data_source']}\n";
    } else {
        echo "❌ SENSEX not found in database\n";
    }
    
    echo "\n3. Testing API Endpoint for SENSEX...\n";
    
    // Test the live data API
    $apiUrl = 'http://127.0.0.1:8000/api/truedata/live-data';
    $response = file_get_contents($apiUrl);
    
    if ($response) {
        $data = json_decode($response, true);
        if ($data['success'] && isset($data['data']['SENSEX'])) {
            $sensex = $data['data']['SENSEX'];
            echo "✅ SENSEX found in API response!\n";
            echo "   - Price: ₹{$sensex['ltp']}\n";
            echo "   - Change: {$sensex['change']} ({$sensex['change_percent']}%)\n";
        } else {
            echo "❌ SENSEX not found in API response\n";
        }
    } else {
        echo "❌ API not accessible\n";
    }
    
    echo "\n4. Testing Search for SENSEX...\n";
    
    // Test search API
    $searchUrl = 'http://127.0.0.1:8000/api/truedata/search-stock?symbol=SENSEX';
    $searchResponse = file_get_contents($searchUrl);
    
    if ($searchResponse) {
        $searchData = json_decode($searchResponse, true);
        if ($searchData['success'] && isset($searchData['data'])) {
            $sensex = $searchData['data'];
            echo "✅ SENSEX search working!\n";
            echo "   - Price: ₹{$sensex['ltp']}\n";
            echo "   - Change: {$sensex['change']} ({$sensex['change_percent']}%)\n";
        } else {
            echo "❌ SENSEX search failed: {$searchData['message']}\n";
        }
    } else {
        echo "❌ Search API not accessible\n";
    }
    
    echo "\n🎉 SENSEX Data Test Complete!\n";
    echo "============================\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
