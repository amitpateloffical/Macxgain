<template>
  <div class="withdrawal-request-screen">
    <!-- Header Section -->
    <div class="page-header">
      <div class="header-content">
        <h1 class="page-title">💳 Withdrawal Request</h1>
        <p class="page-subtitle">Manage and process withdrawal requests from users</p>
      </div>
      <div class="header-actions">
        <!-- New Request button removed -->
      </div>
    </div>

    <!-- Stats Cards -->
    <div class="stats-grid">
      <div 
        class="stat-card total clickable"
        :class="{ active: activeStatus === 'all' }"
        @click="activeStatus = 'all'"
      >
        <div class="stat-icon">📊</div>
        <div class="stat-content">
          <div class="stat-number">{{ totalRequests || 0 }}</div>
          <div class="stat-label">Total Requests</div>
        </div>
      </div>
      <div 
        class="stat-card pending clickable"
        :class="{ active: activeStatus === 'pending' }"
        @click="activeStatus = 'pending'"
      >
        <div class="stat-icon">⏳</div>
        <div class="stat-content">
          <div class="stat-number">{{ pendingRequests || 0 }}</div>
          <div class="stat-label">Pending</div>
        </div>
      </div>
      <div 
        class="stat-card approved clickable"
        :class="{ active: activeStatus === 'approved' }"
        @click="activeStatus = 'approved'"
      >
        <div class="stat-icon">✅</div>
        <div class="stat-content">
          <div class="stat-number">{{ approvedRequests || 0 }}</div>
          <div class="stat-label">Approved</div>
        </div>
      </div>
      <div 
        class="stat-card rejected clickable"
        :class="{ active: activeStatus === 'rejected' }"
        @click="activeStatus = 'rejected'"
      >
        <div class="stat-icon">❌</div>
        <div class="stat-content">
          <div class="stat-number">{{ rejectedRequests || 0 }}</div>
          <div class="stat-label">Rejected</div>
        </div>
      </div>
      <div class="stat-card amount">
        <div class="stat-icon">💰</div>
        <div class="stat-content">
          <div class="stat-number">₹{{ formatAmount(totalAmount) }}</div>
          <div class="stat-label">Total Amount</div>
        </div>
      </div>
    </div>

    <!-- Mobile Navigation Component -->
    <AdminMobileNav />

    <!-- Requests List -->
    <div class="requests-container">
      <div v-if="filteredRequests.length === 0" class="no-requests">
        <div class="no-requests-icon">📭</div>
        <h3>No {{ activeStatus === 'all' ? '' : activeStatus }} requests found</h3>
        <p>No withdrawal requests available at the moment</p>
      </div>
      
      <div v-else class="requests-grid">
        <div 
          v-for="request in filteredRequests" 
          :key="request.id" 
          class="request-card"
          :class="getStatusClass(request.status)"
        >
          <!-- Request Header -->
          <div class="request-header">
            <div class="user-info">
              <div class="user-avatar">
                <img 
                  :src="getProfileImageUrl(request?.requester?.profile_image)"
                  :alt="request?.requester?.name || 'Unknown User'"
                  @error="handleProfileImageError($event, request.requester.name)"
                  @load="() => console.log('Profile image loaded successfully for:', request.requester.name)"
                />
              </div>
              <div class="user-details">
          <h4 class="user-name">{{ request?.requester?.name || 'Unknown' }}</h4>
          <p class="user-email">{{ request?.requester?.email || 'No Email' }}</p>
          <p class="user-phone">
            {{ request?.requester?.mobile_code || '' }} {{ request?.requester?.phone || '' }}
          </p>              </div>
            </div>
            <div class="request-status" :class="getStatusClass(request.status)">
              <span class="status-badge">{{ getStatusLabel(request.status) }}</span>
            </div>
          </div>

          <!-- Request Details -->
          <div class="request-details">
            <div class="detail-row">
              <span class="detail-label">Amount:</span>
              <span class="detail-value amount">₹{{ parseFloat(request.amount).toLocaleString() }}</span>
            </div>
            <div class="detail-row">
              <span class="detail-label">Requested:</span>
              <span class="detail-value">{{ formatDate(request.created_at) }}</span>
            </div>
           <div class="detail-row" v-if="request?.requester?.bank_name">
  <span class="detail-label">Bank Details:</span>
  <span class="detail-value bank-details">
    {{ request?.requester?.bank_name || 'N/A' }} - 
    {{ request?.requester?.account_no || 'N/A' }} - 
    {{ request?.requester?.ifsc_code || 'N/A' }}
  </span>
