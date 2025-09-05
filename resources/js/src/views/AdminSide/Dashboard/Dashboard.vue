<template>
  <div class="dashboard-screen">
    <!-- Floating Particles -->
    <div class="floating-particles">
      <div class="particle" v-for="i in 15" :key="i" :style="{ 
        left: Math.random() * 100 + '%', 
        animationDelay: Math.random() * 15 + 's',
        animationDuration: (Math.random() * 8 + 8) + 's'
      }"></div>
    </div>

    <!-- Dashboard Title -->
    <div class="dashboard-header">
      <h1 class="dashboard-title">Dashboard</h1>
      <p class="dashboard-subtitle">Welcome back 👋</p>
    </div>

    <!-- Cards -->
    <div class="dashboard-cards">
      <div class="dashboard-card" @click="navigateTo('/admin/register-request')">
        <div class="card-content">
          <div class="card-icon">📝</div>
          <h3 class="card-title">Register Request</h3>
          <p class="card-description">
            Approve new user registrations
          </p>
        </div>
      </div>

      <div class="dashboard-card" @click="navigateTo('/admin/money-request')">
        <div class="card-content">
          <div class="card-icon">💰</div>
          <h3 class="card-title">Wallet Request</h3>
          <p class="card-description">
            Manage and approve wallet transactions
          </p>
        </div>
      </div>

      <div class="dashboard-card" @click="navigateTo('/admin/user-management')">
        <div class="card-content">
          <div class="card-icon">👥</div>
          <h3 class="card-title">User Management</h3>
          <p class="card-description">
            View and manage registered users
          </p>
        </div>
      </div>

      <div class="dashboard-card" @click="navigateTo('/admin/withdrawal-request')">
        <div class="card-content">
          <div class="card-icon">💳</div>
          <h3 class="card-title">Withdrawal Request</h3>
          <p class="card-description">
            Process and manage withdrawal requests
          </p>
        </div>
      </div>

      <div class="dashboard-card" @click="navigateTo('/admin/analytics')">
        <div class="card-content">
          <div class="card-icon">📊</div>
          <h3 class="card-title">Analytics & Reports</h3>
          <p class="card-description">
            View detailed analytics and generate reports
          </p>
        </div>
      </div>

      <div class="dashboard-card" @click="navigateTo('/admin/ai-trading')">
        <div class="card-content">
          <div class="card-icon">🤖</div>
          <h3 class="card-title">AI Trading</h3>
          <p class="card-description">
            View all users in AI trading cart
          </p>
        </div>
      </div>

      <div class="dashboard-card" @click="navigateTo('/admin/payment-collector')">
        <div class="card-content">
          <div class="card-icon">💸</div>
          <h3 class="card-title">Payment Collector</h3>
          <p class="card-description">
            Manage payment collections and transactions
          </p>
        </div>
      </div>

      <div class="dashboard-card" @click="navigateTo('/admin/stock-market')">
        <div class="card-content">
          <div class="card-icon">📈</div>
          <h3 class="card-title">Stock Market</h3>
          <p class="card-description">
            Live stock prices and market data via Upstox
          </p>
        </div>
      </div>

      <div class="dashboard-card" @click="navigateTo('/admin/fund-adjust')">
        <div class="card-content">
          <div class="card-icon">⚖️</div>
          <h3 class="card-title">Fund Adjust</h3>
          <p class="card-description">
            Adjust user wallet balances and fund management
          </p>
        </div>
      </div>
    </div>

    <!-- Mobile Navigation Component -->
    <AdminMobileNav />

  </div>
</template>

<script>
import AdminMobileNav from '../../../components/AdminMobileNav.vue';

