<template>
  <div class="container mt-4">
    <b-card class="p-3">
        <div class="page__breadcrumb">
     <p> <b-link :to="{ name: 'customer' }" >  Customer <i class="fas fa-chevron-right"></i></b-link> View</p>
    </div>
      <b-row>
        <b-col md="4">
          <h5>{{ userData.customer_id }}- | {{ userData.name }}</h5>
          <ul class="list-unstyled">
            <li><strong>Contact Person:</strong> {{ userData.name }}</li>
            <li><strong>Priority:</strong> {{ userData.priority }}</li>
            <li><strong>Email:</strong> {{ userData.email }}</li>
            <li><strong>Phone:</strong> {{ userData.contactnumber }}</li>
            <li><strong>Org. Name:</strong>{{ userData.orgName ?? "Qtech Software" }}  </li>
            <li><strong>Assigned To:</strong>{{ userData.assignee_name ?? "Admin" }} </li>
            <li><strong>Add Line 1:</strong> {{ userData.engagement }}</li>
            <li><strong>Add Line 2:</strong> {{ userData.addLine2 ?? "Mumbai" }}</li>
            <li><strong>State:</strong> {{ userData.state ?? "Mumbai" }}</li>
            <li><strong>City:</strong> {{ userData.city ?? "Mumbai" }}</li>
            <li><strong>Country:</strong> {{ userData.country ?? "India" }}</li>
            <li><strong>ZIP CODE:</strong> {{ userData.zip ?? 123455 }}</li>
          </ul>
        </b-col>
        <b-col md="8">
          <b-tabs>
            <b-tab title="Health">
              <p>Health content here...</p>
            </b-tab>
            <b-tab title="Product/Service">
              <p>Product/Service content here...</p>
            </b-tab>
            <b-tab title="Invoice">
              <p>Invoice content here...</p>
            </b-tab>
            <b-tab @click="toggleTicketHandler" title="Ticket">
           <h5><feather-icon icon="CalendarIcon" size="14" />
                     </h5>
                     <TicketEventHandler 
                :customerid="this.$route.params.id" 
                class="ticket-event-handler"
                :customerview="'customerview'"
              />

            </b-tab>
            <b-tab title="Activity Logs" @click="fetchActivityLogs">
  <div class="activity-logs-section">
    <b-card
      v-for="(activity, index) in activities"
      :key="index"
      class="activity-log"
    >
      <p>
        <span v-if="activity.type === 'ticket'">
          Ticket added by
          <strong>{{ activity.created_by }}</strong> on
          {{ formatDate(activity.created_at) }}
        </span>
        <span v-if="activity.type === 'note'">
          Note added by
          <strong>{{ activity.created_by }}</strong> with
          description {{ activity.note }} on
          {{ formatDate(activity.created_at) }}
        </span>
        <span v-if="activity.type === 'customer_created'">
          Customer created by
          <strong>{{ activity.created_by }}</strong> on
          {{ formatDate(activity.created_at) }}.
          <i
            class="fas fa-eye ml-2"
            @click="openModal(activity.id,activity.event)"
          ></i>
        </span>
        <span v-if="activity.type === 'customer_updated'">
          Customer updated by
          <strong>{{ activity.created_by }}</strong> on
          on {{ formatDate(activity.created_at) }}.
          <i
            class="fas fa-eye ml-2"
            @click="openModal(activity.id,activity.event)"
          ></i>
        </span>
      </p>
    </b-card>
    <div v-if="activities.length === 0">
      <p>No activities found for this ticket.</p>
    </div>
  </div>
