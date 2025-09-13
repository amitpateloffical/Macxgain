#!/bin/bash

# Quick Host Setup for Macxgain with External MySQL
echo "🚀 Macxgain Host Setup with External MySQL"
echo "=========================================="

# Check if MySQL is already installed
if command -v mysql >/dev/null 2>&1; then
    echo "✅ MySQL is already installed"
    
    # Check if trading database exists
    if mysql -u root -p"Kabirisgod@7354$" -e "USE trading;" 2>/dev/null; then
        echo "✅ Trading database exists"
    else
        echo "📊 Creating Trading database..."
        mysql -u root -p"Kabirisgod@7354$" -e "CREATE DATABASE IF NOT EXISTS trading;"
        mysql -u root -p"Kabirisgod@7354$" -e "CREATE USER IF NOT EXISTS 'macxgain'@'%' IDENTIFIED BY 'macxgain123';"
        mysql -u root -p"Kabirisgod@7354$" -e "GRANT ALL PRIVILEGES ON trading.* TO 'macxgain'@'%';"
        mysql -u root -p"Kabirisgod@7354$" -e "CREATE USER IF NOT EXISTS 'macxgain'@'localhost' IDENTIFIED BY 'macxgain123';"
        mysql -u root -p"Kabirisgod@7354$" -e "GRANT ALL PRIVILEGES ON trading.* TO 'macxgain'@'localhost';"
        mysql -u root -p"Kabirisgod@7354$" -e "FLUSH PRIVILEGES;"
    fi
else
    echo "❌ MySQL not found. Running full setup..."
    chmod +x setup_host_mysql.sh
    ./setup_host_mysql.sh
fi

# Create media directories if they don't exist
echo "📁 Setting up media directories..."
sudo mkdir -p /var/macxgain-data/{trading,uploads,profiles,screenshots,documents}
sudo chown -R 33:33 /var/macxgain-data  # www-data user ID
sudo chmod -R 755 /var/macxgain-data

# Configure MySQL for Docker access
echo "🔧 Configuring MySQL for Docker containers..."
if grep -q "bind-address.*127\.0\.0\.1" /etc/mysql/mysql.conf.d/mysqld.cnf 2>/dev/null; then
    sudo sed -i 's/^bind-address\s*=\s*127\.0\.0\.1/bind-address = 0.0.0.0/' /etc/mysql/mysql.conf.d/mysqld.cnf
    sudo systemctl restart mysql
    echo "✅ MySQL configured for external access"
fi

echo ""
echo "✅ Host setup complete!"
echo ""
echo "📊 Next Steps:"
echo "   1. Run: docker-compose down (if running)"
echo "   2. Run: docker-compose up --build"
echo ""
echo "🌐 Access URLs:"
echo "   - Application: http://localhost"
echo "   - Database: http://localhost:8080 (PHPMyAdmin)"
echo ""
echo "📁 User Data Storage:"
echo "   - Host Path: /var/macxgain-data/"
echo "   - Container will automatically sync with this directory"
echo ""
echo "💡 Benefits:"
echo "   ✅ MySQL data persists even if containers are deleted"
echo "   ✅ User uploads/screenshots persist across rebuilds"
echo "   ✅ Better performance (host MySQL vs containerized)"
echo "   ✅ Easier database management and backups"
