#!/bin/bash

# Fix Production API - Quick Deployment Script
# This script helps fix the production API configuration

echo "🔧 Fixing Production API Configuration..."

# Check if we're in the right directory
if [ ! -f "artisan" ]; then
    echo "❌ Error: Please run this script from the Laravel project root directory"
    exit 1
fi

echo "📋 Step 1: Building assets..."
npm run build

echo "📋 Step 2: Clearing caches..."
php artisan config:clear
php artisan route:clear
php artisan cache:clear

echo "📋 Step 3: Checking backup routes..."
php artisan route:list | grep -i backup

if [ $? -eq 0 ]; then
    echo "✅ Backup routes found"
else
    echo "❌ Backup routes not found!"
    exit 1
fi

echo "📋 Step 4: Caching for production..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "📋 Step 5: Testing API locally..."
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

echo "📋 Step 6: Checking .htaccess..."
if [ -f ".htaccess" ]; then
    if grep -q "api/backups" .htaccess; then
        echo "✅ .htaccess has API configuration"
    else
        echo "⚠️  .htaccess missing API configuration"
    fi
else
    echo "❌ .htaccess file not found"
fi

echo ""
echo "✅ Local setup completed!"
echo ""
echo "📋 Next steps for production server:"
echo "1. Upload these files to production server:"
echo "   - .htaccess"
echo "   - app/Http/Controllers/BackupController.php"
echo "   - routes/api.php"
echo "   - public/build/ (entire folder)"
echo ""
echo "2. Run on production server:"
echo "   php artisan config:cache"
echo "   php artisan route:cache"
echo ""
echo "3. Test production API:"
echo "   curl https://GainTradeX.com/api/backups"
echo ""
echo "4. If still getting HTML response, check:"
echo "   - Web server configuration"
echo "   - .htaccess file permissions"
echo "   - Laravel route caching"
echo ""
echo "📖 For detailed instructions, see: PRODUCTION_API_SETUP.md"
