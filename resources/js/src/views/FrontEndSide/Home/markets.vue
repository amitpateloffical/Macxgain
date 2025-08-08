<template>
  <div class="dashboard">
    <button class="back-button" @click="goBack">← Back</button>

    <h2>📊 Market Dashboard</h2>

    <div class="indices">
      <div v-for="(index, i) in indices" :key="i" class="card">
        <h3 class="index-name">{{ index.name }}</h3>
        <p>
          <span
            class="price"
            :class="{ up: index.change >= 0, down: index.change < 0 }"
          >
            ₹{{ index.price }}
          </span>
          <span :class="{ up: index.change >= 0, down: index.change < 0 }">
            ({{ index.change >= 0 ? '+' : '' }}{{ index.change.toFixed(2) }}%)
          </span>
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()
const goBack = () => {
  router.back()
}

// ⚠️ Alpha Vantage doesn't support BSE directly — using US stocks instead for demo
const indices = ref([
  { name: 'Apple', symbol: 'AAPL', price: 0, change: 0 },
  { name: 'Microsoft', symbol: 'MSFT', price: 0, change: 0 },
  { name: 'Google', symbol: 'GOOGL', price: 0, change: 0 },
  { name: 'Amazon', symbol: 'AMZN', price: 0, change: 0 },
  { name: 'Tesla', symbol: 'TSLA', price: 0, change: 0 }
])

const apiKey = 'demo' // 🔑 Replace with your actual Alpha Vantage API key

const fetchMarketData = async () => {
  for (let index of indices.value) {
    try {
      const url = `https://www.alphavantage.co/query?function=TIME_SERIES_INTRADAY&symbol=${index.symbol}&interval=5min&apikey=${apiKey}`
      const response = await fetch(url)
      const data = await response.json()

      const timeSeries = data['Time Series (5min)']
      if (timeSeries) {
        const latestTimestamp = Object.keys(timeSeries)[0]
        const latestData = timeSeries[latestTimestamp]

        const openPrice = parseFloat(latestData['1. open'])
        const closePrice = parseFloat(latestData['4. close'])
        const percentChange = ((closePrice - openPrice) / openPrice) * 100

        index.price = closePrice
        index.change = percentChange
      } else {
        console.warn(`No data for ${index.symbol}`, data)
      }
    } catch (err) {
      console.error(`Error fetching data for ${index.symbol}`, err)
    }
  }
}

let interval

onMounted(async () => {
  await fetchMarketData()
  interval = setInterval(fetchMarketData, 60000) // ⏱️ refresh every 60 seconds
})

onUnmounted(() => {
  clearInterval(interval)
})
</script>

<style scoped>
.dashboard {
  padding: 20px;
  font-family: 'Segoe UI', sans-serif;
  background-color: #121212;
  color: #f0f0f0;
  min-height: 100vh;
}

.back-button {
  background-color: #1f1f1f;
  color: #ffffff;
  border: 1px solid #333;
  padding: 10px 16px;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 500;
  transition: background-color 0.2s ease;
  margin-bottom: 20px;
}

.back-button:hover {
  background-color: #333333;
}

h2 {
  margin-bottom: 20px;
  font-size: 1.5rem;
}

.indices {
  display: flex;
  flex-wrap: wrap;
  gap: 16px;
  justify-content: flex-start;
}

.card {
  background-color: #1e1e1e;
  border-radius: 12px;
  padding: 16px;
  width: 100%;
  max-width: 200px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.4);
}

.card h3 {
  margin: 0 0 8px;
  font-size: 1.1rem;
}

.card p {
  font-size: 1rem;
  margin: 0 0 10px;
}

.price {
  font-size: 1.1rem;
  font-weight: bold;
  margin-right: 6px;
  transition: color 0.3s ease;
}

.up {
  color: #4caf50;
}

.down {
  color: #e53935;
}

.index-name {
  font-size: 1.1rem;
  font-weight: 600;
  margin-bottom: 8px;
  color: #ffffff;
}

@media (max-width: 768px) {
  .indices {
    justify-content: center;
  }

  .card {
    max-width: 90%;
  }
}

@media (max-width: 480px) {
  h2 {
    font-size: 1.2rem;
  }

  .back-button {
    padding: 8px 12px;
    font-size: 0.9rem;
  }
}
</style>
