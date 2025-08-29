<template>
  <div class="bottom-app-bar">
    <div class="app-bar-container">
      <div class="app-bar-item" @click="navigateToPage('/user/dashboard')" :class="{ active: isActive('/user/dashboard') }">
        <div class="app-bar-icon">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
            <path d="M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z"/>
          </svg>
        </div>
        <span class="app-bar-label">Dashboard</span>
      </div>

      <div class="app-bar-item" @click="navigateToPage('/markets')" :class="{ active: isActive('/markets') }">
        <div class="app-bar-icon">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
            <path d="M16 6l2.29 2.29-4.88 4.88-4-4L2 16.59 3.41 18l6-6 4 4 6.3-6.29L22 12V6z"/>
          </svg>
        </div>
        <span class="app-bar-label">Market</span>
      </div>

      <div class="app-bar-item" @click="navigateToPage('/user/portfolio')" :class="{ active: isActive('/user/portfolio') }">
        <div class="app-bar-icon">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
            <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
          </svg>
        </div>
        <span class="app-bar-label">Portfolio</span>
      </div>

      <div class="app-bar-item" @click="navigateToPage('/MoneyRequest')" :class="{ active: isActive('/MoneyRequest') }">
        <div class="app-bar-icon">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
            <path d="M11 15h2v-3h3v-2h-3V7h-2v3H8v2h3v3zM12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8z"/>
          </svg>
        </div>
        <span class="app-bar-label">Deposit</span>
      </div>

      <div class="app-bar-item" @click="navigateToPage('/profile')" :class="{ active: isActive('/profile') }">
        <div class="app-bar-icon">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
            <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
          </svg>
        </div>
        <span class="app-bar-label">Profile</span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';

const route = useRoute();
const router = useRouter();

// Check if current route is active
const isActive = (path) => {
  return route.path === path || route.path.startsWith(path);
};

// Navigation function
const navigateToPage = (path) => {
  // Check if it's an internal Vue route or external page
  const internalRoutes = ['/user/dashboard', '/markets', '/user/portfolio'];
  
  if (internalRoutes.includes(path)) {
    // Use Vue Router for internal routes
    router.push(path);
  } else {
    // Use window.location for external pages
    window.location.href = `http://127.0.0.1:8000${path}`;
  }
};
</script>

<style scoped>
/* Bottom App Bar */
.bottom-app-bar {
  position: fixed;
  bottom: 0;
  left: 0;
  right: 0;
  background: rgba(17, 24, 39, 0.95);
  backdrop-filter: blur(20px);
  border-top: 1px solid rgba(0, 255, 128, 0.2);
  z-index: 1000;
  padding: 8px 0;
  box-shadow: 0 -4px 20px rgba(0, 0, 0, 0.3);
}

.app-bar-container {
  display: flex;
  justify-content: space-around;
  align-items: center;
  max-width: 500px;
  margin: 0 auto;
  padding: 0 20px;
}

.app-bar-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 8px 12px;
  cursor: pointer;
  transition: all 0.3s ease;
  border-radius: 12px;
  min-width: 60px;
}

.app-bar-item:hover {
  background: rgba(0, 255, 128, 0.1);
  transform: translateY(-2px);
}

.app-bar-item.active {
  background: rgba(0, 255, 128, 0.15);
  color: #00ff80;
}

.app-bar-item.active .app-bar-icon {
  color: #00ff80;
  transform: scale(1.1);
}

.app-bar-icon {
  color: #9ca3af;
  transition: all 0.3s ease;
  margin-bottom: 4px;
}

.app-bar-label {
  font-size: 10px;
  font-weight: 500;
  color: #9ca3af;
  transition: color 0.3s ease;
  text-align: center;
}

.app-bar-item.active .app-bar-label {
  color: #00ff80;
  font-weight: 600;
}

/* Mobile specific styles */
@media (max-width: 768px) {
  .app-bar-container {
    padding: 0 10px;
  }
  
  .app-bar-item {
    min-width: 50px;
    padding: 6px 8px;
  }
  
  .app-bar-label {
    font-size: 9px;
  }
}

@media (max-width: 480px) {
  .app-bar-item {
    min-width: 45px;
    padding: 4px 6px;
  }
  
  .app-bar-label {
    font-size: 8px;
  }
}

/* Hide app bar on large desktop screens */
@media (min-width: 1200px) {
  .bottom-app-bar {
    display: none;
  }
}
</style>
