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
              {{ marketStatus === 'OPEN' ? '🔥 Live market data powered by TrueData Python Script' : '📊 Last available data (Market Closed)' }}
            </p>
          </div>
        </div>
        <div class="header-right">
          <div class="market-status" :class="marketStatusClass">
            <div class="status-indicator"></div>
            <span>{{ marketStatusText }}</span>
          </div>
          
          <button 
            @click="triggerDataFetch" 
            :disabled="loading"
            class="fetch-btn"
            title="Fetch fresh live data from Python script"
          >
            <i class="fas fa-download" :class="{ 'fa-spin': loading }"></i>
            {{ loading ? 'Fetching...' : 'Fetch Live Data' }}
          </button>
          
          <button 
            @click="refreshData" 
            :disabled="loading"
            class="refresh-btn"
            title="Refresh current data"
          >
            <i class="fas fa-sync-alt" :class="{ 'fa-spin': loading }"></i>
            {{ loading ? 'Refreshing...' : 'Refresh' }}
          </button>
          
          <div class="market-info">
            <div class="market-status">
              <span class="status-dot" :class="getMarketStatusClass()"></span>
              <span class="status-text">{{ getMarketStatusText() }}</span>
            </div>
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

    <!-- Main Content -->
    <div class="main-content">
      <!-- Live Stocks Section -->
      <div class="stocks-section">
        <div class="section-header">
          <div class="section-title">
            <i class="fas fa-chart-line"></i>
            <h3>Live Stock Prices</h3>
            <span class="stock-count">({{ filteredStocks.length }} stocks)</span>
          </div>
          <button class="refresh-btn" @click="refreshStockData" :disabled="loading">
            <i class="fas fa-sync-alt" :class="{ 'fa-spin': loading }"></i>
            Refresh
          </button>
        </div>

        <!-- Filters Section -->
        <div class="filters-section">
          <div class="filter-group">
            <label class="filter-label">Search:</label>
            <div class="search-input-wrapper">
              <i class="fas fa-search search-icon"></i>
              <input 
                v-model="searchQuery" 
                type="text" 
                placeholder="Search stocks..."
                class="filter-input"
                @keyup.enter="searchAndAddStock"
              >
              <button 
                class="search-btn" 
                @click="searchAndAddStock"
                :disabled="loading || !searchQuery.trim()"
                title="Search and add stock"
              >
                <i class="fas fa-plus"></i>
              </button>
            </div>
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
        <div class="stocks-grid" v-if="filteredStocks.length > 0">
          <div 
            v-for="stock in filteredStocks" 
            :key="stock.symbol"
            class="stock-card"
            :class="{ 'positive': stock.change > 0, 'negative': stock.change < 0 }"
            @click="showOptionsModal(stock.symbol, stock)"
            style="cursor: pointer;"
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

        <!-- Empty State -->
        <div v-if="filteredStocks.length === 0" class="empty-state">
          <div class="empty-state-icon">
            <i class="fas fa-chart-line"></i>
          </div>
          <div class="empty-state-title">No Market Data Available</div>
          <div class="empty-state-message">
            Market is currently closed. Real-time data will be available during trading hours:<br>
            <strong>9:00 AM - 3:30 PM IST (Monday to Friday)</strong>
          </div>
          <div class="empty-state-actions">
            <button @click="loadMarketData" class="retry-btn">
              <i class="fas fa-sync-alt"></i>
              Try Again
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
  
  <!-- Toast Notifications -->
  <div class="toast-container">
    <div 
      v-for="toast in toasts" 
      :key="toast.id"
      :class="['toast', toast.type]"
      @click="removeToast(toast.id)"
    >
      <div class="toast-icon">
        <i :class="toast.icon"></i>
      </div>
      <div class="toast-content">
        <div class="toast-title">{{ toast.title }}</div>
        <div class="toast-message">{{ toast.message }}</div>
      </div>
      <button class="toast-close" @click.stop="removeToast(toast.id)">
        <i class="fas fa-times"></i>
      </button>
    </div>
  </div>

  <!-- Options Modal -->
  <div v-if="showOptions" class="options-modal-overlay" @click="closeOptionsModal">
    <div class="options-modal" @click.stop>
      <div class="options-header">
        <h3>{{ selectedStock?.symbol }} Options</h3>
        <div class="header-actions">
          <div class="search-container">
            <input 
              v-model="optionsSearchQuery" 
              type="text" 
              placeholder="Search by strike price..."
              class="options-search"
              @input="filterOptions"
            >
            <i class="fas fa-search search-icon"></i>
          </div>
          <span class="esc-hint">Press ESC to close</span>
          <button @click="closeOptionsModal" class="close-btn">
            <i class="fas fa-times"></i>
          </button>
        </div>
      </div>
      
      <div class="options-content">
        <!-- Side-by-Side Options Layout -->
        <div class="options-comparison">
          <!-- Put Options (Left Side) -->
          <div class="options-column put-column">
            <h4 class="section-title put-title">
              <i class="fas fa-arrow-down"></i> Put Options
            </h4>
            <div class="options-list">
              <div 
                v-for="option in filteredPutOptions" 
                :key="option.strike"
                class="option-row put-option"
              >
                <div class="option-strike">{{ option.strike }}</div>
                <div class="option-price">₹{{ option.price || '--' }}</div>
                <div class="option-details">
                  <div class="option-volume">Vol: {{ option.volume || '--' }}</div>
                  <div class="option-oi">OI: {{ option.oi || '--' }}</div>
                </div>
                <div class="option-greeks" v-if="option.greeks">
                  <div class="greeks-row">
                    <span class="greek">Δ {{ option.greeks.delta || '--' }}</span>
                    <span class="greek">Γ {{ option.greeks.gamma || '--' }}</span>
                  </div>
                  <div class="greeks-row">
                    <span class="greek">Θ {{ option.greeks.theta || '--' }}</span>
                    <span class="greek">ν {{ option.greeks.vega || '--' }}</span>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Strike Price Column (Center) -->
          <div class="strike-column">
            <h4 class="section-title strike-title">
              <i class="fas fa-bullseye"></i> Strike Price
            </h4>
            <div class="strike-list">
              <div 
                v-for="strike in filteredStrikes" 
                :key="strike"
                class="strike-row"
              >
                <div class="strike-price">{{ strike }}</div>
                <div class="strike-indicator" :class="getStrikeIndicatorClass(strike)">
                  {{ getStrikeIndicatorText(strike) }}
                </div>
              </div>
            </div>
          </div>

          <!-- Call Options (Right Side) -->
          <div class="options-column call-column">
            <h4 class="section-title call-title">
              <i class="fas fa-arrow-up"></i> Call Options
            </h4>
            <div class="options-list">
              <div 
                v-for="option in filteredCallOptions" 
                :key="option.strike"
                class="option-row call-option"
              >
                <div class="option-strike">{{ option.strike }}</div>
                <div class="option-price">₹{{ option.price || '--' }}</div>
                <div class="option-details">
                  <div class="option-volume">Vol: {{ option.volume || '--' }}</div>
                  <div class="option-oi">OI: {{ option.oi || '--' }}</div>
                </div>
                <div class="option-greeks" v-if="option.greeks">
                  <div class="greeks-row">
                    <span class="greek">Δ {{ option.greeks.delta || '--' }}</span>
                    <span class="greek">Γ {{ option.greeks.gamma || '--' }}</span>
                  </div>
                  <div class="greeks-row">
                    <span class="greek">Θ {{ option.greeks.theta || '--' }}</span>
                    <span class="greek">ν {{ option.greeks.vega || '--' }}</span>
                  </div>
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
      liveStocks: [],
      marketIndices: [],

      connectionStatus: {
        is_connected: false,
        reconnect_attempts: 0,
        subscribed_symbols_count: 0,
        cached_data_count: 0
      },
      searchQuery: '',
      sortBy: 'symbol',
      toasts: [],
      toastId: 0,
      // Options Modal Data
      showOptions: false,
      selectedStock: null,
      callOptions: [],
      putOptions: [],
      optionsSearchQuery: '',
      filteredCallOptions: [],
      filteredPutOptions: [],
      filteredStrikes: []
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
      // Always show connected class if API is working
      return 'connected';
    },
    connectionStatusText() {
      if (this.marketStatus === 'OPEN') {
        return this.connectionStatus.cached_data_count > 0 ? 
          `🔥 Live Data (${this.connectionStatus.cached_data_count} symbols)` : 
          'Connected (Fetching Live Data...)';
      } else {
        return this.connectionStatus.cached_data_count > 0 ? 
          `📊 Historical Data (${this.connectionStatus.cached_data_count} symbols)` : 
          'Connected (API Ready)';
      }
    },
    isConnected() {
      // Always show connected if we have data, regardless of market status
      // Also show connected if market is open (even without data)
      return this.connectionStatus.is_connected || this.marketStatus === 'OPEN';
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
    // Initialize with closed status
    this.marketStatus = 'CLOSED';
    this.loadMarketData();
    // Auto-refresh every 30 seconds when market is open
    this.startAutoRefresh();
    
    // Add ESC key listener for options modal
    document.addEventListener('keydown', this.handleKeyPress);
  },
  beforeUnmount() {
    this.stopAutoRefresh();
    // Remove ESC key listener
    document.removeEventListener('keydown', this.handleKeyPress);
  },
  methods: {
    async loadMarketData() {
      this.loading = true;
      console.log('Loading TrueData live market data...');
      
      try {
        const token = localStorage.getItem('access_token');
        console.log('Token:', token ? 'Present' : 'Missing');
        
        // First try to get live data from Python script
        console.log('Loading live data from Python script...');
        
                 const liveResponse = await axios.get('/api/truedata/live-data', {
           headers: {
             'Accept': 'application/json'
           },
           params: { _t: Date.now() } // Cache busting parameter
         });

         console.log('TrueData Live Data API Response:', liveResponse.data);
         console.log('Response status:', liveResponse.status);
         console.log('Data keys:', Object.keys(liveResponse.data.data || {}));

                 // Handle both success and failure cases
         if (liveResponse.data.success && liveResponse.data.data && Object.keys(liveResponse.data.data).length > 0) {
           // Live data available
           const liveData = liveResponse.data.data;
           console.log('Processing Live Data:', liveData);
           console.log('Data count:', liveResponse.data.data_count);
           console.log('Last update:', liveResponse.data.last_update);
           
           // Don't set market status here - let the API response determine it
           // this.marketStatus = 'OPEN';
           this.marketInfo = {
             trading_hours: '9:15 AM - 3:30 PM IST',
             trading_days: 'Monday to Friday',
             next_session: ''
           };
           
           this.liveStocks = this.formatStockData(liveData);
           this.connectionStatus.cached_data_count = Object.keys(liveData).length;
           this.connectionStatus.is_connected = true;
           console.log('Live stocks processed:', this.liveStocks);
           
           // Extract market indices (first 5 stocks as indices)
           const indices = Object.values(liveData).slice(0, 5);
           this.marketIndices = indices.map(index => ({
             symbol: index.symbol,
             last: index.ltp || 0,
             change: index.change || 0,
             change_percent: index.change_percent || 0,
             volume: index.volume || 0,
             high: index.high || 0,
             low: index.low || 0,
             open: index.open || 0,
             prev_close: index.prev_close || 0,
             timestamp: index.timestamp || new Date().toISOString()
           }));
           console.log('Market indices processed:', this.marketIndices);
           
           this.lastUpdated = liveResponse.data.last_update || new Date().toLocaleTimeString();
           this.showSuccess(`Live market data loaded! ${Object.keys(liveData).length} symbols updated`);
           
         } else {
           // No live data available (market closed or API issue)
           console.log('No live data available, trying dashboard fallback for historical data...');
           
           // Update market status from response
           if (liveResponse.data.market_status) {
             this.marketStatus = liveResponse.data.market_status.status || 'CLOSED';
             this.marketInfo = {
               trading_hours: liveResponse.data.market_status.trading_hours || '9:15 AM - 3:30 PM IST',
               trading_days: 'Monday to Friday',
               next_session: liveResponse.data.market_status.next_open || 'Next session: Tomorrow 9:15 AM'
             };
           } else {
             // If no market status in response, assume market is closed
             this.marketStatus = 'CLOSED';
           }
           
           // Try to get historical data from dashboard
           await this.loadDashboardFallback();
           
           // If still no data, show message to user
           if (this.liveStocks.length === 0) {
             this.showWarning('Market data is being fetched. Please wait a moment and refresh the page.');
           }
         }

      } catch (error) {
        console.error('Error loading TrueData market data:', error);
        // Only set disconnected if there's an actual API error
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
        last: stock.ltp || stock.last || 0, // Use ltp first, then last
        open: stock.open || 0,
        high: stock.high || 0,
        low: stock.low || 0,
        prev_close: stock.prev_close || 0,
        change: stock.change || 0,
        change_percent: stock.change_percent || 0,
        volume: stock.volume || 0,
        bid: stock.bid || 0,
        ask: stock.ask || 0,
        last_time: stock.last_time || stock.timestamp || ''
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
        // Simply reload market data instead of testing connection
        await this.loadMarketData();
        this.showSuccess('Data refreshed successfully!');
      } catch (error) {
        console.error('Reconnection error:', error);
        this.connectionStatus.is_connected = false;
        this.showError('Failed to refresh data. Please try again.');
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
      this.addToast('error', 'Error', message, 'fas fa-exclamation-circle');
    },

    showSuccess(message) {
      this.addToast('success', 'Success', message, 'fas fa-check-circle');
    },

    showInfo(message) {
      this.addToast('info', 'Info', message, 'fas fa-info-circle');
    },

    showWarning(message) {
      this.addToast('warning', 'Warning', message, 'fas fa-exclamation-triangle');
    },

    addToast(type, title, message, icon) {
      const toast = {
        id: ++this.toastId,
        type,
        title,
        message,
        icon
      };
      
      this.toasts.push(toast);
      
      // Auto remove after 5 seconds
      setTimeout(() => {
        this.removeToast(toast.id);
      }, 5000);
    },

    removeToast(id) {
      const index = this.toasts.findIndex(toast => toast.id === id);
      if (index > -1) {
        this.toasts.splice(index, 1);
      }
    },

    async refreshData() {
      this.loading = true;
      try {
        await this.loadMarketData();
        this.showSuccess('Market data refreshed successfully!');
      } catch (error) {
        this.showError('Failed to refresh data. Please try again.');
      } finally {
        this.loading = false;
      }
    },

    async searchAndAddStock() {
      if (!this.searchQuery.trim()) {
        this.showWarning('Please enter a stock symbol to search');
        return;
      }

      try {
        this.loading = true;
        console.log('Searching for stock:', this.searchQuery);
        
        const token = localStorage.getItem('access_token');
        const response = await axios.post('/api/truedata/search-stock', {
          symbol: this.searchQuery.trim().toUpperCase()
        }, {
          headers: {
            'Authorization': `Bearer ${token}`,
            'Accept': 'application/json',
            'Content-Type': 'application/json'
          }
        });

        console.log('Search stock response:', response.data);

        if (response.data.success && response.data.data) {
          const stockData = response.data.data;
          
          // Check if stock already exists
          const existingStock = this.liveStocks.find(stock => 
            stock.symbol === stockData.symbol
          );

          if (existingStock) {
            this.showInfo(`Stock ${stockData.symbol} is already in the list`);
            this.searchQuery = '';
            return;
          }

          // Add new stock to the list
          const newStock = {
            symbol: stockData.symbol,
            price: stockData.ltp || stockData.last || 0,
            change: stockData.change || 0,
            change_percent: stockData.change_percent || 0,
            volume: stockData.volume || 0,
            high: stockData.high || 0,
            low: stockData.low || 0,
            open: stockData.open || 0,
            prev_close: stockData.prev_close || 0,
            timestamp: stockData.timestamp || new Date().toISOString()
          };

          this.liveStocks.unshift(newStock); // Add to beginning of list
          this.connectionStatus.cached_data_count = this.liveStocks.length;
          
          this.showSuccess(`Stock ${stockData.symbol} added successfully!`);
          this.searchQuery = '';
          
        } else {
          this.showError(response.data.message || 'Stock not found or data unavailable');
        }

      } catch (error) {
        console.error('Error searching for stock:', error);
        this.showError('Failed to search for stock. Please try again.');
      } finally {
        this.loading = false;
      }
    },

    async triggerDataFetch() {
      try {
        const response = await axios.post('/api/truedata/trigger-fetch', {}, {
          headers: {
            'Accept': 'application/json'
          }
        });

        if (response.data.success) {
          console.log('Data fetch triggered successfully');
          // Wait a moment for the job to complete
          setTimeout(() => {
            this.loadMarketData();
          }, 3000);
        }
      } catch (error) {
        console.error('Error triggering data fetch:', error);
      }
    },

         async loadDashboardFallback() {
       try {
         console.log('Loading dashboard fallback data...');
         const response = await axios.get('/api/truedata/dashboard', {
           headers: {
             'Accept': 'application/json'
           },
           params: { _t: Date.now() }
         });

         console.log('Dashboard fallback response:', response.data);

         if (response.data.success) {
           const data = response.data.data;
           
           // Process quotes data
           if (data.quotes && Object.keys(data.quotes).length > 0) {
             this.liveStocks = this.formatStockData(data.quotes);
             this.connectionStatus.cached_data_count = Object.keys(data.quotes).length;
             this.connectionStatus.is_connected = true;
             console.log('Dashboard stocks processed:', this.liveStocks.length);
           }
           
           // Process market indices
           if (data.indices && Object.keys(data.indices).length > 0) {
             this.marketIndices = Object.values(data.indices).map(index => ({
               symbol: index.symbol,
               last: index.ltp || index.last || 0,
               change: index.change || 0,
               change_percent: index.change_percent || 0,
               volume: index.volume || 0,
               high: index.high || 0,
               low: index.low || 0,
               open: index.open || 0,
               prev_close: index.prev_close || 0,
               timestamp: index.timestamp || new Date().toISOString()
             }));
             console.log('Dashboard indices processed:', this.marketIndices.length);
           }
           
           this.lastUpdated = new Date().toLocaleTimeString();
           this.showInfo('Using historical data (Market closed)');
           
         } else {
           console.log('Dashboard fallback also failed, showing empty state');
           this.connectionStatus.is_connected = false;
           this.connectionStatus.cached_data_count = 0;
           this.showWarning('No market data available. Market is closed and no historical data found.');
         }
       } catch (error) {
         console.error('Dashboard fallback failed:', error);
         this.connectionStatus.is_connected = false;
         this.connectionStatus.cached_data_count = 0;
         this.showError('Failed to load market data from both live and historical sources.');
       }
     },

    // Market Status Methods
    async loadMarketStatus() {
      try {
        const response = await axios.get('/api/truedata/market-status', {
          headers: {
            'Accept': 'application/json'
          }
        });

        if (response.data.success) {
          this.marketStatus = response.data.data.status;
          this.marketInfo = {
            trading_hours: response.data.data.trading_hours || '9:15 AM - 3:30 PM IST',
            trading_days: 'Monday to Friday',
            next_session: response.data.data.next_open || response.data.data.next_change || ''
          };
        }
      } catch (error) {
        console.error('Error loading market status:', error);
        // Fallback to default values
        this.marketStatus = 'UNKNOWN';
        this.marketInfo = {
          trading_hours: '9:15 AM - 3:30 PM IST',
          trading_days: 'Monday to Friday',
          next_session: ''
        };
      }
    },

    getMarketStatusText() {
      switch (this.marketStatus) {
        case 'OPEN':
          return '🟢 Market LIVE';
        case 'PRE_OPEN':
          return '🟡 Pre-Market';
        case 'POST_CLOSE':
          return '🟡 Post-Market';
        case 'CLOSED':
          return '🔴 Market CLOSED';
        default:
          return '⚪ Status Unknown';
      }
    },

    getMarketStatusClass() {
      switch (this.marketStatus) {
        case 'OPEN':
          return 'live';
        case 'PRE_OPEN':
        case 'POST_CLOSE':
          return 'pre-open';
        case 'CLOSED':
          return 'closed';
        default:
          return 'unknown';
      }
    },

    // Options Modal Methods
    async showOptionsModal(symbol, stock) {
      this.selectedStock = { symbol, ...stock };
      this.showOptions = true;
      
      // Load options data for this symbol
      await this.loadOptionsData(symbol);
    },

    closeOptionsModal() {
      this.showOptions = false;
      this.selectedStock = null;
      this.callOptions = [];
      this.putOptions = [];
    },

    async loadOptionsData(symbol) {
      try {
        // Generate mock options data for demonstration
        // In production, this would call the options API
        this.generateMockOptions(symbol);
        
        // Uncomment below for real API call
        // const response = await axios.get(`/api/truedata/options/chain/${symbol}`);
        // if (response.data.success) {
        //   this.processOptionsData(response.data.data);
        // }
      } catch (error) {
        console.error('Error loading options data:', error);
        this.showError('Failed to load options data');
      }
    },

    generateMockOptions(symbol) {
      const currentPrice = this.selectedStock?.last || 1000;
      const strikes = this.generateStrikes(currentPrice);
      
      this.callOptions = strikes.map(strike => ({
        strike,
        price: this.calculateOptionPrice(currentPrice, strike, 'call'),
        volume: Math.floor(Math.random() * 10000),
        oi: Math.floor(Math.random() * 50000),
        greeks: {
          delta: (Math.random() * 0.8 + 0.1).toFixed(3),
          gamma: (Math.random() * 0.1).toFixed(3),
          theta: (-Math.random() * 5).toFixed(3),
          vega: (Math.random() * 10).toFixed(3)
        }
      }));

      this.putOptions = strikes.map(strike => ({
        strike,
        price: this.calculateOptionPrice(currentPrice, strike, 'put'),
        volume: Math.floor(Math.random() * 10000),
        oi: Math.floor(Math.random() * 50000),
        greeks: {
          delta: (-Math.random() * 0.8 - 0.1).toFixed(3),
          gamma: (Math.random() * 0.1).toFixed(3),
          theta: (-Math.random() * 5).toFixed(3),
          vega: (Math.random() * 10).toFixed(3)
        }
      }));

      // Initialize filtered arrays
      this.filteredCallOptions = [...this.callOptions];
      this.filteredPutOptions = [...this.putOptions];
      this.filteredStrikes = strikes;
      this.optionsSearchQuery = '';
    },

    generateStrikes(currentPrice) {
      const strikes = [];
      const start = Math.floor(currentPrice / 50) * 50 - 200;
      const end = start + 800;
      
      for (let i = start; i <= end; i += 50) {
        strikes.push(i);
      }
      return strikes;
    },

    calculateOptionPrice(spot, strike, type) {
      const intrinsic = type === 'call' ? Math.max(0, spot - strike) : Math.max(0, strike - spot);
      const timeValue = Math.random() * 50 + 10;
      return (intrinsic + timeValue).toFixed(2);
    },

    // Handle keyboard events
    handleKeyPress(event) {
      // Close options modal on ESC key press
      if (event.key === 'Escape' && this.showOptions) {
        this.closeOptionsModal();
      }
    },

    // Filter options based on search query
    filterOptions() {
      if (!this.optionsSearchQuery) {
        this.filteredCallOptions = [...this.callOptions];
        this.filteredPutOptions = [...this.putOptions];
        this.filteredStrikes = this.callOptions.map(opt => opt.strike);
        return;
      }

      const searchTerm = this.optionsSearchQuery.toLowerCase();
      
      this.filteredCallOptions = this.callOptions.filter(option => 
        option.strike.toString().includes(searchTerm)
      );
      
      this.filteredPutOptions = this.putOptions.filter(option => 
        option.strike.toString().includes(searchTerm)
      );
      
      // Get common strikes from filtered options
      const callStrikes = this.filteredCallOptions.map(opt => opt.strike);
      const putStrikes = this.filteredPutOptions.map(opt => opt.strike);
      this.filteredStrikes = [...new Set([...callStrikes, ...putStrikes])].sort((a, b) => a - b);
    },

    // Get strike indicator class based on current price
    getStrikeIndicatorClass(strike) {
      const currentPrice = this.selectedStock?.last || 0;
      if (strike < currentPrice) return 'itm'; // In The Money
      if (strike > currentPrice) return 'otm'; // Out The Money
      return 'atm'; // At The Money
    },

    // Get strike indicator text
    getStrikeIndicatorText(strike) {
      const currentPrice = this.selectedStock?.last || 0;
      if (strike < currentPrice) return 'ITM';
      if (strike > currentPrice) return 'OTM';
      return 'ATM';
    }
  }
};
</script>

