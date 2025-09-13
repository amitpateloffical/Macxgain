# 🚀 Server Deployment Commands for Data Population

## **Step 1: Pull Latest Code**
```bash
git pull origin main
```

## **Step 2: Install Dependencies**
```bash
composer install --no-dev --optimize-autoloader
npm install
npm run build
```

## **Step 3: Clear Cache & Migrate**
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan migrate --force
```

## **Step 4: Start Services**
```bash
# Start Laravel server
php artisan serve --host=0.0.0.0 --port=8000 &

# Start WebSocket daemon
nohup python3 truedata_continuous_websocket.py > websocket.log 2>&1 &
echo $! > websocket.pid

# Start queue worker
nohup php artisan queue:work --daemon > queue.log 2>&1 &
echo $! > queue.pid
```

## **Step 5: Populate Data**
```bash
# Populate cache with fresh data
php artisan truedata:populate-cache

# Process any pending jobs
php artisan queue:work --once

# Verify data
php artisan tinker --execute="echo 'Data Count: ' . count(Cache::get('truedata_live_data', []));"
```

## **Step 6: Setup Monitoring**
```bash
# Make scripts executable
chmod +x ensure_data_flow.sh
chmod +x monitor_data_flow.sh
chmod +x setup_data_monitoring.sh

# Setup automatic monitoring
./setup_data_monitoring.sh
```

## **Step 7: Verify Everything**
```bash
# Check status
./monitor_data_flow.sh

# Test API
curl -s http://localhost:8000/api/truedata/live-data | jq '.success, .data_count'
```

## **Quick Commands for Daily Use:**

### **Check Status:**
```bash
./monitor_data_flow.sh
```

### **Force Data Refresh:**
```bash
./ensure_data_flow.sh
```

### **Quick Status:**
```bash
./check_status.sh
```

### **Restart WebSocket:**
```bash
kill $(cat websocket.pid) 2>/dev/null
nohup python3 truedata_continuous_websocket.py > websocket.log 2>&1 &
echo $! > websocket.pid
```

### **Restart Queue Worker:**
```bash
kill $(cat queue.pid) 2>/dev/null
nohup php artisan queue:work --daemon > queue.log 2>&1 &
echo $! > queue.pid
```

## **Expected Results:**
- ✅ API returns 50+ symbols
- ✅ NIFTY 50 price shows correctly
- ✅ WebSocket running and connected
- ✅ Admin page shows live data
- ✅ Auto-refresh every 2 minutes

## **Troubleshooting:**
If data not showing:
1. Run: `./ensure_data_flow.sh`
2. Check: `./monitor_data_flow.sh`
3. Restart WebSocket if needed
4. Check logs: `tail -f websocket.log`
