# Brand Configuration Guide

## 🎯 Quick Start - Change Logo and Name

To change the logo and company name from **admin login → dashboard** (and throughout the app), simply edit one file:

### File: `resources/js/src/config/brand.js`

```javascript
export const brandConfig = {
  // Company Information
  companyName: 'YOUR_COMPANY_NAME',        // ← Change this (appears in login, header, dashboard)
  tagline: 'YOUR_TAGLINE',                 // ← Change this (appears in login page)
  welcomeText: 'Welcome Back',
  subtitle: 'Sign in to your account to continue',
  
  // Logo Paths
  logoPath: '../assest/img/logo.png',           // Login/Register pages
  logoPathHeader: '../FrontEndSide/logo.png',   // Admin Dashboard Header (← Change this)
  logoPathPublic: '/logo.png',                  // Public pages
  logoPathFrontend: '../FrontEndSide/logo.png', // Frontend pages
  
  // Page Titles
  pageTitle: 'YOUR_COMPANY - Trading Platform',  // ← Change this
  loginTitle: 'Login - YOUR_COMPANY',
  registerTitle: 'Register - YOUR_COMPANY',
  
  // Contact Information
  email: 'info@yourcompany.com',          // ← Change this
  website: 'https://yourcompany.com',     // ← Change this
  
  // Copyright
  copyrightYear: '2025',
  copyrightText: 'YOUR_COMPANY. All Rights Reserved.'
};
```

## 📝 Steps to Rebrand

1. **Replace Logo Images**
   - **Login/Register logo**: Place your logo in `resources/js/src/views/assest/img/logo.png`
   - **Dashboard Header logo**: Place your logo in `resources/js/src/views/FrontEndSide/logo.png`
   - Or update the paths in `brand.js` to point to your logo locations

2. **Update Company Name**
   - Open `resources/js/src/config/brand.js`
   - Change `companyName: 'Macxgain'` to your company name
   - Change `tagline: 'Smart Trading Solutions'` to your tagline
   - This will update: Login page, Register page, Dashboard header, and page titles

3. **Update Contact Info**
   - Change `email` and `website` in the config file

4. **Rebuild the Application**
   ```bash
   npm run build
   # or for development
   npm run dev
   ```

## ✅ What's Already Updated

The following pages/components now use the brand configuration:
- ✅ **Login page** (`/login`) - Logo, company name, tagline
- ✅ **Register page** (`/register`) - Logo, company name, tagline
- ✅ **Admin Dashboard Header** - Logo and company name (visible on all admin pages)
- ✅ **App title** (document title) - Browser tab title

## 📍 Logo File Locations

Your logos should be placed in these locations:
- **Login/Register**: `resources/js/src/views/assest/img/logo.png`
- **Dashboard Header**: `resources/js/src/views/FrontEndSide/logo.png` ⭐ (This is what shows in admin dashboard)
- **Public pages**: `public/logo.png`

Or update the paths in `brand.js` to match your logo locations.

## 🔄 Example: Changing to "MyTradingApp"

```javascript
export const brandConfig = {
  companyName: 'MyTradingApp',
  tagline: 'Professional Trading Platform',
  logoPath: '../assest/img/mytradingapp-logo.png',
  // ... rest of config
};
```

After making changes, rebuild the app and the new branding will appear!

