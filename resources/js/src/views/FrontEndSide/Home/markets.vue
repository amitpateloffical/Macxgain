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

    <!-- Navigation Header -->
    <header class="main-header">
      <div class="header-container">
        <div class="logo-section">
          <img src="../logo.png" alt="Macxgain Logo" class="logo" />
          <h1 class="brand-name">Macxgain</h1>
        </div>
        
        <nav class="nav-menu">
          <router-link to="/" class="nav-link">Home</router-link>
          <router-link to="/markets" class="nav-link active">Markets</router-link>
          <a href="#" class="nav-link">Download APK</a>
        </nav>
        
        <div class="auth-buttons">
          <a href="/login" class="btn btn-outline">Login</a>
          <a href="/register" class="btn btn-primary">Register</a>
        </div>
      </div>
    </header>

    <!-- Markets Header -->
    <header class="markets-header">
      <div class="header-container">
        <button class="back-button fade-in-left" @click="goBack">
          <i class="fas fa-arrow-left"></i>
          Back to Home
        </button>
        
        <div class="header-content fade-in-up">
          <h1>Indian Markets</h1>
          <p>Real-time Indian market data and live charts</p>
        </div>
        
        <div class="market-status fade-in-right">
          <div class="status-indicator live"></div>
          <span>Live Data</span>
        </div>
      </div>
    </header>

    <!-- Stock Search Section -->
    <section class="stock-search-section">
      <div class="container">
        <div class="search-container fade-in-up">
          <div class="search-header">
            <h2>Indian Stock Search</h2>
            <p>Search for any Indian stock symbol to get real-time data</p>
          </div>
          
          <div class="search-box">
            <div class="search-input-wrapper">
              <i class="fas fa-search search-icon"></i>
              <input 
                v-model="searchQuery" 
                @input="handleSearch"
                @keyup.enter="searchStock"
                type="text" 
                placeholder="Enter Indian stock symbol (e.g., RELIANCE, TCS, INFY)"
                class="search-input"
              />
              <button @click="searchStock" class="search-btn">
                <i class="fas fa-search"></i>
              </button>
            </div>
            
            <!-- Search Suggestions -->
            <div v-if="searchSuggestions.length > 0 && searchQuery" class="search-suggestions">
              <div class="suggestions-header">
                <span>Found {{ searchSuggestions.length }} matches</span>
                <span v-if="searchSuggestions.length >= 10" class="view-all">View All Results</span>
              </div>
              <div 
                v-for="suggestion in searchSuggestions" 
                :key="suggestion.symbol"
                @click="selectSuggestion(suggestion)"
                class="suggestion-item"
              >
                <div class="suggestion-symbol">{{ suggestion.symbol }}</div>
                <div class="suggestion-name">{{ suggestion.name }}</div>
                <div class="suggestion-category">{{ getStockCategory(suggestion.symbol) }}</div>
              </div>
              <div v-if="searchSuggestions.length >= 10" class="suggestions-footer">
                <button @click="showAllResults" class="view-all-btn">
                  Show All {{ getTotalMatches() }} Results
                </button>
              </div>
            </div>
          </div>
          
          <!-- Search Results -->
          <div v-if="searchResult" class="search-result">
            <div class="result-card">
              <div class="result-header">
                <div class="result-info">
                  <h3>{{ searchResult.symbol }}</h3>
                  <p>{{ searchResult.name }}</p>
                </div>
                <div class="result-change" :class="{ up: searchResult.change >= 0, down: searchResult.change < 0 }">
                  <span class="change-value">{{ searchResult.change >= 0 ? '+' : '' }}{{ searchResult.change.toFixed(2) }}%</span>
                  <span class="change-arrow">{{ searchResult.change >= 0 ? '↗' : '↘' }}</span>
                </div>
              </div>
              
              <div class="result-price">
                <span class="price-value">₹{{ searchResult.price.toFixed(2) }}</span>
                <span class="price-change">{{ searchResult.change >= 0 ? '+' : '' }}{{ searchResult.priceChange.toFixed(2) }}</span>
              </div>
              
              <div class="result-stats">
                <div class="stat">
                  <span class="stat-label">Open</span>
                  <span class="stat-value">₹{{ searchResult.open.toFixed(2) }}</span>
                </div>
                <div class="stat">
                  <span class="stat-label">High</span>
                  <span class="stat-value">₹{{ searchResult.high.toFixed(2) }}</span>
                </div>
                <div class="stat">
                  <span class="stat-label">Low</span>
                  <span class="stat-value">₹{{ searchResult.low.toFixed(2) }}</span>
                </div>
                <div class="stat">
                  <span class="stat-label">Volume</span>
                  <span class="stat-value">{{ formatVolume(searchResult.volume) }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Top 10 Stocks Section -->
    <section class="top-stocks-section">
      <div class="container">
        <div class="section-header fade-in-up">
          <h2>Top 10 Indian Stocks</h2>
          <div class="section-subtitle">Most actively traded Indian stocks</div>
          <div class="refresh-controls">
            <button @click="refreshTopStocks" class="refresh-btn" :disabled="isLoading">
              <i class="fas fa-sync-alt" :class="{ 'fa-spin': isLoading }"></i>
              {{ isLoading ? 'Loading...' : 'Refresh' }}
            </button>
          </div>
        </div>
        
        <div v-if="isLoading" class="loading-container">
          <div class="loading-spinner"></div>
          <p>Loading Indian market data...</p>
        </div>
        
        <div v-else-if="topStocks.length > 0" class="top-stocks-grid">
          <div v-for="(stock, idx) in topStocks" :key="stock.symbol" :class="['stock-card', idx % 2 === 0 ? 'fade-in-left' : 'fade-in-right']">
            <div class="stock-header">
              <div class="stock-info">
                <h3>{{ stock.symbol }}</h3>
                <span class="company-name">{{ stock.name }}</span>
              </div>
              <div class="stock-change" :class="{ up: stock.change >= 0, down: stock.change < 0 }">
                <span class="change-value">{{ stock.change >= 0 ? '+' : '' }}{{ stock.change.toFixed(2) }}%</span>
                <span class="change-arrow">{{ stock.change >= 0 ? '↗' : '↘' }}</span>
              </div>
            </div>
            
            <div class="stock-price">
              <span class="price-value">₹{{ stock.price.toFixed(2) }}</span>
              <span class="price-change">{{ stock.change >= 0 ? '+' : '' }}{{ stock.priceChange.toFixed(2) }}</span>
            </div>
            
            <div class="stock-stats">
              <div class="stat">
                <span class="stat-label">Open</span>
                <span class="stat-value">₹{{ stock.open.toFixed(2) }}</span>
              </div>
              <div class="stat">
                <span class="stat-label">Volume</span>
                <span class="stat-value">{{ formatVolume(stock.volume) }}</span>
              </div>
            </div>
            
            <div class="stock-chart">
              <div class="mini-chart">
                <div 
                  v-for="(point, index) in stock.chartData" 
                  :key="index"
                  class="chart-bar"
                  :style="{ height: point + '%' }"
                  :class="{ up: point > 50, down: point <= 50 }"
                ></div>
              </div>
            </div>
          </div>
        </div>
        
        <div v-else class="no-data">
          <i class="fas fa-chart-line no-data-icon"></i>
          <h3>No Data Available</h3>
          <p>Unable to fetch Indian stock data. Please try refreshing.</p>
        </div>
      </div>
    </section>

    <!-- Market Categories Tabs -->
    <section class="market-tabs">
      <div class="container">
        <div class="tabs-container fade-in-up">
          <button 
            v-for="tab in marketTabs" 
            :key="tab.id"
            :class="['tab-btn', { active: activeTab === tab.id }]"
            @click="activeTab = tab.id"
          >
            <i :class="tab.icon"></i>
            {{ tab.name }}
          </button>
        </div>
      </div>
    </section>

    <!-- Indices Section -->
    <section v-if="activeTab === 'indices'" class="market-section">
      <div class="container">
        <div class="section-header fade-in-up">
          <h2>Major Indices</h2>
          <div class="section-subtitle">Global market benchmarks</div>
        </div>
        
        <div class="indices-grid">
          <div v-for="(index, idx) in indices" :key="index.symbol" :class="['index-card', idx % 2 === 0 ? 'fade-in-left' : 'fade-in-right']">
            <div class="index-header">
              <div class="index-info">
                <h3>{{ index.name }}</h3>
                <span class="symbol">{{ index.symbol }}</span>
              </div>
              <div class="index-change" :class="{ up: index.change >= 0, down: index.change < 0 }">
                <span class="change-value">{{ index.change >= 0 ? '+' : '' }}{{ index.change.toFixed(2) }}%</span>
                <span class="change-arrow">{{ index.change >= 0 ? '↗' : '↘' }}</span>
              </div>
            </div>
            
            <div class="index-price">
              <span class="price-value">{{ index.price.toFixed(2) }}</span>
              <span class="price-change">{{ index.change >= 0 ? '+' : '' }}{{ index.priceChange.toFixed(2) }}</span>
            </div>
            
            <div class="index-chart">
              <div class="mini-chart">
                <div 
                  v-for="(point, index) in index.chartData" 
                  :key="index"
                  class="chart-bar"
                  :style="{ height: point + '%' }"
                  :class="{ up: point > 50, down: point <= 50 }"
                ></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- India Section -->
    <section v-if="activeTab === 'india'" class="market-section">
      <div class="container">
        <div class="section-header">
          <h2>Major Indices</h2>
          <div class="section-subtitle">Indian market benchmarks</div>
        </div>
        
        <div class="indices-grid">
          <div v-for="index in indiaIndices" :key="index.symbol" class="index-card">
            <div class="index-header">
              <div class="index-info">
                <div class="index-logo">
                  <span class="logo-text">{{ index.logo }}</span>
                </div>
                <div>
                  <h3>{{ index.name }}</h3>
                  <span class="symbol">{{ index.symbol }}</span>
                </div>
              </div>
              <div class="index-change" :class="{ up: index.change >= 0, down: index.change < 0 }">
                <span class="change-value">{{ index.change >= 0 ? '+' : '' }}{{ index.change.toFixed(2) }}%</span>
                <span class="change-arrow">{{ index.change >= 0 ? '↗' : '↘' }}</span>
              </div>
            </div>
            
            <div class="index-price">
              <span class="price-value">{{ index.price.toFixed(2) }} INR</span>
              <span class="price-change">{{ index.change >= 0 ? '+' : '' }}{{ index.priceChange.toFixed(2) }}</span>
            </div>
            
            <div class="index-chart">
              <div class="mini-chart">
                <div 
                  v-for="(point, index) in index.chartData" 
                  :key="index"
                  class="chart-bar"
                  :style="{ height: point + '%' }"
                  :class="{ up: point > 50, down: point <= 50 }"
                ></div>
              </div>
            </div>
          </div>
        </div>

        <!-- Indian Stocks Section -->
        <div class="section-header fade-in-up" style="margin-top: 3rem;">
          <h2>Indian Stocks</h2>
          <div class="section-subtitle">Community trends</div>
        </div>
        
        <div class="indian-stocks-scroll">
          <div v-for="(stock, idx) in indianStocks" :key="stock.symbol" :class="['indian-stock-card', idx % 2 === 0 ? 'fade-in-left' : 'fade-in-right']">
            <div class="stock-logo">
              <span class="logo-text">{{ stock.logo }}</span>
            </div>
            <div class="stock-info">
              <div class="stock-symbol">{{ stock.symbol }}</div>
              <div class="stock-name">{{ stock.name }}</div>
            </div>
            <div class="stock-price-info">
              <div class="stock-price">{{ stock.price.toFixed(2) }} INR</div>
              <div class="stock-change" :class="{ up: stock.change >= 0, down: stock.change < 0 }">
                {{ stock.change >= 0 ? '+' : '' }}{{ stock.change.toFixed(2) }}%
              </div>
            </div>
          </div>
        </div>

        <!-- Market Overview Section -->
        <div class="market-overview">
          <div class="overview-section fade-in-left">
            <h3>Highest Volume Stocks</h3>
            <div class="overview-list">
              <div v-for="stock in highestVolumeStocks" :key="stock.symbol" class="overview-item">
                <div class="stock-logo-small">
                  <span class="logo-text">{{ stock.logo }}</span>
                </div>
                <div class="stock-details">
                  <div class="stock-name">{{ stock.name }}</div>
                  <div class="stock-symbol-small">{{ stock.symbol }}</div>
                </div>
                <div class="stock-price-details">
                  <div class="stock-price-small">{{ stock.price.toFixed(2) }} INR</div>
                  <div class="stock-change-small" :class="{ up: stock.change >= 0, down: stock.change < 0 }">
                    {{ stock.change >= 0 ? '+' : '' }}{{ stock.change.toFixed(2) }}%
                  </div>
                </div>
              </div>
            </div>
            <div class="see-all-link">See all most actively traded stocks ></div>
          </div>

          <div class="overview-section fade-in-right">
            <h3>Most Volatile Stocks</h3>
            <div class="overview-list">
              <div v-for="stock in mostVolatileStocks" :key="stock.symbol" class="overview-item">
                <div class="stock-logo-small">
                  <span class="logo-text">{{ stock.logo }}</span>
                </div>
                <div class="stock-details">
                  <div class="stock-name">{{ stock.name }}</div>
                  <div class="stock-symbol-small">{{ stock.symbol }}</div>
                </div>
                <div class="stock-price-details">
                  <div class="stock-price-small">{{ stock.price.toFixed(2) }} INR</div>
                  <div class="stock-change-small" :class="{ up: stock.change >= 0, down: stock.change < 0 }">
                    {{ stock.change >= 0 ? '+' : '' }}{{ stock.change.toFixed(2) }}%
                  </div>
                </div>
              </div>
            </div>
            <div class="see-all-link">See all stocks with biggest price changes ></div>
          </div>
        </div>
      </div>
    </section>

    <!-- US Stocks Section -->
    <section v-if="activeTab === 'stocks'" class="market-section">
      <div class="container">
        <div class="section-header">
          <h2>US Stocks</h2>
          <div class="stock-filters">
            <button class="filter-btn active">All</button>
            <button class="filter-btn">Gainers</button>
            <button class="filter-btn">Losers</button>
            <button class="filter-btn">Volume</button>
          </div>
        </div>
        
        <div class="stocks-grid">
          <div v-for="stock in stocks" :key="stock.symbol" class="stock-card">
            <div class="stock-header">
              <div class="stock-info">
                <h3>{{ stock.name }}</h3>
                <span class="symbol">{{ stock.symbol }}</span>
              </div>
              <div class="stock-change" :class="{ up: stock.change >= 0, down: stock.change < 0 }">
                <span class="change-value">{{ stock.change >= 0 ? '+' : '' }}{{ stock.change.toFixed(2) }}%</span>
                <span class="change-arrow">{{ stock.change >= 0 ? '↗' : '↘' }}</span>
              </div>
            </div>
            
            <div class="stock-price">
              <span class="price-value">${{ stock.price.toFixed(2) }}</span>
              <span class="price-change">{{ stock.change >= 0 ? '+' : '' }}{{ stock.priceChange.toFixed(2) }}</span>
            </div>
            
            <div class="stock-stats">
              <div class="stat">
                <span class="stat-label">Market Cap</span>
                <span class="stat-value">{{ stock.marketCap }}</span>
              </div>
              <div class="stat">
                <span class="stat-label">Volume</span>
                <span class="stat-value">{{ stock.volume }}M</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Crypto Section -->
    <section v-if="activeTab === 'crypto'" class="market-section">
      <div class="container">
        <div class="section-header">
          <h2>Cryptocurrency</h2>
          <div class="crypto-filters">
            <button class="filter-btn active">All</button>
            <button class="filter-btn">Gainers</button>
            <button class="filter-btn">Losers</button>
            <button class="filter-btn">Market Cap</button>
          </div>
        </div>
        
        <div class="crypto-grid">
          <div v-for="crypto in cryptos" :key="crypto.symbol" class="crypto-card">
            <div class="crypto-header">
              <div class="crypto-info">
                <div class="crypto-icon">
                  <i :class="crypto.icon"></i>
                </div>
                <div>
                  <h3>{{ crypto.name }}</h3>
                  <span class="symbol">{{ crypto.symbol }}</span>
                </div>
              </div>
              <div class="crypto-change" :class="{ up: crypto.change >= 0, down: crypto.change < 0 }">
                <span class="change-value">{{ crypto.change >= 0 ? '+' : '' }}{{ crypto.change.toFixed(2) }}%</span>
                <span class="change-arrow">{{ crypto.change >= 0 ? '↗' : '↘' }}</span>
              </div>
            </div>
            
            <div class="crypto-price">
              <span class="price-value">${{ crypto.price.toFixed(2) }}</span>
              <span class="price-change">{{ crypto.change >= 0 ? '+' : '' }}{{ crypto.priceChange.toFixed(2) }}</span>
            </div>
            
            <div class="crypto-stats">
              <div class="stat">
                <span class="stat-label">Market Cap</span>
                <span class="stat-value">{{ crypto.marketCap }}</span>
              </div>
              <div class="stat">
                <span class="stat-label">24h Vol</span>
                <span class="stat-value">{{ crypto.volume24h }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Forex Section -->
    <section v-if="activeTab === 'forex'" class="market-section">
      <div class="container">
        <div class="section-header">
          <h2>Forex & Currencies</h2>
          <div class="forex-filters">
            <button class="filter-btn active">Majors</button>
            <button class="filter-btn">Minors</button>
            <button class="filter-btn">Exotics</button>
          </div>
        </div>
        
        <div class="forex-grid">
          <div v-for="forex in forexPairs" :key="forex.symbol" class="forex-card">
            <div class="forex-header">
              <div class="forex-info">
                <h3>{{ forex.name }}</h3>
                <span class="symbol">{{ forex.symbol }}</span>
              </div>
              <div class="forex-change" :class="{ up: forex.change >= 0, down: forex.change < 0 }">
                <span class="change-value">{{ forex.change >= 0 ? '+' : '' }}{{ forex.change.toFixed(4) }}%</span>
                <span class="change-arrow">{{ forex.change >= 0 ? '↗' : '↘' }}</span>
              </div>
            </div>
            
            <div class="forex-price">
              <span class="price-value">{{ forex.price.toFixed(5) }}</span>
              <span class="price-change">{{ forex.change >= 0 ? '+' : '' }}{{ forex.priceChange.toFixed(5) }}</span>
            </div>
            
            <div class="forex-stats">
              <div class="stat">
                <span class="stat-label">High</span>
                <span class="stat-value">{{ forex.high.toFixed(5) }}</span>
              </div>
              <div class="stat">
                <span class="stat-label">Low</span>
                <span class="stat-value">{{ forex.low.toFixed(5) }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Commodities Section -->
    <section v-if="activeTab === 'commodities'" class="market-section">
      <div class="container">
        <div class="section-header">
          <h2>Commodities & Futures</h2>
          <div class="commodity-filters">
            <button class="filter-btn active">All</button>
            <button class="filter-btn">Energy</button>
            <button class="filter-btn">Metals</button>
            <button class="filter-btn">Agriculture</button>
          </div>
        </div>
        
        <div class="commodities-grid">
          <div v-for="commodity in commodities" :key="commodity.symbol" class="commodity-card">
            <div class="commodity-header">
              <div class="commodity-info">
                <div class="commodity-icon">
                  <i :class="commodity.icon"></i>
                </div>
                <div>
                  <h3>{{ commodity.name }}</h3>
                  <span class="symbol">{{ commodity.symbol }}</span>
                </div>
              </div>
              <div class="commodity-change" :class="{ up: commodity.change >= 0, down: commodity.change < 0 }">
                <span class="change-value">{{ commodity.change >= 0 ? '+' : '' }}{{ commodity.change.toFixed(2) }}%</span>
                <span class="change-arrow">{{ commodity.change >= 0 ? '↗' : '↘' }}</span>
              </div>
            </div>
            
            <div class="commodity-price">
              <span class="price-value">${{ commodity.price.toFixed(2) }}</span>
              <span class="price-change">{{ commodity.change >= 0 ? '+' : '' }}{{ commodity.priceChange.toFixed(2) }}</span>
            </div>
            
            <div class="commodity-stats">
              <div class="stat">
                <span class="stat-label">Open</span>
                <span class="stat-value">${{ commodity.open.toFixed(2) }}</span>
              </div>
              <div class="stat">
                <span class="stat-label">Volume</span>
                <span class="stat-value">{{ commodity.volume }}K</span>
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
          <div class="category-card" v-for="category in globalMarketCategories" :key="category.id">
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
  router.push('/')
}

// Stock search functionality
const searchQuery = ref('')
const searchSuggestions = ref([])
const searchResult = ref(null)
const isSearching = ref(false)

// Top stocks data
const topStocks = ref([])
const isLoading = ref(false)

// Comprehensive list of Indian stocks for search suggestions
const indianStocksList = [
  // Adani Group
  { symbol: 'ADANIENT', name: 'Adani Enterprises Ltd.' },
  { symbol: 'ADANIPORTS', name: 'Adani Ports & SEZ Ltd.' },
  { symbol: 'ADANIGREEN', name: 'Adani Green Energy Ltd.' },
  { symbol: 'ADANITRANS', name: 'Adani Transmission Ltd.' },
  { symbol: 'ADANIPOWER', name: 'Adani Power Ltd.' },
  { symbol: 'ADANIGAS', name: 'Adani Total Gas Ltd.' },
  { symbol: 'ADANICAP', name: 'Adani Capital Ltd.' },
  { symbol: 'ADANIWILMAR', name: 'Adani Wilmar Ltd.' },
  
  // Reliance Group
  { symbol: 'RELIANCE', name: 'Reliance Industries Ltd.' },
  { symbol: 'RELIANCEINFRA', name: 'Reliance Infrastructure Ltd.' },
  { symbol: 'RELIANCEPOWER', name: 'Reliance Power Ltd.' },
  { symbol: 'RELIANCECAP', name: 'Reliance Capital Ltd.' },
  
  // Tata Group
  { symbol: 'TCS', name: 'Tata Consultancy Services Ltd.' },
  { symbol: 'TATAMOTORS', name: 'Tata Motors Ltd.' },
  { symbol: 'TATASTEEL', name: 'Tata Steel Ltd.' },
  { symbol: 'TATAPOWER', name: 'Tata Power Company Ltd.' },
  { symbol: 'TATACONSUM', name: 'Tata Consumer Products Ltd.' },
  { symbol: 'TATACOMM', name: 'Tata Communications Ltd.' },
  { symbol: 'TATACHEM', name: 'Tata Chemicals Ltd.' },
  { symbol: 'TATAMETALI', name: 'Tata Metaliks Ltd.' },
  
  // Banking Sector
  { symbol: 'HDFCBANK', name: 'HDFC Bank Ltd.' },
  { symbol: 'ICICIBANK', name: 'ICICI Bank Ltd.' },
  { symbol: 'SBIN', name: 'State Bank of India' },
  { symbol: 'AXISBANK', name: 'Axis Bank Ltd.' },
  { symbol: 'KOTAKBANK', name: 'Kotak Mahindra Bank Ltd.' },
  { symbol: 'INDUSINDBK', name: 'IndusInd Bank Ltd.' },
  { symbol: 'BANKBARODA', name: 'Bank of Baroda' },
  { symbol: 'CANBK', name: 'Canara Bank' },
  { symbol: 'PNB', name: 'Punjab National Bank' },
  { symbol: 'UNIONBANK', name: 'Union Bank of India' },
  
  // IT Sector
  { symbol: 'INFY', name: 'Infosys Ltd.' },
  { symbol: 'WIPRO', name: 'Wipro Ltd.' },
  { symbol: 'TECHM', name: 'Tech Mahindra Ltd.' },
  { symbol: 'HCLTECH', name: 'HCL Technologies Ltd.' },
  { symbol: 'MINDTREE', name: 'Mindtree Ltd.' },
  { symbol: 'MPHASIS', name: 'Mphasis Ltd.' },
  { symbol: 'LTI', name: 'Larsen & Toubro Infotech Ltd.' },
  { symbol: 'PERSISTENT', name: 'Persistent Systems Ltd.' },
  
  // FMCG Sector
  { symbol: 'HINDUNILVR', name: 'Hindustan Unilever Ltd.' },
  { symbol: 'ITC', name: 'ITC Ltd.' },
  { symbol: 'NESTLEIND', name: 'Nestle India Ltd.' },
  { symbol: 'MARICO', name: 'Marico Ltd.' },
  { symbol: 'DABUR', name: 'Dabur India Ltd.' },
  { symbol: 'BRITANNIA', name: 'Britannia Industries Ltd.' },
  { symbol: 'COLPAL', name: 'Colgate Palmolive India Ltd.' },
  { symbol: 'GODREJCP', name: 'Godrej Consumer Products Ltd.' },
  
  // Auto Sector
  { symbol: 'MARUTI', name: 'Maruti Suzuki India Ltd.' },
  { symbol: 'HEROMOTOCO', name: 'Hero MotoCorp Ltd.' },
  { symbol: 'BAJAJ-AUTO', name: 'Bajaj Auto Ltd.' },
  { symbol: 'EICHERMOT', name: 'Eicher Motors Ltd.' },
  { symbol: 'ASHOKLEY', name: 'Ashok Leyland Ltd.' },
  { symbol: 'M&M', name: 'Mahindra & Mahindra Ltd.' },
  { symbol: 'TVSMOTOR', name: 'TVS Motor Company Ltd.' },
  
  // Pharma Sector
  { symbol: 'SUNPHARMA', name: 'Sun Pharmaceutical Industries Ltd.' },
  { symbol: 'DRREDDY', name: 'Dr. Reddy\'s Laboratories Ltd.' },
  { symbol: 'CIPLA', name: 'Cipla Ltd.' },
  { symbol: 'DIVISLAB', name: 'Divi\'s Laboratories Ltd.' },
  { symbol: 'APOLLOHOSP', name: 'Apollo Hospitals Enterprise Ltd.' },
  { symbol: 'BIOCON', name: 'Biocon Ltd.' },
  { symbol: 'TORNTPHARM', name: 'Torrent Pharmaceuticals Ltd.' },
  
  // Energy Sector
  { symbol: 'NTPC', name: 'NTPC Ltd.' },
  { symbol: 'POWERGRID', name: 'Power Grid Corporation of India Ltd.' },
  { symbol: 'TATAPOWER', name: 'Tata Power Company Ltd.' },
  { symbol: 'ADANIPOWER', name: 'Adani Power Ltd.' },
  { symbol: 'JSWENERGY', name: 'JSW Energy Ltd.' },
  { symbol: 'TATACOMM', name: 'Tata Communications Ltd.' },
  
  // Real Estate
  { symbol: 'DLF', name: 'DLF Ltd.' },
  { symbol: 'GODREJPROP', name: 'Godrej Properties Ltd.' },
  { symbol: 'SUNTV', name: 'Sun TV Network Ltd.' },
  { symbol: 'PEL', name: 'Piramal Enterprises Ltd.' },
  
  // Telecom
  { symbol: 'BHARTIARTL', name: 'Bharti Airtel Ltd.' },
  { symbol: 'IDEA', name: 'Vodafone Idea Ltd.' },
  { symbol: 'MTNL', name: 'Mahanagar Telephone Nigam Ltd.' },
  
  // Cement
  { symbol: 'ULTRACEMCO', name: 'UltraTech Cement Ltd.' },
  { symbol: 'AMBUJACEM', name: 'Ambuja Cements Ltd.' },
  { symbol: 'ACC', name: 'ACC Ltd.' },
  { symbol: 'SHREECEM', name: 'Shree Cement Ltd.' },
  { symbol: 'RAMCOCEM', name: 'The Ramco Cements Ltd.' },
  
  // Metals
  { symbol: 'HINDALCO', name: 'Hindalco Industries Ltd.' },
  { symbol: 'VEDL', name: 'Vedanta Ltd.' },
  { symbol: 'JSWSTEEL', name: 'JSW Steel Ltd.' },
  { symbol: 'SAIL', name: 'Steel Authority of India Ltd.' },
  { symbol: 'HINDCOPPER', name: 'Hindustan Copper Ltd.' },
  
  // Oil & Gas
  { symbol: 'ONGC', name: 'Oil & Natural Gas Corporation Ltd.' },
  { symbol: 'IOC', name: 'Indian Oil Corporation Ltd.' },
  { symbol: 'BPCL', name: 'Bharat Petroleum Corporation Ltd.' },
  { symbol: 'HPCL', name: 'Hindustan Petroleum Corporation Ltd.' },
  { symbol: 'GAIL', name: 'GAIL India Ltd.' },
  
  // Consumer Durables
  { symbol: 'HAVELLS', name: 'Havells India Ltd.' },
  { symbol: 'CROMPTON', name: 'Crompton Greaves Consumer Electricals Ltd.' },
  { symbol: 'VOLTAS', name: 'Voltas Ltd.' },
  { symbol: 'BLUESTARCO', name: 'Blue Star Ltd.' },
  
  // Others
  { symbol: 'LARSEN', name: 'Larsen & Toubro Ltd.' },
  { symbol: 'BHARATFORG', name: 'Bharat Forge Ltd.' },
  { symbol: 'SIEMENS', name: 'Siemens Ltd.' },
  { symbol: 'ABB', name: 'ABB India Ltd.' },
  { symbol: 'SCHNEIDER', name: 'Schneider Electric Infrastructure Ltd.' }
]

// Popular stock symbols for suggestions (keeping the top ones)
const popularStocks = [
  { symbol: 'RELIANCE', name: 'Reliance Industries Ltd.' },
  { symbol: 'TCS', name: 'Tata Consultancy Services Ltd.' },
  { symbol: 'HDFCBANK', name: 'HDFC Bank Ltd.' },
  { symbol: 'INFY', name: 'Infosys Ltd.' },
  { symbol: 'ICICIBANK', name: 'ICICI Bank Ltd.' },
  { symbol: 'HINDUNILVR', name: 'Hindustan Unilever Ltd.' },
  { symbol: 'ITC', name: 'ITC Ltd.' },
  { symbol: 'SBIN', name: 'State Bank of India' },
  { symbol: 'BHARTIARTL', name: 'Bharti Airtel Ltd.' },
  { symbol: 'AXISBANK', name: 'Axis Bank Ltd.' }
]

// Handle search input
const handleSearch = () => {
  if (searchQuery.value.length < 2) {
    searchSuggestions.value = []
    return
  }
  
  const query = searchQuery.value.toLowerCase()
  
  // Search through comprehensive Indian stocks list
  const suggestions = indianStocksList.filter(stock => 
    stock.symbol.toLowerCase().includes(query) || 
    stock.name.toLowerCase().includes(query)
  )
  
  // Sort by relevance (exact matches first, then partial matches)
  const sortedSuggestions = suggestions.sort((a, b) => {
    const aSymbolMatch = a.symbol.toLowerCase().startsWith(query)
    const bSymbolMatch = b.symbol.toLowerCase().startsWith(query)
    const aNameMatch = a.name.toLowerCase().includes(query)
    const bNameMatch = b.name.toLowerCase().includes(query)
    
    if (aSymbolMatch && !bSymbolMatch) return -1
    if (!aSymbolMatch && bSymbolMatch) return 1
    if (aNameMatch && !bNameMatch) return -1
    if (!aNameMatch && bNameMatch) return 1
    return a.symbol.localeCompare(b.symbol)
  })
  
  searchSuggestions.value = sortedSuggestions.slice(0, 10) // Show top 10 matches
}

// Select a suggestion
const selectSuggestion = (suggestion) => {
  searchQuery.value = suggestion.symbol
  searchSuggestions.value = []
  searchStock()
}

// Search for a stock
const searchStock = async () => {
  if (!searchQuery.value.trim()) return
  
  isSearching.value = true
  searchResult.value = null
  
  try {
    const symbol = searchQuery.value.toUpperCase().trim()
    const stockData = await fetchStockData(symbol)
    
    if (stockData) {
      searchResult.value = stockData
    } else {
      // Show error or fallback data
      searchResult.value = {
        symbol: symbol,
        name: 'Company Name Not Available',
        price: 0,
        priceChange: 0,
        change: 0,
        open: 0,
        high: 0,
        low: 0,
        volume: 0
      }
    }
  } catch (error) {
    console.error('Error searching for stock:', error)
    // Show error message to user
  } finally {
    isSearching.value = false
  }
}

// Fetch stock data from free API
const fetchStockData = async (symbol) => {
  try {
    // For Indian stocks, we need to add .NS suffix for NSE
    const nseSymbol = symbol.includes('.NS') ? symbol : `${symbol}.NS`
    
    // Try using Yahoo Finance for Indian stocks (no API key required)
    const response = await fetch(`https://api.allorigins.win/get?url=https://query1.finance.yahoo.com/v8/finance/chart/${nseSymbol}`)
    const data = await response.json()
    
    if (data.contents) {
      const yahooData = JSON.parse(data.contents)
      if (yahooData.chart && yahooData.chart.result && yahooData.chart.result[0]) {
        const quote = yahooData.chart.result[0].meta
        const indicators = yahooData.chart.result[0].indicators.quote[0]
        
        const currentPrice = quote.regularMarketPrice
        const previousClose = quote.previousClose
        const priceChange = currentPrice - previousClose
        const changePercent = (priceChange / previousClose) * 100
        
        return {
          symbol: symbol.replace('.NS', ''),
          name: quote.symbol.replace('.NS', ''),
          price: currentPrice,
          priceChange: priceChange,
          change: changePercent,
          open: indicators.open[indicators.open.length - 1] || currentPrice,
          high: indicators.high[indicators.high.length - 1] || currentPrice,
          low: indicators.low[indicators.low.length - 1] || currentPrice,
          volume: indicators.volume[indicators.volume.length - 1] || 0
        }
      }
    }
    
    // Fallback: Try using Alpha Vantage API for Indian stocks
    return await fetchStockDataFallback(symbol)
  } catch (error) {
    console.error('Error fetching Indian stock data:', error)
    return await fetchStockDataFallback(symbol)
  }
}

// Fallback API method using Alpha Vantage API for Indian stocks
const fetchStockDataFallback = async (symbol) => {
  try {
    // Try Alpha Vantage API with .NS suffix for Indian stocks
    const nseSymbol = symbol.includes('.NS') ? symbol : `${symbol}.NS`
    const response = await fetch(`https://www.alphavantage.co/query?function=GLOBAL_QUOTE&symbol=${nseSymbol}&apikey=demo`)
    const data = await response.json()
    
    if (data['Global Quote']) {
      const quote = data['Global Quote']
      return {
        symbol: symbol.replace('.NS', ''),
        name: symbol.replace('.NS', ''),
        price: parseFloat(quote['05. price']) || 0,
        priceChange: parseFloat(quote['09. change']) || 0,
        change: parseFloat(quote['10. change percent']?.replace('%', '')) || 0,
        open: parseFloat(quote['02. open']) || 0,
        high: parseFloat(quote['03. high']) || 0,
        low: parseFloat(quote['04. low']) || 0,
        volume: parseInt(quote['06. volume']) || 0
      }
    }
    
    // If no data found, return null
    return null
  } catch (error) {
    console.error('Error fetching fallback data for Indian stocks:', error)
    return null
  }
}

// Fetch top 10 stocks data
const fetchTopStocks = async () => {
  isLoading.value = true
  
  try {
    // Fetch data for popular stocks
    const stockPromises = popularStocks.slice(0, 10).map(async (stock) => {
      const data = await fetchStockData(stock.symbol)
      if (data) {
        // Generate random chart data for visualization
        data.chartData = Array.from({ length: 20 }, () => Math.random() * 100)
        return data
      }
      return null
    })
    
    const results = await Promise.all(stockPromises)
    topStocks.value = results.filter(stock => stock !== null)
    
  } catch (error) {
    console.error('Error fetching top stocks:', error)
    // Fallback to mock data if API fails
    topStocks.value = generateMockTopStocks()
  } finally {
    isLoading.value = false
  }
}

// Generate mock data for top Indian stocks (fallback)
const generateMockTopStocks = () => {
  return popularStocks.slice(0, 10).map(stock => {
    // Generate realistic Indian stock prices based on typical ranges
    let basePrice, priceRange
    switch(stock.symbol) {
      case 'RELIANCE':
        basePrice = 2500
        priceRange = 500
        break
      case 'TCS':
        basePrice = 3800
        priceRange = 400
        break
      case 'HDFCBANK':
        basePrice = 1600
        priceRange = 200
        break
      case 'INFY':
        basePrice = 1400
        priceRange = 150
        break
      case 'ICICIBANK':
        basePrice = 900
        priceRange = 100
        break
      case 'HINDUNILVR':
        basePrice = 2500
        priceRange = 200
        break
      case 'ITC':
        basePrice = 400
        priceRange = 50
        break
      case 'SBIN':
        basePrice = 600
        priceRange = 80
        break
      case 'BHARTIARTL':
        basePrice = 1100
        priceRange = 150
        break
      case 'AXISBANK':
        basePrice = 900
        priceRange = 100
        break
      default:
        basePrice = 1000
        priceRange = 200
    }
    
    const price = basePrice + (Math.random() - 0.5) * priceRange
    const priceChange = (Math.random() - 0.5) * (price * 0.1) // ±5% change
    const change = (priceChange / (price - priceChange)) * 100
    
    return {
      symbol: stock.symbol,
      name: stock.name,
      price: price,
      priceChange: priceChange,
      change: change,
      open: price + (Math.random() - 0.5) * (price * 0.02),
      high: price + Math.random() * (price * 0.03),
      low: price - Math.random() * (price * 0.03),
      volume: Math.floor(Math.random() * 10000000) + 1000000,
      chartData: Array.from({ length: 20 }, () => Math.random() * 100)
    }
  })
}

// Refresh top stocks
const refreshTopStocks = () => {
  fetchTopStocks()
}

// Format volume numbers
const formatVolume = (volume) => {
  if (volume >= 1000000000) {
    return (volume / 1000000000).toFixed(1) + 'B'
  } else if (volume >= 1000000) {
    return (volume / 1000000).toFixed(1) + 'M'
  } else if (volume >= 1000) {
    return (volume / 1000).toFixed(1) + 'K'
  }
  return volume.toString()
}

// Scroll animations
const initScrollAnimations = () => {
  const observerOptions = {
    threshold: 0.15,
    rootMargin: '0px 0px -100px 0px'
  };

  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.classList.add('animate-in');
        setTimeout(() => {
          entry.target.style.opacity = '1';
          entry.target.style.transform = 'translateX(0) translateY(0) scale(1)';
        }, 100);
      }
    });
  }, observerOptions);

  const animatedElements = document.querySelectorAll('.fade-in-left, .fade-in-right, .fade-in-up, .fade-in-down, .scale-in, .slide-in');
  animatedElements.forEach(el => {
    observer.observe(el);
    el.style.opacity = '0';
    el.style.transform = 'translateX(-100px) translateY(50px) scale(0.8)';
  });
};

