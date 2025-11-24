<template>
  <div class="alphavantage-screen">
    <!-- Floating Particles -->
    <div class="floating-particles">
      <div class="particle" v-for="i in 15" :key="i" :style="{ 
        left: Math.random() * 100 + '%', 
        animationDelay: Math.random() * 15 + 's',
        animationDuration: (Math.random() * 8 + 8) + 's'
      }"></div>
    </div>

    <!-- Header -->
    <div class="dashboard-header">
      <div class="header-content">
        <button @click="goBack" class="back-button">
          <span class="back-icon">←</span> Back
        </button>
        <div class="header-info">
          <h1 class="dashboard-title">Alpha Vantage Market</h1>
          <p class="dashboard-subtitle">US Stock Market Data & Options Chain</p>
        </div>
        <div class="market-status" :class="marketStatusClass">
          <span class="status-dot"></span>
          <span class="status-text">{{ marketStatusText }}</span>
        </div>
      </div>
    </div>

    <!-- Search Bar -->
    <div class="search-container">
      <div class="search-input-wrapper">
        <span class="search-icon">🔍</span>
        <input 
          v-model="searchQuery"
          @input="handleSearch"
          type="text" 
          placeholder="Search stocks (e.g., AAPL, MSFT, GOOGL...)"
          class="search-input"
        >
        <button v-if="searchQuery" @click="clearSearch" class="clear-button">
          <span>✕</span>
        </button>
      </div>
      <div v-if="searchResults.length > 0" class="search-results">
        <div 
          v-for="result in searchResults" 
          :key="result.symbol"
          @click="selectStock(result.symbol)"
          class="search-result-item"
        >
          <div class="result-symbol">{{ result.symbol }}</div>
          <div class="result-name">{{ result.name }}</div>
        </div>
      </div>
    </div>

    <!-- Tab Navigation -->
    <div class="tab-navigation">
      <button 
        @click="activeTab = 'popular'"
        :class="['tab-button', { active: activeTab === 'popular' }]"
      >
        <span class="tab-icon">⭐</span>
        All Stocks
      </button>
      <button 
        @click="activeTab = 'indian'"
        :class="['tab-button', { active: activeTab === 'indian' }]"
      >
        <span class="tab-icon">🇮🇳</span>
        Indian Markets
      </button>
      <button 
        @click="activeTab = 'ustech'"
        :class="['tab-button', { active: activeTab === 'ustech' }]"
      >
        <span class="tab-icon">💻</span>
        US Tech
      </button>
      <button 
        @click="activeTab = 'gainers'"
        :class="['tab-button', { active: activeTab === 'gainers' }]"
      >
        <span class="tab-icon">📈</span>
        Top Gainers
      </button>
      <button 
        @click="activeTab = 'losers'"
        :class="['tab-button', { active: activeTab === 'losers' }]"
      >
        <span class="tab-icon">📉</span>
        Top Losers
      </button>
    </div>

    <!-- Category Filter (when showing all stocks) -->
    <div v-if="activeTab === 'popular' && stockCategories.length > 0" class="category-filters">
      <button 
        @click="selectedCategory = null"
        :class="['category-button', { active: selectedCategory === null }]"
      >
        All Categories ({{ allStocks.length }})
      </button>
      <button 
        v-for="category in stockCategories" 
        :key="category"
        @click="selectedCategory = category"
        :class="['category-button', { active: selectedCategory === category }]"
      >
        {{ category }} ({{ getStockCountByCategory(category) }})
      </button>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="loading-container">
      <div class="loading-spinner"></div>
      <p class="loading-text">Loading market data...</p>
    </div>

    <!-- Stock List -->
    <div v-else class="stock-list-container">
      <!-- All Stocks Tab -->
      <div v-if="activeTab === 'popular'" class="stock-grid">
        <div 
          v-for="stock in filteredStocks" 
          :key="stock.symbol"
          @click="openOptionsChain(stock.symbol)"
          class="stock-card"
        >
          <div class="stock-header">
            <div class="stock-symbol">{{ stock.symbol }}</div>
            <div class="stock-price" v-if="stock.quote && !stock.quote.error">
              ${{ formatPrice(stock.quote.price) }}
            </div>
            <div class="stock-status" v-else-if="stock.quote && stock.quote.error">
              <span class="error-indicator">⚠️</span>
            </div>
          </div>
          <div class="stock-name">{{ stock.name }}</div>
          <div v-if="stock.category" class="stock-category">{{ stock.category }}</div>
          <div v-if="stock.quote && !stock.quote.error" class="stock-stats">
            <div class="stat-item">
              <span class="stat-label">Change:</span>
              <span 
                class="stat-value" 
                :class="stock.quote.change >= 0 ? 'positive' : 'negative'"
              >
                {{ formatChange(stock.quote.change) }}
              </span>
            </div>
            <div class="stat-item">
              <span class="stat-label">Volume:</span>
              <span class="stat-value">{{ formatVolume(stock.quote.volume) }}</span>
            </div>
            <div v-if="stock.quote.isLive" class="live-indicator">
              <span class="live-dot"></span>
              <span class="live-text">Live Data</span>
            </div>
          </div>
          <div v-else-if="stock.quote && stock.quote.error" class="quote-error">
            <div class="error-message">{{ stock.quote.errorMessage }}</div>
            <button 
              @click.stop="fetchStockQuote(stock.symbol)" 
              class="retry-button"
            >
              Try Again
            </button>
          </div>
          <div v-else class="no-quote">
            <button 
              v-if="isSymbolSupported(stock.symbol)"
              @click.stop="fetchStockQuote(stock.symbol)" 
              class="load-price-button"
            >
              Load Live Price
            </button>
            <div v-else class="unsupported-symbol">
              <div class="unsupported-label">Limited Support</div>
              <div class="unsupported-message">Alpha Vantage mainly supports US stocks</div>
              <button 
                @click.stop="fetchStockQuote(stock.symbol)" 
                class="try-anyway-button"
              >
                Try Anyway
              </button>
            </div>
          </div>
          <div class="options-label">Click for Options Chain</div>
        </div>
      </div>

      <!-- Indian Markets Tab -->
      <div v-if="activeTab === 'indian'" class="stock-grid">
        <div 
          v-for="stock in indianStocks" 
          :key="stock.symbol"
          @click="openOptionsChain(stock.symbol)"
          class="stock-card indian-market"
        >
          <div class="stock-header">
            <div class="stock-symbol">{{ stock.symbol }}</div>
            <div class="stock-price" v-if="stock.quote && !stock.quote.error">
              ${{ formatPrice(stock.quote.price) }}
            </div>
            <div class="stock-status" v-else-if="stock.quote && stock.quote.error">
              <span class="error-indicator">⚠️</span>
            </div>
          </div>
          <div class="stock-name">{{ stock.name }}</div>
          <div v-if="stock.quote" class="stock-stats">
            <div class="stat-item">
              <span class="stat-label">Change:</span>
              <span 
                class="stat-value" 
                :class="stock.quote.change >= 0 ? 'positive' : 'negative'"
              >
                {{ formatChange(stock.quote.change) }}
              </span>
            </div>
            <div class="stat-item">
              <span class="stat-label">Volume:</span>
              <span class="stat-value">{{ formatVolume(stock.quote.volume) }}</span>
            </div>
          </div>
          <div v-else class="loading-quote">
            <span class="loading-dots">Loading...</span>
          </div>
          <div class="options-label">Click for Options Chain</div>
        </div>
      </div>

      <!-- US Tech Tab -->
      <div v-if="activeTab === 'ustech'" class="stock-grid">
        <div 
          v-for="stock in usTechStocks" 
          :key="stock.symbol"
          @click="openOptionsChain(stock.symbol)"
          class="stock-card us-tech"
        >
          <div class="stock-header">
            <div class="stock-symbol">{{ stock.symbol }}</div>
            <div class="stock-price" v-if="stock.quote && !stock.quote.error">
              ${{ formatPrice(stock.quote.price) }}
            </div>
            <div class="stock-status" v-else-if="stock.quote && stock.quote.error">
              <span class="error-indicator">⚠️</span>
            </div>
          </div>
          <div class="stock-name">{{ stock.name }}</div>
          <div v-if="stock.quote" class="stock-stats">
            <div class="stat-item">
              <span class="stat-label">Change:</span>
              <span 
                class="stat-value" 
                :class="stock.quote.change >= 0 ? 'positive' : 'negative'"
              >
                {{ formatChange(stock.quote.change) }}
              </span>
            </div>
            <div class="stat-item">
              <span class="stat-label">Volume:</span>
              <span class="stat-value">{{ formatVolume(stock.quote.volume) }}</span>
            </div>
          </div>
          <div v-else class="loading-quote">
            <span class="loading-dots">Loading...</span>
          </div>
          <div class="options-label">Click for Options Chain</div>
        </div>
      </div>

      <!-- Top Gainers Tab -->
      <div v-if="activeTab === 'gainers'" class="stock-grid">
        <div 
          v-for="stock in topGainers" 
          :key="stock.ticker"
          @click="openOptionsChain(stock.ticker)"
          class="stock-card gainer"
        >
          <div class="stock-header">
            <div class="stock-symbol">{{ stock.ticker }}</div>
            <div class="stock-price">${{ formatPrice(stock.price) }}</div>
          </div>
          <div class="stock-stats">
            <div class="stat-item">
              <span class="stat-label">Change:</span>
              <span class="stat-value positive">+{{ stock.change_amount }}</span>
            </div>
            <div class="stat-item">
              <span class="stat-label">Change %:</span>
              <span class="stat-value positive">+{{ stock.change_percentage }}</span>
            </div>
            <div class="stat-item">
              <span class="stat-label">Volume:</span>
              <span class="stat-value">{{ formatVolume(stock.volume) }}</span>
            </div>
          </div>
          <div class="options-label">Click for Options Chain</div>
        </div>
      </div>

      <!-- Top Losers Tab -->
      <div v-if="activeTab === 'losers'" class="stock-grid">
        <div 
          v-for="stock in topLosers" 
          :key="stock.ticker"
          @click="openOptionsChain(stock.ticker)"
          class="stock-card loser"
        >
          <div class="stock-header">
            <div class="stock-symbol">{{ stock.ticker }}</div>
            <div class="stock-price">${{ formatPrice(stock.price) }}</div>
          </div>
          <div class="stock-stats">
            <div class="stat-item">
              <span class="stat-label">Change:</span>
              <span class="stat-value negative">{{ stock.change_amount }}</span>
            </div>
            <div class="stat-item">
              <span class="stat-label">Change %:</span>
              <span class="stat-value negative">{{ stock.change_percentage }}</span>
            </div>
            <div class="stat-item">
              <span class="stat-label">Volume:</span>
              <span class="stat-value">{{ formatVolume(stock.volume) }}</span>
            </div>
          </div>
          <div class="options-label">Click for Options Chain</div>
        </div>
      </div>
    </div>

    <!-- Options Chain Modal -->
    <div v-if="showOptionsModal" class="modal-overlay" @click="closeOptionsModal">
      <div class="options-modal" @click.stop>
        <div class="modal-header">
          <div class="modal-title">
            <span class="stock-symbol-large">{{ selectedStock }}</span>
            <span class="modal-subtitle">Options Chain</span>
          </div>
          <button @click="closeOptionsModal" class="close-modal-btn">✕</button>
        </div>
        
        <div class="modal-content">
          <div v-if="loadingOptions" class="loading-options">
            <div class="loading-spinner"></div>
            <p>Loading options data...</p>
          </div>
          
          <div v-else-if="optionsError" class="options-error">
            <div class="error-icon">⚠️</div>
            <h3>Options Data Unavailable</h3>
            <p>{{ optionsError }}</p>
            <div class="error-note">
              <strong>Note:</strong> Options data requires a Premium Alpha Vantage subscription.
              <br>Free tier provides stock data only.
            </div>
          </div>

          <div v-else-if="optionsData.length > 0" class="options-table-container">
            <div class="options-tabs">
              <button 
                @click="optionsType = 'calls'"
                :class="['options-tab', { active: optionsType === 'calls' }]"
              >
                Calls
              </button>
              <button 
                @click="optionsType = 'puts'"
                :class="['options-tab', { active: optionsType === 'puts' }]"
              >
                Puts
              </button>
            </div>

            <div class="options-table">
              <div class="options-table-header">
                <div class="header-cell">Strike</div>
                <div class="header-cell">Last Price</div>
                <div class="header-cell">Change</div>
                <div class="header-cell">Volume</div>
                <div class="header-cell">Open Interest</div>
              </div>
              
              <div class="options-table-body">
                <div 
                  v-for="option in filteredOptions" 
                  :key="option.contractID"
                  class="options-row"
                >
                  <div class="table-cell">{{ option.strike }}</div>
                  <div class="table-cell">${{ option.lastPrice }}</div>
                  <div 
                    class="table-cell"
                    :class="option.change >= 0 ? 'positive' : 'negative'"
                  >
                    {{ formatChange(option.change) }}
                  </div>
                  <div class="table-cell">{{ option.volume }}</div>
                  <div class="table-cell">{{ option.openInterest }}</div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Error Toast -->
    <div v-if="error" class="error-toast">
      <span class="error-icon">⚠️</span>
      <span class="error-message">{{ error }}</span>
      <button @click="error = ''" class="close-error-btn">✕</button>
    </div>

    <!-- Mobile Navigation Component -->
    <AdminMobileNav />
  </div>
