<template>
  <div class="ai-trading-screen">
    <!-- Header -->
    <div class="page-header">
              <div class="header-content">
          <h1 class="page-title">🤖 AI Trading - Regular Users</h1>
          <p class="page-subtitle">Manage regular users (non-admin) in AI trading cart</p>
        </div>
      <div class="header-actions">
        <button class="refresh-btn" @click="refreshUsers" :disabled="isLoading">
          <i class="fas fa-sync-alt" :class="{ 'fa-spin': isLoading }"></i>
          {{ isLoading ? 'Loading...' : 'Refresh' }}
        </button>
      </div>
    </div>

    <!-- Stats Cards -->
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-icon">👥</div>
        <div class="stat-content">
          <div class="stat-number">{{ totalUsers }}</div>
          <div class="stat-label">Regular Users</div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon">💰</div>
        <div class="stat-content">
          <div class="stat-number">₹{{ totalBalance.toLocaleString() }}</div>
          <div class="stat-label">Total Balance</div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon">📈</div>
        <div class="stat-content">
          <div class="stat-number">{{ activeUsers }}</div>
          <div class="stat-label">Active Users</div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon">🆕</div>
        <div class="stat-content">
          <div class="stat-number">{{ newUsers }}</div>
          <div class="stat-label">New This Month</div>
        </div>
      </div>
    </div>

    <!-- Search and Filters -->
    <div class="search-filters">
      <div class="search-box">
        <i class="fas fa-search search-icon"></i>
        <input 
          v-model="searchQuery" 
          type="text" 
          placeholder="Search users by name, mobile, or ID..."
          class="search-input"
        />
      </div>
      <div class="filter-buttons">
        <button 
          v-for="filter in filters" 
          :key="filter.value"
          :class="['filter-btn', { active: activeFilter === filter.value }]"
          @click="activeFilter = filter.value"
        >
          {{ filter.label }}
        </button>
      </div>
    </div>

    <!-- Users Grid -->
    <div class="users-grid-container">
      
      <!-- Mobile Navigation Component -->
      <AdminMobileNav />
      <div v-if="isLoading" class="loading-container">
        <div class="loading-spinner"></div>
        <p>Loading users...</p>
      </div>
      
      <div v-else-if="filteredUsers.length === 0" class="no-data">
        <div class="no-data-icon">👥</div>
        <h3>No Users Found</h3>
        <p>{{ searchQuery ? 'Try adjusting your search terms' : 'No users available at the moment' }}</p>
      </div>
      
      <div v-else class="users-grid">
        <div 
          v-for="user in paginatedUsers" 
          :key="user.id" 
          class="user-card"
        >
          <!-- User Card Header -->
          <div class="card-header">
            <div class="user-avatar">
              <img 
                v-if="user.profile_image"
                :src="getProfileImageUrl(user.profile_image)"
                :alt="user.name"
                @error="handleProfileImageError($event, user.name)"
                @load="() => console.log('Profile image loaded successfully for:', user.name)"
              />
              <span v-else class="avatar-text">{{ getUserInitials(user.name) }}</span>
            </div>
            <div class="user-status">
              <span class="status-badge" :class="getUserStatus(user.status)">
                {{ getUserStatusText(user.status) }}
              </span>
            </div>
          </div>
          
          <!-- User Info -->
          <div class="user-info">
            <h3 class="user-name">{{ user.name }}</h3>
            <p class="user-mobile">
              <i class="fas fa-mobile-alt"></i>
              {{ user.phone || 'No Mobile' }}
            </p>
            <div class="user-id">ID: {{ user.id }}</div>
          </div>
          
          <!-- Balance Section -->
          <div class="balance-section">
            <div class="balance-label">Total Balance</div>
            <div class="balance-amount">₹{{ user.total_balance || '0.00' }}</div>
            <div class="balance-status" :class="getBalanceStatus(user.total_balance)">
              {{ getBalanceStatusText(user.total_balance) }}
            </div>
          </div>
          
          <!-- Card Actions -->
          <div class="card-actions">
            <button class="action-btn view-btn" @click="viewUserDetails(user)">
              <i class="fas fa-eye"></i>
              View Details
            </button>
            <button class="action-btn trade-btn" @click="tradeWithUser(user)">
              <i class="fas fa-robot"></i>
              Trade with AI
            </button>
          </div>
        </div>
      </div>
      
      <!-- Pagination -->
      <div v-if="totalPages > 1" class="pagination">
        <button 
          class="page-btn" 
          :disabled="currentPage === 1"
          @click="currentPage = currentPage - 1"
        >
          <i class="fas fa-chevron-left"></i>
        </button>
        
        <div class="page-numbers">
          <button 
            v-for="page in visiblePages" 
            :key="page"
            :class="['page-btn', { active: page === currentPage }]"
            @click="currentPage = page"
          >
            {{ page }}
          </button>
        </div>
        
        <button 
          class="page-btn" 
          :disabled="currentPage === totalPages"
          @click="currentPage = currentPage + 1"
        >
          <i class="fas fa-chevron-right"></i>
        </button>
      </div>
    </div>

    <!-- User Details Modal -->
    <div v-if="showUserModal" class="modal-overlay" @click="closeUserModal">
      <div class="modal-content" @click.stop>
        <div class="modal-header">
          <h2 class="modal-title">👤 User Details</h2>
          <button class="modal-close" @click="closeUserModal">
            <i class="fas fa-times"></i>
          </button>
        </div>
        
        <div class="modal-body" v-if="selectedUser">
          <!-- User Profile Section -->
          <div class="user-profile-section">
            <div class="profile-avatar">
              <span class="avatar-text-large">{{ getUserInitials(selectedUser.name) }}</span>
            </div>
            <div class="profile-info">
              <h3 class="profile-name">{{ selectedUser.name }}</h3>
              <p class="profile-status">
                <span class="status-badge" :class="getUserStatus(selectedUser.status)">
                  {{ getUserStatusText(selectedUser.status) }}
                </span>
              </p>
            </div>
          </div>
          
          <!-- User Details Grid -->
          <div class="details-grid">
            <div class="detail-item">
              <div class="detail-label">
                <i class="fas fa-id-card"></i>
                User ID
              </div>
              <div class="detail-value">{{ selectedUser.id }}</div>
            </div>
            
            <div class="detail-item">
              <div class="detail-label">
                <i class="fas fa-envelope"></i>
                Email Address
              </div>
              <div class="detail-value">{{ selectedUser.email }}</div>
            </div>
            
            <div class="detail-item">
              <div class="detail-label">
                <i class="fas fa-mobile-alt"></i>
                Mobile Number
              </div>
              <div class="detail-value">{{ selectedUser.phone || 'Not provided' }}</div>
            </div>
            
            <div class="detail-item">
              <div class="detail-label">
                <i class="fas fa-calendar-alt"></i>
                Joined Date
              </div>
              <div class="detail-value">{{ formatDate(selectedUser.created_at) }}</div>
            </div>
            
            <div class="detail-item">
              <div class="detail-label">
                <i class="fas fa-wallet"></i>
                Current Balance
              </div>
              <div class="detail-value balance-highlight">₹{{ selectedUser.total_balance || '0.00' }}</div>
            </div>
            
            <div class="detail-item">
              <div class="detail-label">
                <i class="fas fa-chart-line"></i>
                Balance Status
              </div>
              <div class="detail-value">
                <span class="balance-status-badge" :class="getBalanceStatus(selectedUser.total_balance)">
                  {{ getBalanceStatusText(selectedUser.total_balance) }}
                </span>
              </div>
            </div>
          </div>
          
          <!-- Additional Actions -->
          <div class="modal-actions">
            <button class="action-btn trade-btn-large" @click="tradeWithUser(selectedUser)">
              <i class="fas fa-robot"></i>
              Start AI Trading
            </button>
            <button class="action-btn close-btn" @click="closeUserModal">
              <i class="fas fa-times"></i>
              Close
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Mobile Bottom Navigation -->
    <div class="mobile-bottom-nav">
      <div class="nav-item" @click="navigateTo('admin_dashboard')">
        <div class="nav-icon">🏠</div>
        <span class="nav-label">Home</span>
      </div>
      <div class="nav-item" @click="navigateTo('register_request')">
        <div class="nav-icon">📝</div>
        <span class="nav-label">Register</span>
      </div>
      <div class="nav-item" @click="navigateTo('money_request')">
        <div class="nav-icon">💰</div>
        <span class="nav-label">Wallet</span>
      </div>
      <div class="nav-item" @click="navigateTo('user_management')">
        <div class="nav-icon">👥</div>
        <span class="nav-label">Users</span>
      </div>
      <div class="nav-item" @click="navigateTo('withdrawal_request')">
        <div class="nav-icon">💳</div>
        <span class="nav-label">Withdraw</span>
      </div>
      <div class="nav-item" @click="navigateTo('analytics')">
        <div class="nav-icon">📊</div>
        <span class="nav-label">Analytics</span>
      </div>
      <div class="nav-item active">
        <div class="nav-icon">🤖</div>
        <span class="nav-label">AI Trading</span>
      </div>
    </div>
  </div>
