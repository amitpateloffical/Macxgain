#!/bin/bash
echo "🔍 Quick Status Check:"
echo "====================="
./monitor_data_flow.sh | grep -E "(API|Cache|WebSocket|Summary)"
