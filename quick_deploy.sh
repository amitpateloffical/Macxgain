#!/bin/bash

# Quick Deploy Script for Macxgain
# Simple one-command deployment

echo "🚀 Macxgain Quick Deploy"
echo "========================"

# Check if deploy.sh exists
if [ ! -f "deploy.sh" ]; then
    echo "❌ deploy.sh not found!"
    exit 1
fi

# Make sure deploy.sh is executable
chmod +x deploy.sh

# Run deployment
echo "Starting deployment..."
./deploy.sh deploy

echo ""
echo "✅ Quick deployment completed!"
echo ""
echo "🌐 Access your application at: http://localhost"
echo "🗄️  Database management: http://localhost:8080"
echo ""
echo "📊 Check status: ./deploy.sh status"
echo "📋 View logs: ./deploy.sh logs"
echo "🔄 Rollback if needed: ./deploy.sh rollback"