</div>


            <div class="detail-row" v-if="request.reject_reason">
              <span class="detail-label">Rejection Reason:</span>
              <span class="detail-value reject-reason">{{ request.reject_reason }}</span>
            </div>
            <div class="detail-row" v-if="request.approve_date">
              <span class="detail-label">Processed On:</span>
              <span class="detail-value">{{ formatDate(request.approve_date) }}</span>
            </div>
          </div>

          <!-- Action Buttons -->
        <div class="request-actions">
      <!-- Approve -->
      <button 
        v-if="request.status === 'pending'"
        class="btn-action btn-approve"
        @click="confirmApprove(request.id, request.amount)"
        :disabled="loading"
      >
        <i class="fa-solid fa-check"></i> Approve
      </button>
      
      <!-- Reject -->
      <button 
        v-if="request.status === 'pending'"
        class="btn-action btn-reject"
        @click="showRejectDialog(request.id)"
        :disabled="loading"
      >
        <i class="fa-solid fa-times"></i> Reject
      </button>
      
      <!-- Locked (approved/rejected) -->
      <button 
        v-if="request.status !== 'pending'"
        class="btn-action btn-lock"
        disabled
      >
        <i class="fa-solid fa-lock"></i> 
        {{ request.status === 'approved' ? 'Approved' : 'Rejected' }}
      </button>
    </div>

 

          <!-- Status Timeline -->
          <div class="status-timeline">
            <div class="timeline-item" :class="{ active: ['pending', 'approved', 'rejected'].includes(request.status) }">
              <div class="timeline-icon">📝</div>
              <div class="timeline-content">
                <span class="timeline-status">Request Submitted</span>
                <span class="timeline-date">{{ formatDate(request.created_at) }}</span>
              </div>
            </div>
            
            <div class="timeline-item" :class="{ active: ['approved', 'rejected'].includes(request.status) }">
              <div class="timeline-icon">{{ request.status === 'approved' ? '✅' : '❌' }}</div>
              <div class="timeline-content">
                <span class="timeline-status">{{ request.status === 'approved' ? 'Approved' : 'Rejected' }}</span>
                <span class="timeline-date" v-if="request.approve_date">{{ formatDate(request.approve_date) }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

 <b-modal v-model="showRejectModal" title="Reject Request" hide-footer>
  <div>
    <!-- Full width textarea -->
    <b-form-textarea
      v-model="rejectReason"
      placeholder="Enter rejection reason"
      rows="4"
      class="w-100"
    ></b-form-textarea>


    <div class="request-actions mt-3 d-flex justify-content-end">
      <button 
        @click="confirmReject" 
        class="btn-action btn-approve me-2"
      >
        Confirm
      </button>
      <button 
        @click="showRejectModal = false" 
        class="btn-action btn-reject"
      >
        Cancel
      </button>
    </div>
  </div>
</b-modal>


    <!-- Pagination -->
    <div class="pagination" v-if="totalPages > 1">
      <button @click="prevPage" :disabled="currentPage === 1">
        <i class="fa-solid fa-chevron-left"></i>
      </button>
      <span>Page {{ currentPage }} of {{ totalPages }}</span>
      <button @click="nextPage" :disabled="currentPage === totalPages">
        <i class="fa-solid fa-chevron-right"></i>
      </button>
    </div>

    <!-- Loading Indicator -->
    <div v-if="loading" class="loading-indicator">
      <i class="fa-solid fa-spinner fa-spin"></i> Loading requests...
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import axios from '@axios'
import AdminMobileNav from '../../components/AdminMobileNav.vue';

// Reactive Data
const activeStatus = ref('all')
const requests = ref([])
const loading = ref(false)
const currentPage = ref(1)
const perPage = ref(10)
const totalrows = ref(0)
const showRejectModal = ref(false)
const rejectReason = ref('')
const currentRejectId = ref(null)

// Status Options
const statusOptions = [
  { value: 'all', label: 'All Requests' },
  { value: 'pending', label: 'Pending' },
  { value: 'approved', label: 'Approved' },
  { value: 'rejected', label: 'Rejected' }
]

// Stats Computed Properties
const totalRequests = computed(() => requests.value.length)
const pendingRequests = computed(() => requests.value.filter(r => r.status === 'pending').length)
const approvedRequests = computed(() => requests.value.filter(r => r.status === 'approved').length)
const rejectedRequests = computed(() => requests.value.filter(r => r.status === 'rejected').length)
const totalAmount = computed(() => {
  return requests.value
    .filter(r => r.status === 'approved')
    .reduce((sum, r) => sum + parseFloat(r.amount || 0), 0)
})

// Computed Properties
const filteredRequests = computed(() => {
  if (activeStatus.value === 'all') {
    return requests.value
  }
  return requests.value.filter(request => request.status === activeStatus.value)
})

const totalPages = computed(() => {
  return Math.ceil(totalrows.value / perPage.value)
})

// Helper Methods for Profile Images
const getProfileImageUrl = (profileImagePath) => {
  if (!profileImagePath) {
    return '/build/assets/tableprofileimg-DaN7tIxX.png'
  }

  // If it's already a full URL, return as is
  if (profileImagePath.startsWith('http')) {
    return profileImagePath
  }

  // Construct full URL for stored images
  return `${window.location.origin}/storage/${profileImagePath}`
}


const handleProfileImageError = (event, userName) => {
  console.log(`Profile image failed to load for ${userName}, creating initials fallback`)
  console.log('Failed image src:', event.target.src)
  
  event.target.style.display = 'none'
  // Create initials fallback
  const initials = userName.split(' ').map(n => n[0]).join('').toUpperCase().substring(0, 3)
  const fallbackDiv = document.createElement('div')
  fallbackDiv.className = 'profile-initials-fallback'
  fallbackDiv.textContent = initials
  fallbackDiv.style.cssText = `
    width: 45px;
    height: 45px;
    border-radius: 50%;
    background: linear-gradient(135deg, #FFD700, #DAA520);
    color: #000;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 14px;
    border: 3px solid #FFD700;
  `
  event.target.parentNode.appendChild(fallbackDiv)
}

// Methods
const fetchRequests = async () => {
  try {
    loading.value = true
    const response = await axios.get("/withdrawal-request", {
      params: {
        page: currentPage.value,
        perPage: perPage.value,
        status: activeStatus.value === 'all' ? null : activeStatus.value
      }
    })
    
    requests.value = response.data.data
    totalrows.value = response.data.total
  } catch (error) {
    console.error("Error fetching requests:", error)
    alert("Failed to load withdrawal requests")
  } finally {
    loading.value = false
  }
}

const updateStatus = async (requestId, newStatus) => {
  try {
    loading.value = true
    const payload = newStatus === 'rejected'
      ? { reject_reason: rejectReason.value }
      : {}

    const response = await axios.patch(`/withdrawal-request/${requestId}/status`, {
      status: newStatus,
      ...payload
    })

    // Check if response contains an error message
    if (response.data && response.data.error) {
      alert(response.data.message || "Failed to update request status")
      return
    }

    await fetchRequests()

    if (newStatus === 'rejected') {
      showRejectModal.value = false
      rejectReason.value = ''
      currentRejectId.value = null
    }

    alert(`Request has been ${newStatus} successfully!`)
  } catch (error) {
    console.error("Error updating request status:", error)
    const errorMessage = error.response?.data?.message || error.message || "Failed to update request status"
    alert(errorMessage)
  } finally {
    loading.value = false
  }
}

// Confirm approve
const confirmApprove = (requestId, amount) => {
  if (confirm(`Are you sure you want to approve this withdrawal request of ₹${parseFloat(amount).toLocaleString()}?`)) {
    updateStatus(requestId, 'approved')
  }
}

// Show reject modal
const showRejectDialog = (requestId) => {
  currentRejectId.value = requestId
  rejectReason.value = ''
  showRejectModal.value = true
}

// Confirm reject
const confirmReject = () => {
  if (!rejectReason.value.trim()) {
    alert("Please enter a rejection reason")
    return
  }
  updateStatus(currentRejectId.value, 'rejected')
}

const getStatusCount = (status) => {
  if (status === 'all') return totalrows.value
  return requests.value.filter(r => r.status === status).length
}

const getStatusClass = (status) => {
  return {
    pending: 'status-pending',
    approved: 'status-approved',
    rejected: 'status-rejected'
  }[status] || 'status-pending'
}

const getStatusLabel = (status) => {
  return {
    pending: 'Pending',
    approved: 'Approved',
    rejected: 'Rejected'
  }[status] || 'Pending'
}

const formatDate = (dateString) => {
  if (!dateString) return 'N/A'
  const date = new Date(dateString)
  return date.toLocaleDateString('en-IN', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const formatAmount = (amount) => {
  if (!amount) return '0'
  return parseFloat(amount).toLocaleString('en-IN')
}

const nextPage = () => {
  if (currentPage.value < totalPages.value) {
    currentPage.value++
    fetchRequests()
  }
}

const prevPage = () => {
  if (currentPage.value > 1) {
    currentPage.value--
    fetchRequests()
  }
}

// Initial fetch
onMounted(fetchRequests)

// Watch for status filter changes
watch(activeStatus, (newVal, oldVal) => {
  if (newVal !== oldVal) {
    currentPage.value = 1
    fetchRequests()
  }
})
</script>



<style scoped>
.withdrawal-request-screen {
  padding: 24px;
  max-width: 1400px;
  margin: 0 auto;
  min-height: 100vh;
  background: linear-gradient(135deg, #0d0d1a 0%, #101022 100%);
  color: white;
}

/* Page Header */
.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 32px;
  padding: 24px;
  background: rgba(255, 255, 255, 0.05);
  border-radius: 16px;
  border: 1px solid rgba(255, 215, 0, 0.2);
}

.header-content h1 {
  font-size: 2.5rem;
  font-weight: bold;
  color: #FFD700;
  margin: 0 0 8px 0;
}

.header-content p {
  font-size: 1.1rem;
  color: #a1a1a1;
  margin: 0;
}

/* Stats Grid */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 20px;
  margin-bottom: 30px;
}

.stat-card {
  background: rgba(255, 255, 255, 0.05);
  backdrop-filter: blur(10px);
  border-radius: 16px;
  padding: 20px;
  border: 1px solid rgba(255, 255, 255, 0.1);
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  gap: 15px;
}

.stat-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 15px 35px rgba(0, 0, 0, 0.3);
}

.stat-card.clickable {
  cursor: pointer;
  position: relative;
  overflow: hidden;
}

.stat-card.clickable:hover {
  transform: translateY(-8px);
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4);
}

