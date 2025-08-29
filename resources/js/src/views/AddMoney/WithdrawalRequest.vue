<template>
  <div class="dashboard">
    <div class="list__page">
      <div v-if="successMessage" class="alert alert-success">
        {{ successMessage }}
      </div>
      <div class="list__title">
        <div class="title-section">
          <h4>Withdrawal Requests</h4>
          <!-- Balance Display -->
          <div v-if="!isAdmin" class="balance-display">
            <div class="balance-card-small">
              <div class="balance-header-small">
                <i class="bi bi-wallet2 me-1"></i>
                <span>Available Balance</span>
              </div>
              <div class="balance-amount-small">
                ₹{{ formatBalance(userBalance) }}
              </div>
              <button @click="fetchUserBalance" class="refresh-btn-small" :disabled="balanceLoading">
                <i class="bi bi-arrow-clockwise" :class="{ 'spinning': balanceLoading }"></i>
              </button>
            </div>
          </div>
        </div>
        <b-button
          v-if="!isAdmin"
          variant="primary"
          :disabled="loading"
          @click="openRequestModal()"
        >
          Create Request
        </b-button>
        <b-button
          variant="primary"
          class="mb-0 ml-md-1 basicButton"
          v-ripple.400="'rgba(113, 102, 240, 0.15)'"
          @click="showfilter = !showfilter"
        >
          Filter
        </b-button>
      </div>

      <div v-if="showfilter">
        <b-card class="filterBox mb-0">
          <b-row>
            <!-- <b-col md="4">
              <b-form-group label="Transaction ID" label-for="transaction_id">
                <b-form-input
                  v-model="search.transaction_id"
                  id="transaction_id"
                  placeholder="Enter Transaction ID"
                />
              </b-form-group>
            </b-col> -->
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

      <div class="table-container">
        <div v-if="!modalLoading">
          <b-table
            responsive
            stacked="sm"
            ref="refRequestListTable"
            :items="fetchRequests"
            :fields="fields"
            @sort-changed="onSortChanged"
            class="mb-2 staticTable"
            empty-text="No matching records found"
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
        :title="'Create Withdrawal Request'"
        hide-footer
      >
        <!-- Loading Spinner -->
        <div
          v-if="modalLoading"
          class="d-flex justify-content-center align-items-center"
          style="height: 100px"
        >
          <i class="fas fa-spinner fa-spin fa-3x"></i>
        </div>

        <!-- Error / Bank Info Missing -->
        <div v-else-if="!canWithdraw">
          <div class="alert alert-danger">
            {{ errorMessage }}
          </div>
        </div>

        <!-- Withdrawal Form -->
        <div v-else>
          <b-alert variant="info" show>
            Available Balance: ₹{{ availableBalance }}
          </b-alert>

          <b-form @submit.prevent="submitRequest">
            <b-row>
              <b-col md="6">
                <b-form-group label="Amount (₹)" label-for="amount">
                  <b-form-input
                    id="amount"
                    type="number"
                    v-model="requestData.amount"
                    required
                    min="1"
                    :max="availableBalance"
                    placeholder="Enter amount"
                    @input="removeError('amount')"
                  />
                    <small class="text-muted">Available Balance: ₹{{ availableBalance }}</small>
                  <div class="text-danger" v-if="hasErrors('amount')">
                    {{ getErrors("amount") }}
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
              <b-button variant="primary" :disabled="loading" type="submit">
                <span v-if="loading">
                  <i class="fas fa-spinner fa-spin"></i> Processing...
                </span>
                <span v-else> Submit Request </span>
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
      userBalance: 0,
      balanceLoading: false,
      fields: [
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
      canWithdraw: false,
      availableBalance: 0,
      errorMessage: "",
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
    this.fetchUserBalance();
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
    
    async fetchUserBalance() {
      this.balanceLoading = true;
      try {
        const userData = JSON.parse(localStorage.getItem("userData"));
        if (userData && userData.id) {
          const res = await axios.post("/total_b", {
            id: userData.id
          });
          
          if (res.data && res.data.total_balance !== undefined) {
            this.userBalance = res.data.total_balance;
            
            // Update localStorage with latest balance
            userData.total_balance = res.data.total_balance;
            localStorage.setItem("userData", JSON.stringify(userData));
          }
        }
      } catch (error) {
        console.error("Error fetching balance:", error);
        // Try to get balance from localStorage as fallback
        const userData = JSON.parse(localStorage.getItem("userData"));
        if (userData && userData.total_balance !== undefined) {
          this.userBalance = userData.total_balance;
        }
      } finally {
        this.balanceLoading = false;
      }
    },
    
    formatBalance(balance) {
      if (balance === null || balance === undefined) return '0.00';
      return parseFloat(balance).toLocaleString('en-IN', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
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
        .get("/withdrawal-request", {
          params: {
            page: this.currentPage,
            perPage: this.perPage,
            sortBy: this.sortBy,
            sortDesc: this.sortDesc,
            ...this.search,
          },
        })
        .then((response) => {
          this.fetchRequests = response.data.data;
          this.totalrows = response.data.total;
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
      this.modalLoading = true;
      this.canWithdraw = false;
      axios
        .get(`/checkBankInfo`)
        .then((res) => {
          if (res.data.status === "success") {
            this.availableBalance = res.data.data.balance;
            this.canWithdraw = true;
          } else {
            this.errorMessage = res.data.message;
            this.canWithdraw = false;
          }
        })
        .catch((err) => {
          this.errorMessage =
            err.response?.data?.message || "Something went wrong!";
          this.canWithdraw = false;
        })
        .finally(() => {
          this.modalLoading = false;
        });

      this.isEdit = !!editId;
      this.currentEditId = editId;

      if (editId) {
        this.editmodalLoading = true;
        axios
          .get(`/withdrawal-request/${editId}`)
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
        ? axios.put(`/withdrawal-request/${this.currentEditId}`, formData)
        : axios.post("/withdrawal-request", formData);

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
          .patch(`/withdrawal-request/${request.id}/status`, {
            status: "approved",
          })
          .then((response) => {
            this.fetchRequestss();
            this.successMessage =
              response.data.message || "Request approved successfully!";
            this.clearSuccessMessage();
          })
          .catch((error) => {
            console.error(error);
            this.successMessage =
              "Something went wrong while approving the request.";
            this.clearSuccessMessage();
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
        .patch(`/withdrawal-request/${this.currentRequestToReject.id}/status`, {
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
          .delete(`/withdrawal-request/${id}`)
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
  },
};
</script>

<style scoped>
/* Modern Dark Theme Withdrawal Dashboard */
.dashboard {
  background: linear-gradient(135deg, #0d0d1a 0%, #1a1a2e 100%);
  min-height: 100vh;
  padding: 20px;
  color: white;
}

.list__page {
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 16px;
  padding: 30px;
  backdrop-filter: blur(10px);
}

.list__title {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 30px;
  padding-bottom: 20px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.list__title h4 {
  font-size: 2rem;
  font-weight: 700;
  margin: 0;
  background: linear-gradient(135deg, #00ff80, #00cc66);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

/* Modern Buttons */
.btn-primary {
  background: linear-gradient(135deg, #00ff80, #00cc66) !important;
  border: none !important;
  color: #0d0d1a !important;
  font-weight: 600 !important;
  padding: 12px 24px !important;
  border-radius: 12px !important;
  transition: all 0.3s ease !important;
}

.btn-primary:hover {
  transform: translateY(-2px) !important;
  box-shadow: 0 8px 25px rgba(0, 255, 128, 0.3) !important;
}

.basicButton {
  background: rgba(255, 255, 255, 0.1) !important;
  border: 1px solid rgba(255, 255, 255, 0.2) !important;
  color: white !important;
}

.basicButton:hover {
  background: rgba(0, 255, 128, 0.1) !important;
  border-color: #00ff80 !important;
  color: #00ff80 !important;
}

/* Success Alert */
.alert-success {
  background: rgba(0, 255, 128, 0.1) !important;
  border: 1px solid rgba(0, 255, 128, 0.3) !important;
  border-radius: 12px !important;
  color: #00ff80 !important;
  padding: 16px 20px !important;
}

/* Filter Box */
.filterBox {
  background: rgba(255, 255, 255, 0.03) !important;
  border: 1px solid rgba(255, 255, 255, 0.1) !important;
  border-radius: 12px !important;
  padding: 24px !important;
  margin-bottom: 30px !important;
  backdrop-filter: blur(5px) !important;
}

/* Table Container */
.table-container {
  background: rgba(255, 255, 255, 0.03) !important;
  border: 1px solid rgba(255, 255, 255, 0.1) !important;
  border-radius: 12px !important;
  padding: 24px !important;
  backdrop-filter: blur(5px) !important;
}

/* Modern Table */
.staticTable {
  width: 100%;
  background: transparent !important;
  color: white !important;
}

.staticTable th {
  background: rgba(0, 255, 128, 0.1) !important;
  border-color: rgba(0, 255, 128, 0.2) !important;
  color: #00ff80 !important;
  font-weight: 600 !important;
  padding: 16px 12px !important;
  border-top: none !important;
}

.staticTable td {
  background: transparent !important;
  border-color: rgba(255, 255, 255, 0.1) !important;
  padding: 16px 12px !important;
  color: white !important;
}

.staticTable tbody tr {
  background: transparent !important;
}

.staticTable tbody tr:hover {
  background: rgba(0, 255, 128, 0.05) !important;
}

.staticTable tbody tr:nth-child(even) {
  background: rgba(255, 255, 255, 0.02) !important;
}

/* Form Controls */
.form-control, .form-select {
  background: rgba(255, 255, 255, 0.1) !important;
  border: 1px solid rgba(255, 255, 255, 0.2) !important;
  color: white !important;
  border-radius: 8px !important;
}

.form-control:focus, .form-select:focus {
  background: rgba(255, 255, 255, 0.1) !important;
  border-color: #00ff80 !important;
  box-shadow: 0 0 0 3px rgba(0, 255, 128, 0.1) !important;
  color: white !important;
}

.form-label {
  color: #e5e7eb !important;
  font-weight: 600 !important;
}

/* Badges */
.badge {
  font-size: 0.85em;
  padding: 0.5em 0.8em;
  border-radius: 8px;
  font-weight: 600;
}

.badge-success {
  background: rgba(0, 255, 128, 0.2) !important;
  color: #00ff80 !important;
  border: 1px solid rgba(0, 255, 128, 0.3) !important;
}

.badge-warning {
  background: rgba(255, 193, 7, 0.2) !important;
  color: #ffc107 !important;
  border: 1px solid rgba(255, 193, 7, 0.3) !important;
}

.badge-danger {
  background: rgba(220, 53, 69, 0.2) !important;
  color: #dc3545 !important;
  border: 1px solid rgba(220, 53, 69, 0.3) !important;
}

/* Pagination */
.pagination {
  --bs-pagination-bg: rgba(255, 255, 255, 0.1);
  --bs-pagination-border-color: rgba(255, 255, 255, 0.2);
  --bs-pagination-color: white;
  --bs-pagination-hover-bg: rgba(0, 255, 128, 0.1);
  --bs-pagination-hover-border-color: #00ff80;
  --bs-pagination-hover-color: #00ff80;
  --bs-pagination-active-bg: #00ff80;
  --bs-pagination-active-border-color: #00ff80;
  --bs-pagination-active-color: #0d0d1a;
}

/* Pagination Results */
.showing_pagination_result {
  color: #9ca3af;
  font-size: 14px;
}

/* Loading Spinner */
.fa-spinner {
  color: #00ff80;
}

/* Links */
.text-primary {
  color: #00ff80 !important;
}

.text-primary:hover {
  color: #00cc66 !important;
}

.text-danger {
  color: #ff6b6b !important;
}

.text-muted {
  color: #9ca3af !important;
}

/* Modal Improvements */
.modal-content {
  background: rgba(26, 26, 46, 0.95) !important;
  border: 1px solid rgba(255, 255, 255, 0.1) !important;
  backdrop-filter: blur(10px) !important;
  color: white !important;
}

.modal-header {
  border-bottom: 1px solid rgba(255, 255, 255, 0.1) !important;
}

.modal-title {
  color: white !important;
}

.btn-close {
  filter: invert(1) !important;
}

.cursor-pointer {
  cursor: pointer;
}

/* Enhanced Responsive Design */

/* Large Tablets and Small Desktops */
@media (max-width: 1024px) {
  .dashboard {
    padding: 18px;
  }
  
  .list__page {
    padding: 25px;
  }
  
  .list__title h4 {
    font-size: 1.85rem;
  }
}

/* Tablets */
@media (max-width: 768px) {
  .dashboard {
    padding: 15px;
  }
  
  .list__page {
    padding: 20px;
    margin: 0;
  }
  
  .list__title {
    flex-direction: column;
    gap: 15px;
    align-items: stretch;
    text-align: center;
  }

  .list__title h4 {
    font-size: 1.75rem;
    text-align: center;
    margin-bottom: 10px;
  }

  /* Button Responsive */
  .btn-primary {
    width: 100% !important;
    margin-bottom: 10px !important;
    padding: 14px 20px !important;
    font-size: 16px !important;
  }
  
  .basicButton {
    width: 100% !important;
    padding: 14px 20px !important;
    font-size: 16px !important;
  }

  /* Filter Responsive */
  .filterBox {
    padding: 20px !important;
    margin-bottom: 20px !important;
  }
  
  .filterBox .row {
    margin: 0 !important;
  }
  
  .filterBox .col-md-4,
  .filterBox .col-md-3,
  .filterBox .col-md-2 {
    padding: 0 !important;
    margin-bottom: 15px !important;
  }
  
  .form-control, .form-select {
    font-size: 16px !important;
    padding: 12px 16px !important;
  }

  /* Table Responsive */
  .table-container {
    padding: 15px !important;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
  }
  
  .staticTable {
    min-width: 600px;
    font-size: 14px;
  }
  
  .staticTable th,
  .staticTable td {
    padding: 12px 8px !important;
    white-space: nowrap;
  }
  
  /* Pagination Responsive */
  .mx-2.mb-2 {
    margin: 0 !important;
    padding: 15px 0;
  }
  
  .mx-2.mb-2 .row {
    margin: 0 !important;
  }
  
  .mx-2.mb-2 .col-12 {
    padding: 5px 0 !important;
    text-align: center;
  }
  
  .showing_pagination_result {
    font-size: 13px;
    display: block;
    margin: 5px 0;
  }
  
  .pagination {
    justify-content: center !important;
    margin: 10px 0 !important;
  }
  
  .pagination .page-link {
    padding: 8px 12px !important;
    font-size: 14px !important;
  }
}

/* Mobile Phones */
@media (max-width: 480px) {
  .dashboard {
    padding: 10px;
  }
  
  .list__page {
    padding: 15px;
    border-radius: 12px;
  }
  
  .list__title h4 {
    font-size: 1.5rem;
    line-height: 1.3;
  }
  
  /* Ultra Compact Buttons */
  .btn-primary, .basicButton {
    padding: 12px 16px !important;
    font-size: 15px !important;
    border-radius: 10px !important;
  }
  
  /* Compact Filter */
  .filterBox {
    padding: 15px !important;
    border-radius: 10px !important;
  }
  
  .form-control, .form-select {
    font-size: 16px !important;
    padding: 10px 14px !important;
    border-radius: 6px !important;
  }
  
  .form-label {
    font-size: 14px !important;
    margin-bottom: 5px !important;
  }
  
  /* Mobile Table */
  .table-container {
    padding: 10px !important;
    border-radius: 10px !important;
  }
  
  .staticTable {
    font-size: 12px;
    min-width: 500px;
  }
  
  .staticTable th,
  .staticTable td {
    padding: 8px 6px !important;
    font-size: 12px !important;
  }
  
  .badge {
    font-size: 0.75em !important;
    padding: 0.3em 0.6em !important;
  }
  
  /* Mobile Pagination */
  .pagination .page-link {
    padding: 6px 10px !important;
    font-size: 12px !important;
  }
  
  .showing_pagination_result {
    font-size: 12px;
  }
  
  /* Mobile Modal */
  .modal-dialog {
    margin: 10px !important;
  }
  
  .modal-content {
    border-radius: 10px !important;
  }
}

/* Extra Small Phones */
@media (max-width: 360px) {
  .dashboard {
    padding: 8px;
  }
  
  .list__page {
    padding: 12px;
  }
  
  .list__title h4 {
    font-size: 1.3rem;
  }
  
  .staticTable {
    min-width: 450px;
    font-size: 11px;
  }
  
  .staticTable th,
  .staticTable td {
    padding: 6px 4px !important;
    font-size: 11px !important;
  }
  
  .btn-primary, .basicButton {
    padding: 10px 14px !important;
    font-size: 14px !important;
  }
}

/* Landscape Mobile */
@media (max-width: 768px) and (orientation: landscape) {
  .list__title {
    flex-direction: row !important;
    justify-content: space-between !important;
    align-items: center !important;
  }
  
  .list__title h4 {
    text-align: left !important;
    margin-bottom: 0 !important;
  }
  
  .btn-primary, .basicButton {
    width: auto !important;
    margin-left: 10px !important;
    margin-bottom: 0 !important;
  }
}

/* Touch Device Optimizations */
@media (hover: none) and (pointer: coarse) {
  .btn-primary, .basicButton {
    min-height: 44px !important;
    min-width: 44px !important;
  }
  
  .form-control, .form-select {
    min-height: 44px !important;
  }
  
  .pagination .page-link {
    min-height: 44px !important;
    min-width: 44px !important;
  }
  
  .cursor-pointer {
    -webkit-tap-highlight-color: rgba(0, 255, 128, 0.2);
  }
}

/* High DPI Displays */
@media (-webkit-min-device-pixel-ratio: 2), (min-resolution: 192dpi) {
  .list__title h4 {
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
  }
}

/* Print Styles */
@media print {
  .dashboard {
    background: white !important;
    color: black !important;
  }
  
  .list__page {
    background: white !important;
    border: 1px solid #ccc !important;
  }
  
  .btn-primary, .basicButton {
    display: none !important;
  }
  
  .filterBox {
    display: none !important;
  }
  
  .staticTable th {
    background: #f5f5f5 !important;
    color: black !important;
  }
  
  .staticTable td {
    color: black !important;
  }
}

/* Balance Display Styles */
.title-section {
  display: flex;
  flex-direction: column;
  gap: 15px;
}

.balance-display {
  display: flex;
  justify-content: flex-start;
}

.balance-card-small {
  background: linear-gradient(135deg, rgba(0, 255, 128, 0.1), rgba(0, 204, 102, 0.05));
  border: 1px solid rgba(0, 255, 128, 0.3);
  border-radius: 12px;
  padding: 12px 16px;
  display: flex;
  align-items: center;
  gap: 12px;
  backdrop-filter: blur(10px);
  transition: all 0.3s ease;
  min-width: 200px;
}

.balance-card-small:hover {
  border-color: rgba(0, 255, 128, 0.5);
  box-shadow: 0 4px 15px rgba(0, 255, 128, 0.2);
  transform: translateY(-1px);
}

.balance-header-small {
  display: flex;
  align-items: center;
  color: #00ff80;
  font-weight: 600;
  font-size: 12px;
  white-space: nowrap;
}

.balance-amount-small {
  font-size: 18px;
  font-weight: bold;
  color: #00ff80;
  text-shadow: 0 0 8px rgba(0, 255, 128, 0.3);
  flex: 1;
}

.refresh-btn-small {
  background: rgba(0, 255, 128, 0.1);
  border: 1px solid rgba(0, 255, 128, 0.3);
  color: #00ff80;
  padding: 4px 6px;
  border-radius: 6px;
  font-size: 10px;
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
}

.refresh-btn-small:hover:not(:disabled) {
  background: rgba(0, 255, 128, 0.2);
  border-color: rgba(0, 255, 128, 0.5);
}

.refresh-btn-small:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.spinning {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}

/* Responsive Balance Display */
@media (max-width: 768px) {
  .title-section {
    align-items: center;
    text-align: center;
  }
  
  .balance-display {
    justify-content: center;
    width: 100%;
  }
  
  .balance-card-small {
    min-width: 180px;
    padding: 10px 14px;
  }
  
  .balance-amount-small {
    font-size: 16px;
  }
}

@media (max-width: 480px) {
  .balance-card-small {
    min-width: 160px;
    padding: 8px 12px;
    gap: 8px;
  }
  
  .balance-header-small {
    font-size: 11px;
  }
  
  .balance-amount-small {
    font-size: 14px;
  }
}
</style>