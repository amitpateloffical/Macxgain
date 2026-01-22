#!/bin/bash
# GainTradeX Server Status Check Script
# This script checks the status of all services on your server

echo "🔍 GainTradeX Server Status Check - $(date)"
echo "=================================================="

# Check if we're in the right directory
if [ ! -f "artisan" ]; then
    echo "❌ Error: Not in Laravel project directory. Please run from public_html/"
    exit 1
fi

echo ""
echo "📊 1. LARAVEL APPLICATION STATUS"
echo "--------------------------------"
# Check Laravel app
if php artisan --version > /dev/null 2>&1; then
    echo "✅ Laravel application: RUNNING"
    php artisan --version
else
    echo "❌ Laravel application: NOT WORKING"
fi

echo ""
echo "🗄️  2. DATABASE STATUS"
echo "----------------------"
# Check database connection
if php artisan migrate:status > /dev/null 2>&1; then
    echo "✅ Database connection: WORKING"
    echo "📋 Migration status:"
    php artisan migrate:status | tail -5
else
    echo "❌ Database connection: FAILED"
fi

echo ""
echo "💾 3. CACHE STATUS"
echo "------------------"
# Check cache
if php artisan cache:clear > /dev/null 2>&1; then
    echo "✅ Cache system: WORKING"
else
    echo "❌ Cache system: FAILED"
fi

echo ""
echo "📡 4. WEBSOCKET STATUS"
echo "---------------------"
# Check if WebSocket Python script is running
if pgrep -f "python3 truedata_websocket.py" > /dev/null; then
    echo "✅ WebSocket script: RUNNING"
    echo "📊 WebSocket PID: $(pgrep -f 'python3 truedata_websocket.py')"
else
    echo "❌ WebSocket script: NOT RUNNING"
    echo "💡 Start with: python3 truedata_websocket.py > /dev/null 2>&1 &"
fi

echo ""
echo "🔄 5. QUEUE STATUS"
echo "------------------"
# Check if queue worker is running
if pgrep -f "php artisan queue:work" > /dev/null; then
    echo "✅ Queue worker: RUNNING"
    echo "📊 Queue PID: $(pgrep -f 'php artisan queue:work')"
else
    echo "❌ Queue worker: NOT RUNNING"
    echo "💡 Start with: php artisan queue:work > /dev/null 2>&1 &"
fi

echo ""
echo "🌐 6. WEB SERVER STATUS"
echo "----------------------"
# Check if web server is running
if pgrep -f "php artisan serve" > /dev/null; then
    echo "✅ Laravel dev server: RUNNING"
    echo "📊 Server PID: $(pgrep -f 'php artisan serve')"
    echo "🌐 Access URL: http://your-domain.com:8000"
else
    echo "❌ Laravel dev server: NOT RUNNING"
    echo "💡 Start with: php artisan serve --host=0.0.0.0 --port=8000 > /dev/null 2>&1 &"
fi

echo ""
echo "📈 7. LIVE DATA STATUS"
echo "---------------------"
# Check if live data is available
echo "🔍 Checking live data availability..."
if curl -s http://localhost:8000/api/truedata/live-data > /dev/null 2>&1; then
    echo "✅ Live data API: ACCESSIBLE"
    echo "📊 Data timestamp: $(curl -s http://localhost:8000/api/truedata/live-data | grep -o '"last_update":"[^"]*"' | head -1)"
else
    echo "❌ Live data API: NOT ACCESSIBLE"
    echo "💡 Check if Laravel server is running"
fi

echo ""
echo "📋 8. RECENT LOGS"
echo "-----------------"
echo "🔍 Checking recent application logs..."
if [ -f "storage/logs/laravel.log" ]; then
    echo "📄 Recent errors (last 5 lines):"
    tail -5 storage/logs/laravel.log | grep -i error || echo "No recent errors found"
else
    echo "❌ Log file not found"
fi

echo ""
echo "🚀 9. QUICK START COMMANDS"
echo "-------------------------"
echo "To start all services:"
echo "1. python3 truedata_websocket.py > /dev/null 2>&1 &"
echo "2. php artisan serve --host=0.0.0.0 --port=8000 > /dev/null 2>&1 &"
echo "3. php artisan queue:work > /dev/null 2>&1 &"
echo ""
echo "To check this status again:"
echo "bash server_status_check.sh"

echo ""
echo "✅ Status check completed at $(date)"
echo "=================================================="