</template>

<script>
import AdminMobileNav from '../../components/AdminMobileNav.vue';

export default {
  name: 'AITrading',
  components: {
    AdminMobileNav
  },
  data() {
    return {
      users: [],
      isLoading: false,
      searchQuery: '',
      activeFilter: 'all',
      currentPage: 1,
      itemsPerPage: 20,
      filters: [
        { label: 'All', value: 'all' },
        { label: 'Active', value: 'A' },
        { label: 'Inactive', value: 'I' },
        { label: 'New', value: 'new' }
      ],
      apiBaseUrl: `${window.location.origin}/api`,
      showUserModal: false,
      selectedUser: null
    }
  },
  computed: {
    filteredUsers() {
      let filtered = this.users;
      
      // Apply search filter
      if (this.searchQuery) {
        const query = this.searchQuery.toLowerCase();
        filtered = filtered.filter(user => 
          user.name.toLowerCase().includes(query) ||
          user.email.toLowerCase().includes(query) ||
          user.id.toString().includes(query)
        );
      }
      
      // Apply status filter
      if (this.activeFilter !== 'all') {
        switch (this.activeFilter) {
          case 'A':
            filtered = filtered.filter(user => user.status === 'A');
            break;
          case 'I':
            filtered = filtered.filter(user => user.status === 'I');
            break;
          case 'new':
            const oneMonthAgo = new Date();
            oneMonthAgo.setMonth(oneMonthAgo.getMonth() - 1);
            filtered = filtered.filter(user => new Date(user.created_at) > oneMonthAgo);
            break;
        }
      }
      
      return filtered;
    },
    paginatedUsers() {
      const start = (this.currentPage - 1) * this.itemsPerPage;
      const end = start + this.itemsPerPage;
      return this.filteredUsers.slice(start, end);
    },
    totalPages() {
      return Math.ceil(this.filteredUsers.length / this.itemsPerPage);
    },
    visiblePages() {
      const pages = [];
      const maxVisible = 5;
      let start = Math.max(1, this.currentPage - Math.floor(maxVisible / 2));
      let end = Math.min(this.totalPages, start + maxVisible - 1);
      
      if (end - start + 1 < maxVisible) {
        start = Math.max(1, end - maxVisible + 1);
      }
      
      for (let i = start; i <= end; i++) {
        pages.push(i);
      }
      
      return pages;
    },
    totalUsers() {
      return this.users.length;
    },
    totalBalance() {
      return this.users.reduce((sum, user) => sum + (parseFloat(user.total_balance) || 0), 0);
    },
    activeUsers() {
      return this.users.filter(user => user.status === 'A').length;
    },
    newUsers() {
      const oneMonthAgo = new Date();
      oneMonthAgo.setMonth(oneMonthAgo.getMonth() - 1);
      return this.users.filter(user => new Date(user.created_at) > oneMonthAgo).length;
    }
  },
  mounted() {
    this.fetchUsers();
  },
  methods: {
    async fetchUsers() {
      this.isLoading = true;
      try {
        // Get auth token from localStorage
        const token = localStorage.getItem('access_token');
        
        if (!token) {
          throw new Error('No authentication token found');
        }

        // Fetch regular users from live API (non-admins only)
        const response = await fetch(`${this.apiBaseUrl}/users`, {
          method: 'GET',
          headers: {
            'Authorization': `Bearer ${token}`,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
          }
        });

        if (!response.ok) {
          throw new Error(`HTTP error! status: ${response.status}`);
        }

        const result = await response.json();
        
        if (result.success && result.data) {
          // Transform API data to match our component structure
          // Additional frontend filtering to ensure no admin users
          this.users = result.data
            .filter(user => !user.is_admin) // Filter out admin users
            .map(user => ({
              id: user.id,
              name: user.name,
              email: user.email,
              total_balance: user.total_balance || 0,
              status: user.status || 'I',
              created_at: user.created_at,
              phone: user.phone,
              is_admin: user.is_admin || 0,
              profile_image: user.profile_image // Include profile image
            }));
        } else {
          throw new Error(result.message || 'Failed to fetch users');
        }
      } catch (error) {
        console.error('Error fetching users:', error);
        // Show error message to user
        this.$toast?.error?.(error.message || 'Failed to fetch users');
        
        // Fallback to empty array instead of mock data
        this.users = [];
      } finally {
        this.isLoading = false;
      }
    },
    refreshUsers() {
      this.fetchUsers();
    },
    getProfileImageUrl(profileImagePath) {
      if (!profileImagePath) {
        return '/build/assets/tableprofileimg-DaN7tIxX.png'
      }
      
      // If it's already a full URL, return as is
      if (profileImagePath.startsWith('http')) {
        return profileImagePath
      }
      
      // Construct full URL for stored images
      return `${window.location.origin}/storage/${profileImagePath}`
    },
    
    handleProfileImageError(event, userName) {
      console.log(`Profile image failed to load for ${userName}, creating initials fallback`)
      console.log('Failed image src:', event.target.src)
      
      event.target.style.display = 'none'
      // Create initials fallback
      const initials = userName.split(' ').map(n => n[0]).join('').toUpperCase().substring(0, 3)
      const fallbackDiv = document.createElement('div')
      fallbackDiv.className = 'profile-initials-fallback'
      fallbackDiv.textContent = initials
      fallbackDiv.style.cssText = `
        width: 48px;
        height: 48px;
        border-radius: 50%;
        background: linear-gradient(135deg, #00ff80, #00cc66);
        color: #000;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: 16px;
        border: 3px solid #00ff80;
      `
      event.target.parentNode.appendChild(fallbackDiv)
    },
    
    getUserInitials(name) {
      return name.split(' ').map(n => n[0]).join('').toUpperCase();
    },
    getBalanceStatus(balance) {
      const bal = parseFloat(balance) || 0;
      if (bal > 50000) return 'high';
      if (bal > 20000) return 'medium';
      return 'low';
    },
    getBalanceStatusText(balance) {
      const bal = parseFloat(balance) || 0;
      if (bal > 50000) return 'High Balance';
      if (bal > 20000) return 'Medium Balance';
      return 'Low Balance';
    },
    getUserStatus(status) {
      return status === 'A' ? 'active' : 'inactive';
    },
    getUserStatusText(status) {
      return status === 'A' ? 'Active' : 'Inactive';
    },
    formatDate(dateString) {
      return new Date(dateString).toLocaleDateString('en-IN');
    },
    formatTime(dateString) {
      return new Date(dateString).toLocaleTimeString('en-IN', { 
        hour: '2-digit', 
        minute: '2-digit' 
      });
    },
    viewUserDetails(user) {
      console.log('View user details:', user);
      this.selectedUser = user;
      this.showUserModal = true;
    },
    tradeWithUser(user) {
      console.log('Trade with AI for user:', user);
      // Professional AI Trading functionality
      this.$toast?.success?.(`Initiating AI Trading session for ${user.name}`);
      
      // You can implement:
      // 1. Redirect to AI Trading dashboard
      // 2. Open AI Trading modal
      // 3. Start AI Trading session
      // 4. Show trading analytics
      
      // Example: Redirect to AI Trading page
      // this.$router.push(`/admin/ai-trading-session/${user.id}`);
    },
    closeUserModal() {
      this.showUserModal = false;
      this.selectedUser = null;
    },
    deleteUser(user) {
      if (confirm(`Are you sure you want to delete ${user.name}?`)) {
        console.log('Delete user:', user);
        // Implement user deletion
        this.users = this.users.filter(u => u.id !== user.id);
      }
    },
    navigateTo(routeName) {
      this.$router.push({ name: routeName });
    }
  }
}
</script>

