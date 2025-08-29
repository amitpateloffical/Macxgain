<template>
  <div class="most-active-nse-section w-full bg-transparent p-4 rounded-lg">
    <h3 class="text-2xl font-semibold mb-4 text-white">NSE Most Active Stocks</h3>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div 
        v-for="(stock, index) in mostActiveStocks" 
        :key="index"
        class="stock-card bg-gray-800 border border-gray-600 p-4 rounded-lg shadow hover:shadow-lg hover:border-green-400 transition text-white"
      >
        <div class="flex justify-between items-center mb-2">
          <h4 class="text-lg font-semibold text-white">{{ stock.company }}</h4>
          <span
            class="px-2 py-1 text-xs rounded"
            :class="stock.percent_change >= 0
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
import { ref, onMounted } from "vue";
import axios from "axios";

const mostActiveStocks = ref([]);

const fetchMostActiveNSE = async () => {
  try {
    const res = await axios.get("https://stock.indianapi.in/NSE_most_active", {
      headers: {
        "x-api-key": "sk-live-dO2pc2cIt3N2ZVUMJEnGqiIP1NXu1be88iWGXEVX"
      }
    });
    mostActiveStocks.value = res.data || [];
  } catch (err) {
    console.error("Error fetching NSE most active stocks:", err.response?.data || err.message);
  }
};

onMounted(() => {
  fetchMostActiveNSE();
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
