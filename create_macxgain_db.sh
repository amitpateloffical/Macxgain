#!/bin/bash

# Create Trading Database with Existing Root Password
echo "📊 Creating Trading Database..."

# Create database and user
mysql -u root -p"Kabirisgod@7354$" << 'EOF'
CREATE DATABASE IF NOT EXISTS trading;
CREATE USER IF NOT EXISTS 'macxgain'@'%' IDENTIFIED BY 'macxgain123';
GRANT ALL PRIVILEGES ON trading.* TO 'macxgain'@'%';
CREATE USER IF NOT EXISTS 'macxgain'@'localhost' IDENTIFIED BY 'macxgain123';
GRANT ALL PRIVILEGES ON trading.* TO 'macxgain'@'localhost';
FLUSH PRIVILEGES;
EOF

if [ $? -eq 0 ]; then
    echo "✅ Trading database created successfully!"
    
    # Configure MySQL for Docker access
    echo "🔧 Configuring MySQL for Docker containers..."
    sudo sed -i 's/^bind-address\s*=\s*127\.0\.0\.1/bind-address = 0.0.0.0/' /etc/mysql/mysql.conf.d/mysqld.cnf
    
    # Restart MySQL
    sudo systemctl restart mysql
    
    # Create media directories
    echo "📁 Creating media directories..."
    sudo mkdir -p /var/macxgain-data/{trading,uploads,profiles,screenshots,documents}
    sudo chown -R 33:33 /var/macxgain-data  # www-data user ID
    sudo chmod -R 755 /var/macxgain-data
    
    echo ""
    echo "✅ Setup complete! Now you can run:"
    echo "   sh quick_deploy.sh"
    echo ""
    echo "🌐 Access URLs:"
    echo "   - Application: http://localhost"
    echo "   - Database: http://localhost:8080"
    
else
    echo "❌ Database creation failed!"
    echo "💡 Please check if root password is correct: Kabirisgod@7354$"
    echo "   Or run: mysql -u root -p"
fi
