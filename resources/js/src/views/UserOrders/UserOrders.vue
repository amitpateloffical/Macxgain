<template>
  <div class="orders-container">
    <!-- Header Section -->
    <div class="orders-header">
      <div class="header-content">
        <div class="header-left">
          <div class="header-icon">
            <i class="fas fa-list-alt"></i>
          </div>
          <div class="header-text">
            <h1 class="page-title">Orders</h1>
            <p class="page-subtitle">Track your buy and sell orders</p>
          </div>
        </div>
        <div class="header-actions">
          <button class="btn-refresh" @click="loadOrders" :disabled="loading">
            <i class="fas fa-sync-alt" :class="{ 'fa-spin': loading }"></i>
            {{ loading ? 'Loading...' : 'Refresh' }}
          </button>
        </div>
      </div>
    </div>



    <!-- Orders Stats -->
    <div class="stats-section">
      <div class="stats-grid">
        <div class="stat-card">
          <div class="stat-icon">
            <i class="fas fa-shopping-cart"></i>
          </div>
          <div class="stat-content">
            <div class="stat-value">{{ totalOrders }}</div>
            <div class="stat-label">Total Orders</div>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon executed">
            <i class="fas fa-check-circle"></i>
          </div>
          <div class="stat-content">
            <div class="stat-value">{{ executedOrders }}</div>
            <div class="stat-label">Executed</div>
          </div>
        </div>
        <div class="stat-card profit">
          <div class="stat-icon profit">
            <i class="fas fa-arrow-up"></i>
          </div>
          <div class="stat-content">
            <div class="stat-value profit">+₹{{ totalProfit?.toLocaleString() }}</div>
            <div class="stat-label">Total Profit</div>
          </div>
        </div>
        <div class="stat-card loss">
          <div class="stat-icon loss">
            <i class="fas fa-arrow-down"></i>
          </div>
          <div class="stat-content">
            <div class="stat-value loss">-₹{{ Math.abs(totalLoss)?.toLocaleString() }}</div>
            <div class="stat-label">Total Loss</div>
          </div>
        </div>
      </div>
    </div>

    <!-- P&L Analysis Section -->
    <div class="pnl-analysis-section" v-if="closedTrades.length > 0">
      <div class="section-header">
        <h3>📊 Trading Performance Analysis</h3>
        <button class="toggle-btn" @click="showDetailedAnalysis = !showDetailedAnalysis">
          <i class="fas" :class="showDetailedAnalysis ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
          {{ showDetailedAnalysis ? 'Hide Details' : 'Show Details' }}
        </button>
      </div>
      
      <div class="pnl-summary">
        <div class="pnl-card profit">
          <div class="pnl-icon">
            <i class="fas fa-arrow-up"></i>
          </div>
          <div class="pnl-content">
            <div class="pnl-value">+₹{{ totalProfit?.toLocaleString() }}</div>
            <div class="pnl-label">Total Profit</div>
            <div class="pnl-count">{{ profitableTrades.length }} trades</div>
          </div>
        </div>
        
        <div class="pnl-card loss">
          <div class="pnl-icon">
            <i class="fas fa-arrow-down"></i>
          </div>
          <div class="pnl-content">
            <div class="pnl-value">-₹{{ Math.abs(totalLoss)?.toLocaleString() }}</div>
            <div class="pnl-label">Total Loss</div>
            <div class="pnl-count">{{ lossTrades.length }} trades</div>
          </div>
        </div>
        
        <div class="pnl-card" :class="overallPnL >= 0 ? 'profit' : 'loss'">
          <div class="pnl-icon">
            <i class="fas fa-balance-scale"></i>
          </div>
          <div class="pnl-content">
            <div class="pnl-value">{{ overallPnL >= 0 ? '+' : '' }}₹{{ overallPnL?.toLocaleString() }}</div>
            <div class="pnl-label">Net P&L</div>
            <div class="pnl-count">{{ closedTrades.length }} total trades</div>
          </div>
        </div>
      </div>
      
      <div v-if="showDetailedAnalysis" class="detailed-analysis">
        <div class="analysis-grid">
          <div class="analysis-item">
            <span class="analysis-label">Total Invested:</span>
            <span class="analysis-value">₹{{ totalInvested?.toLocaleString() }}</span>
          </div>
          <div class="analysis-item">
            <span class="analysis-label">Total Return:</span>
            <span class="analysis-value">₹{{ totalReturn?.toLocaleString() }}</span>
          </div>
          <div class="analysis-item">
            <span class="analysis-label">Win Rate:</span>
            <span class="analysis-value">{{ winRate }}%</span>
          </div>
          <div class="analysis-item">
            <span class="analysis-label">Avg Profit per Trade:</span>
            <span class="analysis-value">₹{{ avgProfitPerTrade?.toLocaleString() }}</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Orders Content -->
    <div class="orders-content">

      
      <!-- Empty State -->
      <div v-if="filteredOrders.length === 0 && !loading" class="empty-state">
        <div class="empty-icon">
          <i class="fas fa-inbox"></i>
        </div>
        <h3 class="empty-title">No orders found</h3>
        <p class="empty-subtitle">
          You haven't placed any orders yet
        </p>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="loading-state">
        <div class="loading-spinner">
          <i class="fas fa-spinner fa-spin"></i>
        </div>
        <p>Loading your orders...</p>
      </div>

      <!-- Orders List -->
      <div v-if="filteredOrders.length > 0" class="orders-list">
        <div 
          v-for="order in filteredOrders" 
          :key="order.id" 
          class="order-card"
          :class="[
            order.type.toLowerCase(),
            order.status.toLowerCase(),
            { 'profit': order.status === 'CLOSED' && calculateTradePnL(order) > 0, 'loss': order.status === 'CLOSED' && calculateTradePnL(order) < 0 }
          ]"
        >
          <!-- Order Header -->
          <div class="order-header">
            <div class="order-main-info">
              <div class="stock-info">
                <h3 class="stock-symbol">{{ order.symbol }}</h3>
                <span class="stock-exchange">{{ order.exchange }}</span>
              </div>
              <div class="order-type-badge" :class="order.type.toLowerCase()">
                {{ order.type }}
              </div>
            </div>
            <div class="order-status">
              <div class="status-badge" :class="order.status.toLowerCase()">
                <i :class="getStatusIcon(order.status)"></i>
                {{ order.status }}
              </div>
              <div class="order-time">{{ formatTime(order.timestamp) }}</div>
            </div>
          </div>

          <!-- Order Details -->
          <div class="order-details">
            <div class="detail-row">
              <div class="detail-group">
                <span class="detail-label">Lot Size</span>
                <span class="detail-value">{{ order.quantity }}</span>
              </div>

              <div class="detail-group">
                <span class="detail-label">Quantity</span>
                <span class="detail-value">{{ order.lot_size }}</span>
              </div>


              <div class="detail-group">
                <span class="detail-label">Purchase Price (Unit)</span>
                <span class="detail-value">₹{{ order.unit_price.toFixed(2)}}</span>
              </div>
              <div class="detail-group">
                <span class="detail-label">Strike Price</span>
                <span class="detail-value">₹{{ order.price.toFixed(2) }}</span>
              </div>
              <div class="detail-group">
                <span class="detail-label">Total Amount</span>
                <span class="detail-value">₹{{ order.totalAmount?.toFixed(2) || (order.quantity * order.price).toFixed(2) }}</span>
              </div>
            </div>
            
            <div class="detail-row" v-if="order.optionType">
              <div class="detail-group">
                <span class="detail-label">Option Type</span>
                <span class="detail-value option-type" :class="order.optionType.toLowerCase()">
                  <i :class="order.optionType === 'CALL' ? 'fas fa-arrow-up' : 'fas fa-arrow-down'"></i>
                  {{ order.optionType }}
                </span>
              </div>
            </div>
            
            <div class="detail-row" v-if="order.status === 'EXECUTED' || order.status === 'CLOSED'">
              <div class="detail-group" v-if="order.executedPrice && order.status === 'CLOSED'">
                <span class="detail-label">Exit Price</span>
                <span class="detail-value">₹{{ order.executedPrice.toFixed(2) * order.quantity}}</span>
              </div>

              <div class="detail-group" v-if="order.executedPrice && order.status === 'CLOSED'">
                <span class="detail-label">Exit Price(unit)</span>
                <span class="detail-value">₹{{ order.executedPrice.toFixed(2)}}</span>
              </div>
              <div class="detail-group" v-if="order.status === 'CLOSED' && order.executedPrice && order.totalAmount">
                <span class="detail-label">P&L</span>
                <span class="detail-value" :class="{ 'profit': calculateTradePnL(order) > 0, 'loss': calculateTradePnL(order) < 0 }">
                  {{ calculateTradePnL(order) > 0 ? '+' : '' }}₹{{ calculateTradePnL(order).toFixed(2) }}
                </span>
              </div>
              <div class="detail-group" v-if="order.executedAt && order.status === 'CLOSED'">
                <span class="detail-label">Closed At</span>
                <span class="detail-value">{{ formatTime(order.executedAt) }}</span>
              </div>
            </div>
          </div>


        </div>
      </div>
    </div>

    <!-- Place Order Modal -->
    <b-modal
      v-model="showPlaceOrderModal"
      title="Place Order"
      centered
      size="lg"
      hide-footer
      modal-class="place-order-modal"
    >
      <div class="place-order-content">
        <div class="order-form">
          <!-- Stock Selection -->
          <div class="form-group">
            <label class="form-label">Select Stock</label>
            <div class="stock-search-wrapper">
              <i class="fas fa-search search-icon"></i>
              <input
                type="text"
                v-model="orderForm.stockSearch"
                placeholder="Search stock symbol or name..."
                class="stock-search-input"
                @input="searchStocks"
              />
            </div>
            <div v-if="stockSearchResults.length > 0" class="search-results">
              <div 
                v-for="stock in stockSearchResults" 
                :key="stock.symbol"
                class="search-result-item"
                @click="selectStock(stock)"
              >
                <div class="result-info">
                  <div class="result-symbol">{{ stock.symbol }}</div>
                  <div class="result-name">{{ stock.name }}</div>
                </div>
                <div class="result-price">₹{{ stock.price.toFixed(2) }}</div>
              </div>
            </div>
          </div>

          <!-- Order Type -->
          <div class="form-row">
            <div class="form-group">
              <label class="form-label">Order Type</label>
              <div class="radio-group">
                <label class="radio-option">
                  <input type="radio" v-model="orderForm.type" value="BUY" />
                  <span class="radio-label buy">BUY</span>
                </label>
                <label class="radio-option">
                  <input type="radio" v-model="orderForm.type" value="SELL" />
                  <span class="radio-label sell">SELL</span>
                </label>
              </div>
            </div>
            <div class="form-group">
              <label class="form-label">Product Type</label>
              <select v-model="orderForm.product" class="form-select">
                <option value="CNC">CNC (Cash & Carry)</option>
                <option value="MIS">MIS (Intraday)</option>
                <option value="NRML">NRML (Normal)</option>
              </select>
            </div>
          </div>

          <!-- Quantity and Price -->
          <div class="form-row">
            <div class="form-group">
              <label class="form-label">Quantity</label>
              <input
                type="number"
                v-model="orderForm.quantity"
                placeholder="Enter quantity"
                class="form-input"
                min="1"
              />
            </div>
            <div class="form-group">
              <label class="form-label">Price</label>
              <input
                type="number"
                v-model="orderForm.price"
                placeholder="Enter price"
                class="form-input"
                step="0.01"
              />
            </div>
          </div>

          <!-- Order Value -->
          <div class="order-summary">
            <div class="summary-row">
              <span class="summary-label">Order Value:</span>
              <span class="summary-value">₹{{ orderValue.toFixed(2) }}</span>
            </div>
            <div class="summary-row">
              <span class="summary-label">Brokerage (₹20):</span>
              <span class="summary-value">₹20.00</span>
            </div>
            <div class="summary-row total">
              <span class="summary-label">Total Amount:</span>
              <span class="summary-value">₹{{ (orderValue + 20).toFixed(2) }}</span>
            </div>
          </div>
        </div>

        <!-- Modal Actions -->
        <div class="modal-actions">
          <button class="btn-cancel" @click="closeOrderModal">
            <i class="fas fa-times"></i>
            Cancel
          </button>
          <button class="btn-place-order" @click="placeOrder" :disabled="!canPlaceOrder">
            <i class="fas fa-check"></i>
            Place Order
          </button>
        </div>
      </div>
    </b-modal>

    <!-- Bottom App Bar -->
    <BottomAppBar />
  </div>
