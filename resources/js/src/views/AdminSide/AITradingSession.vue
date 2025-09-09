<template>
  <div class="ai-trading-session">
    <!-- Header -->
    <div class="page-header">
      <div class="header-content">
        <button class="back-btn" @click="goBack">
          <i class="fas fa-arrow-left"></i>
          Back to AI Trading
        </button>
        <div class="user-info">
          <h1 class="page-title">🤖 AI Trading Session</h1>
          <p class="page-subtitle">
            Trading for: <strong>{{ user.name || 'Loading...' }}</strong> 
            (Available: ₹{{ user.balance?.toLocaleString() || '0' }} | Total: ₹{{ user.total_balance?.toLocaleString() || '0' }} | Blocked: ₹{{ user.blocked_amount?.toLocaleString() || '0' }})
          </p>

        </div>
      </div>
      <div class="header-actions">
        <button class="refresh-btn" @click="refreshMarketData" :disabled="loading">
          <i class="fas fa-sync-alt" :class="{ 'fa-spin': loading }"></i>
          {{ loading ? 'Loading...' : 'Refresh Data' }}
        </button>
        <button class="orders-btn" @click="viewOrders">
          <i class="fas fa-list"></i>
          View Orders
        </button>
        <button class="refresh-btn" @click="loadUserBalance(true)" :disabled="loading">
          <i class="fas fa-wallet"></i>
          Refresh Balance
        </button>
      </div>
    </div>

    <!-- Market Status -->
    <div class="market-status">
      <div class="market-status-top">
        <div class="status-indicator" :class="marketStatus.is_open ? 'open' : 'closed'">
          <div class="status-dot"></div>
          <span>{{ marketStatus.is_open ? 'Market LIVE' : 'Market CLOSED' }}</span>
          <span class="market-time">{{ marketStatus.current_time }} IST</span>
        </div>
        <div class="last-update">
          Last Update: {{ lastUpdate || 'Never' }}
        </div>
      </div>
      <div v-if="!marketStatus.is_open && marketStatus.next_open_time" class="next-open">
        Next Open: {{ formatDateTime(marketStatus.next_open_time) }}
      </div>
      <!-- Trading Status Notice -->
      <div v-if="!marketStatus.is_open" class="trading-enabled-notice">
        <i class="fas fa-check-circle"></i>
        <span>Trading is enabled 24/7 for testing purposes</span>
      </div>
    </div>



    <!-- 24/7 Trading Notice -->
    <div class="trading-enabled-notice">
      <div class="notice-content">
        <i class="fas fa-rocket"></i>
        <div class="notice-text">
          <h3>🚀 Trading Enabled 24/7</h3>
          <p class="notice-info"><strong>Trading is enabled 24/7 for testing purposes! You can trade anytime!</strong></p>
          <p class="notice-sub">Normal market hours: <strong>{{ marketStatus.market_hours?.days || 'Monday to Friday' }}, {{ marketStatus.market_hours?.open || '9:15 AM' }} - {{ marketStatus.market_hours?.close || '3:30 PM' }} {{ marketStatus.market_hours?.timezone || 'IST' }}</strong> (for reference only)</p>
        </div>
      </div>
    </div>

    <!-- Live Market Data -->
    <div class="market-data-section">
      <h2 class="section-title">
        <i class="fas fa-chart-line"></i>
        Live Market Data
        <span class="stock-count">({{ liveStocks.length }} stocks)</span>
      </h2>

      <!-- Search and Filters -->
      <div class="filters-section">
        <div class="search-input-wrapper">
          <i class="fas fa-search search-icon"></i>
          <input 
            v-model="searchQuery" 
            type="text" 
            class="filter-input" 
            placeholder="Search stocks..."
            @keyup.enter="searchStocks"
          >
          <button class="search-btn" @click="searchStocks">
            <i class="fas fa-search"></i>
          </button>
        </div>
        <select v-model="sortBy" class="filter-select">
          <option value="symbol">Sort by Symbol</option>
          <option value="price">Sort by Price</option>
          <option value="change">Sort by Change</option>
          <option value="volume">Sort by Volume</option>
        </select>
      </div>

      <!-- Stock Cards - Simple List -->
      <div class="stocks-grid">
        <div v-for="stock in filteredStocks" :key="stock.symbol" class="stock-card" @click="selectStock(stock)">
          <div class="stock-header">
            <div class="stock-info">
              <h3 class="stock-symbol">{{ stock.symbol }}</h3>
              <div class="stock-price">₹{{ stock.ltp?.toFixed(2) || '0.00' }}</div>
            </div>
            <div class="stock-change" :class="stock.change >= 0 ? 'positive' : 'negative'">
              <i :class="stock.change >= 0 ? 'fas fa-arrow-up' : 'fas fa-arrow-down'"></i>
              {{ stock.change >= 0 ? '+' : '' }}{{ stock.change?.toFixed(2) || '0.00' }}
              ({{ stock.change_percent >= 0 ? '+' : '' }}{{ stock.change_percent?.toFixed(2) || '0.00' }}%)
            </div>
          </div>

          <div class="stock-details">
            <div class="detail-row">
              <span>High:</span>
              <span>₹{{ stock.high?.toFixed(2) || '0.00' }}</span>
            </div>
            <div class="detail-row">
              <span>Low:</span>
              <span>₹{{ stock.low?.toFixed(2) || '0.00' }}</span>
            </div>
            <div class="detail-row">
              <span>Volume:</span>
              <span>{{ stock.volume?.toLocaleString() || '0' }}</span>
            </div>
          </div>

          <!-- Click to View Options -->
          <div class="click-hint">
            <i class="fas fa-mouse-pointer"></i>
            <span>Click to view Options Trading</span>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-if="filteredStocks.length === 0" class="empty-state">
        <div class="empty-icon">📊</div>
        <h3 class="empty-title">No Market Data Available</h3>
        <p class="empty-message">
          {{ marketStatus === 'CLOSED' 
            ? 'Market is currently closed. Real-time data will be available during trading hours: 9:00 AM - 3:30 PM IST (Monday to Friday)' 
            : 'Unable to fetch market data. Please try refreshing.' 
          }}
        </p>
        <button class="retry-btn" @click="refreshMarketData">
          <i class="fas fa-sync-alt"></i>
          Try Again
        </button>
      </div>
    </div>

    <!-- Stock Options Modal -->
    <div v-if="selectedStock" class="modal-overlay" @click="closeStockOptions">
      <div class="stock-options-modal" @click.stop>
        <div class="modal-header">
          <h2 class="modal-title">
            <i class="fas fa-chart-line"></i>
            Options Trading - {{ selectedStock.symbol }}
          </h2>
          <button class="close-btn" @click="closeStockOptions">
            <i class="fas fa-times"></i>
          </button>
        </div>

        <div class="modal-body">
          <!-- Stock Info -->
          <div class="stock-info-section">
            <div class="stock-price-large">₹{{ selectedStock.ltp?.toFixed(2) || '0.00' }}</div>
            <div class="stock-change-large" :class="selectedStock.change >= 0 ? 'positive' : 'negative'">
              <i :class="selectedStock.change >= 0 ? 'fas fa-arrow-up' : 'fas fa-arrow-down'"></i>
              {{ selectedStock.change >= 0 ? '+' : '' }}{{ selectedStock.change?.toFixed(2) || '0.00' }}
              ({{ selectedStock.change_percent >= 0 ? '+' : '' }}{{ selectedStock.change_percent?.toFixed(2) || '0.00' }}%)
            </div>
          </div>

          <!-- Options Trading Section -->
          <div class="options-trading-section">
            <h3 class="options-main-title">Options Trading</h3>
            
            <!-- Loading State -->
            <div v-if="loadingOptions" class="loading-options">
              <div class="loading-spinner">
                <i class="fas fa-spinner fa-spin"></i>
              </div>
              <p>Loading options data...</p>
            </div>
            
            <!-- Options Chain -->
            <div v-else class="options-chain">
              <!-- Call Options -->
              <div class="options-section">
                <h4 class="options-section-title call-title">
                  <i class="fas fa-arrow-up"></i>
                  CALL Options
                </h4>
                <div class="options-table">
                  <div class="options-header">
                    <div class="header-cell">Strike</div>
                    <div class="header-cell">Bid</div>
                    <div class="header-cell">Ask</div>
                    <div class="header-cell">Volume</div>
                    <div class="header-cell">OI</div>
                    <div class="header-cell">Action</div>
                  </div>
                  <div 
                    v-for="option in callOptions.slice(0, 10)" 
                    :key="`call-${option.strike_price}`"
                    class="option-row call-row"
                  >
                    <div class="option-cell strike">{{ option.strike_price }}</div>
                    <div class="option-cell bid">{{ option.bid }}</div>
                    <div class="option-cell ask">{{ option.ask }}</div>
                    <div class="option-cell volume">{{ option.volume }}</div>
                    <div class="option-cell oi">{{ option.open_interest }}</div>
                    <div class="option-cell actions">
                      <button 
                        class="mini-btn buy-btn" 
                        @click="openTradeModal(selectedStock, 'CALL', 'BUY', option)"
                        :title="'Buy Call - Trading enabled 24/7'"
                      >
                        <i class="fas fa-arrow-up"></i>
                        <span>BUY</span>
                      </button>
                      <button 
                        class="mini-btn sell-btn" 
                        @click="openTradeModal(selectedStock, 'CALL', 'SELL', option)"
                        :title="'Sell Call - Trading enabled 24/7'"
                      >
                        <i class="fas fa-arrow-down"></i>
                        <span>SELL</span>
                      </button>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Put Options -->
              <div class="options-section">
                <h4 class="options-section-title put-title">
                  <i class="fas fa-arrow-down"></i>
                  PUT Options
                </h4>
                <div class="options-table">
                  <div class="options-header">
                    <div class="header-cell">Strike</div>
                    <div class="header-cell">Bid</div>
                    <div class="header-cell">Ask</div>
                    <div class="header-cell">Volume</div>
                    <div class="header-cell">OI</div>
                    <div class="header-cell">Action</div>
                  </div>
                  <div 
                    v-for="option in putOptions.slice(0, 10)" 
                    :key="`put-${option.strike_price}`"
                    class="option-row put-row"
                  >
                    <div class="option-cell strike">{{ option.strike_price }}</div>
                    <div class="option-cell bid">{{ option.bid }}</div>
                    <div class="option-cell ask">{{ option.ask }}</div>
                    <div class="option-cell volume">{{ option.volume }}</div>
                    <div class="option-cell oi">{{ option.open_interest }}</div>
                    <div class="option-cell actions">
                      <button 
                        class="mini-btn buy-btn" 
                        @click="openTradeModal(selectedStock, 'PUT', 'BUY', option)"
                        :title="'Buy Put - Trading enabled 24/7'"
                      >
                        <i class="fas fa-arrow-up"></i>
                        <span>BUY</span>
                      </button>
                      <button 
                        class="mini-btn sell-btn" 
                        @click="openTradeModal(selectedStock, 'PUT', 'SELL', option)"
                        :title="'Sell Put - Trading enabled 24/7'"
                      >
                        <i class="fas fa-arrow-down"></i>
                        <span>SELL</span>
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Trade Modal -->
    <div v-if="showTradeModal" class="modal-overlay" @click="closeTradeModal">
      <div class="modal-content" @click.stop>
        <div class="modal-header">
          <h3 class="modal-title">
            <i class="fas fa-chart-line"></i>
            {{ tradeData.action }} {{ tradeData.optionType }} - {{ tradeData.stock.symbol }}
          </h3>
          <button class="close-btn" @click="closeTradeModal">
            <i class="fas fa-times"></i>
          </button>
        </div>

        <div class="modal-body">
          <div class="trade-info">
            <div class="info-row">
              <span>Stock:</span>
              <span>{{ tradeData.stock.symbol }}</span>
            </div>
            <div class="info-row">
              <span>Current Price:</span>
              <span>₹{{ tradeData.stock.ltp?.toFixed(2) }}</span>
            </div>
            <div class="info-row">
              <span>Option Type:</span>
              <span class="option-type-badge" :class="tradeData.optionType.toLowerCase()">
                {{ tradeData.optionType }}
              </span>
            </div>
            <div class="info-row">
              <span>Action:</span>
              <span class="action-badge" :class="tradeData.action.toLowerCase()">
                {{ tradeData.action }}
              </span>
            </div>
            <div class="info-row">
              <span>Strike Price:</span>
              <span>₹{{ tradeData.strikePrice }}</span>
            </div>
          </div>

          <div class="quantity-section">
            <label class="quantity-label">Lot Size & Quantity:</label>
            <div class="lot-info">
              <div class="lot-details">
                <span class="lot-label">Lot Size:</span>
                <span class="lot-value">{{ getLotSize(tradeData.stock.symbol) }} shares</span>
              </div>
              <div class="lot-details">
                <span class="lot-label">Lots:</span>
                <div class="lot-input-wrapper">
                  <button class="qty-btn" @click="decreaseLots">-</button>
                  <input 
                    v-model.number="tradeData.lots" 
                    type="number" 
                    class="lot-input"
                    min="1"
                    max="100"
                    @input="updateQuantityFromLots"
                  >
                  <button class="qty-btn" @click="increaseLots">+</button>
                </div>
              </div>
              <div class="lot-details">
                <span class="lot-label">Total Shares:</span>
                <span class="quantity-display">{{ getTotalShares() }} shares</span>
              </div>
            </div>
          </div>

          <div class="trade-summary">
            <div class="summary-row">
              <span>Option Price (LTP):</span>
              <span>₹{{ getOptionPrice() }}</span>
            </div>
            <div class="summary-row">
              <span>Lots:</span>
              <span>{{ tradeData.lots }} lots</span>
            </div>
            <div class="summary-row">
              <span>Total Shares:</span>
              <span>{{ getTotalShares() }} shares</span>
            </div>
            <div class="summary-row">
              <span>Total Amount:</span>
              <span class="total-amount">₹{{ getTotalAmount().toLocaleString() }}</span>
            </div>
            <div class="summary-row">
              <span>Available Balance:</span>
              <span>₹{{ user.balance?.toLocaleString() }}</span>
            </div>
            <div class="summary-row" :class="getTotalAmount() > user.balance ? 'insufficient' : 'sufficient'">
              <span>Status:</span>
              <span>{{ getTotalAmount() > user.balance ? 'Insufficient Balance' : 'Sufficient Balance' }}</span>
            </div>
          </div>
        </div>

        <div class="modal-footer">
          <button class="btn btn-secondary" @click="closeTradeModal">
            Cancel
          </button>
          <div class="trade-options">
            <button 
              class="btn btn-profit" 
              @click="executeTradeWithProfit"
              :disabled="getTotalAmount() > user.balance || tradeData.lots < 1"
            >
              <i class="fas fa-arrow-up"></i>
              Trade with Profit
            </button>
            <button 
              class="btn btn-loss" 
              @click="executeTradeWithLoss"
              :disabled="getTotalAmount() > user.balance || tradeData.lots < 1"
            >
              <i class="fas fa-arrow-down"></i>
              Trade with Loss
            </button>
          </div>
        </div>
      </div>
    </div>



  </div>
