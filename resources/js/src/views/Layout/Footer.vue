<template>
    <div class="footer_screen px-4 py-2">
         <span>@{{ brandConfig.copyrightYear }} {{ brandConfig.companyName }}.</span>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
import { getBrandConfig } from '@/config/brand';

// Create reactive brand config
const brandConfigRef = ref(getBrandConfig());

// Listen for brand config updates
onMounted(() => {
  const handleUpdate = () => {
    brandConfigRef.value = getBrandConfig();
  };
  window.addEventListener('brandConfigUpdated', handleUpdate);
  
  onBeforeUnmount(() => {
    window.removeEventListener('brandConfigUpdated', handleUpdate);
  });
});

// Use computed to access brand config reactively
const brandConfig = computed(() => brandConfigRef.value);
</script>