import { createApp, reactive } from "vue";
import App from "./App.vue";
import router from "./router";
import '@fortawesome/fontawesome-free/css/all.min.css';
import BootstrapVue3 from "bootstrap-vue-3";
import "bootstrap/dist/css/bootstrap.css";
import "bootstrap-vue-3/dist/bootstrap-vue-3.css";
import Swal from 'sweetalert2';
import mitt from 'mitt'; 
import axios from "@axios";
import "../src/views/assest/scss/style.scss"
import "../../css/template-themes.css"
import BottomAppBar from "./components/BottomAppBar.vue";
// Initialize template system (must be after styles)
import '@/config/templates';
// Load aggressive overrides last to ensure they take precedence
import "../../css/template-overrides.css";
// Load landing page templates
import "../../css/landing-templates.css";

// Global template sync mechanism
if (typeof window !== 'undefined') {
  // Listen for template changes from other tabs/windows
  if (typeof BroadcastChannel !== 'undefined') {
    const templateChannel = new BroadcastChannel('template-updates');
    templateChannel.addEventListener('message', (event) => {
      if (event.data.action === 'templateChanged') {
        // Just reload page - simpler and faster than async operations
        setTimeout(() => {
          window.location.reload();
        }, 300);
      }
    });
  }
  
  // Listen for templateChanged events - force re-render
  window.addEventListener('templateChanged', (event) => {
    console.log('Template changed event received:', event.detail?.id);
    // Force all Vue components to update by triggering a resize event
    window.dispatchEvent(new Event('resize'));
    // Also trigger a custom event that components can listen to
    window.dispatchEvent(new CustomEvent('forceTemplateUpdate'));
  });
  
  // Periodically check for template updates (every 5 minutes - very infrequent)
  // Only check when page is visible to save resources
  setInterval(() => {
    if (document.hidden) return; // Don't check when tab is hidden
    
    import('@/config/templates').then(({ getCurrentTemplate, applyTemplate, getCurrentTemplateSync }) => {
      // Use Promise.race with timeout to prevent blocking
      Promise.race([
        getCurrentTemplate(),
        new Promise((_, reject) => setTimeout(() => reject(new Error('Timeout')), 2000))
      ]).then((template) => {
        const currentLocal = getCurrentTemplateSync();
        if (template.id !== currentLocal.id) {
          applyTemplate(template.id);
        }
      }).catch(() => {
        // Silently fail
      });
    });
  }, 300000); // Check every 5 minutes (much less frequent)
}




const app = createApp(App);  

const emitter = mitt();
app.config.globalProperties.emitter = emitter;

const state = reactive({
    isLoggedIn: true 
});

const inactivityTime = 900000; 
let inactivityTimeout;

function showInactivityModal() {
    if (state.isLoggedIn) {
        clearTimeout(inactivityTimeout);
        Swal.fire({
            title: 'Session Timeout Warning',
            text: 'You have been inactive for a while. Would you like to stay logged in?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Stay Logged In',
            cancelButtonText: 'Log Out',
        }).then((result) => {
            if (result.isConfirmed) {
                resetInactivityTimeout();
            } else {
                handleLogout();
            }
        });
    }
}

function handleLogout() {
    axios.post("/logout").then(() => {
        localStorage.removeItem('userData');
        localStorage.removeItem('access_token');
        state.isLoggedIn = false;
        clearAllTimeouts();
        app.config.globalProperties.emitter.emit('logout');
        Swal.fire({
            title: 'Logged Out!',
            text: 'Your session has expired. Redirecting to login...',
            icon: 'info',
            timer: 2000,
            showConfirmButton: false
        });
        setTimeout(() => {
            router.push('/login');
        }, 2000);
    }).catch((error) => {
        console.error('Logout error:', error);
        Swal.fire({
            title: 'Logout Failed!',
            text: 'Please try again.',
            icon: 'error'
        });
    });
}

