<template>
  <b-container fluid class="main-content">
    <b-card class="profile-card shadow-lg border-0">
      <b-row>
        <!-- Left Side - Icon and Info -->
        <b-col md="4" class="text-center border-end border-gray-700 pe-4">
          <div class="password-icon-container mb-4">
            <div class="password-icon">
              <i class="bi bi-shield-lock"></i>
            </div>
          </div>
          
          <div class="mt-4">
            <h5 class="fw-bold text-primary">Password Security</h5>
            <p class="text-muted">
              <strong>Keep your account safe</strong>
            </p>
            <p class="text-muted">
              <strong>Use a strong password</strong>
            </p>
            
            <!-- Security Tips Card -->
            <div class="security-tips-card mt-3">
              <div class="tips-header">
                <i class="bi bi-lightbulb me-2"></i>
                <span>Security Tips</span>
              </div>
              <div class="tips-content">
                <ul class="tips-list">
                  <li>Use at least 8 characters</li>
                  <li>Include numbers & symbols</li>
                  <li>Don't reuse old passwords</li>
                </ul>
              </div>
            </div>
          </div>
        </b-col>

        <!-- Right Side - Password Form -->
        <b-col md="8" class="ps-4">
          <h4 class="fw-bold mb-4 text-primary">
            <i class="bi bi-key me-2"></i>Change Password
          </h4>
          
          <b-form @submit.prevent="changePassword">
            <!-- Current Password -->
            <b-form-group label="Current Password" label-class="fw-semibold" class="mb-3">
              <b-input-group class="input-group-merge">
                <b-form-input
                  :type="showOldPassword ? 'text' : 'password'"
                  v-model="userpassword.current_password"
                  id="current_password"
                  @input="RemoveError('current_password')"
                  :state="hasErrors('current_password') ? false : null"
                  placeholder="Enter your current password"
                  autocomplete="current-password"
                  class="form-control"
                />
                <b-input-group-append is-text>
                  <button type="button" @click="toggleOldPasswordView" class="toggle-password-btn">
                    <i :class="showOldPassword ? 'bi bi-eye-slash' : 'bi bi-eye'"></i>
                  </button>
                </b-input-group-append>
              </b-input-group>
              <div class="text-danger small" v-if="hasErrors('current_password')">
                {{ getErrors("current_password") }}
              </div>
            </b-form-group>

            <!-- New Password -->
            <b-form-group label="New Password" label-class="fw-semibold" class="mb-3">
              <b-input-group class="input-group-merge">
                <b-form-input
                  :type="showNewPassword ? 'text' : 'password'"
                  v-model="userpassword.new_password"
                  id="new_password"
                  @input="RemoveError('new_password')"
                  :state="hasErrors('new_password') ? false : null"
                  placeholder="Enter your new password"
                  autocomplete="new-password"
                  class="form-control"
                />
                <b-input-group-append is-text>
                  <button type="button" @click="toggleNewPasswordView" class="toggle-password-btn">
                    <i :class="showNewPassword ? 'bi bi-eye-slash' : 'bi bi-eye'"></i>
                  </button>
                </b-input-group-append>
              </b-input-group>
              <div class="text-danger small" v-if="hasErrors('new_password')">
                {{ getErrors("new_password") }}
              </div>
            </b-form-group>

            <!-- Confirm Password -->
            <b-form-group label="Confirm New Password" label-class="fw-semibold" class="mb-4">
              <b-input-group class="input-group-merge">
                <b-form-input
                  :type="showConfirmPassword ? 'text' : 'password'"
                  v-model="userpassword.new_password_confirmation"
                  id="new_password_confirmation"
                  @input="RemoveError('new_password_confirmation')"
                  :state="hasErrors('new_password_confirmation') ? false : null"
                  placeholder="Confirm your new password"
                  autocomplete="new-password"
                  class="form-control"
                />
                <b-input-group-append is-text>
                  <button type="button" @click="toggleConfirmPasswordView" class="toggle-password-btn">
                    <i :class="showConfirmPassword ? 'bi bi-eye-slash' : 'bi bi-eye'"></i>
                  </button>
                </b-input-group-append>
              </b-input-group>
              <div class="text-danger small" v-if="hasErrors('new_password_confirmation')">
                {{ getErrors("new_password_confirmation") }}
              </div>
            </b-form-group>

            <!-- Submit Button -->
            <div class="mt-4">
              <b-button 
                type="submit" 
                variant="success" 
                class="change-password-btn"
                :disabled="isSubmitting"
              >
                <i class="bi bi-check-circle me-1"></i> 
                {{ isSubmitting ? 'Changing Password...' : 'Change Password' }}
              </b-button>
            </div>
          </b-form>
        </b-col>
      </b-row>
    </b-card>
  </b-container>
