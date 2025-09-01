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
              {{ marketStatus === 'OPEN' ? 'Live market data powered by Upstox' : 'Last available data (Market Closed)' }}
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

    <!-- Market Indices -->
    <div class="indices-section">
      <h2 class="section-title">Market Indices</h2>
      <div class="indices-grid">
        <div 
          v-for="index in marketIndices" 
          :key="index.instrument_key"
          class="index-card"
          :class="{ 'positive': index.net_change > 0, 'negative': index.net_change < 0 }"
        >
          <div class="index-name">{{ index.trading_symbol }}</div>
          <div class="index-price">₹{{ formatNumber(index.last_price) }}</div>
          <div class="index-change">
            <span class="change-value">{{ index.net_change > 0 ? '+' : '' }}{{ formatNumber(index.net_change) }}</span>
            <span class="change-percent">({{ index.percent_change > 0 ? '+' : '' }}{{ index.percent_change?.toFixed(2) }}%)</span>
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
              <label class="filter-label">Sector:</label>
              <select v-model="selectedSector" class="filter-select">
                <option v-for="sector in sectors" :key="sector.id" :value="sector.id">
                  {{ sector.name }}
                </option>
              </select>
            </div>
            
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
              <label class="filter-label">Items per page:</label>
              <select v-model="itemsPerPage" class="filter-select">
                <option value="20">20</option>
                <option value="40">40</option>
                <option value="50">50</option>
                <option value="100">100</option>
              </select>
            </div>
          </div>
          
          <div class="stocks-grid">
            <div 
              v-for="stock in paginatedStocks" 
              :key="stock.instrument_key"
              class="stock-card"
              :class="{ 'positive': stock.net_change > 0, 'negative': stock.net_change < 0 }"
            >
              <div class="stock-header">
                <div class="stock-symbol">{{ stock.trading_symbol }}</div>
                <div class="stock-change-badge" :class="stock.net_change > 0 ? 'positive' : 'negative'">
                  {{ stock.percent_change > 0 ? '+' : '' }}{{ stock.percent_change?.toFixed(2) }}%
                </div>
              </div>
              <div class="stock-price">₹{{ formatNumber(stock.last_price) }}</div>
              <div class="stock-change">
                {{ stock.net_change > 0 ? '+' : '' }}{{ formatNumber(stock.net_change) }}
              </div>
              <div class="stock-details">
                <div class="detail-item">
                  <span class="label">Open:</span>
                  <span class="value">₹{{ formatNumber(stock.open) }}</span>
                </div>
                <div class="detail-item">
                  <span class="label">High:</span>
                  <span class="value">₹{{ formatNumber(stock.high) }}</span>
                </div>
                <div class="detail-item">
                  <span class="label">Low:</span>
                  <span class="value">₹{{ formatNumber(stock.low) }}</span>
                </div>
                <div class="detail-item">
                  <span class="label">Volume:</span>
                  <span class="value">{{ formatVolume(stock.volume) }}</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Pagination -->
          <div v-if="totalPages > 1" class="pagination-section">
            <div class="pagination-info">
              Showing {{ (currentPage - 1) * itemsPerPage + 1 }}-{{ Math.min(currentPage * itemsPerPage, filteredStocks.length) }} of {{ filteredStocks.length }} stocks
            </div>
            <div class="pagination-controls">
              <button 
                @click="currentPage = 1" 
                :disabled="currentPage === 1"
                class="pagination-btn"
              >
                <i class="fas fa-angle-double-left"></i>
              </button>
              <button 
                @click="currentPage--" 
                :disabled="currentPage === 1"
                class="pagination-btn"
              >
                <i class="fas fa-angle-left"></i>
              </button>
              
              <span class="pagination-current">{{ currentPage }} / {{ totalPages }}</span>
              
              <button 
                @click="currentPage++" 
                :disabled="currentPage === totalPages"
                class="pagination-btn"
              >
                <i class="fas fa-angle-right"></i>
              </button>
              <button 
                @click="currentPage = totalPages" 
                :disabled="currentPage === totalPages"
                class="pagination-btn"
              >
                <i class="fas fa-angle-double-right"></i>
              </button>
            </div>
          </div>
        </div>



        <!-- Search Tab -->
        <div v-if="activeTab === 'search'" class="tab-panel">
          <div class="panel-header">
            <h3>Search Stocks</h3>
            <div class="search-summary">
              <span class="summary-text">🔍 Find stocks from {{ liveStocks.length }} available stocks</span>
            </div>
          </div>
          
          <div class="search-section">
            <div class="search-header">
              <h4>Quick Search</h4>
              <p>Type stock name or symbol to get instant suggestions</p>
            </div>

            <div class="search-input-container">
              <div class="search-input-group">
                <div class="search-icon">
                  <i class="fas fa-search"></i>
                </div>
                <input 
                  v-model="searchQuery"
                  type="text"
                  placeholder="Type stock name or symbol (e.g., RELIANCE, TCS, INFY)..."
                  class="search-input"
                  @input="onSearchInput"
                  @focus="showSuggestions = true"
                  @blur="hideSuggestions"
                >
                <button v-if="searchQuery" class="clear-btn" @click="clearSearch">
                  <i class="fas fa-times"></i>
                </button>
              </div>

              <!-- Dropdown Suggestions -->
              <div v-if="showSuggestions && searchSuggestions.length > 0" class="suggestions-dropdown">
                <div class="suggestions-header">
                  <span>{{ searchSuggestions.length }} matches found</span>
                </div>
                <div 
                  v-for="(suggestion, index) in searchSuggestions" 
                  :key="suggestion.instrument_key"
                  class="suggestion-item"
                  @mousedown="selectStock(suggestion)"
                >
                  <div class="suggestion-content">
                    <div class="suggestion-symbol">{{ suggestion.trading_symbol }}</div>
                    <div class="suggestion-price">₹{{ formatNumber(suggestion.last_price) }}</div>
                    <div class="suggestion-change" :class="suggestion.net_change > 0 ? 'positive' : 'negative'">
                      {{ suggestion.net_change > 0 ? '+' : '' }}{{ suggestion.percent_change?.toFixed(2) }}%
                    </div>
                  </div>
                  <div class="suggestion-sector">{{ getStockSectorName(suggestion.trading_symbol) }}</div>
                </div>
              </div>
            </div>



            <!-- Selected Stock Details -->
            <div v-if="selectedStock" class="selected-stock-section">
              <h4>Stock Details</h4>
              <div class="selected-stock-card">
                <div class="stock-header-section">
                  <div class="stock-main-info">
                    <h3 class="stock-title">{{ selectedStock.trading_symbol }}</h3>
                    <div class="stock-sector-tag">{{ getStockSectorName(selectedStock.trading_symbol) }}</div>
                  </div>
                  <div class="stock-price-info">
                    <div class="current-price">₹{{ formatNumber(selectedStock.last_price) }}</div>
                    <div class="price-change" :class="selectedStock.net_change > 0 ? 'positive' : 'negative'">
                      {{ selectedStock.net_change > 0 ? '+' : '' }}₹{{ formatNumber(selectedStock.net_change) }}
                      ({{ selectedStock.percent_change > 0 ? '+' : '' }}{{ selectedStock.percent_change?.toFixed(2) }}%)
                    </div>
                  </div>
                </div>

                <div class="stock-details-grid">
                  <div class="detail-card">
                    <div class="detail-label">Open</div>
                    <div class="detail-value">₹{{ formatNumber(selectedStock.open) }}</div>
                  </div>
                  <div class="detail-card">
                    <div class="detail-label">High</div>
                    <div class="detail-value high">₹{{ formatNumber(selectedStock.high) }}</div>
                  </div>
                  <div class="detail-card">
                    <div class="detail-label">Low</div>
                    <div class="detail-value low">₹{{ formatNumber(selectedStock.low) }}</div>
                  </div>
                  <div class="detail-card">
                    <div class="detail-label">Volume</div>
                    <div class="detail-value">{{ formatVolume(selectedStock.volume) }}</div>
                  </div>
                </div>

                <div class="stock-actions">
                  <button class="action-btn primary" @click="addToWatchlist(selectedStock)">
                    <i class="fas fa-star"></i>
                    Add to Watchlist
                  </button>
                  <button class="action-btn secondary" @click="viewStockChart(selectedStock)">
                    <i class="fas fa-chart-line"></i>
                    View Chart
                  </button>
                  <button class="action-btn secondary" @click="selectedStock = null">
                    <i class="fas fa-times"></i>
                    Close
                  </button>
                </div>
              </div>
            </div>


          </div>
        </div>
      </div>
    </div>

    <!-- Loading Overlay -->
    <div v-if="loading" class="loading-overlay">
      <div class="loading-spinner">
        <i class="fas fa-spinner fa-spin"></i>
        <p>Loading market data...</p>
      </div>
    </div>

    <!-- Mobile Bottom Navigation -->
    <div class="mobile-bottom-nav">
      <button @click="navigateTo('/admin/dashboard')" class="nav-item">
        <i class="fas fa-home"></i>
        <span>Dashboard</span>
      </button>
      <button @click="refreshStockData" class="nav-item">
        <i class="fas fa-sync-alt"></i>
        <span>Refresh</span>
      </button>
      <button @click="activeTab = 'search'" class="nav-item">
        <i class="fas fa-search"></i>
        <span>Search</span>
      </button>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'StockMarket',
  data() {
    return {
      loading: false,
      activeTab: 'stocks',
      searchQuery: '',
      lastUpdated: '',
      marketStatus: 'CLOSED',
      marketInfo: {
        trading_hours: '9:00 AM - 3:30 PM IST',
        trading_days: 'Monday to Friday',
        next_session: ''
      },
      liveStocks: [],
      marketIndices: [],

      searchResults: [],
      searchSuggestions: [],
      selectedStock: null,

      showSuggestions: false,
      currentPage: 1,
      itemsPerPage: 40,
      selectedSector: 'all',
      tabs: [
        { id: 'stocks', name: 'Live Stocks', icon: 'fas fa-chart-line' },
        { id: 'search', name: 'Search', icon: 'fas fa-search' }
      ],
      sectors: [
        { id: 'all', name: 'All Stocks' },
        { id: 'banking', name: 'Banking' },
        { id: 'it', name: 'IT & Tech' },
        { id: 'auto', name: 'Automobile' },
        { id: 'fmcg', name: 'FMCG' },
        { id: 'metal', name: 'Metal & Mining' },
        { id: 'energy', name: 'Energy & Oil' }
      ]
    };
  },
  computed: {
    marketStatusClass() {
      return {
        'market-open': this.marketStatus === 'OPEN',
        'market-closed': this.marketStatus === 'CLOSED'
      };
    },
    marketStatusText() {
      return this.marketStatus === 'OPEN' ? 'Market Open' : 'Market Closed';
    },

    filteredStocks() {
      let filtered = this.liveStocks;
      
      // Filter by sector
      if (this.selectedSector !== 'all') {
        filtered = filtered.filter(stock => this.getStockSector(stock.trading_symbol) === this.selectedSector);
      }
      
      // Filter by search query
      if (this.searchQuery) {
        const query = this.searchQuery.toLowerCase();
        filtered = filtered.filter(stock => 
          stock.trading_symbol.toLowerCase().includes(query)
        );
      }
      
      return filtered;
    },
    paginatedStocks() {
      const start = (this.currentPage - 1) * this.itemsPerPage;
      const end = start + this.itemsPerPage;
      return this.filteredStocks.slice(start, end);
    },
    totalPages() {
      return Math.ceil(this.filteredStocks.length / this.itemsPerPage);
    },

  },
  mounted() {
    this.loadMarketData();
    this.updateTimestamp();
    
    // Initial interval setup
    this.adjustRefreshInterval();
  },
  beforeUnmount() {
    if (this.refreshInterval) {
      clearInterval(this.refreshInterval);
    }
  },
  methods: {
    async loadMarketData() {
      this.loading = true;
      console.log('Loading market data...');
      
      try {
        const token = localStorage.getItem('access_token');
        console.log('Token:', token ? 'Present' : 'Missing');
        
        // Load dashboard data
        const response = await axios.get('/api/upstox/dashboard', {
          headers: {
            'Authorization': `Bearer ${token}`,
            'Accept': 'application/json'
          }
        });

        console.log('Dashboard API Response:', response.data);

        if (response.data.success) {
          const data = response.data.data;
          console.log('Processing data:', data);
          
          // Process market quotes
          if (data.quotes && data.quotes.success && data.quotes.data && data.quotes.data.data) {
            this.liveStocks = this.formatStockData(data.quotes.data.data);
            console.log('Live stocks processed:', this.liveStocks);
          }
          
          // Process market indices
          if (data.indices && data.indices.success && data.indices.data && data.indices.data.data) {
            this.marketIndices = this.formatStockData(data.indices.data.data);
            console.log('Market indices processed:', this.marketIndices);
          }
          
          // Process market status
          if (data.status && data.status.success && data.status.data) {
            this.marketStatus = data.status.data.market_status || 'CLOSED';
            this.marketInfo = {
              trading_hours: data.status.data.trading_hours || '9:00 AM - 3:30 PM IST',
              trading_days: data.status.data.trading_days || 'Monday to Friday',
              next_session: data.status.data.next_session || ''
            };
            console.log('Market status:', this.marketStatus);
            console.log('Market info:', this.marketInfo);
          }
        }



      } catch (error) {
        console.error('Error loading market data:', error);
        if (error.response) {
          console.error('Error response:', error.response.data);
        }
        // Don't show toast error for now during debugging
        // this.$toast.error('Failed to load market data');
      } finally {
        this.loading = false;
        this.updateTimestamp();
        console.log('Market data loading completed');
      }
    },



    async refreshStockData() {
      await this.loadMarketData();
      console.log('Market data refreshed');
      
      // Adjust refresh interval based on market status
      this.adjustRefreshInterval();
    },

    adjustRefreshInterval() {
      // Clear existing interval
      if (this.refreshInterval) {
        clearInterval(this.refreshInterval);
      }
      
      if (this.marketStatus === 'OPEN') {
        // Market is open - refresh every 15 seconds
        this.refreshInterval = setInterval(() => {
          this.refreshStockData();
        }, 15000);
        console.log('Market is OPEN - refreshing every 15 seconds');
      } else {
        // Market is closed - just update timestamp every minute
        this.refreshInterval = setInterval(() => {
          this.updateTimestamp();
        }, 60000); // 1 minute
        console.log('Market is CLOSED - updating timestamp every minute');
      }
    },

    async searchStocks() {
      if (!this.searchQuery.trim()) {
        this.searchResults = [];
        return;
      }

      try {
        const token = localStorage.getItem('access_token');
        const response = await axios.get('/api/upstox/search-instruments', {
          params: { query: this.searchQuery },
          headers: { 'Authorization': `Bearer ${token}` }
        });

        if (response.data.success) {
          this.searchResults = response.data.data || [];
        }

      } catch (error) {
        console.error('Error searching stocks:', error);
        // this.$toast.error('Failed to search stocks');
      }
    },

    formatStockData(data) {
      const formatted = [];
      for (const [key, stock] of Object.entries(data)) {
        formatted.push({
          instrument_key: key,
          trading_symbol: stock.trading_symbol || 'N/A',
          last_price: stock.last_price || 0,
          net_change: stock.net_change || 0,
          percent_change: stock.percent_change || 0,
          volume: stock.volume || 0,
          open: stock.ohlc?.open || 0,
          high: stock.ohlc?.high || 0,
          low: stock.ohlc?.low || 0,
          close: stock.ohlc?.close || 0
        });
      }
      return formatted;
    },

    formatNumber(num) {
      if (!num) return '0';
      return new Intl.NumberFormat('en-IN').format(num);
    },

    formatVolume(volume) {
      if (!volume) return '0';
      if (volume >= 10000000) return (volume / 10000000).toFixed(1) + 'Cr';
      if (volume >= 100000) return (volume / 100000).toFixed(1) + 'L';
      if (volume >= 1000) return (volume / 1000).toFixed(1) + 'K';
      return volume.toString();
    },

    updateTimestamp() {
      this.lastUpdated = new Date().toLocaleTimeString();
    },

    addToWatchlist(stock) {
      console.log(`${stock.trading_symbol} added to watchlist`);
      // this.$toast.success(`${stock.trading_symbol} added to watchlist`);
      // Implement watchlist functionality
    },

    navigateTo(path) {
      this.$router.push(path);
    },

    getStockSector(symbol) {
      const bankingStocks = ['HDFCBANK', 'ICICIBANK', 'AXISBANK', 'KOTAKBANK', 'SBIN', 'INDUSINDBK', 'FEDERALBNK', 'BANKBARODA', 'PNB', 'CANBK'];
      const itStocks = ['INFY', 'TCS', 'WIPRO', 'HCLTECH', 'TECHM', 'MINDTREE', 'MPHASIS', 'LTI', 'PERSISTENT', 'COFORGE'];
      const autoStocks = ['MARUTI', 'TATAMOTORS', 'M&M', 'BAJAJ-AUTO', 'HEROMOTOCO', 'EICHERMOT', 'TVSMOTOR'];
      const fmcgStocks = ['ITC', 'HINDUNILVR', 'BRITANNIA', 'DABUR', 'GODREJCP', 'MARICO', 'COLPAL', 'NESTLEIND'];
      const metalStocks = ['TATASTEEL', 'JSWSTEEL', 'HINDALCO', 'VEDL', 'COALINDIA', 'SAIL'];
      const energyStocks = ['RELIANCE', 'ONGC', 'IOC', 'BPCL', 'GAIL'];

      if (bankingStocks.includes(symbol)) return 'banking';
      if (itStocks.includes(symbol)) return 'it';
      if (autoStocks.includes(symbol)) return 'auto';
      if (fmcgStocks.includes(symbol)) return 'fmcg';
      if (metalStocks.includes(symbol)) return 'metal';
      if (energyStocks.includes(symbol)) return 'energy';
      return 'all';
    },

    getStockSectorName(symbol) {
      const sector = this.getStockSector(symbol);
      const sectorNames = {
        'banking': 'Banking & Finance',
        'it': 'IT & Technology',
        'auto': 'Automobile',
        'fmcg': 'FMCG & Consumer',
        'metal': 'Metal & Mining',
        'energy': 'Energy & Oil',
        'all': 'Others'
      };
      return sectorNames[sector] || 'Others';
    },

    onSearchInput() {
      if (this.searchQuery.trim().length >= 1) {
        this.updateSearchSuggestions();
        this.showSuggestions = true;
      } else {
        this.searchSuggestions = [];
        this.showSuggestions = false;
      }
    },

    updateSearchSuggestions() {
      const query = this.searchQuery.toLowerCase().trim();
      if (!query) {
        this.searchSuggestions = [];
        return;
      }

      this.searchSuggestions = this.liveStocks
        .filter(stock => 
          stock.trading_symbol.toLowerCase().includes(query)
        )
        .slice(0, 15) // Show max 15 suggestions
        .sort((a, b) => {
          // Prioritize exact matches
          const aExact = a.trading_symbol.toLowerCase().startsWith(query);
          const bExact = b.trading_symbol.toLowerCase().startsWith(query);
          if (aExact && !bExact) return -1;
          if (!aExact && bExact) return 1;
          return a.trading_symbol.localeCompare(b.trading_symbol);
        });
    },

    selectStock(stock) {
      this.selectedStock = stock;
      this.searchQuery = stock.trading_symbol;
      this.showSuggestions = false;
      console.log('Selected stock:', stock.trading_symbol);
    },

    clearSearch() {
      this.searchQuery = '';
      this.searchSuggestions = [];
      this.selectedStock = null;
      this.showSuggestions = false;
    },

    hideSuggestions() {
      // Delay hiding to allow click events on suggestions
      setTimeout(() => {
        this.showSuggestions = false;
      }, 200);
    },



    viewStockChart(stock) {
      console.log('View chart for:', stock.trading_symbol);
      // Implement chart view functionality
    }
  },
  watch: {
    selectedSector() {
      this.currentPage = 1;
    },
    searchQuery() {
      this.currentPage = 1;
    },
    itemsPerPage() {
      this.currentPage = 1;
    },
    marketStatus(newStatus, oldStatus) {
      // Adjust refresh interval when market status changes
      if (newStatus !== oldStatus) {
        console.log(`Market status changed from ${oldStatus} to ${newStatus}`);
        this.adjustRefreshInterval();
      }
    }
  }
};
</script>

