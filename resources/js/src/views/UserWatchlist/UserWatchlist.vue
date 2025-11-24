<template>
  <div class="watchlist-container">
    <!-- Header Section -->
    <div class="watchlist-header">
      <div class="header-content">
        <div class="header-left">
          <div class="header-icon">
            <i class="fas fa-eye"></i>
          </div>
          <div class="header-text">
            <h1 class="page-title">My Watchlist</h1>
            <p class="page-subtitle">Track your favorite stocks and monitor market movements</p>
          </div>
        </div>

      </div>
    </div>

    <!-- Search and Filter Section -->
    <div class="search-section">
      <div class="search-container">
        <div class="search-input-wrapper">
          <i class="fas fa-search search-icon"></i>
          <input
            type="text"
            v-model="searchQuery"
            placeholder="Search and add stocks to your watchlist..."
            class="search-input"
            @input="handleStockSearch"
            @keypress.enter="addStockFromSearch"
          />
          <button v-if="searchQuery" @click="clearSearch" class="clear-search">
            <i class="fas fa-times"></i>
          </button>
        </div>
        
        <!-- Stock Search Results -->
        <div v-if="stockSearchResults.length > 0 && searchQuery" class="search-results-dropdown">
          <div class="results-header">
            <span>Search Results - Click to add to watchlist</span>
          </div>
          <div 
            v-for="stock in stockSearchResults" 
            :key="stock.symbol"
            class="search-result-item"
            @click="addStockToWatchlist(stock)"
          >
            <div class="result-info">
              <div class="result-symbol">{{ stock.symbol }}</div>
              <div class="result-name">{{ stock.name }}</div>
            </div>
            <div class="result-price">₹{{ stock.price.toFixed(2) }}</div>
            <div class="result-add-btn">
              <i class="fas fa-plus"></i>
            </div>
          </div>
        </div>
        <div class="filter-buttons">
          <button 
            :class="['filter-btn', { active: selectedFilter === 'all' }]"
            @click="setFilter('all')"
          >
            All
          </button>
          <button 
            :class="['filter-btn', { active: selectedFilter === 'gainers' }]"
            @click="setFilter('gainers')"
          >
            Gainers
          </button>
          <button 
            :class="['filter-btn', { active: selectedFilter === 'losers' }]"
            @click="setFilter('losers')"
          >
            Losers
          </button>
        </div>
      </div>
    </div>

    <!-- Watchlist Stats -->
    <div class="stats-section">
      <div class="stats-grid">
        <div class="stat-card">
          <div class="stat-icon">
            <i class="fas fa-list"></i>
          </div>
          <div class="stat-content">
            <div class="stat-value">{{ watchlistStocks.length }}</div>
            <div class="stat-label">Total Stocks</div>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon gainers">
            <i class="fas fa-arrow-up"></i>
          </div>
          <div class="stat-content">
            <div class="stat-value">{{ gainersCount }}</div>
            <div class="stat-label">Gainers</div>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon losers">
            <i class="fas fa-arrow-down"></i>
          </div>
          <div class="stat-content">
            <div class="stat-value">{{ losersCount }}</div>
            <div class="stat-label">Losers</div>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon">
            <i class="fas fa-chart-line"></i>
          </div>
          <div class="stat-content">
            <div class="stat-value">{{ averageChange.toFixed(2) }}%</div>
            <div class="stat-label">Avg Change</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Watchlist Content -->
    <div class="watchlist-content">
      <!-- Empty State -->
      <div v-if="filteredWatchlist.length === 0 && !loading" class="empty-state">
        <div class="empty-icon">
          <i class="fas fa-eye-slash"></i>
        </div>
        <h3 class="empty-title">
          {{ searchQuery ? 'No stocks found in watchlist' : 'Your watchlist is empty' }}
        </h3>
        <p class="empty-subtitle">
          {{ searchQuery ? 'Try adjusting your search criteria or search for new stocks to add' : 'Use the search bar above to find and add stocks to your watchlist' }}
        </p>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="loading-state">
        <div class="loading-spinner">
          <i class="fas fa-spinner fa-spin"></i>
        </div>
        <p>Loading your watchlist...</p>
      </div>

      <!-- Watchlist Grid -->
      <div v-if="filteredWatchlist.length > 0" class="watchlist-grid">
        <div 
          v-for="stock in filteredWatchlist" 
          :key="stock.symbol" 
          class="stock-card"
          :class="{ 'positive': stock.change > 0, 'negative': stock.change < 0 }"
        >
          <div class="stock-header">
            <div class="stock-info">
              <h3 class="stock-symbol">{{ stock.symbol }}</h3>
              <p class="stock-name">{{ stock.name }}</p>
            </div>
            <div class="stock-actions">
              <button class="action-btn" @click="viewStockDetails(stock)" title="View Details">
                <i class="fas fa-chart-area"></i>
              </button>
              <button class="action-btn remove" @click="removeFromWatchlist(stock)" title="Remove">
                <i class="fas fa-trash"></i>
              </button>
            </div>
          </div>
          
          <div class="stock-price">
            <div class="current-price">₹{{ stock.price.toFixed(2) }}</div>
            <div class="price-change" :class="{ 'positive': stock.change > 0, 'negative': stock.change < 0 }">
              <i :class="stock.change > 0 ? 'fas fa-arrow-up' : 'fas fa-arrow-down'"></i>
              {{ stock.change > 0 ? '+' : '' }}{{ stock.change.toFixed(2) }}%
            </div>
          </div>

          <div class="stock-details">
            <div class="detail-row">
              <span class="detail-label">High:</span>
              <span class="detail-value">₹{{ stock.high.toFixed(2) }}</span>
            </div>
            <div class="detail-row">
              <span class="detail-label">Low:</span>
              <span class="detail-value">₹{{ stock.low.toFixed(2) }}</span>
            </div>
            <div class="detail-row">
              <span class="detail-label">Volume:</span>
              <span class="detail-value">{{ formatVolume(stock.volume) }}</span>
            </div>
          </div>

          <div class="stock-chart">
            <canvas :ref="`chart-${stock.symbol}`" class="mini-chart"></canvas>
          </div>
        </div>
      </div>
    </div>



    <!-- Bottom App Bar (Global Component) -->
    <BottomAppBar />
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'UserWatchlist',
  data() {
    return {
      loading: false,
      searchQuery: '',
      selectedFilter: 'all',
      stockSearchResults: [],
      watchlistStocks: [],
      availableStocks: [
        // Available stocks for search
        { symbol: 'RELIANCE', name: 'Reliance Industries Ltd', price: 2456.75 },
        { symbol: 'TCS', name: 'Tata Consultancy Services', price: 3678.45 },
        { symbol: 'INFY', name: 'Infosys Limited', price: 1567.80 },
        { symbol: 'HDFC', name: 'HDFC Bank Limited', price: 1678.90 },
        { symbol: 'ADANIPORTS', name: 'Adani Ports & SEZ', price: 789.45 },
        { symbol: 'ASIANPAINT', name: 'Asian Paints Ltd', price: 3234.56 },
        { symbol: 'AXISBANK', name: 'Axis Bank Ltd', price: 987.65 },
        { symbol: 'BAJAJ-AUTO', name: 'Bajaj Auto Ltd', price: 4567.89 },
        { symbol: 'BHARTIARTL', name: 'Bharti Airtel Ltd', price: 876.54 },
        { symbol: 'CIPLA', name: 'Cipla Ltd', price: 1234.56 },
        { symbol: 'WIPRO', name: 'Wipro Limited', price: 456.78 },
        { symbol: 'MARUTI', name: 'Maruti Suzuki India Ltd', price: 9876.54 },
        { symbol: 'ICICIBANK', name: 'ICICI Bank Ltd', price: 987.65 },
        { symbol: 'SBIN', name: 'State Bank of India', price: 543.21 },
        { symbol: 'ITC', name: 'ITC Limited', price: 432.10 },
        { symbol: 'HDFCBANK', name: 'HDFC Bank Ltd', price: 1543.67 },
        { symbol: 'LT', name: 'Larsen & Toubro Ltd', price: 2345.89 },
        { symbol: 'ONGC', name: 'Oil & Natural Gas Corp', price: 234.56 },
        { symbol: 'NTPC', name: 'NTPC Limited', price: 178.90 },
        { symbol: 'POWERGRID', name: 'Power Grid Corp of India', price: 234.67 }
      ]
    };
  },
  computed: {
    filteredWatchlist() {
      let filtered = this.watchlistStocks;
      
      // Apply category filter only (search is now for adding stocks)
      if (this.selectedFilter === 'gainers') {
        filtered = filtered.filter(stock => stock.change > 0);
      } else if (this.selectedFilter === 'losers') {
        filtered = filtered.filter(stock => stock.change < 0);
      }
      
      return filtered;
    },
    gainersCount() {
      return this.watchlistStocks.filter(stock => stock.change > 0).length;
    },
    losersCount() {
      return this.watchlistStocks.filter(stock => stock.change < 0).length;
    },
    averageChange() {
      if (this.watchlistStocks.length === 0) return 0;
      const totalChange = this.watchlistStocks.reduce((sum, stock) => sum + stock.change, 0);
      return totalChange / this.watchlistStocks.length;
    }
  },
  mounted() {
    this.loadWatchlistFromStorage();
  },
  methods: {
    loadWatchlistFromStorage() {
      this.loading = true;
      
      // Load watchlist from localStorage
      const savedWatchlist = localStorage.getItem('userWatchlist');
      if (savedWatchlist) {
        try {
          this.watchlistStocks = JSON.parse(savedWatchlist);
        } catch (error) {
          console.error('Error loading watchlist from storage:', error);
          this.watchlistStocks = [];
        }
      }
      
      // Simulate loading delay
      setTimeout(() => {
        this.loading = false;
      }, 500);
    },
    
    saveWatchlistToStorage() {
      try {
        localStorage.setItem('userWatchlist', JSON.stringify(this.watchlistStocks));
      } catch (error) {
        console.error('Error saving watchlist to storage:', error);
      }
    },
    
    handleStockSearch() {
      if (this.searchQuery.length < 2) {
        this.stockSearchResults = [];
        return;
      }
      
      // Filter available stocks based on search query
      this.stockSearchResults = this.availableStocks.filter(stock => 
        stock.symbol.toLowerCase().includes(this.searchQuery.toLowerCase()) ||
        stock.name.toLowerCase().includes(this.searchQuery.toLowerCase())
      ).slice(0, 5); // Limit to 5 results
    },
    
    addStockFromSearch() {
      if (this.stockSearchResults.length > 0) {
        this.addStockToWatchlist(this.stockSearchResults[0]);
      }
    },
    
    clearSearch() {
      this.searchQuery = '';
      this.stockSearchResults = [];
    },
    
    setFilter(filter) {
      this.selectedFilter = filter;
    },
    
    addStockToWatchlist(stock) {
      // Check if stock already exists
      const exists = this.watchlistStocks.find(s => s.symbol === stock.symbol);
      if (exists) {
        this.$bvToast.toast('Stock is already in your watchlist', {
          title: 'Already Added',
          variant: 'warning',
          solid: true
        });
        this.clearSearch();
        return;
      }
      
      // Add stock to watchlist with random market data
      const newStock = {
        ...stock,
        change: (Math.random() * 6 - 3), // Random change between -3% to +3%
        high: stock.price * (1 + Math.random() * 0.05), // Random high up to 5% above
        low: stock.price * (1 - Math.random() * 0.05), // Random low up to 5% below
        volume: Math.floor(Math.random() * 5000000) + 100000 // Random volume
      };
      
      this.watchlistStocks.push(newStock);
      this.saveWatchlistToStorage();
      this.clearSearch();
      
      this.$bvToast.toast(`${stock.symbol} added to your watchlist`, {
        title: 'Stock Added',
        variant: 'success',
        solid: true
      });
    },
    
    removeFromWatchlist(stock) {
      this.$bvModal.msgBoxConfirm(`Remove ${stock.symbol} from your watchlist?`, {
        title: 'Confirm Removal',
        size: 'sm',
        buttonSize: 'sm',
        okVariant: 'danger',
        okTitle: 'Remove',
        cancelTitle: 'Cancel',
        footerClass: 'p-2',
        hideHeaderClose: false,
        centered: true
      }).then(value => {
        if (value) {
          this.watchlistStocks = this.watchlistStocks.filter(s => s.symbol !== stock.symbol);
          this.saveWatchlistToStorage();
          
          this.$bvToast.toast(`${stock.symbol} removed from watchlist`, {
            title: 'Stock Removed',
            variant: 'info',
            solid: true
          });
        }
      });
    },
    
    viewStockDetails(stock) {
      // Navigate to stock details page or show modal
      this.$bvToast.toast(`Viewing details for ${stock.symbol}`, {
        title: 'Stock Details',
        variant: 'info',
        solid: true
      });
    },
    
    formatVolume(volume) {
      if (volume >= 1000000) {
        return (volume / 1000000).toFixed(1) + 'M';
      } else if (volume >= 1000) {
        return (volume / 1000).toFixed(1) + 'K';
      }
      return volume.toString();
    }
  }
};
</script>