export default {
  name: 'Dashboard',
  components: {
    AdminMobileNav
  },
  data() {
    return {
      isMobile: false
    }
  },
  mounted() {
    console.log('🚨 Dashboard component mounted successfully!');
    console.log('🚨 Current route:', this.$route.path);
    console.log('🚨 Component data:', this.$data);
    console.log('🚨 HOT RELOAD TEST - All 6 cards should be visible!');
    
    this.checkDeviceType();
    this.getUserInfo();
    window.addEventListener('resize', this.checkDeviceType);
    
    // Force a re-render to ensure cards are visible
    this.$nextTick(() => {
      console.log('🚨 Dashboard rendered, checking for cards...');
      const cards = document.querySelectorAll('.dashboard-card');
      console.log('🚨 Found cards:', cards.length);
    });
  },
  beforeUnmount() {
    window.removeEventListener('resize', this.checkDeviceType);
  },
  methods: {
    toggleSidebar() {
      console.log('Sidebar toggle clicked');
    },
    navigateTo(route) {
      if (route.startsWith('/')) {
        this.$router.push(route);
      } else {
        this.$router.push({ name: route });
      }
    },
    checkDeviceType() {
      this.isMobile = window.innerWidth <= 768;
    },
    handleLogout() {
      // Clear local storage
      localStorage.removeItem('userData');
      localStorage.removeItem('access_token');
      
      // Show logout message
      alert('Logout successful! Redirecting to login...');
      
      // Redirect to login
      this.$router.push({ name: 'login' });
    },
    getUserInfo() {
      const userData = localStorage.getItem('userData');
      if (userData) {
        try {
          const user = JSON.parse(userData);
          this.userName = user.name || 'Admin User';
        } catch (e) {
          this.userName = 'Admin User';
        }
      }
    }
  }
};
</script>

<style scoped>
/* Mobile-First Responsive Design - NO HORIZONTAL SCROLLING */
.dashboard-screen {
  background: linear-gradient(135deg, #0f0f23, #1a1a2e, #16213e);
  background-attachment: fixed;
  color: white;
  min-height: 100vh;
  padding: 20px;
  width: 100%;
  max-width: 100vw;
  overflow-x: hidden;
  box-sizing: border-box;
  position: relative;
}

.dashboard-screen::before {
  content: '';
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: 
    radial-gradient(circle at 20% 80%, rgba(0, 255, 128, 0.06) 0%, transparent 50%),
    radial-gradient(circle at 80% 20%, rgba(0, 255, 128, 0.04) 0%, transparent 50%),
    radial-gradient(circle at 40% 40%, rgba(0, 255, 128, 0.02) 0%, transparent 50%);
  pointer-events: none;
  z-index: 0;
}

/* Floating Particles */
.floating-particles {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  pointer-events: none;
  z-index: 1;
}

.particle {
  position: absolute;
  width: 3px;
  height: 3px;
  background: rgba(0, 255, 128, 0.5);
  border-radius: 50%;
  animation: float linear infinite;
  box-shadow: 0 0 8px rgba(0, 255, 128, 0.4);
}

@keyframes float {
  0% {
    transform: translateY(100vh) rotate(0deg);
    opacity: 0;
  }
  10% {
    opacity: 1;
  }
  90% {
    opacity: 1;
  }
  100% {
    transform: translateY(-100px) rotate(360deg);
    opacity: 0;
  }
}

.dashboard-header {
  margin-bottom: 32px;
  text-align: left;
  width: 100%;
  background: linear-gradient(145deg, rgba(0, 255, 128, 0.05), rgba(0, 255, 128, 0.02));
  border: 1px solid rgba(0, 255, 128, 0.1);
  border-radius: 20px;
  padding: 24px 32px;
  backdrop-filter: blur(10px);
  position: relative;
  z-index: 1;
  box-shadow: 0 8px 32px rgba(0, 255, 128, 0.1);
}

.dashboard-title {
  font-size: 28px;
  font-weight: 800;
  color: #00ff80;
  margin: 0 0 8px 0;
  line-height: 1.2;
  word-wrap: break-word;
  overflow-wrap: break-word;
  text-shadow: 0 0 20px rgba(0, 255, 128, 0.3);
  background: linear-gradient(135deg, #00ff80, #00cc66);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.dashboard-subtitle {
  font-size: 16px;
  color: #b0b0b0;
  margin: 0;
  line-height: 1.4;
  word-wrap: break-word;
  overflow-wrap: break-word;
  font-weight: 400;
}

.dashboard-cards {
  display: flex;
  flex-direction: column;
  gap: 20px;
  width: 100%;
  max-width: 100%;
  position: relative;
  z-index: 1;
}

.dashboard-card {
  background: linear-gradient(145deg, rgba(16, 16, 34, 0.8), rgba(13, 13, 26, 0.9));
  border: 1px solid rgba(0, 255, 128, 0.2);
  border-radius: 16px;
  padding: 24px 20px;
  text-decoration: none;
  color: white;
  width: 100%;
  min-height: 160px;
  transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
  box-sizing: border-box;
  overflow: hidden;
  cursor: pointer;
  user-select: none;
  backdrop-filter: blur(10px);
  position: relative;
}

.dashboard-card::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: linear-gradient(135deg, rgba(0, 255, 128, 0.05), transparent);
  opacity: 0;
  transition: opacity 0.3s ease;
  pointer-events: none;
}

.dashboard-card:hover::before {
  opacity: 1;
}

.dashboard-card:hover {
  background: linear-gradient(145deg, rgba(15, 23, 42, 0.9), rgba(11, 11, 22, 0.95));
  border-color: #00ff80;
  transform: translateY(-8px) scale(1.02);
  box-shadow: 0 20px 40px rgba(0, 255, 128, 0.3);
}

.dashboard-card::after {
  content: '';
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.1), transparent);
  transition: left 0.6s;
  pointer-events: none;
}