</template>

<script>
import axios from 'axios';
import Swal from 'sweetalert2';

export default {
  name: 'AITradingSession',
  data() {
    return {
      user: {},
      loading: false,
      liveStocks: [],
      lastUpdate: null,
      searchQuery: '',
      sortBy: 'symbol',
      showTradeModal: false,

      userOrders: [],
      exitingTrade: null,
      currentMarketPrices: {},
      marketStatus: {
        is_open: false,
        status: 'CLOSED',
        current_time: '',
        next_open_time: null,
        market_hours: {
          open: '09:15',
          close: '15:30',
          timezone: 'Asia/Kolkata',
          days: 'Monday to Friday'
        }
      },
      tradeData: {
        stock: {},
        optionType: '',
        action: '',
        strikePrice: 0,
        quantity: 1,
        lots: 1,
        option: null
      },
      selectedStock: null,
      callOptions: [],
      putOptions: [],
      loadingOptions: false,
      autoRefreshInterval: null
    }
  },
  computed: {
    filteredStocks() {
      let stocks = [...this.liveStocks];
      
      // Search filter
      if (this.searchQuery) {
        stocks = stocks.filter(stock => 
          (stock.symbol || '').toString().toLowerCase().includes(this.searchQuery.toLowerCase())
        );
      }
      
      // Sort
      stocks.sort((a, b) => {
        switch (this.sortBy) {
          case 'price':
            return (b.ltp || 0) - (a.ltp || 0);
          case 'change':
            return (b.change || 0) - (a.change || 0);
          case 'volume':
            return (b.volume || 0) - (a.volume || 0);
          default:
            // Safe symbol comparison with fallback
            const symbolA = (a.symbol || '').toString();
            const symbolB = (b.symbol || '').toString();
            return symbolA.localeCompare(symbolB);
        }
      });
      
      return stocks;
    }
  },
  mounted() {
    // Get user data from route query parameters
    this.user = {
      id: this.$route.query.userId,
      name: this.$route.query.userName,
      balance: parseFloat(this.$route.query.userBalance) || 0,
      email: this.$route.query.userEmail
    };
    console.log('User data:', this.user);
    
    // Check if user data is available
    if (!this.user.id) {
      this.showError('User data not found. Please go back and try again.');
      this.goBack();
      return;
    }
    
    this.loadMarketData();
    this.loadUserOrders();
    this.loadUserBalance(); // Fetch live balance from database
    this.loadMarketStatus(); // Load market status
    
    // Add ESC key listener for closing modals
    document.addEventListener('keydown', this.handleKeyDown);
    
    // Start auto-refresh for market data and status
    this.startAutoRefresh();
    
    // Debug: Test balance loading immediately
    console.log('About to test balance loading...');
    setTimeout(() => {
      console.log('Testing balance load after 2 seconds...');
      this.loadUserBalance();
    }, 2000);
  },
  beforeUnmount() {
    // Remove ESC key listener when component is destroyed
    document.removeEventListener('keydown', this.handleKeyDown);
    
    // Clear auto-refresh interval
    if (this.autoRefreshInterval) {
      clearInterval(this.autoRefreshInterval);
    }
  },
  methods: {
    handleKeyDown(event) {
      // Close modals on ESC key press
      if (event.key === 'Escape') {
        if (this.selectedStock) {
          this.closeStockOptions();
        }
        if (this.showTradeModal) {
          this.closeTradeModal();
        }
      }
    },
    startAutoRefresh() {
      // Auto-refresh market data and status every 10 seconds
      this.autoRefreshInterval = setInterval(() => {
        this.loadMarketStatus();
        this.loadMarketData();
        console.log('Auto-refresh: Market data and status updated');
      }, 10000); // 10 seconds for live market data (reduced frequency to prevent page refresh)
    },
    goBack() {
      this.$router.push({ name: 'ai_trading' });
    },
    async loadMarketData() {
      try {
        this.loading = true;
        const token = localStorage.getItem('access_token');
        
        const response = await axios.get('/api/truedata/live-data', {
          headers: {
            'Authorization': `Bearer ${token}`,
            'Accept': 'application/json'
          }
        });

        if (response.data.success && response.data.data) {
          console.log('Market data received:', response.data.data);
          
          // Convert object data to array format
          const dataObject = response.data.data;
          this.liveStocks = Object.values(dataObject).filter(item => 
            item && typeof item === 'object' && item.symbol && item.ltp
          );
          
          // Get market status from first stock or default
          const firstStock = this.liveStocks[0];
          this.marketStatus = {
            is_open: firstStock?.is_live || false,
            status: firstStock?.market_status || 'CLOSED'
          };
          
          this.lastUpdate = firstStock?.timestamp || new Date().toISOString();
          console.log('Live stocks loaded:', this.liveStocks.length, 'stocks');
          console.log('First stock sample:', this.liveStocks[0]);
        } else {
          console.log('No market data received:', response.data);
        }
      } catch (error) {
        console.error('Error loading market data:', error);
        this.showError('Failed to load market data');
      } finally {
        this.loading = false;
      }
    },
    async refreshMarketData() {
      await this.loadMarketData();
      // Only show toast for manual refresh, not auto-refresh
      if (!this.autoRefreshInterval) {
        this.showSuccess('Market data refreshed');
      }
    },
    searchStocks() {
      // Search functionality is handled by computed property
    },
    async selectStock(stock) {
      this.selectedStock = stock;
      this.loadingOptions = true;
      
      try {
        // Load options data for this symbol
        await this.loadOptionsData(stock.symbol);
      } catch (error) {
        console.error('Error loading options:', error);
        this.showError('Failed to load options data');
      } finally {
        this.loadingOptions = false;
      }
    },
    closeStockOptions() {
      this.selectedStock = null;
      this.callOptions = [];
      this.putOptions = [];
    },
    async loadOptionsData(symbol) {
      try {
        console.log(`🔄 Loading options data for ${symbol}`);
        console.log(`🌐 API URL: /api/truedata/options/chain/${encodeURIComponent(symbol)}`);
        
        // Check if symbol has options trading
        if (symbol === 'SENSEX') {
          this.showError('SENSEX options are not actively traded. Please try NIFTY 50 or NIFTY BANK for options trading.');
          this.closeStockOptions();
          return;
        }
        
        // Call the real options API
        const response = await axios.get(`/api/truedata/options/chain/${encodeURIComponent(symbol)}`);
        console.log('📡 Options API response:', response);
        console.log('📊 Response data:', response.data);
        console.log('✅ Response status:', response.status);
        
        if (response.data.success && response.data.data) {
          console.log('🎯 Processing real API data...');
          this.processOptionsData(response.data.data);
        } else {
          console.log('⚠️ API response not successful');
          this.showError('No options data available for this symbol. Please try another stock.');
          this.closeStockOptions();
          return;
        }
      } catch (error) {
        console.error('❌ Error loading options data:', error);
        this.showError('Failed to load options data. Please check your connection and try again.');
        this.closeStockOptions();
      }
    },
    processOptionsData(data) {
      console.log('🔄 Processing options data:', data);
      
      // Initialize arrays
      this.callOptions = [];
      this.putOptions = [];
      
      // Check if we have data array (TrueData API format)
      if (data && Array.isArray(data) && data.length > 0) {
        console.log('📊 Processing TrueData API format with', data.length, 'records');
        
        // Get current stock price
        const currentPrice = this.selectedStock?.ltp || this.selectedStock?.last || 24000;
        console.log('💰 Current stock price:', currentPrice);
        
        // Process all real options data
        const allOptions = data.map(option => ({
          symbol: option.symbol_id || option.symbol,
          strike_price: option.strike_price || 0,
          ltp: option.ltp || 0,
          bid: option.bid || 0,
          ask: option.ask || 0,
          volume: option.volume || 0,
          open_interest: option.oi || option.open_interest || 0,
          option_type: option.option_type,
          implied_volatility: option.implied_volatility || 0
        }));
        
        // Get all available strikes and sort them
        const allStrikes = [...new Set(allOptions.map(option => option.strike_price))].sort((a, b) => a - b);
        console.log('📊 All available strikes:', allStrikes);
        
        // Find strikes around current price (within reasonable range)
        const currentPriceRounded = Math.round(currentPrice / 100) * 100; // Round to nearest 100
        const relevantStrikes = allStrikes.filter(strike => 
          strike >= currentPriceRounded - 3000 && strike <= currentPriceRounded + 3000
        );
        console.log('🎯 Relevant strikes around current price:', relevantStrikes);
        
        // Get CALL options for relevant strikes
        this.callOptions = allOptions
          .filter(option => (option.option_type === 'CE' || option.option_type === 'CALL') && relevantStrikes.includes(option.strike_price))
          .sort((a, b) => a.strike_price - b.strike_price);
          
        // Get PUT options for relevant strikes
        this.putOptions = allOptions
          .filter(option => (option.option_type === 'PE' || option.option_type === 'PUT') && relevantStrikes.includes(option.strike_price))
          .sort((a, b) => a.strike_price - b.strike_price);
          
        // If still no options, use all available options
        if (this.callOptions.length === 0 && this.putOptions.length === 0) {
          console.log('⚠️ No options found for relevant strikes, using all available options');
          this.callOptions = allOptions
            .filter(option => option.option_type === 'CE' || option.option_type === 'CALL')
            .sort((a, b) => a.strike_price - b.strike_price);
          this.putOptions = allOptions
            .filter(option => option.option_type === 'PE' || option.option_type === 'PUT')
            .sort((a, b) => a.strike_price - b.strike_price);
        }
          
        console.log('✅ Processed', this.callOptions.length, 'CALL options and', this.putOptions.length, 'PUT options');
        } else {
          console.log('⚠️ No data array found');
          this.showError('No options data available for this symbol. Please try another stock.');
          this.closeStockOptions();
        }
    },
    
    generateAngelOneStrikes(currentPrice) {
      const strikes = [];
      const step = 50; // Angel One style: 50 points for NIFTY 50
      
      // Round current price to nearest 50
      const roundedPrice = Math.round(currentPrice / step) * step;
      
      // Generate 10 strikes around current price (5 above, 5 below) - Angel One style
      for (let i = -5; i <= 5; i++) {
        const strike = roundedPrice + (i * step);
        if (strike > 0) {
          strikes.push(strike);
        }
      }
      
      return strikes.sort((a, b) => a - b);
    },
    
    generateProperStrikes(currentPrice, symbol) {
      const strikes = [];
      let step = 500; // Default step - API uses 500 point intervals
      
      // Set step based on symbol type
      if (symbol === 'NIFTY 50' || symbol === 'NIFTY BANK' || symbol === 'NIFTY IT' || symbol === 'FINNIFTY') {
        step = 500; // 500 points for indices (as per API data)
      } else if (symbol === 'SENSEX' || symbol === 'BANKEX') {
        step = 1000; // 1000 points for SENSEX
      } else {
        step = 100; // 100 points for individual stocks
      }
      
      // Round current price to nearest step
      const roundedPrice = Math.round(currentPrice / step) * step;
      
      // Generate 10 strikes around current price (5 above, 5 below)
      for (let i = -5; i <= 5; i++) {
        const strike = roundedPrice + (i * step);
        if (strike > 0) {
          strikes.push(strike);
        }
      }
      
      return strikes.sort((a, b) => a - b);
    },
    openTradeModal(stock, optionType, action, option = null) {
      this.tradeData = {
        stock: stock,
        optionType: optionType,
        action: action,
        strikePrice: option ? option.strike_price : (optionType === 'CALL' ? (stock.ltp * 1.02) : (stock.ltp * 0.98)),
        quantity: 1, // In Angel One style: quantity = lots
        lots: 1,
        option: option
      };
      this.showTradeModal = true;
    },
    closeTradeModal() {
      this.showTradeModal = false;
      this.tradeData = {
        stock: {},
        optionType: '',
        action: '',
        strikePrice: 0,
        quantity: 1,
        lots: 1,
        option: null
      };
    },
    increaseQuantity() {
      if (this.tradeData.quantity < 1000) {
        this.tradeData.quantity++;
      }
    },
    decreaseQuantity() {
      if (this.tradeData.quantity > 1) {
        this.tradeData.quantity--;
      }
    },
    // Lot size methods
    getLotSize(symbol) {
      // Standard lot sizes for different stocks (like Angel One)
      const lotSizes = {
        'NIFTY 50': 75,
        'NIFTY BANK': 25,
        'NIFTY IT': 25,
        'SENSEX': 10,
        'FINNIFTY': 40,
        'NIFTY MIDCAP': 50,
        'BANKEX': 15,
        'AXISBANK': 1200,
        'RELIANCE': 250,
        'TCS': 125,
        'HDFC': 200,
        'INFY': 200,
        'HDFC BANK': 500,
        'ICICI BANK': 1000,
        'SBIN': 1500,
        'BHARTIARTL': 400,
        'ITC': 800,
        'KOTAKBANK': 200,
        'LT': 200,
        'MARUTI': 50
      };
      return lotSizes[symbol] || 100; // Default lot size
    },
    increaseLots() {
      if (this.tradeData.lots < 100) {
        this.tradeData.lots++;
        this.updateQuantityFromLots();
      }
    },
    decreaseLots() {
      if (this.tradeData.lots > 1) {
        this.tradeData.lots--;
        this.updateQuantityFromLots();
      }
    },
    updateQuantityFromLots() {
      // In Angel One style: quantity = lots (not total shares)
      this.tradeData.quantity = this.tradeData.lots;
    },
    getOptionPrice() {
      if (this.tradeData.option) {
        return this.tradeData.option.ltp || 0;
      }
      // Fallback calculation
      const currentPrice = this.tradeData.stock.ltp || 1000;
      const strike = this.tradeData.strikePrice;
      if (this.tradeData.optionType === 'CALL') {
        return Math.max(0, currentPrice - strike) + (currentPrice * 0.01);
      } else {
        return Math.max(0, strike - currentPrice) + (currentPrice * 0.01);
      }
    },
    getTotalShares() {
      const lotSize = this.getLotSize(this.tradeData.stock.symbol);
      return this.tradeData.lots * lotSize;
    },
    getTotalAmount() {
      const optionPrice = this.getOptionPrice();
      const totalShares = this.getTotalShares();
      return optionPrice * totalShares;
    },
    async executeTrade() {
      try {
        console.log('Executing trade...', this.tradeData);
        
        const token = localStorage.getItem('access_token');
        if (!token) {
          this.showError('Please login to execute trades');
          return;
        }
        
        const tradePayload = {
          user_id: this.user.id,
          stock_symbol: this.tradeData.stock.symbol,
          option_type: this.tradeData.optionType,
          action: this.tradeData.action,
          strike_price: this.tradeData.strikePrice,
          quantity: this.tradeData.lots, // Send lots, not total shares
          total_amount: this.getTotalAmount(),
          lot_size: this.getLotSize(this.tradeData.stock.symbol),
          total_shares: this.getTotalShares(),
          option_price: this.getOptionPrice()
        };
        
        console.log('Trade payload:', tradePayload);

        const response = await axios.post('/api/ai-trading/execute-trade', tradePayload, {
          headers: {
            'Authorization': `Bearer ${token}`,
            'Accept': 'application/json',
            'Content-Type': 'application/json'
          }
        });

        console.log('Trade response:', response.data);
        
        if (response.data.success) {
          this.showSuccess(`Trade executed successfully! Order #${response.data.order_id}`);
          this.closeTradeModal();
          this.loadUserOrders();
          // Refresh live user balance from database
          this.loadUserBalance();
        } else {
          this.showError(response.data.message || 'Failed to execute trade');
        }
      } catch (error) {
        console.error('Error executing trade:', error);
        console.error('Error details:', error.response?.data);
        this.showError(`Trade execution failed: ${error.response?.data?.message || error.message}`);
      }
    },
    async executeTradeWithProfit() {
      try {
        console.log('Executing trade with profit...', this.tradeData);
        
        const token = localStorage.getItem('access_token');
        if (!token) {
          this.showError('Please login to execute trades');
          return;
        }
        
        // Calculate profit based on market price movement
        const currentPrice = this.tradeData.stock.ltp || this.tradeData.stock.last || 1000;
        const optionPrice = this.getOptionPrice();
        const profitPercentage = 0.15; // 15% profit
        const profitAmount = optionPrice * profitPercentage;
        const exitPrice = optionPrice + profitAmount;
        
        const tradePayload = {
          user_id: this.user.id,
          stock_symbol: this.tradeData.stock.symbol,
          option_type: this.tradeData.optionType,
          action: this.tradeData.action,
          strike_price: this.tradeData.strikePrice,
          quantity: this.tradeData.lots,
          total_amount: this.getTotalAmount(),
          lot_size: this.getLotSize(this.tradeData.stock.symbol),
          total_shares: this.getTotalShares(),
          option_price: this.getOptionPrice(),
          trade_type: 'PROFIT',
          exit_price: exitPrice,
          profit_amount: profitAmount,
          current_market_price: currentPrice
        };
        
        console.log('Profit trade payload:', tradePayload);

        const response = await axios.post('/api/ai-trading/execute-trade', tradePayload, {
          headers: {
            'Authorization': `Bearer ${token}`,
            'Accept': 'application/json',
            'Content-Type': 'application/json'
          }
        });

        console.log('Trade response:', response.data);
        
        if (response.data.success) {
          this.showSuccess(`Trade executed with profit! Order #${response.data.order_id} - Profit: ₹${profitAmount.toFixed(2)}`);
          this.closeTradeModal();
          this.loadUserOrders();
          this.loadUserBalance();
        } else {
          this.showError(response.data.message || 'Failed to execute trade');
        }
      } catch (error) {
        console.error('Error executing trade with profit:', error);
        console.error('Error details:', error.response?.data);
        this.showError(`Trade execution failed: ${error.response?.data?.message || error.message}`);
      }
    },
    async executeTradeWithLoss() {
      try {
        console.log('Executing trade with loss...', this.tradeData);
        
        const token = localStorage.getItem('access_token');
        if (!token) {
          this.showError('Please login to execute trades');
          return;
        }
        
        // Calculate loss based on market price movement
        const currentPrice = this.tradeData.stock.ltp || this.tradeData.stock.last || 1000;
        const optionPrice = this.getOptionPrice();
        const lossPercentage = 0.10; // 10% loss
        const lossAmount = optionPrice * lossPercentage;
        const exitPrice = optionPrice - lossAmount;
        
        const tradePayload = {
          user_id: this.user.id,
          stock_symbol: this.tradeData.stock.symbol,
          option_type: this.tradeData.optionType,
          action: this.tradeData.action,
          strike_price: this.tradeData.strikePrice,
          quantity: this.tradeData.lots,
          total_amount: this.getTotalAmount(),
          lot_size: this.getLotSize(this.tradeData.stock.symbol),
          total_shares: this.getTotalShares(),
          option_price: this.getOptionPrice(),
          trade_type: 'LOSS',
          exit_price: exitPrice,
          loss_amount: lossAmount,
          current_market_price: currentPrice
        };
        
        console.log('Loss trade payload:', tradePayload);

        const response = await axios.post('/api/ai-trading/execute-trade', tradePayload, {
          headers: {
            'Authorization': `Bearer ${token}`,
            'Accept': 'application/json',
            'Content-Type': 'application/json'
          }
        });

        console.log('Trade response:', response.data);
        
        if (response.data.success) {
          this.showSuccess(`Trade executed with loss! Order #${response.data.order_id} - Loss: ₹${lossAmount.toFixed(2)}`);
          this.closeTradeModal();
          this.loadUserOrders();
          this.loadUserBalance();
        } else {
          this.showError(response.data.message || 'Failed to execute trade');
        }
      } catch (error) {
        console.error('Error executing trade with loss:', error);
        console.error('Error details:', error.response?.data);
        this.showError(`Trade execution failed: ${error.response?.data?.message || error.message}`);
      }
    },
    async loadUserOrders() {
      try {
        const token = localStorage.getItem('access_token');
        
        const response = await axios.get(`/api/ai-trading/user-orders/${this.user.id}`, {
          headers: {
            'Authorization': `Bearer ${token}`,
            'Accept': 'application/json'
          }
        });

        if (response.data.success) {
          this.userOrders = response.data.orders || [];
        }
      } catch (error) {
        console.error('Error loading user orders:', error);
      }
    },
    async loadUserBalance(showToast = false) {
      try {
        console.log('Loading user balance for user ID:', this.user.id);
        const token = localStorage.getItem('access_token');
        console.log('Token exists:', !!token);
        
        if (!token) {
          console.error('No access token found');
          this.showError('No access token found. Please login again.');
          return;
        }
        
        const response = await axios.get(`/api/ai-trading/user-balance/${this.user.id}`, {
          headers: {
            'Authorization': `Bearer ${token}`,
            'Accept': 'application/json'
          }
        });

        console.log('Balance API response:', response.data);

        if (response.data.success) {
          // Update user balance with live data from wallet transactions
          const availableBalance = parseFloat(response.data.balance) || 0;
          const totalBalance = parseFloat(response.data.total_balance) || 0;
          const blockedAmount = parseFloat(response.data.blocked_amount) || 0;
          
          console.log('Live user balance updated:', {
            available: availableBalance,
            total: totalBalance,
            blocked: blockedAmount
          });
          
          // Update user balance (Vue 3 reactive approach)
          this.user = { 
            ...this.user, 
            balance: availableBalance,
            total_balance: totalBalance,
            blocked_amount: blockedAmount
          };
          
          console.log('User object after update:', this.user);
          
          // Only show toast when manually refreshing balance
          if (showToast) {
            this.showSuccess(`Balance updated: ${response.data.formatted_balance} (Available) | Total: ${response.data.formatted_total_balance} | Blocked: ${response.data.formatted_blocked_amount}`);
          }
        } else {
          console.error('API returned success: false', response.data);
        }
      } catch (error) {
        console.error('Error loading user balance:', error);
        console.error('Error details:', error.response?.data);
        this.showError('Failed to load user balance: ' + (error.response?.data?.message || error.message));
        // Keep the balance from URL params as fallback
      }
    },
    async loadMarketStatus() {
      try {
        const token = localStorage.getItem('access_token');
        const response = await axios.get('/api/ai-trading/market-status', {
          headers: {
            'Authorization': `Bearer ${token}`,
            'Accept': 'application/json'
          }
        });
        if (response.data.success) {
          this.marketStatus = response.data.data;
          console.log('Market status updated:', this.marketStatus);
        }
      } catch (error) {
        console.error('Error loading market status:', error);
        // Set default market status if API fails
        this.marketStatus = {
          is_open: false,
          status: 'CLOSED',
          current_time: new Date().toLocaleTimeString('en-IN'),
          next_open_time: null,
          market_hours: {
            open: '09:15',
            close: '15:30',
            timezone: 'Asia/Kolkata',
            days: 'Monday to Friday'
          }
        };
        console.log('Using default market status due to API error');
      }
    },
    viewOrders() {
      console.log('View Orders clicked - navigating to orders page');
      
      // Force navigation to orders page (no popup)
      window.location.href = `/admin/ai-trading-orders?userId=${this.user.id}&userName=${encodeURIComponent(this.user.name)}&userBalance=${this.user.balance}&userEmail=${encodeURIComponent(this.user.email)}`;
    },


    formatDateTime(dateString) {
      return new Date(dateString).toLocaleString('en-IN');
    },
    showSuccess(message) {
      // Use SweetAlert2 for success notifications
      if (typeof Swal !== 'undefined') {
        Swal.fire({
          title: 'Success!',
          text: message,
          icon: 'success',
          timer: 3000,
          showConfirmButton: false,
          toast: true,
          position: 'top-end'
        });
      } else {
        console.log('Success:', message);
        alert('Success: ' + message);
      }
    },
    getStatusIcon(status) {
      switch (status) {
        case 'COMPLETED': return 'fa-check-circle';
        case 'PENDING': return 'fa-clock';
        case 'CANCELLED': return 'fa-times-circle';
        case 'CLOSED': return 'fa-lock';
        default: return 'fa-question-circle';
      }
    },
    getOptionIcon(optionType) {
      switch (optionType) {
        case 'CALL': return 'fa-arrow-up';
        case 'PUT': return 'fa-arrow-down';
        default: return 'fa-question';
      }
    },
    getActionIcon(action) {
      switch (action) {
        case 'BUY': return 'fa-shopping-cart';
        case 'SELL': return 'fa-money-bill-wave';
        default: return 'fa-exchange-alt';
      }
    },
    getCurrentPrice(symbol) {
      // Get current price from live stocks data
      const stock = this.liveStocks.find(s => s.symbol === symbol);
      return stock ? stock.ltp.toFixed(2) : 'N/A';
    },
    calculateUnrealizedPnL(order) {
      const currentPrice = parseFloat(this.getCurrentPrice(order.stock_symbol));
      if (isNaN(currentPrice)) return 0;

      const strikePrice = parseFloat(order.strike_price);
      const quantity = parseInt(order.quantity);
      const optionType = order.option_type;
      const action = order.action;

      let pnl = 0;

      // For CALL options
      if (optionType === 'CALL') {
        if (action === 'BUY') {
          // Bought CALL: Profit if current price > strike price
          const intrinsicValue = Math.max(0, currentPrice - strikePrice);
          pnl = (intrinsicValue - strikePrice) * quantity;
        } else {
          // Sold CALL: Profit if current price < strike price
          const intrinsicValue = Math.max(0, currentPrice - strikePrice);
          pnl = (strikePrice - intrinsicValue) * quantity;
        }
      }
      // For PUT options
      else {
        if (action === 'BUY') {
          // Bought PUT: Profit if current price < strike price
          const intrinsicValue = Math.max(0, strikePrice - currentPrice);
          pnl = (intrinsicValue - strikePrice) * quantity;
        } else {
          // Sold PUT: Profit if current price > strike price
          const intrinsicValue = Math.max(0, strikePrice - currentPrice);
          pnl = (strikePrice - intrinsicValue) * quantity;
        }
      }

      return Math.round(pnl * 100) / 100; // Round to 2 decimal places
    },
    getPnLClass(pnl) {
      if (pnl > 0) return 'profit';
      if (pnl < 0) return 'loss';
      return 'neutral';
    },
    getPnLText(pnl) {
      if (pnl > 0) return `+₹${pnl.toLocaleString()}`;
      if (pnl < 0) return `-₹${Math.abs(pnl).toLocaleString()}`;
      return '₹0';
    },
    async exitTrade(orderId) {
      if (!confirm('Are you sure you want to exit this trade? This action cannot be undone.')) {
        return;
      }

      this.exitingTrade = orderId;

      try {
        const token = localStorage.getItem('access_token');
        
        const response = await axios.post(`/api/ai-trading/orders/${orderId}/exit`, {}, {
          headers: {
            'Authorization': `Bearer ${token}`,
            'Accept': 'application/json',
            'Content-Type': 'application/json'
          }
        });

        if (response.data.success) {
          const data = response.data.data;
          this.showSuccess(`Trade exited successfully! ${data.pnl_type === 'profit' ? 'Profit' : 'Loss'}: ₹${Math.abs(data.pnl).toLocaleString()}`);
          
          // Refresh user orders and balance
          this.loadUserOrders();
          this.loadUserBalance();
        } else {
          this.showError(response.data.message || 'Failed to exit trade');
        }
      } catch (error) {
        console.error('Error exiting trade:', error);
        this.showError('Failed to exit trade');
      } finally {
        this.exitingTrade = null;
      }
    },
    showError(message) {
      // Use SweetAlert2 for error notifications
      if (typeof Swal !== 'undefined') {
        Swal.fire({
          title: 'Error!',
          text: message,
          icon: 'error',
          timer: 4000,
          showConfirmButton: false,
          toast: true,
          position: 'top-end'
        });
      } else {
        console.error('Error:', message);
        alert('Error: ' + message);
      }
    },
    formatDateTime(dateTimeString) {
      if (!dateTimeString) return '';
      const date = new Date(dateTimeString);
      return date.toLocaleString('en-IN', {
        timeZone: 'Asia/Kolkata',
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      });
    }
  }
}
</script>

