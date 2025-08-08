<template>
  <div class="markets-page">
    <!-- Trading Background Animation -->
    <div class="trading-background">
      <div class="stock-chart chart-1">
        <div class="candlestick up"></div>
        <div class="candlestick down"></div>
        <div class="candlestick up"></div>
        <div class="candlestick up"></div>
        <div class="candlestick down"></div>
      </div>
      <div class="stock-chart chart-2">
        <div class="candlestick down"></div>
        <div class="candlestick up"></div>
        <div class="candlestick up"></div>
        <div class="candlestick down"></div>
        <div class="candlestick up"></div>
      </div>
      
      <div class="price-tickers">
        <div class="ticker">
          <span class="symbol">AAPL</span>
          <span class="price up">$185.42</span>
          <span class="change up">+2.3%</span>
        </div>
        <div class="ticker">
          <span class="symbol">TSLA</span>
          <span class="price down">$248.50</span>
          <span class="change down">-1.2%</span>
        </div>
        <div class="ticker">
          <span class="symbol">NVDA</span>
          <span class="price up">$485.09</span>
          <span class="change up">+3.8%</span>
        </div>
      </div>
    </div>

    <!-- Header -->
    <header class="markets-header">
      <div class="header-container">
        <button class="back-button" @click="goBack">
          <i class="fas fa-arrow-left"></i>
          Back to Home
        </button>
        
        <div class="header-content">
          <h1>Global Markets</h1>
          <p>Real-time market data and live charts</p>
        </div>
        
        <div class="market-status">
          <div class="status-indicator live"></div>
          <span>Live Data</span>
        </div>
      </div>
    </header>

    <!-- Market Overview Section -->
    <section class="market-overview">
      <div class="container">
        <div class="section-header">
          <h2>Market Overview</h2>
          <div class="market-filters">
            <button 
              v-for="filter in marketFilters" 
              :key="filter.id"
              :class="['filter-btn', { active: activeFilter === filter.id }]"
              @click="activeFilter = filter.id"
            >
              {{ filter.name }}
            </button>
          </div>
        </div>
        
        <div class="market-grid">
          <div v-for="market in filteredMarkets" :key="market.symbol" class="market-card">
            <div class="market-header">
              <div class="market-info">
                <h3>{{ market.name }}</h3>
                <span class="symbol">{{ market.symbol }}</span>
              </div>
              <div class="market-change" :class="{ up: market.change >= 0, down: market.change < 0 }">
                <span class="change-value">{{ market.change >= 0 ? '+' : '' }}{{ market.change.toFixed(2) }}%</span>
                <span class="change-arrow">{{ market.change >= 0 ? '↗' : '↘' }}</span>
              </div>
            </div>
            
            <div class="market-price">
              <span class="price-value">${{ market.price.toFixed(2) }}</span>
              <span class="price-change">{{ market.change >= 0 ? '+' : '' }}{{ market.priceChange.toFixed(2) }}</span>
            </div>
            
            <div class="market-chart">
              <div class="mini-chart">
                <div 
                  v-for="(point, index) in market.chartData" 
                  :key="index"
                  class="chart-bar"
                  :style="{ height: point + '%' }"
                  :class="{ up: point > 50, down: point <= 50 }"
                ></div>
              </div>
            </div>
            
            <div class="market-stats">
              <div class="stat">
                <span class="stat-label">Open</span>
                <span class="stat-value">${{ market.open.toFixed(2) }}</span>
              </div>
              <div class="stat">
                <span class="stat-label">High</span>
                <span class="stat-value">${{ market.high.toFixed(2) }}</span>
              </div>
              <div class="stat">
                <span class="stat-label">Low</span>
                <span class="stat-value">${{ market.low.toFixed(2) }}</span>
              </div>
              <div class="stat">
                <span class="stat-label">Vol</span>
                <span class="stat-value">{{ market.volume }}M</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Live Charts Section -->
    <section class="live-charts">
      <div class="container">
        <div class="section-header">
          <h2>Live Charts</h2>
          <div class="chart-controls">
            <button class="timeframe-btn active">1D</button>
            <button class="timeframe-btn">1W</button>
            <button class="timeframe-btn">1M</button>
            <button class="timeframe-btn">3M</button>
            <button class="timeframe-btn">1Y</button>
          </div>
        </div>
        
        <div class="charts-grid">
          <div class="chart-container">
            <div class="chart-header">
              <h3>AAPL - Apple Inc.</h3>
              <div class="chart-price">
                <span class="current-price">$185.42</span>
                <span class="price-change up">+4.15 (+2.29%)</span>
              </div>
            </div>
            <div class="chart-widget">
              <!-- TradingView Widget Placeholder -->
              <div class="chart-placeholder">
                <div class="chart-lines">
                  <div class="price-line up"></div>
                  <div class="volume-bars">
                    <div class="volume-bar" v-for="i in 20" :key="i" :style="{ height: Math.random() * 100 + '%' }"></div>
                  </div>
                </div>
                <div class="chart-overlay">
                  <div class="chart-tools">
                    <button class="tool-btn"><i class="fas fa-chart-line"></i></button>
                    <button class="tool-btn"><i class="fas fa-chart-bar"></i></button>
                    <button class="tool-btn"><i class="fas fa-chart-area"></i></button>
                  </div>
                </div>
              </div>
            </div>
          </div>
          
          <div class="chart-container">
            <div class="chart-header">
              <h3>TSLA - Tesla Inc.</h3>
              <div class="chart-price">
                <span class="current-price">$248.50</span>
                <span class="price-change down">-3.02 (-1.20%)</span>
              </div>
            </div>
            <div class="chart-widget">
              <div class="chart-placeholder">
                <div class="chart-lines">
                  <div class="price-line down"></div>
                  <div class="volume-bars">
                    <div class="volume-bar" v-for="i in 20" :key="i" :style="{ height: Math.random() * 100 + '%' }"></div>
                  </div>
                </div>
                <div class="chart-overlay">
                  <div class="chart-tools">
                    <button class="tool-btn"><i class="fas fa-chart-line"></i></button>
                    <button class="tool-btn"><i class="fas fa-chart-bar"></i></button>
                    <button class="tool-btn"><i class="fas fa-chart-area"></i></button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Market Categories -->
    <section class="market-categories">
      <div class="container">
        <div class="section-header">
          <h2>Market Categories</h2>
        </div>
        
        <div class="categories-grid">
          <div class="category-card" v-for="category in marketCategories" :key="category.id">
            <div class="category-icon">
              <i :class="category.icon"></i>
            </div>
            <div class="category-content">
              <h3>{{ category.name }}</h3>
              <p>{{ category.description }}</p>
              <div class="category-stats">
                <span class="stat">{{ category.symbols }} Symbols</span>
                <span class="stat">{{ category.change >= 0 ? '+' : '' }}{{ category.change.toFixed(2) }}%</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- TradingView Widget Integration -->
    <section class="tradingview-widgets">
      <div class="container">
        <div class="section-header">
          <h2>Advanced Charts</h2>
          <p>Powered by TradingView</p>
        </div>
        
        <div class="widget-grid">
          <div class="widget-container">
            <h3>Market Overview Widget</h3>
            <div class="widget-placeholder">
              <div class="widget-content">
                <div class="widget-header">
                  <span class="widget-title">Global Markets</span>
                  <span class="widget-time">Live</span>
                </div>
                <div class="widget-data">
                  <div class="data-row" v-for="item in widgetData" :key="item.symbol">
                    <span class="symbol">{{ item.symbol }}</span>
                    <span class="price">{{ item.price }}</span>
                    <span class="change" :class="{ up: item.change >= 0, down: item.change < 0 }">
                      {{ item.change >= 0 ? '+' : '' }}{{ item.change.toFixed(2) }}%
                    </span>
                  </div>
                </div>
              </div>
            </div>
          </div>
          
          <div class="widget-container">
            <h3>Technical Analysis</h3>
            <div class="widget-placeholder">
              <div class="widget-content">
                <div class="analysis-indicators">
                  <div class="indicator" v-for="indicator in technicalIndicators" :key="indicator.name">
                    <span class="indicator-name">{{ indicator.name }}</span>
                    <span class="indicator-value" :class="indicator.signal">{{ indicator.value }}</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()
