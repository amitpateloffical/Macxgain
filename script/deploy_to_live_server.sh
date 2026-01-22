#!/bin/bash

echo "🚀 Deploying working data system to live server..."
echo "=================================================="

# Step 1: Pull latest code
echo "📥 Pulling latest code..."
git pull origin main

# Step 2: Install dependencies
echo "📦 Installing dependencies..."
composer install --no-dev --optimize-autoloader

# Step 3: Clear caches
echo "🧹 Clearing caches..."
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Step 4: Run migrations
echo "🗄️ Running migrations..."
php artisan migrate --force

# Step 5: Import data from JSON file
echo "📊 Importing data from market_data.json..."
if [ -f "market_data.json" ]; then
    php safe_data_import.php
else
    echo "⚠️ market_data.json not found, skipping data import"
fi

# Step 6: Populate cache
echo "💾 Populating cache..."
php artisan truedata:populate-cache

# Step 7: Start services
echo "🔄 Starting services..."

# Kill existing processes
pkill -f "php artisan serve"
pkill -f "python3 truedata_continuous_websocket.py"
pkill -f "php artisan queue:work"

# Start Laravel server
echo "🌐 Starting Laravel server..."
nohup php artisan serve --host=0.0.0.0 --port=8000 > server.log 2>&1 &
echo $! > server.pid

# Start WebSocket daemon
echo "🔌 Starting WebSocket daemon..."
nohup python3 truedata_continuous_websocket.py > websocket.log 2>&1 &
echo $! > websocket.pid

# Start Queue worker
echo "⚙️ Starting Queue worker..."
nohup php artisan queue:work --daemon > queue.log 2>&1 &
echo $! > queue.pid

# Step 8: Test API
echo "🧪 Testing API..."
sleep 5
API_RESPONSE=$(curl -s http://localhost:8000/api/truedata/live-data | jq -r '.success, .data_count' 2>/dev/null || echo "false null")

if [[ $API_RESPONSE == *"true"* ]]; then
    echo "✅ API is working!"
    echo "📊 Data count: $(echo $API_RESPONSE | cut -d' ' -f2)"
else
    echo "❌ API test failed"
fi

echo ""
echo "🎉 Deployment complete!"
echo "📋 Service status:"
echo "   - Laravel Server: $(if [ -f server.pid ] && kill -0 $(cat server.pid) 2>/dev/null; then echo '✅ Running'; else echo '❌ Not running'; fi)"
echo "   - WebSocket: $(if [ -f websocket.pid ] && kill -0 $(cat websocket.pid) 2>/dev/null; then echo '✅ Running'; else echo '❌ Not running'; fi)"
echo "   - Queue Worker: $(if [ -f queue.pid ] && kill -0 $(cat queue.pid) 2>/dev/null; then echo '✅ Running'; else echo '❌ Not running'; fi)"
echo ""
echo "🌐 Test your API: curl -s http://localhost:8000/api/truedata/live-data | jq '.success, .data_count'"
echo "📱 Your admin/stock-market page should now work at: https://GainTradeX.com/admin/stock-market"