</template>

<script>
import AdminMobileNav from '../../components/AdminMobileNav.vue';
import axios from 'axios';

export default {
  name: 'AlphaVantageMarket',
  components: {
    AdminMobileNav
  },
  data() {
    return {
      loading: true,
      error: '',
      searchQuery: '',
      searchResults: [],
      searchTimeout: null,
      activeTab: 'popular',
      
      // Market status
      marketStatus: 'Unknown',
      
      // Stock data
      allStocks: [],
      stockCategories: [],
      selectedCategory: null,
      topGainers: [],
      topLosers: [],
      stockQuotes: {},
      
      // Options modal
      showOptionsModal: false,
      selectedStock: '',
      optionsData: [],
      loadingOptions: false,
      optionsError: '',
      optionsType: 'calls'
    }
  },
  computed: {
    filteredStocks() {
      let stocks = this.allStocks;
      
      // Filter by selected category
      if (this.selectedCategory) {
        stocks = stocks.filter(stock => stock.category === this.selectedCategory);
      }
      
      // Add quote data
      return stocks.map(stock => ({
        ...stock,
        quote: this.stockQuotes[stock.symbol] || null
      }));
    },
    indianStocks() {
      return this.allStocks
        .filter(stock => stock.category === 'Indian Markets')
        .map(stock => ({
          ...stock,
          quote: this.stockQuotes[stock.symbol] || null
        }));
    },
    usTechStocks() {
      return this.allStocks
        .filter(stock => stock.category === 'US Tech Giants')
        .map(stock => ({
          ...stock,
          quote: this.stockQuotes[stock.symbol] || null
        }));
    },
    marketStatusClass() {
      return this.marketStatus.toLowerCase().replace(/\s+/g, '-');
    },
    marketStatusText() {
      return this.marketStatus === 'Unknown' ? 'Market Status' : this.marketStatus;
    },
    filteredOptions() {
      if (!this.optionsData.length) return [];
      
      return this.optionsData.filter(option => {
        if (this.optionsType === 'calls') {
          return option.type === 'call' || option.contractSymbol?.includes('C');
        } else {
          return option.type === 'put' || option.contractSymbol?.includes('P');
        }
      });
    }
  },
  async mounted() {
    console.log('🚀 Alpha Vantage Market component mounted');
    await this.initializeData();
  },
  methods: {
    async initializeData() {
      this.loading = true;
      try {
        // Load data in parallel
        await Promise.all([
          this.fetchMarketStatus(),
          this.fetchAllStocks(),
          this.fetchStockCategories(),
          this.fetchTopGainersLosers()
        ]);
      } catch (error) {
        console.error('Failed to initialize data:', error);
        this.error = 'Failed to load market data. Please try again.';
      } finally {
        this.loading = false;
      }
    },
    
    async fetchMarketStatus() {
      try {
        const response = await axios.get('/api/alphavantage/market/status');
        if (response.data.success && response.data.data.markets) {
          // Find US market status
          const usMarket = response.data.data.markets.find(m => 
            m.region === 'United States' || m.market_type === 'Equity'
          );
          this.marketStatus = usMarket ? usMarket.current_status : 'Unknown';
        }
      } catch (error) {
        console.error('Failed to fetch market status:', error);
        this.marketStatus = 'Unknown';
      }
    },
    
    async fetchAllStocks() {
      try {
        const response = await axios.get('/api/alphavantage/stocks/popular');
        if (response.data.success) {
          this.allStocks = response.data.data;
          console.log('✅ Loaded', response.data.total_stocks, 'stocks');
          
          // Only fetch quotes for first few US stocks (Alpha Vantage supports US better)
          // User can click on stocks they want to see live data for
          const usStocks = this.allStocks.filter(stock => 
            !stock.symbol.includes('.BSE') && !stock.symbol.includes('.NSE')
          );
          
          for (let i = 0; i < Math.min(usStocks.length, 5); i++) {
            const stock = usStocks[i];
            await this.fetchStockQuote(stock.symbol);
            // Add delay between API calls to avoid hitting rate limits
            await new Promise(resolve => setTimeout(resolve, 300));
          }
        }
      } catch (error) {
        console.error('Failed to fetch stocks:', error);
        this.error = 'Failed to load stocks.';
      }
    },

    async fetchStockCategories() {
      try {
        const response = await axios.get('/api/alphavantage/stocks/categories');
        if (response.data.success) {
          this.stockCategories = response.data.data;
          console.log('✅ Loaded', response.data.total_categories, 'stock categories');
        }
      } catch (error) {
        console.error('Failed to fetch stock categories:', error);
      }
    },
    
    async fetchTopGainersLosers() {
      try {
        const response = await axios.get('/api/alphavantage/market/top-gainers-losers');
        if (response.data.success && response.data.data) {
          this.topGainers = response.data.data.top_gainers || [];
          this.topLosers = response.data.data.top_losers || [];
        }
      } catch (error) {
        console.error('Failed to fetch top gainers/losers:', error);
        // Don't show error for this as it's not critical
      }
    },
    
    async fetchStockQuote(symbol) {
      try {
        const response = await axios.get(`/api/alphavantage/stocks/${symbol}/quote`);
        
        if (response.data.success && response.data.data && response.data.data['Global Quote']) {
          const quote = response.data.data['Global Quote'];
          this.stockQuotes[symbol] = {
            price: parseFloat(quote['05. price']) || 0,
            change: parseFloat(quote['09. change']) || 0,
            changePercent: quote['10. change percent'] || '0%',
            volume: parseInt(quote['06. volume']) || 0,
            isLive: true
          };
          console.log(`✅ Live quote for ${symbol}: $${quote['05. price']}`);
        } else {
          // Handle case where success is false or no Global Quote data
          const errorMessage = response.data.message || 'No quote data available';
          console.warn(`⚠️ ${symbol}: ${errorMessage}`);
          this.stockQuotes[symbol] = {
            error: true,
            errorMessage: errorMessage,
            isLive: false
          };
        }
      } catch (error) {
        console.error(`Failed to fetch quote for ${symbol}:`, error);
        
        // Handle HTTP error responses (like 400 Bad Request)
        let errorMessage = 'Failed to fetch quote';
        if (error.response && error.response.data) {
          if (error.response.data.error) {
            errorMessage = error.response.data.error;
          } else if (error.response.data.message) {
            errorMessage = error.response.data.message;
          }
        } else if (error.message) {
          errorMessage = error.message;
        }
        
        console.warn(`⚠️ ${symbol}: ${errorMessage}`);
        this.stockQuotes[symbol] = {
          error: true,
          errorMessage: errorMessage,
          isLive: false
        };
      }
    },

    async loadMoreQuotes(stocks) {
      // Load quotes on demand when user scrolls or clicks
      const stocksToLoad = stocks.slice(0, 10); // Load max 10 at a time
      
      for (const stock of stocksToLoad) {
        if (!this.stockQuotes[stock.symbol]) {
          await this.fetchStockQuote(stock.symbol);
          await new Promise(resolve => setTimeout(resolve, 250)); // Rate limit friendly
        }
      }
    },
    
    handleSearch() {
      clearTimeout(this.searchTimeout);
      
      if (this.searchQuery.length < 2) {
        this.searchResults = [];
        return;
      }
      
      this.searchTimeout = setTimeout(async () => {
        try {
          const response = await axios.get('/api/alphavantage/stocks/search', {
            params: { keywords: this.searchQuery }
          });
          
          if (response.data.success && response.data.data.bestMatches) {
            this.searchResults = response.data.data.bestMatches.slice(0, 5).map(item => ({
              symbol: item['1. symbol'],
              name: item['2. name']
            }));
          }
        } catch (error) {
          console.error('Search failed:', error);
        }
      }, 500);
    },
    
    selectStock(symbol) {
      this.searchQuery = symbol;
      this.searchResults = [];
      this.openOptionsChain(symbol);
    },
    
    clearSearch() {
      this.searchQuery = '';
      this.searchResults = [];
    },
    
    async openOptionsChain(symbol) {
      this.selectedStock = symbol;
      this.showOptionsModal = true;
      this.loadingOptions = true;
      this.optionsError = '';
      this.optionsData = [];
      
      try {
        const response = await axios.get(`/api/alphavantage/options/${symbol}`);
        
        if (response.data.success && response.data.data) {
          // Check if we got actual options data or an error
          if (response.data.data.error || response.data.data['Error Message']) {
            this.optionsError = response.data.data.error || 
                               response.data.data['Error Message'] || 
                               'Options data is not available for this symbol.';
          } else {
            // Process options data (structure may vary)
            this.optionsData = this.processOptionsData(response.data.data);
          }
        } else {
          this.optionsError = 'Options data is not available. This feature requires a Premium Alpha Vantage subscription.';
        }
      } catch (error) {
        console.error('Failed to fetch options data:', error);
        if (error.response?.status === 400 || error.response?.data?.error) {
          this.optionsError = error.response.data.error || 'Options data requires Premium subscription.';
        } else {
          this.optionsError = 'Failed to load options data. Please try again.';
        }
      } finally {
        this.loadingOptions = false;
      }
    },
    
    processOptionsData(data) {
      // Alpha Vantage options data structure processing
      if (data.data) {
        return data.data.map(option => ({
          contractID: option.contractID || Math.random().toString(36).substring(7),
          contractSymbol: option.contractSymbol || '',
          strike: option.strike || 0,
          lastPrice: option.lastPrice || 0,
          change: option.change || 0,
          volume: option.volume || 0,
          openInterest: option.openInterest || 0,
          type: option.type || (option.contractSymbol?.includes('C') ? 'call' : 'put')
        }));
      }
      return [];
    },
    
    closeOptionsModal() {
      this.showOptionsModal = false;
      this.selectedStock = '';
      this.optionsData = [];
      this.optionsError = '';
    },
    
    goBack() {
      this.$router.push('/admin/dashboard');
    },

    getStockCountByCategory(category) {
      return this.allStocks.filter(stock => stock.category === category).length;
    },

    isSymbolSupported(symbol) {
      // Alpha Vantage mainly supports US stocks and some international ones
      // BSE/NSE stocks have limited support
      return !symbol.includes('.BSE') && !symbol.includes('.NSE');
    },
    
    // Utility methods
    formatPrice(price) {
      if (!price) return '0.00';
      return parseFloat(price).toFixed(2);
    },
    
    formatChange(change) {
      if (!change) return '0.00';
      const formatted = parseFloat(change).toFixed(2);
      return change >= 0 ? `+${formatted}` : formatted;
    },
    
    formatVolume(volume) {
      if (!volume) return '0';
      if (volume >= 1000000) {
        return (volume / 1000000).toFixed(1) + 'M';
      } else if (volume >= 1000) {
        return (volume / 1000).toFixed(1) + 'K';
      }
      return volume.toLocaleString();
    }
  }
};
</script>

