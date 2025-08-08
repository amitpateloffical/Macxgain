<template>
<div class="dashboard">
    <div class="list__page">
      <b-row>
        <b-col md="12" lg="12">
          <div class="list__title">
            <h2><i class="fas fa-envelope"></i> Login Logs</h2>
            <b-button @click="showfilter = !showfilter" variant="primary">
              Filter
            </b-button>
          </div>
          <div v-if="showfilter">
            <b-card class="filterBox mb-0">
              <b-row class="p-2">
                <b-col md="6" xl="4">
                  <v-select
                    :options="months"
                    v-model="LoginFilter.month"
                    label="name"
                    :reduce="(option) => option.value"
                    placeholder="Select a Month"
                  />
                </b-col>
              </b-row>
              <b-row>
                <b-col md="2" xl="2">
                  <b-button @click="LoginFilterDataSubmit" variant="primary w-100"
                    >Search</b-button
                  >
                </b-col>
                <b-col md="2" xl="2">
                  <b-button @click="ResetFilter" variant="secondary w-100"
                    >Reset</b-button
                  >
                </b-col>
              </b-row>
            </b-card>
          </div>
          <b-card no-body class="mb-0 mt-2 tblhedclr">
            <b-table
              responsive
              ref="refTeamListTable"
              :items="fetchTeam"
              :fields="fields"
              :sort-by.sync="sortBy"
              :sort-desc.sync="sortDesc"
              @sort-changed="onSortChanged"
              class="mb-2 staticTable"
              show-empty
              empty-text="No matching records found"
            >
          <template #cell(created_at)="data">
            <span>{{ formatDate(data.value) }}</span>
          </template>
         
            <template #head(created_at)="data">
                <span @click="changeSorting('created_at')">
                  Date & Time
                  <i :class="getSortIcon('created_at')"></i>
                </span>
              </template>
              <template #head(user)="data">
                <span @click="changeSorting('user')">
                  User
                  <i :class="getSortIcon('user')"></i>
                </span>
              </template>
              <template #head(ip_address)="data">
                <span @click="changeSorting('ip_address')">
                  Ip Address
                  <i :class="getSortIcon('ip_address')"></i>
                </span>
              </template>
              <template #cell(login_at)="data">
            <span>{{ formatDate(data.value) }}</span>
          </template>
         
            <template #head(login_at)="data">
                <span @click="changeSorting('login_at')">
                  Login At
                  <i :class="getSortIcon('login_at')"></i>
                </span>
              </template>
              <template #cell(logout_at)="data">
            <span>{{ formatDate(data.value) }}</span>
          </template>
         
            <template #head(logout_at)="data">
                <span @click="changeSorting('logout_at')">
                  Date & Time
                  <i :class="getSortIcon('logout_at')"></i>
                </span>
              </template>

              <!-- <template #cell(created_at)="data">
                <div class="text-nowrap">
                  {{ data.item.created_at }}
                </div>
              </template> -->
              <template #cell(user)="data">
                <div class="text-nowrap">
                  {{ data.item.user_name }}
                </div>
              </template>
            </b-table>
            <div class="mx-2 mb-2">
              <b-row class="align-items-center">
                <b-col cols="12" md="4">
                  <div class="d-flex align-items-center">
                    <span class="mr-2 showing_pagination_result">Show</span>
                    <v-select
                      v-model="perPage"
                      :options="perPageOptions"
                      :clearable="false"
                      class="mr-2"
                    />
                    <span class="showing_pagination_result">entries</span>
                  </div>
                </b-col>
                <b-col cols="12" md="4">
                  <div class="d-flex align-items-center">
                    <span class="showing_pagination_result"
                      >Showing {{ rangeStart }} to {{ rangeEnd }} of
                      {{ totalrows }} entries</span
                    >
                  </div>
                </b-col>
                <b-col cols="12" md="4">
                  <div class="d-flex justify-content-end">
                    <b-pagination
                    v-if="totalrows > 0"
                    v-model="currentPage"
                    :total-rows="totalrows"
                    :per-page="perPage"
                    @input="refetchData"
                    pills first-number last-number prev-class="prev-item" next-class="next-item">
                      <template #prev-text>
                        <i class="fa-solid fa-angles-left">
                        </i>
                      </template>
                      <template #next-text>
                        <i class="fa-solid fa-angles-right">
                        </i>
                      </template>
                    </b-pagination>
                  </div>
                </b-col>
              </b-row>
            </div>
          </b-card>
        </b-col>
      </b-row>
    </div>
