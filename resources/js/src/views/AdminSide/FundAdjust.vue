<template>
  <div class="fund-adjust-screen">
    <!-- Floating Particles -->
    <div class="floating-particles">
      <div class="particle" v-for="i in 20" :key="i" :style="{ 
        left: Math.random() * 100 + '%', 
        animationDelay: Math.random() * 20 + 's',
        animationDuration: (Math.random() * 10 + 10) + 's'
      }"></div>
    </div>

    <!-- Header Section -->
    <div class="page-header">
      <div class="header-content">
        <h1 class="page-title">⚖️ Fund Adjust</h1>
        <p class="page-subtitle">Adjust user wallet balances and manage funds</p>
      </div>
      <div class="header-actions">
        <button 
          class="btn-recent-adjustments" 
          @click="openRecentAdjustmentsModal"
        >
          📊 Recent Adjustments
        </button>
      </div>
    </div>

    <!-- User List Section -->
    <div class="users-section">
      <h3 class="section-title">Select User to Adjust Balance</h3>
      <div v-if="loading" class="loading-state">
        <i class="fa-solid fa-spinner fa-spin"></i>
        Loading users...
      </div>
      <div v-else-if="error" class="error-state">
        <i class="fa-solid fa-exclamation-triangle"></i>
        {{ error }}
      </div>
      <div v-else class="users-grid">
        <div 
          v-for="user in users" 
          :key="user.id"
          class="user-card"
          :class="{ 'selected': selectedUser && selectedUser.id === user.id }"
          @click="selectUser(user)"
        >
          <div class="user-avatar">
            <img 
              v-if="user.profile_photo && user.profile_photo.trim() !== '' && getProfileImageUrl(user.profile_photo)"
              :src="getProfileImageUrl(user.profile_photo)" 
              :alt="user.name" 
              @error="handleProfileImageError($event, user.name)"
            />
            <div 
              v-else
              class="avatar-fallback"
            >
              {{ user.name ? user.name.charAt(0).toUpperCase() : '👤' }}
            </div>
          </div>
          <div class="user-info">
            <div class="user-id">ID: {{ user.id }}</div>
            <h4 class="user-name">{{ user.name }}</h4>
            <p class="user-email">{{ user.email }}</p>
            <div class="user-balance">₹{{ user.total_balance || '0.00' }}</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Balance Update Modal -->
    <div v-if="showBalanceModal" class="modal-overlay" @click="closeBalanceModal">
      <div class="modal-content" @click.stop>
        <div class="modal-header">
          <h3>Update Balance - {{ selectedUser?.name }}</h3>
          <button class="close-btn" @click="closeBalanceModal">&times;</button>
        </div>
        
        <div class="modal-body">
          <div class="user-info-section">
            <div class="user-avatar-large">
              <img 
                v-if="selectedUser?.profile_photo && selectedUser.profile_photo.trim() !== '' && getProfileImageUrl(selectedUser.profile_photo)"
                :src="getProfileImageUrl(selectedUser.profile_photo)" 
                :alt="selectedUser?.name" 
                @error="handleProfileImageError($event, selectedUser?.name)"
              />
              <div 
                v-else
                class="avatar-fallback-large"
              >
                {{ selectedUser?.name ? selectedUser.name.charAt(0).toUpperCase() : '👤' }}
              </div>
            </div>
            <div class="user-details">
              <h4 class="user-name">{{ selectedUser?.name }}</h4>
              <p class="user-email">{{ selectedUser?.email }}</p>
              <p class="user-id">ID: {{ selectedUser?.id }}</p>
            </div>
          </div>

          <div class="balance-fields">
            <div class="form-group">
              <label class="form-label">Current Balance (Locked)</label>
              <input 
                type="text" 
                :value="selectedUser?.total_balance || '0.00'"
                class="form-input locked-field"
                readonly
                disabled
              />
              <small class="field-note">This field cannot be changed</small>
            </div>

            <div class="form-group">
              <label class="form-label">Updated Balance (₹)</label>
              <input 
                type="number" 
                v-model="updatedBalance" 
                class="form-input"
                placeholder="Enter new balance amount"
                min="0"
                step="0.01"
                @input="calculateAdjustment"
                ref="balanceInput"
              />
              <small class="field-note">Enter the new balance amount</small>
            </div>
          </div>

          <div class="adjustment-preview" v-if="adjustmentAmount !== 0">
            <div class="preview-item">
              <span class="preview-label">Adjustment Amount:</span>
              <span class="preview-value" :class="adjustmentAmount > 0 ? 'positive' : 'negative'">
                {{ adjustmentAmount > 0 ? '+' : '' }}₹{{ Math.abs(adjustmentAmount).toFixed(2) }}
              </span>
            </div>
            <div class="preview-item">
              <span class="preview-label">New Balance:</span>
              <span class="preview-value">₹{{ updatedBalance || '0.00' }}</span>
            </div>
          </div>
        </div>

        <div class="modal-actions">
          <button class="btn-secondary" @click="closeBalanceModal">Cancel</button>
          <button 
            class="btn-primary" 
            @click="submitAdjustment"
            :disabled="!isFormValid || submitting"
          >
            <i class="fa-solid fa-check" :class="{ 'fa-spin': submitting }"></i>
            {{ submitting ? 'Updating...' : 'Update Balance' }}
          </button>
        </div>
      </div>
    </div>


    <!-- Recent Adjustments Modal -->
    <div v-if="showRecentAdjustmentsModal" class="modal-overlay" @click="closeRecentAdjustmentsModal" @keydown.esc="closeRecentAdjustmentsModal">
      <div class="modal-content recent-adjustments-modal" @click.stop>
        <div class="modal-header">
          <h3>📊 Recent Adjustments</h3>
          <button class="close-btn" @click="closeRecentAdjustmentsModal">&times;</button>
        </div>
        
        <div class="modal-body">
          <div v-if="loadingRecentAdjustments" class="loading-state">
            <i class="fa-solid fa-spinner fa-spin"></i>
            <p>Loading recent adjustments...</p>
          </div>
          
          <div v-else-if="recentAdjustments.length === 0" class="no-adjustments">
            <i class="fa-solid fa-info-circle"></i>
            <p>No recent adjustments found</p>
          </div>
          
          <div v-else class="adjustments-list">
            <div 
              v-for="adjustment in recentAdjustments" 
              :key="adjustment.id"
              class="adjustment-item"
            >
              <div class="adjustment-header">
                <div class="user-info">
                  <div class="user-avatar-small">
                    <img 
                      v-if="adjustment.user?.profile_photo && adjustment.user.profile_photo.trim() !== ''"
                      :src="getProfileImageUrl(adjustment.user.profile_photo)" 
                      :alt="adjustment.user?.name"
                      @error="handleProfileImageError($event, adjustment.user?.name)"
                    />
                    <div v-else class="avatar-fallback-small">
                      {{ adjustment.user?.name ? adjustment.user.name.charAt(0).toUpperCase() : '👤' }}
                    </div>
                  </div>
                  <div class="user-details">
                    <h4 class="user-name">{{ adjustment.user?.name || 'Unknown User' }}</h4>
                    <p class="user-id">ID: {{ adjustment.user?.id || 'N/A' }}</p>
                  </div>
                </div>
                <div class="adjustment-type" :class="getAdjustmentTypeClass(adjustment.adjustment_type)">
                  {{ adjustment.adjustment_type === 'add' ? '+' : adjustment.adjustment_type === 'subtract' ? '-' : 'Set' }}
                </div>
              </div>
              
              <div class="adjustment-details">
                <div class="amount-section">
                  <span class="amount-label">Amount:</span>
                  <span class="amount-value">₹{{ adjustment.amount }}</span>
                </div>
                <div class="balance-section">
                  <span class="balance-label">New Balance:</span>
                  <span class="balance-value">₹{{ adjustment.running_balance }}</span>
                </div>
                <div class="description-section">
                  <span class="description-label">Description:</span>
                  <span class="description-value">{{ adjustment.description || 'Balance adjustment by admin' }}</span>
                </div>
                <div class="timestamp-section">
                  <span class="timestamp-label">Date:</span>
                  <span class="timestamp-value">{{ formatDate(adjustment.created_at) }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>
        
        <div class="modal-footer">
          <button class="btn-secondary" @click="closeRecentAdjustmentsModal">
            Close
          </button>
        </div>
      </div>
    </div>

    <!-- Toast Notification -->
    <div v-if="showToast" class="toast-notification">
      <div class="toast-content">
        <i class="fa-solid fa-check-circle"></i>
        <span>{{ toastMessage }}</span>
      </div>
    </div>

    <!-- Mobile Navigation Component -->
    <AdminMobileNav />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import AdminMobileNav from '../../components/AdminMobileNav.vue'

const router = useRouter()

// Reactive Data
const users = ref([])
const selectedUser = ref(null)
const updatedBalance = ref('')
const adjustmentAmount = ref(0)
const loading = ref(false)
const submitting = ref(false)
const error = ref(null)
const recentAdjustments = ref([])
const showBalanceModal = ref(false)
const balanceInput = ref(null)
const showRecentAdjustmentsModal = ref(false)
const loadingRecentAdjustments = ref(false)
const toastMessage = ref('')
const showToast = ref(false)

// Computed Properties
const isFormValid = computed(() => {
  return updatedBalance.value && 
         parseFloat(updatedBalance.value) >= 0 && 
         parseFloat(updatedBalance.value) !== parseFloat(selectedUser.value?.total_balance || 0)
})

// Methods
const fetchUsers = async () => {
  loading.value = true
  error.value = null
  try {
    const token = localStorage.getItem('access_token')
    if (!token) {
      throw new Error('No access token found. Please login again.')
    }

    const response = await fetch(`http://localhost:8000/api/users`, {
      method: 'GET',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      }
    })

    if (!response.ok) {
      throw new Error('Failed to fetch users')
    }

    const result = await response.json()
    
    if (result.success && result.data) {
      // Filter out admin users, only show regular users
      users.value = result.data
        .filter(user => !user.is_admin)
        .map(user => {
          console.log(`User ${user.name} profile_image:`, user.profile_image)
          return {
            ...user,
            // Don't set avatar here, let template handle it
          }
        })
    } else {
      throw new Error(result.message || 'Failed to fetch users')
    }
  } catch (err) {
    console.error('Error fetching users:', err)
    error.value = err.message
  } finally {
    loading.value = false
  }
}