function handleLogin(credentials) {
    axios.post("/login", credentials).then((response) => {
        localStorage.setItem('userData', response.data.user);
        localStorage.setItem('access_token', response.data.token);
        state.isLoggedIn = true;
        app.config.globalProperties.emitter.emit('login');

        Swal.fire({
            title: 'Login Successful!',
            text: 'You have been logged in successfully.',
            icon: 'success',
            timer: 2000,
            showConfirmButton: false
        });

        resetInactivityTimeout();
        router.push('/admin/dashboard'); 
    }).catch((error) => {
        console.error('Login error:', error);
        Swal.fire({
            title: 'Login Failed!',
            text: 'Please check your credentials.',
            icon: 'error'
        });
    });
}

function resetInactivityTimeout() {
    clearTimeout(inactivityTimeout);
    if (state.isLoggedIn) {
        inactivityTimeout = setTimeout(() => {
            showInactivityModal();
        }, inactivityTime);
    }
}

function clearAllTimeouts() {
    clearTimeout(inactivityTimeout);
}

const resetTimer = () => {
    if (state.isLoggedIn) {
        resetInactivityTimeout(); 
    }
};

window.addEventListener('mousemove', resetTimer);
window.addEventListener('keypress', resetTimer);
window.addEventListener('click', resetTimer);

app.config.globalProperties.emitter.on('login', () => {
    state.isLoggedIn = true;
    resetInactivityTimeout();
});

app.config.globalProperties.emitter.on('logout', () => {
    clearAllTimeouts();
});

// Register global components
app.component('BottomAppBar', BottomAppBar);

app.use(router);
app.use(BootstrapVue3);
app.mount("#app");

resetInactivityTimeout();
router.beforeEach((to, from, next) => {
    if (to.path === '/login') {
        clearAllTimeouts();
    }
    next(); 
});

// 🔒 SECURITY FEATURES IMPLEMENTATION
// Get security settings from environment variables
const securityConfig = {
    disableF12: import.meta.env.VITE_DISABLE_F12 === 'true',
    disableRightClick: import.meta.env.VITE_DISABLE_RIGHT_CLICK === 'true',
    disableDevTools: import.meta.env.VITE_DISABLE_DEV_TOOLS === 'true',
    disableCtrlShiftI: import.meta.env.VITE_DISABLE_CTRL_SHIFT_I === 'true',
    disableCtrlU: import.meta.env.VITE_DISABLE_CTRL_U === 'true',
    disableCtrlShiftC: import.meta.env.VITE_DISABLE_CTRL_SHIFT_C === 'true',
    disableSelectText: import.meta.env.VITE_DISABLE_SELECT_TEXT === 'true',
    disableDrag: import.meta.env.VITE_DISABLE_DRAG === 'true',
    showSecurityWarning: import.meta.env.VITE_SHOW_SECURITY_WARNING === 'true',
    securityWarningMessage: import.meta.env.VITE_SECURITY_WARNING_MESSAGE || 'Developer tools are disabled for security reasons'
};

console.log('🔒 Security Config Loaded:', securityConfig);

// Function to show security warning
function showSecurityWarning() {
    if (securityConfig.showSecurityWarning) {
        Swal.fire({
            title: '🚫 Security Warning',
            text: securityConfig.securityWarningMessage,
            icon: 'warning',
            confirmButtonText: 'OK',
            allowOutsideClick: false,
            allowEscapeKey: false
        });
    }
}

// 🚫 DISABLE F12 KEY
if (securityConfig.disableF12) {
    document.addEventListener('keydown', function(e) {
        if (e.key === 'F12') {
            e.preventDefault();
            e.stopPropagation();
            showSecurityWarning();
            console.log('🚫 F12 key blocked!');
            return false;
        }
    });
    console.log('✅ F12 key disabled');
}

// 🚫 DISABLE RIGHT-CLICK
if (securityConfig.disableRightClick) {
    document.addEventListener('contextmenu', function(e) {
        e.preventDefault();
        e.stopPropagation();
        showSecurityWarning();
        console.log('🚫 Right-click blocked!');
        return false;
    });
    console.log('✅ Right-click disabled');
}

