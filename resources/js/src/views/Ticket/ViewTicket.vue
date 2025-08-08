 <template>
 <div class="dashboard">
  <div class="view-ticket-type">
    <div v-if="successMessage" class="alert alert-success">
      {{ successMessage }}
    </div>
  
    <b-card v-if="ticketType" class="details-card">
          <div class="page__breadcrumb">
     <p> <b-link :to="{ name: 'ticket' }" >  Ticket <i class="fas fa-chevron-right"></i></b-link> View</p>
    </div>
      <div class="mt-3">
         <b-overlay :show="ticketloader" rounded="lg" opacity="0.6" spinner-variant="primary" spinner-type="grow"
          class="base-loader">
        <b-row>
          <b-col md="3" class="p-0">
            <div class="information_box mb-1">
              <h4 class="card-title">
                 <!-- #TC-{{ ticketType.id }} -->
             {{ticketType.ticket_id}} - | {{ ticketType.ticket_name }}
              </h4>
              <b-card-body>
                <div class="row">
                  <div class="col-md-6">
                    <p><strong>Priority:</strong> {{ ticketType.priority }}</p>
                    <p>
                      <strong>Ticket Type:</strong> {{ ticketType.ticket_type }}
                    </p>
                    <p>
                      <strong>Requester/Client:</strong>
                      {{ ticketType.request_client_name }}
                    </p>
                    <p>
                      <strong>Assignee:</strong> {{ ticketType.assignee_name }}
                    </p>
                    <p><strong>Tags:</strong> {{ ticketType.tags }}</p>
                    <p>
                      <strong>Followers:</strong>
                      {{ ticketType.follower_names }}
                    </p>
                  </div>
                  <div class="col-md-6">
                    <div class="message-box">
                      <p><strong>Message:</strong></p>
                      <p>{{ ticketType.message }}</p>
                    </div>
                  </div>
                </div>
              </b-card-body>
            </div>
          </b-col>
          <b-col md="9" class="pr-0">
            <div class="information_box">
              <h5 class="title__view"></h5>
              <hr />
              <div class="p-2">
                <b-tabs class="staging_tab">
                  <b-tab @click="fetchconversations">
                    <template #title> Conversation </template>

                    <!-- Notes Section -->
                    <div class="notes-section">
                      <div
                        class="note"
                        v-for="(Conversation, index) in conversations"
                        :key="index"
                      >
                        <div class="d-flex align-items-center mb-2">
                          <b-avatar :variant="Conversation.avatarVariant">{{
                            Conversation.initials
                          }}</b-avatar>
                          <div style="margin-left: 10px;">
                            <strong>{{ Conversation.author }}</strong>
                            <small  style="margin-left: 10px;" class="text-muted">{{
                              Conversation.added_at
                            }}</small>
                          </div>
                        </div>
                        <div style="margin-bottom: 10px;">
                          <strong>{{ Conversation.conversation_type }}</strong>
                        </div>
                        <div>
                          <p>{{ Conversation.message }}</p>
                        </div>
                      </div>
                    </div>

                    <!-- Form Section -->
                    <b-form @submit.prevent="addConversation">
                      <b-row>
                        <!-- Via Section -->
                        <b-col md="3">
                          <b-form-group label="Via" label-for="via-select">
                            <v-select
                            id="conversation_type"
                            v-model="newConversation.via"
                            :options="viaOptions"
                            :reduce="(val) => val.value"
                            @change="RemoveError('via')"
                          />
                          <small class="text-danger">{{ errors[0] }}</small>
                          <div class="text-danger" v-if="hasErrors('via')">
                            {{ getErrors("via") }}
                          </div>
                          </b-form-group>
                        </b-col>

                        <!-- From Section -->
                        <b-col md="3">
                          <b-form-group label="From" label-for="from-select">
                            <v-select
                              id="from-select"
                              v-model="newConversation.author"
                              :options="users"
                              :reduce="(val) => val.value"
                              @change="RemoveError('author')"
                            />
                            <small class="text-danger">{{ errors[0] }}</small>
                            <div class="text-danger" v-if="hasErrors('author')">
                              {{ getErrors("author") }}
                            </div>
                          </b-form-group>
                        </b-col>
                      </b-row>

                      <!-- Message Input Section -->
                      <b-form-group>
                        <b-form-textarea
                          v-model="newConversation.message"
                          @input="RemoveError('message')"
                          placeholder="Enter here..."
                          rows="3"
                        ></b-form-textarea>
                        <small class="text-danger">{{ errors[0] }}</small>
                        <div class="text-danger" v-if="hasErrors('message')">
                          {{ getErrors("message") }}
                        </div>
                      </b-form-group>

                      <!-- Bottom Section (Dropdown + Send Button) -->
                      <b-row class="mt-2 align-items-center">
                        <b-col md="3">
                          <v-select
                            id="conversation_type"
                            v-model="newConversation.conversation_type"
                            :options="conversationtypeOptions"
                            :reduce="(val) => val.value"
                            @change="RemoveError('conversation_type')"
                          />
                          <small class="text-danger">{{ errors[0] }}</small>
                          <div class="text-danger" v-if="hasErrors('conversation_type')">
                            {{ getErrors("conversation_type") }}
                          </div>
                        </b-col>
                        <b-col class="text-right">
                          <b-button type="submit" variant="primary"
                            >Send</b-button
                          >
                        </b-col>
                      </b-row>
                    </b-form>
                  </b-tab>
                  <b-tab @click="fetchtask">
                    <template #title>
                      <h5>
                        <feather-icon icon="CalendarIcon" size="14" /> Task
                      </h5>
                    </template>
                    <div class="my-2 mx-1 d-flex flex-column">
                      <div class="my-2 d-flex justify-content-end mt-4">
                        <b-button
                          variant="info"
                          size="sm"
                          class="mr-1"
                          @click="toggleTaskFormVisibility"
                        >
                          <feather-icon icon="CheckCircleIcon" size="14" /> Add
                          Task
                        </b-button>
                      </div>
                      <h5 class="mt-9">Task List</h5>
                      <div
                        v-for="(activity, index) in gettaskdata"
                        :key="`activity-${index}`"
                        class="mt-9"
                      >
                        <b-card
                          :class="[
                            'timeline_box',
                            getTimelineBoxClass(activity.type),
                          ]"
                          style="margin-left: -10px; width: 500px"
                        >
                          <div class="p-1">
                            <h6 class="ml-2 text-left">{{ activity.title }}</h6>
                            <div class="ml-2 text-left">
                              {{ activity.description }}
                            </div>
                            <div class="ml-2 text-left">
                              {{ formatDateTime(activity.date, activity.time) }}
                            </div>
                            <div class="ml-2 text-left">
                              {{ activity.assignee_name }}
                            </div>
                          </div>
                        </b-card>
                      </div>
                      <b-modal
                        v-model="showEmailModal"
                        centered
                        ok-only
                        ok-title="Submit"
                        @ok="submitEmail"
                      >
                        <b-card-text>
                          <b-form @submit.stop.prevent="submitEmail">
                            <b-row>
                              <b-col cols="12" class="mb">
                                <b-form-group
                                  label="Email"
                                  label-for="email"
                                  label-cols-md="12"
                                  label-class="d-block"
                                >
                                  <b-form-textarea
                                    placeholder="Click here to write email..."
                                    class="staging_textareaemail"
                                    v-model="email.description"
                                    id="email"
                                  ></b-form-textarea>
                                </b-form-group>
                              </b-col>
                            </b-row>
                          </b-form>
                        </b-card-text>
                        <template #modal-footer>
                          <b-button
                            type="button"
                            variant="outline-secondary"
                            @click="showEmailModal = false"
                            class="mr-1"
                          >
                            <feather-icon icon="ChevronLeftIcon" size="14" />
                            Cancel
                          </b-button>
                          <b-button
                            variant="primary"
                            type="submit"
                            @click="submitEmail"
                          >
                            Submit
                            <feather-icon icon="ChevronRightIcon" size="14" />
                          </b-button>
                        </template>
                      </b-modal>
                    </div>
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
                           <span v-if="activity.type === 'conversation'">
                            Conversation added by <strong>{{ activity.created_by }}</strong> 
                            with description {{ activity.message }} on {{ formatDate(activity.created_at) }}
                          </span>
                          <span v-if="activity.type === 'ticket_created'">
                            Ticket created by <strong>{{ activity.created_by }}</strong> on {{ formatDate(activity.created_at) }}.
                            <!-- <i class="fas fa-eye ml-2" @click="openModal(activitiesModal.id,activity.event)"></i> -->
                            <i class="fas fa-eye ml-2" @click="openModal(activity.id,activity.event)"></i>
                          </span>
                          <span v-if="activity.type === 'ticket_updated'">
                            Ticket updated by <strong>{{ activity.user_name || activity.added_by }}</strong> on {{ formatDate(activity.created_at) }}.
                            <!-- <i class="fas fa-eye ml-2" @click="openModal(activitiesModal.id,activity.event)"></i> -->
                             <i class="fas fa-eye ml-2" @click="openModal(activity.id,activity.event)"></i>
                          </span>
                        </p>
                      </b-card>
                      <div v-if="activities.length === 0">
                        <p>No activities found for this ticket.</p>
                      </div>
                    </div>
                  <b-modal v-model="isModalVisible" centered 
         cancel-variant="outline-secondary" hide-footer 
         class="custom-modal-width">
  <div class="align-text-top text-capitalize myDescription" ref="modelDescription">
    <div class="row">
      <!-- New Data Section -->
      <div class="col-12 col-md-6">
        <div v-if="newActivity.description">
          <p class="font-weight-bold mb-2">New Data</p>
          <div class="table-responsive">
            <table class="table table-bordered">
              <tbody v-if="oldActivity.description">
                <tr v-for="(value, key) in filteredNewModelDescription" :key="key" 
                    :class="{ 'highlight': isDifferent(key) }">
                  <td>{{ key }}</td>
                  <td>
                    {{
                      key === 'created_at' || key === 'updated_at' || key === 'Created At' || key === 'Updated At' || key === 'added_at'
                        ? formatDateactivity(value)
                        : key === 'deleted_at'
                          ? (value === null ? '' : formatDateactivity(value))
                          : key === 'status'
                            ? (value === 'A' ? 'Active' : value === 'I' ? 'Inactive' : '')
                            : value
                    }}
                  </td>
                </tr>
              </tbody>
              <tbody v-if="!oldActivity.description">
                <tr v-for="(value, key) in filteredNewModelDescription" :key="key">
                  <td>{{ key }}</td>
                  <td>
                    {{
                      key === 'created_at' || key === 'updated_at' || key === 'Created At' || key === 'Updated At' || key === 'added_at'
                        ? formatDateactivity(value)
                        : key === 'deleted_at'
                          ? (value === null ? '' : formatDateactivity(value))
                          : key === 'status'
                            ? (value === 'A' ? 'Active' : value === 'I' ? 'Inactive' : '')
                            : value
                    }}
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Old Data Section -->
      <div class="col-12 col-md-6">
        <div v-if="oldActivity.description">
          <p class="font-weight-bold mb-2">Old Data</p>
          <div class="table-responsive">
            <table class="table table-bordered">
              <tbody>
                <tr v-for="(value, key) in filteredOldModelDescription" :key="key" 
                    :class="{ 'highlight': isDifferent(key) }">
                  <td>{{ key }}</td>
                  <td>
                    {{
                      key === 'created_at' || key === 'updated_at' || key === 'Created At' || key === 'Updated At' || key === 'added_at'
                        ? formatDateactivity(value)
                        : key === 'deleted_at'
                          ? (value === null ? '' : formatDateactivity(value))
                          : key === 'status'
                            ? (value === 'A' ? 'Active' : value === 'I' ? 'Inactive' : '')
                            : value
                    }}
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
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
                  <!-- <b-tab @click="loadActivities">
                    <template #title> Status </template>
                  </b-tab> -->
                </b-tabs>
              </div>
            </div>
          </b-col>
        </b-row>
         </b-overlay>
      </div>
    </b-card>
    <!-- <div v-else-if="loading">
      <p>Loading...</p>
    </div>
    <div v-else>
      <p>{{ error }}</p>
      <b-button @click="goBack" variant="primary">Go Back</b-button>
    </div> -->
    <b-col md="4" v-if="isTaskFormVisible">
      <h3>{{ taskData.id != null ? "Update Task ." : "Add Task " }}</h3>
      <b-form class="">
        <b-row>
          <b-col md="12">
            <b-form-group label="Title" label-for="Title">
              <b-form-input
                id="subject"
                v-model="taskData.title"
                autofocus
                trim
                placeholder="Enter Title"
                @input="RemoveError('title')"
              />
              <small class="text-danger">{{ errors[0] }}</small>
              <div class="text-danger" v-if="hasErrors('title')">
                {{ getErrors("title") }}
              </div>
            </b-form-group>
          </b-col>
          <b-col md="6">
            <b-form-group label="Priority" label-for="priority">
              <v-select
                v-model="taskData.priority"
                :options="priorityoption"
                :reduce="(val) => val.value"
                :clearable="true"
                input-id="status"
                placeholder="Select Priority"
                @input="RemoveError('priority')"
              />
              <small class="text-danger">{{ errors[0] }}</small>
              <div class="text-danger" v-if="hasErrors('priority')">
                {{ getErrors("priority") }}
              </div>
            </b-form-group>
          </b-col>
          <b-col md="12">
            <b-form-group label="Assignee" label-for="Assignee">
              <v-select
                v-model="taskData.assignee"
                label="label"
                value="value"
                :options="getassignee"
                placeholder="Select Assignee"
                :reduce="(val) => val.value"
                @input="RemoveError('assignee')"
              />
              <small class="text-danger">{{ errors[0] }}</small>
              <div class="text-danger" v-if="hasErrors('assignee')">
                {{ getErrors("assignee") }}
              </div>
            </b-form-group>
          </b-col>
         
          <b-col md="12">
            <b-form-group label="Reporter" label-for="Reporter">
              <v-select
                v-model="taskData.reporter"
                label="label"
                value="value"
                :options="getassignee"
                placeholder="Select Reporter"
                :reduce="(val) => val.value"
                @input="RemoveError('reporter')"
              />
              <small class="text-danger">{{ errors[0] }}</small>
              <div class="text-danger" v-if="hasErrors('reporter')">
                {{ getErrors("reporter") }}
              </div>
            </b-form-group>
          </b-col>
          <b-col md="12">
            <b-form-group label="Description" label-for="description">
              <b-form-textarea
                id="description"
                v-model="taskData.description"
                placeholder="Enter description here..."
                rows="5"
                @input="RemoveError('description')"
              ></b-form-textarea>
              <small class="text-danger">{{ errors[0] }}</small>
              <div class="text-danger" v-if="hasErrors('description')">
                {{ getErrors("description") }}
              </div>
            </b-form-group>
          </b-col>
          <b-col md="6">
            <b-form-group label="Start Date" label-for="start-date">
              <b-form-input
                type="date"
                id="start-date"
                v-model="taskData.date"
                placeholder="Select Date"
                required
                @input="RemoveError('date')"
              />
              <small class="text-danger">{{ errors[0] }}</small>
              <div class="text-danger" v-if="hasErrors('date')">
                {{ getErrors("date") }}
              </div>
            </b-form-group>
          </b-col>
          <b-col lg="6">
            <b-form-group label="Time" label-for="time">
              <b-form-input
                type="time"
                v-model="taskData.time"
                placeholder="Select Time"
                @input="RemoveError('time')"
              />
              <small class="text-danger">{{ errors[0] }}</small>
              <div class="text-danger" v-if="hasErrors('time')">
                {{ getErrors("time") }}
              </div>
            </b-form-group>
          </b-col>
          <b-col class="text-right mt-1">
            <b-button
              type="button"
              variant="primary-outline"
              @click="resetModal"
              class="mr-1"
            >
              <feather-icon icon="ChevronLeftIcon" size="14" /> Cancel
            </b-button>
            <b-button variant="primary" type="submit" @click="saveEntry">
              Submit <feather-icon icon="ChevronRightIcon" size="14" />
            </b-button>
          </b-col>
        </b-row>
      </b-form>
    </b-col>
  </div>
 </div>
