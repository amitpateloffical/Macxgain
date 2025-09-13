#!/bin/bash

# Setup Host MySQL for Macxgain
# This script installs MySQL on host machine instead of container

echo "🗄️ Setting up Host MySQL for Macxgain..."

# Install MySQL 8.0
echo "📦 Installing MySQL 8.0..."
sudo apt update
sudo apt install -y mysql-server mysql-client

# Start and enable MySQL
echo "🚀 Starting MySQL service..."
sudo systemctl start mysql
sudo systemctl enable mysql

# Set root password and secure MySQL
echo "🔒 Setting MySQL root password..."
sudo mysql -e "ALTER USER 'root'@'localhost' IDENTIFIED WITH mysql_native_password BY 'root123';"
sudo mysql -e "FLUSH PRIVILEGES;"

# Secure MySQL installation (optional)
echo "🔒 Securing MySQL installation (optional)..."
echo "Note: Root password is already set to 'root123'"

# Create database and user for Trading
echo "👤 Creating Trading database and user..."
mysql -u root -p"Kabirisgod@7354$" -e "CREATE DATABASE IF NOT EXISTS trading;"
mysql -u root -p"Kabirisgod@7354$" -e "CREATE USER IF NOT EXISTS 'macxgain'@'%' IDENTIFIED BY 'macxgain123';"
mysql -u root -p"Kabirisgod@7354$" -e "GRANT ALL PRIVILEGES ON trading.* TO 'macxgain'@'%';"
mysql -u root -p"Kabirisgod@7354$" -e "CREATE USER IF NOT EXISTS 'macxgain'@'localhost' IDENTIFIED BY 'macxgain123';"
mysql -u root -p"Kabirisgod@7354$" -e "GRANT ALL PRIVILEGES ON trading.* TO 'macxgain'@'localhost';"
mysql -u root -p"Kabirisgod@7354$" -e "FLUSH PRIVILEGES;"

# Configure MySQL to accept connections from Docker containers
echo "🔧 Configuring MySQL for Docker access..."
sudo sed -i 's/^bind-address\s*=\s*127\.0\.0\.1/bind-address = 0.0.0.0/' /etc/mysql/mysql.conf.d/mysqld.cnf

# Restart MySQL to apply changes
echo "🔄 Restarting MySQL..."
sudo systemctl restart mysql

# Create media directories on host
echo "📁 Creating media directories..."
sudo mkdir -p /var/macxgain-data/{trading,uploads,profiles,screenshots,documents}
sudo chown -R 33:33 /var/macxgain-data  # www-data user ID in container
sudo chmod -R 755 /var/macxgain-data

echo "✅ Host MySQL setup complete!"
echo ""
echo "📊 Database Details:"
echo "   Host: localhost (or host.docker.internal from container)"
echo "   Database: trading"  
echo "   Username: macxgain"
echo "   Password: macxgain123"
echo ""
echo "📁 Media Storage:"
echo "   Path: /var/macxgain-data/"
echo "   Directories: trading, uploads, profiles, screenshots, documents"
echo ""
echo "🔍 Test connections:"
echo "   mysql -u root -p\"Kabirisgod@7354$\" trading        # Root user"
echo "   mysql -h localhost -u macxgain -p trading  # App user"
echo ""
echo "🚀 Now run: docker-compose up --build"