<style scoped>
/* Base Styles */
.alphavantage-screen {
  background: linear-gradient(135deg, #0f0f23, #1a1a2e, #16213e);
  background-attachment: fixed;
  color: white;
  min-height: 100vh;
  padding: 20px;
  width: 100%;
  max-width: 100vw;
  overflow-x: hidden;
  box-sizing: border-box;
  position: relative;
}

.alphavantage-screen::before {
  content: '';
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: 
    radial-gradient(circle at 20% 80%, rgba(255, 215, 0, 0.06) 0%, transparent 50%),
    radial-gradient(circle at 80% 20%, rgba(255, 215, 0, 0.04) 0%, transparent 50%),
    radial-gradient(circle at 40% 40%, rgba(255, 215, 0, 0.02) 0%, transparent 50%);
  pointer-events: none;
  z-index: 0;
}

/* Floating Particles */
.floating-particles {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  pointer-events: none;
  z-index: 1;
}

.particle {
  position: absolute;
  width: 3px;
  height: 3px;
  background: rgba(255, 215, 0, 0.5);
  border-radius: 50%;
  animation: float linear infinite;
  box-shadow: 0 0 8px rgba(255, 215, 0, 0.4);
}

@keyframes float {
  0% {
    transform: translateY(100vh) rotate(0deg);
    opacity: 0;
  }
  10% {
    opacity: 1;
  }
  90% {
    opacity: 1;
  }
  100% {
    transform: translateY(-100px) rotate(360deg);
    opacity: 0;
  }
}

/* Header Styles */
.dashboard-header {
  margin-bottom: 24px;
  position: relative;
  z-index: 2;
}

.header-content {
  background: linear-gradient(145deg, rgba(255, 215, 0, 0.05), rgba(255, 215, 0, 0.02));
  border: 1px solid rgba(255, 215, 0, 0.1);
  border-radius: 20px;
  padding: 24px;
  backdrop-filter: blur(10px);
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 16px;
}

.back-button {
  background: rgba(255, 215, 0, 0.1);
  border: 1px solid rgba(255, 215, 0, 0.3);
  border-radius: 12px;
  padding: 12px 16px;
  color: #FFD700;
  font-size: 16px;
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  gap: 8px;
}

.back-button:hover {
  background: rgba(255, 215, 0, 0.2);
  transform: translateX(-2px);
}

.back-icon {
  font-size: 18px;
}

.header-info {
  flex: 1;
  text-align: center;
  min-width: 250px;
}

.dashboard-title {
  font-size: 24px;
  font-weight: 800;
  color: #FFD700;
  margin: 0 0 8px 0;
  text-shadow: 0 0 20px rgba(255, 215, 0, 0.3);
}

.dashboard-subtitle {
  font-size: 14px;
  color: #b0b0b0;
  margin: 0;
}

.market-status {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 12px 16px;
  border-radius: 12px;
  background: rgba(0, 0, 0, 0.3);
  border: 1px solid rgba(255, 255, 255, 0.1);
}

.status-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: #666;
}

