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

/* Responsive Design */
@media (max-width: 1200px) {
  .hero-content {
    flex-direction: column;
    text-align: center;
    gap: 32px;
  }
  
  .hero-visual {
    order: -1;
  }
  
  .quick-actions-grid {
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  }
  
  .analytics-grid {
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  }
}

@media (max-width: 768px) {
  .dashboard-screen {
    padding: 16px;
  }
  
  .hero-section {
    padding: 24px;
    margin-bottom: 24px;
  }
  
  .hero-content {
    flex-direction: column;
    text-align: center;
    gap: 24px;
  }
  
  .hero-title {
    font-size: 2rem;
  }
  
  .hero-subtitle {
    font-size: 1rem;
  }
  
  .hero-stats {
    justify-content: center;
    flex-wrap: wrap;
    gap: 16px;
  }
  
  .stat-item {
    padding: 16px;
    min-width: 100px;
  }
  
  .stat-number {
    font-size: 1.5rem;
  }
  
  .hero-visual {
    display: none;
  }
  
  .quick-actions-grid {
    grid-template-columns: 1fr;
    gap: 16px;
  }
  
  .action-card {
    padding: 20px;
  }
  
  .analytics-grid {
    grid-template-columns: 1fr;
    gap: 16px;
  }
  
  .analytics-card {
    padding: 20px;
  }
  
  .chart-container {
    height: 200px;
  }
  
  .status-grid {
    grid-template-columns: 1fr;
    gap: 16px;
  }
  
  .status-card {
    padding: 16px;
  }
}

@media (max-width: 480px) {
  .dashboard-screen {
    padding: 12px;
  }
  
  .hero-section {
    padding: 20px;
    border-radius: 16px;
  }
  
  .hero-title {
    font-size: 1.75rem;
  }
  
  .hero-subtitle {
    font-size: 0.9rem;
  }
  
  .current-time {
    font-size: 0.875rem;
    padding: 6px 12px;
  }
  
  .hero-stats {
    gap: 12px;
  }
  
  .stat-item {
    padding: 12px;
    min-width: 80px;
    border-radius: 12px;
  }
  
  .stat-number {
    font-size: 1.25rem;
    margin-bottom: 6px;
  }
  
  .stat-label {
    font-size: 0.75rem;
  }
  
  .section-title {
    font-size: 1.5rem;
    margin-bottom: 20px;
  }
  
  .quick-actions-grid {
    gap: 12px;
  }
  
  .action-card {
    padding: 16px;
    border-radius: 12px;
  }
  
  .action-icon {
    font-size: 2rem;
    margin-bottom: 12px;
  }
  
  .action-title {
    font-size: 1.125rem;
    margin-bottom: 6px;
  }
  
  .action-description {
    font-size: 0.8rem;
    margin-bottom: 12px;
  }
  
  .action-badge {
    font-size: 0.7rem;
    padding: 3px 8px;
  }
  
  .analytics-grid {
    gap: 12px;
  }
  
  .analytics-card {
    padding: 16px;
    border-radius: 12px;
  }
  
  .analytics-header {
    margin-bottom: 16px;
  }
  
  .analytics-title {
    font-size: 1rem;
  }
  
  .trend-indicator {
    font-size: 0.8rem;
    padding: 3px 8px;
  }
  
  .chart-container {
    height: 150px;
    margin-bottom: 12px;
  }
  
  .chart-label {
    font-size: 0.7rem;
  }
  
  .activity-list {
    border-radius: 12px;
  }
  
  .activity-item {
    padding: 16px;
  }
  
  .activity-icon {
    width: 32px;
    height: 32px;
    font-size: 1rem;
    margin-right: 12px;
  }
  
  .activity-title {
    font-size: 0.8rem;
    margin-bottom: 3px;
  }
  
  .activity-time {
    font-size: 0.7rem;
  }
  
  .activity-status {
    font-size: 0.7rem;
    padding: 3px 8px;
  }
  
  .status-grid {
    gap: 12px;
  }
  
  .status-card {
    padding: 16px;
    border-radius: 12px;
  }
  
  .status-info h4 {
    font-size: 0.9rem;
    margin-bottom: 3px;
  }
  
  .status-info p {
    font-size: 0.8rem;
  }
}

