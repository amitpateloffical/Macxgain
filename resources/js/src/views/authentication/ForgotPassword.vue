<template>
  <div class="forgot-wrapper" :style="{ background: 'linear-gradient(rgba(255,255,255,.5), rgba(255,255,255,.5)), url(' + bgimg + ')' }">
    <div class="forgot-card">
      <h2>Forgot Password? 🔒</h2>
      <p>Enter your email and we'll send you instructions to reset your password</p>

      <!-- form -->
      <form @submit.prevent="validationForm">
        <div class="form-group">
          <input
            type="email"
            v-model="userEmail"
            required
            placeholder="Email"
            class="form-input"
          />
        </div>

        <b-overlay :show="show" rounded="sm" class="loadShow forpassoverlaybtn">
          <template #overlay>
            <div class="showtxtload">
              <p id="cancel-label">Wait for email response</p>
              <div class="lds-dual-ring"></div>
            </div>
          </template>

          <button type="submit" class="login-btn" :disabled="show">
            Send reset link
          </button>
        </b-overlay>

        <p v-if="errorMessage" class="error">{{ errorMessage }}</p>
        <div class="text-center mt-1">
          <router-link to="/login" class="back_to_login">Back to login</router-link>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
import axios from '@axios';
import Swal from 'sweetalert2';

export default {
  data() {
    return {
      userEmail: '',
      bgimg: '/tenancy/assets/img/BG2024.png',
      show: false,
      errorMessage: ''
    };
  },
  methods: {
    validationForm() {
      if (!this.userEmail) {
        this.errorMessage = 'Email is required';
        return;
      }

      this.show = true;
      axios.post('/admin/forgot-password', { email: this.userEmail })
        .then(response => {
          if (response.data.status === 'success') {
            Swal.fire({
              title: 'Reset Password Mail Sent Successfully.',
              icon: 'success',
              timer: 2000,
              showConfirmButton: false
            });
            this.show = false;
            this.$router.push('/login');
          } else {
            // this.errorMessage = 'This User name does not exit';
            this.errorMessage = 'Something Went Wrong! Please try again later.';
            this.show = false;
          }
        })
        .catch(() => {
          this.errorMessage = 'This User name does not exit';
          this.show = false;
        });
    }
  }
};
</script>

<style scoped>
/* Center the forgot password form on the page */
.forgot-wrapper {
  display: flex;
  justify-content: center;
  align-items: center;
  height: 100vh;
  background: linear-gradient(135deg, #81ecec, #6c5ce7);
}

.forgot-card {
  background-color: #fff;
  border-radius: 10px;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
  padding: 40px 30px;
  text-align: center;
  width: 350px;
  transition: transform 0.3s ease-in-out;
}

.forgot-card:hover {
  transform: translateY(-10px);
}

h2 {
  font-size: 2rem;
  color: #2d3436;
  margin-bottom: 10px;
}

p {
  color: #636e72;
  margin-bottom: 30px;
}

/* Input styles */
.form-group {
  margin-bottom: 20px;
}

.form-input {
  width: 100%;
  padding: 12px 20px;
  border: 2px solid #dfe6e9;
  border-radius: 5px;
  background-color: #f5f6fa;
  transition: border-color 0.3s;
  outline: none;
  font-size: 1rem;
}

.form-input:focus {
  border-color: #6c5ce7;
}

.login-btn {
  width: 100%;
  padding: 12px 0;
  background-color: #6c5ce7;
  color: #fff;
  font-size: 1.2rem;
  border: none;
  border-radius: 5px;
  cursor: pointer;
  transition: background-color 0.3s;
}

.login-btn:hover {
  background-color: #a29bfe;
}

.error {
  color: red;
  margin-top: 10px;
}

@media (max-width: 768px) {
  .forgot-card {
    width: 90%;
    padding: 30px 20px;
  }

  h2 {
    font-size: 1.5rem;
  }
}
</style>