.market-status.open .status-dot {
  background: #FFD700;
  box-shadow: 0 0 8px rgba(255, 215, 0, 0.5);
}

.market-status.closed .status-dot {
  background: #ff6b6b;
}

.status-text {
  font-size: 14px;
  font-weight: 500;
}

/* Search Styles */
.search-container {
  margin-bottom: 24px;
  position: relative;
  z-index: 10;
}

.search-input-wrapper {
  position: relative;
  display: flex;
  align-items: center;
}

.search-icon {
  position: absolute;
  left: 16px;
  font-size: 18px;
  color: #666;
  z-index: 2;
}

.search-input {
  width: 100%;
  padding: 16px 50px 16px 50px;
  background: rgba(0, 0, 0, 0.3);
  border: 1px solid rgba(255, 215, 0, 0.2);
  border-radius: 16px;
  color: white;
  font-size: 16px;
  outline: none;
  transition: all 0.3s ease;
}

.search-input:focus {
  border-color: #FFD700;
  box-shadow: 0 0 20px rgba(255, 215, 0, 0.2);
}

.clear-button {
  position: absolute;
  right: 16px;
  background: rgba(255, 255, 255, 0.1);
  border: none;
  color: #999;
  font-size: 16px;
  cursor: pointer;
  padding: 4px 8px;
  border-radius: 50%;
  transition: all 0.3s ease;
}