const goBack = () => {
  router.back()
}

// Market filters
const activeFilter = ref('all')
const marketFilters = ref([
  { id: 'all', name: 'All Markets' },
  { id: 'stocks', name: 'Stocks' },
  { id: 'crypto', name: 'Crypto' },
  { id: 'forex', name: 'Forex' },
  { id: 'commodities', name: 'Commodities' }
])

// Market data
const markets = ref([
  {
    id: 1,
    name: 'Apple Inc.',
    symbol: 'AAPL',
    price: 185.42,
    priceChange: 4.15,
    change: 2.29,
    open: 181.27,
    high: 186.50,
    low: 180.80,
    volume: 45.2,
    category: 'stocks',
    chartData: [45, 52, 48, 55, 62, 58, 65, 70, 68, 75, 72, 78, 82, 85, 88, 92, 89, 94, 91, 95]
  },
  {
    id: 2,
    name: 'Tesla Inc.',
    symbol: 'TSLA',
    price: 248.50,
    priceChange: -3.02,
    change: -1.20,
    open: 251.52,
    high: 253.80,
    low: 247.20,
    volume: 32.8,
    category: 'stocks',
    chartData: [75, 72, 68, 65, 62, 58, 55, 52, 48, 45, 42, 38, 35, 32, 28, 25, 22, 18, 15, 12]
  },
  {
    id: 3,
    name: 'NVIDIA Corp.',
    symbol: 'NVDA',
    price: 485.09,
    priceChange: 17.85,
    change: 3.82,
    open: 467.24,
    high: 488.50,
    low: 465.80,
    volume: 28.5,
    category: 'stocks',
    chartData: [60, 65, 70, 75, 80, 85, 90, 95, 92, 88, 85, 82, 78, 75, 72, 68, 65, 62, 58, 55]
  },
  {
    id: 4,
    name: 'Bitcoin',
    symbol: 'BTCUSD',
    price: 43250.50,
    priceChange: 1250.30,
    change: 2.98,
    open: 42000.20,
    high: 43500.00,
    low: 41800.50,
    volume: 15.8,
    category: 'crypto',
    chartData: [40, 45, 50, 55, 60, 65, 70, 75, 80, 85, 90, 95, 92, 88, 85, 82, 78, 75, 72, 68]
  },
  {
    id: 5,
    name: 'Ethereum',
    symbol: 'ETHUSD',
    price: 2650.75,
    priceChange: -45.25,
    change: -1.68,
    open: 2696.00,
    high: 2710.50,
    low: 2640.20,
    volume: 12.3,
    category: 'crypto',
    chartData: [70, 68, 65, 62, 58, 55, 52, 48, 45, 42, 38, 35, 32, 28, 25, 22, 18, 15, 12, 10]
  },
  {
    id: 6,
    name: 'EUR/USD',
    symbol: 'EURUSD',
    price: 1.0856,
    priceChange: 0.0023,
    change: 0.21,
    open: 1.0833,
    high: 1.0870,
    low: 1.0820,
    volume: 8.9,
    category: 'forex',
    chartData: [50, 52, 55, 58, 62, 65, 68, 72, 75, 78, 82, 85, 88, 92, 95, 92, 88, 85, 82, 78]
  }
])

