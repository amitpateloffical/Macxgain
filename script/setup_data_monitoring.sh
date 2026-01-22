#!/bin/bash

# Setup Data Monitoring Script
# This script sets up automatic data monitoring and refresh

echo "🔧 Setting up data monitoring for admin/stock-market page..."

# Get the current directory
CURRENT_DIR=$(pwd)

# Create a simple cron job that runs every 2 minutes
CRON_JOB="*/2 * * * * cd $CURRENT_DIR && ./ensure_data_flow.sh >> data_flow.log 2>&1"

# Add to crontab if not already present
if ! crontab -l 2>/dev/null | grep -q "ensure_data_flow.sh"; then
    echo "📅 Adding cron job for data monitoring..."
    (crontab -l 2>/dev/null; echo "$CRON_JOB") | crontab -
    echo "✅ Cron job added: runs every 2 minutes"
else
    echo "✅ Cron job already exists"
fi

# Create a simple status check script
cat > check_status.sh << 'EOF'
#!/bin/bash
echo "🔍 Quick Status Check:"
echo "====================="
./monitor_data_flow.sh | grep -E "(API|Cache|WebSocket|Summary)"
EOF

chmod +x check_status.sh

echo ""
echo "🎉 Setup Complete!"
echo ""
echo "📋 Available commands:"
echo "  ./monitor_data_flow.sh  - Full status check"
echo "  ./check_status.sh       - Quick status check"
echo "  ./ensure_data_flow.sh   - Force data refresh"
echo ""
echo "📊 Cron job will run every 2 minutes to ensure data availability"
echo "📝 Logs will be saved to data_flow.log"
echo ""
echo "🚀 Your admin/stock-market page should now have continuous data flow!"
