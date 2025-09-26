#!/usr/bin/env python3
# TrueData WebSocket Data Fetcher for Laravel (Limited Time Version)

import json
import time
import sys
from websocket._core import create_connection

def force_logout(username, password, port=8086):
    """Force logout from all previous sessions"""
    import urllib.request
    import urllib.parse
    
    try:
        logout_url = f"https://api.truedata.in/logoutRequest?user={username}&password={password}&port={port}"
        print(f"Force logout URL: {logout_url}", file=sys.stderr)
        
        with urllib.request.urlopen(logout_url, timeout=10) as response:
            result = response.read().decode('utf-8')
            print(f"Force logout response: {result}", file=sys.stderr)
            return True
    except Exception as e:
        print(f"Force logout failed: {e}", file=sys.stderr)
        return False

def fetch_live_data():
    """Fetch live market data from TrueData WebSocket"""
    
    # Configuration
    realtime_port = 8086  # Sandbox port for trial
    username = 'tdwsp759'
    password = 'mosh@759'
    
    # Force logout from previous sessions
    print("Attempting force logout from previous sessions...", file=sys.stderr)
    force_logout(username, password, realtime_port)
    time.sleep(2)  # Wait 2 seconds after logout
    
    # Symbols prioritized so first 50 are EQUITY stocks (subscribe limit ≈ 50)
    symbols = [
        # Requested indices first
        "NIFTY 50", "NIFTY BANK", "SENSEX",

        # Core banking & low-price favorites
        "YESBANK", "PNB", "HDFCBANK", "ICICIBANK", "SBIN", "AXISBANK", "IDFCFIRSTB", "BANKBARODA", "CANBK", "FEDERALBNK",
        "INDUSINDBK", "BANDHANBNK", "AUBANK", "KOTAKBANK",

        # Popular low to mid price PSU/infra/energy
        "IOC", "ONGC", "BPCL", "GAIL", "SAIL", "BHEL", "PFC", "RECLTD", "IRFC", "NBCC", "IEX", "COALINDIA",

        # Additional affordable equities to reach 50+
        "TATASTEEL", "NMDC", "NATIONALUM", "HINDCOPPER", "IDEA", "SUZLON", "RPOWER", "TATAPOWER", "JSWSTEEL", "POWERGRID",
        "TVSMOTOR", "M&M", "DABUR", "HAVELLS", "HINDALCO", "ADANIPORTS", "ITC", "BHARTIARTL", "RELIANCE", "LT",
        "HINDUNILVR", "ASIANPAINT", "MARUTI", "NESTLEIND", "SUNPHARMA", "TITAN", "NTPC",

        # Keep the rest after first ~60
        "INFY", "HCLTECH", "TECHM", "UPL", "SHREECEM", "BRITANNIA", "CIPLA", "DIVISLAB", "EICHERMOT", "WIPRO",
        "HDFCLIFE", "SBILIFE", "BAJAJ-AUTO", "ICICIPRULI", "PIDILITIND", "TATACONSUM", "ADANIENT", "LTIM", "APOLLOHOSP",
        "SIEMENS", "AMBUJACEM", "DMART", "ABB",

        # Other indices removed per request to favor stocks

        # Futures / Commodities last
        "NIFTY-I", "BANKNIFTY-I", "RELIANCE-I", "TCS-I", "HDFCBANK-I",
        "CRUDEOIL-I", "GOLDM-I", "SILVERM-I", "COPPER-I", "SILVER-I", "NATURALGAS-I", "ALUMINIUM-I", "ZINC-I",
        "MCXCOMPDEX", "MCXMETAL", "MCXENERGY", "MCXAGRI"
    ]
    
    ws = None
    market_data = {}
    
    try:
        # Create WebSocket connection
        ws = create_connection(f"wss://push.truedata.in:{realtime_port}?user={username}&password={password}")
        
        # Wait for connection confirmation
        connection_msg = ws.recv()
        print(f"Connection: {connection_msg}", file=sys.stderr)
        
        # Check if connection is successful
        if "success" in connection_msg and "false" in connection_msg:
            print("Connection failed, trying to reconnect...", file=sys.stderr)
            ws.close()
            time.sleep(5)  # Wait 5 seconds before reconnecting
            ws = create_connection(f"wss://push.truedata.in:{realtime_port}?user={username}&password={password}")
            connection_msg = ws.recv()
            print(f"Reconnection: {connection_msg}", file=sys.stderr)
            
            # If still failing, wait longer and try once more
            if "success" in connection_msg and "false" in connection_msg:
                print("Second reconnection attempt...", file=sys.stderr)
                ws.close()
                time.sleep(10)  # Wait 10 seconds
                ws = create_connection(f"wss://push.truedata.in:{realtime_port}?user={username}&password={password}")
                connection_msg = ws.recv()
                print(f"Second reconnection: {connection_msg}", file=sys.stderr)
        
        # Subscribe to first 50 (equities-first ordering ensures 50 stocks)
        subscribe_msg = {
            "method": "addsymbol",
            "symbols": symbols[:50]
        }
        ws.send(json.dumps(subscribe_msg))
        
        # Wait for subscription confirmation
        subscribe_response = ws.recv()
        print(f"Subscription: {subscribe_response}", file=sys.stderr)
        
        # Process initial symbol list data
        try:
            sub_data = json.loads(subscribe_response)
            if 'symbollist' in sub_data:
                for symbol_data in sub_data['symbollist']:
                    if len(symbol_data) >= 10:
                        symbol = symbol_data[0]
                        ltp = float(symbol_data[3])
                        prev_close = float(symbol_data[10])
                        change = ltp - prev_close
                        change_percent = (change / prev_close) * 100 if prev_close > 0 else 0
                        
                        market_data[symbol] = {
                            'symbol': symbol,
                            'ltp': ltp,
                            'change': change,
                            'change_percent': round(change_percent, 2),
                            'high': float(symbol_data[7]),
                            'low': float(symbol_data[8]),
                            'open': float(symbol_data[9]),
                            'prev_close': prev_close,
                            'volume': float(symbol_data[6]) if len(symbol_data) > 6 else 0,
                            'timestamp': symbol_data[2],
                            'data_source': 'TrueData Real WebSocket'
                        }
        except Exception as e:
            print(f"Error processing symbol list: {e}", file=sys.stderr)
        
        # Collect data continuously for real-time updates (limited time)
        start_time = time.time()
        data_count = 0
        
        # Collect data for 3 seconds to get fresh data
        while (time.time() - start_time) < 3:
            try:
                result = ws.recv()
                data_count += 1
                
                # Parse JSON data
                try:
                    data = json.loads(result)
                    
                    # Process trade data
                    if 'trade' in data:
                        trade = data['trade']
                        symbol_id = trade[0]
                        
                        # Map symbol ID to symbol name
                        symbol_map = {
                            "200000001": "NIFTY 50",
                            "200000004": "NIFTY BANK",
                            "800000372": "MCXCOMPDEX",
                            "100000011": "AARTIIND",
                            "100000243": "BRITANNIA",
                            "100000310": "COLPAL",
                            "100000382": "DMART",
                            "100000409": "EICHERMOT",
                            "100000508": "GILLETTE",
                            "100000589": "HDFCBANK",
                            "100000647": "ICICIBANK",
                            "100000781": "JKTYRE",
                            "100000807": "KAJARIACER",
                            "100000893": "LICHSGFIN",
                            "100000995": "MINDTREE",
                            "100001105": "OFSS",
                            "100001182": "PNB",
                            "100001229": "QUICKHEAL",
                            "100001262": "RELIANCE",
                            "100001337": "SBIN",
                            "100001528": "TCS",
                            "100001598": "UJJIVAN",
                            "100001692": "WIPRO",
                            "100001700": "YESBANK",
                            "100001701": "ZEEL",
                            "900000596": "NIFTY-I",
                            "900000110": "BANKNIFTY-I",
                            "900000840": "UPL-I",
                            "900000846": "VEDL-I",
                            "900000852": "VOLTAS-I",
                            "900000870": "ZEEL-I",
                            "950000072": "CRUDEOIL-I",
                            "950000114": "GOLDM-I",
                            "950000182": "SILVERM-I",
                            "950000026": "COPPER-I",
                            "950000172": "SILVER-I"
                        }
                        
                        symbol = symbol_map.get(symbol_id, symbol_id)
                        ltp = float(trade[2])
                        prev_close = float(trade[5]) if len(trade) > 5 else ltp
                        change = ltp - prev_close
                        change_percent = (change / prev_close) * 100 if prev_close > 0 else 0
                        
                        market_data[symbol] = {
                            'symbol': symbol,
                            'ltp': ltp,
                            'change': change,
                            'change_percent': round(change_percent, 2),
                            'high': float(trade[7]) if len(trade) > 7 else ltp,
                            'low': float(trade[8]) if len(trade) > 8 else ltp,
                            'open': float(trade[7]) if len(trade) > 7 else ltp,
                            'prev_close': prev_close,
                            'volume': float(trade[6]) if len(trade) > 6 else 0,
                            'timestamp': trade[1] if len(trade) > 1 else time.strftime('%Y-%m-%dT%H:%M:%S.000Z'),
                            'data_source': 'TrueData Real WebSocket'
                        }
                        
                except json.JSONDecodeError:
                    pass  # Skip non-JSON messages
                    
            except Exception as e:
                print(f"Error processing message: {e}", file=sys.stderr)
                break
                
        print(f"Collected {data_count} messages, {len(market_data)} symbols", file=sys.stderr)
        
    except Exception as e:
        print(f"WebSocket error: {e}", file=sys.stderr)
        return None
        
    finally:
        if ws:
            ws.close()
    
    return market_data

