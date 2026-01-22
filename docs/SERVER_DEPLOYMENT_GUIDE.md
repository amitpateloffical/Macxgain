# 🚀 Server Deployment Guide - GainTradeX Trading Platform

## 📋 Prerequisites
- Ubuntu/CentOS server with root access
- PHP 8.1+ with Laravel
- Python 3.8+
- Node.js & NPM
- Supervisor (for process management)
- Nginx/Apache

## 🔧 Step 1: Upload Files to Server

```bash
# Upload your project to server
scp -r /Users/amitpatel/Documents/GitHub/GainTradeX user@your-server-ip:/var/www/
```

## 🐍 Step 2: Install Python Dependencies

```bash
# SSH into your server
ssh user@your-server-ip

# Install Python dependencies
cd /var/www/GainTradeX
pip3 install websocket-client requests

# Make scripts executable
chmod +x start_websocket_daemon.sh
chmod +x keep_websocket_alive.sh
```

## ⚙️ Step 3: Setup Supervisor for Process Management

```bash
# Install supervisor
sudo apt update
sudo apt install supervisor

# Create supervisor config
sudo nano /etc/supervisor/conf.d/GainTradeX.conf
```

**Add this configuration:**

```ini
[program:GainTradeX-websocket]
command=/var/www/GainTradeX/start_websocket_daemon.sh
directory=/var/www/GainTradeX
autostart=true
autorestart=true
user=www-data
redirect_stderr=true
stdout_logfile=/var/www/GainTradeX/logs/websocket.log
stdout_logfile_maxbytes=10MB
stdout_logfile_backups=5

[program:GainTradeX-laravel]
command=php artisan serve --host=0.0.0.0 --port=8000
directory=/var/www/GainTradeX
autostart=true
autorestart=true
user=www-data
redirect_stderr=true
stdout_logfile=/var/www/GainTradeX/logs/laravel.log
stdout_logfile_maxbytes=10MB
stdout_logfile_backups=5

[program:GainTradeX-queue]
command=php artisan queue:work --sleep=3 --tries=3
directory=/var/www/GainTradeX
autostart=true
autorestart=true
user=www-data
redirect_stderr=true
stdout_logfile=/var/www/GainTradeX/logs/queue.log
stdout_logfile_maxbytes=10MB
stdout_logfile_backups=5
```

## 🔄 Step 4: Start Services

```bash
# Create logs directory
mkdir -p /var/www/GainTradeX/logs

# Reload supervisor
sudo supervisorctl reread
sudo supervisorctl update

# Start all services
sudo supervisorctl start GainTradeX-websocket
sudo supervisorctl start GainTradeX-laravel
sudo supervisorctl start GainTradeX-queue

# Check status
sudo supervisorctl status
```

## 🌐 Step 5: Setup Nginx (Optional)

```bash
# Create nginx config
sudo nano /etc/nginx/sites-available/GainTradeX
```

**Add this configuration:**

```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /var/www/GainTradeX/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

```bash
# Enable site
sudo ln -s /etc/nginx/sites-available/GainTradeX /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

## 📊 Step 6: Monitor Services

```bash
# Check WebSocket daemon status
sudo supervisorctl status GainTradeX-websocket

# View logs
tail -f /var/www/GainTradeX/logs/websocket.log
tail -f /var/www/GainTradeX/logs/laravel.log
tail -f /var/www/GainTradeX/logs/queue.log

# Restart services if needed
sudo supervisorctl restart GainTradeX-websocket
sudo supervisorctl restart GainTradeX-laravel
sudo supervisorctl restart GainTradeX-queue
```

## 🔧 Step 7: Environment Configuration

```bash
# Copy environment file
cp .env.example .env

# Edit environment
nano .env

# Generate key
php artisan key:generate

# Run migrations
php artisan migrate

# Clear cache
php artisan cache:clear
php artisan config:clear
```

## 🚀 Quick Start Commands

```bash
# Start everything manually (for testing)
cd /var/www/GainTradeX

# Start WebSocket daemon
./start_websocket_daemon.sh &

# Start Laravel server
php artisan serve --host=0.0.0.0 --port=8000 &

# Start queue worker
php artisan queue:work &

# Check if everything is running
ps aux | grep -E "(websocket|artisan|python)"
```

## 📱 Access Your Application

- **Web Interface:** http://your-server-ip:8000
- **Admin Panel:** http://your-server-ip:8000/admin/stock-market
- **API Endpoint:** http://your-server-ip:8000/api/truedata/live-data

## 🔍 Troubleshooting

```bash
# Check if WebSocket is receiving data
curl http://your-server-ip:8000/api/truedata/live-data

# Check supervisor logs
sudo supervisorctl tail -f GainTradeX-websocket
sudo supervisorctl tail -f GainTradeX-laravel

# Restart all services
sudo supervisorctl restart all
```

## 📈 Monitoring Script

Create a monitoring script to check if everything is working:

```bash
#!/bin/bash
# monitor.sh

echo "🔍 Checking GainTradeX Services..."

# Check WebSocket
if curl -s http://localhost:8000/api/truedata/live-data | grep -q "success"; then
    echo "✅ WebSocket: Working"
else
    echo "❌ WebSocket: Not working"
fi

# Check Laravel
if curl -s http://localhost:8000 | grep -q "Laravel"; then
    echo "✅ Laravel: Working"
else
    echo "❌ Laravel: Not working"
fi

# Check processes
if pgrep -f "start_websocket_daemon.sh" > /dev/null; then
    echo "✅ WebSocket Daemon: Running"
else
    echo "❌ WebSocket Daemon: Not running"
fi

echo "📊 System Status Complete"
```

## 🎯 Final Checklist

- [ ] Files uploaded to server
- [ ] Python dependencies installed
- [ ] Supervisor configured
- [ ] All services started
- [ ] Nginx configured (optional)
- [ ] Environment variables set
- [ ] Database migrated
- [ ] WebSocket receiving live data
- [ ] UI accessible and updating

## 🆘 Emergency Commands

```bash
# Stop all services
sudo supervisorctl stop all

# Start all services
sudo supervisorctl start all

# Restart everything
sudo supervisorctl restart all

# Check status
sudo supervisorctl status
```

---

**🎉 Your GainTradeX trading platform is now live on the server with continuous WebSocket data flow!**