</b-tab>

            <b-tab @click="fetchNotes">
    <template #title> Notes </template>

    <b-form @submit.prevent="addNote" class="mt-4">
      <b-form-group label="From" label-for="from-select">
        <v-select
          id="assignee"
          v-model="newNote.author"
          :options="users"
          :reduce="(val) => val.value"
          @change="RemoveError('author')"
        />
        <small class="text-danger">{{ errors[0] }}</small>
        <div class="text-danger" v-if="hasErrors('author')">
          {{ getErrors("author") }}
        </div>
      </b-form-group>

      <b-form-group>
        <b-form-textarea
          v-model="newNote.message"
          @input="RemoveError('message')"
          placeholder="Enter here..."
          rows="3"
        ></b-form-textarea>
        <small class="text-danger">{{ errors[0] }}</small>
        <div class="text-danger" v-if="hasErrors('message')">
          {{ getErrors("message") }}
        </div>
      </b-form-group>

      <b-button type="submit" variant="primary">Submit</b-button>
    </b-form>

    <!-- Scrollable Notes Section -->
    <div class="notes-section mt-2">
      <div
        class="note"
        v-for="(note, index) in notes"
        :key="index"
      >
        <div class="d-flex align-items-center mb-2">
          <b-avatar :variant="note.avatarVariant">{{ note.initials }}</b-avatar>
          <div class="datetime">
            <strong>{{ note.author }}</strong>
            <small class="text-muted datetime">{{ formatDatetime(note.added_at) }}</small>
          </div>
        </div>
        <div>
          <p>{{ note.note }}</p>
        </div>
      </div>
    </div>
  </b-tab>

             
          </b-tabs>
        </b-col>
      </b-row>
    </b-card>
    <b-modal v-model="isModalVisible" title="Activity Details">
      <div>
        <h5>Old Activity:</h5>
        <p>{{ oldActivity.description }}</p>
        <h5>New Activity:</h5>
        <p>{{ newActivity.description }}</p>
      </div>
      <template #modal-footer="{ ok }">
        <b-button variant="secondary" @click="isModalVisible = false"
          >Close</b-button
        >
      </template>
    </b-modal>
  </div>
</template>

<script>
import {
  BCard, BRow, BCol, BForm, BFormInput, BFormGroup, BFormCheckbox, BLink,
} from 'bootstrap-vue-3';
import "vue-select/dist/vue-select.css";
import axios from '@axios';
import TicketEventHandler from "../Ticket/Ticket.vue";
import { ref, watch, computed, onUnmounted } from 'vue';
import vSelect from "vue-select";
import Swal from 'sweetalert2';

