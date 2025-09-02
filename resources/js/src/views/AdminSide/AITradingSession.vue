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
            (Balance: ₹{{ user.balance?.toLocaleString() || '0' }})
          </p>
          <p style="font-size: 12px; color: #a0a0a0;">Debug: User ID: {{ user.id }}, Balance: {{ user.balance }}</p>
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
        <button class="refresh-btn" @click="loadUserBalance" :disabled="loading">
          <i class="fas fa-wallet"></i>
          Refresh Balance
        </button>
      </div>
    </div>

    <!-- Market Status -->
    <div class="market-status">
      <div class="status-indicator" :class="marketStatus.is_open ? 'open' : 'closed'">
        <div class="status-dot"></div>
        <span>{{ marketStatus.is_open ? 'Market LIVE' : 'Market CLOSED' }}</span>
        <span class="market-time">{{ marketStatus.current_time }} IST</span>
      </div>
      <div class="last-update">
        Last Update: {{ lastUpdate || 'Never' }}
        <span v-if="!marketStatus.is_open && marketStatus.next_open_time" class="next-open">
          Next Open: {{ formatDateTime(marketStatus.next_open_time) }}
        </span>
      </div>
    </div>



    <!-- Market Hours Notice -->
    <div v-if="!marketStatus.is_open" class="market-notice">
      <div class="notice-content">
        <i class="fas fa-clock"></i>
        <div class="notice-text">
          <h3>Market is Currently Closed</h3>
          <p>Trading is only allowed during market hours: <strong>{{ marketStatus.market_hours.days }}, {{ marketStatus.market_hours.open }} - {{ marketStatus.market_hours.close }} {{ marketStatus.market_hours.timezone }}</strong></p>
          <p v-if="marketStatus.next_open_time">Next market opens: <strong>{{ formatDateTime(marketStatus.next_open_time) }}</strong></p>
          <p class="notice-info">💡 <strong>You can still view your orders and check P&L anytime!</strong></p>
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

      <!-- Stock Cards with Call/Put Options -->
      <div class="stocks-grid">
        <div v-for="stock in filteredStocks" :key="stock.symbol" class="stock-card">
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

          <!-- Call/Put Options -->
          <div class="options-section">
            <h4 class="options-title">Options Trading</h4>
            
            <!-- Call Options -->
            <div class="option-type call-option">
              <div class="option-header">
                <span class="option-label">📈 CALL</span>
                <span class="option-price">₹{{ (stock.ltp * 1.02).toFixed(2) }}</span>
              </div>
              <div class="option-actions">
                <button 
                  class="trade-btn buy-btn" 
                  @click="openTradeModal(stock, 'CALL', 'BUY')"
                  :disabled="!marketStatus.is_open"
                  :title="!marketStatus.is_open ? 'Trading only allowed during market hours' : ''"
                >
                  <i class="fas fa-arrow-up"></i>
                  Buy Call
                </button>
                <button 
                  class="trade-btn sell-btn" 
                  @click="openTradeModal(stock, 'CALL', 'SELL')"
                  :disabled="!marketStatus.is_open"
                  :title="!marketStatus.is_open ? 'Trading only allowed during market hours' : ''"
                >
                  <i class="fas fa-arrow-down"></i>
                  Sell Call
                </button>
              </div>
            </div>

            <!-- Put Options -->
            <div class="option-type put-option">
              <div class="option-header">
                <span class="option-label">📉 PUT</span>
                <span class="option-price">₹{{ (stock.ltp * 0.98).toFixed(2) }}</span>
              </div>
              <div class="option-actions">
                <button 
                  class="trade-btn buy-btn" 
                  @click="openTradeModal(stock, 'PUT', 'BUY')"
                  :disabled="!marketStatus.is_open"
                  :title="!marketStatus.is_open ? 'Trading only allowed during market hours' : ''"
                >
                  <i class="fas fa-arrow-up"></i>
                  Buy Put
                </button>
                <button 
                  class="trade-btn sell-btn" 
                  @click="openTradeModal(stock, 'PUT', 'SELL')"
                  :disabled="!marketStatus.is_open"
                  :title="!marketStatus.is_open ? 'Trading only allowed during market hours' : ''"
                >
                  <i class="fas fa-arrow-down"></i>
                  Sell Put
                </button>
              </div>
            </div>
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
            <label class="quantity-label">Quantity:</label>
            <div class="quantity-input-wrapper">
              <button class="qty-btn" @click="decreaseQuantity">-</button>
              <input 
                v-model.number="tradeData.quantity" 
                type="number" 
                class="quantity-input"
                min="1"
                max="1000"
              >
              <button class="qty-btn" @click="increaseQuantity">+</button>
            </div>
          </div>

          <div class="trade-summary">
            <div class="summary-row">
              <span>Total Amount:</span>
              <span class="total-amount">₹{{ (tradeData.strikePrice * tradeData.quantity).toLocaleString() }}</span>
            </div>
            <div class="summary-row">
              <span>Available Balance:</span>
              <span>₹{{ user.balance?.toLocaleString() }}</span>
            </div>
            <div class="summary-row" :class="(tradeData.strikePrice * tradeData.quantity) > user.balance ? 'insufficient' : 'sufficient'">
              <span>Status:</span>
              <span>{{ (tradeData.strikePrice * tradeData.quantity) > user.balance ? 'Insufficient Balance' : 'Sufficient Balance' }}</span>
            </div>
          </div>
        </div>

        <div class="modal-footer">
          <button class="btn btn-secondary" @click="closeTradeModal">
            Cancel
          </button>
          <button 
            class="btn btn-primary" 
            @click="executeTrade"
            :disabled="(tradeData.strikePrice * tradeData.quantity) > user.balance || tradeData.quantity < 1"
          >
            <i class="fas fa-check"></i>
            Execute Trade
          </button>
        </div>
      </div>
    </div>

    <!-- Orders Modal (Vue-based - keeping for reference but not used) -->
    <div v-if="showOrdersModal" class="modal-overlay" @click="closeOrdersModal">
      <div class="modal-content orders-modal" @click.stop>
        <div class="modal-header">
          <div class="header-left">
            <div class="user-avatar">
              <i class="fas fa-user"></i>
            </div>
            <div class="header-info">
              <h3 class="modal-title">Trading Orders</h3>
              <p class="user-name">{{ user.name }}</p>
            </div>
          </div>
          <button class="close-btn" @click="closeOrdersModal">
            <i class="fas fa-times"></i>
          </button>
        </div>

        <div class="modal-body">
          <div v-if="userOrders.length === 0" class="empty-orders">
            <div class="empty-icon">
              <i class="fas fa-chart-line"></i>
            </div>
            <h4>No Trading Orders</h4>
            <p>This user hasn't made any trades yet.</p>
            <button class="start-trading-btn" @click="closeOrdersModal">
              <i class="fas fa-plus"></i>
              Start Trading
            </button>
          </div>

          <div v-else class="orders-container">
            <div class="orders-stats">
              <div class="stat-card">
                <div class="stat-icon">
                  <i class="fas fa-list"></i>
                </div>
                <div class="stat-info">
                  <span class="stat-number">{{ userOrders.length }}</span>
                  <span class="stat-label">Total Orders</span>
                </div>
              </div>
              
              <div class="stat-card">
                <div class="stat-icon">
                  <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-info">
                  <span class="stat-number">{{ userOrders.filter(o => o.status === 'COMPLETED').length }}</span>
                  <span class="stat-label">Completed</span>
                </div>
              </div>
              
              <div class="stat-card">
                <div class="stat-icon">
                  <i class="fas fa-clock"></i>
                </div>
                <div class="stat-info">
                  <span class="stat-number">{{ userOrders.filter(o => o.status === 'PENDING').length }}</span>
                  <span class="stat-label">Pending</span>
                </div>
              </div>
              
              <div class="stat-card">
                <div class="stat-icon">
                  <i class="fas fa-times-circle"></i>
                </div>
                <div class="stat-info">
                  <span class="stat-number">{{ userOrders.filter(o => o.status === 'CLOSED').length }}</span>
                  <span class="stat-label">Closed</span>
                </div>
              </div>
              
              <div class="stat-card">
                <div class="stat-icon">
                  <i class="fas fa-rupee-sign"></i>
                </div>
                <div class="stat-info">
                  <span class="stat-number">₹{{ userOrders.reduce((sum, o) => sum + parseFloat(o.total_amount), 0).toLocaleString() }}</span>
                  <span class="stat-label">Total Volume</span>
                </div>
              </div>
            </div>
            
            <div class="orders-list">
              <div v-for="order in userOrders" :key="order.id" class="order-card">
                <div class="order-header">
                  <div class="order-id-section">
                    <span class="order-id">#{{ order.id }}</span>
                    <span class="order-date">{{ formatDateTime(order.created_at) }}</span>
                  </div>
                  <span class="order-status" :class="order.status.toLowerCase()">
                    <i class="fas" :class="getStatusIcon(order.status)"></i>
                    {{ order.status }}
                  </span>
                </div>
                
                <div class="order-content">
                  <div class="trade-info">
                    <div class="stock-info">
                      <span class="stock-symbol">{{ order.stock_symbol }}</span>
                      <span class="option-type" :class="order.option_type.toLowerCase()">
                        <i class="fas" :class="getOptionIcon(order.option_type)"></i>
                        {{ order.option_type }}
                      </span>
                    </div>
                    
                    <div class="action-info">
                      <span class="action" :class="order.action.toLowerCase()">
                        <i class="fas" :class="getActionIcon(order.action)"></i>
                        {{ order.action }}
                      </span>
                      <span class="quantity">{{ order.quantity }} units</span>
                    </div>
                  </div>
                  
                  <div class="price-info">
                    <div class="price-item">
                      <span class="label">Strike Price</span>
                      <span class="value">₹{{ order.strike_price }}</span>
                    </div>
                    <div class="price-item">
                      <span class="label">Current Price</span>
                      <span class="value">₹{{ getCurrentPrice(order.stock_symbol) }}</span>
                    </div>
                    <div class="price-item">
                      <span class="label">Total Amount</span>
                      <span class="value total">₹{{ order.total_amount }}</span>
                    </div>
                    <div v-if="order.status === 'COMPLETED'" class="price-item pnl-item">
                      <span class="label">Unrealized P&L</span>
                      <span class="value" :class="getPnLClass(calculateUnrealizedPnL(order))">
                        {{ getPnLText(calculateUnrealizedPnL(order)) }}
                      </span>
                    </div>
                    <div v-if="order.status === 'CLOSED'" class="price-item pnl-item">
                      <span class="label">Realized P&L</span>
                      <span class="value" :class="getPnLClass(order.pnl)">
                        {{ getPnLText(order.pnl) }}
                      </span>
                    </div>
                  </div>
                  
                  <div v-if="order.notes" class="order-notes">
                    <i class="fas fa-sticky-note"></i>
                    <span>{{ order.notes }}</span>
                  </div>
                  
                  <div v-if="order.status === 'COMPLETED'" class="order-actions">
                    <button 
                      class="exit-trade-btn" 
                      @click="exitTrade(order.id)"
                      :disabled="exitingTrade === order.id || !marketStatus.is_open"
                      :title="!marketStatus.is_open ? 'Trade exit only allowed during market hours' : ''"
                    >
                      <i class="fas fa-sign-out-alt" v-if="exitingTrade !== order.id"></i>
                      <i class="fas fa-spinner fa-spin" v-else></i>
                      {{ exitingTrade === order.id ? 'Exiting...' : 'Exit Trade' }}
                    </button>
                    <div v-if="!marketStatus.is_open" class="market-closed-notice">
                      <i class="fas fa-clock"></i>
                      <span>Exit trade available during market hours (9:15 AM - 3:30 PM IST)</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="modal-footer">
          <button class="btn btn-secondary" @click="closeOrdersModal">
            Close
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

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
      showOrdersModal: false,
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
        quantity: 1
      }
    }
  },
  computed: {
    filteredStocks() {
      let stocks = [...this.liveStocks];
      
      // Search filter
      if (this.searchQuery) {
        stocks = stocks.filter(stock => 
          stock.symbol.toLowerCase().includes(this.searchQuery.toLowerCase())
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
            return a.symbol.localeCompare(b.symbol);
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
    
    // Debug: Test balance loading immediately
    console.log('About to test balance loading...');
    setTimeout(() => {
      console.log('Testing balance load after 2 seconds...');
      this.loadUserBalance();
    }, 2000);
  },
  methods: {
    goBack() {
      this.$router.push({ name: 'ai_trading' });
    },
    async loadMarketData() {
      try {
        this.loading = true;
        const token = localStorage.getItem('access_token');
        
        const response = await axios.get('/api/truedata/dashboard', {
          headers: {
            'Authorization': `Bearer ${token}`,
            'Accept': 'application/json'
          }
        });

        if (response.data.success && response.data.data) {
          this.liveStocks = response.data.data.live_stocks || [];
          this.marketStatus = response.data.data.market_status?.status || 'CLOSED';
          this.lastUpdate = response.data.data.last_update;
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
      this.showSuccess('Market data refreshed');
    },
    searchStocks() {
      // Search functionality is handled by computed property
    },
    openTradeModal(stock, optionType, action) {
      this.tradeData = {
        stock: stock,
        optionType: optionType,
        action: action,
        strikePrice: optionType === 'CALL' ? (stock.ltp * 1.02) : (stock.ltp * 0.98),
        quantity: 1
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
        quantity: 1
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
    async executeTrade() {
      try {
        const token = localStorage.getItem('access_token');
        
        const tradePayload = {
          user_id: this.user.id,
          stock_symbol: this.tradeData.stock.symbol,
          option_type: this.tradeData.optionType,
          action: this.tradeData.action,
          strike_price: this.tradeData.strikePrice,
          quantity: this.tradeData.quantity,
          total_amount: this.tradeData.strikePrice * this.tradeData.quantity
        };

        const response = await axios.post('/api/ai-trading/execute-trade', tradePayload, {
          headers: {
            'Authorization': `Bearer ${token}`,
            'Accept': 'application/json',
            'Content-Type': 'application/json'
          }
        });

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
        this.showError('Failed to execute trade');
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
    async loadUserBalance() {
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
          const newBalance = parseFloat(response.data.balance) || 0;
          console.log('Live user balance updated:', newBalance);
          console.log('Formatted balance:', response.data.formatted_balance);
          
          // Force Vue reactivity update
          this.$set(this.user, 'balance', newBalance);
          // Alternative approach
          this.user = { ...this.user, balance: newBalance };
          
          console.log('User object after update:', this.user);
          this.showSuccess(`Balance updated: ${response.data.formatted_balance}`);
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
      console.log('View Orders clicked - using direct DOM approach');
      
      // Remove any existing orders modal
      const existingModal = document.getElementById('orders-modal');
      if (existingModal) {
        existingModal.remove();
      }
      
      // Create orders modal directly in DOM
      const modal = document.createElement('div');
      modal.id = 'orders-modal';
      modal.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.8);
        z-index: 99999;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
      `;
      
      // Get user orders data
      const orders = this.userOrders || [];
      const ordersHtml = orders.length > 0 ? 
        orders.map(order => `
          <div style="background: #1a1a2e; border: 1px solid #333; border-radius: 8px; padding: 20px; margin-bottom: 16px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
              <h4 style="color: white; margin: 0;">Order #${order.id}</h4>
              <span style="background: ${order.status === 'COMPLETED' ? '#00ff88' : order.status === 'PENDING' ? '#ffc107' : '#ff4444'}; color: black; padding: 4px 8px; border-radius: 4px; font-size: 12px;">${order.status}</span>
            </div>
            <div style="color: #e0e0e0; font-size: 14px;">
              <p><strong>Stock:</strong> ${order.stock_symbol}</p>
              <p><strong>Option:</strong> ${order.option_type} ${order.action}</p>
              <p><strong>Strike Price:</strong> ₹${order.strike_price}</p>
              <p><strong>Quantity:</strong> ${order.quantity}</p>
              <p><strong>Total Amount:</strong> ₹${order.total_amount}</p>
              <p><strong>Date:</strong> ${new Date(order.created_at).toLocaleString('en-IN')}</p>
              ${order.pnl ? `<p><strong>P&L:</strong> <span style="color: ${order.pnl >= 0 ? '#00ff88' : '#ff4444'}">₹${order.pnl}</span></p>` : ''}
            </div>
          </div>
        `).join('') :
        `<div style="text-align: center; color: #a0a0a0; padding: 40px;">
          <i class="fas fa-inbox" style="font-size: 48px; margin-bottom: 16px;"></i>
          <h3>No Orders Found</h3>
          <p>This user hasn't placed any trades yet.</p>
        </div>`;
      
      modal.innerHTML = `
        <div style="background: #1a1a2e; border-radius: 12px; max-width: 800px; width: 100%; max-height: 80vh; overflow-y: auto;">
          <div style="display: flex; justify-content: space-between; align-items: center; padding: 20px; border-bottom: 1px solid #333;">
            <div>
              <h2 style="color: white; margin: 0;">Trading Orders - ${this.user.name}</h2>
              <p style="color: #a0a0a0; margin: 4px 0 0 0;">Total Orders: ${orders.length}</p>
            </div>
            <button onclick="document.getElementById('orders-modal').remove()" style="background: #ff4444; color: white; border: none; padding: 8px 16px; border-radius: 6px; cursor: pointer;">
              <i class="fas fa-times"></i> Close
            </button>
          </div>
          <div style="padding: 20px;">
            ${ordersHtml}
          </div>
        </div>
      `;
      
      // Add click outside to close
      modal.addEventListener('click', (e) => {
        if (e.target === modal) {
          modal.remove();
        }
      });
      
      document.body.appendChild(modal);
      console.log('Orders modal created with direct DOM');
    },
    closeOrdersModal() {
      this.showOrdersModal = false;
    },

    formatDateTime(dateString) {
      return new Date(dateString).toLocaleString('en-IN');
    },
    showSuccess(message) {
      if (this.$toast && this.$toast.success) {
        this.$toast.success(message);
      } else {
        console.log('Success:', message);
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
      if (this.$toast && this.$toast.error) {
        this.$toast.error(message);
      } else {
        console.error('Error:', message);
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
}

/* Page Header */
.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24px;
  padding: 20px;
  background: linear-gradient(145deg, #101022, #0d0d1a);
  border: 1px solid rgba(0, 255, 136, 0.2);
  border-radius: 16px;
}

.header-content {
  display: flex;
  align-items: center;
  gap: 20px;
}

.back-btn {
  background: rgba(255, 255, 255, 0.1);
  border: 1px solid rgba(255, 255, 255, 0.2);
  color: white;
  padding: 10px 16px;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.3s ease;
}

.back-btn:hover {
  background: rgba(255, 255, 255, 0.2);
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
}

.header-actions {
  display: flex;
  gap: 12px;
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

/* Market Status */
.market-status {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24px;
  padding: 16px 20px;
  background: rgba(255, 255, 255, 0.05);
  border-radius: 12px;
  border: 1px solid rgba(255, 255, 255, 0.1);
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

/* Stocks Grid */
.stocks-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
  gap: 20px;
}

.stock-card {
  background: rgba(255, 255, 255, 0.05);
  backdrop-filter: blur(10px);
  border-radius: 16px;
  border: 1px solid rgba(255, 255, 255, 0.1);
  padding: 20px;
  transition: all 0.3s ease;
}

.stock-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 12px 40px rgba(0, 255, 136, 0.2);
}

.stock-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 16px;
}

.stock-info h3 {
  font-size: 20px;
  font-weight: bold;
  color: #00ff88;
  margin: 0;
}

.stock-price {
  font-size: 18px;
  font-weight: 600;
  color: white;
}

.stock-change {
  display: flex;
  align-items: center;
  gap: 4px;
  font-weight: 600;
  padding: 4px 8px;
  border-radius: 6px;
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
}

.modal-content {
  background: #1a1a2e;
  border-radius: 16px;
  border: 1px solid rgba(255, 255, 255, 0.1);
  max-width: 500px;
  width: 100%;
  max-height: 90vh;
  overflow-y: auto;
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
.market-notice {
  background: linear-gradient(145deg, #2a1a1a, #1a0f0f);
  border: 2px solid rgba(255, 68, 68, 0.3);
  border-radius: 12px;
  padding: 20px;
  margin-bottom: 24px;
  box-shadow: 0 8px 32px rgba(255, 68, 68, 0.1);
}

.notice-content {
  display: flex;
  align-items: center;
  gap: 16px;
}

.notice-content i {
  font-size: 24px;
  color: #ff4444;
  flex-shrink: 0;
}

.notice-text h3 {
  color: #ff4444;
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

.quantity-input-wrapper {
  display: flex;
  align-items: center;
  gap: 12px;
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

.btn {
  flex: 1;
  padding: 12px 20px;
  border: none;
  border-radius: 10px;
  cursor: pointer;
  font-weight: 600;
  transition: all 0.3s ease;
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

/* Responsive */
@media (max-width: 768px) {
  .stocks-grid {
    grid-template-columns: 1fr;
  }
  
  .filters-section {
    flex-direction: column;
    align-items: stretch;
  }
  
  .header-content {
    flex-direction: column;
    align-items: flex-start;
    gap: 12px;
  }
  
  .header-actions {
    flex-direction: column;
    width: 100%;
  }
  
  .modal-content {
    margin: 10px;
    max-width: none;
  }
}
</style>