def generate_fallback_data():
    """Generate fallback market data when WebSocket connection fails"""
    import random
    from datetime import datetime
    
    fallback_data = {}
    base_prices = {
        'NIFTY 50': 19500, 'NIFTY BANK': 45000, 'NIFTY IT': 32000, 'NIFTY FMCG': 28000,
        'RELIANCE': 2500, 'TCS': 3800, 'HDFCBANK': 1600, 'ICICIBANK': 950,
        'SBIN': 580, 'BHARTIARTL': 900, 'ITC': 450, 'KOTAKBANK': 1800,
        'LT': 3200, 'HINDUNILVR': 2500, 'ASIANPAINT': 3200, 'MARUTI': 10000,
        'AXISBANK': 1100, 'NESTLEIND': 18000, 'ULTRACEMCO': 7000, 'SUNPHARMA': 1000
    }
    
    symbol_list = [
        'NIFTY 50', 'NIFTY BANK', 'NIFTY IT', 'NIFTY FMCG', 'NIFTY AUTO', 'NIFTY PHARMA', 'NIFTY METAL', 'NIFTY ENERGY',
        'RELIANCE', 'TCS', 'HDFCBANK', 'ICICIBANK', 'SBIN', 'BHARTIARTL', 'ITC', 'KOTAKBANK',
        'LT', 'HINDUNILVR', 'ASIANPAINT', 'MARUTI'
    ]
    
    for symbol in symbol_list:  # Generate data for first 20 symbols
        base_price = base_prices.get(symbol, 1000)
        # Add some random variation
        variation = random.uniform(-0.05, 0.05)  # ±5% variation
        ltp = base_price * (1 + variation)
        change = ltp - base_price
        change_percent = (change / base_price) * 100
        
        fallback_data[symbol] = {
            'symbol': symbol,
            'ltp': round(ltp, 2),
            'change': round(change, 2),
            'change_percent': round(change_percent, 2),
            'high': round(ltp * 1.02, 2),
            'low': round(ltp * 0.98, 2),
            'open': round(base_price, 2),
            'prev_close': round(base_price, 2),
            'volume': random.randint(100000, 1000000),
            'timestamp': datetime.now().strftime('%Y-%m-%d %H:%M:%S'),
            'data_source': 'TrueData Fallback (Connection Failed)'
        }
    
    return fallback_data

if __name__ == "__main__":
    data = fetch_live_data()
    if data:
        print(json.dumps(data, indent=2))
        # Write real data to JSON file
        try:
            with open('market_data.json', 'w') as f:
                json.dump(data, f, indent=2)
            print(f"Real data written to market_data.json", file=sys.stderr)
        except Exception as e:
            print(f"Error writing real data to JSON file: {e}", file=sys.stderr)
    else:
        # Generate fallback data if connection fails
        fallback_data = generate_fallback_data()
        print(json.dumps(fallback_data, indent=2))
        # Write fallback data to JSON file
        try:
            with open('market_data.json', 'w') as f:
                json.dump(fallback_data, f, indent=2)
            print(f"Fallback data written to market_data.json", file=sys.stderr)
        except Exception as e:
            print(f"Error writing fallback data to JSON file: {e}", file=sys.stderr)
