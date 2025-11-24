<template>
  <div class="analytics-screen">
    <!-- Header Section -->
    <div class="analytics-header">
      <div class="header-content">
        <div class="header-left">
          <h1 class="page-title">
            <i class="fa-solid fa-chart-line"></i>
            Analytics Dashboard
          </h1>
          <p class="page-subtitle">
            Comprehensive insights into your platform's performance (Manual refresh only)
          </p>
        </div>
        <div class="header-actions">
          <button class="btn-refresh" @click="refreshData" :disabled="loading">
            <i class="fa-solid fa-rotate" :class="{ 'fa-spin': loading }"></i>
            Refresh
          </button>
          <div class="export-buttons">
            <button class="btn-export csv" @click="exportCSV">
              <i class="fa-solid fa-file-csv"></i>
              CSV
            </button>
            <button class="btn-export pdf" @click="exportPDF">
              <i class="fa-solid fa-file-pdf"></i>
              PDF
            </button>
            <button class="btn-export json" @click="exportReport">
              <i class="fa-solid fa-file-code"></i>
              JSON
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Mobile Navigation Component -->
    <AdminMobileNav />

    <!-- Filter Controls -->
    <div class="filter-section">
      <div class="filter-controls">
        <div class="filter-group">
          <label class="filter-label">Time Period</label>
          <select v-model="selectedPeriod" class="filter-select" @change="updateData">
            <option value="current_month">Current Month</option>
            <option value="last_month">Last Month</option>
            <option value="last_3_months">Last 3 Months</option>
            <option value="last_6_months">Last 6 Months</option>
            <option value="current_year">Current Year</option>
            <option value="last_year">Last Year</option>
            <option value="custom">Custom Range</option>
          </select>
        </div>
        
        <div class="filter-group" v-if="selectedPeriod === 'custom'">
          <label class="filter-label">From Date</label>
          <input type="date" v-model="customStartDate" class="filter-input" @change="updateData">
        </div>
        
        <div class="filter-group" v-if="selectedPeriod === 'custom'">
          <label class="filter-label">To Date</label>
          <input type="date" v-model="customEndDate" class="filter-input" @change="updateData">
        </div>
      </div>
    </div>

    <!-- Key Metrics Cards -->
    <div class="metrics-section">
      <div class="metrics-grid">
        <div class="metric-card total-users">
          <div class="metric-icon">👥</div>
          <div class="metric-content">
            <h3 class="metric-title">Total Users</h3>
            <div class="metric-value">{{ formatNumber(totalUsers) }}</div>
            <div class="metric-change positive">
              <i class="fa-solid fa-arrow-up"></i>
              +{{ userGrowthRate }}% this month
            </div>
          </div>
        </div>

        <div class="metric-card active-users">
          <div class="metric-icon">🟢</div>
          <div class="metric-content">
            <h3 class="metric-title">Active Users</h3>
            <div class="metric-value">{{ formatNumber(activeUsers) }}</div>
            <div class="metric-change positive">
              <i class="fa-solid fa-arrow-up"></i>
              +{{ activeUserGrowth }}% this month
            </div>
          </div>
        </div>

        <div class="metric-card total-deposits">
          <div class="metric-icon">💰</div>
          <div class="metric-content">
            <h3 class="metric-title">Total Deposits</h3>
            <div class="metric-value">₹{{ formatNumber(totalDeposits) }}</div>
            <div class="metric-change positive">
              <i class="fa-solid fa-arrow-up"></i>
              +{{ depositGrowthRate }}% this month
            </div>
          </div>
        </div>

        <div class="metric-card total-withdrawals">
          <div class="metric-icon">💳</div>
          <div class="metric-content">
            <h3 class="metric-title">Total Withdrawals</h3>
            <div class="metric-value">₹{{ formatNumber(totalWithdrawals) }}</div>
            <div class="metric-change" :class="withdrawalGrowthRate >= 0 ? 'positive' : 'negative'">
              <i :class="withdrawalGrowthRate >= 0 ? 'fa-solid fa-arrow-up' : 'fa-solid fa-arrow-down'"></i>
              {{ withdrawalGrowthRate >= 0 ? '+' : '' }}{{ withdrawalGrowthRate }}% this month
            </div>
          </div>
        </div>



        <div class="metric-card net-revenue">
          <div class="metric-icon">💰</div>
          <div class="metric-content">
            <h3 class="metric-title">Net Revenue</h3>
            <div class="metric-value">₹{{ formatNumber(netRevenue) }}</div>
            <div class="metric-change" :class="revenueGrowthRate >= 0 ? 'positive' : 'negative'">
              <i :class="revenueGrowthRate >= 0 ? 'fa-solid fa-arrow-up' : 'fa-solid fa-arrow-down'"></i>
              {{ revenueGrowthRate >= 0 ? '+' : '' }}{{ revenueGrowthRate }}% this month
            </div>
          </div>
        </div>
      </div>
    </div>



    <!-- Detailed Data Tables -->
    <div class="data-section">
      <div class="data-tabs">
        <button 
          class="tab-btn" 
          :class="{ active: activeTab === 'users' }"
          @click="activeTab = 'users'"
        >
          <i class="fa-solid fa-users"></i>
          Users Data
        </button>
        <button 
          class="tab-btn" 
          :class="{ active: activeTab === 'deposits' }"
          @click="activeTab = 'deposits'"
        >
          <i class="fa-solid fa-money-bill-wave"></i>
          Deposits
        </button>
        <button 
          class="tab-btn" 
          :class="{ active: activeTab === 'withdrawals' }"
          @click="activeTab = 'withdrawals'"
        >
          <i class="fa-solid fa-credit-card"></i>
          Withdrawals
        </button>
      </div>

      <!-- Users Tab -->
      <div v-if="activeTab === 'users'" class="tab-content">
        <div class="table-header">
          <h3>User Registration Details</h3>
          <div class="table-actions">
            <input 
              type="text" 
              v-model="userSearch" 
              placeholder="Search users..." 
              class="search-input"
            >
            <select v-model="userStatusFilter" class="filter-select">
              <option value="all">All Status</option>
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
              <option value="pending">Pending</option>
            </select>
          </div>
        </div>
        <div class="table-container">
          <table class="data-table">
            <thead>
              <tr>
                <th>User ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Status</th>
                <th>Registration Date</th>
                <th>Last Login</th>
                <th>Total Balance</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="user in filteredUsers" :key="user.id">
                <td>{{ user.id }}</td>
                <td>{{ user.name }}</td>
                <td>{{ user.email }}</td>
                <td>
                  <span class="status-badge" :class="getUserStatusClass(user.status)">
                    {{ getUserStatusText(user.status) }}
                  </span>
                </td>
                <td>{{ formatDate(user.created_at) }}</td>
                <td>{{ formatDate(user.last_login_at) || 'Never' }}</td>
                <td>₹{{ formatNumber(user.total_balance || 0) }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Deposits Tab -->
      <div v-if="activeTab === 'deposits'" class="tab-content">
        <div class="table-header">
          <h3>Deposit Transactions</h3>
          <div class="table-actions">
            <input 
              type="text" 
              v-model="depositSearch" 
              placeholder="Search deposits..." 
              class="search-input"
            >
            <select v-model="depositStatusFilter" class="filter-select">
              <option value="all">All Status</option>
              <option value="completed">Completed</option>
              <option value="pending">Pending</option>
              <option value="failed">Failed</option>
            </select>
          </div>
        </div>
        <div class="table-container">
          <table class="data-table">
            <thead>
              <tr>
                <th>Transaction ID</th>
                <th>User</th>
                <th>Amount</th>
                <th>Status</th>
                <th>Date</th>
                <th>Description</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="deposit in filteredDeposits" :key="deposit.id">
                <td>{{ deposit.transaction_id }}</td>
                <td>{{ deposit.user_name }}</td>
                <td>₹{{ formatNumber(deposit.amount) }}</td>
                <td>
                  <span class="status-badge" :class="getDepositStatusClass(deposit.status)">
                    {{ deposit.status }}
                  </span>
                </td>
                <td>{{ formatDate(deposit.created_at) }}</td>
                <td>{{ deposit.description || 'N/A' }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Withdrawals Tab -->
      <div v-if="activeTab === 'withdrawals'" class="tab-content">
        <div class="table-header">
          <h3>Withdrawal Transactions</h3>
          <div class="table-actions">
            <input 
              type="text" 
              v-model="withdrawalSearch" 
              placeholder="Search withdrawals..." 
              class="search-input"
            >
            <select v-model="withdrawalStatusFilter" class="filter-select">
              <option value="all">All Status</option>
              <option value="completed">Completed</option>
              <option value="pending">Pending</option>
              <option value="rejected">Rejected</option>
            </select>
          </div>
        </div>
        <div class="table-container">
          <table class="data-table">
            <thead>
              <tr>
                <th>Transaction ID</th>
                <th>User</th>
                <th>Amount</th>
                <th>Status</th>
                <th>Request Date</th>
                <th>Processed Date</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="withdrawal in filteredWithdrawals" :key="withdrawal.id">
                <td>{{ withdrawal.transaction_id }}</td>
                <td>{{ withdrawal.user_name }}</td>
                <td>₹{{ formatNumber(withdrawal.amount) }}</td>
                <td>
                  <span class="status-badge" :class="getWithdrawalStatusClass(withdrawal.status)">
                    {{ withdrawal.status }}
                  </span>
                </td>
                <td>{{ formatDate(withdrawal.created_at) }}</td>
                <td>{{ formatDate(withdrawal.updated_at) || 'Pending' }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="loading-overlay">
      <div class="loading-content">
        <i class="fa-solid fa-spinner fa-spin"></i>
        <p>Loading analytics data...</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount, computed, watch } from 'vue'
import AdminMobileNav from '../../components/AdminMobileNav.vue'

// Reactive Data
const loading = ref(false)
const selectedPeriod = ref('current_month')
const customStartDate = ref('')
const customEndDate = ref('')
const activeTab = ref('users')

// Search and Filter
const userSearch = ref('')
const userStatusFilter = ref('all')
const depositSearch = ref('')
const depositStatusFilter = ref('all')
const withdrawalSearch = ref('')
const withdrawalStatusFilter = ref('all')

// Metrics Data
const totalUsers = ref(0)
const activeUsers = ref(0)
const totalDeposits = ref(0)
const totalWithdrawals = ref(0)
const netRevenue = ref(0)

// Growth Rates
const userGrowthRate = ref(0)
const activeUserGrowth = ref(0)
const depositGrowthRate = ref(0)
const withdrawalGrowthRate = ref(0)
const revenueGrowthRate = ref(0)



// Table Data
const users = ref([])
const deposits = ref([])
const withdrawals = ref([])

// API Base URL - Dynamic based on current domain
const API_BASE = `${window.location.origin}/api`

// Computed Properties
const filteredUsers = computed(() => {
  let filtered = users.value

  if (userSearch.value) {
    const query = userSearch.value.toLowerCase()
    filtered = filtered.filter(user => 
      user.name.toLowerCase().includes(query) ||
      user.email.toLowerCase().includes(query)
    )
  }

  if (userStatusFilter.value !== 'all') {
    filtered = filtered.filter(user => user.status === userStatusFilter.value)
  }

  return filtered
})

const filteredDeposits = computed(() => {
  let filtered = deposits.value

  if (depositSearch.value) {
    const query = depositSearch.value.toLowerCase()
    filtered = filtered.filter(deposit => 
      deposit.transaction_id.toLowerCase().includes(query) ||
      deposit.user_name.toLowerCase().includes(query)
    )
  }

  if (depositStatusFilter.value !== 'all') {
    filtered = filtered.filter(deposit => deposit.status === depositStatusFilter.value)
  }

  return filtered
})

const filteredWithdrawals = computed(() => {
  let filtered = withdrawals.value

  if (withdrawalSearch.value) {
    const query = withdrawalSearch.value.toLowerCase()
    filtered = filtered.filter(withdrawal => 
      withdrawal.transaction_id.toLowerCase().includes(query) ||
      withdrawal.user_name.toLowerCase().includes(query)
    )
  }

  if (withdrawalStatusFilter.value !== 'all') {
    filtered = filtered.filter(withdrawal => withdrawal.status === withdrawalStatusFilter.value)
  }

  return filtered
})

// Methods
const refreshData = async () => {
  await updateData()
}

const updateData = async () => {
  try {
    loading.value = true
    
    const token = localStorage.getItem('access_token')
    if (!token) {
      throw new Error('No access token found')
    }

    // Get analytics data
    const response = await fetch(`${API_BASE}/analytics?period=${selectedPeriod.value}&start_date=${customStartDate.value}&end_date=${customEndDate.value}`, {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
      }
    })

    if (!response.ok) {
      throw new Error('Failed to fetch analytics data')
    }

    const data = await response.json()
    
    if (data.success) {
      // Update metrics
      totalUsers.value = data.metrics.total_users
      activeUsers.value = data.metrics.active_users
      totalDeposits.value = data.metrics.total_deposits
      totalWithdrawals.value = data.metrics.total_withdrawals
      netRevenue.value = data.metrics.net_revenue

      // Update growth rates
      userGrowthRate.value = data.growth_rates.user_growth
      activeUserGrowth.value = data.growth_rates.active_user_growth
      depositGrowthRate.value = data.growth_rates.deposit_growth
      withdrawalGrowthRate.value = data.growth_rates.withdrawal_growth
      revenueGrowthRate.value = data.growth_rates.revenue_growth

      // Update table data
      users.value = data.users
      deposits.value = data.deposits
      withdrawals.value = data.withdrawals


    }
  } catch (error) {
    console.error('Error updating analytics data:', error)
    // Show error message to user
  } finally {
    loading.value = false
  }
}



const exportReport = () => {
  // Generate and download report
  const reportData = {
    period: selectedPeriod.value,
    metrics: {
      totalUsers: totalUsers.value,
      activeUsers: activeUsers.value,
      totalDeposits: totalDeposits.value,
      totalWithdrawals: totalWithdrawals.value,
      netRevenue: netRevenue.value
    },
    users: users.value,
    deposits: deposits.value,
    withdrawals: withdrawals.value
  }

  const blob = new Blob([JSON.stringify(reportData, null, 2)], { type: 'application/json' })
  const url = URL.createObjectURL(blob)
  const a = document.createElement('a')
  a.href = url
  a.download = `analytics-report-${selectedPeriod.value}-${new Date().toISOString().split('T')[0]}.json`
  a.click()
  URL.revokeObjectURL(url)
}

const exportCSV = () => {
  let csvContent = ''
  
  // Add header
  csvContent += 'Analytics Report\n'
  csvContent += `Period: ${selectedPeriod.value}\n`
  csvContent += `Generated: ${new Date().toLocaleString()}\n\n`
  
  // Add metrics
  csvContent += 'Metrics\n'
  csvContent += 'Metric,Value\n'
  csvContent += `Total Users,${totalUsers.value}\n`
  csvContent += `Active Users,${activeUsers.value}\n`
  csvContent += `Total Deposits,₹${formatNumber(totalDeposits.value)}\n`
  csvContent += `Total Withdrawals,₹${formatNumber(totalWithdrawals.value)}\n`
  csvContent += `Net Revenue,₹${formatNumber(netRevenue.value)}\n\n`
  
  // Add users data
  if (users.value.length > 0) {
    csvContent += 'Users Data\n'
    csvContent += 'ID,Name,Email,Status,Registration Date,Last Login,Total Balance\n'
    users.value.forEach(user => {
      csvContent += `${user.id},"${user.name}","${user.email}","${getUserStatusText(user.status)}","${formatDate(user.created_at)}","${formatDate(user.last_login_at) || 'Never'}",₹${formatNumber(user.total_balance || 0)}\n`
    })
    csvContent += '\n'
  }
  
  // Add deposits data
  if (deposits.value.length > 0) {
    csvContent += 'Deposits Data\n'
    csvContent += 'Transaction ID,User,Amount,Status,Date,Description\n'
    deposits.value.forEach(deposit => {
      csvContent += `${deposit.transaction_id},"${deposit.user_name}",₹${formatNumber(deposit.amount)},"${deposit.status}","${formatDate(deposit.created_at)}","${deposit.description || 'N/A'}"\n`
    })
    csvContent += '\n'
  }
  
  // Add withdrawals data
  if (withdrawals.value.length > 0) {
    csvContent += 'Withdrawals Data\n'
    csvContent += 'Transaction ID,User,Amount,Status,Request Date,Processed Date\n'
    withdrawals.value.forEach(withdrawal => {
      csvContent += `${withdrawal.transaction_id},"${withdrawal.user_name}",₹${formatNumber(withdrawal.amount)},"${withdrawal.status}","${formatDate(withdrawal.created_at)}","${formatDate(withdrawal.updated_at) || 'Pending'}"\n`
    })
  }
  
  // Download CSV
  const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' })
  const url = URL.createObjectURL(blob)
  const a = document.createElement('a')
  a.href = url
  a.download = `analytics-report-${selectedPeriod.value}-${new Date().toISOString().split('T')[0]}.csv`
  a.click()
  URL.revokeObjectURL(url)
}

const exportPDF = () => {
  // Create PDF content using jsPDF
  try {
    // For now, we'll create a simple text-based PDF
    // In a real implementation, you'd use jsPDF library
    const pdfContent = `
Analytics Report
Period: ${selectedPeriod.value}
Generated: ${new Date().toLocaleString()}

METRICS:
- Total Users: ${totalUsers.value}
- Active Users: ${activeUsers.value}
- Total Deposits: ₹${formatNumber(totalDeposits.value)}
- Total Withdrawals: ₹${formatNumber(totalWithdrawals.value)}
- Net Revenue: ₹${formatNumber(netRevenue.value)}

USERS: ${users.value.length}
DEPOSITS: ${deposits.value.length}
WITHDRAWALS: ${withdrawals.value.length}
    `
    
    // Create a simple text file as PDF alternative for now
    const blob = new Blob([pdfContent], { type: 'text/plain' })
    const url = URL.createObjectURL(blob)
    const a = document.createElement('a')
    a.href = url
    a.download = `analytics-report-${selectedPeriod.value}-${new Date().toISOString().split('T')[0]}.txt`
    a.click()
    URL.revokeObjectURL(url)
    
    // Show message about PDF generation
    alert('PDF export is being prepared. For now, a text file has been downloaded. Full PDF support will be added soon!')
  } catch (error) {
    console.error('PDF export error:', error)
    alert('PDF export failed. Please try CSV or JSON export instead.')
  }
}

// Utility Functions
const formatNumber = (num) => {
  return new Intl.NumberFormat('en-IN').format(num)
}

const formatDate = (dateString) => {
  if (!dateString) return 'N/A'
  return new Date(dateString).toLocaleDateString('en-IN')
}

const getUserStatusClass = (status) => {
  const statusMap = {
    'A': 'active',
    'I': 'inactive',
    'P': 'pending'
  }
  return statusMap[status] || 'unknown'
}

const getUserStatusText = (status) => {
  const statusMap = {
    'A': 'Active',
    'I': 'Inactive',
    'P': 'Pending'
  }
  return statusMap[status] || 'Unknown'
}

const getDepositStatusClass = (status) => {
  const statusMap = {
    'completed': 'success',
    'pending': 'warning',
    'failed': 'danger'
  }
  return statusMap[status] || 'neutral'
}

const getWithdrawalStatusClass = (status) => {
  const statusMap = {
    'completed': 'success',
    'pending': 'warning',
    'rejected': 'danger'
  }
  return statusMap[status] || 'neutral'
}

// Auto refresh interval
let refreshInterval = null

// Lifecycle
onMounted(async () => {
  await updateData()
  
  // Auto refresh disabled - only manual refresh via button
})

// Cleanup on unmount
onBeforeUnmount(() => {
  if (refreshInterval) {
    clearInterval(refreshInterval)
  }
})

// Watchers
watch([selectedPeriod, customStartDate, customEndDate], () => {
  if (selectedPeriod.value === 'custom' && customStartDate.value && customEndDate.value) {
    updateData()
  } else if (selectedPeriod.value !== 'custom') {
    updateData()
  }
})
</script>

<style scoped>
/* Analytics Screen Styles */
.analytics-screen {
  background: linear-gradient(135deg, #0f0f23 0%, #1a1a2e 50%, #16213e 100%);
  color: white;
  min-height: 100vh;
  padding: 24px;
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
  overflow-x: hidden;
}

/* Header Section */
.analytics-header {
  background: linear-gradient(135deg, rgba(255, 215, 0, 0.1) 0%, rgba(218, 165, 32, 0.05) 100%);
  border-radius: 24px;
  padding: 32px;
  margin-bottom: 24px;
  border: 1px solid rgba(255, 215, 0, 0.2);
  backdrop-filter: blur(20px);
}

.header-content {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.header-left h1 {
  font-size: 2.5rem;
  font-weight: 800;
  background: linear-gradient(135deg, #FFD700, #DAA520);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  margin: 0 0 8px 0;
}

.header-left p {
  font-size: 1.125rem;
  color: #a1a1aa;
  margin: 0;
}

.live-indicator {
  background: linear-gradient(135deg, #ef4444, #dc2626);
  color: white;
  padding: 4px 8px;
  border-radius: 12px;
  font-size: 0.75rem;
  font-weight: 700;
  margin-right: 8px;
  animation: pulse 2s infinite;
}

@keyframes pulse {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.7; }
}

.header-actions {
  display: flex;
  gap: 16px;
}

.export-buttons {
  display: flex;
  gap: 16px;
}

.btn-refresh, .btn-export {
  background: rgba(255, 255, 255, 0.1);
  color: white;
  border: 1px solid rgba(255, 255, 255, 0.2);
  padding: 12px 20px;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.3s ease;
  font-size: 0.875rem;
  font-weight: 500;
}

.btn-refresh:hover, .btn-export:hover {
  background: rgba(255, 255, 255, 0.2);
  border-color: #FFD700;
}

.btn-export {
  background: linear-gradient(135deg, #FFD700, #DAA520);
  color: #0d0d1a;
  border: none;
}

.btn-export:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(255, 215, 0, 0.3);
}

.btn-export.csv {
  background: linear-gradient(135deg, #4f46e5, #6366f1);
  color: white;
  border: none;
}

.btn-export.csv:hover {
  background: linear-gradient(135deg, #6366f1, #4f46e5);
  box-shadow: 0 8px 25px rgba(79, 70, 229, 0.3);
}

.btn-export.pdf {
  background: linear-gradient(135deg, #10b981, #34d399);
  color: #0d0d1a;
  border: none;
}

.btn-export.pdf:hover {
  background: linear-gradient(135deg, #34d399, #10b981);
  box-shadow: 0 8px 25px rgba(16, 185, 129, 0.3);
}

.btn-export.json {
  background: linear-gradient(135deg, #ef4444, #f59e0b);
  color: #0d0d1a;
  border: none;
}

.btn-export.json:hover {
  background: linear-gradient(135deg, #f59e0b, #ef4444);
  box-shadow: 0 8px 25px rgba(239, 68, 68, 0.3);
}

/* Filter Section */
.filter-section {
  background: rgba(255, 255, 255, 0.03);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 16px;
  padding: 24px;
  margin-bottom: 24px;
  backdrop-filter: blur(10px);
}

.filter-controls {
  display: flex;
  gap: 24px;
  align-items: end;
  flex-wrap: wrap;
}

.filter-group {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.filter-label {
  font-size: 0.875rem;
  color: #a1a1aa;
  font-weight: 500;
}

.filter-select, .filter-input {
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.2);
  border-radius: 8px;
  padding: 10px 16px;
  color: white;
  font-size: 0.875rem;
  min-width: 200px;
}

.filter-select:focus, .filter-input:focus {
  outline: none;
  border-color: #FFD700;
}

/* Metrics Section */
.metrics-section {
  margin-bottom: 32px;
}

.metrics-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 16px;
}

.metric-card {
  background: rgba(255, 255, 255, 0.03);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 16px;
  padding: 18px;
  backdrop-filter: blur(10px);
  transition: all 0.3s ease;
  position: relative;
  overflow: hidden;
}

.metric-card::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: linear-gradient(135deg, transparent 0%, rgba(255, 255, 255, 0.02) 100%);
  opacity: 0;
  transition: opacity 0.3s ease;
}

.metric-card:hover::before {
  opacity: 1;
}

.metric-card:hover {
  transform: translateY(-4px);
  border-color: rgba(255, 215, 0, 0.2);
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
}

.metric-icon {
  font-size: 2rem;
  margin-bottom: 12px;
  display: block;
}

.metric-title {
  font-size: 1rem;
  color: #a1a1aa;
  margin: 0 0 12px 0;
  font-weight: 500;
}

.metric-value {
  font-size: 1.75rem;
  font-weight: 700;
  color: white;
  margin-bottom: 6px;
}

.metric-change {
  font-size: 0.875rem;
  font-weight: 500;
  display: flex;
  align-items: center;
  gap: 6px;
}

.metric-change.positive {
  color: #10b981;
}

.metric-change.negative {
  color: #ef4444;
}

.metric-change.neutral {
  color: #6b7280;
}



/* Data Section */
.data-section {
  background: rgba(255, 255, 255, 0.03);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 16px;
  overflow: hidden;
  backdrop-filter: blur(10px);
}

.data-tabs {
  display: flex;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.tab-btn {
  background: none;
  border: none;
  color: #a1a1aa;
  padding: 16px 24px;
  cursor: pointer;
  transition: all 0.3s ease;
  font-size: 0.875rem;
  font-weight: 500;
  display: flex;
  align-items: center;
  gap: 8px;
}

.tab-btn:hover {
  color: white;
  background: rgba(255, 255, 255, 0.05);
}

.tab-btn.active {
  color: #FFD700;
  border-bottom: 2px solid #FFD700;
  background: rgba(255, 215, 0, 0.05);
}

.tab-content {
  padding: 24px;
}

.table-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.table-header h3 {
  font-size: 1.25rem;
  font-weight: 600;
  margin: 0;
  color: white;
}

.table-actions {
  display: flex;
  gap: 16px;
}

.search-input {
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.2);
  border-radius: 8px;
  padding: 8px 16px;
  color: white;
  font-size: 0.875rem;
  min-width: 200px;
}

.search-input:focus {
  outline: none;
  border-color: #FFD700;
}

.table-container {
  overflow-x: auto;
}

.data-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 0.875rem;
}

.data-table th,
.data-table td {
  padding: 12px 16px;
  text-align: left;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.data-table th {
  background: rgba(255, 255, 255, 0.05);
  font-weight: 600;
  color: white;
}

.data-table td {
  color: #a1a1aa;
}

.data-table tbody tr:hover {
  background: rgba(255, 255, 255, 0.02);
}

.status-badge {
  padding: 4px 12px;
  border-radius: 20px;
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: capitalize;
}

.status-badge.active,
.status-badge.success {
  background: rgba(16, 185, 129, 0.2);
  color: #10b981;
  border: 1px solid rgba(16, 185, 129, 0.3);
}

.status-badge.inactive,
.status-badge.failed,
.status-badge.danger {
  background: rgba(239, 68, 68, 0.2);
  color: #ef4444;
  border: 1px solid rgba(239, 68, 68, 0.3);
}

.status-badge.pending,
.status-badge.warning {
  background: rgba(245, 158, 11, 0.2);
  color: #f59e0b;
  border: 1px solid rgba(245, 158, 11, 0.3);
}

.status-badge.neutral {
  background: rgba(107, 114, 128, 0.2);
  color: #6b7280;
  border: 1px solid rgba(107, 114, 128, 0.3);
}

/* Loading State */
.loading-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.8);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 9999;
}

.loading-content {
  text-align: center;
  color: white;
}

.loading-content i {
  font-size: 3rem;
  margin-bottom: 16px;
  color: #FFD700;
}

/* Responsive Design */
@media (max-width: 1200px) {
  
}

@media (max-width: 768px) {
  .analytics-screen {
    padding: 16px;
  }
  
  .analytics-header {
    padding: 24px;
  }
  
  .header-content {
    flex-direction: column;
    text-align: center;
    gap: 20px;
  }
  
  .header-left h1 {
    font-size: 2rem;
  }
  
  .header-actions {
    flex-direction: column;
    gap: 12px;
  }
  
  .export-buttons {
    flex-direction: column;
    gap: 10px;
  }
  
  .filter-controls {
    flex-direction: column;
    align-items: stretch;
    gap: 16px;
  }
  
  .filter-group {
    min-width: auto;
  }
  
  .filter-select, .filter-input {
    min-width: auto;
    width: 100%;
  }
  
  .metrics-grid {
    grid-template-columns: 1fr;
    gap: 16px;
  }
  
  .metric-card {
    padding: 20px;
  }
  
  .metric-value {
    font-size: 1.75rem;
  }
  

  
  .data-tabs {
    flex-direction: column;
    border-bottom: none;
  }
  
  .tab-btn {
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 0;
  }
  
  .tab-btn.active {
    border-bottom: 2px solid #FFD700;
    border-radius: 8px 8px 0 0;
  }
  
  .table-header {
    flex-direction: column;
    gap: 16px;
    align-items: stretch;
  }
  
  .table-actions {
    flex-direction: column;
    gap: 12px;
  }
  
  .search-input, .filter-select {
    width: 100%;
    min-width: auto;
  }
  
  .table-container {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
  }
  
  .data-table {
    min-width: 600px;
  }
  
  .data-table th,
  .data-table td {
    padding: 10px 12px;
    font-size: 0.8rem;
  }
}

@media (max-width: 480px) {
  .analytics-screen {
    padding: 12px;
  }
  
  .analytics-header {
    padding: 20px;
    border-radius: 16px;
  }
  
  .header-left h1 {
    font-size: 1.75rem;
  }
  
  .header-left p {
    font-size: 1rem;
  }
  
  .filter-section {
    padding: 20px;
    border-radius: 12px;
  }
  
  .metrics-grid {
    gap: 12px;
  }
  
  .metric-card {
    padding: 16px;
    border-radius: 12px;
  }
  
  .metric-icon {
    font-size: 2rem;
    margin-bottom: 12px;
  }
  
  .metric-value {
    font-size: 1.5rem;
    margin-bottom: 6px;
  }
  
  .metric-change {
    font-size: 0.8rem;
  }
  

  

  
  .data-section {
    border-radius: 12px;
  }
  
  .tab-content {
    padding: 16px;
  }
  
  .table-header h3 {
    font-size: 1.125rem;
  }
  
  .data-table {
    min-width: 500px;
    font-size: 0.75rem;
  }
  
  .data-table th,
  .data-table td {
    padding: 8px 10px;
  }
  
  .status-badge {
    font-size: 0.7rem;
    padding: 3px 8px;
  }
}

@media (max-width: 360px) {
  .analytics-screen {
    padding: 8px;
  }
  
  .analytics-header {
    padding: 16px;
  }
  
  .header-left h1 {
    font-size: 1.5rem;
  }
  
  .header-left p {
    font-size: 0.9rem;
  }
  
  .filter-section {
    padding: 16px;
  }
  
  .metric-card {
    padding: 12px;
  }
  
  .metric-icon {
    font-size: 1.75rem;
  }
  
  .metric-value {
    font-size: 1.25rem;
  }
  

  
  .tab-content {
    padding: 12px;
  }
  
  .data-table {
    min-width: 450px;
    font-size: 0.7rem;
  }
}

/* Landscape Mobile */
@media (max-width: 768px) and (orientation: landscape) {
  .analytics-header {
    padding: 20px;
  }
  
  .header-left h1 {
    font-size: 1.75rem;
  }
  
  .metrics-grid {
    grid-template-columns: repeat(2, 1fr);
    gap: 12px;
  }
  
  .chart-container {
    height: 200px;
  }
}

/* Tablet Specific */
@media (min-width: 769px) and (max-width: 1024px) {
  .analytics-screen {
    padding: 20px;
  }
  
  .metrics-grid {
    grid-template-columns: repeat(2, 1fr);
    gap: 18px;
  }
  

}

/* High DPI Displays */
@media (-webkit-min-device-pixel-ratio: 2), (min-resolution: 192dpi) {
  .metric-card,
  .data-section {
    border-width: 0.5px;
  }
}

/* Touch Device Optimizations */
@media (hover: none) and (pointer: coarse) {
  .metric-card:hover {
    transform: none;
  }
  
  .metric-card:active {
    transform: scale(0.98);
  }
  
  .tab-btn:active {
    background: rgba(255, 215, 0, 0.1);
  }
}

/* Print Styles */
@media print {
  .analytics-screen {
    background: white;
    color: black;
    padding: 0;
  }
  
  .analytics-header,
  .filter-section,
  .header-actions,
  .export-buttons,
  .data-tabs {
    display: none;
  }
  
  .metrics-grid {
    grid-template-columns: repeat(2, 1fr);
    gap: 1rem;
  }
  
  .metric-card {
    background: white;
    border: 1px solid #000;
    color: black;
    box-shadow: none;
  }
  

  
  .data-table {
    border: 1px solid #000;
  }
  
  .data-table th,
  .data-table td {
    border: 1px solid #ccc;
    color: black;
  }
}
</style>