const selectUser = (user) => {
  selectedUser.value = user
  updatedBalance.value = user.total_balance || '0.00'
  adjustmentAmount.value = 0
  showBalanceModal.value = true
  
  // Focus on the balance input after modal opens
  setTimeout(() => {
    if (balanceInput.value) {
      balanceInput.value.focus()
    }
  }, 100)
}

const calculateAdjustment = () => {
  if (selectedUser.value && updatedBalance.value) {
    const currentBalance = parseFloat(selectedUser.value.total_balance || 0)
    const newBalance = parseFloat(updatedBalance.value)
    adjustmentAmount.value = newBalance - currentBalance
  }
}

const submitAdjustment = async () => {
  if (!isFormValid.value) return
  
  submitting.value = true
  try {
    const token = localStorage.getItem('access_token')
    if (!token) {
      throw new Error('No access token found. Please login again.')
    }

    const response = await fetch(`http://localhost:8000/api/admin/fund-adjust`, {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify({
        user_id: selectedUser.value.id,
        type: 'set', // Always set the balance to the new value
        amount: parseFloat(updatedBalance.value),
        reason: 'Balance adjustment by admin'
      })
    })

    if (!response.ok) {
      throw new Error('Failed to update balance')
    }

    const result = await response.json()
    
    if (result.success) {
      // Show success toast
      showToastNotification(`Balance updated successfully! New balance: ₹${updatedBalance.value}`, 'success')
      
      // Update the user's balance in the local state
      selectedUser.value.total_balance = parseFloat(updatedBalance.value)
      // Update the user in the users list
      const userIndex = users.value.findIndex(u => u.id === selectedUser.value.id)
      if (userIndex !== -1) {
        users.value[userIndex].total_balance = parseFloat(updatedBalance.value)
      }
      closeBalanceModal()
      fetchRecentAdjustments()
    } else {
      throw new Error(result.message || 'Failed to update balance')
    }
  } catch (error) {
    console.error('Error updating balance:', error)
    showToastNotification(`Error updating balance: ${error.message}`, 'error')
  } finally {
    submitting.value = false
  }
}

