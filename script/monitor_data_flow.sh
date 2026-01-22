#!/bin/bash

# Monitor Data Flow Script
# This script monitors the data flow and provides status information

echo "🔍 Monitoring data flow for admin/stock-market page..."
echo "=================================================="

# Check API endpoint
echo "📡 API Status:"
api_response=$(curl -s http://localhost:8000/api/truedata/live-data 2>/dev/null)
if [ $? -eq 0 ] && echo "$api_response" | grep -q '"success":true'; then
    data_count=$(echo "$api_response" | jq -r '.data_count // "N/A"' 2>/dev/null)
    market_status=$(echo "$api_response" | jq -r '.market_status.status // "N/A"' 2>/dev/null)
    data_type=$(echo "$api_response" | jq -r '.data_type // "N/A"' 2>/dev/null)
    echo "✅ API: Working (Data: $data_count symbols, Status: $market_status, Type: $data_type)"
else
    echo "❌ API: Not responding"
fi

# Check cache status
echo ""
echo "💾 Cache Status:"
cache_data=$(php artisan tinker --execute="echo Cache::has('truedata_live_data') ? 'EXISTS' : 'MISSING';" 2>/dev/null)
cache_count=$(php artisan tinker --execute="echo count(Cache::get('truedata_live_data', []));" 2>/dev/null)
cache_update=$(php artisan tinker --execute="echo Cache::get('truedata_last_update', 'NEVER');" 2>/dev/null)

echo "📊 Live Data: $cache_data ($cache_count symbols)"
echo "🕐 Last Update: $cache_update"

# Check WebSocket status
echo ""
echo "🌐 WebSocket Status:"
if [ -f websocket.pid ] && ps -p $(cat websocket.pid) > /dev/null 2>&1; then
    echo "✅ WebSocket: Running (PID: $(cat websocket.pid))"
else
    echo "❌ WebSocket: Not running"
fi

# Check data file
echo ""
echo "📁 Data File Status:"
if [ -f market_data.json ]; then
    file_size=$(ls -lh market_data.json | awk '{print $5}')
    file_time=$(ls -l market_data.json | awk '{print $6, $7, $8}')
    echo "✅ market_data.json: Exists ($file_size, modified: $file_time)"
else
    echo "❌ market_data.json: Missing"
fi

# Check Laravel server
echo ""
echo "🚀 Laravel Server Status:"
if curl -s http://localhost:8000 > /dev/null 2>&1; then
    echo "✅ Laravel Server: Running on port 8000"
else
    echo "❌ Laravel Server: Not responding on port 8000"
fi

echo ""
echo "🎯 Summary:"
if [ "$cache_count" -gt 0 ] && [ "$api_response" != "" ]; then
    echo "✅ Admin/stock-market page should be working with live data"
else
    echo "❌ Admin/stock-market page may not have data - run ./ensure_data_flow.sh"
fi