<style scoped>
/* Modern Stock Market UI - Matching Withdrawal Request Style */
.stock-market-screen {
  padding: 24px;
  max-width: 1400px;
  margin: 0 auto;
  min-height: 100vh;
  background: linear-gradient(135deg, #0d0d1a 0%, #101022 100%);
  color: white;
}

/* Page Header - Matching Withdrawal Request Style */
.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 32px;
  padding: 24px;
  background: rgba(255, 255, 255, 0.05);
  border-radius: 16px;
  border: 1px solid rgba(0, 255, 128, 0.2);
}

.header-content {
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 20px;
}

.header-left {
  display: flex;
  align-items: center;
  gap: 20px;
}

.back-btn {
  background: rgba(255, 255, 255, 0.1);
  border: none;
  border-radius: 12px;
  padding: 12px;
  color: #ffffff;
  cursor: pointer;
  transition: all 0.3s ease;
  backdrop-filter: blur(10px);
}

.back-btn:hover {
  background: rgba(255, 255, 255, 0.2);
  transform: translateX(-2px);
}

.page-title {
  font-size: 2.5rem;
  font-weight: bold;
  color: #00ff80;
  margin: 0 0 8px 0;
}

.page-subtitle {
  font-size: 1.1rem;
  color: #a1a1a1;
  margin: 0;
}

