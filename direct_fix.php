<?php

/**
 * Direct Database Fix Script
 * This script directly adds missing columns and imports data
 */

require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\DB;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

try {
    echo "🔧 Starting direct database fix...\n";
    
    // 1. Add missing timestamp column
    echo "📊 Adding missing timestamp column...\n";
    try {
        DB::statement('ALTER TABLE market_data ADD COLUMN timestamp TIMESTAMP NULL AFTER volume');
        echo "✅ Timestamp column added successfully\n";
    } catch (Exception $e) {
        if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
            echo "ℹ️  Timestamp column already exists\n";
        } else {
            echo "⚠️  Error adding timestamp column: " . $e->getMessage() . "\n";
        }
    }
    
    // 2. Check if market_data.json exists
    if (!file_exists('market_data.json')) {
        echo "❌ market_data.json not found!\n";
        exit(1);
    }
    
    // 3. Load JSON data
    $jsonData = json_decode(file_get_contents('market_data.json'), true);
    if (!$jsonData) {
        echo "❌ Failed to parse market_data.json!\n";
        exit(1);
    }
    
    echo "📊 Found " . count($jsonData) . " symbols in JSON file\n";
    
    // 4. Clear existing data
    DB::table('market_data')->truncate();
    echo "🗑️  Cleared existing market data\n";
    
    // 5. Import data with all required fields
    $count = 0;
    foreach ($jsonData as $symbol => $data) {
        try {
            DB::table('market_data')->insert([
                'symbol' => $symbol,
                'ltp' => $data['ltp'] ?? 0,
                'change' => $data['change'] ?? 0,
                'change_percent' => $data['change_percent'] ?? 0,
                'high' => $data['high'] ?? 0,
                'low' => $data['low'] ?? 0,
                'open' => $data['open'] ?? 0,
                'prev_close' => $data['prev_close'] ?? 0,
                'volume' => $data['volume'] ?? 0,
                'timestamp' => $data['timestamp'] ?? now(),
                'data_timestamp' => now(),
                'data_source' => $data['data_source'] ?? 'TrueData Real WebSocket',
                'raw_data' => json_encode($data),
                'is_live' => true,
                'market_status' => 'CLOSED',
                'created_at' => now(),
                'updated_at' => now()
            ]);
            $count++;
        } catch (Exception $e) {
            echo "⚠️  Error importing $symbol: " . $e->getMessage() . "\n";
        }
    }
    
    echo "✅ Successfully imported $count symbols to database\n";
    
    // 6. Update cache
    Cache::put('truedata_live_data', $jsonData, 3600);
    Cache::put('truedata_last_update', now(), 3600);
    Cache::put('truedata_data_type', 'LIVE', 3600);
    
    echo "💾 Updated cache with $count symbols\n";
    
    // 7. Verify data
    $dbCount = DB::table('market_data')->count();
    $cacheCount = count(Cache::get('truedata_live_data', []));
    
    echo "🔍 Verification:\n";
    echo "   Database: $dbCount symbols\n";
    echo "   Cache: $cacheCount symbols\n";
    
    if ($dbCount > 0 && $cacheCount > 0) {
        echo "🎉 Direct fix completed successfully!\n";
        echo "🚀 API should now return data properly\n";
    } else {
        echo "❌ Direct fix failed!\n";
        exit(1);
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