.clear-button:hover {
  background: rgba(255, 255, 255, 0.2);
  color: white;
}

.search-results {
  position: absolute;
  top: 100%;
  left: 0;
  right: 0;
  background: rgba(13, 13, 26, 0.95);
  border: 1px solid rgba(255, 215, 0, 0.2);
  border-radius: 12px;
  margin-top: 8px;
  backdrop-filter: blur(20px);
  overflow: hidden;
  z-index: 20;
}

.search-result-item {
  padding: 12px 16px;
  cursor: pointer;
  transition: all 0.2s ease;
  border-bottom: 1px solid rgba(255, 255, 255, 0.05);
}

.search-result-item:hover {
  background: rgba(255, 215, 0, 0.1);
}

.search-result-item:last-child {
  border-bottom: none;
}

.result-symbol {
  font-weight: 600;
  color: #FFD700;
  font-size: 16px;
}

.result-name {
  color: #b0b0b0;
  font-size: 14px;
  margin-top: 2px;
}

/* Tab Navigation */
.tab-navigation {
  display: flex;
  gap: 8px;
  margin-bottom: 24px;
  position: relative;
  z-index: 2;
}

.tab-button {
  flex: 1;
  padding: 12px 16px;
  background: rgba(0, 0, 0, 0.3);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 12px;
  color: #b0b0b0;
  font-size: 14px;
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
}

