<template>
    <b-container fluid class="main-content">
      <b-card class="small-card">
        <b-row>
          <b-col md="4" class="text-center">
            <b-img
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
              <b-button variant="primary" @click="triggerFileUpload">Upload Image</b-button>
            </div>
            <b-card-text class="mt-2 small-text">
              (Allowed JPG, GIF, or PNG. Max size of 800kB)
            </b-card-text>
            <h5>{{ options.name }}</h5>
            <p><strong>Email: </strong>{{ options.email }}</p>
            <p><strong>Contact No: </strong>{{ options.phone }}</p>

          </b-col>

          <b-col md="8">
            <b-form @submit.prevent="saveGeneralInfo" class="mt-3">
              <b-row>
                <b-col md="6">
                  <b-form-group label="Name">
                    <b-form-input 
                      id="name"
                      v-model="options.name" 
                      @input="RemoveError('name')" 
                    :state="errors.length > 0 ? false : null"
                      placeholder="Enter Name"
                      autocomplete="off"
                      />
                    <small class="text-danger">{{ errors[0] }}</small>
                    <div class="text-danger" v-if="hasErrors('name')">
                      {{ getErrors("name") }}
                    </div>
              </b-form-group>
                </b-col>
                <b-col md="6">
                  <b-form-group label="Email">
                    <b-form-input v-model="options.email" readonly />
                  </b-form-group>
                </b-col>
                <b-form-group label="Phone">
                  <b-form-input 
                    id="phone"
                    v-model="options.phone" 
                    @input="RemoveError('phone')" 
                  :state="errors.length > 0 ? false : null"
                    placeholder="Enter Mobile Number"
                    autocomplete="off"
                  />
                  <small class="text-danger">{{ errors[0] }}</small>
                    <div class="text-danger" v-if="hasErrors('phone')">
                      {{ getErrors("phone") }}
                    </div>
              </b-form-group>
                <!-- <b-col md="6">
                  <b-form-group label="Role">
                    <b-form-input v-model="options.role" readonly />
                  </b-form-group>
                </b-col> -->
              </b-row>
              <b-button type="submit" variant="primary" class="me-2">Save</b-button>
          <b-button type="reset" variant="secondary">Cancel</b-button>
            </b-form>
          </b-col>
        </b-row>
      </b-card>
    </b-container>
  </template>
    
  <script>
  import axios from "@axios";
  import Swal from "sweetalert2";
  import { useRouter } from "vue-router";

  export default {
    props: {
      userId: {
        type: String,
        required: true,
      },
    },
    data() {
      return {
        options: {
          name: "",
          email: "",
          phone: "",
          role: "",
          profile_image: "",
        },
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

      getUserProfile() {
      axios
      .get(`/userprofile`) 
        .then((response) => {
          this.options = response.data.data;
        })
        .catch((error) => {
        });
      },
      onFileChange(event) {
        this.selectedFile = event.target.files[0];
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
        if (this.selectedFile) {
          formData.append("profile_image", this.selectedFile);
        }
        axios
          .post(`/update-profile/${this.encodeBase64(this.options.id)}`, formData, {
            headers: {
              "Content-Type": "multipart/form-data",
            },
          })
          .then((response) => {
            this.options = response.data.data;
            this.selectedFile = null;
            this.imagePreview = null;
            window.dispatchEvent(new Event("profileUpdated"));
            Swal.fire({
              icon: "success",
              title: "Profile updated successfully",
              timer: 1500,
              showConfirmButton: false,
            });
            this.$router.push("/dashboard");
          })
          .catch((error) => {
            if (error.response && error.response.data.code === 422) {
              this.errors = error.response.data.errors;
            }
          });
      },
      encodeBase64(data) {
          return btoa(data);
        },
    },
  };
  </script>

  <style scoped>
  .profile-avatar {
    width: 150px;
    height: 150px;
    object-fit: cover;
    border: 3px solid #ddd;
    border-radius: 50%;
  }
  .small-card {
    max-width: 1000px;
    margin: auto;
    padding: 20px;
  }
  .text-center {
    text-align: center;
  }
  .mb-3 {
    margin-bottom: 1rem;
  }
  .text-start {
    text-align: start;
  }
  .small-text {
    font-size: 0.8rem;
    color: #6c757d;
  }
  </style>