<template>
  <div class="payment-collector-screen">
    <!-- Header Section -->
    <div class="page-header">
      <div class="header-content">
        <div class="header-left">
          <button class="back-btn" @click="navigateTo('/admin/dashboard')">
            <i class="fas fa-arrow-left"></i>
          </button>
          <div class="header-info">
            <h1 class="page-title">💳 Payment Collector</h1>
            <p class="page-subtitle">Manage payment collection details and bank accounts</p>
          </div>
        </div>
        <div class="header-right">
          <button class="add-btn" @click="showAddForm = true">
            <i class="fas fa-plus"></i>
            Add Payment Details
          </button>
        </div>
      </div>
    </div>

    <!-- Mobile Navigation Component -->
    <AdminMobileNav />

    <!-- Add/Edit Form Modal -->
    <div v-if="showAddForm || editingCollector" class="modal-overlay" @click="closeForm">
      <div class="modal-content" @click.stop>
        <div class="modal-header">
          <h3>{{ editingCollector ? 'Edit' : 'Add' }} Payment Details</h3>
          <button class="close-btn" @click="closeForm">
            <i class="fas fa-times"></i>
          </button>
        </div>

        <form @submit.prevent="submitForm" class="payment-form" novalidate>
          <div class="form-grid">
            <div class="form-group">
              <label class="form-label">Bank Name</label>
              <input 
                v-model="formData.bank_name" 
                type="text" 
                class="form-input"
                placeholder="e.g., State Bank of India"
              >
            </div>

            <div class="form-group">
              <label class="form-label">Account Holder Name</label>
              <input 
                v-model="formData.account_holder_name" 
                type="text" 
                class="form-input"
                placeholder="e.g., Macxgain Technologies"
              >
            </div>

            <div class="form-group">
              <label class="form-label">Account Number</label>
              <input 
                v-model="formData.account_number" 
                type="text" 
                class="form-input"
                placeholder="e.g., 1234567890123456"
              >
            </div>

            <div class="form-group">
              <label class="form-label">IFSC Code</label>
              <input 
                v-model="formData.ifsc_code" 
                type="text" 
                class="form-input"
                placeholder="e.g., SBIN0001234"
              >
            </div>

            <div class="form-group">
              <label class="form-label">Branch Name</label>
              <input 
                v-model="formData.branch_name" 
                type="text" 
                class="form-input"
                placeholder="e.g., Mumbai Main Branch"
              >
            </div>

            <div class="form-group">
              <label class="form-label">QR Code/UPI ID</label>
              <input 
                v-model="formData.qr_code" 
                type="text" 
                class="form-input"
                placeholder="e.g., macxgain@paytm or UPI QR code data"
              >
            </div>
          </div>

          <div class="form-group full-width">
            <label class="form-label">Barcode/QR Image</label>
            <div class="file-upload-area">
              <input 
                type="file" 
                ref="fileInput"
                @change="handleFileUpload"
                accept="image/*"
                class="file-input"
              >
              <div class="upload-placeholder" v-if="!formData.barcode_image">
                <i class="fas fa-cloud-upload-alt"></i>
                <p>Click to upload barcode/QR image</p>
              </div>
              <div v-if="formData.barcode_image" class="image-preview">
                <img :src="getImageSrc(formData.barcode_image)" alt="Barcode Preview" />
                <button type="button" @click="removeImage" class="remove-img-btn">
                  <i class="fas fa-times"></i>
                </button>
              </div>
            </div>
          </div>

          <div class="form-group full-width">
            <label class="form-label">Notes</label>
            <textarea 
              v-model="formData.notes" 
              class="form-textarea"
              placeholder="Additional notes or instructions..."
              rows="3"
            ></textarea>
          </div>

          <div class="form-group full-width">
            <label class="checkbox-label">
              <input 
                v-model="formData.is_primary" 
                type="checkbox" 
                class="form-checkbox"
              >
              <span class="checkbox-text">Mark as Primary Payment Method</span>
            </label>
          </div>

          <div class="form-actions">
            <button type="button" @click="closeForm" class="btn-cancel">
              Cancel
            </button>
            <button type="submit" class="btn-submit" :disabled="loading">
              <i v-if="loading" class="fas fa-spinner fa-spin"></i>
              {{ editingCollector ? 'Update' : 'Add' }} Payment Details
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Payment Collectors List -->
    <div class="collectors-section">
      <div class="section-header">
        <h2>Payment Collection Methods</h2>
        <div class="collectors-count">{{ paymentCollectors.length }} methods configured</div>
      </div>

      <div v-if="paymentCollectors.length === 0" class="empty-state">
        <div class="empty-icon">💳</div>
        <h3>No Payment Methods</h3>
        <p>Add your first payment collection method to get started</p>
        <button class="btn-primary" @click="showAddForm = true">
          <i class="fas fa-plus"></i>
          Add Payment Method
        </button>
      </div>

      <div v-else class="collectors-grid">
        <div 
          v-for="collector in paymentCollectors" 
          :key="collector.id"
          class="collector-card"
          :class="{ 'primary': collector.is_primary, 'inactive': !collector.is_active }"
        >
          <div class="card-header">
            <div class="bank-info">
              <h3 class="bank-name">{{ collector.bank_name }}</h3>
              <div class="account-holder">{{ collector.account_holder_name }}</div>
            </div>
            <div class="card-badges">
              <span v-if="collector.is_primary" class="badge primary">
                <i class="fas fa-star"></i>
                Primary
              </span>
              <span class="badge" :class="collector.is_active ? 'active' : 'inactive'">
                {{ collector.is_active ? 'Active' : 'Inactive' }}
              </span>
            </div>
          </div>

          <div class="card-content">
            <div class="account-details">
              <div class="detail-row">
                <span class="label">Account Number:</span>
                <span class="value">{{ maskAccountNumber(collector.account_number) }}</span>
              </div>
              <div class="detail-row">
                <span class="label">IFSC Code:</span>
                <span class="value">{{ collector.ifsc_code }}</span>
              </div>
              <div v-if="collector.branch_name" class="detail-row">
                <span class="label">Branch:</span>
                <span class="value">{{ collector.branch_name }}</span>
              </div>
              <div v-if="collector.qr_code" class="detail-row">
                <span class="label">UPI/QR:</span>
                <span class="value">{{ collector.qr_code }}</span>
              </div>
            </div>

            <div v-if="collector.barcode_image" class="barcode-section">
              <div class="barcode-label">Payment QR/Barcode:</div>
              <img 
                :src="getImageSrc(collector.barcode_image)" 
                alt="Payment Barcode" 
                class="barcode-image"
                @error="handleImageError"
                @load="handleImageLoad"
              >
            </div>

            <div v-if="collector.notes" class="notes-section">
              <div class="notes-label">Notes:</div>
              <div class="notes-text">{{ collector.notes }}</div>
            </div>
          </div>

          <div class="card-actions">
            <button 
              v-if="!collector.is_primary && collector.is_active"
              @click="markAsPrimary(collector.id)"
              class="action-btn primary-btn"
              :disabled="loading"
            >
              <i class="fas fa-star"></i>
              Make Primary
            </button>
            
            <button 
              @click="toggleStatus(collector.id)"
              class="action-btn status-btn"
              :class="collector.is_active ? 'deactivate' : 'activate'"
              :disabled="loading"
            >
              <i :class="collector.is_active ? 'fas fa-pause' : 'fas fa-play'"></i>
              {{ collector.is_active ? 'Deactivate' : 'Activate' }}
            </button>

            <button 
              @click="editCollector(collector)"
              class="action-btn edit-btn"
            >
              <i class="fas fa-edit"></i>
              Edit
            </button>

            <button 
              @click="deleteCollector(collector.id)"
              class="action-btn delete-btn"
              :disabled="loading"
            >
              <i class="fas fa-trash"></i>
              Delete
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Loading Overlay -->
    <div v-if="loading" class="loading-overlay">
      <div class="loading-spinner">
        <i class="fas fa-spinner fa-spin"></i>
        <p>Processing...</p>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
