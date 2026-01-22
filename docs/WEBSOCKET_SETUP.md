# TrueData WebSocket Automatic Management

## 🚀 Overview

The GainTradeX Trading Platform now includes automatic WebSocket management that starts the TrueData WebSocket script during market hours without manual intervention.

## ⏰ Automatic Schedule

The WebSocket script automatically:
- **Starts** at 9:15 AM IST (market open)
- **Runs** during market hours (9:15 AM - 3:30 PM IST)
- **Stops** at 3:30 PM IST (market close)
- **Works** Monday to Friday only
- **Skips** weekends and holidays

## 🛠️ Setup Instructions

### 1. Start the Project
```bash
# Start everything automatically
./start_project.sh
```

This will:
- Start Laravel development server
- Start Laravel scheduler
- Check market hours and start WebSocket if needed
- Provide management commands

### 2. Manual WebSocket Management
```bash
# Check WebSocket status
php artisan truedata:websocket status

# Start WebSocket manually
php artisan truedata:websocket start

# Stop WebSocket
php artisan truedata:websocket stop

# Restart WebSocket
php artisan truedata:websocket restart
```

### 3. Start Only Scheduler
```bash
# Start only the Laravel scheduler
./start_scheduler.sh
```

## 📊 How It Works

### Automatic Process
1. **Laravel Scheduler** runs every minute
2. **Checks Market Hours** (9:15 AM - 3:30 PM IST, Monday-Friday)
3. **Starts WebSocket** if market is open and script not running
4. **Logs Activity** in `storage/logs/laravel.log`

### Market Hours Logic
```php
// Market hours: Monday to Friday, 9:15 AM to 3:30 PM IST
$isWeekday = $dayOfWeek >= 1 && $dayOfWeek <= 5;
$isMarketHours = ($hour > 9 || ($hour == 9 && $minute >= 15)) && 
                ($hour < 15 || ($hour == 15 && $minute <= 30));
```

## 🔧 Technical Details

### Files Modified
- `app/Providers/AppServiceProvider.php` - Added automatic scheduling
- `app/Console/Commands/ManageTrueDataWebSocket.php` - Management commands
- `start_project.sh` - Complete project startup
- `start_scheduler.sh` - Scheduler only startup

### Dependencies
- Laravel Scheduler (`php artisan schedule:work`)
- Python 3 with websocket library
- `truedata_websocket.py` script

## 📝 Logs and Monitoring

### View Logs
```bash
# Application logs
tail -f storage/logs/laravel.log

# WebSocket process
ps aux | grep truedata_websocket
```

### Log Messages
- `TrueData WebSocket script started successfully`
- `TrueData WebSocket script is already running`
- `TrueData WebSocket script stopped successfully`

## 🎯 Benefits

### For Developers
- ✅ **No Manual Intervention** - WebSocket starts automatically
- ✅ **Market-Aware** - Only runs during trading hours
- ✅ **Process Management** - Prevents duplicate processes
- ✅ **Easy Commands** - Simple management commands
- ✅ **Comprehensive Logging** - Full activity tracking

### For Users
- ✅ **Live Data** - Real-time market data during trading hours
- ✅ **Reliable** - Automatic restart if process fails
- ✅ **Efficient** - No unnecessary resource usage outside market hours
- ✅ **Seamless** - Works without user intervention

## 🚨 Troubleshooting

### WebSocket Not Starting
1. Check if Python 3 is installed: `python3 --version`
2. Check if websocket library is installed: `pip3 install websocket-client`
3. Check logs: `tail -f storage/logs/laravel.log`
4. Manual start: `php artisan truedata:websocket start`

### Scheduler Not Working
1. Ensure Laravel scheduler is running: `php artisan schedule:work`
2. Check if AppServiceProvider is loaded
3. Verify market hours logic

### Process Issues
1. Check running processes: `ps aux | grep truedata_websocket`
2. Kill stuck processes: `pkill -f truedata_websocket.py`
3. Restart: `php artisan truedata:websocket restart`

## 📈 Market Data Flow

```
🕐 Market Hours Check (Every Minute)
├── 9:15 AM IST → Start WebSocket
├── 9:15 AM - 3:30 PM → Keep Running
├── 3:30 PM IST → Stop WebSocket
└── Weekend/Holiday → Skip
```

## 🎉 Result

Now when you start the project with `./start_project.sh`, the WebSocket script will automatically:
- Start during market hours
- Provide live data to the trading platform
- Stop when market closes
- Restart the next trading day

**No more manual WebSocket management needed!** 🚀
