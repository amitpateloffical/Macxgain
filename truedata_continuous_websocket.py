#!/usr/bin/env python3
# TrueData Continuous WebSocket Real-Time Data Fetcher for Laravel

import json
import time
import sys
import signal
from websocket._core import create_connection

class TrueDataWebSocket:
    def __init__(self):
        self.running = True
        self.ws = None
        self.realtime_port = 8084
        self.username = 'tdwsp759'
        self.password = 'mosh@759'
        
        # Signal handler for graceful shutdown
        signal.signal(signal.SIGINT, self.signal_handler)
        signal.signal(signal.SIGTERM, self.signal_handler)
        
        # All symbols to fetch
        self.symbols = [
            # Major Indices
            "NIFTY 50", "NIFTY BANK", "NIFTY IT", "NIFTY FMCG", "NIFTY AUTO", "NIFTY PHARMA", "NIFTY METAL", "NIFTY ENERGY", "SENSEX",
            "NIFTY REALTY", "NIFTY PSU BANK", "NIFTY PVT BANK", "NIFTY MEDIA", "NIFTY INFRA", "NIFTY COMMODITIES",
            
            # Large Cap Stocks
            "RELIANCE", "TCS", "HDFCBANK", "ICICIBANK", "SBIN", "BHARTIARTL", "ITC", "KOTAKBANK", "LT", "HINDUNILVR",
            "ASIANPAINT", "MARUTI", "AXISBANK", "NESTLEIND", "ULTRACEMCO", "SUNPHARMA", "TITAN", "POWERGRID", "NTPC", "ONGC",
            
            # Mid Cap Stocks
            "AARTIIND", "BRITANNIA", "COLPAL", "DMART", "EICHERMOT", "GILLETTE", "JKTYRE", "KAJARIACER", "LICHSGFIN",
            "MINDTREE", "OFSS", "PNB", "QUICKHEAL", "UJJIVAN", "WIPRO", "YESBANK", "ZEEL", "ADANIPORTS", "BAJFINANCE",
            "BAJAJFINSV", "DRREDDY", "GRASIM", "HCLTECH", "HDFCLIFE", "HEROMOTOCO", "INDUSINDBK", "INFY", "JSWSTEEL",
            
            # Futures
            "NIFTY-I", "BANKNIFTY-I", "UPL-I", "VEDL-I", "VOLTAS-I", "ZEEL-I", "RELIANCE-I", "TCS-I", "HDFCBANK-I",
            
            # Commodities
            "CRUDEOIL-I", "GOLDM-I", "SILVERM-I", "COPPER-I", "SILVER-I", "NATURALGAS-I", "ALUMINIUM-I", "ZINC-I",
            
            # MCX Indices
            "MCXCOMPDEX", "MCXMETAL", "MCXENERGY", "MCXAGRI"
        ]
        
        # Symbol ID mapping
        self.symbol_map = {
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
        
        self.market_data = {}

    def signal_handler(self, signum, frame):
        print(f"\nReceived signal {signum}, shutting down gracefully...", file=sys.stderr)
        self.running = False
        if self.ws:
            self.ws.close()

    def force_logout(self):
        """Force logout from all previous sessions"""
        import urllib.request
        import urllib.parse
        
        try:
            logout_url = f"https://api.truedata.in/logoutRequest?user={self.username}&password={self.password}&port={self.realtime_port}"
            print(f"Force logout URL: {logout_url}", file=sys.stderr)
            
            with urllib.request.urlopen(logout_url, timeout=10) as response:
                result = response.read().decode('utf-8')
                print(f"Force logout response: {result}", file=sys.stderr)
                return True
        except Exception as e:
            print(f"Force logout failed: {e}", file=sys.stderr)
            return False

    def connect(self):
        """Connect to TrueData WebSocket"""
        try:
            # Force logout from previous sessions
            print("Attempting force logout from previous sessions...", file=sys.stderr)
            self.force_logout()
            time.sleep(2)
            
            # Create WebSocket connection
            self.ws = create_connection(f"wss://push.truedata.in:{self.realtime_port}?user={self.username}&password={self.password}")
            
            # Wait for connection confirmation
            connection_msg = self.ws.recv()
            print(f"Connection: {connection_msg}", file=sys.stderr)
            
            # Check if connection is successful
            if "success" in connection_msg and "false" in connection_msg:
                print("Connection failed, trying to reconnect...", file=sys.stderr)
                self.ws.close()
                time.sleep(5)
                self.ws = create_connection(f"wss://push.truedata.in:{self.realtime_port}?user={self.username}&password={self.password}")
                connection_msg = self.ws.recv()
                print(f"Reconnection: {connection_msg}", file=sys.stderr)
            
            # Subscribe to symbols (respect server maxsymbols=50)
            symbols_to_send = self.symbols[:50]
            subscribe_msg = {
                "method": "addsymbol",
                "symbols": symbols_to_send
            }
            self.ws.send(json.dumps(subscribe_msg))
            
            # Wait for subscription confirmation
            subscribe_response = self.ws.recv()
            print(f"Subscription: {subscribe_response}", file=sys.stderr)
            
            # Process initial symbol list data
            self.process_initial_data(subscribe_response)
            
            return True
            
        except Exception as e:
            print(f"WebSocket connection error: {e}", file=sys.stderr)
            return False

    def process_initial_data(self, subscribe_response):
        """Process initial symbol list data"""
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
                        
                        self.market_data[symbol] = {
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

    def process_trade_data(self, data):
        """Process real-time trade data"""
        try:
            if 'trade' in data:
                trade = data['trade']
                symbol_id = trade[0]
                symbol = self.symbol_map.get(symbol_id, symbol_id)
                
                if symbol in self.market_data:
                    ltp = float(trade[2])
                    prev_close = self.market_data[symbol].get('prev_close', ltp)
                    change = ltp - prev_close
                    change_percent = (change / prev_close) * 100 if prev_close > 0 else 0
                    
                    # Update existing data with new trade
                    self.market_data[symbol].update({
                        'ltp': ltp,
                        'change': change,
                        'change_percent': round(change_percent, 2),
                        'high': max(self.market_data[symbol].get('high', ltp), float(trade[7]) if len(trade) > 7 else ltp),
                        'low': min(self.market_data[symbol].get('low', ltp), float(trade[8]) if len(trade) > 8 else ltp),
                        'volume': float(trade[6]) if len(trade) > 6 else self.market_data[symbol].get('volume', 0),
                        'timestamp': trade[1] if len(trade) > 1 else time.strftime('%Y-%m-%dT%H:%M:%S.000Z'),
                        'data_source': 'TrueData Real WebSocket Live'
                    })
                    
        except Exception as e:
            print(f"Error processing trade data: {e}", file=sys.stderr)

    def save_data(self):
        """Save market data to JSON file"""
        try:
            with open('market_data.json', 'w') as f:
                json.dump(self.market_data, f, indent=2)
            print(f"Data saved to market_data.json - {len(self.market_data)} symbols", file=sys.stderr)
        except Exception as e:
            print(f"Error saving data: {e}", file=sys.stderr)

    def run(self):
        """Main run loop"""
        print("🚀 Starting TrueData Continuous WebSocket...", file=sys.stderr)
        
        while self.running:
            try:
                if not self.ws or self.ws.connected == False:
                    print("Connecting to WebSocket...", file=sys.stderr)
                    if not self.connect():
                        print("Failed to connect, retrying in 10 seconds...", file=sys.stderr)
                        time.sleep(10)
                        continue
                
                # Set timeout for receiving data
                self.ws.settimeout(5.0)
                
                try:
                    result = self.ws.recv()
                    
                    # Parse JSON data
                    try:
                        data = json.loads(result)
                        self.process_trade_data(data)
                    except json.JSONDecodeError:
                        pass  # Skip non-JSON messages
                        
                except Exception as e:
                    print(f"Error receiving data: {e}", file=sys.stderr)
                    self.ws.close()
                    self.ws = None
                    continue
                
                # Save data every 5 seconds
                if int(time.time()) % 5 == 0:
                    self.save_data()
                
            except KeyboardInterrupt:
                print("Received keyboard interrupt, shutting down...", file=sys.stderr)
                break
            except Exception as e:
                print(f"Unexpected error: {e}", file=sys.stderr)
                time.sleep(5)
        
        # Final save
        self.save_data()
        if self.ws:
            self.ws.close()
        print("TrueData WebSocket stopped.", file=sys.stderr)

if __name__ == "__main__":
    websocket = TrueDataWebSocket()
    websocket.run()
