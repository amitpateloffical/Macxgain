<template>
  <div :class="layout">
    <div class="app">
<<<<<<< HEAD
      <Header v-if="layout !== 'FullLayout' && !isMobile" />
      <div class="main_screen_sidebar_devider" :class="{ 'mobile-layout': isMobile }">
        <!-- <Sidebar v-if="layout !== 'FullLayout'" /> -->
=======
      <Header v-if="layout !== 'FullLayout'" />
      <div class="main_screen_sidebar_devider">
        <!-- <Sidebar v-if="layout !== 'FullLayout'" /> -->
        <div class="main_content_screen">
          <router-view />
        </div>
      </div>      
    </div>
  </div>

</template>

<script setup>
import { watch, computed, onMounted, ref, onBeforeUnmount } from 'vue'
import Header from "./views/Layout/Header.vue";
import { useRouter } from 'vue-router';

const route = useRouter();
const isMobile = ref(false);

const layout = computed(() => {  
  return route.currentRoute.value.meta.layout === 'full' ? 'FullLayout' : 'DefaultLayout';
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