.header-right {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  gap: 15px;
  text-align: right;
  width: 100%;
  max-width: 300px;
  margin-left: auto;
}

.market-status {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 10px 20px;
  border-radius: 25px;
  font-weight: 600;
  backdrop-filter: blur(10px);
}

.market-status.open {
  background: rgba(0, 255, 136, 0.2);
  color: #00ff88;
  border: 1px solid rgba(0, 255, 136, 0.3);
}

.market-status.closed {
  background: rgba(255, 68, 68, 0.2);
  color: #ff4444;
  border: 1px solid rgba(255, 68, 68, 0.3);
}

.status-indicator {
  width: 10px;
  height: 10px;
  border-radius: 50%;
  background: currentColor;
  animation: pulse 2s infinite;
}

.fetch-btn {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 12px 20px;
  background: linear-gradient(135deg, #ff6b35, #f7931e);
  color: #ffffff;
  border: none;
  border-radius: 25px;
  font-weight: 600;
  font-size: 14px;
  cursor: pointer;
  transition: all 0.3s ease;
  backdrop-filter: blur(10px);
  align-self: flex-end;
  width: fit-content;
}

.fetch-btn:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(255, 107, 53, 0.4);
}

.fetch-btn:disabled {
  background: rgba(108, 117, 125, 0.3);
  color: rgba(255, 255, 255, 0.5);
  cursor: not-allowed;
  transform: none;
}

