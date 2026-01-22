# GainTradeX AI Trading System - Complete Project Documentation

## 📋 Project Overview
**GainTradeX AI Trading System** is a comprehensive web-based trading platform that provides real-time market data, AI-powered trading interface, and complete order management system. The system integrates with TrueData for live market feeds and offers a modern, responsive interface for both administrators and users.

### 🎯 Key Features:
- **Real-time Market Data** from TrueData WebSocket
- **AI Trading Interface** for administrators
- **Live Stock Prices** with Call/Put options
- **User Balance Management** with wallet transactions
- **Trade Execution** with P&L tracking
- **Market Hours Validation** (9:15 AM - 3:30 PM IST)
- **Order Management** with exit functionality
- **Modern Dark Theme UI** with responsive design

## 🔐 Server Access Details
```
HOSTNAME / IP 	: 103.235.106.85
PORT 	: 2642
USERNAME	: root
PASSWORD	: f{7*g@3rj7}4qvMO
```

### Connect to Server:
```bash
ssh -p 2642 root@103.235.106.85
```

## 🗄️ Database Setup
```sql
CREATE USER 'webappssolution'@'localhost' IDENTIFIED BY 'webappssolution';
GRANT ALL PRIVILEGES ON *.* TO 'webappssolution'@'localhost' WITH GRANT OPTION;	
```

## 👤 Admin Login Credentials
```
Email: admin@gmail.com
Password: admin123	
```

## 🚀 Complete Server Deployment Commands

### 1. First Time Setup
```bash
# Install Python WebSocket library
pip3 install websocket-client

# Make files executable
chmod +x truedata_test.py
chmod +x refresh_stock_data.sh

# Install Laravel dependencies
composer install --optimize-autoloader --no-dev

# Build frontend assets
npm install
npm run build

# Set permissions
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### 2. Database Setup
```bash
# Run migrations
php artisan migrate --force

# Clear and cache config
php artisan config:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 3. Create Initial Data Cache
```bash
# Method 1: Using Queue Worker (Recommended)
php artisan queue:work --once

# Method 2: Using Artisan Command
php artisan tinker --execute="
use App\Jobs\FetchTrueDataJob;
dispatch(new FetchTrueDataJob());
echo 'Cache populated successfully';
"

# Method 3: Using Refresh Script
./refresh_stock_data.sh

# Method 4: Manual Cache Population
php artisan tinker --execute="
use App\Jobs\FetchTrueDataJob;
dispatch(new FetchTrueDataJob());
echo 'Cache populated successfully';
"
```

### 4. Setup Cron Jobs (Essential!)
```bash
# Edit crontab
crontab -e

# Add these lines:
* * * * * cd /path/to/your/project && php artisan schedule:run >> /dev/null 2>&1
*/5 * * * * cd /path/to/your/project && php artisan queue:work --once >> /dev/null 2>&1
```

### 5. Test Everything
```bash
# Test Python script
python3 truedata_test.py

# Test cache population
php artisan tinker --execute="
echo 'Cache status:';
echo 'Live Data: ' . (Cache::has('truedata_live_data') ? 'EXISTS' : 'MISSING');
echo 'Last Update: ' . Cache::get('truedata_last_update', 'NEVER');
echo 'Market Data: ' . (Cache::has('truedata_market_data') ? 'EXISTS' : 'MISSING');
"

# Test cache content
php artisan tinker --execute="
echo 'Cache Content:';
echo 'Live Data Count: ' . count(Cache::get('truedata_live_data', []));
echo 'Last Update: ' . Cache::get('truedata_last_update', 'NEVER');
echo 'Market Status: ' . Cache::get('truedata_market_status', 'UNKNOWN');
"
```

### 6. Queue Worker (For Real-time Data)
```bash
# Start queue worker (run in background)
nohup php artisan queue:work --daemon > /dev/null 2>&1 &

# Or use supervisor (recommended)
# Create /etc/supervisor/conf.d/laravel-worker.conf
```

## 📋 Complete Deploy Script

Create a file `deploy.sh`:

