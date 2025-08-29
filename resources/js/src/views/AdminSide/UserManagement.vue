<template>
  <div class="user-management-screen">
    <!-- Header Section -->
    <div class="page-header">
      <div class="header-content">
        <h1 class="page-title">👥 User Management</h1>
        <p class="page-subtitle">Manage all registered users and their accounts</p>
      </div>
      <div class="header-actions">
        <button class="btn-primary" @click="showAddUserModal = true">
          <i class="fa-solid fa-user-plus"></i> Add User
        </button>
      </div>
    </div>

    <!-- Search and Filters -->
    <div class="search-filters">
      <div class="search-box">
        <i class="fa-solid fa-search search-icon"></i>
        <input 
          type="text" 
          v-model="searchQuery" 
          placeholder="Search users by name, email, or phone..."
          class="search-input"
        />
      </div>
      
      <div class="filter-options">
        <select v-model="statusFilter" class="filter-select">
          <option value="all">All Users</option>
          <option value="active">Active</option>
          <option value="inactive">Inactive</option>
          <option value="suspended">Suspended</option>
        </select>
        
        <select v-model="roleFilter" class="filter-select">
          <option value="all">All Roles</option>
          <option value="admin">Admin</option>
          <option value="user">User</option>
        </select>
        
        <button class="btn-secondary" @click="resetFilters">
          <i class="fa-solid fa-rotate"></i> Reset
        </button>
      </div>
    </div>

    <!-- Users Stats -->
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-icon">👥</div>
        <div class="stat-content">
          <h3 class="stat-number">{{ totalUsers }}</h3>
          <p class="stat-label">Total Users</p>
        </div>
      </div>
      
      <div class="stat-card">
        <div class="stat-icon">✅</div>
        <div class="stat-content">
          <h3 class="stat-number">{{ activeUsers }}</h3>
          <p class="stat-label">Active Users</p>
        </div>
      </div>
      
      <div class="stat-card">
        <div class="stat-icon">👑</div>
        <div class="stat-content">
          <h3 class="stat-number">{{ adminUsers }}</h3>
          <p class="stat-label">Admin Users</p>
        </div>
      </div>
      
      <div class="stat-card">
        <div class="stat-icon">📅</div>
        <div class="stat-content">
          <h3 class="stat-number">{{ newUsersThisMonth }}</h3>
          <p class="stat-label">New This Month</p>
        </div>
      </div>
    </div>

    <!-- Users Table -->
    <div class="users-container">
      <div class="table-header">
        <h3>Users List ({{ filteredUsers.length }})</h3>
        <div class="table-actions">
          <button class="btn-refresh" @click="fetchUsers" :disabled="loading">
            <i class="fa-solid fa-rotate" :class="{ 'fa-spin': loading }"></i>
            {{ loading ? 'Loading...' : 'Refresh' }}
          </button>
          <button class="btn-export" @click="exportUsers">
            <i class="fa-solid fa-download"></i> Export
          </button>
        </div>
      </div>
      
      <div v-if="loading" class="no-users">
        <div class="no-users-icon">🔄</div>
        <h3>Loading users...</h3>
        <p>Please wait while we fetch the data.</p>
      </div>
      
      <div v-else-if="error" class="no-users">
        <div class="no-users-icon">❌</div>
        <h3>Error: {{ error }}</h3>
        <p>Failed to load users. Please try again later.</p>
      </div>
      
      <div v-else-if="filteredUsers.length === 0" class="no-users">
        <div class="no-users-icon">👥</div>
        <h3>No users found</h3>
        <p>Try adjusting your search or filters</p>
      </div>
      
      <div v-else class="users-table">
        <div class="table-row header-row">
          <div class="col-user">User</div>
          <div class="col-email">Email</div>
          <div class="col-phone">Phone</div>
          <div class="col-role">Role</div>
          <div class="col-status">Status</div>
          <div class="col-joined">Joined</div>
          <div class="col-actions">Actions</div>
        </div>
        
        <div 
          v-for="user in paginatedUsers" 
          :key="user.id" 
          class="table-row user-row"
          :class="{ 'user-inactive': user.status === 'inactive', 'user-suspended': user.status === 'suspended' }"
        >
          <div class="col-user">
            <div class="user-info">
              <img :src="user.avatar" :alt="user.name" class="user-avatar" />
              <div class="user-details">
                <h4 class="user-name">{{ user.name }}</h4>
                <p class="user-id">ID: {{ user.id }}</p>
              </div>
            </div>
          </div>
          
          <div class="col-email">
            <span class="email-text">{{ user.email }}</span>
          </div>
          
          <div class="col-phone">
            <span class="phone-text">{{ user.phone || 'N/A' }}</span>
          </div>
          
          <div class="col-role">
            <span class="role-badge" :class="getRoleClass(user.role)">
              {{ user.role }}
            </span>
          </div>
          
          <div class="col-status">
            <span class="status-badge" :class="getStatusClass(user.status)">
              {{ user.status }}
            </span>
          </div>
          
          <div class="col-joined">
            <span class="date-text">{{ formatDate(user.created_at) }}</span>
          </div>
          
          <div class="col-actions">
            <div class="action-buttons">
              <button class="btn-action btn-view" @click="viewUser(user)" title="View Details" :disabled="isViewingUser">
                <i class="fa-solid fa-eye" :class="{ 'fa-spin': isViewingUser }"></i>
              </button>
              <button class="btn-action btn-edit" @click="editUser(user)" title="Edit User">
                <i class="fa-solid fa-edit"></i>
              </button>
              <button 
                v-if="user.status === 'suspended'"
                class="btn-action btn-activate" 
                @click="toggleUserStatus(user, 'active')"
                title="Activate User"
              >
                <i class="fa-solid fa-play"></i>
              </button>
              <button 
                class="btn-action btn-delete" 
                @click="deleteUser(user)"
                title="Delete User"
              >
                <i class="fa-solid fa-trash"></i>
              </button>
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

    <!-- Add User Modal -->
    <div v-if="showAddUserModal" class="modal-overlay" @click="closeAddUserModal">
      <div class="modal-content" @click.stop ref="addUserModalRef">
        <div class="modal-header">
          <h3>Add New User</h3>
          <button class="close-btn" @click="closeAddUserModal">&times;</button>
        </div>
        <div class="modal-body">
          <form @submit.prevent="addNewUser" class="user-form">
            <div class="form-row">
              <div class="form-group">
                <label for="name">Full Name *</label>
                <input 
                  type="text" 
                  id="name" 
                  v-model="newUser.name" 
                  required 
                  class="form-control"
                  placeholder="Enter full name"
                />
              </div>
              
              <div class="form-group">
                <label for="email">Email Address *</label>
                <input 
                  type="email" 
                  id="email" 
                  v-model="newUser.email" 
                  required 
                  class="form-control"
                  placeholder="Enter email address"
                />
              </div>
            </div>
            
            <div class="form-row">
              <div class="form-group">
                <label for="phone">Phone Number *</label>
                <input 
                  type="tel" 
                  id="phone" 
                  v-model="newUser.phone" 
                  required 
                  class="form-control"
                  placeholder="Enter phone number"
                />
              </div>
              
              <div class="form-group">
                <label for="role">User Role *</label>
                <select id="role" v-model="newUser.role" required class="form-control">
                  <option value="">Select Role</option>
                  <option value="user">👤 User</option>
                  <option value="admin">👑 Admin</option>
                </select>
              </div>
            </div>
            
            <div class="form-row">
              <div class="form-group">
                <label for="status">Account Status *</label>
                <select id="status" v-model="newUser.status" required class="form-control">
                  <option value="">Select Status</option>
                  <option value="active">✅ Active</option>
                  <option value="inactive">⏸️ Inactive</option>
                  <option value="suspended">🚫 Suspended</option>
                </select>
              </div>
            </div>
            
            <div class="form-row">
              <div class="form-group">
                <label for="password">Password *</label>
                <input 
                  type="password" 
                  id="password" 
                  v-model="newUser.password" 
                  required 
                  class="form-control"
                  placeholder="Enter password (min 6 characters)"
                  minlength="6"
                />
                <small class="form-help">Password must be at least 6 characters long</small>
              </div>
              
              <div class="form-group">
                <label for="confirmPassword">Confirm Password *</label>
                <input 
                  type="password" 
                  id="confirmPassword" 
                  v-model="newUser.confirmPassword" 
                  required 
                  class="form-control"
                  placeholder="Confirm your password"
                  minlength="6"
                />
              </div>
            </div>
            
            <div class="form-row">
              <div class="form-group full-width">
                <label for="profile_image">Profile Image</label>
                <input 
                  type="file" 
                  id="profile_image" 
                  @change="handleProfileImageUpload" 
                  accept="image/*"
                  class="form-control file-input"
                />
                <small class="form-help">Upload profile picture (JPG, PNG, GIF - Max 2MB)</small>
              </div>
            </div>
            
            <div class="form-actions">
              <button type="button" class="btn-secondary" @click="closeAddUserModal">Cancel</button>
              <button type="submit" class="btn-primary" :disabled="isSubmitting">
                <span v-if="isSubmitting">
                  <i class="fa-solid fa-spinner fa-spin"></i> Creating User...
                </span>
                <span v-else">
                  <i class="fa-solid fa-user-plus"></i> Add User
                </span>
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Edit User Modal -->
    <div v-if="showEditUserModal" class="modal-overlay" @click="closeEditUserModal">
      <div class="modal-content" @click.stop ref="editUserModalRef">
        <div class="modal-header">
          <h3>Edit User: {{ editingUser.name }}</h3>
          <button class="close-btn" @click="closeEditUserModal">&times;</button>
        </div>
        <div class="modal-body">
          <form @submit.prevent="updateUser" class="user-form">
            <div class="form-row">
              <div class="form-group">
                <label for="editName">Full Name</label>
                <input 
                  type="text" 
                  id="editName" 
                  v-model="editingUser.name" 
                  required 
                  class="form-control"
                  placeholder="Enter full name"
                />
              </div>
              
              <div class="form-group">
                <label for="editEmail">Email</label>
                <input 
                  type="email" 
                  id="editEmail" 
                  v-model="editingUser.email" 
                  required 
                  class="form-control"
                  placeholder="Enter email address"
                />
              </div>
            </div>
            
            <div class="form-row">
              <div class="form-group">
                <label for="editPhone">Phone</label>
                <input 
                  type="tel" 
                  id="editPhone" 
                  v-model="editingUser.phone" 
                  class="form-control"
                  placeholder="Enter phone number"
                />
              </div>
              
              <div class="form-group">
                <label for="editRole">Role</label>
                <select id="editRole" v-model="editingUser.role" required class="form-control">
                  <option value="user">User</option>
                  <option value="admin">Admin</option>
                </select>
              </div>
            </div>
            
            <div class="form-row">
              <div class="form-group">
                <label for="editStatus">Status</label>
                <select id="editStatus" v-model="editingUser.status" required class="form-control">
                  <option value="active">Active</option>
                  <option value="inactive">Inactive</option>
                  <option value="suspended">Suspended</option>
                </select>
              </div>
              
              <div class="form-group">
                <label for="editPassword">New Password (Optional)</label>
                <input 
                  type="password" 
                  id="editPassword" 
                  v-model="editingUser.newPassword" 
                  class="form-control"
                  placeholder="Leave blank to keep current password"
                />
              </div>
            </div>
            
            <div class="form-actions">
              <button type="button" class="btn-secondary" @click="closeEditUserModal">Cancel</button>
              <button type="submit" class="btn-primary">Update User</button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- View User Modal -->
    <div v-if="showViewUserModal" class="modal-overlay" @click="closeViewUserModal">
      <div class="modal-content" @click.stop>
        <div class="modal-header">
          <h3>User Details: {{ viewingUser.name }}</h3>
          <button class="close-btn" @click="closeViewUserModal">&times;</button>
        </div>
        <div class="modal-body">
          <div class="user-details-view">
            <div class="user-avatar-large">
              <img :src="viewingUser.avatar" :alt="viewingUser.name" />
            </div>
            
            <div class="user-info-grid">
              <div class="info-row">
                <span class="info-label">ID:</span>
                <span class="info-value">{{ viewingUser.id }}</span>
              </div>
              
              <div class="info-row">
                <span class="info-label">Name:</span>
                <span class="info-value">{{ viewingUser.name }}</span>
              </div>
              
              <div class="info-row">
                <span class="info-label">Email:</span>
                <span class="info-value">{{ viewingUser.email }}</span>
              </div>
              
              <div class="info-row">
                <span class="info-label">Phone:</span>
                <span class="info-value">{{ viewingUser.phone || 'N/A' }}</span>
              </div>
              
              <div class="info-row">
                <span class="info-label">Role:</span>
                <span class="info-value">
                  <span class="role-badge" :class="getRoleClass(viewingUser.role)">
                    {{ viewingUser.role }}
                  </span>
                </span>
              </div>
              
              <div class="info-row">
                <span class="info-label">Status:</span>
                <span class="info-value">
                  <span class="status-badge" :class="getStatusClass(viewingUser.status)">
                    {{ viewingUser.status }}
                  </span>
                </span>
              </div>
              
              <div class="info-row">
                <span class="info-label">Joined:</span>
                <span class="info-value">{{ formatDate(viewingUser.created_at) }}</span>
              </div>
              
              <div class="info-row">
                <span class="info-label">Last Login:</span>
                <span class="info-value">{{ viewingUser.last_login || 'Never' }}</span>
              </div>
              
              <!-- Balance Information -->
              <div class="info-row">
                <span class="info-label">Total Balance:</span>
                <span class="info-value balance-value">₹{{ viewingUser.total_balance || '0.00' }}</span>
              </div>
              
              <!-- Bank Details Section -->
              <div class="bank-details-section">
                <h4 class="section-title">🏦 Bank Details</h4>
                
                <div class="info-row">
                  <span class="info-label">Bank Name:</span>
                  <span class="info-value">{{ viewingUser.bank_name || 'Not provided' }}</span>
                </div>
                
                <div class="info-row">
                  <span class="info-label">Account Number:</span>
                  <span class="info-value">{{ viewingUser.account_no || 'Not provided' }}</span>
                </div>
                
                <div class="info-row">
                  <span class="info-label">IFSC Code:</span>
                  <span class="info-value">{{ viewingUser.ifsc_code || 'Not provided' }}</span>
                </div>
              </div>
            </div>
          </div>
          
          <div class="modal-actions">
            <button class="btn-secondary" @click="closeViewUserModal">Close</button>
            <button class="btn-primary" @click="editUserFromView">Edit User</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()

