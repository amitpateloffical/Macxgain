<template>
  <div class="brand-settings-screen">
    <!-- Header Section -->
    <div class="page-header">
      <div class="header-content">
        <h1 class="page-title">🎨 Brand Settings</h1>
        <p class="page-subtitle">Customize your logo and company name</p>
      </div>
    </div>

    <!-- Success Message -->
    <div v-if="showSuccess" class="success-alert">
      <i class="fas fa-check-circle"></i>
      <span>Brand settings saved successfully! Refresh the page to see changes.</span>
    </div>

    <!-- Brand Configuration Form -->
    <div class="settings-card">
      <h2 class="card-title">Company Information</h2>
      
      <form @submit.prevent="saveSettings" class="settings-form">
        <!-- Company Name -->
        <div class="form-group">
          <label for="companyName" class="form-label">
            <i class="fas fa-building"></i>
            Company Name
          </label>
          <input
            id="companyName"
            type="text"
            v-model="formData.companyName"
            placeholder="Enter your company name"
            class="form-input"
            required
          />
          <p class="form-help">This will appear in the header, login page, and throughout the app</p>
        </div>

        <!-- Tagline -->
        <div class="form-group">
          <label for="tagline" class="form-label">
            <i class="fas fa-quote-left"></i>
            Tagline
          </label>
          <input
            id="tagline"
            type="text"
            v-model="formData.tagline"
            placeholder="Enter your tagline"
            class="form-input"
          />
          <p class="form-help">Short description that appears on login page</p>
        </div>

        <!-- Welcome Text -->
        <div class="form-group">
          <label for="welcomeText" class="form-label">
            <i class="fas fa-hand-wave"></i>
            Welcome Text
          </label>
          <input
            id="welcomeText"
            type="text"
            v-model="formData.welcomeText"
            placeholder="Welcome Back"
            class="form-input"
          />
        </div>

        <!-- Logo Upload Section -->
        <div class="form-group">
          <label class="form-label">
            <i class="fas fa-image"></i>
            Logo Images
          </label>
          
          <div class="logo-upload-section">
            <!-- Login/Register Logo -->
            <div class="logo-upload-item">
              <label class="logo-label">Login/Register Logo</label>
              <div class="logo-preview">
                <img 
                  v-if="formData.loginLogoBase64 || formData.logoPath" 
                  :src="formData.loginLogoBase64 || formData.logoPath" 
                  alt="Logo Preview" 
                  class="preview-image"
                />
                <div v-else class="preview-placeholder">
                  <i class="fas fa-image"></i>
                  <span>No logo</span>
                </div>
                <div v-if="uploadingLogin" class="upload-overlay">
                  <i class="fas fa-spinner fa-spin"></i>
                  <span>Uploading...</span>
                </div>
              </div>
              <div class="file-upload-wrapper">
                <input
                  type="file"
                  ref="loginLogoInput"
                  @change="handleLoginLogoUpload"
                  accept="image/*"
                  class="file-input"
                  id="login-logo-upload"
                />
                <label for="login-logo-upload" class="file-upload-btn">
                  <i class="fas fa-upload"></i>
                  {{ formData.loginLogoBase64 ? 'Change Logo' : 'Upload Logo' }}
                </label>
                <button 
                  v-if="formData.loginLogoBase64" 
                  type="button"
                  @click="removeLoginLogo"
                  class="remove-logo-btn"
                >
                  <i class="fas fa-times"></i> Remove
                </button>
              </div>
              <p class="form-help">Upload logo for login/register pages (PNG, JPG, SVG recommended)</p>
              <input
                v-if="!formData.loginLogoBase64"
                type="text"
                v-model="formData.logoPath"
                placeholder="../assest/img/logo.png (or upload above)"
                class="form-input"
                style="margin-top: 8px;"
              />
            </div>

            <!-- Header Logo -->
            <div class="logo-upload-item">
              <label class="logo-label">Dashboard Header Logo</label>
              <div class="logo-preview">
                <img 
                  v-if="formData.headerLogoBase64 || formData.logoPathHeader" 
                  :src="formData.headerLogoBase64 || formData.logoPathHeader" 
                  alt="Header Logo Preview" 
                  class="preview-image"
                />
                <div v-else class="preview-placeholder">
                  <i class="fas fa-image"></i>
                  <span>No logo</span>
                </div>
                <div v-if="uploadingHeader" class="upload-overlay">
                  <i class="fas fa-spinner fa-spin"></i>
                  <span>Uploading...</span>
                </div>
              </div>
              <div class="file-upload-wrapper">
                <input
                  type="file"
                  ref="headerLogoInput"
                  @change="handleHeaderLogoUpload"
                  accept="image/*"
                  class="file-input"
                  id="header-logo-upload"
                />
                <label for="header-logo-upload" class="file-upload-btn">
                  <i class="fas fa-upload"></i>
                  {{ formData.headerLogoBase64 ? 'Change Logo' : 'Upload Logo' }}
                </label>
                <button 
                  v-if="formData.headerLogoBase64" 
                  type="button"
                  @click="removeHeaderLogo"
                  class="remove-logo-btn"
                >
                  <i class="fas fa-times"></i> Remove
                </button>
              </div>
              <p class="form-help">Upload logo for dashboard header (PNG, JPG, SVG recommended)</p>
              <input
                v-if="!formData.headerLogoBase64"
                type="text"
                v-model="formData.logoPathHeader"
                placeholder="../FrontEndSide/logo.png (or upload above)"
                class="form-input"
                style="margin-top: 8px;"
              />
            </div>
          </div>
        </div>

        <!-- Page Title -->
        <div class="form-group">
          <label for="pageTitle" class="form-label">
            <i class="fas fa-heading"></i>
            Page Title
          </label>
          <input
            id="pageTitle"
            type="text"
            v-model="formData.pageTitle"
            placeholder="Your Company - Trading Platform"
            class="form-input"
          />
          <p class="form-help">This appears in the browser tab</p>
        </div>

        <!-- Contact Information -->
        <h2 class="card-title" style="margin-top: 30px;">Contact Information</h2>
        
        <div class="form-group">
          <label for="email" class="form-label">
            <i class="fas fa-envelope"></i>
            Contact Email
          </label>
          <input
            id="email"
            type="email"
            v-model="formData.email"
            placeholder="info@yourcompany.com"
            class="form-input"
          />
        </div>

        <div class="form-group">
          <label for="website" class="form-label">
            <i class="fas fa-globe"></i>
            Website URL
          </label>
          <input
            id="website"
            type="url"
            v-model="formData.website"
            placeholder="https://yourcompany.com"
            class="form-input"
          />
        </div>

        <!-- Action Buttons -->
        <div class="form-actions">
          <button type="button" class="btn-secondary" @click="resetToDefault">
            <i class="fas fa-undo"></i> Reset to Default
          </button>
          <button type="submit" class="btn-primary" :disabled="isSaving" @click="saveSettings">
            <i class="fas fa-save"></i> 
            {{ isSaving ? 'Saving...' : 'Save Settings' }}
          </button>
        </div>
      </form>
    </div>

    <!-- Instructions Card -->
    <div class="info-card">
      <h3 class="info-title">
        <i class="fas fa-info-circle"></i> How to Update Logos
      </h3>
      <ol class="info-list">
        <li><strong>Upload Method (Recommended):</strong>
          <ul>
            <li>Click "Upload Logo" button for each logo type</li>
            <li>Select an image file (PNG, JPG, or SVG - max 2MB)</li>
            <li>Preview will show immediately</li>
            <li>Click "Save Settings" to apply</li>
          </ul>
        </li>
        <li><strong>File Path Method (Alternative):</strong>
          <ul>
            <li>Replace logo files in your project:
              <ul>
                <li><strong>Login/Register:</strong> <code>resources/js/src/views/assest/img/logo.png</code></li>
                <li><strong>Dashboard Header:</strong> <code>resources/js/src/views/FrontEndSide/logo.png</code></li>
              </ul>
            </li>
            <li>Enter the file path in the text field (only shown if no logo is uploaded)</li>
          </ul>
        </li>
        <li><strong>Note:</strong> Uploaded logos are stored in your browser and will persist across sessions. To remove an uploaded logo, click the "Remove" button.</li>
      </ol>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { getBrandConfig } from '@/config/brand';
