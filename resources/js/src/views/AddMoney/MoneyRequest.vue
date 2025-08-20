<template>
  <div class="money-request-screen">
    <!-- Header Section -->
    <div class="page-header">
      <div class="header-content">
        <h1 class="page-title">💰 Money Requests</h1>
        <p class="page-subtitle">Manage all money requests and transactions</p>
      </div>
      <div class="header-actions">
        <button 
          v-if="!isAdmin"
          class="btn-primary" 
          :disabled="loading"
          @click="openRequestModal()"
        >
          <i class="fa-solid fa-plus"></i> Create Request
        </button>
        <button class="btn-secondary" @click="showfilter = !showfilter">
          <i class="fa-solid fa-filter"></i> {{ showfilter ? 'Hide' : 'Show' }} Filters
        </button>
      </div>
    </div>

    <!-- Success Message -->
    <div v-if="successMessage" class="success-alert">
      <i class="fa-solid fa-check-circle"></i>
      {{ successMessage }}
    </div>

    <!-- Stats Cards -->
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-icon">📊</div>
        <div class="stat-content">
          <div class="stat-number">{{ totalRequests || 0 }}</div>
          <div class="stat-label">Total Requests</div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon">⏳</div>
        <div class="stat-content">
          <div class="stat-number">{{ pendingRequests || 0 }}</div>
          <div class="stat-label">Pending</div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon">✅</div>
        <div class="stat-content">
          <div class="stat-number">{{ approvedRequests || 0 }}</div>
          <div class="stat-label">Approved</div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon">❌</div>
        <div class="stat-content">
          <div class="stat-number">{{ rejectedRequests || 0 }}</div>
          <div class="stat-label">Rejected</div>
        </div>
      </div>
    </div>

      <div v-if="showfilter">
        <b-card class="filterBox mb-0">
          <b-row>
            <b-col md="4">
              <b-form-group label="Transaction ID" label-for="transaction_id">
                <b-form-input
                  v-model="search.transaction_id"
                  id="transaction_id"
                  placeholder="Enter Transaction ID"
                />
              </b-form-group>
            </b-col>
            <b-col md="4">
              <b-form-group label="Status" label-for="status">
                <b-form-select
                  v-model="search.status"
                  :options="statusOptions"
                  id="status"
                >
                  <template #first>
                    <b-form-select-option :value="null"
                      >All Status</b-form-select-option
                    >
                  </template>
                </b-form-select>
              </b-form-group>
            </b-col>
            <b-col md="4" v-if="isAdmin">
              <b-form-group label="Recipient" label-for="recipient">
                <v-select
                  v-model="search.recipient"
                  :options="userOptions"
                  label="name"
                  :reduce="(user) => user.id"
                  placeholder="Select Recipient"
                />
              </b-form-group>
            </b-col>
          </b-row>
          <b-row>
            <b-col md="3">
              <b-form-group label="From Date" label-for="from_date">
                <b-form-datepicker v-model="search.from_date" id="from_date" />
              </b-form-group>
            </b-col>
            <b-col md="3">
              <b-form-group label="To Date" label-for="to_date">
                <b-form-datepicker v-model="search.to_date" id="to_date" />
              </b-form-group>
            </b-col>
            <b-col md="2">
              <b-button @click="searchFilter" variant="primary mt-1 w-100">
                Search
              </b-button>
            </b-col>
            <b-col md="2">
              <b-button @click="resetFilter" variant="primary mt-1 w-100">
                Reset
              </b-button>
            </b-col>
          </b-row>
        </b-card>
      </div>

    <!-- Requests Table -->
    <div class="requests-container">
      <div class="table-header">
        <h3>Money Requests ({{ totalRequests || 0 }})</h3>
        <div class="table-actions">
                  <button class="btn-refresh" @click="fetchRequestss" :disabled="modalLoading">
          <i class="fa-solid fa-rotate" :class="{ 'fa-spin': modalLoading }"></i>
          {{ modalLoading ? 'Loading...' : 'Refresh' }}
        </button>
        <button class="btn-refresh ml-1" @click="updateStats" style="margin-left: 8px;">
          <i class="fa-solid fa-chart-bar"></i>
          Update Stats
        </button>
        </div>
      </div>
      
      <div v-if="modalLoading" class="loading-container">
        <div class="loading-spinner"></div>
        <p>Loading requests...</p>
      </div>
      
      <div v-else-if="!fetchRequests || fetchRequests.length === 0" class="no-data">
        <div class="no-data-icon">💰</div>
        <h3>No Money Requests Found</h3>
        <p>No requests match your current filters</p>
      </div>
      
      <div v-else class="requests-table">
        <b-table
          :items="fetchRequests"
          :fields="tableFields"
          :current-page="currentPage"
          :per-page="perPage"
          :busy="modalLoading"
          responsive
          striped
          hover
          class="requests-table"
        >
            <template #cell(transaction_id)="data">
              <span class="font-weight-bold">{{
                data.item.transaction_id
              }}</span>
            </template>

            <template #cell(amount)="data">
              ₹{{ data.item.amount.toLocaleString() }}
            </template>

            <template #cell(status)="data">
              <b-badge :variant="getStatusVariant(data.item.status)">
                {{ data.item.status }}
              </b-badge>
              <div
                v-if="
                  data.item.status === 'rejected' && data.item.reject_reason
                "
                class="text-danger small mt-1"
              >
                Reason: {{ data.item.reject_reason }}
              </div>
            </template>

            <template #cell(image_path)="data">
              <b-link
                @click="showImage(data.item.image_path)"
                class="text-primary cursor-pointer"
              >
                View Receipt
              </b-link>
            </template>

            <template #cell(actions)="data">
              <template v-if="data.item.status === 'pending' && isAdmin">
                <b-button
                  size="sm"
                  variant="success"
                  @click="approveRequest(data.item)"
                >
                  Approve
                </b-button>
                <b-button
                  size="sm"
                  variant="danger"
                  @click="rejectRequest(data.item)"
                  class="ml-1"
                >
                  Reject
                </b-button>
              </template>
              <template
                v-else-if="
                  !isAdmin &&
                  data.item.request_by === currentUserId &&
                  data.item.status === 'pending'
                "
              >
                <b-button
                  size="sm"
                  variant="primary"
                  @click="openRequestModal(data.item.id)"
                  class="ml-1"
                >
                  Edit
                </b-button>
                <b-button
                  size="sm"
                  variant="danger"
                  @click="deleteRequest(data.item.id)"
                  class="ml-1"
                >
                  Delete
                </b-button>
              </template>
              <template v-else>
                <span class="text-muted">No actions available</span>
              </template>
            </template>
          </b-table>
        </div>

        <div
          v-if="modalLoading"
          class="d-flex justify-content-center align-items-center"
          style="height: 100px"
        >
          <i class="fas fa-spinner fa-spin fa-3x"></i>
        </div>

        <div class="mx-2 mb-2">
          <b-row class="align-items-center">
            <b-col cols="12" md="4">
              <div class="d-flex align-items-center" v-if="!modalLoading">
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
            <b-col cols="12" md="4" v-if="!modalLoading">
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
                  v-if="totalrows > 0 && !modalLoading"
                  v-model="currentPage"
                  :total-rows="totalrows"
                  :per-page="perPage"
                  @input="fetchEntries"
                  pills
                  first-number
                  last-number
                  prev-class="prev-item"
                  next-class="next-item"
                >
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
      <!-- Request Modal -->
      <b-modal
        v-model="showRequestModal"
        @hide="resetModal"
        centered
        size="lg"
        :title="isEdit ? 'Edit Money Request' : 'Create Money Request'"
        hide-footer
      >
        <div
          v-if="editmodalLoading && isEdit"
          class="d-flex justify-content-center align-items-center"
          style="height: 100px"
        >
          <i class="fas fa-spinner fa-spin fa-3x"></i>
        </div>
        <div v-if="!editmodalLoading">
          <b-form @submit.prevent="submitRequest">
            <b-row>
              <b-col md="6">
                <b-form-group label="Transaction ID" label-for="transaction_id">
                  <b-form-input
                    id="transaction_id"
                    v-model="requestData.transaction_id"
                    required
                    placeholder="Enter transaction ID"
                    @input="removeError('transaction_id')"
                  />
                  <div class="text-danger" v-if="hasErrors('transaction_id')">
                    {{ getErrors("transaction_id") }}
                  </div>
                </b-form-group>
              </b-col>
              <b-col md="6">
                <b-form-group label="Amount (₹)" label-for="amount">
                  <b-form-input
                    id="amount"
                    type="number"
                    v-model="requestData.amount"
                    required
                    min="1"
                    placeholder="Enter amount"
                    @input="removeError('amount')"
                  />
                  <div class="text-danger" v-if="hasErrors('amount')">
                    {{ getErrors("amount") }}
                  </div>
                </b-form-group>
              </b-col>
            </b-row>

            <b-row>
              <!-- <b-col md="6">
                <b-form-group label="Recipient" label-for="request_create_for">
                  <v-select v-model="requestData.request_create_for" :options="userOptions" label="name" 
                    :reduce="user => user.id" placeholder="Select recipient" required @input="removeError('request_create_for')" />
                  <div class="text-danger" v-if="hasErrors('request_create_for')">
                    {{ getErrors("request_create_for") }}
                  </div>
                </b-form-group>
              </b-col> -->
              <b-col md="6">
                <b-form-group label="Payment Receipt" label-for="image">
                  <input
                    type="file"
                    @change="handleFileChange"
                    id="image"
                    accept="image/*"
                    :required="!isEdit"
                  />
                  <div class="text-danger" v-if="hasErrors('image')">
                    {{ getErrors("image") }}
                  </div>
                  <small class="text-muted">Max 2MB (JPEG, PNG, GIF)</small>
                  <div v-if="isEdit && requestData.image_path" class="mt-2">
                    <b-link
                      @click="showImage(requestData.image_path)"
                      class="text-primary cursor-pointer"
                    >
                      View Current Receipt
                    </b-link>
                  </div>
                </b-form-group>
              </b-col>
            </b-row>

            <b-form-group label="Description" label-for="description">
              <b-form-textarea
                id="description"
                v-model="requestData.description"
                rows="3"
                placeholder="Optional description..."
              />
            </b-form-group>

            <div class="d-flex justify-content-end">
              <b-button
                variant="primary"
                :disabled="loading"
                @click="submitRequest"
              >
                <span v-if="loading">
                  <i class="fas fa-spinner fa-spin"></i> Processing...
                </span>
                <span v-else>
                  {{ isEdit ? "Update Request" : "Submit Request" }}
                </span>
              </b-button>
            </div>
          </b-form>
        </div>
      </b-modal>

      <!-- Image Preview Modal -->
      <b-modal v-model="showImageModal" title="Payment Receipt" centered>
        <div class="text-center">
          <img :src="imagePreviewUrl" alt="Payment Receipt" class="img-fluid" />
        </div>
        <template #modal-footer>
          <b-button variant="primary" @click="downloadImage">Download</b-button>
        </template>
      </b-modal>

      <!-- Reject Reason Modal -->
      <b-modal
        v-model="showRejectModal"
        title="Reject Request"
        centered
        @ok="confirmReject"
      >
        <b-form-group label="Reject Reason" label-for="rejectReason">
          <b-form-textarea
            id="rejectReason"
            v-model="rejectReason"
            rows="3"
            required
            placeholder="Please specify the reason for rejection..."
          />
        </b-form-group>
      </b-modal>
  </div>