</template>

<script>
import axios from '@/axios';

export default {
  name: 'UserOrders',
  data() {
    return {
      loading: false,

      showPlaceOrderModal: false,
      showDetailedAnalysis: false,
      orders: [],
      orderForm: {
        stockSearch: '',
        selectedStock: null,
        type: 'BUY',
        product: 'CNC',
        quantity: 1,
        price: 0
      },
      stockSearchResults: [],
      availableStocks: [
        { symbol: 'RELIANCE', name: 'Reliance Industries Ltd', price: 2456.75 },
        { symbol: 'TCS', name: 'Tata Consultancy Services', price: 3678.45 },
        { symbol: 'INFY', name: 'Infosys Limited', price: 1567.80 },
        { symbol: 'HDFC', name: 'HDFC Bank Limited', price: 1678.90 },
        { symbol: 'WIPRO', name: 'Wipro Limited', price: 456.78 },
        { symbol: 'MARUTI', name: 'Maruti Suzuki India Ltd', price: 9876.54 },
        { symbol: 'ICICIBANK', name: 'ICICI Bank Ltd', price: 987.65 }
      ],
      autoRefreshInterval: null
    };
  },
  computed: {
        filteredOrders() {
      return this.orders;
    },
    totalOrders() {
      return this.orders.length;
    },
    pendingOrders() {
      return this.orders.filter(order => order.status === 'PENDING').length;
    },
    executedOrders() {
      return this.orders.filter(order => order.status === 'CLOSED').length;
    },
    cancelledOrders() {
      return this.orders.filter(order => order.status === 'CANCELLED').length;
    },
    
    // P&L Analysis Computed Properties
    closedTrades() {
      return this.orders.filter(order => order.status === 'CLOSED' && order.executedPrice && order.totalAmount);
    },
    
    profitableTrades() {
      return this.closedTrades.filter(trade => this.calculateTradePnL(trade) > 0);
    },
    
    lossTrades() {
      return this.closedTrades.filter(trade => this.calculateTradePnL(trade) < 0);
    },
    
    totalProfit() {
      return this.profitableTrades.reduce((sum, trade) => sum + this.calculateTradePnL(trade), 0);
    },
    
    totalLoss() {
      return this.lossTrades.reduce((sum, trade) => sum + this.calculateTradePnL(trade), 0);
    },
    
    overallPnL() {
      return this.totalProfit + this.totalLoss;
    },
    
    totalInvested() {
      return this.orders.filter(order => order.status === 'CLOSED').reduce((sum, order) => sum + (order.totalAmount || 0), 0);
    },
    
    totalReturn() {
      return this.totalInvested + this.overallPnL;
    },
    
    winRate() {
      if (this.closedTrades.length === 0) return 0;
      return Math.round((this.profitableTrades.length / this.closedTrades.length) * 100);
    },
    
    avgProfitPerTrade() {
      if (this.closedTrades.length === 0) return 0;
      return this.overallPnL / this.closedTrades.length;
    },

    orderValue() {
      return (this.orderForm.quantity || 0) * (this.orderForm.price || 0);
    },
    canPlaceOrder() {
      return this.orderForm.selectedStock && 
             this.orderForm.quantity > 0 && 
             this.orderForm.price > 0 &&
             this.orderForm.type;
    }
  },
  mounted() {
    this.loadOrders();
    
    // Start auto-refresh for live data updates
    this.startAutoRefresh();
  },
  beforeUnmount() {
    // Clear auto-refresh interval when component is destroyed
    if (this.autoRefreshInterval) {
      clearInterval(this.autoRefreshInterval);
    }
  },
  methods: {
    async loadOrders() {
      try {
        this.loading = true;
        const token = localStorage.getItem('access_token');
        const user = JSON.parse(localStorage.getItem('userData') || '{}');
        
        if (!user.id) {
          this.showError('User not found. Please login again.');
          return;
        }
        
        if (!token) {
          this.showError('Authentication token not found. Please login again.');
          return;
        }
        
        const response = await axios.get(`ai-trading/user-orders/${user.id}`);

        if (response.data.success) {
          // Transform AI trading orders to match the expected format
          this.orders = response.data.orders.map(order => ({
            id: order.id,
            symbol: order.stock_symbol,
            exchange: 'NSE',
            type: order.action,
            status: order.status === 'COMPLETED' ? 'EXECUTED' : (order.status === 'CLOSED' ? 'CLOSED' : order.status),
            quantity: parseInt(order.quantity),
            lot_size: parseInt(order.lot_size),
            price: parseFloat(order.strike_price),
            executedPrice: order.exit_price ? parseFloat(order.exit_price) : null,
            unit_price: order.unit_price ? parseFloat(order.unit_price) : null,
            timestamp: new Date(order.created_at),
            executedAt: order.closed_at ? new Date(order.closed_at) : null,
            optionType: order.option_type,
            totalAmount: parseFloat(order.total_amount)
          }));
        } else {
          this.showError(response.data.message || 'Failed to load orders');
        }
      } catch (error) {
        console.error('Error loading orders:', error);
        
        if (error.response?.status === 401) {
          this.showError('Authentication failed. Please login again.');
        } else if (error.response?.status === 403) {
          this.showError('Access denied. You do not have permission to view orders.');
        } else {
          this.showError('Failed to load orders. Please try again.');
        }
      } finally {
        this.loading = false;
      }
    },
    
    startAutoRefresh() {
      // Auto-refresh orders every 10 seconds for live P&L updates
      this.autoRefreshInterval = setInterval(() => {
        this.loadOrders();
        console.log('Auto-refresh: Orders updated at', new Date().toLocaleTimeString());
      }, 10000); // 10 seconds for faster updates
    },
    
    showError(message) {
      // Simple error notification - you can enhance this with a proper notification system
      alert(message);
    },

    calculateTradePnL(trade) {
      // Calculate P&L using the same formula as admin: exit_price * quantity - total_amount
      if (!trade.executedPrice || !trade.totalAmount) {
        return 0;
      }
      return (trade.executedPrice * trade.quantity) - trade.totalAmount;
    },

    getStatusIcon(status) {
      switch (status) {
        case 'PENDING': return 'fas fa-clock';
        case 'EXECUTED': return 'fas fa-check-circle';
        case 'CANCELLED': return 'fas fa-times-circle';
        default: return 'fas fa-question-circle';
      }
    },
    formatTime(timestamp) {
      if (!timestamp) return '-';
      return new Date(timestamp).toLocaleString('en-IN', {
        day: '2-digit',
        month: 'short',
        hour: '2-digit',
        minute: '2-digit'
      });
    },
    searchStocks() {
      if (this.orderForm.stockSearch.length < 2) {
        this.stockSearchResults = [];
        return;
      }
      
      this.stockSearchResults = this.availableStocks.filter(stock => 
        stock.symbol.toLowerCase().includes(this.orderForm.stockSearch.toLowerCase()) ||
        stock.name.toLowerCase().includes(this.orderForm.stockSearch.toLowerCase())
      ).slice(0, 5);
    },
    selectStock(stock) {
      this.orderForm.selectedStock = stock;
      this.orderForm.stockSearch = stock.symbol;
      this.orderForm.price = stock.price;
      this.stockSearchResults = [];
    },
    placeOrder() {
      if (!this.canPlaceOrder) return;
      
      const newOrder = {
        id: this.orders.length + 1,
        symbol: this.orderForm.selectedStock.symbol,
        exchange: 'NSE',
        type: this.orderForm.type,
        status: 'PENDING',
        quantity: parseInt(this.orderForm.quantity),
        price: parseFloat(this.orderForm.price),
        executedPrice: 0,
        pnl: 0,
        timestamp: new Date(),
        executedAt: null
      };
      
      this.orders.unshift(newOrder);
      this.closeOrderModal();
      
      this.$bvToast.toast(`${this.orderForm.type} order for ${this.orderForm.quantity} ${this.orderForm.selectedStock.symbol} placed successfully`, {
        title: 'Order Placed',
        variant: 'success',
        solid: true
      });
    },
    closeOrderModal() {
      this.showPlaceOrderModal = false;
      this.orderForm = {
        stockSearch: '',
        selectedStock: null,
        type: 'BUY',
        product: 'CNC',
        quantity: 1,
        price: 0
      };
      this.stockSearchResults = [];
    },

  }
};
</script>