.stat-card.clickable.active {
  background: rgba(255, 215, 0, 0.1);
  border-color: #FFD700;
  box-shadow: 0 0 20px rgba(255, 215, 0, 0.3);
}

.stat-card.clickable.active::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 3px;
  background: linear-gradient(135deg, #FFD700, #00d4ff);
}

.stat-card.total {
  border-left: 4px solid #3b82f6;
}

.stat-card.pending {
  border-left: 4px solid #f59e0b;
}

.stat-card.approved {
  border-left: 4px solid #10b981;
}

.stat-card.rejected {
  border-left: 4px solid #ef4444;
}

.stat-card.amount {
  border-left: 4px solid #8b5cf6;
}

.stat-icon {
  font-size: 2rem;
  opacity: 0.8;
}

.stat-content {
  flex: 1;
}

.stat-number {
  font-size: 1.8rem;
  font-weight: 700;
  margin-bottom: 5px;
  color: #ffffff;
}

.stat-label {
  font-size: 0.9rem;
  color: rgba(255, 255, 255, 0.7);
  font-weight: 500;
}

.btn-primary {
  background: linear-gradient(135deg, #FFD700, #DAA520);
  color: #0d0d1a;
  border: none;
  padding: 12px 24px;
  border-radius: 8px;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  gap: 8px;
}

.btn-primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(255, 215, 0, 0.3);
}