```bash
#!/bin/bash
echo "🚀 Starting Laravel Deploy..."

# Install dependencies
echo "📦 Installing dependencies..."
composer install --optimize-autoloader --no-dev
npm install
npm run build

# Set permissions
echo "🔐 Setting permissions..."
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

# Clear caches
echo "🧹 Clearing caches..."
php artisan config:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run migrations
echo "🗄️ Running migrations..."
php artisan migrate --force

# Install Python dependencies
echo "🐍 Installing Python dependencies..."
pip3 install websocket-client

# Make files executable
echo "⚙️ Making files executable..."
chmod +x truedata_test.py
chmod +x refresh_stock_data.sh

# Create initial cache
echo "💾 Creating initial cache..."
php artisan queue:work --once

echo "✅ Deploy completed successfully!"
echo "🔧 Don't forget to setup cron jobs!"
```

## ⚠️ Important Notes

1. **Python Version:** Make sure server has Python 3.6+ and use `python3` command
2. **Cron Jobs:** Essential for automatic data updates every 5 minutes
3. **Queue Worker:** Keep running for real-time data processing
4. **Permissions:** Ensure web server can write to storage and cache directories
5. **Environment:** Update `.env` file with production database and API keys

## 🎯 Quick Test Commands

```bash
# Test if everything works
php artisan tinker --execute="
echo 'Testing cache...';
Cache::put('test', 'working', 60);
echo Cache::get('test');
"

# Test Python script
python3 truedata_test.py

# Test API endpoint
curl -H "Authorization: Bearer YOUR_TOKEN" http://your-domain.com/api/truedata/dashboard
```

## 🔧 Troubleshooting

### If Python WebSocket fails:
```bash
# Check Python version
python3 --version

# Install websocket-client
pip3 install websocket-client

# Test import
python3 -c "import websocket; print('WebSocket library installed')"
```

### If cache is not updating:
```bash
# Check cache status
php artisan tinker --execute="
echo 'Cache keys:';
print_r(Cache::get('truedata_live_data', []);
echo 'Last update: ' . Cache::get('truedata_last_update', 'NEVER');
"

# Force refresh cache
php artisan queue:work --once

# Clear and recreate cache
php artisan cache:clear
php artisan queue:work --once

# Check cache after refresh
php artisan tinker --execute="
echo 'Cache after refresh:';
echo 'Live Data: ' . (Cache::has('truedata_live_data') ? 'EXISTS' : 'MISSING');
echo 'Data Count: ' . count(Cache::get('truedata_live_data', []));
echo 'Last Update: ' . Cache::get('truedata_last_update', 'NEVER');
"
```

### If queue worker stops:
```bash
# Check queue status
php artisan queue:work --once

# Restart queue worker
nohup php artisan queue:work --daemon > /dev/null 2>&1 &
```

## 🔍 Detailed Data Sources

### **TrueData WebSocket Connection:**
- **Script:** `truedata_test.py`
- **Purpose:** Connects to TrueData WebSocket for real-time market data
- **Data:** Live stock prices, market indices, trading volumes
- **Frequency:** Continuous streaming during market hours

### **Laravel Job Processing:**
- **Job:** `FetchTrueDataJob`
- **Purpose:** Processes WebSocket data and stores in cache
- **Trigger:** Every 5 minutes via cron job
- **Output:** Formatted market data for frontend consumption

### **Cache Management:**
- **Storage:** Laravel Cache (Redis/File based)
- **Duration:** 1 hour for market data
- **Keys:** 
  - `truedata_live_data` - Stock prices and market data
  - `truedata_last_update` - Timestamp of last update
  - `truedata_market_data` - Market status and indices
  - `truedata_market_status` - Market open/closed status

### **Frontend Data Consumption:**
- **Component:** `TrueDataStockMarket.vue`
- **API Calls:** Axios requests to Laravel API endpoints
- **Real-time Updates:** Polling every 5 seconds
- **Data Display:** Live stock prices, market status, trading interface

### **Trading System Data:**
- **User Balance:** Fetched from `wallet_transactions` table
- **Trade Execution:** Stored in `ai_trading_orders` table
- **P&L Calculation:** Real-time calculation based on current market prices
- **Market Hours:** Validated against current time and weekday