<style scoped>
.orders-container {
  min-height: 100vh;
  background: linear-gradient(135deg, #0f0f23 0%, #1a1a2e 50%, #16213e 100%);
  color: white;
  padding-bottom: 100px; /* Space for app bar */
}

/* Header Section */
.orders-header {
  padding: 20px;
  border-bottom: 1px solid rgba(0, 255, 128, 0.2);
}

.header-content {
  display: flex;
  justify-content: space-between;
  align-items: center;
  max-width: 1200px;
  margin: 0 auto;
}

.header-left {
  display: flex;
  align-items: center;
  gap: 16px;
}

.header-icon {
  width: 60px;
  height: 60px;
  background: linear-gradient(135deg, #00ff80 0%, #00d4aa 100%);
  border-radius: 16px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 24px;
  color: #0f0f23;
}

.page-title {
  font-size: 2rem;
  font-weight: 700;
  margin: 0;
  background: linear-gradient(135deg, #00ff80 0%, #00d4aa 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.page-subtitle {
  color: #a1a1a1;
  margin: 4px 0 0 0;
  font-size: 1rem;
}

.btn-refresh {
  background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
  color: white;
  border: none;
  padding: 12px 20px;
  border-radius: 12px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  gap: 8px;
  margin-right: 12px;
}

.btn-refresh:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(40, 167, 69, 0.3);
}

.btn-refresh:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.btn-place-order {
  background: linear-gradient(135deg, #00ff80 0%, #00d4aa 100%);
  color: #0f0f23;
  border: none;
  padding: 12px 24px;
  border-radius: 12px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  gap: 8px;
}

.btn-place-order:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(0, 255, 128, 0.3);
}



/* Stats Section */
.stats-section {
  padding: 20px;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 16px;
  max-width: 1200px;
  margin: 0 auto;
}

.stat-card {
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 16px;
  padding: 20px;
  display: flex;
  align-items: center;
  gap: 16px;
  transition: all 0.3s ease;
}

.stat-card:hover {
  background: rgba(255, 255, 255, 0.08);
  transform: translateY(-2px);
}

.stat-icon {
  width: 48px;
  height: 48px;
  background: rgba(0, 255, 128, 0.2);
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 20px;
  color: #00ff80;
}

.stat-icon.pending {
  background: rgba(255, 193, 7, 0.2);
  color: #ffc107;
}

.stat-icon.executed {
  background: rgba(34, 197, 94, 0.2);
  color: #22c55e;
}

.stat-icon.cancelled {
  background: rgba(239, 68, 68, 0.2);
  color: #ef4444;
}

.stat-value {
  font-size: 1.5rem;
  font-weight: 700;
  color: white;
}

.stat-label {
  color: #a1a1a1;
  font-size: 0.875rem;
}

/* Orders Content */
.orders-content {
  padding: 20px;
  max-width: 1200px;
  margin: 0 auto;
}

/* Empty State */
.empty-state {
  text-align: center;
  padding: 60px 20px;
}

.empty-icon {
  font-size: 4rem;
  color: #a1a1a1;
  margin-bottom: 20px;
}

.empty-title {
  font-size: 1.5rem;
  font-weight: 600;
  margin-bottom: 8px;
  color: white;
}

.empty-subtitle {
  color: #a1a1a1;
  margin-bottom: 24px;
}

.btn-primary {
  background: linear-gradient(135deg, #00ff80 0%, #00d4aa 100%);
  color: #0f0f23;
  border: none;
  padding: 12px 24px;
  border-radius: 12px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  display: inline-flex;
  align-items: center;
  gap: 8px;
}

.btn-primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(0, 255, 128, 0.3);
}

/* Loading State */
.loading-state {
  text-align: center;
  padding: 60px 20px;
  color: #a1a1a1;
}

.loading-spinner {
  font-size: 2rem;
  margin-bottom: 16px;
}

/* Orders List */
.orders-list {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.order-card {
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 16px;
  padding: 20px;
  transition: all 0.3s ease;
  position: relative;
  overflow: hidden;
}

.order-card:hover {
  background: rgba(255, 255, 255, 0.08);
  transform: translateY(-2px);
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
}

.order-card.buy {
  border-left: 4px solid #22c55e;
}

.order-card.sell {
  border-left: 4px solid #ef4444;
}

.order-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 16px;
}

.order-main-info {
  display: flex;
  align-items: center;
  gap: 16px;
}

.stock-symbol {
  font-size: 1.25rem;
  font-weight: 700;
  margin: 0;
  color: white;
}

.stock-exchange {
  background: rgba(0, 255, 128, 0.2);
  color: #00ff80;
  padding: 2px 8px;
  border-radius: 4px;
  font-size: 0.75rem;
  font-weight: 500;
}

.order-type-badge {
  padding: 4px 12px;
  border-radius: 8px;
  font-size: 0.875rem;
  font-weight: 600;
}

.order-type-badge.buy {
  background: rgba(34, 197, 94, 0.2);
  color: #22c55e;
}

.order-type-badge.sell {
  background: rgba(239, 68, 68, 0.2);
  color: #ef4444;
}

.order-status {
  text-align: right;
}

.status-badge {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 6px 12px;
  border-radius: 8px;
  font-size: 0.875rem;
  font-weight: 600;
  margin-bottom: 4px;
}

.status-badge.pending {
  background: rgba(255, 193, 7, 0.2);
  color: #ffc107;
}

.status-badge.executed {
  background: rgba(34, 197, 94, 0.2);
  color: #22c55e;
}

.status-badge.cancelled {
  background: rgba(239, 68, 68, 0.2);
  color: #ef4444;
}

.order-time {
  color: #a1a1a1;
  font-size: 0.75rem;
}

.order-details {
  margin-bottom: 16px;
}

.detail-row {
  display: flex;
  justify-content: space-between;
  margin-bottom: 12px;
}

.detail-group {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.detail-label {
  color: #a1a1a1;
  font-size: 0.75rem;
  font-weight: 500;
}

.detail-value {
  color: white;
  font-size: 0.875rem;
  font-weight: 600;
}

.detail-value.profit {
  color: #22c55e;
}

.detail-value.loss {
  color: #ef4444;
}

.option-type {
  display: flex;
  align-items: center;
  gap: 4px;
  font-weight: 600;
}

.option-type.call {
  color: #28a745;
}

.option-type.put {
  color: #dc3545;
}

/* P&L Analysis Section */
.pnl-analysis-section {
  background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
  border-radius: 16px;
  padding: 24px;
  margin: 20px;
  border: 1px solid rgba(255, 255, 255, 0.1);
}

.section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.section-header h3 {
  color: #ffffff;
  font-size: 1.5rem;
  font-weight: 600;
  margin: 0;
}

.toggle-btn {
  background: rgba(255, 255, 255, 0.1);
  border: 1px solid rgba(255, 255, 255, 0.2);
  color: #ffffff;
  padding: 8px 16px;
  border-radius: 8px;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 14px;
  transition: all 0.3s ease;
}

.toggle-btn:hover {
  background: rgba(255, 255, 255, 0.2);
}

.pnl-summary {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 16px;
  margin-bottom: 20px;
}

.pnl-card {
  background: rgba(255, 255, 255, 0.05);
  border-radius: 12px;
  padding: 20px;
  display: flex;
  align-items: center;
  gap: 16px;
  border: 1px solid rgba(255, 255, 255, 0.1);
  transition: all 0.3s ease;
}

.pnl-card:hover {
  background: rgba(255, 255, 255, 0.08);
  transform: translateY(-2px);
}

.pnl-card.profit {
  border-color: rgba(40, 167, 69, 0.3);
  background: rgba(40, 167, 69, 0.1);
}

.pnl-card.loss {
  border-color: rgba(220, 53, 69, 0.3);
  background: rgba(220, 53, 69, 0.1);
}

.pnl-icon {
  width: 48px;
  height: 48px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 20px;
}

.pnl-card.profit .pnl-icon {
  background: rgba(40, 167, 69, 0.2);
  color: #28a745;
}

.pnl-card.loss .pnl-icon {
  background: rgba(220, 53, 69, 0.2);
  color: #dc3545;
}

.pnl-content {
  flex: 1;
}

.pnl-value {
  font-size: 1.5rem;
  font-weight: 700;
  margin-bottom: 4px;
}

.pnl-card.profit .pnl-value {
  color: #28a745;
}

.pnl-card.loss .pnl-value {
  color: #dc3545;
}

.pnl-label {
  color: #a0a0a0;
  font-size: 14px;
  margin-bottom: 2px;
}

.pnl-count {
  color: #666;
  font-size: 12px;
}

.detailed-analysis {
  background: rgba(255, 255, 255, 0.03);
  border-radius: 12px;
  padding: 20px;
  border: 1px solid rgba(255, 255, 255, 0.1);
}

.analysis-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 16px;
}

.analysis-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 12px 0;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.analysis-item:last-child {
  border-bottom: none;
}

.analysis-label {
  color: #a0a0a0;
  font-size: 14px;
}

.analysis-value {
  color: #ffffff;
  font-weight: 600;
  font-size: 14px;
}

/* Stats Icons */
.stat-icon.profit {
  color: #28a745;
}

.stat-icon.loss {
  color: #dc3545;
}

/* Stats Cards */
.stat-card.profit {
  border-color: rgba(40, 167, 69, 0.3);
  background: rgba(40, 167, 69, 0.1);
}

.stat-card.loss {
  border-color: rgba(220, 53, 69, 0.3);
  background: rgba(220, 53, 69, 0.1);
}

.stat-value.profit {
  color: #28a745;
}

.stat-value.loss {
  color: #dc3545;
}



/* Place Order Modal - Force Dark Theme */
.place-order-modal .modal-dialog {
  max-width: 600px;
  margin: 1.75rem auto;
}

.place-order-modal .modal-content {
  background: #1a1a2e !important;
  border: 1px solid rgba(0, 255, 128, 0.3) !important;
  border-radius: 20px;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.8) !important;
  overflow: hidden;
}

.place-order-modal .modal-header {
  background: #1a1a2e !important;
  border-bottom: 1px solid rgba(0, 255, 128, 0.2) !important;
  color: white !important;
  padding: 20px 24px;
  border: none !important;
}

.place-order-modal .modal-body {
  background: #1a1a2e !important;
  color: white !important;
  padding: 0 24px 24px 24px;
  border: none !important;
}

.place-order-modal .modal-title {
  color: white !important;
  font-weight: 600;
  font-size: 1.25rem;
}

.place-order-modal .btn-close {
  background: none !important;
  border: none !important;
  color: white !important;
  opacity: 0.8;
  font-size: 1.5rem;
  box-shadow: none !important;
}

.place-order-modal .btn-close:hover {
  opacity: 1;
  color: #00ff80 !important;
  box-shadow: none !important;
}

.place-order-modal .btn-close:focus {
  box-shadow: none !important;
  outline: none !important;
}

.place-order-content {
  padding: 20px 0;
  background: #1a1a2e !important;
  color: white !important;
}

/* Force all form elements to dark theme */
.place-order-modal input,
.place-order-modal select,
.place-order-modal textarea {
  background: rgba(0, 0, 0, 0.4) !important;
  border: 1px solid rgba(0, 255, 128, 0.4) !important;
  color: white !important;
  border-radius: 8px !important;
}

.place-order-modal input:focus,
.place-order-modal select:focus,
.place-order-modal textarea:focus {
  background: rgba(0, 0, 0, 0.5) !important;
  border-color: #00ff80 !important;
  box-shadow: 0 0 0 2px rgba(0, 255, 128, 0.3) !important;
  outline: none !important;
}

.place-order-modal input::placeholder {
  color: #a1a1a1 !important;
  opacity: 1 !important;
}

.place-order-modal label {
  color: white !important;
  font-weight: 500;
}

.place-order-modal .form-group {
  margin-bottom: 20px;
}

.place-order-modal .form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 20px;
}

