<template>
  <div class="dashboard-screen">
    <!-- Dashboard Title -->
    <div class="flex justify-between items-center flex-wrap mb-8">
      <h1 class="text-3xl font-bold text-green-400">Dashboard</h1>
      <p class="text-sm text-gray-400">Welcome back 👋</p>
    </div>

    <!-- Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
      <router-link
        :to="{ name: 'money_request' }"
        class="dashboard-card"
        @click="toggleSidebar"
      >
        <div class="text-center">
          <div class="text-4xl mb-2">💰</div>
          <h3 class="text-xl font-semibold">Wallet Request</h3>
          <p class="text-gray-400 text-sm">
            Manage and approve wallet transactions
          </p>
        </div>
      </router-link>

      <router-link
        :to="{ name: 'money_request' }"
        class="dashboard-card"
        @click="toggleSidebar"
      >
        <div class="text-center">
          <div class="text-4xl mb-2">👥</div>
          <h3 class="text-xl font-semibold">User Management</h3>
          <p class="text-gray-400 text-sm">
            View and manage registered users
          </p>
        </div>
      </router-link>
    </div>
  </div>
</template>

<script>
export default {
  methods: {
    toggleSidebar() {
      // Sidebar toggle logic here
    }
  }
};
</script>

<style scoped>
.dashboard-screen {
  background-color: #0d0d1a;
  color: white;
  min-height: 100%; /* Full height */
  padding: 1.5rem;
  box-sizing: border-box;
}

.dashboard-card {
  background: linear-gradient(145deg, #101022, #0d0d1a);
  border: 1px solid rgba(0, 255, 128, 0.2);
  border-radius: 1rem;
  padding: 2rem 1rem;
  text-decoration: none;
  color: white;
  display: flex;
  justify-content: center;
  align-items: center;
  transition: all 0.3s ease-in-out;
  box-shadow: 0 4px 10px rgba(0, 255, 128, 0.05);
}

.dashboard-card:hover {
  background: linear-gradient(145deg, #0f172a, #0b0b16);
  border-color: #00ff80;
  transform: translateY(-4px) scale(1.02);
  box-shadow: 0 8px 20px rgba(0, 255, 128, 0.25);
}
</style>