// Market categories
const marketCategories = ref([
  {
    id: 1,
    name: 'US Stocks',
    description: 'Major US stock market indices and individual stocks',
    icon: 'fas fa-chart-line',
    symbols: 2500,
    change: 1.85
  },
  {
    id: 2,
    name: 'Cryptocurrency',
    description: 'Digital currencies and blockchain assets',
    icon: 'fab fa-bitcoin',
    symbols: 150,
    change: 3.42
  },
  {
    id: 3,
    name: 'Forex',
    description: 'Foreign exchange currency pairs',
    icon: 'fas fa-globe',
    symbols: 80,
    change: 0.75
  },
  {
    id: 4,
    name: 'Commodities',
    description: 'Gold, silver, oil, and other commodities',
    icon: 'fas fa-gem',
    symbols: 45,
    change: -0.32
  }
])

// Widget data
const widgetData = ref([
  { symbol: 'AAPL', price: '$185.42', change: 2.29 },
  { symbol: 'TSLA', price: '$248.50', change: -1.20 },
  { symbol: 'NVDA', price: '$485.09', change: 3.82 },
  { symbol: 'BTC', price: '$43,250', change: 2.98 },
  { symbol: 'ETH', price: '$2,650', change: -1.68 }
])

// Technical indicators
const technicalIndicators = ref([
  { name: 'RSI', value: '65.2', signal: 'neutral' },
  { name: 'MACD', value: 'Bullish', signal: 'bullish' },
  { name: 'MA 50', value: '$182.50', signal: 'bullish' },
  { name: 'MA 200', value: '$175.20', signal: 'bullish' },
  { name: 'Volume', value: '45.2M', signal: 'neutral' },
  { name: 'Support', value: '$180.80', signal: 'neutral' }
])

// Computed properties
const filteredMarkets = computed(() => {
  if (activeFilter.value === 'all') {
    return markets.value
  }
  return markets.value.filter(market => market.category === activeFilter.value)
})