/* Radio buttons styling */
.place-order-modal .radio-group {
  display: flex;
  gap: 16px;
}

.place-order-modal .radio-option {
  display: flex;
  align-items: center;
  cursor: pointer;
}

.place-order-modal .radio-label {
  padding: 8px 16px;
  border-radius: 8px;
  font-weight: 600;
  transition: all 0.3s ease;
  border: 2px solid transparent;
  background: rgba(0, 0, 0, 0.2) !important;
  color: white !important;
}

.place-order-modal .radio-label.buy {
  border-color: #22c55e;
  color: #22c55e !important;
}

.place-order-modal .radio-label.sell {
  border-color: #ef4444;
  color: #ef4444 !important;
}

.place-order-modal .radio-option input[type="radio"]:checked + .radio-label {
  background: rgba(0, 255, 128, 0.2) !important;
}

/* Search results dropdown */
.place-order-modal .search-results {
  background: rgba(0, 0, 0, 0.9) !important;
  border: 1px solid rgba(0, 255, 128, 0.4) !important;
  border-radius: 8px;
  backdrop-filter: blur(10px);
}

.place-order-modal .search-result-item {
  background: transparent !important;
  color: white !important;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1) !important;
}

.place-order-modal .search-result-item:hover {
  background: rgba(0, 255, 128, 0.1) !important;
}

