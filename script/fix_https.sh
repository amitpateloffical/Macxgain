#!/bin/bash

echo "🔒 Fixing HTTPS Mixed Content Issues..."

# 1. Update .env file for HTTPS
echo "📝 Updating .env configuration..."

# Check if .env exists
if [ -f .env ]; then
    # Update APP_URL to HTTPS
    if grep -q "APP_URL=" .env; then
        sed -i 's|APP_URL=http://|APP_URL=https://|g' .env
        sed -i 's|APP_URL=https://localhost|APP_URL=https://www.GainTradeX.com|g' .env
        sed -i 's|APP_URL=https://127.0.0.1|APP_URL=https://www.GainTradeX.com|g' .env
        echo "✅ APP_URL updated to HTTPS"
    else
        echo "APP_URL=https://www.GainTradeX.com" >> .env
        echo "✅ APP_URL added with HTTPS"
    fi
    
    # Set APP_ENV to production
    if grep -q "APP_ENV=" .env; then
        sed -i 's|APP_ENV=.*|APP_ENV=production|g' .env
    else
        echo "APP_ENV=production" >> .env
    fi
    
    # Set APP_DEBUG to false
    if grep -q "APP_DEBUG=" .env; then
        sed -i 's|APP_DEBUG=.*|APP_DEBUG=false|g' .env
    else
        echo "APP_DEBUG=false" >> .env
    fi
    
    echo "✅ Environment variables updated"
else
    echo "❌ .env file not found!"
    exit 1
fi

# 2. Clear Laravel caches
echo "🧹 Clearing Laravel caches..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# 3. Rebuild assets for production
echo "🏗️ Building production assets..."
npm run build

# 4. Update asset permissions
echo "🔧 Setting correct permissions..."
chmod -R 755 public/build/
chown -R www-data:www-data public/build/ 2>/dev/null || echo "Note: Could not change ownership (may require sudo)"

# 5. Test the configuration
echo "🔍 Testing configuration..."
php artisan config:show app.url

echo ""
echo "🎉 HTTPS Fix Complete!"
echo "📋 Next steps:"
echo "1. Make sure your web server (Apache/Nginx) is configured for HTTPS"
echo "2. Ensure SSL certificate is properly installed"
echo "3. Test your website: https://www.GainTradeX.com"
echo ""
echo "🌐 If still having issues, check:"
echo "   - Web server HTTPS configuration"
echo "   - SSL certificate validity"
echo "   - Cloudflare/CDN HTTPS settings"
