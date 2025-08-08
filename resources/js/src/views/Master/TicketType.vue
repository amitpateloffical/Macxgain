<template>
  <div class="dashboard">
    <div class="list__page">
      <div v-if="successMessage" class="alert alert-success">
        {{ successMessage }}
      </div>
      <div class="list__title">
        <h4>Ticket Type</h4>
        <b-button variant="primary" :disabled="loading" @click="openModal()">
          Add
        </b-button>
        <b-button variant="primary" class="mb-0 ml-md-1 basicButton shift3" v-ripple.400="'rgba(113, 102, 240, 0.15)'"
          @click="showfilter = !showfilter">
          Filter
        </b-button>
      </div>
      <div v-if="showfilter">
        <b-card class="filterBox mb-0">
          <b-row>
            <b-col md="5" xl="3">
              <b-form-group label="Title" label-for="Name">
                <b-form-input v-model="search.title" id="basicInput" placeholder="Enter Title" />
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
      <div class="table-container">
        <div v-if="!modalLoading">

  <!-- Table Section -->
  <b-table
    responsive
    stacked="sm"
    ref="refTeamListTable"
    :items="fetchSystem"
    :fields="fields"
    @sort-changed="onSortChanged"
    class="mb-2 staticTable"
    empty-text="No matching records found"
  >
    <template #head(title)="data">
      <span @click="changeSorting('title')">
        Title
        <i :class="getSortIcon('title')"></i>
      </span>
    </template>

    <template #cell(actions)="data">
      <b-button size="sm" @click="editticket(encodeBase64(data.item.id))">
        <i class="fa fa-pencil" aria-hidden="true"></i>
      </b-button>
      &nbsp;
      <b-button size="sm" variant="danger" @click="deleteticket(encodeBase64(data.item.id))">
        <i class="fa fa-trash" aria-hidden="true"></i>
      </b-button>
      <button size="sm" class="shift2">
        <b-link :to="{ name: 'ViewTicketType', params: { id: encodeBase64(data.item.id) } }" title="View">
          <i class="fas fa-eye"></i>
        </b-link>
      </button>
    </template>
  </b-table>
        </div>
  <!-- Loader Section (Centered) -->
  <div v-if="modalLoading" class="d-flex justify-content-center align-items-center" style="height: 100px;">
    <i class="fas fa-spinner fa-spin fa-3x"></i> <!-- Loader icon -->
  </div>

  <!-- Pagination Section -->
  <div class="mx-2 mb-2">
    <b-row class="align-items-center">
      <b-col cols="12" md="4">
        <div class="d-flex align-items-center" v-if="!modalLoading">
          <span class="mr-2 showing_pagination_result">Show</span>
          <v-select v-model="perPage" :options="perPageOptions" :clearable="false" class="mr-2" />
          <span class="showing_pagination_result">entries</span>
        </div>
      </b-col>
      <b-col cols="12" md="4" v-if="!modalLoading">
        <div class="d-flex align-items-center">
          <span class="showing_pagination_result">Showing {{ rangeStart }} to {{ rangeEnd }} of
            {{ totalrows }} entries</span>
        </div>
      </b-col>
      <b-col cols="12" md="4">
        <div class="d-flex justify-content-end">
          <b-pagination v-if="totalrows > 0 && !modalLoading" v-model="currentPage" :total-rows="totalrows" :per-page="perPage" @input="fetchEntries"
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

      <b-modal v-model="showticketType" @ok="handleOk" @hide="resetModal" centered cancel-variant="outline-secondary"
        :title="EdittickettypeId ? 'Update Ticket Type' : 'Add Ticket Type'"
        :ok-title="EdittickettypeId ? 'Update' : 'Add'" hide-footer>
        <div v-if="editmodalLoading && EdittickettypeId" class="d-flex justify-content-center align-items-center" style="height: 100px;">
    <i class="fas fa-spinner fa-spin fa-3x"></i> <!-- Loader icon -->
  </div>
  <div v-if="!editmodalLoading">

        <b-form @submit.prevent="saveEntry">
          <label class="form-label">
            Title <span style="color:red;"> *</span>
          </label>
          <b-form-group label-for="title">
            <input type="text" id="title" v-model="tickettypeData.title" required class="form-control"
              @input="RemoveError('title')" placeholder="Enter Ticket Type"/>
            <small class="text-danger">{{ errors[0] }}</small>
            <div class="text-danger" v-if="hasErrors('title')">
              {{ getErrors("title") }}
            </div>
          </b-form-group>

          <div class="d-flex justify-content-end">
            <b-button variant="primary" :disabled="loading" @click="saveEntry">
              <span v-if="loading">
                <i class="fas fa-spinner fa-spin"></i> Processing...
              </span>
              <span v-else>
                {{ EdittickettypeId ? "Update" : "Add" }}
              </span>
            </b-button>
          </div>
        </b-form>
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
    key: "title", 
    label: "Title", 
    sortable: true,
    formatter: (value) => {
      if (!value) return '';
      return value.charAt(0).toUpperCase() + value.slice(1).toLowerCase(); 
    }
  },
  { key: "actions", label: "Actions" },
],
      tickettypeData: {
        id: "",
        title: "",

      },
      modalLoading: false,  
      editmodalLoading: false,  

      showticketType: false,
      EdittickettypeId: false,
      loading: false,
      successMessage: "",
      StatusOption: [
        { label: "Active", value: "A" },
        { label: "Inactive", value: "I" },
      ],
      totalrows: 0,

    };
  },
  mounted() {
    this.fetchEntries();
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
      title: '',
    });
    const currentPage = ref(1);
    const perPage = ref(10);
    const perPageOptions = [10, 50, 75, 100];

    const RemoveError = (errorName) => {
      errors.value[errorName] = " ";
    };
    const hasErrors = (fieldName) => {
      return fieldName in errors.value;
    };

    const getErrors = (fieldName) => {
      return errors.value[fieldName][0];
    };

    const errors = ref([]);
    const dataMeta = computed(() => {
      const localItemsCount = refTeamListTable.value
        ? refTeamListTable.value.localItems.length
        : 0;
      return {
        from:
          perPage.value * (currentPage.value - 1) + (localItemsCount ? 1 : 0),
        to: perPage.value * (currentPage.value - 1) + localItemsCount,
        of: totalsystem.value,
      };
    });

    return {
      search,
      RemoveError,
      hasErrors,
      getErrors, errors, currentPage, perPage, perPageOptions, dataMeta,

    };
  },
  methods: {
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
        title: '',
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
      this.modalLoading = true; 
      axios.get('/ticket-type', {
        params: {
          title: this.search.title,
          page: this.currentPage,
          perPage: this.perPage,
          sortBy: this.sortBy,
          sortDesc: this.sortDesc,
        },
      }).then((response) => {
      this.modalLoading = false; 
        this.fetchSystem = response.data.data.data;
        this.totalrows = response.data.data.total;
      }).catch((error) => {
      this.modalLoading = false; 

      });
    },
    openModal() {
      this.showticketType = true;
      this.EdittickettypeId = false;
      this.tickettypeData = {
        id: "",
        title: "",

      };
    },
    editticket(id) {
      this.showticketType = true;
      this.EdittickettypeId = true;
      this.editmodalLoading = true; 

      axios.get(`/ticket-type/${id}/edit`).then((response) => {
        this.tickettypeData = response.data.data;
        this.editmodalLoading = false;
        })
        .catch((error) => {
          this.editmodalLoading = false; 
          console.error(error);
        });

    },
    saveEntry() {
      this.loading = true;
      if (this.EdittickettypeId) {
        const encodedId = this.encodeBase64(this.tickettypeData.id);
        axios.put(`/ticket-type/${encodedId}`, this.tickettypeData)
          .then(() => {
            this.showticketType = false;
            this.fetchEntries();
            this.loading = false;
            this.successMessage = "Ticket Type Updated Successfully!";
            this.clearSuccessMessage();
            this.$router.push('/master/ticket-type');
          })
          .catch((error) => {
            this.loading = false;
            if (error.response.data.code == 422) {
              this.errors = error.response.data.errors;
            }
          });
      } else {
        axios.post('/ticket-type', this.tickettypeData)
          .then(response => {
            this.showticketType = false;
            this.loading = false;
            this.successMessage = "Ticket Type saved successfully!";
            this.clearSuccessMessage();
            this.$router.push('/master/ticket-type');
            this.fetchEntries();
          })
          .catch((error) => {
            this.loading = false;
            if (error.response.data.code == 422) {
              this.errors = error.response.data.errors;
            }
          });
      }
    },
    deleteticket(id) {
      axios.delete(`/ticket-type/${id}`).then(() => {
        this.fetchEntries();
        this.successMessage = "Ticket Type Deleted Successfully!";
        this.clearSuccessMessage();
      }).catch(error => {
      });
    },
    hasErrors(fieldName) {
      return fieldName in this.errors;
    },
    getErrors(fieldName) {
      return this.errors[fieldName][0];
    },
    removeError(errorName) {
      this.errors[errorName] = " ";
    },
    clearSuccessMessage() {
      setTimeout(() => {
        this.successMessage = "";
      }, 2000);
    },
    resetModal() {
      this.tickettypeData = {
        id: "",
        title: "",

      };
      this.showticketType = false;
      this.errors = [];
    },
  },
};
</script>

<style scoped>
.list__page {
  padding: 20px;
  /* padding-top: 100px;
  padding-left: 300px; */
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
<style scoped>
.shift-left {
  margin-left: -500px;
}

.shift {
  margin-left: -450px;
}

.shift2 {
  margin-left: -110px;
}

.shift3 {
  margin-left: 10px;
}
</style>