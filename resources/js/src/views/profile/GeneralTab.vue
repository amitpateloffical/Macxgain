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
            
            <!-- Balance Display -->
            <div class="balance-card mt-3">
              <div class="balance-header">
                <i class="bi bi-wallet2 me-2"></i>
                <span>Current Balance</span>
              </div>
              <div class="balance-amount">
                ₹{{ formatBalance(userBalance) }}
              </div>
              <div class="balance-refresh">
                <button @click="fetchUserBalance" class="refresh-btn" :disabled="balanceLoading">
                  <i class="bi bi-arrow-clockwise" :class="{ 'spinning': balanceLoading }"></i>
                  {{ balanceLoading ? 'Updating...' : 'Refresh' }}
                </button>
              </div>
            </div>
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
                    :readonly="!isEditMode"
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
                    :readonly="!isEditMode"
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
                    :readonly="!isEditMode"
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
                    :readonly="!isEditMode"
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
                    :readonly="!isEditMode"
                    placeholder="Enter IFSC Code"
                    autocomplete="off"
                  />
                  <div class="text-danger small" v-if="hasErrors('ifsc_code')">
                    {{ getErrors("ifsc_code") }}
                  </div>
                </b-form-group>
              </b-col>
            </b-row>

            <!-- Additional Fields Row -->
            <b-row>
              <b-col md="6" class="mb-3">
                <b-form-group label="Aadhar Number" label-class="fw-semibold">
                  <b-form-input
                    id="aadhar_number"
                    v-model="options.aadhar_number"
                    @input="RemoveError('aadhar_number')"
                    :state="hasErrors('aadhar_number') ? false : null"
                    :readonly="!isEditMode"
                    placeholder="Enter Aadhar Number"
                    autocomplete="off"
                    maxlength="12"
                  />
                  <div class="text-danger small" v-if="hasErrors('aadhar_number')">
                    {{ getErrors("aadhar_number") }}
                  </div>
                </b-form-group>
              </b-col>

              <b-col md="6" class="mb-3">
                <b-form-group label="PAN Card Number" label-class="fw-semibold">
                  <b-form-input
                    id="pan_number"
                    v-model="options.pan_number"
                    @input="RemoveError('pan_number')"
                    :state="hasErrors('pan_number') ? false : null"
                    :readonly="!isEditMode"
                    placeholder="Enter PAN Number"
                    autocomplete="off"
                    maxlength="10"
                    style="text-transform: uppercase"
                  />
                  <div class="text-danger small" v-if="hasErrors('pan_number')">
                    {{ getErrors("pan_number") }}
                  </div>
                </b-form-group>
              </b-col>
            </b-row>

            <!-- Address Row -->
            <b-row>
              <b-col md="12" class="mb-3">
                <b-form-group label="Address" label-class="fw-semibold">
                  <b-form-textarea
                    id="address"
                    v-model="options.address"
                    @input="RemoveError('address')"
                    :state="hasErrors('address') ? false : null"
                    :readonly="!isEditMode"
                    placeholder="Enter Complete Address"
                    rows="3"
                    autocomplete="off"
                  />
                  <div class="text-danger small" v-if="hasErrors('address')">
                    {{ getErrors("address") }}
                  </div>
                </b-form-group>
              </b-col>
            </b-row>

            <!-- KYC Document Images Row -->
            <b-row>
              <b-col md="12" class="mb-3">
                <h6 class="fw-semibold text-primary mb-3">
                  <i class="bi bi-file-earmark-image me-2"></i>KYC Documents
                </h6>
              </b-col>
            </b-row>

            <!-- Aadhar Images Row -->
            <b-row>
              <b-col md="6" class="mb-3">
                <b-form-group label="Aadhar Front Image" label-class="fw-semibold">
                  <div v-if="!isEditMode && options.aadhar_front_image" class="mb-2">
                    <img 
                      :src="getImageUrl(options.aadhar_front_image)" 
                      alt="Aadhar Front" 
                      class="img-thumbnail" 
                      style="max-width: 200px; max-height: 150px;"
                    />
                  </div>
                  <div class="file-input-wrapper">
                    <input
                      v-if="isEditMode"
                      type="file"
                      id="aadhar_front_image"
                      @change="handleKYCImageUpload('aadhar_front_image', $event)"
                      accept="image/*"
                      class="form-control"
                      :class="{ 'is-invalid': hasErrors('aadhar_front_image') }"
                    />
                    <label for="aadhar_front_image" class="file-input-label">
                      <i class="bi bi-cloud-upload me-2"></i>Choose Aadhar Front Image
                    </label>
                  </div>
                  
                  <!-- Real-time Preview -->
                  <div v-if="isEditMode && kycImagePreviews.aadhar_front_image" class="image-preview-container">
                    <div class="preview-label">Selected Image Preview:</div>
                    <img 
                      :src="kycImagePreviews.aadhar_front_image" 
                      alt="Aadhar Front Preview" 
                      class="selected-image-preview"
                    />
                    <button 
                      type="button" 
                      class="btn-remove-image"
                      @click="removeKYCImage('aadhar_front_image')"
                    >
                      <i class="bi bi-x-circle"></i> Remove
                    </button>
                  </div>
                  <div class="text-danger small" v-if="hasErrors('aadhar_front_image')">
                    {{ getErrors("aadhar_front_image") }}
                  </div>
                  <small class="text-muted">Max size: 2MB, Format: JPG, PNG, JPEG</small>
                </b-form-group>
              </b-col>

              <b-col md="6" class="mb-3">
                <b-form-group label="Aadhar Back Image" label-class="fw-semibold">
                  <div v-if="!isEditMode && options.aadhar_back_image" class="mb-2">
                    <img 
                      :src="getImageUrl(options.aadhar_back_image)" 
                      alt="Aadhar Back" 
                      class="img-thumbnail" 
                      style="max-width: 200px; max-height: 150px;"
                    />
                  </div>
                  <div class="file-input-wrapper">
                    <input
                      v-if="isEditMode"
                      type="file"
                      id="aadhar_back_image"
                      @change="handleKYCImageUpload('aadhar_back_image', $event)"
                      accept="image/*"
                      class="form-control"
                      :class="{ 'is-invalid': hasErrors('aadhar_back_image') }"
                    />
                    <label for="aadhar_back_image" class="file-input-label">
                      <i class="bi bi-cloud-upload me-2"></i>Choose Aadhar Back Image
                    </label>
                  </div>
                  
                  <!-- Real-time Preview -->
                  <div v-if="isEditMode && kycImagePreviews.aadhar_back_image" class="image-preview-container">
                    <div class="preview-label">Selected Image Preview:</div>
                    <img 
                      :src="kycImagePreviews.aadhar_back_image" 
                      alt="Aadhar Back Preview" 
                      class="selected-image-preview"
                    />
                    <button 
                      type="button" 
                      class="btn-remove-image"
                      @click="removeKYCImage('aadhar_back_image')"
                    >
                      <i class="bi bi-x-circle"></i> Remove
                    </button>
                  </div>
                  <div class="text-danger small" v-if="hasErrors('aadhar_back_image')">
                    {{ getErrors("aadhar_back_image") }}
                  </div>
                  <small class="text-muted">Max size: 2MB, Format: JPG, PNG, JPEG</small>
                </b-form-group>
              </b-col>
            </b-row>

            <!-- PAN Card Image Row -->
            <b-row>
              <b-col md="6" class="mb-3">
                <b-form-group label="PAN Card Image" label-class="fw-semibold">
                  <div v-if="!isEditMode && options.pan_card_image" class="mb-2">
                    <img 
                      :src="getImageUrl(options.pan_card_image)" 
                      alt="PAN Card" 
                      class="img-thumbnail" 
                      style="max-width: 200px; max-height: 150px;"
                    />
                  </div>
                  <div class="file-input-wrapper">
                    <input
                      v-if="isEditMode"
                      type="file"
                      id="pan_card_image"
                      @change="handleKYCImageUpload('pan_card_image', $event)"
                      accept="image/*"
                      class="form-control"
                      :class="{ 'is-invalid': hasErrors('pan_card_image') }"
                    />
                    <label for="pan_card_image" class="file-input-label">
                      <i class="bi bi-cloud-upload me-2"></i>Choose PAN Card Image
                    </label>
                  </div>
                  
                  <!-- Real-time Preview -->
                  <div v-if="isEditMode && kycImagePreviews.pan_card_image" class="image-preview-container">
                    <div class="preview-label">Selected Image Preview:</div>
                    <img 
                      :src="kycImagePreviews.pan_card_image" 
                      alt="PAN Card Preview" 
                      class="selected-image-preview"
                    />
                    <button 
                      type="button" 
                      class="btn-remove-image"
                      @click="removeKYCImage('pan_card_image')"
                    >
                      <i class="bi bi-x-circle"></i> Remove
                    </button>
                  </div>
                  <div class="text-danger small" v-if="hasErrors('pan_card_image')">
                    {{ getErrors("pan_card_image") }}
                  </div>
                  <small class="text-muted">Max size: 2MB, Format: JPG, PNG, JPEG</small>
                </b-form-group>
              </b-col>
            </b-row>

            <div class="mt-4">
              <!-- Edit Mode Buttons -->
              <template v-if="isEditMode">
                <b-button
                  type="submit"
                  variant="success"
                  class="me-2 rounded-pill px-4"
                >
                  <i class="bi bi-check-circle me-1"></i> Save Changes
                </b-button>
                <b-button
                  type="button"
                  variant="secondary"
                  class="rounded-pill px-4"
                  @click="toggleEditMode"
                >
                  <i class="bi bi-x-circle me-1"></i> Cancel
                </b-button>
              </template>
              
              <!-- View Mode Button -->
              <template v-else>
                <b-button
                  type="button"
                  variant="primary"
                  class="me-2 rounded-pill px-4"
                  @click="toggleEditMode"
                >
                  <i class="bi bi-pencil me-1"></i> Edit Profile
                </b-button>
              </template>
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
      options: { 
        name: "", 
        email: "", 
        phone: "", 
        role: "", 
        profile_image: "",
        bank_name: "",
        account_no: "",
        ifsc_code: "",
        aadhar_number: "",
        pan_number: "",
        address: "",
        aadhar_front_image: "",
        aadhar_back_image: "",
        pan_card_image: ""
      },
      selectedFile: null,
      imagePreview: null,
      errors: {},
      userBalance: 0,
      balanceLoading: false,
      isEditMode: false,
      kycImages: {},
      kycImagePreviews: {},
    };
  },
  created() {
    this.getUserProfile();
    this.fetchUserBalance();
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
    
    async fetchUserBalance() {
      this.balanceLoading = true;
      try {
        const userData = JSON.parse(localStorage.getItem("userData"));
        if (userData && userData.id) {
          const res = await axios.post("/total_b", {
            id: userData.id
          });
          
          if (res.data && res.data.total_balance !== undefined) {
            this.userBalance = res.data.total_balance;
            
            // Update localStorage with latest balance
            userData.total_balance = res.data.total_balance;
            localStorage.setItem("userData", JSON.stringify(userData));
          }
        }
      } catch (error) {
        console.error("Error fetching balance:", error);
        // Try to get balance from localStorage as fallback
        const userData = JSON.parse(localStorage.getItem("userData"));
        if (userData && userData.total_balance !== undefined) {
          this.userBalance = userData.total_balance;
        }
      } finally {
        this.balanceLoading = false;
      }
    },
    
    formatBalance(balance) {
      if (balance === null || balance === undefined) return '0.00';
      return parseFloat(balance).toLocaleString('en-IN', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
      });
    },
    
    toggleEditMode() {
      this.isEditMode = !this.isEditMode;
      if (!this.isEditMode) {
        // If switching to view mode, reset any unsaved changes
        this.getUserProfile();
        this.errors = {};
      }
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
      formData.append("aadhar_number", this.options.aadhar_number || "");
      formData.append("pan_number", this.options.pan_number || "");
      formData.append("address", this.options.address || "");

      if (this.selectedFile) {
        formData.append("profile_image", this.selectedFile);
      }

      // Append KYC images if they exist
      if (this.kycImages.aadhar_front_image) {
        formData.append("aadhar_front_image", this.kycImages.aadhar_front_image);
      }
      if (this.kycImages.aadhar_back_image) {
        formData.append("aadhar_back_image", this.kycImages.aadhar_back_image);
      }
      if (this.kycImages.pan_card_image) {
        formData.append("pan_card_image", this.kycImages.pan_card_image);
      }

      axios
        .post(`/update-profile/${btoa(this.options.id)}`, formData, {
          headers: { "Content-Type": "multipart/form-data" },
        })
        .then((res) => {
          this.options = res.data.data;
          this.selectedFile = null;
          this.imagePreview = null;
          this.kycImages = {}; // Clear KYC images
          this.isEditMode = false; // Switch back to view mode
          window.dispatchEvent(new Event("profileUpdated"));

          Swal.fire({
            icon: "success",
            title: "Profile updated successfully",
            timer: 1500,
            showConfirmButton: false,
          });

          // Don't redirect, just stay on profile page
        })
        .catch((err) => {
          if (err.response?.data.code === 422) {
            this.errors = err.response.data.errors;
          }
        });
    },

    // Handle KYC image uploads
    handleKYCImageUpload(field, event) {
      const file = event.target.files[0];
      if (file) {
        // Validate file size (2MB max)
        if (file.size > 2 * 1024 * 1024) {
          Swal.fire({
            icon: "error",
            title: "File too large",
            text: "Please select a file smaller than 2MB",
          });
          event.target.value = ''; // Clear the input
          return;
        }

        // Validate file type
        const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
        if (!allowedTypes.includes(file.type)) {
          Swal.fire({
            icon: "error",
            title: "Invalid file type",
            text: "Please select JPG, PNG, or JPEG files only",
          });
          event.target.value = ''; // Clear the input
          return;
        }

        // Store the file for upload
        if (!this.kycImages) {
          this.kycImages = {};
        }
        this.kycImages[field] = file;

        // Create preview URL
        if (!this.kycImagePreviews) {
          this.kycImagePreviews = {};
        }
        this.kycImagePreviews[field] = URL.createObjectURL(file);
      }
    },

    // Get image URL for display
    getImageUrl(imagePath) {
      if (!imagePath) return '';
      if (imagePath.startsWith('http')) return imagePath;
      return `${window.location.origin}/storage/${imagePath}`;
    },

    // Remove selected KYC image
    removeKYCImage(field) {
      // Clear the file input
      const fileInput = document.getElementById(field);
      if (fileInput) {
        fileInput.value = '';
      }
      
      // Remove from kycImages
      if (this.kycImages[field]) {
        delete this.kycImages[field];
      }
      
      // Remove from previews
      if (this.kycImagePreviews[field]) {
        URL.revokeObjectURL(this.kycImagePreviews[field]);
        delete this.kycImagePreviews[field];
      }
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

/* Balance Card Styles */
.balance-card {
  background: linear-gradient(135deg, rgba(0, 255, 128, 0.1), rgba(0, 204, 102, 0.05));
  border: 1px solid rgba(0, 255, 128, 0.3);
  border-radius: 16px;
  padding: 20px;
  text-align: center;
  backdrop-filter: blur(10px);
  transition: all 0.3s ease;
}

.balance-card:hover {
  border-color: rgba(0, 255, 128, 0.5);
  box-shadow: 0 8px 25px rgba(0, 255, 128, 0.2);
  transform: translateY(-2px);
}

.balance-header {
  display: flex;
  align-items: center;
  justify-content: center;
  color: #00ff80;
  font-weight: 600;
  font-size: 14px;
  margin-bottom: 10px;
}

.balance-amount {
  font-size: 28px;
  font-weight: bold;
  color: #00ff80;
  margin-bottom: 15px;
  text-shadow: 0 0 10px rgba(0, 255, 128, 0.3);
}

.balance-refresh {
  margin-top: 10px;
}

.refresh-btn {
  background: rgba(0, 255, 128, 0.1);
  border: 1px solid rgba(0, 255, 128, 0.3);
  color: #00ff80;
  padding: 6px 12px;
  border-radius: 8px;
  font-size: 12px;
  cursor: pointer;
  transition: all 0.3s ease;
  display: inline-flex;
  align-items: center;
  gap: 5px;
}

.refresh-btn:hover:not(:disabled) {
  background: rgba(0, 255, 128, 0.2);
  border-color: rgba(0, 255, 128, 0.5);
  transform: translateY(-1px);
}

.refresh-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.spinning {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}

/* Edit Mode Styles */
.form-control[readonly] {
  background: rgba(255, 255, 255, 0.02) !important;
  border: 1px solid rgba(255, 255, 255, 0.1) !important;
  color: #9ca3af !important;
  cursor: not-allowed;
}

.form-control[readonly]:focus {
  background: rgba(255, 255, 255, 0.02) !important;
  border-color: rgba(255, 255, 255, 0.1) !important;
  box-shadow: none !important;
}

/* Edit Button Styles */
.btn-primary i.bi-pencil {
  transition: transform 0.3s ease;
}

.btn-primary:hover i.bi-pencil {
  transform: rotate(15deg);
}

/* Save Button Animation */
.btn-success {
  background: linear-gradient(135deg, #00ff80, #00cc66) !important;
  border: none !important;
  transition: all 0.3s ease;
}

.btn-success:hover {
  background: linear-gradient(135deg, #00cc66, #00aa55) !important;
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(0, 255, 128, 0.3);
}

/* Form Field Focus in Edit Mode */
.form-control:not([readonly]):focus {
  border-color: #00ff80 !important;
  box-shadow: 0 0 0 0.2rem rgba(0, 255, 128, 0.25) !important;
  background: rgba(255, 255, 255, 0.1) !important;
}

/* Textarea Styles */
.form-control[readonly].form-control {
  background: rgba(255, 255, 255, 0.02) !important;
  border: 1px solid rgba(255, 255, 255, 0.1) !important;
  color: #9ca3af !important;
  cursor: not-allowed;
  resize: none;
}

textarea.form-control:not([readonly]):focus {
  border-color: #00ff80 !important;
  box-shadow: 0 0 0 0.2rem rgba(0, 255, 128, 0.25) !important;
  background: rgba(255, 255, 255, 0.1) !important;
  resize: vertical;
}

/* Special Input Styles */
input[maxlength="12"], input[maxlength="10"] {
  font-family: 'Courier New', monospace;
  letter-spacing: 1px;
}

/* PAN Number uppercase styling */
input[style*="text-transform: uppercase"] {
  font-weight: 600;
}

/* Aadhar Number styling */
input[maxlength="12"]:not([readonly]) {
  background: rgba(255, 255, 255, 0.08) !important;
}

/* Address textarea styling */
textarea.form-control {
  min-height: 80px;
  line-height: 1.5;
}

/* File Input Styles */
.file-input-wrapper {
  position: relative;
  margin-bottom: 10px;
}

.file-input-wrapper input[type="file"] {
  opacity: 0;
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  cursor: pointer;
  z-index: 2;
}

.file-input-label {
  display: block;
  padding: 12px 16px;
  background: rgba(0, 255, 128, 0.1);
  border: 2px dashed rgba(0, 255, 128, 0.3);
  border-radius: 8px;
  text-align: center;
  color: #00ff80;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.3s ease;
  margin-bottom: 8px;
}

.file-input-label:hover {
  background: rgba(0, 255, 128, 0.15);
  border-color: rgba(0, 255, 128, 0.5);
  transform: translateY(-1px);
}

.file-input-label i {
  font-size: 1.1rem;
}

/* File input focus state */
.file-input-wrapper input[type="file"]:focus + .file-input-label {
  border-color: #00ff80;
  box-shadow: 0 0 0 0.2rem rgba(0, 255, 128, 0.25);
}

/* Invalid state */
.file-input-wrapper input[type="file"].is-invalid + .file-input-label {
  border-color: #dc3545;
  background: rgba(220, 53, 69, 0.1);
  color: #dc3545;
}

/* Image Preview Styles */
.image-preview-container {
  margin-top: 15px;
  padding: 15px;
  background: rgba(0, 255, 128, 0.05);
  border: 1px solid rgba(0, 255, 128, 0.2);
  border-radius: 8px;
}

.preview-label {
  font-weight: 600;
  color: #00ff80;
  margin-bottom: 10px;
  font-size: 0.9rem;
}

.selected-image-preview {
  max-width: 200px;
  max-height: 150px;
  object-fit: cover;
  border-radius: 6px;
  border: 2px solid rgba(0, 255, 128, 0.3);
  margin-bottom: 10px;
}

.btn-remove-image {
  background: rgba(220, 53, 69, 0.1);
  color: #dc3545;
  border: 1px solid rgba(220, 53, 69, 0.3);
  padding: 6px 12px;
  border-radius: 6px;
  font-size: 0.8rem;
  cursor: pointer;
  transition: all 0.3s ease;
  display: inline-flex;
  align-items: center;
  gap: 6px;
}

.btn-remove-image:hover {
  background: rgba(220, 53, 69, 0.2);
  border-color: rgba(220, 53, 69, 0.5);
  transform: translateY(-1px);
}

.btn-remove-image i {
  font-size: 0.9rem;
}
</style>