.dashboard-card:hover::after {
  left: 100%;
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
  position: relative;
  z-index: 2;
}

.card-icon {
  font-size: 36px;
  margin-bottom: 16px;
  display: block;
  line-height: 1;
  flex-shrink: 0;
  transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
  text-shadow: 0 0 15px rgba(0, 255, 128, 0.3);
}

.dashboard-card:hover .card-icon {
  transform: scale(1.1) rotate(5deg);
  text-shadow: 0 0 25px rgba(0, 255, 128, 0.5);
}

.card-title {
  font-size: 20px;
  font-weight: 700;
  margin: 0 0 12px 0;
  color: white;
  line-height: 1.3;
  word-wrap: break-word;
  overflow-wrap: break-word;
  hyphens: auto;
  max-width: 100%;
  text-shadow: 0 0 10px rgba(255, 255, 255, 0.1);
  transition: all 0.3s ease;
}

.dashboard-card:hover .card-title {
  color: #00ff80;
  text-shadow: 0 0 15px rgba(0, 255, 128, 0.4);
}

.card-description {
  font-size: 15px;
  color: #b0b0b0;
  line-height: 1.4;
  margin: 0;
  padding: 0 12px;
  word-wrap: break-word;
  overflow-wrap: break-word;
  hyphens: auto;
  max-width: 100%;
  text-align: center;
  transition: all 0.3s ease;
}

.dashboard-card:hover .card-description {
  color: #d0d0d0;
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

/* Mobile Bottom Navigation Styles */
.mobile-bottom-nav {
  position: fixed;
  bottom: 0;
  left: 0;
  right: 0;
  background: rgba(13, 13, 26, 0.95);
  backdrop-filter: blur(20px);
  border-top: 1px solid rgba(0, 255, 136, 0.2);
  padding: 12px 0;
  display: none;
  z-index: 1000;
}

.mobile-bottom-nav .nav-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 8px;
  cursor: pointer;
  padding: 12px 10px;
  border-radius: 12px;
  transition: all 0.2s ease;
  user-select: none;
  -webkit-tap-highlight-color: transparent;
  min-height: 70px;
  width: 100%;
  max-width: 100%;
  overflow: hidden;
}

.mobile-bottom-nav .nav-item:active {
  background: rgba(0, 255, 136, 0.1);
  transform: scale(0.95);
}

.mobile-bottom-nav .nav-icon {
  font-size: 24px;
  line-height: 1;
  margin-bottom: 2px;
  max-width: 100%;
}

.mobile-bottom-nav .nav-label {
  font-size: 12px;
  font-weight: 600;
  color: rgba(255, 255, 255, 0.9);
  text-align: center;
  line-height: 1.2;
  white-space: nowrap;
  max-width: 100%;
  padding: 0 4px;
  min-width: 60px;
}



/* Show mobile navigation on mobile devices */
@media (max-width: 768px) {
  .mobile-bottom-nav {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 10px;
    padding: 20px 10px 20px 16px;
    justify-items: center;
    align-items: center;
  }
  
  /* Add bottom padding to prevent content from being hidden behind nav */
  .dashboard-screen {
    padding-bottom: 120px;
  }
}

/* Mobile-specific optimizations */
@media (max-width: 480px) {
  .mobile-bottom-nav {
    padding: 16px 6px 16px 12px;
    gap: 6px;
  }
  
  .mobile-bottom-nav .nav-icon {
    font-size: 20px;
  }
  
  .mobile-bottom-nav .nav-label {
    font-size: 10px;
  }
  
  .mobile-nav-toggle {
    width: 52px;
    height: 52px;
    bottom: 16px;
    right: 16px;
  }
  
  .mobile-nav-toggle .toggle-icon span {
    width: 18px;
    height: 2px;
  }
  
  /* Ensure full mobile experience without header */
  .dashboard-screen {
    padding-top: 20px;
    padding-bottom: 120px;
  }
}

