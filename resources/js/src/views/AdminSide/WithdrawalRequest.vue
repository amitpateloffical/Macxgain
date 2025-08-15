<template>
  <div class="withdrawal-request-screen">
    <!-- Header Section -->
    <div class="page-header">
      <div class="header-content">
        <h1 class="page-title">💳 Withdrawal Request</h1>
        <p class="page-subtitle">Manage and process withdrawal requests from users</p>
      </div>
      <div class="header-actions">
        <button class="btn-primary" @click="showNewRequestForm = true">
          <i class="fa-solid fa-plus"></i> New Request
        </button>
      </div>
    </div>

    <!-- New Request Form Modal -->
    <div v-if="showNewRequestForm" class="modal-overlay" @click="closeModal">
      <div class="modal-content" @click.stop>
        <div class="modal-header">
          <h3>Create New Withdrawal Request</h3>
          <button class="close-btn" @click="closeModal">&times;</button>
        </div>
        <div class="modal-body">
          <form @submit.prevent="createWithdrawalRequest" class="request-form">
            <div class="form-group">
              <label for="userSelect">Select User</label>
              <select id="userSelect" v-model="newRequest.user_id" required class="form-control">
                <option value="">Choose a user...</option>
                <option v-for="user in users" :key="user.id" :value="user.id">
                  {{ user.name }} ({{ user.email }})
                </option>
              </select>
            </div>
            
            <div class="form-group">
              <label for="amount">Amount (₹)</label>
              <input 
                type="number" 
                id="amount" 
                v-model="newRequest.amount" 
                required 
                min="100"
                step="100"
                class="form-control"
                placeholder="Enter amount"
              />
            </div>
            
            <div class="form-group">
              <label for="bankDetails">Bank Details</label>
              <textarea 
                id="bankDetails" 
                v-model="newRequest.bank_details" 
                required 
                class="form-control"
                rows="3"
                placeholder="Account number, IFSC, Bank name..."
              ></textarea>
            </div>
            
            <div class="form-group">
              <label for="reason">Reason</label>
              <textarea 
                id="reason" 
                v-model="newRequest.reason" 
                class="form-control"
                rows="2"
                placeholder="Optional reason for withdrawal"
              ></textarea>
            </div>
            
            <div class="form-actions">
              <button type="button" class="btn-secondary" @click="closeModal">Cancel</button>
              <button type="submit" class="btn-primary">Create Request</button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Status Filter Tabs -->
    <div class="status-tabs">
      <button 
        v-for="status in statusOptions" 
        :key="status.value"
        :class="['status-tab', { active: activeStatus === status.value }]"
        @click="activeStatus = status.value"
      >
        {{ status.label }}
        <span class="status-count">{{ getStatusCount(status.value) }}</span>
      </button>
    </div>

    <!-- Requests List -->
    <div class="requests-container">
      <div v-if="filteredRequests.length === 0" class="no-requests">
        <div class="no-requests-icon">📭</div>
        <h3>No {{ activeStatus === 'all' ? '' : activeStatus }} requests found</h3>
        <p>Create a new withdrawal request to get started</p>
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
                <img :src="getUserAvatar(request.user_id)" :alt="getUserName(request.user_id)" />
              </div>
              <div class="user-details">
                <h4 class="user-name">{{ getUserName(request.user_id) }}</h4>
                <p class="user-email">{{ getUserEmail(request.user_id) }}</p>
              </div>
            </div>
            <div class="request-status" :class="getStatusClass(request.status)">
              <span class="status-badge">{{ getStatusLabel(request.status) }}</span>
            </div>
          </div>

          <!-- Request Details -->
          <div class="request-details">
            <div class="detail-row">
              <span class="detail-label">Amount:</span>
              <span class="detail-value amount">₹{{ request.amount.toLocaleString() }}</span>
            </div>
            <div class="detail-row">
              <span class="detail-label">Requested:</span>
              <span class="detail-value">{{ formatDate(request.created_at) }}</span>
            </div>
            <div class="detail-row" v-if="request.bank_details">
              <span class="detail-label">Bank Details:</span>
              <span class="detail-value bank-details">{{ request.bank_details }}</span>
            </div>
            <div class="detail-row" v-if="request.reason">
              <span class="detail-label">Reason:</span>
              <span class="detail-value">{{ request.reason }}</span>
            </div>
          </div>

          <!-- Action Buttons -->
          <div class="request-actions">
            <button 
              v-if="request.status === 'pending'"
              class="btn-action btn-approve"
              @click="updateStatus(request.id, 'in_progress')"
              :disabled="request.status !== 'pending'"
            >
              <i class="fa-solid fa-play"></i> Initiate Withdrawal
            </button>
            
            <button 
              v-if="request.status === 'in_progress'"
              class="btn-action btn-complete"
              @click="updateStatus(request.id, 'completed')"
              :disabled="request.status !== 'in_progress'"
            >
              <i class="fa-solid fa-check"></i> Mark Complete
            </button>
            
            <button 
              v-if="request.status === 'pending'"
              class="btn-action btn-reject"
              @click="updateStatus(request.id, 'rejected')"
              :disabled="request.status !== 'pending'"
            >
              <i class="fa-solid fa-times"></i> Reject
            </button>
            
            <button 
              v-if="request.status === 'completed' || request.status === 'rejected'"
              class="btn-action btn-lock"
              disabled
            >
              <i class="fa-solid fa-lock"></i> Locked
            </button>
          </div>

          <!-- Status Timeline -->
          <div class="status-timeline">
            <div class="timeline-item" :class="{ active: request.status === 'pending' }">
              <div class="timeline-icon">📝</div>
              <div class="timeline-content">
                <span class="timeline-status">Request Submitted</span>
                <span class="timeline-date">{{ formatDate(request.created_at) }}</span>
              </div>
            </div>
            
            <div class="timeline-item" :class="{ active: request.status === 'in_progress' || request.status === 'completed' }">
              <div class="timeline-icon">🔄</div>
              <div class="timeline-content">
                <span class="timeline-status">Withdrawal Initiated</span>
                <span class="timeline-date" v-if="request.status_updated_at">{{ formatDate(request.status_updated_at) }}</span>
              </div>
            </div>
            
            <div class="timeline-item" :class="{ active: request.status === 'completed' }">
              <div class="timeline-icon">✅</div>
              <div class="timeline-content">
                <span class="timeline-status">Completed</span>
                <span class="timeline-date" v-if="request.status_updated_at">{{ formatDate(request.status_updated_at) }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()

// Reactive Data
const showNewRequestForm = ref(false)
const activeStatus = ref('all')
const requests = ref([])
const users = ref([])

// New Request Form Data
const newRequest = ref({
  user_id: '',
  amount: '',
  bank_details: '',
  reason: ''
})

// Status Options
const statusOptions = [
  { value: 'all', label: 'All Requests' },
  { value: 'pending', label: 'Pending' },
  { value: 'in_progress', label: 'In Progress' },
  { value: 'completed', label: 'Completed' },
  { value: 'rejected', label: 'Rejected' }
]

// Computed Properties
const filteredRequests = computed(() => {
  if (activeStatus.value === 'all') {
    return requests.value
  }
  return requests.value.filter(request => request.status === activeStatus.value)
})

// Methods
const closeModal = () => {
  showNewRequestForm.value = false
  resetForm()
}

const resetForm = () => {
  newRequest.value = {
    user_id: '',
    amount: '',
    bank_details: '',
    reason: ''
  }
}

const createWithdrawalRequest = async () => {
  try {
    // Simulate API call
    const request = {
      id: Date.now(),
      user_id: newRequest.value.user_id,
      amount: parseFloat(newRequest.value.amount),
      bank_details: newRequest.value.bank_details,
      reason: newRequest.value.reason,
      status: 'pending',
      created_at: new Date().toISOString(),
      status_updated_at: null
    }
    
    requests.value.unshift(request)
    closeModal()
    
    // Show success message
    alert('Withdrawal request created successfully!')
  } catch (error) {
    console.error('Error creating request:', error)
    alert('Error creating withdrawal request')
  }
}

const updateStatus = async (requestId, newStatus) => {
  try {
    const request = requests.value.find(r => r.id === requestId)
    if (request) {
      request.status = newStatus
      request.status_updated_at = new Date().toISOString()
      
      // Show success message
      const statusLabel = getStatusLabel(newStatus)
      alert(`Request status updated to: ${statusLabel}`)
    }
  } catch (error) {
    console.error('Error updating status:', error)
    alert('Error updating request status')
  }
}

const getStatusCount = (status) => {
  if (status === 'all') {
    return requests.value.length
  }
  return requests.value.filter(request => request.status === status).length
}

const getStatusClass = (status) => {
  const statusClasses = {
    pending: 'status-pending',
    in_progress: 'status-progress',
    completed: 'status-completed',
    rejected: 'status-rejected'
  }
  return statusClasses[status] || 'status-pending'
}

const getStatusLabel = (status) => {
  const statusLabels = {
    pending: 'Pending',
    in_progress: 'In Progress',
    completed: 'Completed',
    rejected: 'Rejected'
  }
  return statusLabels[status] || 'Pending'
}

const getUserName = (userId) => {
  const user = users.value.find(u => u.id === userId)
  return user ? user.name : 'Unknown User'
}

const getUserEmail = (userId) => {
  const user = users.value.find(u => u.id === userId)
  return user ? user.email : 'unknown@email.com'
}

const getUserAvatar = (userId) => {
  const user = users.value.find(u => u.id === userId)
  return user ? user.avatar : '../assest/img/tableprofileimg.png'
}

const formatDate = (dateString) => {
  if (!dateString) return 'N/A'
  return new Date(dateString).toLocaleDateString('en-IN', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

// Load Sample Data
onMounted(() => {
  // Sample Users
  users.value = [
    { id: 1, name: 'Kamlesh Patel', email: 'kamlesh@example.com', avatar: '../assest/img/tableprofileimg.png' },
    { id: 2, name: 'Rahul Sharma', email: 'rahul@example.com', avatar: '../assest/img/tableprofileimg.png' },
    { id: 3, name: 'Priya Singh', email: 'priya@example.com', avatar: '../assest/img/tableprofileimg.png' }
  ]
  
  // Sample Requests
  requests.value = [
    {
      id: 1,
      user_id: 1,
      amount: 5000,
      bank_details: 'HDFC Bank - 1234567890 - HDFC0001234',
      reason: 'Emergency funds needed',
      status: 'pending',
      created_at: '2024-01-15T10:30:00Z',
      status_updated_at: null
    },
    {
      id: 2,
      user_id: 2,
      amount: 10000,
      bank_details: 'SBI Bank - 0987654321 - SBIN0001234',
      reason: 'Business investment',
      status: 'in_progress',
      created_at: '2024-01-14T15:45:00Z',
      status_updated_at: '2024-01-15T09:00:00Z'
    },
    {
      id: 3,
      user_id: 3,
      amount: 7500,
      bank_details: 'ICICI Bank - 1122334455 - ICIC0001234',
      reason: 'Medical expenses',
      status: 'completed',
      created_at: '2024-01-13T11:20:00Z',
      status_updated_at: '2024-01-15T14:30:00Z'
    }
  ]
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
  border: 1px solid rgba(0, 255, 128, 0.2);
}

.header-content h1 {
  font-size: 2.5rem;
  font-weight: bold;
  color: #00ff80;
  margin: 0 0 8px 0;
}

.header-content p {
  font-size: 1.1rem;
  color: #a1a1a1;
  margin: 0;
}

.btn-primary {
  background: linear-gradient(135deg, #00ff80, #00cc66);
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
  box-shadow: 0 8px 25px rgba(0, 255, 128, 0.3);
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
  border: 1px solid rgba(0, 255, 128, 0.3);
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
  color: #00ff80;
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
  border-color: #00ff80;
  box-shadow: 0 0 0 3px rgba(0, 255, 128, 0.1);
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

/* Status Tabs */
.status-tabs {
  display: flex;
  gap: 8px;
  margin-bottom: 24px;
  flex-wrap: wrap;
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
  background: #00ff80;
  color: #0d0d1a;
  border-color: #00ff80;
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
  border-color: rgba(0, 255, 128, 0.3);
}

/* Status Classes */
.status-pending {
  border-left: 4px solid #ffa500;
}

.status-progress {
  border-left: 4px solid #00bfff;
}

.status-completed {
  border-left: 4px solid #00ff80;
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
  border: 2px solid #00ff80;
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
  background: rgba(0, 255, 128, 0.2);
  color: #00ff80;
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
  color: #00ff80;
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
  background: #00ff80;
  color: #0d0d1a;
}

.btn-approve:hover:not(:disabled) {
  background: #00cc66;
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
