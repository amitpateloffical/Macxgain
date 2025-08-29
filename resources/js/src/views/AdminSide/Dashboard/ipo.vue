<template>
  <div class="ipo_section w-full bg-transparent rounded-xl p-4">
    <h3 class="text-2xl font-semibold mb-4 text-white">IPO Updates</h3>

    <!-- Tabs -->
    <div class="flex flex-wrap border-b mb-4">
      <div
        v-for="tab in ipoTabs"
        :key="tab"
        class="px-4 py-2 cursor-pointer text-sm md:text-base"
        :class="activeTab === tab ? 'border-b-2 border-green-400 text-green-400 font-medium' : 'text-gray-300'"
        @click="activeTab = tab"
      >
        {{ tab }}
      </div>
    </div>

    <!-- IPO Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div
        v-for="(ipo, i) in ipoData[activeTab]"
        :key="i"
        class="border border-gray-600 bg-gray-800 rounded-lg p-4 hover:shadow-lg hover:border-green-400 transition text-white"
      >
        <!-- IPO Name -->
        <div class="flex justify-between items-center mb-2">
          <h4 class="text-lg font-semibold">{{ ipo.name }}</h4>
          <span
            class="px-2 py-1 rounded text-xs"
            :class="{
              'bg-green-100 text-green-700': ipo.status === 'active',
              'bg-yellow-100 text-yellow-700': ipo.status === 'upcoming',
              'bg-blue-100 text-blue-700': ipo.status === 'listed',
              'bg-red-100 text-red-700': ipo.status === 'closed'
            }"
          >
            {{ ipo.status }}
          </span>
        </div>

        <!-- Details -->
        <p class="text-sm text-gray-300 mb-1">Symbol: <span class="font-medium text-white">{{ ipo.symbol }}</span></p>
        <p class="text-sm text-gray-300 mb-1">Price Band: 
          <span class="font-medium text-white">
            {{ ipo.min_price }} - {{ ipo.max_price }}
          </span>
        </p>
        <p class="text-sm text-gray-300 mb-1">Lot Size: <span class="font-medium text-white">{{ ipo.lot_size }}</span></p>
        
        <p class="text-sm text-gray-300 mb-1">Start Date: <span class="font-medium text-green-400">{{ ipo.bidding_start_date }}</span></p>
        <p class="text-sm text-gray-300 mb-1">End Date: <span class="font-medium text-red-400">{{ ipo.bidding_end_date }}</span></p>

        <!-- Additional Info -->
        <p v-if="ipo.additional_text" class="text-xs mt-2 text-blue-400 italic">
          {{ ipo.additional_text }}
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import axios from "axios";

const ipoTabs = ["active", "upcoming", "listed", "closed"];
const activeTab = ref("active");

const ipoData = ref({
  active: [],
  upcoming: [],
  listed: [],
  closed: []
});

// Fetch IPO Data
const fetchIPOData = async () => {
  try {
    const res = await axios.get("https://stock.indianapi.in/ipo", {
      headers: {
        "x-api-key": "sk-live-dO2pc2cIt3N2ZVUMJEnGqiIP1NXu1be88iWGXEVX"
      }
    });
    ipoData.value = res.data; // API response directly assign
  } catch (error) {
    console.error("IPO API error:", error.response?.data || error.message);
  }
};

onMounted(() => {
  fetchIPOData();
});
</script>

<style>
.ipo_section {
  transition: all 0.3s ease;
}
</style>
