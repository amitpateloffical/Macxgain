<template>
  <div :class="layout">
    <div class="app">
      <Header v-if="layout !== 'FullLayout'" />
      <div class="main_screen_sidebar_devider">
        <div class="main_content_screen">
          <router-view />
        </div>
      </div>      
    </div>
  </div>

</template>

<script setup>
import { watch, computed, onMounted } from 'vue'
import Header from "./views/Layout/Header.vue";
import { useRouter } from 'vue-router';

const route = useRouter();

const layout = computed(() => {  
  return route.currentRoute.value.meta.layout === 'full' ? 'FullLayout' : 'DefaultLayout';
})

// Set default title
onMounted(() => {
  document.title = 'Macxgain - Trading with AI and Gain Profit';
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
</style>