// Market tabs
const activeTab = ref('indices')
const marketTabs = ref([
  { id: 'indices', name: 'Indices', icon: 'fas fa-chart-line' },
  { id: 'india', name: 'India', icon: 'fas fa-flag' },
  { id: 'stocks', name: 'Stocks', icon: 'fas fa-building' },
  { id: 'crypto', name: 'Crypto', icon: 'fab fa-bitcoin' },
  { id: 'forex', name: 'Forex', icon: 'fas fa-globe' },
  { id: 'commodities', name: 'Commodities', icon: 'fas fa-gem' }
])

// Indices data
const indices = ref([
  {
    name: 'S&P 500',
    symbol: 'SPX',
    price: 4489.30,
    priceChange: 10.40,
    change: 0.23,
    chartData: [60, 65, 70, 75, 80, 85, 90, 95, 92, 88, 85, 82, 78, 75, 72, 68, 65, 62, 58, 55]
  },
  {
    name: 'Nasdaq 100',
    symbol: 'NDX',
    price: 14679.60,
    priceChange: -105.50,
    change: -0.71,
    chartData: [75, 72, 68, 65, 62, 58, 55, 52, 48, 45, 42, 38, 35, 32, 28, 25, 22, 18, 15, 12]
  },
  {
    name: 'Dow Jones',
    symbol: 'DJI',
    price: 37432.50,
    priceChange: 85.20,
    change: 0.23,
    chartData: [50, 52, 55, 58, 62, 65, 68, 72, 75, 78, 82, 85, 88, 92, 95, 92, 88, 85, 82, 78]
  },
  {
    name: 'FTSE 100',
    symbol: 'UKX',
    price: 7689.40,
    priceChange: -12.30,
    change: -0.16,
    chartData: [40, 45, 50, 55, 60, 65, 70, 75, 80, 85, 90, 95, 92, 88, 85, 82, 78, 75, 72, 68]
  },
  {
    name: 'DAX',
    symbol: 'DAX',
    price: 16542.80,
    priceChange: 45.60,
    change: 0.28,
    chartData: [55, 60, 65, 70, 75, 80, 85, 90, 95, 92, 88, 85, 82, 78, 75, 72, 68, 65, 62, 58]
  },
  {
    name: 'Nikkei 225',
    symbol: 'NI225',
    price: 33464.20,
    priceChange: 125.40,
    change: 0.38,
    chartData: [65, 70, 75, 80, 85, 90, 95, 92, 88, 85, 82, 78, 75, 72, 68, 65, 62, 58, 55, 52]
  }
])

