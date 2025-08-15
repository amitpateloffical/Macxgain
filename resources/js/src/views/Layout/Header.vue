<template>
  <div class="header_screen">
    <!-- Logo -->
    <div
  class="logo-section"
  @click="storedUser.is_admin ? router.push('/admin/dashboard') : router.push('/user/dashboard')"
>
  <img src="../FrontEndSide/logo.png" alt="Macxgain Logo" class="logo" />
  <h1 class="brand-name">Macxgain</h1>
</div>


    <!-- Header Menu -->
    <div class="header_menu">
      <!-- Calendar & Notifications -->
      <ul>
        <li>
          <b-link><i class="fa-regular fa-calendar-check"></i></b-link>
        </li>
        <li>
          <b-link><i class="fa-regular fa-bell"></i></b-link>
        </li>
      </ul>

      <!-- Profile Dropdown (Sidebar Toggle) -->
      <div class="profile_dropdown" @click="toggleSidebar">
        <div class="header_profile_pic">
          <img src="../assest/img/tableprofileimg.png" alt="profile" />
        </div>
      </div>

      <!-- Logout Button -->
      <button class="logout_btn" @click="handleLogout">
        <i class="fa-solid fa-right-from-bracket"></i> Logout
      </button>
    </div>

    <!-- Sidebar -->
    <div :class="['sidebar', { open: sidebarOpen }]">
      <div class="sidebar-header">
        <img src="../assest/img/tableprofileimg.png" alt="Profile" class="sidebar-profile-pic" />
        <h3>{{ storedUser?.name || 'User' }}</h3>
        <span class="balance">💰 {{ storedUser?.total_balance || '0.00' }}</span>
        <button class="close-btn" @click="toggleSidebar">&times;</button>
      </div>

      <ul class="sidebar-menu">
        <li>
          <router-link :to="{ name: 'add_money' }" class="sidebar-link" @click="toggleSidebar">💰 Add Money</router-link>
        </li>
        <li>
          <router-link :to="{ name: 'money_request' }" class="sidebar-link" @click="toggleSidebar">📝 Request Add Money</router-link>
        </li>
        <li>
          <router-link :to="{ name: 'profile' }" class="sidebar-link" @click="toggleSidebar">⚙️ Update Profile</router-link>
        </li>
        <li @click="handleLogout">
          <b-link class="sidebar-link" :to="{ name: 'login' }" @click="toggleSidebar">🚪 Log out</b-link>
        </li>
      </ul>
    </div>

    <!-- Overlay -->
    <div v-if="sidebarOpen" class="overlay" @click="toggleSidebar"></div>
  </div>
</template>

<script setup>
import { ref } from "vue";
import { useRouter } from "vue-router";

const router = useRouter();
const storedUser = JSON.parse(localStorage.getItem("userData"));
const sidebarOpen = ref(false);

const goToDashboard = () => {
  if(storedUser && storedUser.is_admin){
    router.push("/admin/dashboard");
  }else{
    router.push("/user/dashboard");
  }
};
const toggleSidebar = () => {
  sidebarOpen.value = !sidebarOpen.value;
};
const handleLogout = () => {
  localStorage.removeItem("userData");
  localStorage.removeItem("access_token");
  router.push("/login");
};
</script>

