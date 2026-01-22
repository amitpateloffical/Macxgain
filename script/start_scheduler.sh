#!/bin/bash

# Laravel Scheduler Startup Script
# This script starts the Laravel scheduler for automatic WebSocket management

echo "🚀 Starting Laravel Scheduler for TrueData WebSocket Management..."

# Navigate to project directory
cd /Users/amitpatel/Documents/GitHub/GainTradeX

# Start Laravel scheduler
echo "📅 Starting Laravel scheduler..."
php artisan schedule:work

echo "✅ Laravel Scheduler started successfully!"
echo "🕐 WebSocket script will automatically start during market hours (9:15 AM - 3:30 PM IST, Monday to Friday)"
echo "📊 Check logs with: tail -f storage/logs/laravel.log"
