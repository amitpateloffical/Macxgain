#!/bin/bash

# TrueData WebSocket Daemon Script
# This script ensures continuous WebSocket data flow

echo "🚀 Starting TrueData WebSocket Daemon..."

# Function to start WebSocket
start_websocket() {
    echo "📡 Starting WebSocket script..."
    nohup python3 truedata_websocket.py > websocket.log 2>&1 &
    echo $! > websocket.pid
    echo "✅ WebSocket started with PID: $(cat websocket.pid)"
}

# Function to check if WebSocket is running
check_websocket() {
    if [ -f websocket.pid ]; then
        PID=$(cat websocket.pid)
        if ps -p $PID > /dev/null 2>&1; then
            return 0  # Running
        else
            return 1  # Not running
        fi
    else
        return 1  # No PID file
    fi
}

# Function to populate cache
populate_cache() {
    echo "🔄 Populating cache with fresh data..."
    php artisan truedata:populate-cache
    php artisan queue:work --once
}

# Start WebSocket initially
start_websocket
populate_cache

# Main monitoring loop
while true; do
    if ! check_websocket; then
        echo "⚠️  WebSocket not running, restarting..."
        start_websocket
        sleep 5
        populate_cache
    else
        echo "✅ WebSocket is running - $(date)"
    fi
    
    # Populate cache every 30 seconds
    populate_cache
    
    # Wait 30 seconds before next check
    sleep 30
done