export default {
  name: "UserDetailCard",
   components: {
      BCard, BRow, BCol, BForm, BFormInput, BFormGroup,
      vSelect, BFormCheckbox, BLink, TicketEventHandler
  },
  data() {
    const decodeBase64 = (data) => {
      return decodeURIComponent(escape(atob(data)));
    };

    return {
            errors: {},

      activities: [],
      isModalVisible: false,
      oldActivity: {},
      newActivity: {},
      activitiesModal: [],
      showTicketHandler:false,
      notes:[],
      newNote: {
        author: '',
        message: '',
        note_for:'customers',
        note_for_id:null,
      },
      ticketTypeOptions:[],
      priorityOptions:[],
      tagOptions:[],
      assigneeOptions:[],
      requesterOptions:[],
      followerOptions:[],
      users:[],
      decodeBase64
    };
    
  },
  computed: {
  decodedCustomerId() {
    return this.$route.params.id ? atob(this.$route.params.id) : null;
  }
},
created() {
  
      this.fetchNotes();
      this.newNote.note_for_id = this.decodeBase64(this.$route.params.id);
  },
  watch: {
   'newNote.author': function () {
    this.RemoveError('author');
  }, 
},
  mounted() {
    const id = this.$route.params.id;
    axios
      .get(`/customer/${id}`)
      .then((response) => {
        this.userData = response.data.data;
      })
      .catch(() => {
        this.error = "Failed to load ticket type.";
        this.loading = false;
      });
      axios.get("/getallcustomer").then((response) => {
      this.users = response.data.alluser;
    });
    this.fetchActivityLogs();
    // this.fetchNotes();
    axios.get(`/get-ticket-type`).then((response) => {
          this.ticketTypeOptions = response.data.data;
          this.priorityOptions = response.data.priorityOptions
      });
      axios.get(`/get-ticket-teg`).then((response) => {
          this.tagOptions = response.data.data;
      });
      axios.get("/alluser").then((response) => {
          this.assigneeOptions = response.data.alluser;
          this.requesterOptions = response.data.alluser;
          this.followerOptions = response.data.alluser;
          // this.users= response.data.alluser;
      });
  },
  methods: {
    formatDatetime(dateString) { 
      const date = new Date(dateString);
      const options = {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        hour12: true, 
      };
      return new Intl.DateTimeFormat('en-GB', options).format(date).replace(',', '');
      
    },

    fetchNotes() {
          const id = this.$route.params.id;
          axios.get(`/notes/${id}`)
              .then(response => {
                  this.notes = response.data.data;
              })
              .catch(() => {
                  this.error = "Failed to load notes.";
                  this.loading = false;
              });
      },
      hasErrors(fieldName) {
      return fieldName in this.errors;
    },
    getErrors(fieldName) {
      return this.errors[fieldName][0];
    },
    RemoveError(errorName) {
      this.errors[errorName] = " ";
    },
   
    resetNotes(){
      this.newNote.author="";
      this.newNote.message="";
      this.newNote.note_for="tickets";
      this.newNote.note_for_id = this.decodeBase64(this.$route.params.id);
    },
    addNote() {
        axios
          .post("/notes", this.newNote)
          .then((response) => {
            this.fetchNotes();
            Swal.fire({
              title: 'Note Added Successful!',
                icon: 'success',
              timer: 2000,
              showConfirmButton: false
            });
            setTimeout(() => {
              this.resetNotes();
            }, 2000);
            })
          .catch((error) => {
            this.loading = false;
            if (error.response.data.code == 422) {
              this.errors = error.response.data.errors;
            }
          });
    },
    toggleTicketHandler() {
          this.showTicketHandler = !this.showTicketHandler;
      },
    openModal(activityId,event) {
    this.fetchModalActivity(activityId) 
    .then(() => {
          if (Array.isArray(this.activitiesModal) && this.activitiesModal.length > 0) {
              const activity = event=='created'?this.activitiesModal[0]:this.activitiesModal.find(item => item.id === activityId);
              if (activity && activity.properties) {
                  try {
                      const properties = JSON.parse(activity.properties);
                      this.oldActivity = properties.old ? { description: properties.old || 'No description' } : { description: 'No old data' };
                      this.newActivity = { description: properties.attributes || 'No description' };
                      this.isModalVisible = true;
                  } catch (error) {
                  }
              } else {
              }
          } else {
          }
      })
      .catch((error) => {
      });
    },

    fetchModalActivity() {
      const id = this.$route.params.id;
      return axios.get(`/getCustomerOldNewActivity/${id}`)
      .then(response => {
            if (response.data && Array.isArray(response.data.data)) {
                this.activitiesModal = response.data.data;
      
            } else {
            }
            this.loading = false;
        })
        .catch((error) => {
            console.error("Failed to load activity details:", error);
            this.loading = false;
        });
    },
    fetchActivityLogs() {
      const id = this.$route.params.id;
      axios
        .get(`/customer-activities/${id}`)
        .then((response) => {
          if (response && response.data && response.data.status === "success") {
            this.activities = (response.data.data || []).map((activity) => {
              return {
                ...activity,
              };
            });
          } else {
          }
      this.fetchNotes();

        })
        .catch(() => {
          this.loading = false;
          this.error = "Failed to fetch activities.";
        });
    },
    formatDate(dateString) {
      const date = new Date(dateString);
      const monthNames = [
        "January",
        "February",
        "March",
        "April",
        "May",
        "June",
        "July",
        "August",
        "September",
        "October",
        "November",
        "December",
      ];
      const day = date.getDate();
      const monthIndex = date.getMonth();
      return monthNames[monthIndex] + " " + day;
    },
    fetchUserData() {
      // Simulate fetching data from an API
      console.log("Fetching user data...");
    },
  },
  setup() {
    const userData = ref({
      name: "",
      contactPerson: "",
      priority: "-",
      email: "",
      phone: "",
      orgName: "",
      assignedTo: "",
      addLine1: "",
      addLine2: "",
      state: "",
      city: "",
      country: "",
      zip: "",
    });
    return {
      userData,
    };
  },
};
</script>

<style scoped>
.list-unstyled {
  padding-left: 0;
  margin: 0;
}
.datetime {
  margin-left: 10px; 
}


</style>
<style scoped>
.notes-section {
  max-height: 400px;
  overflow-y: auto;
  padding-right: 15px;
}

.note {
  margin-bottom: 15px;
}
.activity-logs-section {
  max-height: 500px; 
  overflow-y: auto;  
  margin-bottom: 15px;
  padding-right: 15px; 
}

.activity-log {
  margin-bottom: 10px;
}

.activity-logs-section > div {
  text-align: center;
  padding: 10px;
  font-style: italic;
}

</style>