<template>
  <div class="w-full p-4">
    <h3 class="text-2xl font-semibold mb-4">52-Week High / Low Stocks</h3>
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <!-- BSE Section -->
      <div class="bg-white p-4 rounded-lg shadow">
        <h4 class="text-xl font-semibold mb-3">BSE</h4>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div v-for="stock in bseData.high52Week" :key="stock.ticker" class="border-b pb-2">
            <p class="font-medium">{{ stock.company }} (High)</p>
            <p>Price: ₹{{ stock.price }}, 52-W High: ₹{{ stock["52_week_high"] }}</p>
          </div>
          <div v-for="stock in bseData.low52Week" :key="stock.ticker" class="border-b pb-2">
            <p class="font-medium">{{ stock.company }} (Low)</p>
            <p>Price: ₹{{ stock.price }}, 52-W Low: ₹{{ stock["52_week_low"] }}</p>
          </div>
        </div>
      </div>

      <!-- NSE Section -->
      <div class="bg-white p-4 rounded-lg shadow">
        <h4 class="text-xl font-semibold mb-3">NSE</h4>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div v-for="stock in nseData.high52Week" :key="stock.ticker" class="border-b pb-2">
            <p class="font-medium">{{ stock.company }} (High)</p>
            <p>Price: ₹{{ stock.price }}, 52-W High: ₹{{ stock["52_week_high"] }}</p>
          </div>
          <div v-for="stock in nseData.low52Week" :key="stock.ticker" class="border-b pb-2">
            <p class="font-medium">{{ stock.company }} (Low)</p>
            <p>Price: ₹{{ stock.price }}, 52-W Low: ₹{{ stock["52_week_low"] }}</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const bseData = ref({ high52Week: [], low52Week: [] });
const nseData = ref({ high52Week: [], low52Week: [] });

const fetchHighLowData = async () => {
  try {
    const res = await axios.get('https://stock.indianapi.in/fetch_52_week_high_low_data', {
      headers: {
        'x-api-key': 'sk-live-dO2pc2cIt3N2ZVUMJEnGqiIP1NXu1be88iWGXEVX'
      }
    });
    bseData.value = res.data.BSE_52WeekHighLow || {};
    nseData.value = res.data.NSE_52WeekHighLow || {};
  } catch (err) {
    console.error('Error fetching 52-week high/low data:', err.response?.data || err.message);
  }
};

onMounted(fetchHighLowData);
</script>

<style>
/* Optional hover effect */
.w-full > div:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 10px rgba(0,0,0,0.1);
}
</style>
