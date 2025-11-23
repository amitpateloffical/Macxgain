<template>
  <div class="template-settings-screen">
    <!-- Header Section -->
    <div class="page-header">
      <div class="header-content">
        <h1 class="page-title">🎨 Template Settings</h1>
        <p class="page-subtitle">Choose a color theme for your entire website</p>
      </div>
    </div>

    <!-- Success Message -->
    <div v-if="showSuccess" class="success-alert">
      <i class="fas fa-check-circle"></i>
      <span>Template applied successfully! The page will reload to apply changes.</span>
    </div>

    <!-- Add Custom Template Button -->
    <div class="add-template-section">
      <button class="add-template-btn" @click="showAddTemplateModal = true">
        <i class="fas fa-plus"></i> Create Custom Template
      </button>
    </div>

    <!-- Template Selection -->
    <div class="templates-container">
      <h2 class="section-title">Available Templates</h2>
      <p class="section-description">Click on any template to preview and apply it</p>
      
      <div class="templates-grid">
        <!-- Template 1: Green Tech -->
        <div 
          class="template-card" 
          :class="{ active: selectedTemplate === 'template1' }"
          @click="selectTemplate('template1')"
        >
          <div class="template-preview" style="background: linear-gradient(135deg, #0d0d1a 0%, #101022 50%, #16213e 100%);">
            <div class="preview-header" style="background: rgba(0, 255, 136, 0.1); border-color: rgba(0, 255, 136, 0.2);">
              <div class="preview-logo" style="background: #00ff88; color: #0d0d1a;">LG</div>
              <div class="preview-title" style="color: #00ff88;">Green Tech</div>
            </div>
            <div class="preview-content">
              <div class="preview-card" style="background: rgba(0, 255, 136, 0.1); border-color: rgba(0, 255, 136, 0.2);">
                <div class="preview-text" style="color: #00ff88;">Sample Card</div>
              </div>
            </div>
          </div>
          <div class="template-info">
            <h3 class="template-name">Green Tech (Default)</h3>
            <p class="template-desc">Modern green tech theme</p>
            <div class="template-colors">
              <span class="color-dot" style="background: #00ff88;"></span>
              <span class="color-dot" style="background: #0d0d1a;"></span>
              <span class="color-dot" style="background: #101022;"></span>
            </div>
          </div>
        </div>

        <!-- Template 2: Blue Ocean -->
        <div 
          class="template-card" 
          :class="{ active: selectedTemplate === 'template2' }"
          @click="selectTemplate('template2')"
        >
          <div class="template-preview" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #334155 100%);">
            <div class="preview-header" style="background: rgba(59, 130, 246, 0.1); border-color: rgba(59, 130, 246, 0.2);">
              <div class="preview-logo" style="background: #3b82f6; color: #0f172a;">LG</div>
              <div class="preview-title" style="color: #3b82f6;">Blue Ocean</div>
            </div>
            <div class="preview-content">
              <div class="preview-card" style="background: rgba(59, 130, 246, 0.1); border-color: rgba(59, 130, 246, 0.2);">
                <div class="preview-text" style="color: #3b82f6;">Sample Card</div>
              </div>
            </div>
          </div>
          <div class="template-info">
            <h3 class="template-name">Blue Ocean</h3>
            <p class="template-desc">Calm blue professional theme</p>
            <div class="template-colors">
              <span class="color-dot" style="background: #3b82f6;"></span>
              <span class="color-dot" style="background: #0f172a;"></span>
              <span class="color-dot" style="background: #1e293b;"></span>
            </div>
          </div>
        </div>

        <!-- Template 3: Purple Royal -->
        <div 
          class="template-card" 
          :class="{ active: selectedTemplate === 'template3' }"
          @click="selectTemplate('template3')"
        >
          <div class="template-preview" style="background: linear-gradient(135deg, #1a0b2e 0%, #2d1b3d 50%, #3d2a4d 100%);">
            <div class="preview-header" style="background: rgba(139, 92, 246, 0.1); border-color: rgba(139, 92, 246, 0.2);">
              <div class="preview-logo" style="background: #8b5cf6; color: #1a0b2e;">LG</div>
              <div class="preview-title" style="color: #8b5cf6;">Purple Royal</div>
            </div>
            <div class="preview-content">
              <div class="preview-card" style="background: rgba(139, 92, 246, 0.1); border-color: rgba(139, 92, 246, 0.2);">
                <div class="preview-text" style="color: #8b5cf6;">Sample Card</div>
              </div>
            </div>
          </div>
          <div class="template-info">
            <h3 class="template-name">Purple Royal</h3>
            <p class="template-desc">Elegant purple luxury theme</p>
            <div class="template-colors">
              <span class="color-dot" style="background: #8b5cf6;"></span>
              <span class="color-dot" style="background: #1a0b2e;"></span>
              <span class="color-dot" style="background: #2d1b3d;"></span>
            </div>
          </div>
        </div>

        <!-- Template 4: Orange Fire -->
        <div 
          class="template-card" 
          :class="{ active: selectedTemplate === 'template4' }"
          @click="selectTemplate('template4')"
        >
          <div class="template-preview" style="background: linear-gradient(135deg, #1c0a00 0%, #2d1500 50%, #3d1f00 100%);">
            <div class="preview-header" style="background: rgba(249, 115, 22, 0.1); border-color: rgba(249, 115, 22, 0.2);">
              <div class="preview-logo" style="background: #f97316; color: #1c0a00;">LG</div>
              <div class="preview-title" style="color: #f97316;">Orange Fire</div>
            </div>
            <div class="preview-content">
              <div class="preview-card" style="background: rgba(249, 115, 22, 0.1); border-color: rgba(249, 115, 22, 0.2);">
                <div class="preview-text" style="color: #f97316;">Sample Card</div>
              </div>
            </div>
          </div>
          <div class="template-info">
            <h3 class="template-name">Orange Fire</h3>
            <p class="template-desc">Energetic orange vibrant theme</p>
            <div class="template-colors">
              <span class="color-dot" style="background: #f97316;"></span>
              <span class="color-dot" style="background: #1c0a00;"></span>
              <span class="color-dot" style="background: #2d1500;"></span>
            </div>
          </div>
        </div>

        <!-- Template 5: Cyan Modern -->
        <div 
          class="template-card" 
          :class="{ active: selectedTemplate === 'template5' }"
          @click="selectTemplate('template5')"
        >
          <div class="template-preview" style="background: linear-gradient(135deg, #0a1628 0%, #1a2838 50%, #2a3848 100%);">
            <div class="preview-header" style="background: rgba(6, 182, 212, 0.1); border-color: rgba(6, 182, 212, 0.2);">
              <div class="preview-logo" style="background: #06b6d4; color: #0a1628;">LG</div>
              <div class="preview-title" style="color: #06b6d4;">Cyan Modern</div>
            </div>
            <div class="preview-content">
              <div class="preview-card" style="background: rgba(6, 182, 212, 0.1); border-color: rgba(6, 182, 212, 0.2);">
                <div class="preview-text" style="color: #06b6d4;">Sample Card</div>
              </div>
            </div>
          </div>
          <div class="template-info">
            <h3 class="template-name">Cyan Modern</h3>
            <p class="template-desc">Modern cyan fresh theme</p>
            <div class="template-colors">
              <span class="color-dot" style="background: #06b6d4;"></span>
              <span class="color-dot" style="background: #0a1628;"></span>
              <span class="color-dot" style="background: #1a2838;"></span>
            </div>
          </div>
        </div>

        <!-- Custom Templates -->
        <div 
          v-for="(template, templateId) in customTemplates" 
          :key="templateId"
          class="template-card custom-template" 
          :class="{ active: selectedTemplate === templateId }"
          @click="selectTemplate(templateId)"
        >
          <div class="template-preview" :style="getTemplatePreviewStyle(template)">
            <div class="preview-header" :style="getTemplateHeaderStyle(template)">
              <div class="preview-logo" :style="{ background: template.colors.primary, color: template.colors.bgPrimary }">LG</div>
              <div class="preview-title" :style="{ color: template.colors.primary }">{{ template.name }}</div>
            </div>
            <div class="preview-content">
              <div class="preview-card" :style="getTemplateCardStyle(template)">
                <div class="preview-text" :style="{ color: template.colors.primary }">Sample Card</div>
              </div>
            </div>
          </div>
          <div class="template-info">
            <div class="template-header-row">
              <h3 class="template-name">{{ template.name }}</h3>
              <button class="delete-template-btn" @click.stop="deleteCustomTemplate(templateId)" title="Delete Template">
                <i class="fas fa-trash"></i>
              </button>
            </div>
            <p class="template-desc">{{ template.description || 'Custom template' }}</p>
            <div class="template-colors">
              <span class="color-dot" :style="{ background: template.colors.primary }"></span>
              <span class="color-dot" :style="{ background: template.colors.bgPrimary }"></span>
              <span class="color-dot" :style="{ background: template.colors.bgSecondary }"></span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Add Custom Template Modal -->
    <div v-if="showAddTemplateModal" class="modal-overlay" @click="showAddTemplateModal = false">
      <div class="modal-content" @click.stop>
        <div class="modal-header">
          <h2>Create Custom Template</h2>
          <button class="close-modal-btn" @click="showAddTemplateModal = false">&times;</button>
        </div>
        
        <div class="modal-body">
          <div class="form-group">
            <label>Template Name *</label>
            <input v-model="newTemplate.name" type="text" placeholder="e.g., My Custom Theme" />
          </div>
          
          <div class="form-group">
            <label>Description</label>
            <input v-model="newTemplate.description" type="text" placeholder="Brief description of the theme" />
          </div>

          <div class="color-grid">
            <div class="form-group">
              <label>Primary Color *</label>
              <div class="color-input-group">
                <input v-model="newTemplate.colors.primary" type="color" />
                <input v-model="newTemplate.colors.primary" type="text" placeholder="#00ff88" />
              </div>
            </div>

            <div class="form-group">
              <label>Primary Dark</label>
              <div class="color-input-group">
                <input v-model="newTemplate.colors.primaryDark" type="color" />
                <input v-model="newTemplate.colors.primaryDark" type="text" placeholder="#00cc66" />
              </div>
            </div>

            <div class="form-group">
              <label>Primary Light</label>
              <div class="color-input-group">
                <input v-model="newTemplate.colors.primaryLight" type="color" />
                <input v-model="newTemplate.colors.primaryLight" type="text" placeholder="#00d4aa" />
              </div>
            </div>

            <div class="form-group">
              <label>Background Primary *</label>
              <div class="color-input-group">
                <input v-model="newTemplate.colors.bgPrimary" type="color" />
                <input v-model="newTemplate.colors.bgPrimary" type="text" placeholder="#0d0d1a" />
              </div>
            </div>

            <div class="form-group">
              <label>Background Secondary *</label>
              <div class="color-input-group">
                <input v-model="newTemplate.colors.bgSecondary" type="color" />
                <input v-model="newTemplate.colors.bgSecondary" type="text" placeholder="#101022" />
              </div>
            </div>

            <div class="form-group">
              <label>Background Tertiary</label>
              <div class="color-input-group">
                <input v-model="newTemplate.colors.bgTertiary" type="color" />
                <input v-model="newTemplate.colors.bgTertiary" type="text" placeholder="#1a1a2e" />
              </div>
            </div>

            <div class="form-group">
              <label>Background Quaternary</label>
              <div class="color-input-group">
                <input v-model="newTemplate.colors.bgQuaternary" type="color" />
                <input v-model="newTemplate.colors.bgQuaternary" type="text" placeholder="#16213e" />
              </div>
            </div>

            <div class="form-group">
              <label>Text Primary</label>
              <div class="color-input-group">
                <input v-model="newTemplate.colors.textPrimary" type="color" />
                <input v-model="newTemplate.colors.textPrimary" type="text" placeholder="#ffffff" />
              </div>
            </div>

            <div class="form-group">
              <label>Text Secondary</label>
              <div class="color-input-group">
                <input v-model="newTemplate.colors.textSecondary" type="text" placeholder="rgba(255, 255, 255, 0.7)" />
              </div>
            </div>

            <div class="form-group">
              <label>Text Muted</label>
              <div class="color-input-group">
                <input v-model="newTemplate.colors.textMuted" type="color" />
                <input v-model="newTemplate.colors.textMuted" type="text" placeholder="#9ca3af" />
              </div>
            </div>

            <div class="form-group">
              <label>Success Color</label>
              <div class="color-input-group">
                <input v-model="newTemplate.colors.success" type="color" />
                <input v-model="newTemplate.colors.success" type="text" placeholder="#00ff88" />
              </div>
            </div>

            <div class="form-group">
              <label>Error Color</label>
              <div class="color-input-group">
                <input v-model="newTemplate.colors.error" type="color" />
                <input v-model="newTemplate.colors.error" type="text" placeholder="#ff4444" />
              </div>
            </div>

            <div class="form-group">
              <label>Warning Color</label>
              <div class="color-input-group">
                <input v-model="newTemplate.colors.warning" type="color" />
                <input v-model="newTemplate.colors.warning" type="text" placeholder="#ffaa00" />
              </div>
            </div>
          </div>

          <div class="template-preview-box">
            <h4>Preview</h4>
            <div class="preview-box" :style="getTemplatePreviewStyle(newTemplate)">
              <div class="preview-header" :style="getTemplateHeaderStyle(newTemplate)">
                <div class="preview-logo" :style="{ background: newTemplate.colors.primary, color: newTemplate.colors.bgPrimary }">LG</div>
                <div class="preview-title" :style="{ color: newTemplate.colors.primary }">{{ newTemplate.name || 'Template Name' }}</div>
              </div>
              <div class="preview-content">
                <div class="preview-card" :style="getTemplateCardStyle(newTemplate)">
                  <div class="preview-text" :style="{ color: newTemplate.colors.primary }">Sample Card</div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="modal-footer">
          <button class="cancel-btn" @click="showAddTemplateModal = false">Cancel</button>
          <button class="save-btn" @click="saveCustomTemplate">Save Template</button>
        </div>
      </div>
    </div>

    <!-- Apply Button -->
    <div class="action-section">
      <button 
        class="apply-btn" 
        @click="applyTemplatePermanently"
        :disabled="isApplying || selectedTemplate === currentTemplate"
      >
        <i class="fas fa-check"></i>
        {{ isApplying ? 'Applying...' : selectedTemplate === currentTemplate ? 'Current Template' : 'Apply Template' }}
      </button>
      <button 
        class="preview-btn" 
        @click="previewTemplate"
        :disabled="isApplying"
      >
        <i :class="isPreviewing ? 'fas fa-undo' : 'fas fa-eye'"></i> 
        {{ isPreviewing ? 'Restore Original' : 'Preview' }}
      </button>
    </div>

    <!-- Info Card -->
    <div class="info-card">
      <h3 class="info-title">
        <i class="fas fa-info-circle"></i> How Templates Work
      </h3>
      <ul class="info-list">
        <li>Templates change the entire website's color scheme instantly</li>
        <li>All pages, buttons, cards, and UI elements will use the selected template colors</li>
        <li>Your logo and company name remain unchanged</li>
        <li>Click "Preview" to see the template without applying</li>
        <li>Click "Apply Template" to save and reload with the new theme</li>
      </ul>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount, computed } from 'vue';
