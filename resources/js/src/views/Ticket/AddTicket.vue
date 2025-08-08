<template>
  <div  class="dashboard"> 
    <div class="list__page">
      <div v-if="successMessage" class="alert alert-success">
        {{ successMessage }}
      </div>
        <!-- <h4><i class="fas fa-ticket"></i> Add Ticket </h4> -->
    <div class="page__breadcrumb">
     <p> <b-link :to="{ name: 'ticket' }" >  Ticket <i class="fas fa-chevron-right"></i></b-link> Add</p>
    </div>
      <b-container fluid>
        
        <b-form @submit.prevent="submitTicket">
                <b-row md="12">
                <!-- Ticket Name -->
                 <b-col md="3">

                   <label class="form-label">
                     Ticket Name <span style="color:red;"> *</span>
                    </label>
                   <b-form-group label-for="ticket-name">
                     <b-form-input
                       id="ticket-name"
                       v-model="TicketData.ticket_name"
                       placeholder="Enter here..."
                       trim
                       @input="removeError('ticket_name')"
                     />
                     <small class="text-danger">{{ errors[0] }}</small>
                     <div class="text-danger" v-if="hasErrors('ticket_name')">
                       {{ getErrors("ticket_name") }}
                     </div>
                   </b-form-group>
                 </b-col>

                <!-- Priority -->
                 <b-col md="3">
                  <label class="form-label">
                  Priority<span style="color:red;"> *</span>
                 </label>
                <b-form-group label-for="priority">
                  <b-button-group size="sm" id="priority">
                    <b-button
                      v-for="(label, index) in priorityOptions"
                      :key="index"
                      :variant="
                        TicketData.priority === label.value
                          ? label.variant
                          : 'outline-' + label.variant
                      "
                      @click="selectPriority(label.value)"
                    >
                      <font-awesome-icon :icon="label.icon" class="mr-1" />
                      {{ label.label }}
                    </b-button>
                  </b-button-group>
                  <div v-if="hasErrors('priority')" class="text-danger">
              {{ getErrors('priority') }}
            </div>
                </b-form-group>
                 </b-col>


                <!-- Ticket Type -->
                 <b-col md="3">

                   <label class="form-label">
                     Ticket Type <span style="color:red;"> *</span>
                    </label>
                   <b-form-group label-for="ticket-type">
                     <v-select
                       label="title"
                       value="id"
                       :reduce="(val) => val.id"
                       id="ticket-type"
                       placeholder="Select ticket type"
                       trim
                       v-model="TicketData.ticket_type"
                       :options="ticketTypeOptions"
                       @input="removeError('ticket_type')"
                     />
                     <small class="text-danger">{{ errors[0] }}</small>
                     <div class="text-danger" v-if="hasErrors('ticket_type')">
                       {{ getErrors("ticket_type") }}
                     </div>
                   </b-form-group>
                 </b-col>

                <!-- Requester/Client -->
                 <b-col md="3">

                   <label class="form-label">
                     Requester/Client <span style="color:red;"> *</span>
                    </label>
                   <b-form-group label-for="requester">
                   <v-select
                     id="requester"
                     v-model="TicketData.requester"  
                     :options="requesterOptions" 
                     placeholder="Select requester/client"
                     :reduce="(val) => val.value"  
                     label="label"
                     value="value"
                     @input="removeError('requester')"
                   />
                   <small class="text-danger">{{ errors[0] }}</small>
                     <div class="text-danger" v-if="hasErrors('requester')">
                       {{ getErrors("requester") }}
                     </div>
                 </b-form-group>
                 </b-col>

                <!-- Assignee -->
                 <b-col md="3">

                   <label class="form-label">
                     Assignee <span style="color:red;"> *</span>
                    </label>
                   <b-form-group label-for="assignee">
                     <v-select
                       id="assignee"
                       v-model="TicketData.assignee"
                       :options="assigneeOptions"
                       :reduce="(val) => val.value"
                       @input="removeError('assignee')"
                       placeholder="Select assignee"
                     />
                     <small class="text-danger">{{ errors[0] }}</small>
                     <div class="text-danger" v-if="hasErrors('assignee')">
                       {{ getErrors("assignee") }}
                     </div>
                   </b-form-group>
                 </b-col>

                <!-- Tags -->
                 <b-col md="3">

                   <label class="form-label">
                     Tags <span style="color:red;"> *</span>
                    </label>
                   <b-form-group label-for="tags">
                     <v-select
                       id="tags"
                       v-model="TicketData.tags"
                       :options="tagOptions"
                       label="title"
                       value="id"
                       :reduce="(val) => val.id"
                       multiple
                       @input="removeError('tags')"
                        placeholder="Select tag"
                     />
                     <small class="text-danger">{{ errors[0] }}</small>
                     <div class="text-danger" v-if="hasErrors('tags')">
                       {{ getErrors("tags") }}
                     </div>
                   </b-form-group>
                 </b-col>

                <!-- Followers -->
                 <b-col md="3">

                   <label class="form-label">
                     Followers 
                    </label>
                   <b-form-group label-for="followers">
                     <v-select
                       id="followers"
                       v-model="TicketData.followers"
                       :options="followerOptions"
                       :reduce="(val) => val.value"
                       multiple
                        placeholder="Select followers"
                     />
                   </b-form-group>
                 </b-col>

                 <!-- Requester/Client -->
                  <b-col md="3">

                    <label class="form-label">
                     Message From 
                    </label>
                    <b-form-group label-for="messsage_from">
                     <v-select
                       id="messsage_from"
                       v-model="TicketData.message_from"
                       :options="message_fromOptions"
                       :reduce="(val) => val.value"
                         placeholder="Select message from"
                     />
                   </b-form-group>
                  </b-col>
                  <b-col md="3">

                    <label class="form-label">
                      Message
                     </label>
                    <b-form-group label-for="message">
                        <b-form-textarea
                            id="message"
                            v-model="TicketData.message"
                            placeholder="Enter your message here..."
                            rows="3"
                        />
                    </b-form-group>
                  </b-col>

                <!-- Form Actions -->
                 <!-- v-if="!isLoading" -->
                <div class="d-flex justify-content-end">
                  <b-button variant="secondary" @click="cancelAndRedirect"
                    >Cancel</b-button
                  >
                  <b-button  type="submit" variant="primary" class="ml-2"
                    >Submit</b-button
                  >
                </div>
              </b-row>
              </b-form>
     
          
            </b-container>

    
    
    </div>
  </div>
