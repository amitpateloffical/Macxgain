<template>
  <div class="listing_screen">
    <div class="title_line_screen px-4 py-2">
      <div class="title_line_left">
        <h5>Tickets</h5>
        <v-select v-if="ticketData.tabledata.length" :options="filterFieldsData.filterSelectField" label="profile_name"
          class="v_select_profile">
          <template #option="{ profile_name, profile_image }">
            <span class="table_img_cell">
              <span class="table_img">
                <img src="`../assest/img/tableprofileimg.png`" alt="profile">
              </span> {{ profile_name }}
            </span>
          </template>
        </v-select>
        <span class="titlelineicon" @click="sidebarstatus.filter = !sidebarstatus.filter"
          v-if="ticketData.tabledata.length">
          <filterIcon /> Filters
        </span>
        <span class="titlelineicon" v-if="ticketData.tabledata.length">
          <deleteIcon /> Delete
        </span>
      </div>
      <div class="title_line_right">
        <i class="fa-regular fa-rectangle-list" @click="ticketData.tableGridStatus = !ticketData.tableGridStatus" v-if="ticketData.tabledata.length && ticketData.tableGridStatus"></i>
        <i class="fa-solid fa-table-cells" @click="ticketData.tableGridStatus = !ticketData.tableGridStatus" v-if="ticketData.tabledata.length && !ticketData.tableGridStatus"></i>        
        <b-button class="btn_primary" @click="sidebarstatus.add = !sidebarstatus.add">Add Ticket</b-button>
      </div>
    </div>
    <div class="mt-4 px-4">
      <b-overlay :show="ticketData.loader" rounded="lg" opacity="0.8" class="loader_section" no-wrap>
        <template #overlay>
          <span class="loadersdots"></span>
        </template>
        <div class="table_listing" v-if="ticketData.tabledata.length">
          <b-table :items="ticketData.tabledata" :fields="ticketData.tablefield" v-if="ticketData.tableGridStatus">
            <template #head(select)="data">
              <b-form-checkbox>All</b-form-checkbox>
            </template>
            <template #cell(select)="data">
              <b-form-checkbox></b-form-checkbox>
            </template>
            <template #cell(priority)="data">
              <span v-if="data.value == 'High'" class="badgehigh">{{ data.value }}</span>
              <span v-if="data.value == 'Medium'" class="badgemedium">{{ data.value }}</span>
              <span v-if="data.value == 'Low'" class="badgelow">{{ data.value }}</span>
            </template>
            <template #cell(ticket_id)="data">
              <b-link @click="sidebarstatus.view = !sidebarstatus.view">{{ data.value }}</b-link>
            </template>
            <template #cell(requester)="data">
              <span class="table_img_cell"><span class="table_img"><img
                    src="../assest/img/tableprofileimg.png" alt="profile" /></span>{{ data.value }}</span>
            </template>
            <template #cell(actions)="data">
              <div class="grid_table_action">
                <i class="fa-solid fa-ellipsis" @click="toggletabledropdown($event)"></i>
                <div class="table_action_dropdown">
                  <ul>
                    <li>Edit</li>
                    <li>Make Copy</li>
                    <li>Merge Ticket</li>
                    <li>Close</li>
                    <li>Delete</li>
                  </ul>
                </div>
              </div>
            </template>
          </b-table>
          <div class="table_grid_view" v-else>
            <div class="table_grid_list">

              <div class="table_grid_list_item border_warnning">
                <div class="table_grid_title">
                  <span class="table_grid_label">#TC-190</span>
                  <span class="table_grid_title_right">
                    <p><span>Request Date:</span> 10/10/2024</p>
                    <div class="table_grid_action">
                      <i class="fa-solid fa-ellipsis" @click="toggletabledropdown($event)"></i>
                      <div class="table_action_dropdown">
                        <ul>
                          <li>Edit</li>
                          <li>Make Copy</li>
                          <li>Merge Ticket</li>
                          <li>Close</li>
                          <li>Delete</li>
                        </ul>
                      </div>
                    </div>
                  </span>
                </div>
                <div class="table_grid_item_details">
                  <h4>ABC Corp</h4>
                  <p>Our client ABC Corp is experiencing issues with their website taking too long to load. Please
                    investigate the issue and provide a solution.</p>
                  <span class="table_grid_sub_label">Type</span>
                  <p>Dummy type</p>
                </div>
              </div>

              <div class="table_grid_list_item border_hot">
                <div class="table_grid_title">
                  <span class="table_grid_label">#TC-190</span>
                  <span class="table_grid_title_right">
                    <p><span>Request Date:</span> 10/10/2024</p>
                    <div class="table_grid_action">
                      <i class="fa-solid fa-ellipsis" @click="toggletabledropdown($event)"></i>
                      <div class="table_action_dropdown">
                        <ul>
                          <li>Edit</li>
                          <li>Make Copy</li>
                          <li>Merge Ticket</li>
                          <li>Close</li>
                          <li>Delete</li>
                        </ul>
                      </div>
                    </div>
                  </span>
                </div>
                <div class="table_grid_item_details">
                  <h4>ABC Corp</h4>
                  <p>Our client ABC Corp is experiencing issues with their website taking too long to load. Please
                    investigate the issue and provide a solution.</p>
                  <span class="table_grid_sub_label">Type</span>
                  <p>Dummy type</p>
                </div>
              </div>

            </div>
          </div>
        </div>
        <div class="No_data_available" v-else>
          <div>
            <img src="../assest/img/ticket/no_ticket.svg" alt="no_ticket">
            <p>No Tickets Found</p>
            <span>No tickets added.</span>
            <span>Click <b>'add ticket'</b> to start!</span>
          </div>
        </div>
      </b-overlay>
    </div>

    <div class="backdrop_shadow" v-if="sidebarstatus.shadow"></div>

    <!-- filter sidebar -->
    <div class="filter_sidebar sidebar_main" :class="[sidebarstatus.filter ? 'filter_active' : '']">
      <div class="sidebar_toolbox">
        <h6>Filter Ticket</h6>
        <div class="sidebar_toolbox_right_side">
          <b-link @click="sidebarstatus.filter = !sidebarstatus.filter">
            <CloseIcon />
          </b-link>
        </div>
      </div>
      <div class="sidebar_form">
        <b-form>
          <div class="px-4 py-3 column_sidebar">
            <!-- Ticket Name -->
            <label for="ticket-name">Ticket Name <span>*</span></label>
            <b-form-group label-for="ticket-name">
              <b-form-input />
              <small class="text-danger"></small>
              <div class="text-danger"></div>
            </b-form-group>

            <!-- Priority -->
            <label for="priority">Priority<span> *</span></label>
            <b-form-group label-for="priority" class="radio_group">
              <b-form-radio name="radio_priority" v-model="prioritystatus" button button-variant="outline_high"
                class="radio_btn"><span></span> High</b-form-radio>
              <b-form-radio name="radio_priority" v-model="prioritystatus" button button-variant="outline_medium"
                class="radio_btn"><span></span> Medium</b-form-radio>
              <b-form-radio name="radio_priority" v-model="prioritystatus" button button-variant="outline_low"
                class="radio_btn"><span></span> Low</b-form-radio>
            </b-form-group>

            <!-- Ticket Type -->
            <label for="ticket-type">Ticket Type <span> *</span></label>
            <b-form-group label-for="ticket-type">
              <v-select label="title" value="id" id="ticket-type" placeholder="Select ticket type" />
            </b-form-group>

            <!-- Requester/Client -->
            <label for="requester">Requester/Client <span> *</span></label>
            <b-form-group label-for="requester">
              <v-select id="requester" placeholder="Select requester/client" label="label" value="value" />
            </b-form-group>

            <!-- Assignee -->
            <label for="assignee">Assignee <span> *</span></label>
            <b-form-group label-for="assignee">
              <v-select id="assignee" placeholder="Select assignee" />
            </b-form-group>

            <!-- Tags -->
            <label for="Tags">Tags <span> *</span></label>
            <b-form-group label-for="tags">
              <v-select id="tags" label="title" value="id" placeholder="Select tag" />
            </b-form-group>

            <!-- Followers -->
            <label for="Followers">Followers</label>
            <b-form-group label-for="followers">
              <v-select id="followers" multiple placeholder="Select followers" />
            </b-form-group>

            <!-- Requester/Client -->
            <label for="messsage_from">Message From</label>
            <b-form-group label-for="messsage_from">
              <v-select id="messsage_from" placeholder="Select message from" />
            </b-form-group>

            <label for="message">Message</label>
            <b-form-group label-for="message">
              <b-form-textarea id="message" placeholder="Enter your message here..." rows="2" />
            </b-form-group>
          </div>
          <!-- Form Actions -->
          <div class="sidebarbtn_group">
            <b-button type="submit" class="btn_primary me-2">Apply</b-button>
            <b-button class="btn_secondary_border"
              @click="sidebarstatus.filter = !sidebarstatus.filter">Cancel</b-button>
          </div>
        </b-form>
      </div>
    </div>

    <!-- add and edit sidebar -->
    <div class="add_sidebar sidebar_main"
      :class="[(sidebarstatus.add ? 'add_active' : ''), (addFormData.FormMaxMinStatus ? '' : 'extra_add_active')]">
      <div class="sidebar_toolbox">
        <h6>Create New Ticket</h6>
        <div class="sidebar_toolbox_right_side">
          <b-link class="me-3" @click="addFormData.FormMaxMinStatus = !addFormData.FormMaxMinStatus">
            <MaximizeIcon v-if="addFormData.FormMaxMinStatus" />
            <MinimizeIcon v-else />
          </b-link>
          <b-link @click="(sidebarstatus.add = false), (addFormData.FormMaxMinStatus = true)">
            <CloseIcon />
          </b-link>
        </div>
      </div>
      <div class="sidebar_form">
        <b-form>
          <b-row>
            <b-col md="6" class="border_right" v-if="!addFormData.FormMaxMinStatus">
              <div class="px-4 py-3 column_sidebar">
                <p class="sidebar_small_heading">Message</p>

                <!-- From -->
                <label for="from">From <span> *</span></label>
                <b-form-group label-for="from">
                  <v-select id="from" label="from" value="id" placeholder="Select" />
                </b-form-group>
                <b-form-group label-for="Message">
                  <b-form-textarea placeholder="Type your message" rows="20"></b-form-textarea>
                </b-form-group>

              </div>
            </b-col>
            <b-col :md="addFormData.FormMaxMinStatus ? 12 : 6">
              <div class="px-4 py-3 column_sidebar">
                <!-- Ticket Name -->
                <label for="ticket-name">Ticket Name <span>*</span></label>
                <b-form-group label-for="ticket-name">
                  <b-form-input />
                  <small class="text-danger"></small>
                  <div class="text-danger"></div>
                </b-form-group>

                <!-- Priority -->
                <label for="priority">Priority<span> *</span></label>
                <b-form-group label-for="priority" class="radio_group">
                  <b-form-radio name="radio_priority" v-model="prioritystatus" button button-variant="outline_high"
                    class="radio_btn"><span></span> High</b-form-radio>
                  <b-form-radio name="radio_priority" v-model="prioritystatus" button button-variant="outline_medium"
                    class="radio_btn"><span></span> Medium</b-form-radio>
                  <b-form-radio name="radio_priority" v-model="prioritystatus" button button-variant="outline_low"
                    class="radio_btn"><span></span> Low</b-form-radio>
                </b-form-group>

                <!-- Ticket Type -->
                <label for="ticket-type">Ticket Type <span> *</span></label>
                <b-form-group label-for="ticket-type">
                  <v-select label="title" value="id" id="ticket-type" placeholder="Select ticket type" />
                </b-form-group>

                <!-- Requester/Client -->
                <label for="requester">Requester/Client <span> *</span></label>
                <b-form-group label-for="requester">
                  <v-select id="requester" placeholder="Select requester/client" label="label" value="value" />
                </b-form-group>

                <!-- Assignee -->
                <label for="assignee">Assignee <span> *</span></label>
                <b-form-group label-for="assignee">
                  <v-select id="assignee" placeholder="Select assignee" />
                </b-form-group>

                <!-- Tags -->
                <label for="tags">Tags <span> *</span></label>
                <b-form-group label-for="tags">
                  <v-select id="tags" label="title" value="id" placeholder="Select tag" />
                </b-form-group>

                <!-- Followers -->
                <label for="followers">Followers</label>
                <b-form-group label-for="followers">
                  <v-select id="followers" multiple placeholder="Select followers" />
                </b-form-group>

                <!-- Requester/Client -->
                <label for="messsage_from">Message From</label>
                <b-form-group label-for="messsage_from">
                  <v-select id="messsage_from" placeholder="Select message from" />
                </b-form-group>

                <label for="message">Message</label>
                <b-form-group label-for="message">
                  <b-form-textarea id="message" placeholder="Enter your message here..." rows="2" />
                </b-form-group>
              </div>
            </b-col>
          </b-row>
          <!-- Form Actions -->
          <div class="sidebarbtn_group">
            <b-button type="submit" class="btn_primary me-2">Submit</b-button>
            <b-button class="btn_secondary_border" @click="sidebarstatus.add = !sidebarstatus.add">Cancel</b-button>
          </div>
        </b-form>
      </div>
    </div>

    <!-- view details sidebar -->
    <div class="view_sidebar sidebar_main" :class="[sidebarstatus.view ? 'view_active' : '']">
      <div class="sidebar_toolbox">
        <h6>Ticket Details</h6>
        <div class="sidebar_toolbox_right_side">
          <b-button class="btn_primary me-3" to="/ticket/view-ticket-details/">View Details</b-button>
          <b-link @click="sidebarstatus.view = !sidebarstatus.view">
            <CloseIcon />
          </b-link>
        </div>
      </div>
      <div class="sidebar_view">
        <div class="px-4 py-2 border_bottom">
          <h4 class="my-3">#TC-190</h4>
          <span class="view_label">Ticket Name</span>
          <p class="view_value">Website Outage</p>
          <div class="view_divide">
            <div class="view_divider_part">
              <div class="view_divider_part_item">
                <span class="view_label">Priority:</span>
                <span class="view_status_badge status_medium">Medium</span>
              </div>
              <div class="view_divider_part_item">
                <span class="view_label">Requester/Client:</span>
                <span>ABC Corp</span>
              </div>
              <div class="view_divider_part_item border-bottom-0">
                <span class="view_label">Tags:</span>
                <span>Report, Task, Monthly</span>
              </div>
            </div>
            <div class="view_divider_part">
              <div class="view_divider_part_item">
                <span class="view_label">Ticket Type:</span>
                <span>Task</span>
              </div>
              <div class="view_divider_part_item">
                <span class="view_label">Assignee:</span>
                <span>John Doe</span>
              </div>
              <div class="view_divider_part_item border-bottom-0">
                <span class="view_label">Followers:</span>
                <span>Helen White</span>
              </div>
            </div>
          </div>
          <h4 class="mt-4">Message</h4>
          <div class="view_sidebar_message">
            <span class="view_label">From</span>
            <p>Technical Support Team</p>
            <span class="view_label">Message</span>
            <p>Our client ABC Corp is experiencing issues with their website taking too long to load. Please investigate
              the
              issue and provide a solution.</p>
          </div>
        </div>
        <div class="px-4 py-2 border_bottom">
          <h4 class="my-3">Activities Logs</h4>
          <div class="timeline_view grey_timeline">
            <ul>
              <li>
                <h5>Notify Client</h5>
                <p>The website is now operational. Please check and confirm.</p>
                <span>September 20, 2024, 10:50 AM</span>
              </li>
              <li>
                <h5>Resolve Outage</h5>
                <p>Identified and fixed the server issue. Website is back online.</p>
                <span>September 20, 2024, 10:45 AM</span>
              </li>
            </ul>
          </div>
        </div>
        <div class="px-4 py-2">
          <h4 class="my-3">Task</h4>
          <div class="timeline_view grey_timeline">
            <ul>
              <li>
                <h5>Investigate Website Outage | IT Team</h5>
                <p>Check server logs and monitoring tools to identify the cause of the website downtime.</p>
                <span>September 20, 2024, 10:15 AM</span>
              </li>
              <li>
                <h5>Resolve Server Issue | IT Team</h5>
                <p>Fix the identified server configuration issue causing the website outage.</p>
                <span>September 20, 2024, 10:15 AM</span>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import "vue-select/dist/vue-select.css";
