<template>
  <div class="register-request-screen">
    <!-- Header -->
    <div class="page-header">
      <div class="header-content">
        <div class="header-left">
          <h1 class="page-title">
            <i class="fa-solid fa-user-plus"></i>
            Register Request
          </h1>
          <p class="page-subtitle">
            Review and approve new user registration requests
          </p>
        </div>
        <div class="header-right">
          <button class="btn-refresh" @click="fetchRegisterRequests" :disabled="loading">
            <i class="fa-solid fa-rotate" :class="{ 'fa-spin': loading }"></i>
            Refresh
          </button>
        </div>
      </div>
    </div>

    <!-- Statistics Cards -->
    <div class="stats-cards">
      <div class="stat-card">
        <div class="stat-icon pending">📝</div>
        <div class="stat-content">
          <h3 class="stat-number">{{ pendingRequests }}</h3>
          <p class="stat-label">Pending Requests</p>
        </div>
      </div>
      
      <div class="stat-card">
        <div class="stat-icon approved">✅</div>
        <div class="stat-content">
          <h3 class="stat-number">{{ approvedRequests }}</h3>
          <p class="stat-label">Approved Today</p>
        </div>
      </div>
      
      <div class="stat-card">
        <div class="stat-icon rejected">❌</div>
        <div class="stat-content">
          <h3 class="stat-number">{{ rejectedRequests }}</h3>
          <p class="stat-label">Rejected Today</p>
        </div>
      </div>
    </div>

    <!-- Filters and Search -->
    <div class="filters-section">
      <div class="search-box">
        <i class="fa-solid fa-search"></i>
        <input 
          type="text" 
          v-model="searchQuery" 
          placeholder="Search by name, email, or phone..."
          class="search-input"
        />
      </div>
      
      <div class="filter-options">
        <select v-model="statusFilter" class="filter-select">
          <option value="all">All Status</option>
          <option value="pending">Pending</option>
          <option value="approved">Approved</option>
          <option value="rejected">Rejected</option>
        </select>
        
        <select v-model="dateFilter" class="filter-select">
          <option value="all">All Time</option>
          <option value="today">Today</option>
          <option value="week">This Week</option>
          <option value="month">This Month</option>
        </select>
        
        <select v-model="sortBy" class="filter-select">
          <option value="latest">🕐 Latest First</option>
          <option value="oldest">🕐 Oldest First</option>
          <option value="name">🔤 Name A-Z</option>
          <option value="email">📧 Email A-Z</option>
        </select>
      </div>
      
      <div class="results-info">
        <span class="results-count">
          Showing {{ filteredRequests.length }} of {{ requests.length }} requests
        </span>
      </div>
    </div>

    <!-- Requests List -->
    <div class="requests-container">
      <div v-if="loading" class="loading-state">
        <i class="fa-solid fa-spinner fa-spin"></i>
        <p>Loading registration requests...</p>
      </div>
      
      <div v-else-if="error" class="error-state">
        <i class="fa-solid fa-exclamation-triangle"></i>
        <p>{{ error }}</p>
        <button class="btn-retry" @click="fetchRegisterRequests">Retry</button>
      </div>
      
      <div v-else-if="filteredRequests.length === 0" class="empty-state">
        <i class="fa-solid fa-inbox"></i>
        <p>No registration requests found</p>
      </div>
      
      <div v-else class="requests-list">
        <div 
          v-for="request in paginatedRequests" 
          :key="request.id" 
          class="request-card"
          :class="getStatusClass(request.status)"
        >
          <div class="request-header">
            <div class="user-info">
              <div class="user-avatar">
                <img 
                  :src="request.profile_image || '../assest/img/tableprofileimg.png'"
                  :alt="request.name"
                />
              </div>
              <div class="user-details">
                <h4 class="user-name">{{ request.name }}</h4>
                <p class="user-email">{{ request.email }}</p>
                <p class="user-phone">{{ request.phone }}</p>
              </div>
            </div>
            
            <div class="request-status">
              <span class="status-badge" :class="getStatusClass(request.status)">
                {{ getStatusText(request.status) }}
              </span>
            </div>
          </div>
          
          <div class="request-body">
            <div class="request-meta">
              <div class="meta-item">
                <i class="fa-solid fa-calendar"></i>
                <span>Requested: {{ formatDate(request.created_at) }}</span>
              </div>
              <div class="meta-item">
                <i class="fa-solid fa-clock"></i>
                <span>{{ getTimeAgo(request.created_at) }}</span>
              </div>
            </div>
            
            <div class="request-actions" v-if="request.status === 'pending'">
              <button 
                class="btn-approve" 
                @click="approveRequest(request)"
                :disabled="processingRequest === request.id"
              >
                <i class="fa-solid fa-check"></i>
                <span v-if="processingRequest === request.id">Approving...</span>
                <span v-else>Approve</span>
              </button>
              
              <button 
                class="btn-reject" 
                @click="rejectRequest(request)"
                :disabled="processingRequest === request.id"
              >
                <i class="fa-solid fa-times"></i>
                <span v-if="processingRequest === request.id">Rejecting...</span>
                <span v-else>Reject</span>
              </button>
            </div>
            
            <div class="request-actions" v-else>
              <div class="processed-info">
                <span v-if="request.status === 'approved'" class="approved-info">
                  <i class="fa-solid fa-check-circle"></i>
                  Approved by {{ request.approved_by || 'Admin' }} on {{ formatDate(request.updated_at) }}
                </span>
                <span v-else-if="request.status === 'rejected'" class="rejected-info">
                  <i class="fa-solid fa-times-circle"></i>
                  Rejected by {{ request.rejected_by || 'Admin' }} on {{ formatDate(request.updated_at) }}
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Pagination -->
      <div v-if="totalPages > 1" class="pagination">
        <button 
          class="btn-page" 
          :disabled="currentPage === 1"
          @click="currentPage = 1"
        >
          <i class="fa-solid fa-angles-left"></i>
        </button>
        <button 
          class="btn-page" 
          :disabled="currentPage === 1"
          @click="currentPage--"
        >
          <i class="fa-solid fa-angle-left"></i>
        </button>
        
        <span class="page-info">
          Page {{ currentPage }} of {{ totalPages }}
        </span>
        
        <button 
          class="btn-page" 
          :disabled="currentPage === totalPages"
          @click="currentPage++"
        >
          <i class="fa-solid fa-angle-right"></i>
        </button>
        <button 
          class="btn-page" 
          :disabled="currentPage === totalPages"
          @click="currentPage = totalPages"
        >
          <i class="fa-solid fa-angles-right"></i>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'

