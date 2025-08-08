<script setup>
import { Swiper, SwiperSlide } from 'swiper/vue';
import 'swiper/css';

const slides = [
  { id: 1, image: '/img1.jpg', title: 'Slide 1' },
  { id: 2, image: '/img2.jpg', title: 'Slide 2' },
  { id: 3, image: '/img3.jpg', title: 'Slide 3' }
];
</script>

<template>
  <swiper class="w-full h-64" :loop="true" :autoplay="{ delay: 3000 }">
    <swiper-slide v-for="slide in slides" :key="slide.id">
      <img :src="slide.image" class="w-full h-64 object-cover" />
    </swiper-slide>
  </swiper>
</template>
