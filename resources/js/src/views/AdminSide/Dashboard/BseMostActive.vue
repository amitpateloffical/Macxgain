<template>
  <div class="most-active-section w-full bg-gray-50 p-4 rounded-lg mt-6">
    <h3 class="text-2xl font-semibold mb-4">BSE Most Active Stocks</h3>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div 
        v-for="(stock, index) in mostActiveStocks" 
        :key="index"
        class="stock-card bg-white p-4 rounded-lg shadow hover:shadow-lg transition"
      >
        <div class="flex justify-between items-center mb-2">
          <h4 class="text-lg font-semibold">{{ stock.company }}</h4>
          <span
            class="px-2 py-1 text-xs rounded"
            :class=" stock.percent_change >= 0
              ? 'bg-green-100 text-green-800'
              : 'bg-red-100 text-red-800'"
          >
            {{ stock.percent_change >= 0 ? '+' : '' }}{{ stock.percent_change }}%
          </span>
        </div>
        <p class="text-gray-700 mb-1">Price: ₹{{ stock.price }}</p>
        <p class="text-gray-700 mb-1">Change: {{ stock.net_change }}</p>
        <p class="text-gray-700">Volume: {{ stock.volume }}</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import axios from "axios";

const mostActiveStocks = ref([]);

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
  }
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