/* Modal Styles */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.8);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.modal-content {
  background: #1a1a2e;
  border-radius: 16px;
  width: 90%;
  max-width: 600px;
  max-height: 90vh;
  overflow-y: auto;
  border: 1px solid rgba(255, 215, 0, 0.3);
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 24px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.modal-header h3 {
  margin: 0;
  color: #FFD700;
  font-size: 1.5rem;
}

.close-btn {
  background: none;
  border: none;
  color: #a1a1a1;
  font-size: 24px;
  cursor: pointer;
  padding: 0;
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  transition: all 0.3s ease;
}

.close-btn:hover {
  background: rgba(255, 255, 255, 0.1);
  color: white;
}

.modal-body {
  padding: 24px;
}

/* Form Styles */
.request-form {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.form-group label {
  font-weight: 600;
  color: #e0e0e0;
  font-size: 0.95rem;
}

.form-control {
  padding: 12px 16px;
  border: 1px solid rgba(255, 255, 255, 0.2);
  border-radius: 8px;
  background: rgba(255, 255, 255, 0.05);
  color: white;
  font-size: 1rem;
  transition: all 0.3s ease;
}

.form-control:focus {
  outline: none;
  border-color: #FFD700;
  box-shadow: 0 0 0 3px rgba(255, 215, 0, 0.1);
}

.form-control::placeholder {
  color: #666;
}

.form-actions {
  display: flex;
  gap: 16px;
  justify-content: flex-end;
  margin-top: 8px;
}

.btn-secondary {
  background: rgba(255, 255, 255, 0.1);
  color: white;
  border: 1px solid rgba(255, 255, 255, 0.2);
  padding: 12px 24px;
  border-radius: 8px;
  font-size: 1rem;
  cursor: pointer;
  transition: all 0.3s ease;
}

.btn-secondary:hover {
  background: rgba(255, 255, 255, 0.2);
}



.status-tab {
  background: rgba(255, 255, 255, 0.05);
  color: #a1a1a1;
  border: 1px solid rgba(255, 255, 255, 0.1);
  padding: 12px 20px;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  gap: 8px;
  font-weight: 500;
}

.status-tab:hover {
  background: rgba(255, 255, 255, 0.1);
  color: white;
}

.status-tab.active {
  background: #FFD700;
  color: #0d0d1a;
  border-color: #FFD700;
}

.status-count {
  background: rgba(0, 0, 0, 0.2);
  padding: 2px 8px;
  border-radius: 12px;
  font-size: 0.8rem;
  font-weight: 600;
}

/* Requests Container */
.requests-container {
  margin-top: 24px;
}

.no-requests {
  text-align: center;
  padding: 60px 20px;
  color: #a1a1a1;
}

.no-requests-icon {
  font-size: 4rem;
  margin-bottom: 16px;
}

.no-requests h3 {
  margin: 0 0 8px 0;
  color: #e0e0e0;
}

.no-requests p {
  margin: 0;
  font-size: 1.1rem;
}

/* Requests Grid */
.requests-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
  gap: 24px;
}

/* Request Card */
.request-card {
  background: rgba(255, 255, 255, 0.05);
  border-radius: 16px;
  padding: 24px;
  border: 1px solid rgba(255, 255, 255, 0.1);
  transition: all 0.3s ease;
}

.request-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 12px 40px rgba(0, 0, 0, 0.3);
  border-color: rgba(255, 215, 0, 0.3);
}