// 🚫 DISABLE KEYBOARD SHORTCUTS
if (securityConfig.disableDevTools || securityConfig.disableCtrlShiftI || securityConfig.disableCtrlU || securityConfig.disableCtrlShiftC) {
    document.addEventListener('keydown', function(e) {
        // Ctrl+Shift+I (Developer Tools)
        if (securityConfig.disableCtrlShiftI && e.ctrlKey && e.shiftKey && e.key === 'I') {
            e.preventDefault();
            e.stopPropagation();
            showSecurityWarning();
            console.log('🚫 Ctrl+Shift+I blocked!');
            return false;
        }
        
        // Ctrl+U (View Source)
        if (securityConfig.disableCtrlU && e.ctrlKey && e.key === 'u') {
            e.preventDefault();
            e.stopPropagation();
            showSecurityWarning();
            console.log('🚫 Ctrl+U blocked!');
            return false;
        }
        
        // Ctrl+Shift+C (Inspect Element)
        if (securityConfig.disableCtrlShiftC && e.ctrlKey && e.shiftKey && e.key === 'C') {
            e.preventDefault();
            e.stopPropagation();
            showSecurityWarning();
            console.log('🚫 Ctrl+Shift+C blocked!');
            return false;
        }
        
        // Ctrl+Shift+J (Console)
        if (securityConfig.disableDevTools && e.ctrlKey && e.shiftKey && e.key === 'J') {
            e.preventDefault();
            e.stopPropagation();
            showSecurityWarning();
            console.log('🚫 Ctrl+Shift+J blocked!');
            return false;
        }
        
        // Ctrl+Shift+K (Firefox Console)
        if (securityConfig.disableDevTools && e.ctrlKey && e.shiftKey && e.key === 'K') {
            e.preventDefault();
            e.stopPropagation();
            showSecurityWarning();
            console.log('🚫 Ctrl+Shift+K blocked!');
            return false;
        }
    });
    console.log('✅ Keyboard shortcuts disabled');
}

// 🚫 DISABLE TEXT SELECTION
if (securityConfig.disableSelectText) {
    document.addEventListener('selectstart', function(e) {
        e.preventDefault();
        return false;
    });
    
    document.addEventListener('mousedown', function(e) {
        if (e.target.tagName !== 'INPUT' && e.target.tagName !== 'TEXTAREA') {
            e.preventDefault();
            return false;
        }
    });
    
    // Apply CSS class
    document.body.classList.add('security-disabled-select');
    console.log('✅ Text selection disabled');
}

// 🚫 DISABLE DRAG & DROP
if (securityConfig.disableDrag) {
    document.addEventListener('dragstart', function(e) {
        e.preventDefault();
        return false;
    });
    
    // Apply CSS class
    document.body.classList.add('security-disabled-drag');
    console.log('✅ Drag & drop disabled');
}

