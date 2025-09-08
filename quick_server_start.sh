#!/bin/bash

# 🚀 Quick Server Start Script for Macxgain
# Run this script on your server to start everything

echo "🚀 Starting Macxgain Trading Platform..."

# Create logs directory
mkdir -p logs

# Kill any existing processes
echo "🔄 Stopping existing processes..."
pkill -f "start_websocket_daemon.sh"
pkill -f "artisan serve"
pkill -f "queue:work"
pkill -f "truedata_websocket.py"

# Wait a moment
sleep 2

# Start WebSocket daemon
echo "📡 Starting WebSocket daemon..."
nohup ./start_websocket_daemon.sh > logs/daemon.log 2>&1 &
echo "✅ WebSocket daemon started"

# Start Laravel server
echo "🌐 Starting Laravel server..."
nohup php artisan serve --host=0.0.0.0 --port=8005 > logs/laravel.log 2>&1 &
echo "✅ Laravel server started on port 8005"

# Start queue worker
echo "⚙️ Starting queue worker..."
nohup php artisan queue:work --sleep=3 --tries=3 > logs/queue.log 2>&1 &
echo "✅ Queue worker started"

# Wait for services to start
echo "⏳ Waiting for services to start..."
sleep 5

# Check if services are running
echo "🔍 Checking service status..."

if pgrep -f "start_websocket_daemon.sh" > /dev/null; then
    echo "✅ WebSocket daemon: Running"
else
    echo "❌ WebSocket daemon: Not running"
fi

if pgrep -f "artisan serve" > /dev/null; then
    echo "✅ Laravel server: Running"
else
    echo "❌ Laravel server: Not running"
fi

if pgrep -f "queue:work" > /dev/null; then
    echo "✅ Queue worker: Running"
else
    echo "❌ Queue worker: Not running"
fi

# Test API endpoint
echo "🧪 Testing API endpoint..."
if curl -s http://localhost:8005/api/truedata/live-data | grep -q "success"; then
    echo "✅ API endpoint: Working"
else
    echo "❌ API endpoint: Not working"
fi

echo ""
echo "🎉 Macxgain Trading Platform is now running!"
echo "📱 Access your application at: http://your-server-ip:8005"
echo "📊 Admin panel: http://your-server-ip:8005/admin/stock-market"
echo "🔗 API endpoint: http://your-server-ip:8005/api/truedata/live-data"
echo ""
echo "📋 To monitor logs:"
echo "   tail -f logs/daemon.log"
echo "   tail -f logs/laravel.log"
echo "   tail -f logs/queue.log"
echo ""
echo "🛑 To stop all services:"
echo "   pkill -f 'start_websocket_daemon.sh'"
echo "   pkill -f 'artisan serve'"
echo "   pkill -f 'queue:work'"