.place-order-modal .result-symbol {
  color: white !important;
}

.place-order-modal .result-name {
  color: #a1a1a1 !important;
}

.place-order-modal .result-price {
  color: #00ff80 !important;
}

/* Order summary styling */
.place-order-modal .order-summary {
  background: rgba(0, 0, 0, 0.3) !important;
  border: 1px solid rgba(0, 255, 128, 0.3) !important;
  border-radius: 12px;
  padding: 16px;
  margin-top: 20px;
}

.place-order-modal .summary-row {
  display: flex;
  justify-content: space-between;
  margin-bottom: 8px;
  color: white !important;
}

.place-order-modal .summary-label {
  color: #a1a1a1 !important;
}

.place-order-modal .summary-value {
  color: white !important;
  font-weight: 600;
}

.place-order-modal .summary-row.total {
  border-top: 1px solid rgba(255, 255, 255, 0.2);
  padding-top: 8px;
  margin-top: 8px;
  font-weight: 700;
}

/* Modal action buttons */
.place-order-modal .modal-actions {
  border-top: 1px solid rgba(255, 255, 255, 0.1);
  padding-top: 20px;
  display: flex;
  justify-content: flex-end;
  gap: 12px;
}

.place-order-modal .btn-cancel {
  background: rgba(239, 68, 68, 0.2) !important;
  color: #ef4444 !important;
  border: 1px solid rgba(239, 68, 68, 0.4) !important;
  padding: 10px 20px;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.3s ease;
}

