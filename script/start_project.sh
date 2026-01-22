#!/bin/bash

# GainTradeX Project Startup Script
# This script starts the entire project with automatic WebSocket management

echo "🚀 Starting GainTradeX Trading Platform..."

# Navigate to project directory
cd /Users/amitpatel/Documents/GitHub/GainTradeX

# Check if Laravel is installed
if [ ! -f "artisan" ]; then
    echo "❌ Laravel project not found. Please run this script from the project root."
    exit 1
fi

# Deploy real option prices update
echo "🔄 Deploying real option prices update..."

# # Pull latest changes
# echo "📥 Pulling latest changes..."
# git pull origin main

# # Install dependencies
# echo "📦 Installing dependencies..."
# composer install --no-dev --optimize-autoloader

# Clear caches
echo "🧹 Clearing caches..."
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

echo "✅ Real option prices deployment complete!"

# Start Laravel development server
echo "🌐 Starting Laravel development server..."
php artisan serve --host=127.0.0.1 --port=8000 &
LARAVEL_PID=$!

# Wait for Laravel to start
sleep 3

# Start Laravel scheduler for automatic WebSocket management
echo "📅 Starting Laravel scheduler for automatic WebSocket management..."
php artisan schedule:work &
SCHEDULER_PID=$!

# Start queue worker for processing jobs
echo "⚙️  Starting queue worker for processing jobs..."
php artisan queue:work &
QUEUE_PID=$!

# Check if WebSocket script should run (market hours)
current_hour=$(date +%H)
current_minute=$(date +%M)
current_day=$(date +%u) # 1 = Monday, 7 = Sunday

# Market hours: Monday to Friday, 9:15 AM to 3:30 PM IST
if [ $current_day -ge 1 ] && [ $current_day -le 5 ]; then
    if [ $current_hour -gt 9 ] || ([ $current_hour -eq 9 ] && [ $current_minute -ge 15 ]); then
        if [ $current_hour -lt 15 ] || ([ $current_hour -eq 15 ] && [ $current_minute -le 30 ]); then
            echo "🟢 Market is OPEN - Starting WebSocket script..."
            php artisan truedata:websocket start
        else
            echo "🔴 Market is CLOSED - WebSocket will start automatically when market opens"
        fi
    else
        echo "🔴 Market is CLOSED - WebSocket will start automatically when market opens"
    fi
else
    echo "🔴 Weekend - WebSocket will start automatically on Monday"
fi

echo ""
echo "✅ GainTradeX Trading Platform started successfully!"
echo ""
echo "📊 Services Running:"
echo "   🌐 Laravel Server: http://127.0.0.1:8000 (PID: $LARAVEL_PID)"
echo "   📅 Laravel Scheduler: Running (PID: $SCHEDULER_PID)"
echo "   ⚙️  Queue Worker: Running (PID: $QUEUE_PID)"
echo ""
echo "🎯 Features:"
echo "   📈 Live Market Data: Auto-refresh every 5 seconds"
echo "   📊 Real Option Chain Data: NSE India Free API (1-2 min delayed)"
echo "   💰 Accurate P&L Calculation: Based on real option prices"
echo "   🔌 WebSocket Script: Auto-starts during market hours"
echo "   ⏰ Market Hours: 9:15 AM - 3:30 PM IST (Monday to Friday)"
echo ""
echo "🛠️  Management Commands:"
echo "   php artisan truedata:websocket start   - Start WebSocket manually"
echo "   php artisan truedata:websocket stop    - Stop WebSocket"
echo "   php artisan truedata:websocket status  - Check WebSocket status"
echo "   php artisan truedata:websocket restart - Restart WebSocket"
echo ""
echo "📝 Logs:"
echo "   tail -f storage/logs/laravel.log       - View application logs"
echo ""
echo "🛑 To stop all services, press Ctrl+C"

# Wait for user to stop
wait
