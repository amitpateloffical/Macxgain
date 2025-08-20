<template>
  <div class="payment-collector-screen">
    <!-- Header -->
    <div class="page-header">
      <div class="header-content">
        <h1 class="page-title">💸 Payment Collector</h1>
        <p class="page-subtitle">Manage payment collections, barcodes, and banker details</p>
      </div>
      <div class="header-actions">
        <button class="refresh-btn" @click="refreshData" :disabled="isLoading">
          <i class="fas fa-sync-alt" :class="{ 'fa-spin': isLoading }"></i>
          {{ isLoading ? 'Loading...' : 'Refresh' }}
        </button>
      </div>
    </div>

    <!-- Stats Cards -->
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-icon">📊</div>
        <div class="stat-content">
          <div class="stat-number">{{ totalCollections }}</div>
          <div class="stat-label">Total Collections</div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon">💳</div>
        <div class="stat-content">
          <div class="stat-number">{{ totalAmount }}</div>
          <div class="stat-label">Total Amount</div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon">🏦</div>
        <div class="stat-content">
          <div class="stat-number">{{ totalBankers }}</div>
          <div class="stat-label">Active Bankers</div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon">📱</div>
        <div class="stat-content">
          <div class="stat-number">{{ totalBarcodes }}</div>
          <div class="stat-label">Barcodes</div>
        </div>
      </div>
    </div>

    <!-- Main Content Tabs -->
    <div class="content-tabs">
      <div class="tab-buttons">
        <button 
          v-for="tab in tabs" 
          :key="tab.id"
          :class="['tab-btn', { active: activeTab === tab.id }]"
          @click="activeTab = tab.id"
        >
          <i :class="tab.icon"></i>
          {{ tab.label }}
        </button>
      </div>

      <!-- Tab Content -->
      <div class="tab-content">
        <!-- Barcode Management Tab -->
        <div v-if="activeTab === 'barcodes'" class="tab-panel">
          <div class="panel-header">
            <h3>📱 Barcode Management</h3>
            <button class="add-btn" @click="showBarcodeModal = true">
              <i class="fas fa-plus"></i>
              Add New Barcode
            </button>
          </div>
          
          <div class="barcode-grid">
            <div v-for="barcode in barcodes" :key="barcode.id" class="barcode-card">
              <div class="barcode-header">
                <div class="barcode-icon">📱</div>
                <div class="barcode-actions">
                  <button class="action-btn edit-btn" @click="editBarcode(barcode)">
                    <i class="fas fa-edit"></i>
                  </button>
                  <button class="action-btn delete-btn" @click="deleteBarcode(barcode.id)">
                    <i class="fas fa-trash"></i>
                  </button>
                </div>
              </div>
              <div class="barcode-content">
                <h4>{{ barcode.name }}</h4>
                <p class="barcode-number">{{ barcode.barcode_number }}</p>
                <p class="barcode-type">{{ barcode.type }}</p>
                <p class="barcode-status" :class="barcode.status">
                  {{ barcode.status === 'active' ? '🟢 Active' : '🔴 Inactive' }}
                </p>
              </div>
            </div>
          </div>
        </div>

        <!-- Banker Management Tab -->
        <div v-if="activeTab === 'bankers'" class="tab-panel">
          <div class="panel-header">
            <h3>🏦 Banker Management</h3>
            <button class="add-btn" @click="showBankerModal = true">
              <i class="fas fa-plus"></i>
              Add New Banker
            </button>
          </div>
          
          <div class="banker-grid">
            <div v-for="banker in bankers" :key="banker.id" class="banker-card">
              <div class="banker-header">
                <div class="banker-avatar">
                  <span class="avatar-text">{{ getBankerInitials(banker.name) }}</span>
                </div>
                <div class="banker-actions">
                  <button class="action-btn edit-btn" @click="editBanker(banker)">
                    <i class="fas fa-edit"></i>
                  </button>
                  <button class="action-btn delete-btn" @click="deleteBanker(banker.id)">
                    <i class="fas fa-trash"></i>
                  </button>
                </div>
              </div>
              <div class="banker-content">
                <h4>{{ banker.name }}</h4>
                <p class="banker-phone">{{ banker.phone }}</p>
                <p class="banker-bank">{{ banker.bank_name }}</p>
                <p class="banker-status" :class="banker.status">
                  {{ banker.status === 'active' ? '🟢 Active' : '🔴 Inactive' }}
                </p>
              </div>
            </div>
          </div>
        </div>

        <!-- Collections Tab -->
        <div v-if="activeTab === 'collections'" class="tab-panel">
          <div class="panel-header">
            <h3>💰 Payment Collections</h3>
            <button class="add-btn" @click="showCollectionModal = true">
              <i class="fas fa-plus"></i>
              Add Collection
            </button>
          </div>
          
          <div class="collections-table">
            <div class="table-header">
              <div class="header-cell">Date</div>
              <div class="header-cell">Amount</div>
              <div class="header-cell">Banker</div>
              <div class="header-cell">Status</div>
              <div class="header-cell">Actions</div>
            </div>
            
            <div v-for="collection in collections" :key="collection.id" class="table-row">
              <div class="table-cell">{{ formatDate(collection.date) }}</div>
              <div class="table-cell">₹{{ collection.amount }}</div>
              <div class="table-cell">{{ collection.banker_name }}</div>
              <div class="table-cell">
                <span class="status-badge" :class="collection.status">
                  {{ collection.status === 'completed' ? '✅ Completed' : '⏳ Pending' }}
                </span>
              </div>
              <div class="table-cell">
                <button class="action-btn view-btn" @click="viewCollection(collection)">
                  <i class="fas fa-eye"></i>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Barcode Modal -->
    <div v-if="showBarcodeModal" class="modal-overlay" @click="closeBarcodeModal">
      <div class="modal-content" @click.stop>
        <div class="modal-header">
          <h2>{{ editingBarcode ? 'Edit Barcode' : 'Add New Barcode' }}</h2>
          <button class="modal-close" @click="closeBarcodeModal">
            <i class="fas fa-times"></i>
          </button>
        </div>
        
        <div class="modal-body">
          <form @submit.prevent="saveBarcode">
            <div class="form-group">
              <label>Barcode Name</label>
              <input 
                v-model="barcodeForm.name" 
                type="text" 
                placeholder="Enter barcode name"
                required
              />
            </div>
            
            <div class="form-group">
              <label>Barcode Number</label>
              <input 
                v-model="barcodeForm.barcode_number" 
                type="text" 
                placeholder="Enter barcode number"
                required
              />
            </div>
            
            <div class="form-group">
              <label>Type</label>
              <select v-model="barcodeForm.type" required>
                <option value="">Select type</option>
                <option value="UPI">UPI</option>
                <option value="QR">QR Code</option>
                <option value="Bank">Bank Transfer</option>
              </select>
            </div>
            
            <div class="form-group">
              <label>Status</label>
              <select v-model="barcodeForm.status" required>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
              </select>
            </div>
            
            <div class="form-actions">
              <button type="submit" class="save-btn">
                {{ editingBarcode ? 'Update' : 'Save' }}
              </button>
              <button type="button" class="cancel-btn" @click="closeBarcodeModal">
                Cancel
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Banker Modal -->
    <div v-if="showBankerModal" class="modal-overlay" @click="closeBankerModal">
      <div class="modal-content" @click.stop>
        <div class="modal-header">
          <h2>{{ editingBanker ? 'Edit Banker' : 'Add New Banker' }}</h2>
          <button class="modal-close" @click="closeBankerModal">
            <i class="fas fa-times"></i>
          </button>
        </div>
        
        <div class="modal-body">
          <form @submit.prevent="saveBanker">
            <div class="form-group">
              <label>Banker Name</label>
              <input 
                v-model="bankerForm.name" 
                type="text" 
                placeholder="Enter banker name"
                required
              />
            </div>
            
            <div class="form-group">
              <label>Phone Number</label>
              <input 
                v-model="bankerForm.phone" 
                type="tel" 
                placeholder="Enter phone number"
                required
              />
            </div>
            
            <div class="form-group">
              <label>Bank Name</label>
              <input 
                v-model="bankerForm.bank_name" 
                type="text" 
                placeholder="Enter bank name"
                required
              />
            </div>
            
            <div class="form-group">
              <label>Status</label>
              <select v-model="bankerForm.status" required>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
              </select>
            </div>
            
            <div class="form-actions">
              <button type="submit" class="save-btn">
                {{ editingBanker ? 'Update' : 'Save' }}
              </button>
              <button type="button" class="cancel-btn" @click="closeBankerModal">
                Cancel
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Collection Modal -->
    <div v-if="showCollectionModal" class="modal-overlay" @click="closeCollectionModal">
      <div class="modal-content" @click.stop>
        <div class="modal-header">
          <h2>Add New Collection</h2>
          <button class="modal-close" @click="closeCollectionModal">
            <i class="fas fa-times"></i>
          </button>
        </div>
        
        <div class="modal-body">
          <form @submit.prevent="saveCollection">
            <div class="form-group">
              <label>Amount</label>
              <input 
                v-model="collectionForm.amount" 
                type="number" 
                placeholder="Enter amount"
                required
              />
            </div>
            
            <div class="form-group">
              <label>Banker</label>
              <select v-model="collectionForm.banker_id" required>
                <option value="">Select banker</option>
                <option v-for="banker in bankers" :key="banker.id" :value="banker.id">
                  {{ banker.name }} - {{ banker.bank_name }}
                </option>
              </select>
            </div>
            
            <div class="form-group">
              <label>Date</label>
              <input 
                v-model="collectionForm.date" 
                type="date" 
                required
              />
            </div>
            
            <div class="form-group">
              <label>Notes</label>
              <textarea 
                v-model="collectionForm.notes" 
                placeholder="Enter any additional notes"
                rows="3"
              ></textarea>
            </div>
            
            <div class="form-actions">
              <button type="submit" class="save-btn">Save Collection</button>
              <button type="button" class="cancel-btn" @click="closeCollectionModal">
                Cancel
              </button>
            </div>
          </form>
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
      <div class="nav-item" @click="navigateTo('ai_trading')">
        <div class="nav-icon">🤖</div>
        <span class="nav-label">AI Trading</span>
      </div>
      <div class="nav-item active">
        <div class="nav-icon">💸</div>
        <span class="nav-label">Payments</span>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'PaymentCollector',
  data() {
    return {
      isLoading: false,
      activeTab: 'barcodes',
      tabs: [
        { id: 'barcodes', label: 'Barcodes', icon: 'fas fa-barcode' },
        { id: 'bankers', label: 'Bankers', icon: 'fas fa-university' },
        { id: 'collections', label: 'Collections', icon: 'fas fa-money-bill-wave' }
      ],
      
      // Barcode Management
      showBarcodeModal: false,
      editingBarcode: null,
      barcodeForm: {
        name: '',
        barcode_number: '',
        type: '',
        status: 'active'
      },
      barcodes: [
        { id: 1, name: 'UPI QR Code', barcode_number: 'UPI123456789', type: 'UPI', status: 'active' },
        { id: 2, name: 'Bank Transfer', barcode_number: 'BANK987654321', type: 'Bank', status: 'active' }
      ],
      
      // Banker Management
      showBankerModal: false,
      editingBanker: null,
      bankerForm: {
        name: '',
        phone: '',
        bank_name: '',
        status: 'active'
      },
      bankers: [
        { id: 1, name: 'John Doe', phone: '+91 98765 43210', bank_name: 'HDFC Bank', status: 'active' },
        { id: 2, name: 'Jane Smith', phone: '+91 87654 32109', bank_name: 'ICICI Bank', status: 'active' }
      ],
      
      // Collections
      showCollectionModal: false,
      collectionForm: {
        amount: '',
        banker_id: '',
        date: '',
        notes: ''
      },
      collections: [
        { id: 1, date: '2024-01-15', amount: '50000', banker_name: 'John Doe', status: 'completed' },
        { id: 2, date: '2024-01-16', amount: '75000', banker_name: 'Jane Smith', status: 'pending' }
      ]
    }
  },
  computed: {
    totalCollections() {
      return this.collections.length;
    },
    totalAmount() {
      return this.collections.reduce((sum, collection) => sum + parseInt(collection.amount), 0).toLocaleString();
    },
    totalBankers() {
      return this.bankers.filter(banker => banker.status === 'active').length;
    },
    totalBarcodes() {
      return this.barcodes.filter(barcode => barcode.status === 'active').length;
    }
  },
  mounted() {
    this.refreshData();
  },
  methods: {
    refreshData() {
      this.isLoading = true;
      // Simulate API call
      setTimeout(() => {
        this.isLoading = false;
      }, 1000);
    },
    
    // Barcode Methods
    editBarcode(barcode) {
      this.editingBarcode = barcode;
      this.barcodeForm = { ...barcode };
      this.showBarcodeModal = true;
    },
    
    saveBarcode() {
      if (this.editingBarcode) {
        // Update existing barcode
        const index = this.barcodes.findIndex(b => b.id === this.editingBarcode.id);
        if (index !== -1) {
          this.barcodes[index] = { ...this.barcodeForm, id: this.editingBarcode.id };
        }
      } else {
        // Add new barcode
        const newBarcode = {
          ...this.barcodeForm,
          id: Date.now()
        };
        this.barcodes.push(newBarcode);
      }
      
      this.closeBarcodeModal();
    },
    
    deleteBarcode(id) {
      if (confirm('Are you sure you want to delete this barcode?')) {
        this.barcodes = this.barcodes.filter(b => b.id !== id);
      }
    },
    
    closeBarcodeModal() {
      this.showBarcodeModal = false;
      this.editingBarcode = null;
      this.barcodeForm = {
        name: '',
        barcode_number: '',
        type: '',
        status: 'active'
      };
    },
    
    // Banker Methods
    editBanker(banker) {
      this.editingBanker = banker;
      this.bankerForm = { ...banker };
      this.showBankerModal = true;
    },
    
    saveBanker() {
      if (this.editingBanker) {
        // Update existing banker
        const index = this.bankers.findIndex(b => b.id === this.editingBanker.id);
        if (index !== -1) {
          this.bankers[index] = { ...this.bankerForm, id: this.editingBanker.id };
        }
      } else {
        // Add new banker
        const newBanker = {
          ...this.bankerForm,
          id: Date.now()
        };
        this.bankers.push(newBanker);
      }
      
      this.closeBankerModal();
    },
    
    deleteBanker(id) {
      if (confirm('Are you sure you want to delete this banker?')) {
        this.bankers = this.bankers.filter(b => b.id !== id);
      }
    },
    
    closeBankerModal() {
      this.showBankerModal = false;
      this.editingBanker = null;
      this.bankerForm = {
        name: '',
        phone: '',
        bank_name: '',
        status: 'active'
      };
    },
    
    // Collection Methods
    saveCollection() {
      const banker = this.bankers.find(b => b.id == this.collectionForm.banker_id);
      const newCollection = {
        ...this.collectionForm,
        id: Date.now(),
        banker_name: banker ? banker.name : 'Unknown',
        status: 'pending'
      };
      
      this.collections.push(newCollection);
      this.closeCollectionModal();
    },
    
    closeCollectionModal() {
      this.showCollectionModal = false;
      this.collectionForm = {
        amount: '',
        banker_id: '',
        date: '',
        notes: ''
      };
    },
    
    viewCollection(collection) {
      console.log('View collection:', collection);
      // Implement collection details view
    },
    
    // Utility Methods
    getBankerInitials(name) {
      return name.split(' ').map(n => n[0]).join('').toUpperCase();
    },
    
    formatDate(dateString) {
      return new Date(dateString).toLocaleDateString('en-IN');
    },
    
    navigateTo(route) {
      this.$router.push(`/admin/${route}`);
    }
  }
}
</script>

