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
                <span>Total Balance</span>
              </div>
              <div class="balance-amount-small">
                ₹{{ formatBalance(userBalance) }}
              </div>
              <button @click="fetchUserBalance" class="refresh-btn-small" :disabled="balanceLoading">
                <i class="bi bi-arrow-clockwise" :class="{ 'spinning': balanceLoading }"></i>
              </button>
            </div>
            <div class="balance-card-small available">
              <div class="balance-header-small">
                <i class="bi bi-check-circle me-1"></i>
                <span>Available for Withdrawal</span>
              </div>
              <div class="balance-amount-small">
                ₹{{ formatBalance(availableWithdrawalBalance) }}
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
          Withdrawal Request
        </b-button>
        <b-button
          v-if="false"
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
              <span class="amount-value">₹{{ data.item.amount.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}</span>
            </template>

            <template #cell(created_at)="data">
              <span class="date-value">{{ formatDate(data.item.created_at) }}</span>
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
      <!-- Professional Withdrawal Request Modal -->
      <b-modal
        v-model="showRequestModal"
        @hide="resetModal"
        centered
        size="lg"
        hide-footer
        modal-class="modern-modal"
        header-class="modern-modal-header"
        body-class="modern-modal-body"
      >
        <template #modal-header="{ close }">
          <div class="modal-header-content">
            <div class="modal-icon">
              <i class="fas fa-money-bill-wave"></i>
            </div>
            <div class="modal-title-section">
              <h4 class="modal-title">Create Withdrawal Request</h4>
              <p class="modal-subtitle">Withdraw funds from your account</p>
            </div>
            <button type="button" class="modal-close-btn" @click="close()">
              <i class="fas fa-times"></i>
            </button>
          </div>
        </template>

        <!-- Loading State -->
        <div v-if="modalLoading" class="loading-container">
          <div class="loading-spinner">
            <i class="fas fa-spinner fa-spin"></i>
          </div>
          <p class="loading-text">Loading your account information...</p>
        </div>

        <!-- Error State -->
        <div v-else-if="!canWithdraw" class="error-container">
          <div class="error-icon">
            <i class="fas fa-exclamation-triangle"></i>
          </div>
          <h5 class="error-title">Unable to Process Withdrawal</h5>
          <p class="error-message">{{ errorMessage }}</p>
          <div class="error-actions">
            <button class="btn-secondary" @click="resetModal">
              <i class="fas fa-arrow-left"></i> Go Back
            </button>
          </div>
        </div>

        <!-- Withdrawal Form -->
        <div v-else class="withdrawal-form-container">
          <!-- Balance Display -->
          <div class="balance-display-card">
            <div class="balance-icon">
              <i class="fas fa-wallet"></i>
            </div>
            <div class="balance-info">
              <span class="balance-label">Available for Withdrawal</span>
              <span class="balance-amount">₹{{ formatBalance(availableBalance) }}</span>
              <div class="balance-breakdown">
                <div class="balance-detail">
                  <span class="detail-label">Total Balance:</span>
                  <span class="detail-value">₹{{ formatBalance(balanceData.total_balance || 0) }}</span>
                </div>
                <div class="balance-detail">
                  <span class="detail-label">Blocked in Trades:</span>
                  <span class="detail-value blocked">₹{{ formatBalance(balanceData.blocked_amount || 0) }}</span>
                </div>
              </div>
            </div>
          </div>

          <form @submit.prevent="submitRequest" class="withdrawal-form">
            <!-- Amount Input -->
            <div class="form-group-modern">
              <label class="form-label-modern">
                <i class="fas fa-rupee-sign"></i>
                Amount (₹)
              </label>
              <div class="input-wrapper">
                <input
                  type="number"
                  v-model="requestData.amount"
                  required
                  min="1"
                  :max="availableBalance"
                  placeholder="Enter amount"
                  class="form-input-modern"
                  :class="{ 'error': hasErrors('amount') }"
                  @input="removeError('amount')"
                />
                <div class="input-icon">
                  <i class="fas fa-rupee-sign"></i>
                </div>
              </div>
              <div class="input-help">
                Maximum withdrawal: ₹{{ formatBalance(availableBalance) }}
              </div>
              <div v-if="hasErrors('amount')" class="error-text">
                <i class="fas fa-exclamation-circle"></i>
                {{ getErrors("amount") }}
              </div>
            </div>



            <!-- Form Actions -->
            <div class="form-actions">
              <button type="button" class="btn-cancel" @click="closeModal">
                <i class="fas fa-times"></i>
                Cancel
              </button>
              <button type="submit" class="btn-submit" :disabled="loading">
                <span v-if="loading" class="btn-loading">
                  <i class="fas fa-spinner fa-spin"></i>
                  Processing...
                </span>
                <span v-else class="btn-content">
                  <i class="fas fa-paper-plane"></i>
                  Submit Request
                </span>
              </button>
            </div>
          </form>
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
      withdrawalBalance: 0,
      blockedAmount: 0,
      balanceLoading: false,
      fields: [
        { key: "amount", label: "Amount", sortable: true },
        { key: "status", label: "Status", sortable: true },
        {
          key: "created_at",
          label: "Request Date",
          sortable: true,
        },
        { key: "actions", label: "Actions" },
      ],
      requestData: {
        transaction_id: "",
        amount: "",
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
      balanceData: {
        total_balance: 0,
        blocked_amount: 0,
        available_balance: 0
      },
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
    availableWithdrawalBalance() {
      return Math.max(0, this.userBalance - this.blockedAmount);
    },
  },
  setup() {
    const search = ref({
      transaction_id: "",
      status: null,
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
        
        // Also fetch withdrawal balance details
        await this.fetchWithdrawalBalance();
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
    
    async fetchWithdrawalBalance() {
      try {
        const res = await axios.get('/withdrawal-balance');
        console.log('Withdrawal balance response:', res.data);
        
        if (res.data.status === 'success') {
          this.withdrawalBalance = res.data.data.available_balance;
          this.blockedAmount = res.data.data.blocked_amount;
          console.log('Updated withdrawal balance:', {
            withdrawalBalance: this.withdrawalBalance,
            blockedAmount: this.blockedAmount,
            userBalance: this.userBalance,
            availableWithdrawalBalance: this.availableWithdrawalBalance,
            totalBalance: res.data.data.total_balance
          });
        } else {
          console.log('withdrawal-balance returned error status:', res.data.status, res.data.message);
          // If API fails, set blocked amount to 0 so available = total
          this.blockedAmount = 0;
          this.withdrawalBalance = this.userBalance;
        }
      } catch (error) {
        console.error('Error fetching withdrawal balance:', error);
        console.error('Error details:', error.response?.data);
        // If API call fails, set blocked amount to 0 so available = total
        this.blockedAmount = 0;
        this.withdrawalBalance = this.userBalance;
      }
    },
    
    formatBalance(balance) {
      if (balance === null || balance === undefined) return '0.00';
      return parseFloat(balance).toLocaleString('en-IN', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
      });
    },
    formatDate(dateValue) {
      if (!dateValue) return 'N/A';
      try {
        const date = new Date(dateValue);
        if (isNaN(date.getTime())) return 'N/A';
        return date.toLocaleString('en-IN', {
          day: '2-digit',
          month: '2-digit',
          year: 'numeric',
          hour: '2-digit',
          minute: '2-digit',
          second: '2-digit',
          hour12: false
        });
      } catch (error) {
        console.error('Date formatting error:', error);
        return 'N/A';
      }
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
    openRequestModal(editId = null) {
      this.showRequestModal = true;
      this.modalLoading = true;
      this.canWithdraw = false;
      axios
        .get(`/checkBankInfo`)
        .then((res) => {
          if (res.data.status === "success") {
            this.availableBalance = res.data.data.available_balance || res.data.data.balance;
            this.balanceData = {
              total_balance: res.data.data.total_balance || 0,
              blocked_amount: res.data.data.blocked_amount || 0,
              available_balance: res.data.data.available_balance || res.data.data.balance
            };
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
            ? "Withdrawal request updated successfully!"
            : "Withdrawal request submitted successfully!";
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
              if (confirm(`Are you sure you want to approve this withdrawal request?`)) {
        axios
          .patch(`/withdrawal-request/${request.id}/status`, {
            status: "approved",
          })
          .then((response) => {
            this.fetchRequestss();
            this.successMessage =
              response.data.message || "Withdrawal request approved successfully!";
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
                      this.successMessage = "Withdrawal request rejected successfully!";
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
        image: null,
        request_create_for: null,
        image_path: null,
      };
      this.errors = {};
      this.isEdit = false;
      this.currentEditId = null;
    },
    closeModal() {
      this.showRequestModal = false;
      this.resetModal();
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
  background: linear-gradient(135deg, var(--color-bg-primary, #0d0d1a) 0%, var(--color-bg-tertiary, #1a1a2e) 100%);
  min-height: 100vh;
  padding: 20px;
  color: var(--color-text-primary, white);
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
  background: linear-gradient(135deg, var(--color-primary, #FFD700), var(--color-primary-dark, #DAA520));
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

/* Modern Buttons */
.btn-primary {
  background: linear-gradient(135deg, var(--color-primary, #FFD700), var(--color-primary-dark, #DAA520)) !important;
  border: none !important;
  color: var(--color-bg-primary, #0d0d1a) !important;
  font-weight: 600 !important;
  padding: 12px 24px !important;
  border-radius: 12px !important;
  transition: all 0.3s ease !important;
}

.btn-primary:hover {
  transform: translateY(-2px) !important;
  box-shadow: 0 8px 25px rgba(255, 215, 0, 0.3) !important;
}

.basicButton {
  background: rgba(255, 255, 255, 0.1) !important;
  border: 1px solid rgba(255, 255, 255, 0.2) !important;
  color: white !important;
}

.basicButton:hover {
  background: rgba(255, 215, 0, 0.1) !important;
  border-color: var(--color-primary, #FFD700) !important;
  color: var(--color-primary, #FFD700) !important;
}

/* Success Alert */
.alert-success {
  background: rgba(255, 215, 0, 0.1) !important;
  border: 1px solid var(--color-border-primary, rgba(255, 215, 0, 0.3)) !important;
  border-radius: 12px !important;
  color: #FFD700 !important;
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
  background: linear-gradient(135deg, rgba(255, 255, 255, 0.08), rgba(255, 255, 255, 0.05)) !important;
  border: 2px solid rgba(255, 255, 255, 0.15) !important;
  border-radius: 20px !important;
  padding: 30px !important;
  backdrop-filter: blur(10px) !important;
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3), inset 0 1px 2px rgba(255, 255, 255, 0.1) !important;
  position: relative !important;
  overflow: hidden !important;
}

.table-container::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 3px;
  background: linear-gradient(90deg, transparent, var(--color-primary, #FFD700), transparent);
  opacity: 0.6;
}

/* Modern Table */
.staticTable {
  width: 100%;
  background: linear-gradient(135deg, rgba(255, 255, 255, 0.08), rgba(255, 255, 255, 0.05)) !important;
  color: #000000 !important;
  border-radius: 16px !important;
  overflow: hidden !important;
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3) !important;
}

.staticTable th {
  background: linear-gradient(135deg, rgba(var(--color-primary-rgb, 255, 215, 0), 0.2), rgba(var(--color-primary-rgb, 255, 215, 0), 0.15)) !important;
  border-color: rgba(var(--color-primary-rgb, 255, 215, 0), 0.3) !important;
  color: var(--color-primary, #FFD700) !important;
  font-weight: 700 !important;
  padding: 20px 16px !important;
  border-top: none !important;
  font-size: 0.95rem !important;
  text-transform: uppercase !important;
  letter-spacing: 1px !important;
  text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2) !important;
  position: relative !important;
}

.staticTable th::after {
  content: '';
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  height: 2px;
  background: linear-gradient(90deg, transparent, var(--color-primary, #FFD700), transparent);
  opacity: 0.5;
}

.staticTable td {
  background: linear-gradient(135deg, rgba(255, 255, 255, 0.2), rgba(255, 255, 255, 0.15)) !important;
  border-color: rgba(255, 255, 255, 0.25) !important;
  padding: 18px 16px !important;
  color: #000000 !important;
  font-size: 0.95rem !important;
  vertical-align: middle !important;
  word-wrap: break-word !important;
  white-space: normal !important;
  font-weight: 600 !important;
  text-shadow: 0 1px 3px rgba(255, 255, 255, 0.4) !important;
  transition: all 0.3s ease !important;
  position: relative !important;
}

.staticTable tbody tr {
  background: rgba(255, 255, 255, 0.1) !important;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
  border-left: 3px solid transparent !important;
}

.staticTable tbody tr:hover {
  background: linear-gradient(135deg, rgba(var(--color-primary-rgb, 255, 215, 0), 0.25), rgba(var(--color-primary-rgb, 255, 215, 0), 0.15)) !important;
  transform: translateX(4px) !important;
  box-shadow: 0 4px 12px rgba(var(--color-primary-rgb, 255, 215, 0), 0.2) !important;
  border-left-color: var(--color-primary, #FFD700) !important;
}

.staticTable tbody tr:hover td {
  background: transparent !important;
  color: #000000 !important;
  font-weight: 700 !important;
}

.staticTable tbody tr:nth-child(even) {
  background: rgba(255, 255, 255, 0.12) !important;
}

.staticTable tbody tr:nth-child(even) td {
  background: linear-gradient(135deg, rgba(255, 255, 255, 0.22), rgba(255, 255, 255, 0.18)) !important;
}

/* Amount column styling */
.amount-value {
  font-weight: 800 !important;
  color: #000000 !important;
  font-size: 1.1rem !important;
  letter-spacing: 0.5px !important;
  background: linear-gradient(135deg, rgba(var(--color-primary-rgb, 255, 215, 0), 0.4), rgba(var(--color-primary-rgb, 255, 215, 0), 0.3)) !important;
  padding: 8px 14px !important;
  border-radius: 12px !important;
  text-shadow: 0 2px 4px rgba(255, 255, 255, 0.6) !important;
  border: 2px solid rgba(var(--color-primary-rgb, 255, 215, 0), 0.6) !important;
  display: inline-block !important;
  box-shadow: 0 4px 8px rgba(var(--color-primary-rgb, 255, 215, 0), 0.2), inset 0 1px 2px rgba(255, 255, 255, 0.3) !important;
  transition: all 0.3s ease !important;
}

.amount-value:hover {
  transform: scale(1.05) !important;
  box-shadow: 0 6px 12px rgba(var(--color-primary-rgb, 255, 215, 0), 0.3), inset 0 1px 2px rgba(255, 255, 255, 0.4) !important;
}

/* Date column styling */
.date-value {
  color: #000000 !important;
  font-size: 0.9rem !important;
  font-family: 'Courier New', monospace !important;
  white-space: normal !important;
  font-weight: 700 !important;
  background: linear-gradient(135deg, rgba(255, 255, 255, 0.3), rgba(255, 255, 255, 0.25)) !important;
  padding: 6px 12px !important;
  border-radius: 8px !important;
  text-shadow: 0 1px 3px rgba(255, 255, 255, 0.5) !important;
  border: 1px solid rgba(255, 255, 255, 0.4) !important;
  display: inline-block !important;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1), inset 0 1px 2px rgba(255, 255, 255, 0.4) !important;
  transition: all 0.3s ease !important;
}

/* Ensure all table text is visible with black color and highlight */
.staticTable td,
.staticTable td *,
.staticTable td span,
.staticTable td div,
.staticTable td p,
.staticTable td label {
  color: #000000 !important;
  visibility: visible !important;
  opacity: 1 !important;
  text-shadow: 0 1px 2px rgba(255, 255, 255, 0.3) !important;
}

/* Override any white text colors in table */
.staticTable td span:not(.amount-value):not(.date-value):not(.badge),
.staticTable td div:not(.badge),
.staticTable td p {
  color: #000000 !important;
  font-weight: 600 !important;
  text-shadow: 0 1px 2px rgba(255, 255, 255, 0.3) !important;
}

/* Ensure text-muted class in table is visible with highlight */
.staticTable .text-muted {
  color: #1a1a1a !important;
  background: rgba(255, 255, 255, 0.2) !important;
  padding: 2px 6px !important;
  border-radius: 4px !important;
  font-weight: 600 !important;
}

/* Action buttons in table */
.staticTable td .btn,
.staticTable td .btn-sm {
  padding: 10px 18px !important;
  border-radius: 10px !important;
  font-weight: 700 !important;
  font-size: 0.85rem !important;
  letter-spacing: 0.5px !important;
  text-transform: uppercase !important;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2), inset 0 1px 2px rgba(255, 255, 255, 0.3) !important;
  border: 2px solid !important;
  margin: 0 4px !important;
}

.staticTable td .btn-primary {
  background: linear-gradient(135deg, var(--color-primary, #FFD700), var(--color-primary-dark, #DAA520)) !important;
  color: #000000 !important;
  border-color: rgba(var(--color-primary-rgb, 255, 215, 0), 0.6) !important;
  text-shadow: 0 1px 2px rgba(255, 255, 255, 0.5) !important;
}

.staticTable td .btn-primary:hover {
  transform: translateY(-3px) scale(1.05) !important;
  box-shadow: 0 6px 16px rgba(var(--color-primary-rgb, 255, 215, 0), 0.4), inset 0 1px 2px rgba(255, 255, 255, 0.4) !important;
}

.staticTable td .btn-danger {
  background: linear-gradient(135deg, #ef4444, #dc2626) !important;
  color: #ffffff !important;
  border-color: rgba(239, 68, 68, 0.6) !important;
  text-shadow: 0 1px 2px rgba(0, 0, 0, 0.3) !important;
}

.staticTable td .btn-danger:hover {
  transform: translateY(-3px) scale(1.05) !important;
  box-shadow: 0 6px 16px rgba(239, 68, 68, 0.4), inset 0 1px 2px rgba(255, 255, 255, 0.2) !important;
}

.staticTable td .btn-success {
  background: linear-gradient(135deg, #22c55e, #16a34a) !important;
  color: #ffffff !important;
  border-color: rgba(34, 197, 94, 0.6) !important;
  text-shadow: 0 1px 2px rgba(0, 0, 0, 0.3) !important;
}

.staticTable td .btn-success:hover {
  transform: translateY(-3px) scale(1.05) !important;
  box-shadow: 0 6px 16px rgba(34, 197, 94, 0.4), inset 0 1px 2px rgba(255, 255, 255, 0.2) !important;
}

/* Responsive table text visibility */
@media (max-width: 768px) {
  .staticTable td[data-label]::before {
    color: var(--color-primary, #FFD700) !important;
    font-weight: 600 !important;
  }
  
  .staticTable td {
    color: #000000 !important;
    font-size: 0.9rem !important;
    background: rgba(255, 255, 255, 0.15) !important;
  }
  
  .amount-value {
    color: #000000 !important;
    font-size: 1rem !important;
    background: rgba(var(--color-primary-rgb, 255, 215, 0), 0.3) !important;
  }
  
  .date-value {
    color: #000000 !important;
    font-size: 0.85rem !important;
    background: rgba(255, 255, 255, 0.25) !important;
  }
}

/* Form Controls */
.form-control, .form-select {
  background: rgba(255, 255, 255, 0.1) !important;
  border: 1px solid rgba(255, 255, 255, 0.2) !important;
  color: #d1d5db !important;
  border-radius: 8px !important;
}

.form-control:focus, .form-select:focus {
  background: rgba(255, 255, 255, 0.1) !important;
  border-color: var(--color-primary, #FFD700) !important;
  box-shadow: 0 0 0 3px rgba(var(--color-primary-rgb, 255, 215, 0), 0.1) !important;
  color: var(--color-text-primary, white) !important;
}

.form-label {
  color: #e5e7eb !important;
  font-weight: 600 !important;
}

/* Badges */
.badge {
  font-size: 0.85em;
  padding: 8px 14px;
  border-radius: 20px;
  font-weight: 700;
  text-transform: capitalize;
  letter-spacing: 0.5px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15), inset 0 1px 2px rgba(255, 255, 255, 0.3) !important;
  transition: all 0.3s ease !important;
  border: 2px solid !important;
}

.badge:hover {
  transform: translateY(-2px) !important;
  box-shadow: 0 6px 12px rgba(0, 0, 0, 0.2), inset 0 1px 2px rgba(255, 255, 255, 0.4) !important;
}

.badge-success {
  background: linear-gradient(135deg, rgba(255, 215, 0, 0.3), rgba(255, 215, 0, 0.2)) !important;
  color: #000000 !important;
  border-color: rgba(255, 215, 0, 0.5) !important;
  text-shadow: 0 1px 2px rgba(255, 255, 255, 0.5) !important;
}

.badge-warning {
  background: linear-gradient(135deg, rgba(255, 193, 7, 0.4), rgba(255, 193, 7, 0.3)) !important;
  color: #000000 !important;
  border-color: rgba(255, 193, 7, 0.6) !important;
  text-shadow: 0 1px 2px rgba(255, 255, 255, 0.5) !important;
}

.badge-danger {
  background: linear-gradient(135deg, rgba(220, 53, 69, 0.3), rgba(220, 53, 69, 0.2)) !important;
  color: #000000 !important;
  border-color: rgba(220, 53, 69, 0.5) !important;
  text-shadow: 0 1px 2px rgba(255, 255, 255, 0.5) !important;
}

/* Pagination */
.pagination {
  --bs-pagination-bg: rgba(255, 255, 255, 0.1);
  --bs-pagination-border-color: rgba(255, 255, 255, 0.2);
  --bs-pagination-color: white;
  --bs-pagination-hover-bg: rgba(255, 215, 0, 0.1);
  --bs-pagination-hover-border-color: #FFD700;
  --bs-pagination-hover-color: #FFD700;
  --bs-pagination-active-bg: #FFD700;
  --bs-pagination-active-border-color: #FFD700;
  --bs-pagination-active-color: #0d0d1a;
}

/* Pagination Results */
.showing_pagination_result {
  color: #9ca3af;
  font-size: 14px;
}

/* Loading Spinner */
.fa-spinner {
  color: #FFD700;
}

/* Links */
.text-primary {
  color: #FFD700 !important;
}

.text-primary:hover {
  color: #DAA520 !important;
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
    -webkit-tap-highlight-color: rgba(255, 215, 0, 0.2);
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
  gap: 15px;
  flex-wrap: wrap;
}

.balance-card-small {
  background: linear-gradient(135deg, rgba(255, 215, 0, 0.1), rgba(218, 165, 32, 0.05));
  border: 1px solid var(--color-border-primary, rgba(255, 215, 0, 0.3));
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
  border-color: rgba(255, 215, 0, 0.5);
  box-shadow: 0 4px 15px rgba(255, 215, 0, 0.2);
  transform: translateY(-1px);
}

.balance-card-small.available {
  background: linear-gradient(135deg, rgba(0, 191, 255, 0.1), rgba(0, 150, 255, 0.05));
  border: 1px solid rgba(0, 191, 255, 0.3);
}

.balance-card-small.available:hover {
  border-color: rgba(0, 191, 255, 0.5);
  box-shadow: 0 4px 15px rgba(0, 191, 255, 0.2);
}

.balance-card-small.available .balance-header-small {
  color: #00bfff;
}

.balance-card-small.available .balance-amount-small {
  color: #00bfff;
  text-shadow: 0 0 8px rgba(0, 191, 255, 0.3);
}

.balance-header-small {
  display: flex;
  align-items: center;
  color: #FFD700;
  font-weight: 600;
  font-size: 12px;
  white-space: nowrap;
}

.balance-amount-small {
  font-size: 18px;
  font-weight: bold;
  color: #FFD700;
  text-shadow: 0 0 8px rgba(255, 215, 0, 0.3);
  flex: 1;
}

.refresh-btn-small {
  background: rgba(255, 215, 0, 0.1);
  border: 1px solid var(--color-border-primary, rgba(255, 215, 0, 0.3));
  color: #FFD700;
  padding: 4px 6px;
  border-radius: 6px;
  font-size: 10px;
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
}

.refresh-btn-small:hover:not(:disabled) {
  background: rgba(255, 215, 0, 0.2);
  border-color: rgba(255, 215, 0, 0.5);
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
    gap: 10px;
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

/* Modern Modal Styles */
.modern-modal .modal-dialog {
  max-width: 600px;
}

.modern-modal .modal-content {
  background: linear-gradient(135deg, rgba(26, 26, 46, 0.98), rgba(16, 16, 34, 0.98)) !important;
  border: 2px solid rgba(var(--color-primary-rgb, 255, 215, 0), 0.4) !important;
  border-radius: 24px !important;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.8), 0 0 40px rgba(var(--color-primary-rgb, 255, 215, 0), 0.2) !important;
  overflow: hidden !important;
  backdrop-filter: blur(20px) !important;
  position: relative !important;
}

.modern-modal .modal-content::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 4px;
  background: linear-gradient(90deg, transparent, var(--color-primary, #FFD700), transparent);
  opacity: 0.8;
}

.modern-modal .modal-body {
  background: transparent !important;
  color: white !important;
  padding: 0 !important;
}

.modal-header-content {
  display: flex;
  align-items: center;
  gap: 20px;
  padding: 28px 32px;
  background: linear-gradient(135deg, rgba(var(--color-primary-rgb, 255, 215, 0), 0.2), rgba(var(--color-primary-rgb, 255, 215, 0), 0.15)) !important;
  border-bottom: 2px solid rgba(var(--color-primary-rgb, 255, 215, 0), 0.3);
  position: relative;
}

.modal-header-content::after {
  content: '';
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  height: 1px;
  background: linear-gradient(90deg, transparent, var(--color-primary, #FFD700), transparent);
  opacity: 0.5;
}

.modal-icon {
  width: 60px;
  height: 60px;
  background: linear-gradient(135deg, var(--color-primary, #FFD700), var(--color-primary-light, #FFE55C));
  border-radius: 16px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 24px;
  color: #000000;
  box-shadow: 0 8px 25px rgba(var(--color-primary-rgb, 255, 215, 0), 0.4), inset 0 1px 2px rgba(255, 255, 255, 0.3);
  transition: all 0.3s ease;
}

.modal-icon:hover {
  transform: scale(1.1) rotate(5deg);
  box-shadow: 0 12px 35px rgba(var(--color-primary-rgb, 255, 215, 0), 0.5), inset 0 1px 2px rgba(255, 255, 255, 0.4);
}

.modal-title-section {
  flex: 1;
}

.modal-title {
  margin: 0 0 6px 0;
  background: linear-gradient(135deg, var(--color-primary, #FFD700), var(--color-primary-light, #FFE55C));
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  font-size: 1.75rem;
  font-weight: 800;
  letter-spacing: 0.5px;
  text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
}

.modal-subtitle {
  margin: 0;
  color: rgba(255, 255, 255, 0.7);
  font-size: 0.95rem;
  font-weight: 500;
}

.modal-close-btn {
  width: 44px;
  height: 44px;
  background: rgba(255, 255, 255, 0.1);
  border: 2px solid rgba(255, 255, 255, 0.2);
  border-radius: 12px;
  color: rgba(255, 255, 255, 0.8);
  cursor: pointer;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 18px;
}

.modal-close-btn:hover {
  background: linear-gradient(135deg, rgba(255, 77, 77, 0.3), rgba(255, 77, 77, 0.2));
  border-color: rgba(255, 77, 77, 0.5);
  color: #ff4d4d;
  transform: rotate(90deg) scale(1.1);
  box-shadow: 0 4px 12px rgba(255, 77, 77, 0.3);
}

/* Loading Container */
.loading-container {
  text-align: center;
  padding: 60px 20px;
}

.loading-spinner {
  font-size: 3rem;
  color: #FFD700;
  margin-bottom: 20px;
}

.loading-text {
  color: #a1a1a1;
  font-size: 1.1rem;
  margin: 0;
}

/* Error Container */
.error-container {
  text-align: center;
  padding: 40px 20px;
}

.error-icon {
  font-size: 4rem;
  color: #ff6b6b;
  margin-bottom: 20px;
}

.error-title {
  color: #ff6b6b;
  margin-bottom: 12px;
  font-size: 1.3rem;
}

.error-message {
  color: #a1a1a1;
  margin-bottom: 30px;
  line-height: 1.5;
}

.error-actions {
  display: flex;
  justify-content: center;
}

/* Form Container */
.withdrawal-form-container,
.money-form-container {
  padding: 32px;
  background: transparent !important;
}

.balance-display-card {
  background: linear-gradient(135deg, rgba(var(--color-primary-rgb, 255, 215, 0), 0.2), rgba(var(--color-primary-rgb, 255, 215, 0), 0.15)) !important;
  border: 2px solid rgba(var(--color-primary-rgb, 255, 215, 0), 0.4);
  border-radius: 20px;
  padding: 28px;
  margin-bottom: 32px;
  display: flex;
  align-items: center;
  gap: 20px;
  box-shadow: 0 8px 24px rgba(var(--color-primary-rgb, 255, 215, 0), 0.2), inset 0 1px 2px rgba(255, 255, 255, 0.1);
  transition: all 0.3s ease;
  position: relative;
  overflow: hidden;
}

.balance-display-card::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 3px;
  background: linear-gradient(90deg, transparent, var(--color-primary, #FFD700), transparent);
  opacity: 0.6;
}

.balance-display-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 12px 32px rgba(var(--color-primary-rgb, 255, 215, 0), 0.3), inset 0 1px 2px rgba(255, 255, 255, 0.15);
  border-color: rgba(var(--color-primary-rgb, 255, 215, 0), 0.6);
}

.balance-icon {
  width: 64px;
  height: 64px;
  background: linear-gradient(135deg, var(--color-primary, #FFD700), var(--color-primary-light, #FFE55C));
  border-radius: 16px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 28px;
  color: #000000;
  box-shadow: 0 8px 20px rgba(var(--color-primary-rgb, 255, 215, 0), 0.4), inset 0 1px 2px rgba(255, 255, 255, 0.3);
  flex-shrink: 0;
}

.balance-info {
  flex: 1;
}

.balance-label {
  display: block;
  color: rgba(255, 255, 255, 0.7);
  font-size: 0.95rem;
  margin-bottom: 8px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.balance-amount {
  display: block;
  background: linear-gradient(135deg, var(--color-primary, #FFD700), var(--color-primary-light, #FFE55C));
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  font-size: 2.2rem;
  font-weight: 800;
  letter-spacing: 1px;
  text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
  margin-bottom: 4px;
}

.balance-breakdown {
  margin-top: 16px;
  padding-top: 16px;
  border-top: 2px solid rgba(var(--color-primary-rgb, 255, 215, 0), 0.2);
  position: relative;
}

.balance-breakdown::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 1px;
  background: linear-gradient(90deg, transparent, var(--color-primary, #FFD700), transparent);
  opacity: 0.5;
}

.balance-detail {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 10px;
  padding: 8px 12px;
  background: rgba(255, 255, 255, 0.05);
  border-radius: 8px;
  transition: all 0.3s ease;
}

.balance-detail:hover {
  background: rgba(255, 255, 255, 0.1);
  transform: translateX(4px);
}

.balance-detail:last-child {
  margin-bottom: 0;
}

.detail-label {
  color: rgba(255, 255, 255, 0.8);
  font-size: 0.9rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.3px;
}

.detail-value {
  color: #000000;
  font-size: 0.95rem;
  font-weight: 700;
  background: rgba(255, 255, 255, 0.2);
  padding: 4px 10px;
  border-radius: 6px;
  text-shadow: 0 1px 2px rgba(255, 255, 255, 0.3);
}

.detail-value.blocked {
  background: rgba(255, 107, 107, 0.2);
  color: #000000;
  border: 1px solid rgba(255, 107, 107, 0.4);
}

/* Form Styles */
.withdrawal-form,
.money-form {
  display: flex;
  flex-direction: column;
  gap: 24px;
}

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 20px;
}

.form-group-modern {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.form-label-modern {
  display: flex;
  align-items: center;
  gap: 8px;
  color: #e0e0e0;
  font-weight: 600;
  font-size: 0.95rem;
}

.form-label-modern i {
  color: #FFD700;
  font-size: 0.9rem;
}

.input-wrapper {
  position: relative;
}

.form-input-modern,
.form-textarea-modern {
  width: 100%;
  padding: 18px 50px 18px 18px;
  background: linear-gradient(135deg, rgba(255, 255, 255, 0.15), rgba(255, 255, 255, 0.1)) !important;
  border: 2px solid rgba(var(--color-primary-rgb, 255, 215, 0), 0.3) !important;
  border-radius: 14px;
  color: #000000 !important;
  font-size: 1.05rem;
  font-weight: 600;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1), inset 0 1px 2px rgba(255, 255, 255, 0.3);
  text-shadow: 0 1px 2px rgba(255, 255, 255, 0.3);
}

.form-input-modern:focus,
.form-textarea-modern:focus {
  outline: none !important;
  border-color: var(--color-primary, #FFD700) !important;
  box-shadow: 0 0 0 4px rgba(var(--color-primary-rgb, 255, 215, 0), 0.25), 0 6px 16px rgba(var(--color-primary-rgb, 255, 215, 0), 0.2), inset 0 1px 2px rgba(255, 255, 255, 0.4) !important;
  background: linear-gradient(135deg, rgba(255, 255, 255, 0.25), rgba(255, 255, 255, 0.2)) !important;
  transform: translateY(-2px);
}

.form-input-modern.error,
.form-textarea-modern.error {
  border-color: #ff6b6b !important;
  box-shadow: 0 0 0 3px rgba(255, 107, 107, 0.2) !important;
  background: rgba(0, 0, 0, 0.3) !important;
}

.form-textarea-modern {
  resize: vertical;
  min-height: 100px;
  padding-right: 16px;
}

.form-input-modern::placeholder,
.form-textarea-modern::placeholder {
  color: #a1a1a1 !important;
  opacity: 1 !important;
}

.input-icon {
  position: absolute;
  right: 16px;
  top: 50%;
  transform: translateY(-50%);
  color: var(--color-primary, #FFD700);
  font-size: 0.9rem;
}

.input-help {
  color: rgba(255, 255, 255, 0.7);
  font-size: 0.9rem;
  line-height: 1.5;
  font-weight: 500;
  padding: 8px 12px;
  background: rgba(var(--color-primary-rgb, 255, 215, 0), 0.1);
  border-radius: 8px;
  border-left: 3px solid var(--color-primary, #FFD700);
  margin-top: 4px;
}

.error-text {
  display: flex;
  align-items: center;
  gap: 8px;
  color: #000000;
  font-size: 0.9rem;
  font-weight: 600;
  padding: 10px 14px;
  background: linear-gradient(135deg, rgba(255, 107, 107, 0.3), rgba(255, 107, 107, 0.2));
  border: 2px solid rgba(255, 107, 107, 0.5);
  border-radius: 10px;
  margin-top: 8px;
  box-shadow: 0 4px 8px rgba(255, 107, 107, 0.2), inset 0 1px 2px rgba(255, 255, 255, 0.3);
  text-shadow: 0 1px 2px rgba(255, 255, 255, 0.3);
}

/* File Upload Styles */
.file-upload-wrapper {
  position: relative;
}

.file-input-hidden {
  position: absolute;
  opacity: 0;
  width: 0;
  height: 0;
}

.file-upload-area {
  display: block;
  padding: 30px 20px;
  background: rgba(255, 255, 255, 0.05);
  border: 2px dashed var(--color-border-primary, rgba(255, 215, 0, 0.3));
  border-radius: 12px;
  cursor: pointer;
  transition: all 0.3s ease;
  text-align: center;
}

.file-upload-area:hover {
  border-color: var(--color-primary, #FFD700);
  background: rgba(var(--color-primary-rgb, 255, 215, 0), 0.05);
}

.file-upload-content {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 12px;
}

.file-upload-icon {
  font-size: 2.5rem;
  color: var(--color-primary, #FFD700);
}

.file-upload-text {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.upload-title {
  color: #e0e0e0;
  font-weight: 600;
  font-size: 1rem;
}

.upload-subtitle {
  color: #a1a1a1;
  font-size: 0.85rem;
}

.file-selected {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  color: var(--color-primary, #FFD700);
  font-weight: 600;
  margin-top: 12px;
}

.current-receipt {
  margin-top: 12px;
}

.view-receipt-btn {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  background: rgba(0, 191, 255, 0.2);
  color: #00bfff;
  border: 1px solid rgba(0, 191, 255, 0.3);
  padding: 8px 16px;
  border-radius: 8px;
  font-size: 0.9rem;
  cursor: pointer;
  transition: all 0.3s ease;
}

.view-receipt-btn:hover {
  background: rgba(0, 191, 255, 0.3);
  border-color: rgba(0, 191, 255, 0.5);
}

/* Form Actions */
.form-actions {
  display: flex;
  justify-content: flex-end;
  gap: 16px;
  margin-top: 28px;
  padding-top: 24px;
  border-top: 2px solid rgba(var(--color-primary-rgb, 255, 215, 0), 0.2);
  position: relative;
}

.form-actions::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 1px;
  background: linear-gradient(90deg, transparent, var(--color-primary, #FFD700), transparent);
  opacity: 0.5;
}

.btn-cancel {
  display: flex;
  align-items: center;
  gap: 10px;
  background: linear-gradient(135deg, rgba(255, 255, 255, 0.15), rgba(255, 255, 255, 0.1));
  color: #000000;
  border: 2px solid rgba(255, 255, 255, 0.3);
  padding: 14px 28px;
  border-radius: 12px;
  font-weight: 700;
  font-size: 0.95rem;
  letter-spacing: 0.5px;
  text-transform: uppercase;
  cursor: pointer;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2), inset 0 1px 2px rgba(255, 255, 255, 0.3);
  text-shadow: 0 1px 2px rgba(255, 255, 255, 0.3);
}

.btn-cancel:hover {
  background: linear-gradient(135deg, rgba(255, 255, 255, 0.25), rgba(255, 255, 255, 0.2));
  border-color: rgba(255, 255, 255, 0.5);
  color: #000000;
  transform: translateY(-3px) scale(1.02);
  box-shadow: 0 6px 16px rgba(0, 0, 0, 0.3), inset 0 1px 2px rgba(255, 255, 255, 0.4);
}

.btn-submit {
  display: flex;
  align-items: center;
  gap: 10px;
  background: linear-gradient(135deg, var(--color-primary, #FFD700), var(--color-primary-light, #FFE55C));
  color: #000000;
  border: 2px solid rgba(var(--color-primary-rgb, 255, 215, 0), 0.6);
  padding: 14px 32px;
  border-radius: 12px;
  font-weight: 800;
  font-size: 0.95rem;
  letter-spacing: 0.5px;
  text-transform: uppercase;
  cursor: pointer;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  box-shadow: 0 8px 25px rgba(var(--color-primary-rgb, 255, 215, 0), 0.4), inset 0 1px 2px rgba(255, 255, 255, 0.3);
  text-shadow: 0 1px 2px rgba(255, 255, 255, 0.5);
}

.btn-submit:hover:not(:disabled) {
  transform: translateY(-3px) scale(1.05);
  box-shadow: 0 12px 35px rgba(var(--color-primary-rgb, 255, 215, 0), 0.5), inset 0 1px 2px rgba(255, 255, 255, 0.4);
  border-color: rgba(var(--color-primary-rgb, 255, 215, 0), 0.8);
}

.btn-submit:disabled {
  opacity: 0.7;
  cursor: not-allowed;
  transform: none;
}

.btn-loading,
.btn-content {
  display: flex;
  align-items: center;
  gap: 8px;
}

/* Responsive Design */
@media (max-width: 768px) {
  .modal-header-content {
    padding: 20px;
    gap: 12px;
  }
  
  .modal-icon {
    width: 40px;
    height: 40px;
    font-size: 16px;
  }
  
  .modal-title {
    font-size: 1.3rem;
  }
  
  .withdrawal-form-container,
  .money-form-container {
    padding: 20px;
  }
  
  .form-row {
    grid-template-columns: 1fr;
    gap: 16px;
  }
  
  .form-actions {
    flex-direction: column;
  }
  
  .btn-cancel,
  .btn-submit {
    width: 100%;
    justify-content: center;
  }
}
</style>