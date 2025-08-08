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