// Indian Indices data
const indiaIndices = ref([
  {
    name: 'Nifty 50 Index',
    symbol: 'NIFTY 50',
    logo: '50',
    price: 24363.30,
    priceChange: -234.50,
    change: -0.95,
    chartData: [45, 52, 48, 55, 62, 58, 65, 70, 68, 75, 72, 78, 82, 85, 88, 92, 89, 94, 91, 95]
  },
  {
    name: 'S&P BSE SENSEX Index',
    symbol: 'SENSEX',
    logo: 'BSE',
    price: 79857.79,
    priceChange: -765.20,
    change: -0.95,
    chartData: [60, 65, 70, 75, 80, 85, 90, 95, 92, 88, 85, 82, 78, 75, 72, 68, 65, 62, 58, 55]
  },
  {
    name: 'S&P BSE LargeCap Index',
    symbol: 'BSE LargeCap',
    logo: 'BSE',
    price: 9370.95,
    priceChange: -98.50,
    change: -1.04,
    chartData: [50, 52, 55, 58, 62, 65, 68, 72, 75, 78, 82, 85, 88, 92, 95, 92, 88, 85, 82, 78]
  },
  {
    name: 'S&P BSE MidCap Index',
    symbol: 'BSE MidCap',
    logo: 'BSE',
    price: 44570.89,
    priceChange: -705.30,
    change: -1.56,
    chartData: [75, 72, 68, 65, 62, 58, 55, 52, 48, 45, 42, 38, 35, 32, 28, 25, 22, 18, 15, 12]
  }
])

