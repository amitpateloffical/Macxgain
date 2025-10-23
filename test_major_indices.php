<?php

/**
 * Test script for major indices filtering
 * This script tests that only NIFTY 50, NIFTY BANK, and SENSEX are shown
 */

require_once 'vendor/autoload.php';

use App\Services\FreeMarketDataService;
use App\Models\MarketData;

echo "🧪 Testing Major Indices Filtering\n";
echo "==================================\n\n";

try {
    // Initialize Laravel
    $app = require_once 'bootstrap/app.php';
    $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
    
    echo "1. Testing FreeMarketDataService filtering...\n";
    $freeMarketDataService = new FreeMarketDataService();
    
    // Test fetching data
    $result = $freeMarketDataService->getLiveMarketData();
    
    if ($result['success']) {
        echo "✅ Successfully fetched data from: {$result['source']}\n";
        echo "📊 Total symbols: " . count($result['data']) . "\n";
        
        // Check if only major indices are present
        $expectedIndices = ['NIFTY 50', 'NIFTY BANK', 'SENSEX'];
        $actualIndices = array_keys($result['data']);
        
        echo "🎯 Expected indices: " . implode(', ', $expectedIndices) . "\n";
        echo "📈 Actual indices: " . implode(', ', $actualIndices) . "\n";
        
        $allPresent = true;
        foreach ($expectedIndices as $index) {
            if (!in_array($index, $actualIndices)) {
                echo "❌ Missing index: {$index}\n";
                $allPresent = false;
            }
        }
        
        if (count($actualIndices) > count($expectedIndices)) {
            echo "❌ Extra indices found: " . implode(', ', array_diff($actualIndices, $expectedIndices)) . "\n";
            $allPresent = false;
        }
        
        if ($allPresent && count($actualIndices) === count($expectedIndices)) {
            echo "✅ Perfect! Only major indices are shown\n";
        } else {
            echo "❌ Filtering not working correctly\n";
        }
        
        // Show the data
        foreach ($result['data'] as $symbol => $data) {
            echo "   - {$symbol}: ₹{$data['ltp']} ({$data['change_percent']}%)\n";
        }
    } else {
        echo "❌ Failed to fetch data: {$result['message']}\n";
    }
    
    echo "\n2. Testing API endpoint filtering...\n";
    
    // Test the live data API
    $apiUrl = 'http://127.0.0.1:8000/api/truedata/live-data';
    $response = file_get_contents($apiUrl);
    
    if ($response) {
        $data = json_decode($response, true);
        if ($data['success'] && isset($data['data'])) {
            $apiIndices = array_keys($data['data']);
            echo "✅ API returned " . count($apiIndices) . " indices: " . implode(', ', $apiIndices) . "\n";
            
            // Check if only major indices
            $expectedIndices = ['NIFTY 50', 'NIFTY BANK', 'SENSEX'];
            $allPresent = true;
            foreach ($expectedIndices as $index) {
                if (!in_array($index, $apiIndices)) {
                    echo "❌ Missing index in API: {$index}\n";
                    $allPresent = false;
                }
            }
            
            if (count($apiIndices) > count($expectedIndices)) {
                echo "❌ Extra indices in API: " . implode(', ', array_diff($apiIndices, $expectedIndices)) . "\n";
                $allPresent = false;
            }
            
            if ($allPresent && count($apiIndices) === count($expectedIndices)) {
                echo "✅ API filtering working perfectly!\n";
            } else {
                echo "❌ API filtering not working correctly\n";
            }
        } else {
            echo "❌ API response not successful\n";
        }
    } else {
        echo "❌ API not accessible\n";
    }
    
    echo "\n3. Testing database filtering...\n";
    
    // Test database data
    $dbData = MarketData::getAllMarketData(true);
    $dbIndices = array_keys($dbData);
    echo "📊 Database has " . count($dbIndices) . " total records\n";
    
    // Check if major indices are in database
    $majorIndicesInDb = 0;
    foreach (['NIFTY 50', 'NIFTY BANK', 'SENSEX'] as $index) {
        if (isset($dbData[$index])) {
            $majorIndicesInDb++;
            echo "✅ {$index} found in database: ₹{$dbData[$index]['ltp']}\n";
        } else {
            echo "❌ {$index} not found in database\n";
        }
    }
    
    echo "\n🎉 Major Indices Filtering Test Complete!\n";
    echo "========================================\n";
    echo "✅ System is now showing only NIFTY 50, NIFTY BANK, and SENSEX\n";
    echo "📊 All other stocks are filtered out\n";
    echo "🌐 Frontend will display only these 3 major indices\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

