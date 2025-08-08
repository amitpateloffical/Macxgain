<template>
  <div class="header_screen">
    <div class="header_logo">
      <img
        :src="shortLogo"
        :alt="Macxgain"
        class="logo svg-logo"
      />
    </div>
    <div class="header_menu">
      <ul>
        <li><b-link><i class="fa-regular fa-calendar-check"></i></b-link></li>
        <li><b-link><i class="fa-regular fa-bell"></i></b-link></li>        
      </ul>
      <div class="profile_dropdown">
        <div class="header_profile_pic">
          <img src="../assest/img/tableprofileimg.png" alt="profile">
        </div>
        <div class="header_profile_name_role">
          <p>Kamlesh Patel</p>
          <span>Super Admin</span>
        </div>
        <i class="fa-solid fa-ellipsis"></i>
      </div>
    </div>
  </div>
</template>

<script setup>
import shortLogo from '../assest/img/logo1.svg'; // Short Logo

</script>

<style scoped>
.header {
  background-color: #4a89dc;
  color: white;
  padding: 20px;
  text-align: right;
  position: relative;
}
.header nav {
  display: flex;
  align-items: center;
  justify-content: space-between;
}
.header nav a {
  color: white;
  margin: 0 15px;
  text-decoration: none;
}
.header nav a:hover {
  text-decoration: underline;
}
.logo {
  height: 50px;
  margin-right: auto;
}

.profile-dropdown {
  position: relative;
  text-align: center; 
}

.profile-img {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  cursor: pointer;
}

.profile-name {
  margin-left: 10px;
  font-size: 14px;
  color: white; 
  text-align: left;
}

.dropdown-item {
  padding: 5px 10px;
  text-align: left;
  cursor: pointer;
  display: block;
  text-decoration: none;
  color: black;
}

.dropdown-item:hover {
  background-color: #f1f1f1;
}
</style>
