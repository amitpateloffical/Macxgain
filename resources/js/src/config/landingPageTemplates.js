/**
 * Landing Page Template Configurations
 * Each template defines a different design style inspired by popular trading platforms
 */

export const landingPageTemplates = {
  groww: {
    id: 'groww',
    name: 'Groww Style',
    description: 'Clean, modern design inspired by Groww with green accents and card-based layout',
    style: 'groww',
    features: ['Card-based design', 'Green accents', 'Modern typography', 'Clean interface']
  },
  upstox: {
    id: 'upstox',
    name: 'Upstox Style',
    description: 'Professional trading platform design with blue theme and data-focused layout',
    style: 'upstox',
    features: ['Data-focused', 'Blue theme', 'Professional layout', 'Chart integration']
  },
  zerodha: {
    id: 'zerodha',
    name: 'Zerodha Style',
    description: 'Minimalist design with orange accents, clean lines and efficient layout',
    style: 'zerodha',
    features: ['Minimalist design', 'Orange accents', 'Clean lines', 'Efficient layout']
  },
  paytm: {
    id: 'paytm',
    name: 'Paytm Money Style',
    description: 'Vibrant design with blue gradient, modern cards and user-friendly interface',
    style: 'paytm',
    features: ['Blue gradient', 'Modern cards', 'User-friendly', 'Vibrant design']
  },
  angelone: {
    id: 'angelone',
    name: 'Angel One Style',
    description: 'Bold design with red accents, strong typography and professional trading interface',
    style: 'angelone',
    features: ['Red accents', 'Strong typography', 'Professional interface', 'Bold design']
  }
};

/**
 * Get current landing page template from localStorage
 */
export const getCurrentLandingTemplate = () => {
  try {
    const saved = localStorage.getItem('landingPageTemplate');
    return saved || 'classic';
  } catch (error) {
    console.error('Error getting landing page template:', error);
    return 'classic';
  }
};

/**
 * Save landing page template to localStorage
 */
export const saveLandingTemplate = (templateId) => {
  try {
    localStorage.setItem('landingPageTemplate', templateId);
    // Dispatch event for components to react
    window.dispatchEvent(new CustomEvent('landingTemplateUpdated', { detail: { templateId } }));
    return true;
  } catch (error) {
    console.error('Error saving landing page template:', error);
    return false;
  }
};

/**
 * Get template configuration by ID
 */
export const getLandingTemplate = (templateId) => {
  return landingPageTemplates[templateId] || landingPageTemplates.groww;
};

