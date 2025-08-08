<script setup>
const projects = [
  { id: 1, name: 'Project Alpha', description: 'A new innovative solution.' },
  { id: 2, name: 'Project Beta', description: 'A cutting-edge tech project.' },
  { id: 3, name: 'Project Gamma', description: 'A creative design venture.' }
];
</script>

<template>
  <div class="p-4">
    <h2 class="text-xl font-bold mb-4">Our Projects</h2>
    <div class="grid grid-cols-3 gap-4">
      <div v-for="project in projects" :key="project.id" class="p-4 border rounded-lg shadow">
        <h3 class="font-bold">{{ project.name }}</h3>
        <p>{{ project.description }}</p>
      </div>
    </div>
  </div>
</template>
