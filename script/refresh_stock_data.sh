#!/bin/bash

echo "🔄 Refreshing TrueData Stock Market Cache..."
echo ""

# Run the command to populate cache
php artisan truedata:populate-cache

# Wait for job to complete
echo "⏳ Waiting for data fetch to complete..."
sleep 3

# Process the job
php artisan queue:work --once

# Check cache status
echo ""
echo "📊 Cache Status:"
php artisan tinker --execute="echo 'Cache has data: ' . (Cache::has('truedata_live_data') ? 'YES' : 'NO'); echo 'Stock count: ' . count(Cache::get('truedata_live_data', []));"

echo ""
echo "✅ Stock market data refresh completed!"
echo "🌐 You can now visit: http://127.0.0.1:8000/admin/stock-market"
