#!/bin/bash

# Restart Services Script
# This script restarts all background services

echo "🔄 Restarting all services..."

# 1. Stop existing services
echo "🛑 Stopping existing services..."
./stop_all_services.sh

# 2. Wait a moment
sleep 3

# 3. Start services again
echo "🚀 Starting services..."
nohup python3 truedata_continuous_websocket.py > websocket.log 2>&1 &
echo $! > websocket.pid

nohup php artisan queue:work --daemon > queue.log 2>&1 &
echo $! > queue.pid

nohup php artisan serve --host=0.0.0.0 --port=8000 > server.log 2>&1 &
echo $! > server.pid

echo "✅ Services restarted successfully!"
echo "📊 Check status with: ./check_status.sh"
