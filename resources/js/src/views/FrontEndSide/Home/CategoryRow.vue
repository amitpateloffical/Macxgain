<script setup>
const categories = ['Tech', 'Fashion', 'Health', 'Travel', 'Food'];
</script>

<template>
  <div class="flex gap-4 p-4 bg-gray-100">
    <div v-for="category in categories" :key="category" class="px-4 py-2 bg-blue-500 text-white rounded">
      {{ category }}
    </div>
  </div>
</template>