.tab-button:hover {
  background: rgba(255, 215, 0, 0.05);
  color: white;
}

.tab-button.active {
  background: rgba(255, 215, 0, 0.1);
  border-color: rgba(255, 215, 0, 0.3);
  color: #FFD700;
}

.tab-icon {
  font-size: 16px;
}

/* Category Filters */
.category-filters {
  display: flex;
  gap: 12px;
  flex-wrap: wrap;
  padding: 20px 0;
  margin-bottom: 24px;
  border-bottom: 1px solid rgba(255, 215, 0, 0.1);
}

.category-button {
  background: linear-gradient(145deg, rgba(13, 13, 26, 0.8), rgba(16, 16, 34, 0.6));
  border: 1px solid rgba(255, 215, 0, 0.3);
  color: #b0b0b0;
  padding: 8px 16px;
  border-radius: 20px;
  font-size: 14px;
  cursor: pointer;
  transition: all 0.3s ease;
  backdrop-filter: blur(5px);
  white-space: nowrap;
}

.category-button:hover {
  color: #FFD700;
  border-color: rgba(255, 215, 0, 0.6);
  transform: translateY(-1px);
}

.category-button.active {
  background: linear-gradient(145deg, rgba(255, 215, 0, 0.2), rgba(255, 215, 0, 0.1));
  border-color: #FFD700;
  color: #ffffff;
  font-weight: 600;
}

/* Loading Styles */
.loading-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 60px 20px;
  position: relative;
  z-index: 2;
}

.loading-spinner {
  width: 40px;
  height: 40px;
  border: 3px solid rgba(255, 215, 0, 0.1);
  border-top: 3px solid #FFD700;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin-bottom: 16px;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.loading-text {
  color: #b0b0b0;
  font-size: 16px;
}

/* Stock Grid */
.stock-list-container {
  position: relative;
  z-index: 2;
}

.stock-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 20px;
}

.stock-card {
  background: linear-gradient(145deg, rgba(16, 16, 34, 0.8), rgba(13, 13, 26, 0.9));
  border: 1px solid rgba(255, 215, 0, 0.2);
  border-radius: 16px;
  padding: 20px;
  cursor: pointer;
  transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
  backdrop-filter: blur(10px);
  position: relative;
  overflow: hidden;
}

.stock-card:hover {
  transform: translateY(-4px) scale(1.02);
  border-color: #FFD700;
  box-shadow: 0 20px 40px rgba(255, 215, 0, 0.2);
}

.stock-card::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: linear-gradient(135deg, rgba(255, 215, 0, 0.05), transparent);
  opacity: 0;
  transition: opacity 0.3s ease;
  pointer-events: none;
}