// Reactive Data
const loading = ref(false)
const error = ref(null)
const requests = ref([])
const searchQuery = ref('')
const statusFilter = ref('all')
const dateFilter = ref('all')
const sortBy = ref('latest')
const currentPage = ref(1)
const requestsPerPage = ref(10)
const processingRequest = ref(null)

// API Base URL
const API_BASE = `${window.location.origin}/api`

// Computed Properties
const filteredRequests = computed(() => {
  let filtered = requests.value

  // Search filter
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase()
    filtered = filtered.filter(request => 
      request.name.toLowerCase().includes(query) ||
      request.email.toLowerCase().includes(query) ||
      request.phone.includes(query)
    )
  }

  // Status filter
  if (statusFilter.value !== 'all') {
    filtered = filtered.filter(request => request.status === statusFilter.value)
  }

  // Date filter
  if (dateFilter.value !== 'all') {
    const now = new Date()
    const today = new Date(now.getFullYear(), now.getMonth(), now.getDate())
    
    filtered = filtered.filter(request => {
      const requestDate = new Date(request.created_at)
      
      switch (dateFilter.value) {
        case 'today':
          return requestDate >= today
        case 'week':
          const weekAgo = new Date(today.getTime() - 7 * 24 * 60 * 60 * 1000)
          return requestDate >= weekAgo
        case 'month':
          const monthAgo = new Date(today.getFullYear(), today.getMonth() - 1, today.getDate())
          return requestDate >= monthAgo
        default:
          return true
      }
    })
  }

  // Sort the filtered results
  filtered.sort((a, b) => {
    switch (sortBy.value) {
      case 'latest':
        return new Date(b.created_at) - new Date(a.created_at)
      case 'oldest':
        return new Date(a.created_at) - new Date(b.created_at)
      case 'name':
        return a.name.localeCompare(b.name)
      case 'email':
        return a.email.localeCompare(b.email)
      default:
        return new Date(b.created_at) - new Date(a.created_at)
    }
  })

  return filtered
})

