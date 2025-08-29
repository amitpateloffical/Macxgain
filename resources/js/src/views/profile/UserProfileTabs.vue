<template>
    <div class="profile-tabs-container">
      <div class="tabs-wrapper">
        <b-tabs pills content-class="mt-3" nav-class="modern-nav-tabs">
          <b-tab title="👤 General" active>
            <GeneralTab :userId="userId" />
          </b-tab>
          <b-tab title="🔒 Change Password">
            <ChangePasswordTab :userId="userId" />
          </b-tab>
        </b-tabs>
      </div>
    </div>
  </template>
  
  <script>
  import GeneralTab from './GeneralTab.vue'; 
  import ChangePasswordTab from './ChangePasswordTab.vue'; 
  
  export default {
    props: {
      userId: {
        type: String,
        required: true
      }
    },
    components: {
      GeneralTab,
      ChangePasswordTab
    }
  };
  </script>
  
  <style scoped>
  /* Modern Profile Tabs Container */
  .profile-tabs-container {
    background: linear-gradient(135deg, #0d0d1a 0%, #1a1a2e 100%);
    min-height: 100vh;
    padding: 0;
  }
  
  .tabs-wrapper {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
  }
  
  /* Modern Tab Navigation */
  .modern-nav-tabs {
    justify-content: center !important;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1) !important;
    margin-bottom: 0 !important;
    padding-bottom: 20px;
  }
  
  /* Tab Pills Styling */
  :deep(.nav-pills .nav-link) {
    background: rgba(255, 255, 255, 0.1) !important;
    border: 1px solid rgba(255, 255, 255, 0.2) !important;
    color: #9ca3af !important;
    font-weight: 600 !important;
    padding: 12px 24px !important;
    margin: 0 8px !important;
    border-radius: 12px !important;
    transition: all 0.3s ease !important;
  }
  
  :deep(.nav-pills .nav-link:hover) {
    background: rgba(0, 255, 128, 0.1) !important;
    border-color: rgba(0, 255, 128, 0.3) !important;
    color: #00ff80 !important;
    transform: translateY(-2px);
  }
  
  :deep(.nav-pills .nav-link.active) {
    background: linear-gradient(135deg, #00ff80, #00cc66) !important;
    border-color: transparent !important;
    color: #0d0d1a !important;
    box-shadow: 0 4px 15px rgba(0, 255, 128, 0.3) !important;
  }
  
  /* Tab Content */
  :deep(.tab-content) {
    background: transparent !important;
    border: none !important;
    padding: 0 !important;
    margin-top: 0 !important;
  }
  
  /* Enhanced Responsive Design */
  
  /* Large Tablets and Small Desktops */
  @media (max-width: 1024px) {
    .tabs-wrapper {
      padding: 18px;
    }
  }
  
  /* Tablets */
  @media (max-width: 768px) {
    .tabs-wrapper {
      padding: 15px;
    }
    
    .modern-nav-tabs {
      flex-direction: column !important;
      align-items: center !important;
    }
    
    :deep(.nav-pills .nav-link) {
      width: 100% !important;
      max-width: 300px !important;
      margin: 5px 0 !important;
      text-align: center !important;
      padding: 14px 20px !important;
      font-size: 16px !important;
    }
  }
  
  /* Mobile Phones */
  @media (max-width: 480px) {
    .tabs-wrapper {
      padding: 10px;
    }
    
    :deep(.nav-pills .nav-link) {
      max-width: 100% !important;
      padding: 12px 16px !important;
      font-size: 15px !important;
      border-radius: 10px !important;
    }
  }
  
  /* Extra Small Phones */
  @media (max-width: 360px) {
    .tabs-wrapper {
      padding: 8px;
    }
    
    :deep(.nav-pills .nav-link) {
      padding: 10px 14px !important;
      font-size: 14px !important;
    }
  }
  
  /* Touch Device Optimizations */
  @media (hover: none) and (pointer: coarse) {
    :deep(.nav-pills .nav-link) {
      min-height: 44px !important;
      display: flex !important;
      align-items: center !important;
      justify-content: center !important;
      -webkit-tap-highlight-color: rgba(0, 255, 128, 0.2);
    }
  }
  
  /* High DPI Displays */
  @media (-webkit-min-device-pixel-ratio: 2), (min-resolution: 192dpi) {
    :deep(.nav-pills .nav-link) {
      -webkit-font-smoothing: antialiased;
      -moz-osx-font-smoothing: grayscale;
    }
  }
  </style>
  