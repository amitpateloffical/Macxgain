<template>
  <div class="dashboard-screen">
    <!-- Dashboard Title -->
    <div class="dashboard-header">
      <h1 class="dashboard-title">Dashboard</h1>
      <p class="dashboard-subtitle">Welcome back 👋</p>
    </div>

    <!-- Cards -->
    <div class="dashboard-cards">
      <router-link
        :to="{ name: 'register_request' }"
        class="dashboard-card"
        @click="toggleSidebar"
      >
        <div class="card-content">
          <div class="card-icon">📝</div>
          <h3 class="card-title">Register Request</h3>
          <p class="card-description">
            Approve new user registrations
          </p>
        </div>
      </router-link>

      <router-link
        :to="{ name: 'money_request' }"
        class="dashboard-card"
        @click="toggleSidebar"
      >
        <div class="card-content">
          <div class="card-icon">💰</div>
          <h3 class="card-title">Wallet Request</h3>
          <p class="card-description">
            Manage and approve wallet transactions
          </p>
        </div>
      </router-link>

      <router-link
        :to="{ name: 'user_management' }"
        class="dashboard-card"
        @click="toggleSidebar"
      >
        <div class="card-content">
          <div class="card-icon">👥</div>
          <h3 class="card-title">User Management</h3>
          <p class="card-description">
            View and manage registered users
          </p>
        </div>
      </router-link>

      <router-link
        :to="{ name: 'withdrawal_request' }"
        class="dashboard-card"
        @click="toggleSidebar"
      >
        <div class="card-content">
          <div class="card-icon">💳</div>
          <h3 class="card-title">Withdrawal Request</h3>
          <p class="card-description">
            Process and manage withdrawal requests
          </p>
        </div>
      </router-link>

      <router-link
        :to="{ name: 'analytics' }"
        class="dashboard-card"
        @click="toggleSidebar"
      >
        <div class="card-content">
          <div class="card-icon">📊</div>
          <h3 class="card-title">Analytics & Reports</h3>
          <p class="card-description">
            View detailed analytics and generate reports
          </p>
        </div>
      </router-link>
    </div>
  </div>
</template>

<script>
export default {
  name: 'Dashboard',
  mounted() {
    console.log('🚨 Dashboard component mounted successfully!');
    console.log('🚨 Current route:', this.$route.path);
    console.log('🚨 Component data:', this.$data);
    console.log('🚨 HOT RELOAD TEST - All 4 cards should be visible!');
  },
  methods: {
    toggleSidebar() {
      console.log('Sidebar toggle clicked');
    }
  }
};
</script>

<style scoped>
/* Mobile-First Responsive Design - NO HORIZONTAL SCROLLING */
.dashboard-screen {
  background-color: #0d0d1a;
  color: white;
  min-height: 100vh;
  padding: 16px;
  width: 100%;
  max-width: 100vw;
  overflow-x: hidden;
  box-sizing: border-box;
}

.dashboard-header {
  margin-bottom: 24px;
  text-align: left;
  width: 100%;
}

.dashboard-title {
  font-size: 24px;
  font-weight: bold;
  color: #00ff80;
  margin: 0 0 8px 0;
  line-height: 1.2;
  word-wrap: break-word;
  overflow-wrap: break-word;
}

.dashboard-subtitle {
  font-size: 16px;
  color: #9ca3af;
  margin: 0;
  line-height: 1.4;
  word-wrap: break-word;
  overflow-wrap: break-word;
}

.dashboard-cards {
  display: flex;
  flex-direction: column;
  gap: 16px;
  width: 100%;
  max-width: 100%;
}

