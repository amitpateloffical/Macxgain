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
.profile-card {
  background: linear-gradient(145deg, #111827, #0d0d1a);
  color: white;
  border-radius: 1rem;
  padding: 2rem;
  box-shadow: 0 4px 15px rgba(0, 255, 128, 0.05);
}

label {
  color: #a5f3fc;
  font-weight: 500;
}

.input-group-merge {
  border-radius: 0.5rem;
  overflow: hidden;
}

input.form-control {
  background-color: #1f2937 !important;
  color: white !important;
  border: 1px solid #374151 !important;
  padding: 0.75rem 1rem !important;
  font-size: 0.95rem;
}
input.form-control:focus {
  border-color: #00ff80 !important;
  box-shadow: 0 0 0 0.2rem rgba(0, 255, 128, 0.25);
}

.toggle-password-btn {
  border: none;
  background: transparent;
  cursor: pointer;
  color: #a5f3fc;
  font-size: 1.2rem;
  padding: 0 0.75rem;
}
.toggle-password-btn:hover {
  color: #00ff80;
}

button.btn-success {
  background-color: #00ff80 !important;
  border-color: #00ff80 !important;
  color: #0d0d1a;
  padding: 0.6rem 1.25rem;
  font-weight: 600;
  border-radius: 0.5rem;
}
button.btn-success:hover {
  background-color: #00cc66 !important;
  border-color: #00cc66 !important;
}
</style>
