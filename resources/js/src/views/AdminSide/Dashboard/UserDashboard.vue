<template>
  <div class="modern-dashboard">
    <!-- Dashboard Header -->
    <!-- <div class="dashboard-header">
      <div class="header-content">
        <h1 class="dashboard-title">📊 Live Market Data</h1>
        <p class="dashboard-subtitle">Real-time stock prices from database ({{ totalSymbols }} symbols)</p>
      </div>
      <div class="header-stats">
        <div class="stat-item">
          <span class="stat-label">Market Status</span>
          <span class="stat-value" :class="marketStatus.is_live ? 'market-open' : 'market-closed'">
            {{ marketStatus.status || 'Loading...' }}
          </span>
        </div>
        <div class="stat-item">
          <span class="stat-label">Last Updated</span>
          <span class="stat-value">
            {{ lastUpdated }}
            <span class="live-indicator" v-if="!loading">●</span>
          </span>
        </div>
        <div class="stat-item">
          <span class="stat-label">Current Time</span>
          <span class="stat-value">
            {{ realTimeClock }}
            <span class="live-indicator">●</span>
          </span>
        </div>
      </div>
    </div> -->

    <!-- Quick Stats Cards -->
    <!-- <div class="stats-grid quick-stats">
      <div class="stat-card">
        <div class="stat-icon">
          <i class="fas fa-chart-line"></i>
        </div>
        <div class="stat-content">
          <h3 class="stat-title">Total Symbols</h3>
          <p class="stat-value">{{ totalSymbols }}</p>
          <p class="stat-description">Live market data</p>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon">
          <i class="fas fa-arrow-up"></i>
        </div>
        <div class="stat-content">
          <h3 class="stat-title">Gainers</h3>
          <p class="stat-value">{{ gainersCount }}</p>
          <p class="stat-description">Stocks gaining today</p>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon">
          <i class="fas fa-arrow-down"></i>
        </div>
        <div class="stat-content">
          <h3 class="stat-title">Losers</h3>
          <p class="stat-value">{{ losersCount }}</p>
          <p class="stat-description">Stocks declining today</p>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon">
          <i class="fas fa-clock"></i>
        </div>
        <div class="stat-content">
          <h3 class="stat-title">Last Update</h3>
          <p class="stat-value">{{ lastUpdated }}</p>
          <p class="stat-description">Real-time data</p>
        </div>
      </div>
    </div> -->

    <!-- Live Market Data Section -->
    <div class="section-container">
      <div class="section-header">
        <h2 class="section-title">📈 Live Market Data</h2>
        <div class="header-actions">
          <div class="search-container">
            <input 
              type="text" 
              v-model="searchQuery" 
              placeholder="Search stocks..." 
              class="search-input"
              @input="filterStocks"
            />
            <i class="fas fa-search search-icon"></i>
          </div>
          <button class="refresh-btn" @click="fetchLiveData" :disabled="loading">
            <i class="fas fa-sync-alt" :class="{ 'fa-spin': loading }"></i>
            {{ loading ? 'Updating...' : 'Refresh' }}
          </button>
        </div>
      </div>
      
      <div class="market-data-grid">
        <!-- All Live Stocks -->
        <div class="market-card all-stocks-card">
          <div class="card-header">
            <div class="card-icon blue">📊</div>
            <div class="card-title-section">
              <h3 class="card-title">All Live Stocks</h3>
              <p class="card-subtitle">
                {{ searchQuery ? `${filteredStocks.length} of ${totalSymbols}` : totalSymbols }} symbols from database
                <span class="update-indicator" v-if="!loading && lastUpdated">• Updated {{ lastUpdated }}</span>
              </p>
            </div>
          </div>
          
          <div class="card-content">
            <div v-if="loading" class="loading-state">
              <div class="loading-spinner"></div>
              <p>Loading live market data...</p>
            </div>
            
            <div v-else-if="!liveStocks.length" class="empty-state">
              <div class="empty-icon">📊</div>
              <p>No live data available</p>
            </div>
            
            <div v-else class="stocks-list">
              <div v-for="(stock, i) in filteredStocks" :key="i" class="stock-item">
                <div class="stock-info">
                  <span class="stock-name">{{ stock.symbol }}</span>
                  <span class="stock-symbol">{{ formatNumber(stock.ltp) }}</span>
                </div>
                <div class="stock-metrics">
                  <span class="stock-price">₹{{ formatNumber(stock.ltp) }}</span>
                  <span class="stock-change" :class="stock.change_percent >= 0 ? 'positive' : 'negative'">
                    {{ stock.change_percent >= 0 ? '+' : '' }}{{ stock.change_percent.toFixed(2) }}%
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import axios from "@axios";