// Simulate real-time updates
let updateInterval

onMounted(() => {
  updateInterval = setInterval(() => {
    markets.value.forEach(market => {
      // Simulate price changes
      const change = (Math.random() - 0.5) * 2
      market.price += change
      market.priceChange += change
      market.change = (market.priceChange / (market.price - market.priceChange)) * 100
      
      // Update chart data
      market.chartData = market.chartData.map(() => Math.random() * 100)
    })
  }, 5000) // Update every 5 seconds
})

onUnmounted(() => {
  if (updateInterval) {
    clearInterval(updateInterval)
  }
})
</script>

<style scoped>
/* Markets Page Styles */
.markets-page {
  min-height: 100vh;
  background: linear-gradient(135deg, #0a0a1a 0%, #1a1a2e 50%, #16213e 100%);
  color: white;
  position: relative;
  overflow-x: hidden;
}

/* Trading Background Animation */
.trading-background {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  pointer-events: none;
  z-index: 1;
}

.stock-chart {
  position: absolute;
  display: flex;
  gap: 2px;
  opacity: 0.1;
}

.chart-1 {
  top: 15%;
  left: 5%;
  animation: slideRight 20s linear infinite;
}

.chart-2 {
  top: 45%;
  right: 10%;
  animation: slideLeft 25s linear infinite;
}

.candlestick {
  width: 4px;
  background: #00ff88;
  border-radius: 1px;
}

.candlestick.up {
  height: 20px;
  background: #00ff88;
}

.candlestick.down {
  height: 15px;
  background: #ff4757;
}

.price-tickers {
  position: absolute;
  top: 10%;
  right: 5%;
  opacity: 0.2;
}

.ticker {
  background: rgba(0, 255, 136, 0.1);
  padding: 8px 12px;
  border-radius: 6px;
  margin-bottom: 8px;
  display: flex;
  gap: 8px;
  font-size: 12px;
  animation: slideLeft 15s linear infinite;
}

.ticker .symbol {
  font-weight: bold;
}

.ticker .price.up {
  color: #00ff88;
}

.ticker .price.down {
  color: #ff4757;
}

.ticker .change.up {
  color: #00ff88;
}

.ticker .change.down {
  color: #ff4757;
}

/* Animations */
@keyframes slideRight {
  0% { transform: translateX(-100px); opacity: 0; }
  10% { opacity: 0.1; }
  90% { opacity: 0.1; }
  100% { transform: translateX(calc(100vw + 100px)); opacity: 0; }
}

@keyframes slideLeft {
  0% { transform: translateX(100px); opacity: 0; }
  10% { opacity: 0.1; }
  90% { opacity: 0.1; }
  100% { transform: translateX(calc(-100vw - 100px)); opacity: 0; }
}

/* Header */
.markets-header {
  position: relative;
  z-index: 10;
  background: rgba(10, 10, 26, 0.95);
  backdrop-filter: blur(10px);
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
  padding: 2rem 0;
}

.header-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 2rem;
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.back-button {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  background: rgba(255, 255, 255, 0.1);
  color: white;
  border: 1px solid rgba(255, 255, 255, 0.2);
  padding: 0.75rem 1.5rem;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.3s ease;
  font-weight: 500;
}

.back-button:hover {
  background: rgba(255, 255, 255, 0.15);
  border-color: rgba(255, 255, 255, 0.3);
}

.header-content h1 {
  font-size: 2.5rem;
  font-weight: bold;
  margin: 0;
  color: #00ff88;
}

.header-content p {
  color: rgba(255, 255, 255, 0.8);
  margin: 0.5rem 0 0 0;
}

.market-status {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  color: #00ff88;
  font-weight: 500;
}

.status-indicator {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: #00ff88;
  animation: pulse 2s ease-in-out infinite;
}

@keyframes pulse {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.5; }
}

/* Container */
.container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 2rem;
}

/* Section Headers */
.section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
  flex-wrap: wrap;
  gap: 1rem;
}

.section-header h2 {
  font-size: 2rem;
  font-weight: bold;
  color: white;
  margin: 0;
}

.section-header p {
  color: rgba(255, 255, 255, 0.7);
  margin: 0;
}

/* Market Filters */
.market-filters {
  display: flex;
  gap: 0.5rem;
  flex-wrap: wrap;
}

