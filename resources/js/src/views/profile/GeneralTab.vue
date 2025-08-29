<template>
  <b-container fluid class="main-content">
    <b-card class="profile-card shadow-lg border-0">
      <b-row>
        <!-- Profile Picture + Info -->
        <b-col md="4" class="text-center border-end border-gray-700 pe-4">
          <!--           <b-img
            :src="imagePreview || `/storage/${options.profile_image}?t=${new Date().getTime()}`"
            rounded
            class="profile-avatar mb-3"
            alt="User Avatar"
            @error="setDefaultImage"
          />
          <div>
            <input
              type="file"
              ref="fileInput"
              @change="onFileChange"
              id="profile_image"
              accept=".jpg, .png, .gif"
              style="display: none"
            />
            <b-button variant="success" class="rounded-pill px-4" @click="triggerFileUpload">
              <i class="bi bi-upload me-1"></i> Upload Image
            </b-button>
          </div>
          <b-card-text class="mt-2 small-text">
            (Allowed JPG, GIF, or PNG. Max size of 800kB)
          </b-card-text> -->

          <div class="mt-4">
            <h5 class="fw-bold">{{ options.name }}</h5>
            <p class="text-muted">
              <strong>Email:</strong> {{ options.email }}
            </p>
            <p class="text-muted">
              <strong>Contact:</strong> {{ options.phone }}
            </p>
          </div>
        </b-col>

        <!-- Profile Form -->
        <b-col md="8" class="ps-4">
          <h4 class="fw-bold mb-3 text-primary">Edit Profile</h4>
          <b-form @submit.prevent="saveGeneralInfo">
            <b-row>
              <b-col md="6" class="mb-3">
                <b-form-group label="Full Name" label-class="fw-semibold">
                  <b-form-input
                    id="name"
                    v-model="options.name"
                    @input="RemoveError('name')"
                    :state="hasErrors('name') ? false : null"
                    placeholder="Enter Name"
                    autocomplete="off"
                  />
                  <div class="text-danger small" v-if="hasErrors('name')">
                    {{ getErrors("name") }}
                  </div>
                </b-form-group>
              </b-col>

              <b-col md="6" class="mb-3">
                <b-form-group label="Email" label-class="fw-semibold">
                  <b-form-input v-model="options.email" readonly />
                </b-form-group>
              </b-col>

              <b-col md="6" class="mb-3">
                <b-form-group label="Phone" label-class="fw-semibold">
                  <b-form-input
                    id="phone"
                    v-model="options.phone"
                    @input="RemoveError('phone')"
                    :state="hasErrors('phone') ? false : null"
                    placeholder="Enter Mobile Number"
                    autocomplete="off"
                  />
                  <div class="text-danger small" v-if="hasErrors('phone')">
                    {{ getErrors("phone") }}
                  </div>
                </b-form-group>
              </b-col>
              <b-col md="6" class="mb-3">
                <b-form-group label="Bank Name" label-class="fw-semibold">
                  <b-form-input
                    id="bank_name"
                    v-model="options.bank_name"
                    @input="RemoveError('bank_name')"
                    :state="hasErrors('bank_name') ? false : null"
                    placeholder="Enter Bank Name"
                    autocomplete="off"
                  />
                  <div class="text-danger small" v-if="hasErrors('bank_name')">
                    {{ getErrors("bank_name") }}
                  </div>
                </b-form-group>
              </b-col>

              <b-col md="6" class="mb-3">
                <b-form-group label="Account Number" label-class="fw-semibold">
                  <b-form-input
                    id="account_no"
                    v-model="options.account_no"
                    @input="RemoveError('account_no')"
                    :state="hasErrors('account_no') ? false : null"
                    placeholder="Enter Account Number"
                    autocomplete="off"
                  />
                  <div class="text-danger small" v-if="hasErrors('account_no')">
                    {{ getErrors("account_no") }}
                  </div>
                </b-form-group>
              </b-col>

              <b-col md="6" class="mb-3">
                <b-form-group label="IFSC Code" label-class="fw-semibold">
                  <b-form-input
                    id="ifsc_code"
                    v-model="options.ifsc_code"
                    @input="RemoveError('ifsc_code')"
                    :state="hasErrors('ifsc_code') ? false : null"
                    placeholder="Enter IFSC Code"
                    autocomplete="off"
                  />
                  <div class="text-danger small" v-if="hasErrors('ifsc_code')">
                    {{ getErrors("ifsc_code") }}
                  </div>
                </b-form-group>
              </b-col>
            </b-row>

            <div class="mt-4">
              <b-button
                type="submit"
                variant="primary"
                class="me-2 rounded-pill px-4"
              >
                <i class="bi bi-check-circle me-1"></i> Save
              </b-button>
              <b-button
                type="reset"
                variant="secondary"
                class="rounded-pill px-4"
              >
                <i class="bi bi-x-circle me-1"></i> Cancel
              </b-button>
            </div>
          </b-form>
        </b-col>
      </b-row>
    </b-card>
  </b-container>