.stock-card:hover::before {
  opacity: 1;
}

.stock-card.gainer {
  border-color: rgba(255, 215, 0, 0.4);
}

.stock-card.gainer::before {
  background: linear-gradient(135deg, rgba(255, 215, 0, 0.1), transparent);
}

.stock-card.loser {
  border-color: rgba(255, 107, 107, 0.4);
}

.stock-card.loser::before {
  background: linear-gradient(135deg, rgba(255, 107, 107, 0.1), transparent);
}

.stock-card.indian-market {
  border-color: rgba(255, 153, 51, 0.4);
}

.stock-card.indian-market::before {
  background: linear-gradient(135deg, rgba(255, 153, 51, 0.1), transparent);
}

.stock-card.us-tech {
  border-color: rgba(64, 224, 255, 0.4);
}

.stock-card.us-tech::before {
  background: linear-gradient(135deg, rgba(64, 224, 255, 0.1), transparent);
}

.stock-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 12px;
}

.stock-symbol {
  font-size: 20px;
  font-weight: 700;
  color: #FFD700;
  text-shadow: 0 0 10px rgba(255, 215, 0, 0.3);
}

.stock-price {
  font-size: 18px;
  font-weight: 600;
  color: white;
}

.stock-status {
  font-size: 16px;
  font-weight: 600;
}

.error-indicator {
  color: #ff6b6b;
  font-size: 18px;
}

.stock-name {
  color: #b0b0b0;
  font-size: 14px;
  margin-bottom: 16px;
  line-height: 1.4;
}

.stock-category {
  font-size: 12px;
  color: #FFD700;
  background: rgba(255, 215, 0, 0.1);
  padding: 2px 8px;
  border-radius: 12px;
  display: inline-block;
  margin: 4px 0;
  font-weight: 500;
}

.stock-stats {
  display: flex;
  flex-direction: column;
  gap: 8px;
  margin-bottom: 12px;
}

.stat-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.stat-label {
  color: #999;
  font-size: 13px;
}

.stat-value {
  font-weight: 500;
  font-size: 13px;
}

.stat-value.positive {
  color: #FFD700;
}

.stat-value.negative {
  color: #ff6b6b;
}

.loading-quote {
  padding: 20px;
  text-align: center;
  color: #666;
}

.loading-dots {
  animation: pulse 1.5s ease-in-out infinite;
}

@keyframes pulse {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.5; }
}

.options-label {
  text-align: center;
  padding: 8px;
  background: rgba(255, 215, 0, 0.1);
  border-radius: 8px;
  color: #FFD700;
  font-size: 12px;
  font-weight: 500;
  margin-top: 12px;
}

/* Live Data Indicator */
.live-indicator {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  margin-top: 8px;
  font-size: 11px;
  color: #FFD700;
}

.live-dot {
  width: 6px;
  height: 6px;
  background: #FFD700;
  border-radius: 50%;
  animation: pulse 2s infinite;
}

@keyframes pulse {
  0% { opacity: 1; }
  50% { opacity: 0.5; }
  100% { opacity: 1; }
}

.live-text {
  font-weight: 500;
}

/* Error Handling */
.quote-error {
  padding: 12px 0;
  text-align: center;
}

.error-message {
  color: #ff6b6b;
  font-size: 12px;
  margin-bottom: 8px;
  line-height: 1.4;
}

.retry-button, .load-price-button {
  background: linear-gradient(145deg, rgba(255, 215, 0, 0.2), rgba(255, 215, 0, 0.1));
  border: 1px solid rgba(255, 215, 0, 0.4);
  color: #FFD700;
  padding: 6px 12px;
  border-radius: 16px;
  font-size: 11px;
  cursor: pointer;
  transition: all 0.3s ease;
  font-weight: 500;
}

.retry-button:hover, .load-price-button:hover {
  background: linear-gradient(145deg, rgba(255, 215, 0, 0.3), rgba(255, 215, 0, 0.2));
  border-color: #FFD700;
  transform: translateY(-1px);
}

.no-quote {
  padding: 12px 0;
  text-align: center;
}

.load-price-button {
  background: linear-gradient(145deg, rgba(64, 224, 255, 0.2), rgba(64, 224, 255, 0.1));
  border-color: rgba(64, 224, 255, 0.4);
  color: #40e0ff;
}

.load-price-button:hover {
  background: linear-gradient(145deg, rgba(64, 224, 255, 0.3), rgba(64, 224, 255, 0.2));
  border-color: #40e0ff;
}

/* Unsupported Symbol Styles */
.unsupported-symbol {
  padding: 12px 0;
  text-align: center;
}

.unsupported-label {
  color: #ff9500;
  font-size: 12px;
  font-weight: 600;
  margin-bottom: 4px;
}

.unsupported-message {
  color: #b0b0b0;
  font-size: 11px;
  margin-bottom: 8px;
  line-height: 1.4;
}

.try-anyway-button {
  background: linear-gradient(145deg, rgba(255, 149, 0, 0.2), rgba(255, 149, 0, 0.1));
  border: 1px solid rgba(255, 149, 0, 0.4);
  color: #ff9500;
  padding: 4px 8px;
  border-radius: 12px;
  font-size: 10px;
  cursor: pointer;
  transition: all 0.3s ease;
  font-weight: 500;
}

