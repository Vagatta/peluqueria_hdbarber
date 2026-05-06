import { defineStore } from 'pinia'
import { computed, ref } from 'vue'
import { api, setAuthToken, clearAuthToken } from '@/api/client'
import type { User } from '@/types'

export const useAuthStore = defineStore('auth', () => {
  const user = ref<User | null>(null)
  const loading = ref(false)
  const initialized = ref(false)
  const isAuthenticated = computed(() => !!user.value)
  const isAdmin = computed(() => user.value?.role === 'admin')

  async function fetchMe() {
    try {
      console.log('fetchMe: Checking session...')
      const { data } = await api.get('/api/auth/me')
      console.log('fetchMe: Authenticated as', data.user?.email)
      user.value = data.user
    } catch (err: any) {
      const status = err?.response?.status
      if (status === 401) {
        console.log('ℹfetchMe: No session (401)')
        clearAuthToken()
      } else {
        console.error('fetchMe error:', status, err?.message)
      }
      user.value = null
    } finally {
      initialized.value = true
    }
  }

  async function login(email: string, password: string) {
    loading.value = true
    try {
      const { data } = await api.post('/api/auth/login', { email, password })
      user.value = data.user
      if (data.token) {
        setAuthToken(data.token)
      }
    } finally {
      loading.value = false
    }
  }

  async function register(payload: { name: string; email: string; password: string; password_confirmation: string; phone?: string }) {
    loading.value = true
    try {
      const { data } = await api.post('/api/auth/register', payload)
      user.value = data.user
      if (data.token) {
        setAuthToken(data.token)
      }
    } finally {
      loading.value = false
    }
  }

  async function logout() {
    try { await api.post('/api/auth/logout') } catch {}
    clearAuthToken()
    user.value = null
  }

  async function updateProfile(payload: Partial<{ name: string; phone: string | null; password: string; password_confirmation: string }>) {
    const { data } = await api.patch('/api/auth/profile', payload)
    user.value = data.user
  }

  return { user, loading, initialized, isAuthenticated, isAdmin, fetchMe, login, register, logout, updateProfile }
})
