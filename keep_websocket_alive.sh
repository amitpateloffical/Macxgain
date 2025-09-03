#!/bin/bash

# Keep WebSocket Alive Script
# This script ensures the TrueData WebSocket stays running

echo "🔄 Starting WebSocket Keep-Alive Monitor..."

while true; do
    # Check if WebSocket is running
    if ! php artisan truedata:websocket status | grep -q "is running"; then
        echo "⚠️  WebSocket not running, restarting..."
        php artisan truedata:websocket start
        sleep 5
    else
        echo "✅ WebSocket is running - $(date)"
    fi
    
    # Wait 5 seconds before next check
    sleep 5
done
