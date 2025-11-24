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

      <!-- Watchlist temporarily hidden -->
      <!-- <div class="app-bar-item" @click="navigateToPage('/user/watchlist')" :class="{ active: isActive('/user/watchlist') }">
        <div class="app-bar-icon">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
            <path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/>
          </svg>
        </div>
        <span class="app-bar-label">Watchlist</span>
      </div> -->

      <div class="app-bar-item" @click="navigateToPage('/user/orders')" :class="{ active: isActive('/user/orders') }">
        <div class="app-bar-icon">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
            <path d="M9,5V9H21V5M9,19H21V15H9M9,14H21V10H9M4,9H8V5H4M4,19H8V15H4M4,14H8V10H4V14Z"/>
          </svg>
        </div>
        <span class="app-bar-label">Orders</span>
      </div>

      <div class="app-bar-item" @click="navigateToPage('/MoneyRequest')" :class="{ active: isActive('/MoneyRequest') }">
        <div class="app-bar-icon">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
            <path d="M11 15h2v-3h3v-2h-3V7h-2v3H8v2h3v3zM12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8z"/>
          </svg>
        </div>
        <span class="app-bar-label">Deposit</span>
      </div>

      <div class="app-bar-item" @click="navigateToPage('/Withdrawal/Request')" :class="{ active: isActive('/Withdrawal/Request') }">
        <div class="app-bar-icon">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm5 11H7v-2h10v2z"/>
          </svg>
        </div>
        <span class="app-bar-label">Withdraw</span>
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
import { computed, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';

const route = useRoute();
const router = useRouter();
const activeSection = ref('');

// Check if current route is active
const isActive = (path) => {
  return route.path === path || route.path.startsWith(path);
};

// Navigation function
const navigateToPage = (path) => {
  // Check if it's an internal Vue route or external page
  const internalRoutes = ['/user/dashboard', '/user/portfolio'];
  
  if (internalRoutes.includes(path)) {
    // Use Vue Router for internal routes
    router.push(path);
  } else {
    // Use dynamic URL for external pages
    window.location.href = `${window.location.origin}${path}`;
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
  background: var(--color-bg-secondary, rgba(17, 24, 39, 0.95));
  backdrop-filter: blur(20px);
  border-top: 1px solid var(--color-border-primary, rgba(255, 215, 0, 0.2));
  z-index: 1000;
  padding: 8px 0;
  box-shadow: 0 -4px 20px rgba(0, 0, 0, 0.3);
}

.app-bar-container {
  display: flex;
  justify-content: space-around;
  align-items: center;
  max-width: 600px;
  margin: 0 auto;
  padding: 0 15px;
}

.app-bar-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 6px 8px;
  cursor: pointer;
  transition: all 0.3s ease;
  border-radius: 12px;
  min-width: 50px;
  flex: 1;
  max-width: 80px;
}

.app-bar-item:hover {
  background: rgba(var(--color-primary-rgb, 0, 255, 128), 0.1);
  transform: translateY(-2px);
}

.app-bar-item.active {
  background: rgba(var(--color-primary-rgb, 0, 255, 128), 0.15);
  color: var(--color-primary, #FFD700);
}

.app-bar-item.active .app-bar-icon {
  color: var(--color-primary, #FFD700);
  transform: scale(1.1);
}

.app-bar-icon {
  color: var(--color-text-muted, #9ca3af);
  transition: all 0.3s ease;
  margin-bottom: 4px;
}

.app-bar-label {
  font-size: 9px;
  font-weight: 500;
  color: var(--color-text-muted, #9ca3af);
  transition: color 0.3s ease;
  text-align: center;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.app-bar-item.active .app-bar-label {
  color: var(--color-primary, #FFD700);
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

/* Tablet styles */
@media (min-width: 769px) and (max-width: 1199px) {
  .app-bar-container {
    max-width: 700px;
    padding: 0 18px;
  }
  
  .app-bar-item {
    padding: 8px 10px;
    max-width: 90px;
  }
  
  .app-bar-label {
    font-size: 10px;
  }
}

/* Desktop styles - keep app bar visible on all screen sizes */
@media (min-width: 1200px) {
  .bottom-app-bar {
    background: var(--color-bg-secondary, rgba(17, 24, 39, 0.98));
    backdrop-filter: blur(25px);
    border-top: 1px solid var(--color-border-primary, rgba(255, 215, 0, 0.3));
  }
  
  .app-bar-container {
    max-width: 800px;
    padding: 0 20px;
  }
  
  .app-bar-item {
    padding: 10px 12px;
    max-width: 100px;
  }
  
  .app-bar-label {
    font-size: 11px;
  }
  
  .app-bar-item:hover {
    background: rgba(var(--color-primary-rgb, 0, 255, 128), 0.15);
    transform: translateY(-3px);
  }
}
</style>