// Indian Stocks data
const indianStocks = ref([
  {
    name: 'ADANI ENTERPRISES LTD',
    symbol: 'ADANIENT',
    logo: 'A',
    price: 2178.10,
    change: -3.19
  },
  {
    name: 'ADANI PORT & SEZ LTD',
    symbol: 'ADANIPORTS',
    logo: 'AP',
    price: 1325.00,
    change: -1.52
  },
  {
    name: 'BHARTI AIRTEL LTD',
    symbol: 'BHARTIARTL',
    logo: 'BA',
    price: 1858.60,
    change: -3.33
  },
  {
    name: 'AMBUJA CEMENTS LTD',
    symbol: 'AMBUJACEM',
    logo: 'AC',
    price: 580.00,
    change: -2.08
  },
  {
    name: 'APOLLO HOSPITALS ENT...',
    symbol: 'APOLLOHOSP',
    logo: 'AH',
    price: 7084.50,
    change: -1.19
  },
  {
    name: 'RELIANCE INDUSTRIES',
    symbol: 'RELIANCE',
    logo: 'R',
    price: 2850.75,
    change: 1.25
  },
  {
    name: 'TCS',
    symbol: 'TCS',
    logo: 'T',
    price: 3850.20,
    change: -0.85
  },
  {
    name: 'INFOSYS LTD',
    symbol: 'INFY',
    logo: 'I',
    price: 1423.10,
    change: -0.96
  }
])

// Highest Volume Stocks
const highestVolumeStocks = ref([
  {
    name: 'BHARTI AIRTEL L...',
    symbol: 'BHARTIARTL',
    logo: 'BA',
    price: 1858.60,
    change: -3.33
  },
  {
    name: 'KALYAN JEWELLE...',
    symbol: 'KALYANKJIL',
    logo: 'KJ',
    price: 528.10,
    change: -10.64
  },
  {
    name: 'INFOSYS LTD',
    symbol: 'INFY',
    logo: 'I',
    price: 1423.10,
    change: -0.96
  },
  {
    name: 'NATIONAL SEC... D',
    symbol: 'NSDL',
    logo: 'NS',
    price: 1300.30,
    change: 15.77
  },
  {
    name: 'BSE LTD',
    symbol: 'BSE',
    logo: 'B',
    price: 2392.90,
    change: -2.02
  },
  {
    name: 'CUMMINS INDIA...',
    symbol: 'CUMMINSIND',
    logo: 'C',
    price: 3806.90,
    change: 3.62
  }
])

// Most Volatile Stocks
const mostVolatileStocks = ref([
  {
    name: 'MOS UTILITY LTD',
    symbol: 'MOS',
    logo: 'M',
    price: 49.50,
    change: -80.05
  },
  {
    name: 'NATURA HUE CHE... D',
    symbol: 'NATHUEC',
    logo: 'NH',
    price: 9.18,
    change: -13.40
  },
  {
    name: 'MIRC ELECTRONIC...',
    symbol: 'MIRCELECTR',
    logo: 'ME',
    price: 20.37,
    change: 19.61
  },
  {
    name: 'NESTLE INDIA LTD',
    symbol: 'NESTLEIND',
    logo: 'N',
    price: 1096.50,
    change: -1.86
  },
  {
    name: 'PG ELECTROPLAS...',
    symbol: 'PGEL',
    logo: 'PG',
    price: 588.80,
    change: -20.09
  },
  {
    name: 'MADHUVEER CO... D',
    symbol: 'MADHUVEER',
    logo: 'MC',
    price: 160.50,
    change: -13.24
  }
])

// US Stocks data
const stocks = ref([
  {
    name: 'Apple Inc.',
    symbol: 'AAPL',
    price: 185.42,
    priceChange: 4.15,
    change: 2.29,
    marketCap: '2.9T',
    volume: 45.2,
    chartData: [45, 52, 48, 55, 62, 58, 65, 70, 68, 75, 72, 78, 82, 85, 88, 92, 89, 94, 91, 95]
  },
  {
    name: 'NVIDIA Corp.',
    symbol: 'NVDA',
    price: 485.09,
    priceChange: 17.85,
    change: 3.82,
    marketCap: '1.2T',
    volume: 28.5,
    chartData: [60, 65, 70, 75, 80, 85, 90, 95, 92, 88, 85, 82, 78, 75, 72, 68, 65, 62, 58, 55]
  },
  {
    name: 'Microsoft Corp.',
    symbol: 'MSFT',
    price: 378.85,
    priceChange: 2.45,
    change: 0.65,
    marketCap: '2.8T',
    volume: 32.1,
    chartData: [50, 52, 55, 58, 62, 65, 68, 72, 75, 78, 82, 85, 88, 92, 95, 92, 88, 85, 82, 78]
  },
  {
    name: 'Tesla Inc.',
    symbol: 'TSLA',
    price: 248.50,
    priceChange: -3.02,
    change: -1.20,
    marketCap: '789B',
    volume: 32.8,
    chartData: [75, 72, 68, 65, 62, 58, 55, 52, 48, 45, 42, 38, 35, 32, 28, 25, 22, 18, 15, 12]
  },
  {
    name: 'Amazon.com',
    symbol: 'AMZN',
    price: 151.20,
    priceChange: 1.85,
    change: 1.24,
    marketCap: '1.6T',
    volume: 38.7,
    chartData: [55, 60, 65, 70, 75, 80, 85, 90, 95, 92, 88, 85, 82, 78, 75, 72, 68, 65, 62, 58]
  },
  {
    name: 'Alphabet Inc.',
    symbol: 'GOOGL',
    price: 142.65,
    priceChange: 0.95,
    change: 0.67,
    marketCap: '1.8T',
    volume: 25.3,
    chartData: [65, 70, 75, 80, 85, 90, 95, 92, 88, 85, 82, 78, 75, 72, 68, 65, 62, 58, 55, 52]
  }
])

