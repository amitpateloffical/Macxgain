<template>
  <div class="ai-trading-orders">
    <!-- Header -->
    <div class="page-header">
      <div class="header-content">
        <button class="back-btn" @click="goBack">
          <i class="fas fa-arrow-left"></i>
          Back to AI Trading Session
        </button>
        <div class="user-info">
          <h1 class="page-title">📋 Trading Orders</h1>
          <p class="page-subtitle">
            Orders for: <strong>{{ user.name || 'Loading...' }}</strong>
          </p>
          <div class="balance-breakdown">
            <div class="balance-item">
              <span class="balance-label">Available Balance:</span>
              <span class="balance-value available">₹{{ (user.balance || 0).toLocaleString() }}</span>
            </div>
            <div class="balance-item" v-if="user.total_balance">
              <span class="balance-label">Total Balance:</span>
              <span class="balance-value total">₹{{ (user.total_balance || 0).toLocaleString() }}</span>
            </div>
            <div class="balance-item" v-if="user.blocked_amount && user.blocked_amount > 0">
              <span class="balance-label">Blocked in Trades:</span>
              <span class="balance-value blocked">₹{{ (user.blocked_amount || 0).toLocaleString() }}</span>
            </div>
            <div class="balance-item" v-if="overallPnL !== 0">
              <span class="balance-label">Live P&L:</span>
              <span class="balance-value live-pnl" :class="overallPnL >= 0 ? 'profit' : 'loss'">
                {{ overallPnL >= 0 ? '+' : '' }}₹{{ (overallPnL || 0).toLocaleString() }}
              </span>
            </div>
          </div>
        </div>
      </div>
      <div class="header-actions">
        <button class="refresh-btn" @click="refreshAllData" :disabled="loading">
          <i class="fas fa-sync-alt" :class="{ 'fa-spin': loading }"></i>
          {{ loading ? 'Loading...' : 'Refresh All' }}
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
      </div>
    </div>

    <!-- Orders Summary -->
    <div class="orders-summary">
      <div class="summary-card">
        <div class="summary-item">
          <span class="summary-label">Total Orders</span>
          <span class="summary-value">{{ userOrders.length }}</span>
        </div>
        <div class="summary-item">
          <span class="summary-label">Completed</span>
          <span class="summary-value completed">{{ getOrdersByStatus('COMPLETED').length }}</span>
        </div>
        <div class="summary-item">
          <span class="summary-label">Pending</span>
          <span class="summary-value pending">{{ getOrdersByStatus('PENDING').length }}</span>
        </div>
        <div class="summary-item">
          <span class="summary-label">Closed</span>
          <span class="summary-value closed">{{ getOrdersByStatus('CLOSED').length }}</span>
        </div>
      </div>
    </div>

    <!-- P&L Analysis -->
    <div class="pnl-analysis">
      <div class="analysis-header">
        <h2>📊 Profit & Loss Analysis</h2>
        <div class="analysis-toggle">
          <button 
            class="toggle-btn" 
            :class="{ active: showDetailedAnalysis }"
            @click="showDetailedAnalysis = !showDetailedAnalysis"
          >
            <i class="fas fa-chart-line"></i>
            {{ showDetailedAnalysis ? 'Hide Details' : 'Show Details' }}
          </button>
        </div>
      </div>

      <!-- Overall P&L Summary -->
      <div class="pnl-summary">
        <div class="pnl-card total-pnl" :class="overallPnL >= 0 ? 'profit' : 'loss'">
          <div class="pnl-icon">
            <i class="fas" :class="overallPnL >= 0 ? 'fa-arrow-up' : 'fa-arrow-down'"></i>
          </div>
          <div class="pnl-info">
            <span class="pnl-label">Overall P&L</span>
            <span class="pnl-value">
              {{ overallPnL >= 0 ? '+' : '' }}₹{{ overallPnL?.toLocaleString() }}
            </span>
            <span class="pnl-percentage">
              ({{ overallPnLPercentage >= 0 ? '+' : '' }}{{ overallPnLPercentage?.toFixed(2) }}%)
            </span>
          </div>
        </div>

        <div class="pnl-stats">
          <div class="stat-item">
            <span class="stat-label">Total Invested</span>
            <span class="stat-value">₹{{ totalInvested?.toLocaleString() }}</span>
          </div>
          <div class="stat-item">
            <span class="stat-label">Total Return</span>
            <span class="stat-value">₹{{ totalReturn?.toLocaleString() }}</span>
          </div>
          <div class="stat-item">
            <span class="stat-label">Win Rate</span>
            <span class="stat-value" :class="winRate >= 50 ? 'profit' : 'loss'">
              {{ winRate?.toFixed(1) }}%
            </span>
          </div>
          <div class="stat-item">
            <span class="stat-label">Avg Profit per Trade</span>
            <span class="stat-value" :class="avgProfitPerTrade >= 0 ? 'profit' : 'loss'">
              {{ avgProfitPerTrade >= 0 ? '+' : '' }}₹{{ avgProfitPerTrade?.toLocaleString() }}
            </span>
          </div>
        </div>
      </div>

      <!-- Detailed Analysis -->
      <div v-if="showDetailedAnalysis" class="detailed-analysis">
        <div class="analysis-grid">
          <!-- Profit Trades -->
          <div class="analysis-section profit-section">
            <div class="section-header">
              <i class="fas fa-trophy"></i>
              <h3>Profitable Trades ({{ profitableTrades.length }})</h3>
            </div>
            <div class="trades-list">
              <div v-for="trade in profitableTrades" :key="trade.id" class="trade-item profit">
                <div class="trade-info">
                  <span class="trade-symbol">{{ trade.stock_symbol }}</span>
                  <span class="trade-type">{{ trade.option_type }} {{ trade.action }}</span>
                </div>
                <div class="trade-pnl">
                  <span class="pnl-amount">+₹{{ trade.pnl?.toLocaleString() }}</span>
                  <span class="trade-date">{{ formatDate(trade.closed_at || trade.created_at) }}</span>
                </div>
              </div>
            </div>
            <div class="section-total">
              <span>Total Profit: +₹{{ totalProfit?.toLocaleString() }}</span>
            </div>
          </div>

          <!-- Loss Trades -->
          <div class="analysis-section loss-section">
            <div class="section-header">
              <i class="fas fa-chart-line-down"></i>
              <h3>Loss Trades ({{ lossTrades.length }})</h3>
            </div>
            <div class="trades-list">
              <div v-for="trade in lossTrades" :key="trade.id" class="trade-item loss">
                <div class="trade-info">
                  <span class="trade-symbol">{{ trade.stock_symbol }}</span>
                  <span class="trade-type">{{ trade.option_type }} {{ trade.action }}</span>
                </div>
                <div class="trade-pnl">
                  <span class="pnl-amount">{{ trade.pnl >= 0 ? '+' : '' }}₹{{ trade.pnl?.toLocaleString() }}</span>
                  <span class="trade-date">{{ formatDate(trade.closed_at || trade.created_at) }}</span>
                </div>
              </div>
            </div>
            <div class="section-total">
              <span>Total Loss: ₹{{ Math.abs(totalLoss)?.toLocaleString() }}</span>
            </div>
          </div>

          <!-- Active Trades -->
          <div class="analysis-section active-section">
            <div class="section-header">
              <i class="fas fa-clock"></i>
              <h3>Active Trades ({{ activeTrades.length }})</h3>
            </div>
            <div class="trades-list">
              <div v-for="trade in activeTrades" :key="trade.id" class="trade-item active">
                <div class="trade-info">
                  <span class="trade-symbol">{{ trade.stock_symbol }}</span>
                  <span class="trade-type">{{ trade.option_type }} {{ trade.action }}</span>
                  <span class="strike-price">Strike: ₹{{ trade.strike_price }}</span>
                </div>
                <div class="trade-pnl">
                  <div class="price-info">
                    <span class="current-price" v-if="getCurrentPrice(trade.stock_symbol) > 0">
                      <i class="fas fa-circle live-dot" :class="marketStatus.is_open ? 'live' : 'offline'"></i>
                      Current: ₹{{ (getCurrentPrice(trade.stock_symbol) || 0).toFixed(2) }}
                    </span>
                    <span class="invested-amount">Invested: ₹{{ trade.total_amount?.toLocaleString() }}</span>
                  </div>
                  <div class="live-pnl" :class="getLivePnLValue(trade) >= 0 ? 'profit' : 'loss'">
                    <i class="fas fa-chart-line"></i>
                    Live P&L: {{ getLivePnLValue(trade) >= 0 ? '+' : '' }}₹{{ (getLivePnLValue(trade) || 0).toFixed(2) }}
                    <span class="pnl-percentage" v-if="getLivePnLPercentage(trade) !== 0">
                      ({{ getLivePnLPercentage(trade) >= 0 ? '+' : '' }}{{ getLivePnLPercentage(trade).toFixed(2) }}%)
                    </span>
                    <i class="fas fa-sync-alt live-refresh" v-if="marketStatus.is_open" title="Live updates every 3 seconds"></i>
                  </div>
                  <span class="trade-date">{{ formatDate(trade.created_at) }}</span>
                </div>
              </div>
            </div>
            <div class="section-total">
              <span>Total Invested: ₹{{ totalActiveInvestment?.toLocaleString() }}</span>
              <span class="live-total-pnl" :class="calculateActiveTradesPnL() >= 0 ? 'profit' : 'loss'">
                <i class="fas fa-chart-line"></i>
                Live P&L: {{ calculateActiveTradesPnL() >= 0 ? '+' : '' }}₹{{ (calculateActiveTradesPnL() || 0).toFixed(2) }}
                <span class="pnl-percentage" v-if="totalActiveInvestment > 0">
                  ({{ calculateActiveTradesPnL() >= 0 ? '+' : '' }}{{ ((calculateActiveTradesPnL() / totalActiveInvestment) * 100).toFixed(2) }}%)
                </span>
                <i class="fas fa-sync-alt live-refresh" v-if="marketStatus.is_open" title="Live portfolio updates"></i>
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Orders List -->
    <div class="orders-container">
      <div class="orders-header">
        <h2>Trading Orders</h2>
        <div class="filter-controls">
          <select v-model="statusFilter" class="filter-select">
            <option value="">All Status</option>
            <option value="COMPLETED">Completed</option>
            <option value="PENDING">Pending</option>
            <option value="CANCELLED">Cancelled</option>
            <option value="CLOSED">Closed</option>
          </select>
          <select v-model="sortBy" class="filter-select">
            <option value="created_at">Date (Newest)</option>
            <option value="created_at_old">Date (Oldest)</option>
            <option value="total_amount">Amount (High to Low)</option>
            <option value="total_amount_low">Amount (Low to High)</option>
          </select>
        </div>
      </div>

      <div v-if="loading" class="loading-container">
        <div class="loading-spinner"></div>
        <p>Loading orders...</p>
      </div>

      <div v-else-if="filteredOrders.length === 0" class="empty-state">
        <i class="fas fa-inbox"></i>
        <h3>No Orders Found</h3>
        <p>{{ statusFilter ? `No orders with status: ${statusFilter}` : 'This user hasn\'t placed any trades yet.' }}</p>
      </div>

      <div v-else class="orders-list">
        <div 
          v-for="order in filteredOrders" 
          :key="order.id" 
          class="order-card"
          :class="order.status.toLowerCase()"
        >
          <div class="order-header">
            <div class="order-info">
              <h3 class="order-symbol">{{ order.stock_symbol }}</h3>
              <span class="order-id">#{{ order.id }}</span>
            </div>
            <div class="order-status">
              <i :class="getStatusIcon(order.status)"></i>
              <span>{{ order.status }}</span>
            </div>
          </div>

          <div class="order-details">
            <div class="detail-row">
              <span class="detail-label">Option Type:</span>
              <span class="detail-value option-type" :class="order.option_type.toLowerCase()">
                <i :class="getOptionIcon(order.option_type)"></i>
                {{ order.option_type }}
              </span>
            </div>
            <div class="detail-row">
              <span class="detail-label">Action:</span>
              <span class="detail-value action-type" :class="order.action.toLowerCase()">
                <i :class="getActionIcon(order.action)"></i>
                {{ order.action }}
              </span>
            </div>
            <div class="detail-row">
              <span class="detail-label">Strike Price:</span>
              <span class="detail-value">₹{{ order.strike_price }}</span>
            </div>
            <div class="detail-row">
              <span class="detail-label">Quantity:</span>
              <span class="detail-value">{{ order.quantity }}</span>
            </div>
            <div class="detail-row">
              <span class="detail-label">Total Amount:</span>
              <span class="detail-value amount">₹{{ order.total_amount?.toLocaleString() }}</span>
            </div>
            <div class="detail-row">
              <span class="detail-label">Date:</span>
              <span class="detail-value">{{ formatDateTime(order.created_at) }}</span>
            </div>
            <div v-if="order.exit_price" class="detail-row">
              <span class="detail-label">Exit Price:</span>
              <span class="detail-value">₹{{ order.exit_price }}</span>
            </div>
            <!-- P&L Display - Live for active trades, Final for closed trades -->
            <div v-if="order.status === 'CLOSED' && order.pnl !== null && order.pnl !== undefined" class="detail-row">
              <span class="detail-label">P&L (Final):</span>
              <span class="detail-value pnl" :class="order.pnl >= 0 ? 'profit' : 'loss'">
                {{ order.pnl >= 0 ? '+' : '' }}₹{{ order.pnl?.toLocaleString() }}
              </span>
            </div>
            <div v-else-if="order.status === 'COMPLETED'" class="detail-row live-pnl-row">
              <span class="detail-label">
                <i class="fas fa-chart-line"></i>
                Live P&L:
              </span>
              <span class="detail-value pnl live-pnl-value" :class="getLivePnLValue(order) >= 0 ? 'profit' : 'loss'">
                <span class="pnl-amount">
                  {{ getLivePnLValue(order) >= 0 ? '+' : '' }}₹{{ (getLivePnLValue(order) || 0).toFixed(2) }}
                </span>
                <span class="pnl-percentage" v-if="order.total_amount > 0">
                  ({{ getLivePnLValue(order) >= 0 ? '+' : '' }}{{ ((getLivePnLValue(order) / order.total_amount) * 100).toFixed(2) }}%)
                </span>
                <span class="live-indicator" :class="marketStatus.is_open ? 'live' : 'offline'">
                  <i class="fas fa-circle"></i>
                  {{ marketStatus.is_open ? 'LIVE' : 'OFFLINE' }}
                  <span class="update-time" v-if="lastUpdate">{{ lastUpdate }}</span>
                </span>
              </span>
            </div>
            <!-- Current Option Price for active trades -->
            <div v-if="order.status === 'COMPLETED' && getCurrentPrice(order.stock_symbol) > 0" class="detail-row">
              <span class="detail-label">Current Option Price:</span>
              <span class="detail-value current-price">
                ₹{{ formatPrice(getCurrentOptionPrice(order.strike_price, order.option_type, order.stock_symbol)) }}
              </span>
            </div>
            <!-- Current Stock Price for reference -->
            <div v-if="order.status === 'COMPLETED' && getCurrentPrice(order.stock_symbol) > 0" class="detail-row">
              <span class="detail-label">Current Stock Price:</span>
              <span class="detail-value current-price">
                ₹{{ (getCurrentPrice(order.stock_symbol) || 0).toFixed(2) }}
              </span>
            </div>
          </div>

          <div v-if="order.status === 'COMPLETED'" class="order-actions">
            <button 
              class="exit-trade-btn" 
              @click="exitTrade(order.id)"
              :disabled="exitingTrade === order.id"
              :title="'Exit trade - 24/7 trading enabled'"
            >
              <i class="fas fa-sign-out-alt" v-if="exitingTrade !== order.id"></i>
              <i class="fas fa-spinner fa-spin" v-else></i>
              {{ exitingTrade === order.id ? 'Exiting...' : 'Exit Trade' }}
            </button>
            <div class="trading-enabled-notice">
              <i class="fas fa-rocket"></i>
              <span>24/7 trading enabled - Exit anytime!</span>
            </div>
          </div>
          
          <div v-else-if="order.status === 'CLOSED'" class="order-actions">
            <div class="trade-closed-info">
              <i class="fas fa-check-circle"></i>
              <span>Trade Closed - {{ order.closed_at ? formatDateTime(order.closed_at) : 'Recently' }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from '../../axios';
import Swal from 'sweetalert2';

export default {
  name: 'AITradingOrders',
  data() {
    return {
      user: {},
      loading: false,
      userOrders: [],
      exitingTrade: null,
      statusFilter: '',
      sortBy: 'created_at',
      showDetailedAnalysis: false,
      liveStocks: {},
      autoRefreshInterval: null,
      marketStatus: {
        is_open: false,
        status: 'CLOSED',
        current_time: '',
        next_open_time: null
      },
      lastUpdate: null,
      livePnLValues: {} // Store live P&L values for each order
    }
  },
  computed: {
    filteredOrders() {
      let orders = [...this.userOrders];
      
      // Filter by status
      if (this.statusFilter) {
        orders = orders.filter(order => order.status === this.statusFilter);
      }
      
      // Sort orders
      orders.sort((a, b) => {
        switch (this.sortBy) {
          case 'created_at':
            return new Date(b.created_at) - new Date(a.created_at);
          case 'created_at_old':
            return new Date(a.created_at) - new Date(b.created_at);
          case 'total_amount':
            return (b.total_amount || 0) - (a.total_amount || 0);
          case 'total_amount_low':
            return (a.total_amount || 0) - (b.total_amount || 0);
          default:
            return new Date(b.created_at) - new Date(a.created_at);
        }
      });
      
      return orders;
    },
    
    // P&L Analysis Computed Properties
    closedTrades() {
      return this.userOrders.filter(order => order.status === 'CLOSED' && order.pnl !== null && order.pnl !== undefined);
    },
    
    profitableTrades() {
      return this.closedTrades.filter(trade => trade.pnl > 0);
    },
    
    lossTrades() {
      return this.closedTrades.filter(trade => trade.pnl < 0);
    },
    
    activeTrades() {
      return this.userOrders.filter(order => order.status === 'COMPLETED');
    },
    
    totalProfit() {
      return this.profitableTrades.reduce((sum, trade) => sum + parseFloat(trade.pnl || 0), 0);
    },
    
    totalLoss() {
      // Loss trades already have negative P&L, so we sum them as is
      return this.lossTrades.reduce((sum, trade) => sum + parseFloat(trade.pnl || 0), 0);
    },
    
    overallPnL() {
      // Calculate P&L including live prices for active trades
      const closedPnL = this.totalProfit + this.totalLoss;
      const activePnL = this.calculateActiveTradesPnL();
      return closedPnL + activePnL;
    },
    
    totalInvested() {
      // Only count completed trades (money actually invested)
      return this.userOrders
        .filter(order => order.status === 'COMPLETED' || order.status === 'CLOSED')
        .reduce((sum, order) => sum + parseFloat(order.total_amount || 0), 0);
    },
    
    totalReturn() {
      // Total return = Initial investment + P&L (including live prices for active trades)
      return this.totalInvested + this.overallPnL;
    },
    
    overallPnLPercentage() {
      if (this.totalInvested === 0) return 0;
      const percentage = (this.overallPnL / this.totalInvested) * 100;
      return isNaN(percentage) ? 0 : percentage;
    },
    
    winRate() {
      const totalClosedTrades = this.closedTrades.length;
      if (totalClosedTrades === 0) return 0;
      return (this.profitableTrades.length / totalClosedTrades) * 100;
    },
    
    avgProfitPerTrade() {
      const totalClosedTrades = this.closedTrades.length;
      if (totalClosedTrades === 0) return 0;
      const average = this.overallPnL / totalClosedTrades;
      return isNaN(average) ? 0 : average;
    },
    
    totalActiveInvestment() {
      return this.activeTrades.reduce((sum, trade) => sum + parseFloat(trade.total_amount || 0), 0);
    },
    
    // Updated balance after P&L
    updatedBalance() {
      // Current balance + overall P&L from closed trades
      return parseFloat(this.user.balance || 0) + this.overallPnL;
    }
  },
  mounted() {
    try {
      console.log('AITradingOrders component mounted');
      
      // Get user data from route query parameters
      this.user = {
        id: this.$route.query.userId,
        name: this.$route.query.userName,
        balance: parseFloat(this.$route.query.userBalance) || 0,
        email: this.$route.query.userEmail
      };
      
      console.log('User data:', this.user);
      
      if (!this.user.id) {
        console.error('No user ID found in route query');
        this.showError('User data not found. Please go back and try again.');
        this.goBack();
        return;
      }
      
      // Load data sequentially to avoid race conditions
      this.loadUserOrders().then(() => {
        this.loadUserBalance();
        this.loadMarketStatus();
        this.loadMarketData();
        
        // Auto-refresh every 10 seconds
        this.startAutoRefresh();
      }).catch(error => {
        console.error('Error in mounted lifecycle:', error);
        this.showError('Failed to load initial data');
      });
      
    } catch (error) {
      console.error('Error in mounted lifecycle:', error);
      this.showError('Failed to initialize component');
    }
  },
  beforeDestroy() {
    // Clean up auto-refresh interval when component is destroyed
    this.stopAutoRefresh();
  },
  methods: {
    goBack() {
      this.$router.push({
        name: 'ai_trading_session',
        query: {
          userId: this.user.id,
          userName: this.user.name,
          userBalance: this.user.balance,
          userEmail: this.user.email
        }
      });
    },
    async loadUserOrders() {
      try {
        this.loading = true;
        
        const response = await axios.get(`ai-trading/user-orders/${this.user.id}`);

        if (response.data.success) {
          this.userOrders = response.data.orders || [];
          this.lastUpdate = new Date().toLocaleTimeString();
        }
      } catch (error) {
        console.error('Error loading user orders:', error);
        this.showError('Failed to load orders');
      } finally {
        this.loading = false;
      }
    },
    async loadUserBalance() {
      try {
        const response = await axios.get(`ai-trading/user-balance/${this.user.id}`);

        if (response.data.success) {
          // Update all balance details
          this.user.balance = response.data.balance; // Available balance
          this.user.total_balance = response.data.total_balance; // Total wallet balance
          this.user.blocked_amount = response.data.blocked_amount; // Amount blocked in trades
          
          console.log('Balance updated:', {
            available: response.data.balance,
            total: response.data.total_balance,
            blocked: response.data.blocked_amount
          });
        }
      } catch (error) {
        console.error('Error loading user balance:', error);
      }
    },
    async loadMarketStatus() {
      try {
        const response = await axios.get('truedata/market-status');

        if (response.data.success && response.data.data) {
          // Map the API response to our marketStatus object
          const apiData = response.data.data;
          this.marketStatus = {
            is_open: apiData.status === 'OPEN' && apiData.is_live,
            status: apiData.status,
            current_time: apiData.current_time,
            session: apiData.session,
            next_change: apiData.next_change,
            trading_hours: apiData.trading_hours
          };
          console.log('Market status loaded:', this.marketStatus);
        }
      } catch (error) {
        console.error('Error loading market status:', error);
        // Use default market status
        this.marketStatus = {
          is_open: false,
          status: 'CLOSED',
          current_time: new Date().toLocaleTimeString('en-IN'),
          next_open_time: null
        };
      }
    },
    async loadMarketData() {
      try {
        const response = await axios.get('truedata/live-data', {
          params: { _t: Date.now() } // Cache busting parameter
        });

        if (response.data.success && response.data.data) {
          const oldNiftyPrice = this.getCurrentPrice('NIFTY 50');
          this.liveStocks = response.data.data;
          const newNiftyPrice = this.getCurrentPrice('NIFTY 50');
          
          this.lastUpdate = new Date().toLocaleTimeString();
          console.log('Live market data loaded:', Object.keys(this.liveStocks).length, 'symbols');
          
          // Log price changes
          if (oldNiftyPrice !== newNiftyPrice && oldNiftyPrice > 0) {
            console.log(`NIFTY 50 price changed: ₹${oldNiftyPrice} → ₹${newNiftyPrice} (${newNiftyPrice > oldNiftyPrice ? '+' : ''}${(newNiftyPrice - oldNiftyPrice).toFixed(2)})`);
          }
        }
      } catch (error) {
        console.error('Error loading live market data:', error);
      }
    },
    async refreshMarketData() {
      await this.loadMarketData();
      this.showSuccess('Market data refreshed');
    },
    updateLivePrices() {
      // Update live prices without reloading orders
      // This method will be called by auto-refresh to update prices only
      console.log('Updating live prices for active trades...');
      // The liveStocks data is already updated by loadMarketData()
      // P&L will be recalculated automatically through computed properties
    },
    startAutoRefresh() {
      // Auto-refresh market data every 3 seconds for real-time P&L updates
      console.log('Starting enhanced auto-refresh for live P&L updates...');
      this.autoRefreshInterval = setInterval(async () => {
        try {
          // Refresh market data first
          await this.loadMarketData();
          await this.loadMarketStatus();
          
          // Clear cached P&L values to force recalculation
          this.livePnLValues = {};
          
          // Recalculate live P&L values with fresh market data
          await this.loadLivePnLValues();
          
          // Force reactivity update for computed properties
          this.$forceUpdate();
          
          console.log('Live P&L data refreshed - Market:', this.marketStatus.is_open ? 'OPEN' : 'CLOSED');
          console.log('Updated P&L cache:', Object.keys(this.livePnLValues).length, 'trades');
        } catch (error) {
          console.error('Error in enhanced auto-refresh:', error);
        }
      }, 3000); // 3 seconds interval for real-time updates
    },
    stopAutoRefresh() {
      if (this.autoRefreshInterval) {
        clearInterval(this.autoRefreshInterval);
        this.autoRefreshInterval = null;
        console.log('Auto-refresh stopped');
      }
    },
    getCurrentPrice(symbol) {
      // Get current market price for a symbol
      try {
        if (this.liveStocks && this.liveStocks[symbol] && this.liveStocks[symbol].ltp) {
          const price = parseFloat(this.liveStocks[symbol].ltp);
          return isNaN(price) ? 0 : price;
        }
        return 0;
      } catch (error) {
        console.error('Error getting current price for', symbol, ':', error);
        return 0;
      }
    },
    async getCurrentOptionPrice(strikePrice, optionType, stockSymbol = 'NIFTY 50') {
      // Get current option price with live underlying price
      try {
        // Map symbol names for API compatibility
        let apiSymbol = stockSymbol;
        if (stockSymbol === 'NIFTY 50') {
          apiSymbol = 'NIFTY';
        } else if (stockSymbol === 'BANK NIFTY') {
          apiSymbol = 'BANKNIFTY';
        }
        
        // Get live underlying price from cached market data
        const currentUnderlyingPrice = this.getCurrentPrice(stockSymbol);
        
        console.log(`Getting ${optionType} option price for strike ${strikePrice} of ${stockSymbol} (API: ${apiSymbol}) with underlying: ₹${currentUnderlyingPrice}`);
        
        const response = await axios.get('/api/truedata/options/current-price', {
          params: {
            symbol: apiSymbol,
            strike_price: strikePrice,
            option_type: optionType,
            underlying_price: currentUnderlyingPrice // Pass live underlying price
          }
        });
        
        if (response.data.success && response.data.data) {
          const price = parseFloat(response.data.data.current_price) || 0;
          console.log(`Found ${optionType} option for strike ${strikePrice}: ₹${price} (underlying: ₹${currentUnderlyingPrice})`);
          return price;
        } else {
          console.log(`No ${optionType} option price found for strike ${strikePrice}`);
          return 0; // Return 0 instead of null to prevent .toFixed() errors
        }
      } catch (error) {
        console.error('Error getting current option price:', error);
        return 0; // Return 0 instead of null to prevent .toFixed() errors
      }
    },
    getLivePnLValue(order) {
      // Get cached live P&L value for an order
      const orderId = order.id || order.order_id;
      return this.livePnLValues[orderId] || 0;
    },
    async loadLivePnLValues() {
      // Load live P&L values for all active trades with enhanced error handling
      try {
        const activeTrades = this.userOrders.filter(order => order.status === 'COMPLETED');
        console.log(`Calculating live P&L for ${activeTrades.length} active trades...`);
        
        // Process trades in parallel for better performance
        const pnlPromises = activeTrades.map(async (trade) => {
          try {
            const livePnL = await this.getLivePnL(trade);
            const orderId = trade.id || trade.order_id;
            
            // Update with Vue's reactivity system (Vue 3 compatible)
            this.$set ? this.$set(this.livePnLValues, orderId, livePnL) : (this.livePnLValues[orderId] = livePnL);
            
            console.log(`Trade #${orderId}: Live P&L = ₹${livePnL.toFixed(2)}`);
            return { orderId, livePnL };
          } catch (error) {
            console.error(`Error calculating P&L for trade #${trade.id}:`, error);
            return { orderId: trade.id, livePnL: 0 };
          }
        });
        
        await Promise.all(pnlPromises);
        console.log('Live P&L values updated for all active trades');
        console.log('Current livePnLValues cache:', this.livePnLValues);
        
      } catch (error) {
        console.error('Error loading live P&L values:', error);
      }
    },
    calculateActiveTradesPnL() {
      // Calculate total P&L for active trades using cached live P&L values
      try {
        let totalActivePnL = 0;
        
        this.activeTrades.forEach(trade => {
          const livePnL = this.getLivePnLValue(trade);
          totalActivePnL += livePnL;
        });
        
        return totalActivePnL;
      } catch (error) {
        console.error('Error calculating active trades P&L:', error);
        return 0;
      }
    },
    async getLivePnL(trade) {
      // Calculate live P&L for a specific trade
      try {
        const currentPrice = this.getCurrentPrice(trade.stock_symbol);
        if (currentPrice <= 0) return 0;
        
        // Validate trade data
        if (!trade || !trade.option_type || !trade.action || !trade.strike_price || !trade.quantity || !trade.total_amount) {
          return 0;
        }
        
        // Get current option price from option chain data
        const currentOptionPrice = await this.getCurrentOptionPrice(trade.strike_price, trade.option_type, trade.stock_symbol);
        const entryPremium = trade.total_amount / trade.quantity;
        
        let pnl = 0;
        
        if (currentOptionPrice > 0) {
          // Use real option price for calculation
          if (trade.action === 'BUY') {
            // Bought option: P&L = (Current Option Price - Entry Premium) * Quantity
            const pnlPerShare = currentOptionPrice - entryPremium;
            pnl = pnlPerShare * trade.quantity;
          } else if (trade.action === 'SELL') {
            // Sold option: P&L = (Entry Premium - Current Option Price) * Quantity
            const pnlPerShare = entryPremium - currentOptionPrice;
            pnl = pnlPerShare * trade.quantity;
          }
        } else {
          // Fallback to intrinsic value calculation
          if (trade.option_type === 'CALL') {
            if (trade.action === 'BUY') {
              const intrinsicValue = Math.max(0, currentPrice - trade.strike_price);
              const grossPnL = intrinsicValue * trade.quantity;
              pnl = grossPnL - trade.total_amount;
            } else if (trade.action === 'SELL') {
              const intrinsicValue = Math.max(0, currentPrice - trade.strike_price);
              const grossPnL = intrinsicValue * trade.quantity;
              pnl = trade.total_amount - grossPnL;
            }
          } else if (trade.option_type === 'PUT') {
            if (trade.action === 'BUY') {
              const intrinsicValue = Math.max(0, trade.strike_price - currentPrice);
              const grossPnL = intrinsicValue * trade.quantity;
              pnl = grossPnL - trade.total_amount;
            } else if (trade.action === 'SELL') {
              const intrinsicValue = Math.max(0, trade.strike_price - currentPrice);
              const grossPnL = intrinsicValue * trade.quantity;
              pnl = trade.total_amount - grossPnL;
            }
          }
        }
        
        // Ensure we return a valid number
        const result = parseFloat(pnl) || 0;
        return isNaN(result) ? 0 : result;
        
      } catch (error) {
        console.error('Error calculating live P&L:', error);
        return 0;
      }
    },
    getPnLPercentage(trade) {
      // Calculate P&L percentage based on invested amount
      try {
        if (!trade || !trade.total_amount || trade.total_amount === 0) return 0;
        
        const livePnL = this.getLivePnL(trade);
        const percentage = (livePnL / trade.total_amount) * 100;
        
        return isNaN(percentage) ? 0 : percentage;
      } catch (error) {
        console.error('Error calculating P&L percentage:', error);
        return 0;
      }
    },
    getLivePnLPercentage(trade) {
      // Calculate live P&L percentage using cached values
      try {
        if (!trade || !trade.total_amount || trade.total_amount === 0) return 0;
        
        const livePnL = this.getLivePnLValue(trade);
        const percentage = (livePnL / trade.total_amount) * 100;
        
        return isNaN(percentage) ? 0 : percentage;
      } catch (error) {
        console.error('Error calculating live P&L percentage:', error);
        return 0;
      }
    },
    async refreshAllData() {
      this.loading = true;
      try {
        await Promise.all([
          this.loadUserOrders(),
          this.loadUserBalance(),
          this.loadMarketStatus(),
          this.loadMarketData()
        ]);
        this.showSuccess('All data refreshed successfully');
      } catch (error) {
        this.showError('Failed to refresh some data');
      } finally {
        this.loading = false;
      }
    },
    async exitTrade(orderId) {
      try {
        this.exitingTrade = orderId;
        
        const response = await axios.post(`ai-trading/orders/${orderId}/exit`);

        if (response.data.success) {
          this.showSuccess('Trade exited successfully');
          await this.loadUserOrders();
          await this.loadUserBalance();
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
    getOrdersByStatus(status) {
      return this.userOrders.filter(order => order.status === status);
    },
    formatDateTime(dateString) {
      return new Date(dateString).toLocaleString('en-IN');
    },
    formatDate(dateString) {
      return new Date(dateString).toLocaleDateString('en-IN');
    },
    formatPrice(price) {
      // Safe price formatting that handles null/undefined values
      try {
        const numPrice = parseFloat(price) || 0;
        return numPrice.toFixed(2);
      } catch (error) {
        console.error('Error formatting price:', price, error);
        return '0.00';
      }
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
    }
  },
  beforeUnmount() {
    // Cleanup auto-refresh interval
    if (this.autoRefreshInterval) {
      clearInterval(this.autoRefreshInterval);
    }
  }
}
</script>

<style scoped>
/* AI Trading Orders Page Styles */
.ai-trading-orders {
  background-color: #0d0d1a;
  color: white;
  min-height: 100vh;
  padding: 20px;
  padding-bottom: 120px;
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
  background: linear-gradient(145deg, #1a1a2e, #16213e);
  color: #00ff88;
  border: 1px solid #00ff88;
  padding: 12px 20px;
  border-radius: 8px;
  cursor: pointer;
  font-size: 14px;
  font-weight: 500;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  gap: 8px;
}

.back-btn:hover {
  background: #00ff88;
  color: #0d0d1a;
  transform: translateY(-2px);
}

.page-title {
  font-size: 28px;
  font-weight: bold;
  color: #00ff88;
  margin: 0 0 8px 0;
}

.page-subtitle {
  color: rgba(255, 255, 255, 0.7);
  margin: 0;
  font-size: 16px;
}

.balance-update {
  display: inline-block;
  margin-left: 8px;
  padding: 4px 8px;
  border-radius: 6px;
  font-size: 14px;
  font-weight: 600;
}

.balance-update.profit {
  background: rgba(0, 255, 136, 0.2);
  color: #00ff88;
  border: 1px solid rgba(0, 255, 136, 0.3);
}

.balance-update.loss {
  background: rgba(255, 68, 68, 0.2);
  color: #ff4444;
  border: 1px solid rgba(255, 68, 68, 0.3);
}

.header-actions {
  display: flex;
  gap: 12px;
}

.refresh-btn {
  background: linear-gradient(145deg, #1a1a2e, #16213e);
  color: white;
  border: 1px solid #333;
  padding: 12px 16px;
  border-radius: 8px;
  cursor: pointer;
  font-size: 14px;
  font-weight: 500;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  gap: 8px;
}

.refresh-btn:hover:not(:disabled) {
  background: linear-gradient(145deg, #16213e, #1a1a2e);
  border-color: #00ff88;
  color: #00ff88;
}

.refresh-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

/* Market Status */
.market-status {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24px;
  padding: 16px 20px;
  background: linear-gradient(145deg, #1a1a2e, #16213e);
  border-radius: 12px;
  border: 1px solid #333;
}

.status-indicator {
  display: flex;
  align-items: center;
  gap: 12px;
  font-weight: 600;
}

.status-dot {
  width: 12px;
  height: 12px;
  border-radius: 50%;
  background: #ff4444;
}

.status-indicator.open .status-dot {
  background: #00ff88;
  box-shadow: 0 0 10px rgba(0, 255, 136, 0.5);
}

.market-time {
  color: #a0a0a0;
  font-size: 14px;
}

.last-update {
  color: #a0a0a0;
  font-size: 14px;
}

/* Orders Summary */
.orders-summary {
  margin-bottom: 24px;
}

/* P&L Analysis */
.pnl-analysis {
  margin-bottom: 24px;
  background: linear-gradient(145deg, #1a1a2e, #16213e);
  border-radius: 12px;
  border: 1px solid #333;
  overflow: hidden;
}

.analysis-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px;
  border-bottom: 1px solid #333;
  background: linear-gradient(145deg, #101022, #0d0d1a);
}

.analysis-header h2 {
  margin: 0;
  color: #00ff88;
  font-size: 20px;
}

.toggle-btn {
  background: linear-gradient(145deg, #1a1a2e, #16213e);
  color: white;
  border: 1px solid #333;
  padding: 10px 16px;
  border-radius: 8px;
  cursor: pointer;
  font-size: 14px;
  font-weight: 500;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  gap: 8px;
}

.toggle-btn:hover {
  background: linear-gradient(145deg, #16213e, #1a1a2e);
  border-color: #00ff88;
  color: #00ff88;
}

.toggle-btn.active {
  background: linear-gradient(145deg, #00ff88, #00cc6a);
  color: #0d0d1a;
  border-color: #00ff88;
}

.pnl-summary {
  padding: 20px;
  display: flex;
  gap: 20px;
  align-items: center;
}

.pnl-card {
  display: flex;
  align-items: center;
  gap: 16px;
  padding: 20px;
  border-radius: 12px;
  background: rgba(0, 0, 0, 0.3);
  border: 2px solid;
  flex: 1;
}

.pnl-card.profit {
  border-color: #00ff88;
  background: rgba(0, 255, 136, 0.1);
}

.pnl-card.loss {
  border-color: #ff4444;
  background: rgba(255, 68, 68, 0.1);
}

.pnl-icon {
  width: 48px;
  height: 48px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 20px;
}

.pnl-card.profit .pnl-icon {
  background: #00ff88;
  color: #0d0d1a;
}

.pnl-card.loss .pnl-icon {
  background: #ff4444;
  color: white;
}

.pnl-info {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.pnl-label {
  color: #a0a0a0;
  font-size: 14px;
}

.pnl-value {
  font-size: 24px;
  font-weight: bold;
  color: white;
}

.pnl-card.profit .pnl-value {
  color: #00ff88;
}

.pnl-card.loss .pnl-value {
  color: #ff4444;
}

.pnl-percentage {
  font-size: 14px;
  color: #a0a0a0;
}

.pnl-stats {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 16px;
  flex: 1;
}

.stat-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 12px;
  background: rgba(0, 0, 0, 0.3);
  border-radius: 8px;
  border: 1px solid #333;
}

.stat-label {
  color: #a0a0a0;
  font-size: 14px;
}

.stat-value {
  font-size: 16px;
  font-weight: 600;
  color: white;
}

.stat-value.profit {
  color: #00ff88;
}

.stat-value.loss {
  color: #ff4444;
}

/* Detailed Analysis */
.detailed-analysis {
  padding: 20px;
  border-top: 1px solid #333;
  background: rgba(0, 0, 0, 0.2);
}

.analysis-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 20px;
}

.analysis-section {
  background: rgba(0, 0, 0, 0.3);
  border-radius: 12px;
  border: 1px solid #333;
  overflow: hidden;
}

.profit-section {
  border-color: #00ff88;
}

.loss-section {
  border-color: #ff4444;
}

.active-section {
  border-color: #ffaa00;
}

.section-header {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 16px;
  background: rgba(0, 0, 0, 0.5);
  border-bottom: 1px solid #333;
}

.section-header i {
  font-size: 18px;
}

.profit-section .section-header i {
  color: #00ff88;
}

.loss-section .section-header i {
  color: #ff4444;
}

.active-section .section-header i {
  color: #ffaa00;
}

.section-header h3 {
  margin: 0;
  color: white;
  font-size: 16px;
}

.trades-list {
  padding: 16px;
  display: flex;
  flex-direction: column;
  gap: 12px;
  max-height: 300px;
  overflow-y: auto;
}

.trade-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 12px;
  border-radius: 8px;
  background: rgba(0, 0, 0, 0.3);
  border: 1px solid;
}

.trade-item.profit {
  border-color: rgba(0, 255, 136, 0.3);
  background: rgba(0, 255, 136, 0.05);
}

.trade-item.loss {
  border-color: rgba(255, 68, 68, 0.3);
  background: rgba(255, 68, 68, 0.05);
}

.trade-item.active {
  border-color: rgba(255, 170, 0, 0.3);
  background: rgba(255, 170, 0, 0.05);
}

.trade-info {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.trade-symbol {
  font-weight: 600;
  color: white;
  font-size: 14px;
}

.trade-type {
  color: #a0a0a0;
  font-size: 12px;
}

.trade-pnl {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  gap: 4px;
}

.pnl-amount {
  font-weight: bold;
  font-size: 14px;
}

.trade-item.profit .pnl-amount {
  color: #00ff88;
}

.trade-item.loss .pnl-amount {
  color: #ff4444;
}

.trade-item.active .pnl-amount {
  color: #ffaa00;
}

.trade-date {
  color: #a0a0a0;
  font-size: 11px;
}

.section-total {
  padding: 12px 16px;
  background: rgba(0, 0, 0, 0.5);
  border-top: 1px solid #333;
  text-align: center;
  font-weight: 600;
  color: white;
}

.profit-section .section-total {
  color: #00ff88;
}

.loss-section .section-total {
  color: #ff4444;
}

.active-section .section-total {
  color: #ffaa00;
}

.summary-card {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 16px;
  padding: 20px;
  background: linear-gradient(145deg, #1a1a2e, #16213e);
  border-radius: 12px;
  border: 1px solid #333;
}

.summary-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 12px;
  background: rgba(0, 0, 0, 0.3);
  border-radius: 8px;
}

.summary-label {
  color: #a0a0a0;
  font-size: 14px;
}

.summary-value {
  font-size: 18px;
  font-weight: bold;
  color: white;
}

.summary-value.completed {
  color: #00ff88;
}

.summary-value.pending {
  color: #ffaa00;
}

.summary-value.closed {
  color: #ff4444;
}

/* Orders Container */
.orders-container {
  background: linear-gradient(145deg, #1a1a2e, #16213e);
  border-radius: 12px;
  border: 1px solid #333;
  overflow: hidden;
}

.orders-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px;
  border-bottom: 1px solid #333;
}

.orders-header h2 {
  margin: 0;
  color: #00ff88;
  font-size: 20px;
}

.filter-controls {
  display: flex;
  gap: 12px;
}

.filter-select {
  background: #0d0d1a;
  color: white;
  border: 1px solid #333;
  padding: 8px 12px;
  border-radius: 6px;
  font-size: 14px;
}

.filter-select:focus {
  outline: none;
  border-color: #00ff88;
}

/* Loading State */
.loading-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 60px 20px;
  color: #a0a0a0;
}

.loading-spinner {
  width: 40px;
  height: 40px;
  border: 3px solid #333;
  border-top: 3px solid #00ff88;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin-bottom: 16px;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

/* Empty State */
.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 60px 20px;
  color: #a0a0a0;
  text-align: center;
}

.empty-state i {
  font-size: 48px;
  margin-bottom: 16px;
  color: #333;
}

.empty-state h3 {
  margin: 0 0 8px 0;
  color: white;
}

.empty-state p {
  margin: 0;
  font-size: 14px;
}

/* Orders List */
.orders-list {
  padding: 20px;
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.order-card {
  background: rgba(0, 0, 0, 0.3);
  border-radius: 12px;
  padding: 20px;
  border: 1px solid #333;
  transition: all 0.3s ease;
}

.order-card:hover {
  border-color: #00ff88;
  transform: translateY(-2px);
}

.order-card.completed {
  border-left: 4px solid #00ff88;
}

.order-card.pending {
  border-left: 4px solid #ffaa00;
}

.order-card.cancelled {
  border-left: 4px solid #ff4444;
}

.order-card.closed {
  border-left: 4px solid #666;
}

.order-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 16px;
}

.order-info h3 {
  margin: 0;
  color: #00ff88;
  font-size: 18px;
}

.order-id {
  color: #a0a0a0;
  font-size: 12px;
}

.order-status {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 6px 12px;
  border-radius: 20px;
  background: rgba(0, 0, 0, 0.5);
  font-size: 12px;
  font-weight: 500;
}

.order-status i {
  font-size: 14px;
}

/* Order Details */
.order-details {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 12px;
  margin-bottom: 16px;
}

.detail-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 8px 0;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.detail-label {
  color: #a0a0a0;
  font-size: 14px;
}

.detail-value {
  color: white;
  font-weight: 500;
  font-size: 14px;
}

.detail-value.amount {
  color: #00ff88;
  font-weight: bold;
}

.detail-value.pnl.profit {
  color: #00ff88;
  font-weight: bold;
}

.detail-value.pnl.loss {
  color: #ff4444;
  font-weight: bold;
}

.option-type.call {
  color: #00ff88;
}

.option-type.put {
  color: #ff4444;
}

.action-type.buy {
  color: #00ff88;
}

.action-type.sell {
  color: #ff4444;
}

/* Order Actions */
.order-actions {
  display: flex;
  align-items: center;
  gap: 12px;
  padding-top: 16px;
  border-top: 1px solid rgba(255, 255, 255, 0.1);
}

.exit-trade-btn {
  background: linear-gradient(145deg, #ff4444, #cc0000);
  color: white;
  border: none;
  padding: 10px 16px;
  border-radius: 6px;
  cursor: pointer;
  font-size: 14px;
  font-weight: 500;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  gap: 8px;
}

.exit-trade-btn:hover:not(:disabled) {
  background: linear-gradient(145deg, #cc0000, #ff4444);
  transform: translateY(-1px);
}

.exit-trade-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.trading-enabled-notice {
  display: flex;
  align-items: center;
  gap: 8px;
  color: #22c55e;
  font-size: 12px;
  font-weight: 500;
}

.trade-closed-info {
  display: flex;
  align-items: center;
  gap: 8px;
  color: #28a745;
  font-size: 12px;
}

/* Responsive Design */
@media (max-width: 1200px) {
  .ai-trading-orders {
    padding: 16px;
  }
  
  .page-header {
    padding: 16px;
  }
  
  .page-title {
    font-size: 24px;
  }
  
  .summary-card {
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
  }
}

@media (max-width: 768px) {
  .ai-trading-orders {
    padding: 12px;
    padding-bottom: 100px;
  }
  
  .page-header {
    flex-direction: column;
    gap: 16px;
    align-items: stretch;
    padding: 16px;
  }
  
  .header-content {
    flex-direction: column;
    align-items: stretch;
    gap: 12px;
  }
  
  .back-btn {
    width: 100%;
    justify-content: center;
    padding: 14px 20px;
    font-size: 16px;
  }
  
  .page-title {
    font-size: 22px;
    text-align: center;
  }
  
  .page-subtitle {
    text-align: center;
    font-size: 14px;
  }
  
  .header-actions {
    justify-content: center;
    flex-wrap: wrap;
    gap: 8px;
  }
  
  .refresh-btn {
    flex: 1;
    min-width: 140px;
    justify-content: center;
    padding: 12px 16px;
    font-size: 14px;
  }
  
  .market-status {
    flex-direction: column;
    gap: 12px;
    text-align: center;
    padding: 12px 16px;
  }
  
  .status-indicator {
    justify-content: center;
  }
  
  .summary-card {
    grid-template-columns: 1fr;
    gap: 12px;
    padding: 16px;
  }
  
  .summary-item {
    padding: 10px;
  }
  
  .summary-value {
    font-size: 16px;
  }
  
  /* P&L Analysis Responsive */
  .pnl-summary {
    flex-direction: column;
    gap: 16px;
    padding: 16px;
  }
  
  .pnl-card {
    padding: 16px;
  }
  
  .pnl-value {
    font-size: 20px;
  }
  
  .pnl-stats {
    grid-template-columns: 1fr;
    gap: 12px;
  }
  
  .analysis-header {
    flex-direction: column;
    gap: 12px;
    align-items: stretch;
    padding: 16px;
  }
  
  .analysis-header h2 {
    font-size: 18px;
    text-align: center;
  }
  
  .toggle-btn {
    width: 100%;
    justify-content: center;
    padding: 12px 16px;
  }
  
  .detailed-analysis {
    padding: 16px;
  }
  
  .analysis-grid {
    grid-template-columns: 1fr;
    gap: 16px;
  }
  
  .trades-list {
    max-height: 200px;
    padding: 12px;
  }
  
  .trade-item {
    flex-direction: column;
    gap: 8px;
    text-align: center;
    padding: 10px;
  }
  
  .trade-pnl {
    align-items: center;
  }
  
  .orders-container {
    margin: 0;
  }
  
  .orders-header {
    flex-direction: column;
    gap: 16px;
    align-items: stretch;
    padding: 16px;
  }
  
  .orders-header h2 {
    font-size: 18px;
    text-align: center;
  }
  
  .filter-controls {
    justify-content: center;
    flex-direction: column;
    gap: 8px;
  }
  
  .filter-select {
    width: 100%;
    padding: 10px 12px;
    font-size: 16px;
  }
  
  .orders-list {
    padding: 16px;
    gap: 12px;
  }
  
  .order-card {
    padding: 16px;
  }
  
  .order-header {
    flex-direction: column;
    gap: 12px;
    align-items: stretch;
  }
  
  .order-info h3 {
    font-size: 16px;
    text-align: center;
  }
  
  .order-status {
    justify-content: center;
    padding: 8px 12px;
  }
  
  .order-details {
    grid-template-columns: 1fr;
    gap: 8px;
  }
  
  .detail-row {
    flex-direction: column;
    gap: 4px;
    text-align: center;
    padding: 6px 0;
  }
  
  .detail-label {
    font-size: 12px;
    color: #888;
  }
  
  .detail-value {
    font-size: 14px;
    font-weight: 600;
  }
  
  .order-actions {
    flex-direction: column;
    gap: 8px;
    padding-top: 12px;
  }
  
  .exit-trade-btn {
    width: 100%;
    justify-content: center;
    padding: 12px 16px;
    font-size: 16px;
  }
  
  .trading-enabled-notice {
    text-align: center;
    font-size: 11px;
  }
  
  .trade-closed-info {
    text-align: center;
    font-size: 11px;
  }
  
  .loading-container {
    padding: 40px 16px;
  }
  
  .empty-state {
    padding: 40px 16px;
  }
  
  .empty-state i {
    font-size: 36px;
  }
  
  .empty-state h3 {
    font-size: 18px;
  }
  
  .empty-state p {
    font-size: 14px;
  }
}

@media (max-width: 480px) {
  .ai-trading-orders {
    padding: 8px;
    padding-bottom: 80px;
  }
  
  .page-header {
    padding: 12px;
  }
  
  .page-title {
    font-size: 20px;
  }
  
  .page-subtitle {
    font-size: 13px;
  }
  
  .back-btn {
    padding: 12px 16px;
    font-size: 14px;
  }
  
  .refresh-btn {
    padding: 10px 12px;
    font-size: 13px;
    min-width: 120px;
  }
  
  .market-status {
    padding: 10px 12px;
  }
  
  .summary-card {
    padding: 12px;
  }
  
  .summary-item {
    padding: 8px;
  }
  
  .summary-value {
    font-size: 14px;
  }
  
  .orders-header {
    padding: 12px;
  }
  
  .orders-header h2 {
    font-size: 16px;
  }
  
  .orders-list {
    padding: 12px;
  }
  
  .order-card {
    padding: 12px;
  }
  
  .order-info h3 {
    font-size: 14px;
  }
  
  .detail-value {
    font-size: 13px;
  }
  
  .exit-trade-btn {
    padding: 10px 12px;
    font-size: 14px;
  }
}

/* Tablet Landscape */
@media (min-width: 769px) and (max-width: 1024px) {
  .ai-trading-orders {
    padding: 16px;
  }
  
  .page-header {
    padding: 18px;
  }
  
  .summary-card {
    grid-template-columns: repeat(3, 1fr);
  }
  
  .order-details {
    grid-template-columns: repeat(2, 1fr);
  }
  
  .orders-list {
    padding: 18px;
  }
  
  .order-card {
    padding: 18px;
  }
}

/* Large Desktop */
@media (min-width: 1400px) {
  .ai-trading-orders {
    max-width: 1400px;
    margin: 0 auto;
  }
  
  .page-header {
    padding: 24px;
  }
  
  .summary-card {
    grid-template-columns: repeat(5, 1fr);
  }
  
  .order-details {
    grid-template-columns: repeat(3, 1fr);
  }
}

/* Live Price Display Styles */
.strike-price {
  font-size: 12px;
  color: #666;
  background: #f0f0f0;
  padding: 2px 6px;
  border-radius: 4px;
  margin-left: 8px;
}

.price-info {
  display: flex;
  flex-direction: column;
  gap: 4px;
  margin-bottom: 8px;
}

.current-price {
  font-size: 14px;
  font-weight: 600;
  color: #2c5aa0;
  background: #e3f2fd;
  padding: 4px 8px;
  border-radius: 4px;
  border-left: 3px solid #2196f3;
}

.invested-amount {
  font-size: 12px;
  color: #666;
}

.live-pnl {
  font-size: 14px;
  font-weight: 700;
  padding: 6px 10px;
  border-radius: 6px;
  text-align: center;
  margin-bottom: 8px;
  transition: all 0.3s ease;
}

.live-pnl.profit {
  background: #e8f5e8;
  color: #2e7d32;
  border: 1px solid #4caf50;
}

.live-pnl.loss {
  background: #ffebee;
  color: #c62828;
  border: 1px solid #f44336;
}

.section-total {
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 8px;
}

.live-total-pnl {
  font-size: 16px;
  font-weight: 700;
  padding: 8px 12px;
  border-radius: 6px;
  transition: all 0.3s ease;
}

.live-total-pnl.profit {
  background: #e8f5e8;
  color: #2e7d32;
  border: 1px solid #4caf50;
}

.live-total-pnl.loss {
  background: #ffebee;
  color: #c62828;
  border: 1px solid #f44336;
}

/* Live P&L in orders list */
.detail-row .live-pnl {
  font-weight: 700;
  padding: 4px 8px;
  border-radius: 4px;
  font-size: 14px;
}

.detail-row .live-pnl.profit {
  background: #e8f5e8;
  color: #2e7d32;
  border: 1px solid #4caf50;
}

.detail-row .live-pnl.loss {
  background: #ffebee;
  color: #c62828;
  border: 1px solid #f44336;
}

/* Live indicators and animations */
.live-dot {
  font-size: 8px;
  margin-right: 5px;
  animation: pulse 2s infinite;
}

.live-dot.live {
  color: #4caf50;
}

.live-dot.offline {
  color: #f44336;
  animation: none;
}

.live-refresh {
  margin-left: 8px;
  font-size: 10px;
  animation: spin 2s linear infinite;
  color: #2196f3;
}

.pnl-percentage {
  font-size: 12px;
  margin-left: 5px;
  font-weight: 600;
}

@keyframes pulse {
  0% { opacity: 1; }
  50% { opacity: 0.5; }
  100% { opacity: 1; }
}

@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}

/* Balance breakdown styling */
.balance-breakdown {
  display: flex;
  flex-wrap: wrap;
  gap: 15px;
  margin-top: 10px;
  padding: 12px;
  background: rgba(255, 255, 255, 0.05);
  border-radius: 8px;
  border: 1px solid rgba(255, 255, 255, 0.1);
}

.balance-item {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
}

.balance-label {
  font-size: 11px;
  color: #888;
  text-transform: uppercase;
  margin-bottom: 2px;
  font-weight: 500;
}

.balance-value {
  font-size: 14px;
  font-weight: 700;
  color: #fff;
}

.balance-value.available {
  color: #4caf50;
}

.balance-value.total {
  color: #2196f3;
}

.balance-value.blocked {
  color: #ff9800;
}

.balance-value.live-pnl.profit {
  color: #4caf50;
}

.balance-value.live-pnl.loss {
  color: #f44336;
}

@media (max-width: 768px) {
  .balance-breakdown {
    flex-direction: column;
    gap: 10px;
  }
  
  .balance-item {
    flex-direction: row;
    justify-content: space-between;
    align-items: center;
  }
}

.detail-row .current-price {
  font-weight: 600;
  color: #2c5aa0;
  background: #e3f2fd;
  padding: 2px 6px;
  border-radius: 3px;
  border-left: 2px solid #2196f3;
}

.live-indicator {
  color: #4caf50;
  font-size: 12px;
  margin-left: 4px;
  animation: pulse 2s infinite;
}

/* Enhanced Live P&L Row Styling */
.live-pnl-row {
  background: rgba(0, 255, 136, 0.05);
  border: 1px solid rgba(0, 255, 136, 0.2);
  border-radius: 8px;
  padding: 12px !important;
  margin: 8px 0;
  position: relative;
  overflow: hidden;
}

.live-pnl-row::before {
  content: '';
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(90deg, transparent, rgba(0, 255, 136, 0.1), transparent);
  animation: shimmer 3s infinite;
}

.live-pnl-value {
  display: flex;
  align-items: center;
  gap: 8px;
  flex-wrap: wrap;
  font-weight: 700 !important;
  font-size: 16px !important;
}

.pnl-amount {
  font-size: 18px;
  font-weight: 800;
  text-shadow: 0 0 10px currentColor;
}

.pnl-percentage {
  font-size: 14px;
  opacity: 0.8;
  font-weight: 600;
}

.live-indicator.live {
  color: #4caf50;
  background: rgba(76, 175, 80, 0.1);
  padding: 4px 8px;
  border-radius: 12px;
  font-size: 10px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  animation: livePulse 1.5s infinite;
}

.live-indicator.offline {
  color: #ff5722;
  background: rgba(255, 87, 34, 0.1);
  padding: 4px 8px;
  border-radius: 12px;
  font-size: 10px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.update-time {
  font-size: 8px;
  opacity: 0.7;
  margin-left: 4px;
  font-weight: 400;
  text-transform: none;
}

@keyframes shimmer {
  0% { left: -100%; }
  100% { left: 100%; }
}

@keyframes livePulse {
  0%, 100% { 
    opacity: 1; 
    transform: scale(1);
  }
  50% { 
    opacity: 0.7; 
    transform: scale(1.05);
  }
}

@keyframes pulse {
  0% { opacity: 1; }
  50% { opacity: 0.5; }
  100% { opacity: 1; }
}

/* Responsive adjustments for live price display */
@media (max-width: 768px) {
  .price-info {
    gap: 2px;
  }
  
  .current-price {
    font-size: 12px;
    padding: 3px 6px;
  }
  
  .live-pnl {
    font-size: 12px;
    padding: 4px 8px;
  }
  
  .section-total {
    flex-direction: column;
    align-items: flex-start;
  }
  
  .live-total-pnl {
    font-size: 14px;
    padding: 6px 10px;
  }
}
</style>