import AdminMobileNav from '../../components/AdminMobileNav.vue';

export default {
  name: 'PaymentCollector',
  components: {
    AdminMobileNav
  },
  data() {
    return {
      loading: false,
      showAddForm: false,
      editingCollector: null,
      paymentCollectors: [],
      formData: {
        bank_name: '',
        account_holder_name: '',
        account_number: '',
        ifsc_code: '',
        branch_name: '',
        barcode_image: '',
        qr_code: '',
        is_primary: false,
        notes: ''
      }
    };
  },
  mounted() {
    this.loadPaymentCollectors();
  },
  methods: {
    async loadPaymentCollectors() {
      try {
        this.loading = true;
        const token = localStorage.getItem('access_token');
        
        const response = await axios.get('/api/admin-payment-collectors', {
          headers: {
            'Authorization': `Bearer ${token}`,
            'Accept': 'application/json'
          }
        });

        if (response.data.success) {
          this.paymentCollectors = response.data.data;
        }

      } catch (error) {
        console.error('Error loading payment collectors:', error);
        this.$toast?.error('Failed to load payment collectors');
      } finally {
        this.loading = false;
      }
    },

    async submitForm() {
      try {
        this.loading = true;
        const token = localStorage.getItem('access_token');
        
        const url = this.editingCollector 
          ? `/api/admin-payment-collectors/${this.editingCollector.id}`
          : '/api/admin-payment-collectors';
          
        const method = this.editingCollector ? 'PUT' : 'POST';

        const response = await axios({
          method,
          url,
          data: this.formData,
          headers: {
            'Authorization': `Bearer ${token}`,
            'Accept': 'application/json',
            'Content-Type': 'application/json'
          }
        });

        if (response.data.success) {
          this.$toast?.success(response.data.message);
          this.closeForm();
          await this.loadPaymentCollectors();
        }

      } catch (error) {
        console.error('Error saving payment collector:', error);
        this.$toast?.error(error.response?.data?.message || 'Failed to save payment collector');
      } finally {
        this.loading = false;
      }
    },

    async markAsPrimary(id) {
      try {
        this.loading = true;
        const token = localStorage.getItem('access_token');

        const response = await axios.patch(`/api/admin-payment-collectors/${id}/primary`, {}, {
          headers: {
            'Authorization': `Bearer ${token}`,
            'Accept': 'application/json'
          }
        });

        if (response.data.success) {
          this.$toast?.success('Payment method marked as primary');
          await this.loadPaymentCollectors();
        }

      } catch (error) {
        console.error('Error marking as primary:', error);
        this.$toast?.error('Failed to mark as primary');
      } finally {
        this.loading = false;
      }
    },

    async toggleStatus(id) {
      try {
        this.loading = true;
        const token = localStorage.getItem('access_token');

        const response = await axios.patch(`/api/admin-payment-collectors/${id}/toggle-status`, {}, {
          headers: {
            'Authorization': `Bearer ${token}`,
            'Accept': 'application/json'
          }
        });

        if (response.data.success) {
          this.$toast?.success('Status updated successfully');
          await this.loadPaymentCollectors();
        }

      } catch (error) {
        console.error('Error toggling status:', error);
        this.$toast?.error('Failed to update status');
      } finally {
        this.loading = false;
      }
    },

    async deleteCollector(id) {
      if (!confirm('Are you sure you want to delete this payment method?')) {
        return;
      }

      try {
        this.loading = true;
        const token = localStorage.getItem('access_token');

        const response = await axios.delete(`/api/admin-payment-collectors/${id}`, {
          headers: {
            'Authorization': `Bearer ${token}`,
            'Accept': 'application/json'
          }
        });

        if (response.data.success) {
          this.$toast?.success('Payment method deleted successfully');
          await this.loadPaymentCollectors();
        }

      } catch (error) {
        console.error('Error deleting payment collector:', error);
        this.$toast?.error('Failed to delete payment method');
      } finally {
        this.loading = false;
      }
    },

    editCollector(collector) {
      this.editingCollector = collector;
      this.formData = { ...collector };
      this.showAddForm = false;
    },

    closeForm() {
      this.showAddForm = false;
      this.editingCollector = null;
      this.resetForm();
    },

    resetForm() {
      this.formData = {
        bank_name: '',
        account_holder_name: '',
        account_number: '',
        ifsc_code: '',
        branch_name: '',
        barcode_image: '',
        qr_code: '',
        is_primary: false,
        notes: ''
      };
    },

    handleFileUpload(event) {
      const file = event.target.files[0];
      if (file) {
        const reader = new FileReader();
        reader.onload = (e) => {
          this.formData.barcode_image = e.target.result;
        };
        reader.readAsDataURL(file);
      }
    },

    removeImage() {
      this.formData.barcode_image = '';
      this.$refs.fileInput.value = '';
    },

    getImageSrc(imagePath) {
      if (!imagePath) return '';
      
      // If it's a base64 data URL, return as is
      if (imagePath.startsWith('data:')) {
        return imagePath;
      }
      
      // If it's a file path, prepend storage URL
      return `${window.location.origin}/storage/${imagePath}`;
    },

    maskAccountNumber(accountNumber) {
      if (!accountNumber) return '';
      const length = accountNumber.length;
      if (length <= 4) return accountNumber;
      return '*'.repeat(length - 4) + accountNumber.slice(-4);
    },

    handleImageError(event) {
      console.error('Image failed to load:', event.target.src);
      event.target.style.display = 'none';
      // Show fallback or error message
      const parent = event.target.parentNode;
      if (!parent.querySelector('.image-error')) {
        const errorDiv = document.createElement('div');
        errorDiv.className = 'image-error';
        errorDiv.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Image not available';
        parent.appendChild(errorDiv);
      }
    },

    handleImageLoad(event) {
      console.log('Image loaded successfully:', event.target.src);
    },

    navigateTo(path) {
      this.$router.push(path);
    }
  }
};
</script>