.place-order-modal .btn-cancel:hover {
  background: rgba(239, 68, 68, 0.3) !important;
}

.place-order-modal .btn-place-order {
  background: linear-gradient(135deg, #00ff80 0%, #00d4aa 100%) !important;
  color: #0f0f23 !important;
  border: none !important;
  padding: 10px 20px;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 600;
  transition: all 0.3s ease;
}

.place-order-modal .btn-place-order:hover {
  transform: translateY(-1px);
  box-shadow: 0 4px 15px rgba(0, 255, 128, 0.3);
}

.place-order-modal .btn-place-order:disabled {
  opacity: 0.5;
  cursor: not-allowed;
  transform: none;
  box-shadow: none;
}

/* Remove all white borders and backgrounds */
.place-order-modal * {
  border-color: rgba(0, 255, 128, 0.3) !important;
}

.place-order-modal .modal-content,
.place-order-modal .modal-header,
.place-order-modal .modal-body,
.place-order-modal .modal-footer {
  background: #1a1a2e !important;
  border: none !important;
}

.place-order-modal .form-control,
.place-order-modal .form-select {
  background: rgba(0, 0, 0, 0.4) !important;
  border: 1px solid rgba(0, 255, 128, 0.4) !important;
  color: white !important;
}

.place-order-modal .form-control:focus,
.place-order-modal .form-select:focus {
  background: rgba(0, 0, 0, 0.5) !important;
  border-color: #00ff80 !important;
  box-shadow: 0 0 0 2px rgba(0, 255, 128, 0.3) !important;
}

/* Responsive Design */
@media (max-width: 768px) {
  .place-order-modal .modal-dialog {
    max-width: 95%;
    margin: 1rem auto;
  }
  
  .place-order-modal .modal-header {
    padding: 16px 20px;
  }
  
  .place-order-modal .modal-body {
    padding: 0 20px 20px 20px;
  }
  
  .place-order-modal .form-row {
    grid-template-columns: 1fr !important;
    gap: 16px;
  }
  
  .place-order-modal .modal-actions {
    flex-direction: column;
    gap: 12px;
  }
  
  .place-order-modal .btn-cancel,
  .place-order-modal .btn-place-order {
    width: 100%;
    justify-content: center;
  }
  
  /* P&L Analysis Responsive */
  .pnl-analysis-section {
    margin: 16px;
    padding: 16px;
  }
  
  .section-header {
    flex-direction: column;
    gap: 12px;
    align-items: flex-start;
  }
  
  .section-header h3 {
    font-size: 1.25rem;
  }
  
  .pnl-summary {
    grid-template-columns: 1fr;
    gap: 12px;
  }
  
  .pnl-card {
    padding: 16px;
  }
  
  .analysis-grid {
    grid-template-columns: 1fr;
    gap: 12px;
  }
}

@media (max-width: 480px) {
  .place-order-modal .modal-dialog {
    max-width: 100%;
    margin: 0.5rem;
  }
  
  .place-order-modal .modal-header {
    padding: 12px 16px;
  }
  
  .place-order-modal .modal-body {
    padding: 0 16px 16px 16px;
  }
  
  .place-order-modal .modal-title {
    font-size: 1.1rem;
  }
  
  .place-order-modal .form-group {
    margin-bottom: 16px;
  }
  
  .place-order-modal .radio-group {
    flex-direction: column;
    gap: 8px;
  }
  
  .place-order-modal .radio-label {
    text-align: center;
    width: 100%;
  }
  
  .place-order-modal .order-summary {
    padding: 12px;
    margin-top: 16px;
  }
}

/* Additional overrides for stubborn elements */
.place-order-modal .modal-backdrop {
  background-color: rgba(0, 0, 0, 0.8) !important;
}

.place-order-modal input[type="text"],
.place-order-modal input[type="number"],
.place-order-modal select,
.place-order-modal textarea {
  background-color: rgba(0, 0, 0, 0.4) !important;
  border: 1px solid rgba(0, 255, 128, 0.4) !important;
  color: white !important;
}

.place-order-modal input[type="text"]:focus,
.place-order-modal input[type="number"]:focus,
.place-order-modal select:focus,
.place-order-modal textarea:focus {
  background-color: rgba(0, 0, 0, 0.5) !important;
  border-color: #00ff80 !important;
  box-shadow: 0 0 0 2px rgba(0, 255, 128, 0.3) !important;
  outline: none !important;
}

.order-form {
  margin-bottom: 24px;
}

.form-group {
  margin-bottom: 20px;
}

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 20px;
  margin-bottom: 20px;
}

