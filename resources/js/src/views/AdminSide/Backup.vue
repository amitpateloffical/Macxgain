<template>
  <div class="backup-screen">
    <!-- Header Section -->
    <div class="backup-header">
      <div class="header-content">
        <div class="header-left">
          <h1 class="page-title">
            <i class="fa-solid fa-database"></i>
            Backup Management
          </h1>
          <p class="page-subtitle">
            Create, manage and restore system backups for data protection
          </p>
        </div>
        <div class="header-actions">
          <button class="btn-create-backup" @click="showBackupModal = true" :disabled="creatingBackup">
            <i class="fa-solid fa-plus" :class="{ 'fa-spin': creatingBackup }"></i>
            {{ creatingBackup ? 'Creating...' : 'Create Backup' }}
          </button>
        </div>
      </div>
    </div>

    <!-- Mobile Navigation Component -->
    <AdminMobileNav />

    <!-- Backup Creation Modal -->
    <div v-if="showBackupModal" class="backup-modal-overlay" @click="closeBackupModal">
      <div class="backup-modal" @click.stop>
        <div class="modal-header">
          <h3>Create New Backup</h3>
          <button class="close-btn" @click="closeBackupModal" title="Close">
            <i class="fa-solid fa-times"></i>
          </button>
        </div>
        
        <div class="modal-body">
          <p class="modal-description">Choose the type of backup you want to create:</p>
          
          <div class="backup-options-modal">
            <div class="option-card-modal" 
                 :class="{ active: selectedBackupType === 'database' }"
                 @click="selectBackupType('database')">
              <div class="option-icon">
                <i class="fa-solid fa-database"></i>
              </div>
              <h4>Database Backup</h4>
              <p>Backup all database tables and data</p>
              <div class="option-features">
                <span class="feature">✓ All tables</span>
                <span class="feature">✓ User data</span>
                <span class="feature">✓ Transaction history</span>
              </div>
            </div>

            <div class="option-card-modal" 
                 :class="{ active: selectedBackupType === 'files' }"
                 @click="selectBackupType('files')">
              <div class="option-icon">
                <i class="fa-solid fa-file-archive"></i>
              </div>
              <h4>File Backup</h4>
              <p>Backup uploaded files and documents</p>
              <div class="option-features">
                <span class="feature">✓ User uploads</span>
                <span class="feature">✓ Documents</span>
                <span class="feature">✓ Images</span>
              </div>
            </div>

            <div class="option-card-modal" 
                 :class="{ active: selectedBackupType === 'full' }"
                 @click="selectBackupType('full')">
              <div class="option-icon">
                <i class="fa-solid fa-server"></i>
              </div>
              <h4>Full System Backup</h4>
              <p>Complete system backup including all data</p>
              <div class="option-features">
                <span class="feature">✓ Database</span>
                <span class="feature">✓ Files</span>
                <span class="feature">✓ Configuration</span>
              </div>
            </div>
          </div>
        </div>
        
        <div class="modal-footer">
          <button class="btn-cancel" @click="closeBackupModal">Cancel</button>
          <button class="btn-confirm" @click="confirmCreateBackup" :disabled="creatingBackup">
            <i class="fa-solid fa-plus" :class="{ 'fa-spin': creatingBackup }"></i>
            {{ creatingBackup ? 'Creating...' : 'Create Backup' }}
          </button>
        </div>
      </div>
    </div>

    <!-- Backup Status Cards -->
    <div class="backup-status-grid">
      <div class="status-card">
        <div class="status-icon">
          <i class="fa-solid fa-database"></i>
        </div>
        <div class="status-info">
          <h3>Database Backups</h3>
          <p class="status-count">{{ backupStats.database }}</p>
          <span class="status-label">Total Backups</span>
        </div>
      </div>

      <div class="status-card">
        <div class="status-icon">
          <i class="fa-solid fa-file-archive"></i>
        </div>
        <div class="status-info">
          <h3>File Backups</h3>
          <p class="status-count">{{ backupStats.files }}</p>
          <span class="status-label">Total Backups</span>
        </div>
      </div>

      <div class="status-card">
        <div class="status-icon">
          <i class="fa-solid fa-clock"></i>
        </div>
        <div class="status-info">
          <h3>Last Backup</h3>
          <p class="status-count">{{ lastBackupTime }}</p>
          <span class="status-label">Time</span>
        </div>
      </div>

      <div class="status-card">
        <div class="status-icon">
          <i class="fa-solid fa-hdd"></i>
        </div>
        <div class="status-info">
          <h3>Storage Used</h3>
          <p class="status-count">{{ storageUsed }}</p>
          <span class="status-label">Space</span>
        </div>
      </div>
    </div>


    <!-- Recent Backups -->
    <div class="recent-backups">
      <div class="section-header">
        <h2 class="section-title">Recent Backups</h2>
        <button class="btn-refresh" @click="refreshBackups" :disabled="loading">
          <i class="fa-solid fa-rotate" :class="{ 'fa-spin': loading }"></i>
          Refresh
        </button>
      </div>

      <div class="backups-list" v-if="backups.length > 0">
        <div class="backup-item" v-for="backup in backups" :key="backup.id">
          <div class="backup-info">
            <div class="backup-icon">
              <i :class="getBackupIcon(backup.type)"></i>
            </div>
            <div class="backup-details">
              <h4>{{ backup.name }}</h4>
              <p>{{ backup.description }}</p>
              <div class="backup-meta">
                <span class="backup-size">{{ backup.size }}</span>
                <span class="backup-date">{{ formatDate(backup.created_at) }}</span>
                <span class="backup-status" :class="backup.status">{{ backup.status }}</span>
              </div>
            </div>
          </div>
          <div class="backup-actions">
            <button class="btn-action download" @click="downloadBackup(backup.id)" :disabled="backup.status !== 'completed'">
              <i class="fa-solid fa-download"></i>
            </button>
            <button class="btn-action restore" @click="restoreBackup(backup.id)" :disabled="backup.status !== 'completed'">
              <i class="fa-solid fa-undo"></i>
            </button>
            <button class="btn-action delete" @click="deleteBackup(backup.id)">
              <i class="fa-solid fa-trash"></i>
            </button>
          </div>
        </div>
      </div>

      <div class="empty-state" v-else>
        <div class="empty-icon">
          <i class="fa-solid fa-database"></i>
        </div>
        <h3>No Backups Found</h3>
        <p>Create your first backup to get started with data protection</p>
        <button class="btn-create-backup" @click="createBackup">
          <i class="fa-solid fa-plus"></i>
          Create Backup
        </button>
      </div>
    </div>

    <!-- Toast Notifications -->
    <div class="toast-container">
      <div 
        v-for="toast in toasts" 
        :key="toast.id" 
        :class="['toast', `toast-${toast.type}`]"
        @click="removeToast(toast.id)"
      >
        <div class="toast-icon">
          <i :class="getToastIcon(toast.type)"></i>
        </div>
        <div class="toast-content">
          <p class="toast-message">{{ toast.message }}</p>
        </div>
        <button class="toast-close" @click.stop="removeToast(toast.id)">
          <i class="fa-solid fa-times"></i>
        </button>
      </div>
    </div>

  </div>
