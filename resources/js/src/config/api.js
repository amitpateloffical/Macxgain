// API Configuration
// This file provides dynamic API URL based on environment

/**
 * Get the API base URL
 * Automatically detects environment and uses appropriate URL
 */
export const getApiBaseUrl = () => {
  // Use current domain's origin + /api
  return `${window.location.origin}/api`;
};

/**
 * Get full API URL for specific endpoint
 */
export const getApiUrl = (endpoint) => {
  const baseUrl = getApiBaseUrl();
  const cleanEndpoint = endpoint.startsWith('/') ? endpoint.slice(1) : endpoint;
  return `${baseUrl}/${cleanEndpoint}`;
};

/**
 * Default API configuration
 */
export const API_CONFIG = {
  baseURL: getApiBaseUrl(),
  timeout: 30000,
  headers: {
    'Accept': 'application/json',
    'Content-Type': 'application/json'
  }
};

// Export for backward compatibility
export const API_BASE = getApiBaseUrl();
export default API_CONFIG;