import { toast } from 'vue3-toastify';

// Default brand configuration (fallback)
const defaultBrandConfig = {
  companyName: 'Macxgain',
  tagline: 'Smart Trading Solutions',
  welcomeText: 'Welcome Back',
  subtitle: 'Sign in to your account to continue',
  logoPath: '../assest/img/logo.png',
  logoPathPublic: '/logo.png',
  logoPathFrontend: '../FrontEndSide/logo.png',
  logoPathHeader: '../FrontEndSide/logo.png',
  pageTitle: 'Macxgain - Trading with AI and Gain Profit',
  loginTitle: 'Login - Macxgain',
  registerTitle: 'Register - Macxgain',
  email: 'info@macxgain.com',
  website: 'https://macxgain.com',
  copyrightYear: '2025',
  copyrightText: 'Macxgain. All Rights Reserved.'
};

// Form data
const formData = ref({
  companyName: '',
  tagline: '',
  welcomeText: '',
  subtitle: '',
  logoPath: '',
  logoPathHeader: '',
  logoPathPublic: '',
  logoPathFrontend: '',
  loginLogoBase64: '',  // Base64 encoded logo for login
  headerLogoBase64: '', // Base64 encoded logo for header
  pageTitle: '',
  loginTitle: '',
  registerTitle: '',
  email: '',
  website: '',
  copyrightYear: '2025',
  copyrightText: ''
});