/* Status Classes */
.status-pending {
  border-left: 4px solid #ffa500;
}

.status-progress {
  border-left: 4px solid #00bfff;
}

.status-completed {
  border-left: 4px solid #FFD700;
}

.status-rejected {
  border-left: 4px solid #ff4d4d;
}

/* Request Header */
.request-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 20px;
}

.user-info {
  display: flex;
  align-items: center;
  gap: 12px;
}

.user-avatar img {
  width: 48px;
  height: 48px;
  border-radius: 50%;
  border: 2px solid #FFD700;
  object-fit: cover;
}

.user-details h4 {
  margin: 0 0 4px 0;
  color: #e0e0e0;
  font-size: 1.1rem;
}

.user-email {
  margin: 0;
  color: #a1a1a1;
  font-size: 0.9rem;
}

.status-badge {
  padding: 6px 12px;
  border-radius: 20px;
  font-size: 0.8rem;
  font-weight: 600;
  text-transform: uppercase;
}

.status-pending .status-badge {
  background: rgba(255, 165, 0, 0.2);
  color: #ffa500;
}

.status-progress .status-badge {
  background: rgba(0, 191, 255, 0.2);
  color: #00bfff;
}

.status-completed .status-badge {
  background: rgba(255, 215, 0, 0.2);
  color: #FFD700;
}

.status-rejected .status-badge {
  background: rgba(255, 77, 77, 0.2);
  color: #ff4d4d;
}

/* Request Details */
.request-details {
  margin-bottom: 20px;
}

.detail-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 8px 0;
  border-bottom: 1px solid rgba(255, 255, 255, 0.05);
}

.detail-row:last-child {
  border-bottom: none;
}

.detail-label {
  color: #a1a1a1;
  font-size: 0.9rem;
}

.detail-value {
  color: #e0e0e0;
  font-weight: 500;
  text-align: right;
}

.amount {
  color: #FFD700;
  font-weight: 600;
  font-size: 1.1rem;
}

.bank-details {
  max-width: 200px;
  word-break: break-word;
}

/* Request Actions */
.request-actions {
  display: flex;
  gap: 12px;
  margin-bottom: 20px;
  flex-wrap: wrap;
}

.btn-action {
  padding: 8px 16px;
  border: none;
  border-radius: 6px;
  font-size: 0.9rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  gap: 6px;
  flex: 1;
  min-width: 120px;
  justify-content: center;
}

.btn-action:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.btn-approve {
  background: #FFD700;
  color: #0d0d1a;
}