import axios from "@axios";
import vSelect from "vue-select";
import { ref, watch } from "vue";
import filterIcon from "../assest/img/icons/Filter.vue"
import deleteIcon from "../assest/img/icons/Delete.vue"
import MinimizeIcon from "../assest/img/icons/Minimize.vue"
import MaximizeIcon from "../assest/img/icons/Maximize.vue"
import CloseIcon from "../assest/img/icons/Close.vue"

// Comman variables

// ------------------------------------------- //

// Table data

const ticketData = ref({
  tablefield: [
    { key: "select", label: "" },
    { key: "ticket_id", label: "Ticket ID" },
    { key: "ticket_name", label: "Ticket Name", sortable: true },
    { key: "priority", label: "Priority" },
    { key: "ticket_type", label: "Ticket Type" },
    { key: "requester", label: "Client" },
    { key: "created_at", label: "Request Date" },
    { key: "actions", label: "" },
  ],
  tabledata: [],
  tableGridStatus: true,
  loader: true,
  currentPage: 1,
  perPage: 10,
  sortBy: '',
  sortDesc: false,
  filterData: {
    ticket_id: '',
    ticket_name: '',
    ticket_type: '',
    priority: '',
  },
  customerid: 'MTE2'
});

const filterFieldsData = ref({
  filterSelectField: [
    {
      profile_name: 'Prashant',
      profile_image: 'tableprofileimg.png'
    },
    {
      profile_name: 'Ritesh',
      profile_image: 'tableprofileimg.png'
    },
    {
      profile_name: 'Vishal',
      profile_image: 'tableprofileimg.png'
    },
  ]
});

const sidebarstatus = ref({
  shadow: false,
  filter: false,
  add: false,
  view: false
});

const addFormData = ref({
  FormMaxMinStatus: true,
});

// -------------------------------------------- //

const fetchTicketData = () => {
  axios
    .get(`/tickets`, {
      params: {
        page: ticketData.value.currentPage,
        perPage: ticketData.value.perPage,
        sortBy: ticketData.value.sortBy,
        sortDesc: ticketData.value.sortDesc,
        ...ticketData.value.filterData,
        customer: ticketData.value.customerid
      }
    })
    .then((response) => {
      // console.log(response.data.data.data)
      ticketData.value.tabledata = response.data.data.data;

      ticketData.value.loader = false;
    })
    .catch((error) => {
      console.log(error);
    });
}

fetchTicketData();

const toggletabledropdown = (evt) => {
  evt.target.nextSibling.classList.toggle("active_table_dropdown");
}

watch(sidebarstatus.value, async (newstatus, oldstatus) => {
  if (newstatus.filter || newstatus.add || newstatus.view) {
    sidebarstatus.value.shadow = true;
  }
  else {
    sidebarstatus.value.shadow = false;
  }
});

</script>