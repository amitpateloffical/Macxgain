<template>
  <div class="stock_details w-full bg-white shadow-md rounded-xl p-6 mt-6">
    <h2 class="text-2xl font-semibold mb-4">{{ stock.company_name }}</h2>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <!-- Left Side Info -->
      <div class="space-y-2">
        <p class="text-lg">
          Price: <span class="font-bold">₹{{ stock.price }}</span>
          <span :class="stock.percent_change > 0 ? 'text-green-600' : 'text-red-600'">
            ({{ stock.net_change }} | {{ stock.percent_change }}%)
          </span>
        </p>
        <p>Open: <span class="font-medium">₹{{ stock.open }}</span></p>
        <p>Close: <span class="font-medium">₹{{ stock.close }}</span></p>
        <p>High / Low: 
          <span class="font-medium">₹{{ stock.high }} / ₹{{ stock.low }}</span>
        </p>
        <p>Volume: <span class="font-medium">{{ stock.volume }}</span></p>
      </div>

      <!-- Right Side Info -->
      <div class="space-y-2">
        <p>52 Week High: <span class="font-medium">₹{{ stock.year_high }}</span></p>
        <p>52 Week Low: <span class="font-medium">₹{{ stock.year_low }}</span></p>
        <p>Overall Rating: <span class="font-medium">{{ stock.overall_rating }}</span></p>
        <p>Short Term Trend: <span class="font-medium text-blue-600">{{ stock.short_term_trends }}</span></p>
        <p>Long Term Trend: <span class="font-medium text-purple-600">{{ stock.long_term_trends }}</span></p>
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