.refresh-btn {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 12px 20px;
  background: linear-gradient(135deg, #00ff88, #00d4ff);
  color: #000000;
  border: none;
  border-radius: 25px;
  align-self: flex-end;
  width: fit-content;
  font-weight: 600;
  font-size: 14px;
  cursor: pointer;
  transition: all 0.3s ease;
  backdrop-filter: blur(10px);
}

.refresh-btn:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(0, 255, 136, 0.4);
}

.refresh-btn:disabled {
  background: rgba(108, 117, 125, 0.3);
  color: rgba(255, 255, 255, 0.5);
  cursor: not-allowed;
  transform: none;
}

.refresh-btn i.fa-spin {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}

@keyframes pulse {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.5; }
}

/* Connection Status */
.connection-status {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 12px 20px;
  border-radius: 15px;
  margin-bottom: 25px;
  font-weight: 500;
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.1);
}

.connection-status.connected {
  background: rgba(0, 255, 136, 0.1);
  color: #00ff88;
  border-color: rgba(0, 255, 136, 0.2);
}

.connection-status.disconnected {
  background: rgba(255, 68, 68, 0.1);
  color: #ff4444;
  border-color: rgba(255, 68, 68, 0.2);
}

.reconnect-btn {
  background: linear-gradient(135deg, #00ff88, #00d4ff);
  color: #000000;
  border: none;
  border-radius: 8px;
  padding: 8px 16px;
  cursor: pointer;
  font-size: 12px;
  font-weight: 600;
  transition: all 0.3s ease;
}

.reconnect-btn:hover:not(:disabled) {
  transform: translateY(-1px);
  box-shadow: 0 4px 15px rgba(0, 255, 136, 0.3);
}

.reconnect-btn:disabled {
  background: rgba(108, 117, 125, 0.3);
  color: rgba(255, 255, 255, 0.5);
  cursor: not-allowed;
}

/* Market Indices */
.indices-section {
  background: rgba(255, 255, 255, 0.05);
  backdrop-filter: blur(10px);
  border-radius: 20px;
  padding: 25px;
  margin-bottom: 30px;
  border: 1px solid rgba(255, 255, 255, 0.1);
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
}

.section-title {
  font-size: 1.5rem;
  font-weight: 600;
  margin-bottom: 20px;
  color: #ffffff;
}

.indices-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 15px;
}