### **API Authentication:**
- **Method:** Bearer Token (JWT)
- **Storage:** LocalStorage in browser
- **Headers:** `Authorization: Bearer {token}`
- **Middleware:** `auth:api` for protected routes

## 📊 Complete API Endpoints

### **TrueData Market Data APIs:**
- **`GET /api/truedata/dashboard`** - Get live market data and stock prices
- **`POST /api/truedata/search-stock`** - Search for stocks by symbol/name
- **`GET /api/truedata/market-status`** - Get market open/closed status

### **AI Trading APIs:**
- **`POST /api/ai-trading/execute-trade`** - Execute buy/sell trades
- **`GET /api/ai-trading/user-orders/{userId}`** - Get user's trading orders
- **`GET /api/ai-trading/user-balance/{userId}`** - Get user's live balance
- **`POST /api/ai-trading/orders/{orderId}/exit`** - Exit/close a trade
- **`GET /api/ai-trading/market-status`** - Get market status for trading
- **`GET /api/ai-trading/orders`** - Get all trading orders (admin)
- **`PATCH /api/ai-trading/orders/{orderId}/cancel`** - Cancel a pending order

### **Data Flow Architecture:**
```
TrueData WebSocket → Python Script → Laravel Job → Cache → API → Frontend
```

### **Database Schema:**
- **`users`** - User accounts and profiles
- **`wallet_transactions`** - User balance and transaction history
- **`ai_trading_orders`** - Trading orders with P&L tracking
- **`cache`** - Market data cache storage

## 📱 AI Trading System Features

- ✅ **Real-time Market Data** from TrueData WebSocket
- ✅ **Live Stock Prices** with Call/Put options
- ✅ **AI Trading Interface** for admins
- ✅ **User Balance Management** with wallet transactions
- ✅ **Trade Execution** with P&L tracking
- ✅ **Market Hours Validation** (9:15 AM - 3:30 PM IST)
- ✅ **Order Management** with exit functionality
- ✅ **Modern UI** with dark theme

## 🌐 Access URLs

- **Admin Panel:** `http://103.235.106.85/admin`
- **AI Trading:** `http://103.235.106.85/admin/ai-trading`
- **AI Trading Session:** `http://103.235.106.85/admin/ai-trading-session?userId={id}&userName={name}&userEmail={email}`
- **API Endpoints:** `http://103.235.106.85/api/`

## 🔄 Data Update Process

### **Automatic Updates (Every 5 minutes):**
1. **Cron Job** triggers `php artisan schedule:run`
2. **Laravel Scheduler** runs `FetchTrueDataJob`
3. **Python Script** connects to TrueData WebSocket
4. **Market Data** is fetched and processed
5. **Cache** is updated with new data
6. **Frontend** automatically refreshes

### **Manual Updates:**
```bash
# Method 1: Force refresh market data
php artisan queue:work --once

# Method 2: Use the refresh script
./refresh_stock_data.sh

# Method 3: Clear cache and recreate
php artisan cache:clear
php artisan queue:work --once

# Method 4: Manual cache population
php artisan tinker --execute="
use App\Jobs\FetchTrueDataJob;
dispatch(new FetchTrueDataJob());
echo 'Cache populated successfully';
"

# Method 5: Check cache status
php artisan tinker --execute="
echo 'Cache Status:';
echo 'Live Data: ' . (Cache::has('truedata_live_data') ? 'EXISTS' : 'MISSING');
echo 'Data Count: ' . count(Cache::get('truedata_live_data', []));
echo 'Last Update: ' . Cache::get('truedata_last_update', 'NEVER');
echo 'Market Status: ' . Cache::get('truedata_market_status', 'UNKNOWN');
"
```

### **Real-time Data Sources:**
- **TrueData WebSocket** - Live market data streaming
- **Market Hours** - 9:15 AM to 3:30 PM IST (Monday to Friday)
- **Cache Duration** - 1 hour for market data
- **Update Frequency** - Every 5 minutes during market hours

## 💾 Cache Management Commands

### **Create Cache:**
```bash
# Quick cache creation
php artisan queue:work --once

# Using refresh script
./refresh_stock_data.sh

# Manual cache population
php artisan tinker --execute="
use App\Jobs\FetchTrueDataJob;
dispatch(new FetchTrueDataJob());
echo 'Cache populated successfully';
"
```

