<template>
  <div class="modern-dashboard">
    <!-- Dashboard Header -->
    <div class="dashboard-header">
      <div class="header-content">
        <h1 class="dashboard-title">📊 Market Dashboard</h1>
        <p class="dashboard-subtitle">Real-time market insights and analytics</p>
      </div>
      <div class="header-stats">
        <div class="stat-item">
          <span class="stat-label">Last Updated</span>
          <span class="stat-value">{{ lastUpdated }}</span>
        </div>
      </div>
    </div>

    <!-- Quick Stats Cards -->
    <div class="stats-grid">
      <div class="stat-card trending-up">
        <div class="stat-icon">📈</div>
        <div class="stat-content">
          <h3 class="stat-title">Market Trend</h3>
          <p class="stat-number">Bullish</p>
          <span class="stat-change">+2.4% today</span>
        </div>
      </div>
      
      <div class="stat-card">
        <div class="stat-icon">💰</div>
        <div class="stat-content">
          <h3 class="stat-title">Active IPOs</h3>
          <p class="stat-number">{{ activeIPOCount }}</p>
          <span class="stat-change">{{ upcomingIPOCount }} upcoming</span>
        </div>
      </div>
      
      <div class="stat-card">
        <div class="stat-icon">🎯</div>
        <div class="stat-content">
          <h3 class="stat-title">Top Gainers</h3>
          <p class="stat-number">{{ topGainersCount }}</p>
          <span class="stat-change">Above 5%</span>
        </div>
      </div>
      
      <div class="stat-card">
        <div class="stat-icon">⚡</div>
        <div class="stat-content">
          <h3 class="stat-title">Volume Leaders</h3>
          <p class="stat-number">{{ volumeLeadersCount }}</p>
          <span class="stat-change">High activity</span>
        </div>
      </div>
    </div>

    <!-- Market Movers Section -->
    <div class="section-container">
      <div class="section-header">
        <h2 class="section-title">🚀 Market Movers</h2>
        <button class="refresh-btn" @click="fetchMarketMovers" :disabled="loading">
          <i class="fas fa-sync-alt" :class="{ 'fa-spin': loading }"></i>
          Refresh
        </button>
      </div>
      
      <div class="market-cards-grid">
        <!-- Top Gainers Card -->
        <div class="market-card gainers-card">
          <div class="card-header">
            <div class="card-icon green">📈</div>
            <div class="card-title-section">
              <h3 class="card-title">Top Gainers</h3>
              <p class="card-subtitle">Stocks with highest gains</p>
            </div>
          </div>
          
          <div class="card-content">
            <div v-if="loading" class="loading-state">
              <div class="loading-spinner"></div>
              <p>Loading market data...</p>
            </div>
            
            <div v-else-if="!allStocks.top_gainers?.length" class="empty-state">
              <div class="empty-icon">📊</div>
              <p>No gainers data available</p>
            </div>
            
            <div v-else class="stocks-list">
              <div v-for="(stock, i) in allStocks.top_gainers?.slice(0, 5)" :key="i" class="stock-item">
                <div class="stock-info">
                  <span class="stock-name">{{ stock.company_name }}</span>
                  <span class="stock-symbol">{{ stock.symbol }}</span>
                </div>
                <div class="stock-metrics">
                  <span class="stock-price">₹{{ formatNumber(stock.price) }}</span>
                  <span class="stock-change positive">+{{ stock.percent_change }}%</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Top Losers Card -->
        <div class="market-card losers-card">
          <div class="card-header">
            <div class="card-icon red">📉</div>
            <div class="card-title-section">
              <h3 class="card-title">Top Losers</h3>
              <p class="card-subtitle">Stocks with highest losses</p>
            </div>
          </div>
          
          <div class="card-content">
            <div v-if="loading" class="loading-state">
              <div class="loading-spinner"></div>
              <p>Loading market data...</p>
            </div>
            
            <div v-else-if="!allStocks.top_losers?.length" class="empty-state">
              <div class="empty-icon">📊</div>
              <p>No losers data available</p>
            </div>
            
            <div v-else class="stocks-list">
              <div v-for="(stock, i) in allStocks.top_losers?.slice(0, 5)" :key="i" class="stock-item">
                <div class="stock-info">
                  <span class="stock-name">{{ stock.company_name }}</span>
                  <span class="stock-symbol">{{ stock.symbol }}</span>
                </div>
                <div class="stock-metrics">
                  <span class="stock-price">₹{{ formatNumber(stock.price) }}</span>
                  <span class="stock-change negative">{{ stock.percent_change }}%</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- IPO Section -->
    <div class="section-container">
      <div class="section-header">
        <h2 class="section-title">🏢 IPO Updates</h2>
        <div class="section-actions">
          <button class="view-all-btn">View All IPOs</button>
        </div>
      </div>
      <div class="component-wrapper">
        <ipo/>
      </div>
    </div>

    <!-- Stock Details Section -->
    <div class="section-container">
      <div class="section-header">
        <h2 class="section-title">📊 Featured Stock</h2>
        <div class="section-actions">
          <button class="view-all-btn">View All Stocks</button>
        </div>
      </div>
      <div class="component-wrapper">
        <stockDetails/>
      </div>
    </div>

    <!-- Exchange Data Section -->
    <div class="section-container">
      <div class="section-header">
        <h2 class="section-title">🏛️ Exchange Data</h2>
      </div>
      
      <div class="exchange-grid">
        <div class="exchange-card">
          <div class="exchange-header">
            <h3 class="exchange-title">BSE Most Active</h3>
            <span class="exchange-badge bse">BSE</span>
          </div>
          <div class="component-wrapper">
            <BseMostActive/>
          </div>
        </div>
        
        <div class="exchange-card">
          <div class="exchange-header">
            <h3 class="exchange-title">NSE Most Active</h3>
            <span class="exchange-badge nse">NSE</span>
          </div>
          <div class="component-wrapper">
            <NseMostActiveStock/>
          </div>
        </div>
      </div>
    </div>

    <!-- 52-Week High/Low Section -->
    <div class="section-container">
      <div class="section-header">
        <h2 class="section-title">📅 52-Week Performance</h2>
      </div>
      <div class="component-wrapper">
        <yearWeekHighLow/>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import axios from "axios";