.form-label {
  display: block;
  margin-bottom: 8px;
  font-weight: 600;
  color: white !important;
  font-size: 0.875rem;
}

.stock-search-wrapper {
  position: relative;
}

.search-icon {
  position: absolute;
  left: 16px;
  top: 50%;
  transform: translateY(-50%);
  color: #a1a1a1;
}

.stock-search-input,
.form-input {
  width: 100%;
  padding: 12px 16px 12px 48px;
  background: rgba(0, 0, 0, 0.3) !important;
  border: 1px solid rgba(0, 255, 128, 0.3) !important;
  border-radius: 12px;
  color: white !important;
  font-size: 1rem;
  transition: all 0.3s ease;
}

.form-input {
  padding-left: 16px !important;
}

.stock-search-input:focus,
.form-input:focus,
.form-select:focus {
  outline: none !important;
  border-color: #00ff80 !important;
  box-shadow: 0 0 0 3px rgba(0, 255, 128, 0.2) !important;
  background: rgba(0, 0, 0, 0.4) !important;
}

.form-select {
  width: 100%;
  padding: 12px 16px;
  background: rgba(0, 0, 0, 0.3) !important;
  border: 1px solid rgba(0, 255, 128, 0.3) !important;
  border-radius: 12px;
  color: white !important;
  font-size: 1rem;
  transition: all 0.3s ease;
}

