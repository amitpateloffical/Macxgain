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
          placeholder="Search users by name, email, or ID..."
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

    <!-- Users Table -->
    <div class="users-table-container">
      <div v-if="isLoading" class="loading-container">
        <div class="loading-spinner"></div>
        <p>Loading users...</p>
      </div>
      
      <div v-else-if="filteredUsers.length === 0" class="no-data">
        <div class="no-data-icon">👥</div>
        <h3>No Users Found</h3>
        <p>{{ searchQuery ? 'Try adjusting your search terms' : 'No users available at the moment' }}</p>
      </div>
      
      <div v-else class="users-table">
        <div class="table-header">
          <div class="header-cell">User</div>
          <div class="header-cell">Balance</div>
          <div class="header-cell">Status</div>
        </div>
        
        <div 
          v-for="user in paginatedUsers" 
          :key="user.id" 
          class="table-row"
        >
          <div class="user-cell">
            <div class="user-avatar">
              <span class="avatar-text">{{ getUserInitials(user.name) }}</span>
            </div>
            <div class="user-info">
              <div class="user-name">{{ user.name }}</div>
              <div class="user-email">{{ user.email }}</div>
              <div class="user-id">ID: {{ user.id }}</div>
            </div>
          </div>
          
          <div class="balance-cell">
            <div class="balance-amount">₹{{ user.total_balance || '0.00' }}</div>
            <div class="balance-status" :class="getBalanceStatus(user.total_balance)">
              {{ getBalanceStatusText(user.total_balance) }}
            </div>
          </div>
          
          <div class="status-cell">
            <span class="status-badge" :class="getUserStatus(user.status)">
              {{ getUserStatusText(user.status) }}
            </span>
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
export default {
  name: 'AITrading',
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
      apiBaseUrl: process.env.NODE_ENV === 'production' ? '/api' : 'http://127.0.0.1:8000/api'
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
              is_admin: user.is_admin || 0
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
    viewUser(user) {
      console.log('View user:', user);
      // Implement user view functionality
    },
    editUser(user) {
      console.log('Edit user:', user);
      // Implement user edit functionality
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

/* Users Table */
.users-table-container {
  background: rgba(255, 255, 255, 0.02);
  border-radius: 12px;
  overflow: hidden;
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

.users-table {
  width: 100%;
}

.table-header {
  display: grid;
  grid-template-columns: 2fr 1fr 1fr 1fr 1fr;
  gap: 16px;
  padding: 16px 20px;
  background: rgba(0, 255, 136, 0.05);
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
  font-weight: 600;
  color: rgba(255, 255, 255, 0.8);
}

.table-row {
  display: grid;
  grid-template-columns: 2fr 1fr 1fr 1fr 1fr;
  gap: 16px;
  padding: 16px 20px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.05);
  transition: all 0.3s ease;
}

.table-row:hover {
  background: rgba(255, 255, 255, 0.02);
}

.user-cell {
  display: flex;
  align-items: center;
  gap: 12px;
}

.user-avatar {
  width: 40px;
  height: 40px;
  background: linear-gradient(135deg, #00ff88 0%, #00d4aa 100%);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: bold;
  color: #0d0d1a;
  font-size: 14px;
}

.user-info {
  flex: 1;
}

.user-name {
  font-weight: 600;
  color: white;
  margin-bottom: 4px;
}

.user-email {
  color: rgba(255, 255, 255, 0.7);
  font-size: 12px;
  margin-bottom: 2px;
}

.user-id {
  color: rgba(255, 255, 255, 0.5);
  font-size: 11px;
}

.balance-cell {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.balance-amount {
  font-weight: 600;
  color: white;
}

.balance-status {
  font-size: 12px;
  padding: 2px 8px;
  border-radius: 4px;
  text-align: center;
}

.balance-status.high {
  background: rgba(0, 255, 136, 0.2);
  color: #00ff88;
}

.balance-status.medium {
  background: rgba(255, 193, 7, 0.2);
  color: #ffc107;
}

.balance-status.low {
  background: rgba(255, 107, 107, 0.2);
  color: #ff6b6b;
}

.status-cell {
  display: flex;
  align-items: center;
}

.status-badge {
  padding: 4px 12px;
  border-radius: 12px;
  font-size: 12px;
  font-weight: 500;
}

.status-badge.active {
  background: rgba(0, 255, 136, 0.2);
  color: #00ff88;
}

.status-badge.inactive {
  background: rgba(255, 107, 107, 0.2);
  color: #ff6b6b;
}

.date-cell {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.join-date {
  color: white;
  font-weight: 500;
}

.join-time {
  color: rgba(255, 255, 255, 0.6);
  font-size: 12px;
}

.actions-cell {
  display: flex;
  gap: 8px;
}

.action-btn {
  width: 32px;
  height: 32px;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.3s ease;
  font-size: 12px;
}

.view-btn {
  background: rgba(0, 255, 136, 0.2);
  color: #00ff88;
}

.view-btn:hover {
  background: rgba(0, 255, 136, 0.3);
}

.edit-btn {
  background: rgba(255, 193, 7, 0.2);
  color: #ffc107;
}

.edit-btn:hover {
  background: rgba(255, 193, 7, 0.3);
}

.delete-btn {
  background: rgba(255, 107, 107, 0.2);
  color: #ff6b6b;
}

.delete-btn:hover {
  background: rgba(255, 107, 107, 0.3);
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
@media (max-width: 768px) {
  .ai-trading-screen {
    padding: 16px;
    padding-bottom: 120px;
  }
  
  .page-header {
    flex-direction: column;
    gap: 16px;
    text-align: center;
  }
  
  .stats-grid {
    grid-template-columns: repeat(2, 1fr);
    gap: 12px;
  }
  
  .stat-card {
    padding: 16px;
  }
  
  .search-filters {
    gap: 12px;
  }
  
  .filter-buttons {
    justify-content: center;
  }
  
  .table-header,
  .table-row {
    grid-template-columns: 1fr;
    gap: 8px;
  }
  
  .table-header {
    display: none;
  }
  
  .table-row {
    background: rgba(255, 255, 255, 0.02);
    border-radius: 8px;
    margin-bottom: 8px;
    padding: 16px;
  }
  
  .user-cell {
    justify-content: center;
    text-align: center;
  }
  
  .balance-cell,
  .status-cell,
  .date-cell,
  .actions-cell {
    text-align: center;
  }
  
  .mobile-bottom-nav {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 4px;
    padding: 16px 8px;
  }
}

@media (max-width: 480px) {
  .ai-trading-screen {
    padding: 12px;
    padding-bottom: 100px;
  }
  
  .page-title {
    font-size: 24px;
  }
  
  .stats-grid {
    grid-template-columns: 1fr;
  }
  
  .stat-card {
    padding: 12px;
  }
  
  .stat-icon {
    width: 40px;
    height: 40px;
    font-size: 24px;
  }
  
  .search-input {
    padding: 12px 12px 12px 40px;
    font-size: 14px;
  }
  
  .filter-btn {
    padding: 6px 12px;
    font-size: 12px;
  }
  
  .mobile-bottom-nav {
    padding: 12px 6px;
    gap: 2px;
  }
  
  .mobile-bottom-nav .nav-icon {
    font-size: 18px;
  }
  
  .mobile-bottom-nav .nav-label {
    font-size: 9px;
  }
}
</style>
