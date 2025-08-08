<template>
  <div class="listing_screen">
    <div class="title_line_screen px-4 py-2">
      <div class="title_line_left">
        <h5>Customers</h5>
        <v-select v-if="customersData.tabledata.length" :options="filterFieldsData.filterSelectField"
          label="profile_name" class="v_select_profile">
          <template #option="{ profile_name, profile_image }">
            <span class="table_img_cell">
              <span class="table_img">
                <img src="`../assest/img/tableprofileimg.png`" alt="profile">
              </span> {{ profile_name }}
            </span>
          </template>
        </v-select>
        <span class="titlelineicon" @click="sidebarstatus.filter = !sidebarstatus.filter"
          v-if="customersData.tabledata.length">
          <filterIcon /> Filters
        </span>
        <span class="titlelineicon" v-if="customersData.tabledata.length">
          <deleteIcon /> Delete
        </span>
      </div>
      <div class="title_line_right">
        <i class="fa-regular fa-rectangle-list" @click="customersData.tableGridStatus = !customersData.tableGridStatus"
          v-if="customersData.tabledata.length && customersData.tableGridStatus"></i>
        <i class="fa-solid fa-table-cells" @click="customersData.tableGridStatus = !customersData.tableGridStatus"
          v-if="customersData.tabledata.length && !customersData.tableGridStatus"></i>
        <b-button class="btn_primary me-2" @click="uploadModal.modalStatus = true">Upload</b-button>
        <b-button class="btn_primary" @click="sidebarstatus.add = !sidebarstatus.add">Add Customer</b-button>
      </div>
    </div>
    <div class="mt-4 px-4">
      <b-overlay :show="customersData.loader" rounded="lg" opacity="0.8" class="loader_section" no-wrap>
        <template #overlay>
          <span class="loadersdots"></span>
        </template>
        <div class="table_listing" v-if="customersData.tabledata.length">
          <b-table :items="customersData.tabledata" :fields="customersData.tablefield"
            v-if="customersData.tableGridStatus">
            <template #head(select)="data">
              <b-form-checkbox>All</b-form-checkbox>
            </template>
            <template #cell(select)="data">
              <b-form-checkbox></b-form-checkbox>
            </template>
            <template #cell(name)="data">
              <div class="name_badge" :class="setNameClrLine(data)">
                <div>{{ data.value }}</div> <span class="on_board" title="On Board"></span>
              </div>
            </template>
            <template #cell(customer_id)="data">
              <b-link @click="sidebarstatus.view = !sidebarstatus.view">{{ data.value }}</b-link>
            </template>
            <template #cell(assignedto)="data">
              <span class="table_img_cell"><span class="table_img"><img src="../assest/img/tableprofileimg.png"
                    alt="profile" /></span>{{
                      data.value }}</span>
            </template>
            <template #cell(engagement)="data">
              <span v-if="data.value == 'High'" class="badgehigh">{{ data.value }}</span>
              <span v-if="data.value == 'Medium'" class="badgemedium">{{ data.value }}</span>
              <span v-if="data.value == 'Low'" class="badgelow">{{ data.value }}</span>
              <span v-if="data.value == 'AtRisk'" class="badgeatrisk">{{ data.value }}</span>
              <span v-if="data.value == 'Moderate'" class="badgemoderate">{{ data.value }}</span>
            </template>
            <template #cell(actions)="data">
              <div class="grid_table_action">
                <i class="fa-solid fa-ellipsis" @click="toggletabledropdown($event)"></i>
                <div class="table_action_dropdown">
                  <ul>
                    <li>Details</li>
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
            <img src="../assest/img/customer/no_customers.svg" alt="no_ticket">
            <p>No Customers Found</p>
            <span>Currently, there are no customers in </span>
            <span>the system. </span>
          </div>
        </div>
      </b-overlay>
    </div>

    <div class="backdrop_shadow" v-if="sidebarstatus.shadow"></div>

    <!-- filter sidebar -->
    <div class="filter_sidebar sidebar_main" :class="[sidebarstatus.filter ? 'filter_active' : '']">
      <div class="sidebar_toolbox">
        <h6>Filter Customers</h6>
        <div class="sidebar_toolbox_right_side">
          <b-link @click="sidebarstatus.filter = !sidebarstatus.filter">
            <CloseIcon />
          </b-link>
        </div>
      </div>
      <div class="sidebar_form">
        <b-form>
          <div class="px-4 py-3 column_sidebar">

            <!-- Assigned To -->
            <label for="ticket-type">Assigned To <span> *</span></label>
            <b-form-group label-for="ticket-type">
              <v-select value="id" id="ticket-type" :options="filterFieldsData.filterSelectField" label="profile_name"
                placeholder="Select ticket type" class="v_select_profile">
                <template #option="{ profile_name, profile_image }">
                  <span class="table_img_cell">
                    <span class="table_img">
                      <img src="`../assest/img/tableprofileimg.png`" alt="profile">
                    </span> {{ profile_name }}
                  </span>
                </template>
              </v-select>
            </b-form-group>

            <!-- Engagement Status -->
            <label for="requester">Engagement Status <span> *</span></label>
            <b-form-group label-for="requester">
              <v-select id="requester" placeholder="Select requester/client" label="label" value="value" />
            </b-form-group>

            <!-- Industry -->
            <label for="assignee">Industry <span> *</span></label>
            <b-form-group label-for="assignee">
              <v-select id="assignee" placeholder="Select assignee" />
            </b-form-group>

            <!-- Date -->
            <label for="Tags">Date <span> *</span></label>
            <b-form-group label-for="tags">
              <b-form-input type="date"></b-form-input>
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
    <div class="view_sidebar sidebar_main" :class="[sidebarstatus.view ? 'extra_view_active' : '']">
      <div class="sidebar_toolbox">
        <h6>Customer Details</h6>
        <div class="sidebar_toolbox_right_side">
          <b-button class="btn_primary me-3" to="/customers/view-customer-details/">View Details</b-button>
          <b-link @click="sidebarstatus.view = !sidebarstatus.view">
            <CloseIcon />
          </b-link>
        </div>
      </div>
      <div class="sidebar_view">
        <div class="px-4 py-2 border_bottom">
          <h4 class="my-3">#CI-001</h4>
          <span class="view_label">Customer Name</span>
          <p class="view_value">
            ABC Corporation
            <span class="heart_Status"><span class="heart_icon_count">
                <GreenHeartIcon /> 85
              </span> | <span class="heart_status_badge">Active</span></span>
          </p>
          <div class="view_divider_no_border_grid">
            <div>
              <p>John Doe</p>
            </div>
            <div class="d-flex align-item-center">
              <img src="../assest/img/customer/mail.svg" alt="mail">
              <p>john.doe@abccorp.com</p>
            </div>
            <div class="d-flex align-item-center">
              <img src="../assest/img/customer/call.svg" alt="call">
              <p>123-456-7890</p>
            </div>
            <div class="d-flex align-item-center">
              <p>Priority:</p>
              <span class="badge_priority_high">High</span>
            </div>
          </div>
          <div class="view_single_divider">
            <div class="view_single_divider_item">
              <span>Tickets</span>
              <h6>16</h6>
            </div>
            <div class="view_single_divider_item">
              <span>Overdue ticket</span>
              <h6>4</h6>
            </div>
            <div class="view_single_divider_item">
              <span>AVG. Response time</span>
              <h6>25:00</h6>
            </div>
            <div class="view_single_divider_item">
              <span>Total response time</span>
              <h6>1:32:56</h6>
            </div>
          </div>
          <h4 class="mt-4">Details</h4>
          <div class="view_divide">
            <div class="view_divider_part">
              <div class="view_divider_part_item">
                <span class="view_label">Org. Name:</span>
                <span>ABC Corporation Pvt. Ltd</span>
              </div>
              <div class="view_divider_part_item">
                <span class="view_label">Add. Line 1:</span>
                <span>Springfield, IL 62701, USA</span>
              </div>
              <div class="view_divider_part_item">
                <span class="view_label">State:</span>
                <span>Maharashtra</span>
              </div>
              <div class="view_divider_part_item border-bottom-0">
                <span class="view_label">Country:</span>
                <span>India</span>
              </div>
            </div>
            <div class="view_divider_part">
              <div class="view_divider_part_item">
                <span class="view_label">Assigned To:</span>
                <span>Sarah Smith</span>
              </div>
              <div class="view_divider_part_item">
                <span class="view_label">Add. Line 2:</span>
                <span>123 Maple Street, Apt 4B</span>
              </div>
              <div class="view_divider_part_item">
                <span class="view_label">City:</span>
                <span>Mumbai</span>
              </div>
              <div class="view_divider_part_item border-bottom-0">
                <span class="view_label">ZIP CODE:</span>
                <span>400036</span>
              </div>
            </div>
          </div>
        </div>
        <div class="px-4 py-2 mt-1">
          <div class="d-flex justify-content-between align-items-center">
            <h4 class="mt-4">Active Tickets</h4>
            <b-link class="sidebar_view_more">View More Tickets</b-link>
          </div>
          <div class="view_divide_single_grid">
            <h6>#TC-190</h6>
            <p>Defective item recived</p>
          </div>
          <div class="view_single_divider">
            <div class="view_single_divider_item border_radius_bottom_right">
              <span>Tickets Type</span>
              <h6>16</h6>
            </div>
            <div class="view_single_divider_item">
              <span>Priority</span>
              <span class="badge_view_single_divider_item">Low</span>
            </div>
            <div class="view_single_divider_item">
              <span>Assigned to</span>
              <h6>Bob Lee</h6>
            </div>
            <div class="view_single_divider_item border_radius_bottom_left">
              <span>Request date</span>
              <h6>07/08/2024, 12:17PM</h6>
            </div>
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

    <!--Upload customers Modal-->
    <b-modal id="upload-modal" v-model="uploadModal.modalStatus" hide-header hide-footer ok-title="Upload"
      @ok="uploadFile" size="md" centered>
      <div class="upload_box">
        <h5>Upload File Here
          <CloseIcon class="close_icon_modal" @click="uploadModal.modalStatus = false" />
        </h5>
        <form>
          <input type="file" class="file_input" ref="fileInput" hidden @change="uploadFile">
          <div class="upload_icon" @click="$refs.fileInput.click()">
            <span class="icon_circle">
              <UploadIcon />
            </span>
            <p><span>Choose file</span> to Upload <br> Xls, CSV</p>
          </div>
        </form>
        <section class="loading_area" v-if="showProgress">
          <div class="loading_upload_row" v-for="(file, index) in files">
            <ExcelIcon />
            <div class="content">
              <div class="details">
                <span class="name">{{ file.name }}</span>
                <span class="percent">{{ file.loading }}%</span>
              </div>
              <div class="loading_bar">
                <div class="loading" :style="{ width: file.loading + '%' }"></div>
              </div>
            </div>
          </div>
        </section>
        <section class="upload_area" v-else>
          <div class="loading_upload_row" v-for="(file, index) in uploadFiles">
            <div class="content upload">
              <ExcelIcon />
              <div class="details">
                <span class="name">{{ file.name }}</span>
                <span class="size">{{ file.size }}</span>
              </div>
              <div class="loading_bar">
                <div class="loading" style=""></div>
              </div>
            </div>
          </div>
        </section>
      </div>
    </b-modal>
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
import UploadIcon from "../assest/img/icons/Upload.vue"
import ExcelIcon from "../assest/img/icons/Excel.vue"
import GreenHeartIcon from "../assest/img/icons/GreenHeart.vue"