// Cryptocurrency data
const cryptos = ref([
  {
    name: 'Bitcoin',
    symbol: 'BTC',
    price: 43250.50,
    priceChange: 1250.30,
    change: 2.98,
    marketCap: '847.2B',
    volume24h: '28.5B',
    icon: 'fab fa-bitcoin',
    chartData: [40, 45, 50, 55, 60, 65, 70, 75, 80, 85, 90, 95, 92, 88, 85, 82, 78, 75, 72, 68]
  },
  {
    name: 'Ethereum',
    symbol: 'ETH',
    price: 2650.75,
    priceChange: -45.25,
    change: -1.68,
    marketCap: '318.7B',
    volume24h: '15.2B',
    icon: 'fab fa-ethereum',
    chartData: [70, 68, 65, 62, 58, 55, 52, 48, 45, 42, 38, 35, 32, 28, 25, 22, 18, 15, 12, 10]
  },
  {
    name: 'Binance Coin',
    symbol: 'BNB',
    price: 312.40,
    priceChange: 8.75,
    change: 2.88,
    marketCap: '48.2B',
    volume24h: '2.1B',
    icon: 'fas fa-coins',
    chartData: [60, 65, 70, 75, 80, 85, 90, 95, 92, 88, 85, 82, 78, 75, 72, 68, 65, 62, 58, 55]
  },
  {
    name: 'Solana',
    symbol: 'SOL',
    price: 98.25,
    priceChange: 3.45,
    change: 3.64,
    marketCap: '42.8B',
    volume24h: '3.8B',
    icon: 'fas fa-sun',
    chartData: [55, 60, 65, 70, 75, 80, 85, 90, 95, 92, 88, 85, 82, 78, 75, 72, 68, 65, 62, 58]
  },
  {
    name: 'Cardano',
    symbol: 'ADA',
    price: 0.485,
    priceChange: -0.025,
    change: -4.90,
    marketCap: '17.2B',
    volume24h: '1.2B',
    icon: 'fas fa-cube',
    chartData: [75, 72, 68, 65, 62, 58, 55, 52, 48, 45, 42, 38, 35, 32, 28, 25, 22, 18, 15, 12]
  },
  {
    name: 'XRP',
    symbol: 'XRP',
    price: 0.542,
    priceChange: 0.012,
    change: 2.27,
    marketCap: '29.4B',
    volume24h: '2.8B',
    icon: 'fas fa-bolt',
    chartData: [50, 52, 55, 58, 62, 65, 68, 72, 75, 78, 82, 85, 88, 92, 95, 92, 88, 85, 82, 78]
  }
])

// Forex pairs data
const forexPairs = ref([
  {
    name: 'EUR/USD',
    symbol: 'EURUSD',
    price: 1.0856,
    priceChange: 0.0023,
    change: 0.21,
    high: 1.0870,
    low: 1.0820,
    chartData: [50, 52, 55, 58, 62, 65, 68, 72, 75, 78, 82, 85, 88, 92, 95, 92, 88, 85, 82, 78]
  },
  {
    name: 'GBP/USD',
    symbol: 'GBPUSD',
    price: 1.2654,
    priceChange: -0.0032,
    change: -0.25,
    high: 1.2680,
    low: 1.2620,
    chartData: [75, 72, 68, 65, 62, 58, 55, 52, 48, 45, 42, 38, 35, 32, 28, 25, 22, 18, 15, 12]
  },
  {
    name: 'USD/JPY',
    symbol: 'USDJPY',
    price: 148.25,
    priceChange: 0.45,
    change: 0.30,
    high: 148.50,
    low: 147.80,
    chartData: [60, 65, 70, 75, 80, 85, 90, 95, 92, 88, 85, 82, 78, 75, 72, 68, 65, 62, 58, 55]
  },
  {
    name: 'USD/CHF',
    symbol: 'USDCHF',
    price: 0.8542,
    priceChange: -0.0018,
    change: -0.21,
    high: 0.8560,
    low: 0.8520,
    chartData: [40, 45, 50, 55, 60, 65, 70, 75, 80, 85, 90, 95, 92, 88, 85, 82, 78, 75, 72, 68]
  },
  {
    name: 'AUD/USD',
    symbol: 'AUDUSD',
    price: 0.6724,
    priceChange: 0.0012,
    change: 0.18,
    high: 0.6740,
    low: 0.6700,
    chartData: [55, 60, 65, 70, 75, 80, 85, 90, 95, 92, 88, 85, 82, 78, 75, 72, 68, 65, 62, 58]
  },
  {
    name: 'USD/CAD',
    symbol: 'USDCAD',
    price: 1.3548,
    priceChange: -0.0025,
    change: -0.18,
    high: 1.3570,
    low: 1.3520,
    chartData: [65, 70, 75, 80, 85, 90, 95, 92, 88, 85, 82, 78, 75, 72, 68, 65, 62, 58, 55, 52]
  }
])

// Commodities data
const commodities = ref([
  {
    name: 'Gold Futures',
    symbol: 'GC',
    price: 2045.80,
    priceChange: 12.50,
    change: 0.61,
    open: 2033.30,
    volume: 125.4,
    icon: 'fas fa-gem',
    chartData: [60, 65, 70, 75, 80, 85, 90, 95, 92, 88, 85, 82, 78, 75, 72, 68, 65, 62, 58, 55]
  },
  {
    name: 'Silver Futures',
    symbol: 'SI',
    price: 23.45,
    priceChange: 0.25,
    change: 1.08,
    open: 23.20,
    volume: 85.2,
    icon: 'fas fa-ring',
    chartData: [50, 52, 55, 58, 62, 65, 68, 72, 75, 78, 82, 85, 88, 92, 95, 92, 88, 85, 82, 78]
  },
  {
    name: 'Crude Oil',
    symbol: 'CL',
    price: 78.25,
    priceChange: -1.20,
    change: -1.51,
    open: 79.45,
    volume: 245.8,
    icon: 'fas fa-fire',
    chartData: [75, 72, 68, 65, 62, 58, 55, 52, 48, 45, 42, 38, 35, 32, 28, 25, 22, 18, 15, 12]
  },
  {
    name: 'Copper Futures',
    symbol: 'HG',
    price: 3.85,
    priceChange: 0.08,
    change: 2.12,
    open: 3.77,
    volume: 45.6,
    icon: 'fas fa-industry',
    chartData: [55, 60, 65, 70, 75, 80, 85, 90, 95, 92, 88, 85, 82, 78, 75, 72, 68, 65, 62, 58]
  },
  {
    name: 'Natural Gas',
    symbol: 'NG',
    price: 2.85,
    priceChange: -0.12,
    change: -4.04,
    open: 2.97,
    volume: 125.3,
    icon: 'fas fa-fire-flame-simple',
    chartData: [40, 45, 50, 55, 60, 65, 70, 75, 80, 85, 90, 95, 92, 88, 85, 82, 78, 75, 72, 68]
  },
  {
    name: 'Wheat Futures',
    symbol: 'ZW',
    price: 5.85,
    priceChange: 0.15,
    change: 2.63,
    open: 5.70,
    volume: 35.2,
    icon: 'fas fa-seedling',
    chartData: [65, 70, 75, 80, 85, 90, 95, 92, 88, 85, 82, 78, 75, 72, 68, 65, 62, 58, 55, 52]
  }
])