import { templates, getCurrentTemplate, applyTemplate, getAllTemplates, getCustomTemplates, saveCustomTemplate as saveTemplate, deleteCustomTemplate as deleteTemplate } from '@/config/templates';
import { toast } from 'vue3-toastify';

const selectedTemplate = ref('template1');
const currentTemplate = ref('template1');
const originalTemplate = ref('template1');
const isApplying = ref(false);
const showSuccess = ref(false);
const isPreviewing = ref(false);
const showAddTemplateModal = ref(false);
const customTemplates = ref(getCustomTemplates());

// New template form
const newTemplate = ref({
  name: '',
  description: '',
  colors: {
    primary: '#00ff88',
    primaryDark: '#00cc66',
    primaryLight: '#00d4aa',
    bgPrimary: '#0d0d1a',
    bgSecondary: '#101022',
    bgTertiary: '#1a1a2e',
    bgQuaternary: '#16213e',
    textPrimary: '#ffffff',
    textSecondary: 'rgba(255, 255, 255, 0.7)',
    textMuted: '#9ca3af',
    success: '#00ff88',
    error: '#ff4444',
    warning: '#ffaa00',
    borderPrimary: 'rgba(0, 255, 136, 0.2)',
    borderSecondary: 'rgba(255, 255, 255, 0.1)',
  }
});