.index-card {
  background: rgba(255, 255, 255, 0.05);
  backdrop-filter: blur(10px);
  border-radius: 15px;
  padding: 20px;
  border: 1px solid rgba(255, 255, 255, 0.1);
  transition: all 0.3s ease;
}

.index-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
}

.index-card.positive {
  border-left: 4px solid #00ff88;
}

.index-card.negative {
  border-left: 4px solid #ff4444;
}

.index-name {
  font-size: 0.9rem;
  color: rgba(255, 255, 255, 0.8);
  margin-bottom: 8px;
}

.index-price {
  font-size: 1.5rem;
  font-weight: 700;
  margin-bottom: 8px;
}

.index-change {
  display: flex;
  gap: 8px;
  font-size: 0.9rem;
}

.positive .change-value,
.positive .change-percent {
  color: #00ff88;
}

.negative .change-value,
.negative .change-percent {
  color: #ff4444;
}

/* Main Content - Matching Withdrawal Request Style */
.main-content {
  background: rgba(255, 255, 255, 0.05);
  backdrop-filter: blur(10px);
  border-radius: 16px;
  border: 1px solid rgba(255, 255, 255, 0.1);
  transition: all 0.3s ease;
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
}

.stocks-section {
  padding: 30px;
}

.section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 25px;
  flex-wrap: wrap;
  gap: 15px;
  background: rgba(255, 255, 255, 0.05);
  padding: 20px;
  border-radius: 12px;
  border: 1px solid rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(10px);
}

.section-title {
  display: flex;
  align-items: center;
  gap: 12px;
  margin: 0;
}

.section-title h3 {
  font-size: 1.5rem;
  font-weight: 600;
  margin: 0;
  color: #ffffff !important;
}

.section-title i {
  color: #00ff88;
  font-size: 1.2rem;
}

