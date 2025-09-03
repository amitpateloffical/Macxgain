#!/bin/bash

# 🛑 Stop All Macxgain Services Script

echo "🛑 Stopping all Macxgain services..."

# Stop WebSocket daemon
echo "📡 Stopping WebSocket daemon..."
pkill -f "start_websocket_daemon.sh"
pkill -f "truedata_websocket.py"

# Stop Laravel server
echo "🌐 Stopping Laravel server..."
pkill -f "artisan serve"

# Stop queue worker
echo "⚙️ Stopping queue worker..."
pkill -f "queue:work"

# Stop keep-alive script
echo "🔄 Stopping keep-alive script..."
pkill -f "keep_websocket_alive.sh"

# Wait a moment
sleep 2

# Check if processes are stopped
echo "🔍 Checking if all processes are stopped..."

if pgrep -f "start_websocket_daemon.sh" > /dev/null; then
    echo "⚠️  WebSocket daemon: Still running"
else
    echo "✅ WebSocket daemon: Stopped"
fi

if pgrep -f "artisan serve" > /dev/null; then
    echo "⚠️  Laravel server: Still running"
else
    echo "✅ Laravel server: Stopped"
fi

if pgrep -f "queue:work" > /dev/null; then
    echo "⚠️  Queue worker: Still running"
else
    echo "✅ Queue worker: Stopped"
fi

echo ""
echo "🎯 All Macxgain services have been stopped!"
echo "🚀 To start again, run: ./quick_server_start.sh"
