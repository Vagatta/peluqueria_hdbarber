import axios from 'axios'

// Cliente API usando tokens en lugar de cookies CSRF
// Para cross-origin entre Vercel y Render

const isDev = import.meta.env.DEV
const baseURL = isDev ? '' : (import.meta.env.VITE_API_URL || 'http://localhost:8000')

// Token storage
let authToken: string | null = localStorage.getItem('auth_token')

export function setAuthToken(token: string | null) {
  authToken = token
  if (token) {
    localStorage.setItem('auth_token', token)
  } else {
    localStorage.removeItem('auth_token')
  }
}

export function getAuthToken(): string | null {
  return authToken
}

export function clearAuthToken() {
  authToken = null
  localStorage.removeItem('auth_token')
}

export const api = axios.create({
  baseURL,
  headers: { 
    Accept: 'application/json',
    'Content-Type': 'application/json'
  },
  timeout: 30000
})

// Agregar token a cada request
api.interceptors.request.use((config) => {
  if (authToken) {
    config.headers.Authorization = `Bearer ${authToken}`
  }
  return config
})

api.interceptors.response.use(
  (r) => r,
  async (err) => {
    if (!err.response) {
      console.error('Network Error:', err.message)
      if (err.code === 'ECONNABORTED') {
        err.message = 'La petición tardó demasiado. Inténtalo de nuevo.'
      }
    }
    
    if (err?.response?.status === 401) {
      clearAuthToken()
      window.location.href = '/login'
    }
    
    return Promise.reject(err)
  }
)