// Reactive data
const loading = ref(false);
const marketStatus = ref({});
const liveStocks = ref([]);
const filteredStocks = ref([]);
const lastUpdateTime = ref(null);
const currentTime = ref(new Date());
const searchQuery = ref('');

// Computed properties
const totalSymbols = computed(() => liveStocks.value.length);
const gainersCount = computed(() => liveStocks.value.filter(stock => stock.change_percent > 0).length);
const losersCount = computed(() => liveStocks.value.filter(stock => stock.change_percent < 0).length);

// Real-time last updated timestamp
const lastUpdated = computed(() => {
  if (lastUpdateTime.value) {
    return formatTime(lastUpdateTime.value);
  }
  return 'N/A';
});

// Real-time current time for live indicator
const realTimeClock = computed(() => {
  return formatTime(currentTime.value);
});

// Search functionality
const filterStocks = () => {
  if (!searchQuery.value.trim()) {
    filteredStocks.value = liveStocks.value;
  } else {
    const query = searchQuery.value.toLowerCase();
    filteredStocks.value = liveStocks.value.filter(stock => 
      stock.symbol.toLowerCase().includes(query)
    );
  }
};

// Utility functions
const formatNumber = (num) => {
  if (!num) return '0';
  return parseFloat(num).toLocaleString('en-IN', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  });
};

const formatTime = (timestamp) => {
  if (!timestamp) return 'N/A';
  return new Date(timestamp).toLocaleTimeString('en-IN', {
    hour: '2-digit',
    minute: '2-digit',
    second: '2-digit'
  });
};

// Fetch live data from database
const fetchLiveData = async () => {
  // Only show loading on initial load, not on refresh
  if (liveStocks.value.length === 0) {
    loading.value = true;
  }
  
  try {
    const response = await axios.get('truedata/live-data');
    
    if (response.data.success) {
      const data = response.data.data;
      const newStocks = Object.values(data).map(stock => ({
        symbol: stock.symbol,
        ltp: stock.ltp,
        change: stock.change,
        change_percent: stock.change_percent,
        high: stock.high,
        low: stock.low,
        open: stock.open,
        prev_close: stock.prev_close,
        volume: stock.volume,
        timestamp: stock.timestamp,
        data_source: stock.data_source,
        is_live: stock.is_live,
        market_status: stock.market_status
      }));
      
      // Smooth update - preserve existing stocks and update prices
      if (liveStocks.value.length > 0) {
        // Update existing stocks with new prices
        newStocks.forEach(newStock => {
          const existingIndex = liveStocks.value.findIndex(existing => existing.symbol === newStock.symbol);
          if (existingIndex !== -1) {
            // Update price with smooth transition
            liveStocks.value[existingIndex] = {
              ...liveStocks.value[existingIndex],
              ltp: newStock.ltp,
              change: newStock.change,
              change_percent: newStock.change_percent,
              high: newStock.high,
              low: newStock.low,
              open: newStock.open,
              prev_close: newStock.prev_close,
              volume: newStock.volume,
              timestamp: newStock.timestamp
            };
          } else {
            // Add new stock if not exists
            liveStocks.value.push(newStock);
          }
        });
      } else {
        // Initial load
        liveStocks.value = newStocks;
      }
      
      lastUpdateTime.value = response.data.last_update;
      marketStatus.value = response.data.market_status || {};
      
      // Update filtered stocks
      filterStocks();
      
      console.log(`🔄 Live data updated: ${liveStocks.value.length} symbols from database at ${lastUpdated.value}`);
    } else {
      console.error('Failed to fetch live data:', response.data.message);
    }
  } catch (error) {
    console.error('Error fetching live data:', error);
  } finally {
    loading.value = false;
  }
};

// Fetch market status
const fetchMarketStatus = async () => {
  try {
    const response = await axios.get('truedata/market-status');
    if (response.data.success) {
      marketStatus.value = response.data.data;
    }
  } catch (error) {
    console.error('Error fetching market status:', error);
  }
};

// Initialize data on component mount
onMounted(() => {
  fetchMarketStatus();
  fetchLiveData();
  
  // Auto-refresh every 10 seconds for more real-time updates
  setInterval(() => {
    fetchLiveData();
  }, 10000);
  
  // Also refresh market status every 30 seconds
  setInterval(() => {
    fetchMarketStatus();
  }, 30000);
  
  // Update current time every second for real-time clock
  setInterval(() => {
    currentTime.value = new Date();
  }, 1000);
});
</script>