/* Landscape orientation adjustments */
@media (max-width: 768px) and (orientation: landscape) {
  .mobile-bottom-nav {
    padding: 16px 16px 16px 20px;
    gap: 14px;
  }
  
  .mobile-bottom-nav .nav-icon {
    font-size: 20px;
  }
  
  .mobile-bottom-nav .nav-label {
    font-size: 10px;
  }
  
  .dashboard-screen {
    padding-bottom: 80px;
  }
}

/* High DPI and touch device optimizations */
@media (-webkit-min-device-pixel-ratio: 2), (min-resolution: 192dpi) {
  .mobile-bottom-nav {
    border-top-width: 0.5px;
  }
}

@media (hover: none) and (pointer: coarse) {
  .mobile-bottom-nav .nav-item:hover {
    background: none;
  }
  
  .mobile-bottom-nav .nav-item:active {
    background: rgba(0, 255, 136, 0.15);
    transform: scale(0.9);
  }
  
  .mobile-nav-toggle:hover {
    transform: none;
  }
  
  .mobile-nav-toggle:active {
    transform: scale(0.9);
  }
}

/* Profile Menu Styles */
.mobile-profile-menu {
  position: fixed;
  bottom: 80px;
  left: 16px;
  right: 16px;
  background: rgba(13, 13, 26, 0.98);
  backdrop-filter: blur(20px);
  border: 1px solid rgba(0, 255, 136, 0.3);
  border-radius: 16px;
  z-index: 1002;
  max-height: 400px;
  overflow: hidden;
  animation: slideUp 0.3s ease;
}

.profile-menu-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.profile-info {
  display: flex;
  align-items: center;
  gap: 12px;
}

.profile-avatar {
  width: 48px;
  height: 48px;
  background: linear-gradient(135deg, #00ff88 0%, #00d4aa 100%);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 24px;
  color: #0d0d1a;
}

.profile-details {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.profile-name {
  font-weight: 600;
  color: white;
  font-size: 16px;
}

.profile-role {
  color: rgba(255, 255, 255, 0.7);
  font-size: 14px;
}

.close-profile-btn {
  background: none;
  border: none;
  color: rgba(255, 255, 255, 0.7);
  font-size: 24px;
  cursor: pointer;
  padding: 8px;
  border-radius: 50%;
  transition: all 0.2s ease;
}

.close-profile-btn:hover {
  background: rgba(255, 255, 255, 0.1);
  color: white;
}

.profile-menu-actions {
  padding: 16px 20px;
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.profile-action {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s ease;
}

.profile-action:hover {
  background: rgba(255, 255, 255, 0.05);
}

.profile-action:active {
  background: rgba(0, 255, 136, 0.1);
  transform: scale(0.98);
}

.action-icon {
  font-size: 20px;
  width: 24px;
  text-align: center;
}

.profile-action span {
  color: white;
  font-size: 16px;
  font-weight: 500;
}

.logout-action {
  border-top: 1px solid rgba(255, 255, 255, 0.1);
  margin-top: 8px;
  padding-top: 16px;
}

.logout-action .action-icon {
  color: #ff6b6b;
}

.logout-action span {
  color: #ff6b6b;
}

.profile-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  z-index: 1001;
  animation: fadeIn 0.3s ease;
}

@keyframes slideUp {
  from {
    transform: translateY(100%);
    opacity: 0;
  }
  to {
    transform: translateY(0);
    opacity: 1;
  }
}

@keyframes fadeIn {
  from {
    opacity: 0;
  }
  to {
    opacity: 1;
  }
}

/* Update mobile navigation to 7 columns */
@media (max-width: 768px) {
  .mobile-bottom-nav {
    grid-template-columns: repeat(7, 1fr);
  }
}

@media (max-width: 480px) {
  .mobile-profile-menu {
    left: 12px;
    right: 12px;
    bottom: 90px;
  }
  
  .profile-menu-header {
    padding: 16px;
  }
  
  .profile-avatar {
    width: 40px;
    height: 40px;
    font-size: 20px;
  }
  
  .profile-name {
    font-size: 14px;
  }
  
  .profile-role {
    font-size: 12px;
  }
  
  .profile-menu-actions {
    padding: 12px 16px;
  }
  
  .profile-action {
    padding: 10px;
  }
  
  .profile-action span {
    font-size: 14px;
  }
}

</style>