.btn-approve:hover:not(:disabled) {
  background: #DAA520;
  transform: translateY(-2px);
}

.btn-complete {
  background: #00bfff;
  color: white;
}

.btn-complete:hover:not(:disabled) {
  background: #0099cc;
  transform: translateY(-2px);
}

.btn-reject {
  background: #ff4d4d;
  color: white;
}

.btn-reject:hover:not(:disabled) {
  background: #cc0000;
  transform: translateY(-2px);
}

.btn-lock {
  background: #666;
  color: #ccc;
}

/* Status Timeline */
.status-timeline {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.timeline-item {
  display: flex;
  align-items: center;
  gap: 12px;
  opacity: 0.4;
  transition: all 0.3s ease;
}

.timeline-item.active {
  opacity: 1;
}

.timeline-icon {
  font-size: 1.2rem;
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: rgba(255, 255, 255, 0.1);
  border-radius: 50%;
}

.timeline-content {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.timeline-status {
  font-weight: 600;
  color: #e0e0e0;
  font-size: 0.9rem;
}

.timeline-date {
  color: #a1a1a1;
  font-size: 0.8rem;
}

/* Responsive Design */
@media (max-width: 1200px) {
  .requests-grid {
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 20px;
  }
  
  .withdrawal-request-screen {
    padding: 20px;
  }
}

@media (max-width: 992px) {
  .requests-grid {
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 18px;
  }
  
  .page-header {
    padding: 20px;
    margin-bottom: 28px;
  }
  
  .header-content h1 {
    font-size: 2.2rem;
  }
  
  .status-tabs {
    gap: 6px;
    margin-bottom: 20px;
  }
  
  .status-tab {
    padding: 10px 16px;
    font-size: 0.9rem;
  }
}

@media (max-width: 768px) {
  .withdrawal-request-screen {
    padding: 16px;
    max-width: 100%;
  }
  
  .page-header {
    flex-direction: column;
    gap: 16px;
    text-align: center;
    padding: 20px;
    margin-bottom: 24px;
  }
  
  .header-content h1 {
    font-size: 2rem;
    margin-bottom: 6px;
  }
  
  .header-content p {
    font-size: 1rem;
  }
  
  .btn-primary {
    padding: 10px 20px;
    font-size: 0.95rem;
  }
  
  .requests-grid {
    grid-template-columns: 1fr;
    gap: 16px;
  }
  
  .request-card {
    padding: 20px;
  }
  
  .request-header {
    flex-direction: column;
    gap: 16px;
    align-items: flex-start;
  }
  
  .request-actions {
    flex-direction: column;
    gap: 10px;
  }
  
  .btn-action {
    min-width: auto;
    width: 100%;
    justify-content: center;
  }
  
  .status-tabs {
    justify-content: center;
    gap: 4px;
    margin-bottom: 18px;
    flex-wrap: wrap;
  }
  
  .status-tab {
    padding: 8px 14px;
    font-size: 0.85rem;
    min-width: 100px;
    justify-content: center;
  }
  
  .status-count {
    font-size: 0.75rem;
    padding: 1px 6px;
  }
  
  .modal-content {
    width: 95%;
    margin: 10px;
    max-height: 85vh;
  }
  
  .modal-header,
  .modal-body {
    padding: 18px;
  }
  
  .form-actions {
    flex-direction: column;
    gap: 12px;
  }
  
  .btn-secondary,
  .btn-primary {
    width: 100%;
    justify-content: center;
  }
}

@media (max-width: 576px) {
  .withdrawal-request-screen {
    padding: 12px;
  }
  
  .page-header {
    padding: 16px;
    margin-bottom: 20px;
    border-radius: 12px;
  }
  
  .header-content h1 {
    font-size: 1.8rem;
  }
  
  .header-content p {
    font-size: 0.95rem;
  }
  
  .btn-primary {
    padding: 8px 16px;
    font-size: 0.9rem;
  }
  
  .request-card {
    padding: 16px;
    border-radius: 12px;
  }
  
  .request-header {
    gap: 12px;
  }
  
  .user-info {
    gap: 10px;
  }
  
  .user-avatar img {
    width: 40px;
    height: 40px;
  }
  
  .user-details h4 {
    font-size: 1rem;
  }
  
  .user-email {
    font-size: 0.85rem;
  }
  
  .status-badge {
    padding: 4px 10px;
    font-size: 0.75rem;
  }
  
  .detail-row {
    padding: 6px 0;
  }
  
  .detail-label {
    font-size: 0.85rem;
  }
  
  .detail-value {
    font-size: 0.9rem;
  }
  
  .amount {
    font-size: 1rem;
  }
  
  .bank-details {
    max-width: 150px;
  }
  
  .status-timeline {
    gap: 12px;
  }
  
  .timeline-item {
    gap: 10px;
  }
  
  .timeline-icon {
    width: 28px;
    height: 28px;
    font-size: 1rem;
  }
  
  .timeline-status {
    font-size: 0.85rem;
  }
  
  .timeline-date {
    font-size: 0.75rem;
  }
  
  .status-tabs {
    gap: 3px;
    margin-bottom: 16px;
  }
  
  .status-tab {
    padding: 6px 12px;
    font-size: 0.8rem;
    min-width: 90px;
  }
  
  .status-count {
    font-size: 0.7rem;
    padding: 1px 5px;
  }
  
  .modal-content {
    width: 98%;
    margin: 5px;
    border-radius: 12px;
  }
  
  .modal-header {
    padding: 16px;
  }
  
  .modal-header h3 {
    font-size: 1.3rem;
  }
  
  .modal-body {
    padding: 16px;
  }
  
  .request-form {
    gap: 16px;
  }
  
  .form-group {
    gap: 6px;
  }
  
  .form-group label {
    font-size: 0.9rem;
  }
  
  .form-control {
    padding: 10px 14px;
    font-size: 0.95rem;
  }
  
  .no-requests {
    padding: 40px 16px;
  }
  
  .no-requests-icon {
    font-size: 3rem;
    margin-bottom: 12px;
  }
  
  .no-requests h3 {
    font-size: 1.2rem;
  }
  
  .no-requests p {
    font-size: 1rem;
  }
}

@media (max-width: 480px) {
  .withdrawal-request-screen {
    padding: 8px;
  }
  
  .page-header {
    padding: 14px;
    margin-bottom: 18px;
    border-radius: 10px;
  }
  
  .header-content h1 {
    font-size: 1.6rem;
  }
  
  .header-content p {
    font-size: 0.9rem;
  }
  
  .btn-primary {
    padding: 6px 14px;
    font-size: 0.85rem;
  }
  
  .request-card {
    padding: 14px;
    border-radius: 10px;
  }
  
  .user-avatar img {
    width: 36px;
    height: 36px;
  }
  
  .user-details h4 {
    font-size: 0.95rem;
  }
  
  .user-email {
    font-size: 0.8rem;
  }
  
  .status-badge {
    padding: 3px 8px;
    font-size: 0.7rem;
  }
  
  .detail-row {
    padding: 5px 0;
  }
  
  .detail-label {
    font-size: 0.8rem;
  }
  
  .detail-value {
    font-size: 0.85rem;
  }
  
  .amount {
    font-size: 0.95rem;
  }
  
  .bank-details {
    max-width: 120px;
  }
  
  .btn-action {
    padding: 6px 12px;
    font-size: 0.8rem;
    min-width: auto;
  }
  
  .status-tabs {
    gap: 2px;
    margin-bottom: 14px;
  }
  
  .status-tab {
    padding: 5px 10px;
    font-size: 0.75rem;
    min-width: 80px;
  }
  
  .status-count {
    font-size: 0.65rem;
    padding: 1px 4px;
  }
  
  .modal-content {
    width: 99%;
    margin: 2px;
    border-radius: 10px;
  }
  
  .modal-header {
    padding: 14px;
  }
  
  .modal-header h3 {
    font-size: 1.2rem;
  }
  
  .modal-body {
    padding: 14px;
  }
  
  .request-form {
    gap: 14px;
  }
  
  .form-group {
    gap: 5px;
  }
  
  .form-group label {
    font-size: 0.85rem;
  }
  
  .form-control {
    padding: 8px 12px;
    font-size: 0.9rem;
  }
  
  .no-requests {
    padding: 30px 12px;
  }
  
  .no-requests-icon {
    font-size: 2.5rem;
    margin-bottom: 10px;
  }
  
  .no-requests h3 {
    font-size: 1.1rem;
  }
  
  .no-requests p {
    font-size: 0.9rem;
  }
}

@media (max-width: 360px) {
  .withdrawal-request-screen {
    padding: 6px;
  }
  
  .page-header {
    padding: 12px;
    margin-bottom: 16px;
    border-radius: 8px;
  }
  
  .header-content h1 {
    font-size: 1.4rem;
  }
  
  .header-content p {
    font-size: 0.85rem;
  }
  
  .btn-primary {
    padding: 5px 12px;
    font-size: 0.8rem;
  }
  
  .request-card {
    padding: 12px;
    border-radius: 8px;
  }
  
  .user-avatar img {
    width: 32px;
    height: 32px;
  }
  
  .user-details h4 {
    font-size: 0.9rem;
  }
  
  .user-email {
    font-size: 0.75rem;
  }
  
  .status-badge {
    padding: 2px 6px;
    font-size: 0.65rem;
  }
  
  .detail-row {
    padding: 4px 0;
  }
  
  .detail-label {
    font-size: 0.75rem;
  }
  
  .detail-value {
    font-size: 0.8rem;
  }
  
  .amount {
    font-size: 0.9rem;
  }
  
  .bank-details {
    max-width: 100px;
  }
  
  .btn-action {
    padding: 5px 10px;
    font-size: 0.75rem;
  }
  
  .status-tabs {
    gap: 1px;
    margin-bottom: 12px;
  }
  
  .status-tab {
    padding: 4px 8px;
    font-size: 0.7rem;
    min-width: 70px;
  }
  
  .status-count {
    font-size: 0.6rem;
    padding: 1px 3px;
  }
  
  .modal-content {
    width: 100%;
    margin: 0;
    border-radius: 8px;
  }
  
  .modal-header {
    padding: 12px;
  }
  
  .modal-header h3 {
    font-size: 1.1rem;
  }
  
  .modal-body {
    padding: 12px;
  }
  
  .request-form {
    gap: 12px;
  }
  
  .form-group {
    gap: 4px;
  }
  
  .form-group label {
    font-size: 0.8rem;
  }
  
  .form-control {
    padding: 6px 10px;
    font-size: 0.85rem;
  }
  
  .no-requests {
    padding: 25px 10px;
  }
  
  .no-requests-icon {
    font-size: 2rem;
    margin-bottom: 8px;
  }
  
  .no-requests h3 {
    font-size: 1rem;
  }
  
  .no-requests p {
    font-size: 0.85rem;
  }
}

/* Landscape Mobile Optimization */
@media (max-width: 768px) and (orientation: landscape) {
  .withdrawal-request-screen {
    padding: 12px;
  }
  
  .page-header {
    flex-direction: row;
    text-align: left;
    gap: 12px;
    padding: 16px;
  }
  
  .header-content h1 {
    font-size: 1.8rem;
  }
  
  .requests-grid {
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  }
  
  .modal-content {
    max-height: 80vh;
  }
}

/* Profile Initials Fallback */
.profile-initials-fallback {
  width: 45px;
  height: 45px;
  border-radius: 50%;
  background: linear-gradient(135deg, #FFD700, #DAA520);
  color: #000;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: bold;
  font-size: 14px;
  border: 3px solid #FFD700;
  box-shadow: 0 2px 8px rgba(255, 215, 0, 0.3);
}

/* Touch Device Optimizations */
@media (hover: none) and (pointer: coarse) {
  .btn-primary:hover,
  .btn-secondary:hover,
  .btn-action:hover,
  .status-tab:hover {
    transform: none;
  }
  
  .btn-primary:active,
  .btn-secondary:active,
  .btn-action:active,
  .status-tab:active {
    transform: scale(0.95);
  }
  
  .request-card:hover {
    transform: none;
  }
  
  .request-card:active {
    transform: scale(0.98);
  }
}

/* High DPI Displays */
@media (-webkit-min-device-pixel-ratio: 2), (min-resolution: 192dpi) {
  .user-avatar img,
  .timeline-icon {
    image-rendering: -webkit-optimize-contrast;
    image-rendering: crisp-edges;
  }
}

/* Print Styles */
@media print {
  .withdrawal-request-screen {
    background: white;
    color: black;
  }
  
  .page-header,
  .status-tabs,
  .request-actions,
  .modal-overlay {
    display: none;
  }
  
  .request-card {
    border: 1px solid #ccc;
    break-inside: avoid;
  }
}

/* Accessibility Improvements */
@media (prefers-reduced-motion: reduce) {
  .btn-primary,
  .btn-secondary,
  .btn-action,
  .status-tab,
  .request-card,
  .modal-content {
    transition: none;
  }
  
  .btn-primary:hover,
  .btn-secondary:hover,
  .btn-action:hover,
  .status-tab:hover,
  .request-card:hover {
    transform: none;
  }
}

/* Dark Mode Support */
@media (prefers-color-scheme: dark) {
  .withdrawal-request-screen {
    background: linear-gradient(135deg, #0d0d1a 0%, #101022 100%);
  }
}
</style>