const closeBalanceModal = () => {
  showBalanceModal.value = false
  selectedUser.value = null
  updatedBalance.value = ''
  adjustmentAmount.value = 0
}

const openRecentAdjustmentsModal = async () => {
  showRecentAdjustmentsModal.value = true
  loadingRecentAdjustments.value = true
  try {
    // Fetch fresh data when opening modal
    await fetchRecentAdjustments()
  } finally {
    loadingRecentAdjustments.value = false
  }
}

const closeRecentAdjustmentsModal = () => {
  showRecentAdjustmentsModal.value = false
}

const showToastNotification = (message, type = 'success') => {
  toastMessage.value = message
  showToast.value = true
  
  setTimeout(() => {
    showToast.value = false
  }, 3000)
}

const clearForm = () => {
  selectedUser.value = null
  updatedBalance.value = ''
  adjustmentAmount.value = 0
  showBalanceModal.value = false
}

const fetchRecentAdjustments = async () => {
  try {
    const token = localStorage.getItem('access_token')
    if (!token) return

    const response = await fetch(`http://localhost:8000/api/admin/fund-adjustments`, {
      method: 'GET',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      }
    })

    if (response.ok) {
      const result = await response.json()
      if (result.success) {
        recentAdjustments.value = result.data || []
      }
    }
  } catch (error) {
    console.error('Error fetching recent adjustments:', error)
  }
}

