<template>
  <div class="stock_details w-full bg-transparent rounded-xl p-6">
    <h2 class="text-2xl font-semibold mb-4 text-white">{{ stock.company_name }}</h2>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <!-- Left Side Info -->
      <div class="space-y-2">
        <p class="text-lg text-white">
          Price: <span class="font-bold text-green-400">₹{{ stock.price }}</span>
          <span :class="stock.percent_change > 0 ? 'text-green-400' : 'text-red-400'">
            ({{ stock.net_change }} | {{ stock.percent_change }}%)
          </span>
        </p>
        <p class="text-gray-300">Open: <span class="font-medium text-white">₹{{ stock.open }}</span></p>
        <p class="text-gray-300">Close: <span class="font-medium text-white">₹{{ stock.close }}</span></p>
        <p class="text-gray-300">High / Low: 
          <span class="font-medium text-white">₹{{ stock.high }} / ₹{{ stock.low }}</span>
        </p>
        <p class="text-gray-300">Volume: <span class="font-medium text-white">{{ stock.volume }}</span></p>
      </div>

      <!-- Right Side Info -->
      <div class="space-y-2">
        <p class="text-gray-300">52 Week High: <span class="font-medium text-white">₹{{ stock.year_high }}</span></p>
        <p class="text-gray-300">52 Week Low: <span class="font-medium text-white">₹{{ stock.year_low }}</span></p>
        <p class="text-gray-300">Overall Rating: <span class="font-medium text-green-400">{{ stock.overall_rating }}</span></p>
        <p class="text-gray-300">Short Term Trend: <span class="font-medium text-blue-400">{{ stock.short_term_trends }}</span></p>
        <p class="text-gray-300">Long Term Trend: <span class="font-medium text-purple-400">{{ stock.long_term_trends }}</span></p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import axios from "axios";

const stock = ref({});

// Fetch Single Stock Details
const fetchStockData = async () => {
  try {
    const res = await axios.get("https://stock.indianapi.in/stock?name=Tata+Steel", {
      headers: {
        "x-api-key": "sk-live-dO2pc2cIt3N2ZVUMJEnGqiIP1NXu1be88iWGXEVX"
      }
    });
    stock.value = res.data; // API response assign
  } catch (error) {
    console.error("Stock API error:", error.response?.data || error.message);
  }
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
