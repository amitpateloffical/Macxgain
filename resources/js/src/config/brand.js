/**
 * Brand Configuration
 * Change these values to rebrand the application
 * Settings can be updated from Admin Dashboard -> Brand Settings
 */

// Default brand configuration
const defaultBrandConfig = {
  // Company Information
  companyName: 'ProfitMaxo',
  tagline: 'Maximize Your Trading Profits',
  welcomeText: 'Welcome Back',
  subtitle: 'Sign in to your account to continue',
  
  // Logo Paths
  logoPath: '../assest/img/logo.png',           // Login/Register pages
  logoPathPublic: '/logo.png',                  // Public pages
  logoPathFrontend: '../FrontEndSide/logo.png', // Frontend/Header pages (admin dashboard)
  logoPathHeader: '../FrontEndSide/logo.png',   // Header component (same as frontend)
  
  // Page Titles
  pageTitle: 'ProfitMaxo - Trading with AI and Gain Profit',
  loginTitle: 'Login - ProfitMaxo',
  registerTitle: 'Register - ProfitMaxo',
  
  // Contact Information
  email: 'info@profitmaxo.com',
  website: 'https://profitmaxo.com',
  
  // Copyright
  copyrightYear: '2025',
  copyrightText: 'ProfitMaxo. All Rights Reserved.'
};

// Load saved settings from localStorage or use defaults
const loadBrandConfig = () => {
  try {
    const saved = localStorage.getItem('brandSettings');
    if (saved) {
      const parsed = JSON.parse(saved);
      return { ...defaultBrandConfig, ...parsed };
    }
  } catch (e) {
    console.warn('Failed to load brand settings from localStorage:', e);
  }
  return { ...defaultBrandConfig };
};

// Create a simple config object that always reads from localStorage
let _brandConfigCache = null;
let _cacheTimestamp = 0;
const CACHE_DURATION = 100; // Cache for 100ms to avoid excessive reads

// Function to get brand config (always fresh from localStorage)
export const getBrandConfig = () => {
  const now = Date.now();
  // Use cache if recent, otherwise reload
  if (_brandConfigCache && (now - _cacheTimestamp) < CACHE_DURATION) {
    return _brandConfigCache;
  }
  
  const config = loadBrandConfig();
  _brandConfigCache = config;
  _cacheTimestamp = now;
  return config;
};

// Create a proxy that always returns fresh data
export const brandConfig = new Proxy({}, {
  get(target, prop) {
    return getBrandConfig()[prop];
  },
  ownKeys(target) {
    return Object.keys(getBrandConfig());
  },
  has(target, prop) {
    return prop in getBrandConfig();
  }
});

// Function to update brand config (for use in settings page)
export const updateBrandConfig = (newConfig) => {
  // Save to localStorage
  localStorage.setItem('brandSettings', JSON.stringify(newConfig));
  // Clear cache to force reload
  _brandConfigCache = null;
  _cacheTimestamp = 0;
  // Trigger update event
  if (typeof window !== 'undefined') {
    window.dispatchEvent(new CustomEvent('brandConfigUpdated', { detail: newConfig }));
  }
};

// Function to reload brand config (call this after saving)
export const reloadBrandConfig = () => {
  _brandConfigCache = null;
  _cacheTimestamp = 0;
  if (typeof window !== 'undefined') {
    window.dispatchEvent(new CustomEvent('brandConfigUpdated'));
  }
};

// Listen for storage events (in case settings are changed in another tab)
if (typeof window !== 'undefined') {
  window.addEventListener('storage', (e) => {
    if (e.key === 'brandSettings') {
      _brandConfigCache = null;
      _cacheTimestamp = 0;
      window.dispatchEvent(new CustomEvent('brandConfigUpdated'));
    }
  });
}

