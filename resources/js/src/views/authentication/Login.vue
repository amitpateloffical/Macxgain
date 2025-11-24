<template>
  <div class="login-container">
    <!-- Background with gradient -->
    <div class="login-background">
      <div class="background-pattern"></div>
    </div>

    <!-- Main login card -->
    <div class="login-card">
      <!-- Logo and header -->
      <div class="login-header">
        <div class="logo-container">
          <div class="logo-image">
            <img :src="brandConfig.logoPath" :alt="brandConfig.companyName + ' Logo'" />
          </div>
          <h1 class="logo-text">{{ brandConfig.companyName }}</h1>
          <p class="tagline">{{ brandConfig.tagline }}</p>
        </div>
        <h2 class="welcome-text">{{ brandConfig.welcomeText }}</h2>
        <p class="subtitle">{{ brandConfig.subtitle }}</p>
      </div>

      <!-- Login form -->
      <form @submit.prevent="login" class="login-form">
        <!-- Email field -->
        <div class="form-group">
          <label for="email" class="form-label">
            <i class="fas fa-user"></i>
            Username
          </label>
          <div class="input-container">
            <input
              id="email"
              type="email"
              v-model="formData.email"
              required
              placeholder="Enter your email"
              class="form-input"
              :class="{ 'error': formData.errorMessage && !formData.email }"
            />
            <i class="fas fa-user input-icon"></i>
          </div>
        </div>

        <!-- Password field -->
        <div class="form-group">
          <label for="password" class="form-label">
            <i class="fas fa-lock"></i>
            Password
          </label>
          <div class="input-container">
            <input
              id="password"
              :type="formData.showPassword ? 'text' : 'password'"
              v-model="formData.password"
              required
              placeholder="Enter your password"
              class="form-input"
              :class="{ 'error': formData.errorMessage && !formData.password }"
            />
            <i class="fas fa-lock input-icon"></i>
            <button
              type="button"
              @click="formData.showPassword = !formData.showPassword"
              class="password-toggle"
              :class="{ 'active': formData.showPassword }"
            >
              <i :class="formData.showPassword ? 'fas fa-eye-slash' : 'fas fa-eye'"></i>
            </button>
          </div>
        </div>

        <!-- Remember me and forgot password -->
        <div class="form-options">
          <label class="checkbox-container">
            <input
              type="checkbox"
              v-model="formData.status"
              class="checkbox-input"
            />
            <span class="checkmark"></span>
            Remember me
          </label>
          <router-link to="/forgot-password" class="forgot-link">
            Forgot Password?
          </router-link>
        </div>

        <!-- Error message -->
        <div v-if="formData.errorMessage" class="error-message">
          <i class="fas fa-exclamation-circle"></i>
          {{ formData.errorMessage }}
        </div>

        <!-- Submit button -->
        <button
          type="submit"
          class="login-button"
          :disabled="formData.loader"
        >
          <span v-if="!formData.loader">
            <i class="fas fa-sign-in-alt"></i>
            Sign In
          </span>
          <span v-else class="loading-spinner">
            <i class="fas fa-spinner fa-spin"></i>
            Signing In...
          </span>
        </button>
      </form>

      <!-- Footer -->
      <div class="login-footer">
        <p>Don't have an account? <router-link to="/register" class="signup-link">Register here</router-link></p>
      </div>
    </div>

    <!-- Trading Background Elements -->
    <div class="trading-background">
      <!-- Animated Stock Charts -->
      <div class="stock-chart chart-1">
        <div class="candlestick up"></div>
        <div class="candlestick down"></div>
        <div class="candlestick up"></div>
        <div class="candlestick up"></div>
        <div class="candlestick down"></div>
        <div class="candlestick up"></div>
        <div class="candlestick up"></div>
        <div class="candlestick down"></div>
      </div>
      
      <div class="stock-chart chart-2">
        <div class="candlestick down"></div>
        <div class="candlestick up"></div>
        <div class="candlestick down"></div>
        <div class="candlestick up"></div>
        <div class="candlestick up"></div>
        <div class="candlestick down"></div>
        <div class="candlestick up"></div>
        <div class="candlestick up"></div>
      </div>
      
      <div class="stock-chart chart-3">
        <div class="candlestick up"></div>
        <div class="candlestick up"></div>
        <div class="candlestick down"></div>
        <div class="candlestick up"></div>
        <div class="candlestick down"></div>
        <div class="candlestick up"></div>
        <div class="candlestick down"></div>
        <div class="candlestick up"></div>
      </div>

      <!-- Trading Indicators -->
      <div class="trading-indicators">
        <div class="indicator indicator-1">
          <div class="line"></div>
          <div class="dot"></div>
        </div>
        <div class="indicator indicator-2">
          <div class="line"></div>
          <div class="dot"></div>
        </div>
        <div class="indicator indicator-3">
          <div class="line"></div>
          <div class="dot"></div>
        </div>
      </div>

      <!-- Price Tickers -->
      <div class="price-tickers">
        <div class="ticker ticker-1">
          <span class="symbol">AAPL</span>
          <span class="price up">$185.42</span>
          <span class="change">+2.34%</span>
        </div>
        <div class="ticker ticker-2">
          <span class="symbol">TSLA</span>
          <span class="price down">$245.67</span>
          <span class="change">-1.23%</span>
        </div>
        <div class="ticker ticker-3">
          <span class="symbol">NVDA</span>
          <span class="price up">$892.15</span>
          <span class="change">+3.45%</span>
        </div>
      </div>

      <!-- Floating Numbers -->
      <div class="floating-numbers">
        <div class="number">+2.34%</div>
        <div class="number">-1.23%</div>
        <div class="number">+3.45%</div>
        <div class="number">+1.67%</div>
        <div class="number">-0.89%</div>
        <div class="number">+4.12%</div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from "vue";
