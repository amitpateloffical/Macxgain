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

    <!-- Dashboard Header -->
    <div class="dashboard-header">
      <div class="header-content">
        <div>
          <h1 class="dashboard-title">Dashboard</h1>
          <p class="dashboard-subtitle">Welcome Back, {{ userName || 'Admin' }} 👋</p>
        </div>
        <div class="header-time">{{ currentTime }}</div>
      </div>
    </div>

    <!-- Quick Stats -->
    <div class="stats-grid">
      <div class="stat-card stat-primary">
        <div class="stat-icon-wrapper">
          <div class="stat-icon">👥</div>
        </div>
        <div class="stat-content">
          <div class="stat-value">0</div>
          <div class="stat-label">Total Users</div>
        </div>
        <div class="stat-decoration"></div>
      </div>
      
      <div class="stat-card stat-success">
        <div class="stat-icon-wrapper">
          <div class="stat-icon">💰</div>
        </div>
        <div class="stat-content">
          <div class="stat-value">₹0</div>
          <div class="stat-label">Total Revenue</div>
        </div>
        <div class="stat-decoration"></div>
      </div>
      
      <div class="stat-card stat-warning">
        <div class="stat-icon-wrapper">
          <div class="stat-icon">📊</div>
        </div>
        <div class="stat-content">
          <div class="stat-value">0</div>
          <div class="stat-label">Pending Requests</div>
        </div>
        <div class="stat-decoration"></div>
      </div>
      
      <div class="stat-card stat-info">
        <div class="stat-icon-wrapper">
          <div class="stat-icon">📈</div>
        </div>
        <div class="stat-content">
          <div class="stat-value">0</div>
          <div class="stat-label">Active Trades</div>
        </div>
        <div class="stat-decoration"></div>
      </div>
    </div>

    <!-- Categorized Sections -->
    <div class="sections-container">
      <!-- User Management Section -->
      <div class="section-group">
        <h2 class="section-title">
          <span class="section-icon">👥</span>
          User Management
        </h2>
        <div class="cards-grid">
          <div class="modern-card card-user" @click="navigateTo('/admin/register-request')">
            <div class="card-icon-container">
              <div class="card-icon-bg"></div>
              <div class="card-icon">📝</div>
            </div>
            <div class="card-info">
              <h3 class="card-title">Register Request</h3>
              <p class="card-description">Approve new user registrations</p>
            </div>
            <div class="card-arrow">→</div>
          </div>

          <div class="modern-card card-user" @click="navigateTo('/admin/user-management')">
            <div class="card-icon-container">
              <div class="card-icon-bg"></div>
              <div class="card-icon">👥</div>
            </div>
            <div class="card-info">
              <h3 class="card-title">User Management</h3>
              <p class="card-description">View and manage registered users</p>
            </div>
            <div class="card-arrow">→</div>
          </div>
        </div>
      </div>

      <!-- Financial Section -->
      <div class="section-group">
        <h2 class="section-title">
          <span class="section-icon">💳</span>
          Financial Operations
        </h2>
        <div class="cards-grid">
          <div class="modern-card card-financial" @click="navigateTo('/admin/money-request')">
            <div class="card-icon-container">
              <div class="card-icon-bg"></div>
              <div class="card-icon">💰</div>
            </div>
            <div class="card-info">
              <h3 class="card-title">Wallet Request</h3>
              <p class="card-description">Manage and approve wallet transactions</p>
            </div>
            <div class="card-arrow">→</div>
          </div>

          <div class="modern-card card-financial" @click="navigateTo('/admin/withdrawal-request')">
            <div class="card-icon-container">
              <div class="card-icon-bg"></div>
              <div class="card-icon">💳</div>
            </div>
            <div class="card-info">
              <h3 class="card-title">Withdrawal Request</h3>
              <p class="card-description">Process and manage withdrawal requests</p>
            </div>
            <div class="card-arrow">→</div>
          </div>

          <div class="modern-card card-financial" @click="navigateTo('/admin/payment-collector')">
            <div class="card-icon-container">
              <div class="card-icon-bg"></div>
              <div class="card-icon">💸</div>
            </div>
            <div class="card-info">
              <h3 class="card-title">Payment Collector</h3>
              <p class="card-description">Manage payment collections and transactions</p>
            </div>
            <div class="card-arrow">→</div>
          </div>

          <div class="modern-card card-financial" @click="navigateTo('/admin/fund-adjust')">
            <div class="card-icon-container">
              <div class="card-icon-bg"></div>
              <div class="card-icon">⚖️</div>
            </div>
            <div class="card-info">
              <h3 class="card-title">Fund Adjust</h3>
              <p class="card-description">Adjust user wallet balances and fund management</p>
            </div>
            <div class="card-arrow">→</div>
          </div>
        </div>
      </div>

      <!-- Trading & Analytics Section -->
      <div class="section-group">
        <h2 class="section-title">
          <span class="section-icon">📊</span>
          Trading & Analytics
        </h2>
        <div class="cards-grid">
          <div class="modern-card card-trading" @click="navigateTo('/admin/ai-trading')">
            <div class="card-icon-container">
              <div class="card-icon-bg"></div>
              <div class="card-icon">🤖</div>
            </div>
            <div class="card-info">
              <h3 class="card-title">AI Trading</h3>
              <p class="card-description">View all users in AI trading cart</p>
            </div>
            <div class="card-arrow">→</div>
          </div>

          <div class="modern-card card-trading" @click="navigateTo('/admin/stock-market')">
            <div class="card-icon-container">
              <div class="card-icon-bg"></div>
              <div class="card-icon">📈</div>
            </div>
            <div class="card-info">
              <h3 class="card-title">Stock Market</h3>
              <p class="card-description">Live stock prices and market data via Upstox</p>
            </div>
            <div class="card-arrow">→</div>
          </div>

          <div class="modern-card card-trading" @click="navigateTo('/admin/analytics')">
            <div class="card-icon-container">
              <div class="card-icon-bg"></div>
              <div class="card-icon">📊</div>
            </div>
            <div class="card-info">
              <h3 class="card-title">Analytics & Reports</h3>
              <p class="card-description">View detailed analytics and generate reports</p>
            </div>
            <div class="card-arrow">→</div>
          </div>
        </div>
      </div>

      <!-- System & Settings Section -->
      <div class="section-group">
        <h2 class="section-title">
          <span class="section-icon">⚙️</span>
          System & Settings
        </h2>
        <div class="cards-grid">
          <div class="modern-card card-settings" @click="navigateTo('/admin/brand-settings')">
            <div class="card-icon-container">
              <div class="card-icon-bg"></div>
              <div class="card-icon">🎨</div>
            </div>
            <div class="card-info">
              <h3 class="card-title">Brand Settings</h3>
              <p class="card-description">Customize logo and company name</p>
            </div>
            <div class="card-arrow">→</div>
          </div>

          <div class="modern-card card-settings" @click="navigateTo('/admin/template-settings')">
            <div class="card-icon-container">
              <div class="card-icon-bg"></div>
              <div class="card-icon">🎭</div>
            </div>
            <div class="card-info">
              <h3 class="card-title">Template Settings</h3>
              <p class="card-description">Choose color theme for entire website</p>
            </div>
            <div class="card-arrow">→</div>
          </div>

          <div class="modern-card card-settings" @click="navigateTo('/admin/landing-page-settings')">
            <div class="card-icon-container">
              <div class="card-icon-bg"></div>
              <div class="card-icon">🏠</div>
            </div>
            <div class="card-info">
              <h3 class="card-title">Landing Page Design</h3>
              <p class="card-description">Choose design style for landing page</p>
            </div>
            <div class="card-arrow">→</div>
          </div>

          <div class="modern-card card-settings" @click="navigateTo('/admin/backup')">
            <div class="card-icon-container">
              <div class="card-icon-bg"></div>
              <div class="card-icon">💾</div>
            </div>
            <div class="card-info">
              <h3 class="card-title">Backup</h3>
              <p class="card-description">Create and manage system backups and data recovery</p>
            </div>
            <div class="card-arrow">→</div>
          </div>
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
      isMobile: false,
      userName: 'Admin',
      currentTime: ''
    }
  },
  mounted() {
    this.checkDeviceType();
    this.getUserInfo();
    this.updateTime();
    setInterval(this.updateTime, 1000);
    window.addEventListener('resize', this.checkDeviceType);
  },
  beforeUnmount() {
    window.removeEventListener('resize', this.checkDeviceType);
  },
  methods: {
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
    getUserInfo() {
      const userData = localStorage.getItem('userData');
      if (userData) {
        try {
          const user = JSON.parse(userData);
          this.userName = user.name || 'Admin';
        } catch (e) {
          this.userName = 'Admin';
        }
      }
    },
    updateTime() {
      const now = new Date();
      this.currentTime = now.toLocaleTimeString('en-US', { 
        hour: '2-digit', 
        minute: '2-digit',
        hour12: true 
      });
    }
  }
};
</script>

