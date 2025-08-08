<template>
   <div  class="dashboard"> 
    <div class="list__page">
      <div v-if="successMessage" class="alert alert-success">
        {{ successMessage }}
      </div>
      <div class="list__title">
        <h4><i class="fas fa-user"></i> Activity Log </h4>
        <b-button variant="primary" class="mb-0 ml-md-1 basicButton shift3" v-ripple.400="'rgba(113, 102, 240, 0.15)'"
          @click="showfilter = !showfilter">
          Filter
        </b-button>
      </div>
      <div v-if="showfilter">
        <b-card class="filterBox mb-0 shift-left ">
          <b-row>
            <b-col md="5" xl="3">
              <b-form-group label="Name" label-for="Name">
                <v-select input-id="status" v-model="search.name" :options="username" :reduce="(val) => val.value"
                  :clearable="true" value="value" label="label" placeholder="Select Status"
                  @input="RemoveError('status')" />
              </b-form-group>
            </b-col>
            <b-col md="5" xl="3">
              <b-form-group label="Description" label-for="Description">
                <b-form-input v-model="search.description" id="basicInput" placeholder="Enter Description" />
              </b-form-group>
            </b-col>
            <b-col md="5" xl="3">
              <b-form-group label="Log Name" label-for="Log Name">
                <v-select input-id="log_name" v-model="search.log_name" :options="logname" :clearable="true"
                  :reduce="(val) => val.log_name" value="log_name" label="log_name" placeholder="Select Status" />
              </b-form-group>
            </b-col>
          </b-row>
          <b-row>
            <b-col md="2" xl="2">
              <b-button @click="searchFilter" variant="primary mt-1 w-100">
                Search
              </b-button>
            </b-col>
            <b-col md="2" xl="2">
              <b-button @click="resetFilter" variant="primary mt-1 w-100">
                Reset
              </b-button>
            </b-col>
          </b-row>
        </b-card>
      </div>
      <div class="table-container shift-left">
        <b-table responsive stacked="sm" ref="refTeamListTable" :items="fetchSystem" :fields="fields"
          :sort-by.sync="sortBy" :sort-desc.sync="sortDesc" @sort-changed="onSortChanged" class="mb-2 staticTable"
          empty-text="No matching records found">
          <template #cell(created)="data">
            <span>{{ formatDate(data.value) }}</span>
          </template>

          <template #head(created)="data">
            <span @click="changeSorting('created')">
              Date & Time
              <i :class="getSortIcon('created')"></i>
            </span>
          </template>
          <template #head(description)="data">
            <span @click="changeSorting('description')">
              Description
              <i :class="getSortIcon('description')"></i>
            </span>
          </template>
          <template #head(log_name)="data">
            <span @click="changeSorting('log_name')">
              Log Name
              <i :class="getSortIcon('log_name')"></i>
            </span>
          </template>
          <template #cell(event)="data">

            <span class="align-text-top text-capitalize" style="color:blue" v-b-modal.modal-account-import
              @click="getDescriptionClick(data.item.properties)">{{
        data.item.event
      }}</span>
          </template>
        </b-table>
        <div class="mx-2 mb-2">
          <b-row class="align-items-center">
            <b-col cols="12" md="4">
              <div class="d-flex align-items-center">
                <span class="mr-2 showing_pagination_result">Show</span>
                <v-select v-model="perPage" :options="perPageOptions" :clearable="false" class="mr-2" />
                <span class="showing_pagination_result">entries</span>
              </div>
            </b-col>
            <b-col cols="12" md="4">
              <div class="d-flex align-items-center">
                <span class="showing_pagination_result">Showing {{ rangeStart }} to {{ rangeEnd }} of
                  {{ totalrows }} entries</span>
              </div>
            </b-col>
            <b-col cols="12" md="4">
              <div class="d-flex justify-content-end">
                <b-pagination v-if="totalrows > 0" v-model="currentPage" :total-rows="totalrows" :per-page="perPage" @input="fetchEntries"
                  pills first-number last-number prev-class="prev-item" next-class="next-item">
                  <template #prev-text>
                    <i class="fa-solid fa-angles-left"></i>
                  </template>
                  <template #next-text>
                    <i class="fa-solid fa-angles-right"></i>
                  </template>
                </b-pagination>
              </div>
            </b-col>
          </b-row>
        </div>
      </div>
      <b-modal v-model="showVerificationModal" @ok="handleOk" @hide="resetModal" centered
    cancel-variant="outline-secondary" hide-footer>
    <div class="align-text-top text-capitalize myDescription" ref="modelDescription">
        <div class="row">
            <div class="col">
                <div v-if="NewModelDescription !== undefined">
                    <p class="font-weight-bold mb-2">New Data</p>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <tbody v-if="OldModelDescription !== undefined">
                                <tr v-for="(value, key) in filteredNewModelDescription" :key="key" 
                                    :class="{ 'highlight': isDifferent(key) }">
                                    <td>{{ key }}</td>
                                    <td>
                                        {{
                                            key === 'created_at' || key === 'updated_at' || key === 'Created At' || key === 'Updated At' || key === 'added_at'
                                                ? formatDate(value)
                                                : key === 'deleted_at'
                                                    ? (value === null ? '' : formatDate(value))
                                                    : key === 'status'
                                                        ? (value === 'A' ? 'Active' : value === 'I' ? 'Inactive' : '')
                                                        : value
                                        }}
                                    </td>
                                </tr>
                            </tbody>
                            <tbody v-if="OldModelDescription === undefined">
                                <tr v-for="(value, key) in filteredNewModelDescription" :key="key">
                                    <td>{{ key }}</td>
                                    <td>
                                        {{
                                            key === 'created_at' || key === 'updated_at' || key === 'Created At' || key === 'Updated At' || key === 'added_at'
                                                ? formatDate(value)
                                                : key === 'deleted_at'
                                                    ? (value === null ? '' : formatDate(value))
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
            <div class="col">
                <div v-if="OldModelDescription !== undefined">
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
                                                ? formatDate(value)
                                                : key === 'deleted_at'
                                                    ? (value === null ? '' : formatDate(value))
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
</b-modal>



    </div>
  </div>
</template>
<script>
import "vue-select/dist/vue-select.css";
import axios from "@axios";
import vSelect from 'vue-select';
import { ref, watch, computed } from 'vue';
export default {
  components: { vSelect },
  data() {
    const encodeBase64 = (data) => {
      return btoa(data.toString());
    };
    return {
      encodeBase64,
      errors: {},
      showfilter: false,
      fetchSystem: [],
      fields: [
        {
          key: 'created',
          label: 'Date & Time',
          sortable: true,
          // formatter: (value) => {
          //   const dateParts = value.split(' ')[0].split('-');
          //   return `${dateParts[2]}-${dateParts[1]}-${dateParts[0]}`;
          // }
        },
        { key: 'description', label: 'Description', sortable: true, sortBy: 'description' },
        { key: 'log_name', label: 'Log Name', sortable: true },
        { key: 'event', label: 'Action', sortable: true },
      ],
      systemData: {
        id: "",
        title: "",
        system_url: "",
        login_api: "",
        registration_api: "",
        status: "",
      },
      showsystemType: false,
      EditSystemId: false,
      loading: false,
      successMessage: "",
      StatusOption: [
        { label: "Active", value: "A" },
        { label: "Inactive", value: "I" },
      ],
      totalrows: 0,
      sortBy: "",
      sortDesc: false,
      OldModelDescription: '',
      NewModelDescription: '',
      showVerificationModal: false,
    };
  },
  mounted() {
    this.fetchEntries();
    axios.get('/alluser').then(response => {
      this.username = response.data.alluser;

    })
    axios.get('/getlogname').then(response => {
      this.logname = response.data.logname;

    })
  },
  watch: {
    currentPage(newPage) {
      this.fetchEntries();
    },
    perPage(newPerPage) {
      this.currentPage = 1;
      this.fetchEntries();
    },
  },
  computed: {
    filteredNewModelDescription() {
        return Object.fromEntries(
            Object.entries(this.NewModelDescription).filter(([key]) => key !== 'id')
        );
    },
    filteredOldModelDescription() {
        return Object.fromEntries(
            Object.entries(this.OldModelDescription).filter(([key]) => key !== 'id')
        );
    },
    sortIcon() {
      return this.sortDesc ? "fas fa-sort-down" : "fas fa-sort-up";
    },
    rangeStart() {
      if (this.totalrows === 0) {
        return 0;
      } else {
        return (this.currentPage - 1) * this.perPage + 1;
      }
    },
    rangeEnd() {
      if (this.currentPage * this.perPage >= this.totalrows) {
        return this.totalrows;
      } else {
        return this.currentPage * this.perPage;
      }
    },
  },
  setup() {
    const search = ref({
      name: '',
      description: '',
      log_name: '',
    });
    const currentPage = ref(1);
    const perPage = ref(10);
    const perPageOptions = [10, 50, 75, 100];
    return {
      search,
      currentPage,
      perPage,
      perPageOptions,
    };
  },
  methods: {
    formatDate(dateString) {
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
    isDifferent(key) {
      return this.OldModelDescription[key] !== this.NewModelDescription[key];
    },
    getDescriptionClick: function (data) {
      this.showVerificationModal = true;
      let dataDescription = JSON.parse(data);
      this.OldModelDescription = dataDescription.old;
      this.NewModelDescription = dataDescription.attributes;
    },
    onSortChanged(ctx) {
      this.sortBy = ctx.sortBy;
      this.sortDesc = ctx.sortDesc;
    },
    changeSorting(field) {
      if (this.sortBy === field) {
        this.sortDesc = !this.sortDesc;
      } else {
        this.sortBy = field;
        this.sortDesc = false;
      }
      this.fetchEntries();
    },
    getSortIcon(field) {
      if (this.sortBy === field) {
        return this.sortDesc ? "fas fa-sort-down" : "fas fa-sort-up";
      }
      return "fas fa-sort";
    },
    resetFilter() {
      this.search = {
        name: '',
        description: '',
        log_name: '',
      },
        this.currentPage = 1;
      this.fetchEntries();
      this.showfilter = false;
    },
    searchFilter() {
      this.currentPage = 1;
      this.fetchEntries();
      this.showfilter = false;

    },
    fetchEntries() {
      axios.get('/getactivity', {
        params: {
          name: this.search.name,
          description: this.search.description,
          log_name: this.search.log_name,
          page: this.currentPage,
          perPage: this.perPage,

        },
      }).then((response) => {
        this.fetchSystem = response.data.data.data;
        this.totalrows = response.data.data.total;
      }).catch((error) => {
      });
    },
    openModal() {
      this.showsystemType = true;
      this.EditSystemId = false;
      this.systemData = {
        id: "",
        title: "",
        system_url: "",
        login_api: "",
        registration_api: "",
        status: "",
      };
    },
    editClient(id) {
      this.showsystemType = true;
      this.EditSystemId = true;
      axios.get(`/systems/${id}`).then((response) => {
        this.systemData = response.data.data;
      });
    },
    clearSuccessMessage() {
      setTimeout(() => {
        this.successMessage = "";
      }, 2000);
    },
    resetModal() {
      this.systemData = {
        id: "",
        title: "",
        system_url: "",
        login_api: "",
        registration_api: "",
        status: "",
      };
      this.showsystemType = false;
      this.errors = [];
      this.showVerificationModal = false;
    },
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
  /* padding-top: 100px;
  padding-left: 300px; */
  background-color: #f4f4f4;
  min-height: 100vh;
}
</style>
<style scoped>
.shift {
  margin-left: -950px;
}

.shift2 {
  margin-left: -110px;
}
</style>
<style lang="scss">
.highlight {
  background-color: rgba(66, 133, 244, 0.6) !important;
  color: white;
}
.shift3 {
  margin-left: 10px;
}
</style>
