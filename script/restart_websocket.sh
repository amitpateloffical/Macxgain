#!/bin/bash

# Restart WebSocket with Real-time Data

echo "🔄 Restarting WebSocket with Real-time Data..."

# Stop existing WebSocket
if [ -f websocket.pid ]; then
    PID=$(cat websocket.pid)
    if ps -p $PID > /dev/null 2>&1; then
        echo "🛑 Stopping existing WebSocket (PID: $PID)..."
        kill $PID
        sleep 2
    fi
    rm -f websocket.pid
fi

# Clear old data
rm -f market_data.json

# Start new continuous WebSocket
echo "🚀 Starting Continuous WebSocket..."
nohup python3 truedata_continuous_websocket.py > websocket.log 2>&1 &
echo $! > websocket.pid
echo "✅ Continuous WebSocket started with PID: $(cat websocket.pid)"

# Wait a moment for initial data
sleep 10

# Test the data
echo "🧪 Testing WebSocket data..."
python3 test_websocket.py

echo "✅ WebSocket restart complete!"
echo "📊 Check websocket.log for detailed logs"
echo "📈 Real-time data should now be available in market_data.json"