<style scoped>
.stock-market-screen {
  min-height: 100vh;
  background: linear-gradient(135deg, #0f0f23 0%, #1a1a2e 50%, #16213e 100%);
  color: #ffffff;
  padding: 20px;
  padding-bottom: 300px;
  overflow-x: hidden;
}

/* Header */
.page-header {
  background: rgba(255, 255, 255, 0.05);
  backdrop-filter: blur(10px);
  border-radius: 20px;
  padding: 25px;
  margin-bottom: 30px;
  border: 1px solid rgba(255, 255, 255, 0.1);
}

.header-content {
  display: flex;
  justify-content: space-between;
  align-items: center;
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
}

.back-btn:hover {
  background: rgba(255, 255, 255, 0.2);
  transform: translateX(-2px);
}

.page-title {
  font-size: 2rem;
  font-weight: 700;
  margin: 0;
  background: linear-gradient(135deg, #00ff88, #00d4ff);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
}

.page-subtitle {
  color: rgba(255, 255, 255, 0.7);
  margin: 5px 0 0 0;
}

.header-right {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  gap: 10px;
}

.market-info {
  text-align: right;
  font-size: 0.85rem;
  color: rgba(255, 255, 255, 0.8);
}

.trading-hours {
  font-weight: 600;
  color: #00ff88;
}

.trading-days {
  color: rgba(255, 255, 255, 0.7);
}

.next-session {
  color: #ffaa00;
  font-style: italic;
  margin-top: 2px;
}

.market-status {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 8px 16px;
  border-radius: 20px;
  font-weight: 600;
}

.market-status.market-open {
  background: rgba(0, 255, 136, 0.2);
  color: #00ff88;
}

.market-status.market-closed {
  background: rgba(255, 68, 68, 0.2);
  color: #ff4444;
}

.status-indicator {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: currentColor;
  animation: pulse 2s infinite;
}

.last-updated {
  color: rgba(255, 255, 255, 0.6);
  font-size: 0.9rem;
}

/* Market Indices */
.indices-section {
  margin-bottom: 30px;
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
  margin-bottom: 20px;
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



/* Tabs */
.content-tabs {
  background: rgba(255, 255, 255, 0.05);
  backdrop-filter: blur(10px);
  border-radius: 20px;
  border: 1px solid rgba(255, 255, 255, 0.1);
  overflow: hidden;
}

.tab-buttons {
  display: flex;
  background: rgba(0, 0, 0, 0.2);
  padding: 5px;
  gap: 5px;
}

.tab-btn {
  flex: 1;
  background: transparent;
  border: none;
  padding: 15px 20px;
  color: rgba(255, 255, 255, 0.7);
  border-radius: 15px;
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  font-weight: 500;
}

.tab-btn:hover {
  background: rgba(255, 255, 255, 0.1);
  color: #ffffff;
}

.tab-btn.active {
  background: linear-gradient(135deg, #00ff88, #00d4ff);
  color: #000000;
  font-weight: 600;
}

.tab-content {
  padding: 30px;
}

.panel-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 25px;
}

.panel-header h3 {
  font-size: 1.5rem;
  font-weight: 600;
  margin: 0;
}

.refresh-btn {
  background: linear-gradient(135deg, #00ff88, #00d4ff);
  border: none;
  border-radius: 12px;
  padding: 10px 20px;
  color: #000000;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  gap: 8px;
}

.refresh-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 5px 15px rgba(0, 255, 136, 0.3);
}

.refresh-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
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
}

.filter-group {
  display: flex;
  flex-direction: column;
  gap: 5px;
  min-width: 150px;
}

.filter-label {
  font-size: 0.9rem;
  font-weight: 500;
  color: rgba(255, 255, 255, 0.8);
}

.filter-select,
.filter-input {
  background: rgba(255, 255, 255, 0.1);
  border: 1px solid rgba(255, 255, 255, 0.2);
  border-radius: 8px;
  padding: 8px 12px;
  color: #ffffff;
  font-size: 0.9rem;
}

.filter-select:focus,
.filter-input:focus {
  outline: none;
  border-color: #00ff88;
  box-shadow: 0 0 0 3px rgba(0, 255, 136, 0.1);
}

.filter-input::placeholder {
  color: rgba(255, 255, 255, 0.5);
}

/* Pagination */
.pagination-section {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: 30px;
  padding: 20px;
  background: rgba(255, 255, 255, 0.05);
  border-radius: 15px;
}

.pagination-info {
  color: rgba(255, 255, 255, 0.8);
  font-size: 0.9rem;
}

.pagination-controls {
  display: flex;
  align-items: center;
  gap: 10px;
}

.pagination-btn {
  background: rgba(255, 255, 255, 0.1);
  border: 1px solid rgba(255, 255, 255, 0.2);
  border-radius: 8px;
  padding: 8px 12px;
  color: #ffffff;
  cursor: pointer;
  transition: all 0.3s ease;
}

.pagination-btn:hover:not(:disabled) {
  background: rgba(0, 255, 136, 0.2);
  border-color: #00ff88;
}

.pagination-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.pagination-current {
  background: linear-gradient(135deg, #00ff88, #00d4ff);
  color: #000000;
  font-weight: 600;
  padding: 8px 16px;
  border-radius: 8px;
  min-width: 80px;
  text-align: center;
}

/* Stocks Grid */
.stocks-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 20px;
}

.stock-card {
  background: rgba(255, 255, 255, 0.05);
  border-radius: 15px;
  padding: 20px;
  border: 1px solid rgba(255, 255, 255, 0.1);
  transition: all 0.3s ease;
}

.stock-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
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
}

.stock-change-badge {
  padding: 4px 8px;
  border-radius: 8px;
  font-size: 0.8rem;
  font-weight: 600;
}

.stock-change-badge.positive {
  background: rgba(0, 255, 136, 0.2);
  color: #00ff88;
}

.stock-change-badge.negative {
  background: rgba(255, 68, 68, 0.2);
  color: #ff4444;
}

.stock-price {
  font-size: 1.8rem;
  font-weight: 700;
  margin-bottom: 8px;
}

.stock-change {
  font-size: 1rem;
  font-weight: 600;
  margin-bottom: 15px;
}

.positive .stock-change {
  color: #00ff88;
}

.negative .stock-change {
  color: #ff4444;
}

.stock-details {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 8px;
}

.detail-item {
  display: flex;
  justify-content: space-between;
  font-size: 0.9rem;
}

.detail-item .label {
  color: rgba(255, 255, 255, 0.7);
}

.detail-item .value {
  font-weight: 600;
}



/* Search Section */
.search-section {
  max-width: 800px;
  margin: 0 auto;
  padding-bottom: 200px;
}

.search-summary {
  margin-left: auto;
}

.search-header {
  text-align: center;
  margin-bottom: 30px;
}

.search-header h4 {
  font-size: 1.5rem;
  font-weight: 600;
  margin-bottom: 8px;
  color: #ffffff;
}

.search-header p {
  color: rgba(255, 255, 255, 0.7);
  font-size: 0.95rem;
  margin: 0;
}

/* Search Input */
.search-input-container {
  position: relative;
  margin-bottom: 150px;
  z-index: 10;
}

.search-input-group {
  position: relative;
  display: flex;
  align-items: center;
  background: rgba(255, 255, 255, 0.1);
  border: 2px solid rgba(255, 255, 255, 0.2);
  border-radius: 15px;
  overflow: hidden;
  transition: all 0.3s ease;
}

.search-input-group:focus-within {
  border-color: #00ff88;
  box-shadow: 0 0 0 4px rgba(0, 255, 136, 0.1);
}

.search-icon {
  padding: 15px 20px;
  color: rgba(255, 255, 255, 0.6);
  font-size: 1.1rem;
}

.search-input {
  flex: 1;
  background: transparent;
  border: none;
  padding: 15px 10px;
  color: #ffffff;
  font-size: 1rem;
  outline: none;
}

.search-input::placeholder {
  color: rgba(255, 255, 255, 0.5);
}

.clear-btn {
  background: none;
  border: none;
  padding: 15px 20px;
  color: rgba(255, 255, 255, 0.6);
  cursor: pointer;
  transition: all 0.3s ease;
}

.clear-btn:hover {
  color: #ff4444;
}

/* Suggestions Dropdown */
.suggestions-dropdown {
  position: absolute;
  top: 100%;
  left: 0;
  right: 0;
  background: rgba(15, 15, 35, 0.98);
  backdrop-filter: blur(15px);
  border: 2px solid rgba(0, 255, 136, 0.3);
  border-radius: 15px;
  margin-top: 8px;
  max-height: 90vh;
  min-height: 500px;
  overflow-y: auto;
  z-index: 9999;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.8);
}