</template>

<script>
import axios from "@axios";
import Swal from "sweetalert2";

export default {
  props: {
    userId: { type: String, required: true },
  },
  data() {
    return {
      options: { name: "", email: "", phone: "", role: "", profile_image: "" },
      selectedFile: null,
      imagePreview: null,
      errors: {},
    };
  },
  created() {
    this.getUserProfile();
  },
  methods: {
    RemoveError(field) {
      if (this.errors[field]) delete this.errors[field];
    },
    hasErrors(field) {
      return this.errors && this.errors[field];
    },
    getErrors(field) {
      return this.errors[field] ? this.errors[field][0] : "";
    },
    getUserProfile() {
      axios.get(`/userprofile`).then((res) => {
        this.options = res.data.data;
      });
    },
    onFileChange(e) {
      this.selectedFile = e.target.files[0];
      if (this.selectedFile) {
        this.imagePreview = URL.createObjectURL(this.selectedFile);
      }
    },
    triggerFileUpload() {
      this.$refs.fileInput.click();
    },
    saveGeneralInfo() {
      const formData = new FormData();
      formData.append("id", this.options.id);
      formData.append("name", this.options.name);
      formData.append("email", this.options.email);
      formData.append("phone", this.options.phone);
      formData.append("role", this.options.role);
      formData.append("bank_name", this.options.bank_name || "");
      formData.append("account_no", this.options.account_no || "");
      formData.append("ifsc_code", this.options.ifsc_code || "");

      if (this.selectedFile) {
        formData.append("profile_image", this.selectedFile);
      }
      axios
        .post(`/update-profile/${btoa(this.options.id)}`, formData, {
          headers: { "Content-Type": "multipart/form-data" },
        })
        .then((res) => {
          this.options = res.data.data;
          this.selectedFile = null;
          this.imagePreview = null;
          window.dispatchEvent(new Event("profileUpdated"));

          Swal.fire({
            icon: "success",
            title: "Profile updated successfully",
            timer: 1500,
            showConfirmButton: false,
          });

          if (res.data.data.is_admin == 0) {
            this.$router.push("/user/dashboard");
          } else {
            this.$router.push("/admin/dashboard");
          }
        })
        .catch((err) => {
          if (err.response?.data.code === 422) {
            this.errors = err.response.data.errors;
          }
        });
    },
  },
};
</script>

<style scoped>
/* Modern Profile Card Styles */
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

.profile-avatar {
  width: 160px;
  height: 160px;
  object-fit: cover;
  border: 4px solid #00ff80;
  border-radius: 50%;
  box-shadow: 0 4px 15px rgba(0, 255, 128, 0.3);
  transition: transform 0.3s ease;
}

.profile-avatar:hover {
  transform: scale(1.05);
}

.small-text {
  font-size: 0.8rem;
  color: #9ca3af;
}

/* Enhanced Form Styling */
.form-label, label {
  color: #e5e7eb !important;
  font-weight: 600 !important;
  margin-bottom: 8px !important;
}

.form-control, input {
  background-color: rgba(255, 255, 255, 0.1) !important;
  color: white !important;
  border: 1px solid rgba(255, 255, 255, 0.2) !important;
  border-radius: 8px !important;
  padding: 12px 16px !important;
  font-size: 14px !important;
  transition: all 0.3s ease !important;
}