// Load current template
onMounted(() => {
  const current = getCurrentTemplate();
  currentTemplate.value = current.id;
  originalTemplate.value = current.id;
  selectedTemplate.value = current.id;
  customTemplates.value = getCustomTemplates();
  
  // Listen for template changes
  window.addEventListener('templateChanged', handleTemplateChange);
});

onBeforeUnmount(() => {
  window.removeEventListener('templateChanged', handleTemplateChange);
});

const handleTemplateChange = () => {
  // Update current template reference
  const current = getCurrentTemplate();
  currentTemplate.value = current.id;
};

// Helper function to convert hex to RGB (defined once at top level)
const hexToRgb = (hex) => {
  if (!hex) return null;
  const result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
  return result ? {
    r: parseInt(result[1], 16),
    g: parseInt(result[2], 16),
    b: parseInt(result[3], 16)
  } : null;
};

// Template preview styles
const getTemplatePreviewStyle = (template) => {
  return {
    background: `linear-gradient(135deg, ${template.colors.bgPrimary} 0%, ${template.colors.bgSecondary} 50%, ${template.colors.bgQuaternary || template.colors.bgTertiary} 100%)`
  };
};

const getTemplateHeaderStyle = (template) => {
  const primaryRgb = hexToRgb(template.colors.primary);
  return {
    background: primaryRgb ? `rgba(${primaryRgb.r}, ${primaryRgb.g}, ${primaryRgb.b}, 0.1)` : 'rgba(0, 255, 136, 0.1)',
    borderColor: primaryRgb ? `rgba(${primaryRgb.r}, ${primaryRgb.g}, ${primaryRgb.b}, 0.2)` : 'rgba(0, 255, 136, 0.2)'
  };
};