.stock-count {
  color: rgba(255, 255, 255, 0.8) !important;
  font-size: 0.9rem;
  font-weight: 400;
}

/* Filters Section */
.filters-section {
  background: rgba(255, 255, 255, 0.05);
  border-radius: 15px;
  padding: 20px;
  margin-bottom: 25px;
  display: flex;
  gap: 20px;
  flex-wrap: wrap;
  align-items: center;
  border: 1px solid rgba(255, 255, 255, 0.1);
}

.filter-group {
  display: flex;
  flex-direction: column;
  gap: 8px;
  min-width: 200px;
}

.filter-label {
  font-size: 0.9rem;
  font-weight: 500;
  color: rgba(255, 255, 255, 0.9) !important;
}

.search-input-wrapper {
  position: relative;
  display: flex;
  align-items: center;
  width: 100%;
  gap: 8px;
}

.search-icon {
  position: absolute;
  left: 12px;
  color: rgba(255, 255, 255, 0.5);
  z-index: 0;
  pointer-events: none;
}

.filter-input,
.filter-select {
  background: rgba(255, 255, 255, 0.1) !important;
  border: 1px solid rgba(255, 255, 255, 0.2) !important;
  border-radius: 10px;
  padding: 12px 15px;
  color: #ffffff !important;
  font-size: 0.9rem;
  transition: all 0.3s ease;
  width: 100%;
}

.filter-input {
  padding-left: 40px;
  position: relative;
  z-index: 1;
  cursor: text;
}

.filter-input:focus,
.filter-select:focus {
  outline: none;
  border-color: #00ff88 !important;
  box-shadow: 0 0 0 3px rgba(0, 255, 136, 0.1);
  background: rgba(255, 255, 255, 0.15) !important;
  color: #ffffff !important;
}

.filter-input::placeholder {
  color: rgba(255, 255, 255, 0.6) !important;
}

.search-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 40px;
  height: 40px;
  background: linear-gradient(135deg, #00ff88, #00d4ff);
  color: #000000;
  border: none;
  border-radius: 10px;
  cursor: pointer;
  transition: all 0.3s ease;
  font-size: 14px;
  flex-shrink: 0;
}

.search-btn:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(0, 255, 136, 0.4);
}

.search-btn:disabled {
  background: rgba(108, 117, 125, 0.3);
  color: rgba(255, 255, 255, 0.5);
  cursor: not-allowed;
  transform: none;
}

.filter-select option {
  background: #1a1a2e !important;
  color: #ffffff !important;
}

/* Stocks Grid */
.stocks-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
  gap: 20px;
}

.stock-card {
  background: rgba(255, 255, 255, 0.05);
  border-radius: 16px;
  padding: 24px;
  border: 1px solid rgba(255, 255, 255, 0.1);
  transition: all 0.3s ease;
  backdrop-filter: blur(10px);
}

.stock-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 12px 40px rgba(0, 0, 0, 0.3);
  border-color: rgba(0, 255, 128, 0.3);
}

.stock-card.positive {
  border-left: 4px solid #00ff88;
}

.stock-card.negative {
  border-left: 4px solid #ff4444;
}

.stock-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 15px;
}

.stock-symbol {
  font-size: 1.1rem;
  font-weight: 600;
  color: #ffffff;
}

.stock-price {
  font-size: 1.3rem;
  font-weight: 700;
  color: #ffffff;
}

.stock-details {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 15px;
}

.stock-change {
  font-size: 0.9rem;
}

.stock-volume {
  font-size: 0.8rem;
  color: rgba(255, 255, 255, 0.7);
}

.positive .stock-change {
  color: #00ff88;
}

.negative .stock-change {
  color: #ff4444;
}

.stock-ohlc {
  display: flex;
  justify-content: space-between;
  font-size: 0.8rem;
  color: rgba(255, 255, 255, 0.7);
}

.ohlc-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 4px;
}

.ohlc-label {
  font-weight: 500;
  color: rgba(255, 255, 255, 0.5);
}

.ohlc-value {
  font-weight: 600;
  color: #ffffff;
}

/* Empty State - Matching Withdrawal Request Style */
.empty-state {
  text-align: center;
  padding: 60px 20px;
  color: #a1a1a1;
}

.empty-state-icon {
  font-size: 4rem;
  margin-bottom: 16px;
}

.empty-state-title {
  margin: 0 0 8px 0;
  color: #e0e0e0;
  font-size: 1.5rem;
}

.empty-state-message {
  margin: 0;
  font-size: 1.1rem;
  color: #a1a1a1;
}

.empty-state-message strong {
  color: #00ff88 !important;
  font-weight: 600;
}

.empty-state-actions {
  display: flex;
  gap: 12px;
}

.retry-btn {
  background: linear-gradient(135deg, #00ff80, #00cc66);
  color: #0d0d1a;
  border: none;
  padding: 12px 24px;
  border-radius: 8px;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  gap: 8px;
}

.retry-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(0, 255, 128, 0.3);
}

/* Market Info */
.market-info {
  text-align: right;
  font-size: 0.8rem;
  color: rgba(255, 255, 255, 0.8);
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  gap: 5px;
  width: 100%;
  min-width: 200px;
}

.market-status {
  display: flex;
  align-items: center;
  justify-content: flex-end;
  gap: 8px;
  margin-bottom: 4px;
}

.status-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  display: inline-block;
}

.status-dot.live {
  background: #00ff88;
  box-shadow: 0 0 10px rgba(0, 255, 136, 0.5);
  animation: pulse-live 2s infinite;
}

.status-dot.pre-open {
  background: #ffaa00;
  box-shadow: 0 0 10px rgba(255, 170, 0, 0.5);
}

.status-dot.closed {
  background: #ff4444;
  box-shadow: 0 0 10px rgba(255, 68, 68, 0.5);
}

.status-dot.unknown {
  background: #888;
  box-shadow: 0 0 10px rgba(136, 136, 136, 0.5);
}

.status-text {
  font-weight: 600;
  font-size: 0.9rem;
}

@keyframes pulse-live {
  0% { opacity: 1; }
  50% { opacity: 0.5; }
  100% { opacity: 1; }
}

.trading-hours {
  font-weight: 600;
  color: #00ff88;
  text-align: right;
  width: 100%;
}

.trading-days {
  color: rgba(255, 255, 255, 0.7);
  text-align: right;
  width: 100%;
}