const paginatedRequests = computed(() => {
  const start = (currentPage.value - 1) * requestsPerPage.value
  const end = start + requestsPerPage.value
  return filteredRequests.value.slice(start, end)
})

const totalPages = computed(() => {
  return Math.ceil(filteredRequests.value.length / requestsPerPage.value)
})

const pendingRequests = computed(() => {
  return requests.value.filter(r => r.status === 'pending').length
})

const approvedRequests = computed(() => {
  const today = new Date()
  const todayStart = new Date(today.getFullYear(), today.getMonth(), today.getDate())
  return requests.value.filter(r => 
    r.status === 'approved' && new Date(r.updated_at) >= todayStart
  ).length
})

const rejectedRequests = computed(() => {
  const today = new Date()
  const todayStart = new Date(today.getFullYear(), today.getMonth(), today.getDate())
  return requests.value.filter(r => 
    r.status === 'rejected' && new Date(r.updated_at) >= todayStart
  ).length
})

// Watchers - Reset page when filters change
watch([searchQuery, statusFilter, dateFilter, sortBy], () => {
  currentPage.value = 1
})

// Methods
const fetchRegisterRequests = async () => {
  try {
    loading.value = true
    error.value = null
    
    const token = localStorage.getItem('access_token')
    if (!token) {
      throw new Error('No access token found. Please login again.')
    }

    const response = await fetch(`${API_BASE}/register-requests`, {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      }
    })

    if (response.status === 401) {
      throw new Error('Authentication failed. Please login again.')
    }

    if (!response.ok) {
      const errorData = await response.json()
      throw new Error(errorData.message || 'Failed to fetch requests')
    }

    const data = await response.json()
    
    if (data.success) {
      requests.value = data.data
    } else {
      throw new Error(data.message || 'Failed to fetch requests')
    }
    
  } catch (error) {
    console.error('Error fetching requests:', error)
    error.value = error.message
  } finally {
    loading.value = false
  }
}

const approveRequest = async (request) => {
  try {
    processingRequest.value = request.id
    
    const token = localStorage.getItem('access_token')
    if (!token) {
      throw new Error('No access token found. Please login again.')
    }

    const response = await fetch(`${API_BASE}/register-requests/${request.id}/approve`, {
      method: 'PATCH',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      }
    })

    if (response.status === 401) {
      throw new Error('Authentication failed. Please login again.')
    }

    if (!response.ok) {
      const errorData = await response.json()
      throw new Error(errorData.message || 'Failed to approve request')
    }

    const data = await response.json()
    
    if (data.success) {
      // Update local state
      const requestIndex = requests.value.findIndex(r => r.id === request.id)
      if (requestIndex !== -1) {
        requests.value[requestIndex].status = 'approved'
        requests.value[requestIndex].updated_at = new Date().toISOString()
      }
      
      alert('User registration approved successfully!')
    } else {
      throw new Error(data.message || 'Failed to approve request')
    }
    
  } catch (error) {
    console.error('Error approving request:', error)
    alert(`Error approving request: ${error.message}`)
  } finally {
    processingRequest.value = null
  }
}

const rejectRequest = async (request) => {
  try {
    processingRequest.value = request.id
    
    const token = localStorage.getItem('access_token')
    if (!token) {
      throw new Error('No access token found. Please login again.')
    }

    const response = await fetch(`${API_BASE}/register-requests/${request.id}/reject`, {
      method: 'PATCH',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      }
    })

    if (response.status === 401) {
      throw new Error('Authentication failed. Please login again.')
    }

    if (!response.ok) {
      const errorData = await response.json()
      throw new Error(errorData.message || 'Failed to reject request')
    }

    const data = await response.json()
    
    if (data.success) {
      // Update local state
      const requestIndex = requests.value.findIndex(r => r.id === request.id)
      if (requestIndex !== -1) {
        requests.value[requestIndex].status = 'rejected'
        requests.value[requestIndex].updated_at = new Date().toISOString()
      }
      
      alert('User registration rejected successfully!')
    } else {
      throw new Error(data.message || 'Failed to reject request')
    }
    
  } catch (error) {
    console.error('Error rejecting request:', error)
    alert(`Error rejecting request: ${error.message}`)
  } finally {
    processingRequest.value = null
  }
}

