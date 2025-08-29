<template>
  <b-container fluid class="main-content">
    <b-card class="profile-card">
      <h4 class="mb-4">Change Password</h4>
      <b-form>
        <!-- Old Password -->
        <b-form-group label="Old Password">
          <b-input-group class="input-group-merge">
            <b-form-input
              :type="showOldPassword ? 'text' : 'password'"
              v-model="userpassword.current_password"
              id="current_password"
              @input="RemoveError('current_password')"
              :state="hasErrors('current_password') ? false : null"
              placeholder="Enter Current Password"
              autocomplete="off"
            />
            <b-input-group-append is-text>
              <button type="button" @click="toggleOldPasswordView" class="toggle-password-btn">
                <i :class="showOldPassword ? 'fa fa-eye-slash' : 'fa fa-eye'"></i>
              </button>
            </b-input-group-append>
          </b-input-group>
          <div class="text-danger small" v-if="hasErrors('current_password')">
            {{ getErrors("current_password") }}
          </div>
        </b-form-group>

        <!-- New Password -->
        <b-form-group label="New Password">
          <b-input-group class="input-group-merge">
            <b-form-input
              :type="showNewPassword ? 'text' : 'password'"
              v-model="userpassword.new_password"
              id="new_password"
              @input="RemoveError('new_password')"
              :state="hasErrors('new_password') ? false : null"
              placeholder="Enter New Password"
              autocomplete="off"
            />
            <b-input-group-append is-text>
              <button type="button" @click="toggleNewPasswordView" class="toggle-password-btn">
                <i :class="showNewPassword ? 'fa fa-eye-slash' : 'fa fa-eye'"></i>
              </button>
            </b-input-group-append>
          </b-input-group>
          <div class="text-danger small" v-if="hasErrors('new_password')">
            {{ getErrors("new_password") }}
          </div>
        </b-form-group>

        <!-- Confirm Password -->
        <b-form-group label="Confirm Password">
          <b-input-group class="input-group-merge">
            <b-form-input
              :type="showConfirmPassword ? 'text' : 'password'"
              v-model="userpassword.new_password_confirmation"
              id="new_password_confirmation"
              @input="RemoveError('new_password_confirmation')"
              :state="hasErrors('new_password_confirmation') ? false : null"
              placeholder="Confirm New Password"
              autocomplete="off"
            />
            <b-input-group-append is-text>
              <button type="button" @click="toggleConfirmPasswordView" class="toggle-password-btn">
                <i :class="showConfirmPassword ? 'fa fa-eye-slash' : 'fa fa-eye'"></i>
              </button>
            </b-input-group-append>
          </b-input-group>
          <div class="text-danger small" v-if="hasErrors('new_password_confirmation')">
            {{ getErrors("new_password_confirmation") }}
          </div>
        </b-form-group>

        <b-button type="submit" variant="success" class="mt-3" @click="changePassword">
          Change Password
        </b-button>
      </b-form>
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
  },
  setup() {
    const userpassword = ref({
      current_password: '',
      new_password: '',
      new_password_confirmation: '',
    });
    const errors = ref([]);
    const router = useRouter();

    const changePassword = () => {
      axios.post("/change-password", userpassword.value)
        .then((response) => {
          if (response.data.status === 'success') {
            Swal.fire({
              title: 'Success!',
              text: 'Password changed successfully.',
              icon: 'success',
              timer: 2000,
              showConfirmButton: false
            }).then(() => {
              router.replace({ name: 'dashboard' });
            });
          }
        })
        .catch(error => {
          if (error.response && error.response.data.code === 422) {
            errors.value = error.response.data.errors;
          }
        });
    };

    return {
      userpassword,
      changePassword,
      errors,
    };
  }
};
</script>
<style scoped>
/* Modern Change Password Styles */
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

.profile-card h4 {
  color: #00ff80;
  font-weight: 700;
  margin-bottom: 1.5rem;
}

/* Enhanced Form Styling */
.form-label, label {
  color: #e5e7eb !important;
  font-weight: 600 !important;
  margin-bottom: 8px !important;
}

.input-group-merge {
  border-radius: 8px;
  overflow: hidden;
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
  background: rgba(255, 255, 255, 0.1) !important;
  border: 1px solid rgba(255, 255, 255, 0.2) !important;
  border-left: none !important;
}

.toggle-password-btn {
  border: none;
  background: transparent !important;
  cursor: pointer;
  color: #9ca3af;
  font-size: 1.1rem;
  padding: 0 12px;
  transition: all 0.3s ease;
  height: 100%;
  display: flex;
  align-items: center;
}

.toggle-password-btn:hover {
  color: #00ff80;
  background: rgba(0, 255, 128, 0.1) !important;
}

/* Modern Buttons */
.btn-success, button.btn-success {
  background: linear-gradient(135deg, #00ff80, #00cc66) !important;
  border: none !important;
  color: #0d0d1a !important;
  font-weight: 600 !important;
  padding: 12px 24px !important;
  border-radius: 12px !important;
  transition: all 0.3s ease !important;
}

.btn-success:hover, button.btn-success:hover {
  transform: translateY(-2px) !important;
  box-shadow: 0 8px 25px rgba(0, 255, 128, 0.3) !important;
  background: linear-gradient(135deg, #00ff80, #00cc66) !important;
  border-color: transparent !important;
}

/* Error Messages */
.text-danger {
  color: #ff6b6b !important;
  font-size: 0.875rem;
  margin-top: 4px;
}

/* Enhanced Responsive Design */

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
  
  .profile-card h4 {
    font-size: 1.5rem;
    text-align: center;
    margin-bottom: 1.25rem;
  }
  
  /* Form adjustments */
  .form-control, input.form-control {
    font-size: 16px !important;
    padding: 14px 16px !important;
  }
  
  .toggle-password-btn {
    padding: 0 16px;
    font-size: 1.2rem;
  }
  
  /* Button adjustments */
  .btn-success, button.btn-success {
    width: 100% !important;
    padding: 14px 20px !important;
    font-size: 16px !important;
  }
  
  /* Form groups spacing */
  .form-group {
    margin-bottom: 1.5rem !important;
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
  
  .profile-card h4 {
    font-size: 1.25rem;
    margin-bottom: 1rem;
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
  .btn-success, button.btn-success {
    padding: 12px 16px !important;
    font-size: 15px !important;
    border-radius: 10px !important;
  }
  
  /* Spacing adjustments */
  .form-group {
    margin-bottom: 1.25rem !important;
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
  
  .profile-card h4 {
    font-size: 1.1rem;
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
  .btn-success, button.btn-success {
    padding: 10px 14px !important;
    font-size: 14px !important;
  }
  
  /* Ultra compact spacing */
  .form-group {
    margin-bottom: 1rem !important;
  }
}

/* Touch Device Optimizations */
@media (hover: none) and (pointer: coarse) {
  .btn-success, button.btn-success {
    min-height: 44px !important;
    min-width: 44px !important;
  }
  
  .form-control, input.form-control {
    min-height: 44px !important;
  }
  
  .toggle-password-btn {
    min-height: 44px !important;
    min-width: 44px !important;
    -webkit-tap-highlight-color: rgba(0, 255, 128, 0.2);
  }
}

/* High DPI Displays */
@media (-webkit-min-device-pixel-ratio: 2), (min-resolution: 192dpi) {
  .profile-card h4 {
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
  
  .btn-success, button.btn-success {
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
}
</style>