<style scoped>
.header_screen {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 12px 20px;
  background: linear-gradient(90deg, #0d0d1a, #101022);
  color: white;
  border-bottom: 1px solid rgba(0, 255, 128, 0.2);
  flex-wrap: wrap;
  gap: 10px;
  min-height: 60px;
}

.logo {
  height: 42px;
  width: auto;
}

.logo-section {
  display: flex;
  align-items: center;
  gap: 10px;
  cursor: pointer;
  flex-shrink: 0;
}

.brand-name {
  font-size: 1.5rem;
  font-weight: bold;
  color: #00ff80;
  white-space: nowrap;
}

.header_menu {
  display: flex;
  align-items: center;
  gap: 20px;
  flex-wrap: wrap;
}

.header_menu ul {
  list-style: none;
  display: flex;
  gap: 15px;
  margin: 0;
  padding: 0;
  flex-shrink: 0;
}

.header_menu i {
  font-size: 1.2rem;
  color: #00ff80;
  transition: color 0.3s ease;
}

.header_menu i:hover {
  color: #00cc66;
}

.profile_dropdown {
  display: flex;
  align-items: center;
  justify-content: center;
  background: rgba(255, 255, 255, 0.08);
  padding: 6px;
  border-radius: 50%;
  cursor: pointer;
  transition: all 0.3s ease;
  flex-shrink: 0;
  min-width: 0;
  width: 50px;
  height: 50px;
  border: 2px solid rgba(0, 255, 128, 0.3);
  position: relative;
  overflow: hidden;
}

.profile_dropdown:hover {
  background: rgba(255, 255, 255, 0.15);
  transform: scale(1.08);
  border-color: rgba(0, 255, 128, 0.6);
  box-shadow: 0 4px 12px rgba(0, 255, 128, 0.2);
}

.profile_dropdown:active {
  transform: scale(0.95);
}

.header_profile_pic {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 100%;
  height: 100%;
}

.header_profile_pic img {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  border: 2px solid #00ff80;
  flex-shrink: 0;
  object-fit: cover;
  transition: all 0.3s ease;
}

.profile_dropdown:hover .header_profile_pic img {
  border-color: #00ff80;
  transform: scale(1.05);
}

/* Logout Button */
.logout_btn {
  background: transparent;
  border: 1px solid #ff4d4d;
  color: #ff4d4d;
  padding: 6px 12px;
  border-radius: 6px;
  font-size: 0.9rem;
  display: flex;
  align-items: center;
  gap: 6px;
  cursor: pointer;
  transition: all 0.3s ease;
  flex-shrink: 0;
  white-space: nowrap;
}

.logout_btn:hover {
  background: #ff4d4d;
  color: white;
}

.sidebar {
  position: fixed;
  top: 0;
  right: -100%;
  width: 280px;
  height: 100%;
  background: rgba(16, 16, 34, 0.95);
  backdrop-filter: blur(10px);
  color: white;
  transition: right 0.3s ease;
  z-index: 1200;
  padding: 20px;
  overflow-y: auto;
}

.sidebar.open {
  right: 0;
}

.sidebar-header {
  text-align: center;
  position: relative;
}

.sidebar-profile-pic {
  width: 90px;
  height: 90px;
  border-radius: 50%;
  border: 3px solid #00ff80;
  object-fit: cover;
}

.sidebar-header h3 {
  margin: 10px 0 5px;
}

.balance {
  font-weight: bold;
  color: #00ff80;
}

.close-btn {
  position: absolute;
  top: 8px;
  right: 12px;
  font-size: 24px;
  background: none;
  border: none;
  cursor: pointer;
  color: white;
}

.sidebar-menu {
  list-style: none;
  padding: 20px 0;
}

.sidebar-link {
  display: block;
  padding: 12px;
  text-decoration: none;
  color: white;
  font-weight: 500;
  border-radius: 8px;
  transition: background 0.3s ease, transform 0.2s ease;
}

.sidebar-link:hover {
  background: rgba(0, 255, 128, 0.15);
  transform: translateX(4px);
}

.overlay {
  position: fixed;
  top: 0;
  left: 0;
  height: 100%;
  width: 100%;
  background: rgba(0, 0, 0, 0.6);
  z-index: 1000;
}

/* Tablet Breakpoint (768px and below) */
@media (max-width: 768px) {
  .header_screen {
    padding: 10px 16px;
    gap: 8px;
  }
  
  .logo {
    height: 36px;
  }
  
  .brand-name {
    font-size: 1.2rem;
  }
  
  .header_menu {
    gap: 15px;
  }
  
  .header_menu ul {
    gap: 12px;
  }
  
  .profile_dropdown {
    padding: 5px;
    width: 46px;
    height: 46px;
  }
  
  .header_profile_pic img {
    width: 32px;
    height: 32px;
  }
  
  .logout_btn {
    padding: 5px 10px;
    font-size: 0.8rem;
  }
  
  .sidebar {
    width: 100%;
  }
}

/* Mobile Breakpoint (480px and below) */
@media (max-width: 480px) {
  .header_screen {
    padding: 8px 12px;
    gap: 6px;
    flex-direction: column;
    align-items: stretch;
    min-height: auto;
  }
  
  .logo-section {
    justify-content: center;
    margin-bottom: 8px;
  }
  
  .header_menu {
    justify-content: center;
    gap: 12px;
  }
  
  .header_menu ul {
    gap: 10px;
  }
  
  .profile_dropdown {
    padding: 4px;
    width: 42px;
    height: 42px;
  }
  
  .header_profile_pic img {
    width: 28px;
    height: 28px;
  }
  
  .logout_btn {
    padding: 4px 8px;
    font-size: 0.75rem;
  }
}

/* Small Mobile Breakpoint (320px and below) */
@media (max-width: 320px) {
  .header_screen {
    padding: 6px 8px;
    gap: 4px;
  }
  
  .logo {
    height: 30px;
  }
  
  .brand-name {
    font-size: 1rem;
  }
  
  .header_menu {
    gap: 8px;
  }
  
  .header_menu ul {
    gap: 8px;
  }
  
  .profile_dropdown {
    padding: 2px 4px;
    gap: 4px;
  }
  
  .header_profile_pic img {
    width: 24px;
    height: 24px;
  }
  
  .name {
    max-width: 50px;
    font-size: 0.7rem;
  }
  
  .role {
    max-width: 50px;
    font-size: 0.65rem;
  }
  
  .logout_btn {
    padding: 3px 6px;
    font-size: 0.7rem;
  }
}

/* Landscape Mobile (Orientation) */
@media (max-width: 768px) and (orientation: landscape) {
  .header_screen {
    flex-direction: row;
    align-items: center;
    min-height: 50px;
  }
  
  .logo-section {
    margin-bottom: 0;
  }
  
  .header_menu {
    justify-content: flex-end;
  }
}

/* Touch Device Optimizations */
@media (hover: none) and (pointer: coarse) {
  .profile_dropdown {
    transition: none;
  }
  
  .profile_dropdown:active {
    background: rgba(255, 255, 255, 0.15);
  }
  
  .logout_btn {
    transition: none;
  }
  
  .logout_btn:active {
    background: #ff4d4d;
    color: white;
  }
}

/* High DPI Displays */
@media (-webkit-min-device-pixel-ratio: 2), (min-resolution: 192dpi) {
  .logo {
    image-rendering: -webkit-optimize-contrast;
    image-rendering: crisp-edges;
  }
}

/* Dark Mode Support */
@media (prefers-color-scheme: dark) {
  .header_screen {
    background: linear-gradient(90deg, #0d0d1a, #101022);
  }
}

/* Reduced Motion Support */
@media (prefers-reduced-motion: reduce) {
  .header_screen,
  .profile_dropdown,
  .logout_btn,
  .sidebar {
    transition: none;
  }
}
</style>
