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
    path: "/master/ticket-type",
    name: "tickettype",
    component: () => import("../views/Master/TicketType.vue"),
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
    path: '/viewtickettype/:id',
    name: 'ViewTicketType',
    meta: { requiresAuth: true },
    // component: () => import("../views/Master/ViewTicketeType.vue"),
  },
  {
    path: "/master/tag",
    name: "tag",
    component: () => import("../views/Master/Tag.vue"),
  },


  {
    path: '/viewtag/:id',
    name: 'ViewTag',
    meta: { requiresAuth: true },
    component: () => import("../views/Master/ViewTag.vue"),
  },
  // {
  //   path: "/dashboard",
  //   name: "dashboard",
  //   meta: { requiresAuth: true },
  //   component: () => import("../views/Dashboard.vue"),
  // },
  {
    path: "/admin/dashboard",
    name: "admin-dashboard",
    meta: { requiresAuth: true },
    component: () => import("../views/AdminSide/Dashboard/Dashboard.vue"),
  },
   {
    path: "/user/dashboard",
    name: "user-ashboard",
    meta: { requiresAuth: true },
    component: () => import("../views/AdminSide/Dashboard/UserDashboard.vue"),
  },
  {
    path: "/admin/product",
    name: "admin-product",
    meta: { requiresAuth: true },
    component: () => import("../views/AdminSide/Product/ListProduct.vue"),
  },
  {
    path: "/admin/add/product",
    name: "admin-add-product",
    meta: { requiresAuth: true },
    component: () => import("../views/AdminSide/Product/AddProduct.vue"),
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
        path: "/withdrawal-request",
        name: "withdrawal_request",
        meta: { requiresAuth: true },
        component: () => import("../views/AdminSide/WithdrawalRequest.vue"),
      },

      {
        path: "/analytics",
        name: "analytics",
        meta: { requiresAuth: true },
        component: () => import("../views/AdminSide/Dashboard/Dashboard.vue"), // Temporary - replace with actual component
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
    path: "/ticket/add/:customerid",
    name: "addTicket",
    meta: { requiresAuth: true },
    // component: () => import("../views/ticket/AddTicket.vue"),
  },
  {
    path: "/ticket/edit/:id",
    name: 'ticket_edit',
    meta: { requiresAuth: true },
    // component: () => import("../views/ticket/EditTicket.vue"),

  },
  
  {
    path: "/customers",
    name: "customers",
    meta: { requiresAuth: true },
    component: () => import("../views/customer/Customers.vue"),
  },
  {
    path: "/customers/view-customer-details/",
    name: "view-customer-details",
    meta: { requiresAuth: true },
    // component: () => import("../views/customer/ViewCustomerDetails.vue"),
  },
  {
    path: "/customer/add",
    name: "addCustomer",
    meta: { requiresAuth: true },
    component: () => import("../views/customer/AddCustomer.vue"),
  },
  {
    path: "/ticket/view-ticket-details/",
    name: "view-ticket-details",
    meta: { requiresAuth: true },
    // component: () => import("../views/ticket/ViewTicketDetails.vue"),
  },
  {
    path: "/view-ticket/:id",
    name: "view-ticket",
    meta: { requiresAuth: true },
    // component: () => import("../views/ticket/ViewTicket.vue"),
  },
  {
    path: "/preview-customer/:id",
    name: "preview-customer",
    meta: { requiresAuth: true },
    component: () => import("../views/customer/previewcustomer.vue"),
  },
  {
    path: "/view-customer/:id",
    name: "view-customer",
    meta: { requiresAuth: true },
    component: () => import("../views/customer/viewcustomer.vue"),
  },
  {
    path: "/edit-customer/:id",
    name: "edit-customer",
    meta: { requiresAuth: true },
    component: () => import("../views/customer/EditCustomer.vue"),
  },
  {
    path: "/outlook",
    name: "outlook",
    meta: { requiresAuth: true },
    component: () => import("../views/outlook/outlook.vue"),
  },
  {
    path: "/user-management",
    name: "user_management",
    meta: { requiresAuth: true },
    component: () => import("../views/AdminSide/UserManagement.vue"),
  },
  {
    path: "/register-request",
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
  
  console.log('🚨 Router Guard Debug:');
  console.log('🚨 To route:', to.path);
  console.log('🚨 Requires auth:', to.matched.some(record => record.meta.requiresAuth));
  console.log('🚨 Token exists:', !!token);
  console.log('🚨 Token value:', token);

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