<style scoped>
.modern-dashboard {
  min-height: 100vh;
  background: linear-gradient(135deg, #0f0f23 0%, #1a1a2e 50%, #16213e 100%);
  color: white;
  padding: 20px;
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
}

.dashboard-header {
  background: rgba(255, 255, 255, 0.05);
  backdrop-filter: blur(10px);
  border-radius: 20px;
  padding: 30px;
  margin-bottom: 30px;
  border: 1px solid rgba(255, 255, 255, 0.1);
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
}

.header-content {
  margin-bottom: 20px;
}

.dashboard-title {
  font-size: 2.5rem;
  font-weight: 700;
  margin: 0 0 10px 0;
  background: linear-gradient(135deg, #00ff80, #00d4ff);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.dashboard-subtitle {
  font-size: 1.1rem;
  color: #94a3b8;
  margin: 0;
}

.header-stats {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 20px;
}

.stat-item {
  display: flex;
  flex-direction: column;
  gap: 5px;
}

.stat-label {
  font-size: 0.9rem;
  color: #94a3b8;
  font-weight: 500;
}

.stat-value {
  font-size: 1.1rem;
  font-weight: 600;
  color: white;
}

.market-open {
  color: #10b981;
}

.market-closed {
  color: #ef4444;
}

.live-indicator {
  color: #00ff80;
  animation: pulse 2s infinite;
  margin-left: 8px;
  font-size: 0.8rem;
}

@keyframes pulse {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.5; }
}

.update-indicator {
  color: #00ff80;
  font-size: 0.8rem;
  margin-left: 8px;
  opacity: 0.8;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 20px;
  margin-bottom: 30px;
}

.stat-card {
  background: rgba(255, 255, 255, 0.05);
  backdrop-filter: blur(10px);
  border-radius: 16px;
  padding: 24px;
  border: 1px solid rgba(255, 255, 255, 0.1);
  transition: all 0.3s ease;
  position: relative;
  overflow: hidden;
}

.stat-card::before {
  content: '';
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.1), transparent);
  transition: left 0.5s ease;
}

.stat-card:hover::before {
  left: 100%;
}

.stat-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
  border-color: rgba(0, 255, 128, 0.3);
}

.stat-icon {
  font-size: 2rem;
  margin-bottom: 16px;
  color: #00ff80;
}

.stat-content h3 {
  font-size: 1rem;
  color: #94a3b8;
  margin: 0 0 8px 0;
  font-weight: 500;
}

.stat-value {
  font-size: 2rem;
  font-weight: 700;
  color: white;
  margin: 0 0 8px 0;
}

.stat-description {
  font-size: 0.9rem;
  color: #64748b;
  margin: 0;
}

.section-container {
  margin-bottom: 40px;
}

.section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
  flex-wrap: wrap;
  gap: 15px;
}

.header-actions {
  display: flex;
  align-items: center;
  gap: 15px;
  flex-wrap: wrap;
}

.search-container {
  position: relative;
  display: flex;
  align-items: center;
}

.search-input {
  background: rgba(255, 255, 255, 0.1);
  border: 1px solid rgba(255, 255, 255, 0.2);
  border-radius: 12px;
  padding: 12px 16px 12px 40px;
  color: white;
  font-size: 14px;
  width: 250px;
  transition: all 0.3s ease;
}

.search-input::placeholder {
  color: #94a3b8;
}

.search-input:focus {
  outline: none;
  border-color: #00ff80;
  background: rgba(255, 255, 255, 0.15);
  box-shadow: 0 0 0 3px rgba(0, 255, 128, 0.1);
}

.search-icon {
  position: absolute;
  left: 12px;
  color: #94a3b8;
  font-size: 14px;
}

.section-title {
  font-size: 1.8rem;
  font-weight: 700;
  margin: 0;
  color: white;
}

.refresh-btn {
  background: linear-gradient(135deg, #00ff80, #00d4ff);
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

.refresh-btn:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(0, 255, 128, 0.3);
}

.refresh-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.market-data-grid {
  display: grid;
  grid-template-columns: 1fr;
  gap: 20px;
}

.market-card {
  background: rgba(255, 255, 255, 0.05);
  backdrop-filter: blur(10px);
  border-radius: 20px;
  border: 1px solid rgba(255, 255, 255, 0.1);
  overflow: hidden;
  transition: all 0.3s ease;
}

.market-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
  border-color: rgba(0, 255, 128, 0.3);
}