.dashboard-card {
  background: linear-gradient(145deg, #101022, #0d0d1a);
  border: 1px solid rgba(0, 255, 128, 0.2);
  border-radius: 12px;
  padding: 20px 16px;
  text-decoration: none;
  color: white;
  width: 100%;
  min-height: 140px;
  transition: all 0.3s ease;
  box-shadow: 0 4px 10px rgba(0, 255, 128, 0.05);
  box-sizing: border-box;
  overflow: hidden;
}

.dashboard-card:hover {
  background: linear-gradient(145deg, #0f172a, #0b0b16);
  border-color: #00ff80;
  transform: translateY(-2px);
  box-shadow: 0 6px 16px rgba(0, 255, 128, 0.2);
}

.card-content {
  text-align: center;
  width: 100%;
  height: 100%;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  padding: 0;
}

.card-icon {
  font-size: 32px;
  margin-bottom: 12px;
  display: block;
  line-height: 1;
  flex-shrink: 0;
}

.card-title {
  font-size: 18px;
  font-weight: 600;
  margin: 0 0 8px 0;
  color: white;
  line-height: 1.3;
  word-wrap: break-word;
  overflow-wrap: break-word;
  hyphens: auto;
  max-width: 100%;
}

.card-description {
  font-size: 14px;
  color: #9ca3af;
  line-height: 1.4;
  margin: 0;
  padding: 0 8px;
  word-wrap: break-word;
  overflow-wrap: break-word;
  hyphens: auto;
  max-width: 100%;
  text-align: center;
}

/* Tablet Breakpoint (768px and up) */
@media (min-width: 768px) {
  .dashboard-screen {
    padding: 24px;
  }
  
  .dashboard-header {
    margin-bottom: 32px;
  }
  
  .dashboard-title {
    font-size: 28px;
  }
  
  .dashboard-subtitle {
    font-size: 18px;
  }
  
  .dashboard-cards {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 20px;
  }
  
  .dashboard-card {
    padding: 24px 20px;
    min-height: 160px;
  }
  
  .card-icon {
    font-size: 36px;
    margin-bottom: 16px;
  }
  
  .card-title {
    font-size: 20px;
    margin-bottom: 12px;
  }
  
  .card-description {
    font-size: 16px;
    padding: 0 12px;
  }
}

/* Desktop Breakpoint (1024px and up) */
@media (min-width: 1024px) {
  .dashboard-screen {
    padding: 32px;
    max-width: 1200px;
    margin: 0 auto;
  }
  
  .dashboard-header {
    margin-bottom: 40px;
  }
  
  .dashboard-title {
    font-size: 32px;
  }
  
  .dashboard-subtitle {
    font-size: 20px;
  }
  
  .dashboard-cards {
    grid-template-columns: repeat(2, 1fr);
    gap: 24px;
    max-width: 1000px;
    margin: 0 auto;
  }
  
  .dashboard-card {
    padding: 32px 24px;
    min-height: 180px;
  }
  
  .card-icon {
    font-size: 40px;
    margin-bottom: 20px;
  }
  
  .card-title {
    font-size: 22px;
    margin-bottom: 16px;
  }
  
  .card-description {
    font-size: 18px;
    padding: 0 16px;
  }
}

/* Large Desktop Breakpoint (1440px and up) */
@media (min-width: 1440px) {
  .dashboard-cards {
    grid-template-columns: repeat(4, 1fr);
    gap: 20px;
    max-width: 1400px;
  }
  
  .dashboard-card {
    padding: 24px 20px;
    min-height: 160px;
  }
  
  .card-icon {
    font-size: 36px;
    margin-bottom: 16px;
  }
  
  .card-title {
    font-size: 20px;
    margin-bottom: 12px;
  }
  
  .card-description {
    font-size: 16px;
    padding: 0 12px;
  }
}

/* Landscape Mobile (Orientation) */
@media (max-width: 768px) and (orientation: landscape) {
  .dashboard-screen {
    padding: 16px;
  }
  
  .dashboard-header {
    margin-bottom: 20px;
  }
  
  .dashboard-cards {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 12px;
  }
  
  .dashboard-card {
    padding: 16px 12px;
    min-height: 120px;
  }
  
  .card-icon {
    font-size: 28px;
    margin-bottom: 8px;
  }
  
  .card-title {
    font-size: 16px;
    margin-bottom: 6px;
  }
  
  .card-description {
    font-size: 12px;
    padding: 0 6px;
  }
}

/* Small Mobile (320px and below) */
@media (max-width: 320px) {
  .dashboard-screen {
    padding: 12px;
  }
  
  .dashboard-title {
    font-size: 20px;
  }
  
  .dashboard-subtitle {
    font-size: 14px;
  }
  
  .dashboard-card {
    padding: 16px 12px;
    min-height: 120px;
  }
  
  .card-icon {
    font-size: 24px;
    margin-bottom: 8px;
  }
  
  .card-title {
    font-size: 14px;
    margin-bottom: 6px;
  }
  
  .card-description {
    font-size: 12px;
    padding: 0 4px;
  }
}

/* Touch Device Optimizations */
@media (hover: none) and (pointer: coarse) {
  .dashboard-card {
    transition: none;
  }
  
  .dashboard-card:active {
    transform: scale(0.98);
    background: linear-gradient(145deg, #0f172a, #0b0b16);
  }
}

/* High DPI Displays */
@media (-webkit-min-device-pixel-ratio: 2), (min-resolution: 192dpi) {
  .dashboard-card {
    border-width: 0.5px;
  }
}

/* Dark Mode Support */
@media (prefers-color-scheme: dark) {
  .dashboard-screen {
    background-color: #0d0d1a;
  }
}

/* Reduced Motion Support */
@media (prefers-reduced-motion: reduce) {
  .dashboard-card {
    transition: none;
  }
  
  .dashboard-card:hover {
    transform: none;
  }
}

/* Print Styles */
@media print {
  .dashboard-screen {
    background-color: white;
    color: black;
    padding: 0;
  }
  
  .dashboard-cards {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1rem;
  }
  
  .dashboard-card {
    background: white;
    border: 1px solid #000;
    color: black;
    box-shadow: none;
  }
}
</style>
