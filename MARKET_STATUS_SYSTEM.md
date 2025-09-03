# Market Status Based Data System

## 🎯 **Overview**
The system now intelligently handles data fetching based on market status:

- **Market Open**: Fetches live data every 30 seconds
- **Market Closed**: Uses historical data (cached for 24 hours, no refresh)

## 📊 **Market Status Logic**

### **Market Open (9:15 AM - 3:30 PM IST)**
- ✅ **Live Data**: Real-time data from TrueData WebSocket
- 🔄 **Refresh**: Every 30 seconds
- 💾 **Cache**: 30 seconds expiry
- 🏷️ **Data Type**: `LIVE`

### **Market Closed (Outside trading hours)**
- 📊 **Historical Data**: Last trading day data
- 🚫 **No Refresh**: Data cached for 24 hours
- 💾 **Cache**: 24 hours expiry
- 🏷️ **Data Type**: `HISTORICAL`

## 🔧 **Implementation Details**

### **Backend Changes**

#### **1. FetchTrueDataJob.php**
```php
// Market status check
$marketStatusService = new MarketStatusService();
$isMarketLive = $marketStatusService->isMarketLive();

if ($isMarketLive) {
    $this->fetchLiveData(); // 30 seconds cache
} else {
    $this->useHistoricalData(); // 24 hours cache, no refresh
}
```

#### **2. TrueDataController.php**
```php
// Enhanced response with market status
$responseData = [
    'market_status' => $marketStatus,
    'is_market_live' => $isMarketLive,
    'data_type' => $dataType, // LIVE or HISTORICAL
    'data_source' => $dataSourceMessage,
    // ... other data
];
```

### **Frontend Changes**

#### **1. UI Indicators**
- **Header**: Shows data source message
- **Fetch Button**: Changes text based on market status
- **Market Status**: Real-time status display

#### **2. Smart Fetching**
```javascript
// Market closed protection
if (!this.isMarketLive) {
    this.showError('Market is closed. Historical data is already cached and will not refresh.');
    return;
}
```

## 📈 **User Experience**

### **Market Open**
- 🔥 **Live Data**: "TrueData Live Market Data"
- 🔄 **Fetch Button**: "Fetch Live Data"
- ⚡ **Real-time**: Data updates every 30 seconds

### **Market Closed**
- 📊 **Historical Data**: "TrueData Historical Data (Market Closed)"
- 🚫 **Fetch Button**: "Historical Data" (disabled)
- 💾 **Cached**: No unnecessary API calls

## 🎯 **Benefits**

1. **Efficiency**: No unnecessary API calls when market is closed
2. **Cost Saving**: Reduces TrueData API usage
3. **User Clarity**: Clear indication of data type and status
4. **Performance**: Faster loading with cached historical data
5. **Reliability**: Consistent data availability

## 🔍 **Testing**

### **Test Market Open**
```bash
# Check live data
curl -s "http://localhost:8000/api/truedata/live-data" | jq '.data_type'
# Expected: "LIVE"
```

### **Test Market Closed**
```bash
# Check historical data
curl -s "http://localhost:8000/api/truedata/live-data" | jq '.data_type'
# Expected: "HISTORICAL"
```

## 📝 **Logs**

The system logs all market status changes:
```
FetchTrueDataJob: Starting TrueData fetch... {"market_status":"CLOSED","is_live":false,"reason":"Outside trading hours"}
FetchTrueDataJob: Using existing historical data - no refresh needed
```

## 🚀 **Ready for Production**

The system is now production-ready with:
- ✅ Market status detection
- ✅ Smart data caching
- ✅ User-friendly UI
- ✅ Efficient API usage
- ✅ Clear data source indication