<style scoped>
/* Dashboard Screen */
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
  padding-bottom: 100px;
}

.dashboard-screen::before {
  content: '';
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: 
    radial-gradient(circle at 20% 80%, rgba(255, 215, 0, 0.06) 0%, transparent 50%),
    radial-gradient(circle at 80% 20%, rgba(255, 215, 0, 0.04) 0%, transparent 50%),
    radial-gradient(circle at 40% 40%, rgba(255, 215, 0, 0.02) 0%, transparent 50%);
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
  background: rgba(255, 215, 0, 0.5);
  border-radius: 50%;
  animation: float linear infinite;
  box-shadow: 0 0 8px rgba(255, 215, 0, 0.4);
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

/* Dashboard Header */
.dashboard-header {
  margin-bottom: 30px;
  position: relative;
  z-index: 2;
}

.header-content {
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 20px;
}

.dashboard-title {
  font-size: 32px;
  font-weight: 800;
  margin: 0 0 8px 0;
  background: linear-gradient(135deg, #FFD700, #DAA520);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  text-shadow: 0 0 20px rgba(255, 215, 0, 0.3);
}

.dashboard-subtitle {
  font-size: 16px;
  color: #b0b0b0;
  margin: 0;
}

.header-time {
  font-size: 18px;
  font-weight: 600;
  color: #FFD700;
  padding: 10px 20px;
  background: rgba(255, 215, 0, 0.1);
  border: 1px solid rgba(255, 215, 0, 0.2);
  border-radius: 12px;
  backdrop-filter: blur(10px);
}

/* Stats Grid */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 20px;
  margin-bottom: 40px;
  position: relative;
  z-index: 2;
}

.stat-card {
  background: linear-gradient(145deg, rgba(16, 16, 34, 0.9), rgba(13, 13, 26, 0.95));
  border: 1px solid rgba(255, 215, 0, 0.2);
  border-radius: 20px;
  padding: 24px;
  position: relative;
  overflow: hidden;
  backdrop-filter: blur(10px);
  transition: all 0.3s ease;
  cursor: pointer;
}

.stat-card:hover {
  transform: translateY(-5px);
  border-color: rgba(255, 215, 0, 0.4);
  box-shadow: 0 15px 40px rgba(255, 215, 0, 0.2);
}

.stat-card::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 4px;
  background: linear-gradient(90deg, #FFD700, #DAA520);
  opacity: 0;
  transition: opacity 0.3s ease;
}

.stat-card:hover::before {
  opacity: 1;
}

.stat-icon-wrapper {
  width: 60px;
  height: 60px;
  border-radius: 16px;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 16px;
  position: relative;
  overflow: hidden;
}

.stat-primary .stat-icon-wrapper {
  background: linear-gradient(135deg, rgba(59, 130, 246, 0.2), rgba(37, 99, 235, 0.1));
  border: 1px solid rgba(59, 130, 246, 0.3);
}

.stat-success .stat-icon-wrapper {
  background: linear-gradient(135deg, rgba(255, 215, 0, 0.2), rgba(218, 165, 32, 0.1));
  border: 1px solid rgba(255, 215, 0, 0.3);
}

.stat-warning .stat-icon-wrapper {
  background: linear-gradient(135deg, rgba(251, 191, 36, 0.2), rgba(245, 158, 11, 0.1));
  border: 1px solid rgba(251, 191, 36, 0.3);
}

.stat-info .stat-icon-wrapper {
  background: linear-gradient(135deg, rgba(139, 92, 246, 0.2), rgba(124, 58, 237, 0.1));
  border: 1px solid rgba(139, 92, 246, 0.3);
}

.stat-icon {
  font-size: 28px;
  z-index: 1;
}

.stat-content {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.stat-value {
  font-size: 28px;
  font-weight: 800;
  color: white;
  line-height: 1;
}

.stat-label {
  font-size: 14px;
  color: #b0b0b0;
  font-weight: 500;
}

/* Section Groups */
.sections-container {
  position: relative;
  z-index: 2;
  display: flex;
  flex-direction: column;
  gap: 40px;
}

.section-group {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.section-title {
  font-size: 22px;
  font-weight: 700;
  color: white;
  display: flex;
  align-items: center;
  gap: 12px;
  margin: 0;
  padding-bottom: 12px;
  border-bottom: 2px solid rgba(255, 215, 0, 0.2);
}

.section-icon {
  font-size: 24px;
  width: 40px;
  height: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, rgba(255, 215, 0, 0.2), rgba(218, 165, 32, 0.1));
  border: 1px solid rgba(255, 215, 0, 0.3);
  border-radius: 12px;
}

/* Modern Cards Grid */
.cards-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 20px;
}

.modern-card {
  background: linear-gradient(145deg, rgba(16, 16, 34, 0.9), rgba(13, 13, 26, 0.95));
  border: 1px solid rgba(255, 215, 0, 0.15);
  border-radius: 20px;
  padding: 24px;
  display: flex;
  align-items: center;
  gap: 20px;
  cursor: pointer;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  position: relative;
  overflow: hidden;
  backdrop-filter: blur(10px);
}

.modern-card::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  width: 4px;
  height: 100%;
  background: linear-gradient(180deg, #FFD700, #DAA520);
  transform: scaleY(0);
  transition: transform 0.3s ease;
}

.modern-card:hover::before {
  transform: scaleY(1);
}

.modern-card:hover {
  transform: translateX(8px) translateY(-4px);
  border-color: rgba(255, 215, 0, 0.4);
  box-shadow: 0 12px 40px rgba(255, 215, 0, 0.2);
  background: linear-gradient(145deg, rgba(20, 20, 40, 0.95), rgba(15, 15, 30, 0.98));
}

.card-icon-container {
  position: relative;
  width: 64px;
  height: 64px;
  flex-shrink: 0;
  display: flex;
  align-items: center;
  justify-content: center;
}

.card-icon-bg {
  position: absolute;
  inset: 0;
  border-radius: 16px;
  background: linear-gradient(135deg, rgba(255, 215, 0, 0.15), rgba(218, 165, 32, 0.1));
  border: 1px solid rgba(255, 215, 0, 0.2);
  transition: all 0.3s ease;
}

.modern-card:hover .card-icon-bg {
  background: linear-gradient(135deg, rgba(255, 215, 0, 0.25), rgba(218, 165, 32, 0.15));
  border-color: rgba(255, 215, 0, 0.4);
  transform: scale(1.1) rotate(5deg);
}

.card-icon {
  font-size: 32px;
  position: relative;
  z-index: 1;
  transition: transform 0.3s ease;
}

.modern-card:hover .card-icon {
  transform: scale(1.15);
}

.card-info {
  flex: 1;
  min-width: 0;
}

.card-title {
  font-size: 18px;
  font-weight: 700;
  color: white;
  margin: 0 0 6px 0;
  transition: color 0.3s ease;
}

.modern-card:hover .card-title {
  color: #FFD700;
}

.card-description {
  font-size: 14px;
  color: #b0b0b0;
  margin: 0;
  line-height: 1.4;
  transition: color 0.3s ease;
}

.modern-card:hover .card-description {
  color: #d0d0d0;
}

.card-arrow {
  font-size: 24px;
  color: rgba(255, 215, 0, 0.4);
  transition: all 0.3s ease;
  flex-shrink: 0;
}

.modern-card:hover .card-arrow {
  color: #FFD700;
  transform: translateX(6px);
}

/* Responsive Design */
@media (min-width: 768px) {
  .dashboard-screen {
    padding: 32px;
  }
  
  .stats-grid {
    grid-template-columns: repeat(4, 1fr);
  }
  
  .cards-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (min-width: 1024px) {
  .dashboard-screen {
    padding: 40px;
    max-width: 1400px;
    margin: 0 auto;
  }
  
  .dashboard-title {
    font-size: 36px;
  }
  
  .stats-grid {
    gap: 24px;
  }
  
  .cards-grid {
    grid-template-columns: repeat(3, 1fr);
  }
}

@media (min-width: 1440px) {
  .cards-grid {
    grid-template-columns: repeat(4, 1fr);
  }
}

@media (max-width: 480px) {
  .dashboard-screen {
    padding: 16px;
  }
  
  .dashboard-title {
    font-size: 24px;
  }
  
  .header-content {
    flex-direction: column;
    align-items: flex-start;
  }
  
  .stats-grid {
    grid-template-columns: repeat(2, 1fr);
    gap: 12px;
  }
  
  .stat-card {
    padding: 16px;
  }
  
  .stat-icon-wrapper {
    width: 50px;
    height: 50px;
  }
  
  .stat-icon {
    font-size: 24px;
  }
  
  .stat-value {
    font-size: 22px;
  }
  
  .cards-grid {
    grid-template-columns: 1fr;
    gap: 16px;
  }
  
  .modern-card {
    padding: 20px;
  }
  
  .card-icon-container {
    width: 56px;
    height: 56px;
  }
  
  .card-icon {
    font-size: 28px;
  }
}

</style>
