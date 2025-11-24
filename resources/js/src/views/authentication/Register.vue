<template>
  <div class="register-container">
    <!-- Background with gradient -->
    <div class="register-background">
      <div class="background-pattern"></div>
    </div>

    <!-- Main register card -->
    <div class="register-card">
      <!-- Logo and header -->
      <div class="register-header">
        <div class="logo-container">
          <div class="logo-image">
            <img :src="brandConfig.logoPath" :alt="brandConfig.companyName + ' Logo'" class="logo" />
          </div>
          <h1 class="brand-name">{{ brandConfig.companyName }}</h1>
          <p class="tagline">{{ brandConfig.tagline }}</p>
        </div>
        <h2 class="welcome-text">Create Account</h2>
        <p class="subtitle">Join thousands of successful traders</p>
      </div>

      <!-- Register form -->
      <form @submit.prevent="handleRegister" class="register-form">
        <!-- Name fields in row -->
        <div class="form-group">
          <div class="form-group">
            <label for="name" class="form-label">
              <i class="fas fa-user"></i>
              Full Name
            </label>
            <div class="input-container">
              <input
                id="name"
                type="text"
                v-model="form.name"
                required
                placeholder="Full name"
                class="form-input"
                :class="{ 'error': errorMessage && !form.name }"
              />
              <i class="fas fa-user input-icon"></i>
            </div>
          </div>
        </div>

        <!-- Email field -->
        <div class="form-group">
          <label for="email" class="form-label">
            <i class="fas fa-envelope"></i>
            Email Address
          </label>
          <div class="input-container">
            <input
              id="email"
              type="email"
              v-model="form.email"
              required
              placeholder="Enter your email"
              class="form-input"
              :class="{ 'error': errorMessage && !form.email }"
            />
            <i class="fas fa-envelope input-icon"></i>
          </div>
        </div>

        <!-- Phone field -->
        <div class="form-group">
          <label for="phone" class="form-label">
            <i class="fas fa-phone"></i>
            Phone Number
          </label>
          <div class="input-container">
            <input
              id="phone"
              type="tel"
              v-model="form.phone"
              required
              placeholder="Enter your phone"
              class="form-input"
              :class="{ 'error': errorMessage && !form.phone }"
            />
            <i class="fas fa-phone input-icon"></i>
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
              :type="showPassword ? 'text' : 'password'"
              v-model="form.password"
              required
              placeholder="Create password"
              class="form-input"
              :class="{ 'error': errorMessage && !form.password }"
            />
            <i class="fas fa-lock input-icon"></i>
            <button
              type="button"
              @click="showPassword = !showPassword"
              class="password-toggle"
              :class="{ 'active': showPassword }"
            >
              <i :class="showPassword ? 'fas fa-eye-slash' : 'fas fa-eye'"></i>
            </button>
          </div>
        </div>

        <!-- Confirm Password field -->
        <div class="form-group">
          <label for="confirmPassword" class="form-label">
            <i class="fas fa-lock"></i>
            Confirm Password
          </label>
          <div class="input-container">
            <input
              id="confirmPassword"
              :type="showConfirmPassword ? 'text' : 'password'"
              v-model="form.confirmPassword"
              required
              placeholder="Confirm password"
              class="form-input"
              :class="{ 'error': errorMessage && !form.confirmPassword }"
            />
            <i class="fas fa-lock input-icon"></i>
            <button
              type="button"
              @click="showConfirmPassword = !showConfirmPassword"
              class="password-toggle"
              :class="{ 'active': showConfirmPassword }"
            >
              <i :class="showConfirmPassword ? 'fas fa-eye-slash' : 'fas fa-eye'"></i>
            </button>
          </div>
        </div>

        <!-- Terms and conditions -->
        <div class="form-options">
          <label class="checkbox-container">
            <input
              type="checkbox"
              v-model="form.acceptTerms"
              class="checkbox-input"
              required
            />
            <span class="checkmark"></span>
            I agree to the <router-link to="#" class="terms-link">Terms & Conditions</router-link>
          </label>
        </div>

        <!-- Error message -->
        <div v-if="errorMessage" class="error-message">
          <i class="fas fa-exclamation-circle"></i>
          {{ errorMessage }}
        </div>

        <!-- Submit button -->
        <button
          type="submit"
          class="register-button"
          :disabled="isLoading"
        >
          <span v-if="!isLoading">
            <i class="fas fa-user-plus"></i>
            Create Account
          </span>
          <span v-else class="loading-spinner">
            <i class="fas fa-spinner fa-spin"></i>
            Creating Account...
          </span>
        </button>
      </form>

      <!-- Footer -->
      <div class="register-footer">
        <p>Already have an account? <router-link to="/login" class="login-link">Sign In</router-link></p>
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
        <div class="indicator">
          <div class="line"></div>
          <div class="dot"></div>
        </div>
        <div class="indicator">
          <div class="line"></div>
          <div class="dot"></div>
        </div>
        <div class="indicator">
          <div class="line"></div>
          <div class="dot"></div>
        </div>
      </div>
      
      <div class="price-tickers">
        <div class="ticker">
          <span class="symbol">AAPL</span>
          <span class="price up">$185.42</span>
          <span class="change up">+2.3%</span>
        </div>
        <div class="ticker">
          <span class="symbol">TSLA</span>
          <span class="price down">$248.50</span>
          <span class="change down">-1.2%</span>
        </div>
        <div class="ticker">
          <span class="symbol">NVDA</span>
          <span class="price up">$485.09</span>
          <span class="change up">+3.8%</span>
        </div>
      </div>
      
      <div class="floating-numbers">
        <div class="number">+15.2%</div>
        <div class="number">$2.4M</div>
        <div class="number">+8.7%</div>
        <div class="number">$1.8M</div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue'
