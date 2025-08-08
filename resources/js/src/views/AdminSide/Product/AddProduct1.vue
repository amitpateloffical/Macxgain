<template>
  <div class="dashboard">
    <div class="list__page">
      <div class="page__breadcrumb">
        <p>
          <b-link :to="{ name: 'admin-product' }"> Product <i class="fas fa-chevron-right"></i></b-link> Add Product
        </p>
      </div>
      <b-card >
        <b-overlay :show="isLoading" rounded="sm" class="loadShow btninl">
          <b-form @submit.prevent="addCustomer">
            <b-row>

                
              <!-- Name Field -->
              <b-col md="6">
                <!-- <b-form-group label="Name" label-for="name-input">
                  <b-form-input
                    id="name-input"
                    v-model="customer.name"
                    placeholder="Enter customer name"
                    required
                  ></b-form-input>
                </b-form-group> -->

                <label class="form-label required">
                  Customer Name 
                </label>
                <b-form-group label-for="name-input">
                  <b-form-input
                    id="name-input"
                    v-model="customer.name"
                    placeholder="Enter customer name"
                    trim
                    @input="removeError('name')"
                  />
                  <small class="text-danger">{{ errors[0] }}</small>
                  <div class="text-danger" v-if="hasErrors('name')">
                    {{ getErrors("name") }}
                  </div>
                </b-form-group>
              </b-col>

              <!-- Contact Person -->
              <!-- <b-col md="6">
                <b-form-group label="Contact Person" label-for="contact-person-input">
                  <b-form-input
                    id="contact-person-input"
                    v-model="customer.contactperson"
                    placeholder="Enter contact person"
                    required
                  ></b-form-input>
                </b-form-group>
              </b-col> -->
              <b-col md="6">
                <label class="form-label required ">
                  Contact Person 
                </label>
                <b-form-group label-for="contact-person-input">
                  <b-form-input
                    id="contact-person-input"
                    v-model="customer.contactperson"
                    placeholder="Enter contact person"
                    @input="removeError('contactperson')"
                  ></b-form-input>
                  <small class="text-danger">{{ errors[0] }}</small>
                  <div class="text-danger" v-if="hasErrors('contactperson')">
                    {{ getErrors("contactperson") }}
                  </div>
                </b-form-group>
              </b-col>

              <!-- Email -->
              <b-col md="6">
                <label class="form-label required">
                  Email 
                </label>
                <b-form-group label-for="email-input">
                  <b-form-input
                    id="email-input"
                    v-model="customer.email"
                    type="email"
                    placeholder="Enter email address"
                    @input="removeError('email')"
                  ></b-form-input>
                  <small class="text-danger">{{ errors[0] }}</small>
                  <div class="text-danger" v-if="hasErrors('email')">
                    {{ getErrors("email") }}
                  </div>
                </b-form-group>
              </b-col>

              <!-- Contact Number -->
              <b-col md="6">
                <label class="form-label required" >
                  Contact Number 
                </label>
                <b-form-group label-for="contact-number-input">
                  <b-form-input
                    id="contact-number-input"
                    v-model="customer.contactnumber"
                    type="tel"
                    placeholder="Enter contact number"
                    @input="removeError('contactnumber')"
                  ></b-form-input>
                  <small class="text-danger">{{ errors[0] }}</small>
                  <div class="text-danger" v-if="hasErrors('contactnumber')">
                    {{ getErrors("contactnumber") }}
                  </div>
                </b-form-group>
              </b-col>

              <!-- Assigned To -->
              <b-col md="6">
                <label class="form-label required">
                  Assigned To 
                </label>
                <b-form-group label="" label-for="assigned-to-select">
                  <v-select
                    label="name"
                    value="id"
                    :reduce="(val) => val.id"
                    id="assigned-to-select"
                    v-model="customer.assignedTo"
                    @input="removeError('assignedTo')"
                    :options="assignedToOptions"
                    placeholder="Select assignee"
                  ></v-select>
                  <small class="text-danger">{{ errors[0] }}</small>
                  <div class="text-danger" v-if="hasErrors('assignedTo')">
                    {{ getErrors("assignedTo") }}
                  </div>
                </b-form-group>
              </b-col>

              <!-- Engagement -->
              <b-col md="12">
                <label class="form-label required">
                  Engagement 
                </label>
                <b-form-group  label-for="engagement-select">
                  <v-select
                    id="engagement-select"
                    v-model="customer.engagement"
                    :options="engagementOptions"
                    placeholder="Select engagement level"
                  ></v-select>
                  <small class="text-danger">{{ errors[0] }}</small>
                  <div class="text-danger" v-if="hasErrors('engagement')">
                    {{ getErrors("engagement") }}
                  </div>
                </b-form-group>
              </b-col>
            </b-row>

            <!-- Submit Button -->
            <b-row>
              <b-col>
                <b-button v-if="!isLoading" type="submit" variant="primary"
                  >Add Customer</b-button
                >
              </b-col>
            </b-row>
          </b-form>
        </b-overlay>
      </b-card>
    </div>
  </div>
</template>
    
    <script>
import "vue-select/dist/vue-select.css";
import axios from "@axios";
import vSelect from "vue-select";
import { ref, watch, computed } from "vue";
import { useRouter } from "vue-router";

export default {
  components: {
    vSelect,
  },

  watch: {
    "customer.assignedTo": function () {
      this.removeError("assignedTo");
    },
    "customer.engagement": function () {
      this.removeError("engagement");
    },
  },
  data() {
    return {
      customer: {
        name: "",
        contactperson: "",
        email: "",
        contactnumber: "",
        assignedTo: null,
        engagement: null,
      },
      assignedToOptions: [],
      engagementOptions: ["High", "Medium", "Low"],
      errors: {},
      isLoading: false,
    };
  },
  mounted() {
    axios.get(`/getAssignee`).then((response) => {
      this.assignedToOptions = response.data.data;
    });
  },
  methods: {
    addCustomer() {
      this.isLoading = true;
      const toast = useToast();
      //  this.errors = {};
      axios
        .post("/addCustomers", this.customer)
        .then((response) => {
          this.isLoading = false;
          toast.success("Customer Added successfully!");
          this.$router.push({ name: "customer" });
        })
        .catch((error) => {
          this.isLoading = false;
          if (error.response.data.code == 422) {
            this.errors = error.response.data.errors;
          }
        });
    },

    removeError(field) {
      if (this.errors[field]) {
        delete this.errors[field];
      }
    },
    hasErrors(fieldName) {
      return this.errors && this.errors[fieldName];
    },
    getErrors(fieldName) {
      return this.errors[fieldName] ? this.errors[fieldName][0] : "";
    },
  },
  created() {},
  setup(props) {
    const filterData = ref({
      ticket_id: "",
      ticket_name: "",
      ticket_type: "",
      priority: "",
    });

    const errors = ref([]);

    return {
      filterData,
      errors,
    };
  },
};
</script>
  
    
    <style scoped>
.dashboard {
  padding: 20px;
  background-color: #f4f4f4;
  min-height: 100vh;
  width: 100%;
}
</style>
        