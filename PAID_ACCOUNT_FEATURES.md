# Paid Account Features

## TrueData API - Paid Account Benefits

When you upgrade to a **paid TrueData account**, the following additional indices will be available:

### Additional Major Indices:
- **FINNIFTY** - Financial Services Index
- **NIFTY MIDCAP** - Mid Cap Index

### Current Trial Account Limitations:
- Limited to 50 symbols maximum
- Some premium indices not available
- Basic data access only

### Paid Account Benefits:
- ✅ **FINNIFTY** - Financial services sector index
- ✅ **NIFTY MIDCAP** - Mid cap companies index
- ✅ More symbols (unlimited)
- ✅ Premium data access
- ✅ Real-time options data
- ✅ Historical data access
- ✅ Advanced analytics

## How to Upgrade:

1. Contact TrueData support for paid account
2. Update credentials in:
   - `truedata_fetch.py` (username/password)
   - `app/Jobs/FetchTrueDataJob.php` (API credentials)
3. Restart the application
4. All 5 major indices will be available:
   - NIFTY 50
   - NIFTY BANK
   - SENSEX
   - FINNIFTY (paid)
   - NIFTY MIDCAP (paid)

## Current Status:
- **Trial Account**: 3 major indices available
- **Paid Account**: 5 major indices available
- **UI Ready**: Already configured to show all 5 indices when available
