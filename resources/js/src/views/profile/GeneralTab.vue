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
.profile-card {
  background: linear-gradient(145deg, #111827, #0d0d1a);
  color: white;
  border-radius: 1rem;
  padding: 1.5rem;
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

label {
  color: #a5f3fc;
}

input {
  background-color: #1f2937 !important;
  color: white !important;
  border: 1px solid #374151 !important;
}
input:focus {
  border-color: #00ff80 !important;
  box-shadow: 0 0 0 0.2rem rgba(0, 255, 128, 0.25);
}

button {
  font-weight: 500;
}
</style>