const getStatusClass = (status) => {
  const statusClasses = {
    'pending': 'status-pending',
    'approved': 'status-approved',
    'rejected': 'status-rejected'
  }
  return statusClasses[status] || 'status-pending'
}

const getStatusText = (status) => {
  const statusTexts = {
    'pending': 'Pending',
    'approved': 'Approved',
    'rejected': 'Rejected'
  }
  return statusTexts[status] || 'Pending'
}

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}

const getTimeAgo = (dateString) => {
  const now = new Date()
  const requestDate = new Date(dateString)
  const diffInMinutes = Math.floor((now - requestDate) / (1000 * 60))
  
  if (diffInMinutes < 1) return 'Just now'
  if (diffInMinutes < 60) return `${diffInMinutes} minutes ago`
  if (diffInMinutes < 1440) return `${Math.floor(diffInMinutes / 60)} hours ago`
  return `${Math.floor(diffInMinutes / 1440)} days ago`
}

// Load data on mount
onMounted(() => {
  fetchRegisterRequests()
})
</script>

<style scoped>
/* Main Layout */
.register-request-screen {
  background-color: #0d0d1a;
  color: white;
  min-height: 100vh;
  padding: 20px;
}

/* Header */
.page-header {
  margin-bottom: 30px;
}

.header-content {
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 20px;
}

.header-left h1 {
  font-size: 2.5rem;
  font-weight: 700;
  color: #00ff80;
  margin: 0 0 10px 0;
  display: flex;
  align-items: center;
  gap: 15px;
}

.header-left h1 i {
  font-size: 2rem;
}

.page-subtitle {
  font-size: 1.1rem;
  color: #a1a1a1;
  margin: 0;
}

.btn-refresh {
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

.btn-refresh:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(0, 255, 128, 0.3);
}

.btn-refresh:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

/* Statistics Cards */
.stats-cards {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 20px;
  margin-bottom: 30px;
}

.stat-card {
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 12px;
  padding: 24px;
  display: flex;
  align-items: center;
  gap: 20px;
  transition: all 0.3s ease;
}

.stat-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
}

.stat-icon {
  font-size: 2.5rem;
  width: 60px;
  height: 60px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
}

.stat-icon.pending {
  background: rgba(255, 193, 7, 0.2);
  color: #ffc107;
}

.stat-icon.approved {
  background: rgba(40, 167, 69, 0.2);
  color: #28a745;
}

.stat-icon.rejected {
  background: rgba(220, 53, 69, 0.2);
  color: #dc3545;
}

.stat-number {
  font-size: 2rem;
  font-weight: 700;
  margin: 0 0 5px 0;
  color: white;
}

.stat-label {
  font-size: 0.9rem;
  color: #a1a1a1;
  margin: 0;
}

/* Filters Section */
.filters-section {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 30px;
  flex-wrap: wrap;
  gap: 20px;
}

.search-box {
  position: relative;
  flex: 1;
  max-width: 400px;
}

.search-box i {
  position: absolute;
  left: 16px;
  top: 50%;
  transform: translateY(-50%);
  color: #a1a1a1;
}

.search-input {
  width: 100%;
  padding: 12px 16px 12px 45px;
  border: 2px solid rgba(255, 255, 255, 0.1);
  border-radius: 8px;
  background: rgba(255, 255, 255, 0.05);
  color: white;
  font-size: 1rem;
}

.search-input:focus {
  outline: none;
  border-color: #00ff80;
}

.filter-options {
  display: flex;
  gap: 15px;
}

.filter-select {
  padding: 10px 16px;
  border: 2px solid rgba(255, 255, 255, 0.1);
  border-radius: 8px;
  background: rgba(255, 255, 255, 0.05);
  color: white;
  font-size: 0.9rem;
  cursor: pointer;
}

.filter-select:focus {
  outline: none;
  border-color: #00ff80;
}

.results-info {
  display: flex;
  align-items: center;
  justify-content: flex-end;
}

.results-count {
  color: #a1a1a1;
  font-size: 0.9rem;
  padding: 8px 16px;
  background: rgba(255, 255, 255, 0.05);
  border-radius: 6px;
  border: 1px solid rgba(255, 255, 255, 0.1);
}