<style scoped>
/* AI Trading Page Styles */
.ai-trading-screen {
  background-color: #0d0d1a;
  color: white;
  min-height: 100vh;
  padding: 20px;
  padding-bottom: 120px;
}

/* Page Header */
.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24px;
  padding: 20px;
  background: linear-gradient(145deg, #101022, #0d0d1a);
  border: 1px solid rgba(0, 255, 136, 0.2);
  border-radius: 16px;
}

.page-title {
  font-size: 28px;
  font-weight: bold;
  color: #00ff88;
  margin: 0 0 8px 0;
}

.page-subtitle {
  color: rgba(255, 255, 255, 0.7);
  margin: 0;
  font-size: 16px;
}

.refresh-btn {
  background: linear-gradient(135deg, #00ff88 0%, #00d4aa 100%);
  color: #0d0d1a;
  border: none;
  padding: 12px 20px;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 600;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  gap: 8px;
}

.refresh-btn:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(0, 255, 136, 0.3);
}

.refresh-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

/* Stats Grid */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 16px;
  margin-bottom: 24px;
}

.stat-card {
  background: linear-gradient(145deg, #101022, #0d0d1a);
  border: 1px solid rgba(0, 255, 136, 0.2);
  border-radius: 12px;
  padding: 20px;
  display: flex;
  align-items: center;
  gap: 16px;
  transition: all 0.3s ease;
}

.stat-card:hover {
  border-color: #00ff88;
  transform: translateY(-2px);
}

.stat-icon {
  font-size: 32px;
  width: 48px;
  height: 48px;
  background: linear-gradient(135deg, #00ff88 0%, #00d4aa 100%);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #0d0d1a;
}

.stat-content {
  flex: 1;
}

.stat-number {
  font-size: 24px;
  font-weight: bold;
  color: white;
  margin-bottom: 4px;
}

.stat-label {
  color: rgba(255, 255, 255, 0.7);
  font-size: 14px;
}

/* Search and Filters */
.search-filters {
  display: flex;
  flex-direction: column;
  gap: 16px;
  margin-bottom: 24px;
}

.search-box {
  position: relative;
  flex: 1;
}

.search-icon {
  position: absolute;
  left: 16px;
  top: 50%;
  transform: translateY(-50%);
  color: rgba(255, 255, 255, 0.5);
}

.search-input {
  width: 100%;
  padding: 16px 16px 16px 48px;
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 8px;
  color: white;
  font-size: 16px;
}

.search-input::placeholder {
  color: rgba(255, 255, 255, 0.5);
}

.search-input:focus {
  outline: none;
  border-color: #00ff88;
  background: rgba(255, 255, 255, 0.08);
}

.filter-buttons {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
}

.filter-btn {
  background: rgba(255, 255, 255, 0.05);
  color: white;
  border: 1px solid rgba(255, 255, 255, 0.1);
  padding: 8px 16px;
  border-radius: 6px;
  cursor: pointer;
  transition: all 0.3s ease;
  font-size: 14px;
}

.filter-btn:hover {
  background: rgba(255, 255, 255, 0.1);
}

.filter-btn.active {
  background: #00ff88;
  color: #0d0d1a;
  border-color: #00ff88;
}

/* Users Grid */
.users-grid-container {
  background: rgba(255, 255, 255, 0.02);
  border-radius: 12px;
  padding: 20px;
  margin-bottom: 24px;
}

.loading-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 60px 20px;
  gap: 16px;
}

.loading-spinner {
  width: 40px;
  height: 40px;
  border: 3px solid rgba(0, 255, 136, 0.2);
  border-top: 3px solid #00ff88;
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.no-data {
  text-align: center;
  padding: 60px 20px;
}

.no-data-icon {
  font-size: 48px;
  margin-bottom: 16px;
}

.no-data h3 {
  color: white;
  margin: 0 0 8px 0;
}

.no-data p {
  color: rgba(255, 255, 255, 0.7);
  margin: 0;
}

.users-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
  gap: 20px;
  padding: 20px;
}

.user-card {
  background: linear-gradient(145deg, #101022, #0d0d1a);
  border: 1px solid rgba(0, 255, 136, 0.2);
  border-radius: 16px;
  padding: 24px;
  display: flex;
  flex-direction: column;
  gap: 16px;
  transition: all 0.3s ease;
  position: relative;
  overflow: hidden;
}

.user-card::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 3px;
  background: linear-gradient(90deg, #00ff88, #00d4aa, #00ff88);
  opacity: 0;
  transition: opacity 0.3s ease;
}

.user-card:hover {
  border-color: #00ff88;
  transform: translateY(-4px);
  box-shadow: 0 8px 30px rgba(0, 255, 136, 0.15);
}

.user-card:hover::before {
  opacity: 1;
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.user-avatar {
  width: 48px;
  height: 48px;
  background: linear-gradient(135deg, #00ff88 0%, #00d4aa 100%);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 4px 15px rgba(0, 255, 136, 0.3);
  overflow: hidden;
  flex-shrink: 0;
}

.user-avatar img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  border-radius: 50%;
}

.avatar-text {
  font-weight: bold;
  color: #0d0d1a;
  font-size: 18px;
}

.user-status {
  display: flex;
  align-items: center;
}

.status-badge {
  padding: 6px 14px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.status-badge.active {
  background: rgba(0, 255, 136, 0.2);
  color: #00ff88;
  border: 1px solid rgba(0, 255, 136, 0.3);
}

.status-badge.inactive {
  background: rgba(255, 107, 107, 0.2);
  color: #ff6b6b;
  border: 1px solid rgba(255, 107, 107, 0.3);
}

.user-info {
  margin-bottom: 12px;
}

.user-name {
  font-size: 20px;
  font-weight: 700;
  color: white;
  margin: 0 0 8px 0;
  text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
}

.user-mobile {
  color: rgba(255, 255, 255, 0.8);
  font-size: 14px;
  margin: 0 0 6px 0;
  word-break: break-word;
  display: flex;
  align-items: center;
  gap: 8px;
  justify-content: center;
}

.user-mobile i {
  color: #00ff88;
  font-size: 12px;
}

.user-id {
  color: rgba(255, 255, 255, 0.6);
  font-size: 12px;
  font-family: 'Courier New', monospace;
  background: rgba(255, 255, 255, 0.05);
  padding: 4px 8px;
  border-radius: 6px;
  display: inline-block;
}

.balance-section {
  display: flex;
  flex-direction: column;
  gap: 6px;
  padding: 12px;
  background: rgba(0, 255, 136, 0.05);
  border-radius: 12px;
  border: 1px solid rgba(0, 255, 136, 0.1);
}

.balance-label {
  font-size: 12px;
  color: rgba(255, 255, 255, 0.7);
  text-transform: uppercase;
  letter-spacing: 0.5px;
  font-weight: 500;
}

.balance-amount {
  font-size: 24px;
  font-weight: 700;
  color: #00ff88;
  text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
}

.balance-status {
  font-size: 12px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.balance-status.high {
  color: #00ff88;
}

.balance-status.medium {
  color: #ffd700;
}

.balance-status.low {
  color: #ff6b6b;
}

.card-actions {
  display: flex;
  gap: 12px;
  margin-top: 16px;
}

.action-btn {
  flex: 1;
  padding: 12px 16px;
  border: none;
  border-radius: 10px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  font-size: 14px;
  font-weight: 600;
  transition: all 0.3s ease;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.view-btn {
  background: linear-gradient(135deg, #00ff88 0%, #00d4aa 100%);
  color: #0d0d1a;
  width: 100%;
}

.view-btn:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(0, 255, 136, 0.4);
}

.trade-btn {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  position: relative;
  overflow: hidden;
}

.trade-btn::before {
  content: '';
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
  transition: left 0.5s ease;
}

.trade-btn:hover::before {
  left: 100%;
}

.trade-btn:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
  background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
}

.action-btn:hover:not(:disabled) {
  transform: translateY(-2px);
}

.action-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

/* Pagination */
.pagination {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 8px;
  padding: 20px;
  background: rgba(255, 255, 255, 0.02);
}

.page-btn {
  background: rgba(255, 255, 255, 0.05);
  color: white;
  border: 1px solid rgba(255, 255, 255, 0.1);
  padding: 8px 12px;
  border-radius: 6px;
  cursor: pointer;
  transition: all 0.3s ease;
  min-width: 40px;
}

.page-btn:hover:not(:disabled) {
  background: rgba(255, 255, 255, 0.1);
}

.page-btn.active {
  background: #00ff88;
  color: #0d0d1a;
  border-color: #00ff88;
}

.page-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.page-numbers {
  display: flex;
  gap: 4px;
}

/* Mobile Bottom Navigation */
.mobile-bottom-nav {
  position: fixed;
  bottom: 0;
  left: 0;
  right: 0;
  background: rgba(13, 13, 26, 0.95);
  backdrop-filter: blur(20px);
  border-top: 1px solid rgba(0, 255, 136, 0.2);
  padding: 12px 0;
  display: none;
  z-index: 1000;
}

.mobile-bottom-nav .nav-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 4px;
  cursor: pointer;
  padding: 8px;
  border-radius: 12px;
  transition: all 0.2s ease;
  user-select: none;
  -webkit-tap-highlight-color: transparent;
}

.mobile-bottom-nav .nav-item:active {
  background: rgba(0, 255, 136, 0.1);
  transform: scale(0.95);
}

.mobile-bottom-nav .nav-item.active {
  background: rgba(0, 255, 136, 0.2);
  color: #00ff88;
}

.mobile-bottom-nav .nav-icon {
  font-size: 20px;
  line-height: 1;
}

.mobile-bottom-nav .nav-label {
  font-size: 10px;
  font-weight: 500;
  color: rgba(255, 255, 255, 0.8);
  text-align: center;
  line-height: 1.2;
}

/* Responsive Design */
  /* Mobile Responsive */
  @media (max-width: 768px) {
    .ai-trading-screen {
      padding: 16px;
      padding-bottom: 120px;
    }
    
    .page-header {
      flex-direction: column;
      gap: 16px;
      text-align: center;
      padding: 16px;
    }
    
    .page-title {
      font-size: 24px;
    }
    
    .page-subtitle {
      font-size: 14px;
    }
    
    .stats-grid {
      grid-template-columns: repeat(2, 1fr);
      gap: 12px;
    }
    
    .stat-card {
      padding: 16px;
    }
    
    .stat-icon {
      width: 40px;
      height: 40px;
      font-size: 24px;
    }
    
    .search-filters {
      flex-direction: column;
      gap: 12px;
    }
    
    .search-box {
      width: 100%;
    }
    
    .filter-buttons {
      justify-content: center;
      flex-wrap: wrap;
    }
    
    .filter-btn {
      padding: 8px 16px;
      font-size: 13px;
    }
    
    .users-grid {
      grid-template-columns: 1fr;
      gap: 16px;
      padding: 16px;
    }
    
    .user-card {
      padding: 20px;
    }
    
    .card-header {
      flex-direction: column;
      align-items: flex-start;
      gap: 12px;
    }
    
    .user-avatar {
      width: 48px;
      height: 48px;
      font-size: 18px;
    }
    
    .user-status {
      align-self: flex-start;
    }
    
    .user-info {
      text-align: center;
    }
    
    .user-name {
      font-size: 18px;
    }
    
    .user-mobile {
      font-size: 13px;
    }
    
    .user-id {
      font-size: 11px;
    }
    
    .balance-section {
      align-items: center;
      padding: 12px;
    }
    
    .balance-label {
      font-size: 11px;
    }
    
    .balance-amount {
      font-size: 20px;
    }
    
    .balance-status {
      font-size: 11px;
    }
    
    .card-actions {
      flex-direction: column;
      gap: 10px;
    }
    
    .action-btn {
      width: 100%;
      justify-content: center;
      padding: 10px 14px;
      font-size: 13px;
    }
    
    .pagination {
      flex-direction: column;
      gap: 12px;
      align-items: center;
    }
    
    .page-numbers {
      order: 2;
    }
    
    .page-btn {
      padding: 8px 12px;
      font-size: 14px;
    }
  }
  
  @media (max-width: 480px) {
    .stats-grid {
      grid-template-columns: 1fr;
    }
    
    .user-card {
      padding: 16px;
    }
    
    .user-avatar {
      width: 44px;
      height: 44px;
      font-size: 16px;
    }
    
    .user-name {
      font-size: 16px;
    }
    
    .balance-amount {
      font-size: 18px;
    }
    
    .action-btn {
      padding: 8px 12px;
      font-size: 12px;
    }
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
    background: linear-gradient(145deg, #101022, #0d0d1a);
    border: 1px solid rgba(0, 255, 136, 0.3);
    border-radius: 20px;
    width: 100%;
    max-width: 600px;
    max-height: 90vh;
    overflow-y: auto;
    position: relative;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
  }

  .modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 24px 24px 16px 24px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
  }

  .modal-title {
    font-size: 24px;
    font-weight: 700;
    color: #00ff88;
    margin: 0;
  }

  .modal-close {
    background: rgba(255, 255, 255, 0.1);
    border: none;
    color: rgba(255, 255, 255, 0.7);
    width: 36px;
    height: 36px;
    border-radius: 50%;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
  }

  .modal-close:hover {
    background: rgba(255, 107, 107, 0.2);
    color: #ff6b6b;
  }

  .modal-body {
    padding: 24px;
  }

  .user-profile-section {
    display: flex;
    align-items: center;
    gap: 20px;
    margin-bottom: 32px;
    padding: 20px;
    background: rgba(0, 255, 136, 0.05);
    border-radius: 16px;
    border: 1px solid rgba(0, 255, 136, 0.1);
  }

  .profile-avatar {
    width: 80px;
    height: 80px;
    background: linear-gradient(135deg, #00ff88 0%, #00d4aa 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 8px 25px rgba(0, 255, 136, 0.3);
  }

  .avatar-text-large {
    font-weight: bold;
    color: #0d0d1a;
    font-size: 32px;
  }

  .profile-info {
    flex: 1;
  }

  .profile-name {
    font-size: 28px;
    font-weight: 700;
    color: white;
    margin: 0 0 12px 0;
    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
  }

  .profile-status {
    margin: 0;
  }

  .details-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 32px;
  }

  .detail-item {
    background: rgba(255, 255, 255, 0.02);
    border: 1px solid rgba(255, 255, 255, 0.05);
    border-radius: 12px;
    padding: 20px;
    transition: all 0.3s ease;
  }

  .detail-item:hover {
    border-color: rgba(0, 255, 136, 0.2);
    background: rgba(0, 255, 136, 0.02);
  }

  .detail-label {
    display: flex;
    align-items: center;
    gap: 10px;
    color: rgba(255, 255, 255, 0.7);
    font-size: 14px;
    font-weight: 500;
    margin-bottom: 8px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
  }

  .detail-label i {
    color: #00ff88;
    font-size: 16px;
  }

  .detail-value {
    color: white;
    font-size: 16px;
    font-weight: 600;
    word-break: break-word;
  }

  .balance-highlight {
    color: #00ff88;
    font-size: 20px;
    font-weight: 700;
    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
  }

  .balance-status-badge {
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
  }

  .balance-status-badge.high {
    background: rgba(0, 255, 136, 0.2);
    color: #00ff88;
    border: 1px solid rgba(0, 255, 136, 0.3);
  }

  .balance-status-badge.medium {
    background: rgba(255, 215, 0, 0.2);
    color: #ffd700;
    border: 1px solid rgba(255, 215, 0, 0.3);
  }

  .balance-status-badge.low {
    background: rgba(255, 107, 107, 0.2);
    color: #ff6b6b;
    border: 1px solid rgba(255, 107, 107, 0.3);
  }

  .modal-actions {
    display: flex;
    gap: 16px;
    justify-content: center;
  }

  .trade-btn-large {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 16px 32px;
    font-size: 16px;
    font-weight: 600;
    border: none;
    border-radius: 12px;
    cursor: pointer;
    transition: all 0.3s ease;
    text-transform: uppercase;
    letter-spacing: 0.5px;
  }

  .trade-btn-large:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
  }

  .close-btn {
    background: rgba(255, 255, 255, 0.1);
    color: rgba(255, 255, 255, 0.8);
    padding: 16px 32px;
    font-size: 16px;
    font-weight: 600;
    border: none;
    border-radius: 12px;
    cursor: pointer;
      transition: all 0.3s ease;
  }

  .close-btn:hover {
    background: rgba(255, 255, 255, 0.2);
    color: white;
  }

  /* Modal Mobile Responsive */
  @media (max-width: 768px) {
    .modal-content {
      max-width: 95vw;
      margin: 10px;
    }

    .modal-header {
      padding: 20px 20px 16px 20px;
    }

    .modal-title {
      font-size: 20px;
    }

    .modal-body {
      padding: 20px;
    }

    .user-profile-section {
      flex-direction: column;
      text-align: center;
      gap: 16px;
    }

    .profile-avatar {
      width: 70px;
      height: 70px;
    }

    .avatar-text-large {
      font-size: 28px;
    }

    .profile-name {
      font-size: 24px;
    }

    .details-grid {
      grid-template-columns: 1fr;
      gap: 16px;
    }

    .modal-actions {
      flex-direction: column;
      gap: 12px;
    }

    .trade-btn-large,
    .close-btn {
      width: 100%;
      padding: 14px 24px;
      font-size: 14px;
    }
  }
  
  /* Profile Initials Fallback */
  .profile-initials-fallback {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    background: linear-gradient(135deg, #00ff80, #00cc66);
    color: #000;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 16px;
    border: 3px solid #00ff80;
    box-shadow: 0 2px 8px rgba(0, 255, 128, 0.3);
  }
</style>