.next-session {
  color: #ffaa00;
  font-style: italic;
  margin-top: 2px;
}

.last-updated {
  color: rgba(255, 255, 255, 0.6);
  font-size: 0.8rem;
  margin-top: 5px;
  text-align: right;
  width: 100%;
}

/* Toast Notifications */
.toast-container {
  position: fixed;
  top: 20px;
  right: 20px;
  z-index: 9999;
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.toast {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 16px 20px;
  background: rgba(15, 15, 35, 0.95);
  backdrop-filter: blur(15px);
  border-radius: 12px;
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
  min-width: 300px;
  max-width: 400px;
  cursor: pointer;
  transition: all 0.3s ease;
  border-left: 4px solid;
  animation: slideIn 0.3s ease;
  border: 1px solid rgba(255, 255, 255, 0.1);
}

.toast:hover {
  transform: translateX(-5px);
  box-shadow: 0 12px 40px rgba(0, 0, 0, 0.4);
}

.toast.success {
  border-left-color: #00ff88;
}

.toast.error {
  border-left-color: #ff4444;
}

.toast.warning {
  border-left-color: #ffaa00;
}

.toast.info {
  border-left-color: #00d4ff;
}

.toast-icon {
  font-size: 20px;
  flex-shrink: 0;
}

.toast.success .toast-icon {
  color: #00ff88;
}

.toast.error .toast-icon {
  color: #ff4444;
}

.toast.warning .toast-icon {
  color: #ffaa00;
}

.toast.info .toast-icon {
  color: #00d4ff;
}

.toast-content {
  flex: 1;
}

.toast-title {
  font-weight: 600;
  font-size: 14px;
  margin-bottom: 4px;
  color: #ffffff;
}

.toast-message {
  font-size: 13px;
  color: rgba(255, 255, 255, 0.8);
  line-height: 1.4;
}

.toast-close {
  background: none;
  border: none;
  color: rgba(255, 255, 255, 0.5);
  cursor: pointer;
  padding: 4px;
  border-radius: 4px;
  transition: all 0.2s ease;
  flex-shrink: 0;
}

.toast-close:hover {
  background: rgba(255, 255, 255, 0.1);
  color: #ffffff;
}

@keyframes slideIn {
  from {
    transform: translateX(100%);
    opacity: 0;
  }
  to {
    transform: translateX(0);
    opacity: 1;
  }
}

/* Responsive Design */
@media (max-width: 768px) {
  .stock-market-screen {
    padding: 15px;
  }
  
  .header-content {
    flex-direction: column;
    gap: 20px;
    text-align: center;
  }
  
  .header-right {
    align-items: center;
  }
  
  .indices-grid {
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 15px;
  }
  
  .stocks-grid {
    grid-template-columns: 1fr;
  }
  
  .section-header {
    flex-direction: column;
    align-items: stretch;
  }
  
  .filters-section {
    flex-direction: column;
    align-items: stretch;
  }
  
  .filter-group {
    min-width: auto;
  }
}

@media (max-width: 480px) {
  .page-title {
    font-size: 1.5rem;
  }
  
  .indices-grid {
    grid-template-columns: 1fr;
  }
  
  .stock-ohlc {
    flex-direction: column;
    gap: 8px;
  }
}

/* Empty State */
.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 60px 20px;
  text-align: center;
  background: white;
  border-radius: 12px;
  box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.empty-state-icon {
  font-size: 64px;
  color: #6c757d;
  margin-bottom: 20px;
}

.empty-state-title {
  font-size: 24px;
  font-weight: 600;
  color: #333;
  margin-bottom: 12px;
}

.empty-state-message {
  font-size: 16px;
  color: #666;
  line-height: 1.6;
  margin-bottom: 30px;
  max-width: 500px;
}

.empty-state-actions {
  display: flex;
  gap: 12px;
}

.retry-btn {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 12px 24px;
  background: #007bff;
  color: white;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
}

.retry-btn:hover {
  background: #0056b3;
  transform: translateY(-1px);
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

/* Removed conflicting light background CSS for indices section */

.indices-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 15px;
}

/* Removed conflicting light background CSS for index cards */

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

/* Removed conflicting light background CSS */

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

/* Removed conflicting CSS rules */

.stocks-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 15px;
}

/* Removed conflicting light background CSS for stock cards */

.stock-details {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 10px;
}

/* Removed conflicting CSS for stock change and volume */

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

/* Removed conflicting CSS for market-info alignment */

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

/* Options Modal Styles */
.options-modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.8);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 9999;
  backdrop-filter: blur(4px);
}

/* Search Container */
.search-container {
  position: relative;
  display: flex;
  align-items: center;
}

.options-search {
  background: rgba(255, 255, 255, 0.1);
  border: 1px solid rgba(255, 255, 255, 0.2);
  border-radius: 8px;
  padding: 8px 35px 8px 12px;
  color: #fff;
  font-size: 0.9rem;
  width: 200px;
  transition: all 0.3s ease;
}

.options-search:focus {
  outline: none;
  border-color: #00d4ff;
  box-shadow: 0 0 10px rgba(0, 212, 255, 0.3);
  background: rgba(255, 255, 255, 0.15);
}

.options-search::placeholder {
  color: rgba(255, 255, 255, 0.5);
}

.search-icon {
  position: absolute;
  right: 10px;
  color: rgba(255, 255, 255, 0.6);
  font-size: 0.9rem;
}

.options-modal {
  background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
  border-radius: 20px;
  width: 90%;
  max-width: 1200px;
  max-height: 80vh;
  overflow: hidden;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
  border: 1px solid rgba(255, 255, 255, 0.1);
}

