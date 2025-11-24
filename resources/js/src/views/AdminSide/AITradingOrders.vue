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
        </div>
      </div>
    </div>

    <!-- Debug Info -->
    <!-- <div style="background: #333; color: white; padding: 10px; margin: 10px 0; border-radius: 5px;">
      <strong>Debug Info:</strong><br>
      Loading: {{ loading }}<br>
      User Orders Length: {{ userOrders.length }}<br>
      Filtered Orders Length: {{ filteredOrders.length }}<br>
      User ID: {{ user.id }}
    </div> -->

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
              <span class="detail-label">Unit Price:</span>
              <span class="detail-value unit-price">₹{{ formatUnitPrice(order.unit_price) }}</span>
            </div>
            <div class="detail-row">
              <span class="detail-label">Lot Size:</span>
              <span class="detail-value lot-size">{{ formatLotSize(order.lot_size) }}</span>
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
              <span class="detail-value">₹{{ order.exit_price * order.quantity }}</span>
            </div>
            <div v-if="order.exit_price && order.total_amount" class="detail-row">
    <span class="detail-label">Profit / Loss:</span>
    <span
      class="detail-value"
      :class="(order.exit_price * order.quantity - order.total_amount) >= 0 ? 'profit' : 'loss'"
    >
      {{ (order.exit_price * order.quantity - order.total_amount) >= 0 ? '+' : '-' }}
      ₹{{ Math.abs(order.exit_price * order.quantity - order.total_amount).toLocaleString() }}
    </span>
  </div>
          </div>

          <div v-if="order.status === 'COMPLETED'" class="order-actions">
            <button 
              class="exit-trade-btn" 
              @click="openExitModal(order)"
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
    <!-- Exit Trade Modal -->
    <div v-if="showExitModal" class="modal-overlay" @click="closeExitModal">
      <div class="exit-modal" @click.stop>
        <div class="modal-header">
          <h3 class="modal-title">
            <i class="fas fa-sign-out-alt"></i>
            Exit Trade
          </h3>
          <button class="close-btn" @click="closeExitModal">
            <i class="fas fa-times"></i>
          </button>
        </div>

        <div class="modal-body">
          <div v-if="selectedOrder" class="exit-trade-info">
            <div class="info-section">
              <h4>Trade Details</h4>
              <div class="info-grid">
                <div class="info-item">
                  <span class="label">Stock:</span>
                  <span class="value">{{ selectedOrder.stock_symbol }}</span>
                </div>
                <div class="info-item">
                  <span class="label">Option Type:</span>
                  <span class="value">{{ selectedOrder.option_type }}</span>
                </div>
                <div class="info-item">
                  <span class="label">Action:</span>
                  <span class="value">{{ selectedOrder.action }}</span>
                </div>
                <div class="info-item">
                  <span class="label">Strike Price:</span>
                  <span class="value">₹{{ selectedOrder.strike_price }}</span>
                </div>
                <div class="info-item">
                  <span class="label">Original Unit Price:</span>
                  <span class="value">₹{{ formatUnitPrice(selectedOrder.unit_price) }}</span>
                </div>
                <div class="info-item">
                  <span class="label">Lot Size:</span>
                  <span class="value">{{ formatLotSize(selectedOrder.lot_size) }}</span>
                </div>
                <div class="info-item">
                  <span class="label">Quantity:</span>
                  <span class="value">{{ selectedOrder.quantity }}</span>
                </div>
              </div>
            </div>

            <div class="exit-price-section">
              <h4>Exit Price Details</h4>
              <div class="form-group">
                <label class="form-label">Exit Unit Price</label>
                <div class="input-group">
                  <span class="input-prefix">₹</span>
                  <input
                    type="number"
                    v-model="exitForm.exitUnitPrice"
                    class="form-input"
                    step="0.01"
                    min="0"
                    placeholder="Enter exit unit price"
                    @input="calculateExitPrice"
                  />
                </div>
                <div class="input-hint">Original unit price: ₹{{ formatUnitPrice(selectedOrder.unit_price) }}</div>
              </div>

              <div class="calculated-price">
                <div class="price-row">
                  <span class="price-label">Exit Price (Unit Price × Lot Size):</span>
                  <span class="price-value">₹{{ formatPrice(calculatedExitPrice) }}</span>
                </div>
                <div class="price-row">
                  <span class="price-label">Total Exit Amount (Exit Price × Quantity):</span>
                  <span class="price-value total">₹{{ formatPrice(totalExitAmount) }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="modal-footer">
          <button class="btn btn-secondary" @click="closeExitModal">
            Cancel
          </button>
          <button 
            class="btn btn-primary" 
            @click="confirmExitTrade"
            :disabled="!exitForm.exitUnitPrice || exitForm.exitUnitPrice <= 0"
          >
            <i class="fas fa-sign-out-alt"></i>
            Exit Trade
          </button>
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
      showExitModal: false,
      selectedOrder: null,
      exitForm: {
        exitUnitPrice: 0
      }
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
    calculatedExitPrice() {
      if (!this.selectedOrder || !this.exitForm.exitUnitPrice) {
        return 0;
      }
      const unitPrice = Number(this.exitForm.exitUnitPrice);
      const quantity = Number(this.selectedOrder.quantity);
      const lotSize = Number(this.selectedOrder.lot_size) || 0;
      return unitPrice * lotSize;
    },
    totalExitAmount() {
      if (!this.selectedOrder) {
        return 0;
      }
      const quantity = Number(this.selectedOrder.quantity) || 0;
      return this.calculatedExitPrice * quantity;
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
      
      // Load user orders only
      this.loadUserOrders();
      
    } catch (error) {
      console.error('Error in mounted lifecycle:', error);
      this.showError('Failed to initialize component');
    }
  },
  methods: {
    formatUnitPrice(unitPrice) {
      if (!unitPrice || unitPrice === null || unitPrice === undefined) {
        return 'N/A';
      }
      const num = Number(unitPrice);
      return isNaN(num) ? 'N/A' : num.toFixed(2);
    },
    formatLotSize(lotSize) {
      if (!lotSize || lotSize === null || lotSize === undefined) {
        return 'N/A';
      }
      const num = Number(lotSize);
      return isNaN(num) ? 'N/A' : num.toString();
    },
    calculateExitPrice() {
      // This will be called when exit unit price changes
      // The computed properties will automatically update
    },
    openExitModal(order) {
      this.selectedOrder = order;
      this.exitForm.exitUnitPrice = Number(order.unit_price) || 0;
      this.showExitModal = true;
    },
    closeExitModal() {
      this.showExitModal = false;
      this.selectedOrder = null;
      this.exitForm.exitUnitPrice = 0;
    },
    async confirmExitTrade() {
      if (!this.selectedOrder || !this.exitForm.exitUnitPrice) {
        this.showError('Please enter a valid exit unit price');
        return;
      }

      try {
        this.exitingTrade = this.selectedOrder.id;
        
        const response = await axios.post(`ai-trading/orders/${this.selectedOrder.id}/exit`, {
          exit_unit_price: this.exitForm.exitUnitPrice,
          exit_price: this.calculatedExitPrice
        });

        if (response.data.success) {
          this.showSuccess('Trade exited successfully');
          await this.loadUserOrders();
          await this.loadUserBalance();
          this.closeExitModal();
        } else {
          this.showError(response.data.message || 'Failed to exit trade');
        }
      } catch (error) {
        console.error('Error exiting trade:', error);
        this.showError('Failed to exit trade');
      } finally {
        this.exitingTrade = null;
      }
    },    goBack() {
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
    async refreshAllData() {
      this.loading = true;
      try {
        await this.loadUserOrders();
        this.showSuccess('Data refreshed successfully');
      } catch (error) {
        this.showError('Failed to refresh data');
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
  border: 1px solid rgba(255, 215, 0, 0.2);
  border-radius: 16px;
}

.header-content {
  display: flex;
  align-items: center;
  gap: 20px;
}

.back-btn {
  background: linear-gradient(145deg, #1a1a2e, #16213e);
  color: #FFD700;
  border: 1px solid #FFD700;
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
  background: #FFD700;
  color: #0d0d1a;
  transform: translateY(-2px);
}

.page-title {
  font-size: 28px;
  font-weight: bold;
  color: #FFD700;
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
  background: rgba(255, 215, 0, 0.2);
  color: #FFD700;
  border: 1px solid rgba(255, 215, 0, 0.3);
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
  border-color: #FFD700;
  color: #FFD700;
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
  background: #FFD700;
  box-shadow: 0 0 10px rgba(255, 215, 0, 0.5);
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
  color: #FFD700;
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
  border-color: #FFD700;
  color: #FFD700;
}

.toggle-btn.active {
  background: linear-gradient(145deg, #FFD700, #00cc6a);
  color: #0d0d1a;
  border-color: #FFD700;
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
  border-color: #FFD700;
  background: rgba(255, 215, 0, 0.1);
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
  background: #FFD700;
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
  color: #FFD700;
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
  color: #FFD700;
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
  border-color: #FFD700;
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
  color: #FFD700;
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
  border-color: rgba(255, 215, 0, 0.3);
  background: rgba(255, 215, 0, 0.05);
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
  color: #FFD700;
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
  color: #FFD700;
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
  color: #FFD700;
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
  color: #FFD700;
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
  border-color: #FFD700;
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
  border-top: 3px solid #FFD700;
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
  border-color: #FFD700;
  transform: translateY(-2px);
}

.order-card.completed {
  border-left: 4px solid #FFD700;
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
  color: #FFD700;
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
  color: #FFD700;
  font-weight: bold;
}

.detail-value.unit-price {
  color: #2196f3;
  font-weight: 600;
}

.detail-value.lot-size {
  color: #ff9800;
  font-weight: 600;
}

.detail-value.pnl.profit {
  color: #FFD700;
  font-weight: bold;
}

.detail-value.pnl.loss {
  color: #ff4444;
  font-weight: bold;
}

.option-type.call {
  color: #FFD700;
}

.option-type.put {
  color: #ff4444;
}

.action-type.buy {
  color: #FFD700;
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
  background: rgba(255, 215, 0, 0.05);
  border: 1px solid rgba(255, 215, 0, 0.2);
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
  background: linear-gradient(90deg, transparent, rgba(255, 215, 0, 0.1), transparent);
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
  }

  .exit-modal {
    background: #1a1a2e;
    border: 1px solid rgba(255, 215, 0, 0.3);
    border-radius: 12px;
    width: 90%;
    max-width: 600px;
    max-height: 90vh;
    overflow-y: auto;
  }

  .modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
  }

  .modal-title {
    margin: 0;
    color: #FFD700;
    display: flex;
    align-items: center;
    gap: 10px;
  }

  .close-btn {
    background: none;
    border: none;
    color: #ccc;
    font-size: 20px;
    cursor: pointer;
    padding: 5px;
    border-radius: 4px;
    transition: all 0.3s ease;
  }

  .close-btn:hover {
    background: rgba(255, 255, 255, 0.1);
    color: white;
  }

  .modal-body {
    padding: 20px;
  }

  .info-section h4 {
    color: #FFD700;
    margin: 0 0 15px 0;
    font-size: 18px;
  }

  .info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 10px;
    margin-bottom: 20px;
  }

  .info-item {
    display: flex;
    justify-content: space-between;
    padding: 8px 0;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
  }

  .info-item .label {
    color: #ccc;
    font-weight: 500;
  }

  .info-item .value {
    color: white;
    font-weight: 600;
  }

  .exit-price-section h4 {
    color: #FFD700;
    margin: 0 0 15px 0;
    font-size: 18px;
  }

  .form-group {
    margin-bottom: 20px;
  }

  .form-label {
    display: block;
    color: #ccc;
    font-weight: 500;
    margin-bottom: 8px;
  }

  .input-group {
    display: flex;
    align-items: center;
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 8px;
    overflow: hidden;
  }

  .input-prefix {
    padding: 12px 15px;
    background: rgba(255, 215, 0, 0.1);
    color: #FFD700;
    font-weight: 600;
    border-right: 1px solid rgba(255, 255, 255, 0.1);
  }

  .form-input {
    flex: 1;
    background: none;
    border: none;
    color: white;
    padding: 12px 15px;
    font-size: 16px;
    outline: none;
  }

  .form-input:focus {
    background: rgba(255, 255, 255, 0.05);
  }

  .input-hint {
    color: #999;
    font-size: 12px;
    margin-top: 5px;
  }

  .calculated-price {
    background: rgba(255, 215, 0, 0.05);
    border: 1px solid rgba(255, 215, 0, 0.2);
    border-radius: 8px;
    padding: 15px;
  }

  .price-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 0;
  }

  .price-row:not(:last-child) {
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
  }

  .price-label {
    color: #ccc;
    font-weight: 500;
  }

  .price-value {
    color: white;
    font-weight: 600;
  }

  .price-value.total {
    color: #FFD700;
    font-size: 18px;
    font-weight: bold;
  }

  .modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    padding: 20px;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
  }

  .btn {
    padding: 10px 20px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 600;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 8px;
  }

  .btn-secondary {
    background: rgba(255, 255, 255, 0.1);
    color: #ccc;
    border: 1px solid rgba(255, 255, 255, 0.2);
  }

  .btn-secondary:hover {
    background: rgba(255, 255, 255, 0.2);
    color: white;
  }

  .btn-primary {
    background: linear-gradient(135deg, #ff6b6b, #ee5a52);
    color: white;
  }

  .btn-primary:hover:not(:disabled) {
    background: linear-gradient(135deg, #ff5252, #e53935);
    transform: translateY(-2px);
  }

  .btn-primary:disabled {
    opacity: 0.6;
    cursor: not-allowed;
  }

  /* Responsive Design */
  @media (max-width: 768px) {
    .info-grid {
      grid-template-columns: 1fr;
    }
    
    .modal-footer {
      flex-direction: column;
    }
    
    .btn {
      width: 100%;
      justify-content: center;
    }
    .detail-value.profit {
  color: green;
  font-weight: bold;
}
.detail-value.loss {
  color: red;
  font-weight: bold;
}

  }
</style>
