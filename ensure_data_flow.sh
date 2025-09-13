#!/bin/bash

# Ensure Data Flow Script
# This script ensures that market data is always available for the admin page

echo "🔄 Ensuring data flow for admin/stock-market page..."

# Function to check if data is fresh (less than 5 minutes old)
is_data_fresh() {
    local last_update=$(php artisan tinker --execute="echo Cache::get('truedata_last_update', 'NEVER');" 2>/dev/null)
    if [ "$last_update" = "NEVER" ]; then
        return 1
    fi
    
    # Convert to timestamp and check if it's within 5 minutes
    local timestamp=$(date -d "$last_update" +%s 2>/dev/null || echo "0")
    local current=$(date +%s)
    local diff=$((current - timestamp))
    
    if [ $diff -lt 300 ]; then  # 5 minutes = 300 seconds
        return 0
    else
        return 1
    fi
}

# Function to refresh data
refresh_data() {
    echo "📊 Refreshing market data..."
    php artisan queue:work --once
    sleep 2
}

# Function to start WebSocket if not running
start_websocket() {
    if [ ! -f websocket.pid ] || ! ps -p $(cat websocket.pid) > /dev/null 2>&1; then
        echo "🚀 Starting WebSocket daemon..."
        nohup python3 truedata_continuous_websocket.py > websocket.log 2>&1 &
        echo $! > websocket.pid
        echo "✅ WebSocket started with PID: $(cat websocket.pid)"
    fi
}

# Main logic
if is_data_fresh; then
    echo "✅ Data is fresh, no action needed"
else
    echo "⚠️  Data is stale or missing, refreshing..."
    refresh_data
    
    # Check if data is now available
    if is_data_fresh; then
        echo "✅ Data refreshed successfully"
    else
        echo "❌ Failed to refresh data, trying WebSocket..."
        start_websocket
        sleep 5
        refresh_data
    fi
fi

# Final check
data_count=$(php artisan tinker --execute="echo count(Cache::get('truedata_live_data', []));" 2>/dev/null)
echo "📈 Current data count: $data_count symbols"

if [ "$data_count" -gt 0 ]; then
    echo "✅ Admin/stock-market page should now have data"
else
    echo "❌ No data available - check logs for issues"
fi