<style scoped>
/* Payment Collector Page Styles */
.payment-collector-screen {
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

/* Content Tabs */
.content-tabs {
  background: rgba(255, 255, 255, 0.02);
  border-radius: 12px;
  overflow: hidden;
}

.tab-buttons {
  display: flex;
  background: rgba(0, 0, 0, 0.3);
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.tab-btn {
  flex: 1;
  background: none;
  border: none;
  color: rgba(255, 255, 255, 0.7);
  padding: 16px 20px;
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  font-size: 14px;
  font-weight: 500;
}

.tab-btn:hover {
  color: white;
  background: rgba(255, 255, 255, 0.05);
}

.tab-btn.active {
  color: #00ff88;
  background: rgba(0, 255, 136, 0.1);
  border-bottom: 2px solid #00ff88;
}

.tab-content {
  padding: 24px;
}

.tab-panel {
  min-height: 400px;
}

.panel-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24px;
}

.panel-header h3 {
  font-size: 20px;
  font-weight: 600;
  color: white;
  margin: 0;
}

.add-btn {
  background: linear-gradient(135deg, #00ff88 0%, #00d4aa 100%);
  color: #0d0d1a;
  border: none;
  padding: 10px 20px;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 600;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  gap: 8px;
}

.add-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(0, 255, 136, 0.3);
}

/* Barcode Grid */
.barcode-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 20px;
}