.suggestions-header {
  padding: 15px 20px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
  font-size: 0.9rem;
  color: rgba(255, 255, 255, 0.7);
  font-weight: 500;
}

.suggestion-item {
  padding: 16px 25px;
  cursor: pointer;
  transition: all 0.3s ease;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
  min-height: 65px;
  display: flex;
  flex-direction: column;
  justify-content: center;
  background: rgba(255, 255, 255, 0.02);
}

.suggestion-item:hover {
  background: rgba(0, 255, 136, 0.15);
  border-left: 4px solid #00ff88;
  transform: translateX(5px);
}

.suggestion-item:last-child {
  border-bottom: none;
}

.suggestion-content {
  display: flex;
  align-items: center;
  gap: 15px;
  margin-bottom: 5px;
}

.suggestion-symbol {
  font-weight: 700;
  font-size: 1rem;
  color: #ffffff;
  min-width: 100px;
}

.suggestion-price {
  font-weight: 600;
  color: rgba(255, 255, 255, 0.8);
  min-width: 80px;
}

.suggestion-change {
  font-weight: 600;
  font-size: 0.9rem;
}

.suggestion-change.positive {
  color: #00ff88;
}

.suggestion-change.negative {
  color: #ff4444;
}

.suggestion-sector {
  font-size: 0.8rem;
  color: rgba(255, 255, 255, 0.6);
  font-style: italic;
}



