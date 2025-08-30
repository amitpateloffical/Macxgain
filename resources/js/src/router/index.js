import { createRouter, createWebHistory } from "vue-router";

const routes = [
  {
    path: "/",
    name: "LandingPage",
    meta: { layout: 'full' },
    component: () => import("../views/FrontEndSide/Home/LandingPage.vue"),
  },
  {
    path: "/login",
    name: "login",
    meta: { layout: 'full' },
    component: () => import("../views/authentication/Login.vue"),
  },
  {
    path: "/register",
    name: "register",
    meta: { layout: 'full' },
    component: () => import("../views/authentication/Register.vue"),
  },

  {
    path: '/markets', // Landing Page Route (hidden redirect)    name: "LandingPage",
    name: "markets",
    meta: { layout: 'full' },
    component: () => import("../views/FrontEndSide/Home/markets.vue"),
  },
 
  {
      path: "/forgot-password",
      name: "forgot-password",
      meta: { layout: 'full' },
      component: () => import("../views/authentication/ForgotPassword.vue"),
    },
    { 
      path: "/resetPassword",
      name: "resetPassword",
      meta: { layout: 'full' },
      component: () => import("../views/authentication/ResetPassword.vue"),
    },

 


 
  {
    path: "/admin/dashboard",
    name: "admin-dashboard",
    meta: { requiresAuth: true, requiresAdmin: true },
    component: () => import("../views/AdminSide/Dashboard/Dashboard.vue"),
  },
  {
    path: "/admin/ai-trading",
    name: "admin-ai-trading",
    meta: { requiresAuth: true, requiresAdmin: true },
    component: () => import("../views/AdminSide/AITrading.vue"),
  },
    {
    path: "/admin/payment-collector",
    name: "payment_collector",
    meta: { requiresAuth: true, requiresAdmin: true },
    component: () => import("../views/AdminSide/PaymentCollector.vue"),
  },
  {
    path: "/admin/stock-market",
    name: "stock_market",
    meta: { requiresAuth: true, requiresAdmin: true },
    component: () => import("../views/AdminSide/StockMarket.vue"),
  },
  {
    path: "/user/dashboard",
    name: "user-ashboard",
    meta: { requiresAuth: true, requiresUser: true },
    component: () => import("../views/AdminSide/Dashboard/UserDashboard.vue"),
  },
  {
    path: "/user/watchlist",
    name: "user-watchlist",
    meta: { requiresAuth: true, requiresUser: true },
    component: () => import("../views/UserWatchlist/UserWatchlist.vue"),
  },
  {
    path: "/user/orders",
    name: "user-orders",
    meta: { requiresAuth: true, requiresUser: true },
    component: () => import("../views/UserOrders/UserOrders.vue"),
  },

  
  {
    path: "/profile",
    name: "profile",
    meta: { requiresAuth: true, requiresUser: true },
    component: () => import("../views/profile/UserProfileTabs.vue"),
  },
    {
    path: "/AddMoney",
    name: "add_money",
    meta: { requiresAuth: true, requiresUser: true },
    component: () => import("../views/AddMoney/AddMoney.vue"),
  },

      {
    path: "/MoneyRequest",
    name: "money_request",
    meta: { requiresAuth: true, requiresUser: true },
    component: () => import("../views/AddMoney/MoneyRequest.vue"),
  },
   {
    path: "/Withdrawal/Request",
    name: "withdrawal_request_list",
    meta: { requiresAuth: true, requiresUser: true },
    component: () => import("../views/AddMoney/WithdrawalRequest.vue"),
  },

      {
        path: "/admin/withdrawal-request",
        name: "withdrawal_request",
        meta: { requiresAuth: true, requiresAdmin: true },
        component: () => import("../views/AdminSide/WithdrawalRequest.vue"),
      },
      {
        path: "/admin/money-request",
        name: "admin_money_request",
        meta: { requiresAuth: true, requiresAdmin: true },
        component: () => import("../views/AddMoney/MoneyRequest.vue"),
      },

          {
        path: "/admin/analytics",
        name: "analytics",
        meta: { requiresAuth: true, requiresAdmin: true },
        component: () => import("../views/AdminSide/Analytics.vue"),
      },
  {
    path: "/email-logs",
    name: "email-logs",
    meta: { requiresAuth: true, requiresUser: true },
    component: () => import("../views/logs/emailLog.vue"),
  },
  {
    path: "/login-logs",
    name: "login-logs",
    meta: { requiresAuth: true, requiresUser: true },
    component: () => import("../views/logs/LoginLog.vue"),
  },
  {
    path: "/activity_log",
    name: "activity_log",
    meta: { requiresAuth: true, requiresUser: true },
    component: () => import("../views/activityLog/ActivityLog.vue"),
  },

  {
    path: "/admin/user-management",
    name: "user_management",
    meta: { requiresAuth: true, requiresAdmin: true },
    component: () => import("../views/AdminSide/UserManagement.vue"),
  },  {
    path: "/admin/register-request",
    name: "register_request",
    meta: { requiresAuth: true, requiresAdmin: true },
    component: () => import("../views/AdminSide/RegisterRequest.vue"),
  },
  
  // Catch-all route - must be last
  { path: '/:pathMatch(.*)*', redirect: '/' }
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});
router.beforeEach((to, from, next) => {
  const token = localStorage.getItem('access_token');
  const userData = localStorage.getItem('userData');

  console.log('🚨 Route Guard - Navigating to:', to.path);
  console.log('🚨 Route Guard - From:', from.path);
  console.log('🚨 Route Guard - Token exists:', !!token);
  console.log('🚨 Route Guard - User data exists:', !!userData);

  // Check if route requires authentication
  if (to.matched.some(record => record.meta.requiresAuth)) {
    if (!token) {
      console.log('🚨 No token, redirecting to LandingPage');
      if (to.name !== 'LandingPage') { // Prevent infinite loop
        next({ name: 'LandingPage' }); 
      } else {
        next(); 
      }
    } else {
      // Token exists, check if user data is valid
      if (!userData) {
        console.log('🚨 Token exists but no user data, clearing session and redirecting');
        localStorage.removeItem('access_token');
        next({ name: 'LandingPage' });
        return;
      }

      try {
        const user = JSON.parse(userData);
        console.log('🚨 Route Guard - User role:', user.is_admin ? 'Admin' : 'User');
        
        // Check if route requires admin privileges
        if (to.matched.some(record => record.meta.requiresAdmin)) {
          if (!user.is_admin) {
            console.log('🚨 Non-admin user trying to access admin route, redirecting to user dashboard');
            next({ name: 'user-ashboard' });
            return;
          }
        }
        
        // Check if route requires user privileges (non-admin only)
        if (to.matched.some(record => record.meta.requiresUser)) {
          if (user.is_admin) {
            console.log('🚨 Admin user trying to access user route, redirecting to admin dashboard');
            next({ name: 'admin-dashboard' });
            return;
          }
        }
        
        // If admin is trying to access login/register pages, redirect to admin dashboard
        if (user.is_admin && (to.path === '/login' || to.path === '/register' || to.path === '/')) {
          console.log('🚨 Admin trying to access public pages, redirecting to admin dashboard');
          next({ name: 'admin-dashboard' });
          return;
        }

        // Prevent admin from being redirected to login when already authenticated
        if (user.is_admin && to.path === '/login') {
          console.log('🚨 Admin already authenticated, preventing redirect to login');
          next({ name: 'admin-dashboard' });
          return;
        }

        console.log('🚨 Token and user data valid, proceeding to route');
        next();
      } catch (error) {
        console.error('🚨 Error parsing user data:', error);
        localStorage.removeItem('access_token');
        localStorage.removeItem('userData');
        next({ name: 'LandingPage' });
      }
    }
  } else {
    // Route doesn't require auth
    console.log('🚨 No auth required, proceeding to route');
    
    // If user is logged in and trying to access public routes, redirect to appropriate dashboard
    if (token && userData) {
      try {
        const user = JSON.parse(userData);
        if (to.path === '/' || to.path === '/login' || to.path === '/register') {
          console.log('🚨 Logged in user accessing public route, redirecting to dashboard');
          if (user.is_admin) {
            next({ name: 'admin-dashboard' });
          } else {
            next({ name: 'user-ashboard' });
          }
          return;
        }
      } catch (error) {
        console.error('🚨 Error parsing user data in public route:', error);
      }
    }
    
    next();
  }
});

export default router;
