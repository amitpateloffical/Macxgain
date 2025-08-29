<template>
    <div class="profile-tabs-container">
      <div class="tabs-wrapper">
        <div class="tabs-header">
          <b-tabs pills content-class="mt-3" nav-class="modern-nav-tabs">
            <b-tab title="👤 General" active>
              <GeneralTab :userId="userId" />
            </b-tab>
            <b-tab title="🔒 Change Password">
              <ChangePasswordTab :userId="userId" />
            </b-tab>
          </b-tabs>
          
          <!-- Logout Button -->
          <div class="logout-section">
            <button @click="logout" class="logout-btn">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                <path d="M17 7l-1.41 1.41L18.17 11H8v2h10.17l-2.58 2.59L17 17l5-5zM4 5h8V3H4c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h8v-2H4V5z"/>
              </svg>
              <span>Logout</span>
            </button>
          </div>
        </div>
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
    },
    methods: {
      logout() {
        // Show confirmation dialog
        this.$bvModal.msgBoxConfirm('Are you sure you want to logout?', {
          title: 'Confirm Logout',
          size: 'sm',
          buttonSize: 'sm',
          okVariant: 'danger',
          okTitle: 'Yes, Logout',
          cancelTitle: 'Cancel',
          footerClass: 'p-2',
          hideHeaderClose: false,
          centered: true
        })
        .then(value => {
          if (value) {
            // User confirmed logout
            this.performLogout();
          }
        })
        .catch(err => {
          // User cancelled or closed modal
          console.log('Logout cancelled');
        });
      },

      performLogout() {
        // Clear local storage
        localStorage.removeItem("token");
        localStorage.removeItem("user");
        localStorage.removeItem("auth_token");
        
        // Clear session storage
        sessionStorage.clear();
        
        // Redirect to login page
        this.$router.push("/login");
        
        // Show success message
        this.$toast.success("Logged out successfully!");
        
        // Optional: Call logout API
        this.callLogoutAPI();
      },

      callLogoutAPI() {
        // Optional API call to invalidate token on server
        if (window.axios) {
          window.axios.post("/api/logout")
            .catch(error => {
              console.log('Logout API call failed:', error);
            });
        }
      }
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
  
  /* Tabs Header Layout */
  .tabs-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 20px;
    margin-bottom: 20px;
  }
  
  /* Modern Tab Navigation */
  .modern-nav-tabs {
    justify-content: center !important;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1) !important;
    margin-bottom: 0 !important;
    padding-bottom: 20px;
    flex: 1;
  }
  
  /* Logout Section */
  .logout-section {
    display: flex;
    align-items: flex-start;
    padding-top: 8px;
  }
  
  .logout-btn {
    display: flex;
    align-items: center;
    gap: 8px;
    background: linear-gradient(135deg, #ff4757, #ff3742) !important;
    border: none;
    color: white;
    font-weight: 600;
    padding: 12px 20px;
    border-radius: 12px;
    cursor: pointer;
    transition: all 0.3s ease;
    font-size: 14px;
    white-space: nowrap;
  }
  
  .logout-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(255, 71, 87, 0.4);
    background: linear-gradient(135deg, #ff3742, #ff2d3a) !important;
  }
  
  .logout-btn:active {
    transform: translateY(0);
  }
  
  .logout-btn svg {
    transition: transform 0.3s ease;
  }
  
  .logout-btn:hover svg {
    transform: translateX(2px);
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
    
    .tabs-header {
      flex-direction: column;
      align-items: center;
      gap: 15px;
    }
    
    .modern-nav-tabs {
      flex-direction: column !important;
      align-items: center !important;
      width: 100%;
    }
    
    .logout-section {
      width: 100%;
      justify-content: center;
      padding-top: 0;
    }
    
    .logout-btn {
      width: 100%;
      max-width: 300px;
      justify-content: center;
      padding: 14px 20px;
      font-size: 16px;
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
    
    .logout-btn {
      max-width: 100%;
      padding: 12px 16px;
      font-size: 15px;
      border-radius: 10px;
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
    
    .logout-btn {
      padding: 10px 14px;
      font-size: 14px;
    }
    
    :deep(.nav-pills .nav-link) {
      padding: 10px 14px !important;
      font-size: 14px !important;
    }
  }
  
  /* Touch Device Optimizations */
  @media (hover: none) and (pointer: coarse) {
    .logout-btn {
      min-height: 44px !important;
      -webkit-tap-highlight-color: rgba(255, 71, 87, 0.2);
    }
    
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
  