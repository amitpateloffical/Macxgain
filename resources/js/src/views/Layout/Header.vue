<template>
  <div class="header_screen">
    <div class="logo-section">
      <img src="../FrontEndSide/logo.png" alt="Macxgain Logo" class="logo" />
      <h1 class="brand-name">Macxgain</h1>
    </div>

    <div class="header_menu">
      <ul>
        <li>
          <b-link><i class="fa-regular fa-calendar-check"></i></b-link>
        </li>
        <li>
          <b-link><i class="fa-regular fa-bell"></i></b-link>
        </li>
      </ul>
      <div class="profile_dropdown" @click="toggleSidebar">
        <div class="header_profile_pic">
          <img src="../assest/img/tableprofileimg.png" alt="profile" />
        </div>
        <div class="header_profile_name_role">
          <p>Kamlesh Patel</p>
          <span>Super Admin</span>
        </div>
        <i class="fa-solid fa-ellipsis"></i>
      </div>
    </div>

    <!-- Sidebar -->
    <div :class="['sidebar', { open: sidebarOpen }]">
      <div class="sidebar-header">
        <img
          src="../assest/img/tableprofileimg.png"
          alt="Profile"
          class="sidebar-profile-pic"
        />
        <h3>Kamlesh Patel</h3>
        <span> 💰 999</span>
        <button class="close-btn" @click="toggleSidebar">&times;</button>
      </div>

      <ul class="sidebar-menu">
        <li>
          <router-link
            :to="{ name: 'add_money' }"
            class="sidebar-link"
            @click="toggleSidebar"
          >
            💰 Add Money
          </router-link>
        </li>
        <li>
          <router-link
            :to="{ name: 'money_request' }"
            class="sidebar-link"
            @click="toggleSidebar"
          >
            📝 Request Add Money
          </router-link>
        </li>
        <li>
          <router-link
            :to="{ name: 'add_money' }"
            class="sidebar-link"
            @click="toggleSidebar"
          >
            ⚙️ Update Profile
          </router-link>
        </li>

          <li>
          <router-link
            :to="{ name: 'login' }"
            class="sidebar-link"
           
          >
           Log out
          </router-link>
        </li>
      </ul>

      <!-- Request Add Money Form -->
      <div v-if="showRequestForm" class="request-form">
        <h4>Request Add Money</h4>
        <input
          type="text"
          v-model="transactionId"
          placeholder="Transaction ID"
        />
        <input type="file" @change="handleFileUpload" />
        <button @click="submitRequest">Send Request</button>
      </div>
    </div>

    <!-- Overlay -->
    <div v-if="sidebarOpen" class="overlay" @click="toggleSidebar"></div>
  </div>
</template>

<script setup>
import { ref } from "vue";

const sidebarOpen = ref(false);
const showRequestForm = ref(false);
const transactionId = ref("");
const file = ref(null);

const toggleSidebar = () => {
  sidebarOpen.value = !sidebarOpen.value;
  showRequestForm.value = false;
};

const openRequestForm = () => {
  showRequestForm.value = true;
};

const addMoney = () => {
  alert("Add Money Clicked");
};

const updateProfile = () => {
  alert("Update Profile Clicked");
};

const handleFileUpload = (e) => {
  file.value = e.target.files[0];
};

const submitRequest = () => {
  if (!transactionId.value || !file.value) {
    alert("Please enter transaction ID and attach a file.");
    return;
  }
  // Send to backend via API (you need to implement backend part)
  console.log("Transaction ID:", transactionId.value);
  console.log("File:", file.value);

  // Example DB log:
  // {
  //   user: "Kamlesh Patel",
  //   type: "Money Request",
  //   transId: transactionId.value,
  //   screenshot: file.value.name,
  //   date: new Date()
  // }

  alert("Request submitted to Admin.");
  showRequestForm.value = false;
  transactionId.value = "";
  file.value = null;
};
</script>

<style scoped>
.header_screen {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 10px 20px;
  background-color: #4a89dc;
  color: white;
  position: relative;
}
.logo {
  height: 50px;
}
.logo-section {
  display: flex;
  align-items: center;
  gap: 12px;
}
.header_menu {
  display: flex;
  align-items: center;
}
.header_menu ul {
  list-style: none;
  display: flex;
  gap: 20px;
  margin: 0;
}
.profile_dropdown {
  display: flex;
  align-items: center;
  cursor: pointer;
  margin-left: 20px;
}
.header_profile_pic img {
  width: 40px;
  height: 40px;
  border-radius: 50%;
}
.header_profile_name_role {
  margin: 0 10px;
  text-align: left;
}
.sidebar {
  position: fixed;
  top: 0;
  right: -100%;
  width: 30%;
  max-width: 400px;
  height: 100%;
  background: #fff;
  color: #333;
  transition: right 0.3s ease;
  z-index: 1001;
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
  width: 100px;
  height: 100px;
  border-radius: 50%;
  object-fit: cover;
}
.sidebar-header h3 {
  margin: 10px 0 5px;
}
.sidebar-header span {
  color: gray;
}
.close-btn {
  position: absolute;
  top: 10px;
  right: 15px;
  font-size: 24px;
  background: none;
  border: none;
  cursor: pointer;
}
.sidebar-menu {
  list-style: none;
  padding: 20px 0;
}
.sidebar-menu li {
  padding: 12px;
  cursor: pointer;
  border-bottom: 1px solid #ddd;
}
.sidebar-menu li:hover {
  background-color: #f1f1f1;
}
.request-form {
  margin-top: 20px;
}
.request-form input {
  display: block;
  margin-bottom: 10px;
  padding: 8px;
  width: 100%;
  border: 1px solid #ccc;
  border-radius: 4px;
}
.request-form button {
  background-color: #4a89dc;
  color: white;
  border: none;
  padding: 10px;
  cursor: pointer;
  width: 100%;
}
.overlay {
  position: fixed;
  top: 0;
  left: 0;
  height: 100%;
  width: 100%;
  background: rgba(0, 0, 0, 0.5);
  z-index: 1000;
}

/* Responsive */
@media (max-width: 768px) {
  .sidebar {
    width: 100%;
  }
}
</style>
