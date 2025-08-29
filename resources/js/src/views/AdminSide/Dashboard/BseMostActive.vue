<template>
  <div class="most-active-section w-full bg-transparent p-4 rounded-lg">
    <h3 class="text-2xl font-semibold mb-4 text-white">BSE Most Active Stocks</h3>
    
    <!-- Performance Chart -->
    <div class="bg-gray-800 border border-gray-600 rounded-lg p-4 mb-6">
      <h4 class="text-lg font-semibold text-white mb-4">Performance Overview</h4>
      <div class="chart-container" style="height: 300px;">
        <canvas ref="performanceChart"></canvas>
      </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
      <div 
        v-for="(stock, index) in mostActiveStocks" 
        :key="index"
        class="stock-card bg-gray-800 border border-gray-600 p-4 rounded-lg shadow hover:shadow-lg hover:border-green-400 transition text-white"
      >
        <div class="flex justify-between items-center mb-2">
          <h4 class="text-lg font-semibold text-white">{{ stock.company }}</h4>
          <span
            class="px-2 py-1 text-xs rounded"
            :class=" stock.percent_change >= 0
              ? 'bg-green-100 text-green-800'
              : 'bg-red-100 text-red-800'"
          >
            {{ stock.percent_change >= 0 ? '+' : '' }}{{ stock.percent_change }}%
          </span>
        </div>
        <p class="text-gray-300 mb-1">Price: <span class="text-white font-medium">₹{{ stock.price }}</span></p>
        <p class="text-gray-300 mb-1">Change: <span class="text-white font-medium">{{ stock.net_change }}</span></p>
        <p class="text-gray-300">Volume: <span class="text-white font-medium">{{ stock.volume }}</span></p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, nextTick } from "vue";
import axios from "axios";
import { Chart, registerables } from 'chart.js';

Chart.register(...registerables);

const mostActiveStocks = ref([]);
const performanceChart = ref(null);

const createPerformanceChart = () => {
  if (!performanceChart.value) return;

  const ctx = performanceChart.value.getContext('2d');
  
  // Generate sample data for top 10 stocks
  const stockNames = mostActiveStocks.value.slice(0, 10).map(stock => stock.company || `Stock ${Math.floor(Math.random() * 100)}`);
  const volumes = mostActiveStocks.value.slice(0, 10).map(stock => stock.volume || Math.floor(Math.random() * 1000000));
  const changes = mostActiveStocks.value.slice(0, 10).map(stock => stock.percent_change || (Math.random() - 0.5) * 10);

  new Chart(ctx, {
    type: 'bar',
    data: {
      labels: stockNames,
      datasets: [{
        label: 'Volume (in Lakhs)',
        data: volumes.map(v => v / 100000),
        backgroundColor: changes.map(change => change >= 0 ? 'rgba(0, 255, 128, 0.7)' : 'rgba(255, 59, 48, 0.7)'),
        borderColor: changes.map(change => change >= 0 ? '#00ff80' : '#ff3b30'),
        borderWidth: 2
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
            color: '#9ca3af',
            maxRotation: 45
          }
        },
        y: {
          grid: {
            color: 'rgba(255, 255, 255, 0.1)'
          },
          ticks: {
            color: '#9ca3af'
          }
        }
      }
    }
  });
};

const fetchMostActiveBSE = async () => {
  try {
    const res = await axios.get("https://stock.indianapi.in/BSE_most_active", {
      headers: {
        "x-api-key": "sk-live-dO2pc2cIt3N2ZVUMJEnGqiIP1NXu1be88iWGXEVX"
      }
    });
    mostActiveStocks.value = res.data || [];
  } catch (err) {
    console.error("Error fetching BSE most active stocks:", err.response?.data || err.message);
    // Fallback data
    mostActiveStocks.value = [
      { company: 'Reliance Industries', price: 2456.75, percent_change: 2.45, volume: 1234567, net_change: 58.90 },
      { company: 'TCS', price: 3890.20, percent_change: 1.78, volume: 987654, net_change: 68.20 },
      { company: 'HDFC Bank', price: 1534.60, percent_change: -1.23, volume: 2345678, net_change: -19.10 },
      { company: 'Infosys', price: 1678.90, percent_change: 3.45, volume: 1876543, net_change: 56.30 },
      { company: 'ICICI Bank', price: 987.30, percent_change: -0.89, volume: 3456789, net_change: -8.80 }
    ];
  }
  
  await nextTick();
  createPerformanceChart();
};

onMounted(() => {
  fetchMostActiveBSE();
});
</script>

<style>
.stock-card {
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.stock-card:hover {
  transform: translateY(-3px);
}
</style>