const getTemplateCardStyle = (template) => {
  const primaryRgb = hexToRgb(template.colors.primary);
  return {
    background: primaryRgb ? `rgba(${primaryRgb.r}, ${primaryRgb.g}, ${primaryRgb.b}, 0.1)` : 'rgba(0, 255, 136, 0.1)',
    borderColor: primaryRgb ? `rgba(${primaryRgb.r}, ${primaryRgb.g}, ${primaryRgb.b}, 0.2)` : 'rgba(0, 255, 136, 0.2)'
  };
};

// Save custom template
const saveCustomTemplate = () => {
  if (!newTemplate.value.name || !newTemplate.value.colors.primary || !newTemplate.value.colors.bgPrimary || !newTemplate.value.colors.bgSecondary) {
    toast.error('Please fill in all required fields (Name, Primary Color, Background Primary, Background Secondary)', {
      autoClose: 3000,
      position: "top-right"
    });
    return;
  }

  const templateId = 'custom_' + Date.now();
  const templateData = {
    name: newTemplate.value.name,
    description: newTemplate.value.description,
    colors: { ...newTemplate.value.colors }
  };

  if (saveTemplate(templateId, templateData)) {
    customTemplates.value = getCustomTemplates();
    showAddTemplateModal.value = false;
    toast.success('Custom template created successfully!', {
      autoClose: 2000,
      position: "top-right"
    });
    
    // Reset form
    newTemplate.value = {
      name: '',
      description: '',
      colors: {
        primary: '#00ff88',
        primaryDark: '#00cc66',
        primaryLight: '#00d4aa',
        bgPrimary: '#0d0d1a',
        bgSecondary: '#101022',
        bgTertiary: '#1a1a2e',
        bgQuaternary: '#16213e',
        textPrimary: '#ffffff',
        textSecondary: 'rgba(255, 255, 255, 0.7)',
        textMuted: '#9ca3af',
        success: '#00ff88',
        error: '#ff4444',
        warning: '#ffaa00',
        borderPrimary: 'rgba(0, 255, 136, 0.2)',
        borderSecondary: 'rgba(255, 255, 255, 0.1)',
      }
    };
  } else {
    toast.error('Failed to save custom template', {
      autoClose: 3000,
      position: "top-right"
    });
  }
};

