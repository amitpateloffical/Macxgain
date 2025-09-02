# TrueData Real Time API - Test script for Laravel
# This script runs for limited time and outputs data

import time
import json
from websocket._core import create_connection

realtime_port = 8086  # 8082 for production & 8086 for sandbox environment during trial

username = 'Trial189'
password = 'patel189'

print("Creating a connection with the server...")

ws = ''
try:
    ws = create_connection(f"wss://push.truedata.in:{realtime_port}?user={username}&password={password}")
    print("Sending 'Connection Request'... for desired symbols ... ")
    print(ws.recv())
    print("Connection Established!")
    print("Adding Symbols, Now...")

    ws.send('{"method": "addsymbol", "symbols":["NIFTY 50","NIFTY BANK","MCXCOMPDEX","AARTIIND",'
            '"BRITANNIA","COLPAL","DMART","EICHERMOT","GILLETTE","HDFCBANK","ICICIBANK","JKTYRE","KAJARIACER",'
            '"LICHSGFIN","MINDTREE","OFSS","PNB","QUICKHEAL","RELIANCE","SBIN","TCS","UJJIVAN","WIPRO","YESBANK",'
            '"ZEEL","NIFTY-I","BANKNIFTY-I","UPL-I","VEDL-I",'
            '"VOLTAS-I","ZEEL-I","CRUDEOIL-I","GOLDM-I","SILVERM-I","COPPER-I", "SILVER-I"]}')

    # Run for 5 seconds only
    start_time = time.time()
    data_count = 0
    
    while time.time() - start_time < 5:
        result = ws.recv()
        data_count += 1
        print(f"Data #{data_count}: {result}")
        
        # Parse and show some key data
        try:
            data = json.loads(result)
            if 'symbollist' in data:
                print(f"✅ Received {data['symbolsadded']} symbols data")
            elif 'trade' in data:
                symbol_id = data['trade'][0] if data['trade'] else 'Unknown'
                price = data['trade'][2] if len(data['trade']) > 2 else 'N/A'
                print(f"📈 Trade: {symbol_id} - Price: {price}")
            elif 'bidask' in data:
                symbol_id = data['bidask'][0] if data['bidask'] else 'Unknown'
                bid = data['bidask'][2] if len(data['bidask']) > 2 else 'N/A'
                ask = data['bidask'][4] if len(data['bidask']) > 4 else 'N/A'
                print(f"💰 Bid/Ask: {symbol_id} - Bid: {bid}, Ask: {ask}")
        except:
            pass
    
    print(f"\n✅ Test completed! Received {data_count} data packets in 5 seconds")
    ws.close()
    
except ConnectionError as error:
    print(f"❌ Connection Error: {error}")
    if ws:
        ws.close()
except Exception as error:
    print(f"❌ Error: {error}")
    if ws:
        ws.close()
