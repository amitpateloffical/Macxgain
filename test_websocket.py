#!/usr/bin/env python3
# Test script to verify WebSocket data

import json
import time
import os

def test_websocket_data():
    """Test if WebSocket is providing real-time data"""
    
    print("🔍 Testing WebSocket Data...")
    
    # Check if market_data.json exists
    if not os.path.exists('market_data.json'):
        print("❌ market_data.json not found!")
        return False
    
    # Read the data
    try:
        with open('market_data.json', 'r') as f:
            data = json.load(f)
    except Exception as e:
        print(f"❌ Error reading market_data.json: {e}")
        return False
    
    if not data:
        print("❌ No data in market_data.json!")
        return False
    
    print(f"✅ Found {len(data)} symbols in market_data.json")
    
    # Check data quality
    real_time_count = 0
    static_count = 0
    
    for symbol, info in data.items():
        if info.get('data_source') == 'TrueData Real WebSocket Live':
            real_time_count += 1
        else:
            static_count += 1
        
        # Check if change is not zero (indicating real movement)
        if info.get('change', 0) != 0:
            print(f"📈 {symbol}: LTP={info.get('ltp')}, Change={info.get('change')}, Change%={info.get('change_percent')}")
    
    print(f"\n📊 Data Summary:")
    print(f"   Real-time updates: {real_time_count}")
    print(f"   Static data: {static_count}")
    
    # Check file age
    file_age = time.time() - os.path.getmtime('market_data.json')
    print(f"   File age: {file_age:.1f} seconds")
    
    if file_age > 60:
        print("⚠️  Warning: Data file is older than 60 seconds")
    
    if real_time_count > 0:
        print("✅ WebSocket is providing real-time data!")
        return True
    else:
        print("❌ WebSocket is only providing static data")
        return False

if __name__ == "__main__":
    test_websocket_data()