const getProfileImageUrl = (profileImage) => {
  console.log('getProfileImageUrl called with:', profileImage)
  // Only return URL if we have a valid profile image
  if (!profileImage || profileImage === '' || profileImage === null) {
    console.log('No valid profile image, returning null')
    return null
  }
  
  if (profileImage.startsWith('http')) {
    console.log('Profile image is already a full URL:', profileImage)
    return profileImage
  }
  
  // Only return local URL if profile image exists
  const url = `http://localhost:8000/${profileImage}`
  console.log('Constructed local URL:', url)
  return url
}

const handleProfileImageError = (event, userName) => {
  console.log('Image load error for user:', userName)
  // Hide the image and show a fallback div instead
  event.target.style.display = 'none'
  const fallbackDiv = event.target.nextElementSibling
  if (fallbackDiv) {
    fallbackDiv.style.display = 'flex'
  }
  // Prevent any further image loading attempts
  event.preventDefault()
  return false
}

const getAdjustmentTypeClass = (type) => {
  switch (type) {
    case 'add': return 'type-add'
    case 'subtract': return 'type-subtract'
    case 'set': return 'type-set'
    default: return ''
  }
}

const formatDate = (dateString) => {
  if (!dateString) return 'N/A'
  const date = new Date(dateString)
  return date.toLocaleDateString('en-GB', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
    second: '2-digit'
  })
}

const getAdjustmentTypeText = (type) => {
  switch (type) {
    case 'add': return '+ Add'
    case 'subtract': return '- Subtract'
    case 'set': return '= Set'
    default: return type
  }
}


// Lifecycle
onMounted(() => {
  fetchUsers()
  fetchRecentAdjustments()
})
</script>

