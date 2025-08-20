<template>
  <div>

 <div class="dashboard_screen p-4 grid grid-cols-1 md:grid-cols-2 gap-4">
    <!-- Top Gainers Card -->
    <div class="card bg-white shadow-md rounded-xl p-4">
      <h3 class="text-xl font-semibold mb-3 text-green-600">Top Gainers</h3>
      <div v-for="(stock, i) in allStocks.top_gainers" :key="i" class="border-b py-2">
        <div class="flex justify-between">
          <span class="font-medium">{{ stock.company_name }}</span>
          <span class="text-green-500">+{{ stock.percent_change }}%</span>
        </div>
        <div class="text-sm text-gray-600">
          ₹{{ stock.price }} ({{ stock.net_change }})
        </div>
      </div>
    </div>

    <!-- Top Losers Card -->
    <div class="card bg-white shadow-md rounded-xl p-4">
      <h3 class="text-xl font-semibold mb-3 text-red-600">Top Losers</h3>
      <div v-for="(stock, i) in allStocks.top_losers" :key="i" class="border-b py-2">
        <div class="flex justify-between">
          <span class="font-medium">{{ stock.company_name }}</span>
          <span class="text-red-500">{{ stock.percent_change }}%</span>
        </div>
        <div class="text-sm text-gray-600">
          ₹{{ stock.price }} ({{ stock.net_change }})
        </div>
      </div>
    </div>
  </div>
  

<div>
  <h3>IPO</h3>

  <ipo/>
</div>
<div>
  <h3>Stock</h3>
  
  <stockDetails/>
</div>
<div>
  <h3>BSE Stock</h3>

  <BseMostActive/>
</div>
<div>
  <h3>NSE Stock</h3>
  <NseMostActiveStock/>
</div>
<div>
  <yearWeekHighLow/>
</div>



  </div>


  
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import axios from "axios";
import ipo from "./ipo.vue";
import stockDetails from "./stockDetails.vue";
import BseMostActive from "./BseMostActive.vue";
import NseMostActiveStock from "./NseMostActiveStock.vue";
import yearWeekHighLow from "./52-WeekHighLow.vue";




const mostBoughtStocks = ref([]);
const tabs = ["Gainers", "Losers", "Volume shockers"];
const activeTab = ref("Gainers");

const allStocks = ref({
  Gainers: [],
  Losers: [],
  "Volume shockers": [],
});

const filteredStocks = computed(() => allStocks.value[activeTab.value]);

// Fetch IPO data (replace Most Bought Stocks)


// Example: Fetch market movers (dummy endpoint - replace when real one given)
const fetchMarketMovers = async () => {
  try {
    const res = await axios.get("https://stock.indianapi.in/trending", {
      headers: {
        "x-api-key": "sk-live-dO2pc2cIt3N2ZVUMJEnGqiIP1NXu1be88iWGXEVX",
      },
    });
    allStocks.value = res.data.trending_stocks || [];
    // allStocks.value["Volume shockers"] = res.data.volume || [];
  } catch (error) {
    console.error(
      "Market Movers API error:",
      error.response?.data || error.message
    );
  }
};

// Call APIs on page load
onMounted(() => {
  fetchMarketMovers();
});
</script>


<style scoped>



.card {
  transition: all 0.3s ease;
}
.card:hover {
  transform: translateY(-4px);
  box-shadow: 0 6px 12px rgba(0,0,0,0.15);
}
</style>
