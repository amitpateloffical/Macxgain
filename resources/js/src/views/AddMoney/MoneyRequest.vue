<template>
  <div class="dashboard">
    <div class="list__page">
      <div v-if="successMessage" class="alert alert-success">
        {{ successMessage }}
      </div>
      <div class="list__title">
        <h4>Money Requests</h4>
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
            {{ requestData }}
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

.list__title {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.filterBox {
  background-color: white;
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  padding: 20px;
  margin-bottom: 20px;
}

.table-container {
  background-color: white;
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  padding: 20px;
}

.staticTable {
  width: 100%;
}

.badge {
  font-size: 0.85em;
  padding: 0.35em 0.65em;
}

.cursor-pointer {
  cursor: pointer;
}

@media (max-width: 768px) {
  .list__title {
    flex-direction: column;
    align-items: flex-start;
  }

  .list__title h4 {
    margin-bottom: 15px;
  }

  .filterBox {
    padding: 15px;
  }

  .table-container {
    padding: 15px;
    overflow-x: auto;
  }
}
</style>