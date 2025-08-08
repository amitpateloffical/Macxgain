<template>
  <div :class="layout">
    <div class="app">
      <Header v-if="layout !== 'FullLayout'" />
      <div class="main_screen_sidebar_devider">
        <Sidebar v-if="layout !== 'FullLayout'" />
        <div class="main_content_screen">
          <router-view />
          <Footer />
        </div>
      </div>      
    </div>
  </div>

</template>

<script setup>
import { watch, computed } from 'vue'
import Header from "./views/Layout/Header.vue";
import Footer from "./views/Layout/Footer.vue";
import Sidebar from "./views/Layout/Sidebar.vue";
import { useRouter } from 'vue-router';

const route = useRouter();

const layout = computed(() => {  
  return route.currentRoute.value.meta.layout === 'full' ? 'FullLayout' : 'DefaultLayout';
})


</script>
