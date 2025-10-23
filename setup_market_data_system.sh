#!/bin/bash

echo "🚀 Setting up Market Data System"
echo "================================"

# Check if Laravel is installed
if [ ! -f "artisan" ]; then
    echo "❌ Laravel not found. Please run this from the Laravel project root."
    exit 1
fi

echo "1. Running database migrations..."
php artisan migrate --force

echo "2. Clearing cache..."
php artisan cache:clear
php artisan config:clear
php artisan route:clear

echo "3. Testing market data update command..."
php artisan market:update

echo "4. Testing smart scheduler..."
php artisan market:smart-schedule

echo "5. Checking database..."
php artisan tinker --execute="
use App\Models\MarketData;
echo 'Database records: ' . MarketData::count() . PHP_EOL;
echo 'Live records: ' . MarketData::where('is_live', true)->count() . PHP_EOL;
echo 'Latest update: ' . MarketData::max('data_timestamp') . PHP_EOL;
"

echo "6. Testing API endpoints..."
echo "Testing dashboard endpoint..."
curl -s http://127.0.0.1:8000/api/truedata/dashboard | jq '.success' || echo "Dashboard API test failed"

echo "Testing market stats endpoint..."
curl -s http://127.0.0.1:8000/api/truedata/market-stats | jq '.success' || echo "Market stats API test failed"

echo ""
echo "✅ Market Data System Setup Complete!"
echo "====================================="
echo "📊 System is ready to fetch NSE data and store in database"
echo "🌐 Frontend will now use database data instead of direct API calls"
echo "⏰ Set up a cron job to run 'php artisan market:smart-schedule' every 2-5 minutes"
echo ""
echo "Cron job example:"
echo "*/3 * * * * cd /path/to/your/project && php artisan market:smart-schedule"
echo ""
echo "Manual update command:"
echo "php artisan market:update"