const isSaving = ref(false);
const showSuccess = ref(false);
const uploadingLogin = ref(false);
const uploadingHeader = ref(false);
const loginLogoInput = ref(null);
const headerLogoInput = ref(null);

// Load saved settings or use defaults
const loadSettings = () => {
  const saved = localStorage.getItem('brandSettings');
  if (saved) {
    try {
      const parsed = JSON.parse(saved);
      // Merge with defaults, prioritizing saved values
      formData.value = { 
        ...defaultBrandConfig, 
        ...parsed,
        loginLogoBase64: '',  // Initialize separately
        headerLogoBase64: '' // Initialize separately
      };
      
      // Check if logoPath contains base64 (uploaded logo)
      if (formData.value.logoPath && formData.value.logoPath.startsWith('data:image')) {
        formData.value.loginLogoBase64 = formData.value.logoPath;
      }
      
      // Check if logoPathHeader contains base64 (uploaded logo)
      if (formData.value.logoPathHeader && formData.value.logoPathHeader.startsWith('data:image')) {
        formData.value.headerLogoBase64 = formData.value.logoPathHeader;
      }
    } catch (e) {
      console.error('Error loading settings:', e);
      formData.value = { ...defaultBrandConfig, loginLogoBase64: '', headerLogoBase64: '' };
    }
  } else {
    // Use current brandConfig from localStorage
    const currentConfig = getBrandConfig();
    formData.value = { 
      ...currentConfig, 
      loginLogoBase64: '', 
      headerLogoBase64: '' 
    };
  }
};