</div>
  </template>
  
  <script>
  import {
    BTable,
    BButton,
    BRow,
    BCol,
    BCard,
    BCollapse,
    BModal,
    BPagination,
  } from "bootstrap-vue-3";
  import vSelect from "vue-select";
  import { ref, watch, computed } from "vue";
  import axios from "@axios";
  import Swal from "sweetalert2";
  
  export default {
    components: {
      BTable,
      BButton,
      vSelect,
      BRow,
      BCol,
      BCard,
      BCollapse,
      BModal,
      BPagination,
    },
    computed: {
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
    data() {
      return {
        fields: [
          { key: "created_at", label: "Date & Time", sortable: true },
          { key: "user", label: "User" },
          { key: "ip_address", label: "Ip Address", sortable: true },
          { key: "login_at", label: "Login At" },
          { key: "logout_at", label: "Logout At" },
          { key: "login_duration", label: "Login Duration" },
          { key: "user_agent", label: "User Agent" },
        ],
        months: [
          { name: "January", value: 1 },
          { name: "February", value: 2 },
          { name: "March", value: 3 },
          { name: "April", value: 4 },
          { name: "May", value: 5 },
          { name: "June", value: 6 },
          { name: "July", value: 7 },
          { name: "August", value: 8 },
          { name: "September", value: 9 },
          { name: "October", value: 10 },
          { name: "November", value: 11 },
          { name: "December", value: 12 },
        ],
        fetchTeam: [],
        LoginFilter: {
          month: new Date().getMonth() + 1,
        },
      };
    },
    mounted() {
      this.LoginFilter.month = new Date().getMonth() + 1;
      this.refetchData();
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
        this.refetchData();
      },
      getSortIcon(field) {
        if (this.sortBy === field) {
          return this.sortDesc ? "fas fa-sort-down" : "fas fa-sort-up";
        }
        return "fas fa-sort";
      },
    },
    setup() {
      const refTeamListTable = ref(null);
      const fetchTeam = ref([]);
      const currentPage = ref(1);
      const perPage = ref(10);
      const sortBy = ref('');
      const sortDesc = ref(false);
      const totalrows = ref(0);
      const perPageOptions = [10, 50, 75, 100];
      const showfilter = ref(false);
      const LoginFilter = ref({ month: new Date().getMonth() + 1 });
  
      const LoginFilterDataSubmit = () => {
        if (LoginFilter.value.month) {
          currentPage.value = 1;
          refetchData();
          showfilter.value = false;
        } else {
          Swal.fire({
            icon: "error",
            title: "Please Select Month",
            text: "You must select a month to filter the logs.",
          });
        }
      };
  
      const refetchData = () => {
        axios
          .get("/login-logs", {
            params: {
              month: LoginFilter.value.month,
              page: currentPage.value,
              perPage: perPage.value,
              sortBy: sortBy.value,
              sortDesc: sortDesc.value,
            },
          })
          .then((response) => {
            const loginLogsData = response.data;
            fetchTeam.value = Array.isArray(loginLogsData.data)
              ? loginLogsData.data
              : [];
            totalrows.value = loginLogsData.total_count;
            currentPage.value = loginLogsData.current_page;
          })
          .catch((error) => {
            Swal.fire({
              icon: "error",
              title: "Error Fetching Login Logs",
              text: error.response
                ? error.response.data.message
                : "Unknown error occurred",
            });
          });
      };
  
      const ResetFilter = () => {
        currentPage.value = 1;
        LoginFilter.value.month = new Date().getMonth() + 1;
        refetchData();
        showfilter.value = false;
      };
  
      const dataMeta = computed(() => {
        if (refTeamListTable.value && refTeamListTable.value.localItems) {
          const localItemsCount = refTeamListTable.value.localItems.length;
          return {
            from:
              perPage.value * (currentPage.value - 1) + (localItemsCount ? 1 : 0),
            to: perPage.value * (currentPage.value - 1) + localItemsCount,
            of: totalrows.value,
          };
        }
        return { from: 0, to: 0, of: 0 };
      });
  
      watch([currentPage, perPage], () => {
        refetchData();
      });
  
      return {
        refTeamListTable,
        fetchTeam,
        LoginFilterDataSubmit,
        ResetFilter,
        refetchData,
        dataMeta,
        showfilter,
        currentPage,
        perPage,
        totalrows,
        LoginFilter,
        sortBy,
        sortDesc,
        perPageOptions
      };
    },
  };
  </script>
  
  <style lang="scss">
  .b-table-selectable {
    .feather {
      font-size: 1.3rem;
    }
  }
  .list__page {
    padding: 20px;
  
    background-color: #f4f4f4;
    min-height: 100vh;
  }
  .dashboard {
  padding: 20px;
  background-color: #f4f4f4;
  min-height: 100vh;
  width: 100%;
}
  </style>
  