.barcode-card {
  background: linear-gradient(145deg, #101022, #0d0d1a);
  border: 1px solid rgba(0, 255, 136, 0.2);
  border-radius: 12px;
  padding: 20px;
  transition: all 0.3s ease;
}

.barcode-card:hover {
  border-color: #00ff88;
  transform: translateY(-2px);
}

.barcode-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 16px;
}

.barcode-icon {
  font-size: 24px;
}

.barcode-actions {
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

.edit-btn {
  background: rgba(74, 144, 226, 0.2);
  color: #4a90e2;
}

.edit-btn:hover {
  background: rgba(74, 144, 226, 0.3);
}

.delete-btn {
  background: rgba(255, 107, 107, 0.2);
  color: #ff6b6b;
}

.delete-btn:hover {
  background: rgba(255, 107, 107, 0.3);
}

.barcode-content h4 {
  font-size: 16px;
  font-weight: 600;
  color: white;
  margin: 0 0 8px 0;
}

.barcode-number {
  color: #00ff88;
  font-family: 'Courier New', monospace;
  font-size: 14px;
  margin: 0 0 4px 0;
}

.barcode-type {
  color: rgba(255, 255, 255, 0.7);
  font-size: 12px;
  margin: 0 0 8px 0;
}

.barcode-status {
  font-size: 12px;
  font-weight: 500;
}

/* Banker Grid */
.banker-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 20px;
}

