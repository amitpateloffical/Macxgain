#!/bin/bash

# Quick MySQL Root Password Fix for GainTradeX
echo "🔒 MySQL Root Password Fix"
echo "=========================="

echo "📊 Setting MySQL root password to 'root123'..."

# Try to set root password
if sudo mysql -e "ALTER USER 'root'@'localhost' IDENTIFIED WITH mysql_native_password BY 'root123';" 2>/dev/null; then
    echo "✅ Root password set successfully"
    sudo mysql -e "FLUSH PRIVILEGES;"
else
    echo "⚠️  Trying alternative method..."
    # Alternative method for fresh MySQL installations
    sudo mysql_secure_installation
fi

# Test the connection
if mysql -u root -proot123 -e "SELECT 1;" 2>/dev/null; then
    echo "✅ Root password is working!"
    
    # Create GainTradeX database if it doesn't exist
    echo "📊 Creating GainTradeX database and user..."
    mysql -u root -proot123 -e "CREATE DATABASE IF NOT EXISTS GainTradeX;"
    mysql -u root -proot123 -e "CREATE USER IF NOT EXISTS 'GainTradeX'@'%' IDENTIFIED BY 'GainTradeX123';"
    mysql -u root -proot123 -e "GRANT ALL PRIVILEGES ON GainTradeX.* TO 'GainTradeX'@'%';"
    mysql -u root -proot123 -e "CREATE USER IF NOT EXISTS 'GainTradeX'@'localhost' IDENTIFIED BY 'GainTradeX123';"
    mysql -u root -proot123 -e "GRANT ALL PRIVILEGES ON GainTradeX.* TO 'GainTradeX'@'localhost';"
    mysql -u root -proot123 -e "FLUSH PRIVILEGES;"
    
    echo "✅ Database setup complete!"
    echo ""
    echo "🚀 Now you can run: sh quick_deploy.sh"
else
    echo "❌ Root password setup failed"
    echo "💡 Please set MySQL root password manually:"
    echo "   sudo mysql"
    echo "   ALTER USER 'root'@'localhost' IDENTIFIED WITH mysql_native_password BY 'root123';"
    echo "   FLUSH PRIVILEGES;"
    echo "   exit"
fi