.stock-search-input::placeholder,
.form-input::placeholder {
  color: #a1a1a1 !important;
  opacity: 1 !important;
}

.search-results {
  position: absolute;
  top: 100%;
  left: 0;
  right: 0;
  background: rgba(26, 26, 46, 0.95);
  backdrop-filter: blur(10px);
  border: 1px solid rgba(0, 255, 128, 0.3);
  border-radius: 12px;
  margin-top: 8px;
  max-height: 200px;
  overflow-y: auto;
  z-index: 1000;
}

.search-result-item {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 12px 16px;
  cursor: pointer;
  transition: all 0.3s ease;
  border-bottom: 1px solid rgba(255, 255, 255, 0.05);
}

.search-result-item:last-child {
  border-bottom: none;
}

.search-result-item:hover {
  background: rgba(0, 255, 128, 0.1);
}

.result-symbol {
  font-weight: 600;
  color: white;
  font-size: 0.875rem;
}

.result-name {
  font-size: 0.75rem;
  color: #a1a1a1;
  margin-top: 2px;
}

.result-price {
  font-weight: 600;
  color: #00ff80;
  font-size: 0.875rem;
}

.radio-group {
  display: flex;
  gap: 16px;
}

.radio-option {
  display: flex;
  align-items: center;
  cursor: pointer;
}

.radio-option input[type="radio"] {
  display: none;
}

.radio-label {
  padding: 8px 16px;
  border-radius: 8px;
  font-weight: 600;
  transition: all 0.3s ease;
  border: 2px solid transparent;
}

.radio-label.buy {
  background: rgba(34, 197, 94, 0.1);
  color: #22c55e;
}

.radio-label.sell {
  background: rgba(239, 68, 68, 0.1);
  color: #ef4444;
}

.radio-option input[type="radio"]:checked + .radio-label {
  border-color: currentColor;
}

.order-summary {
  background: rgba(0, 0, 0, 0.2) !important;
  border: 1px solid rgba(0, 255, 128, 0.2) !important;
  border-radius: 12px;
  padding: 16px;
  margin-top: 20px;
}

.summary-row {
  display: flex;
  justify-content: space-between;
  margin-bottom: 8px;
}

.summary-row.total {
  border-top: 1px solid rgba(255, 255, 255, 0.1);
  padding-top: 8px;
  margin-top: 8px;
  font-weight: 600;
}

.summary-label {
  color: #a1a1a1 !important;
}

.summary-value {
  color: white !important;
  font-weight: 500;
}

.modal-actions {
  display: flex;
  justify-content: flex-end;
  gap: 12px;
  padding-top: 20px;
  border-top: 1px solid rgba(255, 255, 255, 0.1);
}

.btn-cancel {
  background: rgba(239, 68, 68, 0.2);
  color: #ef4444;
  border: 1px solid rgba(239, 68, 68, 0.3);
  padding: 10px 20px;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  gap: 8px;
}

.btn-cancel:hover {
  background: rgba(239, 68, 68, 0.3);
}

.btn-place-order:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

/* Responsive Design */
@media (max-width: 768px) {
  .header-content {
    flex-direction: column;
    gap: 16px;
    text-align: center;
  }
  

  
  .stats-grid {
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
  }
  
  .detail-row {
    flex-direction: column;
    gap: 12px;
  }
  
  .form-row {
    grid-template-columns: 1fr;
  }
  
  .order-header {
    flex-direction: column;
    gap: 12px;
  }
  
  .order-main-info {
    flex-direction: column;
    align-items: flex-start;
    gap: 8px;
  }
}

@media (max-width: 480px) {
  .orders-container {
    padding-bottom: 80px;
  }
  
  .orders-header,
  .stats-section,
  .orders-content {
    padding: 16px;
  }
  
  .page-title {
    font-size: 1.5rem;
  }
  
  .stats-grid {
    grid-template-columns: 1fr;
  }
  
  .stat-card {
    padding: 16px;
  }
  
  .order-card {
    padding: 16px;
  }
}
</style>
