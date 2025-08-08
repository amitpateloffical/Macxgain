<template>
  <div class="auth-wrapper auth-v1 px-2">
    <div class="auth-inner py-2">
      <b-card class="mb-0">
        <b-card-title class="mb-1">Reset Password 🔒</b-card-title>
        <b-card-text class="mb-2">
          Your new password must be different from previously used passwords.
        </b-card-text>

        <b-form
          @submit.prevent="handleSubmit"
          class="auth-reset-password-form mt-2"
        >
          <b-form-group label="New Password" label-for="reset-password-new">
            <b-input-group :class="{ 'is-invalid': errors.password }">
              <b-form-input
                id="reset-password-new"
                v-model="password"
                :type="password1FieldType"
                placeholder="Enter New Password"
              />
              <b-input-group-append is-text>
                <feather-icon
                  class="cursor-pointer"
                  :icon="password1ToggleIcon"
                  @click="togglePassword1Visibility"
                />
              </b-input-group-append>
            </b-input-group>
            <small class="text-danger">{{ errors.password }}</small>
          </b-form-group>

          <b-form-group
            label="Confirm Password"
            label-for="reset-password-confirm"
          >
            <b-input-group :class="{ 'is-invalid': errors.cPassword }">
              <b-form-input
                id="reset-password-confirm"
                v-model="cPassword"
                :type="password2FieldType"
                placeholder="Enter Confirm Password"
              />
              <b-input-group-append is-text>
                <feather-icon
                  class="cursor-pointer"
                  :icon="password2ToggleIcon"
                  @click="togglePassword2Visibility"
                />
              </b-input-group-append>
            </b-input-group>
            <small class="text-danger">{{ errors.cPassword }}</small>
          </b-form-group>

          <b-button
            block
            type="submit"
            variant="primary"
            :disabled="isSubmitting"
          >
            Set New Password
          </b-button>
        </b-form>

        <p class="text-center mt-2">
          <router-link to="/login" class="back_to_login"
            >Back to login</router-link
          >
        </p>
      </b-card>
    </div>
  </div>
</template>


<script>
import axios from "@axios";
import { MailIcon, LockIcon, EyeIcon, EyeOffIcon } from "vue-feather-icons";
import * as yup from "yup";
import { ref, computed } from "vue";
import { useRouter } from "vue-router";
import { useToast } from "vue-toastification";

export default {
  components: {
    MailIcon,
    LockIcon,
    EyeIcon,
    EyeOffIcon,
  },
  setup() {
    const password = ref("");
    const cPassword = ref("");
    const isSubmitting = ref(false);
    const errors = ref({});
    const router = useRouter();
    const toast = useToast();
    const password1FieldType = ref("password");
    const password2FieldType = ref("password");

    const handleSubmit = async () => {
      isSubmitting.value = true;

      try {
        const schema = yup.object().shape({
          password: yup
            .string()
            .required("Password is required")
            .min(8, "Password must be at least 8 characters"),
          cPassword: yup
            .string()
            .oneOf([password.value], "Passwords must match")
            .required("Confirm password is required"),
        });

        await schema.validate(
          { password: password.value, cPassword: cPassword.value },
          { abortEarly: false }
        );
        const token = router.currentRoute.value.query.token;
        const response = await axios.post("/resetPassword", {
          password: password.value,
          cPassword: cPassword.value,
          email: router.currentRoute.value.query.email,
          expires: router.currentRoute.value.query.expires,
          signature: router.currentRoute.value.query.signature,
        });

        if (response.data.status === "success") {
          toast.success("Password Updated Successfully");
          router.push("/login");
        } else {
          toast.error("Error! Please try again later.");
        }
      } catch (error) {
        if (error.name === "ValidationError") {
          errors.value = error.errors.reduce((acc, curr) => {
            acc[curr.path] = curr.message;
            return acc;
          }, {});
        } else {
          console.error(error);
          toast.error("Your reset link has expired.");
        }
      } finally {
        isSubmitting.value = false;
      }
    };

    const togglePassword1Visibility = () => {
      password1FieldType.value =
        password1FieldType.value === "password" ? "text" : "password";
    };

    const togglePassword2Visibility = () => {
      password2FieldType.value =
        password2FieldType.value === "password" ? "text" : "password";
    };

    return {
      password,
      cPassword,
      isSubmitting,
      errors,
      handleSubmit,
      togglePassword1Visibility,
      togglePassword2Visibility,
      password1FieldType,
      password2FieldType,
      password1ToggleIcon: computed(() =>
        password1FieldType.value === "password" ? EyeOffIcon : EyeIcon
      ),
      password2ToggleIcon: computed(() =>
        password2FieldType.value === "password" ? EyeOffIcon : EyeIcon
      ),
    };
  },
};
</script>

<style scoped>
.auth-wrapper {
  display: flex;
  justify-content: center;
  align-items: center;
  height: 100vh;
  background: linear-gradient(135deg, #81ecec, #6c5ce7);
}
.auth-inner {
  width: 350px;
}
.brand-text {
  font-size: 2rem;
  color: #6c5ce7;
}
.mb-0 {
  background-color: #fff;
  border-radius: 10px;
  padding: 20px;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
}
.input-group-merge {
  border-radius: 5px;
}
.text-center {
  margin-top: 20px;
}
</style>
