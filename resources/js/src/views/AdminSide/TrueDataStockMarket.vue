<template>
  <div class="stock-market-screen">
    <!-- Header -->
    <div class="page-header">
      <div class="header-content">
        <div class="header-left">
          <button class="back-btn" @click="navigateTo('/admin/dashboard')">
            <i class="fas fa-arrow-left"></i>
          </button>
          <div class="header-info">
            <h1 class="page-title">📈 Stock Market</h1>
            <p class="page-subtitle">
              {{ marketStatus === 'OPEN' ? 'Live market data powered by TrueData' : 'Last available data (Market Closed)' }}
            </p>
          </div>
        </div>
        <div class="header-right">
          <div class="market-status" :class="marketStatusClass">
            <div class="status-indicator"></div>
            <span>{{ marketStatusText }}</span>
          </div>
          <div class="market-info">
            <div class="trading-hours">{{ marketInfo.trading_hours }}</div>
            <div class="trading-days">{{ marketInfo.trading_days }}</div>
            <div class="next-session" v-if="marketInfo.next_session && marketStatus === 'CLOSED'">
              <i class="fas fa-clock"></i> {{ marketInfo.next_session }}
            </div>
          </div>
          <div class="last-updated">
            <span v-if="marketStatus === 'OPEN'">Last updated: {{ lastUpdated }}</span>
            <span v-else>Last trading data: {{ lastUpdated }}</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Connection Status -->
    <div class="connection-status" :class="connectionStatusClass">
      <div class="status-indicator"></div>
      <span>{{ connectionStatusText }}</span>
      <button v-if="!isConnected" class="reconnect-btn" @click="reconnect" :disabled="reconnecting">
        <i class="fas fa-sync-alt" :class="{ 'fa-spin': reconnecting }"></i>
        Reconnect
      </button>
    </div>

    <!-- Market Indices -->
    <div class="indices-section">
      <h2 class="section-title">Market Indices</h2>
      <div class="indices-grid">
        <div 
          v-for="index in marketIndices" 
          :key="index.symbol"
          class="index-card"
          :class="{ 'positive': index.change > 0, 'negative': index.change < 0 }"
        >
          <div class="index-name">{{ index.symbol }}</div>
          <div class="index-price">₹{{ formatNumber(index.last) }}</div>
          <div class="index-change">
            <span class="change-value">{{ index.change > 0 ? '+' : '' }}{{ formatNumber(index.change) }}</span>
            <span class="change-percent">({{ index.change_percent > 0 ? '+' : '' }}{{ index.change_percent?.toFixed(2) }}%)</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Content Tabs -->
    <div class="content-tabs">
      <div class="tab-buttons">
        <button 
          v-for="tab in tabs" 
          :key="tab.id"
          class="tab-btn"
          :class="{ active: activeTab === tab.id }"
          @click="activeTab = tab.id"
        >
          <i :class="tab.icon"></i>
          {{ tab.name }}
        </button>
      </div>

      <!-- Tab Content -->
      <div class="tab-content">
        <!-- Live Stocks Tab -->
        <div v-if="activeTab === 'stocks'" class="tab-panel">
          <div class="panel-header">
            <h3>Live Stock Prices ({{ filteredStocks.length }} stocks)</h3>
            <button class="refresh-btn" @click="refreshStockData" :disabled="loading">
              <i class="fas fa-sync-alt" :class="{ 'fa-spin': loading }"></i>
              Refresh
            </button>
          </div>

          <!-- Filters Section -->
          <div class="filters-section">
            <div class="filter-group">
              <label class="filter-label">Search:</label>
              <input 
                v-model="searchQuery" 
                type="text" 
                placeholder="Search stocks..."
                class="filter-input"
              >
            </div>
            
            <div class="filter-group">
              <label class="filter-label">Sort by:</label>
              <select v-model="sortBy" class="filter-select">
                <option value="symbol">Symbol</option>
                <option value="last">Price</option>
                <option value="change_percent">Change %</option>
                <option value="volume">Volume</option>
              </select>
            </div>
          </div>

          <!-- Stocks Grid -->
          <div class="stocks-grid">
            <div 
              v-for="stock in filteredStocks" 
              :key="stock.symbol"
              class="stock-card"
              :class="{ 'positive': stock.change > 0, 'negative': stock.change < 0 }"
            >
              <div class="stock-header">
                <div class="stock-symbol">{{ stock.symbol }}</div>
                <div class="stock-price">₹{{ formatNumber(stock.last) }}</div>
              </div>
              <div class="stock-details">
                <div class="stock-change">
                  <span class="change-value">{{ stock.change > 0 ? '+' : '' }}{{ formatNumber(stock.change) }}</span>
                  <span class="change-percent">({{ stock.change_percent > 0 ? '+' : '' }}{{ stock.change_percent?.toFixed(2) }}%)</span>
                </div>
                <div class="stock-volume">Vol: {{ formatNumber(stock.volume) }}</div>
              </div>
              <div class="stock-ohlc">
                <div class="ohlc-item">
                  <span class="ohlc-label">O:</span>
                  <span class="ohlc-value">₹{{ formatNumber(stock.open) }}</span>
                </div>
                <div class="ohlc-item">
                  <span class="ohlc-label">H:</span>
                  <span class="ohlc-value">₹{{ formatNumber(stock.high) }}</span>
                </div>
                <div class="ohlc-item">
                  <span class="ohlc-label">L:</span>
                  <span class="ohlc-value">₹{{ formatNumber(stock.low) }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Top Gainers Tab -->
        <div v-if="activeTab === 'gainers'" class="tab-panel">
          <div class="panel-header">
            <h3>Top Gainers</h3>
            <button class="refresh-btn" @click="refreshGainers" :disabled="loading">
              <i class="fas fa-sync-alt" :class="{ 'fa-spin': loading }"></i>
              Refresh
            </button>
          </div>
          <div class="gainers-list">
            <div 
              v-for="(gainer, index) in topGainers" 
              :key="gainer.symbol"
              class="gainer-item"
            >
              <div class="gainer-rank">{{ index + 1 }}</div>
              <div class="gainer-symbol">{{ gainer.symbol }}</div>
              <div class="gainer-price">₹{{ formatNumber(gainer.last) }}</div>
              <div class="gainer-change positive">
                +{{ gainer.change_percent?.toFixed(2) }}%
              </div>
            </div>
          </div>
        </div>

        <!-- Top Losers Tab -->
        <div v-if="activeTab === 'losers'" class="tab-panel">
          <div class="panel-header">
            <h3>Top Losers</h3>
            <button class="refresh-btn" @click="refreshLosers" :disabled="loading">
              <i class="fas fa-sync-alt" :class="{ 'fa-spin': loading }"></i>
              Refresh
            </button>
          </div>
          <div class="losers-list">
            <div 
              v-for="(loser, index) in topLosers" 
              :key="loser.symbol"
              class="loser-item"
            >
              <div class="loser-rank">{{ index + 1 }}</div>
              <div class="loser-symbol">{{ loser.symbol }}</div>
              <div class="loser-price">₹{{ formatNumber(loser.last) }}</div>
              <div class="loser-change negative">
                {{ loser.change_percent?.toFixed(2) }}%
              </div>
            </div>
          </div>
        </div>

        <!-- Market Status Tab -->
        <div v-if="activeTab === 'status'" class="tab-panel">
          <div class="panel-header">
            <h3>Market Status & Connection Info</h3>
            <button class="refresh-btn" @click="refreshStatus" :disabled="loading">
              <i class="fas fa-sync-alt" :class="{ 'fa-spin': loading }"></i>
              Refresh
            </button>
          </div>
          <div class="status-info">
            <div class="status-card">
              <h4>Market Status</h4>
              <div class="status-details">
                <div class="status-item">
                  <span class="status-label">Status:</span>
                  <span class="status-value" :class="marketStatusClass">{{ marketStatus }}</span>
                </div>
                <div class="status-item">
                  <span class="status-label">Trading Hours:</span>
                  <span class="status-value">{{ marketInfo.trading_hours }}</span>
                </div>
                <div class="status-item">
                  <span class="status-label">Trading Days:</span>
                  <span class="status-value">{{ marketInfo.trading_days }}</span>
                </div>
                <div class="status-item">
                  <span class="status-label">Last Updated:</span>
                  <span class="status-value">{{ lastUpdated }}</span>
                </div>
              </div>
            </div>
            
            <div class="status-card">
              <h4>TrueData Connection</h4>
              <div class="status-details">
                <div class="status-item">
                  <span class="status-label">Connection:</span>
                  <span class="status-value" :class="connectionStatusClass">{{ connectionStatusText }}</span>
                </div>
                <div class="status-item">
                  <span class="status-label">Subscribed Symbols:</span>
                  <span class="status-value">{{ subscribedSymbolsCount }}</span>
                </div>
                <div class="status-item">
                  <span class="status-label">Cached Data:</span>
                  <span class="status-value">{{ cachedDataCount }} symbols</span>
                </div>
                <div class="status-item">
                  <span class="status-label">Reconnect Attempts:</span>
                  <span class="status-value">{{ reconnectAttempts }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'TrueDataStockMarket',
  data() {
    return {
      loading: false,
      reconnecting: false,
      marketStatus: 'CLOSED',
      marketInfo: {
        trading_hours: '9:00 AM - 3:30 PM IST',
        trading_days: 'Monday to Friday',
        next_session: ''
      },
      lastUpdated: '',
      liveStocks: [
        // Fallback data for demonstration
        {
          symbol: 'NIFTY 50',
          last: 19500.50,
          open: 19450.00,
          high: 19520.00,
          low: 19400.00,
          prev_close: 19450.00,
          change: 50.50,
          change_percent: 0.26,
          volume: 1000000,
          last_time: new Date().toISOString()
        },
        {
          symbol: 'RELIANCE',
          last: 2450.50,
          open: 2440.00,
          high: 2460.00,
          low: 2435.00,
          prev_close: 2440.00,
          change: 10.50,
          change_percent: 0.43,
          volume: 500000,
          last_time: new Date().toISOString()
        },
        {
          symbol: 'TCS',
          last: 3850.00,
          open: 3840.00,
          high: 3860.00,
          low: 3830.00,
          prev_close: 3840.00,
          change: 10.00,
          change_percent: 0.26,
          volume: 200000,
          last_time: new Date().toISOString()
        }
      ],
      marketIndices: [
        {
          symbol: 'NIFTY 50',
          last: 19500.50,
          change: 50.50,
          change_percent: 0.26
        },
        {
          symbol: 'NIFTY BANK',
          last: 44500.00,
          change: -100.00,
          change_percent: -0.22
        }
      ],
      topGainers: [],
      topLosers: [],
      connectionStatus: {
        is_connected: false,
        reconnect_attempts: 0,
        subscribed_symbols_count: 0,
        cached_data_count: 0
      },
      activeTab: 'stocks',
      searchQuery: '',
      sortBy: 'symbol',
      tabs: [
        { id: 'stocks', name: 'Live Stocks', icon: 'fas fa-chart-line' },
        { id: 'gainers', name: 'Top Gainers', icon: 'fas fa-arrow-up' },
        { id: 'losers', name: 'Top Losers', icon: 'fas fa-arrow-down' },
        { id: 'status', name: 'Status', icon: 'fas fa-info-circle' }
      ]
    };
  },
  computed: {
    marketStatusClass() {
      return this.marketStatus === 'OPEN' ? 'open' : 'closed';
    },
    marketStatusText() {
      return this.marketStatus === 'OPEN' ? 'Market Open' : 'Market Closed';
    },
    connectionStatusClass() {
      return this.isConnected ? 'connected' : 'disconnected';
    },
    connectionStatusText() {
      return this.isConnected ? 'Connected' : 'Disconnected';
    },
    isConnected() {
      return this.connectionStatus.is_connected;
    },
    subscribedSymbolsCount() {
      return this.connectionStatus.subscribed_symbols_count;
    },
    cachedDataCount() {
      return this.connectionStatus.cached_data_count;
    },
    reconnectAttempts() {
      return this.connectionStatus.reconnect_attempts;
    },
    filteredStocks() {
      let stocks = [...this.liveStocks];
      
      // Filter by search query
      if (this.searchQuery) {
        stocks = stocks.filter(stock => 
          stock.symbol.toLowerCase().includes(this.searchQuery.toLowerCase())
        );
      }
      
      // Sort stocks
      stocks.sort((a, b) => {
        switch (this.sortBy) {
          case 'symbol':
            return a.symbol.localeCompare(b.symbol);
          case 'last':
            return b.last - a.last;
          case 'change_percent':
            return b.change_percent - a.change_percent;
          case 'volume':
            return b.volume - a.volume;
          default:
            return 0;
        }
      });
      
      return stocks;
    }
  },
  mounted() {
    this.loadMarketData();
    // Auto-refresh every 30 seconds when market is open
    this.startAutoRefresh();
  },
  beforeUnmount() {
    this.stopAutoRefresh();
  },
  methods: {
    async loadMarketData() {
      this.loading = true;
      console.log('Loading TrueData market data...');
      
      try {
        const token = localStorage.getItem('access_token');
        console.log('Token:', token ? 'Present' : 'Missing');
        
        // First test the connection
        const testResponse = await axios.get('/api/truedata/test', {
          headers: {
            'Authorization': `Bearer ${token}`,
            'Accept': 'application/json'
          }
        });

        console.log('Connection test:', testResponse.data);
        
        if (testResponse.data.success) {
          this.connectionStatus.is_connected = true;
          console.log('TrueData connection successful');
        } else {
          this.connectionStatus.is_connected = false;
          console.log('TrueData connection failed:', testResponse.data.error);
        }
        
        // Load dashboard data
        const response = await axios.get('/api/truedata/dashboard', {
          headers: {
            'Authorization': `Bearer ${token}`,
            'Accept': 'application/json'
          }
        });

        console.log('TrueData Dashboard API Response:', response.data);

        if (response.data.success) {
          const data = response.data.data;
          console.log('Processing TrueData:', data);
          
          // Update market status
          if (data.market_status) {
            this.marketStatus = data.market_status.market_status || 'CLOSED';
            this.marketInfo = {
              trading_hours: data.market_status.trading_hours || '9:00 AM - 3:30 PM IST',
              trading_days: data.market_status.trading_days || 'Monday to Friday',
              next_session: data.market_status.next_session || ''
            };
          }
          
          // Process quotes data
          if (data.quotes) {
            this.liveStocks = this.formatStockData(data.quotes);
            this.connectionStatus.cached_data_count = Object.keys(data.quotes).length;
            console.log('Live stocks processed:', this.liveStocks);
          }
          
          // Process market indices
          if (data.indices) {
            this.marketIndices = data.indices.map(index => ({
              symbol: index.symbol,
              last: index.ltp || 0,
              change: index.change || 0,
              change_percent: index.change_percent || 0,
              volume: index.volume || 0,
              turnover: index.turnover || 0,
              high: index.high || 0,
              low: index.low || 0,
              open: index.open || 0,
              prev_close: index.prev_close || 0,
              bid: index.bid || 0,
              ask: index.ask || 0,
              timestamp: index.timestamp || new Date().toISOString()
            }));
            console.log('Market indices processed:', this.marketIndices);
          }
          
          // Process top gainers
          if (data.top_gainers) {
            this.topGainers = data.top_gainers.map(stock => ({
              symbol: stock.symbol,
              last: stock.ltp || 0,
              change: stock.change || 0,
              change_percent: stock.change_percent || 0,
              volume: stock.volume || 0,
              turnover: stock.turnover || 0,
              high: stock.high || 0,
              low: stock.low || 0,
              open: stock.open || 0,
              prev_close: stock.prev_close || 0,
              bid: stock.bid || 0,
              ask: stock.ask || 0,
              timestamp: stock.timestamp || new Date().toISOString()
            }));
            console.log('Top gainers processed:', this.topGainers);
          }
          
          // Process top losers
          if (data.top_losers) {
            this.topLosers = data.top_losers.map(stock => ({
              symbol: stock.symbol,
              last: stock.ltp || 0,
              change: stock.change || 0,
              change_percent: stock.change_percent || 0,
              volume: stock.volume || 0,
              turnover: stock.turnover || 0,
              high: stock.high || 0,
              low: stock.low || 0,
              open: stock.open || 0,
              prev_close: stock.prev_close || 0,
              bid: stock.bid || 0,
              ask: stock.ask || 0,
              timestamp: stock.timestamp || new Date().toISOString()
            }));
            console.log('Top losers processed:', this.topLosers);
          }
          
          this.lastUpdated = new Date().toLocaleTimeString();
        }

      } catch (error) {
        console.error('Error loading TrueData market data:', error);
        this.connectionStatus.is_connected = false;
        this.showError('Failed to load market data. Please try again.');
      } finally {
        this.loading = false;
      }
    },

    async loadMarketStatus() {
      try {
        const token = localStorage.getItem('access_token');
        const response = await axios.get('/api/truedata/market-status', {
          headers: {
            'Authorization': `Bearer ${token}`,
            'Accept': 'application/json'
          }
        });

        if (response.data.success) {
          const statusData = response.data.data;
          this.marketStatus = statusData.market_status || 'CLOSED';
          this.marketInfo = {
            trading_hours: statusData.trading_hours || '9:00 AM - 3:30 PM IST',
            trading_days: statusData.trading_days || 'Monday to Friday',
            next_session: statusData.next_session || ''
          };
        }
      } catch (error) {
        console.error('Error loading market status:', error);
      }
    },

    async loadTopGainers() {
      try {
        const token = localStorage.getItem('access_token');
        const response = await axios.get('/api/truedata/top-gainers', {
          headers: {
            'Authorization': `Bearer ${token}`,
            'Accept': 'application/json'
          }
        });

        if (response.data.success) {
          this.topGainers = response.data.data || [];
        }
      } catch (error) {
        console.error('Error loading top gainers:', error);
      }
    },

    async loadTopLosers() {
      try {
        const token = localStorage.getItem('access_token');
        const response = await axios.get('/api/truedata/top-losers', {
          headers: {
            'Authorization': `Bearer ${token}`,
            'Accept': 'application/json'
          }
        });

        if (response.data.success) {
          this.topLosers = response.data.data || [];
        }
      } catch (error) {
        console.error('Error loading top losers:', error);
      }
    },

    formatStockData(data) {
      if (!data || typeof data !== 'object') return [];
      
      return Object.values(data).map(stock => ({
        symbol: stock.symbol || '',
        last: stock.last || 0,
        open: stock.open || 0,
        high: stock.high || 0,
        low: stock.low || 0,
        prev_close: stock.prev_close || 0,
        change: stock.change || 0,
        change_percent: stock.change_percent || 0,
        volume: stock.volume || 0,
        bid: stock.bid || 0,
        ask: stock.ask || 0,
        last_time: stock.last_time || ''
      }));
    },

    updateStockData(newData) {
      const existingIndex = this.liveStocks.findIndex(stock => stock.symbol === newData.symbol);
      if (existingIndex !== -1) {
        this.liveStocks[existingIndex] = {
          ...this.liveStocks[existingIndex],
          ...newData,
          last_time: new Date().toISOString()
        };
      } else {
        this.liveStocks.push({
          symbol: newData.symbol,
          last: newData.last || 0,
          open: newData.open || 0,
          high: newData.high || 0,
          low: newData.low || 0,
          prev_close: newData.prev || 0,
          change: (newData.last || 0) - (newData.prev || 0),
          change_percent: newData.prev > 0 ? (((newData.last || 0) - (newData.prev || 0)) / newData.prev) * 100 : 0,
          volume: newData.volume || 0,
          bid: newData.bid || 0,
          ask: newData.ask || 0,
          last_time: new Date().toISOString()
        });
      }
    },

    async refreshStockData() {
      await this.loadMarketData();
    },

    async refreshGainers() {
      await this.loadTopGainers();
    },

    async refreshLosers() {
      await this.loadTopLosers();
    },

    async refreshStatus() {
      await this.loadMarketStatus();
    },

    async reconnect() {
      this.reconnecting = true;
      try {
        const token = localStorage.getItem('access_token');
        const response = await axios.get('/api/truedata/test', {
          headers: {
            'Authorization': `Bearer ${token}`,
            'Accept': 'application/json'
          }
        });

        console.log('Reconnect response:', response.data);

        if (response.data.success) {
          this.connectionStatus.is_connected = true;
          await this.loadMarketData();
          this.showSuccess('Reconnected successfully!');
        } else {
          this.connectionStatus.is_connected = false;
          this.showError('Reconnection failed: ' + response.data.error);
        }
      } catch (error) {
        console.error('Reconnection error:', error);
        this.connectionStatus.is_connected = false;
        this.showError('Reconnection failed. Please try again.');
      } finally {
        this.reconnecting = false;
      }
    },

    startAutoRefresh() {
      this.autoRefreshInterval = setInterval(() => {
        if (this.marketStatus === 'OPEN') {
          this.loadMarketData();
        }
      }, 30000); // 30 seconds
    },

    stopAutoRefresh() {
      if (this.autoRefreshInterval) {
        clearInterval(this.autoRefreshInterval);
      }
    },

    formatNumber(num) {
      if (num === null || num === undefined) return '0';
      return new Intl.NumberFormat('en-IN', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
      }).format(num);
    },

    navigateTo(path) {
      this.$router.push(path);
    },

    showError(message) {
      // You can implement a toast notification system here
      console.error(message);
      alert(message); // Temporary - replace with proper notification
    },

    showSuccess(message) {
      // You can implement a toast notification system here
      console.log(message);
      alert(message); // Temporary - replace with proper notification
    }
  }
};
</script>