</template>

<script>
import "vue-select/dist/vue-select.css";
import axios from "@axios";
import vSelect from "vue-select";
import { ref, computed, onMounted } from "vue";

export default {
  components: { vSelect },
  data() {
    return {
      errors: {},
      showfilter: false,
      fetchRequests: [],
      fields: [
        { key: "transaction_id", label: "Transaction ID", sortable: true },
        { key: "amount", label: "Amount", sortable: true },
        { key: "description", label: "Description" },
        { key: "status", label: "Status", sortable: true },
        {
          key: "request_create_for",
          label: "Recipient",
          formatter: (value, key, item) => item.recipient.name,
        },
        {
          key: "created_at",
          label: "Request Date",
          sortable: true,
          formatter: (value) => new Date(value).toLocaleString(),
        },
        { key: "image_path", label: "Receipt" },
        { key: "actions", label: "Actions" },
      ],
      requestData: {
        transaction_id: "",
        amount: "",
        description: "",
        image: null,
        request_create_for: null,
        image_path: null,
      },
      showRequestModal: false,
      showImageModal: false,
      showRejectModal: false,
      isEdit: false,
      loading: false,
      successMessage: "",
      statusOptions: [
        { value: "pending", text: "Pending" },
        { value: "approved", text: "Approved" },
        { value: "rejected", text: "Rejected" },
      ],
      userOptions: [],
      totalrows: 0,
      sortBy: "",
      sortDesc: true,
      modalLoading: false,
      editmodalLoading: false,
      imagePreviewUrl: "",
      currentEditId: null,
      currentRequestToReject: null,
      rejectReason: "",
      isAdmin: false,
      currentUserId: null,
    };
  },
  computed: {
    rangeStart() {
      if (this.totalrows === 0) return 0;
      return (this.currentPage - 1) * this.perPage + 1;
    },
    rangeEnd() {
      if (this.currentPage * this.perPage >= this.totalrows) {
        return this.totalrows;
      } else {
        return this.currentPage * this.perPage;
      }
    },
    tableFields() {
      return [
        { key: "transaction_id", label: "Transaction ID", sortable: true },
        { key: "amount", label: "Amount", sortable: true },
        { key: "description", label: "Description" },
        { key: "status", label: "Status", sortable: true },
        { key: "created_at", label: "Request Date", sortable: true },
        { key: "image_path", label: "Receipt" },
        { key: "actions", label: "Actions" }
      ];
    }
  },
  setup() {
    const search = ref({
      transaction_id: "",
      status: null,
      recipient: null,
      from_date: null,
      to_date: null,
    });
    const currentPage = ref(1);
    const perPage = ref(10);
    const perPageOptions = [10, 25, 50, 100];

    return {
      search,
      currentPage,
      perPage,
      perPageOptions,
    };
  },
  mounted() {
    this.fetchUserInfo();
    this.fetchRequestss();
    this.fetchUsers();
    
    // Debug: Check initial state
    console.log('🚨 Component mounted, initial stats:', {
      totalRequests: this.totalRequests,
      pendingRequests: this.pendingRequests,
      approvedRequests: this.approvedRequests,
      rejectedRequests: this.rejectedRequests
    });
  },
  watch: {
    fetchRequests: {
      handler(newVal) {
        console.log('🚨 fetchRequests changed:', newVal);
        console.log('🚨 Length:', newVal ? newVal.length : 0);
      },
      immediate: true
    }
  },
  methods: {
    fetchUserInfo() {
      // Fetch current user info (you need to implement this API endpoint)
      axios
        .get("/user-info")
        .then((response) => {
          this.isAdmin = response.data.is_admin;
          this.currentUserId = response.data.id;
        })
        .catch((error) => {
          console.error(error);
        });
    },
    getStatusVariant(status) {
      switch (status) {
        case "approved":
          return "success";
        case "rejected":
          return "danger";
        default:
          return "warning";
      }
    },
    fetchRequestss() {
      this.modalLoading = true;
      axios
        .get("/money-requests", {
          params: {
            page: this.currentPage,
            perPage: this.perPage,
            sortBy: this.sortBy,
            sortDesc: this.sortDesc,
            ...this.search,
          },
        })
        .then((response) => {
          console.log('🚨 API Response:', response.data);
          console.log('🚨 fetchRequests:', response.data.data);
          console.log('🚨 totalrows:', response.data.total);
          this.fetchRequests = response.data.data;
          this.totalrows = response.data.total;
          
          // Update stats based on fetched data
          this.updateStats();
          
          this.modalLoading = false;
        })
        .catch((error) => {
          console.error(error);
          this.modalLoading = false;
        });
    },
    fetchUsers() {
      axios
        .get("/users")
        .then((response) => {
          this.userOptions = response.data;
        })
        .catch((error) => {
          console.error(error);
        });
    },
    openRequestModal(editId = null) {
      this.showRequestModal = true;
      this.isEdit = !!editId;
      this.currentEditId = editId;

      if (editId) {
        this.editmodalLoading = true;
        axios
          .get(`/money-requests/${editId}`)
          .then((response) => {
            const res = response.data.data; 
            this.requestData.transaction_id = res.transaction_id;
            this.requestData.amount = res.amount;
            this.requestData.description = res.description;
            this.requestData.image_path = res.image_path;
            this.requestData.image = res.image;

            this.editmodalLoading = false;
          })
          .catch((error) => {
            console.error(error);
            this.editmodalLoading = false;
          });
      } else {
        this.requestData = {
          transaction_id: "",
          amount: "",
          description: "",
          image: null,
          request_create_for: null,
          image_path: null,
        };
      }
    },
    submitRequest() {
      this.loading = true;
      const formData = new FormData();

      Object.keys(this.requestData).forEach((key) => {
        if (this.requestData[key] !== null && key !== "image_path") {
          formData.append(key, this.requestData[key]);
        }
      });

      const request = this.isEdit
        ? axios.put(`/money-requests/${this.currentEditId}`, formData)
        : axios.post("/money-requests", formData);

      request
        .then((response) => {
          this.showRequestModal = false;
          this.fetchRequestss();
          this.successMessage = this.isEdit
            ? "Request updated successfully!"
            : "Request submitted successfully!";
          this.clearSuccessMessage();
          this.loading = false;
        })
        .catch((error) => {
          this.loading = false;
          if (error.response?.data?.errors) {
            this.errors = error.response.data.errors;
          }
        });
    },
    approveRequest(request) {
      if (confirm(`Are you sure you want to approve this request?`)) {
        axios
          .patch(`/money-requests/${request.id}/status`, { status: "approved" })
          .then(() => {
            this.fetchRequestss();
            this.successMessage = "Request approved successfully!";
            this.clearSuccessMessage();
          })
          .catch((error) => {
            console.error(error);
          });
      }
    },
    rejectRequest(request) {
      this.currentRequestToReject = request;
      this.rejectReason = "";
      this.showRejectModal = true;
    },
    confirmReject() {
      axios
        .patch(`/money-requests/${this.currentRequestToReject.id}/status`, {
          status: "rejected",
          reject_reason: this.rejectReason,
        })
        .then(() => {
          this.fetchRequestss();
          this.successMessage = "Request rejected successfully!";
          this.clearSuccessMessage();
          this.showRejectModal = false;
        })
        .catch((error) => {
          console.error(error);
        });
    },
    deleteRequest(id) {
      if (confirm("Are you sure you want to delete this request?")) {
        axios
          .delete(`/money-requests/${id}`)
          .then(() => {
            this.fetchRequestss();
            this.successMessage = "Request deleted successfully!";
            this.clearSuccessMessage();
          })
          .catch((error) => {
            console.error(error);
          });
      }
    },
    showImage(imagePath) {
      this.imagePreviewUrl = `/storage/${imagePath}`;
      this.showImageModal = true;
    },
    downloadImage() {
      const link = document.createElement("a");
      link.href = this.imagePreviewUrl;
      link.download = `receipt-${new Date().getTime()}.jpg`;
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
    },
    handleFileChange(event) {
      this.requestData.image = event.target.files[0];
    },
    searchFilter() {
      this.currentPage = 1;
      this.fetchRequestss();
    },
    resetFilter() {
      this.search = {
        transaction_id: "",
        status: null,
        recipient: null,
        from_date: null,
        to_date: null,
      };
      this.currentPage = 1;
      this.fetchRequestss();
    },
    resetModal() {
      this.requestData = {
        transaction_id: "",
        amount: "",
        description: "",
        image: null,
        request_create_for: null,
        image_path: null,
      };
      this.errors = {};
      this.isEdit = false;
      this.currentEditId = null;
    },
    clearSuccessMessage() {
      setTimeout(() => {
        this.successMessage = "";
      }, 3000);
    },
    hasErrors(field) {
      return this.errors && this.errors[field];
    },
    getErrors(field) {
      return this.errors[field][0];
    },
    removeError(field) {
      if (this.errors[field]) {
        delete this.errors[field];
      }
    },
    onSortChanged(ctx) {
      this.currentPage = 1;
      this.sortBy = ctx.sortBy;
      this.sortDesc = ctx.sortDesc;
      this.fetchRequestss();
    },
    updateStats() {
      console.log('🚨 updateStats called with fetchRequests:', this.fetchRequests);
      
      if (!this.fetchRequests || this.fetchRequests.length === 0) {
        console.log('🚨 No requests found, setting stats to 0');
        this.totalRequests = 0;
        this.pendingRequests = 0;
        this.approvedRequests = 0;
        this.rejectedRequests = 0;
        return;
      }
      
      this.totalRequests = this.fetchRequests.length;
      this.pendingRequests = this.fetchRequests.filter(req => req.status === 'pending').length;
      this.approvedRequests = this.fetchRequests.filter(req => req.status === 'approved').length;
      this.rejectedRequests = this.fetchRequests.filter(req => req.status === 'rejected').length;
      
      console.log('🚨 Stats Updated:', {
        total: this.totalRequests,
        pending: this.pendingRequests,
        approved: this.approvedRequests,
        rejected: this.rejectedRequests
      });
      
      // Force reactivity update
      this.$forceUpdate();
    },
  },
};
</script>

