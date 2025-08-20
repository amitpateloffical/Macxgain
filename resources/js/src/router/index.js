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
    meta: { requiresAuth: true },
    component: () => import("../views/AdminSide/Dashboard/Dashboard.vue"),
  },
  {
    path: "/admin/ai-trading",
    name: "admin-ai-trading",
    meta: { requiresAuth: true },
    component: () => import("../views/AdminSide/AITrading.vue"),
  },
   {
    path: "/user/dashboard",
    name: "user-ashboard",
    meta: { requiresAuth: true },
    component: () => import("../views/AdminSide/Dashboard/UserDashboard.vue"),
  },

  
  {
    path: "/profile",
    name: "profile",
    meta: { requiresAuth:true},
    component: () => import("../views/profile/UserProfileTabs.vue"),
  },
    {
    path: "/AddMoney",
    name: "add_money",
    meta: { requiresAuth:true},
    component: () => import("../views/AddMoney/AddMoney.vue"),
  },

      {
    path: "/MoneyRequest",
    name: "money_request",
    meta: { requiresAuth:true},
    component: () => import("../views/AddMoney/MoneyRequest.vue"),
  },
   {
    path: "/Withdrawal/Request",
    name: "withdrawal_request_list",
    meta: { requiresAuth:true},
    component: () => import("../views/AddMoney/WithdrawalRequest.vue"),
  },

      {
        path: "/admin/withdrawal-request",
        name: "withdrawal_request",
        meta: { requiresAuth: true },
        component: () => import("../views/AdminSide/WithdrawalRequest.vue"),
      },
      {
        path: "/admin/money-request",
        name: "admin_money_request",
        meta: { requiresAuth: true },
        component: () => import("../views/AddMoney/MoneyRequest.vue"),
      },

          {
        path: "/admin/analytics",
        name: "analytics",
        meta: { requiresAuth: true },
        component: () => import("../views/AdminSide/Analytics.vue"),
      },
  {
    path: "/email-logs",
    name: "email-logs",
    meta: { requiresAuth:true},
    component: () => import("../views/logs/emailLog.vue"),
  },
  {
    path: "/login-logs",
    name: "login-logs",
    meta: { requiresAuth:true},
    component: () => import("../views/logs/LoginLog.vue"),
  },
  {
    path: "/activity_log",
    name: "activity_log",
    meta: { requiresAuth: true },
    component: () => import("../views/activityLog/ActivityLog.vue"),
  },

  {
    path: "/admin/user-management",
    name: "user_management",
    meta: { requiresAuth: true },
    component: () => import("../views/AdminSide/UserManagement.vue"),
  },  {
    path: "/admin/register-request",
    name: "register_request",
    meta: { requiresAuth: true },
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
  


  if (to.matched.some(record => record.meta.requiresAuth)) {
    if (!token) {
      console.log('🚨 No token, redirecting to LandingPage');
      if (to.name !== 'LandingPage') { // Prevent infinite loop
        next({ name: 'LandingPage' }); 
      } else {
        next(); 
      }
    } else {
      console.log('🚨 Token found, proceeding to route');
      next();
    }
  } else {
    console.log('🚨 No auth required, proceeding to route');
    next();
  }
});

export default router;