import { useRouter } from 'vue-router'
import { toast } from 'vue3-toastify'
import axios from "@axios";
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

const router = useRouter()

// Form data
const form = ref({
  name: '',
  email: '',
  phone: '',
  password: '',
  confirmPassword: '',
  acceptTerms: false
})

// Component state
const showPassword = ref(false)
const showConfirmPassword = ref(false)
const isLoading = ref(false)
const errorMessage = ref('')

// Form validation
const validateForm = () => {
  if (form.value.password !== form.value.confirmPassword) {
    errorMessage.value = 'Passwords do not match.'
    return false
  }
  
  // if (!form.value.acceptTerms) {
  //   errorMessage.value = 'Please accept the Terms & Conditions.'
  //   return false
  // }
  
  if (form.value.password.length < 6) {
    errorMessage.value = 'Password must be at least 6 characters long.'
    return false
  }
  
  return true
}

// Register function
const handleRegister = async () => {
  errorMessage.value = ''
  
  if (!validateForm()) {
    return
  }
  
  isLoading.value = true
  
  try {
    const response = await axios.post('/register', form.value)
    
    toast.success(`Account created successfully! Welcome to ${brandConfig.companyName}.`, {
      autoClose: 2000,
      position: "top-right"
    })
    
    // Redirect to login page
    setTimeout(() => {
      router.push('/login')
    }, 1500)
    
  } catch (error) {
    console.log('Registration error:', error.response?.data)
    
    // Handle specific error types
    if (error.response?.data?.error_type === 'email_exists') {
      errorMessage.value = 'Email already registered. Please use a different email.'
      toast.error('Email already registered. Please use a different email.', {
        autoClose: 4000,
        position: "top-right"
      })
    } else if (error.response?.data?.error_type === 'phone_exists') {
      errorMessage.value = 'Mobile number already registered. Please use a different mobile number.'
      toast.error('Mobile number already registered. Please use a different mobile number.', {
        autoClose: 4000,
        position: "top-right"
      })
    } else if (error.response?.data?.message) {
      errorMessage.value = error.response.data.message
      toast.error(error.response.data.message, {
        autoClose: 4000,
        position: "top-right"
      })
    } else {
      errorMessage.value = 'Registration failed. Please try again.'
      toast.error('Registration failed. Please try again.', {
        autoClose: 3000,
        position: "top-right"
      })
    }
  } finally {
    isLoading.value = false
  }
}
</script>