<style scoped>
.watchlist-container {
  min-height: 100vh;
  background: linear-gradient(135deg, #0f0f23 0%, #1a1a2e 50%, #16213e 100%);
  color: white;
  padding-bottom: 100px; /* Space for app bar */
}

/* Header Section */
.watchlist-header {
  padding: 20px;
  border-bottom: 1px solid rgba(255, 215, 0, 0.2);
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
  background: linear-gradient(135deg, #FFD700 0%, #FFE55C 100%);
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
  background: linear-gradient(135deg, #FFD700 0%, #FFE55C 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.page-subtitle {
  color: #a1a1a1;
  margin: 4px 0 0 0;
  font-size: 1rem;
}

.btn-add-stock {
  background: linear-gradient(135deg, #FFD700 0%, #FFE55C 100%);
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

.btn-add-stock:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(255, 215, 0, 0.3);
}

/* Search Section */
.search-section {
  padding: 20px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.search-container {
  max-width: 1200px;
  margin: 0 auto;
  display: flex;
  gap: 20px;
  align-items: center;
  flex-wrap: wrap;
}

.search-input-wrapper {
  position: relative;
  flex: 1;
  min-width: 300px;
}

.search-icon {
  position: absolute;
  left: 16px;
  top: 50%;
  transform: translateY(-50%);
  color: #a1a1a1;
}

.search-input {
  width: 100%;
  padding: 12px 16px 12px 48px;
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.2);
  border-radius: 12px;
  color: white;
  font-size: 1rem;
  transition: all 0.3s ease;
}

.search-input:focus {
  outline: none;
  border-color: #FFD700;
  box-shadow: 0 0 0 3px rgba(255, 215, 0, 0.1);
}

.clear-search {
  position: absolute;
  right: 12px;
  top: 50%;
  transform: translateY(-50%);
  background: none;
  border: none;
  color: #a1a1a1;
  cursor: pointer;
  padding: 4px;
  border-radius: 4px;
  transition: all 0.3s ease;
}

.clear-search:hover {
  color: white;
  background: rgba(255, 255, 255, 0.1);
}

/* Search Results Dropdown */
.search-results-dropdown {
  position: absolute;
  top: 100%;
  left: 0;
  right: 0;
  background: rgba(26, 26, 46, 0.95);
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 215, 0, 0.3);
  border-radius: 12px;
  margin-top: 8px;
  max-height: 300px;
  overflow-y: auto;
  z-index: 1000;
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
}

.search-results-dropdown .results-header {
  padding: 12px 16px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
  font-size: 0.875rem;
  color: #a1a1a1;
  font-weight: 500;
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
  background: rgba(255, 215, 0, 0.1);
}

.search-result-item .result-info {
  flex: 1;
}

.search-result-item .result-symbol {
  font-weight: 600;
  color: white;
  font-size: 0.875rem;
}

.search-result-item .result-name {
  font-size: 0.75rem;
  color: #a1a1a1;
  margin-top: 2px;
}

.search-result-item .result-price {
  font-weight: 600;
  color: #FFD700;
  margin-right: 12px;
  font-size: 0.875rem;
}

.search-result-item .result-add-btn {
  width: 24px;
  height: 24px;
  background: rgba(255, 215, 0, 0.2);
  border-radius: 6px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #FFD700;
  font-size: 0.75rem;
  transition: all 0.3s ease;
}

.search-result-item:hover .result-add-btn {
  background: rgba(255, 215, 0, 0.3);
  transform: scale(1.1);
}

.filter-buttons {
  display: flex;
  gap: 8px;
}

.filter-btn {
  padding: 8px 16px;
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.2);
  border-radius: 8px;
  color: white;
  cursor: pointer;
  transition: all 0.3s ease;
}

.filter-btn:hover,
.filter-btn.active {
  background: rgba(255, 215, 0, 0.2);
  border-color: #FFD700;
  color: #FFD700;
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
  background: rgba(255, 215, 0, 0.2);
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 20px;
  color: #FFD700;
}

.stat-icon.gainers {
  background: rgba(34, 197, 94, 0.2);
  color: #22c55e;
}

.stat-icon.losers {
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

/* Watchlist Content */
.watchlist-content {
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
  background: linear-gradient(135deg, #FFD700 0%, #FFE55C 100%);
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
  box-shadow: 0 8px 25px rgba(255, 215, 0, 0.3);
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

/* Watchlist Grid */
.watchlist-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
  gap: 20px;
}

.stock-card {
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 16px;
  padding: 20px;
  transition: all 0.3s ease;
  position: relative;
  overflow: hidden;
}

.stock-card:hover {
  background: rgba(255, 255, 255, 0.08);
  transform: translateY(-4px);
  box-shadow: 0 12px 40px rgba(0, 0, 0, 0.3);
}

.stock-card.positive {
  border-left: 4px solid #22c55e;
}

.stock-card.negative {
  border-left: 4px solid #ef4444;
}

.stock-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 16px;
}

.stock-symbol {
  font-size: 1.25rem;
  font-weight: 700;
  margin: 0;
  color: white;
}

.stock-name {
  color: #a1a1a1;
  font-size: 0.875rem;
  margin: 4px 0 0 0;
}

.stock-actions {
  display: flex;
  gap: 8px;
}

.action-btn {
  width: 32px;
  height: 32px;
  background: rgba(255, 255, 255, 0.1);
  border: none;
  border-radius: 8px;
  color: #a1a1a1;
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  justify-content: center;
}

.action-btn:hover {
  background: rgba(255, 215, 0, 0.2);
  color: #FFD700;
}

.action-btn.remove:hover {
  background: rgba(239, 68, 68, 0.2);
  color: #ef4444;
}

.stock-price {
  margin-bottom: 16px;
}

.current-price {
  font-size: 1.5rem;
  font-weight: 700;
  color: white;
}

.price-change {
  display: flex;
  align-items: center;
  gap: 4px;
  font-size: 0.875rem;
  font-weight: 600;
  margin-top: 4px;
}

.price-change.positive {
  color: #22c55e;
}

.price-change.negative {
  color: #ef4444;
}

.stock-details {
  margin-bottom: 16px;
}

.detail-row {
  display: flex;
  justify-content: space-between;
  margin-bottom: 4px;
}

.detail-label {
  color: #a1a1a1;
  font-size: 0.875rem;
}

.detail-value {
  color: white;
  font-size: 0.875rem;
  font-weight: 500;
}

.stock-chart {
  height: 60px;
  margin-top: 16px;
}

.mini-chart {
  width: 100%;
  height: 100%;
}



/* Responsive Design */
@media (max-width: 768px) {
  .header-content {
    flex-direction: column;
    gap: 16px;
    text-align: center;
  }
  
  .search-container {
    flex-direction: column;
    align-items: stretch;
  }
  
  .search-input-wrapper {
    min-width: auto;
  }
  
  .filter-buttons {
    justify-content: center;
  }
  
  .stats-grid {
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
  }
  
  .watchlist-grid {
    grid-template-columns: 1fr;
  }
  
  .popular-stocks-grid {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 480px) {
  .watchlist-container {
    padding-bottom: 80px;
  }
  
  .watchlist-header,
  .search-section,
  .stats-section,
  .watchlist-content {
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
}
</style>