</template>

<script>
import axios from '@axios';
import Swal from 'sweetalert2';
import { ref } from 'vue';
import { useRouter } from "vue-router";

axios.defaults.withCredentials = true;

export default {   
  data() {
    return {
      showOldPassword: false,
      showNewPassword: false,
      showConfirmPassword: false,
      errors: {},
      isSubmitting: false,
    };
  },
  methods: {
    RemoveError(field) {
      if (this.errors[field]) {
        delete this.errors[field];
      }
    },
    hasErrors(fieldName) {
      return this.errors && this.errors[fieldName];
    },
    getErrors(fieldName) {
      return this.errors[fieldName] ? this.errors[fieldName][0] : '';
    },
    toggleOldPasswordView() {
      this.showOldPassword = !this.showOldPassword;
    },
    toggleNewPasswordView() {
      this.showNewPassword = !this.showNewPassword;
    },
    toggleConfirmPasswordView() {
      this.showConfirmPassword = !this.showConfirmPassword;
    },
    async changePassword() {
      this.isSubmitting = true;
      
      try {
        const response = await axios.post("/change-password", this.userpassword);
        
        if (response.data.status === 'success') {
          Swal.fire({
            title: 'Success!',
            text: 'Password changed successfully.',
            icon: 'success',
            timer: 2000,
            showConfirmButton: false
          }).then(() => {
            this.router.replace({ name: 'dashboard' });
          });
        }
      } catch (error) {
        if (error.response && error.response.data.code === 422) {
          this.errors = error.response.data.errors;
        } else {
          Swal.fire({
            title: 'Error!',
            text: 'Failed to change password. Please try again.',
            icon: 'error',
            confirmButtonText: 'OK'
          });
        }
      } finally {
        this.isSubmitting = false;
      }
    },
  },
  setup() {
    const userpassword = ref({
      current_password: '',
      new_password: '',
      new_password_confirmation: '',
    });
    const errors = ref([]);
    const router = useRouter();

    return {
      userpassword,
      errors,
      router,
    };
  }
};
</script>

<style scoped>
/* Modern Change Password Styles - Matching Profile Tab */
.main-content {
  background: linear-gradient(135deg, #0d0d1a 0%, #1a1a2e 100%);
  min-height: 100vh;
  padding: 20px;
}

.profile-card {
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.1);
  color: white;
  border-radius: 1rem;
  padding: 2rem;
  backdrop-filter: blur(10px);
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
}

/* Password Icon Container */
.password-icon-container {
  display: flex;
  justify-content: center;
  align-items: center;
}

.password-icon {
  width: 120px;
  height: 120px;
  background: linear-gradient(135deg, rgba(0, 255, 128, 0.1), rgba(0, 204, 102, 0.05));
  border: 3px solid rgba(0, 255, 128, 0.3);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.3s ease;
}

.password-icon:hover {
  border-color: rgba(0, 255, 128, 0.6);
  transform: scale(1.05);
  box-shadow: 0 8px 25px rgba(0, 255, 128, 0.2);
}

.password-icon i {
  font-size: 3rem;
  color: #00ff80;
}

/* Security Tips Card */
.security-tips-card {
  background: linear-gradient(135deg, rgba(0, 255, 128, 0.1), rgba(0, 204, 102, 0.05));
  border: 1px solid rgba(0, 255, 128, 0.3);
  border-radius: 16px;
  padding: 20px;
  text-align: left;
  backdrop-filter: blur(10px);
  transition: all 0.3s ease;
}

.security-tips-card:hover {
  border-color: rgba(0, 255, 128, 0.5);
  box-shadow: 0 8px 25px rgba(0, 255, 128, 0.2);
  transform: translateY(-2px);
}

.tips-header {
  display: flex;
  align-items: center;
  justify-content: center;
  color: #00ff80;
  font-weight: 600;
  font-size: 14px;
  margin-bottom: 15px;
}

.tips-content {
  color: #9ca3af;
}

.tips-list {
  list-style: none;
  padding: 0;
  margin: 0;
}

.tips-list li {
  padding: 5px 0;
  position: relative;
  padding-left: 20px;
}

.tips-list li:before {
  content: "✓";
  position: absolute;
  left: 0;
  color: #00ff80;
  font-weight: bold;
}