<style scoped>
/* Main container - matches login page */
.register-container {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  position: relative;
  overflow: hidden;
  background: linear-gradient(135deg, var(--color-bg-tertiary, #1a1a2e) 0%, var(--color-bg-quaternary, #16213e) 50%, var(--color-bg-primary, #0f3460) 100%);
  padding: 20px;
}

/* Background with pattern - matches login page */
.register-background {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: linear-gradient(135deg, var(--color-bg-tertiary, #1a1a2e) 0%, var(--color-bg-quaternary, #16213e) 50%, var(--color-bg-primary, #0f3460) 100%);
  z-index: 1;
}

.background-pattern {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-image: 
    radial-gradient(circle at 20% 50%, rgba(var(--color-primary-rgb, 255, 215, 0), 0.02) 0%, transparent 50%),
    radial-gradient(circle at 80% 20%, rgba(var(--color-primary-rgb, 255, 215, 0), 0.02) 0%, transparent 50%),
    radial-gradient(circle at 40% 80%, rgba(var(--color-primary-rgb, 255, 215, 0), 0.02) 0%, transparent 50%);
}

/* Main register card - matches login page */
.register-card {
  position: relative;
  z-index: 10;
  width: 100%;
  max-width: 450px;
  background: rgba(255, 255, 255, 0.05);
  backdrop-filter: blur(20px);
  border-radius: 20px;
  border: 1px solid rgba(255, 255, 255, 0.1);
  padding: 2.5rem;
  box-shadow: 0 25px 50px rgba(0, 0, 0, 0.3);
}

/* Header - matches login page */
.register-header {
  text-align: center;
  margin-bottom: 2rem;
}

.logo-container {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 12px;
  margin-bottom: 1.5rem;
}

.logo {
  width: 50px;
  height: auto;
}

.brand-name {
  font-size: 1.8rem;
  font-weight: bold;
  color: var(--color-primary-light, #FFE55C);
  margin: 0;
}

.tagline {
  color: rgba(255, 255, 255, 0.7);
  font-size: 0.9rem;
  margin: 0.25rem 0 0 0;
}

.welcome-text {
  font-size: 1.8rem;
  font-weight: bold;
  color: white;
  margin: 0.5rem 0;
}

.subtitle {
  color: rgba(255, 255, 255, 0.8);
  font-size: 1rem;
  margin: 0;
}

/* Form styling - matches login page */
.register-form {
  width: 100%;
}

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
  margin-bottom: 1rem;
}

.form-group {
  margin-bottom: 1.5rem;
}

.form-label {
  display: flex;
  align-items: center;
  gap: 8px;
  color: white;
  font-weight: 600;
  margin-bottom: 0.5rem;
  font-size: 14px;
}

.input-container {
  position: relative;
}

.form-input {
  width: 100%;
  padding: 16px 16px 16px 48px;
  background: rgba(255, 255, 255, 0.05);
  border: 2px solid rgba(255, 255, 255, 0.1);
  border-radius: 12px;
  color: white;
  font-size: 16px;
  transition: all 0.3s ease;
  box-sizing: border-box;
}

.form-input::placeholder {
  color: rgba(255, 255, 255, 0.5);
}

.form-input:focus {
  outline: none;
  border-color: var(--color-primary-light, #FFE55C);
  background: rgba(255, 255, 255, 0.08);
}

.form-input.error {
  border-color: #e53e3e;
  background: rgba(229, 62, 62, 0.1);
}

.input-icon {
  position: absolute;
  left: 16px;
  top: 50%;
  transform: translateY(-50%);
  color: rgba(255, 255, 255, 0.6);
  font-size: 16px;
}

.password-toggle {
  position: absolute;
  right: 16px;
  top: 50%;
  transform: translateY(-50%);
  background: none;
  border: none;
  color: rgba(255, 255, 255, 0.6);
  cursor: pointer;
  font-size: 16px;
  transition: color 0.3s ease;
  padding: 4px;
}

.password-toggle:hover {
  color: var(--color-primary-light, #FFE55C);
}

.password-toggle.active {
  color: var(--color-primary-light, #FFE55C);
}

/* Form options - matches login page */
.form-options {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
  flex-wrap: wrap;
  gap: 1rem;
}

.checkbox-container {
  position: relative;
  display: flex;
  align-items: center;
  cursor: pointer;
  user-select: none;
  font-size: 14px;
  color: rgba(255, 255, 255, 0.8);
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
  background-color: rgba(255, 255, 255, 0.1);
  border: 2px solid rgba(255, 255, 255, 0.3);
  border-radius: 4px;
  margin-right: 12px;
  position: relative;
  transition: all 0.3s ease;
}

.checkbox-container:hover .checkmark {
  border-color: var(--color-primary-light, #FFE55C);
}

.checkbox-input:checked ~ .checkmark {
  background-color: var(--color-primary-light, #FFE55C);
  border-color: var(--color-primary-light, #FFE55C);
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

.terms-link {
  color: var(--color-primary-light, #FFE55C);
  text-decoration: none;
  font-weight: 500;
  transition: color 0.3s ease;
}

.terms-link:hover {
  color: var(--color-primary, #00b894);
  text-decoration: underline;
}

/* Error message - matches login page */
.error-message {
  background: #fed7d7;
  border: 1px solid #feb2b2;
  color: #c53030;
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

/* Register button - matches login page */
.register-button {
  width: 100%;
  padding: 16px;
  background: linear-gradient(135deg, var(--color-primary-light, #FFE55C), var(--color-primary, #00b894));
  color: var(--color-text-primary, white);
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
  margin-bottom: 1.5rem;
}

.register-button::before {
  content: '';
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
  transition: left 0.5s;
}

.register-button:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 10px 25px rgba(var(--color-primary-light-rgb, 255, 229, 92), 0.4);
}

.register-button:hover:not(:disabled)::before {
  left: 100%;
}

.register-button:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

.loading-spinner {
  display: flex;
  align-items: center;
  gap: 8px;
}

/* Footer - matches login page */
.register-footer {
  text-align: center;
  color: #718096;
  font-size: 14px;
}

.login-link {
  color: var(--color-primary-light, #FFE55C);
  text-decoration: none;
  font-weight: 600;
}

.login-link:hover {
  text-decoration: underline;
}

/* Trading Background Elements - matches login page */
.trading-background {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  pointer-events: none;
  z-index: 2;
}

.stock-chart {
  position: absolute;
  display: flex;
  gap: 2px;
  opacity: 0.1;
}

.chart-1 {
  top: 20%;
  left: 10%;
  animation: slideRight 25s linear infinite;
}

.chart-2 {
  top: 60%;
  right: 15%;
  animation: slideLeft 30s linear infinite;
}

.chart-3 {
  bottom: 20%;
  left: 20%;
  animation: slideRight 35s linear infinite;
}

.candlestick {
  width: 4px;
  border-radius: 1px;
  animation: pulse 3s ease-in-out infinite;
}

.candlestick.up {
  height: 20px;
  background: var(--color-primary-light, #FFE55C);
}

.candlestick.down {
  height: 15px;
  background: #e53e3e;
}

.trading-indicators {
  position: absolute;
  top: 30%;
  right: 20%;
  opacity: 0.1;
}

.indicator {
  display: flex;
  align-items: center;
  margin-bottom: 10px;
}

.indicator .line {
  width: 40px;
  height: 2px;
  background: var(--color-primary-light, #FFE55C);
  margin-right: 8px;
}

.indicator .dot {
  width: 6px;
  height: 6px;
  background: var(--color-primary-light, #FFE55C);
  border-radius: 50%;
  animation: pulse 2s ease-in-out infinite;
}

.price-tickers {
  position: absolute;
  top: 15%;
  right: 5%;
  opacity: 0.15;
}

.ticker {
  background: rgba(255, 229, 92, 0.1);
  padding: 6px 10px;
  border-radius: 4px;
  margin-bottom: 6px;
  display: flex;
  gap: 6px;
  font-size: 11px;
  animation: slideLeft 20s linear infinite;
}

.ticker .symbol {
  font-weight: bold;
  color: var(--color-primary-light, #FFE55C);
}

.ticker .price.up {
  color: var(--color-primary-light, #FFE55C);
}

.ticker .price.down {
  color: #e53e3e;
}

.ticker .change.up {
  color: var(--color-primary-light, #FFE55C);
}

.ticker .change.down {
  color: #e53e3e;
}

.floating-numbers {
  position: absolute;
  top: 50%;
  left: 5%;
  opacity: 0.1;
}

.number {
  font-size: 12px;
  color: var(--color-primary-light, #FFE55C);
  margin-bottom: 8px;
  animation: floatUp 6s ease-in-out infinite;
}

/* Animations */
@keyframes slideRight {
  0% { transform: translateX(-100px); opacity: 0; }
  10% { opacity: 0.1; }
  90% { opacity: 0.1; }
  100% { transform: translateX(calc(100vw + 100px)); opacity: 0; }
}

@keyframes slideLeft {
  0% { transform: translateX(100px); opacity: 0; }
  10% { opacity: 0.1; }
  90% { opacity: 0.1; }
  100% { transform: translateX(calc(-100vw - 100px)); opacity: 0; }
}

@keyframes pulse {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.5; }
}

@keyframes floatUp {
  0%, 100% { transform: translateY(0); }
  50% { transform: translateY(-15px); }
}

/* Responsive Design */
@media (max-width: 768px) {
  .register-container {
    padding: 15px;
  }
  
  .register-card {
    padding: 2rem;
    max-width: 400px;
  }
  
  .welcome-text {
    font-size: 1.5rem;
  }
  
  .form-row {
    grid-template-columns: 1fr;
    gap: 0;
  }
  
  .brand-name {
    font-size: 1.5rem;
  }
}

@media (max-width: 480px) {
  .register-card {
    padding: 1.5rem;
    border-radius: 15px;
  }
  
  .welcome-text {
    font-size: 1.3rem;
  }
  
  .subtitle {
    font-size: 0.9rem;
  }
  
  .form-input {
    padding: 14px 14px 14px 44px;
    font-size: 15px;
  }
  
  .input-icon {
    left: 14px;
    font-size: 14px;
  }
  
  .password-toggle {
    right: 14px;
    font-size: 14px;
  }
  
  .register-button {
    padding: 14px;
    font-size: 15px;
  }
  
  .form-options {
    flex-direction: column;
    align-items: flex-start;
    gap: 0.5rem;
  }
}
</style>