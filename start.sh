#!/bin/bash

# Macxgain Project Startup Script
# This script starts both the Laravel backend and Vite frontend servers

echo "🚀 Starting Macxgain Project..."
echo "=================================="

# Check if we're in the correct directory
if [ ! -f "artisan" ]; then
    echo "❌ Error: Please run this script from the Macxgain project root directory"
    exit 1
fi

# Function to cleanup background processes on exit
cleanup() {
    echo ""
    echo "🛑 Stopping servers..."
    kill $LARAVEL_PID $VITE_PID 2>/dev/null
    exit 0
}

# Set up signal handlers
trap cleanup SIGINT SIGTERM

# Start Laravel backend server
echo "📡 Starting Laravel backend server..."
php artisan serve --host=127.0.0.1 --port=8000 &
LARAVEL_PID=$!

# Wait a moment for Laravel to start
sleep 2

# Start Vite frontend development server
echo "🎨 Starting Vite frontend server..."
npm run dev &
VITE_PID=$!

# Wait a moment for Vite to start
sleep 3

echo ""
echo "✅ Both servers are now running!"
echo "=================================="
echo "🌐 Laravel Backend: http://127.0.0.1:8000"
echo "🎨 Vite Frontend:   http://localhost:5173"
echo "🏠 Home Page:       http://127.0.0.1:8000/"
echo "🔐 Login:           http://127.0.0.1:8000/login"
echo "📝 Register:        http://127.0.0.1:8000/register"
echo ""
# echo "📋 Login Credentials:"
# echo "   Email: admin@gmail.com"
# echo "   Password: admin123"
echo ""
echo "⏹️  Press Ctrl+C to stop both servers"
echo "=================================="

# Keep the script running and show server logs
wait