import ipo from "./ipo.vue";
import stockDetails from "./stockDetails.vue";
import BseMostActive from "./BseMostActive.vue";
import NseMostActiveStock from "./NseMostActiveStock.vue";
import yearWeekHighLow from "./52-WeekHighLow.vue";

// Reactive data
const loading = ref(false);
const lastUpdated = ref('');
const allStocks = ref({
  top_gainers: [],
  top_losers: [],
  volume_shockers: [],
});

// Computed stats
const activeIPOCount = ref(8);
const upcomingIPOCount = ref(12);
const topGainersCount = computed(() => allStocks.value.top_gainers?.length || 0);
const volumeLeadersCount = ref(15);

// Utility functions
const formatNumber = (num) => {
  if (!num) return '0';
  return parseFloat(num).toLocaleString('en-IN', { 
    minimumFractionDigits: 2, 
    maximumFractionDigits: 2 
  });
};

const updateLastUpdated = () => {
  const now = new Date();
  lastUpdated.value = now.toLocaleTimeString('en-IN', {
    hour: '2-digit',
    minute: '2-digit',
    second: '2-digit'
  });
};

// Fetch market movers data
const fetchMarketMovers = async () => {
  loading.value = true;
  try {
    const res = await axios.get("https://stock.indianapi.in/trending", {
      headers: {
        "x-api-key": "sk-live-dO2pc2cIt3N2ZVUMJEnGqiIP1NXu1be88iWGXEVX",
      },
    });
    
    if (res.data && res.data.trending_stocks) {
      allStocks.value = res.data.trending_stocks;
      updateLastUpdated();
    }
  } catch (error) {
    console.error("Market Movers API error:", error.response?.data || error.message);
    // Set dummy data for demo purposes
    allStocks.value = {
      top_gainers: [
        { company_name: "Reliance Industries", symbol: "RELIANCE", price: 2456.75, percent_change: 3.45, net_change: 82.15 },
        { company_name: "TCS", symbol: "TCS", price: 3890.20, percent_change: 2.78, net_change: 105.30 },
        { company_name: "Infosys", symbol: "INFY", price: 1678.90, percent_change: 2.15, net_change: 35.40 },
        { company_name: "HDFC Bank", symbol: "HDFCBANK", price: 1534.60, percent_change: 1.89, net_change: 28.45 },
        { company_name: "ICICI Bank", symbol: "ICICIBANK", price: 987.30, percent_change: 1.67, net_change: 16.20 }
      ],
      top_losers: [
        { company_name: "Adani Enterprises", symbol: "ADANIENT", price: 2234.50, percent_change: -2.45, net_change: -56.10 },
        { company_name: "Bajaj Finance", symbol: "BAJFINANCE", price: 6789.40, percent_change: -1.98, net_change: -137.20 },
        { company_name: "Asian Paints", symbol: "ASIANPAINT", price: 3456.80, percent_change: -1.76, net_change: -61.90 },
        { company_name: "Maruti Suzuki", symbol: "MARUTI", price: 9876.50, percent_change: -1.45, net_change: -145.30 },
        { company_name: "Titan Company", symbol: "TITAN", price: 2987.60, percent_change: -1.23, net_change: -37.20 }
      ]
    };
    updateLastUpdated();
  } finally {
    loading.value = false;
  }
};