### **Check Cache Status:**
```bash
# Basic cache check
php artisan tinker --execute="
echo 'Cache Status:';
echo 'Live Data: ' . (Cache::has('truedata_live_data') ? 'EXISTS' : 'MISSING');
echo 'Last Update: ' . Cache::get('truedata_last_update', 'NEVER');
"

# Detailed cache check
php artisan tinker --execute="
echo 'Detailed Cache Status:';
echo 'Live Data Count: ' . count(Cache::get('truedata_live_data', []));
echo 'Last Update: ' . Cache::get('truedata_last_update', 'NEVER');
echo 'Market Status: ' . Cache::get('truedata_market_status', 'UNKNOWN');
echo 'Market Data: ' . (Cache::has('truedata_market_data') ? 'EXISTS' : 'MISSING');
"
```

### **Clear Cache:**
```bash
# Clear all cache
php artisan cache:clear

# Clear specific cache keys
php artisan tinker --execute="
Cache::forget('truedata_live_data');
Cache::forget('truedata_last_update');
Cache::forget('truedata_market_data');
Cache::forget('truedata_market_status');
echo 'Cache cleared successfully';
"
```

### **Force Cache Refresh:**
```bash
# Clear and recreate cache
php artisan cache:clear
php artisan queue:work --once

# Check after refresh
php artisan tinker --execute="
echo 'Cache after refresh:';
echo 'Live Data: ' . (Cache::has('truedata_live_data') ? 'EXISTS' : 'MISSING');
echo 'Data Count: ' . count(Cache::get('truedata_live_data', []));
echo 'Last Update: ' . Cache::get('truedata_last_update', 'NEVER');
"
```

## 🎯 How to Use the System

### **For Administrators:**
1. **Login** with admin credentials: `admin@gmail.com` / `admin123`
2. **Go to AI Trading** section from admin panel
3. **Select User** to trade on behalf of
4. **View Live Market Data** with real-time stock prices
5. **Execute Trades** by selecting Call/Put options
6. **Manage Orders** and track P&L in real-time
7. **Exit Trades** when market is open

### **For Users:**
1. **Register/Login** to the system
2. **Add Money** to wallet for trading
3. **View Trading History** and P&L
4. **Monitor Live Balance** and transactions

### **System Workflow:**
```
User Registration → Wallet Funding → Admin Trading → Order Execution → P&L Tracking → Trade Exit
```

## 🔧 Technical Stack

### **Backend:**
- **Laravel 10** - PHP Framework
- **MySQL** - Database
- **Redis/File Cache** - Data caching
- **Python 3.6+** - WebSocket connection
- **Laravel Queues** - Background job processing

### **Frontend:**
- **Vue.js 3** - JavaScript Framework
- **Axios** - HTTP client
- **Bootstrap 5** - CSS Framework
- **Font Awesome** - Icons
- **Vite** - Build tool

### **External APIs:**
- **TrueData WebSocket** - Real-time market data
- **TrueData REST API** - Historical data and market status

## 📱 System Screenshots & Demo

### **Admin Panel:**
- User management interface
- AI Trading dashboard
- Order management system
- Real-time market data display

### **Trading Interface:**
- Live stock prices with Call/Put options
- Trade execution modal
- Order history with P&L tracking
- Market status indicators

## 🚀 Production Deployment Checklist

- [ ] Server access configured
- [ ] Database setup completed
- [ ] Python dependencies installed
- [ ] Laravel dependencies installed
- [ ] Frontend assets built
- [ ] Cache populated with initial data
- [ ] Cron jobs configured
- [ ] Queue worker running
- [ ] SSL certificate installed
- [ ] Environment variables configured
- [ ] Backup strategy implemented

## 📞 Support & Contact

**Development Team:** GainTradeX Development Team
**Email:** support@GainTradeX.com
**Documentation:** This README file
**Version:** 1.0.0
**Status:** Production Ready ✅

---

**Last Updated:** December 2024
**Version:** 1.0.0
**Status:** Production Ready ✅
**License:** Proprietary