/* Requests List */
.requests-container {
  background: rgba(255, 255, 255, 0.02);
  border-radius: 12px;
  padding: 20px;
  border: 1px solid rgba(255, 255, 255, 0.05);
}

.loading-state,
.error-state,
.empty-state {
  text-align: center;
  padding: 60px 20px;
  color: #a1a1a1;
}

.loading-state i,
.error-state i,
.empty-state i {
  font-size: 3rem;
  margin-bottom: 20px;
  display: block;
}

.btn-retry {
  background: linear-gradient(135deg, #00ff80, #00cc66);
  color: #0d0d1a;
  border: none;
  padding: 10px 20px;
  border-radius: 6px;
  font-size: 0.9rem;
  cursor: pointer;
  margin-top: 15px;
}

.requests-list {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.request-card {
  background: rgba(255, 255, 255, 0.03);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 12px;
  padding: 24px;
  transition: all 0.3s ease;
}

.request-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
}

.request-card.status-approved {
  border-left: 4px solid #28a745;
}

.request-card.status-rejected {
  border-left: 4px solid #dc3545;
}

.request-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 20px;
}

.user-info {
  display: flex;
  align-items: center;
  gap: 20px;
}

.user-avatar {
  width: 60px;
  height: 60px;
  border-radius: 50%;
  overflow: hidden;
  border: 3px solid #00ff80;
}

.user-avatar img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.user-name {
  font-size: 1.3rem;
  font-weight: 600;
  margin: 0 0 8px 0;
  color: white;
}

.user-email,
.user-phone {
  font-size: 0.9rem;
  color: #a1a1a1;
  margin: 0 0 5px 0;
}

.status-badge {
  padding: 6px 16px;
  border-radius: 20px;
  font-size: 0.8rem;
  font-weight: 600;
  text-transform: uppercase;
}

.status-badge.status-pending {
  background: rgba(255, 193, 7, 0.2);
  color: #ffc107;
  border: 1px solid rgba(255, 193, 7, 0.3);
}

.status-badge.status-approved {
  background: rgba(40, 167, 69, 0.2);
  color: #28a745;
  border: 1px solid rgba(40, 167, 69, 0.3);
}

.status-badge.status-rejected {
  background: rgba(220, 53, 69, 0.2);
  color: #dc3545;
  border: 1px solid rgba(220, 53, 69, 0.3);
}

.request-body {
  border-top: 1px solid rgba(255, 255, 255, 0.1);
  padding-top: 20px;
}

.request-meta {
  display: flex;
  gap: 30px;
  margin-bottom: 20px;
  color: #a1a1a1;
  font-size: 0.9rem;
}

.meta-item {
  display: flex;
  align-items: center;
  gap: 8px;
}

.request-actions {
  display: flex;
  gap: 15px;
  align-items: center;
}

.btn-approve,
.btn-reject {
  padding: 10px 20px;
  border: none;
  border-radius: 6px;
  font-size: 0.9rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  gap: 8px;
}