// Initialize dashboard
onMounted(() => {
  fetchMarketMovers();
  updateLastUpdated();
  
  // Update time every minute
  setInterval(updateLastUpdated, 60000);
});
</script>


<style scoped>
/* Modern Dashboard Styling */
.modern-dashboard {
  background: linear-gradient(135deg, #0d0d1a 0%, #1a1a2e 100%);
  min-height: 100vh;
  padding: 24px;
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
  color: white;
}

/* Dashboard Header */
.dashboard-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 32px;
  padding: 24px;
  background: linear-gradient(145deg, #16213e, #0f172a);
  border-radius: 16px;
  border: 1px solid rgba(0, 255, 128, 0.2);
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
}

.dashboard-title {
  font-size: 32px;
  font-weight: 700;
  margin: 0;
  background: linear-gradient(135deg, #00ff80, #00cc66);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.dashboard-subtitle {
  font-size: 16px;
  color: #94a3b8;
  margin: 4px 0 0 0;
}

.header-stats .stat-item {
  text-align: right;
}

.stat-label {
  display: block;
  font-size: 12px;
  color: #64748b;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.stat-value {
  display: block;
  font-size: 16px;
  font-weight: 600;
  color: #00ff80;
  margin-top: 4px;
}

/* Quick Stats Grid */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 20px;
  margin-bottom: 32px;
}

.stat-card {
  background: linear-gradient(145deg, #1e293b, #0f172a);
  border-radius: 16px;
  padding: 24px;
  border: 1px solid rgba(0, 255, 128, 0.1);
  transition: all 0.3s ease;
  position: relative;
  overflow: hidden;
}

.stat-card::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 3px;
  background: linear-gradient(90deg, #00ff80, #00cc66);
  opacity: 0;
  transition: opacity 0.3s ease;
}

.stat-card:hover {
  transform: translateY(-4px);
  border-color: rgba(0, 255, 128, 0.3);
  box-shadow: 0 12px 40px rgba(0, 255, 128, 0.1);
}

.stat-card:hover::before {
  opacity: 1;
}

.stat-card.trending-up {
  border-color: rgba(0, 255, 128, 0.3);
}

.stat-card.trending-up::before {
  opacity: 1;
}

.stat-icon {
  font-size: 32px;
  margin-bottom: 12px;
  display: block;
}

.stat-title {
  font-size: 14px;
  color: #94a3b8;
  margin: 0 0 8px 0;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.stat-number {
  font-size: 28px;
  font-weight: 700;
  color: #00ff80;
  margin: 0 0 4px 0;
}

.stat-change {
  font-size: 12px;
  color: #64748b;
}

/* Section Containers */
.section-container {
  background: linear-gradient(145deg, #1e293b, #0f172a);
  border-radius: 16px;
  padding: 24px;
  margin-bottom: 24px;
  border: 1px solid rgba(0, 255, 128, 0.1);
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
}

.section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24px;
  padding-bottom: 16px;
  border-bottom: 1px solid rgba(0, 255, 128, 0.1);
}

.section-title {
  font-size: 24px;
  font-weight: 600;
  margin: 0;
  color: #00ff80;
}

.section-actions {
  display: flex;
  gap: 12px;
}

.refresh-btn, .view-all-btn {
  background: rgba(0, 255, 128, 0.1);
  border: 1px solid rgba(0, 255, 128, 0.3);
  color: #00ff80;
  padding: 8px 16px;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  gap: 8px;
}

.refresh-btn:hover, .view-all-btn:hover {
  background: rgba(0, 255, 128, 0.2);
  border-color: #00ff80;
  transform: translateY(-1px);
}

.refresh-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
  transform: none;
}

/* Market Cards Grid */
.market-cards-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
  gap: 24px;
}

.market-card {
  background: linear-gradient(145deg, #334155, #1e293b);
  border-radius: 12px;
  overflow: hidden;
  border: 1px solid rgba(0, 255, 128, 0.1);
  transition: all 0.3s ease;
}

.market-card:hover {
  transform: translateY(-2px);
  border-color: rgba(0, 255, 128, 0.3);
  box-shadow: 0 8px 32px rgba(0, 255, 128, 0.1);
}

.gainers-card {
  border-left: 4px solid #10b981;
}

.losers-card {
  border-left: 4px solid #ef4444;
}

.card-header {
  display: flex;
  align-items: center;
  padding: 20px;
  background: rgba(0, 0, 0, 0.2);
  border-bottom: 1px solid rgba(0, 255, 128, 0.1);
}

.card-icon {
  font-size: 24px;
  margin-right: 16px;
  width: 48px;
  height: 48px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 12px;
}

.card-icon.green {
  background: rgba(16, 185, 129, 0.2);
}

.card-icon.red {
  background: rgba(239, 68, 68, 0.2);
}

.card-title {
  font-size: 18px;
  font-weight: 600;
  margin: 0 0 4px 0;
  color: white;
}

.card-subtitle {
  font-size: 14px;
  color: #94a3b8;
  margin: 0;
}

.card-content {
  padding: 20px;
}

/* Loading and Empty States */
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

/* Stocks List */
.stocks-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.stock-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 12px 16px;
  background: rgba(0, 0, 0, 0.2);
  border-radius: 8px;
  border: 1px solid rgba(0, 255, 128, 0.1);
  transition: all 0.3s ease;
}

.stock-item:hover {
  background: rgba(0, 255, 128, 0.05);
  border-color: rgba(0, 255, 128, 0.2);
}

.stock-info {
  display: flex;
  flex-direction: column;
}

.stock-name {
  font-weight: 600;
  color: white;
  font-size: 14px;
}

.stock-symbol {
  font-size: 12px;
  color: #94a3b8;
  margin-top: 2px;
}

.stock-metrics {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
}

.stock-price {
  font-weight: 600;
  color: white;
  font-size: 14px;
}

.stock-change {
  font-size: 12px;
  font-weight: 600;
  margin-top: 2px;
}

.stock-change.positive {
  color: #10b981;
}

.stock-change.negative {
  color: #ef4444;
}

/* Exchange Grid */
.exchange-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
  gap: 24px;
}

