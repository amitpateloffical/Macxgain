<template>
  <div class="dashboard">
    <div class="list__page">
      <div v-if="successMessage" class="alert alert-success">
        {{ successMessage }}
      </div>
      <div class="list__title">
        <h2><i class="fas fa-user"></i> Email Logs</h2>
        <b-button @click="showfilter = !showfilter" variant="primary">
          Filter
        </b-button>
      </div>
      <div v-if="showfilter">
        <b-card class="filterBox mb-0">
          <b-row>
            <b-col md="5" xl="3">
              <b-form-group label="Email">
                <b-form-input
                  v-model="search.email"
                  placeholder="Enter Email"
                />
              </b-form-group>
            </b-col>
            <b-col md="5" xl="3">
              <b-form-group label="Status">
                <v-select
                  v-model="search.status"
                  :options="StatusOption"
                  placeholder="Select Status"
                />
              </b-form-group>
            </b-col>
          </b-row>
          <b-row>
            <b-col md="2" xl="2">
              <b-button @click="searchFilter" variant="primary w-100"
                >Search</b-button
              >
            </b-col>
            <b-col md="2" xl="2">
              <b-button @click="resetFilter" variant="secondary w-100"
                >Reset</b-button
              >
            </b-col>
          </b-row>
        </b-card>
      </div>
      <div class="table-container">
        <b-table
          :items="emailLogs.data"
          :fields="fields"
          @sort-changed="onSortChanged"
          :sort-by.sync="sortBy"
          :sort-desc.sync="sortDesc"
          empty-text="No matching records found"
        >
        <template #head(email)="data">
            <span @click="changeSorting('email')">
              Email
              <i :class="getSortIcon('email')"></i>
            </span>
          </template>
          <template #head(status)="data">
            <span @click="changeSorting('status')">
              Status
              <i :class="getSortIcon('status')"></i>
            </span>
          </template>
          <template #head(subject)="data">
            <span @click="changeSorting('subject')">
              Subject
              <i :class="getSortIcon('subject')"></i>
            </span>
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
    </div>
  </div>
</template>
  
  <script>
import vSelect from "vue-select";
import axios from "axios";
import { ref, watch, computed } from 'vue';

export default {
  components: { vSelect },
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
      search: { email: "", status: "" },
      emailLogs: { data: [], total: 0 },
      totalrows: 0,
      sortBy: "",
      sortDesc: false,

      successMessage: "",
      StatusOption: [
        { label: "Sent", value: "sent" },
        { label: "Pending", value: "pending" },
      ],
      fields: [
        { key: "email", label: "Email", sortable: true },
        { key: "status", label: "Status", sortable: false },
        { key: "subject", label: "Subject", sortable: true },
      ],
      showfilter: false,
    };
  },
  mounted() {
    this.fetchEntries();
  },
  methods: {
    fetchEntries() {
      axios
        .get("/api/email-logs", {
          params: {
            email: this.search.email,
            status: this.search.status,
            perPage: this.perPage,
            page: this.currentPage,
          },
        })
        .then((response) => {
          this.emailLogs = response.data;
          this.totalrows = response.data.total_row_count;
        })
        .catch((error) => {
          console.error(error);
        });
    },
    searchFilter() {
      this.currentPage = 1;
      this.fetchEntries();
      this.showfilter = false;
    },
    resetFilter() {
      this.currentPage = 1;
      this.search.email = "";
      this.search.status = "";
      this.showfilter = false;
      this.fetchEntries();
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
  },
  setup() {
    const currentPage = ref(1);
    const perPage = ref(25);
    const perPageOptions = [25, 50, 75, 100];
    return {
      currentPage,
      perPage,
      perPageOptions,
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