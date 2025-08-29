<template>
  <div class="stock_details w-full bg-transparent rounded-xl p-6">
    <h2 class="text-2xl font-semibold mb-4 text-white">{{ stock.company_name || 'Tata Steel' }}</h2>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <!-- Stock Info Cards -->
      <div class="space-y-4">
        <!-- Price Card -->
        <div class="bg-gray-800 border border-gray-600 rounded-lg p-4">
          <div class="flex justify-between items-center">
            <div>
              <p class="text-gray-300 text-sm">Current Price</p>
              <p class="text-2xl font-bold text-green-400">₹{{ formatPrice(stock.price || 1234.56) }}</p>
            </div>
            <div class="text-right">
              <p class="text-gray-300 text-sm">Change</p>
              <p :class="(stock.percent_change || 2.45) > 0 ? 'text-green-400' : 'text-red-400'" class="text-lg font-semibold">
                {{ (stock.percent_change || 2.45) > 0 ? '+' : '' }}{{ stock.percent_change || 2.45 }}%
              </p>
            </div>
          </div>
        </div>

        <!-- OHLC Cards Grid -->
        <div class="grid grid-cols-2 gap-3">
          <div class="bg-gray-800 border border-gray-600 rounded-lg p-3 text-center">
            <p class="text-gray-300 text-xs">Open</p>
            <p class="text-white font-semibold">₹{{ formatPrice(stock.open || 1220.30) }}</p>
          </div>
          <div class="bg-gray-800 border border-gray-600 rounded-lg p-3 text-center">
            <p class="text-gray-300 text-xs">Close</p>
            <p class="text-white font-semibold">₹{{ formatPrice(stock.close || 1234.56) }}</p>
          </div>
          <div class="bg-gray-800 border border-gray-600 rounded-lg p-3 text-center">
            <p class="text-gray-300 text-xs">High</p>
            <p class="text-green-400 font-semibold">₹{{ formatPrice(stock.high || 1250.80) }}</p>
          </div>
          <div class="bg-gray-800 border border-gray-600 rounded-lg p-3 text-center">
            <p class="text-gray-300 text-xs">Low</p>
            <p class="text-red-400 font-semibold">₹{{ formatPrice(stock.low || 1210.20) }}</p>
          </div>
        </div>

        <!-- Volume & 52-Week Range -->
        <div class="space-y-3">
          <div class="bg-gray-800 border border-gray-600 rounded-lg p-4">
            <p class="text-gray-300 text-sm mb-2">Volume</p>
            <p class="text-white font-semibold text-lg">{{ formatVolume(stock.volume || 2456789) }}</p>
          </div>
          
          <div class="bg-gray-800 border border-gray-600 rounded-lg p-4">
            <p class="text-gray-300 text-sm mb-2">52-Week Range</p>
            <div class="flex justify-between">
              <span class="text-red-400 font-medium">₹{{ formatPrice(stock.year_low || 980.50) }}</span>
              <span class="text-gray-400">—</span>
              <span class="text-green-400 font-medium">₹{{ formatPrice(stock.year_high || 1450.75) }}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Price Chart -->
      <div class="bg-gray-800 border border-gray-600 rounded-lg p-4">
        <h3 class="text-white font-semibold mb-4">Price Trend (7 Days)</h3>
        <div class="chart-container" style="height: 300px;">
          <canvas ref="priceChart"></canvas>
        </div>
      </div>
    </div>

    <!-- Performance Indicators -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-6">
      <div class="bg-gray-800 border border-gray-600 rounded-lg p-4 text-center">
        <p class="text-gray-300 text-sm">Overall Rating</p>
        <div class="flex justify-center mt-2">
          <div class="flex space-x-1">
            <div v-for="i in 5" :key="i" 
                 :class="i <= (getRatingStars(stock.overall_rating || 'Buy')) ? 'bg-green-400' : 'bg-gray-600'"
                 class="w-3 h-3 rounded-full"></div>
          </div>
        </div>
        <p class="text-green-400 font-medium mt-1">{{ stock.overall_rating || 'Buy' }}</p>
      </div>
      
      <div class="bg-gray-800 border border-gray-600 rounded-lg p-4 text-center">
        <p class="text-gray-300 text-sm">Short Term</p>
        <p class="text-blue-400 font-medium text-lg mt-2">{{ stock.short_term_trends || 'Bullish' }}</p>
      </div>
      
      <div class="bg-gray-800 border border-gray-600 rounded-lg p-4 text-center">
        <p class="text-gray-300 text-sm">Long Term</p>
        <p class="text-purple-400 font-medium text-lg mt-2">{{ stock.long_term_trends || 'Positive' }}</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, nextTick } from "vue";