<style scoped>
/* Mobile-First Responsive Design */
.fund-adjust-screen {
  background: linear-gradient(135deg, #0f0f23, #1a1a2e, #16213e);
  background-attachment: fixed;
  color: white;
  min-height: 100vh;
  padding: 20px;
  width: 100%;
  max-width: 100vw;
  overflow-x: hidden;
  box-sizing: border-box;
  position: relative;
}

.fund-adjust-screen::before {
  content: '';
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: 
    radial-gradient(circle at 20% 80%, rgba(0, 255, 128, 0.08) 0%, transparent 50%),
    radial-gradient(circle at 80% 20%, rgba(0, 255, 128, 0.05) 0%, transparent 50%),
    radial-gradient(circle at 40% 40%, rgba(0, 255, 128, 0.03) 0%, transparent 50%);
  pointer-events: none;
  z-index: 0;
}

/* Floating Particles */
.floating-particles {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  pointer-events: none;
  z-index: 1;
}

.particle {
  position: absolute;
  width: 4px;
  height: 4px;
  background: rgba(0, 255, 128, 0.6);
  border-radius: 50%;
  animation: float linear infinite;
  box-shadow: 0 0 10px rgba(0, 255, 128, 0.5);
}

@keyframes float {
  0% {
    transform: translateY(100vh) rotate(0deg);
    opacity: 0;
  }
  10% {
    opacity: 1;
  }
  90% {
    opacity: 1;
  }
  100% {
    transform: translateY(-100px) rotate(360deg);
    opacity: 0;
  }
}

.page-header {
  margin-bottom: 32px;
  text-align: left;
  width: 100%;
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 20px;
  background: linear-gradient(145deg, rgba(0, 255, 128, 0.05), rgba(0, 255, 128, 0.02));
  border: 1px solid rgba(0, 255, 128, 0.1);
  border-radius: 20px;
  padding: 24px 32px;
  backdrop-filter: blur(10px);
  position: relative;
  z-index: 1;
  box-shadow: 0 8px 32px rgba(0, 255, 128, 0.1);
}

.page-title {
  font-size: 28px;
  font-weight: 800;
  color: #00ff80;
  margin: 0 0 8px 0;
  line-height: 1.2;
  text-shadow: 0 0 20px rgba(0, 255, 128, 0.3);
  background: linear-gradient(135deg, #00ff80, #00cc66);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.page-subtitle {
  font-size: 16px;
  color: #b0b0b0;
  margin: 0;
  line-height: 1.4;
  font-weight: 400;
}

.header-actions {
  display: flex;
  gap: 12px;
  align-items: center;
}

.btn-recent-adjustments {
  background: linear-gradient(135deg, #00ff80, #00cc66);
  color: #000;
  border: none;
  padding: 14px 24px;
  border-radius: 30px;
  font-size: 15px;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
  display: flex;
  align-items: center;
  gap: 10px;
  box-shadow: 0 6px 20px rgba(0, 255, 128, 0.4);
  position: relative;
  overflow: hidden;
}

.btn-recent-adjustments::before {
  content: '';
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
  transition: left 0.5s;
}

.btn-recent-adjustments:hover::before {
  left: 100%;
}

.btn-recent-adjustments:hover {
  transform: translateY(-3px) scale(1.05);
  box-shadow: 0 10px 25px rgba(0, 255, 128, 0.5);
  background: linear-gradient(135deg, #00cc66, #00ff80);
}

/* Users Section */
.users-section {
  margin-bottom: 32px;
  position: relative;
  z-index: 1;
}

.section-title {
  color: #00ff80;
  font-size: 22px;
  margin: 0 0 20px 0;
  font-weight: 700;
  text-align: center;
  text-shadow: 0 0 15px rgba(0, 255, 128, 0.3);
  background: linear-gradient(135deg, #00ff80, #00cc66);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.loading-state,
.error-state {
  text-align: center;
  padding: 40px 20px;
  color: #9ca3af;
}

.loading-state i,
.error-state i {
  font-size: 24px;
  margin-bottom: 12px;
  display: block;
}

.error-state {
  color: #ff6b6b;
}

.users-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 20px;
  padding: 0 10px;
}

.user-card {
  background: linear-gradient(145deg, rgba(16, 16, 34, 0.8), rgba(13, 13, 26, 0.9));
  border: 1px solid rgba(0, 255, 128, 0.2);
  border-radius: 20px;
  padding: 28px;
  cursor: pointer;
  transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
  min-height: 260px;
  justify-content: space-between;
  gap: 18px;
  position: relative;
  overflow: hidden;
  backdrop-filter: blur(10px);
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
}

.user-card::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: linear-gradient(135deg, rgba(0, 255, 128, 0.05), transparent);
  opacity: 0;
  transition: opacity 0.3s ease;
  pointer-events: none;
}

.user-card:hover::before {
  opacity: 1;
}

.user-card:hover {
  border-color: #00ff80;
  transform: translateY(-8px) scale(1.02);
  box-shadow: 0 20px 40px rgba(0, 255, 128, 0.3);
  background: linear-gradient(145deg, rgba(15, 23, 42, 0.9), rgba(11, 11, 22, 0.95));
}

.user-card.selected {
  border-color: #00ff80;
  background: linear-gradient(145deg, #0f172a, #0b0b16);
  box-shadow: 0 6px 16px rgba(0, 255, 128, 0.3);
}

.user-avatar {
  width: 80px;
  height: 80px;
  border-radius: 50%;
  border: 3px solid #00ff80;
  overflow: hidden;
  flex-shrink: 0;
  box-shadow: 0 8px 20px rgba(0, 255, 128, 0.4);
  transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
  position: relative;
}

.user-avatar::before {
  content: '';
  position: absolute;
  top: -3px;
  left: -3px;
  right: -3px;
  bottom: -3px;
  border-radius: 50%;
  background: linear-gradient(45deg, #00ff80, #00cc66, #00ff80);
  z-index: -1;
  animation: rotate 3s linear infinite;
}

@keyframes rotate {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}

.user-avatar img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.avatar-fallback {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, #00ff80, #00cc66);
  color: #000;
  font-size: 24px;
  font-weight: bold;
  border-radius: 50%;
}

.user-card:hover .user-avatar {
  transform: scale(1.1);
  box-shadow: 0 6px 16px rgba(0, 255, 128, 0.5);
}

.user-info {
  width: 100%;
  min-width: 0;
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.user-id {
  color: #00ff80;
  font-size: 12px;
  font-weight: 600;
  background: rgba(0, 255, 128, 0.1);
  padding: 4px 8px;
  border-radius: 12px;
  border: 1px solid rgba(0, 255, 128, 0.3);
  display: inline-block;
  align-self: center;
  margin-bottom: 4px;
}

.user-name {
  margin: 0;
  color: white;
  font-size: 18px;
  font-weight: 700;
  line-height: 1.2;
  word-break: break-word;
  margin-bottom: 4px;
}

.user-email {
  margin: 0;
  color: #9ca3af;
  font-size: 14px;
  word-break: break-all;
  line-height: 1.3;
  margin-bottom: 8px;
  flex: 1;
}

.user-balance {
  margin: 0;
  color: #00ff80;
  font-size: 20px;
  font-weight: 900;
  background: linear-gradient(135deg, rgba(0, 255, 128, 0.2), rgba(0, 255, 128, 0.1));
  padding: 12px 20px;
  border-radius: 30px;
  border: 2px solid rgba(0, 255, 128, 0.5);
  display: inline-block;
  align-self: center;
  box-shadow: 0 4px 15px rgba(0, 255, 128, 0.3);
  text-shadow: 0 0 10px rgba(0, 255, 128, 0.5);
  position: relative;
  overflow: hidden;
}

.user-balance::before {
  content: '';
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
  transition: left 0.6s;
}

.user-card:hover .user-balance::before {
  left: 100%;
}

/* User Selection */
.user-selection {
  margin-bottom: 24px;
}

.selected-user-card {
  background: linear-gradient(145deg, #101022, #0d0d1a);
  border: 1px solid rgba(0, 255, 128, 0.2);
  border-radius: 12px;
  padding: 20px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 16px;
}

.user-info {
  display: flex;
  align-items: center;
  gap: 16px;
}

.user-avatar {
  width: 60px;
  height: 60px;
  border-radius: 50%;
  border: 3px solid #00ff80;
  object-fit: cover;
}

.user-details h3 {
  margin: 0 0 4px 0;
  color: white;
  font-size: 18px;
}

.user-details p {
  margin: 0;
  color: #9ca3af;
  font-size: 14px;
}

.current-balance {
  text-align: right;
}

.balance-label {
  display: block;
  color: #9ca3af;
  font-size: 14px;
  margin-bottom: 4px;
}

.balance-amount {
  color: #00ff80;
  font-size: 24px;
  font-weight: bold;
}

/* Adjustment Form */
.adjustment-form {
  margin-bottom: 32px;
}

.form-container {
  background: linear-gradient(145deg, #101022, #0d0d1a);
  border: 1px solid rgba(0, 255, 128, 0.2);
  border-radius: 12px;
  padding: 24px;
}

.form-title {
  color: #00ff80;
  font-size: 20px;
  margin: 0 0 20px 0;
}

.form-group {
  margin-bottom: 20px;
}

.form-label {
  display: block;
  color: #e5e7eb;
  font-size: 14px;
  font-weight: 500;
  margin-bottom: 8px;
}

.form-select,
.form-input,
.form-textarea {
  width: 100%;
  background: rgba(0, 0, 0, 0.3);
  border: 1px solid rgba(0, 255, 128, 0.3);
  border-radius: 8px;
  padding: 12px;
  color: white;
  font-size: 16px;
  outline: none;
  transition: border-color 0.3s ease;
}

.form-select:focus,
.form-input:focus,
.form-textarea:focus {
  border-color: #00ff80;
}

.form-textarea {
  resize: vertical;
  min-height: 80px;
}

/* Balance Fields */
.balance-fields {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 20px;
  margin-bottom: 20px;
}

.locked-field {
  background: rgba(0, 0, 0, 0.5) !important;
  color: #6b7280 !important;
  cursor: not-allowed !important;
  border-color: #374151 !important;
}

.field-note {
  display: block;
  color: #6b7280;
  font-size: 12px;
  margin-top: 4px;
}

/* Adjustment Preview */
.adjustment-preview {
  background: rgba(0, 255, 128, 0.1);
  border: 1px solid rgba(0, 255, 128, 0.3);
  border-radius: 8px;
  padding: 16px;
  margin-bottom: 20px;
}

.preview-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 8px;
}

.preview-item:last-child {
  margin-bottom: 0;
}

.preview-label {
  color: #9ca3af;
  font-size: 14px;
}

.preview-value {
  font-weight: 600;
  font-size: 16px;
}

.preview-value.positive {
  color: #00ff80;
}

.preview-value.negative {
  color: #ff6b6b;
}

.form-actions {
  display: flex;
  gap: 12px;
  justify-content: flex-end;
  margin-top: 24px;
}

.btn-primary,
.btn-secondary {
  padding: 12px 24px;
  border-radius: 8px;
  font-size: 16px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.3s ease;
  border: none;
  display: flex;
  align-items: center;
  gap: 8px;
}

.btn-primary {
  background: linear-gradient(145deg, #00ff80, #00cc66);
  color: #000;
}

.btn-primary:hover:not(:disabled) {
  background: linear-gradient(145deg, #00cc66, #00ff80);
  transform: translateY(-2px);
}

.btn-primary:disabled {
  opacity: 0.5;
  cursor: not-allowed;
  transform: none;
}

.btn-secondary {
  background: transparent;
  color: #9ca3af;
  border: 1px solid #6b7280;
}

.btn-secondary:hover {
  background: rgba(107, 114, 128, 0.1);
  color: white;
}

/* Modal Styles */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.8);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  padding: 20px;
}

.modal-content {
  background: linear-gradient(145deg, #1e293b, #0f172a);
  border: 1px solid rgba(0, 255, 128, 0.3);
  border-radius: 16px;
  width: 100%;
  max-width: 500px;
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px 24px;
  border-bottom: 1px solid rgba(0, 255, 128, 0.2);
}

.modal-header h3 {
  margin: 0;
  color: #00ff80;
  font-size: 20px;
  font-weight: 600;
}

.close-btn {
  background: none;
  border: none;
  color: #9ca3af;
  font-size: 24px;
  cursor: pointer;
  padding: 0;
  width: 30px;
  height: 30px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  transition: all 0.3s ease;
}

.close-btn:hover {
  background: rgba(255, 77, 77, 0.2);
  color: #ff4d4d;
}

.modal-body {
  padding: 24px;
}

.user-info-section {
  display: flex;
  align-items: center;
  gap: 16px;
  margin-bottom: 24px;
  padding: 16px;
  background: rgba(0, 255, 128, 0.05);
  border-radius: 12px;
  border: 1px solid rgba(0, 255, 128, 0.1);
}

.user-avatar-large {
  width: 60px;
  height: 60px;
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

.avatar-fallback-large {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, #00ff80, #00cc66);
  color: #000;
  font-size: 32px;
  font-weight: bold;
  border-radius: 50%;
}

.user-details h4 {
  margin: 0 0 4px 0;
  color: white;
  font-size: 18px;
  font-weight: 600;
}

.user-details p {
  margin: 0;
  color: #9ca3af;
  font-size: 14px;
}

.modal-actions {
  display: flex;
  gap: 12px;
  justify-content: flex-end;
  padding: 20px 24px;
  border-top: 1px solid rgba(0, 255, 128, 0.2);
}

/* Responsive Design */
@media (max-width: 768px) {
  .users-grid {
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 16px;
    padding: 0 5px;
  }
  
  .balance-fields {
    grid-template-columns: 1fr;
    gap: 16px;
  }
  
  .user-card {
    min-height: 220px;
    padding: 20px;
    gap: 12px;
  }
  
  .user-avatar {
    width: 65px;
    height: 65px;
  }
  
  .user-name {
    font-size: 16px;
  }
  
  .user-email {
    font-size: 13px;
  }
  
  .user-balance {
    font-size: 16px;
    padding: 6px 12px;
  }
  
  .user-id {
    font-size: 11px;
    padding: 3px 6px;
  }
  
  .form-actions {
    flex-direction: column;
  }
}

@media (max-width: 480px) {
  .users-grid {
    grid-template-columns: 1fr;
    gap: 12px;
    padding: 0;
  }
  
  .user-card {
    min-height: 200px;
    padding: 16px;
    gap: 10px;
  }
  
  .user-avatar {
    width: 60px;
    height: 60px;
  }
  
  .user-name {
    font-size: 15px;
  }
  
  .user-email {
    font-size: 12px;
  }
  
  .user-balance {
    font-size: 15px;
    padding: 5px 10px;
  }
  
  .user-id {
    font-size: 10px;
    padding: 2px 5px;
  }
  
  .btn-primary,
  .btn-secondary {
    width: 100%;
    justify-content: center;
  }
  
  .modal-content {
    margin: 10px;
    max-height: 95vh;
  }
  
  .modal-header {
    padding: 16px 20px;
  }
  
  .modal-body {
    padding: 20px;
  }
  
  .modal-actions {
    padding: 16px 20px;
    flex-direction: column;
  }
  
  .user-info-section {
    flex-direction: column;
    text-align: center;
    gap: 12px;
  }
}

@media (min-width: 768px) {
  .fund-adjust-screen {
    padding: 24px;
  }
  
  .page-title {
    font-size: 32px;
  }
  
  .selected-user-card {
    flex-direction: row;
  }
  
  .form-actions {
    justify-content: flex-start;
  }
}

@media (min-width: 1024px) {
  .fund-adjust-screen {
    max-width: 1200px;
    margin: 0 auto;
  }
  
  .users-grid {
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 24px;
  }
  
  .user-card {
    min-height: 260px;
    padding: 28px;
  }
  
  .user-avatar {
    width: 85px;
    height: 85px;
  }
  
  .user-name {
    font-size: 20px;
  }
  
  .user-email {
    font-size: 15px;
  }
  
  .user-balance {
    font-size: 20px;
    padding: 10px 20px;
  }
  
  .user-id {
    font-size: 13px;
    padding: 5px 10px;
  }
}

/* Recent Adjustments Modal */
.recent-adjustments-modal {
  max-width: 800px;
  max-height: 80vh;
  overflow-y: auto;
}

.loading-state {
  text-align: center;
  padding: 40px 20px;
  color: #9ca3af;
}

.loading-state i {
  font-size: 48px;
  margin-bottom: 16px;
  color: #00ff80;
}

.no-adjustments {
  text-align: center;
  padding: 40px 20px;
  color: #9ca3af;
}

.no-adjustments i {
  font-size: 48px;
  margin-bottom: 16px;
  color: #6b7280;
}

.adjustments-list {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.adjustment-item {
  background: linear-gradient(145deg, #1a1a2e, #16213e);
  border: 1px solid rgba(0, 255, 128, 0.2);
  border-radius: 12px;
  padding: 20px;
  transition: all 0.3s ease;
}

.adjustment-item:hover {
  border-color: rgba(0, 255, 128, 0.4);
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 255, 128, 0.1);
}

.adjustment-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 16px;
}

.user-info {
  display: flex;
  align-items: center;
  gap: 12px;
}

.user-avatar-small {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  border: 2px solid #00ff80;
  overflow: hidden;
  flex-shrink: 0;
}

.user-avatar-small img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.avatar-fallback-small {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, #00ff80, #00cc66);
  color: #000;
  font-size: 16px;
  font-weight: bold;
  border-radius: 50%;
}

.user-details h4 {
  margin: 0 0 4px 0;
  color: white;
  font-size: 16px;
  font-weight: 600;
}

.user-details p {
  margin: 0;
  color: #9ca3af;
  font-size: 12px;
}

.adjustment-type {
  font-size: 24px;
  font-weight: bold;
  padding: 8px 12px;
  border-radius: 8px;
  min-width: 40px;
  text-align: center;
}

.type-add {
  background: rgba(34, 197, 94, 0.2);
  color: #22c55e;
  border: 1px solid rgba(34, 197, 94, 0.3);
}

.type-subtract {
  background: rgba(239, 68, 68, 0.2);
  color: #ef4444;
  border: 1px solid rgba(239, 68, 68, 0.3);
}

.type-set {
  background: rgba(59, 130, 246, 0.2);
  color: #3b82f6;
  border: 1px solid rgba(59, 130, 246, 0.3);
  font-size: 14px;
  padding: 6px 10px;
}

.adjustment-details {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 12px;
}

.amount-section,
.balance-section,
.description-section,
.timestamp-section {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.amount-label,
.balance-label,
.description-label,
.timestamp-label {
  font-size: 12px;
  color: #9ca3af;
  font-weight: 500;
}

.amount-value,
.balance-value {
  font-size: 16px;
  font-weight: 700;
  color: #00ff80;
}

.description-value {
  font-size: 14px;
  color: #e5e7eb;
  word-break: break-word;
}

.timestamp-value {
  font-size: 14px;
  color: #9ca3af;
  font-family: 'Courier New', monospace;
}

/* Responsive for Recent Adjustments Modal */
@media (max-width: 768px) {
  .recent-adjustments-modal {
    max-width: 95vw;
    margin: 20px;
  }
  
  .adjustment-details {
    grid-template-columns: 1fr;
    gap: 8px;
  }
  
  .adjustment-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 12px;
  }
  
  .user-info {
    width: 100%;
  }
  
  .adjustment-type {
    align-self: flex-end;
  }
}

/* Toast Notification */
.toast-notification {
  position: fixed;
  top: 20px;
  right: 20px;
  z-index: 9999;
  animation: slideInRight 0.3s ease-out;
}

.toast-content {
  background: linear-gradient(135deg, #00ff80, #00cc66);
  color: #000;
  padding: 16px 20px;
  border-radius: 12px;
  box-shadow: 0 4px 20px rgba(0, 255, 128, 0.3);
  display: flex;
  align-items: center;
  gap: 12px;
  font-weight: 600;
  min-width: 300px;
  max-width: 400px;
}

.toast-content i {
  font-size: 20px;
  color: #000;
}

@keyframes slideInRight {
  from {
    transform: translateX(100%);
    opacity: 0;
  }
  to {
    transform: translateX(0);
    opacity: 1;
  }
}

/* Responsive Toast */
@media (max-width: 768px) {
  .toast-notification {
    top: 10px;
    right: 10px;
    left: 10px;
  }
  
  .toast-content {
    min-width: auto;
    max-width: none;
  }
}
</style>