// Save settings
const saveSettings = async () => {
  // Prevent double submission
  if (isSaving.value) {
    return;
  }
  
  isSaving.value = true;
  
  try {
    console.log('Saving settings...', formData.value);
    
    // Prepare data for saving
    const dataToSave = { ...formData.value };
    
    // If base64 logos are set, use them; otherwise use paths
    if (dataToSave.loginLogoBase64) {
      dataToSave.logoPath = dataToSave.loginLogoBase64;
    } else if (!dataToSave.logoPath) {
      dataToSave.logoPath = defaultBrandConfig.logoPath;
    }
    
    if (dataToSave.headerLogoBase64) {
      dataToSave.logoPathHeader = dataToSave.headerLogoBase64;
      dataToSave.logoPathFrontend = dataToSave.headerLogoBase64;
    } else if (!dataToSave.logoPathHeader) {
      dataToSave.logoPathHeader = defaultBrandConfig.logoPathHeader;
      dataToSave.logoPathFrontend = defaultBrandConfig.logoPathFrontend;
    }
    
    // Remove the base64 fields from saved data (they're already in logoPath/logoPathHeader)
    delete dataToSave.loginLogoBase64;
    delete dataToSave.headerLogoBase64;
    
    // Save to localStorage
    const jsonString = JSON.stringify(dataToSave);
    
    // Check if data is too large for localStorage (some browsers have 5-10MB limit)
    if (jsonString.length > 5 * 1024 * 1024) {
      throw new Error('Settings data is too large. Please use smaller logo images (under 1MB each).');
    }
    
    localStorage.setItem('brandSettings', jsonString);
    console.log('✅ Settings saved to localStorage');
    console.log('📋 Company Name:', dataToSave.companyName);
    console.log('📋 Logo Path:', dataToSave.logoPath?.substring(0, 50));
    
    // Update the reactive brand config immediately
    const { updateBrandConfig } = await import('@/config/brand');
    updateBrandConfig(dataToSave);
    
    // Update document title immediately
    if (dataToSave.pageTitle) {
      document.title = dataToSave.pageTitle;
    }
    
    // Update favicon immediately if it's a base64 image
    if (dataToSave.logoPath && dataToSave.logoPath.startsWith('data:image')) {
      const faviconLink = document.getElementById('favicon-link');
      if (faviconLink) {
        faviconLink.href = dataToSave.logoPath;
        faviconLink.type = 'image/png';
      }
      const shortcutIcon = document.getElementById('shortcut-icon');
      if (shortcutIcon) {
        shortcutIcon.href = dataToSave.logoPath;
        shortcutIcon.type = 'image/png';
      }
      const appleIcon = document.getElementById('apple-icon');
      if (appleIcon) appleIcon.href = dataToSave.logoPath;
    }
    
    showSuccess.value = true;
    toast.success('Brand settings saved! Reloading page to apply changes everywhere...', {
      autoClose: 2000,
      position: "top-right"
    });
    
    // Force full page reload - this ensures ALL components reload with new config
    setTimeout(() => {
      window.location.reload();
    }, 2000);
    
  } catch (error) {
    console.error('Save error:', error);
    isSaving.value = false;
    showSuccess.value = false;
    
    let errorMessage = 'Failed to save settings.';
    if (error.message) {
      errorMessage += ' ' + error.message;
    } else if (error.name === 'QuotaExceededError') {
      errorMessage = 'Storage limit exceeded. Please use smaller logo images or clear browser storage.';
    }
    
    toast.error(errorMessage, {
      autoClose: 4000,
      position: "top-right"
    });
  }
};

// Handle logo upload - Login/Register
const handleLoginLogoUpload = (event) => {
  const file = event.target.files[0];
  if (!file) return;

  // Validate file type
  if (!file.type.startsWith('image/')) {
    toast.error('Please select an image file', {
      autoClose: 3000,
      position: "top-right"
    });
    return;
  }

  // Validate file size (max 2MB)
  if (file.size > 2 * 1024 * 1024) {
    toast.error('Image size should be less than 2MB', {
      autoClose: 3000,
      position: "top-right"
    });
    return;
  }

  uploadingLogin.value = true;
  const reader = new FileReader();

  reader.onload = (e) => {
    formData.value.loginLogoBase64 = e.target.result;
    uploadingLogin.value = false;
    toast.success('Logo uploaded successfully!', {
      autoClose: 2000,
      position: "top-right"
    });
  };

  reader.onerror = () => {
    uploadingLogin.value = false;
    toast.error('Failed to read image file', {
      autoClose: 3000,
      position: "top-right"
    });
  };

  reader.readAsDataURL(file);
};