@media (max-width: 360px) {
  .dashboard-screen {
    padding: 8px;
  }
  
  .hero-section {
    padding: 16px;
  }
  
  .hero-title {
    font-size: 1.5rem;
  }
  
  .hero-subtitle {
    font-size: 0.8rem;
  }
  
  .stat-item {
    padding: 10px;
    min-width: 70px;
  }
  
  .stat-number {
    font-size: 1.125rem;
  }
  
  .stat-label {
    font-size: 0.7rem;
  }
  
  .section-title {
    font-size: 1.25rem;
    margin-bottom: 16px;
  }
  
  .action-card {
    padding: 12px;
  }
  
  .action-icon {
    font-size: 1.75rem;
  }
  
  .action-title {
    font-size: 1rem;
  }
  
  .action-description {
    font-size: 0.75rem;
  }
  
  .analytics-card {
    padding: 12px;
  }
  
  .chart-container {
    height: 120px;
  }
  
  .activity-item {
    padding: 12px;
  }
  
  .status-card {
    padding: 12px;
  }
}

/* Landscape Mobile */
@media (max-width: 768px) and (orientation: landscape) {
  .hero-section {
    padding: 20px;
  }
  
  .hero-title {
    font-size: 1.75rem;
  }
  
  .hero-stats {
    flex-direction: row;
    justify-content: center;
  }
  
  .quick-actions-grid {
    grid-template-columns: repeat(2, 1fr);
    gap: 12px;
  }
  
  .analytics-grid {
    grid-template-columns: repeat(2, 1fr);
    gap: 12px;
  }
}

/* Tablet Specific */
@media (min-width: 769px) and (max-width: 1024px) {
  .dashboard-screen {
    padding: 20px;
  }
  
  .hero-section {
    padding: 32px;
  }
  
  .hero-title {
    font-size: 2.5rem;
  }
  
  .hero-subtitle {
    font-size: 1.125rem;
  }
  
  .quick-actions-grid {
    grid-template-columns: repeat(2, 1fr);
    gap: 18px;
  }
  
  .analytics-grid {
    grid-template-columns: repeat(2, 1fr);
    gap: 18px;
  }
  
  .chart-container {
    height: 250px;
  }
}

/* High DPI Displays */
@media (-webkit-min-device-pixel-ratio: 2), (min-resolution: 192dpi) {
  .hero-section,
  .action-card,
  .analytics-card,
  .activity-list,
  .status-card {
    border-width: 0.5px;
  }
}

/* Touch Device Optimizations */
@media (hover: none) and (pointer: coarse) {
  .action-card:hover,
  .analytics-card:hover {
    transform: none;
  }
  
  .action-card:active,
  .analytics-card:active {
    transform: scale(0.98);
  }
  
  .action-arrow {
    transition: none;
  }
  
  .action-card:active .action-arrow {
    transform: translateX(4px);
  }
}

/* Print Styles */
@media print {
  .dashboard-screen {
    background: white;
    color: black;
    padding: 0;
  }
  
  .hero-section {
    background: white;
    border: 1px solid #000;
    color: black;
  }
  
  .hero-title {
    background: none;
    -webkit-text-fill-color: black;
    color: black;
  }
  
  .hero-visual,
  .floating-elements {
    display: none;
  }
  
  .quick-actions-grid,
  .analytics-grid {
    grid-template-columns: repeat(2, 1fr);
    gap: 1rem;
  }
  
  .action-card,
  .analytics-card {
    background: white;
    border: 1px solid #000;
    color: black;
    box-shadow: none;
  }
  
  .chart-container {
    height: auto;
    min-height: 150px;
    border: 1px solid #ccc;
  }
}


</style>