<style scoped>
/* Main Container */
.money-request-screen {
  background-color: #0d0d1a;
  color: white;
  min-height: 100vh;
  padding: 20px;
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
}

/* Header Section */
.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 30px;
  padding: 20px;
  background: linear-gradient(145deg, #101022, #0d0d1a);
  border: 1px solid rgba(0, 255, 128, 0.2);
  border-radius: 12px;
  box-shadow: 0 4px 10px rgba(0, 255, 128, 0.05);
}

.header-content h1.page-title {
  font-size: 28px;
  font-weight: bold;
  color: #00ff80;
  margin: 0 0 8px 0;
}

.header-content p.page-subtitle {
  font-size: 16px;
  color: #9ca3af;
  margin: 0;
}

.header-actions {
  display: flex;
  gap: 12px;
}

.btn-primary, .btn-secondary {
  padding: 12px 24px;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  gap: 8px;
}

.btn-primary {
  background: linear-gradient(145deg, #00ff80, #00cc66);
  color: #0d0d1a;
}

.btn-primary:hover {
  background: linear-gradient(145deg, #00cc66, #00ff80);
  transform: translateY(-2px);
}

.btn-primary:disabled {
  opacity: 0.6;
  cursor: not-allowed;
  transform: none;
}

.btn-secondary {
  background: rgba(0, 255, 128, 0.1);
  color: #00ff80;
  border: 1px solid rgba(0, 255, 128, 0.3);
}

.btn-secondary:hover {
  background: rgba(0, 255, 128, 0.2);
  border-color: #00ff80;
}

/* Success Message */
.success-alert {
  background: rgba(0, 255, 128, 0.1);
  border: 1px solid rgba(0, 255, 128, 0.3);
  color: #00ff80;
  padding: 16px;
  border-radius: 8px;
  margin-bottom: 20px;
  display: flex;
  align-items: center;
  gap: 12px;
  font-weight: 500;
}

/* Stats Grid */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 20px;
  margin-bottom: 30px;
}

.stat-card {
  background: linear-gradient(145deg, #101022, #0d0d1a);
  border: 1px solid rgba(0, 255, 128, 0.2);
  border-radius: 12px;
  padding: 24px;
  text-align: center;
  transition: all 0.3s ease;
  box-shadow: 0 4px 10px rgba(0, 255, 128, 0.05);
}

.stat-card:hover {
  border-color: #00ff80;
  transform: translateY(-2px);
  box-shadow: 0 6px 16px rgba(0, 255, 128, 0.1);
}

.stat-icon {
  font-size: 32px;
  margin-bottom: 12px;
}

.stat-number {
  font-size: 32px;
  font-weight: bold;
  color: #00ff80;
  margin-bottom: 8px;
}

.stat-label {
  font-size: 14px;
  color: #9ca3af;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

/* Filter Section */
.filterBox {
  background: linear-gradient(145deg, #101022, #0d0d1a);
  border: 1px solid rgba(0, 255, 128, 0.2);
  border-radius: 12px;
  padding: 24px;
  margin-bottom: 30px;
  box-shadow: 0 4px 10px rgba(0, 255, 128, 0.05);
}

.filterBox .form-group label {
  color: #00ff80;
  font-weight: 600;
  margin-bottom: 8px;
}

.filterBox .form-control {
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(0, 255, 128, 0.2);
  border-radius: 8px;
  color: white;
  padding: 12px;
}

.filterBox .form-control:focus {
  border-color: #00ff80;
  box-shadow: 0 0 0 0.2rem rgba(0, 255, 128, 0.25);
  background: rgba(255, 255, 255, 0.1);
}

.filterBox .btn {
  background: linear-gradient(145deg, #00ff80, #00cc66);
  border: none;
  color: #0d0d1a;
  font-weight: 600;
  padding: 12px 24px;
  border-radius: 8px;
  transition: all 0.3s ease;
}

.filterBox .btn:hover {
  background: linear-gradient(145deg, #00cc66, #00ff80);
  transform: translateY(-2px);
}

/* Requests Container */
.requests-container {
  background: linear-gradient(145deg, #101022, #0d0d1a);
  border: 1px solid rgba(0, 255, 128, 0.2);
  border-radius: 12px;
  padding: 24px;
  box-shadow: 0 4px 10px rgba(0, 255, 128, 0.05);
}

.table-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
  padding-bottom: 16px;
  border-bottom: 1px solid rgba(0, 255, 128, 0.2);
}

.table-header h3 {
  color: #00ff80;
  margin: 0;
  font-size: 20px;
  font-weight: 600;
}

.btn-refresh {
  background: rgba(0, 255, 128, 0.1);
  border: 1px solid rgba(0, 255, 128, 0.3);
  color: #00ff80;
  padding: 8px 16px;
  border-radius: 6px;
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  gap: 8px;
}

.btn-refresh:hover {
  background: rgba(0, 255, 128, 0.2);
  border-color: #00ff80;
}

.btn-refresh:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

/* Loading States */
.loading-container {
  text-align: center;
  padding: 40px;
  color: #9ca3af;
}

.loading-spinner {
  border: 3px solid rgba(0, 255, 128, 0.2);
  border-top: 3px solid #00ff80;
  border-radius: 50%;
  width: 40px;
  height: 40px;
  animation: spin 1s linear infinite;
  margin: 0 auto 16px;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

/* No Data State */
.no-data {
  text-align: center;
  padding: 60px 20px;
  color: #9ca3af;
}

.no-data-icon {
  font-size: 48px;
  margin-bottom: 16px;
}

.no-data h3 {
  color: #00ff80;
  margin: 0 0 8px 0;
  font-size: 20px;
}

.no-data p {
  margin: 0;
  font-size: 16px;
}

/* Table Styling */
.requests-table {
  background: transparent;
  color: white;
}

.requests-table .table {
  background: transparent;
  color: white;
}

.requests-table .table th {
  background: rgba(0, 255, 128, 0.1);
  border-color: rgba(0, 255, 128, 0.2);
  color: #00ff80;
  font-weight: 600;
  padding: 16px 12px;
}

.requests-table .table td {
  border-color: rgba(0, 255, 128, 0.1);
  padding: 16px 12px;
  vertical-align: middle;
}

.requests-table .table tbody tr:hover {
  background: rgba(0, 255, 128, 0.05);
}

/* Badge Styling */
.badge {
  font-size: 0.85em;
  padding: 0.5em 0.8em;
  border-radius: 6px;
  font-weight: 600;
}

.badge-success {
  background: rgba(0, 255, 128, 0.2);
  color: #00ff80;
  border: 1px solid rgba(0, 255, 128, 0.3);
}

.badge-danger {
  background: rgba(255, 59, 48, 0.2);
  color: #ff3b30;
  border: 1px solid rgba(255, 59, 48, 0.3);
}

.badge-warning {
  background: rgba(255, 149, 0, 0.2);
  color: #ff9500;
  border: 1px solid rgba(255, 149, 0, 0.3);
}

/* Button Styling */
.btn {
  padding: 8px 16px;
  border-radius: 6px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  border: none;
  font-size: 14px;
}

.btn-success {
  background: linear-gradient(145deg, #00ff80, #00cc66);
  color: #0d0d1a;
}

.btn-success:hover {
  background: linear-gradient(145deg, #00cc66, #00ff80);
  transform: translateY(-1px);
}

.btn-danger {
  background: linear-gradient(145deg, #ff3b30, #cc2e25);
  color: white;
}

.btn-danger:hover {
  background: linear-gradient(145deg, #cc2e25, #ff3b30);
  transform: translateY(-1px);
}

.btn-primary {
  background: linear-gradient(145deg, #007aff, #0056cc);
  color: white;
}

.btn-primary:hover {
  background: linear-gradient(145deg, #0056cc, #007aff);
  transform: translateY(-1px);
}

/* Pagination */
.pagination {
  justify-content: center;
  margin-top: 20px;
}

.page-link {
  background: rgba(0, 255, 128, 0.1);
  border: 1px solid rgba(0, 255, 128, 0.2);
  color: #00ff80;
  margin: 0 2px;
}

.page-link:hover {
  background: rgba(0, 255, 128, 0.2);
  border-color: #00ff80;
  color: #00ff80;
}

.page-item.active .page-link {
  background: #00ff80;
  border-color: #00ff80;
  color: #0d0d1a;
}

/* Modal Styling */
.modal-content {
  background: linear-gradient(145deg, #101022, #0d0d1a);
  border: 1px solid rgba(0, 255, 128, 0.2);
  color: white;
}

.modal-header {
  border-bottom: 1px solid rgba(0, 255, 128, 0.2);
}

.modal-title {
  color: #00ff80;
}

.modal-footer {
  border-top: 1px solid rgba(0, 255, 128, 0.2);
}

/* Form Controls */
.form-control {
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(0, 255, 128, 0.2);
  border-radius: 8px;
  color: white;
  padding: 12px;
}

.form-control:focus {
  border-color: #00ff80;
  box-shadow: 0 0 0 0.2rem rgba(0, 255, 128, 0.25);
  background: rgba(255, 255, 255, 0.1);
}

.form-control::placeholder {
  color: #9ca3af;
}

/* Responsive Design */
@media (max-width: 768px) {
  .money-request-screen {
    padding: 16px;
  }
  
  .page-header {
    flex-direction: column;
    gap: 16px;
    text-align: center;
  }
  
  .header-actions {
    flex-direction: column;
    width: 100%;
  }
  
  .btn-primary, .btn-secondary {
    width: 100%;
    justify-content: center;
  }
  
  .stats-grid {
    grid-template-columns: 1fr;
    gap: 16px;
  }
  
  .filterBox {
    padding: 16px;
  }
  
  .requests-container {
    padding: 16px;
  }
  
  .table-header {
    flex-direction: column;
    gap: 12px;
    text-align: center;
  }
}

/* Utility Classes */
.cursor-pointer {
  cursor: pointer;
}

.text-primary {
  color: #00ff80 !important;
}

.text-danger {
  color: #ff3b30 !important;
}

.text-muted {
  color: #9ca3af !important;
}

.font-weight-bold {
  font-weight: 600;
}

.ml-1 {
  margin-left: 8px;
}

.mt-1 {
  margin-top: 8px;
}

.mb-2 {
  margin-bottom: 16px;
}

.mx-2 {
  margin-left: 16px;
  margin-right: 16px;
}

.d-flex {
  display: flex;
}

.justify-content-center {
  justify-content: center;
}

.justify-content-end {
  justify-content: flex-end;
}

.align-items-center {
  align-items: center;
}

.text-center {
  text-align: center;
}

.img-fluid {
  max-width: 100%;
  height: auto;
}
</style>