</template>

<script>
import {
  BCard,
  BRow,
  BCol,
  BForm,
  BFormInput,
  BFormGroup,
  BFormCheckbox,
  BLink,
} from "bootstrap-vue-3";
import "vue-select/dist/vue-select.css";
import axios from "@axios";
import { ref, watch, computed, onUnmounted } from "vue";
import vSelect from "vue-select";
import Swal from "sweetalert2";
export default {
  components: {
    BCard,
    BRow,
    BCol,
    BForm,
    BFormInput,
    BFormGroup,
    vSelect,
    BFormCheckbox,
    BLink,
  },
  data() {
    const encodeBase64 = (data) => {
      return btoa(data.toString());
    };
    const decodeBase64 = (data) => {
      return decodeURIComponent(escape(atob(data))); // Decoding from Base64
    };

    return {
      isTaskFormVisible: false,
      errors: {},
      successMessage: "",
      ticketType: null,
      loading: true,
      priorityoption: [],
      getassignee: [],
      tickettypeoption: [],
      encodeBase64,
      decodeBase64,
      error: null,
      showEmailModal: false,
      email: { description: '' }, 
      gettaskdata: [], 

      activities: [],
      activitiesModal:[],
      isModalVisible: false,
      oldActivity: {},
      newActivity: {},
      notes: [],
      newNote: {
        author: "",
        message: "",
        note_for: "tickets",
        note_for_id: null,
      },
      conversations:[],
      newConversation:{
        author: "",
        message: "",
        conversation_type: "",
        via: "",
        added_for: "tickets",
        added_for_id: null,
      },
      viaOptions: [], // Add your via options here
      conversationtypeOptions: [], // Add your status options here

      users: [],
      ticketloader: false,
    };
  },
  created() {
    this.fetchTicketType();
    this.fetchtask();
    this.fetchNotes();
    this.fetchconversations();
    this.newNote.note_for_id = this.decodeBase64(this.$route.params.id);
    this.newConversation.added_for_id = this.decodeBase64(this.$route.params.id);
  },
  computed: {
    reorderedFields() {
      return [
        'Ticket Id',
        'Ticket Name',
        'priority',
        'Requester Client Name',
        'Ticket Type',
        'Assignee Name',
        'Tags',
        'Followers',
        'Message From',
        'Message',
        'Created At',
        'Updated At'
      ];
    },

    filteredNewModelDescription() {
      return this.reorderFieldsBySequence(this.newActivity.description);
    },

    filteredOldModelDescription() {
      return this.reorderFieldsBySequence(this.oldActivity.description);
    }
  },

  watch: {
    'taskData.priority': function () {
      this.RemoveError('priority');
    },
    'taskData.reporter': function () {
      this.RemoveError('reporter');
    },  
    'taskData.assignee': function () {
      this.RemoveError('assignee');
    }, 
    'newConversation.via': function () {
      this.RemoveError('via');
    },
    'newConversation.author': function () {
      this.RemoveError('author');
    },  
    'newConversation.conversation_type': function () {
      this.RemoveError('conversation_type');
    }, 
     'newNote.author': function () {
      this.RemoveError('author');
    }, 
  },
  mounted() {
    this.fetchTicketType();
    this.fetchNotes();
    this.fetchconversations();
    this.fetchActivityLogs();
    axios.get(`/getpriority`).then((response) => {
      this.priorityoption = response.data.data;
    });
    axios.get("/getassignee").then((response) => {
      this.getassignee = response.data.data;
    });
    axios.get("/getticketype").then((response) => {
      this.tickettypeoption = response.data.data;
    });
    axios.get("/getconversationTypes").then((response) => {
      this.conversationtypeOptions = response.data.data;
      this.viaOptions=response.data.viaOptions;
    });
    axios.get("/alluser").then((response) => {
      this.users = response.data.alluser;
    });
    this.fetchtask();
  },
  methods: {
    reorderFieldsBySequence(description) {
      const orderedEntries = this.reorderedFields
        .map(field => [field, description[field]]) 
        .filter(([key, value]) => value !== undefined); 

      return Object.fromEntries(orderedEntries); 
    },
    saveEntry() {
      const ticketId = this.$route.params.id;
      axios.post(`/tasks`, { ...this.taskData, ticket_id: ticketId })
        .then(response => {
        
          this.isTaskFormVisible = false;
          this.resetModal();
          this.fetchtask();
         
          
        })
        .catch((error) => {
          this.loading = false;
          if (error.response && error.response.data.code === 422) {
            this.errors = error.response.data.errors;
          } else {
          }
        });
    },
    resetNotes() {
      this.newNote.author = "";
      this.newNote.message = "";
      this.newNote.note_for = "tickets";
      this.newNote.note_for_id = this.decodeBase64(this.$route.params.id);
    },
    resetConversation(){
      this.newConversation.author = "";
      this.newConversation.message = "";
      this.newConversation.conversation_type = "";
      this.newConversation.via = "";
      this.newConversation.added_for = "tickets";
      this.newConversation.added_for_id = this.decodeBase64(this.$route.params.id);
    },
    addNote() {
      axios
        .post("/notes", this.newNote)
        .then((response) => {
          this.fetchNotes();
          Swal.fire({
            title: "Note Added Successful!",
            icon: "success",
            timer: 2000,
            showConfirmButton: false,
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
    addConversation(){
      axios
        .post("/conversations", this.newConversation)
        .then((response) => {
          this.fetchconversations();
          Swal.fire({
            title: "Conversation Added Successful!",
            icon: "success",
            timer: 2000,
            showConfirmButton: false,
          });
          setTimeout(() => {
            this.resetConversation();
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
    fetchActivityLogs() {
       this.ticketloader = true;
      this.errors = [];

      this.isTaskFormVisible = false;
      const id = this.$route.params.id;
      // this.loading = true;
      axios.get(`/ticket-activities/${id}`)
        .then((response) => {
           this.ticketloader = false;
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
           this.ticketloader = false;
          this.loading = false; 
          this.error = "Failed to fetch activities.";
        });
    },
    loadActivities() {
      this.fetchActivityLogs();
    },
    // openModal(activityId) {
    //   this.fetchModalActivity(activityId) 
    //   .then(() => {
    //         if (Array.isArray(this.activitiesModal) && this.activitiesModal.length > 0) {
    //           console.log(this.activitiesModal,'this.activitiesModal123');
    //             const activity = this.activitiesModal[0];
    //             if (activity && activity.properties) {
    //               console.log(activity.properties,'activity.properties123');
    //                 try {
    //                     const properties = JSON.parse(activity.properties);
    //                     this.oldActivity = properties.old ? { description: properties.old || 'No description' } : { description: 'No old data' };
    //                     this.newActivity = { description: properties.attributes || 'No description' };
    //                     this.isModalVisible = true;
    //                 } catch (error) {
    //                 }
    //             } else {
    //             }
    //         } else {
    //         }
    //     })
    //     .catch((error) => {
    //     });
    // },
//     openModal(activityId, event) {
//   console.log(activityId, 'activityId123'); 
//   this.fetchModalActivity(activityId)
//     .then(() => {
    
//       const activity = this.activitiesModal.find(item => item.id === activityId);

//       if (activity && activity.properties) {
//         try {
//           const properties = JSON.parse(activity.properties);

//           this.oldActivity = properties.old
//             ? { description: properties.old || 'No description' }
//             : { description: 'No old data' };

//           this.newActivity = { description: properties.attributes || 'No description' };
//           this.isModalVisible = true;

//         } catch (error) {
//           console.error("Error parsing properties:", error);
//         }
//       } else {
//         console.error("No activity found with the provided ID.");
//       }
//     })
//     .catch((error) => {
//       console.error("Error fetching activity:", error);
//     });
// },
openModal(activityId, event) {
    this.fetchModalActivity(activityId)
        .then(() => {
            if (Array.isArray(this.activitiesModal) && this.activitiesModal.length > 0) {
                const activity = event === 'created' ? this.activitiesModal[0] : this.activitiesModal.find(item => item.id === activityId);
                if (activity && activity.properties) {
                    try {
                        const properties = JSON.parse(activity.properties);
                        if (event === 'created') {
                            this.oldActivity = { description: '' }; 
                        } else {
                            this.oldActivity = properties.old ? { description: properties.old || 'No description' } : { description: 'No old data' };
                        }
                        this.newActivity = { description: properties.attributes || 'No description' };
                        this.isModalVisible = true;
                    } catch (error) {
                        console.error("Error parsing properties", error);
                    }
                }
            }
        })
        .catch((error) => {
        });
},
    fetchModalActivity() {
      const id = this.$route.params.id;
      return axios.get(`/getTicketOldNewActivity/${id}`)
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
    // fetchModalActivity(activityId) {
    //   // const id = this.$route.params.id;
    //   const id = activityId || this.$route.params.id;
    //   return axios.get(`/getTicketOldNewActivity/${id}`)
    //   .then(response => {
    //         if (response.data && Array.isArray(response.data.data)) {
    //             this.activitiesModal = response.data.data;
    //             console.log(this.activitiesModal,'this.activitiesModal35412')
    //         } else {
    //         }
    //         this.loading = false;
    //     })
    //     .catch((error) => {
    //         console.error("Failed to load activity details:", error);
    //         this.loading = false;
    //     });
    // },
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
    isDifferent(key) {
      return this.oldActivity.description[key] !== this.newActivity.description[key];
    },
    formatDateactivity(dateString) {
  if (!dateString) {
    return '';
  }
  const date = new Date(dateString);
  if (isNaN(date)) {
    return '';
  }
  const options = {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  };
  let formattedDate = new Intl.DateTimeFormat('en-GB', options).format(date).replace(',', '');
  let hours = date.getHours();
  const minutes = date.getMinutes();
  let ampm = 'AM';
  if (hours >= 12) {
    ampm = 'PM';
    if (hours > 12) {
      hours -= 12; 
    }
  } else {
    if (hours === 0) {
      hours = 12; 
    }
    ampm = 'AM';
  }
  const formattedMinutes = minutes < 10 ? `0${minutes}` : minutes;
  formattedDate = `${formattedDate.replace(/(\d{2}):(\d{2})/, `${hours}:${formattedMinutes}`)} ${ampm}`;
  return formattedDate;
      },
    getTimelineBoxClass(type) {
      const classes = {
        call: "timeline_box time_grey mt-1",
        task: "timeline_box time_blue mt-1",
        visit: "timeline_box time_green mt-1",
        notes: "timeline_box time_yellow mt-1",
        email: "timeline_box time_purple mt-1",
      };
      return classes[type] || "timeline_box time_default mt-1";
    },
    toggleTaskFormVisibility() {
      this.isTaskFormVisible = !this.isTaskFormVisible;
    },
    fetchtask() {
      this.errors = [];
      const id = this.$route.params.id;
      axios
        .get(`/gettask/${id}`)
        .then((response) => {
          this.gettaskdata = response.data.data;
          this.loading = false;
        })
        .catch(() => {
          this.error = "Failed to load ticket type.";
          this.loading = false;
        });
    },
    fetchNotes() {
      this.isTaskFormVisible = false;
      const id = this.$route.params.id;
      axios
        .get(`/notes/${id}`)
        .then((response) => {
          this.notes = response.data.data;
        })
        .catch(() => {
          this.error = "Failed to load notes.";
          this.loading = false;
        });
    },
    fetchconversations() {
      this.isTaskFormVisible = false;
      const id = this.$route.params.id;
      axios
        .get(`/conversations/${id}`)
        .then((response) => {
          this.conversations = response.data.data;
        })
        .catch(() => {
          this.error = "Failed to load notes.";
          this.loading = false;
        });
    },
    formatDateTime(dateString, timeString) {
      const combinedDateTime = `${dateString}T${timeString}`;
      const date = new Date(combinedDateTime);
      if (isNaN(date.getTime())) {
        return "Invalid Date";
      }
      const options = {
        weekday: "long",
        year: "numeric",
        month: "long",
        day: "numeric",
        hour: "2-digit",
        minute: "2-digit",
        hour12: true,
      };
      return date.toLocaleString("en-US", options);
    },
    fetchTicketType() {
      this.ticketloader = true;
      const id = this.$route.params.id;
      axios
        .get(`/tickets/${id}`)
        .then((response) => {
          this.ticketloader = false;
          this.ticketType = response.data.data;
          this.loading = false;
        })
        .catch(() => {
          this.ticketloader = false;
          this.error = "Failed to load ticket type.";
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
    resetModal() {
      this.taskData.title='',
      this.taskData.priority='',
      this.taskData.assignee='',
      this.taskData.status='',
      this.taskData.description='',
      this.taskData.reporter='',
      this.taskData.time='',
      this.taskData.date='',
      this.isTaskFormVisible = false;
      this.errors = [];
    },
    // goBack() {
    //   window.history.back();
    // },
    handleEmailButtonClick(id) {
      this.showEmailModal = true;
    },
    submitEmail() {
      this.showEmailModal = false;
    },
    loadActivities() {},
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
    const isTaskHandlerSidebarActive = ref(false);
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
    const taskData = ref(JSON.parse(JSON.stringify(blankUserData)));
    return {
      openAddForm,
      removeTask,
      taskData,
      isTaskHandlerSidebarActive,
      statusOptions,
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
 
}
 .dashboard {
  padding: 20px;
  background-color: #f4f4f4;
  min-height: 100vh;
  width: 100%;
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
  max-height: 300px; /* Adjust this height as per your design */
  overflow-y: auto;
  padding: 10px;
  border: 1px solid #eee; /* Optional, for a border around the scrollable area */
}
.note {
  border-bottom: 1px solid #eee;
  padding-bottom: 10px;
  margin-bottom: 10px;
}
</style>