<style scoped>
/* AI Trading Session Styles */
.ai-trading-session {
  background-color: #0d0d1a;
  color: white;
  min-height: 100vh;
  padding: 20px;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
  text-rendering: optimizeLegibility;
  -webkit-overflow-scrolling: touch;
  overflow-x: hidden;
}

/* Page Header */
.page-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 24px;
  padding: 20px;
  background: linear-gradient(145deg, #101022, #0d0d1a);
  border: 1px solid rgba(0, 255, 136, 0.2);
  border-radius: 16px;
  gap: 16px;
}

.header-content {
  display: flex;
  align-items: center;
  gap: 20px;
  flex: 1;
  min-width: 0;
}

.back-btn {
  background: rgba(255, 255, 255, 0.1);
  border: 1px solid rgba(255, 255, 255, 0.2);
  color: white;
  padding: 10px 16px;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.3s ease;
  min-height: 44px;
  touch-action: manipulation;
  display: flex;
  align-items: center;
  gap: 8px;
}

.back-btn:hover {
  background: rgba(255, 255, 255, 0.2);
}

.back-btn:focus {
  outline: 2px solid #00ff88;
  outline-offset: 2px;
}

.page-title {
  font-size: 28px;
  font-weight: bold;
  color: #00ff88;
  margin: 0;
}

