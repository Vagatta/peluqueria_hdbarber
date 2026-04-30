import { createRouter, createWebHistory, type RouteRecordRaw } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const routes: RouteRecordRaw[] = [
  { path: '/', name: 'home', component: () => import('@/views/Home.vue') },
  { path: '/login', name: 'login', component: () => import('@/views/Login.vue'), meta: { guestOnly: true } },
  { path: '/register', name: 'register', component: () => import('@/views/Register.vue'), meta: { guestOnly: true } },
  { path: '/forgot-password', name: 'forgot-password', component: () => import('@/views/ForgotPassword.vue'), meta: { guestOnly: true } },
  { path: '/forgot-password/sent', name: 'password-link-sent', component: () => import('@/views/PasswordLinkSent.vue'), meta: { guestOnly: true } },
  { path: '/book', name: 'book', component: () => import('@/views/Book.vue'), meta: { auth: true } },
  { path: '/appointments', name: 'appointments', component: () => import('@/views/MyAppointments.vue'), meta: { auth: true } },
  { path: '/profile', name: 'profile', component: () => import('@/views/Profile.vue'), meta: { auth: true } },
  { path: '/payment/success', name: 'payment-success', component: () => import('@/views/PaymentSuccess.vue') },
  { path: '/payment/cancel', name: 'payment-cancel', component: () => import('@/views/PaymentCancel.vue') },

  {
    path: '/admin',
    component: () => import('@/views/admin/AdminLayout.vue'),
    meta: { auth: true, admin: true },
    children: [
      { path: '', name: 'admin-dashboard', component: () => import('@/views/admin/Dashboard.vue') },
      { path: 'calendar', name: 'admin-calendar', component: () => import('@/views/admin/Calendar.vue') },
      { path: 'services', name: 'admin-services', component: () => import('@/views/admin/Services.vue') },
      { path: 'employees', name: 'admin-employees', component: () => import('@/views/admin/Employees.vue') },
      { path: 'clients', name: 'admin-clients', component: () => import('@/views/admin/Clients.vue') },
      { path: 'payments', name: 'admin-payments', component: () => import('@/views/admin/Payments.vue') }
    ]
  },

  { path: '/:pathMatch(.*)*', component: () => import('@/views/NotFound.vue') }
]

const router = createRouter({
  history: createWebHistory(),
  routes,
  scrollBehavior: () => ({ top: 0 })
})

router.beforeEach((to) => {
  const auth = useAuthStore()

  // Si aún no se inicializó (no debería ocurrir, fetchMe corre antes del mount)
  // dejamos pasar y el guard se re-evaluará en la siguiente navegación
  if (!auth.initialized) return true

  if (to.meta.auth && !auth.isAuthenticated) return { name: 'login', query: { redirect: to.fullPath } }
  if (to.meta.guestOnly && auth.isAuthenticated) return { name: 'home' }
  if (to.meta.admin && !auth.isAdmin) return { name: 'home' }
  return true
})

// Capturar errores de navegación
router.onError((err) => {
  console.error('Router error:', err)
})

export default router