// Delete custom template
const deleteCustomTemplate = (templateId) => {
  if (confirm('Are you sure you want to delete this custom template?')) {
    if (deleteTemplate(templateId)) {
      customTemplates.value = getCustomTemplates();
      if (selectedTemplate.value === templateId) {
        selectedTemplate.value = 'template1';
      }
      toast.success('Template deleted successfully', {
        autoClose: 2000,
        position: "top-right"
      });
    } else {
      toast.error('Failed to delete template', {
        autoClose: 3000,
        position: "top-right"
      });
    }
  }
};

// Select template and auto-preview
const selectTemplate = (templateId) => {
  selectedTemplate.value = templateId;
  // Auto-preview when template is selected
  autoPreviewTemplate(templateId);
};

// Auto-preview template when selected
const autoPreviewTemplate = (templateId) => {
  // Don't preview if it's the current active template and not already previewing
  if (templateId === currentTemplate.value && !isPreviewing.value) {
    return;
  }
  
  // Get template - handle both predefined and custom
  const allTemplates = getAllTemplates();
  const template = allTemplates[templateId];
  
  // Validate template exists
  if (!template || !template.colors) {
    console.warn('Template not found:', templateId);
    return;
  }
  
  // If already previewing, just switch to new template preview
  // (no need to restore, just apply new preview)
  
  // Apply preview
  try {
    applyTemplatePreview(template);
    isPreviewing.value = true;
  } catch (error) {
    console.error('Error applying auto-preview:', error);
  }
};

// Restore original template
const restoreOriginalTemplate = () => {
  // Use the applyTemplate function to properly restore (it handles localStorage and events)
  applyTemplate(originalTemplate.value);
  isPreviewing.value = false;
  
  // Also update selected template to match original if needed
  if (selectedTemplate.value !== originalTemplate.value) {
    selectedTemplate.value = originalTemplate.value;
  }
};

