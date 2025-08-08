<template>
    <b-container fluid class="main-content">
      <b-card class="small-card">
      <b-form>
        <!-- <b-form-group label="Old Password">
           <b-input-group class="input-group-merge">
          <b-form-input :type="showOldPassword ? 'text' : 'password'" v-model="userpassword.current_password"  required />
        <b-input-group-append is-text>
        <button type="button" @click="toggleOldPasswordView" class="toggle-password-btn">
            <i :class="showOldPassword ? 'fa fa-eye-slash' : 'fa fa-eye'"></i>
            </button>
              </b-input-group-append>
           </b-input-group>
        </b-form-group> -->


        <b-form-group label="Old Password">
           <b-input-group class="input-group-merge">
          <b-form-input 
          :type="showOldPassword ? 'text' : 'password'" 
          v-model="userpassword.current_password"  
          id="current_password"
          @input="RemoveError('current_password')" 
          :state="errors.length > 0 ? false : null"
          placeholder="Enter Current Password"
          autocomplete="off"
           />

        <b-input-group-append is-text>
        <button type="button" @click="toggleOldPasswordView" class="toggle-password-btn">
            <i :class="showOldPassword ? 'fa fa-eye-slash' : 'fa fa-eye'"></i>
            </button>
        </b-input-group-append>
              
           </b-input-group>
       <small class="text-danger">{{ errors[0] }}</small>
                    <div class="text-danger" v-if="hasErrors('current_password')">
                      {{ getErrors("current_password") }}
                    </div>
        </b-form-group>


        <!-- <b-form-group label="New Password">
          <b-input-group class="input-group-merge">
            
          <b-form-input :type="showNewPassword ? 'text' : 'password'" v-model="userpassword.new_password"  required />
            <b-input-group-append is-text>
           <button type="button" @click="toggleNewPasswordView" class="toggle-password-btn">
            <i :class="showNewPassword ? 'fa fa-eye-slash' : 'fa fa-eye'"></i>
            </button>
            </b-input-group-append>
          </b-input-group>
        </b-form-group> -->

         <b-form-group label="New Password">
          <b-input-group class="input-group-merge">
            
          <b-form-input 
          :type="showNewPassword ? 'text' : 'password'" 
          v-model="userpassword.new_password"  
          id="new_password"
          @input="RemoveError('new_password')" 
          :state="errors.length > 0 ? false : null"
          placeholder="Enter New Password"
          autocomplete="off"
          
           />
            <b-input-group-append is-text>
           <button type="button" @click="toggleNewPasswordView" class="toggle-password-btn">
            <i :class="showNewPassword ? 'fa fa-eye-slash' : 'fa fa-eye'"></i>
            </button>
            </b-input-group-append>
          </b-input-group>
           <small class="text-danger">{{ errors[0] }}</small>
                    <div class="text-danger" v-if="hasErrors('new_password')">
                      {{ getErrors("new_password") }}
                    </div>
        </b-form-group>
        <!-- <b-form-group label="Confirm Password">
            <b-input-group class="input-group-merge">
          <b-form-input :type="showConfirmPassword ? 'text' : 'password'" v-model="userpassword.new_password_confirmation"  required />
             <b-input-group-append is-text>
            <button type="button" @click="toggleConfirmPasswordView" class="toggle-password-btn">
            <i :class="showConfirmPassword ? 'fa fa-eye-slash' : 'fa fa-eye'"></i>
            </button>
             </b-input-group-append>
            </b-input-group>
        </b-form-group> -->

         <b-form-group label="Confirm Password">
            <b-input-group class="input-group-merge">
          <b-form-input 
          :type="showConfirmPassword ? 'text' : 'password'"
          v-model="userpassword.new_password_confirmation"  
            id="new_password_confirmation"
          @input="RemoveError('new_password_confirmation')" 
          :state="errors.length > 0 ? false : null"
          placeholder="Enter New Confirm Password"
          autocomplete="off"
            />
             <b-input-group-append is-text>
            <button type="button" @click="toggleConfirmPasswordView" class="toggle-password-btn">
            <i :class="showConfirmPassword ? 'fa fa-eye-slash' : 'fa fa-eye'"></i>
            </button>
             </b-input-group-append>
            </b-input-group>
             <small class="text-danger">{{ errors[0] }}</small>
                    <div class="text-danger" v-if="hasErrors('new_password_confirmation')">
                      {{ getErrors("new_password_confirmation") }}
                    </div>
        </b-form-group>
        <b-button type="submit" variant="primary" @click="changePassword">Change Password</b-button>
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
        // if (userpassword.value.new_password === userpassword.value.new_password_confirmation) {
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
        // } else {
        //   Swal.fire({
        //     title: 'Password Mismatch',
        //     text: 'The new password and confirmation do not match.',
        //     icon: 'error',
        //     timer: 2000,
        //     showConfirmButton: false
        //   });
        // }
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
  .small-card {
    max-width: 600px; 
    margin: auto;
    padding: 20px; 
  }
.toggle-password-btn {
  border: none;
  background: transparent;
  cursor: pointer;
  padding: 0.5rem;
}
  </style>