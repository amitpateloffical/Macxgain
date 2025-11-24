<template>
  <div class="landing-page-settings">
    <div class="settings-header">
      <h1 class="settings-title">Landing Page Design Templates</h1>
      <p class="settings-subtitle">Choose a design style for your landing page</p>
    </div>

    <div class="templates-grid">
      <div
        v-for="(template, templateId) in templates"
        :key="templateId"
        class="template-card"
        :class="{ active: selectedTemplate === templateId }"
        @click="selectTemplate(templateId)"
      >
        <div class="template-preview-wrapper">
          <div class="template-preview" :class="`preview-${template.style}`">
            <div class="preview-header">
              <div class="preview-logo">LG</div>
              <div class="preview-nav">
                <span></span>
                <span></span>
                <span></span>
              </div>
            </div>
            <div class="preview-hero">
              <div class="preview-title-line"></div>
              <div class="preview-title-line short"></div>
              <div class="preview-buttons">
                <div class="preview-btn primary"></div>
                <div class="preview-btn outline"></div>
              </div>
            </div>
            <div class="preview-content">
              <div class="preview-card"></div>
              <div class="preview-card"></div>
            </div>
          </div>
        </div>
        <div class="template-info">
          <h3 class="template-name">{{ template.name }}</h3>
          <p class="template-desc">{{ template.description }}</p>
          <div class="template-features">
            <span v-for="feature in template.features" :key="feature" class="feature-tag">
              {{ feature }}
            </span>
          </div>
        </div>
      </div>
    </div>

    <div class="settings-actions">
      <button class="btn btn-primary" @click="applyTemplate" :disabled="isApplying">
        <span v-if="!isApplying">Apply Template</span>
        <span v-else>Applying...</span>
      </button>
      <button class="btn btn-secondary" @click="previewTemplate" :disabled="isApplying">
        Preview
      </button>
    </div>

    <div v-if="showPreview" class="preview-notice">
      <p>Preview mode active. Changes will be applied when you click "Apply Template".</p>
    </div>
  </div>
</template>

<script>
import { landingPageTemplates, getCurrentLandingTemplate, saveLandingTemplate, getLandingTemplate } from '@/config/landingPageTemplates';

export default {
  name: 'LandingPageSettings',
  data() {
    return {
      templates: landingPageTemplates,
      selectedTemplate: getCurrentLandingTemplate(),
      isApplying: false,
      showPreview: false
    };
  },
  mounted() {
    // Listen for template updates
    window.addEventListener('landingTemplateUpdated', this.handleTemplateUpdate);
  },
  beforeUnmount() {
    window.removeEventListener('landingTemplateUpdated', this.handleTemplateUpdate);
  },
  methods: {
    selectTemplate(templateId) {
      this.selectedTemplate = templateId;
      this.showPreview = false;
    },
    previewTemplate() {
      if (this.selectedTemplate === getCurrentLandingTemplate()) {
        alert('This template is already active.');
        return;
      }
      
      // Apply preview by adding class to body temporarily
      const template = getLandingTemplate(this.selectedTemplate);
      document.body.classList.add(`landing-template-${template.style}`);
      this.showPreview = true;
      
      // Scroll to top to see changes
      window.scrollTo({ top: 0, behavior: 'smooth' });
    },
    applyTemplate() {
      if (this.isApplying) return;
      
      this.isApplying = true;
      
      try {
        // Save template
        const success = saveLandingTemplate(this.selectedTemplate);
        
        if (success) {
          // Remove preview class if exists
          document.body.classList.remove(...Object.values(this.templates).map(t => `landing-template-${t.style}`));
          
          // Show success message
          alert('Landing page template applied successfully! The page will reload to show changes.');
          
          // Reload page to apply template
          setTimeout(() => {
            window.location.reload();
          }, 1000);
        } else {
          alert('Failed to save template. Please try again.');
          this.isApplying = false;
        }
      } catch (error) {
        console.error('Error applying template:', error);
        alert('An error occurred while applying the template.');
        this.isApplying = false;
      }
    },
    handleTemplateUpdate(event) {
      this.selectedTemplate = event.detail.templateId || getCurrentLandingTemplate();
    }
  }
};
</script>

<style scoped>
.landing-page-settings {
  padding: 2rem;
  max-width: 1400px;
  margin: 0 auto;
}

.settings-header {
  margin-bottom: 2rem;
}

.settings-title {
  font-size: 2rem;
  font-weight: 700;
  color: var(--color-text-primary, #ffffff);
  margin-bottom: 0.5rem;
}

.settings-subtitle {
  font-size: 1.1rem;
  color: var(--color-text-secondary, rgba(255, 255, 255, 0.7));
}

.templates-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
  gap: 2rem;
  margin-bottom: 3rem;
}

