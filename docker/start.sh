#!/bin/bash

# Exit on any error
set -e

# Function to log messages
log() {
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] $1"
}

log "Starting Macxgain application..."

# Wait for database to be ready (if using external database)
# Uncomment and modify if needed:
# log "Waiting for database connection..."
# while ! php artisan db:show --quiet; do
#     echo "Waiting for database connection..."
#     sleep 2
# done

# Run migrations
log "Running database migrations..."
php artisan migrate --force

# Clear and cache config
log "Optimizing Laravel configuration..."
php artisan config:clear
php artisan config:cache

# Clear and cache routes
php artisan route:clear
php artisan route:cache

# Clear and cache views
php artisan view:clear
php artisan view:cache

# Clear and cache events
php artisan event:clear
php artisan event:cache

# Optimize autoloader
php artisan optimize

# Start background services
log "Starting background services..."

# Start TrueData WebSocket daemon in background with keep-alive
if [ -f "truedata_websocket.py" ]; then
    log "Starting TrueData WebSocket daemon with keep-alive..."
    nohup /usr/local/bin/keep_websocket_alive.sh truedata_websocket.py /var/log/truedata_websocket.log > /var/log/truedata_keepalive.log 2>&1 &
    echo $! > /var/run/truedata_keepalive.pid
fi

# Start scheduler in background with keep-alive
if [ -f "start_scheduler.sh" ]; then
    log "Starting Laravel scheduler with keep-alive..."
    nohup /usr/local/bin/keep_websocket_alive.sh start_scheduler.sh /var/log/scheduler.log > /var/log/scheduler_keepalive.log 2>&1 &
    echo $! > /var/run/scheduler_keepalive.pid
fi

# Start WebSocket daemon in background with keep-alive
if [ -f "start_websocket_daemon.sh" ]; then
    log "Starting WebSocket daemon with keep-alive..."
    nohup /usr/local/bin/keep_websocket_alive.sh start_websocket_daemon.sh /var/log/websocket.log > /var/log/websocket_keepalive.log 2>&1 &
    echo $! > /var/run/websocket_keepalive.pid
fi

# Start cache warming process
log "Starting cache warming process..."
nohup php artisan cache:warm > /var/log/cache_warm.log 2>&1 &
echo $! > /var/run/cache_warm.pid

# Set up signal handlers for graceful shutdown
cleanup() {
    log "Shutting down services..."
    
    # Kill keep-alive processes
    [ -f /var/run/truedata_keepalive.pid ] && kill $(cat /var/run/truedata_keepalive.pid) 2>/dev/null || true
    [ -f /var/run/scheduler_keepalive.pid ] && kill $(cat /var/run/scheduler_keepalive.pid) 2>/dev/null || true
    [ -f /var/run/websocket_keepalive.pid ] && kill $(cat /var/run/websocket_keepalive.pid) 2>/dev/null || true
    [ -f /var/run/cache_warm.pid ] && kill $(cat /var/run/cache_warm.pid) 2>/dev/null || true
    
    # Kill actual service processes
    pkill -f "truedata_websocket.py" 2>/dev/null || true
    pkill -f "start_scheduler.sh" 2>/dev/null || true
    pkill -f "start_websocket_daemon.sh" 2>/dev/null || true
    pkill -f "artisan schedule:work" 2>/dev/null || true
    
    # Wait a bit for graceful shutdown
    sleep 2
    
    # Force kill if still running
    pkill -9 -f "truedata_websocket.py" 2>/dev/null || true
    pkill -9 -f "start_scheduler.sh" 2>/dev/null || true
    pkill -9 -f "start_websocket_daemon.sh" 2>/dev/null || true
    
    # Stop Apache
    apache2ctl stop
    
    log "Shutdown complete"
    exit 0
}

# Trap signals
trap cleanup SIGTERM SIGINT

log "Starting Apache web server..."
# Start Apache in foreground
apache2-foreground