.filter-btn {
  background: rgba(255, 255, 255, 0.1);
  color: white;
  border: 1px solid rgba(255, 255, 255, 0.2);
  padding: 0.5rem 1rem;
  border-radius: 6px;
  cursor: pointer;
  transition: all 0.3s ease;
  font-size: 0.9rem;
}

.filter-btn:hover {
  background: rgba(255, 255, 255, 0.15);
}

.filter-btn.active {
  background: #00ff88;
  color: #0a0a1a;
  border-color: #00ff88;
}

/* Market Grid */
.market-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 1.5rem;
  margin-bottom: 3rem;
}

.market-card {
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 12px;
  padding: 1.5rem;
  transition: all 0.3s ease;
  backdrop-filter: blur(10px);
}

.market-card:hover {
  border-color: rgba(0, 255, 136, 0.3);
  transform: translateY(-2px);
}

.market-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 1rem;
}

.market-info h3 {
  font-size: 1.2rem;
  font-weight: bold;
  margin: 0 0 0.25rem 0;
  color: white;
}

.symbol {
  color: rgba(255, 255, 255, 0.6);
  font-size: 0.9rem;
}

.market-change {
  display: flex;
  align-items: center;
  gap: 0.25rem;
  font-weight: bold;
}

.market-change.up {
  color: #00ff88;
}

.market-change.down {
  color: #ff4757;
}

.change-arrow {
  font-size: 1.2rem;
}

.market-price {
  display: flex;
  align-items: baseline;
  gap: 0.5rem;
  margin-bottom: 1rem;
}

.price-value {
  font-size: 1.5rem;
  font-weight: bold;
  color: white;
}

.price-change {
  font-size: 0.9rem;
  color: rgba(255, 255, 255, 0.7);
}

.market-chart {
  margin-bottom: 1rem;
}

.mini-chart {
  display: flex;
  align-items: end;
  gap: 1px;
  height: 40px;
}

.chart-bar {
  flex: 1;
  background: rgba(255, 255, 255, 0.2);
  border-radius: 1px;
  transition: all 0.3s ease;
}

.chart-bar.up {
  background: #00ff88;
}

.chart-bar.down {
  background: #ff4757;
}

.market-stats {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 0.75rem;
}

.stat {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.stat-label {
  color: rgba(255, 255, 255, 0.6);
  font-size: 0.8rem;
}

.stat-value {
  color: white;
  font-weight: 500;
  font-size: 0.9rem;
}

/* Live Charts Section */
.live-charts {
  padding: 3rem 0;
  background: rgba(255, 255, 255, 0.02);
}

.chart-controls {
  display: flex;
  gap: 0.5rem;
}

.timeframe-btn {
  background: rgba(255, 255, 255, 0.1);
  color: white;
  border: 1px solid rgba(255, 255, 255, 0.2);
  padding: 0.5rem 1rem;
  border-radius: 6px;
  cursor: pointer;
  transition: all 0.3s ease;
  font-size: 0.9rem;
}

.timeframe-btn:hover {
  background: rgba(255, 255, 255, 0.15);
}

.timeframe-btn.active {
  background: #00ff88;
  color: #0a0a1a;
  border-color: #00ff88;
}

.charts-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(500px, 1fr));
  gap: 2rem;
}

.chart-container {
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 12px;
  overflow: hidden;
}

.chart-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem 1.5rem;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
  background: rgba(255, 255, 255, 0.02);
}

.chart-header h3 {
  font-size: 1.1rem;
  font-weight: bold;
  margin: 0;
  color: white;
}

.chart-price {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
}

.current-price {
  font-size: 1.2rem;
  font-weight: bold;
  color: white;
}

.price-change {
  font-size: 0.9rem;
}

.price-change.up {
  color: #00ff88;
}

.price-change.down {
  color: #ff4757;
}

.chart-widget {
  height: 300px;
  position: relative;
}

.chart-placeholder {
  width: 100%;
  height: 100%;
  background: rgba(255, 255, 255, 0.02);
  position: relative;
  display: flex;
  align-items: center;
  justify-content: center;
}

.chart-lines {
  width: 100%;
  height: 100%;
  position: relative;
  display: flex;
  align-items: end;
  padding: 1rem;
}

