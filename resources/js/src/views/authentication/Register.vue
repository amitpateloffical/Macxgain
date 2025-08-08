<template>
  <div class="register-page">
    <!-- Trading Background Animation -->
    <div class="trading-background">
      <div class="stock-chart chart-1">
        <div class="candlestick up"></div>
        <div class="candlestick down"></div>
        <div class="candlestick up"></div>
        <div class="candlestick up"></div>
        <div class="candlestick down"></div>
      </div>
      <div class="stock-chart chart-2">
        <div class="candlestick down"></div>
        <div class="candlestick up"></div>
        <div class="candlestick up"></div>
        <div class="candlestick down"></div>
        <div class="candlestick up"></div>
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
    </div>

    <!-- Register Container -->
    <div class="register-container">
      <div class="register-background">
        <div class="register-card">
          <!-- Header -->
          <div class="register-header">
            <div class="logo-container">
              <img src="../assest/img/logo.png" alt="Macxgain Logo" class="logo-image" />
              <div class="logo-text">
                <h2>Macxgain</h2>
                <p>Smart Trading Solutions</p>
              </div>
            </div>
            <div class="welcome-text">
              <h1>Create Your Trading Account</h1>
              <p>Join thousands of successful traders and start your journey today</p>
            </div>
          </div>

          <!-- Register Form -->
          <form @submit.prevent="handleRegister" class="register-form">
            <!-- Account Type Selection -->
            <div class="form-group">
              <label class="form-label">Account Type</label>
              <div class="account-type-selector">
                <div 
                  class="account-type-option" 
                  :class="{ active: selectedAccountType === 'standard' }"
                  @click="selectedAccountType = 'standard'"
                >
                  <div class="option-icon">📊</div>
                  <div class="option-content">
                    <h4>Standard Account</h4>
                    <p>Perfect for beginners</p>
                  </div>
                </div>
                <div 
                  class="account-type-option" 
                  :class="{ active: selectedAccountType === 'commission' }"
                  @click="selectedAccountType = 'commission'"
                >
                  <div class="option-icon">💼</div>
                  <div class="option-content">
                    <h4>Commission Account</h4>
                    <p>Best for active traders</p>
                  </div>
                </div>
                <div 
                  class="account-type-option" 
                  :class="{ active: selectedAccountType === 'pro' }"
                  @click="selectedAccountType = 'pro'"
                >
                  <div class="option-icon">🏆</div>
                  <div class="option-content">
                    <h4>STP Pro Account</h4>
                    <p>For professionals</p>
                  </div>
                </div>
              </div>
            </div>

            <!-- Personal Information -->
            <div class="form-section">
              <h3 class="section-title">Personal Information</h3>
              
              <div class="form-row">
                <div class="form-group">
                  <label class="form-label">First Name</label>
                  <div class="input-container">
                    <i class="input-icon fas fa-user"></i>
                    <input 
                      type="text" 
                      v-model="form.firstName"
                      class="form-input" 
                      placeholder="Enter your first name"
                      required
                    />
                  </div>
                </div>
                
                <div class="form-group">
                  <label class="form-label">Last Name</label>
                  <div class="input-container">
                    <i class="input-icon fas fa-user"></i>
                    <input 
                      type="text" 
                      v-model="form.lastName"
                      class="form-input" 
                      placeholder="Enter your last name"
                      required
                    />
                  </div>
                </div>
              </div>

              <div class="form-group">
                <label class="form-label">Email Address</label>
                <div class="input-container">
                  <i class="input-icon fas fa-envelope"></i>
                  <input 
                    type="email" 
                    v-model="form.email"
                    class="form-input" 
                    placeholder="Enter your email address"
                    required
                  />
                </div>
              </div>

              <div class="form-group">
                <label class="form-label">Phone Number</label>
                <div class="input-container">
                  <i class="input-icon fas fa-phone"></i>
                  <input 
                    type="tel" 
                    v-model="form.phone"
                    class="form-input" 
                    placeholder="Enter your phone number"
                    required
                  />
                </div>
              </div>
            </div>

            <!-- Security Information -->
            <div class="form-section">
              <h3 class="section-title">Security Information</h3>
              
              <div class="form-group">
                <label class="form-label">Username</label>
                <div class="input-container">
                  <i class="input-icon fas fa-user-circle"></i>
                  <input 
                    type="text" 
                    v-model="form.username"
                    class="form-input" 
                    placeholder="Choose a username"
                    required
                  />
                </div>
              </div>

              <div class="form-group">
                <label class="form-label">Password</label>
                <div class="input-container">
                  <i class="input-icon fas fa-lock"></i>
                  <input 
                    :type="showPassword ? 'text' : 'password'" 
                    v-model="form.password"
                    class="form-input" 
                    placeholder="Create a strong password"
                    required
                  />
                  <button 
                    type="button" 
                    class="password-toggle"
                    @click="showPassword = !showPassword"
                  >
                    <i :class="showPassword ? 'fas fa-eye-slash' : 'fas fa-eye'"></i>
                  </button>
                </div>
              </div>

              <div class="form-group">
                <label class="form-label">Confirm Password</label>
                <div class="input-container">
                  <i class="input-icon fas fa-lock"></i>
                  <input 
                    :type="showConfirmPassword ? 'text' : 'password'" 
                    v-model="form.confirmPassword"
                    class="form-input" 
                    placeholder="Confirm your password"
                    required
                  />
                  <button 
                    type="button" 
                    class="password-toggle"
                    @click="showConfirmPassword = !showConfirmPassword"
                  >
                    <i :class="showConfirmPassword ? 'fas fa-eye-slash' : 'fas fa-eye'"></i>
                  </button>
                </div>
              </div>
            </div>

            <!-- Terms and Conditions -->
            <div class="form-group">
              <div class="checkbox-container">
                <input 
                  type="checkbox" 
                  id="terms" 
                  v-model="form.acceptTerms"
                  class="checkbox-input"
                  required
                />
                <label for="terms" class="checkmark"></label>
                <span class="checkbox-text">
                  I agree to the <a href="#" class="link">Terms & Conditions</a> and 
                  <a href="#" class="link">Privacy Policy</a>
                </span>
              </div>
            </div>

            <!-- Error Message -->
            <div v-if="errorMessage" class="error-message">
              <i class="fas fa-exclamation-circle"></i>
              {{ errorMessage }}
            </div>

            <!-- Register Button -->
            <button type="submit" class="register-button" :disabled="isLoading">
              <span v-if="!isLoading">Create Trading Account</span>
              <div v-else class="loading-spinner">
                <i class="fas fa-spinner fa-spin"></i>
                <span>Creating Account...</span>
              </div>
            </button>
          </form>

          <!-- Login Link -->
          <div class="register-footer">
            <p>Already have an account? <a href="/admin/login" class="login-link">Login Now</a></p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: "Register",
  data() {
    return {
      selectedAccountType: 'standard',
      showPassword: false,
      showConfirmPassword: false,
      isLoading: false,
      errorMessage: '',
      form: {
        firstName: '',
        lastName: '',
        email: '',
        phone: '',
        username: '',
        password: '',
        confirmPassword: '',
        acceptTerms: false
      }
    };
  },
  methods: {
    async handleRegister() {
      this.errorMessage = '';
      
      if (!this.validateForm()) {
        return;
      }
      
      this.isLoading = true;
      
      try {
        await new Promise(resolve => setTimeout(resolve, 2000));
        this.$toast.success('Account created successfully! Welcome to Macxgain.');
        setTimeout(() => {
          this.$router.push('/admin/login');
        }, 1500);
      } catch (error) {
        this.errorMessage = 'Registration failed. Please try again.';
        this.$toast.error('Registration failed. Please try again.');
      } finally {
        this.isLoading = false;
      }
    },
    
    validateForm() {
      if (this.form.password !== this.form.confirmPassword) {
        this.errorMessage = 'Passwords do not match.';
        return false;
      }
      
      if (!this.form.acceptTerms) {
        this.errorMessage = 'Please accept the Terms & Conditions.';
        return false;
      }
      
      return true;
    }
  }
};
</script>