// 🔍 DEVELOPER TOOLS DETECTION
if (securityConfig.disableDevTools) {
    let devtools = {
        open: false,
        orientation: null
    };
    
    let threshold = 160;
    
    // Advanced detection function
    function detectDevTools() {
        // Method 1: Window size detection
        const heightDiff = window.outerHeight - window.innerHeight;
        const widthDiff = window.outerWidth - window.innerWidth;
        
        if (heightDiff > threshold || widthDiff > threshold) {
            console.log('🔍 DevTools detected via window size');
            return true;
        }
        
        // Method 2: Performance timing detection
        try {
            const start = performance.now();
            debugger;
            const end = performance.now();
            if (end - start > 100) {
                console.log('🔍 DevTools detected via debugger timing');
                return true;
            }
        } catch(e) {
            console.log('🔍 DevTools detected via debugger exception');
            return true;
        }
        
        // Method 3: Console detection
        let devtools_detected = false;
        const element = new Image();
        Object.defineProperty(element, 'id', {
            get: function() {
                devtools_detected = true;
                return 'detected';
            }
        });
        console.log(element);
        if (devtools_detected) {
            console.log('🔍 DevTools detected via console');
            return true;
        }
        
        return false;
    }
    
    // Handle when dev tools are detected
    function handleDevToolsDetected() {
        if (!devtools.open) {
            devtools.open = true;
            console.log('🚨 SECURITY ALERT: Developer tools detected!');
            
            // Show warning immediately
            showSecurityWarning();
            
            // Create security overlay
            const overlay = document.createElement('div');
            overlay.className = 'security-warning-overlay';
            overlay.style.cssText = `
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0.9);
                color: white;
                display: flex;
                justify-content: center;
                align-items: center;
                z-index: 99999;
                font-family: Arial, sans-serif;
            `;
            
            overlay.innerHTML = `
                <div style="text-align: center; padding: 40px;">
                    <h1 style="color: #ff4444; font-size: 48px; margin-bottom: 20px;">🚫</h1>
                    <h2 style="color: white; margin-bottom: 20px;">Access Restricted</h2>
                    <p style="font-size: 18px; margin-bottom: 10px;">${securityConfig.securityWarningMessage}</p>
                    <p style="font-size: 16px; margin-bottom: 30px;">Close developer tools and refresh the page.</p>
                    <button onclick="window.location.reload()" style="
                        background: #ff4444; 
                        color: white; 
                        border: none; 
                        padding: 15px 30px; 
                        font-size: 16px;
                        border-radius: 5px; 
                        cursor: pointer;
                        margin-right: 10px;
                    ">Reload Page</button>
                    <button onclick="window.location.href='/'" style="
                        background: #666; 
                        color: white; 
                        border: none; 
                        padding: 15px 30px; 
                        font-size: 16px;
                        border-radius: 5px; 
                        cursor: pointer;
                    ">Go Home</button>
                </div>
            `;
            
            document.body.appendChild(overlay);
            
            // Blur page content
            document.querySelectorAll('*:not(.security-warning-overlay)').forEach(el => {
                if (el !== overlay && !overlay.contains(el)) {
                    el.style.filter = 'blur(5px)';
                    el.style.pointerEvents = 'none';
                }
            });
            
            // Auto redirect after 10 seconds
            setTimeout(() => {
                window.location.href = '/';
            }, 10000);
        }
    }
    
    // Initial immediate checks
    setTimeout(() => {
        if (detectDevTools()) {
            handleDevToolsDetected();
        }
    }, 100);
    
    setTimeout(() => {
        if (detectDevTools()) {
            handleDevToolsDetected();
        }
    }, 500);
    
    setTimeout(() => {
        if (detectDevTools()) {
            handleDevToolsDetected();
        }
    }, 1000);
    
    // Regular monitoring
    setInterval(() => {
        if (detectDevTools()) {
            handleDevToolsDetected();
        } else {
            devtools.open = false;
        }
    }, 300);
    
    // Page visibility change detection
    document.addEventListener('visibilitychange', function() {
        if (!document.hidden) {
            setTimeout(() => {
                if (detectDevTools()) {
                    handleDevToolsDetected();
                }
            }, 200);
        }
    });
    
    // Window resize detection
    window.addEventListener('resize', function() {
        setTimeout(() => {
            if (detectDevTools()) {
                handleDevToolsDetected();
            }
        }, 100);
    });
    
    // Console protection
    setInterval(() => {
        console.clear();
        console.log('%c🚫 STOP!', 'color: red; font-size: 50px; font-weight: bold;');
        console.log('%cThis is a browser feature intended for developers.', 'color: red; font-size: 16px;');
        console.log('%cSecurity features are active.', 'color: orange; font-size: 14px;');
    }, 2000);
    
    console.log('✅ Developer tools detection enabled');
}

// Apply security classes immediately
document.addEventListener('DOMContentLoaded', function() {
    if (securityConfig.disableSelectText) {
        document.body.classList.add('security-disabled-select');
    }
    
    if (securityConfig.disableDrag) {
        document.body.classList.add('security-disabled-drag');
    }
});

console.log('🔒 Security system initialized successfully!');