.page-subtitle {
  color: #a0a0a0;
  margin: 4px 0 0 0;
  word-wrap: break-word;
  overflow-wrap: break-word;
  hyphens: auto;
  line-height: 1.4;
}

.header-actions {
  display: flex;
  gap: 12px;
  flex-wrap: wrap;
  align-items: center;
  min-width: 0;
}

.refresh-btn, .orders-btn {
  background: linear-gradient(135deg, #00ff88, #00d4ff);
  color: #000000;
  border: none;
  padding: 12px 20px;
  border-radius: 10px;
  cursor: pointer;
  font-weight: 600;
  transition: all 0.3s ease;
}

.refresh-btn:hover, .orders-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(0, 255, 136, 0.3);
}

.refresh-btn:focus, .orders-btn:focus {
  outline: 2px solid #00ff88;
  outline-offset: 2px;
}

/* Market Status */
.market-status {
  display: flex;
  flex-direction: column;
  gap: 12px;
  margin-bottom: 24px;
  padding: 16px 20px;
  background: rgba(255, 255, 255, 0.05);
  border-radius: 12px;
  border: 1px solid rgba(255, 255, 255, 0.1);
}

.market-status-top {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.trading-disabled-notice {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 8px 12px;
  background: rgba(255, 68, 68, 0.1);
  border: 1px solid rgba(255, 68, 68, 0.2);
  border-radius: 8px;
  color: #ff6b6b;
  font-size: 14px;
  font-weight: 500;
}

.trading-disabled-notice i {
  font-size: 16px;
}

.trading-enabled-notice {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 8px 12px;
  background: rgba(34, 197, 94, 0.1);
  border: 1px solid rgba(34, 197, 94, 0.2);
  border-radius: 8px;
  color: #22c55e;
  font-size: 14px;
  font-weight: 500;
}

.trading-enabled-notice i {
  font-size: 16px;
}

.status-indicator {
  display: flex;
  align-items: center;
  gap: 8px;
  font-weight: 600;
}

.status-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: #ff4444;
}

