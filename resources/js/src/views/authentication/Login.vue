<template>
  <div class="login_screen">
      <!-- <img src="../assest/img/logo.png" class="login-logo" alt="Macxgain"> -->
    <div class="login_form">
      
      <h3>LOG IN</h3>
      <p>Enter your credentials to access your account.</p>
      <form @submit.prevent="login">
        <div class="form-group mb-3">
          <label for="">Email</label>
          <input type="email" v-model="formData.email" required placeholder="Enter Email ID" class="form-control" />
        </div>
        <div class="form-group mb-2">
          <label for="">Password</label>
          <div class="position_password">
            <input :type="formData.showPassword ? 'text' : 'password'" v-model="formData.password" required
              placeholder="Password" class="form-control" />
            <i @click="formData.showPassword = !formData.showPassword" class="password_toggle"
              :class="formData.showPassword ? 'fa fa-eye-slash' : 'fa fa-eye'"></i>
          </div>

        </div>
        <!--<div class="remember_forgot_password">
          <div>
            <b-form-checkbox id="remember-me" v-model="formData.status" name="checkbox-1">
              Remember Me
            </b-form-checkbox>
          </div>
          <div>
            <router-link to="/forgot-password">Forgot Password?</router-link>
          </div>
        </div>-->
        <b-overlay :show="formData.loader" rounded="lg" opacity="0.9" class="loader_section" no-wrap>
          <template #overlay>
            <span class="loadersdots"></span>
          </template>

          <b-button type="submit" class="w-100">Login</b-button>
        </b-overlay>
        <p v-if="formData.errorMessage" class="error">{{ formData.errorMessage }}</p>
      </form>
    </div>
    <div class="login_slider">
    </div>
  </div>
</template>
<script setup>
import { ref } from "vue";
import mitt from 'mitt'; 
import axios from '@axios';
import { useRouter } from 'vue-router';
import { Navigation, Pagination, Scrollbar, A11y, Autoplay } from 'swiper/modules';
import { Swiper, SwiperSlide } from 'swiper/vue';
import 'swiper/css';
import 'swiper/css/navigation';
import 'swiper/css/pagination';
import 'swiper/css/scrollbar';
import 'swiper/css/autoplay';

import { toast } from 'vue3-toastify';
import 'vue3-toastify/dist/index.css';



// Comman variables
const formData = ref({
  email: '',
  password: '',
  errorMessage: '',
  status: false,
  showPassword: false,
  loader: false
});

const router = useRouter();

const emitter = mitt();

// -------------------------------------------------- //

const retrieveStoredCredentials = () => {
  const storedCredentials = localStorage.getItem('user_credentials');
  if (storedCredentials) {
    const credentials = JSON.parse(storedCredentials);
    formData.value.email = credentials.email;
    formData.value.password = credentials.password;
  }
}

const storedStatus = localStorage.getItem('rememberMeStatus');
formData.value.status = storedStatus === 'true';
if (formData.value.status) {
  retrieveStoredCredentials();
}

const removeError = (field) => {
  if (this.errors[field]) {
    delete this.errors[field];
  }
}
const hasErrors = (fieldName) => {
  return this.errors && this.errors[fieldName];
}
const getErrors = (fieldName) => {
  return this.errors[fieldName] ? this.errors[fieldName][0] : '';
}

const login = () => {
  formData.value.loader = true;
  localStorage.setItem('rememberMeStatus', formData.value.status);
  if (!formData.value.email || !formData.value.password) {
    formData.value.errorMessage = 'Both fields are required';
    return;
  }

  axios
    .post('/login', { email: formData.value.email, password: formData.value.password })
    .then(response => {
      const token = response.data.access_token;  // Assume the token is returned as access_token

      // Store the JWT token in localStorage
      localStorage.setItem('access_token', token);

      if (formData.value.status) {
        const credentials = { email: formData.value.email, password: formData.value.password };
        localStorage.setItem('user_credentials', JSON.stringify(credentials));
      }
      // Success alert
      toast("Login Successful!", {
        autoClose: 5000,
        type: 'success'
      });

      // Redirect to the dashboard after a delay
      setTimeout(() => {
        router.push('/landing-page');
        formData.value.loader = false;
      }, 2000);
      emitter.emit('login');
    })
    .catch((error) => {
      if (error.response && error.response.status == 422) {
        this.errors = error.response.data.error;
      }
      else if (error.response && error.response.status === 401) {
        formData.value.errorMessage = error.response.data.error || 'Invalid credentials';
      } else {
        formData.value.errorMessage = 'An unexpected error occurred. Please try again later.';
      }

    });
}


</script>

<style scoped>
.login-logo {
  width: 150px; /* or 120px if large logo is preferred */
  height: auto;
}

</style>