</template>

<script>
import AdminMobileNav from '../../components/AdminMobileNav.vue';

export default {
  name: 'Backup',
  components: {
    AdminMobileNav
  },
  data() {
    return {
      loading: false,
      creatingBackup: false,
      showBackupModal: false,
      selectedBackupType: 'database',
      backupStats: {
        database: 0,
        files: 0,
        lastBackup: null,
        totalSize: '0 B'
      },
      backups: [],
      toasts: []
    }
  },
  computed: {
    lastBackupTime() {
      if (this.backupStats.lastBackup) {
        return new Date(this.backupStats.lastBackup).toLocaleString();
      }
      return 'Never';
    },
    storageUsed() {
      return this.backupStats.totalSize || '0 B';
    }
  },
  mounted() {
    this.loadBackupData();
    // Add ESC key listener
    document.addEventListener('keydown', this.handleKeydown);
  },
  beforeUnmount() {
    // Remove ESC key listener
    document.removeEventListener('keydown', this.handleKeydown);
  },
  methods: {
    async loadBackupData() {
      this.loading = true;
      try {
        const token = localStorage.getItem('access_token');
        const response = await fetch('/api/backups', {
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

        const data = await response.json();
        
        if (data.success) {
          this.backups = data.backups;
          this.backupStats = data.stats;
        } else {
          throw new Error(data.message || 'Failed to load backup data');
        }
      } catch (error) {
        console.error('Error loading backup data:', error);
        alert('Error loading backup data: ' + error.message);
      } finally {
        this.loading = false;
      }
    },
    
    selectBackupType(type) {
      this.selectedBackupType = type;
    },
    
    closeBackupModal() {
      this.showBackupModal = false;
    },
    
    handleKeydown(event) {
      // Close modal on ESC key press
      if (event.key === 'Escape' && this.showBackupModal) {
        this.closeBackupModal();
      }
    },
    
    async confirmCreateBackup() {
      await this.createBackup();
      this.closeBackupModal();
    },
    
    async createBackup() {
      this.creatingBackup = true;
      try {
        const token = localStorage.getItem('access_token');
        const response = await fetch('/api/backups/create', {
          method: 'POST',
          headers: {
            'Authorization': `Bearer ${token}`,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
          },
          body: JSON.stringify({
            type: this.selectedBackupType
          })
        });

        if (!response.ok) {
          throw new Error(`HTTP error! status: ${response.status}`);
        }

        const data = await response.json();
        
        if (data.success) {
          this.showToast('Backup created successfully!', 'success');
          // Refresh the backup list
          await this.loadBackupData();
        } else {
          throw new Error(data.message || 'Failed to create backup');
        }
      } catch (error) {
        console.error('Error creating backup:', error);
        this.showToast('Error creating backup: ' + error.message, 'error');
      } finally {
        this.creatingBackup = false;
      }
    },
    
    async refreshBackups() {
      await this.loadBackupData();
    },
    
    getBackupIcon(type) {
      const icons = {
        database: 'fa-solid fa-database',
        files: 'fa-solid fa-file-archive',
        full: 'fa-solid fa-server'
      };
      return icons[type] || 'fa-solid fa-file';
    },
    
    formatDate(dateString) {
      return new Date(dateString).toLocaleString();
    },
    
    async downloadBackup(backupId) {
      try {
        const token = localStorage.getItem('access_token');
        const response = await fetch(`/api/backups/${backupId}/download`, {
          method: 'GET',
          headers: {
            'Authorization': `Bearer ${token}`,
            'Accept': 'application/octet-stream'
          }
        });

        if (!response.ok) {
          throw new Error(`HTTP error! status: ${response.status}`);
        }

        // Get filename from response headers
        const contentDisposition = response.headers.get('content-disposition');
        let filename = 'backup';
        
        if (contentDisposition) {
          // Try different patterns to extract filename
          let filenameMatch = contentDisposition.match(/filename="(.+)"/);
          if (!filenameMatch) {
            filenameMatch = contentDisposition.match(/filename=([^;]+)/);
          }
          if (!filenameMatch) {
            filenameMatch = contentDisposition.match(/filename=(.+)/);
          }
          
          if (filenameMatch) {
            filename = filenameMatch[1].trim();
          }
        }
        
        // If still no proper filename, use backup data
        if (filename === 'backup' || !filename.includes('.')) {
          const backup = this.backups.find(b => b.id === backupId);
          if (backup) {
            filename = backup.filename || backup.name;
          }
        }

        // Create blob and download
        const blob = await response.blob();
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = filename;
        document.body.appendChild(a);
        a.click();
        window.URL.revokeObjectURL(url);
        document.body.removeChild(a);
        
        this.showToast('Backup downloaded successfully!', 'success');
      } catch (error) {
        console.error('Error downloading backup:', error);
        this.showToast('Error downloading backup: ' + error.message, 'error');
      }
    },
    
    async restoreBackup(backupId) {
      const backup = this.backups.find(b => b.id === backupId);
      if (backup) {
        if (confirm(`Are you sure you want to restore backup: ${backup.name}? This will overwrite current data.`)) {
          try {
            const token = localStorage.getItem('access_token');
            const response = await fetch(`/api/backups/${backupId}/restore`, {
              method: 'POST',
              headers: {
                'Authorization': `Bearer ${token}`,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
              }
            });

            if (!response.ok) {
              throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();
            
            if (data.success) {
              this.showToast('Backup restored successfully!', 'success');
            } else {
              throw new Error(data.message || 'Failed to restore backup');
            }
          } catch (error) {
            console.error('Error restoring backup:', error);
            this.showToast('Error restoring backup: ' + error.message, 'error');
          }
        }
      }
    },
    
    async deleteBackup(backupId) {
      const backup = this.backups.find(b => b.id === backupId);
      if (backup) {
        if (confirm(`Are you sure you want to delete backup: ${backup.name}?`)) {
          try {
            const token = localStorage.getItem('access_token');
            const response = await fetch(`/api/backups/${backupId}`, {
              method: 'DELETE',
              headers: {
                'Authorization': `Bearer ${token}`,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
              }
            });

            if (!response.ok) {
              throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();
            
            if (data.success) {
              this.showToast('Backup deleted successfully!', 'success');
              // Refresh the backup list
              await this.loadBackupData();
            } else {
              throw new Error(data.message || 'Failed to delete backup');
            }
          } catch (error) {
            console.error('Error deleting backup:', error);
            this.showToast('Error deleting backup: ' + error.message, 'error');
          }
        }
      }
    },
    
    showToast(message, type = 'info') {
      const toast = {
        id: Date.now(),
        message,
        type,
        visible: true
      };
      
      this.toasts.push(toast);
      
      // Auto remove after 5 seconds
      setTimeout(() => {
        this.removeToast(toast.id);
      }, 5000);
    },
    
    removeToast(id) {
      const index = this.toasts.findIndex(toast => toast.id === id);
      if (index > -1) {
        this.toasts.splice(index, 1);
      }
    },
    
    getToastIcon(type) {
      const icons = {
        success: 'fa-solid fa-check-circle',
        error: 'fa-solid fa-exclamation-circle',
        warning: 'fa-solid fa-exclamation-triangle',
        info: 'fa-solid fa-info-circle'
      };
      return icons[type] || icons.info;
    }
    
  }
};
</script>

<style scoped>
.backup-screen {
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

.backup-screen::before {
  content: '';
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: 
    radial-gradient(circle at 20% 80%, rgba(0, 255, 128, 0.06) 0%, transparent 50%),
    radial-gradient(circle at 80% 20%, rgba(0, 255, 128, 0.04) 0%, transparent 50%),
    radial-gradient(circle at 40% 40%, rgba(0, 255, 128, 0.02) 0%, transparent 50%);
  pointer-events: none;
  z-index: 0;
}

.backup-header {
  background: linear-gradient(145deg, rgba(0, 255, 128, 0.05), rgba(0, 255, 128, 0.02));
  border: 1px solid rgba(0, 255, 128, 0.1);
  border-radius: 20px;
  padding: 24px 32px;
  margin-bottom: 32px;
  backdrop-filter: blur(10px);
  position: relative;
  z-index: 1;
  box-shadow: 0 8px 32px rgba(0, 255, 128, 0.1);
}

.header-content {
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 20px;
}

.header-left {
  flex: 1;
  min-width: 300px;
}

.page-title {
  font-size: 28px;
  font-weight: 800;
  color: #00ff80;
  margin: 0 0 8px 0;
  display: flex;
  align-items: center;
  gap: 12px;
}

.page-title i {
  font-size: 24px;
}

.page-subtitle {
  font-size: 16px;
  color: #b0b0b0;
  margin: 0;
  line-height: 1.4;
}

.header-actions {
  display: flex;
  gap: 12px;
  flex-wrap: wrap;
}

.btn-create-backup {
  background: linear-gradient(135deg, #00ff80, #00cc66);
  color: #0d0d1a;
  border: none;
  border-radius: 12px;
  padding: 12px 24px;
  font-weight: 600;
  font-size: 14px;
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  gap: 8px;
  box-shadow: 0 4px 16px rgba(0, 255, 128, 0.3);
}

.btn-create-backup:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 8px 24px rgba(0, 255, 128, 0.4);
}

.btn-create-backup:disabled {
  opacity: 0.6;
  cursor: not-allowed;
  transform: none;
}

.backup-status-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 20px;
  margin-bottom: 32px;
  position: relative;
  z-index: 1;
}

.status-card {
  background: linear-gradient(145deg, rgba(16, 16, 34, 0.8), rgba(13, 13, 26, 0.9));
  border: 1px solid rgba(0, 255, 128, 0.2);
  border-radius: 16px;
  padding: 24px;
  display: flex;
  align-items: center;
  gap: 16px;
  transition: all 0.3s ease;
  backdrop-filter: blur(10px);
}

.status-card:hover {
  border-color: #00ff80;
  transform: translateY(-4px);
  box-shadow: 0 12px 32px rgba(0, 255, 128, 0.2);
}

.status-icon {
  font-size: 32px;
  color: #00ff80;
  width: 60px;
  height: 60px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: rgba(0, 255, 128, 0.1);
  border-radius: 12px;
}

.status-info h3 {
  font-size: 16px;
  font-weight: 600;
  color: white;
  margin: 0 0 8px 0;
}

.status-count {
  font-size: 24px;
  font-weight: 700;
  color: #00ff80;
  margin: 0 0 4px 0;
}

.status-label {
  font-size: 12px;
  color: #b0b0b0;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.section-title {
  font-size: 24px;
  font-weight: 700;
  color: white;
  margin: 0 0 20px 0;
  display: flex;
  align-items: center;
  gap: 12px;
}

.recent-backups {
  margin-bottom: 32px;
  position: relative;
  z-index: 1;
}

.section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
  flex-wrap: wrap;
  gap: 16px;
}

.btn-refresh {
  background: rgba(0, 255, 128, 0.1);
  color: #00ff80;
  border: 1px solid rgba(0, 255, 128, 0.3);
  border-radius: 8px;
  padding: 8px 16px;
  font-size: 14px;
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  gap: 8px;
}

.btn-refresh:hover:not(:disabled) {
  background: rgba(0, 255, 128, 0.2);
  border-color: #00ff80;
}

.btn-refresh:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.backups-list {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.backup-item {
  background: linear-gradient(145deg, rgba(16, 16, 34, 0.8), rgba(13, 13, 26, 0.9));
  border: 1px solid rgba(0, 255, 128, 0.2);
  border-radius: 12px;
  padding: 20px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  transition: all 0.3s ease;
  backdrop-filter: blur(10px);
}

.backup-item:hover {
  border-color: #00ff80;
  transform: translateY(-2px);
  box-shadow: 0 8px 24px rgba(0, 255, 128, 0.1);
}

.backup-info {
  display: flex;
  align-items: center;
  gap: 16px;
  flex: 1;
}

.backup-icon {
  font-size: 24px;
  color: #00ff80;
  width: 48px;
  height: 48px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: rgba(0, 255, 128, 0.1);
  border-radius: 8px;
}

.backup-details h4 {
  font-size: 16px;
  font-weight: 600;
  color: white;
  margin: 0 0 4px 0;
}

.backup-details p {
  font-size: 14px;
  color: #b0b0b0;
  margin: 0 0 8px 0;
}

.backup-meta {
  display: flex;
  gap: 16px;
  flex-wrap: wrap;
}

.backup-size, .backup-date {
  font-size: 12px;
  color: #b0b0b0;
}

.backup-status {
  font-size: 12px;
  padding: 4px 8px;
  border-radius: 4px;
  font-weight: 600;
  text-transform: uppercase;
}

.backup-status.completed {
  background: rgba(0, 255, 128, 0.2);
  color: #00ff80;
}

.backup-status.in_progress {
  background: rgba(255, 193, 7, 0.2);
  color: #ffc107;
}

.backup-status.failed {
  background: rgba(220, 53, 69, 0.2);
  color: #dc3545;
}

.backup-actions {
  display: flex;
  gap: 8px;
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
  font-size: 14px;
}

.btn-action.download {
  background: rgba(0, 123, 255, 0.2);
  color: #007bff;
}

.btn-action.download:hover:not(:disabled) {
  background: rgba(0, 123, 255, 0.3);
}

.btn-action.restore {
  background: rgba(40, 167, 69, 0.2);
  color: #28a745;
}

.btn-action.restore:hover:not(:disabled) {
  background: rgba(40, 167, 69, 0.3);
}

.btn-action.delete {
  background: rgba(220, 53, 69, 0.2);
  color: #dc3545;
}

.btn-action.delete:hover {
  background: rgba(220, 53, 69, 0.3);
}

.btn-action:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.empty-state {
  text-align: center;
  padding: 60px 20px;
  background: linear-gradient(145deg, rgba(16, 16, 34, 0.8), rgba(13, 13, 26, 0.9));
  border: 1px solid rgba(0, 255, 128, 0.2);
  border-radius: 16px;
  backdrop-filter: blur(10px);
}

.empty-icon {
  font-size: 64px;
  color: #00ff80;
  margin-bottom: 20px;
}

.empty-state h3 {
  font-size: 24px;
  font-weight: 600;
  color: white;
  margin: 0 0 12px 0;
}

.empty-state p {
  font-size: 16px;
  color: #b0b0b0;
  margin: 0 0 24px 0;
}


/* Backup Modal Styles */
.backup-modal-overlay {
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

.backup-modal {
  background: linear-gradient(145deg, rgba(16, 16, 34, 0.95), rgba(13, 13, 26, 0.98));
  border: 1px solid rgba(0, 255, 128, 0.3);
  border-radius: 20px;
  max-width: 800px;
  width: 100%;
  max-height: 90vh;
  overflow-y: auto;
  backdrop-filter: blur(20px);
  box-shadow: 0 20px 60px rgba(0, 255, 128, 0.2);
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 24px 32px;
  border-bottom: 1px solid rgba(0, 255, 128, 0.2);
}

.modal-header h3 {
  font-size: 24px;
  font-weight: 700;
  color: #00ff80;
  margin: 0;
}

.close-btn {
  background: rgba(255, 255, 255, 0.1);
  border: 1px solid rgba(255, 255, 255, 0.2);
  color: #b0b0b0;
  font-size: 18px;
  cursor: pointer;
  padding: 8px;
  border-radius: 50%;
  transition: all 0.3s ease;
  width: 36px;
  height: 36px;
  display: flex;
  align-items: center;
  justify-content: center;
  position: relative;
  z-index: 10;
}

.close-btn:hover {
  background: rgba(255, 255, 255, 0.2);
  color: white;
  border-color: rgba(255, 255, 255, 0.3);
  transform: scale(1.1);
}

.close-btn:active {
  transform: scale(0.95);
}

.modal-body {
  padding: 32px;
}

.modal-description {
  font-size: 16px;
  color: #b0b0b0;
  margin: 0 0 24px 0;
  text-align: center;
}

.backup-options-modal {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 20px;
  margin-bottom: 24px;
}

.option-card-modal {
  background: linear-gradient(145deg, rgba(16, 16, 34, 0.8), rgba(13, 13, 26, 0.9));
  border: 1px solid rgba(0, 255, 128, 0.2);
  border-radius: 16px;
  padding: 24px;
  cursor: pointer;
  transition: all 0.3s ease;
  backdrop-filter: blur(10px);
  position: relative;
  overflow: hidden;
}

.option-card-modal:hover {
  border-color: #00ff80;
  transform: translateY(-4px);
  box-shadow: 0 12px 32px rgba(0, 255, 128, 0.2);
}

.option-card-modal.active {
  border-color: #00ff80;
  background: linear-gradient(145deg, rgba(0, 255, 128, 0.1), rgba(0, 255, 128, 0.05));
  box-shadow: 0 8px 24px rgba(0, 255, 128, 0.3);
}

.option-card-modal .option-icon {
  font-size: 32px;
  color: #00ff80;
  margin-bottom: 16px;
  width: 60px;
  height: 60px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: rgba(0, 255, 128, 0.1);
  border-radius: 12px;
}

.option-card-modal h4 {
  font-size: 18px;
  font-weight: 600;
  color: white;
  margin: 0 0 8px 0;
}

.option-card-modal p {
  font-size: 14px;
  color: #b0b0b0;
  margin: 0 0 16px 0;
  line-height: 1.4;
}

.option-card-modal .option-features {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.option-card-modal .feature {
  font-size: 12px;
  color: #00ff80;
  display: flex;
  align-items: center;
  gap: 6px;
}

.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 16px;
  padding: 24px 32px;
  border-top: 1px solid rgba(0, 255, 128, 0.2);
}

.btn-cancel {
  background: rgba(255, 255, 255, 0.1);
  color: #b0b0b0;
  border: 1px solid rgba(255, 255, 255, 0.2);
  border-radius: 8px;
  padding: 12px 24px;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
}

.btn-cancel:hover {
  background: rgba(255, 255, 255, 0.2);
  color: white;
  border-color: rgba(255, 255, 255, 0.3);
}

.btn-confirm {
  background: linear-gradient(135deg, #00ff80, #00cc66);
  color: #0d0d1a;
  border: none;
  border-radius: 8px;
  padding: 12px 24px;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  gap: 8px;
  box-shadow: 0 4px 16px rgba(0, 255, 128, 0.3);
}

.btn-confirm:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 8px 24px rgba(0, 255, 128, 0.4);
}

.btn-confirm:disabled {
  opacity: 0.6;
  cursor: not-allowed;
  transform: none;
}

/* Mobile Modal Styles */
@media (max-width: 768px) {
  .backup-modal-overlay {
    padding: 10px;
  }
  
  .backup-modal {
    max-height: 95vh;
  }
  
  .modal-header {
    padding: 20px;
  }
  
  .modal-header h3 {
    font-size: 20px;
  }
  
  .close-btn {
    width: 32px;
    height: 32px;
    font-size: 16px;
  }
  
  .modal-body {
    padding: 20px;
  }
  
  .backup-options-modal {
    grid-template-columns: 1fr;
    gap: 16px;
  }
  
  .option-card-modal {
    padding: 20px;
  }
  
  .modal-footer {
    padding: 20px;
    flex-direction: column;
  }
  
  .btn-cancel,
  .btn-confirm {
    width: 100%;
    justify-content: center;
  }
}

/* Toast Notifications */
.toast-container {
  position: fixed;
  top: 20px;
  right: 20px;
  z-index: 2000;
  display: flex;
  flex-direction: column;
  gap: 12px;
  max-width: 400px;
}

.toast {
  background: linear-gradient(145deg, rgba(16, 16, 34, 0.95), rgba(13, 13, 26, 0.98));
  border: 1px solid rgba(0, 255, 128, 0.3);
  border-radius: 12px;
  padding: 16px 20px;
  display: flex;
  align-items: center;
  gap: 12px;
  cursor: pointer;
  transition: all 0.3s ease;
  backdrop-filter: blur(20px);
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
  animation: slideInRight 0.3s ease;
  position: relative;
  overflow: hidden;
}

.toast:hover {
  transform: translateX(-4px);
  box-shadow: 0 12px 40px rgba(0, 0, 0, 0.4);
}

.toast-success {
  border-color: #00ff80;
  background: linear-gradient(145deg, rgba(0, 255, 128, 0.1), rgba(16, 16, 34, 0.95));
}

.toast-error {
  border-color: #ff4757;
  background: linear-gradient(145deg, rgba(255, 71, 87, 0.1), rgba(16, 16, 34, 0.95));
}

.toast-warning {
  border-color: #ffa502;
  background: linear-gradient(145deg, rgba(255, 165, 2, 0.1), rgba(16, 16, 34, 0.95));
}

.toast-info {
  border-color: #3742fa;
  background: linear-gradient(145deg, rgba(55, 66, 250, 0.1), rgba(16, 16, 34, 0.95));
}

.toast-icon {
  font-size: 20px;
  width: 24px;
  height: 24px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.toast-success .toast-icon {
  color: #00ff80;
}

.toast-error .toast-icon {
  color: #ff4757;
}

.toast-warning .toast-icon {
  color: #ffa502;
}

.toast-info .toast-icon {
  color: #3742fa;
}

.toast-content {
  flex: 1;
  min-width: 0;
}

.toast-message {
  color: white;
  font-size: 14px;
  font-weight: 500;
  margin: 0;
  line-height: 1.4;
  word-wrap: break-word;
}

.toast-close {
  background: none;
  border: none;
  color: #b0b0b0;
  font-size: 14px;
  cursor: pointer;
  padding: 4px;
  border-radius: 4px;
  transition: all 0.2s ease;
  flex-shrink: 0;
  width: 24px;
  height: 24px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.toast-close:hover {
  background: rgba(255, 255, 255, 0.1);
  color: white;
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

@keyframes slideOutRight {
  from {
    transform: translateX(0);
    opacity: 1;
  }
  to {
    transform: translateX(100%);
    opacity: 0;
  }
}

.toast.removing {
  animation: slideOutRight 0.3s ease forwards;
}

/* Mobile Toast Styles */
@media (max-width: 768px) {
  .toast-container {
    top: 10px;
    right: 10px;
    left: 10px;
    max-width: none;
  }
  
  .toast {
    padding: 14px 16px;
  }
  
  .toast-message {
    font-size: 13px;
  }
  
  .toast-icon {
    font-size: 18px;
    width: 20px;
    height: 20px;
  }
  
  .toast-close {
    font-size: 12px;
    width: 20px;
    height: 20px;
  }
}

/* Responsive Design */
@media (max-width: 768px) {
  .backup-screen {
    padding: 16px;
  }
  
  .backup-header {
    padding: 20px;
    margin-bottom: 24px;
  }
  
  .header-content {
    flex-direction: column;
    align-items: flex-start;
  }
  
  .page-title {
    font-size: 24px;
  }
  
  .backup-status-grid {
    grid-template-columns: 1fr;
    gap: 16px;
  }
  
  
  .backup-item {
    flex-direction: column;
    align-items: flex-start;
    gap: 16px;
  }
  
  .backup-actions {
    width: 100%;
    justify-content: flex-end;
  }
  
}

@media (max-width: 480px) {
  .backup-screen {
    padding: 12px;
  }
  
  .backup-header {
    padding: 16px;
  }
  
  .page-title {
    font-size: 20px;
  }
  
  .status-card {
    padding: 16px;
  }
  
  .option-card {
    padding: 16px;
  }
  
  .backup-item {
    padding: 16px;
  }
  
  .backup-meta {
    flex-direction: column;
    gap: 4px;
  }
}
</style>