/* Enhanced Form Styling - Matching Profile Tab */
.form-label, label {
  color: #e5e7eb !important;
  font-weight: 600 !important;
  margin-bottom: 8px !important;
}

.input-group-merge {
  border-radius: 8px;
  overflow: hidden;
  border: 1px solid rgba(255, 255, 255, 0.2);
}

.input-group-merge:focus-within {
  border-color: #00ff80;
  box-shadow: 0 0 0 3px rgba(0, 255, 128, 0.1);
}

.form-control, input.form-control {
  background-color: rgba(255, 255, 255, 0.1) !important;
  color: white !important;
  border: 1px solid rgba(255, 255, 255, 0.2) !important;
  padding: 12px 16px !important;
  font-size: 14px !important;
  transition: all 0.3s ease !important;
}

.form-control:focus, input.form-control:focus {
  background-color: rgba(255, 255, 255, 0.1) !important;
  border-color: #00ff80 !important;
  box-shadow: 0 0 0 3px rgba(0, 255, 128, 0.1) !important;
  color: white !important;
}

.input-group-append {
  background: transparent !important;
  border: none !important;
  border-left: none !important;
}

.toggle-password-btn {
  border: none;
  background: rgba(0, 255, 128, 0.15) !important;
  cursor: pointer;
  color: #00ff80;
  font-size: 1.1rem;
  padding: 0 12px;
  transition: all 0.3s ease;
  height: 100%;
  display: flex;
  align-items: center;
  border-radius: 0 8px 8px 0;
  min-width: 44px;
  justify-content: center;
  font-weight: 600;
}

.toggle-password-btn:hover {
  color: #0d0d1a;
  background: rgba(0, 255, 128, 0.4) !important;
  transform: scale(1.05);
  box-shadow: 0 2px 8px rgba(0, 255, 128, 0.3);
}

.toggle-password-btn:active {
  transform: scale(0.98);
}