.status-indicator.open .status-dot {
  background: #00ff88;
}

.last-update {
  color: #a0a0a0;
  font-size: 14px;
}

/* Market Data Section */
.market-data-section {
  background: rgba(255, 255, 255, 0.05);
  backdrop-filter: blur(10px);
  border-radius: 16px;
  border: 1px solid rgba(255, 255, 255, 0.1);
  padding: 24px;
  margin-bottom: 24px;
}

.section-title {
  display: flex;
  align-items: center;
  gap: 12px;
  font-size: 24px;
  font-weight: bold;
  color: #00ff88;
  margin-bottom: 20px;
}

.stock-count {
  color: #a0a0a0;
  font-size: 16px;
  font-weight: normal;
}

/* Filters */
.filters-section {
  display: flex;
  gap: 16px;
  margin-bottom: 24px;
  align-items: center;
}

.search-input-wrapper {
  display: flex;
  align-items: center;
  background: rgba(255, 255, 255, 0.1);
  border: 1px solid rgba(255, 255, 255, 0.2);
  border-radius: 10px;
  padding: 0 16px;
  flex: 1;
  gap: 8px;
}

.search-icon {
  color: #a0a0a0;
  z-index: 0;
  pointer-events: none;
}

.filter-input {
  background: transparent !important;
  border: none !important;
  color: #ffffff !important;
  padding: 12px 0;
  flex: 1;
  outline: none;
  position: relative;
  z-index: 1;
  cursor: text;
}

.filter-input::placeholder {
  color: #a0a0a0 !important;
}

.filter-input:focus {
  outline: none;
  border-color: #00ff88 !important;
  box-shadow: 0 0 0 2px rgba(0, 255, 136, 0.2);
}

.search-btn {
  background: linear-gradient(135deg, #00ff88, #00d4ff);
  color: #000000;
  border: none;
  padding: 8px 12px;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.3s ease;
}

.search-btn:hover {
  transform: translateY(-1px);
}

.search-btn:focus {
  outline: 2px solid #00ff88;
  outline-offset: 2px;
}

.filter-select {
  background: rgba(255, 255, 255, 0.1) !important;
  border: 1px solid rgba(255, 255, 255, 0.2) !important;
  color: #ffffff !important;
  padding: 12px 16px;
  border-radius: 10px;
  outline: none;
  cursor: pointer;
}

.filter-select option {
  background: #1a1a2e !important;
  color: #ffffff !important;
}

.filter-select:focus {
  outline: 2px solid #00ff88;
  outline-offset: 2px;
}

/* Stocks Grid */
.stocks-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
  gap: 20px;
  width: 100%;
}

.stock-card {
  background: rgba(255, 255, 255, 0.05);
  backdrop-filter: blur(10px);
  border-radius: 16px;
  border: 1px solid rgba(255, 255, 255, 0.1);
  padding: 20px;
  transition: all 0.3s ease;
  cursor: pointer;
  min-width: 0;
  width: 100%;
  box-sizing: border-box;
}

.stock-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 12px 40px rgba(0, 255, 136, 0.2);
  border-color: rgba(0, 255, 136, 0.3);
}

.stock-card:focus {
  outline: 2px solid #00ff88;
  outline-offset: 2px;
  transform: translateY(-2px);
}

.stock-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 16px;
  gap: 12px;
  flex-wrap: wrap;
  min-height: 60px;
}

.stock-info {
  flex: 1;
  min-width: 0;
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.stock-info h3 {
  font-size: 20px;
  font-weight: bold;
  color: #00ff88;
  margin: 0;
  word-wrap: break-word;
  overflow-wrap: break-word;
  line-height: 1.2;
}

.stock-price {
  font-size: 18px;
  font-weight: 600;
  color: white;
  white-space: nowrap;
  line-height: 1.2;
}

.stock-change {
  display: flex;
  align-items: center;
  gap: 4px;
  font-weight: 600;
  padding: 4px 8px;
  border-radius: 6px;
  white-space: nowrap;
  flex-shrink: 0;
  min-width: 120px;
  justify-content: center;
  text-align: center;
}

.stock-change.positive {
  color: #00ff88;
  background: rgba(0, 255, 136, 0.1);
}

.stock-change.negative {
  color: #ff4444;
  background: rgba(255, 68, 68, 0.1);
}

.stock-details {
  margin-bottom: 20px;
}

.detail-row {
  display: flex;
  justify-content: space-between;
  margin-bottom: 8px;
  color: #a0a0a0;
}

.click-hint {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  margin-top: 16px;
  padding: 12px;
  background: rgba(0, 255, 136, 0.1);
  border: 1px solid rgba(0, 255, 136, 0.3);
  border-radius: 8px;
  color: #00ff88;
  font-size: 0.9rem;
  font-weight: 500;
}

.click-hint i {
  font-size: 1rem;
}

/* Stock Options Modal */
.stock-options-modal {
  background: linear-gradient(145deg, #1a1a2e, #16213e);
  border: 2px solid rgba(0, 255, 136, 0.3);
  border-radius: 20px;
  max-width: 800px;
  width: 90%;
  max-height: 90vh;
  overflow-y: auto;
  position: relative;
  animation: modalSlideIn 0.3s ease-out;
  margin: 20px auto;
}

@keyframes modalSlideIn {
  from {
    opacity: 0;
    transform: scale(0.9) translateY(-20px);
  }
  to {
    opacity: 1;
    transform: scale(1) translateY(0);
  }
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px 30px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.modal-title {
  font-size: 1.5rem;
  color: #00ff88;
  margin: 0;
  display: flex;
  align-items: center;
  gap: 10px;
}

.close-btn {
  background: rgba(255, 0, 0, 0.2);
  border: 1px solid rgba(255, 0, 0, 0.4);
  color: #ff4444;
  padding: 8px 12px;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.3s ease;
  font-size: 1rem;
}

.close-btn:hover {
  background: rgba(255, 0, 0, 0.3);
  transform: scale(1.05);
}

.modal-body {
  padding: 30px;
}

.stock-info-section {
  text-align: center;
  margin-bottom: 30px;
  padding: 20px;
  background: rgba(255, 255, 255, 0.05);
  border-radius: 12px;
}

.stock-price-large {
  font-size: 2.5rem;
  font-weight: bold;
  color: #00ff88;
  margin-bottom: 10px;
}

.stock-change-large {
  font-size: 1.2rem;
  font-weight: 600;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
}

.stock-change-large.positive {
  color: #00ff88;
}

.stock-change-large.negative {
  color: #ff4444;
}

.options-trading-section {
  margin-top: 20px;
}

.options-main-title {
  font-size: 1.5rem;
  color: #00ff88;
  margin-bottom: 20px;
  text-align: center;
}

.options-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 20px;
}

.option-card {
  background: rgba(255, 255, 255, 0.05);
  border-radius: 16px;
  padding: 20px;
  border: 1px solid rgba(255, 255, 255, 0.1);
  transition: all 0.3s ease;
}

.option-card.call-option {
  border-color: rgba(0, 255, 136, 0.3);
  background: rgba(0, 255, 136, 0.05);
}

.option-card.put-option {
  border-color: rgba(255, 68, 68, 0.3);
  background: rgba(255, 68, 68, 0.05);
}

.option-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
}

.option-header {
  display: flex;
  align-items: center;
  gap: 15px;
  margin-bottom: 20px;
}

.option-icon {
  font-size: 2rem;
}

.option-info {
  flex: 1;
}

.option-title {
  font-size: 1.2rem;
  font-weight: bold;
  margin: 0 0 5px 0;
  color: white;
}

.option-strike {
  color: #a0a0a0;
  font-size: 0.9rem;
}

.option-actions {
  display: flex;
  gap: 12px;
}

/* Loading Options */
.loading-options {
  text-align: center;
  padding: 40px 20px;
  color: #a0a0a0;
}

.loading-spinner {
  font-size: 2rem;
  margin-bottom: 16px;
  color: #00ff88;
}

/* Options Chain */
.options-chain {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.options-section {
  background: rgba(255, 255, 255, 0.05);
  border-radius: 12px;
  padding: 20px;
  border: 1px solid rgba(255, 255, 255, 0.1);
}

.options-section-title {
  font-size: 1.2rem;
  font-weight: bold;
  margin-bottom: 16px;
  display: flex;
  align-items: center;
  gap: 8px;
}

.call-title {
  color: #00ff88;
}

.put-title {
  color: #ff4444;
}

.options-table {
  display: flex;
  flex-direction: column;
  gap: 8px;
  overflow-x: auto;
  -webkit-overflow-scrolling: touch;
}

.options-header {
  display: grid;
  grid-template-columns: 1fr 1fr 1fr 1fr 1fr 1fr;
  gap: 12px;
  padding: 12px;
  background: rgba(255, 255, 255, 0.1);
  border-radius: 8px;
  font-weight: bold;
  font-size: 0.9rem;
  min-width: 600px;
}

.header-cell {
  text-align: center;
  color: #a0a0a0;
}

.option-row {
  display: grid;
  grid-template-columns: 1fr 1fr 1fr 1fr 1fr 1fr;
  gap: 12px;
  padding: 12px;
  border-radius: 8px;
  transition: all 0.3s ease;
  align-items: center;
  min-width: 600px;
}

.call-row {
  background: rgba(0, 255, 136, 0.05);
  border: 1px solid rgba(0, 255, 136, 0.2);
}

.put-row {
  background: rgba(255, 68, 68, 0.05);
  border: 1px solid rgba(255, 68, 68, 0.2);
}

.option-row:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
}

.option-cell {
  text-align: center;
  font-size: 0.9rem;
}

.option-cell.strike {
  font-weight: bold;
  color: white;
}

.option-cell.bid {
  color: #00ff88;
}

.option-cell.ask {
  color: #ff4444;
}

.option-cell.volume,
.option-cell.oi {
  color: #a0a0a0;
}

.option-cell.actions {
  display: flex;
  gap: 8px;
  justify-content: center;
}

.mini-btn {
  padding: 6px 10px;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-size: 0.8rem;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 4px;
  min-width: 50px;
  font-weight: 600;
  min-height: 32px;
  touch-action: manipulation;
}

.mini-btn i {
  font-size: 0.7rem;
}

.mini-btn span {
  font-size: 0.7rem;
  font-weight: 600;
}

.mini-btn.buy-btn {
  background: rgba(0, 255, 136, 0.2);
  color: #00ff88;
  border: 1px solid rgba(0, 255, 136, 0.4);
}

.mini-btn.buy-btn:hover {
  background: rgba(0, 255, 136, 0.3);
  transform: scale(1.05);
}

.mini-btn.sell-btn {
  background: rgba(255, 68, 68, 0.2);
  color: #ff4444;
  border: 1px solid rgba(255, 68, 68, 0.4);
}

.mini-btn.sell-btn:hover {
  background: rgba(255, 68, 68, 0.3);
  transform: scale(1.05);
}

.mini-btn:disabled {
  opacity: 0.4;
  cursor: not-allowed;
  transform: none;
  background: #444 !important;
  color: #888 !important;
  border: 1px solid #666 !important;
  box-shadow: none !important;
}

.mini-btn:disabled:hover {
  transform: none !important;
  box-shadow: none !important;
  background: #444 !important;
  color: #888 !important;
}

.mini-btn:disabled i {
  color: #888 !important;
}

/* Options Section */
.options-section {
  border-top: 1px solid rgba(255, 255, 255, 0.1);
  padding-top: 16px;
}

.options-title {
  font-size: 16px;
  font-weight: 600;
  color: #00ff88;
  margin-bottom: 12px;
}

.option-type {
  margin-bottom: 12px;
  padding: 12px;
  border-radius: 10px;
  border: 1px solid rgba(255, 255, 255, 0.1);
}

.call-option {
  background: rgba(0, 255, 136, 0.05);
  border-color: rgba(0, 255, 136, 0.2);
}

.put-option {
  background: rgba(255, 68, 68, 0.05);
  border-color: rgba(255, 68, 68, 0.2);
}

.option-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 8px;
}