<style scoped>
/* Register Page Styles */
.register-page {
  min-height: 100vh;
  background: linear-gradient(135deg, #0a0a1a 0%, #1a1a2e 50%, #16213e 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 2rem;
  position: relative;
  overflow: hidden;
}

/* Trading Background Animation */
.trading-background {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  pointer-events: none;
  z-index: 1;
}

.stock-chart {
  position: absolute;
  display: flex;
  gap: 2px;
  opacity: 0.1;
}

.chart-1 {
  top: 15%;
  left: 5%;
  animation: slideRight 20s linear infinite;
}

.chart-2 {
  top: 45%;
  right: 10%;
  animation: slideLeft 25s linear infinite;
}

.candlestick {
  width: 4px;
  background: #00ff88;
  border-radius: 1px;
}

.candlestick.up {
  height: 20px;
  background: #00ff88;
}

.candlestick.down {
  height: 15px;
  background: #ff4757;
}

.price-tickers {
  position: absolute;
  top: 10%;
  right: 5%;
  opacity: 0.2;
}

.ticker {
  background: rgba(0, 255, 136, 0.1);
  padding: 8px 12px;
  border-radius: 6px;
  margin-bottom: 8px;
  display: flex;
  gap: 8px;
  font-size: 12px;
  animation: slideLeft 15s linear infinite;
}

.ticker .symbol {
  font-weight: bold;
}

.ticker .price.up {
  color: #00ff88;
}

.ticker .price.down {
  color: #ff4757;
}

.ticker .change.up {
  color: #00ff88;
}

.ticker .change.down {
  color: #ff4757;
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

/* Register Container */
.register-container {
  position: relative;
  z-index: 10;
  width: 100%;
  max-width: 600px;
}

.register-background {
  background: rgba(255, 255, 255, 0.05);
  backdrop-filter: blur(20px);
  border-radius: 20px;
  border: 1px solid rgba(255, 255, 255, 0.1);
  padding: 3rem;
  box-shadow: 0 25px 50px rgba(0, 0, 0, 0.3);
}

.register-card {
  width: 100%;
}

/* Header */
.register-header {
  text-align: center;
  margin-bottom: 2.5rem;
}

.logo-container {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 1rem;
  margin-bottom: 2rem;
}

.logo-image {
  width: 60px;
  height: auto;
}

.logo-text h2 {
  color: #00ff88;
  font-size: 2rem;
  font-weight: bold;
  margin: 0;
}

.logo-text p {
  color: rgba(255, 255, 255, 0.7);
  margin: 0;
  font-size: 0.9rem;
}

.welcome-text h1 {
  font-size: 2.2rem;
  font-weight: bold;
  color: white;
  margin-bottom: 0.5rem;
}

.welcome-text p {
  color: rgba(255, 255, 255, 0.8);
  font-size: 1.1rem;
  margin: 0;
}

/* Account Type Selector */
.account-type-selector {
  display: grid;
  grid-template-columns: 1fr;
  gap: 1rem;
  margin-bottom: 2rem;
}

.account-type-option {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1.5rem;
  border: 2px solid rgba(255, 255, 255, 0.1);
  border-radius: 12px;
  cursor: pointer;
  transition: all 0.3s ease;
  background: rgba(255, 255, 255, 0.02);
}

.account-type-option:hover {
  border-color: rgba(0, 255, 136, 0.3);
  background: rgba(0, 255, 136, 0.05);
}

.account-type-option.active {
  border-color: #00ff88;
  background: rgba(0, 255, 136, 0.1);
}

.option-icon {
  font-size: 2rem;
}

.option-content h4 {
  color: white;
  margin: 0 0 0.25rem 0;
  font-size: 1.1rem;
}

.option-content p {
  color: rgba(255, 255, 255, 0.7);
  margin: 0;
  font-size: 0.9rem;
}

/* Form Sections */
.form-section {
  margin-bottom: 2.5rem;
}

.section-title {
  color: #00ff88;
  font-size: 1.3rem;
  font-weight: bold;
  margin-bottom: 1.5rem;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
  padding-bottom: 0.5rem;
}

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
}

.form-group {
  margin-bottom: 1.5rem;
}

.form-label {
  display: block;
  color: white;
  font-weight: 600;
  margin-bottom: 0.5rem;
  font-size: 0.95rem;
}

.input-container {
  position: relative;
}

.form-input {
  width: 100%;
  padding: 1rem 1rem 1rem 3rem;
  background: rgba(255, 255, 255, 0.05);
  border: 2px solid rgba(255, 255, 255, 0.1);
  border-radius: 10px;
  color: white;
  font-size: 1rem;
  transition: all 0.3s ease;
}

.form-input::placeholder {
  color: rgba(255, 255, 255, 0.5);
}

.form-input:focus {
  outline: none;
  border-color: #00ff88;
  background: rgba(255, 255, 255, 0.08);
}

.input-icon {
  position: absolute;
  left: 1rem;
  top: 50%;
  transform: translateY(-50%);
  color: rgba(255, 255, 255, 0.6);
  font-size: 1rem;
}

.password-toggle {
  position: absolute;
  right: 1rem;
  top: 50%;
  transform: translateY(-50%);
  background: none;
  border: none;
  color: rgba(255, 255, 255, 0.6);
  cursor: pointer;
  font-size: 1rem;
  transition: color 0.3s ease;
}

.password-toggle:hover {
  color: #00ff88;
}

/* Checkbox */
.checkbox-container {
  display: flex;
  align-items: flex-start;
  gap: 0.75rem;
  margin-bottom: 1rem;
}

.checkbox-input {
  display: none;
}

.checkmark {
  width: 20px;
  height: 20px;
  border: 2px solid rgba(255, 255, 255, 0.3);
  border-radius: 4px;
  position: relative;
  cursor: pointer;
  flex-shrink: 0;
  margin-top: 0.1rem;
}

.checkbox-input:checked + .checkmark {
  background: #00ff88;
  border-color: #00ff88;
}

.checkbox-input:checked + .checkmark::after {
  content: '✓';
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  color: #0a0a1a;
  font-weight: bold;
  font-size: 0.8rem;
}

.checkbox-text {
  color: rgba(255, 255, 255, 0.8);
  font-size: 0.9rem;
  line-height: 1.4;
}

.link {
  color: #00ff88;
  text-decoration: none;
  transition: color 0.3s ease;
}

.link:hover {
  color: #00d4aa;
}

/* Error Message */
.error-message {
  background: rgba(255, 71, 87, 0.1);
  border: 1px solid rgba(255, 71, 87, 0.3);
  color: #ff4757;
  padding: 1rem;
  border-radius: 8px;
  margin-bottom: 1rem;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

/* Register Button */
.register-button {
  width: 100%;
  padding: 1.25rem;
  background: linear-gradient(135deg, #00ff88 0%, #00d4aa 100%);
  color: #0a0a1a;
  border: none;
  border-radius: 12px;
  font-size: 1.1rem;
  font-weight: bold;
  cursor: pointer;
  transition: all 0.3s ease;
  margin-bottom: 1.5rem;
}

.register-button:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(0, 255, 136, 0.3);
}

.register-button:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

.loading-spinner {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
}

/* Footer */
.register-footer {
  text-align: center;
  color: rgba(255, 255, 255, 0.7);
}

.login-link {
  color: #00ff88;
  text-decoration: none;
  font-weight: 600;
  transition: color 0.3s ease;
}

.login-link:hover {
  color: #00d4aa;
}

/* Responsive Design */
@media (max-width: 768px) {
  .register-page {
    padding: 1rem;
  }
  
  .register-background {
    padding: 2rem;
  }
  
  .welcome-text h1 {
    font-size: 1.8rem;
  }
  
  .form-row {
    grid-template-columns: 1fr;
  }
  
  .account-type-selector {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 480px) {
  .register-background {
    padding: 1.5rem;
  }
  
  .welcome-text h1 {
    font-size: 1.5rem;
  }
  
  .welcome-text p {
    font-size: 1rem;
  }
  
  .logo-text h2 {
    font-size: 1.5rem;
  }
  
  .form-input {
    padding: 0.875rem 0.875rem 0.875rem 2.5rem;
    font-size: 0.95rem;
  }
  
  .input-icon {
    left: 0.875rem;
    font-size: 0.9rem;
  }
  
  .password-toggle {
    right: 0.875rem;
    font-size: 0.9rem;
  }
  
  .register-button {
    padding: 1rem;
    font-size: 1rem;
  }
}
</style>

<style scoped>
/* Register Page Styles */
.register-page {
  min-height: 100vh;
  background: linear-gradient(135deg, #0a0a1a 0%, #1a1a2e 50%, #16213e 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 2rem;
  position: relative;
  overflow: hidden;
}

/* Trading Background Animation */
.trading-background {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  pointer-events: none;
  z-index: 1;
}

.stock-chart {
  position: absolute;
  display: flex;
  gap: 2px;
  opacity: 0.1;
}

.chart-1 {
  top: 15%;
  left: 5%;
  animation: slideRight 20s linear infinite;
}

.chart-2 {
  top: 45%;
  right: 10%;
  animation: slideLeft 25s linear infinite;
}

.chart-3 {
  bottom: 20%;
  left: 15%;
  animation: slideRight 30s linear infinite;
}

.candlestick {
  width: 4px;
  background: #00ff88;
  border-radius: 1px;
}

.candlestick.up {
  height: 20px;
  background: #00ff88;
}

.candlestick.down {
  height: 15px;
  background: #ff4757;
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
  background: #00ff88;
  margin-right: 8px;
}

.indicator .dot {
  width: 6px;
  height: 6px;
  background: #00ff88;
  border-radius: 50%;
  animation: pulse 2s ease-in-out infinite;
}

.price-tickers {
  position: absolute;
  top: 10%;
  right: 5%;
  opacity: 0.2;
}

.ticker {
  background: rgba(0, 255, 136, 0.1);
  padding: 8px 12px;
  border-radius: 6px;
  margin-bottom: 8px;
  display: flex;
  gap: 8px;
  font-size: 12px;
  animation: slideLeft 15s linear infinite;
}

.ticker .symbol {
  font-weight: bold;
}

.ticker .price.up {
  color: #00ff88;
}

.ticker .price.down {
  color: #ff4757;
}

.ticker .change.up {
  color: #00ff88;
}

.ticker .change.down {
  color: #ff4757;
}

.floating-numbers {
  position: absolute;
  top: 60%;
  left: 10%;
  opacity: 0.15;
}

.number {
  font-size: 14px;
  color: #00ff88;
  margin-bottom: 10px;
  animation: floatUp 8s ease-in-out infinite;
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
  50% { transform: translateY(-20px); }
}

/* Register Container */
.register-container {
  position: relative;
  z-index: 10;
  width: 100%;
  max-width: 600px;
}

.register-background {
  background: rgba(255, 255, 255, 0.05);
  backdrop-filter: blur(20px);
  border-radius: 20px;
  border: 1px solid rgba(255, 255, 255, 0.1);
  padding: 3rem;
  box-shadow: 0 25px 50px rgba(0, 0, 0, 0.3);
}

.register-card {
  width: 100%;
}

/* Header */
.register-header {
  text-align: center;
  margin-bottom: 2.5rem;
}

.logo-container {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 1rem;
  margin-bottom: 2rem;
}

.logo-image {
  width: 60px;
  height: auto;
}

.logo-text h2 {
  color: #00ff88;
  font-size: 2rem;
  font-weight: bold;
  margin: 0;
}

.logo-text p {
  color: rgba(255, 255, 255, 0.7);
  margin: 0;
  font-size: 0.9rem;
}

.welcome-text h1 {
  font-size: 2.2rem;
  font-weight: bold;
  color: white;
  margin-bottom: 0.5rem;
}

.welcome-text p {
  color: rgba(255, 255, 255, 0.8);
  font-size: 1.1rem;
  margin: 0;
}

/* Account Type Selector */
.account-type-selector {
  display: grid;
  grid-template-columns: 1fr;
  gap: 1rem;
  margin-bottom: 2rem;
}

.account-type-option {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1.5rem;
  border: 2px solid rgba(255, 255, 255, 0.1);
  border-radius: 12px;
  cursor: pointer;
  transition: all 0.3s ease;
  background: rgba(255, 255, 255, 0.02);
}

.account-type-option:hover {
  border-color: rgba(0, 255, 136, 0.3);
  background: rgba(0, 255, 136, 0.05);
}

.account-type-option.active {
  border-color: #00ff88;
  background: rgba(0, 255, 136, 0.1);
}

.option-icon {
  font-size: 2rem;
}

.option-content h4 {
  color: white;
  margin: 0 0 0.25rem 0;
  font-size: 1.1rem;
}

.option-content p {
  color: rgba(255, 255, 255, 0.7);
  margin: 0;
  font-size: 0.9rem;
}

/* Form Sections */
.form-section {
  margin-bottom: 2.5rem;
}

.section-title {
  color: #00ff88;
  font-size: 1.3rem;
  font-weight: bold;
  margin-bottom: 1.5rem;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
  padding-bottom: 0.5rem;
}

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
}

.form-group {
  margin-bottom: 1.5rem;
}

.form-label {
  display: block;
  color: white;
  font-weight: 600;
  margin-bottom: 0.5rem;
  font-size: 0.95rem;
}

.input-container {
  position: relative;
}

.form-input {
  width: 100%;
  padding: 1rem 1rem 1rem 3rem;
  background: rgba(255, 255, 255, 0.05);
  border: 2px solid rgba(255, 255, 255, 0.1);
  border-radius: 10px;
  color: white;
  font-size: 1rem;
  transition: all 0.3s ease;
}

.form-input::placeholder {
  color: rgba(255, 255, 255, 0.5);
}

.form-input:focus {
  outline: none;
  border-color: #00ff88;
  background: rgba(255, 255, 255, 0.08);
}

.input-icon {
  position: absolute;
  left: 1rem;
  top: 50%;
  transform: translateY(-50%);
  color: rgba(255, 255, 255, 0.6);
  font-size: 1rem;
}

.password-toggle {
  position: absolute;
  right: 1rem;
  top: 50%;
  transform: translateY(-50%);
  background: none;
  border: none;
  color: rgba(255, 255, 255, 0.6);
  cursor: pointer;
  font-size: 1rem;
  transition: color 0.3s ease;
}

.password-toggle:hover {
  color: #00ff88;
}

/* Password Strength */
.password-strength {
  margin-top: 0.5rem;
}

.strength-bar {
  width: 100%;
  height: 4px;
  background: rgba(255, 255, 255, 0.1);
  border-radius: 2px;
  overflow: hidden;
  margin-bottom: 0.25rem;
}

.strength-fill {
  height: 100%;
  background: linear-gradient(90deg, #ff4757 0%, #ffa502 50%, #00ff88 100%);
  transition: width 0.3s ease;
}

.strength-text {
  font-size: 0.8rem;
  color: rgba(255, 255, 255, 0.7);
}

/* Checkbox */
.checkbox-container {
  display: flex;
  align-items: flex-start;
  gap: 0.75rem;
  margin-bottom: 1rem;
}

.checkbox-input {
  display: none;
}

.checkmark {
  width: 20px;
  height: 20px;
  border: 2px solid rgba(255, 255, 255, 0.3);
  border-radius: 4px;
  position: relative;
  cursor: pointer;
  flex-shrink: 0;
  margin-top: 0.1rem;
}

.checkbox-input:checked + .checkmark {
  background: #00ff88;
  border-color: #00ff88;
}

.checkbox-input:checked + .checkmark::after {
  content: '✓';
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  color: #0a0a1a;
  font-weight: bold;
  font-size: 0.8rem;
}

.checkbox-text {
  color: rgba(255, 255, 255, 0.8);
  font-size: 0.9rem;
  line-height: 1.4;
}

.link {
  color: #00ff88;
  text-decoration: none;
  transition: color 0.3s ease;
}

.link:hover {
  color: #00d4aa;
}

/* Error Message */
.error-message {
  background: rgba(255, 71, 87, 0.1);
  border: 1px solid rgba(255, 71, 87, 0.3);
  color: #ff4757;
  padding: 1rem;
  border-radius: 8px;
  margin-bottom: 1rem;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

/* Register Button */
.register-button {
  width: 100%;
  padding: 1.25rem;
  background: linear-gradient(135deg, #00ff88 0%, #00d4aa 100%);
  color: #0a0a1a;
  border: none;
  border-radius: 12px;
  font-size: 1.1rem;
  font-weight: bold;
  cursor: pointer;
  transition: all 0.3s ease;
  margin-bottom: 1.5rem;
}

.register-button:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(0, 255, 136, 0.3);
}

.register-button:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

.loading-spinner {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
}

/* Footer */
.register-footer {
  text-align: center;
  color: rgba(255, 255, 255, 0.7);
}

.login-link {
  color: #00ff88;
  text-decoration: none;
  font-weight: 600;
  transition: color 0.3s ease;
}

.login-link:hover {
  color: #00d4aa;
}

/* Responsive Design */
@media (max-width: 768px) {
  .register-page {
    padding: 1rem;
  }
  
  .register-background {
    padding: 2rem;
  }
  
  .welcome-text h1 {
    font-size: 1.8rem;
  }
  
  .form-row {
    grid-template-columns: 1fr;
  }
  
  .account-type-selector {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 480px) {
  .register-background {
    padding: 1.5rem;
  }
  
  .welcome-text h1 {
    font-size: 1.5rem;
  }
  
  .welcome-text p {
    font-size: 1rem;
  }
  
  .logo-text h2 {
    font-size: 1.5rem;
  }
  
  .form-input {
    padding: 0.875rem 0.875rem 0.875rem 2.5rem;
    font-size: 0.95rem;
  }
  
  .input-icon {
    left: 0.875rem;
    font-size: 0.9rem;
  }
  
  .password-toggle {
    right: 0.875rem;
    font-size: 0.9rem;
  }
  
  .register-button {
    padding: 1rem;
    font-size: 1rem;
  }
}
</style>
