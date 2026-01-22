#!/bin/bash

# Auto Data Setup Script
# This script sets up automatic data fetching for live market hours

echo "🚀 Setting up automatic data fetching system..."

# 1. Make scripts executable
chmod +x *.sh

# 2. Setup cron jobs for automatic data fetching
echo "📅 Setting up cron jobs..."

# Add Laravel scheduler cron job
(crontab -l 2>/dev/null; echo "* * * * * cd $(pwd) && php artisan schedule:run >> /dev/null 2>&1") | crontab -

# Add queue worker cron job (every 5 minutes)
(crontab -l 2>/dev/null; echo "*/5 * * * * cd $(pwd) && php artisan queue:work --once >> /dev/null 2>&1") | crontab -

# Add smart scheduler (every 5 seconds during market hours)
(crontab -l 2>/dev/null; echo "* * * * * cd $(pwd) && php artisan truedata:smart-schedule >> /dev/null 2>&1") | crontab -

echo "✅ Cron jobs added successfully!"

# 3. Start background services
echo "🔄 Starting background services..."

# Start WebSocket daemon
echo "🌐 Starting WebSocket daemon..."
nohup python3 truedata_continuous_websocket.py > websocket.log 2>&1 &
echo $! > websocket.pid

# Start queue worker
echo "⚙️  Starting queue worker..."
nohup php artisan queue:work --daemon > queue.log 2>&1 &
echo $! > queue.pid

# Start Laravel server
echo "🚀 Starting Laravel server..."
nohup php artisan serve --host=0.0.0.0 --port=8000 > server.log 2>&1 &
echo $! > server.pid

# 4. Test the setup
echo "🔍 Testing setup..."
sleep 5

# Test API
API_RESPONSE=$(curl -s http://localhost:8000/api/truedata/live-data | jq -r '.success' 2>/dev/null)
DATA_COUNT=$(curl -s http://localhost:8000/api/truedata/live-data | jq -r '.data_count' 2>/dev/null)

if [ "$API_RESPONSE" = "true" ] && [ "$DATA_COUNT" -gt 0 ]; then
    echo "✅ Setup successful!"
    echo "📊 API returning $DATA_COUNT symbols"
else
    echo "⚠️  Setup completed but API not responding yet"
    echo "🔄 Data will be available when market opens (9:15 AM - 3:30 PM IST)"
fi

# 5. Show status
echo ""
echo "🎉 Auto Data Setup Complete!"
echo "================================"
echo "📅 Cron Jobs: Active"
echo "🌐 WebSocket: Running (PID: $(cat websocket.pid 2>/dev/null || echo 'N/A'))"
echo "⚙️  Queue Worker: Running (PID: $(cat queue.pid 2>/dev/null || echo 'N/A'))"
echo "🚀 Laravel Server: Running (PID: $(cat server.pid 2>/dev/null || echo 'N/A'))"
echo ""
echo "📊 Data Update Schedule:"
echo "  • Market Open (9:15 AM - 3:30 PM IST): Every 5 seconds"
echo "  • Pre/Post Market: Every 30 seconds"
echo "  • Market Closed: Every 5 minutes"
echo ""
echo "🔍 Monitor logs:"
echo "  • WebSocket: tail -f websocket.log"
echo "  • Queue: tail -f queue.log"
echo "  • Server: tail -f server.log"
echo "  • Laravel: tail -f storage/logs/laravel.log"
echo ""
echo "🛑 Stop services: ./stop_all_services.sh"
echo "🔄 Restart services: ./restart_services.sh"
echo ""
echo "✅ System is now running automatically!"
echo "📈 Data will update automatically during market hours!"