<style scoped>
.payment-collector-screen {
  min-height: 100vh;
  background: linear-gradient(135deg, var(--color-bg-primary, #0f0f23) 0%, var(--color-bg-tertiary, #1a1a2e) 50%, var(--color-bg-quaternary, #16213e) 100%);
  color: var(--color-text-primary, #ffffff);
  padding: 20px;
}

/* Header */
.page-header {
  background: rgba(255, 255, 255, 0.05);
  backdrop-filter: blur(10px);
  border-radius: 20px;
  padding: 25px;
  margin-bottom: 30px;
  border: 1px solid rgba(255, 255, 255, 0.1);
}

.header-content {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.header-left {
  display: flex;
  align-items: center;
  gap: 20px;
}

.back-btn {
  background: rgba(255, 255, 255, 0.1);
  border: none;
  border-radius: 12px;
  padding: 12px;
  color: #ffffff;
  cursor: pointer;
  transition: all 0.3s ease;
}

.back-btn:hover {
  background: rgba(255, 255, 255, 0.2);
  transform: translateX(-2px);
}

.page-title {
  font-size: 2rem;
  font-weight: 700;
  margin: 0;
  background: linear-gradient(135deg, var(--color-primary, #FFD700), var(--color-primary-light, #00d4ff));
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
}

.page-subtitle {
  color: var(--color-text-secondary, rgba(255, 255, 255, 0.7));
  margin: 5px 0 0 0;
}

.add-btn {
  background: linear-gradient(135deg, var(--color-primary, #FFD700), var(--color-primary-light, #00d4ff));
  border: none;
  border-radius: 12px;
  padding: 12px 20px;
  color: var(--color-bg-primary, #000000);
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  gap: 8px;
}

.add-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 5px 15px rgba(var(--color-primary-rgb, 255, 215, 0), 0.3);
}

/* Modal */
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
}

.modal-content {
  background: linear-gradient(135deg, var(--color-bg-tertiary, #1a1a2e), var(--color-bg-quaternary, #16213e));
  border-radius: 20px;
  padding: 30px;
  width: 90%;
  max-width: 600px;
  max-height: 90vh;
  overflow-y: auto;
  border: 1px solid var(--color-border-secondary, rgba(255, 255, 255, 0.1));
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 25px;
  padding-bottom: 15px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.modal-header h3 {
  font-size: 1.5rem;
  font-weight: 600;
  margin: 0;
  color: #ffffff;
}

.close-btn {
  background: rgba(255, 255, 255, 0.1);
  border: none;
  border-radius: 8px;
  padding: 8px;
  color: #ffffff;
  cursor: pointer;
  transition: all 0.3s ease;
}

.close-btn:hover {
  background: rgba(255, 68, 68, 0.2);
  color: #ff4444;
}

/* Form */
.form-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 20px;
  margin-bottom: 20px;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.form-group.full-width {
  grid-column: 1 / -1;
}

.form-label {
  font-size: 0.9rem;
  font-weight: 500;
  color: rgba(255, 255, 255, 0.8);
}

.form-input,
.form-textarea {
  background: rgba(255, 255, 255, 0.1);
  border: 1px solid rgba(255, 255, 255, 0.2);
  border-radius: 8px;
  padding: 12px;
  color: #ffffff;
  font-size: 0.9rem;
}

.form-input:focus,
.form-textarea:focus {
  outline: none;
  border-color: var(--color-primary, #FFD700);
  box-shadow: 0 0 0 3px rgba(var(--color-primary-rgb, 255, 215, 0), 0.1);
}

.form-input::placeholder,
.form-textarea::placeholder {
  color: rgba(255, 255, 255, 0.5);
}

.file-upload-area {
  border: 2px dashed rgba(255, 255, 255, 0.3);
  border-radius: 12px;
  padding: 20px;
  text-align: center;
  cursor: pointer;
  transition: all 0.3s ease;
  position: relative;
}

.file-upload-area:hover {
  border-color: var(--color-primary, #FFD700);
  background: rgba(var(--color-primary-rgb, 255, 215, 0), 0.05);
}

.file-input {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  opacity: 0;
  cursor: pointer;
}

.upload-placeholder {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 10px;
  color: rgba(255, 255, 255, 0.6);
}

.upload-placeholder i {
  font-size: 2rem;
  color: var(--color-primary, #FFD700);
}

.image-preview {
  position: relative;
  display: inline-block;
}

.image-preview img {
  max-width: 200px;
  max-height: 150px;
  border-radius: 8px;
}

.remove-img-btn {
  position: absolute;
  top: -8px;
  right: -8px;
  background: #ff4444;
  border: none;
  border-radius: 50%;
  width: 24px;
  height: 24px;
  color: white;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
}

.checkbox-label {
  display: flex;
  align-items: center;
  gap: 10px;
  cursor: pointer;
}

.form-checkbox {
  width: 18px;
  height: 18px;
  accent-color: var(--color-primary, #FFD700);
}

.checkbox-text {
  color: rgba(255, 255, 255, 0.8);
  font-weight: 500;
}

.form-actions {
  display: flex;
  gap: 15px;
  justify-content: flex-end;
  margin-top: 25px;
  padding-top: 20px;
  border-top: 1px solid rgba(255, 255, 255, 0.1);
}

.btn-cancel {
  background: rgba(255, 255, 255, 0.1);
  border: 1px solid rgba(255, 255, 255, 0.2);
  border-radius: 8px;
  padding: 12px 24px;
  color: #ffffff;
  cursor: pointer;
  transition: all 0.3s ease;
}

.btn-cancel:hover {
  background: rgba(255, 255, 255, 0.2);
}

.btn-submit {
  background: linear-gradient(135deg, var(--color-primary, #FFD700), var(--color-primary-light, #00d4ff));
  border: none;
  border-radius: 8px;
  padding: 12px 24px;
  color: var(--color-bg-primary, #000000);
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  gap: 8px;
}

.btn-submit:hover {
  transform: translateY(-2px);
  box-shadow: 0 5px 15px rgba(var(--color-primary-rgb, 255, 215, 0), 0.3);
}

.btn-submit:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

/* Collectors Section */
.collectors-section {
  background: rgba(255, 255, 255, 0.05);
  backdrop-filter: blur(10px);
  border-radius: 20px;
  padding: 25px;
  border: 1px solid rgba(255, 255, 255, 0.1);
}

.section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 25px;
  padding-bottom: 15px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.section-header h2 {
  font-size: 1.5rem;
  font-weight: 600;
  margin: 0;
  color: #ffffff;
}

.collectors-count {
  color: rgba(255, 255, 255, 0.7);
  font-size: 0.9rem;
}

/* Empty State */
.empty-state {
  text-align: center;
  padding: 60px 20px;
  color: rgba(255, 255, 255, 0.7);
}

.empty-icon {
  font-size: 4rem;
  margin-bottom: 20px;
  opacity: 0.5;
}

.empty-state h3 {
  font-size: 1.5rem;
  margin-bottom: 10px;
  color: var(--color-text-primary, #ffffff);
}

.empty-state p {
  margin-bottom: 30px;
  font-size: 1rem;
}

.btn-primary {
  background: linear-gradient(135deg, var(--color-primary, #FFD700), var(--color-primary-light, #00d4ff));
  border: none;
  border-radius: 12px;
  padding: 12px 24px;
  color: var(--color-bg-primary, #000000);
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  display: inline-flex;
  align-items: center;
  gap: 8px;
}

.btn-primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 5px 15px rgba(var(--color-primary-rgb, 255, 215, 0), 0.3);
}

/* Collectors Grid */
.collectors-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
  gap: 20px;
}

.collector-card {
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 15px;
  padding: 20px;
  transition: all 0.3s ease;
}

.collector-card.primary {
  border-left: 4px solid var(--color-primary, #FFD700);
  background: rgba(var(--color-primary-rgb, 255, 215, 0), 0.05);
}

.collector-card.inactive {
  opacity: 0.6;
  border-color: rgba(255, 255, 255, 0.05);
}

.collector-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 15px;
}

.bank-name {
  font-size: 1.2rem;
  font-weight: 600;
  margin: 0 0 5px 0;
  color: #ffffff;
}

.account-holder {
  font-size: 0.9rem;
  color: rgba(255, 255, 255, 0.7);
}

.card-badges {
  display: flex;
  flex-direction: column;
  gap: 5px;
  align-items: flex-end;
}

.badge {
  padding: 4px 8px;
  border-radius: 12px;
  font-size: 0.75rem;
  font-weight: 600;
  display: flex;
  align-items: center;
  gap: 4px;
}

.badge.primary {
  background: rgba(var(--color-primary-rgb, 255, 215, 0), 0.2);
  color: var(--color-primary, #FFD700);
}

.badge.active {
  background: rgba(var(--color-primary-rgb, 255, 215, 0), 0.2);
  color: var(--color-primary, #FFD700);
}

.badge.inactive {
  background: rgba(var(--color-error-rgb, 255, 68, 68), 0.2);
  color: var(--color-error, #ff4444);
}

.card-content {
  margin-bottom: 20px;
}

.account-details {
  margin-bottom: 15px;
}

.detail-row {
  display: flex;
  justify-content: space-between;
  margin-bottom: 8px;
  padding: 8px 0;
  border-bottom: 1px solid rgba(255, 255, 255, 0.05);
}

.detail-row .label {
  color: rgba(255, 255, 255, 0.7);
  font-size: 0.9rem;
}

.detail-row .value {
  color: #ffffff;
  font-weight: 500;
  font-family: 'Courier New', monospace;
}

.barcode-section {
  text-align: center;
  margin: 15px 0;
  padding: 15px;
  background: rgba(255, 255, 255, 0.05);
  border-radius: 8px;
}

.barcode-label {
  font-size: 0.8rem;
  color: rgba(255, 255, 255, 0.7);
  margin-bottom: 10px;
  font-weight: 500;
}

.barcode-image {
  max-width: 100%;
  max-height: 120px;
  border-radius: 8px;
  border: 1px solid rgba(255, 255, 255, 0.1);
  transition: all 0.3s ease;
}

.barcode-image:hover {
  transform: scale(1.05);
  border-color: #FFD700;
}

.image-error {
  color: #ff4444;
  font-size: 0.9rem;
  padding: 10px;
  background: rgba(255, 68, 68, 0.1);
  border-radius: 8px;
  border: 1px solid rgba(255, 68, 68, 0.2);
}

.notes-section {
  margin-top: 15px;
  padding: 12px;
  background: rgba(255, 255, 255, 0.03);
  border-radius: 8px;
}

.notes-label {
  font-size: 0.8rem;
  color: rgba(255, 255, 255, 0.6);
  margin-bottom: 5px;
}

.notes-text {
  font-size: 0.9rem;
  color: rgba(255, 255, 255, 0.8);
  line-height: 1.4;
}

/* Actions */
.card-actions {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
}

.action-btn {
  padding: 8px 12px;
  border: none;
  border-radius: 8px;
  font-size: 0.8rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  gap: 4px;
}

.primary-btn {
  background: linear-gradient(135deg, #FFD700, #00d4ff);
  color: #000000;
}

.primary-btn:hover {
  transform: translateY(-1px);
  box-shadow: 0 3px 10px rgba(255, 215, 0, 0.3);
}

.status-btn.activate {
  background: rgba(255, 215, 0, 0.2);
  color: #FFD700;
  border: 1px solid rgba(255, 215, 0, 0.3);
}

.status-btn.deactivate {
  background: rgba(255, 158, 11, 0.2);
  color: #f59e0b;
  border: 1px solid rgba(255, 158, 11, 0.3);
}

.edit-btn {
  background: rgba(59, 130, 246, 0.2);
  color: #3b82f6;
  border: 1px solid rgba(59, 130, 246, 0.3);
}

.delete-btn {
  background: rgba(255, 68, 68, 0.2);
  color: #ff4444;
  border: 1px solid rgba(255, 68, 68, 0.3);
}

.action-btn:hover {
  transform: translateY(-1px);
}

.action-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

/* Loading */
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
  z-index: 1000;
}

.loading-spinner {
  text-align: center;
  color: #ffffff;
}

.loading-spinner i {
  font-size: 3rem;
  color: #FFD700;
  margin-bottom: 15px;
}

/* Responsive */
@media (max-width: 768px) {
  .payment-collector-screen {
    padding: 15px;
  }
  
  .header-content {
    flex-direction: column;
    gap: 20px;
    text-align: center;
  }
  
  .form-grid {
    grid-template-columns: 1fr;
    gap: 15px;
  }
  
  .collectors-grid {
    grid-template-columns: 1fr;
  }
  
  .modal-content {
    width: 95%;
    padding: 20px;
  }
  
  .card-actions {
    justify-content: center;
  }
  
  .form-actions {
    flex-direction: column;
    gap: 10px;
  }
}
</style>