// Market categories for global markets (keeping for other tabs)
const globalMarketCategories = ref([
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

// Add missing activeFilter variable
const activeFilter = ref('all')
const markets = ref([])

// Simulate real-time updates
let updateInterval

onMounted(() => {
  initScrollAnimations();
  
  // Fetch top stocks on component mount
  fetchTopStocks();
  
  updateInterval = setInterval(() => {
    // Update indices
    indices.value.forEach(index => {
      const change = (Math.random() - 0.5) * 0.5
      index.price += change
      index.priceChange += change
      index.change = (index.priceChange / (index.price - index.priceChange)) * 100
      index.chartData = index.chartData.map(() => Math.random() * 100)
    })
    
    // Update Indian indices
    indiaIndices.value.forEach(index => {
      const change = (Math.random() - 0.5) * 10
      index.price += change
      index.priceChange += change
      index.change = (index.priceChange / (index.price - index.priceChange)) * 100
      index.chartData = index.chartData.map(() => Math.random() * 100)
    })
    
    // Update Indian stocks
    indianStocks.value.forEach(stock => {
      const change = (Math.random() - 0.5) * 5
      stock.price += change
      stock.change += (Math.random() - 0.5) * 0.5
    })
    
    // Update highest volume stocks
    highestVolumeStocks.value.forEach(stock => {
      const change = (Math.random() - 0.5) * 3
      stock.price += change
      stock.change += (Math.random() - 0.5) * 0.3
    })
    
    // Update most volatile stocks
    mostVolatileStocks.value.forEach(stock => {
      const change = (Math.random() - 0.5) * 2
      stock.price += change
      stock.change += (Math.random() - 0.5) * 1
    })
    
    // Update stocks
    stocks.value.forEach(stock => {
      const change = (Math.random() - 0.5) * 1
      stock.price += change
      stock.priceChange += change
      stock.change = (stock.priceChange / (stock.price - stock.priceChange)) * 100
      stock.chartData = stock.chartData.map(() => Math.random() * 100)
    })
    
    // Update cryptos
    cryptos.value.forEach(crypto => {
      const change = (Math.random() - 0.5) * 50
      crypto.price += change
      crypto.priceChange += change
      crypto.change = (crypto.priceChange / (crypto.price - crypto.priceChange)) * 100
      crypto.chartData = crypto.chartData.map(() => Math.random() * 100)
    })
    
    // Update forex
    forexPairs.value.forEach(forex => {
      const change = (Math.random() - 0.5) * 0.0001
      forex.price += change
      forex.priceChange += change
      forex.change = (forex.priceChange / (forex.price - forex.priceChange)) * 100
      forex.chartData = forex.chartData.map(() => Math.random() * 100)
    })
    
    // Update commodities
    commodities.value.forEach(commodity => {
      const change = (Math.random() - 0.5) * 0.5
      commodity.price += change
      commodity.priceChange += change
      commodity.change = (commodity.priceChange / (commodity.price - commodity.priceChange)) * 100
      commodity.chartData = commodity.chartData.map(() => Math.random() * 100)
    })
  }, 3000) // Update every 3 seconds for more dynamic feel
})

onUnmounted(() => {
  if (updateInterval) {
    clearInterval(updateInterval)
  }
})

// Market categories for Indian stocks
const marketCategories = ref([
  {
    id: 1,
    name: 'Adani Group',
    description: 'Adani Group companies across various sectors',
    icon: 'fas fa-industry',
    symbols: ['ADANIENT', 'ADANIPORTS', 'ADANIGREEN', 'ADANITRANS', 'ADANIPOWER', 'ADANIGAS', 'ADANICAP', 'ADANIWILMAR'],
    change: 2.85
  },
  {
    id: 2,
    name: 'Banking',
    description: 'Major Indian banks and financial institutions',
    icon: 'fas fa-university',
    symbols: ['HDFCBANK', 'ICICIBANK', 'SBIN', 'AXISBANK', 'KOTAKBANK', 'INDUSINDBK', 'BANKBARODA', 'CANBK', 'PNB', 'UNIONBANK'],
    change: 1.42
  },
  {
    id: 3,
    name: 'IT & Technology',
    description: 'Information technology and software companies',
    icon: 'fas fa-laptop-code',
    symbols: ['TCS', 'INFY', 'WIPRO', 'TECHM', 'HCLTECH', 'MINDTREE', 'MPHASIS', 'LTI', 'PERSISTENT'],
    change: 3.75
  },
  {
    id: 4,
    name: 'FMCG',
    description: 'Fast Moving Consumer Goods companies',
    icon: 'fas fa-shopping-cart',
    symbols: ['HINDUNILVR', 'ITC', 'NESTLEIND', 'MARICO', 'DABUR', 'BRITANNIA', 'COLPAL', 'GODREJCP'],
    change: 0.95
  },
  {
    id: 5,
    name: 'Automobile',
    description: 'Automobile and auto component manufacturers',
    icon: 'fas fa-car',
    symbols: ['TATAMOTORS', 'MARUTI', 'HEROMOTOCO', 'BAJAJ-AUTO', 'EICHERMOT', 'ASHOKLEY', 'M&M', 'TVSMOTOR'],
    change: 2.15
  },
  {
    id: 6,
    name: 'Pharmaceuticals',
    description: 'Pharmaceutical and healthcare companies',
    icon: 'fas fa-pills',
    symbols: ['SUNPHARMA', 'DRREDDY', 'CIPLA', 'DIVISLAB', 'APOLLOHOSP', 'BIOCON', 'TORNTPHARM'],
    change: 1.68
  },
  {
    id: 7,
    name: 'Energy & Power',
    description: 'Power generation and energy companies',
    icon: 'fas fa-bolt',
    symbols: ['NTPC', 'POWERGRID', 'TATAPOWER', 'ADANIPOWER', 'JSWENERGY', 'RELIANCE'],
    change: 0.75
  },
  {
    id: 8,
    name: 'Metals & Mining',
    description: 'Metal, mining and steel companies',
    icon: 'fas fa-gem',
    symbols: ['TATASTEEL', 'HINDALCO', 'VEDL', 'JSWSTEEL', 'SAIL', 'HINDCOPPER'],
    change: -0.45
  },
  {
    id: 9,
    name: 'Oil & Gas',
    description: 'Oil exploration and refining companies',
    icon: 'fas fa-fire',
    symbols: ['ONGC', 'IOC', 'BPCL', 'HPCL', 'GAIL'],
    change: 1.25
  },
  {
    id: 10,
    name: 'Real Estate',
    description: 'Real estate development companies',
    icon: 'fas fa-building',
    symbols: ['DLF', 'GODREJPROP', 'SUNTV', 'PEL'],
    change: 1.85
  }
])

// Get stock category
const getStockCategory = (symbol) => {
  const category = marketCategories.value.find(category => 
    category.symbols.includes(symbol)
  )
  return category ? category.name : 'Other'
}

// Get total matches
const getTotalMatches = () => {
  return indianStocksList.filter(stock => 
    stock.symbol.toLowerCase().includes(searchQuery.value.toLowerCase()) || 
    stock.name.toLowerCase().includes(searchQuery.value.toLowerCase())
  ).length
}

// Show all results
const showAllResults = () => {
  searchSuggestions.value = indianStocksList.filter(stock => 
    stock.symbol.toLowerCase().includes(searchQuery.value.toLowerCase()) || 
    stock.name.toLowerCase().includes(searchQuery.value.toLowerCase())
  )
}
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

/* Main Navigation Header Styles */
.main-header {
  position: relative;
  z-index: 15;
  background: rgba(10, 10, 26, 0.95);
  backdrop-filter: blur(10px);
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
  padding: 1rem 0;
}

.main-header .header-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 2rem;
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.main-header .logo-section {
  display: flex;
  align-items: center;
  gap: 12px;
}

.main-header .logo {
  width: 40px;
  height: auto;
}

.main-header .brand-name {
  font-size: 1.5rem;
  font-weight: bold;
  color: #00ff88;
  margin: 0;
}

.main-header .nav-menu {
  display: flex;
  gap: 2rem;
}

.main-header .nav-link {
  color: white;
  text-decoration: none;
  font-weight: 500;
  transition: color 0.3s ease;
}

.main-header .nav-link:hover {
  color: #00ff88;
}

.main-header .nav-link.active {
  color: #00ff88;
}

.main-header .auth-buttons {
  display: flex;
  gap: 1rem;
}

.main-header .btn {
  padding: 0.75rem 1.5rem;
  border-radius: 8px;
  text-decoration: none;
  font-weight: 600;
  transition: all 0.3s ease;
  border: none;
  cursor: pointer;
}

.main-header .btn-outline {
  background: transparent;
  color: white;
  border: 2px solid rgba(255, 255, 255, 0.3);
}

.main-header .btn-outline:hover {
  border-color: #00ff88;
  color: #00ff88;
}

.main-header .btn-primary {
  background: linear-gradient(135deg, #00ff88 0%, #00d4aa 100%);
  color: #0a0a1a;
}

.main-header .btn-primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(0, 255, 136, 0.3);
}

/* Markets Header */
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
  user-select: none;
  -webkit-tap-highlight-color: transparent;
  touch-action: manipulation;
  min-height: 44px; /* Minimum touch target size */
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

/* Market Tabs */
.market-tabs {
  position: relative;
  z-index: 10;
  background: rgba(10, 10, 26, 0.95);
  backdrop-filter: blur(10px);
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
  padding: 1rem 0;
}

.tabs-container {
  display: flex;
  gap: 0.5rem;
  overflow-x: auto;
  padding: 0 2rem;
  scrollbar-width: none;
  -ms-overflow-style: none;
  -webkit-overflow-scrolling: touch;
  scroll-behavior: smooth;
}

.tabs-container::-webkit-scrollbar {
  display: none;
}

.tab-btn {
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
  white-space: nowrap;
  user-select: none;
  -webkit-tap-highlight-color: transparent;
  touch-action: manipulation;
  min-height: 44px; /* Minimum touch target size */
}

.tab-btn:hover {
  background: rgba(255, 255, 255, 0.15);
  border-color: rgba(255, 255, 255, 0.3);
}

.tab-btn.active {
  background: #00ff88;
  color: #0a0a1a;
  border-color: #00ff88;
}

.tab-btn i {
  font-size: 1rem;
}

/* Market Sections */
.market-section {
  padding: 3rem 0;
}

.section-subtitle {
  color: rgba(255, 255, 255, 0.7);
  font-size: 1rem;
  margin-top: 0.5rem;
}

/* Indices Grid */
.indices-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 1.5rem;
}

.index-card {
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 12px;
  padding: 1.5rem;
  transition: all 0.3s ease;
  backdrop-filter: blur(10px);
  cursor: pointer;
  user-select: none;
  -webkit-tap-highlight-color: transparent;
  touch-action: manipulation;
}

.index-card:hover {
  border-color: rgba(0, 255, 136, 0.3);
  transform: translateY(-2px);
}

.index-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 1rem;
}

.index-info {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.index-logo {
  width: 40px;
  height: 40px;
  background: linear-gradient(135deg, #00ff88 0%, #00d4aa 100%);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: bold;
  color: #0a0a1a;
  font-size: 0.9rem;
}

.logo-text {
  font-weight: bold;
  color: #0a0a1a;
}

.index-info h3 {
  font-size: 1.2rem;
  font-weight: bold;
  margin: 0 0 0.25rem 0;
  color: white;
}

.index-change {
  display: flex;
  align-items: center;
  gap: 0.25rem;
  font-weight: bold;
}

.index-change.up {
  color: #00ff88;
}

.index-change.down {
  color: #ff4757;
}

.index-price {
  display: flex;
  align-items: baseline;
  gap: 0.5rem;
  margin-bottom: 1rem;
}

.index-chart {
  margin-bottom: 1rem;
}

/* Indian Stocks Section */
.indian-stocks-scroll {
  display: flex;
  gap: 1rem;
  overflow-x: auto;
  padding: 1rem 0;
  scrollbar-width: none;
  -ms-overflow-style: none;
  -webkit-overflow-scrolling: touch;
  scroll-behavior: smooth;
  scroll-snap-type: x mandatory;
}

.indian-stocks-scroll::-webkit-scrollbar {
  display: none;
}

.indian-stock-card {
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 12px;
  padding: 1.5rem;
  min-width: 280px;
  transition: all 0.3s ease;
  backdrop-filter: blur(10px);
  display: flex;
  align-items: center;
  gap: 1rem;
  cursor: pointer;
  user-select: none;
  -webkit-tap-highlight-color: transparent;
  touch-action: manipulation;
  scroll-snap-align: start;
}

.indian-stock-card:hover {
  border-color: rgba(0, 255, 136, 0.3);
  transform: translateY(-2px);
}

.stock-logo {
  width: 50px;
  height: 50px;
  background: linear-gradient(135deg, #00ff88 0%, #00d4aa 100%);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: bold;
  color: #0a0a1a;
  font-size: 1rem;
}

.stock-info {
  flex: 1;
}

.stock-symbol {
  font-weight: bold;
  color: white;
  font-size: 1.1rem;
  margin-bottom: 0.25rem;
}

.stock-name {
  color: rgba(255, 255, 255, 0.7);
  font-size: 0.9rem;
  line-height: 1.3;
}

.stock-price-info {
  text-align: right;
}

.stock-price {
  font-weight: bold;
  color: white;
  font-size: 1.1rem;
  margin-bottom: 0.25rem;
}

.stock-change {
  font-weight: bold;
  font-size: 0.9rem;
}

.stock-change.up {
  color: #00ff88;
}

.stock-change.down {
  color: #ff4757;
}

/* Market Overview */
.market-overview {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
  gap: 2rem;
  margin-top: 3rem;
}

.overview-section {
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 12px;
  padding: 1.5rem;
  backdrop-filter: blur(10px);
}

.overview-section h3 {
  font-size: 1.2rem;
  font-weight: bold;
  color: white;
  margin: 0 0 1rem 0;
}

.overview-list {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.overview-item {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 0.75rem;
  background: rgba(255, 255, 255, 0.02);
  border-radius: 8px;
  transition: all 0.3s ease;
}

.overview-item:hover {
  background: rgba(255, 255, 255, 0.05);
}

.stock-logo-small {
  width: 35px;
  height: 35px;
  background: linear-gradient(135deg, #00ff88 0%, #00d4aa 100%);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: bold;
  color: #0a0a1a;
  font-size: 0.8rem;
}

.stock-details {
  flex: 1;
}

.stock-name {
  font-weight: bold;
  color: white;
  font-size: 0.9rem;
  margin-bottom: 0.25rem;
}

.stock-symbol-small {
  color: rgba(255, 255, 255, 0.6);
  font-size: 0.8rem;
}

.stock-price-details {
  text-align: right;
}

.stock-price-small {
  font-weight: bold;
  color: white;
  font-size: 0.9rem;
  margin-bottom: 0.25rem;
}

.stock-change-small {
  font-weight: bold;
  font-size: 0.8rem;
}

.stock-change-small.up {
  color: #00ff88;
}

.stock-change-small.down {
  color: #ff4757;
}

.see-all-link {
  color: #00ff88;
  font-size: 0.9rem;
  font-weight: 500;
  text-decoration: none;
  margin-top: 1rem;
  display: block;
  cursor: pointer;
  transition: color 0.3s ease;
}

.see-all-link:hover {
  color: #00d4aa;
}

/* Stocks Grid */
.stocks-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 1.5rem;
}

.stock-card {
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 12px;
  padding: 1.5rem;
  transition: all 0.3s ease;
  backdrop-filter: blur(10px);
}

.stock-card:hover {
  border-color: rgba(0, 255, 136, 0.3);
  transform: translateY(-2px);
}

.stock-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 1rem;
}

.stock-info h3 {
  font-size: 1.2rem;
  font-weight: bold;
  margin: 0 0 0.25rem 0;
  color: white;
}

.stock-change {
  display: flex;
  align-items: center;
  gap: 0.25rem;
  font-weight: bold;
}

.stock-change.up {
  color: #00ff88;
}

.stock-change.down {
  color: #ff4757;
}

.stock-price {
  display: flex;
  align-items: baseline;
  gap: 0.5rem;
  margin-bottom: 1rem;
}

.stock-stats {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 0.75rem;
}

/* Crypto Grid */
.crypto-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 1.5rem;
}

.crypto-card {
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 12px;
  padding: 1.5rem;
  transition: all 0.3s ease;
  backdrop-filter: blur(10px);
}

.crypto-card:hover {
  border-color: rgba(0, 255, 136, 0.3);
  transform: translateY(-2px);
}

.crypto-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 1rem;
}

.crypto-info {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.crypto-icon {
  width: 40px;
  height: 40px;
  background: rgba(0, 255, 136, 0.1);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.2rem;
  color: #00ff88;
}

.crypto-info h3 {
  font-size: 1.2rem;
  font-weight: bold;
  margin: 0 0 0.25rem 0;
  color: white;
}

.crypto-change {
  display: flex;
  align-items: center;
  gap: 0.25rem;
  font-weight: bold;
}

.crypto-change.up {
  color: #00ff88;
}

.crypto-change.down {
  color: #ff4757;
}

.crypto-price {
  display: flex;
  align-items: baseline;
  gap: 0.5rem;
  margin-bottom: 1rem;
}

.crypto-stats {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 0.75rem;
}

/* Forex Grid */
.forex-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 1.5rem;
}

.forex-card {
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 12px;
  padding: 1.5rem;
  transition: all 0.3s ease;
  backdrop-filter: blur(10px);
}

.forex-card:hover {
  border-color: rgba(0, 255, 136, 0.3);
  transform: translateY(-2px);
}

.forex-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 1rem;
}