.exchange-card {
  background: linear-gradient(145deg, #334155, #1e293b);
  border-radius: 12px;
  overflow: hidden;
  border: 1px solid rgba(0, 255, 128, 0.1);
}

.exchange-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px;
  background: rgba(0, 0, 0, 0.2);
  border-bottom: 1px solid rgba(0, 255, 128, 0.1);
}

.exchange-title {
  font-size: 18px;
  font-weight: 600;
  margin: 0;
  color: white;
}

.exchange-badge {
  padding: 4px 12px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 600;
  text-transform: uppercase;
}

.exchange-badge.bse {
  background: rgba(239, 68, 68, 0.2);
  color: #ef4444;
}

.exchange-badge.nse {
  background: rgba(59, 130, 246, 0.2);
  color: #3b82f6;
}

/* Component Wrapper */
.component-wrapper {
  padding: 0;
}

/* Responsive Design */
@media (max-width: 768px) {
  .modern-dashboard {
    padding: 16px;
  }
  
  .dashboard-header {
    flex-direction: column;
    gap: 16px;
    text-align: center;
  }
  
  .dashboard-title {
    font-size: 24px;
  }
  
  .stats-grid {
    grid-template-columns: 1fr;
    gap: 16px;
  }
  
  .market-cards-grid {
    grid-template-columns: 1fr;
    gap: 16px;
  }
  
  .exchange-grid {
    grid-template-columns: 1fr;
    gap: 16px;
  }
  
  .section-header {
    flex-direction: column;
    gap: 12px;
    text-align: center;
  }
  
  .section-title {
    font-size: 20px;
  }
}

@media (max-width: 480px) {
  .market-cards-grid {
    grid-template-columns: 1fr;
  }
  
  .card-header {
    padding: 16px;
  }
  
  .card-content {
    padding: 16px;
  }
  
  .stock-item {
    padding: 10px 12px;
  }
}
</style>