.option-label {
  font-weight: 600;
  font-size: 14px;
}

.option-price {
  font-weight: 600;
  color: white;
}

.option-actions {
  display: flex;
  gap: 8px;
}

.trade-btn {
  flex: 1;
  padding: 8px 12px;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-weight: 600;
  font-size: 12px;
  transition: all 0.3s ease;
}

.buy-btn {
  background: #00ff88;
  color: #000000;
}

.sell-btn {
  background: #ff4444;
  color: white;
}

.trade-btn:hover {
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
}

/* Empty State */
.empty-state {
  text-align: center;
  padding: 60px 20px;
  background: rgba(255, 255, 255, 0.05);
  backdrop-filter: blur(10px);
  border-radius: 16px;
  border: 1px solid rgba(255, 255, 255, 0.1);
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
}

.empty-icon {
  font-size: 48px;
  margin-bottom: 16px;
}

.empty-title {
  font-size: 24px;
  font-weight: bold;
  color: #00ff88 !important;
  margin-bottom: 12px;
}

.empty-message {
  color: #a0a0a0 !important;
  margin-bottom: 24px;
  line-height: 1.6;
}

.retry-btn {
  background: linear-gradient(135deg, #00ff88, #00d4ff);
  color: #000000;
  border: none;
  padding: 12px 24px;
  border-radius: 10px;
  cursor: pointer;
  font-weight: 600;
  transition: all 0.3s ease;
}

.retry-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(0, 255, 136, 0.3);
}

/* Modal Styles */
.modal-overlay {
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
  padding: 20px;
  overflow-y: auto;
  -webkit-overflow-scrolling: touch;
}

.modal-content {
  background: #1a1a2e;
  border-radius: 16px;
  border: 1px solid rgba(255, 255, 255, 0.1);
  max-width: 500px;
  width: 100%;
  max-height: 90vh;
  overflow-y: auto;
  margin: 20px auto;
  -webkit-overflow-scrolling: touch;
}

.orders-modal {
  max-width: 900px;
  max-height: 90vh;
}

/* Improved Orders Modal Header */
.orders-modal .modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 24px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
  background: linear-gradient(145deg, #101022, #0d0d1a);
}

.header-left {
  display: flex;
  align-items: center;
  gap: 16px;
}