//upload files

const files = ref([]);
const uploadFiles = ref([]);
const showProgress = ref(false);

const uploadFile = (event) => {
  const file = event.target.files[0];
  if (!file) return;
  const fileName = (file.name.length >= 12) ? file.name.substring(0, 13) + '... .' + file.name.split('.')[1] : file.name;
  const formData = new FormData();
  formData.append("file", file);
  files.value.push({ name: fileName, loading: 0 });
  showProgress.value = true;

  axios.post('/abc', formData, {
    onUploadProgress: ({ loaded, total }) => {
      files.value[files.value.length - 1].loading = Math.floor((loaded / total) * 100);
      if (loaded == total) {
        const fileSize = (total < 1024) ? total + 'KB' : (loaded / (1024 * 1024)).toFixed(2) + 'MB';
        uploadFiles.value.push({ name: fileName, size: fileSize });
        files.value = [];
        showProgress.value = false;
      }
    }
  }).catch(console.error(error));
}

// Comman variables

const uploadModal = ref({
  modalStatus: false
})

// ------------------------------------------- //

// Table data

const customersData = ref({
  tablefield: [
    { key: "select", label: "" },
    { key: "customer_id", label: "Cust. ID" },
    { key: "name", label: "Name", sortable: true },
    { key: "contactperson", label: "Contact Person" },
    { key: "email", label: "Email" },
    { key: "contactnumber", label: "Contact Number" },
    { key: "assignedto", label: "Assign To" },
    { key: "engagement", label: "Engagement" },
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
  }
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

const fetchcustomersData = () => {
  axios
    .get(`/getCustomer`, {
      params: {
        page: customersData.value.currentPage,
        perPage: customersData.value.perPage,
        sortBy: customersData.value.sortBy,
        sortDesc: customersData.value.sortDesc,
        ...customersData.value.filterData
      }
    })
    .then((response) => {
      // console.log(response.data.data.data)
      customersData.value.tabledata = response.data.data.data;

      customersData.value.loader = false;
    })
    .catch((error) => {
      console.log(error);
    });
}

fetchcustomersData();

const toggletabledropdown = (evt) => {
  evt.target.nextSibling.classList.toggle("active_table_dropdown");
}

const setNameClrLine = (data) => {
  if (data.item.engagement == 'High') {
    return "name_badge_red";
  }
  if (data.item.engagement == 'Medium') {
    return "name_badge_orange";
  }
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