import mitt from 'mitt'; 
import axios from '@axios';
import { useRouter } from 'vue-router';
import { toast } from 'vue3-toastify';
import 'vue3-toastify/dist/index.css';
import { getBrandConfig } from '@/config/brand';

// Create reactive brand config
const brandConfigRef = ref(getBrandConfig());

// Listen for brand config updates and template changes
onMounted(() => {
  const handleUpdate = () => {
    // Reload config and update reactive ref
    brandConfigRef.value = getBrandConfig();
  };
  
  const handleTemplateChange = () => {
    // Force re-render to apply new template colors
    // The CSS variables are already applied globally, just need to trigger reactivity
    if (typeof window !== 'undefined') {
      window.dispatchEvent(new Event('resize'));
    }
  };
  
  window.addEventListener('brandConfigUpdated', handleUpdate);
  window.addEventListener('templateChanged', handleTemplateChange);
  window.addEventListener('forceTemplateUpdate', handleTemplateChange);
  
  onBeforeUnmount(() => {
    window.removeEventListener('brandConfigUpdated', handleUpdate);
    window.removeEventListener('templateChanged', handleTemplateChange);
    window.removeEventListener('forceTemplateUpdate', handleTemplateChange);
  });
});

// Use computed to access brand config reactively
const brandConfig = computed(() => brandConfigRef.value);

// Form data
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

// Retrieve stored credentials
const retrieveStoredCredentials = () => {
  const storedCredentials = localStorage.getItem('user_credentials');
  if (storedCredentials) {
    const credentials = JSON.parse(storedCredentials);
    formData.value.email = credentials.email;
    formData.value.password = credentials.password;
  }
}

// Check for stored remember me status
const storedStatus = localStorage.getItem('rememberMeStatus');
formData.value.status = storedStatus === 'true';
if (formData.value.status) {
  retrieveStoredCredentials();
}

// Login function
const login = () => {
  formData.value.loader = true;
  formData.value.errorMessage = '';
  
  localStorage.setItem('rememberMeStatus', formData.value.status);
  
  if (!formData.value.email || !formData.value.password) {
    formData.value.errorMessage = 'Please fill in all required fields';
    formData.value.loader = false;
    return;
  }

  axios
    .post('/login', { 
      email: formData.value.email, 
      password: formData.value.password 
    })
    .then(response => {
      const token = response.data.access_token;

     const userData = response.data.user; // ✅ correct key

    // Store token and user data
    localStorage.setItem('access_token', token);
    localStorage.setItem('userData', JSON.stringify(userData));

      // Store credentials if remember me is checked
      if (formData.value.status) {
        const credentials = { 
          email: formData.value.email, 
          password: formData.value.password 
        };
        localStorage.setItem('user_credentials', JSON.stringify(credentials));
      }

      // Success toast
      toast.success("Login successful! Redirecting...", {
        autoClose: 2000,
        position: "top-right"
      });

      // Redirect to dashboard
      setTimeout(() => {
        if(userData.is_admin==1){
          router.push('/admin/dashboard');
        }else{
          router.push('/user/dashboard');
        }
        formData.value.loader = false;
      }, 1500);
      
      emitter.emit('login');
    })
    .catch((error) => {
      formData.value.loader = false;
      
      if (error.response && error.response.status === 422) {
        formData.value.errorMessage = 'Please check your input and try again';
      } else if (error.response && error.response.status === 401) {
        formData.value.errorMessage = error.response.data.error || 'Invalid email or password';
      } else if (error.response && error.response.status === 403) {
        formData.value.errorMessage = error.response.data.error || 'User is inactive';
      } else {
        formData.value.errorMessage = 'Connection error. Please try again.';
      }
    });
}
</script>