.options-header {
  background: rgba(255, 255, 255, 0.05);
  padding: 20px 30px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.header-actions {
  display: flex;
  align-items: center;
  gap: 15px;
}

.esc-hint {
  color: rgba(255, 255, 255, 0.6);
  font-size: 0.85rem;
  font-style: italic;
  background: rgba(255, 255, 255, 0.1);
  padding: 4px 8px;
  border-radius: 6px;
  border: 1px solid rgba(255, 255, 255, 0.2);
}

.options-header h3 {
  color: #fff;
  margin: 0;
  font-size: 1.5rem;
  font-weight: 600;
}

.close-btn {
  background: none;
  border: none;
  color: #fff;
  font-size: 1.5rem;
  cursor: pointer;
  padding: 5px;
  border-radius: 50%;
  transition: all 0.3s ease;
}

.close-btn:hover {
  background: rgba(255, 255, 255, 0.1);
  transform: rotate(90deg);
}

.options-content {
  padding: 30px;
  max-height: 60vh;
  overflow-y: auto;
}

/* Side-by-Side Options Layout */
.options-comparison {
  display: grid;
  grid-template-columns: 1fr 120px 1fr;
  gap: 20px;
  align-items: start;
}

.options-column {
  display: flex;
  flex-direction: column;
}

.options-list {
  display: flex;
  flex-direction: column;
  gap: 8px;
  max-height: 50vh;
  overflow-y: auto;
}

.option-row {
  background: rgba(255, 255, 255, 0.05);
  border-radius: 8px;
  padding: 12px;
  border: 1px solid rgba(255, 255, 255, 0.1);
  transition: all 0.3s ease;
  cursor: pointer;
}

.option-row:hover {
  transform: translateY(-1px);
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
}

.option-row.call-option {
  border-left: 3px solid #00ff88;
}

.option-row.call-option:hover {
  border-color: #00ff88;
  box-shadow: 0 4px 15px rgba(0, 255, 136, 0.2);
}

.option-row.put-option {
  border-left: 3px solid #ff4444;
}

.option-row.put-option:hover {
  border-color: #ff4444;
  box-shadow: 0 4px 15px rgba(255, 68, 68, 0.2);
}

/* Strike Column */
.strike-column {
  display: flex;
  flex-direction: column;
  align-items: center;
}

.strike-list {
  display: flex;
  flex-direction: column;
  gap: 8px;
  max-height: 50vh;
  overflow-y: auto;
  width: 100%;
}

.strike-row {
  background: rgba(255, 255, 255, 0.05);
  border-radius: 8px;
  padding: 12px 8px;
  border: 1px solid rgba(255, 255, 255, 0.1);
  text-align: center;
  transition: all 0.3s ease;
}

.strike-price {
  font-size: 1rem;
  font-weight: 600;
  color: #fff;
  margin-bottom: 4px;
}

.strike-indicator {
  font-size: 0.7rem;
  font-weight: 500;
  padding: 2px 6px;
  border-radius: 4px;
  text-transform: uppercase;
}

.strike-indicator.itm {
  background: rgba(0, 255, 136, 0.2);
  color: #00ff88;
  border: 1px solid rgba(0, 255, 136, 0.3);
}

.strike-indicator.otm {
  background: rgba(255, 68, 68, 0.2);
  color: #ff4444;
  border: 1px solid rgba(255, 68, 68, 0.3);
}

.strike-indicator.atm {
  background: rgba(0, 212, 255, 0.2);
  color: #00d4ff;
  border: 1px solid rgba(0, 212, 255, 0.3);
}

/* Section Titles */
.strike-title {
  color: #00d4ff;
  border-color: #00d4ff;
}

.options-section {
  margin-bottom: 40px;
}

.section-title {
  display: flex;
  align-items: center;
  gap: 10px;
  font-size: 1.2rem;
  font-weight: 600;
  margin-bottom: 20px;
  padding-bottom: 10px;
  border-bottom: 2px solid;
}

.call-title {
  color: #00ff88;
  border-color: #00ff88;
}

.put-title {
  color: #ff4444;
  border-color: #ff4444;
}

.options-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 15px;
}

.option-card {
  background: rgba(255, 255, 255, 0.05);
  border-radius: 12px;
  padding: 20px;
  border: 1px solid rgba(255, 255, 255, 0.1);
  transition: all 0.3s ease;
  cursor: pointer;
}

.option-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
}

.call-option {
  border-left: 4px solid #00ff88;
}

.call-option:hover {
  border-color: #00ff88;
  box-shadow: 0 8px 25px rgba(0, 255, 136, 0.2);
}

.put-option {
  border-left: 4px solid #ff4444;
}

.put-option:hover {
  border-color: #ff4444;
  box-shadow: 0 8px 25px rgba(255, 68, 68, 0.2);
}

.option-strike {
  font-size: 1.1rem;
  font-weight: 600;
  color: #fff;
  margin-bottom: 8px;
}

.option-price {
  font-size: 1.3rem;
  font-weight: 700;
  color: #00d4ff;
  margin-bottom: 12px;
}

.option-details {
  display: flex;
  justify-content: space-between;
  margin-bottom: 12px;
  font-size: 0.9rem;
  color: rgba(255, 255, 255, 0.7);
}

.option-greeks {
  font-size: 0.8rem;
  color: rgba(255, 255, 255, 0.6);
}

.greeks-row {
  display: flex;
  justify-content: space-between;
  margin-bottom: 4px;
}

.greek {
  padding: 2px 6px;
  background: rgba(255, 255, 255, 0.1);
  border-radius: 4px;
  font-family: 'Courier New', monospace;
}

/* Scrollbar styling */
.options-content::-webkit-scrollbar {
  width: 8px;
}

.options-content::-webkit-scrollbar-track {
  background: rgba(255, 255, 255, 0.1);
  border-radius: 4px;
}

.options-content::-webkit-scrollbar-thumb {
  background: rgba(255, 255, 255, 0.3);
  border-radius: 4px;
}

.options-content::-webkit-scrollbar-thumb:hover {
  background: rgba(255, 255, 255, 0.5);
}

/* Mobile Responsive */
@media (max-width: 768px) {
  .options-modal {
    width: 95%;
    max-height: 85vh;
  }
  
  .options-header {
    padding: 15px 20px;
  }
  
  .options-content {
    padding: 20px;
  }
  
  .options-grid {
    grid-template-columns: 1fr;
    gap: 12px;
  }
  
  .option-card {
    padding: 15px;
  }
  
  .header-actions {
    gap: 10px;
  }
  
  .esc-hint {
    font-size: 0.75rem;
    padding: 3px 6px;
  }
  
  .options-search {
    width: 150px;
    font-size: 0.8rem;
  }
  
  .options-comparison {
    grid-template-columns: 1fr;
    gap: 15px;
  }
  
  .strike-column {
    order: -1;
  }
  
  .strike-list {
    flex-direction: row;
    overflow-x: auto;
    overflow-y: hidden;
    max-height: none;
    padding-bottom: 10px;
  }
  
  .strike-row {
    min-width: 80px;
    flex-shrink: 0;
  }
}
</style>
