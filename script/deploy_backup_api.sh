#!/bin/bash

# Deploy Backup API to Production Server
# This script ensures backup API routes are properly configured

echo "🚀 Deploying Backup API to Production Server..."

# Check if we're in the right directory
if [ ! -f "artisan" ]; then
    echo "❌ Error: Please run this script from the Laravel project root directory"
    exit 1
fi

# Clear caches
echo "🧹 Clearing caches..."
php artisan config:clear
php artisan route:clear
php artisan cache:clear

# Check if backup routes exist
echo "🔍 Checking backup routes..."
php artisan route:list | grep -i backup

if [ $? -eq 0 ]; then
    echo "✅ Backup routes found in route list"
else
    echo "❌ Backup routes not found. Please check routes/api.php"
    exit 1
fi

# Test API endpoint locally first
echo "🧪 Testing API endpoint locally..."
php artisan serve --host=0.0.0.0 --port=8001 &
SERVER_PID=$!
sleep 3

# Test the backup API
curl -s "http://localhost:8001/api/backups" -H "Accept: application/json" | head -c 100

if [ $? -eq 0 ]; then
    echo "✅ Local API test successful"
else
    echo "❌ Local API test failed"
    kill $SERVER_PID
    exit 1
fi

# Kill local server
kill $SERVER_PID

echo "📦 Building assets..."
npm run build

echo "🔄 Optimizing for production..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "🔧 Checking .htaccess configuration..."
if [ -f ".htaccess" ]; then
    echo "✅ .htaccess file found"
    if grep -q "api/backups" .htaccess; then
        echo "✅ API routes configuration found in .htaccess"
    else
        echo "⚠️  API routes configuration not found in .htaccess"
    fi
else
    echo "❌ .htaccess file not found"
fi

echo "✅ Backup API deployment completed!"
echo ""
echo "📋 Next steps for production server:"
echo "1. Upload the updated files to production server"
echo "2. Run: php artisan config:cache"
echo "3. Run: php artisan route:cache"
echo "4. Test: curl https://GainTradeX.com/api/backups"
echo ""
echo "🔧 If API still returns HTML, check:"
echo "- .htaccess file for proper API routing"
echo "- Web server configuration for /api/* routes"
echo "- Laravel routes are properly cached"