// Apply template preview (shared function)
const applyTemplatePreview = (template) => {
  // Validate template and colors
  if (!template || !template.colors) {
    throw new Error('Invalid template: missing template or colors');
  }
  
  const root = document.documentElement;
  const colors = template.colors;

  // Validate required colors exist
  if (!colors.primary || !colors.bgPrimary || !colors.bgSecondary) {
    throw new Error('Invalid template: missing required colors');
  }

  // Get RGB values for rgba() usage
  const primaryRgb = hexToRgb(colors.primary);
  const borderPrimaryRgb = primaryRgb || { r: 0, g: 255, b: 136 };

  // Apply CSS variables with fallbacks
  root.style.setProperty('--color-primary', colors.primary || '#00ff88');
  root.style.setProperty('--color-primary-dark', colors.primaryDark || colors.primary || '#00cc66');
  root.style.setProperty('--color-primary-light', colors.primaryLight || colors.primary || '#00d4aa');
  root.style.setProperty('--color-bg-primary', colors.bgPrimary || '#0d0d1a');
  root.style.setProperty('--color-bg-secondary', colors.bgSecondary || '#101022');
  root.style.setProperty('--color-bg-tertiary', colors.bgTertiary || colors.bgSecondary || '#1a1a2e');
  root.style.setProperty('--color-bg-quaternary', colors.bgQuaternary || colors.bgTertiary || '#16213e');
  root.style.setProperty('--color-text-primary', colors.textPrimary || '#ffffff');
  root.style.setProperty('--color-text-secondary', colors.textSecondary || 'rgba(255, 255, 255, 0.7)');
  root.style.setProperty('--color-text-muted', colors.textMuted || '#9ca3af');
  root.style.setProperty('--color-success', colors.success || colors.primary || '#00ff88');
  root.style.setProperty('--color-error', colors.error || '#ff4444');
  root.style.setProperty('--color-warning', colors.warning || '#ffaa00');
  root.style.setProperty('--color-border-primary', colors.borderPrimary || `rgba(${borderPrimaryRgb.r}, ${borderPrimaryRgb.g}, ${borderPrimaryRgb.b}, 0.2)`);
  root.style.setProperty('--color-border-secondary', colors.borderSecondary || 'rgba(255, 255, 255, 0.1)');

  // Set RGB values for rgba() usage
  const successRgb = hexToRgb(colors.success);
  const errorRgb = hexToRgb(colors.error);
  const warningRgb = hexToRgb(colors.warning);
  const primaryLightRgb = hexToRgb(colors.primaryLight);
  
  if (primaryRgb) {
    root.style.setProperty('--color-primary-rgb', `${primaryRgb.r}, ${primaryRgb.g}, ${primaryRgb.b}`);
  }
  if (successRgb) {
    root.style.setProperty('--color-success-rgb', `${successRgb.r}, ${successRgb.g}, ${successRgb.b}`);
  }
  if (errorRgb) {
    root.style.setProperty('--color-error-rgb', `${errorRgb.r}, ${errorRgb.g}, ${errorRgb.b}`);
  }
  if (warningRgb) {
    root.style.setProperty('--color-warning-rgb', `${warningRgb.r}, ${warningRgb.g}, ${warningRgb.b}`);
  }
  if (primaryLightRgb) {
    root.style.setProperty('--color-primary-light-rgb', `${primaryLightRgb.r}, ${primaryLightRgb.g}, ${primaryLightRgb.b}`);
  }
  
  // Force update body background
  if (document.body) {
    document.body.style.backgroundColor = colors.bgPrimary;
  }
};

// Preview template (apply temporarily without saving) - Manual preview button
const previewTemplate = () => {
  // If already previewing, restore original
  if (isPreviewing.value) {
    restoreOriginalTemplate();
    toast.info('Original template restored', {
      autoClose: 2000,
      position: "top-right"
    });
    return;
  }
  
  // Validate selected template exists
  if (!selectedTemplate.value) {
    toast.error('Please select a template first', {
      autoClose: 2000,
      position: "top-right"
    });
    return;
  }
  
  // Get template - handle both predefined and custom
  const allTemplates = getAllTemplates();
  const template = allTemplates[selectedTemplate.value];
  
  if (!template || !template.colors) {
    toast.error('Template not found. Please select a valid template.', {
      autoClose: 3000,
      position: "top-right"
    });
    return;
  }
  
  // Don't preview if it's the current active template (unless we want to show it again)
  if (selectedTemplate.value === currentTemplate.value) {
    toast.info('This template is already active. Select a different template to preview.', {
      autoClose: 2000,
      position: "top-right"
    });
    return;
  }
  
  // Apply preview
  try {
    applyTemplatePreview(template);
    isPreviewing.value = true;
    
    toast.success('Template preview applied! Scroll to see changes. Click "Apply Template" to save permanently.', {
      autoClose: 4000,
      position: "top-right"
    });
  } catch (error) {
    console.error('Error applying preview:', error);
    toast.error('Failed to apply preview. Please try again.', {
      autoClose: 3000,
      position: "top-right"
    });
  }
};

// Apply template permanently
const applyTemplatePermanently = async () => {
  if (selectedTemplate.value === currentTemplate.value && !isPreviewing.value) {
    toast.info('This template is already active', {
      autoClose: 2000,
      position: "top-right"
    });
    return;
  }

  isApplying.value = true;
  isPreviewing.value = false;
  
  try {
    // Apply template using the imported function (saves to localStorage)
    // This will work with both predefined and custom templates
    applyTemplate(selectedTemplate.value);
    
    // Update current template reference
    currentTemplate.value = selectedTemplate.value;
    originalTemplate.value = selectedTemplate.value;
    
    showSuccess.value = true;
    toast.success('Template applied successfully! Reloading page...', {
      autoClose: 2000,
      position: "top-right"
    });
    
    // Reload page to apply template everywhere
    setTimeout(() => {
      window.location.reload();
    }, 2000);
    
  } catch (error) {
    console.error('Error applying template:', error);
    toast.error('Failed to apply template', {
      autoClose: 3000,
      position: "top-right"
    });
  } finally {
    isApplying.value = false;
  }
};
</script>