.user-avatar {
  width: 48px;
  height: 48px;
  background: linear-gradient(145deg, #00ff88, #00cc6a);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #0d0d1a;
  font-size: 20px;
}

.header-info h3 {
  font-size: 24px;
  font-weight: bold;
  color: #00ff88;
  margin: 0;
}

.user-name {
  color: #a0a0a0;
  margin: 4px 0 0 0;
  font-size: 14px;
}

/* Orders Container */
.orders-container {
  padding: 0;
}

/* Orders Stats */
.orders-stats {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
  gap: 16px;
  margin-bottom: 24px;
  padding: 0 24px;
}

.stat-card {
  background: linear-gradient(145deg, #101022, #0d0d1a);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 12px;
  padding: 20px;
  display: flex;
  align-items: center;
  gap: 16px;
  transition: all 0.3s ease;
}

.stat-card:hover {
  border-color: rgba(0, 255, 136, 0.3);
  transform: translateY(-2px);
}

.stat-icon {
  width: 48px;
  height: 48px;
  background: linear-gradient(145deg, #00ff88, #00cc6a);
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #0d0d1a;
  font-size: 20px;
}

.stat-info {
  display: flex;
  flex-direction: column;
}

.stat-number {
  font-size: 24px;
  font-weight: bold;
  color: #00ff88;
  line-height: 1;
}

.stat-label {
  color: #a0a0a0;
  font-size: 14px;
  margin-top: 4px;
}

/* Orders List */
.orders-list {
  padding: 0 24px 24px;
}

.order-card {
  background: linear-gradient(145deg, #101022, #0d0d1a);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 16px;
  margin-bottom: 16px;
  overflow: hidden;
  transition: all 0.3s ease;
}

.order-card:hover {
  border-color: rgba(0, 255, 136, 0.3);
  transform: translateY(-2px);
}

.order-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px 24px;
  background: rgba(0, 255, 136, 0.05);
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.order-id-section {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.order-id {
  font-size: 18px;
  font-weight: bold;
  color: #00ff88;
}

.order-date {
  color: #a0a0a0;
  font-size: 12px;
}

.order-status {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 8px 16px;
  border-radius: 20px;
  font-size: 14px;
  font-weight: 600;
}

.order-status.completed {
  background: rgba(0, 255, 136, 0.2);
  color: #00ff88;
}

.order-status.pending {
  background: rgba(255, 193, 7, 0.2);
  color: #ffc107;
}

.order-status.cancelled {
  background: rgba(255, 68, 68, 0.2);
  color: #ff4444;
}

.order-status.closed {
  background: rgba(108, 117, 125, 0.2);
  color: #6c757d;
}

/* Order Content */
.order-content {
  padding: 24px;
}

.trade-info {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.stock-info {
  display: flex;
  align-items: center;
  gap: 12px;
}

.stock-symbol {
  font-size: 20px;
  font-weight: bold;
  color: white;
}

.option-type {
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 6px 12px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 600;
}

.option-type.call {
  background: rgba(0, 255, 136, 0.2);
  color: #00ff88;
}

.option-type.put {
  background: rgba(255, 68, 68, 0.2);
  color: #ff4444;
}

.action-info {
  display: flex;
  align-items: center;
  gap: 12px;
}

.action {
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 6px 12px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 600;
}

.action.buy {
  background: rgba(0, 255, 136, 0.2);
  color: #00ff88;
}

.action.sell {
  background: rgba(255, 68, 68, 0.2);
  color: #ff4444;
}

.quantity {
  color: #a0a0a0;
  font-size: 14px;
}

/* Price Info */
.price-info {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 20px;
  margin-bottom: 16px;
}

.pnl-item {
  grid-column: 1 / -1;
  border: 2px solid transparent;
  background: rgba(255, 255, 255, 0.05);
}

.pnl-item.profit {
  border-color: rgba(0, 255, 136, 0.3);
  background: rgba(0, 255, 136, 0.1);
}

.pnl-item.loss {
  border-color: rgba(255, 68, 68, 0.3);
  background: rgba(255, 68, 68, 0.1);
}

.price-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 12px 16px;
  background: rgba(255, 255, 255, 0.05);
  border-radius: 8px;
}

.price-item .label {
  color: #a0a0a0;
  font-size: 14px;
}

.price-item .value {
  color: white;
  font-weight: 600;
}

.price-item .value.total {
  color: #00ff88;
  font-size: 16px;
}

.price-item .value.profit {
  color: #00ff88;
  font-weight: bold;
  font-size: 16px;
}

.price-item .value.loss {
  color: #ff4444;
  font-weight: bold;
  font-size: 16px;
}

.price-item .value.neutral {
  color: #a0a0a0;
  font-weight: bold;
  font-size: 16px;
}

/* Order Notes */
.order-notes {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 12px 16px;
  background: rgba(255, 193, 7, 0.1);
  border-radius: 8px;
  color: #ffc107;
  font-size: 14px;
}

/* Empty State */
.empty-orders {
  text-align: center;
  padding: 60px 20px;
}

.empty-orders .empty-icon {
  width: 80px;
  height: 80px;
  background: linear-gradient(145deg, #00ff88, #00cc6a);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 24px;
  color: #0d0d1a;
  font-size: 32px;
}

.empty-orders h4 {
  font-size: 24px;
  color: #00ff88;
  margin: 0 0 12px 0;
}

.empty-orders p {
  color: #a0a0a0;
  margin: 0 0 24px 0;
  font-size: 16px;
}

.start-trading-btn {
  background: linear-gradient(145deg, #00ff88, #00cc6a);
  color: #0d0d1a;
  border: none;
  padding: 12px 24px;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 8px;
  margin: 0 auto;
  transition: all 0.3s ease;
}

.start-trading-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(0, 255, 136, 0.3);
}

/* Order Actions */
.order-actions {
  margin-top: 16px;
  padding-top: 16px;
  border-top: 1px solid rgba(255, 255, 255, 0.1);
  display: flex;
  justify-content: flex-end;
}

.exit-trade-btn {
  background: linear-gradient(145deg, #ff4444, #cc3333);
  color: white;
  border: none;
  padding: 10px 20px;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 8px;
  transition: all 0.3s ease;
  font-size: 14px;
}

.exit-trade-btn:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(255, 68, 68, 0.3);
}

.exit-trade-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
  transform: none;
  background: #666 !important;
  color: #999 !important;
}

.market-closed-notice {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-top: 8px;
  padding: 8px 12px;
  background: rgba(255, 68, 68, 0.1);
  border: 1px solid rgba(255, 68, 68, 0.3);
  border-radius: 6px;
  font-size: 12px;
  color: #ff4444;
}

.market-closed-notice i {
  font-size: 12px;
}

/* Market Time Display */
.market-time {
  font-size: 14px;
  color: #a0a0a0;
  margin-left: 8px;
}

.next-open {
  display: block;
  font-size: 12px;
  color: #00ff88;
  margin-top: 4px;
}

/* Market Notice */
.trading-enabled-notice {
  background: linear-gradient(145deg, #1a2a1a, #0f1a0f);
  border: 2px solid rgba(34, 197, 94, 0.3);
  border-radius: 12px;
  padding: 20px;
  margin-bottom: 24px;
  box-shadow: 0 8px 32px rgba(34, 197, 94, 0.1);
}

.notice-content {
  display: flex;
  align-items: center;
  gap: 16px;
}

.notice-content i {
  font-size: 24px;
  color: #22c55e;
  flex-shrink: 0;
}

.notice-text h3 {
  color: #22c55e;
  margin: 0 0 8px 0;
  font-size: 18px;
  font-weight: 600;
}

.notice-text p {
  color: #e0e0e0;
  margin: 4px 0;
  font-size: 14px;
  line-height: 1.5;
}

.notice-sub {
  color: #a0a0a0 !important;
  font-size: 12px !important;
  font-style: italic;
}

.notice-text strong {
  color: #00ff88;
}

.notice-info {
  color: #00ff88 !important;
  font-weight: 500;
  margin-top: 8px !important;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.modal-title {
  font-size: 20px;
  font-weight: bold;
  color: #00ff88;
  margin: 0;
  display: flex;
  align-items: center;
  gap: 8px;
}

.close-btn {
  background: none;
  border: none;
  color: #a0a0a0;
  font-size: 20px;
  cursor: pointer;
  padding: 4px;
}

.modal-body {
  padding: 20px;
}

.trade-info {
  margin-bottom: 20px;
}

.info-row {
  display: flex;
  justify-content: space-between;
  margin-bottom: 12px;
  padding: 8px 0;
  border-bottom: 1px solid rgba(255, 255, 255, 0.05);
}

.option-type-badge {
  padding: 4px 8px;
  border-radius: 4px;
  font-size: 12px;
  font-weight: 600;
}

.option-type-badge.call {
  background: rgba(0, 255, 136, 0.2);
  color: #00ff88;
}

.option-type-badge.put {
  background: rgba(255, 68, 68, 0.2);
  color: #ff4444;
}

.action-badge {
  padding: 4px 8px;
  border-radius: 4px;
  font-size: 12px;
  font-weight: 600;
}

.action-badge.buy {
  background: rgba(0, 255, 136, 0.2);
  color: #00ff88;
}

.action-badge.sell {
  background: rgba(255, 68, 68, 0.2);
  color: #ff4444;
}

.quantity-section {
  margin-bottom: 20px;
}

.quantity-label {
  display: block;
  margin-bottom: 8px;
  font-weight: 600;
  color: #00ff88;
}

.lot-info {
  display: flex;
  flex-direction: column;
  gap: 12px;
  padding: 16px;
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 12px;
}

.lot-details {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 8px 0;
}

.lot-label {
  font-weight: 500;
  color: #e0e0e0;
}

.lot-value {
  font-weight: 600;
  color: #00ff88;
}

.quantity-display {
  font-weight: 600;
  color: #00ff88;
  font-size: 16px;
}

.lot-input-wrapper {
  display: flex;
  align-items: center;
  gap: 8px;
  flex-wrap: wrap;
}

.lot-input {
  width: 80px;
  padding: 8px 12px;
  background: rgba(255, 255, 255, 0.1);
  border: 1px solid rgba(255, 255, 255, 0.2);
  border-radius: 8px;
  color: white;
  text-align: center;
  font-weight: 600;
}

.quantity-input-wrapper {
  display: flex;
  align-items: center;
  gap: 12px;
  flex-wrap: wrap;
}

.qty-btn {
  background: rgba(255, 255, 255, 0.1);
  border: 1px solid rgba(255, 255, 255, 0.2);
  color: white;
  width: 40px;
  height: 40px;
  border-radius: 8px;
  cursor: pointer;
  font-weight: bold;
  transition: all 0.3s ease;
  min-height: 44px;
  min-width: 44px;
  touch-action: manipulation;
  display: flex;
  align-items: center;
  justify-content: center;
}

.qty-btn:hover {
  background: rgba(255, 255, 255, 0.2);
}

.quantity-input {
  background: rgba(255, 255, 255, 0.1);
  border: 1px solid rgba(255, 255, 255, 0.2);
  color: white;
  padding: 12px;
  border-radius: 8px;
  width: 100px;
  text-align: center;
  font-weight: 600;
}

.trade-summary {
  background: rgba(255, 255, 255, 0.05);
  border-radius: 10px;
  padding: 16px;
  margin-bottom: 20px;
}

.summary-row {
  display: flex;
  justify-content: space-between;
  margin-bottom: 8px;
}

.summary-row.insufficient {
  color: #ff4444;
}

.summary-row.sufficient {
  color: #00ff88;
}

.total-amount {
  font-weight: bold;
  color: #00ff88;
}

.modal-footer {
  display: flex;
  gap: 12px;
  padding: 20px;
  border-top: 1px solid rgba(255, 255, 255, 0.1);
}

.trade-options {
  display: flex;
  gap: 12px;
  flex: 1;
  flex-wrap: wrap;
}

.btn {
  flex: 1;
  padding: 12px 20px;
  border: none;
  border-radius: 10px;
  cursor: pointer;
  font-weight: 600;
  transition: all 0.3s ease;
  min-height: 44px;
  touch-action: manipulation;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
}

.btn-secondary {
  background: rgba(255, 255, 255, 0.1);
  color: white;
  border: 1px solid rgba(255, 255, 255, 0.2);
}

.btn-primary {
  background: linear-gradient(135deg, #00ff88, #00d4ff);
  color: #000000;
}

.btn:hover {
  transform: translateY(-2px);
}

.btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
  transform: none;
  background: #666 !important;
  color: #999 !important;
}

.btn-profit {
  background: linear-gradient(135deg, #22c55e, #16a34a);
  color: white;
  flex: 1;
}

.btn-profit:hover:not(:disabled) {
  background: linear-gradient(135deg, #16a34a, #15803d);
  transform: translateY(-2px);
}

.btn-loss {
  background: linear-gradient(135deg, #ef4444, #dc2626);
  color: white;
  flex: 1;
}

.btn-loss:hover:not(:disabled) {
  background: linear-gradient(135deg, #dc2626, #b91c1c);
  transform: translateY(-2px);
}

/* Orders List */
.empty-orders {
  text-align: center;
  padding: 40px 20px;
}

.empty-orders .empty-icon {
  font-size: 32px;
  margin-bottom: 12px;
}

.empty-orders h4 {
  color: #00ff88;
  margin-bottom: 8px;
}

.empty-orders p {
  color: #a0a0a0;
}

.orders-list {
  max-height: 400px;
  overflow-y: auto;
}

.order-item {
  background: rgba(255, 255, 255, 0.05);
  border-radius: 10px;
  padding: 16px;
  margin-bottom: 12px;
  border: 1px solid rgba(255, 255, 255, 0.1);
}

.order-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 8px;
}

.order-id {
  font-weight: bold;
  color: #00ff88;
}

.order-status {
  padding: 4px 8px;
  border-radius: 4px;
  font-size: 12px;
  font-weight: 600;
}

.order-status.completed {
  background: rgba(0, 255, 136, 0.2);
  color: #00ff88;
}

.order-status.pending {
  background: rgba(255, 193, 7, 0.2);
  color: #ffc107;
}

.order-status.cancelled {
  background: rgba(255, 68, 68, 0.2);
  color: #ff4444;
}

.order-details {
  color: #a0a0a0;
}

.order-info {
  display: flex;
  gap: 12px;
  margin-bottom: 8px;
  flex-wrap: wrap;
}

.order-meta {
  display: flex;
  gap: 16px;
  margin-bottom: 4px;
  font-size: 14px;
}

.order-time {
  font-size: 12px;
  color: #666;
}

/* ===== RESPONSIVE DESIGN ===== */

/* Large Desktop (1400px+) */
@media (min-width: 1400px) {
  .container {
    max-width: 1400px;
  }
  
  .stocks-grid {
    grid-template-columns: repeat(4, 1fr);
  }
  
  .stock-options-modal {
    max-width: 1200px;
  }
}

/* Desktop (1200px - 1399px) */
@media (max-width: 1399px) and (min-width: 1200px) {
  .stocks-grid {
    grid-template-columns: repeat(3, 1fr);
  }
  
  .stock-options-modal {
    max-width: 1000px;
  }
}

/* Laptop (992px - 1199px) */
@media (max-width: 1199px) and (min-width: 992px) {
  .stocks-grid {
    grid-template-columns: repeat(3, 1fr);
  }
  
  .header-content {
    flex-direction: column;
    align-items: flex-start;
    gap: 16px;
  }
  
  .header-actions {
    flex-direction: row;
    flex-wrap: wrap;
    gap: 12px;
  }
  
  .stock-options-modal {
    max-width: 900px;
  }
  
  .options-chain {
    overflow-x: auto;
  }
}

/* Tablet (768px - 991px) */
@media (max-width: 991px) and (min-width: 768px) {
  .ai-trading-session {
    padding: 16px;
  }
  
  .page-header {
    flex-direction: column;
    align-items: stretch;
    gap: 16px;
    padding: 16px;
  }
  
  .header-content {
    flex-direction: column;
    align-items: flex-start;
    gap: 12px;
  }
  
  .header-actions {
    width: 100%;
    justify-content: flex-start;
  }
  
  .stocks-grid {
    grid-template-columns: repeat(2, 1fr);
    gap: 16px;
  }
  
  .stock-card {
    padding: 16px;
  }
  
  .stock-symbol {
    font-size: 1.1rem;
  }
  
  .stock-price {
    font-size: 1.3rem;
  }
  
  .stock-change {
    font-size: 0.9rem;
  }
  
  .filters-section {
    flex-direction: column;
    align-items: stretch;
    gap: 16px;
  }
  
  .search-box {
    width: 100%;
  }
  
  .sort-dropdown {
    width: 100%;
  }
  
  .header-content {
    flex-direction: column;
    align-items: flex-start;
    gap: 16px;
  }
  
  .header-actions {
    flex-direction: row;
    flex-wrap: wrap;
    gap: 12px;
    width: 100%;
  }
  
  .btn {
    flex: 1;
    min-width: 120px;
  }
  
  /* Stock Options Modal */
  .stock-options-modal {
    width: 95%;
    max-width: none;
    margin: 20px auto;
    max-height: 90vh;
    overflow-y: auto;
  }
  
  .modal-header {
    padding: 16px 20px;
    flex-direction: column;
    align-items: flex-start;
    gap: 12px;
  }
  
  .modal-title {
    font-size: 1.3rem;
  }
  
  .modal-body {
    padding: 20px;
  }
  
  .stock-info-section {
    padding: 16px;
    flex-direction: column;
    text-align: center;
    gap: 12px;
  }
  
  .stock-price-large {
    font-size: 2.2rem;
  }
  
  .stock-change-large {
    font-size: 1.1rem;
  }
  
  .options-grid {
    grid-template-columns: 1fr;
    gap: 16px;
  }
  
  .option-card {
    padding: 16px;
  }
  
  .option-header {
    flex-direction: column;
    text-align: center;
    gap: 10px;
  }
  
  .option-actions {
    flex-direction: column;
    gap: 8px;
  }
  
  .trade-btn {
    width: 100%;
    padding: 12px;
  }
  
  /* Options Chain Table */
  .options-chain {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
  }
  
  .options-table {
    min-width: 600px;
  }
  
  .options-header,
  .option-row {
    grid-template-columns: 80px 60px 60px 60px 60px 100px;
    gap: 8px;
    font-size: 0.85rem;
  }
  
  .header-cell,
  .option-cell {
    font-size: 0.85rem;
    padding: 8px 4px;
  }
  
  .mini-btn {
    padding: 6px 10px;
    font-size: 0.75rem;
    min-width: 45px;
    gap: 3px;
  }
  
  .mini-btn i {
    font-size: 0.65rem;
  }
  
  .mini-btn span {
    font-size: 0.65rem;
  }
  
  .options-section {
    padding: 16px;
  }
  
  .options-section-title {
    font-size: 1.1rem;
  }
}

/* Mobile Large (576px - 767px) */
@media (max-width: 767px) and (min-width: 576px) {
  .ai-trading-session {
    padding: 12px;
  }
  
  .page-header {
    flex-direction: column;
    align-items: stretch;
    gap: 12px;
    padding: 12px;
  }
  
  .header-content {
    flex-direction: column;
    align-items: flex-start;
    gap: 8px;
  }
  
  .header-actions {
    width: 100%;
    flex-direction: column;
    gap: 8px;
  }
  
  .refresh-btn, .orders-btn {
    width: 100%;
    justify-content: center;
  }
  
  .stocks-grid {
    grid-template-columns: 1fr;
    gap: 12px;
  }
  
  .stock-header {
    flex-direction: column;
    align-items: stretch;
    gap: 8px;
    min-height: auto;
  }
  
  .stock-info {
    width: 100%;
  }
  
  .stock-change {
    width: 100%;
    justify-content: flex-start;
    min-width: auto;
  }
  
  .stock-card {
    padding: 14px;
  }
  
  .stock-symbol {
    font-size: 1rem;
  }
  
  .stock-price {
    font-size: 1.2rem;
  }
  
  .stock-change {
    font-size: 0.85rem;
  }
  
  .click-hint {
    font-size: 0.75rem;
    padding: 4px 8px;
  }
  
  .filters-section {
    flex-direction: column;
    align-items: stretch;
    gap: 12px;
  }
  
  .search-box {
    width: 100%;
    padding: 10px 12px;
    font-size: 14px;
  }
  
  .sort-dropdown {
    width: 100%;
    padding: 10px 12px;
    font-size: 14px;
  }
  
  .header-content {
    flex-direction: column;
    align-items: flex-start;
    gap: 12px;
  }
  
  .header-actions {
    flex-direction: column;
    width: 100%;
    gap: 8px;
  }
  
  .btn {
    width: 100%;
    padding: 12px 16px;
    font-size: 14px;
  }
  
  /* Stock Options Modal */
  .stock-options-modal {
    width: 98%;
    max-width: none;
    margin: 10px auto;
    max-height: 95vh;
    overflow-y: auto;
  }
  
  .modal-header {
    padding: 12px 16px;
    flex-direction: column;
    align-items: flex-start;
    gap: 8px;
  }
  
  .modal-title {
    font-size: 1.1rem;
  }
  
  .close-btn {
    position: absolute;
    top: 12px;
    right: 16px;
    padding: 8px;
  }
  
  .modal-body {
    padding: 16px;
  }
  
  .stock-info-section {
    padding: 12px;
    flex-direction: column;
    text-align: center;
    gap: 8px;
  }
  
  .stock-price-large {
    font-size: 1.8rem;
  }
  
  .stock-change-large {
    font-size: 1rem;
  }
  
  .options-grid {
    grid-template-columns: 1fr;
    gap: 12px;
  }
  
  .option-card {
    padding: 12px;
  }
  
  .option-header {
    flex-direction: column;
    text-align: center;
    gap: 8px;
  }
  
  .option-actions {
    flex-direction: column;
    gap: 6px;
  }
  
  .trade-btn {
    width: 100%;
    padding: 10px;
    font-size: 14px;
  }
  
  /* Options Chain Table */
  .options-chain {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    border-radius: 8px;
  }
  
  .options-table {
    min-width: 500px;
  }
  
  .options-header,
  .option-row {
    grid-template-columns: 70px 50px 50px 50px 50px 80px;
    gap: 6px;
    font-size: 0.8rem;
  }
  
  .header-cell,
  .option-cell {
    font-size: 0.8rem;
    padding: 6px 3px;
  }
  
  .mini-btn {
    padding: 4px 8px;
    font-size: 0.7rem;
    min-width: 40px;
    gap: 2px;
  }
  
  .mini-btn i {
    font-size: 0.6rem;
  }
  
  .mini-btn span {
    font-size: 0.6rem;
  }
  
  .options-section {
    padding: 12px;
  }
  
  .options-section-title {
    font-size: 1rem;
  }
}

/* Mobile Small (up to 575px) */
@media (max-width: 575px) {
  .ai-trading-session {
    padding: 8px;
  }
  
  .page-header {
    flex-direction: column;
    align-items: stretch;
    gap: 10px;
    padding: 10px;
  }
  
  .header-content {
    flex-direction: column;
    align-items: flex-start;
    gap: 6px;
  }
  
  .header-actions {
    width: 100%;
    flex-direction: column;
    gap: 6px;
  }
  
  .refresh-btn, .orders-btn {
    width: 100%;
    justify-content: center;
    padding: 10px 14px;
    font-size: 13px;
  }
  
  .stocks-grid {
    grid-template-columns: 1fr;
    gap: 10px;
  }
  
  .stock-header {
    flex-direction: column;
    align-items: stretch;
    gap: 6px;
    min-height: auto;
  }
  
  .stock-info {
    width: 100%;
  }
  
  .stock-change {
    width: 100%;
    justify-content: flex-start;
    min-width: auto;
    padding: 6px 8px;
  }
  
  .stock-card {
    padding: 12px;
  }
  
  .stock-symbol {
    font-size: 0.95rem;
  }
  
  .stock-price {
    font-size: 1.1rem;
  }
  
  .stock-change {
    font-size: 0.8rem;
  }
  
  .click-hint {
    font-size: 0.7rem;
    padding: 3px 6px;
  }
  
  .filters-section {
    flex-direction: column;
    align-items: stretch;
    gap: 10px;
  }
  
  .search-box {
    width: 100%;
    padding: 8px 10px;
    font-size: 13px;
  }
  
  .sort-dropdown {
    width: 100%;
    padding: 8px 10px;
    font-size: 13px;
  }
  
  .header-content {
    flex-direction: column;
    align-items: flex-start;
    gap: 10px;
  }
  
  .header-actions {
    flex-direction: column;
    width: 100%;
    gap: 6px;
  }
  
  .btn {
    width: 100%;
    padding: 10px 14px;
    font-size: 13px;
  }
  
  /* Stock Options Modal */
  .stock-options-modal {
    width: 100%;
    max-width: none;
    margin: 0;
    max-height: 100vh;
    border-radius: 0;
    overflow-y: auto;
  }
  
  .modal-header {
    padding: 10px 12px;
    flex-direction: column;
    align-items: flex-start;
    gap: 6px;
  }
  
  .modal-title {
    font-size: 1rem;
  }
  
  .close-btn {
    position: absolute;
    top: 10px;
    right: 12px;
    padding: 6px;
    font-size: 18px;
  }
  
  .modal-body {
    padding: 12px;
  }
  
  .stock-info-section {
    padding: 10px;
    flex-direction: column;
    text-align: center;
    gap: 6px;
  }
  
  .stock-price-large {
    font-size: 1.6rem;
  }
  
  .stock-change-large {
    font-size: 0.9rem;
  }
  
  .options-grid {
    grid-template-columns: 1fr;
    gap: 10px;
  }
  
  .option-card {
    padding: 10px;
  }
  
  .option-header {
    flex-direction: column;
    text-align: center;
    gap: 6px;
  }
  
  .option-actions {
    flex-direction: column;
    gap: 4px;
  }
  
  .trade-btn {
    width: 100%;
    padding: 8px;
    font-size: 13px;
  }
  
  /* Options Chain Table */
  .options-chain {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    border-radius: 6px;
  }
  
  .options-table {
    min-width: 450px;
  }
  
  .options-header,
  .option-row {
    grid-template-columns: 60px 45px 45px 45px 45px 70px;
    gap: 4px;
    font-size: 0.75rem;
  }
  
  .header-cell,
  .option-cell {
    font-size: 0.75rem;
    padding: 4px 2px;
  }
  
  .mini-btn {
    padding: 3px 6px;
    font-size: 0.65rem;
    min-width: 35px;
    gap: 2px;
  }
  
  .mini-btn i {
    font-size: 0.55rem;
  }
  
  .mini-btn span {
    font-size: 0.55rem;
  }
  
  .options-section {
    padding: 10px;
  }
  
  .options-section-title {
    font-size: 0.9rem;
  }
}

/* Very Small Screens (up to 400px) */
@media (max-width: 400px) {
  .ai-trading-session {
    padding: 4px;
  }
  
  .page-header {
    padding: 8px;
    gap: 8px;
  }
  
  .page-title {
    font-size: 20px;
  }
  
  .page-subtitle {
    font-size: 12px;
  }
  
  .stock-card {
    padding: 10px;
  }
  
  .stock-header {
    flex-direction: column;
    align-items: stretch;
    gap: 4px;
    min-height: auto;
  }
  
  .stock-info {
    width: 100%;
  }
  
  .stock-symbol {
    font-size: 16px;
  }
  
  .stock-price {
    font-size: 16px;
  }
  
  .stock-change {
    width: 100%;
    justify-content: flex-start;
    min-width: auto;
    font-size: 12px;
    padding: 3px 6px;
  }
  
  .click-hint {
    font-size: 11px;
    padding: 6px 8px;
  }
  
  .filters-section {
    gap: 8px;
  }
  
  .search-input-wrapper {
    padding: 0 12px;
  }
  
  .filter-input {
    padding: 10px 0;
    font-size: 14px;
  }
  
  .filter-select {
    padding: 10px 12px;
    font-size: 14px;
  }
  
  .refresh-btn, .orders-btn {
    padding: 8px 12px;
    font-size: 12px;
  }
}

/* Landscape Mobile */
@media (max-height: 500px) and (orientation: landscape) {
  .stock-options-modal {
    max-height: 100vh;
    margin: 0;
    border-radius: 0;
  }
  
  .modal-body {
    padding: 8px;
  }
  
  .stock-info-section {
    padding: 8px;
  }
  
  .options-section {
    padding: 8px;
  }
}

/* High DPI Displays */
@media (-webkit-min-device-pixel-ratio: 2), (min-resolution: 192dpi) {
  .stock-card {
    border-width: 0.5px;
  }
  
  .modal-overlay {
    backdrop-filter: blur(2px);
  }
}

/* Mobile Viewport Fixes */
@supports (-webkit-touch-callout: none) {
  .ai-trading-session {
    min-height: -webkit-fill-available;
  }
  
  .modal-overlay {
    min-height: -webkit-fill-available;
  }
}

/* Print Styles */
@media print {
  .stock-options-modal {
    position: static;
    width: 100%;
    height: auto;
    margin: 0;
    box-shadow: none;
    border: 1px solid #000;
  }
  
  .modal-overlay {
    display: none;
  }
  
  .close-btn {
    display: none;
  }
}
</style>