.price-line {
  position: absolute;
  top: 50%;
  left: 0;
  width: 100%;
  height: 2px;
  background: linear-gradient(90deg, #00ff88 0%, #00d4aa 100%);
  border-radius: 1px;
}

.price-line.down {
  background: linear-gradient(90deg, #ff4757 0%, #ff6b6b 100%);
}

.volume-bars {
  position: absolute;
  bottom: 0;
  left: 0;
  width: 100%;
  height: 100%;
  display: flex;
  align-items: end;
  gap: 1px;
  padding: 0 1rem 1rem 1rem;
}

.volume-bar {
  flex: 1;
  background: rgba(0, 255, 136, 0.3);
  border-radius: 1px;
  min-height: 10px;
}

.chart-overlay {
  position: absolute;
  top: 1rem;
  right: 1rem;
}

.chart-tools {
  display: flex;
  gap: 0.5rem;
}

.tool-btn {
  background: rgba(255, 255, 255, 0.1);
  color: white;
  border: 1px solid rgba(255, 255, 255, 0.2);
  padding: 0.5rem;
  border-radius: 4px;
  cursor: pointer;
  transition: all 0.3s ease;
}

.tool-btn:hover {
  background: rgba(255, 255, 255, 0.15);
}

/* Market Categories */
.market-categories {
  padding: 3rem 0;
}

.categories-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 1.5rem;
}

.category-card {
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 12px;
  padding: 1.5rem;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  gap: 1rem;
}

.category-card:hover {
  border-color: rgba(0, 255, 136, 0.3);
  transform: translateY(-2px);
}

.category-icon {
  font-size: 2rem;
  color: #00ff88;
  width: 50px;
  text-align: center;
}

.category-content h3 {
  font-size: 1.1rem;
  font-weight: bold;
  margin: 0 0 0.5rem 0;
  color: white;
}

.category-content p {
  color: rgba(255, 255, 255, 0.7);
  font-size: 0.9rem;
  margin: 0 0 1rem 0;
  line-height: 1.4;
}

.category-stats {
  display: flex;
  gap: 1rem;
}

.stat {
  font-size: 0.8rem;
  color: rgba(255, 255, 255, 0.6);
}

/* TradingView Widgets */
.tradingview-widgets {
  padding: 3rem 0;
  background: rgba(255, 255, 255, 0.02);
}

.widget-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
  gap: 2rem;
}

.widget-container {
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 12px;
  overflow: hidden;
}

.widget-container h3 {
  padding: 1rem 1.5rem;
  margin: 0;
  font-size: 1.1rem;
  font-weight: bold;
  color: white;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
  background: rgba(255, 255, 255, 0.02);
}

.widget-placeholder {
  padding: 1.5rem;
}

.widget-content {
  background: rgba(255, 255, 255, 0.02);
  border-radius: 8px;
  padding: 1rem;
}

.widget-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
  padding-bottom: 0.5rem;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.widget-title {
  font-weight: bold;
  color: white;
}

.widget-time {
  color: #00ff88;
  font-size: 0.9rem;
}

.widget-data {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.data-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.5rem 0;
}

.data-row .symbol {
  font-weight: bold;
  color: white;
}

.data-row .price {
  color: white;
}

.data-row .change {
  font-weight: bold;
}

.data-row .change.up {
  color: #00ff88;
}

.data-row .change.down {
  color: #ff4757;
}

.analysis-indicators {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 1rem;
}

.indicator {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.75rem;
  background: rgba(255, 255, 255, 0.05);
  border-radius: 6px;
}

.indicator-name {
  color: rgba(255, 255, 255, 0.8);
  font-size: 0.9rem;
}

.indicator-value {
  font-weight: bold;
  font-size: 0.9rem;
}

.indicator-value.bullish {
  color: #00ff88;
}

.indicator-value.bearish {
  color: #ff4757;
}

.indicator-value.neutral {
  color: rgba(255, 255, 255, 0.7);
}

/* Responsive Design */
@media (max-width: 768px) {
  .header-container {
    flex-direction: column;
    gap: 1rem;
    text-align: center;
  }
  
  .header-content h1 {
    font-size: 2rem;
  }
  
  .section-header {
    flex-direction: column;
    align-items: flex-start;
  }
  
  .market-grid {
    grid-template-columns: 1fr;
  }
  
  .charts-grid {
    grid-template-columns: 1fr;
  }
  
  .categories-grid {
    grid-template-columns: 1fr;
  }
  
  .widget-grid {
    grid-template-columns: 1fr;
  }
  
  .analysis-indicators {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 480px) {
  .container {
    padding: 0 1rem;
  }
  
  .header-container {
    padding: 0 1rem;
  }
  
  .market-card {
    padding: 1rem;
  }
  
  .chart-container {
    margin: 0 -1rem;
    border-radius: 0;
  }
}
</style>