<style scoped>
.template-settings-screen {
  background-color: var(--color-bg-primary, #0d0d1a);
  color: var(--color-text-primary, white);
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
  background: linear-gradient(145deg, var(--color-bg-secondary, #101022), var(--color-bg-primary, #0d0d1a));
  border: 1px solid var(--color-border-primary, rgba(0, 255, 136, 0.2));
  border-radius: 16px;
}

.page-title {
  font-size: 28px;
  font-weight: bold;
  color: var(--color-primary, #00ff88);
  margin: 0 0 8px 0;
}

.page-subtitle {
  color: var(--color-text-secondary, rgba(255, 255, 255, 0.7));
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

/* Templates Container */
.templates-container {
  margin-bottom: 30px;
}

.section-title {
  font-size: 22px;
  font-weight: 600;
  color: var(--color-primary, #00ff88);
  margin: 0 0 8px 0;
}

.section-description {
  color: var(--color-text-secondary, rgba(255, 255, 255, 0.7));
  margin: 0 0 24px 0;
  font-size: 14px;
}

/* Templates Grid */
.templates-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 24px;
  margin-bottom: 30px;
}

.template-card {
  background: linear-gradient(145deg, var(--color-bg-secondary, #101022), var(--color-bg-primary, #0d0d1a));
  border: 2px solid var(--color-border-secondary, rgba(255, 255, 255, 0.1));
  border-radius: 16px;
  overflow: hidden;
  cursor: pointer;
  transition: all 0.3s ease;
}

.template-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
  border-color: var(--color-primary, #00ff88);
}

.template-card.active {
  border-color: var(--color-primary, #00ff88);
  border-width: 3px;
  box-shadow: 0 0 20px var(--color-primary, #00ff88);
}

.template-preview {
  height: 200px;
  padding: 16px;
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.preview-header {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px;
  border-radius: 8px;
  border: 1px solid;
}

.preview-logo {
  width: 40px;
  height: 40px;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: bold;
  font-size: 14px;
}

.preview-title {
  font-weight: 600;
  font-size: 16px;
}

.preview-content {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
}

.preview-card {
  width: 100%;
  padding: 16px;
  border-radius: 8px;
  border: 1px solid;
  text-align: center;
}

.preview-text {
  font-weight: 500;
  font-size: 14px;
}

.template-info {
  padding: 20px;
  background: var(--color-bg-secondary, #101022);
}

.template-name {
  font-size: 18px;
  font-weight: 600;
  color: var(--color-text-primary, white);
  margin: 0 0 8px 0;
}

.template-desc {
  font-size: 14px;
  color: var(--color-text-secondary, rgba(255, 255, 255, 0.7));
  margin: 0 0 12px 0;
}

.template-colors {
  display: flex;
  gap: 8px;
}

.color-dot {
  width: 24px;
  height: 24px;
  border-radius: 50%;
  border: 2px solid rgba(255, 255, 255, 0.2);
}

/* Action Section */
.action-section {
  display: flex;
  gap: 12px;
  justify-content: center;
  margin-bottom: 30px;
}

.apply-btn, .preview-btn {
  padding: 14px 32px;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  font-size: 16px;
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  gap: 8px;
}

.apply-btn {
  background: linear-gradient(145deg, var(--color-primary, #00ff88), var(--color-primary-dark, #00cc66));
  color: var(--color-bg-primary, #0d0d1a);
}

.apply-btn:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px var(--color-primary, #00ff88);
}

.apply-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.preview-btn {
  background: rgba(255, 255, 255, 0.1);
  color: var(--color-text-primary, white);
  border: 1px solid var(--color-border-secondary, rgba(255, 255, 255, 0.2));
}

.preview-btn:hover:not(:disabled) {
  background: rgba(255, 255, 255, 0.15);
  border-color: var(--color-primary, #00ff88);
}

/* Info Card */
.info-card {
  background: linear-gradient(145deg, var(--color-bg-secondary, #101022), var(--color-bg-primary, #0d0d1a));
  border: 1px solid var(--color-border-primary, rgba(0, 255, 136, 0.2));
  border-radius: 16px;
  padding: 24px;
}

.info-title {
  font-size: 18px;
  font-weight: 600;
  color: var(--color-primary, #00ff88);
  margin: 0 0 16px 0;
  display: flex;
  align-items: center;
  gap: 8px;
}

.info-list {
  margin: 0;
  padding-left: 20px;
  color: var(--color-text-secondary, rgba(255, 255, 255, 0.8));
  line-height: 1.8;
}

.info-list li {
  margin-bottom: 8px;
}

/* Add Template Section */
.add-template-section {
  margin-bottom: 30px;
  display: flex;
  justify-content: center;
}

.add-template-btn {
  padding: 14px 32px;
  background: linear-gradient(145deg, var(--color-primary, #00ff88), var(--color-primary-dark, #00cc66));
  color: var(--color-bg-primary, #0d0d1a);
  border: none;
  border-radius: 8px;
  font-weight: 600;
  font-size: 16px;
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  gap: 8px;
}

.add-template-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px var(--color-primary, #00ff88);
}

/* Custom Template Styles */
.custom-template {
  position: relative;
}

.template-header-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 8px;
}

.delete-template-btn {
  background: rgba(255, 68, 68, 0.1);
  border: 1px solid rgba(255, 68, 68, 0.3);
  color: #ff4444;
  padding: 6px 10px;
  border-radius: 6px;
  cursor: pointer;
  transition: all 0.3s ease;
  font-size: 14px;
}

.delete-template-btn:hover {
  background: rgba(255, 68, 68, 0.2);
  border-color: #ff4444;
}

/* Modal Styles */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.7);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 2000;
  padding: 20px;
}

.modal-content {
  background: var(--color-bg-secondary, #101022);
  border: 1px solid var(--color-border-primary, rgba(0, 255, 136, 0.2));
  border-radius: 16px;
  max-width: 900px;
  width: 100%;
  max-height: 90vh;
  overflow-y: auto;
  color: var(--color-text-primary, white);
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 24px;
  border-bottom: 1px solid var(--color-border-secondary, rgba(255, 255, 255, 0.1));
}

.modal-header h2 {
  margin: 0;
  color: var(--color-primary, #00ff88);
  font-size: 24px;
}

.close-modal-btn {
  background: none;
  border: none;
  color: var(--color-text-primary, white);
  font-size: 28px;
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

.close-modal-btn:hover {
  background: rgba(255, 255, 255, 0.1);
  color: var(--color-error, #ff4444);
}

.modal-body {
  padding: 24px;
}

.form-group {
  margin-bottom: 20px;
}

.form-group label {
  display: block;
  margin-bottom: 8px;
  color: var(--color-text-primary, white);
  font-weight: 500;
  font-size: 14px;
}

.form-group input[type="text"],
.form-group input[type="color"] {
  width: 100%;
  padding: 10px;
  background: var(--color-bg-tertiary, #1a1a2e);
  border: 1px solid var(--color-border-secondary, rgba(255, 255, 255, 0.1));
  border-radius: 8px;
  color: var(--color-text-primary, white);
  font-size: 14px;
}

.form-group input[type="text"]:focus,
.form-group input[type="color"]:focus {
  outline: none;
  border-color: var(--color-primary, #00ff88);
  box-shadow: 0 0 0 3px rgba(var(--color-primary-rgb, 0, 255, 136), 0.1);
}

.color-input-group {
  display: flex;
  gap: 10px;
  align-items: center;
}

.color-input-group input[type="color"] {
  width: 60px;
  height: 40px;
  padding: 2px;
  cursor: pointer;
}

.color-input-group input[type="text"] {
  flex: 1;
}

.color-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 20px;
  margin-bottom: 30px;
}

.template-preview-box {
  margin-top: 30px;
  padding: 20px;
  background: var(--color-bg-tertiary, #1a1a2e);
  border-radius: 12px;
  border: 1px solid var(--color-border-secondary, rgba(255, 255, 255, 0.1));
}

.template-preview-box h4 {
  margin: 0 0 16px 0;
  color: var(--color-primary, #00ff88);
  font-size: 18px;
}

.preview-box {
  height: 200px;
  padding: 16px;
  display: flex;
  flex-direction: column;
  gap: 12px;
  border-radius: 8px;
}

.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 12px;
  padding: 24px;
  border-top: 1px solid var(--color-border-secondary, rgba(255, 255, 255, 0.1));
}

.cancel-btn,
.save-btn {
  padding: 12px 24px;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
}

.cancel-btn {
  background: rgba(255, 255, 255, 0.1);
  color: var(--color-text-primary, white);
  border: 1px solid var(--color-border-secondary, rgba(255, 255, 255, 0.2));
}

.cancel-btn:hover {
  background: rgba(255, 255, 255, 0.15);
}

.save-btn {
  background: linear-gradient(145deg, var(--color-primary, #00ff88), var(--color-primary-dark, #00cc66));
  color: var(--color-bg-primary, #0d0d1a);
}

.save-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px var(--color-primary, #00ff88);
}

/* Responsive */
@media (max-width: 768px) {
  .templates-grid {
    grid-template-columns: 1fr;
  }
  
  .action-section {
    flex-direction: column;
  }
  
  .apply-btn, .preview-btn {
    width: 100%;
    justify-content: center;
  }

  .color-grid {
    grid-template-columns: 1fr;
  }

  .modal-content {
    max-width: 100%;
    margin: 10px;
  }
}
</style>

