#!/bin/bash

echo "🔧 Fixing server database schema issues..."

# 1. Run migrations to add missing columns
echo "📊 Running migrations..."
php artisan migrate --force

# 2. Clear caches
echo "🧹 Clearing caches..."
php artisan cache:clear
php artisan config:clear

# 3. Import data safely
echo "📊 Importing data safely..."
php safe_data_import.php

# 4. Test API
echo "🔍 Testing API..."
API_RESPONSE=$(curl -s http://localhost:8000/api/truedata/live-data | jq -r '.success' 2>/dev/null)
DATA_COUNT=$(curl -s http://localhost:8000/api/truedata/live-data | jq -r '.data_count' 2>/dev/null)

if [ "$API_RESPONSE" = "true" ] && [ "$DATA_COUNT" -gt 0 ]; then
    echo "✅ Server fixed successfully!"
    echo "📊 API returning $DATA_COUNT symbols"
else
    echo "❌ Server fix failed!"
    echo "🔧 Manual fix needed"
fi

echo "🚀 Server fix completed!"