.form-control:focus, input:focus {
  background-color: rgba(255, 255, 255, 0.1) !important;
  border-color: #00ff80 !important;
  box-shadow: 0 0 0 3px rgba(0, 255, 128, 0.1) !important;
  color: white !important;
}

.form-control[readonly] {
  background-color: rgba(255, 255, 255, 0.05) !important;
  opacity: 0.7;
}

/* Modern Buttons */
.btn-primary {
  background: linear-gradient(135deg, #00ff80, #00cc66) !important;
  border: none !important;
  color: #0d0d1a !important;
  font-weight: 600 !important;
  padding: 12px 24px !important;
  border-radius: 12px !important;
  transition: all 0.3s ease !important;
}

.btn-primary:hover {
  transform: translateY(-2px) !important;
  box-shadow: 0 8px 25px rgba(0, 255, 128, 0.3) !important;
}

.btn-secondary {
  background: rgba(255, 255, 255, 0.1) !important;
  border: 1px solid rgba(255, 255, 255, 0.2) !important;
  color: white !important;
  font-weight: 600 !important;
  padding: 12px 24px !important;
  border-radius: 12px !important;
  transition: all 0.3s ease !important;
}

.btn-secondary:hover {
  background: rgba(255, 255, 255, 0.2) !important;
  border-color: rgba(255, 255, 255, 0.3) !important;
  color: white !important;
}

.btn-success {
  background: linear-gradient(135deg, #00ff80, #00cc66) !important;
  border: none !important;
  color: #0d0d1a !important;
  font-weight: 600 !important;
}

/* Profile Info Section */
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
  
  /* Stack profile layout vertically */
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
  
  /* Adjust profile avatar */
  .profile-avatar {
    width: 120px;
    height: 120px;
  }
  
  /* Form adjustments */
  .form-control, input {
    font-size: 16px !important;
    padding: 14px 16px !important;
  }
  
  /* Button adjustments */
  .btn-primary, .btn-secondary, .btn-success {
    width: 100% !important;
    margin-bottom: 10px !important;
    padding: 14px 20px !important;
    font-size: 16px !important;
  }
  
  /* Form columns stack */
  .profile-card .col-md-6 {
    margin-bottom: 20px !important;
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
  
  /* Smaller profile avatar */
  .profile-avatar {
    width: 100px;
    height: 100px;
    border-width: 3px;
  }
  
  /* Compact form styling */
  .form-control, input {
    font-size: 16px !important;
    padding: 12px 14px !important;
    border-radius: 6px !important;
  }
  
  .form-label, label {
    font-size: 14px !important;
    margin-bottom: 6px !important;
  }
  
  /* Compact buttons */
  .btn-primary, .btn-secondary, .btn-success {
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
  
  /* Spacing adjustments */
  .profile-card .col-md-6 {
    margin-bottom: 15px !important;
  }
  
  .profile-card .col-md-4 {
    padding-bottom: 15px !important;
    margin-bottom: 15px !important;
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
  
  /* Ultra compact avatar */
  .profile-avatar {
    width: 80px;
    height: 80px;
    border-width: 2px;
  }
  
  /* Ultra compact forms */
  .form-control, input {
    font-size: 15px !important;
    padding: 10px 12px !important;
  }
  
  .form-label, label {
    font-size: 13px !important;
  }
  
  /* Ultra compact buttons */
  .btn-primary, .btn-secondary, .btn-success {
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
  
  .btn-primary, .btn-secondary, .btn-success {
    width: auto !important;
    display: inline-block !important;
    margin-right: 10px !important;
  }
}

/* Touch Device Optimizations */
@media (hover: none) and (pointer: coarse) {
  .btn-primary, .btn-secondary, .btn-success {
    min-height: 44px !important;
    min-width: 44px !important;
  }
  
  .form-control, input {
    min-height: 44px !important;
  }
  
  /* Enhanced touch targets */
  .profile-avatar {
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
  
  .btn-primary, .btn-secondary, .btn-success {
    display: none !important;
  }
  
  .form-control, input {
    background: white !important;
    color: black !important;
    border: 1px solid #ccc !important;
  }
}
</style>