.banker-card {
  background: linear-gradient(145deg, #101022, #0d0d1a);
  border: 1px solid rgba(0, 255, 136, 0.2);
  border-radius: 12px;
  padding: 20px;
  transition: all 0.3s ease;
}

.banker-card:hover {
  border-color: #00ff88;
  transform: translateY(-2px);
}

.banker-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 16px;
}

.banker-avatar {
  width: 40px;
  height: 40px;
  background: linear-gradient(135deg, #00ff88 0%, #00d4aa 100%);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #0d0d1a;
  font-weight: bold;
  font-size: 16px;
}

.banker-content h4 {
  font-size: 16px;
  font-weight: 600;
  color: white;
  margin: 0 0 8px 0;
}

.banker-phone {
  color: rgba(255, 255, 255, 0.8);
  font-size: 14px;
  margin: 0 0 4px 0;
}

.banker-bank {
  color: rgba(255, 255, 255, 0.7);
  font-size: 12px;
  margin: 0 0 8px 0;
}

.banker-status {
  font-size: 12px;
  font-weight: 500;
}

/* Collections Table */
.collections-table {
  background: rgba(255, 255, 255, 0.02);
  border-radius: 12px;
  overflow: hidden;
}

.table-header {
  display: grid;
  grid-template-columns: 1fr 1fr 2fr 1fr 1fr;
  gap: 16px;
  padding: 16px 20px;
  background: rgba(0, 255, 136, 0.05);
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
  font-weight: 600;
  color: rgba(255, 255, 255, 0.8);
}

.table-row {
  display: grid;
  grid-template-columns: 1fr 1fr 2fr 1fr 1fr;
  gap: 16px;
  padding: 16px 20px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.05);
  transition: all 0.3s ease;
}