import axios from "axios";
import { Chart, registerables } from 'chart.js';

Chart.register(...registerables);

const stock = ref({});
const priceChart = ref(null);

// Utility functions
const formatPrice = (price) => {
  return parseFloat(price).toLocaleString('en-IN', { 
    minimumFractionDigits: 2, 
    maximumFractionDigits: 2 
  });
};

const formatVolume = (volume) => {
  if (volume >= 10000000) {
    return (volume / 10000000).toFixed(1) + 'Cr';
  } else if (volume >= 100000) {
    return (volume / 100000).toFixed(1) + 'L';
  } else if (volume >= 1000) {
    return (volume / 1000).toFixed(1) + 'K';
  }
  return volume.toString();
};

const getRatingStars = (rating) => {
  const ratings = {
    'Strong Buy': 5,
    'Buy': 4,
    'Hold': 3,
    'Sell': 2,
    'Strong Sell': 1
  };
  return ratings[rating] || 4;
};

// Create price trend chart
const createPriceChart = () => {
  if (!priceChart.value) return;

  const ctx = priceChart.value.getContext('2d');
  
  // Generate sample 7-day price data
  const basePrice = stock.value.price || 1234.56;
  const priceData = [];
  const labels = [];
  
  for (let i = 6; i >= 0; i--) {
    const date = new Date();
    date.setDate(date.getDate() - i);
    labels.push(date.toLocaleDateString('en-IN', { month: 'short', day: 'numeric' }));
    
    // Generate realistic price variations
    const variation = (Math.random() - 0.5) * 0.1; // ±5% variation
    const price = basePrice * (1 + variation);
    priceData.push(price.toFixed(2));
  }

  new Chart(ctx, {
    type: 'line',
    data: {
      labels: labels,
      datasets: [{
        label: 'Price (₹)',
        data: priceData,
        borderColor: '#00ff80',
        backgroundColor: 'rgba(0, 255, 128, 0.1)',
        borderWidth: 3,
        fill: true,
        tension: 0.4,
        pointBackgroundColor: '#00ff80',
        pointBorderColor: '#00ff80',
        pointRadius: 5,
        pointHoverRadius: 7
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          display: false
        }
      },
      scales: {
        x: {
          grid: {
            color: 'rgba(255, 255, 255, 0.1)'
          },
          ticks: {
            color: '#9ca3af'
          }
        },
        y: {
          grid: {
            color: 'rgba(255, 255, 255, 0.1)'
          },
          ticks: {
            color: '#9ca3af',
            callback: function(value) {
              return '₹' + value;
            }
          }
        }
      },
      elements: {
        point: {
          hoverBackgroundColor: '#00ff80'
        }
      }
    }
  });
};

// Fetch Single Stock Details
const fetchStockData = async () => {
  try {
    const res = await axios.get("https://stock.indianapi.in/stock?name=Tata+Steel", {
      headers: {
        "x-api-key": "sk-live-dO2pc2cIt3N2ZVUMJEnGqiIP1NXu1be88iWGXEVX"
      }
    });
    stock.value = res.data;
  } catch (error) {
    console.error("Stock API error:", error.response?.data || error.message);
    // Set fallback data
    stock.value = {
      company_name: 'Tata Steel',
      price: 1234.56,
      percent_change: 2.45,
      open: 1220.30,
      close: 1234.56,
      high: 1250.80,
      low: 1210.20,
      volume: 2456789,
      year_high: 1450.75,
      year_low: 980.50,
      overall_rating: 'Buy',
      short_term_trends: 'Bullish',
      long_term_trends: 'Positive'
    };
  }
  
  // Create chart after data is loaded
  await nextTick();
  createPriceChart();
};

onMounted(() => {
  fetchStockData();
});
</script>

<style>
.stock_details {
  transition: all 0.3s ease;
}
.stock_details:hover {
  transform: translateY(-4px);
  box-shadow: 0 6px 12px rgba(0,0,0,0.15);
}
</style>
