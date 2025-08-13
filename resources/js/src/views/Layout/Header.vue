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
        <div class="header_profile_name_role">
          <p class="name">{{ storedUser.name }}</p>
          <span class="role">{{ storedUser.is_admin == 1 ? "Admin" : "User" }}</span>
        </div>
        <i class="fa-solid fa-ellipsis"></i>
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
        <h3>{{ storedUser.name }}</h3>
        <span class="balance">💰 {{ storedUser.total_balance }}</span>
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
  if(storedUser.value.is_admin){
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
}

.logo {
  height: 42px;
}
.logo-section {
  display: flex;
  align-items: center;
  gap: 10px;
  cursor: pointer;
}
.brand-name {
  font-size: 1.5rem;
  font-weight: bold;
  color: #00ff80;
}

.header_menu {
  display: flex;
  align-items: center;
  gap: 20px;
}
.header_menu ul {
  list-style: none;
  display: flex;
  gap: 15px;
  margin: 0;
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
  gap: 10px;
  background: rgba(255, 255, 255, 0.05);
  padding: 5px 10px;
  border-radius: 8px;
  cursor: pointer;
  transition: background 0.3s ease;
}
.profile_dropdown:hover {
  background: rgba(255, 255, 255, 0.1);
}
.header_profile_pic img {
  width: 38px;
  height: 38px;
  border-radius: 50%;
  border: 2px solid #00ff80;
}
.header_profile_name_role {
  line-height: 1.2;
}
.name {
  font-weight: bold;
}
.role {
  font-size: 0.85rem;
  color: #a1a1a1;
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

@media (max-width: 768px) {
  .sidebar {
    width: 100%;
  }
  .brand-name {
    font-size: 1.2rem;
  }
}
</style>