</template>
  
  <script>
  
import "vue-select/dist/vue-select.css";
import axios from "@axios";
import vSelect from "vue-select";
import { ref, watch, computed } from "vue";
// import { useRouter } from 'vue-router';
import { useToast } from "vue-toastification"; // Import Toast functionality

export default {
  components: { vSelect },
 

  watch: {
   
    'TicketData.ticket_type': function () {
      this.removeError('ticket_type');
    },
    'TicketData.requester': function () {
      this.removeError('requester');
    },  'TicketData.assignee': function () {
      this.removeError('assignee');
    },  'TicketData.tags': function () {
      this.removeError('tags');
    },
      'TicketData.followers': function () {
      this.removeError('followers');
    },
    'TicketData.message_from': function () {
      this.removeError('message_from');
    },

  },
  props: {
    customerid: {
      type: Number,
      default: null,
      require: false,
    },
  },

  data() {
    const encodeBase64 = (data) => {
      return btoa(data.toString());
    }; const decodeBase64 = (data) => {
      return decodeURIComponent(escape(atob(data)));
    };
    return {


   

      TicketData: {
        ticket_name: "",
        priority: "",
        ticket_type: null,
        requester: null,
        assignee: null,
        tags: [],
        followers: [],
        message_from:null,
        message:"",
      },
      totalrows: 0,
      encodeBase64,decodeBase64,
      sortBy: "",
      sortDesc: false,
      successMessage: "",
      priorityOptions: [],
      ticketTypeOptions: [],
      requesterOptions: [],
      message_fromOptions: [],

      assigneeOptions: [],
      tagOptions: [],
      followerOptions: [],
    isLoading: false,
    
      errors: {},
    };
  },
  mounted() {
    axios.get(`/get-ticket-type`).then((response) => {
      this.ticketTypeOptions = response.data.data;
      this.priorityOptions = response.data.priorityOptions
    });
    const customerId = this.decodeBase64(this.$route.params.customerid);
  if (customerId && customerId!=0) {
      axios.get("/getallcustomer").then((response) => {
      this.requesterOptions = response.data.alluser;
    });
    this.TicketData.requester = parseInt(customerId); 
  }
   
    axios.get(`/get-ticket-teg`).then((response) => {
      this.tagOptions = response.data.data;
    });
    axios.get("/alluser").then((response) => {
      this.assigneeOptions = response.data.alluser;
      this.message_fromOptions = response.data.alluser;
      this.followerOptions = response.data.alluser;
    });

    axios.get("/getallcustomer").then((response) => {
      this.requesterOptions = response.data.alluser;
    });

  },
  methods: {
    selectPriority(value) {
      this.TicketData.priority = value;
      this.removeError('priority');
    },

    cancelAndRedirect() {
  const customerId = this.decodeBase64(this.$route.params.customerid);
  if(customerId && customerId!=0) {
    this.$router.push({ 
      name: 'view-customer', 
      params: { id: this.encodeBase64(customerId) } 
    });
  } else {
    this.$router.push({ name: 'ticket' });
  }
},


    submitTicket() {
      // this.isLoading = true;
      const toast = useToast();
      // this.errors = {};
  
   
        axios
          .post("/tickets", this.TicketData)
          .then((response) => {
          //  this.isLoading = false;
            toast.success("Ticket Added successfully!");
            this.resetForm();
            this.successMessage = "Ticket Created successfully!";
            this.clearSuccessMessage();

            const customerId = this.decodeBase64(this.$route.params.customerid);
            if(customerId && customerId!=0) {
            this.$router.push({ 
              name: 'view-customer', 
              params: { id: this.encodeBase64(customerId) } 
            });
          } else {
            this.$router.push({ name: 'ticket' });
          }
        
          })
          .catch((error) => {
            // this.isLoading = false;
            if (error.response.data.code == 422) {
              this.errors = error.response.data.errors;
            }
          });
    //   }
    },

 
 
    resetForm() {
  this.TicketData = {
    id: null,
    ticket_name: "",
    priority: "",
    ticket_type: null,
    requester: null, 
    assignee: null,
    tags: [],
    followers: [],
    message_from: null,
    message: "",
  };

  if (!this.customerid) {
  } else {
    this.TicketData.requester = parseInt(this.customerid); 
  }
},


    
   
 
    clearSuccessMessage() {
      setTimeout(() => {
        this.successMessage = "";
      }, 2000);
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
      return this.errors[fieldName] ? this.errors[fieldName][0] : '';
    },
  },
  setup(props){
      const filterData = ref({
      ticket_id: '',
      ticket_name: '',
      ticket_type: '',
      priority: '',
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
.list__page {
    padding: 20px;
    
    background-color: #fcfafa;
    min-height: 100vh;
  }
.ml-2 {
  margin-left: 0.5rem;
}


    .d-flex {
      display: flex;
      align-items: center;
    }
    
   
</style>
  