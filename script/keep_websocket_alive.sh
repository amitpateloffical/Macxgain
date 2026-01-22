#!/bin/bash

# Enhanced Keep WebSocket alive script
# This script will restart any service if it dies and monitor health

SCRIPT_NAME=$1
LOG_FILE=$2
MAX_RESTARTS=100
RESTART_COUNT=0
RESTART_DELAY=5

# Function to log with timestamp
log() {
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] $1" | tee -a "$LOG_FILE"
}

log "🔄 Starting keep-alive monitor for $SCRIPT_NAME"

# Function to check if process is healthy
check_health() {
    local pid=$1
    local script_name=$2
    
    if ! kill -0 "$pid" 2>/dev/null; then
        return 1
    fi
    
    # Check if process is responsive (for Python scripts)
    if [[ "$script_name" == *.py ]]; then
        # Check if process is consuming CPU (not stuck)
        local cpu_usage=$(ps -p "$pid" -o %cpu= 2>/dev/null | tr -d ' ')
        if [ -z "$cpu_usage" ] || [ "$cpu_usage" = "0.0" ]; then
            # Process might be stuck, check if it's been running too long without activity
            local runtime=$(ps -p "$pid" -o etime= 2>/dev/null | tr -d ' ')
            if [ -n "$runtime" ]; then
                # If running for more than 1 hour without CPU usage, consider it stuck
                if [[ "$runtime" == *"hour"* ]] || [[ "$runtime" == *"day"* ]]; then
                    log "⚠️  Process $script_name appears to be stuck (runtime: $runtime, CPU: $cpu_usage%)"
                    return 1
                fi
            fi
        fi
    fi
    
    return 0
}

while [ $RESTART_COUNT -lt $MAX_RESTARTS ]; do
    log "🚀 Starting $SCRIPT_NAME (attempt $((RESTART_COUNT + 1))/$MAX_RESTARTS)"
    
    if [ -f "$SCRIPT_NAME" ]; then
        # Start the script
        if [[ "$SCRIPT_NAME" == *.py ]]; then
            python3 "$SCRIPT_NAME" >> "$LOG_FILE" 2>&1 &
        else
            bash "$SCRIPT_NAME" >> "$LOG_FILE" 2>&1 &
        fi
        
        local pid=$!
        log "✅ Started $SCRIPT_NAME with PID $pid"
        
        # Monitor the process
        while check_health "$pid" "$SCRIPT_NAME"; do
            sleep 10
        done
        
        log "⚠️  $SCRIPT_NAME (PID $pid) stopped or became unresponsive"
        
        # Wait a bit before restarting
        sleep $RESTART_DELAY
        RESTART_COUNT=$((RESTART_COUNT + 1))
        
        # Increase restart delay exponentially (max 60 seconds)
        if [ $RESTART_DELAY -lt 60 ]; then
            RESTART_DELAY=$((RESTART_DELAY * 2))
        fi
    else
        log "❌ Script $SCRIPT_NAME not found, waiting 30 seconds..."
        sleep 30
    fi
done

log "💀 Maximum restart attempts ($MAX_RESTARTS) reached for $SCRIPT_NAME. Giving up."
exit 1
