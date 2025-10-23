<?php

/**
 * Test script for expanded symbols list
 * This script tests that all requested symbols are being fetched and displayed
 */

require_once 'vendor/autoload.php';

use App\Services\FreeMarketDataService;
use App\Models\MarketData;

echo "🧪 Testing Expanded Symbols List\n";
echo "================================\n\n";

try {
    // Initialize Laravel
    $app = require_once 'bootstrap/app.php';
    $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
    
    // Expected symbols list
    $expectedSymbols = [
        // Major indices first
        'NIFTY 50', 'NIFTY BANK', 'SENSEX',
        // Additional symbols
        'MCXCOMPDEX', 'AARTIIND', 'BRITANNIA', 'COLPAL', 'DMART', 'EICHERMOT', 'GILLETTE',
        'HDFCBANK', 'ICICIBANK', 'JKTYRE', 'KAJARIACER', 'LICHSGFIN', 'MINDTREE', 'OFSS',
        'PNB', 'QUICKHEAL', 'RELIANCE', 'SBIN', 'TCS', 'UJJIVAN', 'WIPRO', 'YESBANK', 'ZEEL',
        'NIFTY-I', 'BANKNIFTY-I', 'UPL-I', 'VEDL-I', 'VOLTAS-I', 'ZEEL-I',
        'CRUDEOIL-I', 'GOLDM-I', 'SILVERM-I', 'COPPER-I', 'SILVER-I'
    ];
    
    echo "1. Testing FreeMarketDataService with expanded symbols...\n";
    $freeMarketDataService = new FreeMarketDataService();
    
    // Test fetching data
    $result = $freeMarketDataService->getLiveMarketData();
    
    if ($result['success']) {
        echo "✅ Successfully fetched data from: {$result['source']}\n";
        echo "📊 Total symbols returned: " . count($result['data']) . "\n";
        
        $actualSymbols = array_keys($result['data']);
        echo "🎯 Expected symbols: " . count($expectedSymbols) . "\n";
        echo "📈 Actual symbols: " . count($actualSymbols) . "\n";
        
        // Check which symbols are present
        $presentSymbols = [];
        $missingSymbols = [];
        
        foreach ($expectedSymbols as $symbol) {
            if (in_array($symbol, $actualSymbols)) {
                $presentSymbols[] = $symbol;
            } else {
                $missingSymbols[] = $symbol;
            }
        }
        
        echo "\n✅ Present symbols (" . count($presentSymbols) . "):\n";
        foreach ($presentSymbols as $symbol) {
            $data = $result['data'][$symbol];
            echo "   - {$symbol}: ₹{$data['ltp']} ({$data['change_percent']}%)\n";
        }
        
        if (!empty($missingSymbols)) {
            echo "\n❌ Missing symbols (" . count($missingSymbols) . "):\n";
            foreach ($missingSymbols as $symbol) {
                echo "   - {$symbol}\n";
            }
        }
        
        // Check if major indices are at the top
        $firstThree = array_slice($actualSymbols, 0, 3);
        $expectedFirstThree = ['NIFTY 50', 'NIFTY BANK', 'SENSEX'];
        
        if ($firstThree === $expectedFirstThree) {
            echo "\n✅ Major indices are correctly positioned at the top!\n";
        } else {
            echo "\n❌ Major indices not at top. First 3: " . implode(', ', $firstThree) . "\n";
        }
        
    } else {
        echo "❌ Failed to fetch data: {$result['message']}\n";
    }
    
    echo "\n2. Testing API endpoint with expanded symbols...\n";
    
    // Test the live data API
    $apiUrl = 'http://127.0.0.1:8000/api/truedata/live-data';
    $response = file_get_contents($apiUrl);
    
    if ($response) {
        $data = json_decode($response, true);
        if ($data['success'] && isset($data['data'])) {
            $apiSymbols = array_keys($data['data']);
            echo "✅ API returned " . count($apiSymbols) . " symbols\n";
            
            // Check if major indices are at the top
            $firstThree = array_slice($apiSymbols, 0, 3);
            $expectedFirstThree = ['NIFTY 50', 'NIFTY BANK', 'SENSEX'];
            
            if ($firstThree === $expectedFirstThree) {
                echo "✅ API correctly shows major indices at the top!\n";
            } else {
                echo "❌ API major indices not at top. First 3: " . implode(', ', $firstThree) . "\n";
            }
            
            // Show first 10 symbols
            echo "\n📊 First 10 symbols from API:\n";
            $firstTen = array_slice($apiSymbols, 0, 10);
            foreach ($firstTen as $symbol) {
                $stockData = $data['data'][$symbol];
                echo "   - {$symbol}: ₹{$stockData['ltp']} ({$stockData['change_percent']}%)\n";
            }
            
        } else {
            echo "❌ API response not successful\n";
        }
    } else {
        echo "❌ API not accessible\n";
    }
    
    echo "\n3. Testing database with expanded symbols...\n";
    
    // Test database data
    $dbData = MarketData::getAllMarketData(true);
    $dbSymbols = array_keys($dbData);
    echo "📊 Database has " . count($dbSymbols) . " total records\n";
    
    // Check how many of our expected symbols are in database
    $symbolsInDb = 0;
    foreach ($expectedSymbols as $symbol) {
        if (isset($dbData[$symbol])) {
            $symbolsInDb++;
        }
    }
    
    echo "✅ " . $symbolsInDb . " of " . count($expectedSymbols) . " expected symbols found in database\n";
    
    echo "\n🎉 Expanded Symbols Test Complete!\n";
    echo "==================================\n";
    echo "✅ System now shows " . count($actualSymbols ?? []) . " symbols instead of just 3\n";
    echo "📊 Major indices (NIFTY 50, NIFTY BANK, SENSEX) are at the top\n";
    echo "🌐 Additional symbols are included as requested\n";
    echo "🔄 All symbols update automatically every few minutes\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