// Handle logo upload - Header
const handleHeaderLogoUpload = (event) => {
  const file = event.target.files[0];
  if (!file) return;

  // Validate file type
  if (!file.type.startsWith('image/')) {
    toast.error('Please select an image file', {
      autoClose: 3000,
      position: "top-right"
    });
    return;
  }

  // Validate file size (max 2MB)
  if (file.size > 2 * 1024 * 1024) {
    toast.error('Image size should be less than 2MB', {
      autoClose: 3000,
      position: "top-right"
    });
    return;
  }

  uploadingHeader.value = true;
  const reader = new FileReader();

  reader.onload = (e) => {
    formData.value.headerLogoBase64 = e.target.result;
    uploadingHeader.value = false;
    toast.success('Logo uploaded successfully!', {
      autoClose: 2000,
      position: "top-right"
    });
  };

  reader.onerror = () => {
    uploadingHeader.value = false;
    toast.error('Failed to read image file', {
      autoClose: 3000,
      position: "top-right"
    });
  };

  reader.readAsDataURL(file);
};

// Remove login logo
const removeLoginLogo = () => {
  formData.value.loginLogoBase64 = '';
  if (loginLogoInput.value) {
    loginLogoInput.value.value = '';
  }
  toast.info('Logo removed', {
    autoClose: 2000,
    position: "top-right"
  });
};

// Remove header logo
const removeHeaderLogo = () => {
  formData.value.headerLogoBase64 = '';
  if (headerLogoInput.value) {
    headerLogoInput.value.value = '';
  }
  toast.info('Logo removed', {
    autoClose: 2000,
    position: "top-right"
  });
};

// Reset to default
const resetToDefault = () => {
  if (confirm('Are you sure you want to reset to default settings? This will remove uploaded logos.')) {
    localStorage.removeItem('brandSettings');
    formData.value = { ...defaultBrandConfig, loginLogoBase64: '', headerLogoBase64: '' };
    if (loginLogoInput.value) loginLogoInput.value.value = '';
    if (headerLogoInput.value) headerLogoInput.value.value = '';
    toast.info('Settings reset to default', {
      autoClose: 2000,
      position: "top-right"
    });
  }
};

onMounted(() => {
  loadSettings();
});
</script>