.template-card {
  background: var(--color-bg-secondary, #1a1a2e);
  border: 2px solid var(--color-border-secondary, rgba(255, 255, 255, 0.1));
  border-radius: 12px;
  overflow: hidden;
  cursor: pointer;
  transition: all 0.3s ease;
}

.template-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
  border-color: var(--color-primary, #FFD700);
}

.template-card.active {
  border-color: var(--color-primary, #FFD700);
  box-shadow: 0 0 20px rgba(var(--color-primary-rgb, 255, 215, 0), 0.3);
}

.template-preview-wrapper {
  height: 200px;
  background: var(--color-bg-primary, #0a0a1a);
  padding: 1rem;
  overflow: hidden;
}

.template-preview {
  width: 100%;
  height: 100%;
  border-radius: 8px;
  position: relative;
  overflow: hidden;
}

/* Preview Styles for Each Template - Layout Differences */
.preview-groww {
  background: var(--color-bg-primary);
}

.preview-groww .preview-header {
  background: var(--color-bg-secondary);
  border-bottom: 1px solid var(--color-border-secondary);
}

.preview-groww .preview-content {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 0.5rem;
}

.preview-upstox {
  background: var(--color-bg-primary);
}

.preview-upstox .preview-header {
  background: var(--color-bg-primary);
  border-bottom: 2px solid var(--color-primary);
}

.preview-upstox .preview-content {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.preview-upstox .preview-card {
  border-left: 4px solid var(--color-primary);
}

.preview-zerodha {
  background: var(--color-bg-primary);
}

.preview-zerodha .preview-header {
  background: var(--color-bg-primary);
  border-bottom: 3px solid var(--color-primary);
}

.preview-zerodha .preview-hero {
  text-align: center;
}

.preview-zerodha .preview-title-line {
  margin: 0 auto;
}

.preview-zerodha .preview-content {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  max-width: 60%;
  margin: 0 auto;
}

.preview-zerodha .preview-card {
  border-top: 3px solid var(--color-primary);
}

.preview-paytm {
  background: var(--color-bg-primary);
  position: relative;
}

.preview-paytm::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: radial-gradient(circle at 30% 50%, rgba(var(--color-primary-rgb, 255, 215, 0), 0.1) 0%, transparent 50%);
}

.preview-paytm .preview-header {
  background: var(--color-bg-primary);
  border: none;
}

.preview-paytm .preview-content {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 0.5rem;
}

.preview-paytm .preview-card {
  border-radius: 20px;
}

.preview-angelone {
  background: var(--color-bg-primary);
}

.preview-angelone .preview-header {
  background: var(--color-bg-primary);
  border-bottom: 4px solid var(--color-primary);
}

.preview-angelone .preview-title-line {
  text-transform: uppercase;
  font-weight: 900;
}

.preview-angelone .preview-content {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.preview-angelone .preview-card {
  border-left: 6px solid var(--color-primary);
}

.preview-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.5rem 1rem;
  background: rgba(10, 10, 26, 0.95);
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.preview-logo {
  width: 30px;
  height: 30px;
  background: var(--color-primary, #FFD700);
  border-radius: 4px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #000;
  font-weight: bold;
  font-size: 0.7rem;
}

.preview-nav {
  display: flex;
  gap: 0.5rem;
}

.preview-nav span {
  width: 4px;
  height: 4px;
  background: var(--color-text-primary, white);
  border-radius: 50%;
}

.preview-hero {
  padding: 1.5rem 1rem;
}

.preview-title-line {
  height: 8px;
  background: var(--color-primary, #FFD700);
  border-radius: 4px;
  margin-bottom: 0.5rem;
  width: 80%;
}

.preview-title-line.short {
  width: 60%;
  height: 6px;
  opacity: 0.7;
}

.preview-buttons {
  display: flex;
  gap: 0.5rem;
  margin-top: 1rem;
}

.preview-btn {
  height: 20px;
  border-radius: 4px;
}

.preview-btn.primary {
  width: 80px;
  background: var(--color-primary, #FFD700);
}

.preview-btn.outline {
  width: 60px;
  background: transparent;
  border: 1px solid var(--color-border-secondary, rgba(255, 255, 255, 0.3));
}

.preview-content {
  display: flex;
  gap: 0.5rem;
  padding: 0 1rem 1rem;
}

.preview-card {
  flex: 1;
  height: 40px;
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 4px;
}

.template-info {
  padding: 1.5rem;
}

.template-name {
  font-size: 1.25rem;
  font-weight: 600;
  color: var(--color-text-primary, #ffffff);
  margin-bottom: 0.5rem;
}

.template-desc {
  font-size: 0.95rem;
  color: var(--color-text-secondary, rgba(255, 255, 255, 0.7));
  margin-bottom: 1rem;
  line-height: 1.5;
}

.template-features {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
}

.feature-tag {
  font-size: 0.75rem;
  padding: 0.25rem 0.5rem;
  background: rgba(var(--color-primary-rgb, 255, 215, 0), 0.1);
  color: var(--color-primary, #FFD700);
  border-radius: 4px;
  border: 1px solid rgba(var(--color-primary-rgb, 255, 215, 0), 0.2);
}

.settings-actions {
  display: flex;
  gap: 1rem;
  justify-content: center;
  margin-top: 2rem;
}

.btn {
  padding: 0.875rem 2rem;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  border: none;
}

.btn-primary {
  background: linear-gradient(135deg, var(--color-primary, #FFD700), var(--color-primary-dark, #DAA520));
  color: var(--color-bg-primary, #0a0a1a);
}

.btn-primary:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(var(--color-primary-rgb, 255, 215, 0), 0.3);
}

.btn-primary:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.btn-secondary {
  background: transparent;
  color: var(--color-primary, #FFD700);
  border: 2px solid var(--color-primary, #FFD700);
}

.btn-secondary:hover:not(:disabled) {
  background: rgba(var(--color-primary-rgb, 255, 215, 0), 0.1);
}

.preview-notice {
  margin-top: 1.5rem;
  padding: 1rem;
  background: rgba(var(--color-primary-rgb, 255, 215, 0), 0.1);
  border: 1px solid rgba(var(--color-primary-rgb, 255, 215, 0), 0.2);
  border-radius: 8px;
  text-align: center;
  color: var(--color-primary, #FFD700);
}

@media (max-width: 768px) {
  .templates-grid {
    grid-template-columns: 1fr;
  }
  
  .settings-actions {
    flex-direction: column;
  }
  
  .btn {
    width: 100%;
  }
}
</style>