.try-anyway-button:hover {
  background: linear-gradient(145deg, rgba(255, 149, 0, 0.3), rgba(255, 149, 0, 0.2));
  border-color: #ff9500;
  transform: translateY(-1px);
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

.options-modal {
  background: linear-gradient(145deg, rgba(16, 16, 34, 0.95), rgba(13, 13, 26, 0.98));
  border: 1px solid rgba(255, 215, 0, 0.2);
  border-radius: 20px;
  width: 100%;
  max-width: 800px;
  max-height: 80vh;
  overflow: hidden;
  backdrop-filter: blur(20px);
  position: relative;
}

.modal-header {
  padding: 24px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.modal-title {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.stock-symbol-large {
  font-size: 24px;
  font-weight: 700;
  color: #FFD700;
  text-shadow: 0 0 15px rgba(255, 215, 0, 0.3);
}

.modal-subtitle {
  color: #b0b0b0;
  font-size: 16px;
}

.close-modal-btn {
  background: rgba(255, 255, 255, 0.1);
  border: none;
  color: #999;
  font-size: 24px;
  cursor: pointer;
  padding: 8px;
  border-radius: 50%;
  width: 40px;
  height: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.3s ease;
}

.close-modal-btn:hover {
  background: rgba(255, 255, 255, 0.2);
  color: white;
}

.modal-content {
  padding: 24px;
  max-height: 500px;
  overflow-y: auto;
}

.loading-options {
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 40px;
}

.options-error {
  text-align: center;
  padding: 40px;
}

.error-icon {
  font-size: 48px;
  margin-bottom: 16px;
}

.options-error h3 {
  color: #ff6b6b;
  margin-bottom: 12px;
  font-size: 20px;
}

.options-error p {
  color: #b0b0b0;
  margin-bottom: 16px;
  line-height: 1.5;
}

.error-note {
  background: rgba(255, 107, 107, 0.1);
  border: 1px solid rgba(255, 107, 107, 0.2);
  border-radius: 12px;
  padding: 16px;
  color: #ffcccb;
  font-size: 14px;
  line-height: 1.5;
}

.options-tabs {
  display: flex;
  gap: 8px;
  margin-bottom: 20px;
}

.options-tab {
  flex: 1;
  padding: 12px 16px;
  background: rgba(0, 0, 0, 0.3);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 8px;
  color: #b0b0b0;
  cursor: pointer;
  transition: all 0.3s ease;
}

.options-tab.active {
  background: rgba(255, 215, 0, 0.1);
  border-color: rgba(255, 215, 0, 0.3);
  color: #FFD700;
}

.options-table {
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 12px;
  overflow: hidden;
}

.options-table-header {
  display: grid;
  grid-template-columns: repeat(5, 1fr);
  background: rgba(0, 0, 0, 0.5);
  padding: 16px 0;
}

.header-cell {
  padding: 0 16px;
  font-weight: 600;
  color: #FFD700;
  text-align: center;
  font-size: 14px;
}

.options-table-body {
  max-height: 300px;
  overflow-y: auto;
}

.options-row {
  display: grid;
  grid-template-columns: repeat(5, 1fr);
  border-bottom: 1px solid rgba(255, 255, 255, 0.05);
  padding: 12px 0;
  transition: background 0.2s ease;
}

.options-row:hover {
  background: rgba(255, 215, 0, 0.05);
}

.table-cell {
  padding: 0 16px;
  text-align: center;
  font-size: 14px;
}

/* Error Toast */
.error-toast {
  position: fixed;
  top: 20px;
  right: 20px;
  background: rgba(255, 107, 107, 0.1);
  border: 1px solid rgba(255, 107, 107, 0.3);
  border-radius: 12px;
  padding: 16px 20px;
  color: #ffcccb;
  display: flex;
  align-items: center;
  gap: 12px;
  z-index: 2000;
  backdrop-filter: blur(20px);
}

.error-icon {
  font-size: 20px;
}

.error-message {
  font-size: 14px;
  flex: 1;
}

.close-error-btn {
  background: none;
  border: none;
  color: #ffcccb;
  font-size: 16px;
  cursor: pointer;
  padding: 4px;
}

/* Responsive Design */
@media (max-width: 768px) {
  .alphavantage-screen {
    padding: 16px;
    padding-bottom: 120px;
  }
  
  .header-content {
    flex-direction: column;
    text-align: center;
    gap: 16px;
  }
  
  .back-button {
    align-self: flex-start;
  }
  
  .stock-grid {
    grid-template-columns: 1fr;
    gap: 16px;
  }
  
  .tab-navigation {
    flex-direction: column;
    gap: 8px;
  }
  
  .options-modal {
    max-width: 100%;
    margin: 10px;
    max-height: 90vh;
  }
  
  .options-table-header,
  .options-row {
    grid-template-columns: repeat(3, 1fr);
  }
  
  .header-cell:nth-child(4),
  .header-cell:nth-child(5),
  .table-cell:nth-child(4),
  .table-cell:nth-child(5) {
    display: none;
  }
}

@media (max-width: 480px) {
  .alphavantage-screen {
    padding: 12px;
  }
  
  .modal-header {
    padding: 16px;
  }
  
  .modal-content {
    padding: 16px;
  }
  
  .stock-symbol-large {
    font-size: 20px;
  }
  
  .options-table-header,
  .options-row {
    grid-template-columns: repeat(2, 1fr);
  }
  
  .header-cell:nth-child(3),
  .header-cell:nth-child(4),
  .header-cell:nth-child(5),
  .table-cell:nth-child(3),
  .table-cell:nth-child(4),
  .table-cell:nth-child(5) {
    display: none;
  }
}
</style>
