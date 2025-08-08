<template>
  <div class="view-ticket-type">
      <div v-if="successMessage" class="alert alert-success">
          {{ successMessage }}
      </div>
      <b-card v-if="customerData" class="details-card">
          <div class="mt-3">
              <b-row>
                  <b-col md="3" class="p-0">
                      <div class="information_box mb-1">
                          <h4 class="card-title">#TC-{{ customerData.id }} | {{ customerData.name }}</h4>
                          <b-card-body>
                              <div class="row">
                                  <div class="details-container">
                                      <div class="detail-item">
                                          <p>Contact Person: - <strong>{{ customerData.contactperson }}</strong></p>
                                      </div>
                                      <div class="detail-item">
                                          <p>Priority: - <strong>{{ customerData.priority }}</strong></p>
                                      </div>
                                      <div class="detail-item">
                                          <p><strong><i class="fas fa-envelope"></i>&nbsp;</strong> {{
          customerData.email }}</p>
                                      </div>
                                      <div class="detail-item">
                                          <p><strong> <i class="fas fa-phone"></i> &nbsp;</strong> {{
          customerData.contactnumber }}</p>
                                      </div>
                                  </div>
                                  <hr>
                                  <div class="details-container">
                                      <div class="detail-item">
                                          <p>Org. Name - <strong>qtech software</strong></p>
                                      </div>
                                      <div class="detail-item">
                                          <p>Assigned To - <strong>admin</strong></p>
                                      </div>
                                      <div class="detail-item">
                                          <p> Add Line 1 - <strong>Springfield, IL 62701</strong></p>
                                      </div>
                                      <div class="detail-item">
                                          <p> Add Line 2 - <strong>162 Maple street</strong></p>
                                      </div>
                                      <div class="detail-item">
                                          <p> State - <strong>maharatra</strong></p>
                                      </div>
                                      <div class="detail-item">
                                          <p> City - <strong>mumbai</strong></p>
                                      </div>
                                      <div class="detail-item">
                                          <p> Country - <strong>India</strong></p>
                                      </div>
                                      <div class="detail-item">
                                          <p> ZIP CODE - <strong>123456</strong></p>
                                      </div>
                                  </div>
                              </div>

                          </b-card-body>
                      </div>
                  </b-col>
                  <b-col md="9" class="pr-0">
                      <div class="information_box">
                          <h5 class="title__view"></h5>
                          <hr>
                          <div class="p-2">
                              <b-tabs class="staging_tab">
                                  <b-tab @click="loadActivities">
                                      <template #title>
                                          Health
                                      </template>
                                  </b-tab>
                                  <b-tab @click="loadActivities">
                                      <template #title>
                                          Product/Service
                                      </template>
                                  </b-tab><b-tab @click="loadActivities">
                                      <template #title>
                                          Invoice
                                      </template>
                                  </b-tab>
                                  <b-tab @click="toggleTicketHandler">
                                      <template #title>
                                          <h5><feather-icon icon="CalendarIcon" size="14" /> Ticket
                                          </h5>
                                      </template>
                                      <TicketEventHandler :customerid="decodedCustomerId"
                                          class="ticket-event-handler" />
                                  </b-tab>
                                 
              <b-tab @click="fetchActivityLogs">
                  <template #title>
                    Activity Logs
                  </template>
                  <div class="activity-logs-section">
                    <b-card v-for="(activity, index) in activities" :key="index" class="activity-log">
                      <p>                 
                        <span v-if="activity.type === 'task'">
                         Task added by <strong>{{ activity.created_by }}</strong> 
                         with description {{ activity.description }} on {{ formatDate(activity.created_at) }}
                        </span>
                        <span v-if="activity.type === 'note'">
                          Note added by <strong>{{ activity.created_by }}</strong> 
                          with description {{ activity.note }} on {{ formatDate(activity.created_at) }}
                        </span>
                        <span v-if="activity.type === 'customer_created'">
                          Ticket created by <strong>{{ activity.created_by }}</strong> on {{ formatDate(activity.created_at) }}.
                          <i class="fas fa-eye ml-2" @click="openModal(activitiesModal.id)"></i>
                        </span>
                        <span v-if="activity.type === 'customer_updated'">
                          Ticket updated by <strong>{{ activity.user_name || activity.added_by }}</strong> on {{ formatDate(activity.created_at) }}.
                          <i class="fas fa-eye ml-2" @click="openModal(activitiesModal.id)"></i>
                        </span>
                      </p>
                    </b-card>
                    <div v-if="activities.length === 0">
                      <p>No activities found for this ticket.</p>
                    </div>
                  </div>
                  <b-modal v-model="isModalVisible" title="Activity Details">
                    <div>
                        <h5>Old Activity:</h5>
                        <p>{{ oldActivity.description }}</p>
                        <h5>New Activity:</h5>
                        <p>{{ newActivity.description }}</p>
                    </div>
                    <template #modal-footer="{ ok }">
                        <b-button variant="secondary" @click="isModalVisible = false">Close</b-button>
                    </template>
                    </b-modal>
                    </b-tab>
                  <b-tab @click="fetchNotes">
                  <template #title> Notes </template>
                  <div class="notes-section">
                    <div
                      class="note"
                      v-for="(note, index) in notes"
                      :key="index"
                    >
                      <div class="d-flex align-items-center mb-2">
                        <b-avatar :variant="note.avatarVariant">{{
                          note.initials
                        }}</b-avatar>
                        <div class="ml-2">
                          <strong>{{ note.author }}</strong>
                          <small class="text-muted">{{
                            note.added_at
                          }}</small>
                        </div>
                      </div>
                      <div>
                        <p>{{ note.note }}</p>
                      </div>
                    </div>
                  </div>
                  <b-form @submit.prevent="addNote">
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
                    <b-button type="submit" variant="primary"
                      >Submit</b-button
                    >
                  </b-form>
                </b-tab>
                              </b-tabs>
                          </div>
                      </div>
                  </b-col>
              </b-row>
          </div>
      </b-card>
      <div v-else-if="loading">
          <p>Loading...</p>
      </div>
      <div v-else>
          <p>{{ error }}</p>
          <b-button @click="goBack" variant="primary">Go Back</b-button>
      </div>
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
  components: {
      BCard, BRow, BCol, BForm, BFormInput, BFormGroup,
      vSelect, BFormCheckbox, BLink, TicketEventHandler
  },
  data() {
    const encodeBase64 = (data) => {
      return btoa(data.toString());
    };
    const decodeBase64 = (data) => {
      return decodeURIComponent(escape(atob(data)));
    };

    return {
      showTicketHandler: false, 
      isTaskFormVisible: false,
      errors: {},
      successMessage: "",
      ticketType: null,
      // loading: true,
   
      priorityOptions: [],
    ticketTypeOptions: [],
    requesterOptions: [],
    assigneeOptions: [],
    tagOptions: [],
    followerOptions: [],
    users: [],

      encodeBase64,
      decodeBase64,
      error: null,
      showEmailModal: false,
      email: { description: '' }, 
      gettaskdata: [], 

      notes: [],
      newNote: {
        author: '',
        message: '',
        note_for:'customers',
        note_for_id:null,
      },
      activities: [],
      activitiesModal:[],
      isModalVisible: false,
      oldActivity: {},
      newActivity: {},
      users: []
    };
  },
  created() {
      this.fetchcustomer();
      this.fetchNotes();
      this.newNote.note_for_id = this.decodeBase64(this.$route.params.id);
  },
  watch: {
   'newNote.author': function () {
    this.RemoveError('author');
  }, 
},

  mounted() {
  this.fetchActivityLogs();

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
          this.users= response.data.alluser;
      });
  },
  computed: {
  decodedCustomerId() {
    return this.$route.params.id ? atob(this.$route.params.id) : null;
  },
},
  methods: {
      toggleTicketHandler() {
          this.showTicketHandler = !this.showTicketHandler;
      },
    
      formatDate(dateString) {
          const date = new Date(dateString);
          const monthNames = [
              "January", "February", "March",
              "April", "May", "June", "July",
              "August", "September", "October",
              "November", "December"
          ];
          const day = date.getDate();
          const monthIndex = date.getMonth();
          return monthNames[monthIndex] + ' ' + day;
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
    clearSuccessMessage() {
      setTimeout(() => {
        this.successMessage = "";
      }, 2000);
    },
    fetchcustomer() {
      const id = this.$route.params.id;
      axios.get(`/customer/${id}`)
          .then(response => {
              this.customerData = response.data.data;
              this.loading = false;
          })
          .catch(() => {
              this.error = "Failed to load ticket type.";
              this.loading = false;
          });
      },
    formatDateTime(dateString, timeString) {
      const combinedDateTime = `${dateString}T${timeString}`;
      const date = new Date(combinedDateTime);
      if (isNaN(date.getTime())) {
        return 'Invalid Date';
      }
      const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit', hour12: true };
      return date.toLocaleString('en-US', options);
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
    resetModal() {
      this.ticketData.title='',
      this.ticketData.priority='',
      this.ticketData.assignee='',
      this.ticketData.status='',
      this.ticketData.description='',
      this.ticketData.reporter='',
      this.ticketData.time='',
      this.ticketData.date='',
      this.isTaskFormVisible = false;
      this.errors = [];
    },
    goBack() {
      window.history.back();
    },
    
    fetchActivityLogs() {
      const id = this.$route.params.id;
      // this.loading = true;
      axios.get(`/customer-activities/${id}`)
        .then((response) => {
          this.loading = false;
          if (response && response.data && response.data.status === 'success') {
            this.activities = (response.data.data || []).map(activity => {
              return {
                ...activity,
              };
            });
          } else {
          }
        })
        .catch(() => {
          this.loading = false; 
          this.error = "Failed to fetch activities.";
        });
    },
    loadActivities() {
      this.fetchActivityLogs();
    },
    openModal() {
      const id = this.$route.params.id;
      this.fetchModalActivity(id)
          .then(() => {
              if (Array.isArray(this.activitiesModal) && this.activitiesModal.length > 0) {
                  const activity = this.activitiesModal[0];
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
          this.loading = false;
   });
  },
  },
  setup() {
      const statusOptions = [
          { label: "Open", value: "open" },
          { label: "In Progress", value: "inprogress" },
          { label: "Resolve", value: "resolved" },

          { label: "Closed", value: "closed" },
      ];
      const openAddForm = (id, taskID) => {
          if (id) {

              isTaskHandlerSidebarActive.value = true;
          }
      };
      const removeTask = () => {
          isTaskHandlerSidebarActive.value = true;
          isTaskHandlerSidebarActive.value = !isTaskHandlerSidebarActive.value;
      };
      const isTaskHandlerSidebarActive = ref(false)
      const blankUserData = {
          id: null,
          title: "",
          priority: "",
          assignee: "",
          status: "",
          reporter: "",
          date: "",
          time: "",
          description: "",
      };
      const ticketData = ref(JSON.parse(JSON.stringify(blankUserData)));
      return {
          openAddForm,
          removeTask, ticketData,
          isTaskHandlerSidebarActive, statusOptions
      };
  },
};
</script>

<style scoped>
.view-ticket-type {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  min-height: 100vh;
}

.details-card {
  width: 100%;
  max-width: 1500px;
  margin-top: 10px;
  text-align: left;
}

.information_box {
  background-color: #f7f7f7;
  padding: 10px;
  border: 1px solid #ddd;
  border-radius: 5px;
  box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
}

.message-box {
  background-color: #f7f7f7;
  padding: 10px;
  border: 1px solid #ddd;
  border-radius: 5px;
  box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
}

.title__view {
  font-weight: bold;
  margin-bottom: 10px;
}

.mt-4 {
  margin-top: 4.5rem;
}

.notes-section {
  max-height: 300px;
  overflow-y: auto;
  padding: 10px;
  border: 1px solid #eee;
}

.note {
  border-bottom: 1px solid #eee;
  padding-bottom: 10px;
  margin-bottom: 10px;
}

.ticket-event-handler {
  margin-bottom: -30px;
  align-items: center;
  justify-content: center;

}
</style>