.btn-approve {
  background: linear-gradient(135deg, #28a745, #20c997);
  color: white;
}

.btn-approve:hover:not(:disabled) {
  transform: translateY(-1px);
  box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
}

.btn-reject {
  background: linear-gradient(135deg, #dc3545, #e74c3c);
  color: white;
}

.btn-reject:hover:not(:disabled) {
  transform: translateY(-1px);
  box-shadow: 0 4px 15px rgba(220, 53, 69, 0.3);
}

.btn-approve:disabled,
.btn-reject:disabled {
  opacity: 0.6;
  cursor: not-allowed;
  transform: none;
}

.processed-info {
  font-size: 0.9rem;
  color: #a1a1a1;
}

.approved-info {
  color: #28a745;
}

.rejected-info {
  color: #dc3545;
}

/* Pagination */
.pagination {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 10px;
  margin-top: 30px;
  padding-top: 20px;
  border-top: 1px solid rgba(255, 255, 255, 0.1);
}

.btn-page {
  background: rgba(255, 255, 255, 0.1);
  color: white;
  border: 1px solid rgba(255, 255, 255, 0.2);
  padding: 8px 12px;
  border-radius: 6px;
  cursor: pointer;
  transition: all 0.3s ease;
}

.btn-page:hover:not(:disabled) {
  background: rgba(255, 255, 255, 0.2);
}

.btn-page:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.page-info {
  color: #a1a1a1;
  font-size: 0.9rem;
}

/* Responsive Design */
@media (max-width: 1200px) {
  .filters-section {
    flex-direction: column;
    align-items: stretch;
    gap: 20px;
  }
  
  .filter-options {
    justify-content: center;
    flex-wrap: wrap;
  }
  
  .results-info {
    justify-content: center;
  }
}

@media (max-width: 768px) {
  .register-request-screen {
    padding: 16px;
  }
  
  .page-header {
    margin-bottom: 20px;
  }
  
  .header-content {
    flex-direction: column;
    align-items: flex-start;
    gap: 16px;
  }
  
  .header-left h1 {
    font-size: 1.75rem;
  }
  
  .header-left p {
    font-size: 0.9rem;
  }
  
  .stats-cards {
    grid-template-columns: 1fr;
    gap: 16px;
  }
  
  .stat-card {
    padding: 16px;
    min-width: auto;
  }
  
  .stat-number {
    font-size: 1.5rem;
  }
  
  .stat-label {
    font-size: 0.8rem;
  }
  
  .filters-section {
    flex-direction: column;
    align-items: stretch;
    gap: 16px;
  }
  
  .search-box {
    max-width: none;
  }
  
  .filter-options {
    justify-content: center;
    flex-wrap: wrap;
    gap: 12px;
  }
  
  .filter-select {
    min-width: 150px;
    font-size: 0.875rem;
  }
  
  .results-info {
    justify-content: center;
    margin-top: 0;
  }
  
  .requests-container {
    padding: 16px;
  }
  
  .request-card {
    padding: 20px;
  }
  
  .request-header {
    flex-direction: column;
    gap: 16px;
    align-items: flex-start;
  }
  
  .user-info {
    flex-direction: column;
    text-align: center;
    gap: 12px;
  }
  
  .user-avatar {
    width: 50px;
    height: 50px;
  }
  
  .request-actions {
    flex-direction: column;
    align-items: stretch;
    gap: 12px;
  }
  
  .btn-approve,
  .btn-reject {
    justify-content: center;
    padding: 12px 20px;
  }
  
  .pagination {
    margin-top: 20px;
    padding-top: 16px;
  }
}

@media (max-width: 480px) {
  .register-request-screen {
    padding: 12px;
  }
  
  .page-header {
    margin-bottom: 16px;
  }
  
  .header-left h1 {
    font-size: 1.5rem;
  }
  
  .header-left p {
    font-size: 0.8rem;
  }
  
  .stats-cards {
    gap: 12px;
  }
  
  .stat-card {
    padding: 12px;
    border-radius: 12px;
  }
  
  .stat-icon {
    font-size: 1.5rem;
    margin-bottom: 8px;
  }
  
  .stat-number {
    font-size: 1.25rem;
    margin-bottom: 6px;
  }
  
  .stat-label {
    font-size: 0.75rem;
  }
  
  .filters-section {
    padding: 16px;
    border-radius: 12px;
  }
  
  .filter-options {
    gap: 8px;
  }
  
  .filter-select {
    min-width: 120px;
    font-size: 0.8rem;
    padding: 8px 12px;
  }
  
  .search-input {
    font-size: 0.875rem;
    padding: 8px 12px;
  }
  
  .requests-container {
    padding: 12px;
    border-radius: 12px;
  }
  
  .request-card {
    padding: 16px;
    border-radius: 12px;
  }
  
  .user-avatar {
    width: 45px;
    height: 45px;
    border-width: 2px;
  }
  
  .user-name {
    font-size: 1rem;
    margin-bottom: 6px;
  }
  
  .user-email,
  .user-phone {
    font-size: 0.8rem;
    margin-bottom: 4px;
  }
  
  .status-badge {
    font-size: 0.75rem;
    padding: 4px 8px;
  }
  
  .request-meta {
    flex-direction: column;
    gap: 8px;
    margin-bottom: 16px;
  }
  
  .meta-item {
    font-size: 0.8rem;
  }
  
  .btn-approve,
  .btn-reject {
    font-size: 0.875rem;
    padding: 10px 16px;
  }
  
  .processed-info {
    font-size: 0.8rem;
    text-align: center;
  }
  
  .pagination {
    margin-top: 16px;
    padding-top: 12px;
    gap: 8px;
  }
  
  .btn-page {
    padding: 6px 10px;
    font-size: 0.875rem;
  }
  
  .page-info {
    font-size: 0.8rem;
  }
}

@media (max-width: 360px) {
  .register-request-screen {
    padding: 8px;
  }
  
  .page-header {
    margin-bottom: 12px;
  }
  
  .header-left h1 {
    font-size: 1.25rem;
  }
  
  .header-left p {
    font-size: 0.75rem;
  }
  
  .stats-cards {
    gap: 8px;
  }
  
  .stat-card {
    padding: 10px;
  }
  
  .stat-icon {
    font-size: 1.25rem;
  }
  
  .stat-number {
    font-size: 1.125rem;
  }
  
  .stat-label {
    font-size: 0.7rem;
  }
  
  .filters-section {
    padding: 12px;
  }
  
  .filter-select {
    min-width: 100px;
    font-size: 0.75rem;
    padding: 6px 10px;
  }
  
  .search-input {
    font-size: 0.8rem;
    padding: 6px 10px;
  }
  
  .requests-container {
    padding: 8px;
  }
  
  .request-card {
    padding: 12px;
  }
  
  .user-avatar {
    width: 40px;
    height: 40px;
  }
  
  .user-name {
    font-size: 0.9rem;
  }
  
  .user-email,
  .user-phone {
    font-size: 0.75rem;
  }
  
  .status-badge {
    font-size: 0.7rem;
    padding: 3px 6px;
  }
  
  .btn-approve,
  .btn-reject {
    font-size: 0.8rem;
    padding: 8px 12px;
  }
  
  .pagination {
    gap: 6px;
  }
  
  .btn-page {
    padding: 5px 8px;
    font-size: 0.8rem;
  }
  
  .page-info {
    font-size: 0.75rem;
  }
}

/* Landscape Mobile */
@media (max-width: 768px) and (orientation: landscape) {
  .register-request-screen {
    padding: 12px;
  }
  
  .stats-cards {
    grid-template-columns: repeat(3, 1fr);
    gap: 12px;
  }
  
  .filters-section {
    flex-direction: row;
    align-items: center;
    gap: 12px;
  }
  
  .filter-options {
    flex-direction: row;
    gap: 8px;
  }
  
  .filter-select {
    min-width: 120px;
  }
}

/* Tablet Specific */
@media (min-width: 769px) and (max-width: 1024px) {
  .register-request-screen {
    padding: 20px;
  }
  
  .stats-cards {
    grid-template-columns: repeat(3, 1fr);
    gap: 18px;
  }
  
  .stat-card {
    padding: 18px;
  }
  
  .filter-options {
    gap: 16px;
  }
  
  .filter-select {
    min-width: 180px;
  }
  
  .request-card {
    padding: 22px;
  }
}

/* High DPI Displays */
@media (-webkit-min-device-pixel-ratio: 2), (min-resolution: 192dpi) {
  .page-header,
  .stat-card,
  .filters-section,
  .request-card {
    border-width: 0.5px;
  }
}

/* Touch Device Optimizations */
@media (hover: none) and (pointer: coarse) {
  .stat-card:hover,
  .request-card:hover,
  .btn-approve:hover,
  .btn-reject:hover {
    transform: none;
  }
  
  .stat-card:active,
  .request-card:active {
    transform: scale(0.98);
  }
  
  .btn-approve:active,
  .btn-reject:active {
    transform: scale(0.95);
  }
}

/* Print Styles */
@media print {
  .register-request-screen {
    background: white;
    color: black;
    padding: 0;
  }
  
  .page-header,
  .filters-section,
  .btn-refresh,
  .btn-approve,
  .btn-reject {
    display: none;
  }
  
  .stats-cards {
    grid-template-columns: repeat(3, 1fr);
    gap: 1rem;
  }
  
  .stat-card {
    background: white;
    border: 1px solid #000;
    color: black;
    box-shadow: none;
  }
  
  .request-card {
    background: white;
    border: 1px solid #000;
    color: black;
    box-shadow: none;
    margin-bottom: 1rem;
  }
  
  .pagination {
    display: none;
  }
}
</style>