<style scoped>
.brand-settings-screen {
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

/* Success Alert */
.success-alert {
  background: rgba(0, 255, 136, 0.1);
  border: 1px solid rgba(0, 255, 136, 0.3);
  color: #00ff88;
  padding: 16px;
  border-radius: 8px;
  margin-bottom: 20px;
  display: flex;
  align-items: center;
  gap: 12px;
  font-weight: 500;
}

/* Settings Card */
.settings-card {
  background: linear-gradient(145deg, #101022, #0d0d1a);
  border: 1px solid rgba(0, 255, 136, 0.2);
  border-radius: 16px;
  padding: 30px;
  margin-bottom: 24px;
}

.card-title {
  font-size: 20px;
  font-weight: 600;
  color: #00ff88;
  margin: 0 0 24px 0;
  padding-bottom: 12px;
  border-bottom: 1px solid rgba(0, 255, 136, 0.2);
}

/* Form Styles */
.settings-form {
  display: flex;
  flex-direction: column;
  gap: 24px;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.form-label {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 14px;
  font-weight: 600;
  color: rgba(255, 255, 255, 0.9);
}

.form-label i {
  color: #00ff88;
}

.form-input {
  width: 100%;
  padding: 12px 16px;
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 8px;
  color: white;
  font-size: 14px;
  transition: all 0.3s ease;
}

.form-input:focus {
  outline: none;
  border-color: #00ff88;
  background: rgba(255, 255, 255, 0.08);
  box-shadow: 0 0 0 3px rgba(0, 255, 136, 0.1);
}

.form-input::placeholder {
  color: rgba(255, 255, 255, 0.4);
}

.form-help {
  font-size: 12px;
  color: rgba(255, 255, 255, 0.5);
  margin: 0;
}

/* Logo Upload Section */
.logo-upload-section {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 20px;
  margin-top: 12px;
}

.logo-upload-item {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.logo-label {
  font-size: 13px;
  font-weight: 600;
  color: rgba(255, 255, 255, 0.8);
}

.logo-preview {
  width: 100%;
  height: 120px;
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
  position: relative;
}

.preview-image {
  max-width: 100%;
  max-height: 100%;
  object-fit: contain;
}

.preview-placeholder {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 8px;
  color: rgba(255, 255, 255, 0.4);
}

.preview-placeholder i {
  font-size: 32px;
}

/* File Upload Styles */
.file-upload-wrapper {
  display: flex;
  gap: 8px;
  margin-top: 8px;
}

.file-input {
  display: none;
}

.file-upload-btn {
  flex: 1;
  padding: 10px 16px;
  background: linear-gradient(145deg, #00ff88, #00cc66);
  color: #0d0d1a;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  font-size: 14px;
}

.file-upload-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 255, 136, 0.3);
}

.remove-logo-btn {
  padding: 10px 16px;
  background: rgba(255, 68, 68, 0.1);
  color: #ff4444;
  border: 1px solid rgba(255, 68, 68, 0.3);
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 14px;
}

.remove-logo-btn:hover {
  background: rgba(255, 68, 68, 0.2);
  border-color: #ff4444;
}

.upload-overlay {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.7);
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 8px;
  color: white;
  border-radius: 8px;
  z-index: 10;
}

.upload-overlay i {
  font-size: 24px;
  color: #00ff88;
}

.upload-overlay span {
  font-size: 12px;
}

/* Form Actions */
.form-actions {
  display: flex;
  gap: 12px;
  justify-content: flex-end;
  margin-top: 8px;
  padding-top: 24px;
  border-top: 1px solid rgba(255, 255, 255, 0.1);
}

.btn-primary, .btn-secondary {
  padding: 12px 24px;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 14px;
}

.btn-primary {
  background: linear-gradient(145deg, #00ff88, #00cc66);
  color: #0d0d1a;
}

.btn-primary:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(0, 255, 136, 0.3);
}

.btn-primary:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.btn-secondary {
  background: rgba(0, 255, 128, 0.1);
  color: #00ff80;
  border: 1px solid rgba(0, 255, 128, 0.3);
}

.btn-secondary:hover {
  background: rgba(0, 255, 128, 0.2);
  border-color: #00ff80;
}

/* Info Card */
.info-card {
  background: linear-gradient(145deg, #101022, #0d0d1a);
  border: 1px solid rgba(0, 255, 136, 0.2);
  border-radius: 16px;
  padding: 24px;
}

.info-title {
  font-size: 18px;
  font-weight: 600;
  color: #00ff88;
  margin: 0 0 16px 0;
  display: flex;
  align-items: center;
  gap: 8px;
}

.info-list {
  margin: 0;
  padding-left: 20px;
  color: rgba(255, 255, 255, 0.8);
  line-height: 1.8;
}

.info-list li {
  margin-bottom: 8px;
}

.info-list ul {
  margin-top: 8px;
  margin-left: 20px;
  list-style: disc;
}

.info-list code {
  background: rgba(0, 255, 136, 0.1);
  padding: 2px 6px;
  border-radius: 4px;
  font-size: 12px;
  color: #00ff88;
}

/* Responsive */
@media (max-width: 768px) {
  .brand-settings-screen {
    padding: 16px;
  }
  
  .settings-card {
    padding: 20px;
  }
  
  .logo-upload-section {
    grid-template-columns: 1fr;
  }
  
  .form-actions {
    flex-direction: column;
  }
  
  .btn-primary, .btn-secondary {
    width: 100%;
    justify-content: center;
  }
}
</style>