.table-row:hover {
  background: rgba(255, 255, 255, 0.02);
}

.table-cell {
  display: flex;
  align-items: center;
}

.status-badge {
  padding: 4px 12px;
  border-radius: 12px;
  font-size: 12px;
  font-weight: 500;
}

.status-badge.completed {
  background: rgba(0, 255, 136, 0.2);
  color: #00ff88;
}

.status-badge.pending {
  background: rgba(255, 193, 7, 0.2);
  color: #ffc107;
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
  max-width: 500px;
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

.modal-header h2 {
  font-size: 20px;
  font-weight: 700;
  color: #00ff88;
  margin: 0;
}

.modal-close {
  background: rgba(255, 255, 255, 0.1);
  border: none;
  color: rgba(255, 255, 255, 0.7);
  width: 32px;
  height: 32px;
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

.form-group {
  margin-bottom: 20px;
}

.form-group label {
  display: block;
  color: rgba(255, 255, 255, 0.8);
  font-size: 14px;
  font-weight: 500;
  margin-bottom: 8px;
}

.form-group input,
.form-group select,
.form-group textarea {
  width: 100%;
  padding: 12px 16px;
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 8px;
  color: white;
  font-size: 14px;
  transition: all 0.3s ease;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
  outline: none;
  border-color: #00ff88;
  background: rgba(0, 255, 136, 0.05);
}

.form-group textarea {
  resize: vertical;
  min-height: 80px;
}

.form-actions {
  display: flex;
  gap: 12px;
  justify-content: flex-end;
  margin-top: 24px;
}

.save-btn {
  background: linear-gradient(135deg, #00ff88 0%, #00d4aa 100%);
  color: #0d0d1a;
  border: none;
  padding: 12px 24px;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 600;
  transition: all 0.3s ease;
}

.save-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(0, 255, 136, 0.3);
}

.cancel-btn {
  background: rgba(255, 255, 255, 0.1);
  color: rgba(255, 255, 255, 0.8);
  border: none;
  padding: 12px 24px;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 600;
  transition: all 0.3s ease;
}

.cancel-btn:hover {
  background: rgba(255, 255, 255, 0.2);
  color: white;
}

/* Mobile Bottom Navigation */
.mobile-bottom-nav {
  position: fixed;
  bottom: 0;
  left: 0;
  right: 0;
  background: linear-gradient(145deg, #101022, #0d0d1a);
  border-top: 1px solid rgba(0, 255, 136, 0.2);
  display: grid;
  grid-template-columns: repeat(8, 1fr);
  gap: 4px;
  padding: 16px 8px;
  z-index: 100;
}

.nav-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 4px;
  cursor: pointer;
  transition: all 0.3s ease;
  padding: 8px 4px;
  border-radius: 8px;
}

.nav-item:hover {
  background: rgba(0, 255, 136, 0.1);
}

.nav-item.active {
  background: rgba(0, 255, 136, 0.2);
  color: #00ff88;
}

.nav-icon {
  font-size: 20px;
}

.nav-label {
  font-size: 10px;
  text-align: center;
  line-height: 1.2;
}

/* Responsive Design */
@media (max-width: 768px) {
  .payment-collector-screen {
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
  
  .tab-buttons {
    flex-direction: column;
  }
  
  .tab-btn {
    padding: 12px 16px;
    font-size: 13px;
  }
  
  .tab-content {
    padding: 16px;
  }
  
  .panel-header {
    flex-direction: column;
    gap: 16px;
    align-items: flex-start;
  }
  
  .barcode-grid,
  .banker-grid {
    grid-template-columns: 1fr;
    gap: 16px;
  }
  
  .table-header,
  .table-row {
    grid-template-columns: 1fr;
    gap: 8px;
  }
  
  .mobile-bottom-nav {
    grid-template-columns: repeat(4, 1fr);
    gap: 8px;
    padding: 12px 8px;
  }
  
  .nav-item {
    padding: 6px 4px;
  }
  
  .nav-icon {
    font-size: 18px;
  }
  
  .nav-label {
    font-size: 9px;
  }
}

@media (max-width: 480px) {
  .stats-grid {
    grid-template-columns: 1fr;
  }
  
  .stat-card {
    padding: 12px;
  }
  
  .stat-icon {
    width: 36px;
    height: 36px;
    font-size: 20px;
  }
  
  .stat-number {
    font-size: 20px;
  }
  
  .stat-label {
    font-size: 12px;
  }
  
  .modal-content {
    max-width: 95vw;
    margin: 10px;
  }
  
  .modal-header {
    padding: 20px 20px 16px 20px;
  }
  
  .modal-body {
    padding: 20px;
  }
  
  .form-actions {
    flex-direction: column;
    gap: 8px;
  }
  
  .save-btn,
  .cancel-btn {
    width: 100%;
    padding: 10px 20px;
    font-size: 14px;
  }
}
</style>
