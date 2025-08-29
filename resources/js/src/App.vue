<template>
  <div :class="layout">
    <div class="app">
      <Header v-if="layout !== 'FullLayout' && !isMobile" />
      <div class="main_screen_sidebar_devider" :class="{ 'mobile-layout': isMobile }">
        <!-- <Sidebar v-if="layout !== 'FullLayout'" /> -->
        <div class="main_content_screen">
          <router-view />
        </div>
      </div>
      
      <!-- Global Bottom App Bar for Employee Pages -->
      <BottomAppBar v-if="shouldShowAppBar" />
    </div>
  </div>

</template>

<script setup>
import { watch, computed, onMounted, ref, onBeforeUnmount } from 'vue'
import Header from "./views/Layout/Header.vue";
import BottomAppBar from "./components/BottomAppBar.vue";
import { useRouter } from 'vue-router';

const route = useRouter();
const isMobile = ref(false);

const layout = computed(() => {  
  return route.currentRoute.value.meta.layout === 'full' ? 'FullLayout' : 'DefaultLayout';
})

// Determine when to show the bottom app bar
const shouldShowAppBar = computed(() => {
  const currentPath = route.currentRoute.value.path;
  
  // Show app bar on employee/user pages, hide on admin and auth pages
  const employeePages = [
    '/user/dashboard',
    '/markets', 
    '/user/portfolio',
    '/MoneyRequest',
    '/profile',
    '/user/profile',
    '/user/settings',
    '/user/transactions',
    '/user/wallet'
  ];
  
  // Hide on admin and auth pages
  const hideOnPages = [
    '/login',
    '/register', 
    '/forgot-password',
    '/reset-password',
    '/admin'
  ];
  
  // Don't show on admin pages
  if (currentPath.startsWith('/admin')) {
    return false;
  }
  
  // Don't show on auth pages
  if (hideOnPages.some(page => currentPath.startsWith(page))) {
    return false;
  }
  
  // Show on employee pages or any user-related page
  return employeePages.some(page => currentPath.startsWith(page)) || 
         currentPath.startsWith('/user/') ||
         currentPath === '/markets' ||
         currentPath === '/MoneyRequest' ||
         currentPath === '/profile';
})

// Mobile detection
const checkDeviceType = () => {
  isMobile.value = window.innerWidth <= 768;
};

// Set default title
onMounted(() => {
  document.title = 'Macxgain - Trading with AI and Gain Profit';
  checkDeviceType();
  window.addEventListener('resize', checkDeviceType);
})

onBeforeUnmount(() => {
  window.removeEventListener('resize', checkDeviceType);
})


</script>
<style>
html, body, #app {
  height: 100%;
  margin: 0;
  padding: 0;
  background-color: #0d0d1a; /* Dark background everywhere */
}

.app {
  display: flex;
  flex-direction: column;
  min-height: 100vh; /* Ensure full viewport height */
}

.main_screen_sidebar_devider {
  flex: 1;
  display: flex;
}

.main_content_screen {
  flex: 1;
  background-color: #0d0d1a; /* Keep background consistent */
  padding-bottom: 0; /* Default no padding */
}

/* Add bottom padding when app bar is visible */
.main_content_screen:has(~ .bottom-app-bar),
.main_content_screen.with-app-bar {
  padding-bottom: 80px;
}

/* Mobile Layout Styles */
.mobile-layout {
  margin-top: 0 !important;
  padding-top: 0 !important;
}

/* Mobile-specific adjustments */
@media (max-width: 768px) {
  .app {
    padding-top: 0;
  }
  
  .main_screen_sidebar_devider {
    margin-top: 0;
    padding-top: 0;
  }
  
  .main_content_screen {
    padding-top: 0;
  }
}
</style>