.card-header {
  padding: 24px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
  display: flex;
  align-items: center;
  gap: 16px;
}

.card-icon {
  width: 48px;
  height: 48px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
}

.card-icon.blue {
  background: rgba(59, 130, 246, 0.2);
  color: #3b82f6;
}

.card-title-section h3 {
  font-size: 1.3rem;
  font-weight: 600;
  margin: 0 0 4px 0;
  color: white;
}

.card-subtitle {
  font-size: 0.9rem;
  color: #94a3b8;
  margin: 0;
}

.card-content {
  padding: 24px;
}

.loading-state, .empty-state {
  text-align: center;
  padding: 40px 20px;
  color: #94a3b8;
}

.loading-spinner {
  width: 32px;
  height: 32px;
  border: 3px solid rgba(0, 255, 128, 0.2);
  border-top: 3px solid #00ff80;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin: 0 auto 16px;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.empty-icon {
  font-size: 48px;
  margin-bottom: 16px;
}

.stocks-list {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 12px;
  max-height: 600px;
  overflow-y: auto;
  padding-right: 8px;
}

.stocks-list::-webkit-scrollbar {
  width: 6px;
}

.stocks-list::-webkit-scrollbar-track {
  background: rgba(0, 255, 128, 0.1);
  border-radius: 3px;
}

.stocks-list::-webkit-scrollbar-thumb {
  background: rgba(0, 255, 128, 0.3);
  border-radius: 3px;
}

.stocks-list::-webkit-scrollbar-thumb:hover {
  background: rgba(0, 255, 128, 0.5);
}

.stock-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 16px;
  background: rgba(0, 0, 0, 0.2);
  border-radius: 12px;
  border: 1px solid rgba(0, 255, 128, 0.1);
  transition: all 0.3s ease;
}

.stock-item:hover {
  background: rgba(0, 255, 128, 0.05);
  border-color: rgba(0, 255, 128, 0.2);
  transform: translateY(-2px);
}

.stock-info {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.stock-name {
  font-weight: 600;
  color: white;
  font-size: 1rem;
}

.stock-symbol {
  font-size: 0.85rem;
  color: #94a3b8;
}

.stock-metrics {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  gap: 4px;
}

.stock-price {
  font-weight: 600;
  color: white;
  font-size: 1rem;
  transition: all 0.3s ease;
}

.stock-change {
  font-size: 0.9rem;
  font-weight: 600;
  padding: 4px 8px;
  border-radius: 6px;
  transition: all 0.3s ease;
}

.stock-change.positive {
  color: #10b981;
  background: rgba(16, 185, 129, 0.1);
}

.stock-change.negative {
  color: #ef4444;
  background: rgba(239, 68, 68, 0.1);
}

@media (max-width: 768px) {
  .modern-dashboard {
    padding: 15px;
  }
  
  .dashboard-title {
    font-size: 2rem;
  }
  
  .dashboard-subtitle {
    font-size: 1rem;
  }
  
  .stats-grid {
    grid-template-columns: 1fr;
  }
  
  .stocks-list {
    grid-template-columns: 1fr;
  }
  
  .header-stats {
    grid-template-columns: 1fr;
    gap: 15px;
  }
  
  .section-header {
    flex-direction: column;
    align-items: stretch;
    gap: 15px;
  }
  
  .header-actions {
    flex-direction: column;
    align-items: stretch;
    gap: 10px;
  }
  
  .search-input {
    width: 100%;
  }
  
  .refresh-btn {
    width: 100%;
    justify-content: center;
  }
  
  .dashboard-header {
    padding: 20px;
  }
  
  .stat-card {
    padding: 20px;
  }
  
  .card-content {
    padding: 20px;
  }
  
  .stocks-list {
    max-height: 400px;
  }
  
  .stock-item {
    padding: 12px;
  }
  
  .stock-name {
    font-size: 0.9rem;
  }
  
  .stock-price {
    font-size: 0.9rem;
  }
}

@media (max-width: 480px) {
  .modern-dashboard {
    padding: 10px;
  }
  
  .dashboard-title {
    font-size: 1.8rem;
  }
  
  .dashboard-header {
    padding: 15px;
  }
  
  .stat-card {
    padding: 15px;
  }
  
  .card-content {
    padding: 15px;
  }
  
  .stocks-list {
    max-height: 300px;
  }
  
  .stock-item {
    padding: 10px;
    flex-direction: column;
    align-items: flex-start;
    gap: 8px;
  }
  
  .stock-metrics {
    align-items: flex-start;
  }
}
</style>