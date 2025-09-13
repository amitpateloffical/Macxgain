#!/bin/bash

# Fix Mixed Content Issues - Set APP_URL to HTTP
echo "🔧 Fixing Mixed Content Issues..."

# Create .env file if it doesn't exist
if [ ! -f .env ]; then
    echo "📝 Creating .env file..."
    if [ -f .env.example ]; then
        cp .env.example .env
    else
        touch .env
    fi
fi

# Fix APP_URL to HTTP (not HTTPS)
echo "🌐 Setting APP_URL to HTTP..."
if grep -q "APP_URL=" .env; then
    sed -i 's|APP_URL=https://.*|APP_URL=http://localhost|g' .env
    sed -i 's|APP_URL=https://macxgain.com|APP_URL=http://localhost|g' .env
    echo "✅ APP_URL updated to HTTP"
else
    echo "APP_URL=http://localhost" >> .env
    echo "✅ APP_URL added with HTTP"
fi

# Set other environment variables
if grep -q "APP_ENV=" .env; then
    sed -i 's|APP_ENV=.*|APP_ENV=local|g' .env
else
    echo "APP_ENV=local" >> .env
fi

if grep -q "APP_DEBUG=" .env; then
    sed -i 's|APP_DEBUG=.*|APP_DEBUG=true|g' .env
else
    echo "APP_DEBUG=true" >> .env
fi

# Add database configuration
if ! grep -q "DB_CONNECTION=" .env; then
    echo "" >> .env
    echo "# Database Configuration" >> .env
    echo "DB_CONNECTION=mysql" >> .env
    echo "DB_HOST=127.0.0.1" >> .env
    echo "DB_PORT=3306" >> .env
    echo "DB_DATABASE=trading" >> .env
    echo "DB_USERNAME=macxgain" >> .env
    echo "DB_PASSWORD=macxgain123" >> .env
fi

# Generate app key if needed
if ! grep -q "APP_KEY=" .env; then
    echo "🔑 Generating application key..."
    php artisan key:generate
fi

# Clear all caches
echo "🧹 Clearing caches..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Build assets
echo "🏗️ Building assets..."
npm run build

echo ""
echo "✅ Mixed Content Issues Fixed!"
echo ""
echo "🌐 Your app is now configured for HTTP:"
echo "   APP_URL=http://localhost"
echo ""
echo "🚀 Now restart your Laravel server:"
echo "   php artisan serve --host=0.0.0.0 --port=8000"
echo ""
echo "📱 Access your app at: http://localhost:8000"
echo ""
echo "💡 If you want HTTPS later, run: ./fix_https.sh"
