#!/bin/bash

# Safe Deployment Script
# This script ensures data is properly imported after code pull

echo "🚀 Starting safe deployment..."

# 1. Pull latest code
echo "📥 Pulling latest code..."
git pull origin main

# 2. Install dependencies
echo "📦 Installing dependencies..."
composer install --no-dev --optimize-autoloader
npm install && npm run build

# 3. Clear caches
echo "🧹 Clearing caches..."
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# 4. Run migrations
echo "🗄️  Running migrations..."
php artisan migrate --force

# 5. Safe data import
echo "📊 Importing market data safely..."
php safe_data_import.php

# 6. Start services
echo "🔄 Starting services..."
# Kill existing processes
pkill -f "php artisan serve" 2>/dev/null || true
pkill -f "python3 truedata_continuous_websocket.py" 2>/dev/null || true

# Start Laravel server
nohup php artisan serve --host=0.0.0.0 --port=8000 > server.log 2>&1 &
echo $! > server.pid

# Start WebSocket
nohup python3 truedata_continuous_websocket.py > websocket.log 2>&1 &
echo $! > websocket.pid

# Start queue worker
nohup php artisan queue:work --daemon > queue.log 2>&1 &
echo $! > queue.pid

# 7. Verify everything
echo "🔍 Verifying deployment..."
sleep 5

# Test API
API_RESPONSE=$(curl -s http://localhost:8000/api/truedata/live-data | jq -r '.success' 2>/dev/null)
DATA_COUNT=$(curl -s http://localhost:8000/api/truedata/live-data | jq -r '.data_count' 2>/dev/null)

if [ "$API_RESPONSE" = "true" ] && [ "$DATA_COUNT" -gt 0 ]; then
    echo "✅ Deployment successful!"
    echo "📊 API returning $DATA_COUNT symbols"
    echo "🎉 Admin page should now work properly!"
else
    echo "❌ Deployment failed!"
    echo "🔧 Check logs for issues"
    exit 1
fi

echo "🚀 Safe deployment completed!"