.forex-info h3 {
  font-size: 1.2rem;
  font-weight: bold;
  margin: 0 0 0.25rem 0;
  color: white;
}

.forex-change {
  display: flex;
  align-items: center;
  gap: 0.25rem;
  font-weight: bold;
}

.forex-change.up {
  color: #00ff88;
}

.forex-change.down {
  color: #ff4757;
}

.forex-price {
  display: flex;
  align-items: baseline;
  gap: 0.5rem;
  margin-bottom: 1rem;
}

.forex-stats {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 0.75rem;
}

/* Commodities Grid */
.commodities-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 1.5rem;
}

.commodity-card {
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 12px;
  padding: 1.5rem;
  transition: all 0.3s ease;
  backdrop-filter: blur(10px);
}

.commodity-card:hover {
  border-color: rgba(0, 255, 136, 0.3);
  transform: translateY(-2px);
}

.commodity-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 1rem;
}

.commodity-info {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.commodity-icon {
  width: 40px;
  height: 40px;
  background: rgba(0, 255, 136, 0.1);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.2rem;
  color: #00ff88;
}

.commodity-info h3 {
  font-size: 1.2rem;
  font-weight: bold;
  margin: 0 0 0.25rem 0;
  color: white;
}

.commodity-change {
  display: flex;
  align-items: center;
  gap: 0.25rem;
  font-weight: bold;
}

.commodity-change.up {
  color: #00ff88;
}

.commodity-change.down {
  color: #ff4757;
}

.commodity-price {
  display: flex;
  align-items: baseline;
  gap: 0.5rem;
  margin-bottom: 1rem;
}

.commodity-stats {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 0.75rem;
}

/* Common Styles */
.price-value {
  font-size: 1.5rem;
  font-weight: bold;
  color: white;
}

.price-change {
  font-size: 0.9rem;
  color: rgba(255, 255, 255, 0.7);
}

.change-value {
  font-size: 1rem;
}

.change-arrow {
  font-size: 1.2rem;
}

.symbol {
  color: rgba(255, 255, 255, 0.6);
  font-size: 0.9rem;
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

/* Enhanced Scroll Animations - Markets Page */
.fade-in-left {
  opacity: 0;
  transform: translateX(-100px);
  transition: all 1.2s cubic-bezier(0.25, 0.46, 0.45, 0.94);
}

.fade-in-right {
  opacity: 0;
  transform: translateX(100px);
  transition: all 1.2s cubic-bezier(0.25, 0.46, 0.45, 0.94);
}

.fade-in-up {
  opacity: 0;
  transform: translateY(80px);
  transition: all 1.2s cubic-bezier(0.25, 0.46, 0.45, 0.94);
}

.fade-in-down {
  opacity: 0;
  transform: translateY(-80px);
  transition: all 1.2s cubic-bezier(0.25, 0.46, 0.45, 0.94);
}

.scale-in {
  opacity: 0;
  transform: scale(0.6);
  transition: all 1.2s cubic-bezier(0.25, 0.46, 0.45, 0.94);
}

.slide-in {
  opacity: 0;
  transform: translateX(-150px) translateY(30px);
  transition: all 1.5s cubic-bezier(0.25, 0.46, 0.45, 0.94);
}

.animate-in {
  opacity: 1 !important;
  transform: translateX(0) translateY(0) scale(1) !important;
}

/* Stagger animations for markets page */
.indices-grid .index-card:nth-child(1) { transition-delay: 0.2s; }
.indices-grid .index-card:nth-child(2) { transition-delay: 0.4s; }
.indices-grid .index-card:nth-child(3) { transition-delay: 0.6s; }
.indices-grid .index-card:nth-child(4) { transition-delay: 0.8s; }
.indices-grid .index-card:nth-child(5) { transition-delay: 1.0s; }
.indices-grid .index-card:nth-child(6) { transition-delay: 1.2s; }

.indian-stocks-scroll .indian-stock-card:nth-child(1) { transition-delay: 0.2s; }
.indian-stocks-scroll .indian-stock-card:nth-child(2) { transition-delay: 0.4s; }
.indian-stocks-scroll .indian-stock-card:nth-child(3) { transition-delay: 0.6s; }
.indian-stocks-scroll .indian-stock-card:nth-child(4) { transition-delay: 0.8s; }
.indian-stocks-scroll .indian-stock-card:nth-child(5) { transition-delay: 1.0s; }
.indian-stocks-scroll .indian-stock-card:nth-child(6) { transition-delay: 1.2s; }

/* Responsive Design */

/* Tablet and smaller screens */
@media (max-width: 1024px) {
  .container {
    padding: 0 1.5rem;
  }
  
  .indices-grid {
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 1rem;
  }
  
  .header-content h1 {
    font-size: 2.2rem;
  }
}

/* Mobile landscape and smaller tablets */
@media (max-width: 768px) {
  /* Main Header Responsive */
  .main-header .header-container {
    flex-direction: column;
    gap: 1rem;
    text-align: center;
  }
  
  .main-header .nav-menu {
    gap: 1rem;
  }
  
  .main-header .auth-buttons {
    gap: 0.5rem;
  }
  
  .main-header .btn {
    padding: 0.5rem 1rem;
    font-size: 0.9rem;
  }
  
  .main-header .brand-name {
    font-size: 1.3rem;
  }
  
  /* Markets Page Specific */
  .header-container {
    flex-direction: column;
    gap: 1.5rem;
    text-align: center;
    padding: 0 1rem;
  }
  
  .back-button {
    align-self: flex-start;
    padding: 0.5rem 1rem;
    font-size: 0.9rem;
  }
  
  .header-content {
    order: 1;
  }
  
  .market-status {
    order: 2;
    align-self: center;
  }
  
  .header-content h1 {
    font-size: 2rem;
    line-height: 1.2;
  }
  
  .header-content p {
    font-size: 0.9rem;
  }
  
  /* Stock Search Section Mobile */
  .stock-search-section {
    padding: 2rem 0;
  }
  
  .search-container {
    padding: 0 1rem;
  }
  
  .search-header h2 {
    font-size: 1.6rem;
  }
  
  .search-input-wrapper {
    flex-direction: column;
    gap: 0.5rem;
  }
  
  .search-input {
    width: 100%;
    text-align: center;
  }
  
  .search-btn {
    width: 100%;
    margin-left: 0;
    margin-top: 0.5rem;
  }
  
  .search-suggestions {
    position: relative;
    margin-top: 1rem;
  }
  
  .suggestion-item {
    flex-direction: column;
    text-align: center;
    gap: 0.5rem;
  }
  
  .suggestion-name {
    margin: 0;
  }
  
  .result-stats {
    grid-template-columns: 1fr;
    gap: 0.75rem;
  }
  
  /* Top Stocks Section Mobile */
  .top-stocks-section {
    padding: 2rem 0;
  }
  
  .refresh-controls {
    justify-content: center;
    margin-top: 1rem;
  }
  
  .top-stocks-grid {
    grid-template-columns: 1fr;
    gap: 1rem;
  }
  
  .top-stocks-grid .stock-card {
    padding: 1.25rem;
  }
  
  .top-stocks-grid .stock-header {
    flex-wrap: wrap;
    gap: 0.5rem;
  }
  
  .top-stocks-grid .stock-info h3 {
    font-size: 1.1rem;
  }
  
  .company-name {
    font-size: 0.8rem;
  }
  
  /* Market Tabs */
  .market-tabs .container {
    padding: 0;
  }
  
  .tabs-container {
    padding: 0 1rem;
    gap: 0.25rem;
  }
  
  .tab-btn {
    padding: 0.6rem 1rem;
    font-size: 0.85rem;
    min-width: auto;
  }
  
  .tab-btn i {
    font-size: 0.9rem;
  }
  
  /* Section Headers */
  .section-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 1rem;
    margin-bottom: 1.5rem;
  }
  
  .section-header h2 {
    font-size: 1.6rem;
  }
  
  /* Grids */
  .indices-grid {
    grid-template-columns: 1fr;
    gap: 1rem;
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
    gap: 1rem;
  }
  
  .analysis-indicators {
    grid-template-columns: 1fr;
    gap: 0.75rem;
  }
  
  /* Cards */
  .index-card {
    padding: 1.25rem;
  }
  
  .index-header {
    flex-wrap: wrap;
    gap: 0.5rem;
  }
  
  .index-info {
    flex: 1;
    min-width: 0;
  }
  
  .index-info h3 {
    font-size: 1.1rem;
  }
  
  .symbol {
    font-size: 0.8rem;
  }
  
  /* Indian Stocks Scroll */
  .indian-stocks-scroll {
    margin: 0 -1rem;
    padding: 1rem;
  }
  
  .indian-stock-card {
    min-width: 250px;
    padding: 1rem;
  }
  
  /* Market Categories */
  .category-card {
    padding: 1.25rem;
  }
  
  /* Trading Background */
  .trading-background {
    display: none; /* Hide complex animations on mobile */
  }
  
  /* Price Tickers */
  .price-tickers {
    display: none; /* Hide on mobile for better performance */
  }
}

/* Mobile portrait */
@media (max-width: 480px) {
  /* Main Header Mobile */
  .main-header .header-container {
    padding: 0 1rem;
  }
  
  .main-header .nav-menu {
    flex-direction: column;
    gap: 0.5rem;
  }
  
  .main-header .auth-buttons {
    flex-direction: column;
    width: 100%;
    gap: 0.5rem;
  }
  
  .main-header .btn {
    width: 100%;
    text-align: center;
  }
  
  .main-header .brand-name {
    font-size: 1.2rem;
  }
  
  /* Markets Page Mobile */
  .container {
    padding: 0 1rem;
  }
  
  .header-container {
    padding: 0 1rem;
    gap: 1rem;
  }
  
  .back-button {
    width: 100%;
    justify-content: center;
    margin-bottom: 0.5rem;
  }
  
  .header-content h1 {
    font-size: 1.75rem;
  }
  
  /* Stock Search Section Mobile Portrait */
  .stock-search-section {
    padding: 1.5rem 0;
  }
  
  .search-header h2 {
    font-size: 1.4rem;
  }
  
  .search-header p {
    font-size: 0.9rem;
  }
  
  .search-input-wrapper {
    padding: 0.25rem;
  }
  
  .search-input {
    font-size: 0.9rem;
    padding: 0.5rem 0;
  }
  
  .search-btn {
    padding: 0.5rem 1rem;
    font-size: 0.9rem;
  }
  
  .result-card {
    padding: 1rem;
  }
  
  .result-info h3 {
    font-size: 1.3rem;
  }
  
  .result-price .price-value {
    font-size: 1.6rem;
  }
  
  .result-stats {
    gap: 0.5rem;
  }
  
  /* Top Stocks Section Mobile Portrait */
  .top-stocks-section {
    padding: 1.5rem 0;
  }
  
  .section-header h2 {
    font-size: 1.3rem;
  }
  
  .refresh-btn {
    padding: 0.4rem 0.8rem;
    font-size: 0.8rem;
  }
  
  .top-stocks-grid .stock-card {
    padding: 1rem;
    border-radius: 8px;
  }
  
  .top-stocks-grid .stock-info h3 {
    font-size: 1rem;
  }
  
  .top-stocks-grid .stock-price .price-value {
    font-size: 1.2rem;
  }
  
  .top-stocks-grid .stock-stats {
    gap: 0.5rem;
  }
  
  .market-tabs {
    padding: 0.75rem 0;
  }
  
  .tabs-container {
    padding: 0 1rem;
    gap: 0.125rem;
  }
  
  .tab-btn {
    padding: 0.5rem 0.75rem;
    font-size: 0.8rem;
    border-radius: 6px;
  }
  
  .tab-btn i {
    display: none; /* Hide icons on very small screens */
  }
  
  .market-section {
    padding: 2rem 0;
  }
  
  .section-header h2 {
    font-size: 1.4rem;
  }
  
  .index-card {
    padding: 1rem;
    border-radius: 8px;
  }
  
  .index-header {
    margin-bottom: 0.75rem;
  }
  
  .index-info h3 {
    font-size: 1rem;
  }
  
  .index-price .price-value {
    font-size: 1.4rem;
  }
  
  .indian-stock-card {
    min-width: 220px;
    padding: 0.875rem;
    flex-direction: column;
    text-align: center;
  }
  
  .indian-stock-logo {
    width: 35px;
    height: 35px;
  }
  
  .stock-info h3 {
    font-size: 0.95rem;
  }
  
  .stock-price {
    font-size: 1.1rem;
  }
  
  .category-card {
    padding: 1rem;
    text-align: center;
  }
  
  .category-stats {
    flex-direction: column;
    gap: 0.5rem;
  }
  
  .widget-container {
    padding: 1rem;
  }
  
  .widget-container h3 {
    font-size: 1rem;
    margin-bottom: 0.75rem;
  }
  
  .data-row {
    padding: 0.5rem 0;
  }
  
  .indicator {
    padding: 0.5rem;
    font-size: 0.85rem;
  }
}

/* Extra small screens */
@media (max-width: 360px) {
  .container {
    padding: 0 0.75rem;
  }
  
  .header-content h1 {
    font-size: 1.5rem;
  }
  
  .tab-btn {
    padding: 0.4rem 0.6rem;
    font-size: 0.75rem;
  }
  
  .section-header h2 {
    font-size: 1.25rem;
  }
  
  .index-card {
    padding: 0.875rem;
  }
  
  .indian-stock-card {
    min-width: 200px;
  }
}

/* Landscape orientation adjustments */
@media (max-height: 500px) and (orientation: landscape) {
  .market-section {
    padding: 1.5rem 0;
  }
  
  .header-container {
    flex-direction: row;
    padding: 1rem;
  }
  
  .header-content h1 {
    font-size: 1.5rem;
  }
  
  .trading-background {
    display: block;
    opacity: 0.3;
  }
}

/* High DPI screens */
@media (-webkit-min-device-pixel-ratio: 2), (min-resolution: 192dpi) {
  .index-logo,
  .indian-stock-logo,
  .category-icon {
    image-rendering: -webkit-optimize-contrast;
    image-rendering: crisp-edges;
  }
}

/* Stock Search Section Styles */
.stock-search-section {
  padding: 3rem 0;
  background: rgba(255, 255, 255, 0.02);
}

.search-container {
  max-width: 800px;
  margin: 0 auto;
}

.search-header {
  text-align: center;
  margin-bottom: 2rem;
}

.search-header h2 {
  font-size: 2rem;
  font-weight: bold;
  color: white;
  margin: 0 0 0.5rem 0;
}

.search-header p {
  color: rgba(255, 255, 255, 0.7);
  font-size: 1rem;
  margin: 0;
}

.search-box {
  position: relative;
  margin-bottom: 2rem;
}

.search-input-wrapper {
  display: flex;
  align-items: center;
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 12px;
  padding: 0.5rem;
  transition: all 0.3s ease;
}

.search-input-wrapper:focus-within {
  border-color: #00ff88;
  box-shadow: 0 0 0 3px rgba(0, 255, 136, 0.1);
}

.search-icon {
  color: rgba(255, 255, 255, 0.6);
  margin-right: 0.75rem;
  font-size: 1.1rem;
}

.search-input {
  flex: 1;
  background: transparent;
  border: none;
  color: white;
  font-size: 1rem;
  padding: 0.75rem 0;
  outline: none;
}

.search-input::placeholder {
  color: rgba(255, 255, 255, 0.5);
}

.search-btn {
  background: #00ff88;
  color: #0a0a1a;
  border: none;
  border-radius: 8px;
  padding: 0.75rem 1.5rem;
  cursor: pointer;
  font-weight: 600;
  transition: all 0.3s ease;
  margin-left: 0.5rem;
}

.search-btn:hover {
  background: #00d4aa;
  transform: translateY(-1px);
}

.search-suggestions {
  position: absolute;
  top: 100%;
  left: 0;
  right: 0;
  background: rgba(10, 10, 26, 0.95);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 8px;
  margin-top: 0.5rem;
  max-height: 300px;
  overflow-y: auto;
  z-index: 1000;
  backdrop-filter: blur(10px);
}

.suggestion-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem;
  cursor: pointer;
  transition: all 0.3s ease;
  border-bottom: 1px solid rgba(255, 255, 255, 0.05);
}

