<template>
  <div class="money-request-screen">
    <!-- Header Section -->
    <div class="page-header">
      <div class="header-content">
              <h1 class="page-title">💰 Fund Requests</h1>
      <p class="page-subtitle">Manage all fund requests and transactions</p>
      </div>
      <div class="header-actions">
        <button 
          v-if="!isAdmin"
          class="btn-primary" 
          :disabled="loading"
          @click="openRequestModal()"
        >
          <i class="fa-solid fa-plus"></i> Add Fund
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
      <!-- Balance Card (Only for non-admin users) -->
      <div v-if="!isAdmin" class="stat-card balance-stat-card">
        <div class="stat-icon">💰</div>
        <div class="stat-content">
          <div class="stat-number balance-amount">₹{{ formatBalance(userBalance) }}</div>
          <div class="stat-label">
            Available Balance
            <button @click="fetchUserBalance" class="refresh-btn-mini" :disabled="balanceLoading">
              <i class="fa-solid fa-rotate-right" :class="{ 'spinning': balanceLoading }"></i>
            </button>
          </div>
        </div>
      </div>
      
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

    <!-- Requests Table -->
    <div class="requests-container">
      <div class="table-header">
        <h3>Fund Requests ({{ totalRequests || 0 }})</h3>
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
        <h3>No Fund Requests Found</h3>
        <p>No fund requests found</p>
      </div>
      
      <div v-else class="requests-table">
        <b-table
          :items="fetchRequests"
          :fields="tableFields"
          :current-page="currentPage"
          :per-page="perPage"
          :busy="modalLoading"
          responsive
          class="requests-table dark-table"
        >


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
      <!-- Professional Money Request Modal -->
      <b-modal
        v-model="showRequestModal"
        @hide="resetModal"
        centered
        size="xl"
        hide-footer
        modal-class="modern-modal wide-modal"
        header-class="modern-modal-header"
        body-class="modern-modal-body"
      >
        <template #modal-header="{ close }">
          <div class="modal-header-content">
            <div class="modal-icon">
              <i class="fas fa-credit-card"></i>
            </div>
            <div class="modal-title-section">
              <h4 class="modal-title">{{ isEdit ? 'Edit Money Request' : 'Create Money Request' }}</h4>
              <p class="modal-subtitle">{{ isEdit ? 'Update your money request details' : 'Add funds to your account' }}</p>
            </div>
            <button type="button" class="modal-close-btn" @click="close()">
              <i class="fas fa-times"></i>
            </button>
          </div>
        </template>

        <!-- Loading State -->
        <div v-if="editmodalLoading && isEdit" class="loading-container">
          <div class="loading-spinner">
            <i class="fas fa-spinner fa-spin"></i>
          </div>
          <p class="loading-text">Loading request details...</p>
        </div>

        <!-- Admin Payment Details Section -->
        <div v-if="!editmodalLoading && primaryPaymentDetails" class="admin-payment-section">
          <div class="section-header">
            <div class="section-icon">
              <i class="fas fa-credit-card"></i>
            </div>
                      <div class="section-title">
            <h4>💳 Payment Details</h4>
          </div>
          </div>
          
          <div class="payment-details-grid">
            <div v-if="primaryPaymentDetails.bank_name" class="detail-item locked">
              <label class="detail-label">
                <i class="fas fa-university"></i>
                Bank Name
              </label>
              <span 
                class="detail-value clickable-value" 
                @click="copyToClipboard(primaryPaymentDetails.bank_name, 'Bank Name')"
                :title="`Click to copy ${primaryPaymentDetails.bank_name}`"
              >
                {{ primaryPaymentDetails.bank_name }}
                <i class="fas fa-copy copy-icon"></i>
              </span>
              <i class="fas fa-lock detail-lock"></i>
            </div>
            
            <div v-if="primaryPaymentDetails.account_holder" class="detail-item locked">
              <label class="detail-label">
                <i class="fas fa-user"></i>
                Account Holder
              </label>
              <span 
                class="detail-value clickable-value" 
                @click="copyToClipboard(primaryPaymentDetails.account_holder, 'Account Holder')"
                :title="`Click to copy ${primaryPaymentDetails.account_holder}`"
              >
                {{ primaryPaymentDetails.account_holder }}
                <i class="fas fa-copy copy-icon"></i>
              </span>
              <i class="fas fa-lock detail-lock"></i>
            </div>
            
            <div v-if="primaryPaymentDetails.account_number" class="detail-item locked">
              <label class="detail-label">
                <i class="fas fa-hashtag"></i>
                Account Number
              </label>
              <span 
                class="detail-value clickable-value" 
                @click="copyToClipboard(primaryPaymentDetails.account_number, 'Account Number')"
                :title="`Click to copy ${primaryPaymentDetails.account_number}`"
              >
                {{ primaryPaymentDetails.account_number }}
                <i class="fas fa-copy copy-icon"></i>
              </span>
              <i class="fas fa-lock detail-lock"></i>
            </div>
            
            <div v-if="primaryPaymentDetails.ifsc_code" class="detail-item locked">
              <label class="detail-label">
                <i class="fas fa-angle-double-right"></i>
                IFSC Code
              </label>
              <span 
                class="detail-value clickable-value" 
                @click="copyToClipboard(primaryPaymentDetails.ifsc_code, 'IFSC Code')"
                :title="`Click to copy ${primaryPaymentDetails.ifsc_code}`"
              >
                {{ primaryPaymentDetails.ifsc_code }}
                <i class="fas fa-copy copy-icon"></i>
              </span>
              <i class="fas fa-lock detail-lock"></i>
            </div>
            
            <div v-if="primaryPaymentDetails.qr_code" class="detail-item locked">
              <label class="detail-label">
                <i class="fas fa-qrcode"></i>
                UPI ID
              </label>
              <span 
                class="detail-value clickable-value" 
                @click="copyToClipboard(primaryPaymentDetails.qr_code, 'UPI ID')"
                :title="`Click to copy ${primaryPaymentDetails.qr_code}`"
              >
                {{ primaryPaymentDetails.qr_code }}
                <i class="fas fa-copy copy-icon"></i>
              </span>
              <i class="fas fa-lock detail-lock"></i>
            </div>
            
            <div v-if="primaryPaymentDetails.barcode_image" class="detail-item locked barcode-item">
              <label class="detail-label">
                <i class="fas fa-barcode"></i>
                Barcode
              </label>
              <div class="barcode-preview">
                <img 
                  :src="`/storage/${primaryPaymentDetails.barcode_image}`" 
                  :alt="'Payment Barcode'"
                  class="barcode-thumbnail"
                />
                <button 
                  @click="openBarcodeModal" 
                  class="view-barcode-btn"
                  type="button"
                >
                  <i class="fas fa-eye"></i>
                  View
                </button>
              </div>
              <i class="fas fa-lock detail-lock"></i>
            </div>
          </div>
        </div>

        <!-- Money Request Form -->
        <div v-if="!editmodalLoading" class="money-form-container">
          <form @submit.prevent="submitRequest" class="money-form">
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
                Enter the exact amount you paid
              </div>
              <div v-if="hasErrors('amount')" class="error-text">
                <i class="fas fa-exclamation-circle"></i>
                {{ getErrors("amount") }}
              </div>
            </div>

            <!-- Payment Receipt Upload -->
            <div class="form-group-modern">
              <label class="form-label-modern">
                <i class="fas fa-receipt"></i>
                Payment Receipt
              </label>
              <div class="file-upload-wrapper">
                <input
                  type="file"
                  id="receipt-upload"
                  @change="handleFileChange"
                  accept="image/*"
                  :required="!isEdit"
                  class="file-input-hidden"
                />
                <label for="receipt-upload" class="file-upload-area">
                  <div class="file-upload-content">
                    <div class="file-upload-icon">
                      <i class="fas fa-cloud-upload-alt"></i>
                    </div>
                    <div class="file-upload-text">
                      <span class="upload-title">Choose file or drag here</span>
                      <span class="upload-subtitle">Max 2MB (JPEG, PNG, GIF)</span>
                    </div>
                  </div>
                  <div v-if="requestData.image" class="file-selected">
                    <i class="fas fa-check-circle"></i>
                    File selected
                  </div>
                </label>
              </div>
              <div v-if="isEdit && requestData.image_path" class="current-receipt">
                <button type="button" @click="showImage(requestData.image_path)" class="view-receipt-btn">
                  <i class="fas fa-eye"></i>
                  View Current Receipt
                </button>
              </div>
              <div class="input-help">
                Upload a clear image of your payment receipt or screenshot
              </div>
              <div v-if="hasErrors('image')" class="error-text">
                <i class="fas fa-exclamation-circle"></i>
                {{ getErrors("image") }}
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
                  {{ isEdit ? "Update Request" : "Submit Request" }}
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

      <!-- Barcode Preview Modal -->
      <b-modal 
        v-model="showBarcodeModal" 
        title="Payment Barcode" 
        centered 
        size="lg"
        modal-class="barcode-modal"
      >
        <div class="text-center">
          <img 
            :src="`/storage/${primaryPaymentDetails?.barcode_image}`" 
            alt="Payment Barcode" 
            class="barcode-modal-image" 
          />
        </div>
        <template #modal-footer>
          <b-button variant="primary" @click="downloadBarcode">Download Barcode</b-button>
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

      fetchRequests: [],
      userBalance: 0,
      balanceLoading: false,
      fields: [
        { key: "amount", label: "Amount", sortable: true },
        { key: "status", label: "Status", sortable: true },
        {
          key: "request_by",
          label: "User Name",
          formatter: (value, key, item) => item.requester?.name || 'N/A',
        },
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
      primaryPaymentDetails: null,
      primaryPaymentLoading: false,
      showBarcodeModal: false,
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
        { key: "amount", label: "Amount", sortable: true },
        { key: "status", label: "Status", sortable: true },
        { 
          key: "request_by", 
          label: "User Name", 
          sortable: true,
          formatter: (value, key, item) => item.requester?.name || 'N/A'
        },
        { key: "created_at", label: "Request Date", sortable: true },
        { key: "image_path", label: "Receipt" },
        { key: "actions", label: "Actions" }
      ];
    }
  },
  setup() {
    const currentPage = ref(1);
    const perPage = ref(10);
    const perPageOptions = [10, 25, 50, 100];

    return {
      currentPage,
      perPage,
      perPageOptions,
    };
  },
  mounted() {
    this.fetchUserInfo();
    this.fetchRequestss();
    this.fetchUserBalance();
    this.fetchPrimaryPaymentDetails();
    
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

    fetchPrimaryPaymentDetails() {
      this.primaryPaymentLoading = true;
      axios.get('/payment-collector/primary')
        .then(response => {
          if (response.data.success) {
            this.primaryPaymentDetails = response.data.data;
          }
        })
        .catch(error => {
          console.error('Error fetching primary payment details:', error);
        })
        .finally(() => {
          this.primaryPaymentLoading = false;
        });
    },

      openBarcodeModal() {
    console.log('Opening barcode modal...');
    this.showBarcodeModal = true;
    console.log('showBarcodeModal value:', this.showBarcodeModal);
  },

  copyToClipboard(text, fieldName) {
    if (navigator.clipboard && window.isSecureContext) {
      // Use modern clipboard API
      navigator.clipboard.writeText(text).then(() => {
        this.showCopySuccess(fieldName);
      }).catch(err => {
        console.error('Failed to copy: ', err);
        this.fallbackCopyToClipboard(text, fieldName);
      });
    } else {
      // Fallback for older browsers
      this.fallbackCopyToClipboard(text, fieldName);
    }
  },

  fallbackCopyToClipboard(text, fieldName) {
    const textArea = document.createElement('textarea');
    textArea.value = text;
    textArea.style.position = 'fixed';
    textArea.style.left = '-999999px';
    textArea.style.top = '-999999px';
    document.body.appendChild(textArea);
    textArea.focus();
    textArea.select();
    
    try {
      document.execCommand('copy');
      this.showCopySuccess(fieldName);
    } catch (err) {
      console.error('Fallback copy failed: ', err);
      this.showCopyError(fieldName);
    }
    
    document.body.removeChild(textArea);
  },

  showCopySuccess(fieldName) {
    this.$bvToast.toast(`${fieldName} copied to clipboard!`, {
      title: 'Success',
      variant: 'success',
      solid: true,
      autoHideDelay: 2000,
      appendToast: true
    });
  },

  showCopyError(fieldName) {
    this.$bvToast.toast(`Failed to copy ${fieldName}`, {
      title: 'Error',
      variant: 'danger',
      solid: true,
      autoHideDelay: 3000,
      appendToast: true
    });
  },

    downloadBarcode() {
      if (this.primaryPaymentDetails?.barcode_image) {
        const link = document.createElement('a');
        link.href = `/storage/${this.primaryPaymentDetails.barcode_image}`;
        link.download = 'payment-barcode.png';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
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
        .get("/money-requests", {
          params: {
            page: this.currentPage,
            perPage: this.perPage,
            sortBy: this.sortBy,
            sortDesc: this.sortDesc,
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
            this.requestData.amount = res.amount;
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
        ? axios.put(`/money-requests/${this.currentEditId}`, formData)
        : axios.post("/money-requests", formData);

      request
        .then((response) => {
          this.showRequestModal = false;
          this.fetchRequestss();
          this.successMessage = this.isEdit
            ? "Fund request updated successfully!"
            : "Fund request submitted successfully!";
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
              if (confirm(`Are you sure you want to approve this fund request?`)) {
        axios
          .patch(`/money-requests/${request.id}/status`, { status: "approved" })
          .then(() => {
            this.fetchRequestss();
            this.successMessage = "Fund request approved successfully!";
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
  background: transparent !important;
  color: white !important;
}

.requests-table .table th {
  background: rgba(0, 255, 128, 0.1) !important;
  border-color: rgba(0, 255, 128, 0.2) !important;
  color: #00ff80 !important;
  font-weight: 600;
  padding: 16px 12px;
}

.requests-table .table td {
  background: transparent !important;
  border-color: rgba(0, 255, 128, 0.1) !important;
  padding: 16px 12px;
  vertical-align: middle;
  color: white !important;
}

.requests-table .table tbody tr {
  background: transparent !important;
}

.requests-table .table tbody tr:hover {
  background: rgba(0, 255, 128, 0.05) !important;
}

/* Override Bootstrap striped table styling */
.dark-table .table-striped tbody tr:nth-of-type(odd) {
  background: rgba(0, 255, 128, 0.02) !important;
}

.dark-table .table-striped tbody tr:nth-of-type(even) {
  background: transparent !important;
}

/* Ensure all table cells have dark background */
.dark-table .table tbody tr td,
.dark-table .table thead tr th {
  background-color: transparent !important;
  color: white !important;
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

/* Balance Stat Card Styles */
.balance-stat-card {
  background: linear-gradient(135deg, rgba(0, 255, 128, 0.1), rgba(0, 204, 102, 0.05)) !important;
  border: 1px solid rgba(0, 255, 128, 0.3) !important;
  position: relative;
  overflow: hidden;
}

.balance-stat-card::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 3px;
  background: linear-gradient(90deg, #00ff80, #00cc66);
}

.balance-stat-card:hover {
  border-color: rgba(0, 255, 128, 0.5) !important;
  box-shadow: 0 8px 25px rgba(0, 255, 128, 0.2) !important;
  transform: translateY(-3px) !important;
}

.balance-amount {
  color: #00ff80 !important;
  text-shadow: 0 0 10px rgba(0, 255, 128, 0.3);
  font-weight: 700 !important;
}

.refresh-btn-mini {
  background: rgba(0, 255, 128, 0.1);
  border: 1px solid rgba(0, 255, 128, 0.3);
  color: #00ff80;
  padding: 2px 4px;
  border-radius: 4px;
  font-size: 10px;
  cursor: pointer;
  transition: all 0.3s ease;
  margin-left: 8px;
  display: inline-flex;
  align-items: center;
}

.refresh-btn-mini:hover:not(:disabled) {
  background: rgba(0, 255, 128, 0.2);
  border-color: rgba(0, 255, 128, 0.5);
  transform: scale(1.1);
}

.refresh-btn-mini:disabled {
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

/* Admin Payment Details Section */
.admin-payment-section {
  background: rgba(0, 0, 0, 0.4);
  border: 1px solid rgba(0, 255, 128, 0.4);
  border-radius: 16px;
  padding: 24px;
  margin-bottom: 24px;
  backdrop-filter: blur(10px);
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.4);
}

.section-header {
  display: flex;
  align-items: center;
  gap: 16px;
  margin-bottom: 20px;
}

.section-icon {
  width: 48px;
  height: 48px;
  background: linear-gradient(135deg, #00ff80 0%, #00d4aa 100%);
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #0f0f23;
  font-size: 20px;
}

.section-title h4 {
  margin: 0;
  color: white;
  font-size: 1.25rem;
  font-weight: 600;
}



.payment-details-grid {
  display: grid;
  grid-template-columns: repeat(6, 1fr);
  gap: 12px;
  margin-bottom: 24px;
}



.detail-item {
  background: rgba(0, 0, 0, 0.5);
  border: 1px solid rgba(0, 255, 128, 0.3);
  border-radius: 12px;
  padding: 16px;
  position: relative;
  transition: all 0.3s ease;
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.3);
}

.detail-item:hover {
  border-color: rgba(0, 255, 128, 0.4);
  background: rgba(0, 0, 0, 0.4);
}

.detail-label {
  display: flex;
  align-items: center;
  gap: 8px;
  color: #a1a1a1;
  font-size: 0.875rem;
  font-weight: 500;
  margin-bottom: 8px;
}

.detail-value {
  color: white;
  font-size: 1rem;
  font-weight: 600;
  font-family: 'Courier New', monospace;
  letter-spacing: 0.5px;
}

.clickable-value {
  cursor: pointer;
  position: relative;
  padding: 8px 12px;
  border-radius: 8px;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  justify-content: space-between;
  background: rgba(0, 255, 128, 0.1);
  border: 1px solid rgba(0, 255, 128, 0.2);
}

.clickable-value:hover {
  background: rgba(0, 255, 128, 0.2);
  border-color: rgba(0, 255, 128, 0.4);
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(0, 255, 128, 0.2);
}

.copy-icon {
  color: rgba(0, 255, 128, 0.7);
  font-size: 14px;
  margin-left: 8px;
  opacity: 0.7;
  transition: all 0.3s ease;
}

.clickable-value:hover .copy-icon {
  opacity: 1;
  color: #00ff80;
  transform: scale(1.1);
}

.detail-lock {
  position: absolute;
  top: 12px;
  right: 12px;
  color: rgba(0, 255, 128, 0.6);
  font-size: 14px;
}

/* Barcode Image Styles */
.barcode-preview {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-top: 8px;
}

.barcode-thumbnail {
  width: 60px;
  height: 40px;
  object-fit: cover;
  border-radius: 6px;
  border: 1px solid rgba(0, 255, 128, 0.3);
  background: rgba(0, 0, 0, 0.2);
}

.view-barcode-btn {
  background: linear-gradient(135deg, #00ff80 0%, #00d4aa 100%);
  color: #0f0f23;
  border: none;
  border-radius: 6px;
  padding: 6px 12px;
  font-size: 0.75rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  gap: 6px;
  min-width: 60px;
  justify-content: center;
}

.view-barcode-btn:hover {
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(0, 255, 128, 0.3);
}

.view-barcode-btn:active {
  transform: translateY(0);
}

/* Barcode Modal Styles */
.barcode-modal-image {
  max-width: 100%;
  height: auto;
  border-radius: 12px;
  border: 2px solid rgba(0, 255, 128, 0.3);
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
}

/* Ensure barcode modal is visible */
.barcode-modal .modal-content {
  background: #1a1a2e !important;
  border: 1px solid rgba(0, 255, 128, 0.3) !important;
  color: white !important;
}

.barcode-modal .modal-header {
  background: linear-gradient(135deg, rgba(0, 255, 128, 0.1) 0%, rgba(0, 212, 170, 0.1) 100%);
  border-bottom: 1px solid rgba(0, 255, 128, 0.2);
}

.barcode-modal .modal-title {
  color: #00ff80;
  font-weight: 600;
}

.barcode-modal .modal-body {
  background: #1a1a2e !important;
  padding: 24px;
}

/* Responsive Design for Admin Payment Section */
@media (max-width: 1400px) {
  .payment-details-grid {
    grid-template-columns: repeat(4, 1fr);
    gap: 10px;
  }
  
  .detail-item {
    padding: 14px;
  }
}

@media (max-width: 1200px) {
  .payment-details-grid {
    grid-template-columns: repeat(3, 1fr);
    gap: 12px;
  }
  
  .wide-modal .modal-dialog {
    max-width: 90vw !important;
    width: 90vw !important;
  }
}

@media (max-width: 992px) {
  .payment-details-grid {
    grid-template-columns: repeat(2, 1fr);
    gap: 12px;
  }
  
  .wide-modal .modal-dialog {
    max-width: 95vw !important;
    width: 95vw !important;
  }
  
  .admin-payment-section {
    padding: 20px;
  }
}

@media (max-width: 768px) {
  .admin-payment-section {
    padding: 16px;
    margin-bottom: 16px;
  }
  
  .section-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 12px;
  }
  
  .section-icon {
    width: 40px;
    height: 40px;
    font-size: 16px;
  }
  
  .payment-details-grid {
    grid-template-columns: repeat(2, 1fr);
    gap: 10px;
  }
  
  .detail-item {
    padding: 12px;
  }
  
  .detail-value {
    font-size: 0.9rem;
  }
  
  .clickable-value {
    padding: 6px 8px;
    flex-direction: column;
    gap: 6px;
    text-align: center;
  }
  
  .copy-icon {
    margin-left: 0;
    margin-top: 4px;
  }
  
  .barcode-preview {
    flex-direction: column;
    gap: 8px;
  }
  
  .barcode-thumbnail {
    width: 50px;
    height: 35px;
  }
  
  .view-barcode-btn {
    min-width: 50px;
    padding: 4px 8px;
    font-size: 0.7rem;
  }
}

@media (max-width: 576px) {
  .wide-modal .modal-dialog {
    max-width: 98vw !important;
    width: 98vw !important;
    margin: 10px;
  }
  
  .admin-payment-section {
    padding: 12px;
    margin-bottom: 12px;
  }
  
  .section-title h4 {
    font-size: 1.1rem;
  }
  
  .payment-details-grid {
    grid-template-columns: 1fr;
    gap: 8px;
  }
  
  .detail-item {
    padding: 10px;
  }
  
  .detail-label {
    font-size: 0.8rem;
  }
  
  .detail-value {
    font-size: 0.85rem;
  }
  
  .clickable-value {
    padding: 8px;
    justify-content: space-between;
    flex-direction: row;
  }
  
  .copy-icon {
    margin-left: 8px;
    margin-top: 0;
  }
  
  .barcode-preview {
    flex-direction: row;
    gap: 8px;
    justify-content: center;
  }
  
  .barcode-thumbnail {
    width: 45px;
    height: 30px;
  }
  
  .view-barcode-btn {
    min-width: 45px;
    padding: 3px 6px;
    font-size: 0.65rem;
  }
}

@media (max-width: 480px) {
  .wide-modal .modal-dialog {
    max-width: 100vw !important;
    width: 100vw !important;
    margin: 0;
    border-radius: 0;
  }
  
  .admin-payment-section {
    padding: 10px;
    border-radius: 12px;
  }
  
  .section-header {
    gap: 8px;
  }
  
  .section-icon {
    width: 36px;
    height: 36px;
    font-size: 14px;
  }
  
  .section-title h4 {
    font-size: 1rem;
  }
  
  .detail-item {
    padding: 8px;
    border-radius: 8px;
  }
  
  .detail-label {
    font-size: 0.75rem;
    margin-bottom: 6px;
  }
  
  .detail-value {
    font-size: 0.8rem;
  }
  
  .clickable-value {
    padding: 6px;
  }
  
  .barcode-thumbnail {
    width: 40px;
    height: 28px;
  }
  
  .view-barcode-btn {
    min-width: 40px;
    padding: 2px 4px;
    font-size: 0.6rem;
  }
}

/* Modern Modal Styles */
.modern-modal .modal-dialog {
  max-width: 600px;
}

.wide-modal .modal-dialog {
  max-width: 95vw !important;
  width: 95vw !important;
  margin: 0 auto;
}

/* Enhanced Modal Responsiveness */
@media (max-width: 1400px) {
  .wide-modal .modal-dialog {
    max-width: 92vw !important;
    width: 92vw !important;
  }
}

@media (max-width: 1200px) {
  .wide-modal .modal-dialog {
    max-width: 90vw !important;
    width: 90vw !important;
  }
  
  .modern-modal .modal-content {
    border-radius: 16px;
  }
}

@media (max-width: 992px) {
  .wide-modal .modal-dialog {
    max-width: 95vw !important;
    width: 95vw !important;
  }
  
  .modal-header-content {
    padding: 20px;
  }
  
  .modal-icon {
    width: 45px;
    height: 45px;
    font-size: 18px;
  }
  
  .modal-title {
    font-size: 1.3rem;
  }
}

@media (max-width: 768px) {
  .wide-modal .modal-dialog {
    max-width: 98vw !important;
    width: 98vw !important;
    margin: 10px;
  }
  
  .modal-header-content {
    padding: 16px;
    flex-direction: column;
    gap: 12px;
  }
  
  .modal-icon {
    width: 40px;
    height: 40px;
    font-size: 16px;
  }
  
  .modal-title {
    font-size: 1.2rem;
  }
  
  .modal-subtitle {
    font-size: 0.85rem;
  }
}

@media (max-width: 576px) {
  .wide-modal .modal-dialog {
    max-width: 100vw !important;
    width: 100vw !important;
    margin: 0;
    border-radius: 0;
  }
  
  .modern-modal .modal-content {
    border-radius: 0;
  }
  
  .modal-header-content {
    padding: 12px;
  }
  
  .modal-icon {
    width: 36px;
    height: 36px;
    font-size: 14px;
  }
  
  .modal-title {
    font-size: 1.1rem;
  }
  
  .modal-subtitle {
    font-size: 0.8rem;
  }
}

.modern-modal .modal-content {
  background: #1a1a2e !important;
  border: 1px solid rgba(0, 255, 128, 0.3) !important;
  border-radius: 20px;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.8) !important;
  overflow: hidden;
}

.modern-modal .modal-body {
  background: #1a1a2e !important;
  color: white !important;
}

.modal-header-content {
  display: flex;
  align-items: center;
  gap: 16px;
  padding: 24px;
  background: linear-gradient(135deg, rgba(0, 255, 128, 0.1) 0%, rgba(0, 212, 170, 0.1) 100%);
  border-bottom: 1px solid rgba(0, 255, 128, 0.2);
}

.modal-icon {
  width: 50px;
  height: 50px;
  background: linear-gradient(135deg, #00ff80, #00d4aa);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 20px;
  color: #1a1a2e;
  box-shadow: 0 8px 25px rgba(0, 255, 128, 0.3);
}

.modal-title-section {
  flex: 1;
}

.modal-title {
  margin: 0 0 4px 0;
  color: #00ff80;
  font-size: 1.5rem;
  font-weight: 700;
}

.modal-subtitle {
  margin: 0;
  color: #a1a1a1;
  font-size: 0.9rem;
}

.modal-close-btn {
  width: 40px;
  height: 40px;
  background: rgba(255, 255, 255, 0.1);
  border: none;
  border-radius: 50%;
  color: #a1a1a1;
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  justify-content: center;
}

.modal-close-btn:hover {
  background: rgba(255, 77, 77, 0.2);
  color: #ff4d4d;
  transform: rotate(90deg);
}

/* Loading Container */
.loading-container {
  text-align: center;
  padding: 60px 20px;
}

.loading-spinner {
  font-size: 3rem;
  color: #00ff80;
  margin-bottom: 20px;
}

.loading-text {
  color: #a1a1a1;
  font-size: 1.1rem;
  margin: 0;
}

/* Form Container */
.money-form-container {
  padding: 30px;
  background: #1a1a2e !important;
}

/* Form Styles */
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
  color: #00ff80;
  font-size: 0.9rem;
}

.input-wrapper {
  position: relative;
}

.form-input-modern,
.form-textarea-modern {
  width: 100%;
  padding: 16px 50px 16px 16px;
  background: rgba(0, 0, 0, 0.3) !important;
  border: 1px solid rgba(0, 255, 128, 0.3) !important;
  border-radius: 12px;
  color: white !important;
  font-size: 1rem;
  transition: all 0.3s ease;
}

.form-input-modern:focus,
.form-textarea-modern:focus {
  outline: none !important;
  border-color: #00ff80 !important;
  box-shadow: 0 0 0 3px rgba(0, 255, 128, 0.2) !important;
  background: rgba(0, 0, 0, 0.4) !important;
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
  color: #00ff80;
  font-size: 0.9rem;
}

.input-help {
  color: #a1a1a1;
  font-size: 0.85rem;
  line-height: 1.4;
}

.error-text {
  display: flex;
  align-items: center;
  gap: 6px;
  color: #ff6b6b;
  font-size: 0.85rem;
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
  background: rgba(0, 0, 0, 0.2) !important;
  border: 2px dashed rgba(0, 255, 128, 0.4) !important;
  border-radius: 12px;
  cursor: pointer;
  transition: all 0.3s ease;
  text-align: center;
}

.file-upload-area:hover {
  border-color: #00ff80 !important;
  background: rgba(0, 255, 128, 0.1) !important;
}

.file-upload-content {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 12px;
}

.file-upload-icon {
  font-size: 2.5rem;
  color: #00ff80;
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
  color: #00ff80;
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
  margin-top: 20px;
  padding-top: 20px;
  border-top: 1px solid rgba(255, 255, 255, 0.1);
}

.btn-cancel {
  display: flex;
  align-items: center;
  gap: 8px;
  background: rgba(255, 255, 255, 0.1);
  color: #a1a1a1;
  border: 1px solid rgba(255, 255, 255, 0.2);
  padding: 12px 24px;
  border-radius: 10px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
}

.btn-cancel:hover {
  background: rgba(255, 255, 255, 0.15);
  color: white;
  transform: translateY(-2px);
}

.btn-submit {
  display: flex;
  align-items: center;
  gap: 8px;
  background: linear-gradient(135deg, #00ff80, #00d4aa);
  color: #1a1a2e;
  border: none;
  padding: 12px 24px;
  border-radius: 10px;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.3s ease;
  box-shadow: 0 8px 25px rgba(0, 255, 128, 0.3);
}

.btn-submit:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 12px 35px rgba(0, 255, 128, 0.4);
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