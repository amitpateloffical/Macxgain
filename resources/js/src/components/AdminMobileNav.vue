<template>
  <!-- Mobile Bottom Navigation Bar -->
  <div class="mobile-bottom-nav">
    <div class="nav-item" @click="navigateTo('/admin/dashboard')">
      <div class="nav-icon">🏠</div>
      <span class="nav-label">Dashboard</span>
    </div>
    <div class="nav-item" @click="navigateTo('/admin/register-request')">
      <div class="nav-icon">📝</div>
      <span class="nav-label">Register</span>
    </div>
    <div class="nav-item" @click="navigateTo('/admin/money-request')">
      <div class="nav-icon">💰</div>
      <span class="nav-label">Wallet</span>
    </div>
    <div class="nav-item" @click="navigateTo('/admin/withdrawal-request')">
      <div class="nav-icon">💳</div>
      <span class="nav-label">Withdraw</span>
    </div>
    <div class="nav-item" @click="navigateTo('/admin/user-management')">
      <div class="nav-icon">👥</div>
      <span class="nav-label">Users</span>
    </div>
    <div class="nav-item" @click="navigateTo('/admin/ai-trading')">
      <div class="nav-icon">🤖</div>
      <span class="nav-label">AI Trading</span>
    </div>
    <div class="nav-item" @click="navigateTo('/admin/payment-collector')">
      <div class="nav-icon">💸</div>
      <span class="nav-label">Payments</span>
    </div>
    <div class="nav-item" @click="showProfileMenu = !showProfileMenu">
      <div class="nav-icon">👤</div>
      <span class="nav-label">Profile</span>
    </div>
  </div>

  <!-- Mobile Profile Menu -->
  <div v-if="showProfileMenu" class="mobile-profile-menu">
    <div class="profile-menu-header">
      <div class="profile-info">
        <div class="profile-avatar">👤</div>
        <div class="profile-details">
          <div class="profile-name">{{ userName || 'Admin User' }}</div>
          <div class="profile-role">Administrator</div>
        </div>
      </div>
      <button class="close-profile-btn" @click="showProfileMenu = false">×</button>
    </div>
    
    <div class="profile-menu-actions">
      <div class="profile-action logout-action" @click="handleLogout">
        <div class="action-icon">🚪</div>
        <span>Logout</span>
      </div>
    </div>
  </div>

  <!-- Profile Menu Overlay -->
  <div v-if="showProfileMenu" class="profile-overlay" @click="showProfileMenu = false"></div>
</template>

<script>
export default {
  name: 'AdminMobileNav',
  data() {
    return {
      showProfileMenu: false,
      userName: ''
    }
  },
  mounted() {
    // Get user name from localStorage
    const userData = localStorage.getItem('userData');
    if (userData) {
      try {
        const user = JSON.parse(userData);
        this.userName = user.name || user.email || 'Admin User';
      } catch (e) {
        console.error('Error parsing user data:', e);
      }
    }
  },
  methods: {
    navigateTo(route) {
      if (route.startsWith('/')) {
        this.$router.push(route);
      } else {
        this.$router.push({ name: route });
      }
      this.showProfileMenu = false;
    },
    handleLogout() {
      // Clear all stored data
      localStorage.removeItem('userData');
      localStorage.removeItem('access_token');
      localStorage.removeItem('token');
      localStorage.removeItem('auth_token');
      localStorage.removeItem('user');
      localStorage.removeItem('user_credentials');
      localStorage.removeItem('rememberMeStatus');
      
      // Clear session storage
      sessionStorage.clear();
      
      // Redirect to login
      this.$router.push({ name: 'login' });
    }
  }
}
</script>

<style scoped>
/* Mobile Bottom Navigation Styles */
.mobile-bottom-nav {
  position: fixed;
  bottom: 0;
  left: 0;
  right: 0;
  background: rgba(13, 13, 26, 0.95);
  backdrop-filter: blur(20px);
  border-top: 1px solid rgba(255, 215, 0, 0.2);
  padding: 12px 0;
  display: grid;
  grid-template-columns: repeat(8, 1fr);
  gap: 8px;
  padding: 20px 8px 20px 12px;
  justify-items: center;
  align-items: center;
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
  background: rgba(255, 215, 0, 0.1);
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

/* Responsive adjustments for different screen sizes */
@media (max-width: 768px) {
  .mobile-bottom-nav {
    gap: 8px;
    padding: 20px 8px 20px 12px;
  }
  
  /* Add bottom padding to prevent content from being hidden behind nav */
  body {
    padding-bottom: 120px;
  }
}

/* Desktop and larger screen adjustments */
@media (min-width: 769px) {
  .mobile-bottom-nav {
    gap: 12px;
    padding: 20px 16px 20px 20px;
    max-width: 1200px;
    left: 50%;
    transform: translateX(-50%);
    border-radius: 16px 16px 0 0;
    margin: 0 20px;
  }
  
  /* Add bottom padding for desktop */
  body {
    padding-bottom: 120px;
  }
}

/* Mobile-specific optimizations */
@media (max-width: 480px) {
  .mobile-bottom-nav {
    padding: 16px 4px 16px 8px;
    gap: 4px;
  }
  
  .mobile-bottom-nav .nav-icon {
    font-size: 18px;
  }
  
  .mobile-bottom-nav .nav-label {
    font-size: 9px;
  }
}

/* Landscape orientation adjustments */
@media (max-width: 768px) and (orientation: landscape) {
  .mobile-bottom-nav {
    padding: 16px 12px 16px 16px;
    gap: 12px;
  }
  
  .mobile-bottom-nav .nav-icon {
    font-size: 18px;
  }
  
  .mobile-bottom-nav .nav-label {
    font-size: 9px;
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
    background: rgba(255, 215, 0, 0.15);
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
  border: 1px solid rgba(255, 215, 0, 0.3);
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
  background: linear-gradient(135deg, #FFD700 0%, #FFE55C 100%);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 24px;
  color: white;
}

.profile-details {
  color: white;
}

.profile-name {
  font-weight: 600;
  font-size: 16px;
  margin-bottom: 4px;
}

.profile-role {
  font-size: 14px;
  color: rgba(255, 255, 255, 0.7);
}

.close-profile-btn {
  background: none;
  border: none;
  color: rgba(255, 255, 255, 0.7);
  font-size: 24px;
  cursor: pointer;
  padding: 8px;
  border-radius: 8px;
  transition: all 0.2s ease;
}

.close-profile-btn:hover {
  background: rgba(255, 255, 255, 0.1);
  color: white;
}

.profile-menu-actions {
  padding: 20px;
  text-align: center;
}

.profile-action {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 12px;
  padding: 16px 24px;
  cursor: pointer;
  border-radius: 12px;
  transition: all 0.2s ease;
  color: white;
  background: rgba(255, 107, 107, 0.1);
  border: 1px solid rgba(255, 107, 107, 0.3);
}

.profile-action:hover {
  background: rgba(255, 107, 107, 0.2);
  border-color: rgba(255, 107, 107, 0.5);
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(255, 107, 107, 0.3);
}

.action-icon {
  font-size: 20px;
  width: 24px;
  text-align: center;
}

.profile-action span {
  color: #ff6b6b;
  font-size: 16px;
  font-weight: 600;
}

.logout-action {
  border-top: none;
  margin-top: 0;
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
</style>