// Reactive Data
const showAddUserModal = ref(false)
const showEditUserModal = ref(false)
const showViewUserModal = ref(false)
const isViewingUser = ref(false)
const searchQuery = ref('')
const statusFilter = ref('all')
const roleFilter = ref('all')
const currentPage = ref(1)
const usersPerPage = ref(10)
const isSubmitting = ref(false) // Add loading state

// New User Form Data
const newUser = ref({
  name: '',
  email: '',
  phone: '',
  role: 'user',
  status: 'active',
  password: '',
  confirmPassword: '',
  profile_image: null
})

// Editing User Data
const editingUser = ref({
  id: null,
  name: '',
  email: '',
  phone: '',
  role: 'user',
  status: 'active',
  newPassword: ''
})

// Viewing User Data
const viewingUser = ref({})

// Live Users Data
const users = ref([])
const loading = ref(false)
const error = ref(null)

// API Base URL
const API_BASE = `${window.location.origin}/api`

// Fetch Users from Backend
const fetchUsers = async () => {
  try {
    loading.value = true
    error.value = null
    
    const token = localStorage.getItem('access_token')
    if (!token) {
      throw new Error('No access token found. Please login again.')
    }
    
    const response = await fetch(`${API_BASE}/users`, {
      method: 'GET',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      }
    })
    
    if (response.status === 401) {
      throw new Error('Authentication failed. Please login again.')
    }
    
    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`)
    }
    
    const data = await response.json()
    
    if (data.success && data.data) {
      users.value = data.data.map(user => ({
        id: user.id,
        name: user.name || 'Unknown User',
        email: user.email,
        phone: user.phone || null,
        role: user.is_admin ? 'admin' : 'user',
        status: mapUserStatus(user.status) || 'active',
        avatar: user.profile_image || '../assest/img/tableprofileimg.png',
        created_at: user.created_at,
        last_login: user.last_login_at || null, // Use real last_login_at data
        total_balance: 0 // Field doesn't exist in database
      }))
    } else {
      throw new Error(data.message || 'Failed to fetch users')
    }
    
  } catch (err) {
    console.error('Error fetching users:', err)
    error.value = err.message
    
    // Fallback to sample data if API fails
    if (users.value.length === 0) {
      users.value = [
        {
          id: 1,
          name: 'Kamlesh Patel',
          email: 'kamlesh@example.com',
          phone: '+91 98765 43210',
          role: 'admin',
          status: 'active',
          avatar: '../assest/img/tableprofileimg.png',
          created_at: '2024-01-01T10:00:00Z',
          last_login: '2024-01-15T14:30:00Z',
          total_balance: 50000
        }
      ]
    }
  } finally {
    loading.value = false
  }
}

// Computed Properties
const filteredUsers = computed(() => {
  let filtered = users.value

  // Search filter
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase()
    filtered = filtered.filter(user => 
      user.name.toLowerCase().includes(query) ||
      user.email.toLowerCase().includes(query) ||
      (user.phone && user.phone.includes(query))
    )
  }

  // Status filter
  if (statusFilter.value !== 'all') {
    filtered = filtered.filter(user => user.status === statusFilter.value)
  }

  // Role filter
  if (roleFilter.value !== 'all') {
    filtered = filtered.filter(user => user.role === roleFilter.value)
  }

  return filtered
})

const paginatedUsers = computed(() => {
  const start = (currentPage.value - 1) * usersPerPage.value
  const end = start + usersPerPage.value
  return filteredUsers.value.slice(start, end)
})

const totalPages = computed(() => {
  return Math.ceil(filteredUsers.value.length / usersPerPage.value)
})

const totalUsers = computed(() => users.value.length)
const activeUsers = computed(() => users.value.filter(user => user.status === 'active').length)
const adminUsers = computed(() => users.value.filter(user => user.role === 'admin').length)
const newUsersThisMonth = computed(() => {
  const thisMonth = new Date().getMonth()
  const thisYear = new Date().getFullYear()
  return users.value.filter(user => {
    const userDate = new Date(user.created_at)
    return userDate.getMonth() === thisMonth && userDate.getFullYear() === thisYear
  }).length
})

// Methods
const closeAddUserModal = () => {
  showAddUserModal.value = false
  resetNewUserForm()
}

const closeEditUserModal = () => {
  showEditUserModal.value = false
  resetEditingUserForm()
}

const closeViewUserModal = () => {
  showViewUserModal.value = false
  viewingUser.value = {}
}

const resetNewUserForm = () => {
  newUser.value = {
    name: '',
    email: '',
    phone: '',
    role: 'user',
    status: 'active',
    password: '',
    confirmPassword: '',
    profile_image: null
  }
}

const resetEditingUserForm = () => {
  editingUser.value = {
    id: null,
    name: '',
    email: '',
    phone: '',
    role: 'user',
    status: 'active',
    newPassword: ''
  }
}

// Handle profile image upload
const handleProfileImageUpload = (event) => {
  const file = event.target.files[0]
  if (file) {
    // Validate file size (2MB max)
    if (file.size > 2 * 1024 * 1024) {
      alert('Profile image must be less than 2MB')
      event.target.value = ''
      return
    }
    
    // Validate file type
    if (!file.type.startsWith('image/')) {
      alert('Please select a valid image file')
      event.target.value = ''
      return
    }
    
    newUser.value.profile_image = file
  }
}

// Add New User to Backend
const addNewUser = async () => {
  try {
    // Validate password confirmation
    if (newUser.value.password !== newUser.value.confirmPassword) {
      alert('Password and Confirm Password do not match!')
      return
    }
    
    // Validate password length
    if (newUser.value.password.length < 6) {
      alert('Password must be at least 6 characters long!')
      return
    }
    
    // Validate required fields
    if (!newUser.value.name || !newUser.value.email || !newUser.value.phone || 
        !newUser.value.role || !newUser.value.status) {
      alert('Please fill in all required fields!')
      return
    }
    
    isSubmitting.value = true
    
    const token = localStorage.getItem('access_token')
    if (!token) {
      throw new Error('No access token found. Please login again.')
    }

    // Create FormData for file upload
    const formData = new FormData()
    formData.append('name', newUser.value.name)
    formData.append('email', newUser.value.email)
    formData.append('phone', newUser.value.phone)
    formData.append('password', newUser.value.password)
    formData.append('password_confirmation', newUser.value.confirmPassword)
    formData.append('is_admin', newUser.value.role === 'admin' ? 1 : 0)
    formData.append('status', mapStatusToDb(newUser.value.status))
    
    // Add profile image if selected
    if (newUser.value.profile_image) {
      formData.append('profile_image', newUser.value.profile_image)
    }

    const response = await fetch(`${API_BASE}/users`, {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
        // Note: Don't set Content-Type for FormData, let browser set it
      },
      body: formData
    })

    if (response.status === 401) {
      throw new Error('Authentication failed. Please login again.')
    }

    if (!response.ok) {
      const errorData = await response.json()
      throw new Error(errorData.message || 'Failed to create user')
    }

    const data = await response.json()
    
    if (data.success && data.data) {
      // Add new user to the local list
      const newUserData = {
        id: data.data.id,
        name: data.data.name || 'Unknown User',
        email: data.data.email,
        phone: data.data.phone || null,
        role: data.data.is_admin ? 'admin' : 'user',
        status: mapUserStatus(data.data.status) || 'active',
        avatar: data.data.profile_image || '../assest/img/tableprofileimg.png',
        created_at: data.data.created_at,
        last_login: null, // New user hasn't logged in yet
        total_balance: 0
      }
      
      users.value.unshift(newUserData) // Add to beginning of list
      
      closeAddUserModal()
      alert('User created successfully!')
    } else {
      throw new Error(data.message || 'Failed to create user')
    }
    
  } catch (error) {
    console.error('Error creating user:', error)
    alert(`Error creating user: ${error.message}`)
  } finally {
    isSubmitting.value = false
  }
}

const viewUser = async (user) => {
  isViewingUser.value = true
  try {
    // Fetch fresh user data including balance and bank details
    const token = localStorage.getItem('access_token')
    if (!token) {
      throw new Error('No access token found. Please login again.')
    }

    const response = await fetch(`${API_BASE}/users/${user.id}`, {
      method: 'GET',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      }
    })

    if (!response.ok) {
      throw new Error('Failed to fetch user details')
    }

    const result = await response.json()
    
    if (result.success && result.data) {
      viewingUser.value = result.data
      showViewUserModal.value = true
    } else {
      throw new Error(result.message || 'Failed to fetch user details')
    }
  } catch (error) {
    console.error('Error fetching user details:', error)
    alert(`Error fetching user details: ${error.message}`)
    
    // Fallback to existing user data
    viewingUser.value = user
    showViewUserModal.value = true
  } finally {
    isViewingUser.value = false
  }
}

const editUser = (user) => {
  editingUser.value = {
    id: user.id,
    name: user.name,
    email: user.email,
    phone: user.phone,
    role: user.role,
    status: user.status,
    newPassword: ''
  }
  showEditUserModal.value = true
}

const editUserFromView = () => {
  editingUser.value = {
    id: viewingUser.value.id,
    name: viewingUser.value.name,
    email: viewingUser.value.email,
    phone: viewingUser.value.phone,
    role: viewingUser.value.role,
    status: viewingUser.value.status,
    newPassword: ''
  }
  showEditUserModal.value = true
}

// Update User Status in Backend
const toggleUserStatus = async (user, newStatus) => {
  try {
    const token = localStorage.getItem('access_token')
    if (!token) {
      throw new Error('No access token found. Please login again.')
    }

    // Map frontend status to database status
    const statusMap = {
      'active': 'A',
      'inactive': 'I',
      'suspended': 'D'
    }
    
    const dbStatus = statusMap[newStatus] || 'A'

    const response = await fetch(`${API_BASE}/users/${user.id}/status`, {
      method: 'PATCH',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      },
      body: JSON.stringify({ status: dbStatus })
    })

    if (response.status === 401) {
      throw new Error('Authentication failed. Please login again.')
    }

    if (!response.ok) {
      const errorData = await response.json()
      throw new Error(errorData.message || 'Failed to update user status')
    }

    const data = await response.json()
    
    if (data.success) {
      user.status = newStatus
      const action = newStatus === 'suspended' ? 'suspended' : 'activated'
      alert(`User ${user.name} has been ${action}!`)
    } else {
      throw new Error(data.message || 'Failed to update user status')
    }
    
  } catch (error) {
    console.error('Error updating user status:', error)
    alert(`Error updating user status: ${error.message}`)
  }
}

// Update User in Backend
const updateUser = async () => {
  try {
    const token = localStorage.getItem('access_token')
    if (!token) {
      throw new Error('No access token found. Please login again.')
    }

    const userData = {
      name: editingUser.value.name,
      email: editingUser.value.email,
      phone: editingUser.value.phone,
      is_admin: editingUser.value.role === 'admin' ? 1 : 0,
      status: mapStatusToDb(editingUser.value.status) // Add status field
    }

    // Add password if provided
    if (editingUser.value.newPassword) {
      userData.password = editingUser.value.newPassword
      userData.password_confirmation = editingUser.value.newPassword
    }

    const response = await fetch(`${API_BASE}/users/${editingUser.value.id}`, {
      method: 'PUT',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      },
      body: JSON.stringify(userData)
    })

    if (response.status === 401) {
      throw new Error('Authentication failed. Please login again.')
    }

    if (!response.ok) {
      const errorData = await response.json()
      throw new Error(errorData.message || 'Failed to update user')
    }

    const data = await response.json()
    
    if (data.success && data.data) {
      // Update user in the local list
      const userIndex = users.value.findIndex(u => u.id === editingUser.value.id)
      if (userIndex !== -1) {
        users.value[userIndex] = {
          ...users.value[userIndex],
          name: data.data.name,
          email: data.data.email,
          phone: data.data.phone,
          role: data.data.is_admin ? 'admin' : 'user',
          status: mapUserStatus(data.data.status) // Map database status to frontend
        }
      }
      
      closeEditUserModal()
      alert('User updated successfully!')
    } else {
      throw new Error(data.message || 'Failed to update user')
    }
    
  } catch (error) {
    console.error('Error updating user:', error)
    alert(`Error updating user: ${error.message}`)
  }
}

// Delete User from Backend
const deleteUser = async (user) => {
  try {
    if (confirm(`Are you sure you want to delete user: ${user.name}?`)) {
      const token = localStorage.getItem('access_token')
      if (!token) {
        throw new Error('No access token found')
      }

      const response = await fetch(`${API_BASE}/users/${user.id}`, {
        method: 'DELETE',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest'
        }
      })

      if (response.status === 401) {
        throw new Error('Authentication failed. Please login again.')
      }

      if (!response.ok) {
        const errorData = await response.json()
        throw new Error(errorData.message || 'Failed to delete user')
      }

      const data = await response.json()
      
      if (data.success) {
        const index = users.value.findIndex(u => u.id === user.id)
        if (index > -1) {
          users.value.splice(index, 1)
          alert('User deleted successfully!')
          
          // Reset to first page if current page is empty
          if (paginatedUsers.value.length === 0 && currentPage.value > 1) {
            currentPage.value--
          }
        }
      } else {
        throw new Error(data.message || 'Failed to delete user')
      }
    }
  } catch (error) {
    console.error('Error deleting user:', error)
    alert(`Error deleting user: ${error.message}`)
  }
}

const resetFilters = () => {
  searchQuery.value = ''
  statusFilter.value = 'all'
  roleFilter.value = 'all'
  currentPage.value = 1
}

const exportUsers = () => {
  const csvContent = generateCSV()
  const blob = new Blob([csvContent], { type: 'text/csv' })
  const url = window.URL.createObjectURL(blob)
  const a = document.createElement('a')
  a.href = url
  a.download = 'users_export.csv'
  a.click()
  window.URL.revokeObjectURL(url)
}

const generateCSV = () => {
  const headers = ['ID', 'Name', 'Email', 'Phone', 'Role', 'Status', 'Joined Date']
  const rows = filteredUsers.value.map(user => [
    user.id,
    user.name,
    user.email,
    user.phone || 'N/A',
    user.role,
    user.status,
    formatDate(user.created_at)
  ])
  
  return [headers, ...rows].map(row => row.join(',')).join('\n')
}

const getRoleClass = (role) => {
  return role === 'admin' ? 'role-admin' : 'role-user'
}

const getStatusClass = (status) => {
  const statusClasses = {
    active: 'status-active',
    inactive: 'status-inactive',
    suspended: 'status-suspended'
  }
  return statusClasses[status] || 'status-active'
}

const formatDate = (dateString) => {
  if (!dateString) return 'N/A'
  return new Date(dateString).toLocaleDateString('en-IN', {
    day: '2-digit',
    month: 'short',
    year: 'numeric'
  })
}

// Map frontend status to database status
const mapStatusToDb = (frontendStatus) => {
  const statusMap = {
    'active': 'A',
    'inactive': 'I',
    'suspended': 'D'
  }
  return statusMap[frontendStatus] || 'A'
}

// Map database status to frontend status
const mapUserStatus = (dbStatus) => {
  const statusMap = {
    'A': 'active',
    'I': 'inactive',
    'D': 'suspended'
  }
  return statusMap[dbStatus] || 'active'
}

// Watch for filter changes to reset pagination
watch([searchQuery, statusFilter, roleFilter], () => {
  currentPage.value = 1
})

// ESC Key Handler
const handleEscKey = (event) => {
  if (event.key === 'Escape') {
    if (showAddUserModal.value) {
      closeAddUserModal()
    } else if (showEditUserModal.value) {
      closeEditUserModal()
    } else if (showViewUserModal.value) {
      closeViewUserModal()
    }
  }
}

// Add ESC key event listener
onMounted(() => {
  document.addEventListener('keydown', handleEscKey)
  fetchUsers()
})

// Remove event listener on component unmount
onUnmounted(() => {
  document.removeEventListener('keydown', handleEscKey)
})

// Click Outside Handler
const handleClickOutside = (event, modalRef) => {
  if (modalRef && !modalRef.contains(event.target)) {
    if (showAddUserModal.value) {
      closeAddUserModal()
    } else if (showEditUserModal.value) {
      closeEditUserModal()
    } else if (showViewUserModal.value) {
      closeViewUserModal()
    }
  }
}
</script>

<style scoped>
.user-management-screen {
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

/* Search and Filters */
.search-filters {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24px;
  gap: 20px;
  flex-wrap: wrap;
}

.search-box {
  position: relative;
  flex: 1;
  min-width: 300px;
}

.search-icon {
  position: absolute;
  left: 16px;
  top: 50%;
  transform: translateY(-50%);
  color: #a1a1a1;
  font-size: 1.1rem;
}

.search-input {
  width: 100%;
  padding: 12px 16px 12px 48px;
  border: 1px solid rgba(255, 255, 255, 0.2);
  border-radius: 8px;
  background: rgba(255, 255, 255, 0.05);
  color: white;
  font-size: 1rem;
  transition: all 0.3s ease;
}

.search-input:focus {
  outline: none;
  border-color: #00ff80;
  box-shadow: 0 0 0 3px rgba(0, 255, 128, 0.1);
}

.search-input::placeholder {
  color: #666;
}

.filter-options {
  display: flex;
  gap: 12px;
  align-items: center;
  flex-wrap: wrap;
}

.filter-select {
  padding: 10px 16px;
  border: 1px solid rgba(255, 255, 255, 0.2);
  border-radius: 6px;
  background: rgba(255, 255, 255, 0.05);
  color: white;
  font-size: 0.9rem;
  cursor: pointer;
  transition: all 0.3s ease;
}

.filter-select:focus {
  outline: none;
  border-color: #00ff80;
}

.btn-secondary {
  background: rgba(255, 255, 255, 0.1);
  color: white;
  border: 1px solid rgba(255, 255, 255, 0.2);
  padding: 10px 16px;
  border-radius: 6px;
  font-size: 0.9rem;
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  gap: 6px;
}

.btn-secondary:hover {
  background: rgba(255, 255, 255, 0.2);
}

/* Stats Grid */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 20px;
  margin-bottom: 32px;
}

.stat-card {
  background: rgba(255, 255, 255, 0.05);
  border-radius: 12px;
  padding: 20px;
  border: 1px solid rgba(255, 255, 255, 0.1);
  display: flex;
  align-items: center;
  gap: 16px;
  transition: all 0.3s ease;
}

.stat-card:hover {
  transform: translateY(-4px);
  border-color: rgba(0, 255, 128, 0.3);
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
}

.stat-icon {
  font-size: 2rem;
  width: 60px;
  height: 60px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: rgba(0, 255, 128, 0.1);
  border-radius: 50%;
  border: 2px solid rgba(0, 255, 128, 0.3);
}

.stat-content h3 {
  margin: 0 0 4px 0;
  font-size: 2rem;
  font-weight: bold;
  color: #00ff80;
}

.stat-content p {
  margin: 0;
  color: #a1a1a1;
  font-size: 0.9rem;
}

/* Users Container */
.users-container {
  background: rgba(255, 255, 255, 0.05);
  border-radius: 16px;
  padding: 24px;
  border: 1px solid rgba(255, 255, 255, 0.1);
}

.table-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24px;
}

.table-header h3 {
  margin: 0;
  color: #e0e0e0;
  font-size: 1.3rem;
}

.btn-export {
  background: rgba(0, 191, 255, 0.2);
  color: #00bfff;
  border: 1px solid rgba(0, 191, 255, 0.3);
  padding: 8px 16px;
  border-radius: 6px;
  font-size: 0.9rem;
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  gap: 6px;
}

.btn-export:hover {
  background: rgba(0, 191, 255, 0.3);
  border-color: rgba(0, 191, 255, 0.5);
}

/* No Users */
.no-users {
  text-align: center;
  padding: 60px 20px;
  color: #a1a1a1;
}

.no-users-icon {
  font-size: 4rem;
  margin-bottom: 16px;
}

.no-users h3 {
  margin: 0 0 8px 0;
  color: #e0e0e0;
}

.no-users p {
  margin: 0;
  font-size: 1.1rem;
}

/* Users Table */
.users-table {
  overflow-x: auto;
}

.table-row {
  display: grid;
  grid-template-columns: 2fr 2fr 1.5fr 1fr 1fr 1.5fr 1.5fr;
  gap: 16px;
  align-items: center;
  padding: 16px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.05);
  transition: all 0.3s ease;
}

.table-row:hover {
  background: rgba(255, 255, 255, 0.02);
}

.header-row {
  background: rgba(0, 255, 128, 0.1);
  border-radius: 8px;
  font-weight: 600;
  color: #00ff80;
  text-transform: uppercase;
  font-size: 0.85rem;
  letter-spacing: 0.5px;
}

.user-row {
  border-radius: 8px;
}

.user-row:hover {
  background: rgba(255, 255, 255, 0.05);
}

.user-inactive {
  opacity: 0.6;
}

.user-suspended {
  opacity: 0.4;
  background: rgba(255, 77, 77, 0.1);
}

/* User Info */
.user-info {
  display: flex;
  align-items: center;
  gap: 12px;
}

.user-avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  border: 2px solid #00ff80;
  object-fit: cover;
}

.user-details h4 {
  margin: 0 0 2px 0;
  color: #e0e0e0;
  font-size: 1rem;
}

.user-id {
  margin: 0;
  color: #a1a1a1;
  font-size: 0.8rem;
}

/* Badges */
.role-badge,
.status-badge {
  padding: 4px 12px;
  border-radius: 20px;
  font-size: 0.8rem;
  font-weight: 600;
  text-transform: uppercase;
}

.role-admin {
  background: rgba(255, 215, 0, 0.2);
  color: #ffd700;
}

.role-user {
  background: rgba(0, 191, 255, 0.2);
  color: #00bfff;
}

.status-active {
  background: rgba(0, 255, 128, 0.2);
  color: #00ff80;
}

.status-inactive {
  background: rgba(255, 165, 0, 0.2);
  color: #ffa500;
}

.status-suspended {
  background: rgba(255, 77, 77, 0.2);
  color: #ff4d4d;
}

/* Action Buttons */
.action-buttons {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
}

.btn-action {
  width: 36px;
  height: 36px;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.9rem;
}

.btn-view {
  background: rgba(0, 191, 255, 0.2);
  color: #00bfff;
}

.btn-view:hover {
  background: rgba(0, 191, 255, 0.3);
  transform: scale(1.1);
}

.btn-edit {
  background: rgba(255, 215, 0, 0.2);
  color: #ffd700;
}

.btn-edit:hover {
  background: rgba(255, 215, 0, 0.3);
  transform: scale(1.1);
}

.btn-suspend {
  background: rgba(255, 165, 0, 0.2);
  color: #ffa500;
}

.btn-suspend:hover {
  background: rgba(255, 165, 0, 0.3);
  transform: scale(1.1);
}

.btn-activate {
  background: rgba(0, 255, 128, 0.2);
  color: #00ff80;
}

.btn-activate:hover {
  background: rgba(0, 255, 128, 0.3);
  transform: scale(1.1);
}

.btn-delete {
  background: rgba(255, 77, 77, 0.2);
  color: #ff4d4d;
}

.btn-delete:hover {
  background: rgba(255, 77, 77, 0.3);
  transform: scale(1.1);
}

/* Pagination */
.pagination {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 12px;
  margin-top: 24px;
  padding-top: 24px;
  border-top: 1px solid rgba(255, 255, 255, 0.1);
}

.btn-page {
  background: rgba(255, 255, 255, 0.05);
  color: white;
  border: 1px solid rgba(255, 255, 255, 0.1);
  padding: 8px 12px;
  border-radius: 6px;
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  justify-content: center;
  min-width: 40px;
}

.btn-page:hover:not(:disabled) {
  background: rgba(255, 255, 255, 0.1);
  border-color: rgba(255, 255, 255, 0.2);
}

.btn-page:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.page-info {
  color: #a1a1a1;
  font-size: 0.9rem;
  margin: 0 16px;
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
  max-width: 700px;
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
.user-form {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
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

/* Enhanced Form Styling */
.user-form .form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 20px;
  margin-bottom: 20px;
}

.user-form .form-row:last-child {
  margin-bottom: 0;
}

.user-form .form-group.full-width {
  grid-column: 1 / -1;
}

.user-form .form-group label {
  display: block;
  margin-bottom: 8px;
  font-weight: 600;
  color: #00ff80;
  font-size: 0.95rem;
}

.user-form .form-control {
  width: 100%;
  padding: 12px 16px;
  border: 2px solid rgba(255, 255, 255, 0.1);
  border-radius: 8px;
  background: rgba(255, 255, 255, 0.05);
  color: white;
  font-size: 1rem;
  transition: all 0.3s ease;
}

.user-form .form-control:focus {
  outline: none;
  border-color: #00ff80;
  background: rgba(255, 255, 255, 0.08);
  box-shadow: 0 0 0 3px rgba(0, 255, 128, 0.1);
}

.user-form .form-control::placeholder {
  color: rgba(255, 255, 255, 0.5);
}

.user-form .form-control:invalid {
  border-color: #ff4757;
}

.user-form .form-help {
  display: block;
  margin-top: 6px;
  font-size: 0.85rem;
  color: rgba(255, 255, 255, 0.6);
  font-style: italic;
}

.user-form .file-input {
  padding: 10px;
  cursor: pointer;
}

.user-form .file-input::-webkit-file-upload-button {
  background: linear-gradient(135deg, #00ff80, #00cc66);
  color: #0d0d1a;
  border: none;
  padding: 8px 16px;
  border-radius: 6px;
  font-size: 0.9rem;
  font-weight: 600;
  cursor: pointer;
  margin-right: 12px;
}

.user-form .file-input::-webkit-file-upload-button:hover {
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(0, 255, 128, 0.3);
}

.user-form .form-actions {
  display: flex;
  gap: 16px;
  justify-content: flex-end;
  margin-top: 30px;
  padding-top: 24px;
  border-top: 1px solid rgba(255, 255, 255, 0.1);
}

.user-form .btn-secondary {
  background: rgba(255, 255, 255, 0.1);
  color: white;
  border: 1px solid rgba(255, 255, 255, 0.2);
  padding: 12px 24px;
  border-radius: 8px;
  font-size: 1rem;
  cursor: pointer;
  transition: all 0.3s ease;
}

.user-form .btn-secondary:hover {
  background: rgba(255, 255, 255, 0.2);
  transform: translateY(-1px);
}

.user-form .btn-primary {
  background: linear-gradient(135deg, #00ff80, #00cc66);
  color: #0d0d1a;
  border: none;
  padding: 12px 28px;
  border-radius: 8px;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  gap: 8px;
}

.user-form .btn-primary:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(0, 255, 128, 0.3);
}

.user-form .btn-primary:disabled {
  opacity: 0.6;
  cursor: not-allowed;
  transform: none;
}

.user-form .btn-primary i {
  font-size: 0.9rem;
}

/* Responsive Form */
@media (max-width: 768px) {
  .user-form .form-row {
    grid-template-columns: 1fr;
    gap: 16px;
  }
  
  .user-form .form-actions {
    flex-direction: column;
  }
  
  .user-form .btn-primary,
  .user-form .btn-secondary {
    width: 100%;
    justify-content: center;
  }
}

/* Responsive Design */
@media (max-width: 1200px) {
  .stats-grid {
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 16px;
  }
  
  .table-row {
    grid-template-columns: 2fr 2fr 1.5fr 1fr 1fr 1.5fr 1.2fr;
    gap: 12px;
  }
}

@media (max-width: 992px) {
  .user-management-screen {
    padding: 20px;
  }
  
  .page-header {
    padding: 20px;
    margin-bottom: 28px;
  }
  
  .header-content h1 {
    font-size: 2.2rem;
  }
  
  .search-filters {
    flex-direction: column;
    align-items: stretch;
    gap: 16px;
  }
  
  .search-box {
    min-width: auto;
  }
  
  .filter-options {
    justify-content: center;
  }
  
  .stats-grid {
    grid-template-columns: repeat(2, 1fr);
    gap: 16px;
  }
  
  .table-row {
    grid-template-columns: 1fr;
    gap: 8px;
    padding: 12px;
  }
  
  .header-row {
    display: none;
  }
  
  .col-user,
  .col-email,
  .col-phone,
  .col-role,
  .col-status,
  .col-joined,
  .col-actions {
    display: flex;
    flex-direction: column;
    gap: 4px;
  }
  
  .col-user::before {
    content: "User: ";
    font-weight: 600;
    color: #00ff80;
  }
  
  .col-email::before {
    content: "Email: ";
    font-weight: 600;
    color: #00ff80;
  }
  
  .col-phone::before {
    content: "Phone: ";
    font-weight: 600;
    color: #00ff80;
  }
  
  .col-role::before {
    content: "Role: ";
    font-weight: 600;
    color: #00ff80;
  }
  
  .col-status::before {
    content: "Status: ";
    font-weight: 600;
    color: #00ff80;
  }
  
  .col-joined::before {
    content: "Joined: ";
    font-weight: 600;
    color: #00ff80;
  }
  
  .col-actions::before {
    content: "Actions: ";
    font-weight: 600;
    color: #00ff80;
  }
}

@media (max-width: 768px) {
  .user-management-screen {
    padding: 16px;
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
  }
  
  .header-content p {
    font-size: 1rem;
  }
  
  .btn-primary {
    padding: 10px 20px;
    font-size: 0.95rem;
  }
  
  .stats-grid {
    grid-template-columns: 1fr;
    gap: 12px;
  }
  
  .stat-card {
    padding: 16px;
  }
  
  .stat-icon {
    width: 50px;
    height: 50px;
    font-size: 1.5rem;
  }
  
  .stat-content h3 {
    font-size: 1.8rem;
  }
  
  .users-container {
    padding: 20px;
  }
  
  .table-header {
    flex-direction: column;
    gap: 16px;
    text-align: center;
  }
  
  .form-row {
    grid-template-columns: 1fr;
    gap: 16px;
  }
  
  .modal-content {
    width: 95%;
    margin: 10px;
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

@media (max-width: 480px) {
  .user-management-screen {
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
  
  .search-filters {
    gap: 12px;
  }
  
  .filter-options {
    gap: 8px;
  }
  
  .filter-select {
    padding: 8px 12px;
    font-size: 0.85rem;
  }
  
  .btn-secondary {
    padding: 8px 12px;
    font-size: 0.85rem;
  }
  
  .stats-grid {
    gap: 10px;
  }
  
  .stat-card {
    padding: 14px;
  }
  
  .stat-icon {
    width: 45px;
    height: 45px;
    font-size: 1.3rem;
  }
  
  .stat-content h3 {
    font-size: 1.6rem;
  }
  
  .stat-content p {
    font-size: 0.85rem;
  }
  
  .users-container {
    padding: 16px;
    border-radius: 12px;
  }
  
  .table-header h3 {
    font-size: 1.2rem;
  }
  
  .btn-export {
    padding: 6px 12px;
    font-size: 0.85rem;
  }
  
  .table-row {
    padding: 10px;
    gap: 6px;
  }
  
  .user-avatar {
    width: 35px;
    height: 35px;
  }
  
  .user-details h4 {
    font-size: 0.95rem;
  }
  
  .user-id {
    font-size: 0.75rem;
  }
  
  .role-badge,
  .status-badge {
    padding: 3px 8px;
    font-size: 0.75rem;
  }
  
  .btn-action {
    width: 32px;
    height: 32px;
    font-size: 0.8rem;
  }
  
  .pagination {
    gap: 8px;
    margin-top: 20px;
    padding-top: 20px;
  }
  
  .btn-page {
    padding: 6px 10px;
    min-width: 36px;
    font-size: 0.85rem;
  }
  
  .page-info {
    font-size: 0.85rem;
    margin: 0 12px;
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
  
  .user-form {
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
  
  .no-users {
    padding: 40px 16px;
  }
  
  .no-users-icon {
    font-size: 3rem;
    margin-bottom: 12px;
  }
  
  .no-users h3 {
    font-size: 1.2rem;
  }
  
  .no-users p {
    font-size: 1rem;
  }
}

/* Touch Device Optimizations */
@media (hover: none) and (pointer: coarse) {
  .btn-primary:hover,
  .btn-secondary:hover,
  .btn-action:hover,
  .btn-page:hover {
    transform: none;
  }
  
  .btn-primary:active,
  .btn-secondary:active,
  .btn-action:active,
  .btn-page:active {
    transform: scale(0.95);
  }
  
  .stat-card:hover {
    transform: none;
  }
  
  .stat-card:active {
    transform: scale(0.98);
  }
}

/* High DPI Displays */
@media (-webkit-min-device-pixel-ratio: 2), (min-resolution: 192dpi) {
  .user-avatar {
    image-rendering: -webkit-optimize-contrast;
    image-rendering: crisp-edges;
  }
}

/* Print Styles */
@media print {
  .user-management-screen {
    background: white;
    color: black;
  }
  
  .page-header,
  .search-filters,
  .stats-grid,
  .table-header,
  .col-actions,
  .pagination,
  .modal-overlay {
    display: none;
  }
  
  .table-row {
    border: 1px solid #ccc;
    break-inside: avoid;
  }
}

/* Accessibility Improvements */
@media (prefers-reduced-motion: reduce) {
  .btn-primary,
  .btn-secondary,
  .btn-action,
  .btn-page,
  .stat-card,
  .modal-content {
    transition: none;
  }
  
  .btn-primary:hover,
  .btn-secondary:hover,
  .btn-action:hover,
  .btn-page:hover,
  .stat-card:hover {
    transform: none;
  }
}

/* Dark Mode Support */
@media (prefers-color-scheme: dark) {
  .user-management-screen {
    background: linear-gradient(135deg, #0d0d1a 0%, #101022 100%);
  }
}

.btn-refresh {
  background: rgba(0, 255, 128, 0.2);
  color: #00ff80;
  border: 1px solid rgba(0, 255, 128, 0.3);
  padding: 8px 16px;
  border-radius: 6px;
  font-size: 0.9rem;
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  gap: 6px;
  margin-right: 12px;
}

.btn-refresh:hover:not(:disabled) {
  background: rgba(0, 255, 128, 0.3);
  border-color: rgba(0, 255, 128, 0.5);
}

.btn-refresh:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.fa-spin {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}

/* New styles for View User Modal */
.user-details-view {
  display: flex;
  align-items: center;
  gap: 20px;
  margin-bottom: 24px;
  padding-bottom: 24px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.user-avatar-large {
  width: 100px;
  height: 100px;
  border-radius: 50%;
  border: 3px solid #00ff80;
  overflow: hidden;
  flex-shrink: 0;
}

.user-avatar-large img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.user-info-grid {
  flex-grow: 1;
}

.info-row {
  display: flex;
  justify-content: space-between;
  margin-bottom: 12px;
  font-size: 1.1rem;
  color: #e0e0e0;
}

.info-label {
  font-weight: 600;
  color: #00ff80;
}

.info-value {
  font-weight: 400;
  color: #a1a1a1;
}

.balance-value {
  font-size: 1.2rem;
  font-weight: 700;
  color: #00ff80 !important;
  text-shadow: 0 0 10px rgba(0, 255, 128, 0.3);
}

.bank-details-section {
  margin-top: 20px;
  padding: 20px;
  background: linear-gradient(135deg, rgba(0, 255, 128, 0.05) 0%, rgba(0, 212, 170, 0.05) 100%);
  border: 1px solid rgba(0, 255, 128, 0.2);
  border-radius: 12px;
  box-shadow: 0 4px 20px rgba(0, 255, 128, 0.1);
}

.section-title {
  color: #00ff80;
  font-size: 1.1rem;
  font-weight: 600;
  margin: 0 0 16px 0;
  display: flex;
  align-items: center;
  gap: 8px;
}

.modal-actions {
  display: flex;
  gap: 12px;
  justify-content: flex-end;
  margin-top: 24px;
  padding-top: 24px;
  border-top: 1px solid rgba(255, 255, 255, 0.1);
}

.btn-secondary.modal-action-btn {
  background: rgba(255, 255, 255, 0.1);
  color: white;
  border: 1px solid rgba(255, 255, 255, 0.2);
  padding: 10px 16px;
  border-radius: 6px;
  font-size: 0.9rem;
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  gap: 6px;
}

.btn-secondary.modal-action-btn:hover {
  background: rgba(255, 255, 255, 0.2);
}

.btn-primary.modal-action-btn {
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

.btn-primary.modal-action-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(0, 255, 128, 0.3);
}

</style>