<style scoped>
/* Add your existing styles here or import from the original component */
.stock-market-screen {
  padding: 20px;
  background: #f5f5f5;
  min-height: 100vh;
}

.page-header {
  background: white;
  border-radius: 12px;
  padding: 20px;
  margin-bottom: 20px;
  box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.header-content {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.header-left {
  display: flex;
  align-items: center;
  gap: 15px;
}

.back-btn {
  background: #007bff;
  color: white;
  border: none;
  border-radius: 8px;
  padding: 10px;
  cursor: pointer;
  transition: background 0.3s;
}

.back-btn:hover {
  background: #0056b3;
}

.page-title {
  margin: 0;
  color: #333;
  font-size: 24px;
}

.page-subtitle {
  margin: 5px 0 0 0;
  color: #666;
  font-size: 14px;
}

.header-right {
  display: flex;
  align-items: center;
  gap: 20px;
}

.market-status {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 8px 16px;
  border-radius: 20px;
  font-weight: 500;
}

.market-status.open {
  background: #d4edda;
  color: #155724;
}

.market-status.closed {
  background: #f8d7da;
  color: #721c24;
}

.status-indicator {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: currentColor;
}

.connection-status {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 10px 20px;
  border-radius: 8px;
  margin-bottom: 20px;
  font-weight: 500;
}

.connection-status.connected {
  background: #d4edda;
  color: #155724;
}

.connection-status.disconnected {
  background: #f8d7da;
  color: #721c24;
}

.reconnect-btn {
  background: #007bff;
  color: white;
  border: none;
  border-radius: 4px;
  padding: 5px 10px;
  cursor: pointer;
  font-size: 12px;
}

.reconnect-btn:hover:not(:disabled) {
  background: #0056b3;
}

.reconnect-btn:disabled {
  background: #6c757d;
  cursor: not-allowed;
}

.indices-section {
  background: white;
  border-radius: 12px;
  padding: 20px;
  margin-bottom: 20px;
  box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.section-title {
  margin: 0 0 20px 0;
  color: #333;
  font-size: 20px;
}

.indices-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 15px;
}

.index-card {
  background: #f8f9fa;
  border-radius: 8px;
  padding: 15px;
  border-left: 4px solid #dee2e6;
}

.index-card.positive {
  border-left-color: #28a745;
}

.index-card.negative {
  border-left-color: #dc3545;
}

.index-name {
  font-weight: 600;
  color: #333;
  margin-bottom: 5px;
}

.index-price {
  font-size: 18px;
  font-weight: 700;
  color: #333;
  margin-bottom: 5px;
}

.index-change {
  font-size: 14px;
}

.change-value {
  font-weight: 600;
}

.change-percent {
  margin-left: 5px;
}

.content-tabs {
  background: white;
  border-radius: 12px;
  box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.tab-buttons {
  display: flex;
  border-bottom: 1px solid #dee2e6;
}

.tab-btn {
  background: none;
  border: none;
  padding: 15px 20px;
  cursor: pointer;
  color: #666;
  font-weight: 500;
  transition: all 0.3s;
  border-bottom: 3px solid transparent;
}

.tab-btn:hover {
  background: #f8f9fa;
  color: #333;
}

.tab-btn.active {
  color: #007bff;
  border-bottom-color: #007bff;
  background: #f8f9fa;
}

.tab-content {
  padding: 20px;
}

.panel-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.panel-header h3 {
  margin: 0;
  color: #333;
}

.refresh-btn {
  background: #007bff;
  color: white;
  border: none;
  border-radius: 6px;
  padding: 8px 16px;
  cursor: pointer;
  font-size: 14px;
  transition: background 0.3s;
}

.refresh-btn:hover:not(:disabled) {
  background: #0056b3;
}

.refresh-btn:disabled {
  background: #6c757d;
  cursor: not-allowed;
}

.filters-section {
  display: flex;
  gap: 20px;
  margin-bottom: 20px;
  padding: 15px;
  background: #f8f9fa;
  border-radius: 8px;
}

.filter-group {
  display: flex;
  align-items: center;
  gap: 10px;
}

.filter-label {
  font-weight: 500;
  color: #333;
  white-space: nowrap;
}

.filter-input,
.filter-select {
  padding: 8px 12px;
  border: 1px solid #dee2e6;
  border-radius: 4px;
  font-size: 14px;
}

.stocks-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 15px;
}

.stock-card {
  background: #f8f9fa;
  border-radius: 8px;
  padding: 15px;
  border-left: 4px solid #dee2e6;
  transition: transform 0.2s;
}

.stock-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.stock-card.positive {
  border-left-color: #28a745;
}

.stock-card.negative {
  border-left-color: #dc3545;
}

.stock-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 10px;
}

.stock-symbol {
  font-weight: 600;
  color: #333;
  font-size: 16px;
}

.stock-price {
  font-size: 18px;
  font-weight: 700;
  color: #333;
}

.stock-details {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 10px;
}

.stock-change {
  font-size: 14px;
}

.stock-volume {
  font-size: 12px;
  color: #666;
}

.stock-ohlc {
  display: flex;
  justify-content: space-between;
  font-size: 12px;
  color: #666;
}

.ohlc-item {
  display: flex;
  flex-direction: column;
  align-items: center;
}

.ohlc-label {
  font-weight: 500;
  margin-bottom: 2px;
}

.ohlc-value {
  font-weight: 600;
}

.gainers-list,
.losers-list {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.gainer-item,
.loser-item {
  display: flex;
  align-items: center;
  gap: 15px;
  padding: 15px;
  background: #f8f9fa;
  border-radius: 8px;
  border-left: 4px solid #dee2e6;
}

.gainer-item {
  border-left-color: #28a745;
}

.loser-item {
  border-left-color: #dc3545;
}

.gainer-rank,
.loser-rank {
  background: #007bff;
  color: white;
  border-radius: 50%;
  width: 30px;
  height: 30px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 600;
  font-size: 14px;
}

.gainer-symbol,
.loser-symbol {
  font-weight: 600;
  color: #333;
  flex: 1;
}

.gainer-price,
.loser-price {
  font-weight: 600;
  color: #333;
  margin-right: 20px;
}

.gainer-change {
  color: #28a745;
  font-weight: 600;
}

.loser-change {
  color: #dc3545;
  font-weight: 600;
}

.status-info {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 20px;
}

.status-card {
  background: #f8f9fa;
  border-radius: 8px;
  padding: 20px;
}

.status-card h4 {
  margin: 0 0 15px 0;
  color: #333;
  font-size: 18px;
}

.status-details {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.status-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 8px 0;
  border-bottom: 1px solid #dee2e6;
}

.status-item:last-child {
  border-bottom: none;
}

.status-label {
  font-weight: 500;
  color: #666;
}

.status-value {
  font-weight: 600;
  color: #333;
}

.status-value.connected {
  color: #28a745;
}

.status-value.disconnected {
  color: #dc3545;
}

.status-value.open {
  color: #28a745;
}

.status-value.closed {
  color: #dc3545;
}

.last-updated {
  font-size: 12px;
  color: #666;
  margin-top: 5px;
}

.market-info {
  text-align: right;
  font-size: 12px;
  color: #666;
}

.trading-hours,
.trading-days {
  margin-bottom: 2px;
}

.next-session {
  margin-top: 5px;
  color: #007bff;
}

.fa-spin {
  animation: fa-spin 1s infinite linear;
}

@keyframes fa-spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}
</style>