/* Selected Stock Details */
.selected-stock-section {
  margin-bottom: 40px;
}

.selected-stock-section h4 {
  font-size: 1.2rem;
  font-weight: 600;
  margin-bottom: 20px;
  color: #ffffff;
}

.selected-stock-card {
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 20px;
  padding: 30px;
  backdrop-filter: blur(10px);
}

.stock-header-section {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 25px;
  padding-bottom: 20px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.stock-main-info {
  flex: 1;
}

.stock-title {
  font-size: 2rem;
  font-weight: 700;
  margin-bottom: 10px;
  color: #ffffff;
}

.stock-sector-tag {
  background: linear-gradient(135deg, #00ff88, #00d4ff);
  color: #000000;
  padding: 6px 12px;
  border-radius: 20px;
  font-size: 0.8rem;
  font-weight: 600;
  display: inline-block;
}

.stock-price-info {
  text-align: right;
}

.current-price {
  font-size: 2.5rem;
  font-weight: 700;
  margin-bottom: 8px;
  color: #ffffff;
}

.price-change {
  font-size: 1.1rem;
  font-weight: 600;
}

.price-change.positive {
  color: #00ff88;
}

.price-change.negative {
  color: #ff4444;
}

.stock-details-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
  gap: 20px;
  margin-bottom: 25px;
}

.detail-card {
  background: rgba(255, 255, 255, 0.05);
  border-radius: 12px;
  padding: 20px;
  text-align: center;
  border: 1px solid rgba(255, 255, 255, 0.1);
}

.detail-label {
  font-size: 0.9rem;
  color: rgba(255, 255, 255, 0.7);
  margin-bottom: 8px;
  font-weight: 500;
}

.detail-value {
  font-size: 1.3rem;
  font-weight: 700;
  color: #ffffff;
}

.detail-value.high {
  color: #00ff88;
}

.detail-value.low {
  color: #ff4444;
}

.stock-actions {
  display: flex;
  gap: 15px;
  flex-wrap: wrap;
}

.action-btn {
  padding: 12px 20px;
  border: none;
  border-radius: 12px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 0.9rem;
}

.action-btn.primary {
  background: linear-gradient(135deg, #00ff88, #00d4ff);
  color: #000000;
}

.action-btn.primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 5px 15px rgba(0, 255, 136, 0.3);
}

.action-btn.secondary {
  background: rgba(255, 255, 255, 0.1);
  color: #ffffff;
  border: 1px solid rgba(255, 255, 255, 0.2);
}

.action-btn.secondary:hover {
  background: rgba(255, 255, 255, 0.2);
  transform: translateY(-2px);
}



.search-input-group {
  display: flex;
  gap: 10px;
  margin-bottom: 20px;
}

.search-input {
  flex: 1;
  background: rgba(255, 255, 255, 0.1);
  border: 1px solid rgba(255, 255, 255, 0.2);
  border-radius: 12px;
  padding: 15px 20px;
  color: #ffffff;
  font-size: 1rem;
}

.search-input::placeholder {
  color: rgba(255, 255, 255, 0.5);
}

.search-input:focus {
  outline: none;
  border-color: #00ff88;
  box-shadow: 0 0 0 3px rgba(0, 255, 136, 0.1);
}

.search-btn {
  background: linear-gradient(135deg, #00ff88, #00d4ff);
  border: none;
  border-radius: 12px;
  padding: 15px 20px;
  color: #000000;
  cursor: pointer;
  transition: all 0.3s ease;
}

.search-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 5px 15px rgba(0, 255, 136, 0.3);
}

.search-results {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.search-result-item {
  background: rgba(255, 255, 255, 0.05);
  border-radius: 12px;
  padding: 15px 20px;
  cursor: pointer;
  transition: all 0.3s ease;
  border: 1px solid rgba(255, 255, 255, 0.1);
}

.search-result-item:hover {
  background: rgba(255, 255, 255, 0.1);
  transform: translateX(5px);
}

.result-symbol {
  font-weight: 600;
  margin-bottom: 4px;
}

.result-name {
  color: rgba(255, 255, 255, 0.8);
  font-size: 0.9rem;
  margin-bottom: 4px;
}

.result-exchange {
  color: rgba(255, 255, 255, 0.6);
  font-size: 0.8rem;
}

/* Loading */
.loading-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.8);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.loading-spinner {
  text-align: center;
}

.loading-spinner i {
  font-size: 3rem;
  color: #00ff88;
  margin-bottom: 20px;
}

.loading-spinner p {
  color: #ffffff;
  font-size: 1.1rem;
}

/* Mobile Bottom Navigation */
.mobile-bottom-nav {
  position: fixed;
  bottom: 0;
  left: 0;
  right: 0;
  background: rgba(15, 15, 35, 0.95);
  backdrop-filter: blur(10px);
  border-top: 1px solid rgba(255, 255, 255, 0.1);
  display: flex;
  justify-content: space-around;
  padding: 15px 0;
  z-index: 100;
}

.nav-item {
  background: none;
  border: none;
  color: rgba(255, 255, 255, 0.7);
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 5px;
  cursor: pointer;
  transition: all 0.3s ease;
  padding: 10px;
  border-radius: 12px;
}

.nav-item:hover {
  color: #00ff88;
  background: rgba(0, 255, 136, 0.1);
}

.nav-item i {
  font-size: 1.2rem;
}

.nav-item span {
  font-size: 0.8rem;
}

/* Animations */
@keyframes pulse {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.5; }
}

/* Responsive */
@media (max-width: 768px) {
  .stock-market-screen {
    padding: 15px;
  }
  
  .header-content {
    flex-direction: column;
    gap: 20px;
    text-align: center;
  }
  
  .indices-grid {
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 15px;
  }
  
  .stocks-grid {
    grid-template-columns: 1fr;
  }
  
  .tab-buttons {
    flex-wrap: wrap;
  }
  
  .tab-btn {
    min-width: 120px;
  }
  
  .panel-header {
    flex-direction: column;
    gap: 15px;
    align-items: stretch;
  }
}

@media (max-width: 480px) {
  .page-title {
    font-size: 1.5rem;
  }
  
  .indices-grid {
    grid-template-columns: 1fr;
  }
  
  .stock-details {
    grid-template-columns: 1fr;
  }
}
</style>