<style scoped>
/* Main container */
.login-container {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  position: relative;
  overflow: hidden;
  background: linear-gradient(135deg, var(--color-bg-tertiary, #1a1a2e) 0%, var(--color-bg-quaternary, #16213e) 50%, var(--color-bg-primary, #0f3460) 100%) !important;
  padding: 20px;
}

/* Background with pattern */
.login-background {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: linear-gradient(135deg, var(--color-bg-tertiary, #1a1a2e) 0%, var(--color-bg-quaternary, #16213e) 50%, var(--color-bg-primary, #0f3460) 100%) !important;
  z-index: 1;
}

.background-pattern {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-image: 
    radial-gradient(circle at 25% 25%, rgba(var(--color-primary-light-rgb, 255, 229, 92), 0.1) 0%, transparent 50%),
    radial-gradient(circle at 75% 75%, rgba(var(--color-primary-light-rgb, 255, 229, 92), 0.1) 0%, transparent 50%),
    linear-gradient(45deg, transparent 49%, rgba(var(--color-primary-light-rgb, 255, 229, 92), 0.05) 50%, transparent 51%),
    linear-gradient(-45deg, transparent 49%, rgba(var(--color-primary-light-rgb, 255, 229, 92), 0.05) 50%, transparent 51%);
  background-size: 100% 100%, 100% 100%, 20px 20px, 20px 20px;
  animation: float 6s ease-in-out infinite;
}

/* Login card */
.login-card {
  background: var(--color-bg-secondary, rgba(255, 255, 255, 0.95)) !important;
  backdrop-filter: blur(20px);
  border-radius: 20px;
  padding: 40px;
  width: 100%;
  max-width: 450px;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3) !important;
  border: 1px solid var(--color-border-primary, rgba(255, 215, 0, 0.2)) !important;
  position: relative;
  z-index: 2;
  animation: slideUp 0.6s ease-out;
  color: var(--color-text-primary, #000) !important;
}

/* Header */
.login-header {
  text-align: center;
  margin-bottom: 40px;
}

.logo-container {
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 20px;
}

.logo-image {
  width: 60px;
  height: 60px;
  margin-right: 15px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 8px 20px rgba(var(--color-primary-rgb, 255, 229, 92), 0.2);
  background: var(--color-bg-secondary, white) !important;
  padding: 8px;
}

.logo-image img {
  width: 100%;
  height: 100%;
  object-fit: contain;
  border-radius: 8px;
}

.logo-text {
  font-size: 28px;
  font-weight: 700;
  color: var(--color-primary, #FFE55C) !important;
  margin: 0;
  text-shadow: 0 0 10px rgba(var(--color-primary-rgb, 255, 229, 92), 0.3);
}

.tagline {
  color: var(--color-primary, #FFE55C) !important;
  font-size: 14px;
  font-weight: 500;
  margin: 5px 0 0 0;
  opacity: 0.8;
}

.welcome-text {
  font-size: 24px;
  font-weight: 600;
  color: var(--color-primary, #FFE55C) !important;
  margin: 0 0 8px 0;
  text-shadow: 0 0 8px rgba(var(--color-primary-rgb, 255, 229, 92), 0.2);
}

.subtitle {
  color: var(--color-text-muted, #718096) !important;
  font-size: 16px;
  margin: 0;
}

/* Form */
.login-form {
  margin-bottom: 30px;
}

.form-group {
  margin-bottom: 24px;
}

.form-label {
  display: flex;
  align-items: center;
  font-size: 14px;
  font-weight: 600;
  color: var(--color-text-primary, #4a5568) !important;
  margin-bottom: 8px;
}

.form-label i {
  margin-right: 8px;
  color: var(--color-primary, #FFE55C) !important;
}

.input-container {
  position: relative;
}

.form-input {
  width: 100%;
  padding: 16px 20px 16px 50px;
  border: 2px solid var(--color-border-secondary, rgba(255, 255, 255, 0.2)) !important;
  border-radius: 12px;
  font-size: 16px;
  background: var(--color-bg-secondary, rgba(255, 255, 255, 0.95)) !important;
  color: var(--color-text-primary, #000) !important;
  transition: all 0.3s ease;
  outline: none;
}

.form-input:focus {
  border-color: var(--color-primary, #FFE55C) !important;
  box-shadow: 0 0 0 3px rgba(var(--color-primary-rgb, 255, 229, 92), 0.1) !important;
  background: var(--color-bg-secondary, rgba(255, 255, 255, 1)) !important;
}

.form-input.error {
  border-color: var(--color-error, #e53e3e) !important;
  box-shadow: 0 0 0 3px rgba(var(--color-error-rgb, 229, 62, 62), 0.1) !important;
}

.input-icon {
  position: absolute;
  left: 18px;
  top: 50%;
  transform: translateY(-50%);
  color: var(--color-text-muted, #a0aec0) !important;
  font-size: 16px;
}

.password-toggle {
  position: absolute;
  right: 18px;
  top: 50%;
  transform: translateY(-50%);
  background: none;
  border: none;
  color: var(--color-text-muted, #a0aec0) !important;
  cursor: pointer;
  padding: 0;
  font-size: 16px;
  transition: color 0.3s ease;
}

.password-toggle:hover {
  color: var(--color-primary, #FFE55C) !important;
}

.password-toggle.active {
  color: var(--color-primary, #FFE55C) !important;
}

/* Form options */
.form-options {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24px;
  flex-wrap: wrap;
  gap: 10px;
}

.checkbox-container {
  display: flex;
  align-items: center;
  cursor: pointer;
  font-size: 14px;
  color: var(--color-text-primary, #4a5568) !important;
  user-select: none;
}

.checkbox-input {
  position: absolute;
  opacity: 0;
  cursor: pointer;
  height: 0;
  width: 0;
}

.checkmark {
  height: 18px;
  width: 18px;
  background-color: var(--color-bg-secondary, white) !important;
  border: 2px solid var(--color-border-secondary, #e2e8f0) !important;
  border-radius: 4px;
  margin-right: 8px;
  position: relative;
  transition: all 0.3s ease;
}

.checkbox-input:checked ~ .checkmark {
  background-color: var(--color-primary, #FFE55C) !important;
  border-color: var(--color-primary, #FFE55C) !important;
}

.checkmark:after {
  content: "";
  position: absolute;
  display: none;
  left: 5px;
  top: 2px;
  width: 4px;
  height: 8px;
  border: solid white;
  border-width: 0 2px 2px 0;
  transform: rotate(45deg);
}

.checkbox-input:checked ~ .checkmark:after {
  display: block;
}

.forgot-link {
  color: var(--color-primary, #FFE55C) !important;
  text-decoration: none;
  font-size: 14px;
  font-weight: 500;
  transition: color 0.3s ease;
}

.forgot-link:hover {
  color: var(--color-primary-dark, #00b894) !important;
  text-decoration: underline;
}

/* Error message */
.error-message {
  background: rgba(var(--color-error-rgb, 255, 68, 68), 0.1) !important;
  border: 1px solid var(--color-error, #c53030) !important;
  color: var(--color-error, #c53030) !important;
  padding: 12px 16px;
  border-radius: 8px;
  margin-bottom: 20px;
  display: flex;
  align-items: center;
  font-size: 14px;
}

.error-message i {
  margin-right: 8px;
}

/* Login button */
.login-button {
  width: 100%;
  padding: 16px;
  background: linear-gradient(135deg, var(--color-primary, #FFE55C), var(--color-primary-dark, #00b894)) !important;
  color: var(--color-bg-primary, white) !important;
  border: none;
  border-radius: 12px;
  font-size: 16px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  position: relative;
  overflow: hidden;
}

.login-button::before {
  content: '';
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
  transition: left 0.5s;
}

.login-button:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 10px 25px rgba(var(--color-primary-rgb, 255, 229, 92), 0.4) !important;
}

.login-button:hover:not(:disabled)::before {
  left: 100%;
}

.login-button:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

.loading-spinner {
  display: flex;
  align-items: center;
  gap: 8px;
}

/* Footer */
.login-footer {
  text-align: center;
  color: var(--color-text-muted, #718096) !important;
  font-size: 14px;
}

.signup-link {
  color: var(--color-primary, #FFE55C) !important;
  text-decoration: none;
  font-weight: 600;
}

.signup-link:hover {
  text-decoration: underline;
}

/* Trading Background Elements */
.trading-background {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  pointer-events: none;
  z-index: 1;
  overflow: hidden;
}

/* Stock Charts */
.stock-chart {
  position: absolute;
  display: flex;
  align-items: end;
  gap: 2px;
  opacity: 0.3;
}

.chart-1 {
  top: 15%;
  left: 5%;
  animation: slideRight 8s linear infinite;
}

.chart-2 {
  top: 45%;
  right: 10%;
  animation: slideLeft 10s linear infinite;
}

.chart-3 {
  bottom: 25%;
  left: 15%;
  animation: slideUp 12s linear infinite;
}

.candlestick {
  width: 4px;
  background: var(--color-primary, #FFE55C) !important;
  border-radius: 1px;
  position: relative;
}

.candlestick.up {
  background: var(--color-success, #FFE55C) !important;
  height: 20px;
}

.candlestick.down {
  background: var(--color-error, #ff6b6b) !important;
  height: 15px;
}

.candlestick::before {
  content: '';
  position: absolute;
  left: 50%;
  top: 0;
  width: 1px;
  height: 100%;
  background: inherit;
  transform: translateX(-50%);
}

/* Trading Indicators */
.trading-indicators {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
}

.indicator {
  position: absolute;
  opacity: 0.4;
}

.indicator-1 {
  top: 30%;
  left: 20%;
  animation: pulse 3s ease-in-out infinite;
}

.indicator-2 {
  top: 60%;
  right: 25%;
  animation: pulse 3s ease-in-out infinite 1s;
}

.indicator-3 {
  bottom: 40%;
  left: 30%;
  animation: pulse 3s ease-in-out infinite 2s;
}

.indicator .line {
  width: 40px;
  height: 2px;
  background: var(--color-primary, #FFE55C) !important;
  border-radius: 1px;
}

.indicator .dot {
  width: 6px;
  height: 6px;
  background: var(--color-primary, #FFE55C) !important;
  border-radius: 50%;
  position: absolute;
  right: 0;
  top: -2px;
}

/* Price Tickers */
.price-tickers {
  position: absolute;
  top: 10%;
  right: 5%;
  display: flex;
  flex-direction: column;
  gap: 15px;
  opacity: 0.6;
}

.ticker {
  background: rgba(0, 0, 0, 0.3);
  padding: 8px 12px;
  border-radius: 6px;
  display: flex;
  align-items: center;
  gap: 10px;
  font-size: 12px;
  font-weight: 600;
  backdrop-filter: blur(10px);
  animation: fadeInOut 4s ease-in-out infinite;
}

.ticker-1 { animation-delay: 0s; }
.ticker-2 { animation-delay: 1.5s; }
.ticker-3 { animation-delay: 3s; }

.ticker .symbol {
  color: #ffffff;
  font-weight: 700;
}

.ticker .price {
  color: #ffffff;
}

.ticker .price.up {
  color: var(--color-success, #FFE55C) !important;
}

.ticker .price.down {
  color: var(--color-error, #ff6b6b) !important;
}

.ticker .change {
  font-size: 10px;
  opacity: 0.8;
}

.ticker .change:not(:empty)::before {
  content: '';
  display: inline-block;
  width: 0;
  height: 0;
  margin-right: 4px;
}

.ticker .change:contains('+')::before {
  border-left: 3px solid transparent;
  border-right: 3px solid transparent;
  border-bottom: 5px solid var(--color-success, #FFE55C);
}

.ticker .change:contains('-')::before {
  border-left: 3px solid transparent;
  border-right: 3px solid transparent;
  border-top: 5px solid var(--color-error, #ff6b6b);
}

/* Floating Numbers */
.floating-numbers {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
}

.number {
  position: absolute;
  color: var(--color-primary, #FFE55C) !important;
  font-size: 14px;
  font-weight: 600;
  opacity: 0.7;
  animation: floatUp 6s linear infinite;
}

.number:nth-child(1) {
  top: 20%;
  left: 10%;
  animation-delay: 0s;
}

.number:nth-child(2) {
  top: 40%;
  right: 15%;
  animation-delay: 1s;
  color: #ff6b6b;
}

.number:nth-child(3) {
  top: 60%;
  left: 20%;
  animation-delay: 2s;
}

.number:nth-child(4) {
  bottom: 30%;
  right: 10%;
  animation-delay: 3s;
}

.number:nth-child(5) {
  bottom: 50%;
  left: 25%;
  animation-delay: 4s;
  color: #ff6b6b;
}

.number:nth-child(6) {
  top: 80%;
  right: 20%;
  animation-delay: 5s;
}

/* Animations */
@keyframes slideUp {
  from {
    opacity: 0;
    transform: translateY(30px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes slideRight {
  0% {
    transform: translateX(-100px);
    opacity: 0;
  }
  10% {
    opacity: 0.3;
  }
  90% {
    opacity: 0.3;
  }
  100% {
    transform: translateX(calc(100vw + 100px));
    opacity: 0;
  }
}

@keyframes slideLeft {
  0% {
    transform: translateX(100px);
    opacity: 0;
  }
  10% {
    opacity: 0.3;
  }
  90% {
    opacity: 0.3;
  }
  100% {
    transform: translateX(calc(-100vw - 100px));
    opacity: 0;
  }
}

@keyframes pulse {
  0%, 100% {
    opacity: 0.4;
    transform: scale(1);
  }
  50% {
    opacity: 0.8;
    transform: scale(1.1);
  }
}

@keyframes fadeInOut {
  0%, 100% {
    opacity: 0.6;
    transform: translateY(0);
  }
  50% {
    opacity: 1;
    transform: translateY(-5px);
  }
}

@keyframes floatUp {
  0% {
    opacity: 0;
    transform: translateY(20px);
  }
  20% {
    opacity: 0.7;
  }
  80% {
    opacity: 0.7;
  }
  100% {
    opacity: 0;
    transform: translateY(-50px);
  }
}

@keyframes float {
  0%, 100% {
    transform: translateY(0px);
  }
  50% {
    transform: translateY(-20px);
  }
}

/* Responsive design */
@media (max-width: 768px) {
  .login-container {
    padding: 15px;
  }
  
  .login-card {
    padding: 30px 25px;
    max-width: 100%;
  }
  
  .logo-text {
    font-size: 24px;
  }
  
  .welcome-text {
    font-size: 20px;
  }
  
  .form-options {
    flex-direction: column;
    align-items: flex-start;
    gap: 15px;
  }
  
  .stock-chart {
    opacity: 0.2;
  }
  
  .trading-indicators {
    opacity: 0.3;
  }
  
  .price-tickers {
    opacity: 0.4;
    right: 2%;
    top: 5%;
  }
  
  .floating-numbers {
    opacity: 0.5;
  }
  
  .ticker {
    padding: 6px 8px;
    font-size: 10px;
    gap: 6px;
  }
}

@media (max-width: 480px) {
  .login-card {
    padding: 25px 20px;
  }
  
  .logo-container {
    flex-direction: column;
    gap: 10px;
  }
  
  .logo-image {
    margin-right: 0;
  }
  
  .form-input {
    padding: 14px 18px 14px 45px;
    font-size: 15px;
  }
  
  .input-icon {
    left: 15px;
    font-size: 15px;
  }
  
  .password-toggle {
    right: 15px;
    font-size: 15px;
  }
  
  /* Mobile-optimized trading elements */
  .candlestick {
    width: 3px;
  }
  
  .candlestick.up {
    height: 15px;
  }
  
  .candlestick.down {
    height: 12px;
  }
  
  .indicator .line {
    width: 30px;
  }
  
  .number {
    font-size: 12px;
  }
  
  .chart-1 {
    top: 10%;
    left: 2%;
  }
  
  .chart-2 {
    top: 35%;
    right: 5%;
  }
  
  .chart-3 {
    bottom: 15%;
    left: 8%;
  }
}

/* Dark mode support */
@media (prefers-color-scheme: dark) {
  .login-card {
    background: rgba(26, 32, 44, 0.95);
    color: white;
  }
  
  .logo-text,
  .welcome-text {
    color: white;
  }
  
  .subtitle {
    color: #a0aec0;
  }
  
  .form-label {
    color: #e2e8f0;
  }
  
  .form-input {
    background: rgba(45, 55, 72, 0.8);
    border-color: #4a5568;
    color: white;
  }
  
  .form-input:focus {
    border-color: #667eea;
  }
  
  .checkmark {
    background-color: rgba(45, 55, 72, 0.8);
    border-color: #4a5568;
  }
  
  .error-message {
    background: rgba(197, 48, 48, 0.2);
    border-color: rgba(197, 48, 48, 0.3);
  }
}
</style>