/* Modern Buttons - Matching Profile Tab */
.change-password-btn {
  background: linear-gradient(135deg, #00ff80, #00cc66) !important;
  border: none !important;
  color: #0d0d1a !important;
  font-weight: 600 !important;
  padding: 12px 24px !important;
  border-radius: 12px !important;
  transition: all 0.3s ease !important;
  box-shadow: 0 4px 15px rgba(0, 255, 128, 0.2) !important;
}

.change-password-btn:hover:not(:disabled) {
  transform: translateY(-2px) !important;
  box-shadow: 0 8px 25px rgba(0, 255, 128, 0.4) !important;
  background: linear-gradient(135deg, #00cc66, #00aa55) !important;
}

.change-password-btn:disabled {
  opacity: 0.7;
  cursor: not-allowed;
  transform: none !important;
}

/* Text Styling */
.text-primary {
  color: #00ff80 !important;
}

.text-muted {
  color: #9ca3af !important;
}

.fw-bold {
  font-weight: 700 !important;
}

.fw-semibold {
  font-weight: 600 !important;
}

/* Error Messages */
.text-danger {
  color: #ff6b6b !important;
  font-size: 0.875rem;
  margin-top: 4px;
}

/* Enhanced Responsive Design - Matching Profile Tab */

/* Large Tablets and Small Desktops */
@media (max-width: 1024px) {
  .main-content {
    padding: 18px;
  }
  
  .profile-card {
    padding: 1.5rem;
  }
}

/* Tablets */
@media (max-width: 768px) {
  .main-content {
    padding: 15px;
  }
  
  .profile-card {
    padding: 1.25rem;
    border-radius: 12px;
  }
  
  /* Stack layout vertically */
  .profile-card .row {
    margin: 0 !important;
  }
  
  .profile-card .col-md-4 {
    border-right: none !important;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1) !important;
    padding-right: 0 !important;
    padding-bottom: 20px !important;
    margin-bottom: 20px !important;
  }
  
  .profile-card .col-md-8 {
    padding-left: 0 !important;
    padding-top: 0 !important;
  }
  
  /* Adjust password icon */
  .password-icon {
    width: 100px;
    height: 100px;
  }
  
  .password-icon i {
    font-size: 2.5rem;
  }
  
  /* Form adjustments */
  .form-control, input.form-control {
    font-size: 16px !important;
    padding: 14px 16px !important;
  }
  
  /* Button adjustments */
  .change-password-btn {
    width: 100% !important;
    padding: 14px 20px !important;
    font-size: 16px !important;
  }
  
  /* Tips card adjustments */
  .security-tips-card {
    padding: 15px;
  }
}

/* Mobile Phones */
@media (max-width: 480px) {
  .main-content {
    padding: 10px;
  }
  
  .profile-card {
    padding: 1rem;
    border-radius: 10px;
  }
  
  /* Smaller password icon */
  .password-icon {
    width: 80px;
    height: 80px;
    border-width: 2px;
  }
  
  .password-icon i {
    font-size: 2rem;
  }
  
  /* Compact form styling */
  .form-control, input.form-control {
    font-size: 16px !important;
    padding: 12px 14px !important;
    border-radius: 6px !important;
  }
  
  .form-label, label {
    font-size: 14px !important;
    margin-bottom: 6px !important;
  }
  
  .input-group-merge {
    border-radius: 6px;
  }
  
  .toggle-password-btn {
    padding: 0 12px;
    font-size: 1.1rem;
  }
  
  /* Compact buttons */
  .change-password-btn {
    padding: 12px 16px !important;
    font-size: 15px !important;
    border-radius: 10px !important;
  }
  
  /* Profile info text */
  .profile-card h5 {
    font-size: 1.25rem !important;
  }
  
  .profile-card h4 {
    font-size: 1.5rem !important;
  }
  
  /* Tips card adjustments */
  .security-tips-card {
    padding: 12px;
  }
  
  .tips-list li {
    font-size: 13px;
    padding: 3px 0;
  }
}

/* Extra Small Phones */
@media (max-width: 360px) {
  .main-content {
    padding: 8px;
  }
  
  .profile-card {
    padding: 0.75rem;
  }
  
  /* Ultra compact password icon */
  .password-icon {
    width: 70px;
    height: 70px;
  }
  
  .password-icon i {
    font-size: 1.8rem;
  }
  
  /* Ultra compact forms */
  .form-control, input.form-control {
    font-size: 15px !important;
    padding: 10px 12px !important;
  }
  
  .form-label, label {
    font-size: 13px !important;
  }
  
  .toggle-password-btn {
    padding: 0 10px;
    font-size: 1rem;
  }
  
  /* Ultra compact buttons */
  .change-password-btn {
    padding: 10px 14px !important;
    font-size: 14px !important;
  }
  
  /* Ultra compact text */
  .profile-card h5 {
    font-size: 1.1rem !important;
  }
  
  .profile-card h4 {
    font-size: 1.3rem !important;
  }
  
  /* Ultra compact tips */
  .security-tips-card {
    padding: 10px;
  }
  
  .tips-list li {
    font-size: 12px;
    padding: 2px 0;
  }
}

/* Landscape Mobile */
@media (max-width: 768px) and (orientation: landscape) {
  .profile-card .col-md-4 {
    border-right: 1px solid rgba(255, 255, 255, 0.1) !important;
    border-bottom: none !important;
    padding-right: 20px !important;
    padding-bottom: 0 !important;
    margin-bottom: 0 !important;
  }
  
  .profile-card .col-md-8 {
    padding-left: 20px !important;
  }
  
  .change-password-btn {
    width: auto !important;
    display: inline-block !important;
  }
}

/* Touch Device Optimizations */
@media (hover: none) and (pointer: coarse) {
  .change-password-btn {
    min-height: 44px !important;
    min-width: 44px !important;
  }
  
  .form-control, input.form-control {
    min-height: 44px !important;
  }
  
  .toggle-password-btn {
    min-height: 44px !important;
    min-width: 44px !important;
  }
  
  /* Enhanced touch targets */
  .password-icon {
    cursor: pointer;
    -webkit-tap-highlight-color: rgba(0, 255, 128, 0.2);
  }
}

/* High DPI Displays */
@media (-webkit-min-device-pixel-ratio: 2), (min-resolution: 192dpi) {
  .profile-card h4, .profile-card h5 {
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
  }
}

/* Print Styles */
@media print {
  .main-content {
    background: white !important;
    color: black !important;
  }
  
  .profile-card {
    background: white !important;
    border: 1px solid #ccc !important;
    color: black !important;
  }
  
  .change-password-btn {
    display: none !important;
  }
  
  .form-control, input.form-control {
    background: white !important;
    color: black !important;
    border: 1px solid #ccc !important;
  }
  
  .toggle-password-btn {
    display: none !important;
  }
  
  .password-icon {
    border-color: #ccc !important;
  }
  
  .password-icon i {
    color: #333 !important;
  }
  
  .security-tips-card {
    background: #f8f9fa !important;
    border-color: #dee2e6 !important;
  }
  
  .tips-header {
    color: #333 !important;
  }
  
  .tips-content {
    color: #666 !important;
  }
}
</style>