.suggestion-item:last-child {
  border-bottom: none;
}

.suggestion-item:hover {
  background: rgba(255, 255, 255, 0.05);
}

.suggestion-symbol {
  font-weight: bold;
  color: white;
  font-size: 1.1rem;
  min-width: 120px;
}

.suggestion-name {
  color: rgba(255, 255, 255, 0.7);
  font-size: 0.9rem;
  flex: 1;
  margin: 0 1rem;
  line-height: 1.3;
}

.suggestion-category {
  color: #00ff88;
  font-weight: 500;
  font-size: 0.8rem;
  background: rgba(0, 255, 136, 0.1);
  padding: 0.25rem 0.5rem;
  border-radius: 4px;
  white-space: nowrap;
}

.suggestions-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.75rem 1rem;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
  background: rgba(255, 255, 255, 0.02);
  border-radius: 8px 8px 0 0;
}

.suggestions-header span:first-child {
  color: rgba(255, 255, 255, 0.8);
  font-size: 0.9rem;
}

.view-all {
  color: #00ff88;
  font-size: 0.9rem;
  font-weight: 500;
  cursor: pointer;
  transition: color 0.3s ease;
}

.view-all:hover {
  color: #00d4aa;
}

.suggestions-footer {
  text-align: center;
  padding: 0.75rem;
  border-top: 1px solid rgba(255, 255, 255, 0.1);
  background: rgba(255, 255, 255, 0.02);
  border-radius: 0 0 8px 8px;
}

.view-all-btn {
  background: #00ff88;
  color: #0a0a1a;
  border: none;
  border-radius: 6px;
  padding: 0.5rem 1rem;
  cursor: pointer;
  font-weight: 600;
  font-size: 0.9rem;
  transition: all 0.3s ease;
}

.view-all-btn:hover {
  background: #00d4aa;
  transform: translateY(-1px);
}

.search-result {
  margin-top: 2rem;
}

.result-card {
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 12px;
  padding: 1.5rem;
  backdrop-filter: blur(10px);
}

.result-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 1rem;
}

.result-info h3 {
  font-size: 1.5rem;
  font-weight: bold;
  color: white;
  margin: 0 0 0.25rem 0;
}

.result-info p {
  color: rgba(255, 255, 255, 0.7);
  margin: 0;
}

.result-change {
  display: flex;
  align-items: center;
  gap: 0.25rem;
  font-weight: bold;
  font-size: 1.1rem;
}

.result-change.up {
  color: #00ff88;
}

.result-change.down {
  color: #ff4757;
}

.result-price {
  display: flex;
  align-items: baseline;
  gap: 0.5rem;
  margin-bottom: 1.5rem;
}

.result-price .price-value {
  font-size: 2rem;
  font-weight: bold;
  color: white;
}

.result-price .price-change {
  font-size: 1.1rem;
  color: rgba(255, 255, 255, 0.7);
}

.result-stats {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
  gap: 1rem;
}

/* Top Stocks Section Styles */
.top-stocks-section {
  padding: 3rem 0;
}

.refresh-controls {
  display: flex;
  gap: 0.5rem;
}

.refresh-btn {
  background: rgba(0, 255, 136, 0.1);
  color: #00ff88;
  border: 1px solid rgba(0, 255, 136, 0.3);
  padding: 0.5rem 1rem;
  border-radius: 6px;
  cursor: pointer;
  transition: all 0.3s ease;
  font-size: 0.9rem;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.refresh-btn:hover:not(:disabled) {
  background: rgba(0, 255, 136, 0.2);
  border-color: rgba(0, 255, 136, 0.5);
}

.refresh-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.loading-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 3rem;
  color: rgba(255, 255, 255, 0.7);
}

.loading-spinner {
  width: 40px;
  height: 40px;
  border: 3px solid rgba(255, 255, 255, 0.1);
  border-top: 3px solid #00ff88;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin-bottom: 1rem;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.top-stocks-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 1.5rem;
}

.top-stocks-grid .stock-card {
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 12px;
  padding: 1.5rem;
  transition: all 0.3s ease;
  backdrop-filter: blur(10px);
  cursor: pointer;
}

.top-stocks-grid .stock-card:hover {
  border-color: rgba(0, 255, 136, 0.3);
  transform: translateY(-2px);
}

.top-stocks-grid .stock-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 1rem;
}

.top-stocks-grid .stock-info h3 {
  font-size: 1.3rem;
  font-weight: bold;
  color: white;
  margin: 0 0 0.25rem 0;
}

.company-name {
  color: rgba(255, 255, 255, 0.7);
  font-size: 0.9rem;
  display: block;
}

.top-stocks-grid .stock-change {
  display: flex;
  align-items: center;
  gap: 0.25rem;
  font-weight: bold;
}

.top-stocks-grid .stock-change.up {
  color: #00ff88;
}

.top-stocks-grid .stock-change.down {
  color: #ff4757;
}

.top-stocks-grid .stock-price {
  display: flex;
  align-items: baseline;
  gap: 0.5rem;
  margin-bottom: 1rem;
}

.top-stocks-grid .stock-price .price-value {
  font-size: 1.4rem;
  font-weight: bold;
  color: white;
}

.top-stocks-grid .stock-price .price-change {
  font-size: 0.9rem;
  color: rgba(255, 255, 255, 0.7);
}

.top-stocks-grid .stock-stats {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 0.75rem;
  margin-bottom: 1rem;
}

.top-stocks-grid .stock-chart {
  margin-top: 1rem;
}

.no-data {
  text-align: center;
  padding: 3rem;
  color: rgba(255, 255, 255, 0.7);
}

.no-data-icon {
  font-size: 3rem;
  color: rgba(255, 255, 255, 0.3);
  margin-bottom: 1rem;
}

.no-data h3 {
  color: white;
  margin: 0 0 0.5rem 0;
}

.no-data p {
  margin: 0;
}

/* Landscape orientation adjustments */
@media (max-height: 500px) and (orientation: landscape) {
  .market-section {
    padding: 1.5rem 0;
  }
  
  .header-container {
    flex-direction: row;
    padding: 1rem;
  }
  
  .header-content h1 {
    font-size: 1.5rem;
  }
  
  .trading-background {
    display: block;
    opacity: 0.3;
  }
}

/* High DPI screens */
@media (-webkit-min-device-pixel-ratio: 2), (min-resolution: 192dpi) {
  .index-logo,
  .indian-stock-logo,
  .category-icon {
    image-rendering: -webkit-optimize-contrast;
    image-rendering: crisp-edges;
  }
}

/* Search Suggestions Styles */
.suggestions-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
}

.view-all {
  color: #00ff88;
  font-size: 0.9rem;
  font-weight: 500;
  text-decoration: none;
  cursor: pointer;
  transition: color 0.3s ease;
}

.view-all:hover {
  color: #00d4aa;
}

.suggestions-footer {
  text-align: right;
  margin-top: 1rem;
}

.view-all-btn {
  background: #00ff88;
  color: #0a0a1a;
  border: none;
  border-radius: 6px;
  padding: 0.5rem 1rem;
  cursor: pointer;
  transition: background-color 0.3s ease;
}

.view-all-btn:hover {
  background-color: #00d4aa;
}
</style>
